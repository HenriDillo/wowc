<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
	<style>
body{font-family:'Poppins','Inter',ui-sans-serif,system-ui;}
[x-cloak]{display:none !important;}
	</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family:'Poppins','Inter',ui-sans-serif,system-ui;">
	<nav :class="scrolled ? 'shadow-sm' : ''" class="fixed top-0 inset-x-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex items-center justify-between h-16">
				<div class="flex items-center">
					<img src="/images/logo.png" alt="WOW Carmen" class="w-8 h-8 mr-3"/>
					<a href="{{ route('dashboard') }}" class="text-lg sm:text-xl font-semibold text-gray-900 tracking-wide">WOW Carmen</a>
				</div>
				<div class="hidden md:flex items-center space-x-8">
					<a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Home</a>
					<a href="{{ route('products.index') }}" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Products</a>
				</div>
				<div class="hidden md:flex items-center space-x-4">
					<div class="relative">
						<button @click="dropdownOpen = !dropdownOpen" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Account">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
						</button>
						<div x-show="dropdownOpen" x-cloak x-transition @click.outside="dropdownOpen=false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
							<div class="px-4 py-3 border-b border-gray-100">
								<p class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? 'Guest' }}</p>
								<p class="text-xs text-gray-500">Welcome!</p>
							</div>
							@auth
								<a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">Profile</a>
								<a href="{{ route('customer.orders.index') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">My Orders</a>
								<div class="border-t border-gray-100"></div>
								<form action="{{ route('logout') }}" method="POST" class="m-0">
									@csrf
									<button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-b-lg text-sm">Logout</button>
								</form>
							@else
								<a href="/login" class="block px-4 py-2 hover:bg-gray-50 text-sm">Login</a>
								<a href="/register" class="block px-4 py-2 hover:bg-gray-50 text-sm">Register</a>
							@endauth
						</div>
					</div>
					<a href="{{ route('cart') }}" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Cart">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m2.6 8L6 5H3m4 8a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z"/></svg>
					</a>
				</div>
				<div class="md:hidden">
					<button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-[#c59d5f] p-2" aria-label="Menu">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
							<path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
						</svg>
					</button>
				</div>
			</div>
			<div x-show="mobileMenuOpen" x-cloak x-transition class="md:hidden bg-white border-t border-gray-200">
				<div class="px-2 pt-2 pb-3 space-y-1">
					<a href="{{ route('dashboard') }}" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Home</a>
					<a href="{{ route('products.index') }}" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Products</a>
				</div>
			</div>
		</div>
	</nav>

	<section class="pt-24 pb-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
				<h1 class="text-2xl font-semibold text-gray-900">{{ request()->is('customer/*') ? 'Order Details' : 'Thank you for your order!' }}</h1>
				<p class="mt-2 text-gray-600">Order <span class="font-medium text-gray-900">#{{ $order->id }}</span> • {{ $order->created_at?->format('M d, Y') }}</p>
				@php
					$statusColor = [
						'pending' => 'bg-yellow-100 text-yellow-800',
						'processing' => 'bg-blue-100 text-blue-800',
						'completed' => 'bg-green-100 text-green-800',
						'cancelled' => 'bg-red-100 text-red-800',
						'backorder' => 'bg-indigo-100 text-indigo-800',
					][$order->status] ?? 'bg-gray-100 text-gray-800';
                    $hasBackorder = $order->items->contains(fn($oi) => ($oi->is_backorder ?? false));
				@endphp
				<div class="mt-2 text-sm flex flex-wrap gap-2">
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-100 text-gray-800">Type: {{ $order->order_type }}</span>
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ $statusColor }}">Status: {{ $order->status }}</span>
                </div>

                @if($hasBackorder)
                    <div class="mt-4 p-4 rounded-md border border-blue-200 bg-blue-50">
                        <h3 class="font-medium text-blue-900">Order Status</h3>
                        <div class="mt-2 text-sm text-blue-800 space-y-1">
                            <p><strong>✓ Standard Items:</strong> Ready for processing and will ship soon</p>
                            <p><strong>⏳ Back Order Items:</strong> Awaiting stock - will ship separately once restocked</p>
                        </div>
                    </div>
                @endif
            </div>			<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Items</h2>
                        
                        @php
                            $standardItems = $order->items->filter(fn($oi) => !($oi->is_backorder ?? false));
                            $backOrderItems = $order->items->filter(fn($oi) => ($oi->is_backorder ?? false));
                        @endphp

                        <!-- Standard Items Section -->
                        @if($standardItems->isNotEmpty())
                            <div class="mt-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <h3 class="font-medium text-gray-900">Standard Items</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Ready for processing</span>
                                </div>
                                <div class="divide-y">
                                    @foreach($standardItems as $oi)
                                        <div class="py-4 flex items-center gap-4">
                                            @php $photo = optional(optional($oi->item?->photos)->first())->url; @endphp
                                            @if($photo)
                                                <img src="{{ $photo }}" class="w-16 h-16 rounded object-cover bg-gray-100" alt="{{ $oi->item?->name }}"/>
                                            @else
                                                <div class="w-16 h-16 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900">{{ $oi->item?->name }}</div>
                                                <div class="text-xs text-gray-500">Qty: {{ $oi->quantity }} • ₱{{ number_format($oi->price, 2) }}</div>
                                            </div>
                                            <div class="text-sm font-medium">₱{{ number_format($oi->subtotal, 2) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Back Order Items Section -->
                        @if($backOrderItems->isNotEmpty())
                            <div class="mt-6">
                                <div class="flex items-center gap-2 mb-3">
                                    <h3 class="font-medium text-gray-900">Back Order Items</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Awaiting stock</span>
                                </div>
                                <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                    <p class="text-sm text-blue-700">These items will be shipped separately once they're back in stock.</p>
                                </div>
                                <div class="divide-y">
                                    @foreach($backOrderItems as $oi)
                                        <div class="py-4 flex items-center gap-4">
                                            @php $photo = optional(optional($oi->item?->photos)->first())->url; @endphp
                                            @if($photo)
                                                <img src="{{ $photo }}" class="w-16 h-16 rounded object-cover bg-gray-100" alt="{{ $oi->item?->name }}"/>
                                            @else
                                                <div class="w-16 h-16 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900">{{ $oi->item?->name }}</div>
                                                <div class="text-xs text-gray-500">Qty: {{ $oi->quantity }} • ₱{{ number_format($oi->price, 2) }}</div>
                                                @if($oi->item?->restock_date)
                                                    <div class="text-xs text-blue-700 mt-1">Expected restock: {{ $oi->item->restock_date->format('M d, Y') }}</div>
                                                @endif
                                            </div>
                                            <div class="text-sm font-medium">₱{{ number_format($oi->subtotal, 2) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Delivery Information</h2>
                        <div class="mt-3 text-sm text-gray-700">
                            <div>Shipping Method: Standard</div>
                            <div>Estimated Delivery: {{ now()->addDays(5)->format('M d, Y') }}</div>
                            <div>Tracking Number: —</div>
                        </div>
                    </div>
                </div>

				<div class="space-y-6">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Customer Information</h2>
                        <div class="mt-3 text-sm text-gray-700">
                            <div class="font-medium">{{ $order->user?->name }}</div>
                            <div>{{ $order->user?->email }}</div>
                            <div class="mt-2">{{ $order->user?->address?->address_line }}</div>
                            <div>{{ $order->user?->address?->city }}, {{ $order->user?->address?->province }} {{ $order->user?->address?->postal_code }}</div>
                            <div>{{ $order->user?->address?->phone_number }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Payment</h2>
						@php 
							$latestPayment = optional($order->payments)->sortByDesc('id')->first();
							$method = $latestPayment?->method ?? $order->payment_method ?? '—';
							// Format method display
							if ($method === 'gcash' || $method === 'GCash') {
								$method = 'GCash';
							} elseif ($method === 'bank' || $method === 'Bank' || $method === 'Bank Transfer') {
								$method = 'Bank Transfer';
							}
						@endphp
						<div class="mt-3 text-sm text-gray-700 space-y-1">
							<div>Method: {{ $method }}</div>
							<div>
								Status: 
								@php
									$paymentStatus = $latestPayment?->status ?? $order->payment_status ?? '—';
									$statusBadgeClass = match($paymentStatus) {
										'paid' => 'bg-green-100 text-green-800',
										'pending_verification' => 'bg-yellow-100 text-yellow-800',
										'unpaid' => 'bg-red-100 text-red-800',
										default => 'bg-gray-100 text-gray-800',
									};
									$statusLabel = match($paymentStatus) {
										'paid' => 'Paid ✓',
										'pending_verification' => 'Pending Verification',
										'unpaid' => 'Unpaid',
										default => ucfirst(str_replace('_', ' ', $paymentStatus)),
									};
								@endphp
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
							</div>
							@if($paymentStatus === 'pending_verification')
								<div class="mt-2 p-2 bg-yellow-50 border border-yellow-100 rounded text-xs">
									<p class="text-yellow-800">Your bank transfer proof is being verified by our team. We'll confirm payment shortly.</p>
								</div>
							@endif
							@if(!empty($latestPayment?->transaction_id))
								<div>Reference: {{ $latestPayment->transaction_id }}</div>
							@endif
							@if(!empty($latestPayment?->proof_image))
								<div><a href="{{ Storage::url($latestPayment->proof_image) }}" target="_blank" class="text-[#c59d5f] hover:underline">View Bank Proof</a></div>
							@endif
							<div class="pt-2 border-t mt-2 font-medium">Total: ₱{{ number_format($order->total_amount, 2) }}</div>
						</div>
                    </div>

					<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
						<a href="{{ route('customer.orders.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Back to My Orders</a>
					</div>
                </div>
            </div>
        </div>
    </section>

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


