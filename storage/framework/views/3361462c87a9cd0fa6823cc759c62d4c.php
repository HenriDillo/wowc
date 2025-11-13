<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>WOW Carmen - <?php echo e($item->name); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false, mobileMenuOpen: false, scrolled: false, qty: 1, adding:false, added:false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">

	<?php echo $__env->make('partials.customer-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

	<!-- Product Section -->
	<section class="pt-24 pb-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
				<!-- Left: Image Gallery -->
                <?php $photoUrls = $item->photos->pluck('url')->filter()->values(); ?>
                <div x-data="{photos: <?php echo e($photoUrls->isNotEmpty() ? $photoUrls->toJson() : json_encode([$item->photo_url])); ?>, selected: 0}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 md:p-6">
                    <div class="relative w-full aspect-square bg-gray-50 rounded-lg overflow-hidden flex items-center justify-center">
                        <?php if(($item->status ?? null) === 'back_order'): ?>
                            <span class="absolute top-3 left-3 inline-flex items-center px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">Back-Order</span>
                        <?php endif; ?>
						<template x-if="photos && photos.length">
							<img :src="photos[selected]" alt="<?php echo e($item->name); ?>" class="w-full h-full object-cover"/>
						</template>
					</div>
					<!-- Thumbnails -->
					<div class="mt-4 grid grid-cols-5 gap-3">
						<template x-for="(p, idx) in photos" :key="idx">
							<button type="button" @click="selected = idx" :class="{'ring-2 ring-[#c59d5f]': selected === idx}" class="relative rounded-md overflow-hidden border border-gray-200 hover:border-gray-300 focus:outline-none">
								<img :src="p" alt="Thumbnail" class="aspect-square w-full object-cover"/>
							</button>
						</template>
					</div>
				</div>

				<!-- Right: Details -->
				<div>
					<h1 class="text-2xl md:text-3xl font-semibold text-gray-900"><?php echo e($item->name); ?></h1>
					<p class="mt-2 text-[#c59d5f] text-xl font-semibold">₱<?php echo e(number_format($item->price, 2)); ?></p>
                    <p class="mt-4 text-gray-700 max-w-xl leading-relaxed"><?php echo e($item->description); ?></p>
                    <?php if(($item->status ?? null) === 'back_order'): ?>
                        <p class="mt-2 text-sm text-blue-700">Available on Back Order</p>
                        <?php if($item->restock_date): ?>
                            <p class="text-xs text-blue-600">Restocking soon  Ships after <?php echo e($item->restock_date->format('M d, Y')); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>

					<!-- Stock status -->
					<?php
						$stock = (int) ($item->stock ?? 0);
						$isBackOrder = ($item->status ?? null) === 'back_order';
						if ($isBackOrder) {
							$statusText = 'Available for Back Order';
							$statusColor = 'text-blue-600';
						} else {
							$statusText = $stock > 10 ? 'In Stock (' . $stock . ' available)' : ($stock > 0 ? 'Low Stock (' . $stock . ' left)' : 'Out of Stock — Available for Back Order');
							$statusColor = $stock > 10 ? 'text-green-600' : ($stock > 0 ? 'text-amber-600' : 'text-blue-600');
						}
					?>
					<p class="mt-3 text-sm font-medium <?php echo e($statusColor); ?>"><?php echo e($statusText); ?></p>

					<?php if($stock <= 0 || $isBackOrder): ?>
						<div class="mt-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
							<p class="text-sm text-blue-800">This item is available for back order. We'll ship it as soon as it's back in stock.</p>
							<?php if($item->restock_date): ?>
								<p class="mt-1 text-xs text-blue-700">Expected restock date: <?php echo e($item->restock_date->format('M d, Y')); ?></p>
							<?php endif; ?>
						</div>
					<?php elseif($stock <= 5): ?>
						<div class="mt-3 p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
							<p class="text-sm text-yellow-800">Only <?php echo e($stock); ?> items left in stock. Order soon!</p>
						</div>
					<?php else: ?>
						<div class="mt-3 p-3 bg-green-50 border border-green-100 rounded-lg">
							<p class="text-sm text-green-800"><?php echo e($stock); ?> items available. Plenty of stock!</p>
						</div>
					<?php endif; ?>

					<!-- Quantity selector -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <div class="inline-flex items-center border border-gray-300 rounded-md overflow-hidden">
                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="px-3 py-2 text-gray-600 hover:bg-gray-50">-</button>
                            <input type="number" 
                                x-model.number="qty" 
                                min="1" 
                                @change="qty = <?php echo e($isBackOrder ? 'Math.max(1, qty)' : 'Math.min(qty, Math.max(1, '.$stock.'))'); ?>" 
                                class="w-14 text-center border-0 focus:ring-0"
                            />
                            <button type="button" 
                                @click="qty = <?php echo e($isBackOrder ? 'qty + 1' : 'Math.min(qty + 1, Math.max(1, '.$stock.'))'); ?>" 
                                class="px-3 py-2 text-gray-600 hover:bg-gray-50"
                            >+</button>
                        </div>
                        <?php if($isBackOrder || $stock <= 0): ?>
                            <p class="mt-1 text-xs text-blue-600">No quantity limit for back orders</p>
                        <?php endif; ?>

                    </div>

					<!-- Add to cart (standard form submission, avoids API/fetch) -->
					<div class="mt-6">
						<form action="/api/v1/cart/add" method="POST" x-ref="addForm" @submit.prevent="adding=true; $refs.addForm.submit();">
							<?php echo csrf_field(); ?>
							<input type="hidden" name="item_id" value="<?php echo e($item->id); ?>" />
							<input type="hidden" name="quantity" x-model.number="qty" />
							<button type="submit" :disabled="adding" class="inline-flex items-center justify-center px-6 py-3 rounded-md text-white font-medium shadow-sm hover:shadow transition disabled:opacity-60 disabled:cursor-not-allowed" style="background:#c59d5f;">
								<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m2.6 8L6 5H3m4 8a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z"/></svg>
								<span>
									<?php echo e(($item->status ?? null) === 'back_order' ? 'Order Now (Back Order)' : 'Add to cart'); ?>

								</span>
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php echo $__env->make('partials.customer-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>


<?php /**PATH C:\xampp\htdocs\wowc\resources\views/product-details.blade.php ENDPATH**/ ?>