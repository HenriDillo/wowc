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
</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false, method:'Bank' }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family:'Poppins','Inter',ui-sans-serif,system-ui;">

	<!-- Navbar -->
	<nav :class="scrolled ? 'shadow-sm' : ''" class="fixed top-0 inset-x-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex items-center justify-between h-16">
				<div class="flex items-center">
					<img src="/images/logo.png" alt="WOW Carmen" class="w-8 h-8 mr-3"/>
					<a href="/" class="text-lg sm:text-xl font-semibold text-gray-900 tracking-wide">WOW Carmen</a>
				</div>
				<div class="hidden md:flex items-center space-x-8">
					<a href="/dashboard" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Home</a>
					<a href="/products" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Products</a>
					<a href="/contact" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Contact us</a>
				</div>
			</div>
		</div>
	</nav>

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
							<button class="inline-flex items-center justify-center px-6 py-3 rounded-md text-white font-medium shadow-sm hover:shadow transition" style="background:#c59d5f;">Complete Order</button>
						</div>
					</form>
				</div>

				<!-- Right: Summary -->
				<div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6">
					<h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                        @php
                            // Use the cart line's is_backorder flag to separate standard vs backorder
                            $standardItems = $cartItems->filter(fn($ci) => !($ci->is_backorder ?? false));
                            $backOrderItems = $cartItems->filter(fn($ci) => ($ci->is_backorder ?? false));
                            $standardTotal = $standardItems->sum('subtotal');
                            $backOrderTotal = $backOrderItems->sum('subtotal');
                        @endphp

                        <!-- Standard Items -->
                        @if($standardItems->isNotEmpty())
                            <div class="mt-4">
                                <h3 class="text-sm font-medium text-gray-900">Standard Items</h3>
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
                                @if($backOrderItems->isNotEmpty())
                                    <p class="mt-2 text-sm text-gray-700">Standard items subtotal: ₱{{ number_format($standardTotal, 2) }}</p>
                                @endif
                            </div>
                        @endif

                        <!-- Back Order Items -->
                        @if($backOrderItems->isNotEmpty())
                            <div class="mt-6">
                                <div class="mb-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                                    <h3 class="text-sm font-medium text-blue-800">Back Order Items</h3>
                                    <p class="mt-1 text-xs text-blue-700">These items will be shipped separately once they're back in stock.</p>
                                </div>
                                <div class="space-y-4">
                                    @foreach($backOrderItems as $ci)
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
                                @if($standardItems->isNotEmpty())
                                    <p class="mt-2 text-sm text-gray-700">Back order items subtotal: ₱{{ number_format($backOrderTotal, 2) }}</p>
                                @endif
                            </div>
                        @endif
                        </div>

                    <div class="mt-6 border-t pt-4 space-y-2 text-sm">
						<div class="flex items-center justify-between"><span class="text-gray-600">Subtotal</span><span>₱{{ number_format($subtotal, 2) }}</span></div>
						<div class="flex items-center justify-between"><span class="text-gray-600">Shipping</span><span>₱{{ number_format($shipping, 2) }}</span></div>
						<div class="flex items-center justify-between font-semibold text-gray-900"><span>Total</span><span>₱{{ number_format($total, 2) }}</span></div>
						<p class="text-xs text-gray-500 mt-2">Tax and shipping cost will be calculated later.</p>
                        @if($cartItems->contains(fn($ci) => ($ci->is_backorder ?? false)))
                            <p class="text-xs text-blue-700 mt-1">This item is on back order. We'll ship it once restocked.</p>
                        @endif
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Footer -->
	<footer class="bg-[#1a1a1a] text-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="border-t" style="border-color:#c59d5f"></div>
			<div class="py-12 grid grid-cols-1 md:grid-cols-2 gap-10">
				<div>
					<h4 class="text-lg font-semibold mb-4">About</h4>
					<ul class="space-y-2 text-gray-300">
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">Help</a></li>
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">Shipping</a></li>
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">FAQ</a></li>
					</ul>
				</div>
				<div>
					<h4 class="text-lg font-semibold mb-4">Info</h4>
					<ul class="space-y-2 text-gray-300">
						<li><a href="/contact" class="hover:text-[#c59d5f] transition-colors">Contact Us</a></li>
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">Privacy Policy</a></li>
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">Terms &amp; Conditions</a></li>
					</ul>
				</div>
			</div>
		</div>
	</footer>

</body>
</html>


