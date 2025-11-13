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
            <div class="mb-6">
				<h1 class="text-2xl font-semibold text-gray-900"><?php echo e(request()->is('customer/*') ? 'Order Details' : 'Thank you for your order!'); ?></h1>
				<p class="mt-2 text-gray-600">Order <span class="font-medium text-gray-900">#<?php echo e($order->id); ?></span> • <?php echo e($order->created_at?->format('M d, Y')); ?></p>
				<?php
					$statusColor = [
						'pending' => 'bg-yellow-100 text-yellow-800',
						'processing' => 'bg-blue-100 text-blue-800',
						'completed' => 'bg-green-100 text-green-800',
						'cancelled' => 'bg-red-100 text-red-800',
						'backorder' => 'bg-indigo-100 text-indigo-800',
					][$order->status] ?? 'bg-gray-100 text-gray-800';
                    $hasBackorder = $order->items->contains(fn($oi) => ($oi->is_backorder ?? false));
                    $isCustomOrder = $order->order_type === 'custom';
                    $customOrder = $isCustomOrder ? $order->customOrders->first() : null;
				?>
				<div class="mt-2 text-sm flex flex-wrap gap-2">
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-100 text-gray-800">Type: <?php echo e($order->order_type); ?></span>
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize <?php echo e($statusColor); ?>">Status: <?php echo e($order->status); ?></span>
                </div>

                <?php if($hasBackorder): ?>
                    <div class="mt-4 p-4 rounded-md border border-blue-200 bg-blue-50">
                        <h3 class="font-medium text-blue-900">Order Status</h3>
                        <div class="mt-2 text-sm text-blue-800 space-y-1">
                            <p><strong>✓ Standard Items:</strong> Ready for processing and will ship soon</p>
                            <p><strong>⏳ Back Order Items:</strong> Awaiting stock - will ship separately once restocked</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($isCustomOrder && $customOrder): ?>
                    <?php
                        $customStatusColor = match($customOrder->status) {
                            'pending_review' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'in_production' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                    ?>
                    <div class="mt-4 p-4 rounded-md border border-yellow-200 bg-yellow-50">
                        <h3 class="font-medium text-yellow-900">Custom Order Status</h3>
                        <div class="mt-2 text-sm text-yellow-800 space-y-1">
                            <p><strong>Status:</strong> <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($customStatusColor); ?>"><?php echo e(str_replace('_', ' ', ucfirst($customOrder->status))); ?></span></p>
                            <?php if($customOrder->price_estimate): ?>
                                <p><strong>Price Estimate:</strong> ₱<?php echo e(number_format((float)$customOrder->price_estimate, 2)); ?></p>
                            <?php endif; ?>
                            <?php if($customOrder->estimated_completion_date): ?>
                                <p><strong>Estimated Completion:</strong> <?php echo e($customOrder->estimated_completion_date->format('M d, Y')); ?></p>
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

                        <?php if($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED && ($customOrder->order?->payment_status !== 'paid')): ?>
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                                <h2 class="font-semibold text-gray-900">Payment</h2>
                                <p class="mt-3 text-sm text-gray-700">Your custom order has been confirmed. Please complete payment to begin production.</p>
                                <div class="mt-4">
                                    <a href="<?php echo e(route('checkout.page', ['order_id' => $customOrder->order?->id])); ?>" class="inline-flex items-center px-4 py-2 rounded-md text-white" style="background:#c59d5f;">Proceed to Checkout</a>
                                </div>
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
                                <div class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="font-medium text-gray-900">Standard Items</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Ready for processing</span>
                                    </div>
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
                                <div class="mt-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="font-medium text-gray-900">Back Order Items</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Awaiting stock</span>
                                    </div>
                                    <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                        <p class="text-sm text-blue-700">These items will be shipped separately once they're back in stock.</p>
                                    </div>
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
                        <div class="mt-3 text-sm text-gray-700">
                            <div>Shipping Method: Standard</div>
                            <div>Estimated Delivery: <?php echo e(now()->addDays(5)->format('M d, Y')); ?></div>
                            <div>Tracking Number: —</div>
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
							}
						?>
						<div class="mt-3 text-sm text-gray-700 space-y-1">
							<div>Method: <?php echo e($method); ?></div>
							<div>
								Status: 
								<?php
									$paymentStatus = $latestPayment?->status ?? $order->payment_status ?? '—';
									$statusBadgeClass = match($paymentStatus) {
										'paid' => 'bg-green-100 text-green-800',
										'pending_verification' => 'bg-yellow-100 text-yellow-800',
										'unpaid' => 'bg-red-100 text-red-800',
										default => 'bg-gray-100 text-gray-800',
									};
									$statusLabel = match($paymentStatus) {
										'paid' => 'Paid ✓',
										'pending_verification' => 'Pending Verification',
										'unpaid' => 'Unpaid',
										default => ucfirst(str_replace('_', ' ', $paymentStatus)),
									};
								?>
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($statusBadgeClass); ?>"><?php echo e($statusLabel); ?></span>
							</div>
							<?php if($paymentStatus === 'pending_verification'): ?>
								<div class="mt-2 p-2 bg-yellow-50 border border-yellow-100 rounded text-xs">
									<p class="text-yellow-800">Your bank transfer proof is being verified by our team. We'll confirm payment shortly.</p>
								</div>
							<?php endif; ?>
							<?php if(!empty($latestPayment?->transaction_id)): ?>
								<div>Reference: <?php echo e($latestPayment->transaction_id); ?></div>
							<?php endif; ?>
							<?php if(!empty($latestPayment?->proof_image)): ?>
								<div><a href="<?php echo e(Storage::url($latestPayment->proof_image)); ?>" target="_blank" class="text-[#c59d5f] hover:underline">View Bank Proof</a></div>
							<?php endif; ?>
							<div class="pt-2 border-t mt-2 font-medium">Total: ₱<?php echo e(number_format($order->total_amount, 2)); ?></div>
						</div>
                    </div>

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