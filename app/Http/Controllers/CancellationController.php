<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CancellationRequest;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\ItemStockTransaction;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancellationController extends Controller
{
    /**
     * Customer: Request cancellation for an order
     */
    public function requestCancel(Request $request, Order $order)
    {
        $user = Auth::user();
        
        // Ensure user owns this order
        if ($order->user_id !== $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. You can only request cancellation for your own orders.'], 403);
            }
            return back()->withErrors(['error' => 'Unauthorized. You can only request cancellation for your own orders.']);
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            $denialReason = $order->getCancellationDenialReason();
            if ($request->expectsJson()) {
                return response()->json(['error' => $denialReason ?? 'This order cannot be cancelled.'], 400);
            }
            return back()->withErrors(['error' => $denialReason ?? 'This order cannot be cancelled.']);
        }

        // Check if there's already a pending cancellation request
        $existingRequest = CancellationRequest::where('order_id', $order->id)
            ->where('status', CancellationRequest::STATUS_REQUESTED)
            ->first();

        if ($existingRequest) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You already have a pending cancellation request for this order.'], 400);
            }
            return back()->withErrors(['error' => 'You already have a pending cancellation request for this order.']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ], [
            'reason.required' => 'Please provide a reason for cancellation.',
            'reason.min' => 'Reason must be at least 10 characters.',
            'reason.max' => 'Reason must not exceed 1000 characters.',
        ]);

        try {
            DB::transaction(function () use ($order, $user, $validated) {
                CancellationRequest::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'reason' => $validated['reason'],
                    'requested_by' => 'customer',
                    'status' => CancellationRequest::STATUS_REQUESTED,
                ]);
            });

            // Log audit
            $this->logAudit('cancellation_requested', $order->id, $user->id, [
                'reason' => $validated['reason'],
                'requested_by' => 'customer',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Cancellation request submitted successfully. We will review it shortly.']);
            }

            return back()->with('success', 'Cancellation request submitted successfully. We will review it shortly.');
        } catch (\Exception $e) {
            Log::error('Cancellation request submission failed', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to submit cancellation request. Please try again.'], 500);
            }

            return back()->withErrors(['error' => 'Failed to submit cancellation request. Please try again.']);
        }
    }

    /**
     * Employee: View all cancellation requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can access this page.');
        }

        $query = CancellationRequest::with(['order.user', 'user', 'handledBy'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        // Filter by order type
        if ($request->filled('order_type')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('order_type', $request->string('order_type')->toString());
            });
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to')->toString());
        }

        // Search by cancellation ID, order ID, customer name, or email
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
                        $u->where('first_name', 'like', "%$q%")
                            ->orWhere('last_name', 'like', "%$q%")
                            ->orWhere('email', 'like', "%$q%");
                    });
            });
        }

        $cancellationRequests = $query->paginate(15)->withQueryString();

        return view('employee.cancellation-requests', [
            'cancellationRequests' => $cancellationRequests,
            'activeStatus' => $request->string('status')->toString(),
        ]);
    }

    /**
     * Employee: View cancellation request details
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can access this page.');
        }

        $cancellationRequest = CancellationRequest::with([
            'order.user.address',
            'order.items.item.photos',
            'order.payments.verifier',
            'order.customOrders',
            'order.childOrders',
            'user',
            'handledBy',
        ])->findOrFail($id);

        return view('employee.cancellation-request-show', compact('cancellationRequest'));
    }

    /**
     * Employee: Approve cancellation request
     */
    public function approve($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can approve cancellation requests.');
        }

        $cancellationRequest = CancellationRequest::with(['order.items.item', 'order.customOrders', 'order.childOrders'])->findOrFail($id);

        // Check if status allows approval
        if ($cancellationRequest->status !== CancellationRequest::STATUS_REQUESTED) {
            return back()->withErrors(['error' => 'This cancellation request cannot be approved in its current status.']);
        }

        $order = $cancellationRequest->order;

        // Validate order exists
        if (!$order) {
            return back()->withErrors(['error' => 'Order not found for this cancellation request.']);
        }

        // For mixed orders, validate child orders exist
        if ($order->order_type === Order::TYPE_MIXED) {
            if (!$order->childOrders()->exists()) {
                return back()->withErrors(['error' => 'Mixed order has no child orders. Cannot process cancellation.']);
            }
            
            // Check if payment is verified for mixed orders
            if (!$order->hasVerifiedPayment()) {
                return back()->withErrors(['error' => 'Payment must be verified for all child orders before this cancellation request can be approved or rejected.']);
            }
            
            // Validate that all child orders can be cancelled
            $uncancellableChildren = [];
            foreach ($order->childOrders as $childOrder) {
                if (!$childOrder->canBeCancelled()) {
                    $denialReason = $childOrder->getCancellationDenialReason();
                    $uncancellableChildren[] = "Child Order #{$childOrder->id}: " . ($denialReason ?? 'Cannot be cancelled');
                }
            }
            
            if (!empty($uncancellableChildren)) {
                return back()->withErrors(['error' => 'Some child orders cannot be cancelled: ' . implode('; ', $uncancellableChildren)]);
            }
        } elseif (in_array($order->order_type, [Order::TYPE_BACKORDER])) {
            // Check if payment is verified for backorders
            if (!$order->hasVerifiedPayment()) {
                return back()->withErrors(['error' => 'Payment must be verified before this cancellation request can be approved or rejected.']);
            }
        } elseif ($order->order_type === Order::TYPE_CUSTOM) {
            // For custom orders, only require payment verification if they have payments
            $hasPayments = $order->payments()->exists();
            if ($hasPayments && !$order->hasVerifiedPayment()) {
                return back()->withErrors(['error' => 'Payment must be verified before this cancellation request can be approved or rejected.']);
            }
        }

        // Re-validate that order can still be cancelled
        if (!$order->canBeCancelled()) {
            $denialReason = $order->getCancellationDenialReason();
            return back()->withErrors(['error' => $denialReason ?? 'This order cannot be cancelled at this time.']);
        }

        try {
            DB::transaction(function () use ($cancellationRequest, $order, $user) {
                // Update cancellation request status
                $cancellationRequest->status = CancellationRequest::STATUS_APPROVED;
                $cancellationRequest->handled_by = $user->id;
                $cancellationRequest->save();

                // Calculate refund amount if payment was made
                $refundAmount = null;
                
                // Handle mixed orders - calculate refund from all child orders
                if ($order->order_type === Order::TYPE_MIXED && $order->childOrders()->exists()) {
                    $refundAmount = 0.0;
                    foreach ($order->childOrders as $childOrder) {
                        // Sum all verified/paid payments from child orders
                        // Include payments that are either paid or verified (approved)
                        $childPaidAmount = (float) $childOrder->payments()
                            ->where(function($query) {
                                $query->where('status', 'paid')
                                      ->orWhere('verification_status', 'approved');
                            })
                            ->sum('amount');
                        $refundAmount += $childPaidAmount;
                    }
                    
                    // Cancel all child orders and release their inventory
                    foreach ($order->childOrders as $childOrder) {
                        // Release inventory from child order
                        $this->releaseInventory($childOrder);
                        
                        // Handle custom order status for custom child orders
                        if ($childOrder->order_type === Order::TYPE_CUSTOM) {
                            $customOrder = $childOrder->customOrders()->first();
                            if ($customOrder) {
                                if (!in_array($customOrder->status, [CustomOrder::STATUS_REJECTED, CustomOrder::STATUS_COMPLETED])) {
                                    $customOrder->status = CustomOrder::STATUS_REJECTED;
                                    $customOrder->save();
                                }
                            }
                        }
                        
                        // Mark child order as cancelled
                        $childOrder->status = Order::STATUS_CANCELLED;
                        $childOrder->save();
                    }
                } elseif ($order->payment_status === 'paid' || $order->payment_status === 'partially_paid' || $order->hasVerifiedPayment()) {
                    // For single orders (not mixed), calculate refund based on order type
                    // Include payments that are either paid or verified (approved)
                    $refundAmount = (float) $order->payments()
                        ->where(function($query) {
                            $query->where('status', 'paid')
                                  ->orWhere('verification_status', 'approved');
                        })
                        ->sum('amount');
                }

                // Release inventory if order reserved stock (for non-mixed orders)
                if ($order->order_type !== Order::TYPE_MIXED) {
                    $this->releaseInventory($order);
                }

                // Handle procurement/production adjustments for backorder/custom orders (non-mixed)
                if ($order->order_type === Order::TYPE_BACKORDER) {
                    // For backorders, procurement adjustments are handled by inventory release
                    // If procurement request exists but not processed, it will be cancelled
                } elseif ($order->order_type === Order::TYPE_CUSTOM) {
                    $customOrder = $order->customOrders()->first();
                    if ($customOrder) {
                        // Update custom order status to rejected when order is cancelled
                        if (!in_array($customOrder->status, [CustomOrder::STATUS_REJECTED, CustomOrder::STATUS_COMPLETED])) {
                            $customOrder->status = CustomOrder::STATUS_REJECTED;
                            $customOrder->save();
                        }
                    }
                }

                // Update cancellation request with refund amount if applicable
                if ($refundAmount !== null && $refundAmount > 0) {
                    $cancellationRequest->refund_amount = $refundAmount;
                    $cancellationRequest->status = CancellationRequest::STATUS_REFUND_PROCESSING;
                    $cancellationRequest->save();
                    
                    // Update order status to cancelled even when refund is processing
                    // The order is effectively cancelled, refund is just a follow-up process
                    $order->status = Order::STATUS_CANCELLED;
                    $order->save();
                } else {
                    // No refund needed, mark as cancelled directly
                    $cancellationRequest->status = CancellationRequest::STATUS_CANCELLED;
                    $cancellationRequest->save();
                    
                    // Update order status
                    $order->status = Order::STATUS_CANCELLED;
                    $order->save();
                }
            });

            // Log audit
            $this->logAudit('cancellation_approved', $order->id, $user->id, [
                'cancellation_request_id' => $cancellationRequest->id,
                'order_type' => $order->order_type,
                'refund_amount' => $refundAmount ?? 0,
            ]);

            $successMessage = 'Cancellation request approved.';
            if ($order->order_type === Order::TYPE_MIXED) {
                $childCount = $order->childOrders()->count();
                $successMessage .= " {$childCount} child order(s) have been cancelled.";
            }
            if ($refundAmount !== null && $refundAmount > 0) {
                $successMessage .= " Refund amount: ₱" . number_format($refundAmount, 2) . " (processing).";
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $successMessage]);
            }

            return back()->with('success', $successMessage);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Cancellation approval failed - Database error', [
                'cancellation_request_id' => $id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Database error occurred while processing cancellation. Please contact support if this persists.'], 500);
            }

            return back()->withErrors(['error' => 'A database error occurred while processing the cancellation. Please try again or contact support.']);
        } catch (\Exception $e) {
            Log::error('Cancellation approval failed', [
                'cancellation_request_id' => $id,
                'order_id' => $order->id,
                'order_type' => $order->order_type ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to approve cancellation request. ' . ($e->getMessage() ?: 'Please try again.')], 500);
            }

            return back()->withErrors(['error' => 'Failed to approve cancellation request: ' . ($e->getMessage() ?: 'An unexpected error occurred. Please try again.')]);
        }
    }

    /**
     * Employee: Reject cancellation request
     */
    public function reject($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can reject cancellation requests.');
        }

        $cancellationRequest = CancellationRequest::with('order')->findOrFail($id);

        // Check if status allows rejection
        if ($cancellationRequest->status !== CancellationRequest::STATUS_REQUESTED) {
            return back()->withErrors(['error' => 'This cancellation request cannot be rejected in its current status.']);
        }

        $order = $cancellationRequest->order;

        // Check if payment is verified for mixed, custom, and backorder types
        // Exception: For custom orders that are not paid yet, allow approval/rejection
        if (in_array($order->order_type, [Order::TYPE_MIXED, Order::TYPE_BACKORDER])) {
            if (!$order->hasVerifiedPayment()) {
                return back()->withErrors(['error' => 'Payment must be verified before this cancellation request can be approved or rejected.']);
            }
        } elseif ($order->order_type === Order::TYPE_CUSTOM) {
            // For custom orders, only require payment verification if they have payments
            $hasPayments = $order->payments()->exists();
            if ($hasPayments && !$order->hasVerifiedPayment()) {
                return back()->withErrors(['error' => 'Payment must be verified before this cancellation request can be approved or rejected.']);
            }
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ], [
            'notes.max' => 'Notes must not exceed 1000 characters.',
        ]);

        try {
            DB::transaction(function () use ($cancellationRequest, $user, $validated) {
                $cancellationRequest->status = CancellationRequest::STATUS_REJECTED;
                $cancellationRequest->handled_by = $user->id;
                $cancellationRequest->notes = $validated['notes'] ?? null;
                $cancellationRequest->save();
            });

            // Log audit
            $this->logAudit('cancellation_rejected', $cancellationRequest->order_id, $user->id, [
                'cancellation_request_id' => $cancellationRequest->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Cancellation request rejected.']);
            }

            return back()->with('success', 'Cancellation request rejected.');
        } catch (\Exception $e) {
            Log::error('Cancellation rejection failed', [
                'cancellation_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to reject cancellation request. Please try again.'], 500);
            }

            return back()->withErrors(['error' => 'Failed to reject cancellation request. Please try again.']);
        }
    }

    /**
     * Employee: Process refund for approved cancellation
     */
    public function processRefund($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can process refunds.');
        }

        $cancellationRequest = CancellationRequest::with('order')->findOrFail($id);

        // Check if status allows refund processing
        if (!in_array($cancellationRequest->status, [
            CancellationRequest::STATUS_APPROVED,
            CancellationRequest::STATUS_REFUND_PROCESSING,
            CancellationRequest::STATUS_REFUND_FAILED,
        ])) {
            return back()->withErrors(['error' => 'Refund can only be processed for approved cancellations.']);
        }

        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_method' => 'required|in:gcash,bank_transfer',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ], [
            'refund_amount.required' => 'Please enter the refund amount.',
            'refund_amount.numeric' => 'Refund amount must be a valid number.',
            'refund_amount.min' => 'Refund amount must be greater than 0.',
            'refund_method.required' => 'Please select a refund method.',
            'refund_method.in' => 'Refund method must be either GCash or Bank Transfer.',
            'transaction_id.max' => 'Transaction ID must not exceed 100 characters.',
            'notes.max' => 'Notes must not exceed 1000 characters.',
        ]);

        $order = $cancellationRequest->order;
        
        if (!$order) {
            return back()->withErrors(['error' => 'Order not found for this cancellation request.']);
        }

        // Calculate max refund amount - for mixed orders, sum from child orders
        $maxRefundAmount = 0.0;
        if ($order->order_type === Order::TYPE_MIXED && $order->childOrders()->exists()) {
            foreach ($order->childOrders as $childOrder) {
                // Sum all verified/paid payments from child orders
                // Include payments that are either paid or verified (approved)
                $childPaidAmount = (float) $childOrder->payments()
                    ->where(function($query) {
                        $query->where('status', 'paid')
                              ->orWhere('verification_status', 'approved');
                    })
                    ->sum('amount');
                $maxRefundAmount += $childPaidAmount;
            }
        } else {
            // For single orders, get verified/paid payments from the order itself
            $maxRefundAmount = (float) $order->payments()
                ->where(function($query) {
                    $query->where('status', 'paid')
                          ->orWhere('verification_status', 'approved');
                })
                ->sum('amount');
        }
        
        $refundAmount = (float) $validated['refund_amount'];

        // Validate refund amount doesn't exceed paid amount
        if ($refundAmount > $maxRefundAmount) {
            return back()->withErrors(['refund_amount' => 'Refund amount cannot exceed the paid amount (₱' . number_format($maxRefundAmount, 2) . ').']);
        }

        try {
            DB::transaction(function () use ($cancellationRequest, $order, $validated, $user, $refundAmount, $maxRefundAmount) {
                // Update cancellation request
                $cancellationRequest->refund_amount = $validated['refund_amount'];
                $cancellationRequest->refund_method = $validated['refund_method'];
                $cancellationRequest->status = CancellationRequest::STATUS_REFUND_PROCESSING;
                $cancellationRequest->notes = ($cancellationRequest->notes ?? '') . "\n\nRefund Processed: " . ($validated['notes'] ?? 'Transaction ID: ' . ($validated['transaction_id'] ?? 'N/A'));
                $cancellationRequest->handled_by = $user->id;
                $cancellationRequest->save();

                // Process refund using existing payment system
                // Note: This assumes you have a refund mechanism in your Payment model or service
                // For now, we'll mark the refund as completed and update order status
                
                // Update order payment status
                if ($refundAmount >= $maxRefundAmount) {
                    $order->payment_status = 'refunded';
                } else {
                    // Partial refund - update remaining balance
                    $order->remaining_balance = max(0, $order->total_amount - ($maxRefundAmount - $refundAmount));
                }

                // Mark refund as completed
                $cancellationRequest->status = CancellationRequest::STATUS_REFUND_COMPLETED;
                $cancellationRequest->save();

                // Update order status to cancelled
                $order->status = Order::STATUS_CANCELLED;
                $order->save();
            });

            // Log audit
            $this->logAudit('refund_processed', $order->id, $user->id, [
                'cancellation_request_id' => $cancellationRequest->id,
                'refund_amount' => $refundAmount,
                'refund_method' => $validated['refund_method'],
                'transaction_id' => $validated['transaction_id'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Refund processed successfully.']);
            }

            return back()->with('success', 'Refund processed successfully.');
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'cancellation_request_id' => $id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark as refund failed
            try {
                $cancellationRequest->status = CancellationRequest::STATUS_REFUND_FAILED;
                $cancellationRequest->notes = ($cancellationRequest->notes ?? '') . "\n\nRefund Failed: " . $e->getMessage();
                $cancellationRequest->save();
            } catch (\Exception $e2) {
                Log::error('Failed to update cancellation request status after refund failure', [
                    'cancellation_request_id' => $id,
                    'error' => $e2->getMessage(),
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to process refund. Please try again.'], 500);
            }

            return back()->withErrors(['error' => 'Failed to process refund. Please try again.']);
        }
    }

    /**
     * Employee: Force cancel order (employee-initiated)
     */
    public function forceCancelByEmployee(Request $request, Order $order)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can force cancel orders.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:1000',
        ], [
            'reason.required' => 'Please provide a reason for cancellation.',
            'reason.min' => 'Reason must be at least 10 characters.',
            'reason.max' => 'Reason must not exceed 1000 characters.',
            'notes.max' => 'Notes must not exceed 1000 characters.',
        ]);

        // Validate order can be cancelled
        if (!$order->canBeCancelled()) {
            $denialReason = $order->getCancellationDenialReason();
            if ($request->expectsJson()) {
                return response()->json(['error' => $denialReason ?? 'This order cannot be cancelled at this time.'], 400);
            }
            return back()->withErrors(['error' => $denialReason ?? 'This order cannot be cancelled at this time.']);
        }

        // For mixed orders, validate child orders exist and can be cancelled
        if ($order->order_type === Order::TYPE_MIXED) {
            if (!$order->childOrders()->exists()) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Mixed order has no child orders. Cannot process cancellation.'], 400);
                }
                return back()->withErrors(['error' => 'Mixed order has no child orders. Cannot process cancellation.']);
            }
            
            // Validate that all child orders can be cancelled
            $uncancellableChildren = [];
            foreach ($order->childOrders as $childOrder) {
                if (!$childOrder->canBeCancelled()) {
                    $denialReason = $childOrder->getCancellationDenialReason();
                    $uncancellableChildren[] = "Child Order #{$childOrder->id}: " . ($denialReason ?? 'Cannot be cancelled');
                }
            }
            
            if (!empty($uncancellableChildren)) {
                $errorMessage = 'Some child orders cannot be cancelled: ' . implode('; ', $uncancellableChildren);
                if ($request->expectsJson()) {
                    return response()->json(['error' => $errorMessage], 400);
                }
                return back()->withErrors(['error' => $errorMessage]);
            }
        }

        try {
            DB::transaction(function () use ($order, $user, $validated) {
                // Create cancellation request with requested_by = 'employee'
                $cancellationRequest = CancellationRequest::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'reason' => $validated['reason'],
                    'requested_by' => 'employee',
                    'status' => CancellationRequest::STATUS_APPROVED,
                    'handled_by' => $user->id,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Calculate refund amount if payment was made
                $refundAmount = null;
                
                // Handle mixed orders - calculate refund from all child orders
                if ($order->order_type === Order::TYPE_MIXED && $order->childOrders()->exists()) {
                    $refundAmount = 0.0;
                    foreach ($order->childOrders as $childOrder) {
                        // Sum all verified/paid payments from child orders
                        // Include payments that are either paid or verified (approved)
                        $childPaidAmount = (float) $childOrder->payments()
                            ->where(function($query) {
                                $query->where('status', 'paid')
                                      ->orWhere('verification_status', 'approved');
                            })
                            ->sum('amount');
                        $refundAmount += $childPaidAmount;
                    }
                    
                    // Cancel all child orders and release their inventory
                    foreach ($order->childOrders as $childOrder) {
                        // Release inventory from child order
                        $this->releaseInventory($childOrder);
                        
                        // Handle custom order status for custom child orders
                        if ($childOrder->order_type === Order::TYPE_CUSTOM) {
                            $customOrder = $childOrder->customOrders()->first();
                            if ($customOrder) {
                                if (!in_array($customOrder->status, [CustomOrder::STATUS_REJECTED, CustomOrder::STATUS_COMPLETED])) {
                                    $customOrder->status = CustomOrder::STATUS_REJECTED;
                                    $customOrder->save();
                                }
                            }
                        }
                        
                        // Mark child order as cancelled
                        $childOrder->status = Order::STATUS_CANCELLED;
                        $childOrder->save();
                    }
                } elseif ($order->payment_status === 'paid' || $order->payment_status === 'partially_paid' || $order->hasVerifiedPayment()) {
                    // For single orders (not mixed), calculate refund
                    // Include payments that are either paid or verified (approved)
                    $refundAmount = (float) $order->payments()
                        ->where(function($query) {
                            $query->where('status', 'paid')
                                  ->orWhere('verification_status', 'approved');
                        })
                        ->sum('amount');
                }

                // Release inventory if order reserved stock (for non-mixed orders)
                if ($order->order_type !== Order::TYPE_MIXED) {
                    $this->releaseInventory($order);
                }

                // Update custom order status if applicable (for non-mixed orders)
                if ($order->order_type === Order::TYPE_CUSTOM) {
                    $customOrder = $order->customOrders()->first();
                    if ($customOrder) {
                        // Update custom order status to rejected when order is cancelled
                        if (!in_array($customOrder->status, [CustomOrder::STATUS_REJECTED, CustomOrder::STATUS_COMPLETED])) {
                            $customOrder->status = CustomOrder::STATUS_REJECTED;
                            $customOrder->save();
                        }
                    }
                }

                // Update cancellation request and order
                if ($refundAmount !== null && $refundAmount > 0) {
                    $cancellationRequest->refund_amount = $refundAmount;
                    $cancellationRequest->status = CancellationRequest::STATUS_REFUND_PROCESSING;
                    $cancellationRequest->save();
                } else {
                    $cancellationRequest->status = CancellationRequest::STATUS_CANCELLED;
                    $cancellationRequest->save();
                    
                    $order->status = Order::STATUS_CANCELLED;
                    $order->save();
                }
            });

            // Log audit
            $this->logAudit('force_cancellation', $order->id, $user->id, [
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'order_type' => $order->order_type,
                'refund_amount' => $refundAmount ?? 0,
            ]);

            $successMessage = 'Order cancelled successfully.';
            if ($order->order_type === Order::TYPE_MIXED) {
                $childCount = $order->childOrders()->count();
                $successMessage .= " {$childCount} child order(s) have been cancelled.";
            }
            if ($refundAmount !== null && $refundAmount > 0) {
                $successMessage .= " Refund amount: ₱" . number_format($refundAmount, 2) . " (processing).";
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $successMessage]);
            }

            return back()->with('success', $successMessage);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Force cancellation failed - Database error', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'order_type' => $order->order_type ?? 'unknown',
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Database error occurred while processing cancellation. Please contact support if this persists.'], 500);
            }

            return back()->withErrors(['error' => 'A database error occurred while processing the cancellation. Please try again or contact support.']);
        } catch (\Exception $e) {
            Log::error('Force cancellation failed', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'order_type' => $order->order_type ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to cancel order. ' . ($e->getMessage() ?: 'Please try again.')], 500);
            }

            return back()->withErrors(['error' => 'Failed to cancel order: ' . ($e->getMessage() ?: 'An unexpected error occurred. Please try again.')]);
        }
    }

    /**
     * Release inventory reserved by order
     */
    private function releaseInventory(Order $order): void
    {
        foreach ($order->items as $orderItem) {
            $item = $orderItem->item;

            if (!$orderItem->is_backorder) {
                // Standard item - release stock back
                if ($item) {
                    $item->increment('stock', $orderItem->quantity);

                    // Log stock transaction
                    ItemStockTransaction::create([
                        'item_id' => $item->id,
                        'user_id' => Auth::id(),
                        'type' => 'in',
                        'quantity' => $orderItem->quantity,
                        'remarks' => "Order #{$order->id} cancelled - Stock released",
                    ]);
                }
                continue;
            }

            // Backorder items: generally no stock was reserved. However, if a backorder
            // item was already fulfilled (i.e. stock was deducted when order moved to
            // preparing/ready_to_ship), we should return that stock on cancellation.
            // Only restore stock when the order item indicates it was fulfilled.
            try {
                $wasFulfilled = ($orderItem->backorder_status ?? null) === \App\Models\OrderItem::BO_FULFILLED;
            } catch (\Throwable $e) {
                $wasFulfilled = false;
            }

            if ($wasFulfilled && $item) {
                $item->increment('stock', $orderItem->quantity);

                ItemStockTransaction::create([
                    'item_id' => $item->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $orderItem->quantity,
                    'remarks' => "Order #{$order->id} cancelled - Backorder fulfilled item stock returned",
                ]);
            }
        }
    }

    /**
     * Log audit entry for cancellation actions
     */
    private function logAudit(string $action, int $orderId, int $userId, array $data = []): void
    {
        Log::info('Cancellation Audit', [
            'action' => $action,
            'order_id' => $orderId,
            'user_id' => $userId,
            'timestamp' => now()->toDateTimeString(),
            'data' => $data,
        ]);
    }
}
