<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'Employee Dashboard'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{ display: none !important; }</style>
</head>
<body x-data="{ dropdownOpen: false }">

<?php
use Illuminate\Support\Facades\DB;
?>

<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="w-64 bg-[#c49b6e] flex flex-col shadow-lg">
        <div class="p-6 border-b border-[#b08a5c]">
            <span class="text-white font-semibold text-lg">Wow Carmen</span>
        </div>
        <nav class="flex-1 p-4 space-y-1">
            <div class="flex items-center space-x-3 p-3 text-white bg-[#b08a5c] rounded-lg">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2l8 6v9a1 1 0 01-1 1h-5v-5H8v5H3a1 1 0 01-1-1V8l8-6z"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </div>
            <a href="<?php echo e(route('employee.raw-materials')); ?>" class="flex items-center space-x-3 p-3 text-white hover:bg-[#b08a5c] rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3h12a1 1 0 011 1v3H3V4a1 1 0 011-1z"></path>
                    <path d="M3 8h14v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"></path>
                </svg>
                <span class="font-medium">Raw Materials</span>
            </a>
            <a href="<?php echo e(route('employee.items')); ?>" class="flex items-center space-x-3 p-3 text-white hover:bg-[#b08a5c] rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 3a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1V4a1 1 0 00-1-1H6z"></path>
                </svg>
                <span class="font-medium">Production</span>
            </a>
            <a href="<?php echo e(route('employee.orders')); ?>" class="flex items-center space-x-3 p-3 text-white hover:bg-[#b08a5c] rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 3h14a1 1 0 011 1v3H2V4a1 1 0 011-1z"></path>
                    <path d="M2 8h16v8a1 1 0 01-1 1H3a1 1 0 01-1-1V8z"></path>
                </svg>
                <span class="font-medium">Orders</span>
            </a>
            <a href="<?php echo e(route('employee.reports')); ?>" class="flex items-center space-x-3 p-3 text-white hover:bg-[#b08a5c] rounded-lg transition-colors">
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
            <h1 class="text-2xl font-semibold text-gray-800">Employee Dashboard</h1>
            <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800">
                    <span>Hello, <?php echo e(Auth::user()->name ?? 'Employee'); ?></span>
                    <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                </button>
                <div x-show="dropdownOpen" x-cloak x-transition @click.outside="dropdownOpen=false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <a href="<?php echo e(route('profile.edit')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        Profile
                    </a>
                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">Log Out</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="p-6 space-y-10">
            <!-- Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="<?php echo e(route('employee.raw-materials')); ?>" class="block bg-white border rounded-xl shadow-sm hover:shadow transition">
                    <div class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white" style="background:#c49b6e;">RM</div>
                        <div>
                            <div class="text-sm text-gray-600">Total Raw Materials</div>
                            <div class="text-2xl font-semibold text-gray-900"><?php echo e($totalRawMaterials ?? 0); ?></div>
                        </div>
                    </div>
                </a>
                <a href="<?php echo e(route('employee.items')); ?>" class="block bg-white border rounded-xl shadow-sm hover:shadow transition">
                    <div class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white bg-blue-600">IT</div>
                        <div>
                            <div class="text-sm text-gray-600">Total Items</div>
                            <div class="text-2xl font-semibold text-gray-900"><?php echo e($totalItems ?? 0); ?></div>
                        </div>
                    </div>
                </a>
                <a href="<?php echo e(route('admin.stock.index')); ?>" class="block bg-white border rounded-xl shadow-sm hover:shadow transition">
                    <div class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white bg-green-600">ST</div>
                        <div>
                            <div class="text-sm text-gray-600">Total Stock (Items/Materials)</div>
                            <div class="text-2xl font-semibold text-gray-900"><?php echo e(($totalItemStock ?? 0) + ($totalMaterialStock ?? 0)); ?></div>
                        </div>
                    </div>
                </a>
                <a href="<?php echo e(route('employee.orders')); ?>" class="block bg-white border rounded-xl shadow-sm hover:shadow transition">
                    <div class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white bg-indigo-600">OR</div>
                        <div>
                            <div class="text-sm text-gray-600">Total Orders</div>
                            <div class="text-2xl font-semibold text-gray-900"><?php echo e($totalOrders ?? 0); ?></div>
                            <div class="mt-1 text-xs text-gray-600">Pending: <?php echo e($ordersPending ?? 0); ?> • Completed: <?php echo e($ordersCompleted ?? 0); ?> • Cancelled: <?php echo e($ordersCancelled ?? 0); ?></div>
                        </div>
                    </div>
                </a>
                <a href="<?php echo e(route('employee.orders')); ?>?type=backorder&backorder_status=pending_stock" class="block bg-white border rounded-xl shadow-sm hover:shadow transition">
                    <div class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white bg-blue-600">BO</div>
                        <div>
                            <div class="text-sm text-gray-600">Pending Back Orders</div>
                            <div class="text-2xl font-semibold text-gray-900"><?php echo e($pendingBackOrders ?? 0); ?></div>
                            <div class="mt-1 text-xs text-gray-600">Awaiting restock</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Reports Section -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Reports & Analytics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <a href="<?php echo e(route('employee.reports.sales')); ?>" class="block bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl shadow-sm hover:shadow transition p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-sm font-semibold text-blue-900 mb-2">Sales Report</div>
                                <p class="text-xs text-blue-700">View sales metrics by period, order type, and payment method</p>
                            </div>
                            <div class="text-3xl">📊</div>
                        </div>
                        <div class="mt-3 text-xs text-blue-600 font-medium">View Report →</div>
                    </a>
                    <a href="<?php echo e(route('employee.reports.inventory')); ?>" class="block bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl shadow-sm hover:shadow transition p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-sm font-semibold text-green-900 mb-2">Inventory Report</div>
                                <p class="text-xs text-green-700">Monitor stock levels, reorder alerts, and movements</p>
                            </div>
                            <div class="text-3xl">📦</div>
                        </div>
                        <div class="mt-3 text-xs text-green-600 font-medium">View Report →</div>
                    </a>
                </div>
            </div>

            <!-- Calendar Widget -->
            <div x-data="{ calendarMonth: <?php echo e(now()->month); ?>, calendarYear: <?php echo e(now()->year); ?> }">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Events Calendar</h2>
                <div class="bg-white border rounded-xl shadow-sm p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-gray-900" x-text="`${new Date(calendarYear, calendarMonth - 1).toLocaleString('default', { month: 'long', year: 'numeric' })}`"></h3>
                        <div class="flex gap-2">
                            <button @click="calendarMonth = calendarMonth === 1 ? 12 : calendarMonth - 1; if(calendarMonth === 12) calendarYear--" class="p-1 hover:bg-gray-100 rounded text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button @click="calendarMonth = <?php echo e(now()->month); ?>; calendarYear = <?php echo e(now()->year); ?>" class="px-2 py-1 text-xs font-medium text-[#c49b6e] hover:bg-amber-50 rounded">Today</button>
                            <button @click="calendarMonth = calendarMonth === 12 ? 1 : calendarMonth + 1; if(calendarMonth === 1) calendarYear++" class="p-1 hover:bg-gray-100 rounded text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-1 mb-3">
                        <div class="text-center text-xs font-semibold text-gray-600 py-1">Sun</div>
                        <div class="text-center text-xs font-semibold text-gray-600 py-1">Mon</div>
                        <div class="text-center text-xs font-semibold text-gray-600 py-1">Tue</div>
                        <div class="text-center text-xs font-semibold text-gray-600 py-1">Wed</div>
                        <div class="text-center text-xs font-semibold text-gray-600 py-1">Thu</div>
                        <div class="text-center text-xs font-semibold text-gray-600 py-1">Fri</div>
                        <div class="text-center text-xs font-semibold text-gray-600 py-1">Sat</div>
                    </div>

                    <?php
                        $month = now()->month;
                        $year = now()->year;
                        $firstDay = \Carbon\Carbon::createFromDate($year, $month, 1);
                        $lastDay = $firstDay->copy()->endOfMonth();
                        $startDate = $firstDay->copy()->startOfWeek();
                        $endDate = $lastDay->copy()->endOfWeek();

                        // Get events for current month
                        $restockDates = \App\Models\Order::whereBetween('expected_restock_date', [$firstDay, $lastDay])
                            ->where('order_type', 'backorder')
                            ->where('expected_restock_date', '!=', null)
                            ->selectRaw("DATE(expected_restock_date) as date, COUNT(*) as count")
                            ->groupBy('date')
                            ->get()
                            ->keyBy('date');

                        $orderDeadlines = \App\Models\Order::whereBetween('created_at', [$firstDay, $lastDay])
                            ->whereIn('status', ['pending', 'processing'])
                            ->selectRaw("DATE(DATE_ADD(created_at, INTERVAL 3 DAY)) as deadline_date, COUNT(*) as count")
                            ->groupBy('deadline_date')
                            ->get()
                            ->keyBy('deadline_date');

                        $customOrderDates = DB::table('custom_orders')
                            ->whereBetween('created_at', [$firstDay, $lastDay])
                            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
                            ->groupBy('date')
                            ->get()
                            ->keyBy('date');

                        $deliveries = \App\Models\Order::whereBetween('created_at', [$firstDay, $lastDay])
                            ->where('status', 'shipped')
                            ->where('delivered_at', null)
                            ->selectRaw("DATE(DATE_ADD(created_at, INTERVAL 3 DAY)) as delivery_date, COUNT(*) as count")
                            ->groupBy('delivery_date')
                            ->get()
                            ->keyBy('delivery_date');

                        $events = [
                            'restock' => $restockDates,
                            'deadlines' => $orderDeadlines,
                            'customs' => $customOrderDates,
                            'deliveries' => $deliveries,
                        ];
                    ?>

                    <div class="grid grid-cols-7 gap-1">
                        <?php for($date = $startDate; $date <= $endDate; $date->addDay()): ?>
                            <?php
                                $dateString = $date->format('Y-m-d');
                                $restockCount = $events['restock'][$dateString]->count ?? 0;
                                $deadlineCount = $events['deadlines'][$dateString]->count ?? 0;
                                $customCount = $events['customs'][$dateString]->count ?? 0;
                                $deliveryCount = $events['deliveries'][$dateString]->count ?? 0;
                                $totalEvents = $restockCount + $deadlineCount + $customCount + $deliveryCount;
                                $hasEvent = $totalEvents > 0;
                            ?>
                            <div class="h-12 flex flex-col items-center justify-start text-xs border rounded p-0.5
                                <?php echo e($date->month != $month ? 'bg-gray-50 text-gray-400' : 'bg-white text-gray-700'); ?>

                                <?php echo e($date->isToday() ? 'bg-[#c49b6e] text-white font-bold ring-2 ring-[#b08a5c]' : ''); ?>

                                <?php echo e($hasEvent ? 'border-[#c49b6e] border-2' : ''); ?>"
                                title="<?php echo e($hasEvent ? "📦: $restockCount | ⏰: $deadlineCount | ✏️: $customCount | 🚚: $deliveryCount" : ''); ?>">
                                <div class="font-semibold"><?php echo e($date->day); ?></div>
                                <?php if($hasEvent): ?>
                                    <div class="flex flex-wrap gap-0.5 mt-0.5 justify-center w-full">
                                        <?php if($restockCount > 0): ?>
                                            <span class="w-1 h-1 bg-purple-500 rounded-full"></span>
                                        <?php endif; ?>
                                        <?php if($deadlineCount > 0): ?>
                                            <span class="w-1 h-1 bg-orange-500 rounded-full"></span>
                                        <?php endif; ?>
                                        <?php if($customCount > 0): ?>
                                            <span class="w-1 h-1 bg-pink-500 rounded-full"></span>
                                        <?php endif; ?>
                                        <?php if($deliveryCount > 0): ?>
                                            <span class="w-1 h-1 bg-green-500 rounded-full"></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Event Legend -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-4 pt-4 border-t text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                            <span class="text-gray-600">Restock</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                            <span class="text-gray-600">Deadline</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-pink-500 rounded-full"></span>
                            <span class="text-gray-600">Custom</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span class="text-gray-600">Delivery</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="bg-white border rounded-xl shadow-sm">
                    <div class="px-4 py-3 border-b bg-gray-50 font-medium">Recent Raw Materials</div>
                    <div class="p-4 divide-y">
                        <?php $__empty_1 = true; $__currentLoopData = ($recentMaterials ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="py-2 text-sm flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($mat->name); ?></div>
                                    <div class="text-xs text-gray-500">Added <?php echo e($mat->created_at?->diffForHumans()); ?></div>
                                </div>
                                <a href="<?php echo e(route('employee.raw-materials')); ?>" class="text-[#c49b6e] text-xs">Manage</a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-sm text-gray-500">No recent materials</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bg-white border rounded-xl shadow-sm">
                    <div class="px-4 py-3 border-b bg-gray-50 font-medium">Recent Items</div>
                    <div class="p-4 divide-y">
                        <?php $__empty_1 = true; $__currentLoopData = ($recentItems ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="py-2 text-sm flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($it->name); ?></div>
                                    <div class="text-xs text-gray-500">Added <?php echo e($it->created_at?->diffForHumans()); ?></div>
                                </div>
                                <a href="<?php echo e(route('employee.items')); ?>" class="text-[#c49b6e] text-xs">Manage</a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-sm text-gray-500">No recent items</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bg-white border rounded-xl shadow-sm">
                    <div class="px-4 py-3 border-b bg-gray-50 font-medium">Latest Orders</div>
                    <div class="p-4 divide-y">
                        <?php $__empty_1 = true; $__currentLoopData = ($recentOrders ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="py-2 text-sm flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">Order #<?php echo e($ord->id); ?></div>
                                    <div class="text-xs text-gray-500 capitalize"><?php echo e($ord->status); ?> • <?php echo e($ord->created_at?->diffForHumans()); ?></div>
                                </div>
                                <a href="<?php echo e(route('employee.orders.show', $ord->id)); ?>" class="text-[#c49b6e] text-xs">View</a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-sm text-gray-500">No recent orders</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html><?php /**PATH C:\xampp\htdocs\wowc\resources\views/dashboard.blade.php ENDPATH**/ ?>