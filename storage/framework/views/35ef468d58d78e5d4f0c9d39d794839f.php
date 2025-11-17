<?php
    $hasPendingCancellation = $order->hasPendingCancellationRequest();
    $latestCancellation = $order->getLatestCancellationRequest();
    $canRequestCancellation = $order->canBeCancelled() && !$hasPendingCancellation;
?>

<?php if($canRequestCancellation): ?>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mt-6">
        <h2 class="font-semibold text-gray-900 mb-4">Cancel Order</h2>
        <form action="<?php echo e(route('customer.orders.cancel', $order->id)); ?>" method="POST" x-data="{ submitting: false, showConfirm: false }" @submit="submitting = true">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Cancellation <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="reason" 
                        name="reason" 
                        rows="4" 
                        required
                        minlength="10"
                        maxlength="1000"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all"
                        placeholder="Please provide a detailed reason for cancellation (minimum 10 characters)..."
                    ><?php echo e(old('reason')); ?></textarea>
                    <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <?php if($order->payment_status === 'paid' || $order->payment_status === 'partially_paid'): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-900">Payment Made</p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    <?php if($order->order_type === 'backorder' || $order->order_type === 'custom'): ?>
                                        If your cancellation is approved, you will receive a refund of your down payment (50% of order total). Refunds may take 3-5 business days to process.
                                    <?php else: ?>
                                        If your cancellation is approved, you will receive a full refund. Refunds may take 3-5 business days to process.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="flex items-start gap-2">
                        <input 
                            type="checkbox" 
                            name="confirm" 
                            required
                            class="mt-1 rounded border-gray-300 text-[#c59d5f] focus:ring-[#c59d5f]"
                        >
                        <span class="text-sm text-gray-700">
                            I understand that cancellation is subject to approval and that I may not be eligible for a full refund if production/procurement has already started.
                        </span>
                    </label>
                </div>

                <div>
                    <button 
                        type="submit" 
                        :disabled="submitting"
                        class="w-full px-4 py-2.5 rounded-lg text-white font-medium shadow-sm hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background:#c59d5f;"
                    >
                        <span x-show="!submitting">Submit Cancellation Request</span>
                        <span x-show="submitting">Submitting...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
<?php elseif(!$order->canBeCancelled() && !$hasPendingCancellation): ?>
    <?php
        $denialReason = $order->getCancellationDenialReason();
    ?>
    <?php if($denialReason): ?>
        <div class="bg-white rounded-xl border border-red-100 shadow-sm p-5 mt-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-red-900 mb-2">Cancellation Not Available</h3>
                    <p class="text-sm text-red-800"><?php echo e($denialReason); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if($hasPendingCancellation && $latestCancellation): ?>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mt-6">
        <h2 class="font-semibold text-gray-900 mb-4">Cancellation Request Status</h2>
        <div class="space-y-4">
            <?php
                $statusColor = match($latestCancellation->status) {
                    \App\Models\CancellationRequest::STATUS_REQUESTED => 'bg-yellow-100 text-yellow-800',
                    \App\Models\CancellationRequest::STATUS_APPROVED => 'bg-blue-100 text-blue-800',
                    \App\Models\CancellationRequest::STATUS_REJECTED => 'bg-red-100 text-red-800',
                    \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING => 'bg-indigo-100 text-indigo-800',
                    \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED => 'bg-green-100 text-green-800',
                    \App\Models\CancellationRequest::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
                    \App\Models\CancellationRequest::STATUS_REFUND_FAILED => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800',
                };
            ?>
            
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo e($statusColor); ?>">
                    <?php echo e($latestCancellation->getStatusLabel()); ?>

                </span>
            </div>

            <div>
                <div class="text-sm font-medium text-gray-700">Reason:</div>
                <div class="mt-1 text-sm text-gray-900"><?php echo e($latestCancellation->reason); ?></div>
            </div>

            <?php if($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REJECTED && $latestCancellation->notes): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="text-sm font-medium text-red-900 mb-1">Rejection Reason:</div>
                    <div class="text-sm text-red-800 whitespace-pre-line"><?php echo e($latestCancellation->notes); ?></div>
                </div>
            <?php endif; ?>

            <?php if($latestCancellation->refund_amount): ?>
                <div>
                    <div class="text-sm font-medium text-gray-700">Refund Amount:</div>
                    <div class="mt-1 text-sm text-gray-900 font-semibold">₱<?php echo e(number_format($latestCancellation->refund_amount, 2)); ?></div>
                    <?php if($latestCancellation->refund_method): ?>
                        <div class="mt-1 text-xs text-gray-500">Method: <?php echo e(ucfirst(str_replace('_', ' ', $latestCancellation->refund_method))); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if($latestCancellation->handledBy): ?>
                <div>
                    <div class="text-sm font-medium text-gray-700">Handled By:</div>
                    <div class="mt-1 text-sm text-gray-900"><?php echo e($latestCancellation->handledBy->name); ?></div>
                    <div class="mt-1 text-xs text-gray-500"><?php echo e($latestCancellation->updated_at->format('M d, Y h:i A')); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php /**PATH C:\xampp\htdocs\wowc\resources\views/partials/cancellation-request-form.blade.php ENDPATH**/ ?>