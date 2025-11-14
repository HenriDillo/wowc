<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('page_title', 'Employee Dashboard'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{ display: none !important; }</style>
</head>
<body x-data="{ dropdownOpen: false }">

<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="w-64 bg-[#c49b6e] flex flex-col shadow-lg">
        <div class="p-6 border-b border-[#b08a5c]">
            <span class="text-white font-semibold text-lg">Wow Carmen</span>
        </div>
        <nav class="flex-1 p-4 space-y-1">
            <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center space-x-3 p-3 text-white <?php echo e(request()->routeIs('dashboard') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]'); ?> rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2l8 6v9a1 1 0 01-1 1h-5v-5H8v5H3a1 1 0 01-1-1V8l8-6z"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="<?php echo e(route('employee.raw-materials')); ?>" class="flex items-center space-x-3 p-3 text-white <?php echo e(request()->routeIs('employee.raw-materials') || request()->routeIs('employee.materials.*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]'); ?> rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3h12a1 1 0 011 1v3H3V4a1 1 0 011-1z"></path>
                    <path d="M3 8h14v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"></path>
                </svg>
                <span class="font-medium">Raw Materials</span>
            </a>
            <a href="<?php echo e(route('employee.items')); ?>" class="flex items-center space-x-3 p-3 text-white <?php echo e(request()->routeIs('employee.items*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]'); ?> rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 3a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1V4a1 1 0 00-1-1H6z"></path>
                </svg>
                <span class="font-medium">Production</span>
            </a>
            <a href="<?php echo e(route('employee.orders')); ?>" class="flex items-center space-x-3 p-3 text-white <?php echo e(request()->routeIs('employee.orders*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]'); ?> rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 3h14a1 1 0 011 1v3H2V4a1 1 0 011-1z"></path>
                    <path d="M2 8h16v8a1 1 0 01-1 1H3a1 1 0 01-1-1V8z"></path>
                </svg>
                <span class="font-medium">Orders</span>
            </a>
            <a href="<?php echo e(route('employee.reports')); ?>" class="flex items-center space-x-3 p-3 text-white <?php echo e(request()->routeIs('employee.reports*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]'); ?> rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                </svg>
                <span class="font-medium">Reports</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 bg-white">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800"><?php echo $__env->yieldContent('page_title', 'Employee Dashboard'); ?></h1>
            <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800">
                    <span>Hello, <?php echo e(Auth::user()->name ?? 'Employee'); ?></span>
                    <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                </button>
                <div x-show="dropdownOpen" x-cloak x-transition @click.outside="dropdownOpen=false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <a href="<?php echo e(route('profile.edit')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Profile</a>
                    <form action="<?php echo e(route('logout')); ?>" method="POST"><?php echo csrf_field(); ?>
                        <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">Log Out</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="p-6">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
</div>

<?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html>


<?php /**PATH C:\xampp\htdocs\wowc\resources\views/layouts/employee.blade.php ENDPATH**/ ?>