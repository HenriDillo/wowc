<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
	<style>
body{font-family:'Poppins','Inter',ui-sans-serif,system-ui;}
[x-cloak]{display:none !important;}
	</style>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family:'Poppins','Inter',ui-sans-serif,system-ui;">
	<?php echo $__env->make('partials.customer-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

	<section class="pt-24 pb-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('info')): ?>
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    <?php echo e(session('info')); ?>

                </div>
            <?php endif; ?>
            <div class="mb-6">
				<h1 class="text-2xl font-semibold text-gray-900"><?php echo e(request()->is('customer/*') ? 'Order Details' : 'Thank you for your order!'); ?></h1>
				<p class="mt-2 text-gray-600">Order <span class="font-medium text-gray-900">#<?php echo e($order->id); ?></span> • <?php echo e($order->created_at?->format('M d, Y')); ?></p>
				<?php
					// Check for cancellation and return requests
					$latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
					$latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
					
					// Determine the primary status to display
					$displayStatus = null;
					$displayStatusColor = null;
					$displayStatusLabel = null;
					
					// Priority: Cancelled > Return > Cancellation Request > Return Request > Order Status
					if ($order->status === 'cancelled') {
						$displayStatus = 'cancelled';
						$displayStatusColor = 'bg-red-100 text-red-800';
						$displayStatusLabel = 'Cancelled';
					} elseif ($latestReturn && in_array($latestReturn->status, [
						\App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
						\App\Models\ReturnRequest::STATUS_COMPLETED,
					])) {
						$displayStatus = 'returned';
						$displayStatusColor = 'bg-purple-100 text-purple-800';
						$displayStatusLabel = 'Returned';
					} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED) {
						$displayStatus = 'return_verified';
						$displayStatusColor = 'bg-green-100 text-green-800';
						$displayStatusLabel = 'Return Verified';
					} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT) {
						$displayStatus = 'return_in_transit';
						$displayStatusColor = 'bg-indigo-100 text-indigo-800';
						$displayStatusLabel = 'Return In Transit';
					} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED) {
						$displayStatus = 'return_approved';
						$displayStatusColor = 'bg-blue-100 text-blue-800';
						$displayStatusLabel = 'Return Approved';
					} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED) {
						$displayStatus = 'return_requested';
						$displayStatusColor = 'bg-yellow-100 text-yellow-800';
						$displayStatusLabel = 'Return Requested';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED) {
						$displayStatus = 'cancelled_refunded';
						$displayStatusColor = 'bg-green-100 text-green-800';
						$displayStatusLabel = 'Cancelled - Refunded';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING) {
						$displayStatus = 'cancellation_refund_processing';
						$displayStatusColor = 'bg-indigo-100 text-indigo-800';
						$displayStatusLabel = 'Cancellation - Refund Processing';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED) {
						$displayStatus = 'cancellation_approved';
						$displayStatusColor = 'bg-blue-100 text-blue-800';
						$displayStatusLabel = 'Cancellation Approved';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED) {
						$displayStatus = 'cancellation_requested';
						$displayStatusColor = 'bg-yellow-100 text-yellow-800';
						$displayStatusLabel = 'Cancellation Requested';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REJECTED) {
						$displayStatus = 'cancellation_rejected';
						$displayStatusColor = 'bg-red-100 text-red-800';
						$displayStatusLabel = 'Cancellation Rejected';
					} else {
						// Use normal order status
						$statusColor = [
							'pending' => 'bg-yellow-100 text-yellow-800',
							'processing' => 'bg-blue-100 text-blue-800',
							'ready_to_ship' => 'bg-indigo-100 text-indigo-800',
							'shipped' => 'bg-purple-100 text-purple-800',
							'delivered' => 'bg-green-100 text-green-800',
							'completed' => 'bg-green-100 text-green-800',
							'cancelled' => 'bg-red-100 text-red-800',
							'backorder' => 'bg-indigo-100 text-indigo-800',
							'in_design' => 'bg-blue-100 text-blue-800',
							'in_production' => 'bg-indigo-100 text-indigo-800',
							'ready_for_delivery' => 'bg-purple-100 text-purple-800',
						][$order->status] ?? 'bg-gray-100 text-gray-800';
						
						// Friendly status labels based on order type
						$statusLabels = [
							'standard' => [
								'pending' => 'Order Placed',
								'processing' => 'Processing',
								'ready_to_ship' => 'Ready to Ship',
								'shipped' => 'Shipped',
								'delivered' => 'Delivered',
								'completed' => 'Completed',
								'cancelled' => 'Cancelled',
							],
							'backorder' => [
								'pending' => 'Order Placed',
								'processing' => 'Awaiting Stock',
								'ready_to_ship' => 'Preparing to Ship',
								'shipped' => 'Shipped',
								'delivered' => 'Delivered',
								'completed' => 'Completed',
								'cancelled' => 'Cancelled',
							],
							'custom' => [
								'pending' => 'Awaiting Price',
								'in_design' => 'In Design',
								'in_production' => 'In Production',
								'ready_for_delivery' => 'Ready for Delivery',
								'ready_to_ship' => 'Ready to Ship',
								'shipped' => 'Shipped',
								'delivered' => 'Delivered',
								'completed' => 'Completed',
								'cancelled' => 'Cancelled',
							],
						];
						
						$orderType = $order->order_type;
						$currentStatus = $order->status;
						$displayStatusLabel = $statusLabels[$orderType][$currentStatus] ?? ucwords(str_replace('_', ' ', $currentStatus));
						$displayStatusColor = $statusColor;
					}
					
                    $hasBackorder = $order->items->contains(fn($oi) => ($oi->is_backorder ?? false));
                    $isCustomOrder = $order->order_type === 'custom';
                    $customOrder = $isCustomOrder ? $order->customOrders->first() : null;
				?>
                <div class="mt-2 text-sm flex flex-wrap gap-2">
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-100 text-gray-800">Type: <?php echo e($order->order_type); ?></span>
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($displayStatusColor); ?>">Status: <?php echo e($displayStatusLabel); ?></span>
					<?php if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED): ?>
						<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⚠️ Cancellation Pending</span>
					<?php endif; ?>
					<?php if($latestReturn && in_array($latestReturn->status, [
						\App\Models\ReturnRequest::STATUS_REQUESTED,
						\App\Models\ReturnRequest::STATUS_APPROVED,
						\App\Models\ReturnRequest::STATUS_IN_TRANSIT,
					])): ?>
						<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">🔄 Return In Progress</span>
					<?php endif; ?>
                </div>

                <!-- Parent-Sub Order Info -->
                <?php if($order->order_type === 'mixed' && $order->childOrders->isNotEmpty()): ?>
                    <div class="mt-4 p-4 rounded-md border border-purple-200 bg-purple-50">
                        <h3 class="font-medium text-purple-900">Mixed Order Details</h3>
                        <p class="text-sm text-purple-800 mt-1">Your order contains both standard and back order items. They will be processed and shipped separately for efficiency.</p>
                        <div class="mt-3 space-y-2">
                            <?php $__currentLoopData = $order->childOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center justify-between bg-white px-3 py-2 rounded border border-purple-100 text-sm">
                                    <span class="font-medium text-gray-900"><?php echo e(ucfirst($child->order_type)); ?> Sub-Order #<?php echo e($child->id); ?></span>
                                    <span class="text-purple-700">₱<?php echo e(number_format($child->total_amount, 2)); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="mt-3 pt-3 border-t border-purple-200">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-purple-900">Total Amount</span>
                                <span class="text-lg font-bold text-purple-900">₱<?php echo e(number_format($order->total_amount, 2)); ?></span>
                            </div>
                        </div>
                    </div>
                <?php elseif($order->parent_order_id): ?>
                    <!-- This is a sub-order -->
                    <?php $parentOrder = $order->parentOrder; ?>
                    <div class="mt-4 p-4 rounded-md border border-purple-200 bg-purple-50">
                        <h3 class="font-medium text-purple-900">Part of Mixed Order</h3>
                        <p class="text-sm text-purple-800 mt-1">This is a <?php echo e(ucfirst($order->order_type)); ?> sub-order from your parent mixed order.</p>
                        <div class="mt-2 text-sm">
                            <strong>Parent Order:</strong> #<?php echo e($parentOrder->id); ?> (₱<?php echo e(number_format($parentOrder->total_amount, 2)); ?>)
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($hasBackorder && !$order->parent_order_id && $order->order_type !== 'mixed'): ?>
                    <?php
                        $standardItems = $order->items->filter(fn($oi) => !($oi->is_backorder ?? false));
                        $backOrderItems = $order->items->filter(fn($oi) => ($oi->is_backorder ?? false));
                        $isOrderCancelled = $order->status === 'cancelled';
                        $isOrderCompleted = in_array($order->status, ['delivered', 'completed']);
                        $isOrderShipped = in_array($order->status, ['shipped', 'delivered', 'completed']);
                        $isOrderProcessing = in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed']);
                        $latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
                        $latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
                    ?>
                    <div class="mt-4 p-4 rounded-md border <?php echo e($isOrderCancelled ? 'border-red-200 bg-red-50' : 'border-blue-200 bg-blue-50'); ?>">
                        <h3 class="font-medium <?php echo e($isOrderCancelled ? 'text-red-900' : 'text-blue-900'); ?>">Order Status</h3>
                        <div class="mt-2 text-sm <?php echo e($isOrderCancelled ? 'text-red-800' : 'text-blue-800'); ?> space-y-1">
                            <?php if($isOrderCancelled): ?>
                                <p><strong>✗ Order:</strong> Cancelled</p>
                                <?php if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED): ?>
                                    <p><strong>✓ Refund:</strong> Completed</p>
                                <?php elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING): ?>
                                    <p><strong>⏳ Refund:</strong> Processing</p>
                                <?php endif; ?>
                                
                                <?php if($latestCancellation && ($latestCancellation->refund_amount || $latestCancellation->refund_method)): ?>
                                    <div class="mt-3 pt-3 border-t border-red-200">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Refund Details</h4>
                                        <div class="space-y-1.5 text-xs">
                                            <?php if($latestCancellation->refund_amount): ?>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Refund Amount:</span>
                                                    <span class="font-semibold text-gray-900">₱<?php echo e(number_format($latestCancellation->refund_amount, 2)); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($latestCancellation->refund_method): ?>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Refund Method:</span>
                                                    <span class="font-medium text-gray-900"><?php echo e(ucfirst(str_replace('_', ' ', $latestCancellation->refund_method))); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED): ?>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Status:</span>
                                                    <span class="font-medium text-green-700">Completed</span>
                                                </div>
                                            <?php elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING): ?>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Status:</span>
                                                    <span class="font-medium text-indigo-700">Processing</span>
                                                </div>
                                            <?php elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_FAILED): ?>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Status:</span>
                                                    <span class="font-medium text-red-700">Failed</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($latestCancellation->handledBy): ?>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Processed By:</span>
                                                    <span class="text-gray-900"><?php echo e($latestCancellation->handledBy->name); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($latestCancellation->updated_at): ?>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Processed Date:</span>
                                                    <span class="text-gray-900"><?php echo e($latestCancellation->updated_at->format('M d, Y h:i A')); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php
                                                // Extract transaction ID from notes if available
                                                $transactionId = null;
                                                if ($latestCancellation->notes) {
                                                    if (preg_match('/Transaction ID:\s*([^\n]+)/i', $latestCancellation->notes, $matches)) {
                                                        $transactionId = trim($matches[1]);
                                                    } elseif (preg_match('/Refund Processed:.*?Transaction ID:\s*([^\n]+)/i', $latestCancellation->notes, $matches)) {
                                                        $transactionId = trim($matches[1]);
                                                    }
                                                }
                                            ?>
                                            <?php if($transactionId): ?>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Transaction ID:</span>
                                                    <span class="text-gray-900 font-mono text-xs"><?php echo e($transactionId); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php elseif($latestReturn && in_array($latestReturn->status, [
                                \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                \App\Models\ReturnRequest::STATUS_COMPLETED,
                            ])): ?>
                                <p><strong>✓ Order:</strong> Returned</p>
                            <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED): ?>
                                <p><strong>✓ Return:</strong> Verified - Refund processing</p>
                            <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT): ?>
                                <p><strong>🔄 Return:</strong> In Transit</p>
                            <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED): ?>
                                <p><strong>✓ Return:</strong> Approved - Please submit tracking number</p>
                            <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED): ?>
                                <p><strong>⏳ Return:</strong> Request Pending Review</p>
                            <?php elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED): ?>
                                <p><strong>⏳ Cancellation:</strong> Request Pending Review</p>
                            <?php elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED): ?>
                                <p><strong>✓ Cancellation:</strong> Approved</p>
                            <?php else: ?>
                                <?php if($standardItems->isNotEmpty()): ?>
                                    <?php if($isOrderCompleted): ?>
                                        <p><strong>✓ Standard Items:</strong> Delivered</p>
                                    <?php elseif($isOrderShipped): ?>
                                        <p><strong>✓ Standard Items:</strong> Shipped</p>
                                    <?php elseif($isOrderProcessing): ?>
                                        <p><strong>✓ Standard Items:</strong> Processing</p>
                                    <?php else: ?>
                                        <p><strong>✓ Standard Items:</strong> Ready for processing and will ship soon</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if($backOrderItems->isNotEmpty()): ?>
                                    <?php if($isOrderCompleted): ?>
                                        <p><strong>✓ Back Order Items:</strong> Delivered</p>
                                    <?php elseif($isOrderShipped): ?>
                                        <p><strong>✓ Back Order Items:</strong> Shipped</p>
                                    <?php elseif($order->status === 'ready_to_ship'): ?>
                                        <p><strong>✓ Back Order Items:</strong> Preparing to ship</p>
                                    <?php else: ?>
                                        <p><strong>⏳ Back Order Items:</strong> Awaiting stock - will ship separately once restocked</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($isCustomOrder && $customOrder): ?>
                    <?php
                        $isOrderCancelled = $order->status === 'cancelled';
                        $latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
                        $isOrderReturned = $latestReturn && in_array($latestReturn->status, [
                            \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                            \App\Models\ReturnRequest::STATUS_COMPLETED,
                        ]);
                        $customStatusColor = match($customOrder->status) {
                            'pending_review' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'in_production' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                        $borderColor = $customOrder->status === 'rejected' || $isOrderCancelled || $isOrderReturned ? 'border-red-200 bg-red-50' : ($customOrder->status === 'approved' ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50');
                        $textColor = $customOrder->status === 'rejected' || $isOrderCancelled || $isOrderReturned ? 'text-red-900' : ($customOrder->status === 'approved' ? 'text-green-900' : 'text-yellow-900');
                        $contentColor = $customOrder->status === 'rejected' || $isOrderCancelled || $isOrderReturned ? 'text-red-800' : ($customOrder->status === 'approved' ? 'text-green-800' : 'text-yellow-800');
                    ?>
                    <div class="mt-4 p-4 rounded-md border <?php echo e($borderColor); ?>">
                        <h3 class="font-medium <?php echo e($textColor); ?>">Custom Order Status</h3>
                        <div class="mt-2 text-sm <?php echo e($contentColor); ?> space-y-2">
                            <p><strong>Status:</strong> <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($customStatusColor); ?>"><?php echo e(str_replace('_', ' ', ucfirst($customOrder->status))); ?></span></p>
                            
                            <?php if($isOrderCancelled): ?>
                                <p class="italic">Your custom order has been cancelled.</p>
                            <?php elseif($isOrderReturned): ?>
                                <p class="italic">Your custom order has been returned.</p>
                            <?php elseif($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED): ?>
                                <?php
                                    $orderStatus = $customOrder->order->status ?? 'pending';
                                    $isOrderCompleted = in_array($orderStatus, ['delivered', 'completed']);
                                    $isOrderShipped = in_array($orderStatus, ['shipped', 'delivered', 'completed']);
                                    $isOrderInProduction = in_array($orderStatus, ['in_production', 'ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed']);
                                ?>
                                <?php if($customOrder->price_estimate): ?>
                                    <p><strong>Price:</strong> ₱<?php echo e(number_format((float)$customOrder->price_estimate, 2)); ?></p>
                                <?php endif; ?>
                                <?php if($customOrder->estimated_completion_date): ?>
                                    <p><strong>Expected Completion Date:</strong> <?php echo e($customOrder->estimated_completion_date->format('M d, Y')); ?></p>
                                <?php endif; ?>
                                <?php if($isOrderCompleted): ?>
                                    <p class="text-xs mt-2 italic text-green-700">✓ Your custom order has been delivered.</p>
                                <?php elseif($isOrderShipped): ?>
                                    <p class="text-xs mt-2 italic text-purple-700">✓ Your custom order has been shipped.</p>
                                <?php elseif($isOrderInProduction): ?>
                                    <p class="text-xs mt-2 italic text-blue-700">Your custom order is currently in production.</p>
                                <?php elseif(!$customOrder->order || !$customOrder->order->isFullyPaid()): ?>
                                    <p class="text-xs mt-2 italic">Your order has been accepted. Please proceed to payment to begin production.</p>
                                <?php else: ?>
                                    <p class="text-xs mt-2 italic text-blue-700">Payment received. Production will begin soon.</p>
                                <?php endif; ?>
                            <?php elseif($customOrder->status === \App\Models\CustomOrder::STATUS_REJECTED): ?>
                                <?php if($customOrder->rejection_note): ?>
                                    <div class="mt-2 p-3 bg-white border border-red-200 rounded-md">
                                        <p class="font-semibold text-red-900 mb-1">Rejection Reason:</p>
                                        <p class="text-red-800 whitespace-pre-line"><?php echo e($customOrder->rejection_note); ?></p>
                                    </div>
                                <?php endif; ?>
                            <?php elseif($customOrder->status === \App\Models\CustomOrder::STATUS_PENDING_REVIEW): ?>
                                <p class="text-xs mt-2 italic">Your order is currently under review. We'll notify you once a decision has been made.</p>
                                <?php if($customOrder->price_estimate): ?>
                                    <p><strong>Price Estimate:</strong> ₱<?php echo e(number_format((float)$customOrder->price_estimate, 2)); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if($customOrder->price_estimate): ?>
                                    <p><strong>Price Estimate:</strong> ₱<?php echo e(number_format((float)$customOrder->price_estimate, 2)); ?></p>
                                <?php endif; ?>
                                <?php if($customOrder->estimated_completion_date): ?>
                                    <p><strong>Estimated Completion:</strong> <?php echo e($customOrder->estimated_completion_date->format('M d, Y')); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>			<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <?php if($isCustomOrder && $customOrder): ?>
                        <!-- Custom Order Details -->
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                            <h2 class="font-semibold text-gray-900">Custom Order Details</h2>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Product Name</div>
                                    <div class="mt-1 text-gray-900 font-semibold"><?php echo e($customOrder->custom_name); ?></div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Description</div>
                                    <div class="mt-1 text-gray-900 whitespace-pre-line"><?php echo e($customOrder->description); ?></div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Quantity</div>
                                    <div class="mt-1 text-gray-900"><?php echo e($customOrder->quantity); ?></div>
                                </div>
                                
                                <?php if($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED): ?>
                                    <div class="pt-4 border-t border-gray-200 space-y-2">
                                        <div>
                                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Price</div>
                                            <div class="mt-1 text-gray-900 font-semibold text-lg">₱<?php echo e(number_format((float)$customOrder->price_estimate, 2)); ?></div>
                                        </div>
                                        <?php if($customOrder->estimated_completion_date): ?>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Expected Completion Date</div>
                                                <div class="mt-1 text-gray-900"><?php echo e($customOrder->estimated_completion_date->format('M d, Y')); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif($customOrder->status === \App\Models\CustomOrder::STATUS_REJECTED && $customOrder->rejection_note): ?>
                                    <div class="pt-4 border-t border-gray-200">
                                        <div class="p-3 bg-red-50 border border-red-200 rounded-md">
                                            <div class="text-xs font-medium text-red-900 uppercase tracking-wide mb-2">Rejection Reason</div>
                                            <div class="text-sm text-red-800 whitespace-pre-line"><?php echo e($customOrder->rejection_note); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                            <h2 class="font-semibold text-gray-900">Reference Images</h2>
                            <?php
                                $images = data_get($customOrder->customization_details, 'images', []);
                                // Fallback to single image for backward compatibility
                                if (empty($images) && $customOrder->reference_image_path) {
                                    $images = [$customOrder->reference_image_path];
                                }
                            ?>
                            <?php if(!empty($images)): ?>
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $imagePath): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="relative">
                                            <img src="<?php echo e(Storage::url($imagePath)); ?>" alt="Reference Image <?php echo e($loop->iteration); ?>" class="w-full h-auto rounded-lg border border-gray-200 shadow-sm object-cover">
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php else: ?>
                                <p class="mt-4 text-sm text-gray-500">No reference images provided.</p>
                            <?php endif; ?>
                        </div>

                        <?php if($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED): ?>
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                                <h2 class="font-semibold text-gray-900">Payment</h2>
                                <?php
                                    $orderStatus = $customOrder->order->status ?? 'pending';
                                    $isOrderCompleted = in_array($orderStatus, ['delivered', 'completed']);
                                    $isOrderShipped = in_array($orderStatus, ['shipped', 'delivered', 'completed']);
                                    $isOrderInProduction = in_array($orderStatus, ['in_production', 'ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed']);
                                ?>
                                <?php if($customOrder->order && $customOrder->order->isFullyPaid()): ?>
                                    <?php if($isOrderCompleted): ?>
                                        <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm font-medium text-green-800">Fully Paid</span>
                                            </div>
                                            <p class="mt-2 text-xs text-green-700">✓ Your custom order has been delivered.</p>
                                        </div>
                                    <?php elseif($isOrderShipped): ?>
                                        <div class="mt-3 p-3 bg-purple-50 border border-purple-200 rounded-md">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm font-medium text-purple-800">Fully Paid</span>
                                            </div>
                                            <p class="mt-2 text-xs text-purple-700">✓ Your custom order has been shipped.</p>
                                        </div>
                                    <?php elseif($isOrderInProduction): ?>
                                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm font-medium text-blue-800">Fully Paid</span>
                                            </div>
                                            <p class="mt-2 text-xs text-blue-700">Your custom order is currently in production.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm font-medium text-green-800">Fully Paid</span>
                                            </div>
                                            <p class="mt-2 text-xs text-green-700">Your payment has been completed. Production will begin soon.</p>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="mt-3 text-sm text-gray-700">Your custom order has been confirmed. Please complete payment to begin production.</p>
                                    <div class="mt-4">
                                        <a href="<?php echo e(route('checkout.page', ['order_id' => $customOrder->order?->id])); ?>" class="inline-flex items-center px-4 py-2 rounded-md text-white font-medium hover:opacity-95 transition-opacity" style="background:#c59d5f;">Proceed to Checkout</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Standard/Back Order Items -->
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                            <h2 class="font-semibold text-gray-900">Items</h2>
                            
                            <?php
                                $standardItems = $order->items->filter(fn($oi) => !($oi->is_backorder ?? false));
                                $backOrderItems = $order->items->filter(fn($oi) => ($oi->is_backorder ?? false));
                            ?>

                            <!-- Standard Items Section -->
                            <?php if($standardItems->isNotEmpty()): ?>
                                <?php
                                    $isOrderCancelled = $order->status === 'cancelled';
                                    $isOrderCompleted = in_array($order->status, ['delivered', 'completed']);
                                    $isOrderShipped = in_array($order->status, ['shipped', 'delivered', 'completed']);
                                    $isOrderProcessing = in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed']);
                                    $latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
                                    $latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
                                    
                                    // Determine status based on cancellation/return first
                                    if ($isOrderCancelled) {
                                        $standardStatusLabel = 'Cancelled';
                                        $standardStatusColor = 'bg-red-100 text-red-800';
                                    } elseif ($latestReturn && in_array($latestReturn->status, [
                                        \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                        \App\Models\ReturnRequest::STATUS_COMPLETED,
                                    ])) {
                                        $standardStatusLabel = 'Returned';
                                        $standardStatusColor = 'bg-purple-100 text-purple-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED) {
                                        $standardStatusLabel = 'Return Verified';
                                        $standardStatusColor = 'bg-green-100 text-green-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT) {
                                        $standardStatusLabel = 'Return In Transit';
                                        $standardStatusColor = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED) {
                                        $standardStatusLabel = 'Return Approved';
                                        $standardStatusColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED) {
                                        $standardStatusLabel = 'Return Requested';
                                        $standardStatusColor = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED) {
                                        $standardStatusLabel = 'Cancelled - Refunded';
                                        $standardStatusColor = 'bg-green-100 text-green-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING) {
                                        $standardStatusLabel = 'Refund Processing';
                                        $standardStatusColor = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED) {
                                        $standardStatusLabel = 'Cancellation Approved';
                                        $standardStatusColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED) {
                                        $standardStatusLabel = 'Cancellation Requested';
                                        $standardStatusColor = 'bg-yellow-100 text-yellow-800';
                                    } else {
                                        $standardStatusLabel = $isOrderCompleted ? 'Delivered' : ($isOrderShipped ? 'Shipped' : ($order->status === 'ready_to_ship' ? 'Ready to Ship' : ($isOrderProcessing ? 'Processing' : 'Ready for processing')));
                                        $standardStatusColor = $isOrderCompleted ? 'bg-green-100 text-green-800' : ($isOrderShipped ? 'bg-purple-100 text-purple-800' : ($order->status === 'ready_to_ship' ? 'bg-indigo-100 text-indigo-800' : ($isOrderProcessing ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')));
                                    }
                                ?>
                                <div class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="font-medium text-gray-900">Standard Items</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($standardStatusColor); ?>"><?php echo e($standardStatusLabel); ?></span>
                                    </div>
                                    <?php if($isOrderCancelled): ?>
                                        <div class="p-3 mb-3 bg-red-50 border border-red-100 rounded-lg">
                                            <p class="text-sm text-red-700">✗ This order has been cancelled.</p>
                                            <?php if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED): ?>
                                                <p class="text-sm text-green-700 mt-1">✓ Refund has been processed.</p>
                                            <?php elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING): ?>
                                                <p class="text-sm text-indigo-700 mt-1">⏳ Refund is being processed.</p>
                                            <?php endif; ?>
                                            
                                            <?php if($latestCancellation && ($latestCancellation->refund_amount || $latestCancellation->refund_method)): ?>
                                                <div class="mt-3 pt-3 border-t border-red-200">
                                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Refund Details</h4>
                                                    <div class="space-y-1.5 text-xs">
                                                        <?php if($latestCancellation->refund_amount): ?>
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Refund Amount:</span>
                                                                <span class="font-semibold text-gray-900">₱<?php echo e(number_format($latestCancellation->refund_amount, 2)); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if($latestCancellation->refund_method): ?>
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Refund Method:</span>
                                                                <span class="font-medium text-gray-900"><?php echo e(ucfirst(str_replace('_', ' ', $latestCancellation->refund_method))); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED): ?>
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Status:</span>
                                                                <span class="font-medium text-green-700">Completed</span>
                                                            </div>
                                                        <?php elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING): ?>
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Status:</span>
                                                                <span class="font-medium text-indigo-700">Processing</span>
                                                            </div>
                                                        <?php elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_FAILED): ?>
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Status:</span>
                                                                <span class="font-medium text-red-700">Failed</span>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if($latestCancellation->handledBy): ?>
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Processed By:</span>
                                                                <span class="text-gray-900"><?php echo e($latestCancellation->handledBy->name); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if($latestCancellation->updated_at): ?>
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Processed Date:</span>
                                                                <span class="text-gray-900"><?php echo e($latestCancellation->updated_at->format('M d, Y h:i A')); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php
                                                            // Extract transaction ID from notes if available
                                                            $transactionId = null;
                                                            if ($latestCancellation->notes) {
                                                                if (preg_match('/Transaction ID:\s*([^\n]+)/i', $latestCancellation->notes, $matches)) {
                                                                    $transactionId = trim($matches[1]);
                                                                } elseif (preg_match('/Refund Processed:.*?Transaction ID:\s*([^\n]+)/i', $latestCancellation->notes, $matches)) {
                                                                    $transactionId = trim($matches[1]);
                                                                }
                                                            }
                                                        ?>
                                                        <?php if($transactionId): ?>
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Transaction ID:</span>
                                                                <span class="text-gray-900 font-mono text-xs"><?php echo e($transactionId); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif($latestReturn && in_array($latestReturn->status, [
                                        \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                        \App\Models\ReturnRequest::STATUS_COMPLETED,
                                    ])): ?>
                                        <div class="p-3 mb-3 bg-purple-50 border border-purple-100 rounded-lg">
                                            <p class="text-sm text-purple-700">✓ This order has been returned and processed.</p>
                                        </div>
                                    <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED): ?>
                                        <div class="p-3 mb-3 bg-green-50 border border-green-100 rounded-lg">
                                            <p class="text-sm text-green-700">✓ Return has been verified. Refund is being processed.</p>
                                        </div>
                                    <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT): ?>
                                        <div class="p-3 mb-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                                            <p class="text-sm text-indigo-700">🔄 Return is in transit. We'll verify once received.</p>
                                        </div>
                                    <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED): ?>
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">✓ Return approved. Please submit your tracking number.</p>
                                        </div>
                                    <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED): ?>
                                        <div class="p-3 mb-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                                            <p class="text-sm text-yellow-700">⏳ Return request is pending review.</p>
                                        </div>
                                    <?php elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED): ?>
                                        <div class="p-3 mb-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                                            <p class="text-sm text-yellow-700">⏳ Cancellation request is pending review.</p>
                                        </div>
                                    <?php elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED): ?>
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">✓ Cancellation approved. Processing refund.</p>
                                        </div>
                                    <?php elseif($isOrderCompleted): ?>
                                        <div class="p-3 mb-3 bg-green-50 border border-green-100 rounded-lg">
                                            <p class="text-sm text-green-700">✓ These items have been delivered.</p>
                                        </div>
                                    <?php elseif($isOrderShipped): ?>
                                        <div class="p-3 mb-3 bg-purple-50 border border-purple-100 rounded-lg">
                                            <p class="text-sm text-purple-700">✓ These items have been shipped.</p>
                                        </div>
                                    <?php elseif($order->status === 'ready_to_ship'): ?>
                                        <div class="p-3 mb-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                                            <p class="text-sm text-indigo-700">✓ These items are ready to ship.</p>
                                        </div>
                                    <?php elseif($isOrderProcessing): ?>
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">These items are being processed.</p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="divide-y">
                                        <?php $__currentLoopData = $standardItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="py-4 flex items-center gap-4">
                                                <?php $photo = optional(optional($oi->item?->photos)->first())->url; ?>
                                                <?php if($photo): ?>
                                                    <img src="<?php echo e($photo); ?>" class="w-16 h-16 rounded object-cover bg-gray-100" alt="<?php echo e($oi->item?->name); ?>"/>
                                                <?php else: ?>
                                                    <div class="w-16 h-16 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-900"><?php echo e($oi->item?->name); ?></div>
                                                    <div class="text-xs text-gray-500">Qty: <?php echo e($oi->quantity); ?> • ₱<?php echo e(number_format($oi->price, 2)); ?></div>
                                                </div>
                                                <div class="text-sm font-medium">₱<?php echo e(number_format($oi->subtotal, 2)); ?></div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Back Order Items Section -->
                            <?php if($backOrderItems->isNotEmpty()): ?>
                                <?php
                                    $isOrderCancelled = $order->status === 'cancelled';
                                    $isOrderCompleted = in_array($order->status, ['delivered', 'completed']);
                                    $isOrderShipped = in_array($order->status, ['shipped', 'delivered', 'completed']);
                                    $latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
                                    $latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
                                    
                                    // Determine status based on cancellation/return first
                                    if ($isOrderCancelled) {
                                        $backorderStatusLabel = 'Cancelled';
                                        $backorderStatusColor = 'bg-red-100 text-red-800';
                                    } elseif ($latestReturn && in_array($latestReturn->status, [
                                        \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                        \App\Models\ReturnRequest::STATUS_COMPLETED,
                                    ])) {
                                        $backorderStatusLabel = 'Returned';
                                        $backorderStatusColor = 'bg-purple-100 text-purple-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED) {
                                        $backorderStatusLabel = 'Return Verified';
                                        $backorderStatusColor = 'bg-green-100 text-green-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT) {
                                        $backorderStatusLabel = 'Return In Transit';
                                        $backorderStatusColor = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED) {
                                        $backorderStatusLabel = 'Return Approved';
                                        $backorderStatusColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED) {
                                        $backorderStatusLabel = 'Return Requested';
                                        $backorderStatusColor = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED) {
                                        $backorderStatusLabel = 'Cancelled - Refunded';
                                        $backorderStatusColor = 'bg-green-100 text-green-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING) {
                                        $backorderStatusLabel = 'Refund Processing';
                                        $backorderStatusColor = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED) {
                                        $backorderStatusLabel = 'Cancellation Approved';
                                        $backorderStatusColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED) {
                                        $backorderStatusLabel = 'Cancellation Requested';
                                        $backorderStatusColor = 'bg-yellow-100 text-yellow-800';
                                    } else {
                                        $backorderStatusLabel = $isOrderCompleted ? 'Delivered' : ($isOrderShipped ? 'Shipped' : ($order->status === 'ready_to_ship' ? 'Preparing to Ship' : 'Awaiting stock'));
                                        $backorderStatusColor = $isOrderCompleted ? 'bg-green-100 text-green-800' : ($isOrderShipped ? 'bg-purple-100 text-purple-800' : ($order->status === 'ready_to_ship' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800'));
                                    }
                                ?>
                                <div class="mt-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="font-medium text-gray-900">Back Order Items</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($backorderStatusColor); ?>"><?php echo e($backorderStatusLabel); ?></span>
                                    </div>
                                    <?php if($isOrderCancelled): ?>
                                        <div class="p-3 mb-3 bg-red-50 border border-red-100 rounded-lg">
                                            <p class="text-sm text-red-700">✗ This order has been cancelled.</p>
                                            <?php if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED): ?>
                                                <p class="text-sm text-green-700 mt-1">✓ Refund has been processed.</p>
                                            <?php elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING): ?>
                                                <p class="text-sm text-indigo-700 mt-1">⏳ Refund is being processed.</p>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif($latestReturn && in_array($latestReturn->status, [
                                        \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                        \App\Models\ReturnRequest::STATUS_COMPLETED,
                                    ])): ?>
                                        <div class="p-3 mb-3 bg-purple-50 border border-purple-100 rounded-lg">
                                            <p class="text-sm text-purple-700">✓ This order has been returned and processed.</p>
                                        </div>
                                    <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED): ?>
                                        <div class="p-3 mb-3 bg-green-50 border border-green-100 rounded-lg">
                                            <p class="text-sm text-green-700">✓ Return has been verified. Refund is being processed.</p>
                                        </div>
                                    <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT): ?>
                                        <div class="p-3 mb-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                                            <p class="text-sm text-indigo-700">🔄 Return is in transit. We'll verify once received.</p>
                                        </div>
                                    <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED): ?>
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">✓ Return approved. Please submit your tracking number.</p>
                                        </div>
                                    <?php elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED): ?>
                                        <div class="p-3 mb-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                                            <p class="text-sm text-yellow-700">⏳ Return request is pending review.</p>
                                        </div>
                                    <?php elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED): ?>
                                        <div class="p-3 mb-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                                            <p class="text-sm text-yellow-700">⏳ Cancellation request is pending review.</p>
                                        </div>
                                    <?php elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED): ?>
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">✓ Cancellation approved. Processing refund.</p>
                                        </div>
                                    <?php elseif(!$isOrderShipped): ?>
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">These items will be shipped separately once they're back in stock.</p>
                                        </div>
                                    <?php elseif($isOrderCompleted): ?>
                                        <div class="p-3 mb-3 bg-green-50 border border-green-100 rounded-lg">
                                            <p class="text-sm text-green-700">✓ These items have been delivered.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-3 mb-3 bg-purple-50 border border-purple-100 rounded-lg">
                                            <p class="text-sm text-purple-700">✓ These items have been shipped.</p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="divide-y">
                                        <?php $__currentLoopData = $backOrderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="py-4 flex items-center gap-4">
                                                <?php $photo = optional(optional($oi->item?->photos)->first())->url; ?>
                                                <?php if($photo): ?>
                                                    <img src="<?php echo e($photo); ?>" class="w-16 h-16 rounded object-cover bg-gray-100" alt="<?php echo e($oi->item?->name); ?>"/>
                                                <?php else: ?>
                                                    <div class="w-16 h-16 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-900"><?php echo e($oi->item?->name); ?></div>
                                                    <div class="text-xs text-gray-500">Qty: <?php echo e($oi->quantity); ?> • ₱<?php echo e(number_format($oi->price, 2)); ?></div>
                                                    <?php if($oi->item?->restock_date): ?>
                                                        <div class="text-xs text-blue-700 mt-1">Expected restock: <?php echo e($oi->item->restock_date->format('M d, Y')); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-sm font-medium">₱<?php echo e(number_format($oi->subtotal, 2)); ?></div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Delivery Information</h2>
                        <div class="mt-3 text-sm text-gray-700 space-y-2">
                            <div>Shipping Method: Standard</div>
                            <div>Estimated Delivery: <?php echo e(now()->addDays(5)->format('M d, Y')); ?></div>
                            <?php if($order->order_type === 'mixed' && $order->childOrders->isNotEmpty()): ?>
                                <!-- For mixed orders, show tracking numbers for each child order -->
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <div class="font-medium text-gray-900 mb-2">Tracking Numbers by Sub-Order:</div>
                                    <div class="space-y-2">
                                        <?php $__currentLoopData = $order->childOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="bg-gray-50 p-2 rounded border border-gray-100">
                                                <div class="text-xs text-gray-600 mb-1"><?php echo e(ucfirst($child->order_type)); ?> Sub-Order #<?php echo e($child->id); ?></div>
                                                <?php if($child->tracking_number): ?>
                                                    <div class="bg-blue-50 p-2 rounded border border-blue-100">
                                                        <div class="text-xs text-gray-600 mb-1">Tracking Number</div>
                                                        <div class="font-mono font-bold text-blue-900"><?php echo e($child->tracking_number); ?></div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-xs text-gray-500 italic">Tracking number not yet assigned</div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- For single orders (standard, backorder, custom) -->
                                <?php if($order->tracking_number): ?>
                                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                        <div class="text-xs text-gray-600 mb-1">Tracking Number</div>
                                        <div class="font-mono font-bold text-blue-900"><?php echo e($order->tracking_number); ?></div>
                                    </div>
                                <?php else: ?>
                                    <div>Tracking Number: <span class="text-gray-500 italic">Not yet assigned</span></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

				<div class="space-y-6">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Customer Information</h2>
                        <div class="mt-3 text-sm text-gray-700">
                            <div class="font-medium"><?php echo e($order->user?->name); ?></div>
                            <div><?php echo e($order->user?->email); ?></div>
                            <div class="mt-2"><?php echo e($order->user?->address?->address_line); ?></div>
                            <div><?php echo e($order->user?->address?->city); ?>, <?php echo e($order->user?->address?->province); ?> <?php echo e($order->user?->address?->postal_code); ?></div>
                            <div><?php echo e($order->user?->address?->phone_number); ?></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Payment</h2>
						<?php 
							$latestPayment = optional($order->payments)->sortByDesc('id')->first();
							$method = $latestPayment?->method ?? $order->payment_method ?? '—';
							// Format method display
							if ($method === 'gcash' || $method === 'GCash') {
								$method = 'GCash';
							} elseif ($method === 'bank' || $method === 'Bank' || $method === 'Bank Transfer') {
								$method = 'Bank Transfer';
							} elseif ($method === 'COD') {
								$method = 'COD';
							}
							$isCod = $order->payment_method === 'COD';
						?>
						<div class="mt-3 text-sm text-gray-700 space-y-1">
							<div>Method: <?php echo e($method); ?></div>
							<div>
								Status: 
								<?php
									$paymentStatus = $latestPayment?->status ?? $order->payment_status ?? '—';
									$isRejected = $latestPayment && $latestPayment->isRejected();
									$isPendingVerification = $latestPayment && $latestPayment->isPendingVerification();
									
									// Check order payment status for rejection
									if ($order->payment_status === 'payment_rejected' || $isRejected) {
										$statusBadgeClass = 'bg-red-100 text-red-800';
										$statusLabel = 'Payment Rejected';
									} else {
										// Check for refund status
										$latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
										$latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
										$isRefunded = ($order->payment_status === 'refunded') || 
											($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED) ||
											($latestReturn && in_array($latestReturn->status, [
												\App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
												\App\Models\ReturnRequest::STATUS_COMPLETED,
											]));
										
										if ($isRefunded) {
											$statusBadgeClass = 'bg-purple-100 text-purple-800';
											$statusLabel = 'Refunded';
										} else {
											$statusBadgeClass = match($paymentStatus) {
												'paid' => 'bg-green-100 text-green-800',
												'pending_verification' => 'bg-yellow-100 text-yellow-800',
												'pending_cod' => 'bg-blue-100 text-blue-800',
												'unpaid' => 'bg-red-100 text-red-800',
												default => 'bg-gray-100 text-gray-800',
											};
											$statusLabel = match($paymentStatus) {
												'paid' => 'Paid ✓',
												'pending_verification' => 'Pending Verification',
												'pending_cod' => 'Pending COD',
												'unpaid' => 'Unpaid',
												default => ucfirst(str_replace('_', ' ', $paymentStatus)),
											};
										}
									}
								?>
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($statusBadgeClass); ?>"><?php echo e($statusLabel); ?></span>
							</div>
							<?php if($isPendingVerification || $paymentStatus === 'pending_verification'): ?>
								<div class="mt-2 p-2 bg-yellow-50 border border-yellow-100 rounded text-xs">
									<p class="text-yellow-800">Your bank transfer proof is being verified by our team. We'll confirm payment shortly.</p>
								</div>
							<?php endif; ?>
							<?php if($isRejected || $order->payment_status === 'payment_rejected'): ?>
								<div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
									<p class="text-red-800 font-medium text-sm mb-1">✗ Payment Rejected</p>
									<?php if($latestPayment && $latestPayment->verification_notes): ?>
										<p class="text-red-700 text-xs mt-1">
											<strong>Reason:</strong> <?php echo e($latestPayment->verification_notes); ?>

										</p>
									<?php else: ?>
										<p class="text-red-700 text-xs mt-1">Your payment was rejected. Please contact support for more information.</p>
									<?php endif; ?>
									<p class="text-red-600 text-xs mt-2">Please submit a new payment or contact our support team for assistance.</p>
								</div>
							<?php endif; ?>
							<?php if(!empty($latestPayment?->transaction_id)): ?>
								<div>Reference: <?php echo e($latestPayment->transaction_id); ?></div>
							<?php endif; ?>
							<?php if(!empty($latestPayment?->proof_image)): ?>
								<div><a href="<?php echo e(Storage::url($latestPayment->proof_image)); ?>" target="_blank" class="text-[#c59d5f] hover:underline">View Bank Proof</a></div>
							<?php endif; ?>
							
							<?php if($isCod): ?>
								<div class="pt-2 border-t mt-2 space-y-2">
									<?php if($order->recipient_name): ?>
										<div>
											<span class="text-gray-600">Recipient:</span>
											<span class="font-medium"><?php echo e($order->recipient_name); ?></span>
										</div>
										<?php if($order->recipient_phone): ?>
											<div>
												<span class="text-gray-600">Contact:</span>
												<span class="font-medium"><?php echo e($order->recipient_phone); ?></span>
											</div>
										<?php endif; ?>
									<?php endif; ?>
									<?php if($order->shipping_fee > 0): ?>
										<div class="flex justify-between">
											<span class="text-gray-600">Shipping Fee (LBC):</span>
											<span class="font-medium">₱<?php echo e(number_format($order->shipping_fee, 2)); ?></span>
										</div>
									<?php endif; ?>
									<?php if($order->cod_fee > 0): ?>
										<div class="flex justify-between">
											<span class="text-gray-600">COD Fee:</span>
											<span class="font-medium">₱<?php echo e(number_format($order->cod_fee, 2)); ?></span>
										</div>
									<?php endif; ?>
									<div class="pt-2 border-t mt-2">
										<p class="text-xs text-blue-700 mb-1">💡 Pay the total amount (items + shipping + COD fee) to LBC upon delivery.</p>
									</div>
								</div>
							<?php endif; ?>
							
							<div class="pt-2 border-t mt-2 font-medium flex justify-between">
								<span>Total:</span>
								<span>₱<?php echo e(number_format($order->total_amount, 2)); ?></span>
							</div>
							
							<?php
								$hasRemainingBalance = ($order->remaining_balance ?? 0) > 0;
								$requires50PercentUpfront = $order->order_type === 'backorder' || $order->order_type === 'custom' || ($order->order_type === 'mixed' && $hasRemainingBalance);
							?>
							<?php if($hasRemainingBalance && $requires50PercentUpfront): ?>
								<div class="pt-3 border-t mt-3 space-y-2">
									<div class="flex justify-between items-center bg-blue-50 p-3 rounded border border-blue-200">
										<div>
											<span class="text-sm font-medium text-blue-900">Remaining Balance</span>
											<p class="text-xs text-blue-700 italic mt-1">To be collected by courier</p>
										</div>
										<span class="text-sm font-semibold text-blue-900">₱<?php echo e(number_format($order->remaining_balance, 2)); ?></span>
									</div>
								</div>
							<?php endif; ?>
						</div>
                    </div>

					<?php
						$latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
					?>
					<?php if($latestCancellation && ($latestCancellation->refund_amount || $latestCancellation->refund_method || in_array($latestCancellation->status, [
						\App\Models\CancellationRequest::STATUS_REFUND_COMPLETED,
						\App\Models\CancellationRequest::STATUS_REFUND_PROCESSING,
						\App\Models\CancellationRequest::STATUS_REFUND_FAILED,
					]))): ?>
						<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
							<h2 class="font-semibold text-gray-900 mb-3">Refund Information</h2>
							<div class="space-y-3">
								<?php if($latestCancellation->refund_amount): ?>
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Amount</span>
										<span class="text-lg font-semibold text-gray-900">₱<?php echo e(number_format($latestCancellation->refund_amount, 2)); ?></span>
									</div>
								<?php endif; ?>
								
								<?php if($latestCancellation->refund_method): ?>
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Method</span>
										<span class="text-sm font-medium text-gray-900"><?php echo e(ucfirst(str_replace('_', ' ', $latestCancellation->refund_method))); ?></span>
									</div>
								<?php endif; ?>
								
								<?php if($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED): ?>
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Status</span>
										<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
									</div>
								<?php elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING): ?>
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Status</span>
										<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Processing</span>
									</div>
								<?php elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_FAILED): ?>
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Status</span>
										<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Failed</span>
									</div>
								<?php endif; ?>
								
								<?php
									// Extract transaction ID from notes if available
									$transactionId = null;
									if ($latestCancellation->notes) {
										// Try multiple patterns to extract transaction ID
										$patterns = [
											'/Transaction ID:\s*([^\n\r]+)/i',
											'/Refund Processed:.*?Transaction ID:\s*([^\n\r]+)/i',
											'/Transaction ID[:\s]+([^\n\r]+)/i',
											'/Transaction[:\s]+([A-Za-z0-9]+)/i',
										];
										foreach ($patterns as $pattern) {
											if (preg_match($pattern, $latestCancellation->notes, $matches)) {
												$transactionId = trim($matches[1]);
												if ($transactionId && $transactionId !== 'N/A') {
													break;
												}
											}
										}
									}
								?>
								<?php if($transactionId): ?>
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Transaction ID</span>
										<span class="text-sm font-mono text-gray-900"><?php echo e($transactionId); ?></span>
									</div>
								<?php endif; ?>
								
								<?php if($latestCancellation->handledBy): ?>
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Processed By</span>
										<span class="text-sm text-gray-900"><?php echo e($latestCancellation->handledBy->name); ?></span>
									</div>
								<?php endif; ?>
								
								<?php if($latestCancellation->updated_at): ?>
									<div class="flex justify-between items-center py-2">
										<span class="text-sm text-gray-600">Processed Date</span>
										<span class="text-sm text-gray-900"><?php echo e($latestCancellation->updated_at->format('M d, Y h:i A')); ?></span>
									</div>
								<?php endif; ?>
								
								<?php if($latestCancellation->notes && $transactionId === null): ?>
									<div class="mt-3 pt-3 border-t border-gray-200">
										<p class="text-xs font-medium text-gray-600 mb-1">Notes</p>
										<p class="text-xs text-gray-700 whitespace-pre-line"><?php echo e($latestCancellation->notes); ?></p>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Order Status Timeline -->
					<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
						<h2 class="font-semibold text-gray-900 mb-3">Order Status</h2>
						<div class="space-y-3 text-sm">
							<?php
								// Show cancelled status if order is cancelled
								if ($order->status === 'cancelled') {
									$statusFlow = ['cancelled' => ['label' => 'Cancelled', 'icon' => '✗', 'done' => true]];
								} else {
									$statusFlow = match($order->order_type) {
										'standard' => [
											'pending' => ['label' => 'Order Placed', 'icon' => '📋', 'done' => true],
											'processing' => ['label' => 'Processing', 'icon' => '⚙️', 'done' => in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'ready_to_ship' => ['label' => 'Ready to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
											'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
										],
										'backorder' => [
											'pending' => ['label' => 'Order Placed', 'icon' => '📋', 'done' => true],
											'processing' => ['label' => 'Awaiting Stock', 'icon' => '⏳', 'done' => in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'ready_to_ship' => ['label' => 'Preparing to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
											'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
										],
										'custom' => [
											'pending' => ['label' => 'Awaiting Price', 'icon' => '💰', 'done' => true],
											'in_design' => ['label' => 'In Design', 'icon' => '🎨', 'done' => in_array($order->status, ['in_design', 'in_production', 'ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'in_production' => ['label' => 'In Production', 'icon' => '⚙️', 'done' => in_array($order->status, ['in_production', 'ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'ready_for_delivery' => ['label' => 'Ready for Delivery', 'icon' => '📦', 'done' => in_array($order->status, ['ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'ready_to_ship' => ['label' => 'Ready to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
											'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
										],
										default => []
									};
								}
							?>
							<?php $__currentLoopData = $statusFlow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<?php
									$isCancelled = $order->status === 'cancelled';
									$isDone = $info['done'] ?? false;
								?>
								<div class="flex items-start gap-3">
									<div class="text-lg leading-none pt-0.5"><?php echo e($info['icon']); ?></div>
									<div class="flex-1">
										<div class="text-xs font-medium <?php echo e($isCancelled ? 'text-red-700' : ($isDone ? 'text-green-700' : 'text-gray-500')); ?>">
											<?php echo e($info['label']); ?>

										</div>
									</div>
									<?php if($isDone): ?>
										<span class="<?php echo e($isCancelled ? 'text-red-600' : 'text-green-600'); ?> text-xs font-bold"><?php echo e($isCancelled ? '✗' : '✓'); ?></span>
									<?php endif; ?>
								</div>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</div>
					</div>

					<?php echo $__env->make('partials.cancellation-request-form', ['order' => $order], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
					<?php echo $__env->make('partials.return-request-form', ['order' => $order], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

					<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
						<a href="<?php echo e(route('customer.orders.index')); ?>" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Back to My Orders</a>
					</div>
                </div>
            </div>
        </div>
    </section>

    <?php echo $__env->make('partials.customer-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>


<?php /**PATH C:\xampp\htdocs\wowc\resources\views/order-confirmation.blade.php ENDPATH**/ ?>