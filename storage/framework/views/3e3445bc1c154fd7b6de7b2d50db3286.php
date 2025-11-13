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
    <!-- Full-screen container with responsive layout -->
    <div class="min-h-screen w-full flex flex-col lg:flex-row">

        <!-- ========== LEFT PANEL (Background + Welcome Text) ========== -->
        <div class="w-full lg:w-1/2 hidden lg:flex items-center justify-center bg-cover bg-center relative"
             style="background-image: url('<?php echo e(asset('images/login-bg.jpg')); ?>');">

            <!-- Optional overlay for readability -->
            <div class="absolute inset-0 bg-[#A9793E] bg-opacity-50"></div>

            <div class="relative z-10 text-white text-center px-10">
                <h2 class="text-4xl font-bold mb-4">Welcome back!</h2>
                <p class="text-white text-opacity-90">
                    You can sign in to access your existing account.
                </p>
            </div>
        </div>

        <!-- ========== RIGHT PANEL (Login Form) ========== -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md space-y-6">

                <!-- Heading -->
                <h2 class="text-3xl font-semibold text-[#1F1F1F]">Sign In</h2>

                <!-- Session Status -->
                <?php if(session('status')): ?>
                    <div class="text-sm text-green-600">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-5">
                    <?php echo csrf_field(); ?>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" type="email" name="email" required autofocus
                               value="<?php echo e(old('email')); ?>"
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

                    <!-- Remember Me + Forgot Password -->
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="remember" class="rounded border-gray-300">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div>
                        <button type="submit"
                                class="w-full py-3 bg-[#A9793E] hover:bg-[#8F6532] text-white font-semibold rounded-full transition">
                            Sign In
                        </button>
                    </div>

                    <!-- Register Link -->
                    <p class="text-sm text-center text-gray-600">
                        New here?
                        <a href="<?php echo e(route('register')); ?>" class="text-[#A9793E] hover:underline">
                            Create an Account
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
<?php /**PATH C:\xampp\htdocs\wowc\resources\views/auth/login.blade.php ENDPATH**/ ?>