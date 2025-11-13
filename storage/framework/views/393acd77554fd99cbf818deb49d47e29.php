<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">
    <?php echo $__env->make('partials.customer-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="pt-24 pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">My Orders</h1>
            <a href="/products" class="px-4 py-2 rounded-md bg-[#c59d5f] text-white hover:opacity-90">Continue Shopping</a>
        </div>

		

        <?php if(!empty($customOrders) && $customOrders->isNotEmpty()): ?>
            <div class="mb-6 bg-white border border-yellow-50 rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-medium text-gray-900">Your Custom Orders</h2>
                <p class="text-sm text-yellow-700 mt-1">Custom order requests currently under review or in production.</p>
                <div class="mt-3 divide-y">
                    <?php $__currentLoopData = $customOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $co): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <?php
                                    $images = data_get($co->customization_details, 'images', []);
                                    $firstImage = !empty($images) ? $images[0] : ($co->reference_image_path ?? null);
                                ?>
                                <?php if($firstImage): ?>
                                    <img src="<?php echo e(Storage::url($firstImage)); ?>" class="w-12 h-12 rounded object-cover bg-gray-100"/>
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($co->custom_name); ?></div>
                                    <div class="text-xs text-gray-500">Qty: <?php echo e($co->quantity); ?> • Status: <?php echo e(ucfirst(str_replace('_', ' ', $co->status))); ?></div>
                                    <?php if($co->price_estimate): ?>
                                        <div class="text-xs text-yellow-700">Estimated Price: ₱<?php echo e(number_format($co->price_estimate, 2)); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <div class="text-sm text-gray-700">Order #<?php echo e($co->order->id); ?></div>
                                <a href="<?php echo e(route('customer.orders.show', $co->order->id)); ?>" class="text-xs text-yellow-700 hover:underline">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

		<?php
			$standardOrders = $orders->filter(fn($o) => ($o->order_type ?? '') === 'standard');
			$backOrdersOrders = $orders->filter(fn($o) => ($o->order_type ?? '') === 'backorder');
			$firstPhoto = function($order){
				$firstItem = optional($order->items)->first();
				return optional(optional($firstItem)->item?->photos?->first())->url;
			};
		?>

		<?php if($standardOrders->isNotEmpty()): ?>
			<div class="mb-6 bg-white border border-gray-100 rounded-xl shadow-sm p-4">
				<h2 class="text-lg font-medium text-gray-900">Your Standard Orders</h2>
				<p class="text-sm text-gray-700 mt-1">Orders fulfilled from available stock.</p>
				<div class="mt-3 divide-y">
					<?php $__currentLoopData = $standardOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<div class="py-3 flex items-center justify-between">
							<div class="flex items-center gap-3">
								<?php if($firstPhoto($o)): ?>
									<img src="<?php echo e($firstPhoto($o)); ?>" class="w-12 h-12 rounded object-cover bg-gray-100"/>
								<?php else: ?>
									<div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center text-gray-400">
										<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
										</svg>
									</div>
								<?php endif; ?>
								<div>
									<?php $firstName = optional(optional($o->items)->first()?->item)->name; ?>
									<div class="font-medium text-gray-900"><?php echo e($firstName ?? ('Order #'.$o->id)); ?></div>
									<div class="text-xs text-gray-500">Placed: <?php echo e($o->created_at?->format('M d, Y')); ?> • Status: <?php echo e(ucfirst($o->status)); ?></div>
								</div>
							</div>
							<div class="flex flex-col items-end gap-2">
								<div class="text-sm text-gray-700">Order #<?php echo e($o->id); ?></div>
								<a href="<?php echo e(route('customer.orders.show', $o->id)); ?>" class="text-xs text-yellow-700 hover:underline">View Details</a>
							</div>
						</div>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if($backOrdersOrders->isNotEmpty()): ?>
			<div class="mb-6 bg-white border border-gray-100 rounded-xl shadow-sm p-4">
				<h2 class="text-lg font-medium text-gray-900">Your Back-Orders</h2>
				<p class="text-sm text-blue-700 mt-1">Orders awaiting stock; we’ll notify you when ready.</p>
				<div class="mt-3 divide-y">
					<?php $__currentLoopData = $backOrdersOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<div class="py-3 flex items-center justify-between">
							<div class="flex items-center gap-3">
								<?php if($firstPhoto($o)): ?>
									<img src="<?php echo e($firstPhoto($o)); ?>" class="w-12 h-12 rounded object-cover bg-gray-100"/>
								<?php else: ?>
									<div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center text-gray-400">
										<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
										</svg>
									</div>
								<?php endif; ?>
								<div>
									<?php $firstName = optional(optional($o->items)->first()?->item)->name; ?>
									<div class="font-medium text-gray-900"><?php echo e($firstName ?? ('Order #'.$o->id)); ?></div>
									<div class="text-xs text-gray-500">Placed: <?php echo e($o->created_at?->format('M d, Y')); ?> • Status: <?php echo e(ucfirst($o->status)); ?></div>
									<?php if($o->expected_restock_date): ?>
										<div class="text-xs text-blue-700">Expected: <?php echo e($o->expected_restock_date->format('M d, Y')); ?></div>
									<?php endif; ?>
								</div>
							</div>
							<div class="flex flex-col items-end gap-2">
								<div class="text-sm text-gray-700">Order #<?php echo e($o->id); ?></div>
								<a href="<?php echo e(route('customer.orders.show', $o->id)); ?>" class="text-xs text-yellow-700 hover:underline">View Details</a>
							</div>
						</div>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if($standardOrders->isEmpty() && $backOrdersOrders->isEmpty() && (empty($customOrders) || $customOrders->isEmpty())): ?>
			<div class="px-4 py-6 bg-white border border-gray-100 rounded-xl shadow-sm text-center text-gray-500">No orders yet.</div>
		<?php endif; ?>
    </section>

    <?php echo $__env->make('partials.customer-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>


<?php /**PATH C:\xampp\htdocs\wowc\resources\views/customer-orders.blade.php ENDPATH**/ ?>