<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="min-h-screen w-full flex flex-col lg:flex-row">

        <!-- ========== LEFT PANEL (Image + Message) ========== -->
        <div class="w-full lg:w-1/2 hidden lg:flex items-center justify-center bg-cover bg-center relative"
             style="background-image: url('<?php echo e(asset('images/login-bg.jpg')); ?>');">

            <div class="absolute inset-0 bg-[#A9793E] bg-opacity-50"></div>

            <div class="relative z-10 text-white text-center px-10">
                <h2 class="text-4xl font-bold mb-4">Join WOW Carmen</h2>
                <p class="text-white text-opacity-90">
                    Create an account to explore our handcrafted products.
                </p>
            </div>
        </div>

        <!-- ========== RIGHT PANEL (Register Form) ========== -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md space-y-6">

                <h2 class="text-3xl font-semibold text-[#1F1F1F]">Create Account</h2>

                <form method="POST" action="<?php echo e(route('register')); ?>" class="space-y-5">
                    <?php echo csrf_field(); ?>

                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input id="first_name" type="text" name="first_name" value="<?php echo e(old('first_name')); ?>" required autofocus
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                                   placeholder="First Name">
                            <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input id="last_name" type="text" name="last_name" value="<?php echo e(old('last_name')); ?>" required
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                                   placeholder="Last Name">
                            <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Address Line -->
                    <div>
                        <label for="address_line" class="block text-sm font-medium text-gray-700">Address</label>
                        <input id="address_line" type="text" name="address_line" value="<?php echo e(old('address_line')); ?>" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="Street / Barangay">
                        <?php $__errorArgs = ['address_line'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- City / Province / Postal -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                            <input id="city" type="text" name="city" value="<?php echo e(old('city')); ?>" required
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]">
                        </div>
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700">Province</label>
                            <input id="province" type="text" name="province" value="<?php echo e(old('province')); ?>" required
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]">
                        </div>
                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                            <input id="postal_code" type="text" name="postal_code" value="<?php echo e(old('postal_code')); ?>" required
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]">
                        </div>
                    </div>

                    <!-- Contact Number (kept) -->
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <input id="contact_number" type="text" name="contact_number" value="<?php echo e(old('contact_number')); ?>" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                        <?php $__errorArgs = ['contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="Email">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" type="password" name="password" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="Password">
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="Confirm Password">
                    </div>

                    <!-- Submit -->
                    <div>
                        <button type="submit"
                                class="w-full py-3 bg-[#A9793E] hover:bg-[#8F6532] text-white font-semibold rounded-full transition">
                            Register
                        </button>
                    </div>

                    <!-- Already have an account -->
                    <p class="text-sm text-center text-gray-600">
                        Already have an account?
                        <a href="<?php echo e(route('login')); ?>" class="text-[#A9793E] hover:underline">
                            Sign In
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\wowc\resources\views/auth/register.blade.php ENDPATH**/ ?>