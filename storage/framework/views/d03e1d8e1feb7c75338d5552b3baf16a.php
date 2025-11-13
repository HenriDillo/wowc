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
                    $canProcess = $paymentStatus === 'paid';
                ?>
                <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-medium
                    <?php if($paymentStatus === 'paid'): ?> bg-green-100 text-green-800
                    <?php elseif($paymentStatus === 'pending_verification'): ?> bg-yellow-100 text-yellow-800
                    <?php else: ?> bg-red-100 text-red-800
                    <?php endif; ?>">
                    <?php if($paymentStatus === 'paid'): ?> ✓ Payment Confirmed
                    <?php elseif($paymentStatus === 'pending_verification'): ?> ⏳ Payment Pending Verification
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
        <?php if(!$canProcess): ?>
            <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="text-red-700 font-semibold">⚠ Payment Required</div>
                    <div class="text-sm text-red-700 flex-1">
                        <?php if($paymentStatus === 'pending_verification'): ?>
                            This order is awaiting admin verification of bank transfer proof. Until verified, processing actions are limited.
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

        <!-- Status Update Form (only if paid) -->
        <?php if($canProcess): ?>
            <form method="POST" action="<?php echo e(route('employee.orders.update', $order->id)); ?>" class="mb-6 flex items-center gap-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <label class="text-sm font-medium text-blue-900">Update Status:</label>
                <select name="status" class="rounded-md border border-gray-300 text-sm px-3 py-2">
                    <?php 
                        $statuses = match($order->order_type) {
                            'standard' => ['pending', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled'],
                            'backorder' => ['pending', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled'],
                            'custom' => ['pending', 'in_design', 'in_production', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled'],
                            default => ['pending', 'processing', 'completed', 'cancelled']
                        };
                    ?>
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php if($order->status===$s): echo 'selected'; endif; ?>><?php echo e(ucwords(str_replace('_',' ',$s))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="px-4 py-2 rounded-md text-white font-medium" style="background:#c59d5f;">Update</button>
            </form>
        <?php else: ?>
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-sm text-gray-600">Status update available after payment confirmation.</p>
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
                                            <?php if($canProcess): ?>
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
                                    <select name="carrier" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]">
                                        <option value="">Select Carrier</option>
                                        <option value="lalamove" <?php if(($order->carrier ?? '') === 'lalamove'): echo 'selected'; endif; ?>>Lalamove</option>
                                        <option value="jnt" <?php if(($order->carrier ?? '') === 'jnt'): echo 'selected'; endif; ?>>J&T Express</option>
                                        <option value="ninjavan" <?php if(($order->carrier ?? '') === 'ninjavan'): echo 'selected'; endif; ?>>Ninja Van</option>
                                        <option value="2go" <?php if(($order->carrier ?? '') === '2go'): echo 'selected'; endif; ?>>2GO</option>
                                        <option value="pickup" <?php if(($order->carrier ?? '') === 'pickup'): echo 'selected'; endif; ?>>Customer Pickup</option>
                                    </select>
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
                <!-- Payment Card -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Payment Details</h2>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Method:</span>
                            <span class="font-medium">
                                <?php if($order->payment_method === 'gcash'): ?>
                                    GCash
                                <?php elseif($order->payment_method === 'bank'): ?>
                                    Bank Transfer
                                <?php else: ?>
                                    <?php echo e(ucfirst($order->payment_method ?? 'N/A')); ?>

                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium inline-flex px-2 py-0.5 rounded-full text-xs
                                <?php if($paymentStatus === 'paid'): ?> bg-green-100 text-green-800
                                <?php elseif($paymentStatus === 'partially_paid'): ?> bg-blue-100 text-blue-800
                                <?php elseif($paymentStatus === 'pending_verification'): ?> bg-yellow-100 text-yellow-800
                                <?php else: ?> bg-red-100 text-red-800
                                <?php endif; ?>">
                                <?php if($paymentStatus === 'paid'): ?> ✓ Fully Paid
                                <?php elseif($paymentStatus === 'partially_paid'): ?> 💰 Partially Paid
                                <?php elseif($paymentStatus === 'pending_verification'): ?> ⏳ Pending Verification
                                <?php else: ?> ✗ Unpaid
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <?php if($paymentStatus === 'partially_paid'): ?>
                            <div class="border-t border-gray-200 pt-2 space-y-2">
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
                            <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold">
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
                        <?php if($order->carrier): ?>
                            <div>
                                <div class="text-gray-600 mb-1">Carrier</div>
                                <div class="font-medium capitalize"><?php echo e(str_replace('_', ' ', $order->carrier)); ?></div>
                            </div>
                        <?php endif; ?>
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

<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.employee', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\wowc\resources\views/employee/order-show.blade.php ENDPATH**/ ?>