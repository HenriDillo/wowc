<?php $__env->startSection('page_title', 'Order #'.$order->id); ?>

<?php $__env->startSection('content'); ?>

    <div class="w-full">
        <!-- Back Button and Header -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="<?php echo e(route('employee.orders')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    ← Back to Orders
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Order #<?php echo e($order->id); ?></h1>
                    <div class="mt-1 text-sm text-gray-600">Placed <?php echo e($order->created_at?->format('M d, Y')); ?> • <span class="capitalize"><?php echo e($order->order_type); ?></span></div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <?php 
                    $paymentStatus = $order->payment_status ?? 'unpaid';
                    $hasVerifiedPayment = $order->hasVerifiedPayment();
                    $hasPendingVerification = $order->hasPendingPaymentVerification();
                    $isRejected = $order->payment_status === 'payment_rejected' || ($order->getLatestPayment() && $order->getLatestPayment()->isRejected());
                ?>
                <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-medium
                    <?php if($paymentStatus === 'paid' && $hasVerifiedPayment): ?> bg-green-100 text-green-800
                    <?php elseif($paymentStatus === 'partially_paid'): ?> bg-blue-100 text-blue-800
                    <?php elseif($paymentStatus === 'pending_verification' || $hasPendingVerification): ?> bg-yellow-100 text-yellow-800
                    <?php elseif($paymentStatus === 'pending_cod'): ?> bg-blue-100 text-blue-800
                    <?php elseif($paymentStatus === 'payment_rejected' || $isRejected): ?> bg-red-100 text-red-800
                    <?php else: ?> bg-red-100 text-red-800
                    <?php endif; ?>">
                    <?php if($paymentStatus === 'paid' && $hasVerifiedPayment): ?> ✓ Paid — Verified
                    <?php elseif($paymentStatus === 'partially_paid'): ?> 💰 Partially Paid
                    <?php elseif($paymentStatus === 'pending_verification' || $hasPendingVerification): ?> ⏳ Pending Payment Verification
                    <?php elseif($paymentStatus === 'pending_cod'): ?> 💰 Pending COD
                    <?php elseif($paymentStatus === 'payment_rejected' || $isRejected): ?> ✗ Payment Rejected
                    <?php else: ?> ✗ Payment Unpaid
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <!-- Parent-Sub Order Info (if applicable) -->
        <?php if($order->order_type === 'mixed' && $order->childOrders->isNotEmpty()): ?>
            <!-- This is a parent (mixed) order -->
            <div class="mb-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <h3 class="font-semibold text-purple-900">Mixed Order Structure</h3>
                <p class="text-sm text-purple-700 mt-1">This order contains both standard and back order items split into sub-orders below.</p>
                <div class="mt-3 space-y-2">
                    <?php $totalAmount = $order->total_amount; ?>
                    <?php $__currentLoopData = $order->childOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between bg-white px-3 py-2 rounded border border-purple-100">
                            <div class="text-sm">
                                <span class="font-medium text-gray-900">Sub-Order #<?php echo e($child->id); ?></span>
                                <span class="text-gray-600 ml-2">• <?php echo e(ucfirst($child->order_type)); ?> Items</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium 
                                    <?php if($child->order_type === 'standard'): ?> bg-green-100 text-green-800
                                    <?php else: ?> bg-blue-100 text-blue-800
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst($child->status)); ?>

                                </span>
                                <span class="text-sm font-medium text-gray-700">₱<?php echo e(number_format($child->total_amount, 2)); ?></span>
                                <a href="<?php echo e(route('employee.orders.show', $child->id)); ?>" class="text-xs text-purple-700 hover:underline">View</a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-3 pt-3 border-t border-purple-200 flex items-center justify-between">
                    <span class="font-medium text-purple-900">Total Parent Order Amount</span>
                    <span class="text-lg font-semibold text-purple-900">₱<?php echo e(number_format($totalAmount, 2)); ?></span>
                </div>
            </div>
        <?php elseif($order->parent_order_id): ?>
            <!-- This is a child order (sub-order) -->
            <?php $parentOrder = $order->parentOrder; ?>
            <div class="mb-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <h3 class="font-semibold text-purple-900">Part of Mixed Order</h3>
                <p class="text-sm text-purple-700 mt-1">This is a <?php echo e(ucfirst($order->order_type)); ?> sub-order linked to Parent Order #<?php echo e($parentOrder->id); ?>.</p>
                <div class="mt-3">
                    <a href="<?php echo e(route('employee.orders.show', $parentOrder->id)); ?>" class="inline-flex items-center px-3 py-2 rounded-lg border border-purple-300 text-purple-700 hover:bg-purple-100 text-sm font-medium">
                        ← View Parent Order #<?php echo e($parentOrder->id); ?>

                    </a>
                </div>
            </div>
        <?php endif; ?>
        <?php
            $hasVerifiedPayment = $order->hasVerifiedPayment();
            $hasPendingVerification = $order->hasPendingPaymentVerification();
            $isCod = $order->payment_method === 'COD';
            // COD orders can be updated (except completed status if not paid), non-COD orders need verified payment
            $canUpdateStatus = $isCod || $hasVerifiedPayment || $order->payment_status === 'paid';
        ?>
        <?php if(!$canUpdateStatus): ?>
            <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="text-red-700 font-semibold">⚠ Payment Required</div>
                    <div class="text-sm text-red-700 flex-1">
                        <?php if($hasPendingVerification || $paymentStatus === 'pending_verification'): ?>
                            This order is awaiting admin verification of bank transfer proof. Until verified, processing actions are limited.
                        <?php elseif($paymentStatus === 'payment_rejected'): ?>
                            Payment was rejected. Please verify the payment or contact the customer.
                        <?php else: ?>
                            This order has not been paid yet. Customer must complete payment before processing can begin.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Validation Errors Banner -->
        <?php if($errors->any()): ?>
            <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="text-red-700 font-semibold">✕ Validation Errors</div>
                    <div class="text-sm text-red-700 flex-1">
                        <ul class="list-disc list-inside space-y-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Status Update Form (COD orders can always update, non-COD need verified payment) -->
        <?php
            $hasVerifiedPayment = $order->hasVerifiedPayment();
            $isCod = $order->payment_method === 'COD';
            // COD orders can be updated (except completed if not paid), non-COD orders need verified payment
            $canUpdateStatus = $isCod || $hasVerifiedPayment || $order->payment_status === 'paid';
        ?>
        <?php if($canUpdateStatus): ?>
            <form method="POST" action="<?php echo e(route('employee.orders.update', $order->id)); ?>" class="mb-6 flex items-center gap-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <label class="text-sm font-medium text-blue-900">Update Status:</label>
                <select name="status" class="rounded-md border border-gray-300 text-sm px-3 py-2">
                    <?php 
                        // Get valid next statuses (forward-only flow)
                        $validNextStatuses = $order->getValidNextStatuses();
                        // Always include current status
                        $availableStatuses = array_unique(array_merge([$order->status], $validNextStatuses));
                    ?>
                    <?php $__currentLoopData = $availableStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            // Disable "completed" for COD orders if payment hasn't been collected
                            $disabled = ($s === 'completed' && $isCod && $order->payment_status === 'pending_cod');
                            $isCurrentStatus = ($s === $order->status);
                        ?>
                        <option value="<?php echo e($s); ?>" <?php if($isCurrentStatus): echo 'selected'; endif; ?> <?php if($disabled): echo 'disabled'; endif; ?>>
                            <?php echo e(ucwords(str_replace('_',' ',$s))); ?>

                            <?php if($isCurrentStatus): ?> (Current) <?php endif; ?>
                            <?php if($disabled): ?> (Payment Required) <?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="px-4 py-2 rounded-md text-white font-medium" style="background:#c59d5f;">Update</button>
            </form>
        <?php else: ?>
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-sm text-gray-600">
                    <?php if($order->hasPendingPaymentVerification()): ?>
                        Status update available after payment verification is completed.
                    <?php elseif($order->payment_status === 'payment_rejected'): ?>
                        Status update unavailable. Payment was rejected.
                    <?php else: ?>
                        Status update available after payment confirmation.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900">Customer</h2>
                    <div class="mt-3 text-sm text-gray-700">
                        <div class="font-medium"><?php echo e($order->user?->name); ?></div>
                        <div><?php echo e($order->user?->email); ?></div>
                        <div class="mt-2"><?php echo e($order->user?->address?->address_line); ?></div>
                        <div><?php echo e($order->user?->address?->city); ?>, <?php echo e($order->user?->address?->province); ?> <?php echo e($order->user?->address?->postal_code); ?></div>
                        <div><?php echo e($order->user?->address?->phone_number); ?></div>
                    </div>
                </div>

                <?php if($order->customOrders->isNotEmpty()): ?>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Custom Order Details</h2>
                        <p class="mt-1 text-sm text-gray-500">Review customer specifications and set pricing. Saving keeps the status Pending.</p>

                        <div class="mt-5 space-y-6">
                            <?php $__currentLoopData = $order->customOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customOrder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="border border-gray-100 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div class="space-y-3 text-sm text-gray-700">
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Product Name</div>
                                                <div class="mt-1 text-gray-900 font-semibold"><?php echo e($customOrder->custom_name); ?></div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Customer</div>
                                                <div class="mt-1 text-gray-900"><?php echo e($order->user?->name ?? '—'); ?></div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Description</div>
                                                <div class="mt-1 text-gray-900 whitespace-pre-line"><?php echo e($customOrder->description); ?></div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Quantity</div>
                                                <div class="mt-1 text-gray-900"><?php echo e($customOrder->quantity); ?></div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</div>
                                                <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <?php echo e(str_replace('_',' ', ucfirst($customOrder->status))); ?>

                                                </span>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Current Price</div>
                                                    <div class="mt-1 text-gray-900 font-semibold">
                                                        <?php if(!is_null($customOrder->price_estimate)): ?>
                                                            ₱<?php echo e(number_format((float)$customOrder->price_estimate, 2)); ?>

                                                        <?php else: ?>
                                                            —
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Estimated Completion</div>
                                                    <div class="mt-1 text-gray-900">
                                                        <?php echo e(optional($customOrder->estimated_completion_date)->format('M d, Y') ?? '—'); ?>

                                                    </div>
                                                </div>
                                            </div>
                                            <?php if($customOrder->admin_notes): ?>
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Internal Notes</div>
                                                    <div class="mt-1 text-gray-700 whitespace-pre-line"><?php echo e($customOrder->admin_notes); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="space-y-3">
                                            <?php
                                                $images = data_get($customOrder->customization_details, 'images', []);
                                                // Fallback to single image for backward compatibility
                                                if (empty($images) && $customOrder->reference_image_path) {
                                                    $images = [$customOrder->reference_image_path];
                                                }
                                            ?>
                                            <?php if(!empty($images)): ?>
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Reference Images</div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $imagePath): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($imagePath)); ?>" alt="Reference Image <?php echo e($loop->iteration); ?>" class="rounded-lg border border-gray-200 shadow-sm max-h-80 object-contain w-full bg-gray-50">
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if($customOrder->status === \App\Models\CustomOrder::STATUS_PENDING_REVIEW): ?>
                                                <!-- Accept Order Form -->
                                                <form method="POST" action="<?php echo e(route('employee.custom-orders.accept', $customOrder->id)); ?>" class="space-y-4 border-b border-gray-200 pb-4">
                                                    <?php echo csrf_field(); ?>
                                                    <h4 class="text-sm font-semibold text-green-700 mb-2">Accept Order</h4>
                                                    
                                                    <div>
                                                        <label for="accept_price_<?php echo e($customOrder->id); ?>" class="block text-sm font-medium text-gray-700">Price <span class="text-red-500">*</span></label>
                                                        <input type="number" min="0" step="0.01" id="accept_price_<?php echo e($customOrder->id); ?>" name="price_estimate" value="<?php echo e(old('price_estimate', $customOrder->price_estimate)); ?>" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        <?php $__errorArgs = ['price_estimate'];
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
                                                        <label for="accept_completion_date_<?php echo e($customOrder->id); ?>" class="block text-sm font-medium text-gray-700">Expected Completion Date <span class="text-red-500">*</span></label>
                                                        <input type="date" id="accept_completion_date_<?php echo e($customOrder->id); ?>" name="estimated_completion_date" value="<?php echo e(old('estimated_completion_date', optional($customOrder->estimated_completion_date)->format('Y-m-d'))); ?>" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        <?php $__errorArgs = ['estimated_completion_date'];
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
                                                        <label for="accept_admin_notes_<?php echo e($customOrder->id); ?>" class="block text-sm font-medium text-gray-700">Internal Notes (Optional)</label>
                                                        <textarea id="accept_admin_notes_<?php echo e($customOrder->id); ?>" name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]"><?php echo e(old('admin_notes', $customOrder->admin_notes)); ?></textarea>
                                                    </div>

                                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 w-full" style="background:#2f855a;">
                                                        ✓ Accept Order
                                                    </button>
                                                    <p class="text-xs text-gray-500 mt-1">Status will update to "Accepted / Pending Payment"</p>
                                                </form>

                                                <!-- Reject Order Form -->
                                                <form method="POST" action="<?php echo e(route('employee.custom-orders.reject', $customOrder->id)); ?>" class="space-y-4 mt-4">
                                                    <?php echo csrf_field(); ?>
                                                    <h4 class="text-sm font-semibold text-red-700 mb-2">Reject Order</h4>
                                                    
                                                    <div>
                                                        <label for="rejection_note_<?php echo e($customOrder->id); ?>" class="block text-sm font-medium text-gray-700">Rejection Reason <span class="text-red-500">*</span></label>
                                                        <textarea id="rejection_note_<?php echo e($customOrder->id); ?>" name="rejection_note" rows="4" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-red-500 focus:ring-red-500" placeholder="Please explain why this order is being rejected..." required><?php echo e(old('rejection_note', $customOrder->rejection_note)); ?></textarea>
                                                        <p class="mt-1 text-xs text-gray-500">This note will be visible to the customer.</p>
                                                        <?php $__errorArgs = ['rejection_note'];
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

                                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 w-full bg-red-600 hover:bg-red-700">
                                                        ✗ Reject Order
                                                    </button>
                                                    <p class="text-xs text-gray-500 mt-1">Status will update to "Rejected"</p>
                                                </form>
                                            <?php elseif($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED): ?>
                                                <div class="p-4 bg-green-50 border border-green-200 rounded-md">
                                                    <p class="text-sm text-green-800 font-medium">✓ Order Accepted</p>
                                                    <p class="text-xs text-green-700 mt-1">This order has been accepted and is awaiting payment.</p>
                                                </div>
                                            <?php elseif($customOrder->status === \App\Models\CustomOrder::STATUS_REJECTED): ?>
                                                <div class="p-4 bg-red-50 border border-red-200 rounded-md">
                                                    <p class="text-sm text-red-800 font-medium">✗ Order Rejected</p>
                                                    <?php if($customOrder->rejection_note): ?>
                                                        <p class="text-xs text-red-700 mt-2"><strong>Reason:</strong> <?php echo e($customOrder->rejection_note); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <!-- Legacy forms for other statuses -->
                                                <form method="POST" action="<?php echo e(route('employee.custom-orders.update', $customOrder->id)); ?>" class="space-y-4">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <div>
                                                        <label for="price_estimate_<?php echo e($customOrder->id); ?>" class="block text-sm font-medium text-gray-700">Final Price</label>
                                                        <input type="number" min="0" step="0.01" id="price_estimate_<?php echo e($customOrder->id); ?>" name="price_estimate" value="<?php echo e(old('price_estimate', $customOrder->price_estimate)); ?>" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        <?php $__errorArgs = ['price_estimate'];
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
                                                        <label for="admin_notes_<?php echo e($customOrder->id); ?>" class="block text-sm font-medium text-gray-700">Internal Notes</label>
                                                        <textarea id="admin_notes_<?php echo e($customOrder->id); ?>" name="admin_notes" rows="4" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]"><?php echo e(old('admin_notes', $customOrder->admin_notes)); ?></textarea>
                                                        <?php $__errorArgs = ['admin_notes'];
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
                                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 w-full md:w-auto" style="background:#c59d5f;">
                                                        Save Review (Keep Pending)
                                                    </button>
                                                </form>

                                                <form method="POST" action="<?php echo e(route('employee.custom-orders.confirm', $customOrder->id)); ?>" class="space-y-4 border-t border-gray-100 pt-4 mt-4">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <label for="confirm_price_<?php echo e($customOrder->id); ?>" class="block text-sm font-medium text-gray-700">Confirmed Price</label>
                                                            <input type="number" min="0" step="0.01" id="confirm_price_<?php echo e($customOrder->id); ?>" name="price_estimate" value="<?php echo e(old('price_estimate', $customOrder->price_estimate)); ?>" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        </div>
                                                        <?php $__errorArgs = ['price_estimate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                            <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        <div>
                                                            <label for="estimated_completion_date_<?php echo e($customOrder->id); ?>" class="block text-sm font-medium text-gray-700">Estimated Completion Date</label>
                                                            <input type="date" id="estimated_completion_date_<?php echo e($customOrder->id); ?>" name="estimated_completion_date" value="<?php echo e(old('estimated_completion_date', optional($customOrder->estimated_completion_date)->format('Y-m-d'))); ?>" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        </div>
                                                    </div>
                                                    <?php $__errorArgs = ['estimated_completion_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-xs text-red-600"><?php echo e($message); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    <div>
                                                        <label for="confirm_admin_notes_<?php echo e($customOrder->id); ?>" class="block text-sm font-medium text-gray-700">Internal Notes (Optional)</label>
                                                        <textarea id="confirm_admin_notes_<?php echo e($customOrder->id); ?>" name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]"><?php echo e(old('admin_notes', $customOrder->admin_notes)); ?></textarea>
                                                    </div>
                                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 w-full md:w-auto" style="background:#2f855a;">
                                                        Confirm &amp; Start Production
                                                    </button>
                                                    <p class="text-xs text-gray-500">Confirmation sets status to In Progress and updates dashboards.</p>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900">Items</h2>
                    <div class="mt-4 divide-y">
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="py-4 flex items-start gap-4">
                                <img src="<?php echo e($oi->item?->photo_url); ?>" class="w-16 h-16 rounded object-cover bg-gray-100 flex-shrink-0" alt="<?php echo e($oi->item?->name); ?>"/>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo e($oi->item?->name); ?></div>
                                    <div class="text-xs text-gray-500">Qty: <?php echo e($oi->quantity); ?> • ₱<?php echo e(number_format($oi->price, 2)); ?></div>
                                    <div class="mt-1 text-xs">
                                        <?php if(($oi->is_backorder ?? false)): ?>
                                            <span class="inline-flex px-2 py-0.5 rounded text-[12px] bg-blue-100 text-blue-800">Backorder Item</span>
                                            <div class="text-xs text-blue-700 mt-1">
                                                <div class="font-semibold">Status: <?php echo e(str_replace('_', ' ', ucfirst($oi->backorder_status ?? 'pending_stock'))); ?></div>
                                                <?php if($oi->item?->restock_date): ?>
                                                    <div class="text-blue-600">Expected Restock: <?php echo e(\Carbon\Carbon::parse($oi->item->restock_date)->format('M d, Y')); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if($canUpdateStatus): ?>
                                                <?php if($oi->backorder_status === \App\Models\OrderItem::BO_PENDING || !$oi->backorder_status): ?>
                                                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                                                        <button onclick="updateItem(<?php echo e($order->id); ?>, <?php echo e($oi->id); ?>, 'in_progress')" class="px-3 py-1 rounded bg-yellow-100 text-yellow-800 text-xs font-medium hover:bg-yellow-200">→ In Progress</button>
                                                        <button onclick="updateItem(<?php echo e($order->id); ?>, <?php echo e($oi->id); ?>, 'fulfilled')" class="px-3 py-1 rounded bg-green-100 text-green-800 text-xs font-medium hover:bg-green-200">✓ Fulfilled</button>
                                                    </div>
                                                <?php elseif($oi->backorder_status === \App\Models\OrderItem::BO_IN_PROGRESS): ?>
                                                    <div class="mt-2 flex items-center gap-2">
                                                        <button onclick="updateItem(<?php echo e($order->id); ?>, <?php echo e($oi->id); ?>, 'fulfilled')" class="px-3 py-1 rounded bg-green-100 text-green-800 text-xs font-medium hover:bg-green-200">✓ Mark Fulfilled</button>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="inline-flex px-2 py-0.5 rounded text-[12px] bg-green-100 text-green-800">Standard Item</span>
                                            <div class="text-xs text-amber-700 mt-1">In Stock: <strong><?php echo e($oi->item?->stock ?? 0); ?> units</strong></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-right flex-shrink-0">₱<?php echo e(number_format($oi->subtotal, 2)); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <script>
                        async function updateItem(orderId, itemId, status){
                            const token = document.querySelector('meta[name="csrf-token"]').content;
                            const res = await fetch(`/employee/orders/${orderId}/items/${itemId}/backorder`, {
                                method: 'POST',
                                headers: {'Content-Type':'application/json','X-CSRF-TOKEN': token, 'X-Requested-With':'XMLHttpRequest'},
                                body: JSON.stringify({ backorder_status: status })
                            });
                            if(res.ok){
                                location.reload();
                            } else {
                                const d = await res.json().catch(()=>({}));
                                alert(d.message || 'Failed to update backorder status');
                            }
                        }
                    </script>
                </div>

                <!-- Tracking & Shipping Section (for Standard & Back Orders when ready to ship) -->
                <?php if($order->order_type !== 'custom' && in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])): ?>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Shipping & Tracking</h2>
                        <form method="POST" action="<?php echo e(route('employee.orders.update', $order->id)); ?>" id="shippingForm" class="mt-4 space-y-4">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <input type="hidden" name="status" id="statusInput" value="<?php echo e($order->status); ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number</label>
                                    <input type="text" name="tracking_number" value="<?php echo e(old('tracking_number', $order->tracking_number ?? '')); ?>" placeholder="e.g., TRK123456789" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]">
                                    <?php $__errorArgs = ['tracking_number'];
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Carrier</label>
                                    <div class="w-full rounded-md border border-gray-300 px-3 py-2 bg-gray-50 text-gray-700">
                                        LBC (Automatically Set)
                                    </div>
                                    <input type="hidden" name="carrier" value="lbc">
                                </div>
                            </div>
                            <?php if($order->status === 'ready_to_ship'): ?>
                                <button type="button" onclick="submitWithStatus('shipped')" class="px-4 py-2 rounded-md text-white font-medium bg-blue-600 hover:bg-blue-700">Mark as Shipped</button>
                            <?php elseif($order->status === 'shipped' && !$order->delivered_at): ?>
                                <button type="button" onclick="submitWithStatus('delivered')" class="px-4 py-2 rounded-md text-white font-medium bg-green-600 hover:bg-green-700">Mark as Delivered</button>
                            <?php else: ?>
                                <button type="submit" class="px-4 py-2 rounded-md text-white font-medium bg-gray-600 hover:bg-gray-700">Save Changes</button>
                            <?php endif; ?>
                        </form>
                        <script>
                            function submitWithStatus(status) {
                                document.getElementById('statusInput').value = status;
                                document.getElementById('shippingForm').submit();
                            }
                        </script>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900">Employee Notes</h2>
                    <div class="mt-3">
                        <textarea class="w-full rounded-md border border-gray-300 px-3 py-2" rows="4" placeholder="Add internal remarks..." disabled></textarea>
                        <p class="mt-2 text-xs text-gray-500">Notes persistence not implemented yet.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Payment Verification Card -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Payment Verification</h2>
                    <?php
                        $latestPayment = $order->getLatestPayment();
                        $isRejected = $latestPayment && $latestPayment->isRejected();
                        
                        // Calculate required amount
                        $requiredAmount = 0;
                        if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
                            $requiredAmount = $order->calculateRequiredPaymentForMixedOrder();
                        } else {
                            $requiredAmount = $order->required_payment_amount ?? $order->calculateRequiredPaymentAmount();
                        }
                    ?>
                    
                    <div class="space-y-4">
                        <!-- Payment Method & Status -->
                        <div class="space-y-3 text-sm text-gray-700">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Method:</span>
                                <span class="font-medium">
                                    <?php if($order->payment_method === 'gcash' || ($latestPayment && $latestPayment->method === 'gcash')): ?>
                                        GCash
                                    <?php elseif($order->payment_method === 'bank' || ($latestPayment && $latestPayment->method === 'bank')): ?>
                                        Bank Transfer
                                    <?php elseif($order->payment_method === 'COD'): ?>
                                        COD
                                    <?php else: ?>
                                        <?php echo e(ucfirst($order->payment_method ?? 'N/A')); ?>

                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium inline-flex px-2 py-0.5 rounded-full text-xs
                                    <?php if($paymentStatus === 'paid' && $hasVerifiedPayment): ?> bg-green-100 text-green-800
                                    <?php elseif($paymentStatus === 'partially_paid'): ?> bg-blue-100 text-blue-800
                                    <?php elseif($paymentStatus === 'pending_verification' || $hasPendingVerification): ?> bg-yellow-100 text-yellow-800
                                    <?php elseif($paymentStatus === 'payment_rejected' || $isRejected): ?> bg-red-100 text-red-800
                                    <?php else: ?> bg-gray-100 text-gray-800
                                    <?php endif; ?>">
                                    <?php if($paymentStatus === 'paid' && $hasVerifiedPayment): ?> ✓ Paid — Verified
                                    <?php elseif($paymentStatus === 'partially_paid'): ?> 💰 Partially Paid
                                    <?php elseif($paymentStatus === 'pending_verification' || $hasPendingVerification): ?> ⏳ Pending Payment Verification
                                    <?php elseif($paymentStatus === 'pending_cod'): ?> 💰 Pending COD
                                    <?php elseif($paymentStatus === 'payment_rejected' || $isRejected): ?> ✗ Payment Rejected
                                    <?php else: ?> ✗ Unpaid
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <?php if($latestPayment): ?>
                            <!-- Payment Details -->
                            <div class="border-t border-gray-200 pt-3 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">Required Amount:</span>
                                    <span class="font-semibold text-gray-900">₱<?php echo e(number_format($requiredAmount, 2)); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">Customer Paid Amount:</span>
                                    <span class="font-semibold <?php if(abs($latestPayment->amount - $requiredAmount) < 0.01): ?> text-green-700 <?php else: ?> text-red-700 <?php endif; ?>">
                                        ₱<?php echo e(number_format($latestPayment->amount, 2)); ?>

                                    </span>
                                </div>
                                <?php if($latestPayment->transaction_id): ?>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Reference Number:</span>
                                        <span class="font-medium"><?php echo e($latestPayment->transaction_id); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($latestPayment->proof_image): ?>
                                    <div>
                                        <span class="text-gray-600 block mb-2">Proof of Payment:</span>
                                        <a href="<?php echo e(Storage::url($latestPayment->proof_image)); ?>" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View/Download Proof
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($latestPayment->verifier): ?>
                                    <div class="border-t border-gray-200 pt-3">
                                        <div class="text-xs text-gray-500">
                                            Verified by: <span class="font-medium text-gray-700"><?php echo e($latestPayment->verifier->name ?? 'N/A'); ?></span>
                                            <?php if($latestPayment->verified_at): ?>
                                                on <?php echo e($latestPayment->verified_at->format('M d, Y g:i A')); ?>

                                            <?php endif; ?>
                                        </div>
                                        <?php if($latestPayment->verification_notes): ?>
                                            <div class="mt-2 text-xs">
                                                <span class="text-gray-500">Notes:</span>
                                                <p class="text-gray-700 mt-1"><?php echo e($latestPayment->verification_notes); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- COD Details -->
                        <?php
                            $isCod = $order->payment_method === 'COD';
                        ?>
                        <?php if($isCod): ?>
                            <div class="border-t border-gray-200 pt-3 space-y-3">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <p class="text-sm font-medium text-blue-900 mb-2">📦 COD Order Details</p>
                                    <?php if($order->recipient_name): ?>
                                        <div class="text-xs text-blue-800 mb-1">
                                            <span class="font-medium">Recipient:</span> <?php echo e($order->recipient_name); ?>

                                        </div>
                                    <?php endif; ?>
                                    <?php if($order->recipient_phone): ?>
                                        <div class="text-xs text-blue-800 mb-1">
                                            <span class="font-medium">Contact:</span> <?php echo e($order->recipient_phone); ?>

                                        </div>
                                    <?php endif; ?>
                                    <div class="text-xs text-blue-800 mb-1">
                                        <span class="font-medium">Shipping Address:</span> <?php echo e($order->user->address->address_line ?? 'N/A'); ?>, <?php echo e($order->user->address->city ?? ''); ?>, <?php echo e($order->user->address->province ?? ''); ?>

                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Order Amount:</span>
                                        <span class="font-medium">₱<?php echo e(number_format($order->total_amount - ($order->shipping_fee ?? 0) - ($order->cod_fee ?? 0), 2)); ?></span>
                                    </div>
                                    <?php if($order->shipping_fee > 0): ?>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Shipping Fee (LBC):</span>
                                            <span class="font-medium">₱<?php echo e(number_format($order->shipping_fee, 2)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if($order->cod_fee > 0): ?>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">COD Fee:</span>
                                            <span class="font-medium">₱<?php echo e(number_format($order->cod_fee, 2)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex justify-between text-sm font-semibold pt-2 border-t">
                                        <span class="text-gray-900">Total COD Amount:</span>
                                        <span class="text-blue-900">₱<?php echo e(number_format($order->total_amount, 2)); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Verification Actions -->
                        <?php if($hasPendingVerification): ?>
                            <div class="border-t border-gray-200 pt-4">
                                <form method="POST" action="<?php echo e(route('employee.orders.verify-payment', $order->id)); ?>" id="verifyPaymentForm" class="space-y-4">
                                    <?php echo csrf_field(); ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Verification Action:</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="radio" name="action" value="approve" class="mr-2" required>
                                                <span class="text-sm text-green-700 font-medium">✓ Approve Payment</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="action" value="reject" class="mr-2" required>
                                                <span class="text-sm text-red-700 font-medium">✗ Reject Payment</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="rejectionNotes" class="hidden">
                                        <label for="verification_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                            Rejection Reason <span class="text-red-500">*</span>
                                        </label>
                                        <textarea 
                                            id="verification_notes" 
                                            name="verification_notes" 
                                            rows="3" 
                                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-red-500 focus:ring-red-500"
                                            placeholder="Please explain why the payment is being rejected..."
                                        ></textarea>
                                        <p class="mt-1 text-xs text-gray-500">This note will be visible to the customer.</p>
                                    </div>
                                    <button type="submit" class="w-full px-4 py-2 rounded-md text-white font-medium hover:opacity-95 transition-opacity" style="background:#c59d5f;">
                                        Verify Payment
                                    </button>
                                </form>
                            </div>
                        <?php elseif($hasVerifiedPayment): ?>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="text-sm text-green-800 font-medium">✓ Payment Verified</p>
                                    <p class="text-xs text-green-700 mt-1">Payment has been verified and approved. Order can proceed to processing.</p>
                                </div>
                            </div>
                        <?php elseif($paymentStatus === 'pending_cod'): ?>
                            <div class="border-t border-gray-200 pt-4">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                    <p class="text-sm text-blue-800 font-medium">💰 COD Payment Pending</p>
                                    <p class="text-xs text-blue-700 mt-1">This order can proceed to processing. Payment will be collected upon delivery via LBC. Mark as collected once payment is received to complete the order.</p>
                                </div>
                                <form method="POST" action="<?php echo e(route('employee.orders.collect-cod', $order->id)); ?>" onsubmit="return confirm('Mark this COD order as collected? This will update the payment status to paid.');">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="w-full px-4 py-2 rounded-md text-white font-medium hover:opacity-95 transition-opacity bg-green-600 hover:bg-green-700">
                                        ✓ Mark COD as Collected
                                    </button>
                                </form>
                            </div>
                        <?php elseif($isRejected): ?>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                    <p class="text-sm text-red-800 font-medium">✗ Payment Rejected</p>
                                    <?php if($latestPayment->verification_notes): ?>
                                        <p class="text-xs text-red-700 mt-1"><strong>Reason:</strong> <?php echo e($latestPayment->verification_notes); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif(!$latestPayment): ?>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <p class="text-sm text-gray-700">No payment has been submitted yet.</p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Payment Summary -->
                        <?php if($paymentStatus === 'partially_paid'): ?>
                            <div class="border-t border-gray-200 pt-3 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Order Amount:</span>
                                    <span class="font-medium">₱<?php echo e(number_format($order->total_amount, 2)); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Amount Paid (50%):</span>
                                    <span class="font-medium text-green-700">₱<?php echo e(number_format($order->required_payment_amount ?? ($order->total_amount * 0.5), 2)); ?></span>
                                </div>
                                <div class="flex justify-between font-semibold bg-blue-50 p-2 rounded border border-blue-200">
                                    <span class="text-blue-900">Remaining Balance (50%):</span>
                                    <span class="text-blue-900">₱<?php echo e(number_format($order->remaining_balance ?? ($order->total_amount * 0.5), 2)); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="border-t border-gray-200 pt-3 flex justify-between font-semibold">
                                <span>Total:</span>
                                <span>₱<?php echo e(number_format($order->total_amount, 2)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Delivery & Shipping Card -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Delivery Information</h2>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div>
                            <div class="text-gray-600 mb-1">Shipping Method</div>
                            <div class="font-medium">Standard Delivery</div>
                        </div>
                        <div>
                            <div class="text-gray-600 mb-1">Carrier</div>
                            <div class="font-medium">LBC</div>
                        </div>
                        <?php if($order->tracking_number): ?>
                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                <div class="text-gray-600 text-xs mb-1">Tracking Number</div>
                                <div class="font-mono font-bold text-blue-900"><?php echo e($order->tracking_number); ?></div>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="text-gray-600 mb-1">Estimated Delivery</div>
                            <div class="font-medium"><?php echo e(now()->addDays(3)->format('M d, Y')); ?></div>
                        </div>
                        <?php if($order->delivered_at): ?>
                            <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                                <div class="text-gray-600 text-xs mb-1">Delivered On</div>
                                <div class="font-medium text-green-900"><?php echo e(\Carbon\Carbon::parse($order->delivered_at)->format('M d, Y \a\t g:i A')); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Status Timeline -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Status Timeline</h2>
                    <div class="space-y-3 text-sm">
                        <?php
                            $statusFlow = match($order->order_type) {
                                'standard' => [
                                    'pending' => ['label' => 'Order Placed', 'icon' => '📋', 'done' => true],
                                    'processing' => ['label' => 'Processing', 'icon' => '⚙️', 'done' => in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'ready_to_ship' => ['label' => 'Ready to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
                                    'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
                                ],
                                'backorder' => [
                                    'pending' => ['label' => 'Order Placed', 'icon' => '📋', 'done' => true],
                                    'processing' => ['label' => 'Awaiting Stock', 'icon' => '⏳', 'done' => in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'ready_to_ship' => ['label' => 'Preparing to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
                                    'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
                                ],
                                'custom' => [
                                    'pending' => ['label' => 'Awaiting Price', 'icon' => '💰', 'done' => true],
                                    'in_design' => ['label' => 'In Design', 'icon' => '🎨', 'done' => in_array($order->status, ['in_design', 'in_production', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'in_production' => ['label' => 'In Production', 'icon' => '⚙️', 'done' => in_array($order->status, ['in_production', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'ready_to_ship' => ['label' => 'Ready to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
                                    'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
                                ],
                                default => []
                            };
                        ?>
                        <?php $__currentLoopData = $statusFlow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-start gap-3">
                                <div class="text-lg leading-none pt-0.5"><?php echo e($info['icon']); ?></div>
                                <div class="flex-1">
                                    <div class="text-xs font-medium <?php echo e($info['done'] ? 'text-green-700' : 'text-gray-500'); ?>">
                                        <?php echo e($info['label']); ?>

                                    </div>
                                </div>
                                <?php if($info['done']): ?>
                                    <span class="text-green-600 text-xs font-bold">✓</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle rejection notes field based on action selection
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('verifyPaymentForm');
            if (form) {
                const actionRadios = form.querySelectorAll('input[name="action"]');
                const rejectionNotes = document.getElementById('rejectionNotes');
                const notesTextarea = document.getElementById('verification_notes');
                
                actionRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'reject') {
                            rejectionNotes.classList.remove('hidden');
                            notesTextarea.setAttribute('required', 'required');
                        } else {
                            rejectionNotes.classList.add('hidden');
                            notesTextarea.removeAttribute('required');
                            notesTextarea.value = '';
                        }
                    });
                });
            }
        });
    </script>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.employee', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\wowc\resources\views/employee/order-show.blade.php ENDPATH**/ ?>