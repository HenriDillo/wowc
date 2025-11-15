<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>WOW Carmen - Products</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false, mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">

	<?php echo $__env->make('partials.customer-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

	<!-- Page Header -->
	<section class="pt-24 pb-6">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-center">
				<h1 class="text-2xl md:text-3xl font-bold text-gray-900">Products</h1>
			</div>
		</div>
	</section>

	<!-- Filters + Search -->
	<section class="pb-4">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="bg-white border border-gray-100 rounded-xl shadow-sm p-4 md:p-5">
				<form method="GET" action="<?php echo e(url('/products')); ?>" x-data class="grid grid-cols-1 sm:grid-cols-3 gap-3">
					<!-- Category Filter (expects $categories) -->
					<div>
						<label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
						<div class="relative">
							<select name="category" @change="$root.submit()" class="w-full rounded-md border border-gray-300 hover:border-gray-400 focus:border-[#c59d5f] focus:ring-1 focus:ring-[#c59d5f] bg-white pr-10">
								<option value="">All</option>
								<?php $__currentLoopData = ($categories ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($category->id); ?>" <?php echo e((string)$category->id === (string)request('category') ? 'selected' : ''); ?>><?php echo e($category->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
							<span class="pointer-events-none absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
								<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 12a1 1 0 01-.707-.293l-3-3a1 1 0 111.414-1.414L10 9.586l2.293-2.293a1 1 0 111.414 1.414l-3 3A1 1 0 0110 12z" clip-rule="evenodd"/></svg>
							</span>
						</div>
					</div>

					<!-- Search -->
					<div class="sm:col-span-2">
						<label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
						<div class="relative">
							<input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search item" x-on:input.debounce.400ms="$root.submit()" class="w-full rounded-md border border-gray-300 hover:border-gray-400 pl-10 focus:border-[#c59d5f] focus:ring-1 focus:ring-[#c59d5f]"/>
							<span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 110-16 8 8 0 010 16z"/></svg>
							</span>
						</div>
					</div>
				</form>
			</div>
		</div>
	</section>

	<!-- Products Grid -->
	<section class="py-8">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<?php if(($items ?? collect())->count() === 0): ?>
				<!-- Empty state -->
				<div class="text-center py-20">
					<p class="text-gray-600">No products found. Please adjust your filters or try another search.</p>
				</div>
			<?php else: ?>
				<div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-8">
					<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(url('/products/'.$item->id)); ?>" class="group block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
                            <div class="relative aspect-[4/3] bg-gray-100 rounded-t-xl overflow-hidden">
                                <img src="<?php echo e($item->photo_url); ?>" alt="<?php echo e($item->name); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <?php if($item->stock <= 0 || ($item->status ?? null) === 'back_order'): ?>
                                    <span class="absolute top-2 left-2 inline-flex items-center px-2 py-1 text-[11px] font-semibold rounded bg-blue-100 text-blue-800">Back-Order Available</span>
                                <?php elseif($item->stock <= 5): ?>
                                    <span class="absolute top-2 left-2 inline-flex items-center px-2 py-1 text-[11px] font-semibold rounded bg-yellow-100 text-yellow-800">Low Stock (<?php echo e($item->stock); ?>)</span>
                                <?php else: ?>
                                    <span class="absolute top-2 left-2 inline-flex items-center px-2 py-1 text-[11px] font-semibold rounded bg-green-100 text-green-800">In Stock (<?php echo e($item->stock); ?>)</span>
                                <?php endif; ?>
							</div>
							<div class="p-4">
								<h3 class="text-sm md:text-base font-medium text-gray-900 truncate"><?php echo e($item->name); ?></h3>
								<p class="mt-1 text-[#c59d5f] font-semibold">₱<?php echo e(number_format($item->price, 2)); ?></p>
								<p class="mt-2 text-xs text-gray-600">
									<?php if($item->stock <= 0 || ($item->status ?? null) === 'back_order'): ?>
										Available on back order
									<?php else: ?>
										<?php echo e($item->stock); ?> in stock
									<?php endif; ?>
								</p>
							</div>
						</a>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				</div>

				<!-- Pagination -->
				<div class="mt-10">
					<?php echo e(method_exists($items, 'links') ? $items->withQueryString()->links() : ''); ?>

				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php echo $__env->make('partials.customer-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>


<?php /**PATH C:\xampp\htdocs\wowc\resources\views/products.blade.php ENDPATH**/ ?>