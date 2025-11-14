<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
	{
		$query = Order::query()
			->select('orders.*') // ensure unique orders across filters that may translate to joins/exists
			->distinct()
			->with(['user', 'items.item', 'childOrders'])
			->latest();

        // Filter by order type
        $type = $request->string('type')->toString();
    if (in_array($type, ['standard', 'backorder', 'custom', 'completed', 'cancelled'], true)) {
            if ($type === 'completed') {
                $query->where('status', 'completed');
            } elseif ($type === 'cancelled') {
                $query->where('status', 'cancelled');
            } else {
                $query->where('order_type', $type);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        // Filter by backorder item status (search orders that have items with the given backorder_status)
        if ($request->filled('backorder_status')) {
            $bs = $request->string('backorder_status')->toString();
            $query->whereHas('items', function ($q) use ($bs) {
                $q->where('is_backorder', true)->where('backorder_status', $bs);
            });
        }

        // Filter by date range
        if ($request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $query->whereDate('created_at', '>=', $request->string('from')->toString());
            }
            if ($request->filled('to')) {
                $query->whereDate('created_at', '<=', $request->string('to')->toString());
            }
        }

        // Search by order id, customer name or email
        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            // Remove # symbol if present for ID search
            $idQuery = ltrim($q, '#');
            $query->where(function ($sub) use ($q, $idQuery) {
                $sub->where('id', $idQuery)
                    ->orWhere('id', 'like', "%$idQuery%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%$q%")
                          ->orWhere('email', 'like', "%$q%");
                    })
                    ->orWhere('status', 'like', "%$q%");
            });
        }

        // Exclude child orders from main list (they'll be shown under parent)
        $query->whereNull('parent_order_id');

        $orders = $query->paginate(15)->withQueryString();

        return view('employee.orders', [
            'orders' => $orders,
            'activeType' => $type,
        ]);
    }    public function show($id)
    {
        $order = Order::with([
            'user.address',
            'items.item.photos',
            'customOrders',
            'childOrders',
            'parentOrder',
            'payments.verifier',
        ])->findOrFail($id);
        // If the request expects JSON (modal usage), return JSON; otherwise render a full details page
        if (request()->expectsJson()) {
            return response()->json($order);
        }
        return view('employee.order-show', compact('order'));
    }

    /**
     * Update backorder status for a single order item.
     */
    public function updateItemBackorder($orderId, $itemId, Request $request)
    {
        $validated = $request->validate([
            'backorder_status' => 'required|in:pending_stock,in_progress,fulfilled',
            'expected_restock_date' => 'nullable|date',
        ]);

        $order = Order::with('items')->findOrFail($orderId);
        $oi = $order->items()->where('id', $itemId)->firstOrFail();

        $old = $oi->backorder_status;
        $oi->backorder_status = $validated['backorder_status'];
        $oi->save();

        // If status moved to in_progress or fulfilled, notify customer
        if ($old !== $oi->backorder_status && in_array($oi->backorder_status, [\App\Models\OrderItem::BO_IN_PROGRESS, \App\Models\OrderItem::BO_FULFILLED], true)) {
            try {
                $oi->loadMissing('order.user', 'item');
                if ($oi->order && $oi->order->user && $oi->order->user->email) {
                    \Mail::to($oi->order->user->email)->send(new \App\Mail\BackorderReady($oi));
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send backorder notification', ['error' => $e->getMessage(), 'order_item' => $oi->id]);
            }
        }

        // Optionally update order-level expected_restock_date
        if (!empty($validated['expected_restock_date'])) {
            $order->expected_restock_date = $validated['expected_restock_date'];
            $order->save();
        }

        return response()->json(['success' => true, 'item' => $oi]);
    }

    public function update($id, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,ready_to_ship,shipped,delivered,completed,cancelled,backorder,in_design,in_production,ready_for_delivery',
            'tracking_number' => 'nullable|string|max:100',
            'delivered_at' => 'nullable|date',
        ]);
        
        $order = Order::with(['payments', 'childOrders.payments'])->findOrFail($id);
        
        // Validate forward-only status transition
        if (!$order->canTransitionTo($validated['status'])) {
            $validNextStatuses = $order->getValidNextStatuses();
            $nextStatusesList = !empty($validNextStatuses) ? implode(', ', array_map('ucwords', array_map(fn($s) => str_replace('_', ' ', $s), $validNextStatuses))) : 'None (order is completed or cancelled)';
            return back()->withErrors(['status' => "Invalid status transition. Current status: " . ucwords(str_replace('_', ' ', $order->status)) . ". Valid next statuses: {$nextStatusesList}."]);
        }
        
        // Check if this is a COD order
        $isCod = $order->payment_method === 'COD';
        
        // For COD orders: Allow processing without payment, but block completion until payment is collected
        if ($isCod) {
            // Block completion if COD payment hasn't been collected
            if ($validated['status'] === 'completed' && $order->payment_status === 'pending_cod') {
                return back()->withErrors(['payment' => 'COD payment must be collected before the order can be marked as completed. Please mark COD as collected first.']);
            }
            // Allow all other status changes for COD orders (including processing, shipped, etc.)
        } else {
            // For non-COD orders, validate payment before processing
            $processingStatuses = ['processing', 'ready_to_ship', 'shipped', 'delivered', 'in_design', 'in_production', 'ready_for_delivery', 'completed'];
            if (in_array($validated['status'], $processingStatuses)) {
                if (!$order->hasVerifiedPayment()) {
                    if ($order->hasPendingPaymentVerification()) {
                        return back()->withErrors(['payment' => 'Payment verification is pending. Please verify the payment before processing this order.']);
                    }
                    
                    // Check if payment was rejected
                    $latestPayment = $order->getLatestPayment();
                    if ($latestPayment && $latestPayment->isRejected()) {
                        return back()->withErrors(['payment' => 'Payment was rejected. Cannot process order with rejected payment.']);
                    }
                    
                    return back()->withErrors(['payment' => 'Payment must be verified before the order can be processed.']);
                }
            }
        }
        
        $order->status = $validated['status'];
        
        // Save shipping fields if provided
        if (isset($validated['tracking_number'])) {
            $order->tracking_number = $validated['tracking_number'];
        }
        
        // Automatically set carrier to LBC for all orders
        $order->carrier = 'lbc';
        if (isset($validated['delivered_at'])) {
            $order->delivered_at = $validated['delivered_at'];
        }
        
        // Auto-set delivered_at when status is marked as 'delivered'
        if ($validated['status'] === 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }
        
        $order->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'order' => $order]);
        }
        return back()->with('success', 'Order updated');
    }

    public function destroy($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Order deleted');
    }

    /**
     * Verify payment for an order
     */
    public function verifyPayment($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can verify payments');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'verification_notes' => 'required_if:action,reject|nullable|string|max:500',
        ], [
            'action.required' => 'Please select an action (approve or reject)',
            'action.in' => 'Invalid action. Must be approve or reject',
            'verification_notes.required_if' => 'Rejection reason is required when rejecting payment',
            'verification_notes.max' => 'Rejection reason must not exceed 500 characters',
        ]);

        $order = Order::with(['payments', 'childOrders.payments'])->findOrFail($id);

        // Get the latest payment(s) to verify
        $paymentsToVerify = [];
        if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
            // For mixed orders, verify payments for all child orders
            foreach ($order->childOrders as $child) {
                $latestPayment = $child->payments()->latest()->first();
                if ($latestPayment && $latestPayment->isPendingVerification()) {
                    $paymentsToVerify[] = $latestPayment;
                }
            }
        } else {
            // Single order
            $latestPayment = $order->payments()->latest()->first();
            if (!$latestPayment) {
                return back()->withErrors(['payment' => 'No payment found for this order']);
            }
            if (!$latestPayment->isPendingVerification()) {
                return back()->withErrors(['payment' => 'This payment is not pending verification']);
            }
            $paymentsToVerify[] = $latestPayment;
        }

        if (empty($paymentsToVerify)) {
            return back()->withErrors(['payment' => 'No payments pending verification for this order']);
        }

        try {
            \DB::transaction(function () use ($validated, $paymentsToVerify, $order, $user) {
                $action = $validated['action'];
                $notes = $validated['verification_notes'] ?? null;

                foreach ($paymentsToVerify as $payment) {
                    $payment->verified_by = $user->id;
                    $payment->verification_status = $action === 'approve' ? 'approved' : 'rejected';
                    $payment->verification_notes = $notes;
                    $payment->verified_at = now();
                    $payment->save();

                    // If approved, update payment status to paid
                    if ($action === 'approve') {
                        $payment->status = 'paid';
                        $payment->save();

                        // Update the order's payment status
                        $paymentOrder = $payment->order;
                        $requiredAmount = (float) ($paymentOrder->required_payment_amount ?? $paymentOrder->calculateRequiredPaymentAmount());
                        $paidAmount = (float) $payment->amount;

                        if ($paidAmount >= $paymentOrder->total_amount) {
                            $paymentOrder->payment_status = 'paid';
                            $paymentOrder->remaining_balance = 0;
                            // Set order status to processing if it's still pending
                            if ($paymentOrder->status === Order::STATUS_PENDING) {
                                $paymentOrder->status = Order::STATUS_PROCESSING;
                            }
                        } else {
                            $paymentOrder->payment_status = 'partially_paid';
                            $paymentOrder->remaining_balance = max(0, $paymentOrder->total_amount - $paidAmount);
                            // For partial payments, set to backorder status
                            if ($paymentOrder->status === Order::STATUS_PENDING) {
                                $paymentOrder->status = Order::STATUS_BACKORDER;
                            }
                        }
                        $paymentOrder->save();
                    } else {
                        // Rejected
                        $paymentOrder = $payment->order;
                        $paymentOrder->payment_status = 'payment_rejected';
                        $paymentOrder->save();
                    }
                }

                // For mixed orders, update parent order status
                if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
                    $allVerified = true;
                    $allRejected = true;
                    $hasRejected = false;

                    foreach ($order->childOrders as $child) {
                        $childPayment = $child->payments()->latest()->first();
                        if ($childPayment) {
                            if ($childPayment->isVerified()) {
                                $allRejected = false;
                            } else if ($childPayment->isRejected()) {
                                $hasRejected = true;
                                $allVerified = false;
                            } else {
                                $allVerified = false;
                                $allRejected = false;
                            }
                        } else {
                            $allVerified = false;
                        }
                    }

                    if ($allVerified) {
                        $order->payment_status = 'paid';
                        // Set order status to processing if it's still pending
                        if ($order->status === Order::STATUS_PENDING) {
                            $order->status = Order::STATUS_PROCESSING;
                        }
                    } else if ($hasRejected) {
                        $order->payment_status = 'payment_rejected';
                    }
                    $order->save();
                }
            });

            $message = $validated['action'] === 'approve' 
                ? 'Payment verified and approved successfully' 
                : 'Payment rejected. Customer will be notified.';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Payment verification failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment verification failed: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['payment' => 'Payment verification failed. Please try again.']);
        }
    }

    /**
     * Mark COD payment as collected
     */
    public function collectCod($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can mark COD as collected');
        }

        $order = Order::with(['childOrders'])->findOrFail($id);

        // Validate that this is a COD order
        $isCod = $order->payment_method === 'COD';
        if (!$isCod) {
            return back()->withErrors(['payment' => 'This order is not a COD order.']);
        }

        if ($order->payment_status !== 'pending_cod') {
            return back()->withErrors(['payment' => 'This COD order has already been processed.']);
        }

        try {
            \DB::transaction(function () use ($order) {
                // Update order payment status to paid
                $order->payment_status = 'paid';
                
                // Note: Order status is not automatically changed - employee can update it as needed
                // This allows flexibility if order is already in processing/shipped/etc.
                
                $order->save();

                // For mixed orders, update child orders
                if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
                    foreach ($order->childOrders as $child) {
                        if ($child->payment_status === 'pending_cod') {
                            $child->payment_status = 'paid';
                            // Note: Child order status is not automatically changed
                            $child->save();
                        }
                    }
                }
            });

            $message = 'COD payment marked as collected. Order can now be marked as completed.';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('COD collection failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'COD collection failed: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['payment' => 'COD collection failed. Please try again.']);
        }
    }
}


