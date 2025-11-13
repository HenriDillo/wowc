<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOW Carmen - Custom Order Details</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false, mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">

    <?php echo $__env->make('partials.customer-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Content -->
    <section class="pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="<?php echo e(route('customer.orders.index')); ?>" class="text-sm text-[#c59d5f] hover:underline">&larr; Back to Orders</a>

            <div class="mt-4 bg-white overflow-hidden border border-gray-100 rounded-xl shadow-sm p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Order Details</h3>
                        <dl class="grid grid-cols-1 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Product Name</dt>
                                <dd class="text-gray-900"><?php echo e($customOrder->custom_name); ?></dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Description</dt>
                                <dd class="text-gray-900"><?php echo e($customOrder->description); ?></dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Quantity</dt>
                                <dd class="text-gray-900"><?php echo e($customOrder->quantity); ?></dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Status</dt>
                                <dd class="text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php switch($customOrder->status):
                                            case ('pending_review'): ?>
                                                bg-yellow-100 text-yellow-800
                                                <?php break; ?>
                                            <?php case ('approved'): ?>
                                                bg-green-100 text-green-800
                                                <?php break; ?>
                                            <?php case ('rejected'): ?>
                                                bg-red-100 text-red-800
                                                <?php break; ?>
                                            <?php case ('in_production'): ?>
                                                bg-blue-100 text-blue-800
                                                <?php break; ?>
                                            <?php case ('completed'): ?>
                                                bg-gray-100 text-gray-800
                                                <?php break; ?>
                                        <?php endswitch; ?>
                                    ">
                                        <?php echo e(str_replace('_', ' ', ucfirst($customOrder->status))); ?>

                                    </span>
                                </dd>
                            </div>
                            <?php if(!is_null($customOrder->price_estimate)): ?>
                            <div>
                                <dt class="text-gray-500">Price Estimate</dt>
                                <dd class="text-gray-900">₱<?php echo e(number_format((float)$customOrder->price_estimate, 2)); ?></dd>
                            </div>
                            <?php endif; ?>
                            <?php if($customOrder->estimated_completion_date): ?>
                            <div>
                                <dt class="text-gray-500">Estimated Completion</dt>
                                <dd class="text-gray-900"><?php echo e($customOrder->estimated_completion_date->format('M d, Y')); ?></dd>
                            </div>
                            <?php endif; ?>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Reference Images</h3>
                        <?php
                            $images = data_get($customOrder->customization_details, 'images', []);
                            // Fallback to single image for backward compatibility
                            if (empty($images) && $customOrder->reference_image_path) {
                                $images = [$customOrder->reference_image_path];
                            }
                        ?>
                        <?php if(!empty($images)): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $imagePath): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="relative">
                                        <img src="<?php echo e(Storage::url($imagePath)); ?>" alt="Reference Image <?php echo e($loop->iteration); ?>" class="w-full h-auto rounded-lg border border-gray-200 shadow-sm object-cover">
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 mt-4">No reference images provided.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED && ($customOrder->order?->payment_status !== 'paid')): ?>
            <div class="mt-6 bg-white overflow-hidden border border-gray-100 rounded-xl shadow-sm p-6 md:p-8">
                <h3 class="text-lg font-semibold mb-4">Payment</h3>
                <p class="text-sm text-gray-700">Your custom order has been confirmed. Please complete payment to begin production.</p>
                <div class="mt-4">
                    <a href="<?php echo e(route('checkout.page', ['order_id' => $customOrder->order?->id])); ?>" class="inline-flex items-center px-4 py-2 rounded-md text-white" style="background:#c59d5f;">Proceed to Checkout</a>
                </div>
            </div>

            <?php endif; ?>
        </div>
    </section>

    <?php echo $__env->make('partials.customer-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script></script>
</body>
</html><?php /**PATH C:\xampp\htdocs\wowc\resources\views/custom-order/show.blade.php ENDPATH**/ ?>