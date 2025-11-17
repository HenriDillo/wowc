

<?php $__env->startSection('page_title', 'Return & Cancellation Requests'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-4" x-data="requestsPage()" x-init="init()">
        <h1 class="text-2xl font-semibold text-gray-900">Return & Cancellation Requests</h1>

        <!-- Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-gray-200">
            <a href="<?php echo e(route('employee.returns.index')); ?>" class="px-4 py-2 rounded-lg border text-sm whitespace-nowrap font-medium transition-all <?php echo e(request()->routeIs('employee.returns.index') ? 'bg-[#c49b6e] text-white border-[#c49b6e] shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'); ?>">
                Return Requests
            </a>
            <a href="<?php echo e(route('employee.cancellations.index')); ?>" class="px-4 py-2 rounded-lg border text-sm whitespace-nowrap font-medium transition-all <?php echo e(request()->routeIs('employee.cancellations.index') ? 'bg-[#c49b6e] text-white border-[#c49b6e] shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'); ?>">
                Cancellation Requests
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="mt-4 p-3 rounded border border-green-200 bg-green-50 text-green-800"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mt-4 p-3 rounded border border-red-200 bg-red-50 text-red-700"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="mt-6 space-y-4">
            <form method="GET" action="<?php echo e(route('employee.cancellations.index')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <div class="space-y-4">
                    <!-- Search Bar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Cancellation Requests</label>
                        <div class="flex gap-2">
                            <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search by Cancellation ID, Order ID, customer name, or email..." class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all"/>
                        </div>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all">
                                <option value="">All Statuses</option>
                                <option value="Cancellation Requested" <?php if(request('status')==='Cancellation Requested'): echo 'selected'; endif; ?>>Cancellation Requested</option>
                                <option value="Cancellation Approved" <?php if(request('status')==='Cancellation Approved'): echo 'selected'; endif; ?>>Cancellation Approved</option>
                                <option value="Cancellation Rejected" <?php if(request('status')==='Cancellation Rejected'): echo 'selected'; endif; ?>>Cancellation Rejected</option>
                                <option value="Refund Processing" <?php if(request('status')==='Refund Processing'): echo 'selected'; endif; ?>>Refund Processing</option>
                                <option value="Refund Completed" <?php if(request('status')==='Refund Completed'): echo 'selected'; endif; ?>>Refund Completed</option>
                                <option value="Cancelled" <?php if(request('status')==='Cancelled'): echo 'selected'; endif; ?>>Cancelled</option>
                                <option value="Refund Failed" <?php if(request('status')==='Refund Failed'): echo 'selected'; endif; ?>>Refund Failed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Order Type</label>
                            <select name="order_type" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all">
                                <option value="">All Types</option>
                                <option value="standard" <?php if(request('order_type')==='standard'): echo 'selected'; endif; ?>>Standard</option>
                                <option value="backorder" <?php if(request('order_type')==='backorder'): echo 'selected'; endif; ?>>Backorder</option>
                                <option value="custom" <?php if(request('order_type')==='custom'): echo 'selected'; endif; ?>>Custom</option>
                                <option value="mixed" <?php if(request('order_type')==='mixed'): echo 'selected'; endif; ?>>Mixed</option>
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
                            <?php if(request('q') || request('from') || request('to') || request('status') || request('order_type')): ?>
                                <a href="<?php echo e(route('employee.cancellations.index')); ?>" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-all">
                                    Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="mt-6 bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="hidden md:grid grid-cols-12 text-xs font-medium text-gray-600 px-4 py-3 border-b bg-gray-50">
                <div class="col-span-1">Cancel ID</div>
                <div class="col-span-1">Order ID</div>
                <div class="col-span-2">Customer</div>
                <div class="col-span-1">Type</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-2">Requested At</div>
                <div class="col-span-2">Refund Amount</div>
                <div class="col-span-1 text-right">Action</div>
            </div>
            <div>
                <?php $__empty_1 = true; $__currentLoopData = $cancellationRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="grid grid-cols-12 items-center px-4 py-4 border-b text-sm hover:bg-gray-50/50">
                        <div class="col-span-12 md:col-span-1">
                            #<?php echo e($cr->id); ?>

                        </div>
                        <div class="col-span-12 md:col-span-1 mt-2 md:mt-0">
                            <a href="<?php echo e(route('employee.orders.show', $cr->order_id)); ?>" class="text-[#c59d5f] hover:underline">#<?php echo e($cr->order_id); ?></a>
                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                            <div class="font-medium text-gray-900 truncate"><?php echo e($cr->user->name ?? 'N/A'); ?></div>
                            <div class="text-xs text-gray-500 truncate"><?php echo e($cr->user->email ?? ''); ?></div>
                        </div>
                        <div class="col-span-12 md:col-span-1 mt-2 md:mt-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 capitalize">
                                <?php echo e($cr->order->order_type ?? 'N/A'); ?>

                            </span>
                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                            <?php
                                $statusColor = match($cr->status) {
                                    'Cancellation Requested' => 'bg-yellow-100 text-yellow-800',
                                    'Cancellation Approved' => 'bg-blue-100 text-blue-800',
                                    'Cancellation Rejected' => 'bg-red-100 text-red-800',
                                    'Refund Processing' => 'bg-indigo-100 text-indigo-800',
                                    'Refund Completed' => 'bg-green-100 text-green-800',
                                    'Cancelled' => 'bg-gray-100 text-gray-800',
                                    'Refund Failed' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs <?php echo e($statusColor); ?>"><?php echo e($cr->getStatusLabel()); ?></span>
                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0 text-gray-600"><?php echo e($cr->created_at->format('M d, Y')); ?></div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                            <?php if($cr->refund_amount): ?>
                                <span class="font-medium">₱<?php echo e(number_format($cr->refund_amount, 2)); ?></span>
                                <?php if($cr->refund_method): ?>
                                    <div class="text-xs text-gray-500"><?php echo e(ucfirst(str_replace('_', ' ', $cr->refund_method))); ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-span-12 md:col-span-1 mt-2 md:mt-0 text-right">
                            <a href="<?php echo e(route('employee.cancellations.show', $cr->id)); ?>" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">View</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-4 py-8 text-center text-gray-500">
                        No cancellation requests found.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <?php echo e($cancellationRequests->links()); ?>

        </div>
    </div>

    <script>
        function requestsPage() {
            return {
                init() {
                    // Initialize any JavaScript functionality if needed
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.employee', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\wowc\resources\views/employee/cancellation-requests.blade.php ENDPATH**/ ?>