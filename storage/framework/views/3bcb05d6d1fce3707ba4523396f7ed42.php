

<?php $__env->startSection('page_title', 'Cancellation Request #'.$cancellationRequest->id); ?>

<?php $__env->startSection('content'); ?>
    <div class="w-full">
        <!-- Back Button and Header -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="<?php echo e(route('employee.cancellations.index')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    ← Back to Cancellation Requests
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Cancellation Request #<?php echo e($cancellationRequest->id); ?></h1>
                    <div class="mt-1 text-sm text-gray-600">Created <?php echo e($cancellationRequest->created_at?->format('M d, Y')); ?> • Order #<?php echo e($cancellationRequest->order_id); ?></div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <?php
                    $statusColor = match($cancellationRequest->status) {
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
                <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-medium <?php echo e($statusColor); ?>">
                    <?php echo e($cancellationRequest->getStatusLabel()); ?>

                </span>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-800"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Cancellation Request Details -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Cancellation Request Details</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Reason</div>
                            <div class="mt-1 text-gray-900 whitespace-pre-line"><?php echo e($cancellationRequest->reason); ?></div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Requested By</div>
                            <div class="mt-1 text-gray-900 capitalize"><?php echo e($cancellationRequest->requested_by); ?></div>
                        </div>

                        <?php if($cancellationRequest->handledBy): ?>
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Handled By</div>
                                <div class="mt-1 text-gray-900"><?php echo e($cancellationRequest->handledBy->name); ?></div>
                                <div class="mt-1 text-sm text-gray-600"><?php echo e($cancellationRequest->updated_at->format('M d, Y h:i A')); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if($cancellationRequest->notes): ?>
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Notes</div>
                                <div class="mt-1 text-gray-900 whitespace-pre-line"><?php echo e($cancellationRequest->notes); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if($cancellationRequest->refund_amount): ?>
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Refund Amount</div>
                                <div class="mt-1 text-gray-900 font-semibold text-lg">₱<?php echo e(number_format($cancellationRequest->refund_amount, 2)); ?></div>
                                <?php if($cancellationRequest->refund_method): ?>
                                    <div class="mt-1 text-sm text-gray-600">Method: <?php echo e(ucfirst(str_replace('_', ' ', $cancellationRequest->refund_method))); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Details</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order ID</div>
                            <a href="<?php echo e(route('employee.orders.show', $cancellationRequest->order_id)); ?>" class="mt-1 text-[#c59d5f] hover:underline">
                                View Order #<?php echo e($cancellationRequest->order_id); ?>

                            </a>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Type</div>
                            <div class="mt-1 text-gray-900 capitalize"><?php echo e($cancellationRequest->order->order_type); ?></div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Status</div>
                            <div class="mt-1">
                                <?php
                                    $orderStatusColor = match($cancellationRequest->order->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'shipped' => 'bg-purple-100 text-purple-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                ?>
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium <?php echo e($orderStatusColor); ?>">
                                    <?php echo e(ucwords(str_replace('_', ' ', $cancellationRequest->order->status))); ?>

                                </span>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Payment Status</div>
                            <div class="mt-1 text-gray-900"><?php echo e($cancellationRequest->order->getPaymentStatusLabel()); ?></div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Items</div>
                            <div class="mt-2 space-y-2">
                                <?php $__currentLoopData = $cancellationRequest->order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center gap-3 p-2 bg-gray-50 rounded">
                                        <?php if($item->item && $item->item->photos->first()): ?>
                                            <img src="<?php echo e($item->item->photos->first()->url); ?>" alt="<?php echo e($item->item->name); ?>" class="w-12 h-12 rounded object-cover">
                                        <?php endif; ?>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900"><?php echo e($item->item->name ?? 'N/A'); ?></div>
                                            <div class="text-sm text-gray-600">Qty: <?php echo e($item->quantity); ?> × ₱<?php echo e(number_format($item->price, 2)); ?></div>
                                            <?php if($item->is_backorder): ?>
                                                <div class="text-xs text-blue-600">Backorder</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="font-medium text-gray-900">₱<?php echo e(number_format($item->subtotal, 2)); ?></div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Total</div>
                            <div class="mt-1 text-gray-900 font-semibold text-lg">₱<?php echo e(number_format($cancellationRequest->order->total_amount, 2)); ?></div>
                        </div>

                        <?php if($cancellationRequest->order->order_type === 'backorder'): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="text-sm font-medium text-blue-900 mb-2">Backorder Status</div>
                                <?php
                                    $hasStartedProcurement = $cancellationRequest->order->items()
                                        ->where('is_backorder', true)
                                        ->whereIn('backorder_status', [\App\Models\OrderItem::BO_IN_PROGRESS, \App\Models\OrderItem::BO_FULFILLED])
                                        ->exists();
                                ?>
                                <?php if($hasStartedProcurement): ?>
                                    <p class="text-sm text-red-800">⚠️ Procurement has already started. Cancellation may not be allowed.</p>
                                <?php else: ?>
                                    <p class="text-sm text-blue-800">✓ Procurement has not started. Cancellation is allowed.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($cancellationRequest->order->order_type === 'custom'): ?>
                            <?php
                                $customOrder = $cancellationRequest->order->customOrders->first();
                            ?>
                            <?php if($customOrder): ?>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="text-sm font-medium text-blue-900 mb-2">Custom Order Status</div>
                                    <?php if(in_array($customOrder->status, [\App\Models\CustomOrder::STATUS_IN_PRODUCTION, \App\Models\CustomOrder::STATUS_COMPLETED])): ?>
                                        <p class="text-sm text-red-800">⚠️ Production has already started. Cancellation may not be allowed.</p>
                                    <?php else: ?>
                                        <p class="text-sm text-blue-800">✓ Production has not started. Cancellation is allowed.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if($cancellationRequest->order->payments->count() > 0): ?>
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Payments</div>
                                <div class="mt-2 space-y-2">
                                    <?php $__currentLoopData = $cancellationRequest->order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="p-2 bg-gray-50 rounded">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">₱<?php echo e(number_format($payment->amount, 2)); ?></div>
                                                    <div class="text-xs text-gray-600"><?php echo e(ucfirst($payment->method)); ?> • <?php echo e(ucfirst($payment->status)); ?></div>
                                                </div>
                                                <?php if($payment->isVerified()): ?>
                                                    <span class="text-xs text-green-600">✓ Verified</span>
                                                <?php elseif($payment->isPendingVerification()): ?>
                                                    <span class="text-xs text-yellow-600">Pending</span>
                                                <?php elseif($payment->isRejected()): ?>
                                                    <span class="text-xs text-red-600">Rejected</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar Actions -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Customer Info -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Customer Information</h2>
                    <div class="space-y-2">
                        <div>
                            <div class="text-xs font-medium text-gray-500">Name</div>
                            <div class="mt-1 text-gray-900"><?php echo e($cancellationRequest->user->name ?? 'N/A'); ?></div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500">Email</div>
                            <div class="mt-1 text-gray-900"><?php echo e($cancellationRequest->user->email ?? 'N/A'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Actions</h2>
                    <div class="space-y-3">
                        <?php if($cancellationRequest->status === \App\Models\CancellationRequest::STATUS_REQUESTED): ?>
                            <form action="<?php echo e(route('employee.cancellations.approve', $cancellationRequest->id)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to approve this cancellation request? This will release inventory and may trigger refund processing.');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
                                    Approve Cancellation
                                </button>
                            </form>
                            <form action="<?php echo e(route('employee.cancellations.reject', $cancellationRequest->id)); ?>" method="POST" x-data="{ showNotes: false }">
                                <?php echo csrf_field(); ?>
                                <div class="space-y-2">
                                    <button type="button" @click="showNotes = !showNotes" class="w-full px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                                        Reject Cancellation
                                    </button>
                                    <div x-show="showNotes" class="space-y-2">
                                        <textarea name="notes" rows="3" placeholder="Rejection reason (optional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20"></textarea>
                                        <button type="submit" onclick="return confirm('Are you sure you want to reject this cancellation request?');" class="w-full px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                                            Confirm Rejection
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if(in_array($cancellationRequest->status, [
                            \App\Models\CancellationRequest::STATUS_APPROVED,
                            \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING,
                            \App\Models\CancellationRequest::STATUS_REFUND_FAILED,
                        ]) && $cancellationRequest->refund_amount > 0): ?>
                            <!-- Refund Form -->
                            <div class="border-t pt-4 mt-4">
                                <h3 class="font-medium text-gray-900 mb-3">Process Refund</h3>
                                <form action="<?php echo e(route('employee.cancellations.refund', $cancellationRequest->id)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to process this refund?');">
                                    <?php echo csrf_field(); ?>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Refund Amount</label>
                                            <?php
                                                $order = $cancellationRequest->order;
                                                // For mixed orders, calculate refund from all child orders
                                                if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
                                                    $maxRefund = 0.0;
                                                    foreach ($order->childOrders as $childOrder) {
                                                        // Sum all verified/paid payments from child orders
                                                        // Include payments that are either paid or verified (approved)
                                                        $childPaidAmount = (float) $childOrder->payments()
                                                            ->where(function($query) {
                                                                $query->where('status', 'paid')
                                                                      ->orWhere('verification_status', 'approved');
                                                            })
                                                            ->sum('amount');
                                                        $maxRefund += $childPaidAmount;
                                                    }
                                                } else {
                                                    // For single orders, get verified/paid payments from the order itself
                                                    $maxRefund = (float) $order->payments()
                                                        ->where(function($query) {
                                                            $query->where('status', 'paid')
                                                                  ->orWhere('verification_status', 'approved');
                                                        })
                                                        ->sum('amount');
                                                }
                                                // Use the refund amount from cancellation request if available, otherwise use calculated max
                                                $defaultRefund = $cancellationRequest->refund_amount ?? $maxRefund;
                                            ?>
                                            <input type="number" name="refund_amount" step="0.01" min="0.01" max="<?php echo e($maxRefund > 0 ? $maxRefund : 999999); ?>" value="<?php echo e($defaultRefund); ?>" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm">
                                            <p class="text-xs text-gray-500 mt-1">Max: ₱<?php echo e(number_format($maxRefund, 2)); ?></p>
                                            <?php if($cancellationRequest->refund_amount && $cancellationRequest->refund_amount > $maxRefund): ?>
                                                <p class="text-xs text-yellow-600 mt-1">⚠️ Calculated refund amount (₱<?php echo e(number_format($cancellationRequest->refund_amount, 2)); ?>) exceeds available paid amount. Please verify.</p>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Refund Method</label>
                                            <select name="refund_method" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm">
                                                <option value="">Select Method</option>
                                                <option value="gcash" <?php if($cancellationRequest->refund_method === 'gcash'): echo 'selected'; endif; ?>>GCash</option>
                                                <option value="bank_transfer" <?php if($cancellationRequest->refund_method === 'bank_transfer'): echo 'selected'; endif; ?>>Bank Transfer</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID (Optional)</label>
                                            <input type="text" name="transaction_id" maxlength="100" placeholder="Enter transaction ID" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                            <textarea name="notes" rows="2" placeholder="Additional notes" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm"></textarea>
                                        </div>
                                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition-colors">
                                            Process Refund
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.employee', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\wowc\resources\views/employee/cancellation-request-show.blade.php ENDPATH**/ ?>