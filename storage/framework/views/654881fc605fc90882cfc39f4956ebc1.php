<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>WOW Carmen - Checkout</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
	<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false, method:'Bank' }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family:'Poppins','Inter',ui-sans-serif,system-ui;">

	<?php echo $__env->make('partials.customer-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

	<section class="pt-24 pb-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="<?php echo e(route('cart')); ?>" class="text-sm text-[#c59d5f] hover:underline">Back to cart</a>

			<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-10">
				<!-- Left: Account & Shipping -->
                <div>
                    <form method="POST" action="<?php echo e(route('checkout.store')); ?>" class="bg-white border border-gray-100 rounded-xl shadow-sm p-6">
						<?php echo csrf_field(); ?>
						<h2 class="text-lg font-semibold text-gray-900">Account</h2>
                        <input type="email" value="<?php echo e($user->email ?? ''); ?>" disabled class="mt-3 w-full rounded-md border-gray-300"/>
                        <?php if(isset($payOrder) && $payOrder): ?>
							<p class="mt-2 text-sm text-gray-700">Pay for Custom Order <span class="font-medium">#<?php echo e($payOrder->id); ?></span>. Choose a payment method below to complete your purchase.</p>
                        <?php endif; ?>

                        <?php if($errors->any()): ?>
                            <div class="mt-4 text-sm text-red-600">
                                <ul class="list-disc ml-5 space-y-1">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>

						<h3 class="mt-6 text-lg font-semibold text-gray-900">Shipping Information</h3>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <?php $addr = optional($user?->address); ?>
                            <input name="first_name" value="<?php echo e(old('first_name', $user->first_name ?? '')); ?>" placeholder="First Name" class="rounded-md border-gray-300" required>
                            <input name="last_name" value="<?php echo e(old('last_name', $user->last_name ?? '')); ?>" placeholder="Last Name" class="rounded-md border-gray-300" required>
                            <input name="address_line" value="<?php echo e(old('address_line', $addr->address_line ?? '')); ?>" placeholder="Address" class="sm:col-span-2 rounded-md border-gray-300" required>
                            <input name="city" value="<?php echo e(old('city', $addr->city ?? '')); ?>" placeholder="City" class="rounded-md border-gray-300" required>
                            <input name="postal_code" value="<?php echo e(old('postal_code', $addr->postal_code ?? '')); ?>" placeholder="Postal Code" class="rounded-md border-gray-300" required>
                            <input name="province" value="<?php echo e(old('province', $addr->province ?? '')); ?>" placeholder="Province" class="rounded-md border-gray-300" required>
                            <input name="phone_number" value="<?php echo e(old('phone_number', $addr->phone_number ?? '')); ?>" placeholder="Phone Number" class="sm:col-span-2 rounded-md border-gray-300" required>
                        </div>

						<h3 class="mt-6 text-lg font-semibold text-gray-900">Payment</h3>
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                            <p class="text-sm text-blue-800"><strong>Important:</strong> Payment is required to complete your order. Please select a payment method below.</p>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-3">
							<label class="flex items-center justify-center gap-2 border rounded-md p-3 cursor-pointer hover:border-gray-400">
                                <input type="radio" name="payment_method" value="Bank" x-model="method" class="hidden">
								<span class="text-sm">Bank Transfer</span>
							</label>
							<label class="flex items-center justify-center gap-2 border rounded-md p-3 cursor-pointer hover:border-gray-400">
								<input type="radio" name="payment_method" value="GCash" x-model="method" class="hidden">
								<img src="/images/gcash.png" alt="GCash" class="h-5">
								<span class="text-sm">GCash</span>
							</label>
						</div>

						<div class="mt-6 text-right">
							<button id="completeBtn" type="button" class="inline-flex items-center justify-center px-6 py-3 rounded-md text-white font-medium shadow-sm hover:shadow transition" style="background:#c59d5f;">Complete Order</button>
						</div>
					</form>
				</div>

				<!-- Right: Summary -->
				<div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6">
					<h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                        <?php
							$paymentOnly = isset($payOrder) && $payOrder;
                            // Use the cart line's is_backorder flag to separate standard vs backorder
                            $standardItems = $paymentOnly ? collect() : $cartItems->filter(fn($ci) => !($ci->is_backorder ?? false));
                            $backOrderItems = $paymentOnly ? collect() : $cartItems->filter(fn($ci) => ($ci->is_backorder ?? false));
                            $standardTotal = $paymentOnly ? 0 : $standardItems->sum('subtotal');
                            $backOrderTotal = $paymentOnly ? 0 : $backOrderItems->sum('subtotal');
                        ?>

                        <!-- Standard Items -->
                        <?php if(!$paymentOnly && $standardItems->isNotEmpty()): ?>
                            <div class="mt-4">
                                <h3 class="text-sm font-medium text-gray-900">Standard Items</h3>
                                <div class="mt-3 space-y-4">
                                    <?php $__currentLoopData = $standardItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                    <img src="<?php echo e(optional($ci->item->photos->first())->url); ?>" alt="" class="w-full h-full object-cover"/>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900"><?php echo e($ci->item->name); ?></p>
                                                    <p class="text-xs text-gray-500">Qty: <?php echo e($ci->quantity); ?></p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm text-gray-900">₱<?php echo e(number_format($ci->subtotal, 2)); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php if($backOrderItems->isNotEmpty()): ?>
                                    <p class="mt-2 text-sm text-gray-700">Standard items subtotal: ₱<?php echo e(number_format($standardTotal, 2)); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Back Order Items -->
                        <?php if(!$paymentOnly && $backOrderItems->isNotEmpty()): ?>
                            <div class="mt-6">
                                <div class="mb-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                                    <h3 class="text-sm font-medium text-blue-800">Back Order Items</h3>
                                    <p class="mt-1 text-xs text-blue-700">These items will be shipped separately once they're back in stock.</p>
                                </div>
                                <div class="space-y-4">
                                    <?php $__currentLoopData = $backOrderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                    <img src="<?php echo e(optional($ci->item->photos->first())->url); ?>" alt="" class="w-full h-full object-cover"/>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900"><?php echo e($ci->item->name); ?></p>
                                                    <p class="text-xs text-gray-500">Qty: <?php echo e($ci->quantity); ?></p>
                                                    <span class="inline-flex mt-1 px-2 py-0.5 text-[11px] rounded bg-blue-100 text-blue-800">Back-Order</span>
                                                    <?php if($ci->item->restock_date): ?>
                                                        <span class="block text-[11px] text-blue-700">Ships after <?php echo e($ci->item->restock_date->format('M d, Y')); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm text-gray-900">₱<?php echo e(number_format($ci->subtotal, 2)); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php if($standardItems->isNotEmpty()): ?>
                                    <p class="mt-2 text-sm text-gray-700">Back order items subtotal: ₱<?php echo e(number_format($backOrderTotal, 2)); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        </div>

                    <div class="mt-6 border-t pt-4 space-y-2 text-sm">
						<?php if($paymentOnly): ?>
							<?php
								$coStatus = optional($payOrder->customOrders->first())->status ?? $payOrder->status;
								$badge = match($coStatus){
									'in_production' => 'bg-blue-100 text-blue-800',
									'pending_review' => 'bg-yellow-100 text-yellow-800',
									'approved' => 'bg-green-100 text-green-800',
									'rejected' => 'bg-red-100 text-red-800',
									'completed' => 'bg-gray-100 text-gray-800',
									default => 'bg-gray-100 text-gray-800',
								};
								$coStatusLabel = ucfirst(str_replace('_',' ', $coStatus ?? 'pending'));
							?>
							<div class="mb-3 p-3 border border-gray-200 rounded-lg">
								<div class="text-sm font-medium text-gray-900">Custom Order #<?php echo e($payOrder->id); ?></div>
								<div class="mt-1 text-xs text-gray-600 flex items-center gap-2">
									<span>Status:</span>
									<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium <?php echo e($badge); ?>"><?php echo e($coStatusLabel); ?></span>
								</div>
							</div>
						<?php endif; ?>
						<div class="flex items-center justify-between"><span class="text-gray-600">Subtotal</span><span>₱<?php echo e(number_format($total, 2)); ?></span></div>
						<?php if(!$paymentOnly): ?>
							<div class="flex items-center justify-between"><span class="text-gray-600">Shipping</span><span>₱<?php echo e(number_format($shipping, 2)); ?></span></div>
						<?php endif; ?>
						<div class="flex items-center justify-between font-semibold text-gray-900"><span>Total</span><span>₱<?php echo e(number_format($total, 2)); ?></span></div>
						<p class="text-xs text-gray-500 mt-2">Tax and shipping cost will be calculated later.</p>
                        <?php if(!$paymentOnly && $cartItems->contains(fn($ci) => ($ci->is_backorder ?? false))): ?>
                            <p class="text-xs text-blue-700 mt-1">This item is on back order. We'll ship it once restocked.</p>
                        <?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- GCash Modal -->
	<div id="gcashModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
		<div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
			<div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
				<h2 class="font-semibold">GCash Payment</h2>
				<button onclick="closeModal('gcashModal')" class="text-gray-500 hover:text-gray-700">✕</button>
			</div>
			<div class="p-5 space-y-4">
				<div class="text-sm text-gray-700">Scan the QR code and enter a fake reference number to simulate payment.</div>
				<img src="/images/gcash-qr.png" alt="GCash QR" class="w-full rounded border" />
				<input id="gcashRef" type="text" placeholder="Reference Number" class="w-full rounded-md border-gray-300" />
				<button id="gcashConfirmBtn" class="w-full px-4 py-2 rounded-md text-white" style="background:#0ea5e9;">Confirm Payment</button>
				<p id="gcashErr" class="hidden text-sm text-red-600"></p>
			</div>
		</div>
	</div>

	<!-- Bank Transfer Modal -->
	<div id="bankModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
		<div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
			<div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
				<h2 class="font-semibold">Bank Transfer</h2>
				<button onclick="closeModal('bankModal')" class="text-gray-500 hover:text-gray-700">✕</button>
			</div>
			<div class="p-5 space-y-4">
				<div class="text-sm text-gray-700">
					<strong>BPI</strong><br>
					Account Name: WOW Carmen<br>
					Account Number: 1234-5678-90
				</div>
				<label class="block text-sm text-gray-700">Upload Deposit Slip</label>
				<input id="bankProof" type="file" accept="image/*" class="w-full rounded-md border-gray-300" />
				<button id="bankSubmitBtn" class="w-full px-4 py-2 rounded-md text-white" style="background:#c59d5f;">Submit Proof</button>
				<p id="bankErr" class="hidden text-sm text-red-600"></p>
			</div>
		</div>
	</div>

	<?php echo $__env->make('partials.customer-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const completeBtn = document.getElementById('completeBtn');
const form = document.querySelector('form[action="<?php echo e(route('checkout.store')); ?>"]');
const gcashModal = document.getElementById('gcashModal');
const bankModal = document.getElementById('bankModal');
// If paying an existing order, expose IDs for JS
window.payOrderId = <?php echo e(isset($payOrder) && $payOrder ? $payOrder->id : 'null'); ?>;
window.payAmount = <?php echo e(isset($payOrder) && $payOrder ? (float) $payOrder->total_amount : 0); ?>;

function openModal(id){ const el = document.getElementById(id); el.classList.remove('hidden'); el.classList.add('flex'); }
function closeModal(id){ const el = document.getElementById(id); el.classList.add('hidden'); el.classList.remove('flex'); }

async function createOrder() {
	// If this is payment-only for an existing order, skip order creation
	if(window.payOrderId){
		return { success: true, order_id: window.payOrderId, total: window.payAmount };
	}
	const data = new FormData(form);
	const res = await fetch(form.action, { 
		method:'POST', 
		headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': csrf }, 
		body: data 
	});
	
	// Check content type to ensure we're getting JSON
	const contentType = res.headers.get('content-type');
	if(!contentType || !contentType.includes('application/json')) {
		const text = await res.text();
		console.error('Response is not JSON:', text);
		throw new Error('Server error: Invalid response format. Please check form validation.');
	}
	
	const json = await res.json();
	if(!json?.success){ throw new Error(json?.message || 'Order creation failed'); }
	return json;
}

completeBtn.addEventListener('click', async () => {
	// Validate form first
	if (!form.checkValidity()) {
		form.reportValidity();
		return;
	}

	const selected = document.querySelector('input[name="payment_method"]:checked')?.value || null;
	
	if (!selected) {
		alert('Please select a payment method (GCash or Bank Transfer) to proceed.');
		return;
	}

	if(selected === 'GCash'){
		openModal('gcashModal');
	} else if(selected === 'Bank'){
		openModal('bankModal');
	} else {
		alert('Please select a valid payment method.');
	}
});

document.getElementById('gcashConfirmBtn').addEventListener('click', async () => {
	const ref = document.getElementById('gcashRef').value.trim();
	const err = document.getElementById('gcashErr');
	err.classList.add('hidden'); err.textContent = '';
	if(ref.length < 6){ err.textContent = 'Reference number must be at least 6 characters.'; err.classList.remove('hidden'); return; }
	try{
		const o = await createOrder();
		const params = new URLSearchParams({ order_id: o.order_id, amount: o.total, reference: ref });
		const res = await fetch('<?php echo e(route('payments.gcash')); ?>', { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf }, body: params });
		if(!res.ok) {
			const errData = await res.json().catch(() => ({}));
			throw new Error(errData?.message || 'Failed to confirm GCash payment');
		}
		closeModal('gcashModal');
		location.href = `/customer/orders/${o.order_id}`;
	}catch(e){ 
		err.textContent = e.message || 'Payment failed.'; 
		err.classList.remove('hidden'); 
		console.error('GCash payment error:', e);
	}
});

document.getElementById('bankSubmitBtn').addEventListener('click', async () => {
	const file = document.getElementById('bankProof').files[0];
	const err = document.getElementById('bankErr');
	err.classList.add('hidden'); err.textContent = '';
	if(!file){ err.textContent = 'Please upload an image of the deposit slip.'; err.classList.remove('hidden'); return; }
	try{
		const o = await createOrder();
		const fd = new FormData();
		fd.append('order_id', o.order_id);
		fd.append('amount', o.total);
		fd.append('proof', file);
		const res = await fetch('<?php echo e(route('payments.bank')); ?>', { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf }, body: fd });
		if(!res.ok) {
			const errData = await res.json().catch(() => ({}));
			throw new Error(errData?.message || 'Failed to upload bank proof');
		}
		closeModal('bankModal');
		location.href = `/customer/orders/${o.order_id}`;
	}catch(e){ 
		err.textContent = e.message || 'Upload failed.'; 
		err.classList.remove('hidden'); 
		console.error('Bank transfer error:', e);
	}
});
</script>


<?php /**PATH C:\xampp\htdocs\wowc\resources\views/checkout.blade.php ENDPATH**/ ?>