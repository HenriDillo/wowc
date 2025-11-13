<?php $__env->startSection('page_title', 'Order Management'); ?>

<?php $__env->startSection('content'); ?>

    <div class="space-y-4" x-data="ordersPage()" x-init="init()">
        <h1 class="text-2xl font-semibold text-gray-900">Order Management</h1>

        <?php if(session('success')): ?>
            <div class="mt-4 p-3 rounded border border-green-200 bg-green-50 text-green-800"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mt-4 p-3 rounded border border-red-200 bg-red-50 text-red-700"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="mt-6 space-y-4">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2">
                <?php $type = $activeType; ?>
                <?php $tabs = [
                    '' => 'All Orders',
                    'standard' => 'Standard Orders',
                    'backorder' => 'Back Orders',
                    'custom' => 'Custom Orders',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]; ?>
                <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(url('/employee/orders'.($t ? ('?type='.$t) : ''))); ?>" class="px-4 py-2 rounded-lg border text-sm whitespace-nowrap font-medium transition-all <?php echo e(($type === $t) ? 'bg-[#c49b6e] text-white border-[#c49b6e] shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'); ?>"><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Search and Filters Section -->
            <form method="GET" action="<?php echo e(url('/employee/orders')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <input type="hidden" name="type" value="<?php echo e($activeType); ?>"/>
                
                <div class="space-y-4">
                    <!-- Search Bar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Orders</label>
                        <div class="flex gap-2">
                            <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search by Order ID (e.g., #123), customer name, or email..." class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all"/>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">🔍 Tip: Search by order ID, customer name, or email address</p>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Backorder Status</label>
                            <select name="backorder_status" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all">
                                <option value="">All Statuses</option>
                                <option value="pending_stock" <?php if(request('backorder_status')==='pending_stock'): echo 'selected'; endif; ?>>Pending Stock</option>
                                <option value="in_progress" <?php if(request('backorder_status')==='in_progress'): echo 'selected'; endif; ?>>In Progress</option>
                                <option value="fulfilled" <?php if(request('backorder_status')==='fulfilled'): echo 'selected'; endif; ?>>Fulfilled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                            <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                            <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all" />
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-white font-medium shadow-sm hover:shadow-md transition-all" style="background:#c49b6e;">
                                <span class="flex items-center justify-center gap-2">
                                    🔍 Search
                                </span>
                            </button>
                            <?php if(request('q') || request('from') || request('to') || request('backorder_status')): ?>
                                <a href="<?php echo e(url('/employee/orders?type='.$activeType)); ?>" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-all">
                                    Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    <?php if(request('q') || request('from') || request('to') || request('backorder_status')): ?>
                        <div class="pt-2 border-t border-gray-100">
                            <div class="text-xs text-gray-600 mb-2">Active Filters:</div>
                            <div class="flex flex-wrap gap-2">
                                <?php if(request('q')): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                                        Search: <?php echo e(request('q')); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if(request('from')): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                        From: <?php echo e(\Carbon\Carbon::parse(request('from'))->format('M d, Y')); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if(request('to')): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                        To: <?php echo e(\Carbon\Carbon::parse(request('to'))->format('M d, Y')); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if(request('backorder_status')): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-medium">
                                        BO Status: <?php echo e(ucwords(str_replace('_', ' ', request('backorder_status')))); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="mt-6 bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="hidden md:grid grid-cols-12 text-xs font-medium text-gray-600 px-4 py-3 border-b bg-gray-50">
                <div class="col-span-2">Order ID</div>
                <div class="col-span-2">Customer</div>
                <div class="col-span-1">Type</div>
                <div class="col-span-1">Status</div>
                <div class="col-span-1">Payment</div>
                <div class="col-span-2">Date</div>
                <div class="col-span-1">Total</div>
                <div class="col-span-1 text-right">Action</div>
            </div>
            <div>
                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="grid grid-cols-12 items-center px-4 py-4 border-b text-sm hover:bg-gray-50/50">
                        <div class="col-span-12 md:col-span-2">
                            #<?php echo e($o->id); ?>

                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                            <div class="font-medium text-gray-900 truncate"><?php echo e($o->user->name ?? 'Guest'); ?></div>
                            <div class="text-xs text-gray-500 truncate"><?php echo e($o->user->email ?? ''); ?></div>
                        </div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs border <?php if($o->order_type === 'standard'): ?> border-green-300 bg-green-50 text-green-700 <?php elseif($o->order_type === 'backorder'): ?> border-blue-300 bg-blue-50 text-blue-700 <?php else: ?> border-gray-300 bg-white <?php endif; ?>">
                                <?php if($o->order_type === 'standard'): ?>
                                    Standard
                                <?php elseif($o->order_type === 'backorder'): ?>
                                    Back Order
                                <?php else: ?>
                                    <?php echo e(ucfirst($o->order_type)); ?>

                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                            <?php
                                $statusColor = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'backorder' => 'bg-indigo-100 text-indigo-800',
                                ][$o->status] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs capitalize <?php echo e($statusColor); ?>"><?php echo e($o->status); ?></span>
                        </div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                            <?php
                                $paymentStatus = $o->payment_status ?? 'unpaid';
                                $paymentColor = [
                                    'paid' => 'bg-green-100 text-green-800',
                                    'unpaid' => 'bg-red-100 text-red-800',
                                    'pending_verification' => 'bg-yellow-100 text-yellow-800',
                                ][$paymentStatus] ?? 'bg-gray-100 text-gray-700';
                                $paymentLabel = [
                                    'paid' => 'Paid ✓',
                                    'unpaid' => 'Unpaid',
                                    'pending_verification' => 'Pending',
                                ][$paymentStatus] ?? ucfirst($paymentStatus);
                            ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs <?php echo e($paymentColor); ?>"><?php echo e($paymentLabel); ?></span>
                        </div>
                        <div class="col-span-6 md:col-span-2 mt-2 md:mt-0 text-gray-600"><?php echo e($o->created_at->format('M d, Y')); ?></div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 font-medium">₱<?php echo e(number_format($o->total_amount ?? 0, 2)); ?></div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 text-right">
                            <a href="<?php echo e(route('employee.orders.show', $o->id)); ?>" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">View</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-4 py-10 text-center text-gray-600">No orders found.</div>
                <?php endif; ?>
            </div>
            <div class="px-4 py-3"><?php echo e($orders->links()); ?></div>
        </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        // Scripts removed - all order management done on order-show page
    </script>
    <?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.employee', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\wowc\resources\views/employee/orders.blade.php ENDPATH**/ ?>