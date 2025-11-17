<?php $__env->startSection('page_title', 'Order Management'); ?>

<?php $__env->startSection('content'); ?>

    <div class="space-y-4" x-data="ordersPage()" x-init="init()">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Order Management</h1>
            <div>
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-order' }))" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white font-medium shadow-sm hover:shadow-md transition-all" style="background:#c49b6e;">
                    ➕ Add Order
                </button>
            </div>
        </div>

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
                <?php $type = $activeType ?? ''; ?>
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
                <a href="<?php echo e(route('employee.returns.index')); ?>" class="px-4 py-2 rounded-lg border text-sm whitespace-nowrap font-medium transition-all <?php echo e(request()->routeIs('employee.returns.index') || request()->routeIs('employee.cancellations.index') ? 'bg-[#c49b6e] text-white border-[#c49b6e] shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'); ?>">
                    Requests
                </a>
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
                    <!-- Order Row -->
                    <div class="grid grid-cols-12 items-center px-4 py-4 border-b text-sm hover:bg-gray-50/50 <?php echo e($o->order_type === 'mixed' ? 'bg-purple-50' : ''); ?>">
                        <div class="col-span-12 md:col-span-2">
                            <div class="flex items-center gap-2">
                                <?php echo e($o->order_type === 'mixed' ? '📦' : ''); ?> #<?php echo e($o->id); ?>

                                <?php
                                    $hasPendingCancellation = $o->cancellationRequests
                                        ->where('status', \App\Models\CancellationRequest::STATUS_REQUESTED)
                                        ->isNotEmpty();
                                    $hasActiveReturn = $o->returnRequests
                                        ->whereIn('status', [
                                            \App\Models\ReturnRequest::STATUS_REQUESTED,
                                            \App\Models\ReturnRequest::STATUS_APPROVED,
                                            \App\Models\ReturnRequest::STATUS_IN_TRANSIT,
                                        ])
                                        ->isNotEmpty();
                                ?>
                                <?php if($hasPendingCancellation): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" title="Pending Cancellation Request">
                                        ⚠️ Cancel
                                    </span>
                                <?php endif; ?>
                                <?php if($hasActiveReturn): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" title="Active Return Request">
                                        🔄 Return
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if($o->order_type === 'mixed' && $o->childOrders->isNotEmpty()): ?>
                                <div class="text-xs text-purple-700 mt-1"><?php echo e($o->childOrders->count()); ?> sub-orders</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                            <div class="font-medium text-gray-900 truncate"><?php echo e($o->user->name ?? 'Guest'); ?></div>
                            <div class="text-xs text-gray-500 truncate"><?php echo e($o->user->email ?? ''); ?></div>
                        </div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs border 
                                <?php if($o->order_type === 'standard'): ?> border-green-300 bg-green-50 text-green-700
                                <?php elseif($o->order_type === 'backorder'): ?> border-blue-300 bg-blue-50 text-blue-700
                                <?php elseif($o->order_type === 'mixed'): ?> border-purple-300 bg-purple-50 text-purple-700
                                <?php else: ?> border-gray-300 bg-white 
                                <?php endif; ?>">
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
                                    'partially_paid' => 'bg-blue-100 text-blue-800',
                                    'unpaid' => 'bg-red-100 text-red-800',
                                    'pending_verification' => 'bg-yellow-100 text-yellow-800',
                                ][$paymentStatus] ?? 'bg-gray-100 text-gray-700';
                                $paymentLabel = [
                                    'paid' => 'Fully Paid ✓',
                                    'partially_paid' => 'Partially Paid',
                                    'unpaid' => 'Unpaid',
                                    'pending_verification' => 'Pending Verification',
                                ][$paymentStatus] ?? ucfirst($paymentStatus);
                            ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs <?php echo e($paymentColor); ?>"><?php echo e($paymentLabel); ?></span>
                            <?php if($paymentStatus === 'partially_paid' && ($o->remaining_balance ?? 0) > 0): ?>
                                <div class="text-xs text-blue-700 mt-1 font-semibold">Remaining: ₱<?php echo e(number_format($o->remaining_balance, 2)); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-span-6 md:col-span-2 mt-2 md:mt-0 text-gray-600"><?php echo e($o->created_at->format('M d, Y')); ?></div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 font-medium">₱<?php echo e(number_format($o->total_amount ?? 0, 2)); ?></div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 text-right">
                            <a href="<?php echo e(route('employee.orders.show', $o->id)); ?>" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">View</a>
                        </div>
                    </div>
                    
                    <!-- Sub-Orders Row (if mixed order) -->
                    <?php if($o->order_type === 'mixed' && $o->childOrders->isNotEmpty()): ?>
                        <?php $__currentLoopData = $o->childOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childOrder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="grid grid-cols-12 items-center px-4 py-3 border-b bg-purple-25 text-sm">
                                <div class="col-span-12 md:col-span-2 pl-8 text-gray-600">
                                    └─ Sub-Order #<?php echo e($childOrder->id); ?>

                                </div>
                                <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                                    <div class="text-xs text-gray-600"><?php echo e(ucfirst($childOrder->order_type)); ?> Items</div>
                                </div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs border 
                                        <?php if($childOrder->order_type === 'standard'): ?> border-green-300 bg-green-50 text-green-700
                                        <?php else: ?> border-blue-300 bg-blue-50 text-blue-700
                                        <?php endif; ?>">
                                        <?php echo e(ucfirst($childOrder->order_type)); ?>

                                    </span>
                                </div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                                    <?php
                                        $childStatusColor = $statusColor ?? 'bg-gray-100 text-gray-700';
                                    ?>
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs capitalize <?php echo e($childStatusColor); ?>"><?php echo e($childOrder->status); ?></span>
                                </div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs 
                                        <?php
                                            $childPaymentStatus = $childOrder->payment_status ?? 'unpaid';
                                            echo match($childPaymentStatus) {
                                                'paid' => 'bg-green-100 text-green-800',
                                                'partially_paid' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-red-100 text-red-800'
                                            };
                                        ?>">
                                        <?php echo e(ucfirst($childPaymentStatus)); ?>

                                    </span>
                                </div>
                                <div class="col-span-6 md:col-span-2 mt-2 md:mt-0"></div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 font-medium text-purple-700">₱<?php echo e(number_format($childOrder->total_amount ?? 0, 2)); ?></div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 text-right">
                                    <a href="<?php echo e(route('employee.orders.show', $childOrder->id)); ?>" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-purple-300 text-purple-700 hover:bg-purple-50 text-xs font-medium">View</a>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-4 py-10 text-center text-gray-600">No orders found.</div>
                <?php endif; ?>
            </div>
            <div class="px-4 py-3"><?php echo e($orders->links()); ?></div>
        </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        // Existing order-page scripts kept elsewhere; modal scripts below
    </script>
    <!-- Add Order Modal -->
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'add-order','maxWidth' => '2xl','focusable' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'add-order','maxWidth' => '2xl','focusable' => true]); ?>
        <div x-data="{
            rows: [{item_id: '', quantity: 1}],
            items: <?php echo \Illuminate\Support\Js::from($items ?? [])->toHtml() ?>,
            users: <?php echo \Illuminate\Support\Js::from($users ?? [])->toHtml() ?>,
            errors: <?php echo \Illuminate\Support\Js::from($errors->messages() ?? [])->toHtml() ?>,
            hasErrors: <?php echo \Illuminate\Support\Js::from($errors->any() ?? false)->toHtml() ?>,
            addRow() { this.rows.push({item_id: '', quantity: 1}) },
            removeRow(i) { if (this.rows.length > 1) this.rows.splice(i,1) }
        }" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Create Order</h2>
                <button type="button" class="text-sm text-gray-500" x-on:click="$dispatch('close-modal', 'add-order')">Close</button>
            </div>

            <!-- Error Messages -->
            <div x-show="hasErrors" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200">
                <p class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</p>
                <ul class="text-sm text-red-700 space-y-1">
                    <template x-for="(messages, field) in errors" :key="field">
                        <template x-for="msg in messages" :key="msg">
                            <li>• <span x-text="msg"></span></li>
                        </template>
                    </template>
                </ul>
            </div>

            <form method="POST" action="<?php echo e(route('employee.orders.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Customer <span class="text-red-600">*</span>
                        </label>
                        <select name="user_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">-- Select a customer --</option>
                            <?php $__currentLoopData = $users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?> — <?php echo e($u->email); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Customer selection is required</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select name="payment_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="NONE">None</option>
                            <option value="COD">COD</option>
                            <option value="GCASH">GCash</option>
                            <option value="BANK">Bank</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Shipping Fee</label>
                        <input type="number" step="0.01" name="shipping_fee" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Name</label>
                        <input type="text" name="recipient_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Phone</label>
                        <input type="text" name="recipient_phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <div class="flex items-center gap-2 p-3 rounded-lg bg-blue-50 border border-blue-200">
                    <input type="checkbox" name="paid" value="1" id="paid-checkbox" class="rounded" />
                    <label for="paid-checkbox" class="text-sm font-medium text-gray-700">Mark order as paid (payment received)</label>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-medium">Items <span class="text-gray-500 text-xs">(System will auto-detect backorder if stock insufficient)</span></h3>
                        <button type="button" class="text-sm text-[#c49b6e]" x-on:click.prevent="addRow()">+ Add item</button>
                    </div>

                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-start mb-3 p-2 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="col-span-7">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Item</label>
                                <select :name="`items[${index}][item_id]`" x-model="row.item_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">-- Select item --</option>
                                    <template x-for="it in items" :key="it.id">
                                        <option :value="it.id" :selected="row.item_id == it.id">
                                            <span x-text="it.name + ' (' + (it.stock ?? 0) + ' in stock, ₱' + (it.price ?? 0) + ')'"></span>
                                        </option>
                                    </template>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Requesting more than stock will create a backorder</p>
                            </div>
                            <div class="col-span-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
                                <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="row.quantity" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div class="col-span-2 flex items-end justify-end">
                                <button type="button" x-on:click.prevent="removeRow(index)" class="text-sm text-red-600 hover:text-red-800 font-medium">✕ Remove</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-2 justify-end">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#c49b6e] text-white font-medium">Create Order</button>
                    <button type="button" class="px-4 py-2 rounded-lg border" x-on:click="$dispatch('close-modal', 'add-order')">Cancel</button>
                </div>
            </form>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
    <?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.employee', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\wowc\resources\views/employee/orders.blade.php ENDPATH**/ ?>