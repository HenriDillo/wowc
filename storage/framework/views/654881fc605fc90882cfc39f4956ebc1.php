<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>WOW Carmen - Checkout</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
	<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
	<style>
		[x-cloak] { display: none !important; }
	</style>
</head>
	<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false, method:<?php echo e(old('payment_method', 'null') ? "'".old('payment_method')."'" : "'Bank'"); ?>, paymentMethodError: false, showShippingDetails: false, requires50PercentUpfront: <?php echo e(isset($requires50PercentUpfront) && $requires50PercentUpfront ? 'true' : 'false'); ?> }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family:'Poppins','Inter',ui-sans-serif,system-ui;">

	<?php echo $__env->make('partials.customer-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

	<section class="pt-24 pb-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="<?php echo e(route('cart')); ?>" class="text-sm text-[#c59d5f] hover:underline">Back to cart</a>

			<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-10">
				<!-- Left: Account & Shipping -->
                <div>
                    <form method="POST" action="<?php echo e(route('checkout.store')); ?>" class="bg-white border border-gray-100 rounded-xl shadow-sm p-6">
						<?php echo csrf_field(); ?>
						<h2 class="text-lg font-semibold text-gray-900">Account</h2>
                        <input type="email" value="<?php echo e($user->email ?? ''); ?>" disabled class="mt-3 w-full rounded-md border-2 border-gray-300 px-3 py-2 bg-gray-50 text-gray-500 cursor-not-allowed"/>
                        <?php if(isset($payOrder) && $payOrder): ?>
							<p class="mt-2 text-sm text-gray-700">Pay for Custom Order <span class="font-medium">#<?php echo e($payOrder->id); ?></span>. Choose a payment method below to complete your purchase.</p>
                        <?php endif; ?>

                        <?php if($errors->any()): ?>
                            <div class="mt-4 p-3 bg-red-50 border-2 border-red-200 rounded-lg text-sm text-red-700">
                                <p class="font-semibold mb-1">Please fix the following errors:</p>
                                <ul class="list-disc ml-5 space-y-1">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>

						<h3 class="mt-6 text-lg font-semibold text-gray-900">Shipping Information</h3>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <?php $addr = optional($user?->address); ?>
                            <div>
                                <input name="first_name" value="<?php echo e(old('first_name', $user->first_name ?? '')); ?>" placeholder="First Name" class="w-full rounded-md border-2 <?php echo e($errors->has('first_name') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]'); ?> px-3 py-2 focus:outline-none transition-colors" required>
                                <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <input name="last_name" value="<?php echo e(old('last_name', $user->last_name ?? '')); ?>" placeholder="Last Name" class="w-full rounded-md border-2 <?php echo e($errors->has('last_name') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]'); ?> px-3 py-2 focus:outline-none transition-colors" required>
                                <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="sm:col-span-2">
                                <input name="address_line" value="<?php echo e(old('address_line', $addr->address_line ?? '')); ?>" placeholder="Address" class="w-full rounded-md border-2 <?php echo e($errors->has('address_line') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]'); ?> px-3 py-2 focus:outline-none transition-colors" required>
                                <?php $__errorArgs = ['address_line'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <input name="city" value="<?php echo e(old('city', $addr->city ?? '')); ?>" placeholder="City" class="w-full rounded-md border-2 <?php echo e($errors->has('city') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]'); ?> px-3 py-2 focus:outline-none transition-colors" required>
                                <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <input name="postal_code" value="<?php echo e(old('postal_code', $addr->postal_code ?? '')); ?>" placeholder="Postal Code" class="w-full rounded-md border-2 <?php echo e($errors->has('postal_code') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]'); ?> px-3 py-2 focus:outline-none transition-colors" required>
                                <?php $__errorArgs = ['postal_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <input name="province" value="<?php echo e(old('province', $addr->province ?? '')); ?>" placeholder="Province" class="w-full rounded-md border-2 <?php echo e($errors->has('province') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]'); ?> px-3 py-2 focus:outline-none transition-colors" required>
                                <?php $__errorArgs = ['province'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="sm:col-span-2">
                                <input name="phone_number" value="<?php echo e(old('phone_number', $addr->phone_number ?? '')); ?>" placeholder="Phone Number" class="w-full rounded-md border-2 <?php echo e($errors->has('phone_number') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]'); ?> px-3 py-2 focus:outline-none transition-colors" required>
                                <?php $__errorArgs = ['phone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <h3 class="mt-6 text-lg font-semibold text-gray-900">Payment</h3>
                        <?php
                            $paymentPercentage = $paymentPercentage ?? 1.0;
                            $requiredPaymentAmount = $requiredPaymentAmount ?? $total;
                            $isPartialPayment = $paymentPercentage < 1.0;
                            $orderType = isset($payOrder) && $payOrder ? ($payOrder->order_type ?? 'custom') : 'standard';
                            
                            // Check if there are backorder items in cart
                            $hasBackorderItems = isset($backorderItems) && $backorderItems->isNotEmpty();
                            
                            // Check if this is a custom order payment
                            $isCustomOrderPayment = isset($payOrder) && $payOrder && $payOrder->order_type === 'custom';
                            
                            // Check if this is a backorder order payment
                            $isBackorderOrderPayment = isset($payOrder) && $payOrder && $payOrder->order_type === 'backorder';
                            
                            // Check if this is a mixed order (Standard + Backorder only)
                            $isMixedOrderWithBackorder = isset($isMixedOrder) && $isMixedOrder && isset($standardItems) && isset($backorderItems) && $standardItems->isNotEmpty() && $backorderItems->isNotEmpty();
                            
                            // Orders that require 50% upfront: Backorder, Custom Order, Mixed Order (Standard + Backorder only)
                            // This means COD should be hidden for these orders
                            $requires50PercentUpfront = $hasBackorderItems || $isCustomOrderPayment || $isBackorderOrderPayment || $isMixedOrderWithBackorder;
                        ?>
                        
                        <?php if($requires50PercentUpfront): ?>
                            <div class="mt-3 p-4 bg-amber-50 border-2 border-amber-300 rounded-lg mb-4">
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl">💰</span>
                                    <div>
                                        <p class="font-bold text-amber-900">Down Payment Required (50%)</p>
                                        <p class="text-sm text-amber-800 mt-1">
                                            <?php if($isMixedOrderWithBackorder): ?>
                                                This is a Mixed Order with back order items. You must pay <strong>50% upfront</strong> for back order items now to proceed. Standard items will be processed and paid separately.
                                            <?php elseif($isCustomOrderPayment): ?>
                                                This is a Custom Order. You must pay <strong>50% upfront</strong> now to proceed.
                                            <?php elseif($isBackorderOrderPayment || $hasBackorderItems): ?>
                                                This is a Back Order. You must pay <strong>50% upfront</strong> now to proceed.
                                            <?php endif; ?>
                                            <?php if($isMixedOrderWithBackorder): ?>
                                                The remaining 50% of back order items will be collected by the LBC courier upon delivery.
                                            <?php else: ?>
                                                The remaining 50% will be collected by the LBC courier upon delivery.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                                <p class="text-sm text-blue-800"><strong>Important:</strong> Payment is required to complete your order. Please select a payment method below.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-<?php echo e($requires50PercentUpfront ? '2' : '3'); ?> gap-3">
							<label class="flex items-center justify-center gap-2 border-2 rounded-md p-4 cursor-pointer transition-all duration-200" 
								:class="method === 'Bank' ? 'border-[#c59d5f] bg-[#c59d5f]/5 shadow-sm' : 'border-gray-300 hover:border-gray-400'"
								@click="method = 'Bank'; paymentMethodError = false; showShippingDetails = false">
                                <input type="radio" name="payment_method" value="Bank" x-model="method" class="hidden">
								<svg x-show="method === 'Bank'" class="w-5 h-5 text-[#c59d5f]" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
								</svg>
								<span class="text-sm font-medium" :class="method === 'Bank' ? 'text-[#c59d5f]' : 'text-gray-700'">Bank Transfer</span>
							</label>
							<label class="flex items-center justify-center gap-2 border-2 rounded-md p-4 cursor-pointer transition-all duration-200" 
								:class="method === 'GCash' ? 'border-[#c59d5f] bg-[#c59d5f]/5 shadow-sm' : 'border-gray-300 hover:border-gray-400'"
								@click="method = 'GCash'; paymentMethodError = false; showShippingDetails = false">
								<input type="radio" name="payment_method" value="GCash" x-model="method" class="hidden">
								<svg x-show="method === 'GCash'" class="w-5 h-5 text-[#c59d5f]" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
								</svg>
								<img src="/images/gcash.png" alt="GCash" class="h-5">
								<span class="text-sm font-medium" :class="method === 'GCash' ? 'text-[#c59d5f]' : 'text-gray-700'">GCash</span>
							</label>
							<?php if(!$requires50PercentUpfront): ?>
							<label class="flex items-center justify-center gap-2 border-2 rounded-md p-4 cursor-pointer transition-all duration-200" 
								:class="method === 'COD' ? 'border-[#c59d5f] bg-[#c59d5f]/5 shadow-sm' : 'border-gray-300 hover:border-gray-400'"
								@click="method = 'COD'; paymentMethodError = false; showShippingDetails = true">
								<input type="radio" name="payment_method" value="COD" x-model="method" class="hidden">
								<svg x-show="method === 'COD'" class="w-5 h-5 text-[#c59d5f]" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
								</svg>
								<span class="text-sm font-medium" :class="method === 'COD' ? 'text-[#c59d5f]' : 'text-gray-700'">COD</span>
							</label>
							<?php endif; ?>
						</div>
						
						<!-- COD Shipping Details -->
						<div x-show="method === 'COD'" x-cloak class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
							<p class="text-sm text-blue-800 font-medium mb-2">📦 Pay LBC upon delivery. Shipping fees follow LBC standards.</p>
							<p class="text-xs text-blue-700">Your shipping information will be used as the recipient details for COD delivery.</p>
						</div>
						<?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
							<p class="mt-2 text-xs text-red-600"><?php echo e($message); ?></p>
						<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
						<div x-show="paymentMethodError" x-cloak class="mt-2 p-2 bg-red-50 border border-red-200 rounded-md text-xs text-red-600">
							<strong>⚠</strong> Please select a payment method to proceed.
						</div>
						<div class="mt-6 text-right">
							<button id="completeBtn" type="button" class="inline-flex items-center justify-center px-6 py-3 rounded-md text-white font-medium shadow-sm hover:shadow-md hover:opacity-95 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#c59d5f] focus:ring-offset-2" style="background:#c59d5f;">Complete Order</button>
						</div>
					</form>
				</div>

				<!-- Right: Summary -->
				<div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6">
					<h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                        <?php
							$paymentOnly = isset($payOrder) && $payOrder;
                            $isMixedOrder = isset($isMixedOrder) && $isMixedOrder;
                            $standardItems = $standardItems ?? collect();
                            $backorderItems = $backorderItems ?? collect();
                            $standardSubtotal = $standardSubtotal ?? 0;
                            $backorderSubtotal = $backorderSubtotal ?? 0;
                            $requiredPaymentAmount = $requiredPaymentAmount ?? $total;
                        ?>

                        <?php if($paymentOnly && isset($customOrder) && $customOrder): ?>
                            <!-- Custom Order Details -->
                            <div class="mt-4 space-y-4">
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                    <h3 class="text-sm font-semibold text-amber-900 mb-3">Custom Order</h3>
                                    <div class="space-y-4">
                                        <!-- Product Name -->
                                        <div>
                                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Product Name</div>
                                            <div class="text-sm font-semibold text-gray-900"><?php echo e($customOrder->custom_name); ?></div>
                                        </div>
                                        
                                        <!-- Description -->
                                        <?php if($customOrder->description): ?>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Description / Special Instructions</div>
                                                <div class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($customOrder->description); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Quantity -->
                                        <div>
                                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Quantity</div>
                                            <div class="text-sm text-gray-900"><?php echo e($customOrder->quantity); ?></div>
                                        </div>
                                        
                                        <!-- Reference Images -->
                                        <?php
                                            $images = data_get($customOrder->customization_details, 'images', []);
                                            // Fallback to single image for backward compatibility
                                            if (empty($images) && $customOrder->reference_image_path) {
                                                $images = [$customOrder->reference_image_path];
                                            }
                                            // Limit to 4 images
                                            $images = array_slice($images, 0, 4);
                                        ?>
                                        <?php if(!empty($images)): ?>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Reference Images</div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $imagePath): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="relative rounded-lg border border-gray-200 overflow-hidden bg-gray-50">
                                                            <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($imagePath)); ?>" alt="Reference Image <?php echo e($loop->iteration); ?>" class="w-full h-32 object-cover">
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php elseif($isMixedOrder): ?>
                            <!-- Mixed Order Breakdown -->
                            <div class="mt-4 space-y-4">
                                <!-- Standard Items -->
                                <?php if($standardItems->isNotEmpty()): ?>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Standard Items (100% Due)</h3>
                                        <div class="mt-3 space-y-4">
                                            <?php $__currentLoopData = $standardItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                            <img src="<?php echo e(optional($ci->item->photos->first())->url); ?>" alt="" class="w-full h-full object-cover"/>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900"><?php echo e($ci->item->name); ?></p>
                                                            <p class="text-xs text-gray-500">Qty: <?php echo e($ci->quantity); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm text-gray-900">₱<?php echo e(number_format($ci->subtotal, 2)); ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-700 font-medium">Standard subtotal: ₱<?php echo e(number_format($standardSubtotal, 2)); ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Back Order Items -->
                                <?php if($backorderItems->isNotEmpty()): ?>
                                    <div class="mt-6">
                                        <div class="mb-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <h3 class="text-sm font-medium text-blue-800">Back Order Items (50% Due Now)</h3>
                                            <p class="mt-1 text-xs text-blue-700">These items are on back order. Pay 50% now, 50% when restocked.</p>
                                        </div>
                                        <div class="space-y-4">
                                            <?php $__currentLoopData = $backorderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                            <img src="<?php echo e(optional($ci->item->photos->first())->url); ?>" alt="" class="w-full h-full object-cover"/>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900"><?php echo e($ci->item->name); ?></p>
                                                            <p class="text-xs text-gray-500">Qty: <?php echo e($ci->quantity); ?></p>
                                                            <span class="inline-flex mt-1 px-2 py-0.5 text-[11px] rounded bg-blue-100 text-blue-800">Back-Order</span>
                                                            <?php if($ci->item->restock_date): ?>
                                                                <span class="block text-[11px] text-blue-700">Ships after <?php echo e($ci->item->restock_date->format('M d, Y')); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm text-gray-900">₱<?php echo e(number_format($ci->subtotal, 2)); ?></p>
                                                        <p class="text-xs text-blue-600 font-medium">Pay now: ₱<?php echo e(number_format($ci->subtotal * 0.5, 2)); ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-700 font-medium">Back order subtotal: ₱<?php echo e(number_format($backorderSubtotal, 2)); ?></p>
                                        <p class="mt-1 text-sm text-blue-700">50% Down: ₱<?php echo e(number_format($backorderSubtotal * 0.5, 2)); ?> | Remaining: ₱<?php echo e(number_format($backorderSubtotal * 0.5, 2)); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php elseif(!$paymentOnly): ?>
                            <!-- Non-Mixed Order Display (use existing variable names) -->
                            <?php
                                $nonMixedStdItems = $cartItems->filter(fn($ci) => !($ci->is_backorder ?? false));
                                $nonMixedBackItems = $cartItems->filter(fn($ci) => ($ci->is_backorder ?? false));
                            ?>
                            <?php if($nonMixedStdItems->isNotEmpty()): ?>
                                <div class="mt-4">
                                    <h3 class="text-sm font-medium text-gray-900">Standard Items</h3>
                                    <div class="mt-3 space-y-4">
                                        <?php $__currentLoopData = $nonMixedStdItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                        <img src="<?php echo e(optional($ci->item->photos->first())->url); ?>" alt="" class="w-full h-full object-cover"/>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900"><?php echo e($ci->item->name); ?></p>
                                                        <p class="text-xs text-gray-500">Qty: <?php echo e($ci->quantity); ?></p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-900">₱<?php echo e(number_format($ci->subtotal, 2)); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <?php if($nonMixedBackItems->isNotEmpty()): ?>
                                        <p class="mt-2 text-sm text-gray-700">Standard items subtotal: ₱<?php echo e(number_format($nonMixedStdItems->sum('subtotal'), 2)); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if($nonMixedBackItems->isNotEmpty()): ?>
                                <div class="mt-6">
                                    <div class="mb-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                                        <h3 class="text-sm font-medium text-blue-800">Back Order Items</h3>
                                        <p class="mt-1 text-xs text-blue-700">These items will be shipped separately once they're back in stock.</p>
                                    </div>
                                    <div class="space-y-4">
                                        <?php $__currentLoopData = $nonMixedBackItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                        <img src="<?php echo e(optional($ci->item->photos->first())->url); ?>" alt="" class="w-full h-full object-cover"/>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900"><?php echo e($ci->item->name); ?></p>
                                                        <p class="text-xs text-gray-500">Qty: <?php echo e($ci->quantity); ?></p>
                                                        <span class="inline-flex mt-1 px-2 py-0.5 text-[11px] rounded bg-blue-100 text-blue-800">Back-Order</span>
                                                        <?php if($ci->item->restock_date): ?>
                                                            <span class="block text-[11px] text-blue-700">Ships after <?php echo e($ci->item->restock_date->format('M d, Y')); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-900">₱<?php echo e(number_format($ci->subtotal, 2)); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <?php if($nonMixedStdItems->isNotEmpty()): ?>
                                        <p class="mt-2 text-sm text-gray-700">Back order items subtotal: ₱<?php echo e(number_format($nonMixedBackItems->sum('subtotal'), 2)); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        </div>

                    <div class="mt-6 border-t pt-4 space-y-2 text-sm">
						<?php if($paymentOnly): ?>
							<?php
								$coStatus = optional($payOrder->customOrders->first())->status ?? $payOrder->status;
								$badge = match($coStatus){
									'in_production' => 'bg-blue-100 text-blue-800',
									'pending_review' => 'bg-yellow-100 text-yellow-800',
									'approved' => 'bg-green-100 text-green-800',
									'rejected' => 'bg-red-100 text-red-800',
									'completed' => 'bg-gray-100 text-gray-800',
									default => 'bg-gray-100 text-gray-800',
								};
								$coStatusLabel = ucfirst(str_replace('_',' ', $coStatus ?? 'pending'));
							?>
							<div class="mb-3 p-3 border border-gray-200 rounded-lg">
								<div class="text-sm font-medium text-gray-900">Custom Order #<?php echo e($payOrder->id); ?></div>
								<div class="mt-1 text-xs text-gray-600 flex items-center gap-2">
									<span>Status:</span>
									<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium <?php echo e($badge); ?>"><?php echo e($coStatusLabel); ?></span>
								</div>
							</div>
						<?php endif; ?>
						<div class="flex items-center justify-between"><span class="text-gray-600">Subtotal</span><span>₱<?php echo e(number_format($subtotal, 2)); ?></span></div>
						<?php if(!$paymentOnly): ?>
							<div class="flex items-center justify-between"><span class="text-gray-600">Shipping</span><span>₱<?php echo e(number_format($shipping, 2)); ?></span></div>
						<?php endif; ?>
						
						<!-- COD Shipping Fee Note -->
						<div x-show="method === 'COD'" x-cloak class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
							<p class="text-xs text-blue-800 font-medium mb-1">📦 COD Shipping Fees</p>
							<p class="text-xs text-blue-700">Shipping fees and COD charges will be calculated based on LBC standards and added to your order total. You'll pay the full amount (items + shipping + COD fee) upon delivery.</p>
						</div>
                        
                        <?php
                            $displayAmount = $requiredPaymentAmount ?? $total;
                        ?>
                        
                        <?php if($isMixedOrder): ?>
                            <div class="mt-3 space-y-2 border-t pt-3">
                                <h3 class="font-semibold text-gray-900">Payment Breakdown</h3>
                                <div class="flex items-center justify-between p-2 bg-blue-50 rounded border border-blue-200">
                                    <span class="text-blue-900">Back Order (50% Down)</span>
                                    <span class="font-semibold text-blue-900">₱<?php echo e(number_format($backorderSubtotal * 0.5, 2)); ?></span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-amber-50 rounded border-2 border-amber-300 mt-2">
                                    <span class="font-bold text-amber-900">💰 Total Due Now</span>
                                    <span class="font-bold text-lg text-amber-900">₱<?php echo e(number_format($requiredPaymentAmount, 2)); ?></span>
                                </div>
                                <p class="text-xs text-blue-700 italic font-medium">Remaining: ₱<?php echo e(number_format($backorderSubtotal * 0.5, 2)); ?> (to be collected by LBC courier upon delivery)</p>
                            </div>
                        <?php elseif(($cartItems->contains(fn($ci) => ($ci->is_backorder ?? false)) && !$paymentOnly) || ($paymentOnly && ($payOrder->order_type === 'backorder' || $payOrder->order_type === 'custom'))): ?>
                            <div class="mt-3 space-y-2 border-t pt-3">
                                <?php if(!$paymentOnly): ?>
                                    <h3 class="font-semibold text-gray-900">Payment Required (50% Down Payment)</h3>
                                <?php elseif($payOrder->order_type === 'custom'): ?>
                                    <h3 class="font-semibold text-gray-900">Payment Breakdown</h3>
                                <?php endif; ?>
                                
                                <?php if($paymentOnly && $payOrder->order_type === 'custom'): ?>
                                    <!-- Custom Order Payment Breakdown -->
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200">
                                            <span class="text-gray-700">Total Price</span>
                                            <span class="font-medium text-gray-900">₱<?php echo e(number_format($total, 2)); ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-2 bg-blue-50 rounded border border-blue-200">
                                            <span class="text-blue-900">Required Payment (50% Down)</span>
                                            <span class="font-semibold text-blue-900">₱<?php echo e(number_format($requiredPaymentAmount, 2)); ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200">
                                            <span class="text-gray-700">Remaining Balance (50%)</span>
                                            <span class="font-medium text-gray-900">₱<?php echo e(number_format($total - $requiredPaymentAmount, 2)); ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded border-2 border-amber-300 mt-2">
                                            <span class="font-bold text-amber-900">💰 Total Due Now</span>
                                            <span class="font-bold text-lg text-amber-900">₱<?php echo e(number_format($requiredPaymentAmount, 2)); ?></span>
                                        </div>
                                        <p class="text-xs text-blue-700 italic font-medium">Remaining 50% (₱<?php echo e(number_format($total - $requiredPaymentAmount, 2)); ?>) will be collected by the LBC courier upon delivery</p>
                                    </div>
                                <?php else: ?>
                                    <!-- Back Order Payment Breakdown -->
                                    <div class="flex items-center justify-between p-3 bg-amber-50 rounded border-2 border-amber-300">
                                        <span class="font-bold text-amber-900">💰 Down Payment Due Now</span>
                                        <span class="font-bold text-lg text-amber-900">₱<?php echo e(number_format($displayAmount, 2)); ?></span>
                                    </div>
                                    <p class="text-xs text-blue-700 italic font-medium">Remaining 50% (₱<?php echo e(number_format(($total - $displayAmount), 2)); ?>) will be collected by the LBC courier upon delivery</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center justify-between font-semibold text-gray-900 p-3 bg-gray-100 rounded border border-gray-300 mt-3">
                                <span>Total Amount Due</span>
                                <span>₱<?php echo e(number_format($displayAmount, 2)); ?></span>
                            </div>
                        <?php endif; ?>
                        
						<p class="text-xs text-gray-500 mt-2">Tax and shipping cost will be calculated later.</p>
                        <?php if(!$paymentOnly && $cartItems->contains(fn($ci) => ($ci->is_backorder ?? false))): ?>
                            <p class="text-xs text-blue-700 mt-1">This item is on back order. We'll ship it once restocked.</p>
                        <?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- GCash Modal -->
	<div id="gcashModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 overflow-y-auto">
		<div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden my-auto max-h-[90vh] flex flex-col">
			<div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between flex-shrink-0">
				<h2 class="font-semibold">GCash Payment</h2>
				<button onclick="closeModal('gcashModal')" class="text-gray-500 hover:text-gray-700">✕</button>
			</div>
			<div class="p-5 space-y-4 overflow-y-auto">
				<!-- Required Amount Display -->
				<div class="p-3 bg-amber-50 border-2 border-amber-300 rounded-lg">
					<div class="text-xs font-medium text-amber-900 uppercase tracking-wide mb-1">Required Amount to Pay</div>
					<div class="text-2xl font-bold text-amber-900" id="gcashRequiredAmount">₱0.00</div>
				</div>

				<!-- GCash Details -->
				<div class="text-sm text-gray-700">
					<p class="font-medium mb-2">Send payment to:</p>
					<p class="text-gray-600">GCash Number: <strong>0917-123-4567</strong></p>
					<p class="text-xs text-gray-500 mt-1">Or scan the QR code below</p>
				</div>
				<img src="/images/gcash-qr.png" alt="GCash QR" class="w-full rounded border" />

				<!-- Amount Paid Input -->
				<div>
					<label for="gcashAmount" class="block text-sm font-medium text-gray-700 mb-1">Amount Paid <span class="text-red-500">*</span></label>
					<input id="gcashAmount" type="number" step="0.01" min="0" placeholder="0.00" class="w-full rounded-md border-2 border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f] focus:outline-none transition-colors" required oninput="validateGCashAmount()">
					<p id="gcashAmountErr" class="hidden mt-1 text-xs text-red-600"></p>
				</div>

				<!-- Reference Number Input -->
				<div>
					<label for="gcashRef" class="block text-sm font-medium text-gray-700 mb-1">Reference Number <span class="text-red-500">*</span></label>
					<input id="gcashRef" type="text" placeholder="Enter GCash reference number" class="w-full rounded-md border-2 border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f] focus:outline-none transition-colors" required>
					<p class="mt-1 text-xs text-gray-500">Minimum 6 characters</p>
				</div>

				<!-- Proof Upload -->
				<div>
					<label for="gcashProof" class="block text-sm font-medium text-gray-700 mb-1">Upload Payment Proof <span class="text-red-500">*</span></label>
					<input id="gcashProof" type="file" accept="image/*" class="w-full rounded-md border-2 border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f] focus:outline-none transition-colors" required>
					<p class="mt-1 text-xs text-gray-500">Upload screenshot or receipt of your GCash payment</p>
				</div>

				<button id="gcashConfirmBtn" class="w-full px-4 py-2 rounded-md text-white font-medium hover:opacity-95 transition-opacity focus:outline-none focus:ring-2 focus:ring-[#0ea5e9] focus:ring-offset-2" style="background:#0ea5e9;">Confirm Payment</button>
				<p id="gcashErr" class="hidden text-sm text-red-600"></p>
			</div>
		</div>
	</div>

	<!-- Bank Transfer Modal -->
	<div id="bankModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 overflow-y-auto">
		<div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden my-auto max-h-[90vh] flex flex-col">
			<div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between flex-shrink-0">
				<h2 class="font-semibold">Bank Transfer</h2>
				<button onclick="closeModal('bankModal')" class="text-gray-500 hover:text-gray-700">✕</button>
			</div>
			<div class="p-5 space-y-4 overflow-y-auto">
				<!-- Required Amount Display -->
				<div class="p-3 bg-amber-50 border-2 border-amber-300 rounded-lg">
					<div class="text-xs font-medium text-amber-900 uppercase tracking-wide mb-1">Required Amount to Pay</div>
					<div class="text-2xl font-bold text-amber-900" id="bankRequiredAmount">₱0.00</div>
				</div>

				<!-- Bank Details -->
				<div class="text-sm text-gray-700">
					<p class="font-medium mb-2">Transfer to:</p>
					<p><strong>BPI</strong></p>
					<p>Account Name: <strong>WOW Carmen</strong></p>
					<p>Account Number: <strong>1234-5678-90</strong></p>
				</div>

				<!-- Amount Paid Input -->
				<div>
					<label for="bankAmount" class="block text-sm font-medium text-gray-700 mb-1">Amount Paid <span class="text-red-500">*</span></label>
					<input id="bankAmount" type="number" step="0.01" min="0" placeholder="0.00" class="w-full rounded-md border-2 border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f] focus:outline-none transition-colors" required oninput="validateBankAmount()">
					<p id="bankAmountErr" class="hidden mt-1 text-xs text-red-600"></p>
				</div>

				<!-- Proof Upload -->
				<div>
					<label for="bankProof" class="block text-sm font-medium text-gray-700 mb-1">Upload Deposit Slip <span class="text-red-500">*</span></label>
					<input id="bankProof" type="file" accept="image/*" class="w-full rounded-md border-2 border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f] focus:outline-none transition-colors" required>
					<p class="mt-1 text-xs text-gray-500">Upload image of your bank deposit slip or transfer receipt</p>
				</div>

				<button id="bankSubmitBtn" class="w-full px-4 py-2 rounded-md text-white font-medium hover:opacity-95 transition-opacity focus:outline-none focus:ring-2 focus:ring-[#c59d5f] focus:ring-offset-2" style="background:#c59d5f;">Submit Proof</button>
				<p id="bankErr" class="hidden text-sm text-red-600"></p>
			</div>
		</div>
	</div>

	<?php echo $__env->make('partials.customer-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>

<script>
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
	const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
	const completeBtn = document.getElementById('completeBtn');
	const form = document.querySelector('form[action="<?php echo e(route('checkout.store')); ?>"]');
	const gcashModal = document.getElementById('gcashModal');
	const bankModal = document.getElementById('bankModal');

	// Payment tracking
	window.payOrderId = <?php echo e(isset($payOrder) && $payOrder ? $payOrder->id : 'null'); ?>;
	window.payAmount = <?php echo e(isset($payOrder) && $payOrder ? (float) $payOrder->total_amount : 0); ?>;
	window.requiredPaymentAmount = <?php echo e($requiredPaymentAmount ?? 0); ?>;
	window.isMixedOrder = <?php echo e(isset($isMixedOrder) && $isMixedOrder ? 'true' : 'false'); ?>;
	window.requires50PercentUpfront = <?php echo e(isset($requires50PercentUpfront) && $requires50PercentUpfront ? 'true' : 'false'); ?>;

	// Check if required elements exist
	if (!completeBtn) {
		console.error('Complete button not found');
	}
	if (!form) {
		console.error('Checkout form not found');
	}
	if (!csrf) {
		console.error('CSRF token not found');
	}

	// Global functions for inline handlers
	window.openModal = function(id){ 
		const el = document.getElementById(id); 
		if (!el) return;
		el.classList.remove('hidden'); 
		el.classList.add('flex');
		
		// Set required amount in modal
		const requiredAmount = window.requiredPaymentAmount || 0;
		if (id === 'gcashModal') {
			const reqEl = document.getElementById('gcashRequiredAmount');
			if (reqEl) reqEl.textContent = '₱' + requiredAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
			// Reset form
			const gcashAmount = document.getElementById('gcashAmount');
			const gcashRef = document.getElementById('gcashRef');
			const gcashProof = document.getElementById('gcashProof');
			const gcashAmountErr = document.getElementById('gcashAmountErr');
			const gcashErr = document.getElementById('gcashErr');
			if (gcashAmount) gcashAmount.value = '';
			if (gcashRef) gcashRef.value = '';
			if (gcashProof) gcashProof.value = '';
			if (gcashAmountErr) gcashAmountErr.classList.add('hidden');
			if (gcashErr) gcashErr.classList.add('hidden');
			if (gcashAmount) gcashAmount.classList.remove('border-red-500', 'border-green-500');
		} else if (id === 'bankModal') {
			const reqEl = document.getElementById('bankRequiredAmount');
			if (reqEl) reqEl.textContent = '₱' + requiredAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
			// Reset form
			const bankAmount = document.getElementById('bankAmount');
			const bankProof = document.getElementById('bankProof');
			const bankAmountErr = document.getElementById('bankAmountErr');
			const bankErr = document.getElementById('bankErr');
			if (bankAmount) bankAmount.value = '';
			if (bankProof) bankProof.value = '';
			if (bankAmountErr) bankAmountErr.classList.add('hidden');
			if (bankErr) bankErr.classList.add('hidden');
			if (bankAmount) bankAmount.classList.remove('border-red-500', 'border-green-500');
		}
	};
	
	window.closeModal = function(id){ 
		const el = document.getElementById(id); 
		if (el) {
			el.classList.add('hidden'); 
			el.classList.remove('flex');
		}
	};

	window.validateGCashAmount = function() {
		const amountInput = document.getElementById('gcashAmount');
		const amountErr = document.getElementById('gcashAmountErr');
		if (!amountInput || !amountErr) return;
		
		const enteredAmount = parseFloat(amountInput.value) || 0;
		const requiredAmount = window.requiredPaymentAmount || 0;
		const tolerance = 0.01;
		
		amountInput.classList.remove('border-red-500', 'border-green-500');
		amountErr.classList.add('hidden');
		
		if (amountInput.value && enteredAmount > 0) {
			if (Math.abs(enteredAmount - requiredAmount) > tolerance) {
				amountInput.classList.add('border-red-500');
				amountErr.textContent = `Please enter the correct payment amount. Required: ₱${requiredAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
				amountErr.classList.remove('hidden');
			} else {
				amountInput.classList.add('border-green-500');
			}
		}
	};

	window.validateBankAmount = function() {
		const amountInput = document.getElementById('bankAmount');
		const amountErr = document.getElementById('bankAmountErr');
		if (!amountInput || !amountErr) return;
		
		const enteredAmount = parseFloat(amountInput.value) || 0;
		const requiredAmount = window.requiredPaymentAmount || 0;
		const tolerance = 0.01;
		
		amountInput.classList.remove('border-red-500', 'border-green-500');
		amountErr.classList.add('hidden');
		
		if (amountInput.value && enteredAmount > 0) {
			if (Math.abs(enteredAmount - requiredAmount) > tolerance) {
				amountInput.classList.add('border-red-500');
				amountErr.textContent = `Please enter the correct payment amount. Required: ₱${requiredAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
				amountErr.classList.remove('hidden');
			} else {
				amountInput.classList.add('border-green-500');
			}
		}
	};

	async function createOrder() {
		// If this is payment-only for an existing order, skip order creation
		if(window.payOrderId){
			return { success: true, order_id: window.payOrderId, total: window.payAmount, required: window.requiredPaymentAmount };
		}
		if (!form || !csrf) {
			throw new Error('Form or CSRF token not available');
		}
		const data = new FormData(form);
		const res = await fetch(form.action, { 
			method:'POST', 
			headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': csrf }, 
			body: data 
		});
		
		// Check content type to ensure we're getting JSON
		const contentType = res.headers.get('content-type');
		if(!contentType || !contentType.includes('application/json')) {
			const text = await res.text();
			console.error('Response is not JSON:', text);
			throw new Error('Server error: Invalid response format. Please check form validation.');
		}
		
		const json = await res.json();
		if(!json?.success){ throw new Error(json?.message || 'Order creation failed'); }
		return json;
	}

if (completeBtn && form) {
	completeBtn.addEventListener('click', async () => {
		// Validate form first
		if (!form.checkValidity()) {
			form.reportValidity();
			return;
		}

		const selected = document.querySelector('input[name="payment_method"]:checked')?.value || null;
		
		// Prevent COD for orders requiring 50% upfront
		if (selected === 'COD' && window.requires50PercentUpfront) {
			alert('COD is not available for back order, custom order, or mixed orders with back order items. Please select Bank Transfer or GCash.');
			// Find and select Bank Transfer instead
			const bankRadio = document.querySelector('input[name="payment_method"][value="Bank"]');
			if (bankRadio) {
				bankRadio.checked = true;
				const alpineComponent = Alpine.$data(document.querySelector('[x-data]'));
				if (alpineComponent) {
					alpineComponent.method = 'Bank';
				}
			}
			return;
		}
		
		if (!selected) {
			// Show Alpine.js error state by dispatching a custom event
			const errorDiv = document.querySelector('[x-show="paymentMethodError"]');
			if (errorDiv && window.Alpine) {
				// Use Alpine's reactive system
				const alpineComponent = Alpine.$data(errorDiv.closest('[x-data]'));
				if (alpineComponent) {
					alpineComponent.paymentMethodError = true;
				}
			}
			// Also show alert for immediate feedback
			alert('Please select a payment method (GCash, Bank Transfer, or COD) to proceed.');
			// Scroll to payment method section
			setTimeout(() => {
				document.querySelector('input[name="payment_method"]')?.closest('.grid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}, 100);
			return;
		}

		if(selected === 'GCash'){
			openModal('gcashModal');
		} else if(selected === 'Bank'){
			openModal('bankModal');
		} else if(selected === 'COD'){
			// For COD, submit the form directly
			form.submit();
		} else {
			alert('Please select a valid payment method.');
		}
	});
} else {
	console.error('Cannot attach event listener: completeBtn or form is missing');
}

const gcashConfirmBtn = document.getElementById('gcashConfirmBtn');
if (gcashConfirmBtn) {
	gcashConfirmBtn.addEventListener('click', async () => {
		const amountInput = document.getElementById('gcashAmount');
		const refInput = document.getElementById('gcashRef');
		const proofInput = document.getElementById('gcashProof');
		const amountErr = document.getElementById('gcashAmountErr');
		const err = document.getElementById('gcashErr');
		
		// Reset errors
		err.classList.add('hidden'); err.textContent = '';
		amountErr.classList.add('hidden'); amountErr.textContent = '';
		
		// Validate amount
		const enteredAmount = parseFloat(amountInput.value) || 0;
		const requiredAmount = window.requiredPaymentAmount || 0;
		
		if (!amountInput.value || enteredAmount <= 0) {
			amountErr.textContent = 'Please enter the payment amount.';
			amountErr.classList.remove('hidden');
			amountInput.classList.add('border-red-500');
			return;
		}
		
		// Validate amount matches required (allow small tolerance for rounding)
		const tolerance = 0.01;
		if (Math.abs(enteredAmount - requiredAmount) > tolerance) {
			amountErr.textContent = `Please enter the correct payment amount. Required: ₱${requiredAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
			amountErr.classList.remove('hidden');
			amountInput.classList.add('border-red-500');
			return;
		}
		amountInput.classList.remove('border-red-500');
		
		// Validate reference number
		const ref = refInput.value.trim();
		if (!ref || ref.length < 6) {
			err.textContent = 'Reference number must be at least 6 characters.';
			err.classList.remove('hidden');
			return;
		}
		
		// Validate proof upload
		if (!proofInput.files || !proofInput.files[0]) {
			err.textContent = 'Please upload payment proof.';
			err.classList.remove('hidden');
			return;
		}
		
		try{
			const o = await createOrder();
			const fd = new FormData();
			fd.append('order_id', o.order_id);
			fd.append('amount', enteredAmount);
			fd.append('reference', ref);
			fd.append('proof', proofInput.files[0]);
			
			const res = await fetch('<?php echo e(route('payments.gcash')); ?>', { 
				method:'POST', 
				headers:{ 'X-CSRF-TOKEN': csrf }, 
				body: fd 
			});
			
			if(!res.ok) {
				const errData = await res.json().catch(() => ({}));
				throw new Error(errData?.message || 'Failed to confirm GCash payment');
			}
			closeModal('gcashModal');
			location.href = `/customer/orders/${o.order_id}`;
		}catch(e){ 
			err.textContent = e.message || 'Payment failed.'; 
			err.classList.remove('hidden'); 
			console.error('GCash payment error:', e);
		}
	});
} else {
	console.error('GCash confirm button not found');
}

const bankSubmitBtn = document.getElementById('bankSubmitBtn');
if (bankSubmitBtn) {
	bankSubmitBtn.addEventListener('click', async () => {
		const amountInput = document.getElementById('bankAmount');
		const proofInput = document.getElementById('bankProof');
		const amountErr = document.getElementById('bankAmountErr');
		const err = document.getElementById('bankErr');
		
		// Reset errors
		err.classList.add('hidden'); err.textContent = '';
		amountErr.classList.add('hidden'); amountErr.textContent = '';
		
		// Validate amount
		const enteredAmount = parseFloat(amountInput.value) || 0;
		const requiredAmount = window.requiredPaymentAmount || 0;
		
		if (!amountInput.value || enteredAmount <= 0) {
			amountErr.textContent = 'Please enter the payment amount.';
			amountErr.classList.remove('hidden');
			amountInput.classList.add('border-red-500');
			return;
		}
		
		// Validate amount matches required (allow small tolerance for rounding)
		const tolerance = 0.01;
		if (Math.abs(enteredAmount - requiredAmount) > tolerance) {
			amountErr.textContent = `Please enter the correct payment amount. Required: ₱${requiredAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
			amountErr.classList.remove('hidden');
			amountInput.classList.add('border-red-500');
			return;
		}
		amountInput.classList.remove('border-red-500');
		
		// Validate proof upload
		if (!proofInput.files || !proofInput.files[0]) {
			err.textContent = 'Please upload an image of the deposit slip.';
			err.classList.remove('hidden');
			return;
		}
		
		try{
			const o = await createOrder();
			const fd = new FormData();
			fd.append('order_id', o.order_id);
			fd.append('amount', enteredAmount);
			fd.append('proof', proofInput.files[0]);
			
			const res = await fetch('<?php echo e(route('payments.bank')); ?>', { 
				method:'POST', 
				headers:{ 'X-CSRF-TOKEN': csrf }, 
				body: fd 
			});
			
			if(!res.ok) {
				const errData = await res.json().catch(() => ({}));
				throw new Error(errData?.message || 'Failed to upload bank proof');
			}
			closeModal('bankModal');
			location.href = `/customer/orders/${o.order_id}`;
		}catch(e){ 
			err.textContent = e.message || 'Upload failed.'; 
			err.classList.remove('hidden'); 
			console.error('Bank transfer error:', e);
		}
	});
} else {
	console.error('Bank submit button not found');
}

}); // End of DOMContentLoaded
</script>


<?php /**PATH C:\xampp\htdocs\wowc\resources\views/checkout.blade.php ENDPATH**/ ?>