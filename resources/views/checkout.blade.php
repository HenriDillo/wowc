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
	<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false, method:'Bank' }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family:'Poppins','Inter',ui-sans-serif,system-ui;">

	@include('partials.customer-header')

	<section class="pt-24 pb-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="<?php echo e(route('cart')); ?>" class="text-sm text-[#c59d5f] hover:underline">Back to cart</a>

			<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-10">
				<!-- Left: Account & Shipping -->
                <div>
                    <form method="POST" action="{{ route('checkout.store') }}" class="bg-white border border-gray-100 rounded-xl shadow-sm p-6">
						@csrf
						<h2 class="text-lg font-semibold text-gray-900">Account</h2>
                        <input type="email" value="{{ $user->email ?? '' }}" disabled class="mt-3 w-full rounded-md border-gray-300"/>
                        @if(isset($payOrder) && $payOrder)
							<p class="mt-2 text-sm text-gray-700">Pay for Custom Order <span class="font-medium">#{{ $payOrder->id }}</span>. Choose a payment method below to complete your purchase.</p>
                        @endif

                        @if ($errors->any())
                            <div class="mt-4 text-sm text-red-600">
                                <ul class="list-disc ml-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

						<h3 class="mt-6 text-lg font-semibold text-gray-900">Shipping Information</h3>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @php $addr = optional($user?->address); @endphp
                            <input name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" placeholder="First Name" class="rounded-md border-gray-300" required>
                            <input name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" placeholder="Last Name" class="rounded-md border-gray-300" required>
                            <input name="address_line" value="{{ old('address_line', $addr->address_line ?? '') }}" placeholder="Address" class="sm:col-span-2 rounded-md border-gray-300" required>
                            <input name="city" value="{{ old('city', $addr->city ?? '') }}" placeholder="City" class="rounded-md border-gray-300" required>
                            <input name="postal_code" value="{{ old('postal_code', $addr->postal_code ?? '') }}" placeholder="Postal Code" class="rounded-md border-gray-300" required>
                            <input name="province" value="{{ old('province', $addr->province ?? '') }}" placeholder="Province" class="rounded-md border-gray-300" required>
                            <input name="phone_number" value="{{ old('phone_number', $addr->phone_number ?? '') }}" placeholder="Phone Number" class="sm:col-span-2 rounded-md border-gray-300" required>
                        </div>

                        <h3 class="mt-6 text-lg font-semibold text-gray-900">Payment</h3>
                        @php
                            $paymentPercentage = $paymentPercentage ?? 1.0;
                            $requiredPaymentAmount = $requiredPaymentAmount ?? $total;
                            $isPartialPayment = $paymentPercentage < 1.0;
                            $orderType = isset($payOrder) && $payOrder ? ($payOrder->order_type ?? 'custom') : 'standard';
                            $isBackOrderOrCustom = $orderType === 'backorder' || $orderType === 'custom';
                        @endphp
                        
                        @if($isBackOrderOrCustom)
                            <div class="mt-3 p-4 bg-amber-50 border-2 border-amber-300 rounded-lg mb-4">
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl">💰</span>
                                    <div>
                                        <p class="font-bold text-amber-900">Down Payment Required (50%)</p>
                                        <p class="text-sm text-amber-800 mt-1">This is a {{ $orderType === 'backorder' ? 'Back Order' : 'Custom Order' }}. You must pay <strong>50% upfront</strong> now to proceed. The remaining 50% will be due when the order is ready.</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                                <p class="text-sm text-blue-800"><strong>Important:</strong> Payment is required to complete your order. Please select a payment method below.</p>
                            </div>
                        @endif
                        
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
						</div>						<div class="mt-6 text-right">
							<button id="completeBtn" type="button" class="inline-flex items-center justify-center px-6 py-3 rounded-md text-white font-medium shadow-sm hover:shadow transition" style="background:#c59d5f;">Complete Order</button>
						</div>
					</form>
				</div>

				<!-- Right: Summary -->
				<div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6">
					<h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                        @php
							$paymentOnly = isset($payOrder) && $payOrder;
                            $isMixedOrder = isset($isMixedOrder) && $isMixedOrder;
                            $standardItems = $standardItems ?? collect();
                            $backorderItems = $backorderItems ?? collect();
                            $standardSubtotal = $standardSubtotal ?? 0;
                            $backorderSubtotal = $backorderSubtotal ?? 0;
                            $requiredPaymentAmount = $requiredPaymentAmount ?? $total;
                        @endphp

                        @if($isMixedOrder)
                            <!-- Mixed Order Breakdown -->
                            <div class="mt-4 space-y-4">
                                <!-- Standard Items -->
                                @if($standardItems->isNotEmpty())
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Standard Items (100% Due)</h3>
                                        <div class="mt-3 space-y-4">
                                            @foreach($standardItems as $ci)
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                            <img src="{{ optional($ci->item->photos->first())->url }}" alt="" class="w-full h-full object-cover"/>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">{{ $ci->item->name }}</p>
                                                            <p class="text-xs text-gray-500">Qty: {{ $ci->quantity }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm text-gray-900">₱{{ number_format($ci->subtotal, 2) }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="mt-2 text-sm text-gray-700 font-medium">Standard subtotal: ₱{{ number_format($standardSubtotal, 2) }}</p>
                                    </div>
                                @endif

                                <!-- Back Order Items -->
                                @if($backorderItems->isNotEmpty())
                                    <div class="mt-6">
                                        <div class="mb-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <h3 class="text-sm font-medium text-blue-800">Back Order Items (50% Due Now)</h3>
                                            <p class="mt-1 text-xs text-blue-700">These items are on back order. Pay 50% now, 50% when restocked.</p>
                                        </div>
                                        <div class="space-y-4">
                                            @foreach($backorderItems as $ci)
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                            <img src="{{ optional($ci->item->photos->first())->url }}" alt="" class="w-full h-full object-cover"/>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">{{ $ci->item->name }}</p>
                                                            <p class="text-xs text-gray-500">Qty: {{ $ci->quantity }}</p>
                                                            <span class="inline-flex mt-1 px-2 py-0.5 text-[11px] rounded bg-blue-100 text-blue-800">Back-Order</span>
                                                            @if($ci->item->restock_date)
                                                                <span class="block text-[11px] text-blue-700">Ships after {{ $ci->item->restock_date->format('M d, Y') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm text-gray-900">₱{{ number_format($ci->subtotal, 2) }}</p>
                                                        <p class="text-xs text-blue-600 font-medium">Pay now: ₱{{ number_format($ci->subtotal * 0.5, 2) }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="mt-2 text-sm text-gray-700 font-medium">Back order subtotal: ₱{{ number_format($backorderSubtotal, 2) }}</p>
                                        <p class="mt-1 text-sm text-blue-700">50% Down: ₱{{ number_format($backorderSubtotal * 0.5, 2) }} | Remaining: ₱{{ number_format($backorderSubtotal * 0.5, 2) }}</p>
                                    </div>
                                @endif
                            </div>
                        @elseif(!$paymentOnly)
                            <!-- Non-Mixed Order Display (use existing variable names) -->
                            @php
                                $nonMixedStdItems = $cartItems->filter(fn($ci) => !($ci->is_backorder ?? false));
                                $nonMixedBackItems = $cartItems->filter(fn($ci) => ($ci->is_backorder ?? false));
                            @endphp
                            @if($nonMixedStdItems->isNotEmpty())
                                <div class="mt-4">
                                    <h3 class="text-sm font-medium text-gray-900">Standard Items</h3>
                                    <div class="mt-3 space-y-4">
                                        @foreach($nonMixedStdItems as $ci)
                                            <div class="flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                        <img src="{{ optional($ci->item->photos->first())->url }}" alt="" class="w-full h-full object-cover"/>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $ci->item->name }}</p>
                                                        <p class="text-xs text-gray-500">Qty: {{ $ci->quantity }}</p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-900">₱{{ number_format($ci->subtotal, 2) }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($nonMixedBackItems->isNotEmpty())
                                        <p class="mt-2 text-sm text-gray-700">Standard items subtotal: ₱{{ number_format($nonMixedStdItems->sum('subtotal'), 2) }}</p>
                                    @endif
                                </div>
                            @endif

                            @if($nonMixedBackItems->isNotEmpty())
                                <div class="mt-6">
                                    <div class="mb-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                                        <h3 class="text-sm font-medium text-blue-800">Back Order Items</h3>
                                        <p class="mt-1 text-xs text-blue-700">These items will be shipped separately once they're back in stock.</p>
                                    </div>
                                    <div class="space-y-4">
                                        @foreach($nonMixedBackItems as $ci)
                                            <div class="flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-16 h-16 rounded bg-gray-100 overflow-hidden">
                                                        <img src="{{ optional($ci->item->photos->first())->url }}" alt="" class="w-full h-full object-cover"/>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $ci->item->name }}</p>
                                                        <p class="text-xs text-gray-500">Qty: {{ $ci->quantity }}</p>
                                                        <span class="inline-flex mt-1 px-2 py-0.5 text-[11px] rounded bg-blue-100 text-blue-800">Back-Order</span>
                                                        @if($ci->item->restock_date)
                                                            <span class="block text-[11px] text-blue-700">Ships after {{ $ci->item->restock_date->format('M d, Y') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-900">₱{{ number_format($ci->subtotal, 2) }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($nonMixedStdItems->isNotEmpty())
                                        <p class="mt-2 text-sm text-gray-700">Back order items subtotal: ₱{{ number_format($nonMixedBackItems->sum('subtotal'), 2) }}</p>
                                    @endif
                                </div>
                            @endif
                        @endif
                        </div>

                    <div class="mt-6 border-t pt-4 space-y-2 text-sm">
						@if($paymentOnly)
							@php
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
							@endphp
							<div class="mb-3 p-3 border border-gray-200 rounded-lg">
								<div class="text-sm font-medium text-gray-900">Custom Order #{{ $payOrder->id }}</div>
								<div class="mt-1 text-xs text-gray-600 flex items-center gap-2">
									<span>Status:</span>
									<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $badge }}">{{ $coStatusLabel }}</span>
								</div>
							</div>
						@endif
						<div class="flex items-center justify-between"><span class="text-gray-600">Subtotal</span><span>₱{{ number_format($subtotal, 2) }}</span></div>
						@if(!$paymentOnly)
							<div class="flex items-center justify-between"><span class="text-gray-600">Shipping</span><span>₱{{ number_format($shipping, 2) }}</span></div>
						@endif
                        
                        @php
                            $displayAmount = $requiredPaymentAmount ?? $total;
                        @endphp
                        
                        @if($isMixedOrder)
                            <div class="mt-3 space-y-2 border-t pt-3">
                                <h3 class="font-semibold text-gray-900">Payment Breakdown</h3>
                                <div class="flex items-center justify-between p-2 bg-green-50 rounded border border-green-200">
                                    <span class="text-green-900">Standard Items (100%)</span>
                                    <span class="font-semibold text-green-900">₱{{ number_format($standardSubtotal, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-blue-50 rounded border border-blue-200">
                                    <span class="text-blue-900">Back Order (50% Down)</span>
                                    <span class="font-semibold text-blue-900">₱{{ number_format($backorderSubtotal * 0.5, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-amber-50 rounded border-2 border-amber-300 mt-2">
                                    <span class="font-bold text-amber-900">💰 Total Due Now</span>
                                    <span class="font-bold text-lg text-amber-900">₱{{ number_format($requiredPaymentAmount, 2) }}</span>
                                </div>
                                <p class="text-xs text-gray-600 italic">Remaining: ₱{{ number_format($backorderSubtotal * 0.5, 2) }} (due when back order items arrive)</p>
                            </div>
                        @elseif(($cartItems->contains(fn($ci) => ($ci->is_backorder ?? false)) && !$paymentOnly) || ($paymentOnly && ($payOrder->order_type === 'backorder' || $payOrder->order_type === 'custom')))
                            <div class="mt-3 space-y-2 border-t pt-3">
                                @if(!$paymentOnly)
                                    <h3 class="font-semibold text-gray-900">Payment Required (50% Down Payment)</h3>
                                @endif
                                <div class="flex items-center justify-between p-3 bg-amber-50 rounded border-2 border-amber-300">
                                    <span class="font-bold text-amber-900">💰 Down Payment Due Now</span>
                                    <span class="font-bold text-lg text-amber-900">₱{{ number_format($displayAmount, 2) }}</span>
                                </div>
                                <p class="text-xs text-gray-600 italic">Remaining 50% (₱{{ number_format(($total - $displayAmount), 2) }}) will be due upon completion/arrival</p>
                            </div>
                        @else
                            <div class="flex items-center justify-between font-semibold text-gray-900 p-3 bg-gray-100 rounded border border-gray-300 mt-3">
                                <span>Total Amount Due</span>
                                <span>₱{{ number_format($displayAmount, 2) }}</span>
                            </div>
                        @endif
                        
						<p class="text-xs text-gray-500 mt-2">Tax and shipping cost will be calculated later.</p>
                        @if(!$paymentOnly && $cartItems->contains(fn($ci) => ($ci->is_backorder ?? false)))
                            <p class="text-xs text-blue-700 mt-1">This item is on back order. We'll ship it once restocked.</p>
                        @endif
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

	@include('partials.customer-footer')

</body>
</html>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const completeBtn = document.getElementById('completeBtn');
const form = document.querySelector('form[action="{{ route('checkout.store') }}"]');
const gcashModal = document.getElementById('gcashModal');
const bankModal = document.getElementById('bankModal');

// Payment tracking
window.payOrderId = {{ isset($payOrder) && $payOrder ? $payOrder->id : 'null' }};
window.payAmount = {{ isset($payOrder) && $payOrder ? (float) $payOrder->total_amount : 0 }};
window.requiredPaymentAmount = {{ $requiredPaymentAmount ?? 0 }};
window.isMixedOrder = {{ isset($isMixedOrder) && $isMixedOrder ? 'true' : 'false' }};

function openModal(id){ const el = document.getElementById(id); el.classList.remove('hidden'); el.classList.add('flex'); }
function closeModal(id){ const el = document.getElementById(id); el.classList.add('hidden'); el.classList.remove('flex'); }

async function createOrder() {
	// If this is payment-only for an existing order, skip order creation
	if(window.payOrderId){
		return { success: true, order_id: window.payOrderId, total: window.payAmount, required: window.requiredPaymentAmount };
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
		const requiredAmount = o.required ?? o.total;
		const params = new URLSearchParams({ order_id: o.order_id, amount: requiredAmount, reference: ref });
		const res = await fetch('{{ route('payments.gcash') }}', { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf }, body: params });
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
		const requiredAmount = o.required ?? o.total;
		const fd = new FormData();
		fd.append('order_id', o.order_id);
		fd.append('amount', requiredAmount);
		fd.append('proof', file);
		const res = await fetch('{{ route('payments.bank') }}', { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf }, body: fd });
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


