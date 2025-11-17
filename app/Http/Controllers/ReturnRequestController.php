<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReturnRequestController extends Controller
{
    /**
     * Customer: Submit return request
     */
    public function store(Request $request, Order $order)
    {
        $user = Auth::user();
        
        // Ensure user owns this order
        if ($order->user_id !== $user->id) {
            return back()->withErrors(['error' => 'Unauthorized. You can only request returns for your own orders.']);
        }

        // Check if order is cancelled - cannot return cancelled orders
        if ($order->status === 'cancelled') {
            return back()->withErrors(['error' => 'This order has been cancelled and cannot be returned.']);
        }

        // Check if order is eligible for return (must be delivered or completed)
        if (!in_array($order->status, ['delivered', 'completed'])) {
            return back()->withErrors(['error' => 'This order is not eligible for return. Only delivered or completed orders can be returned.']);
        }

        // Check if there's already an active return request for this order
        $existingRequest = ReturnRequest::where('order_id', $order->id)
            ->whereIn('status', [
                ReturnRequest::STATUS_REQUESTED,
                ReturnRequest::STATUS_APPROVED,
                ReturnRequest::STATUS_IN_TRANSIT,
                ReturnRequest::STATUS_VERIFIED,
            ])
            ->first();

        if ($existingRequest) {
            return back()->withErrors(['error' => 'You already have an active return request for this order.']);
        }

        // Check if there's already a completed return for this order
        $completedReturn = ReturnRequest::where('order_id', $order->id)
            ->whereIn('status', [
                ReturnRequest::STATUS_REFUND_COMPLETED,
                ReturnRequest::STATUS_COMPLETED,
            ])
            ->first();

        if ($completedReturn) {
            return back()->withErrors(['error' => 'This order has already been returned and processed.']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'proof_image' => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
        ], [
            'reason.required' => 'Please provide a reason for the return.',
            'reason.min' => 'Reason must be at least 10 characters.',
            'reason.max' => 'Reason must not exceed 1000 characters.',
            'proof_image.image' => 'Proof image must be an image file.',
            'proof_image.mimes' => 'Proof image must be JPG, PNG, or JPEG format.',
            'proof_image.max' => 'Proof image must not exceed 5MB.',
        ]);

        try {
            DB::transaction(function () use ($order, $user, $validated, $request) {
                $proofPath = null;
                if ($request->hasFile('proof_image')) {
                    $proofPath = $request->file('proof_image')->store('return-proofs', 'public');
                }

                ReturnRequest::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'reason' => $validated['reason'],
                    'proof_image' => $proofPath,
                    'status' => ReturnRequest::STATUS_REQUESTED,
                ]);
            });

            return back()->with('success', 'Return request submitted successfully. We will review it shortly.');
        } catch (\Exception $e) {
            \Log::error('Return request submission failed', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to submit return request. Please try again.']);
        }
    }

    /**
     * Customer: Submit return tracking number
     */
    public function submitTrackingNumber(Request $request, $returnId)
    {
        $user = Auth::user();
        $returnRequest = ReturnRequest::with('order')->findOrFail($returnId);

        // Ensure user owns this return request
        if ($returnRequest->user_id !== $user->id) {
            return back()->withErrors(['error' => 'Unauthorized.']);
        }

        // Check if status allows tracking number submission
        if ($returnRequest->status !== ReturnRequest::STATUS_APPROVED) {
            return back()->withErrors(['error' => 'Tracking number can only be submitted for approved return requests.']);
        }

        $validated = $request->validate([
            'return_tracking_number' => 'required|string|max:100',
        ], [
            'return_tracking_number.required' => 'Please enter the return tracking number.',
            'return_tracking_number.max' => 'Tracking number must not exceed 100 characters.',
        ]);

        try {
            $returnRequest->return_tracking_number = $validated['return_tracking_number'];
            $returnRequest->status = ReturnRequest::STATUS_IN_TRANSIT;
            $returnRequest->save();

            return back()->with('success', 'Return tracking number submitted successfully.');
        } catch (\Exception $e) {
            \Log::error('Return tracking number submission failed', [
                'return_id' => $returnId,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to submit tracking number. Please try again.']);
        }
    }

    /**
     * Employee: View all return requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can access this page.');
        }

        $query = ReturnRequest::with(['order.user', 'user', 'replacementOrder'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to')->toString());
        }

        // Search by order ID, customer name, or return ID
        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $idQuery = ltrim($q, '#');
            $query->where(function ($sub) use ($q, $idQuery) {
                $sub->where('id', $idQuery)
                    ->orWhere('id', 'like', "%$idQuery%")
                    ->orWhereHas('order', function ($o) use ($q) {
                        $o->where('id', $q)
                            ->orWhere('id', 'like', "%$q%");
                    })
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%$q%")
                            ->orWhere('email', 'like', "%$q%");
                    });
            });
        }

        $returnRequests = $query->paginate(15)->withQueryString();

        return view('employee.return-requests', [
            'returnRequests' => $returnRequests,
            'activeStatus' => $request->string('status')->toString(),
        ]);
    }

    /**
     * Employee: View return request details
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can access this page.');
        }

        $returnRequest = ReturnRequest::with([
            'order.user.address',
            'order.items.item.photos',
            'user',
            'replacementOrder',
        ])->findOrFail($id);

        return view('employee.return-request-show', compact('returnRequest'));
    }

    /**
     * Employee: Approve return request
     */
    public function approve($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can approve return requests.');
        }

        $returnRequest = ReturnRequest::with('order')->findOrFail($id);

        // Check if status allows approval
        if ($returnRequest->status !== ReturnRequest::STATUS_REQUESTED) {
            return back()->withErrors(['error' => 'This return request cannot be approved in its current status.']);
        }

        $order = $returnRequest->order;

        // Validate order can still be returned
        if ($order->status === 'cancelled') {
            return back()->withErrors(['error' => 'Cannot approve return for a cancelled order.']);
        }

        try {
            DB::transaction(function () use ($returnRequest, $order) {
                $returnRequest->status = ReturnRequest::STATUS_APPROVED;
                $returnRequest->save();

                // Update order status to indicate return is in process
                // Keep the order status as delivered/completed but mark that return is approved
                // The order status will be updated when return is verified/completed
            });

            return back()->with('success', 'Return request approved. Customer can now submit tracking number.');
        } catch (\Exception $e) {
            \Log::error('Return request approval failed', [
                'return_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to approve return request. Please try again.']);
        }
    }

    /**
     * Employee: Reject return request
     */
    public function reject($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can reject return requests.');
        }

        $returnRequest = ReturnRequest::with('order')->findOrFail($id);

        // Check if status allows rejection
        if (!in_array($returnRequest->status, [ReturnRequest::STATUS_REQUESTED, ReturnRequest::STATUS_APPROVED])) {
            return back()->withErrors(['error' => 'This return request cannot be rejected in its current status.']);
        }

        $order = $returnRequest->order;

        // Validate order can still be returned (though rejection is allowed even if cancelled)
        // This is just for consistency - we allow rejection of return requests even for cancelled orders

        try {
            DB::transaction(function () use ($returnRequest) {
                $returnRequest->status = ReturnRequest::STATUS_REJECTED;
                $returnRequest->save();
            });

            return back()->with('success', 'Return request rejected.');
        } catch (\Exception $e) {
            \Log::error('Return request rejection failed', [
                'return_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to reject return request. Please try again.']);
        }
    }

    /**
     * Employee: Verify return after item is physically received
     */
    public function verifyReturn($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can verify returns.');
        }

        $returnRequest = ReturnRequest::with('order')->findOrFail($id);

        // Check if status allows verification
        if ($returnRequest->status !== ReturnRequest::STATUS_IN_TRANSIT) {
            return back()->withErrors(['error' => 'Return can only be verified when status is "Return In Transit".']);
        }

        // Check if tracking number exists
        if (empty($returnRequest->return_tracking_number)) {
            return back()->withErrors(['error' => 'Tracking number is required before verification.']);
        }

        $order = $returnRequest->order;

        // Validate order can still be returned
        if ($order->status === 'cancelled') {
            return back()->withErrors(['error' => 'Cannot verify return for a cancelled order.']);
        }

        try {
            DB::transaction(function () use ($returnRequest, $order, $user) {
                $returnRequest->status = ReturnRequest::STATUS_VERIFIED;
                $returnRequest->verified_at = now();
                $returnRequest->save();

                // Update order status to indicate return is verified
                // The order remains in delivered/completed status but return is verified
                // Status will be updated to 'returned' or similar when refund/replacement is processed
            });

            return back()->with('success', 'Return verified. You can now process refund or create replacement order.');
        } catch (\Exception $e) {
            \Log::error('Return verification failed', [
                'return_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to verify return. Please try again.']);
        }
    }

    /**
     * Employee: Process refund
     */
    public function processRefund($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can process refunds.');
        }

        $returnRequest = ReturnRequest::with('order')->findOrFail($id);

        // Check if status allows refund
        if ($returnRequest->status !== ReturnRequest::STATUS_VERIFIED) {
            return back()->withErrors(['error' => 'Refund can only be processed after return is verified.']);
        }

        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_method' => 'required|in:gcash,bank',
        ], [
            'refund_amount.required' => 'Please enter the refund amount.',
            'refund_amount.numeric' => 'Refund amount must be a valid number.',
            'refund_amount.min' => 'Refund amount must be greater than 0.',
            'refund_method.required' => 'Please select a refund method.',
            'refund_method.in' => 'Refund method must be either GCash or Bank Transfer.',
        ]);

        // Validate refund amount doesn't exceed order total
        $orderTotal = (float) $returnRequest->order->total_amount;
        $refundAmount = (float) $validated['refund_amount'];
        
        if ($refundAmount > $orderTotal) {
            return back()->withErrors(['refund_amount' => 'Refund amount cannot exceed the order total (₱' . number_format($orderTotal, 2) . ').']);
        }

        $order = $returnRequest->order;

        // Validate order can still be returned
        if ($order->status === 'cancelled') {
            return back()->withErrors(['error' => 'Cannot process refund for a cancelled order.']);
        }

        try {
            DB::transaction(function () use ($returnRequest, $order, $validated) {
                $returnRequest->refund_amount = $validated['refund_amount'];
                $returnRequest->refund_method = $validated['refund_method'];
                
                // Mark return as completed - order status remains delivered/completed
                // but we track that the return has been fully processed
                $returnRequest->status = ReturnRequest::STATUS_REFUND_COMPLETED;
                $returnRequest->save();

                // Update order payment status to refunded if full refund
                if ($returnRequest->refund_amount >= $order->total_amount) {
                    $order->payment_status = 'refunded';
                }
                
                // Update custom order status if applicable
                if ($order->order_type === Order::TYPE_CUSTOM) {
                    $customOrder = $order->customOrders()->first();
                    if ($customOrder && !in_array($customOrder->status, [CustomOrder::STATUS_REJECTED, CustomOrder::STATUS_COMPLETED])) {
                        // Mark custom order as rejected when returned
                        $customOrder->status = CustomOrder::STATUS_REJECTED;
                        $customOrder->save();
                    }
                }
                
                $order->save();
            });

            return back()->with('success', 'Refund processed successfully. Return request marked as completed.');
        } catch (\Exception $e) {
            \Log::error('Refund processing failed', [
                'return_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to process refund. Please try again.']);
        }
    }

    /**
     * Employee: Create replacement order
     */
    public function createReplacementOrder($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can create replacement orders.');
        }

        $returnRequest = ReturnRequest::with(['order.items.item', 'order.user'])->findOrFail($id);

        // Check if status allows replacement
        if ($returnRequest->status !== ReturnRequest::STATUS_VERIFIED) {
            return back()->withErrors(['error' => 'Replacement order can only be created after return is verified.']);
        }

        $order = $returnRequest->order;

        // Validate order can still be returned
        if ($order->status === 'cancelled') {
            return back()->withErrors(['error' => 'Cannot create replacement order for a cancelled order.']);
        }

        // Check if replacement order already exists
        if ($returnRequest->replacement_order_id) {
            return back()->withErrors(['error' => 'A replacement order already exists for this return request.']);
        }

        try {
            DB::transaction(function () use ($returnRequest, $user) {
                $originalOrder = $returnRequest->order;
                
                // Create new order with same items
                $replacementOrder = Order::create([
                    'user_id' => $originalOrder->user_id,
                    'order_type' => $originalOrder->order_type,
                    'status' => Order::STATUS_PENDING,
                    'total_amount' => $originalOrder->total_amount,
                    'required_payment_amount' => $originalOrder->required_payment_amount,
                    'remaining_balance' => $originalOrder->remaining_balance,
                    'payment_method' => $originalOrder->payment_method,
                    'payment_status' => 'unpaid',
                    'recipient_name' => $originalOrder->recipient_name,
                    'recipient_phone' => $originalOrder->recipient_phone,
                ]);

                // Copy order items
                foreach ($originalOrder->items as $originalItem) {
                    $item = $originalItem->item;
                    
                    // Check stock availability
                    if ($item && $item->stock < $originalItem->quantity) {
                        throw new \Exception("Insufficient stock for item: {$item->name}. Required: {$originalItem->quantity}, Available: {$item->stock}");
                    }

                    OrderItem::create([
                        'order_id' => $replacementOrder->id,
                        'item_id' => $originalItem->item_id,
                        'quantity' => $originalItem->quantity,
                        'price' => $originalItem->price,
                        'subtotal' => $originalItem->subtotal,
                        'is_backorder' => $originalItem->is_backorder,
                        'backorder_status' => $originalItem->backorder_status,
                    ]);

                    // Reduce stock if not backorder
                    if ($item && !$originalItem->is_backorder) {
                        $item->stock -= $originalItem->quantity;
                        $item->save();
                    }
                }

                // Link replacement order to return request
                // Note: Status will be updated to STATUS_REPLACEMENT_SHIPPED when employee marks it as shipped
                $returnRequest->replacement_order_id = $replacementOrder->id;
                // Keep status as VERIFIED until replacement is shipped
                $returnRequest->save();
            });

            return back()->with('success', 'Replacement order created successfully.');
        } catch (\Exception $e) {
            \Log::error('Replacement order creation failed', [
                'return_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to create replacement order: ' . $e->getMessage()]);
        }
    }

    /**
     * Employee: Mark replacement as shipped
     */
    public function markReplacementShipped($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can mark replacement as shipped.');
        }

        $returnRequest = ReturnRequest::with('replacementOrder')->findOrFail($id);

        // Check if replacement order exists
        if (!$returnRequest->replacement_order_id || !$returnRequest->replacementOrder) {
            return back()->withErrors(['error' => 'No replacement order found for this return request.']);
        }

        // Check if status allows shipping (must be verified and have replacement order)
        if ($returnRequest->status !== ReturnRequest::STATUS_VERIFIED) {
            return back()->withErrors(['error' => 'Replacement order can only be marked as shipped after return is verified.']);
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
        ], [
            'tracking_number.required' => 'Please enter the tracking number.',
            'tracking_number.max' => 'Tracking number must not exceed 100 characters.',
        ]);

        try {
            DB::transaction(function () use ($returnRequest, $validated) {
                $replacementOrder = $returnRequest->replacementOrder;
                $replacementOrder->tracking_number = $validated['tracking_number'];
                $replacementOrder->carrier = 'lbc';
                $replacementOrder->status = Order::STATUS_PROCESSING; // or 'shipped' based on your flow
                $replacementOrder->save();

                $returnRequest->status = ReturnRequest::STATUS_COMPLETED;
                $returnRequest->save();
            });

            return back()->with('success', 'Replacement order marked as shipped and return request completed.');
        } catch (\Exception $e) {
            \Log::error('Replacement shipping update failed', [
                'return_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to update replacement order. Please try again.']);
        }
    }
}
