<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOW Carmen - Customer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false, mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">

    <?php echo $__env->make('partials.customer-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Hero Section -->
    <section class="relative pt-24">
        <!-- Background image -->
        <div class="absolute inset-0 -z-10 bg-center bg-cover" style="background-image: url('/images/welcome-bg.jpg');"></div>
        <div class="absolute inset-0 -z-10 bg-black/20"></div>

        <!-- Overlay box -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center py-24 md:py-40">
                <div class="bg-white/80 backdrop-blur rounded-xl shadow-lg p-8 md:p-12 text-center max-w-2xl">
                    <h1 class="text-3xl md:text-5xl font-semibold text-gray-900 mb-6">Wow Carmen Handicrafts</h1>
                    <a href="<?php echo e(route('products.index')); ?>" class="inline-block px-8 py-3 rounded-md text-white font-medium transition transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2" style="background:#c59d5f;">
                        Shop Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section id="products" class="py-16 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-14">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Featured Products</h2>
                <p class="text-gray-600 mt-2">Order it for you or for your beloved ones</p>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-8">
                <?php $__empty_1 = true; $__currentLoopData = ($products ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <!-- Product Card -->
                    <a href="<?php echo e(url('/products/'.$product->id)); ?>" class="group block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
                        <div class="aspect-[4/3] bg-gray-100 rounded-t-xl overflow-hidden">
                            <img src="<?php echo e($product->photo_url ?? ''); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        </div>
                        <div class="p-4">
                            <h3 class="text-sm md:text-base font-medium text-gray-900 truncate"><?php echo e($product->name); ?></h3>
                            <p class="mt-1 text-[#c59d5f] font-semibold">₱<?php echo e(number_format($product->price, 2)); ?></p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <?php for($i=0;$i<8;$i++): ?>
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                            <div class="aspect-[4/3] bg-gray-100 rounded-t-xl"></div>
                            <div class="p-4 text-center">
                                <h3 class="text-sm md:text-base font-medium text-gray-800 mb-1">Product Name</h3>
                                <p class="text-[#c59d5f] font-semibold">Price</p>
                            </div>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php echo $__env->make('partials.customer-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html><?php /**PATH C:\xampp\htdocs\wowc\resources\views/customer.blade.php ENDPATH**/ ?>