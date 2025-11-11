<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>WOW Carmen - {{ $item->name }}</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false, mobileMenuOpen: false, scrolled: false, qty: 1, adding:false, added:false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">

	<!-- Navbar (same as site) -->
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
				<div class="hidden md:flex items-center space-x-4">
					<a href="#" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Search">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 110-16 8 8 0 010 16z"/></svg>
					</a>
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
					<a href="/cart" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Cart">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m2.6 8L6 5H3m4 8a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z"/></svg>
					</a>
				</div>

				<!-- Mobile Menu Button -->
				<div class="md:hidden">
					<button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-[#c59d5f] p-2" aria-label="Menu">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
							<path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
						</svg>
					</button>
				</div>
			</div>

			<!-- Mobile Menu -->
			<div x-show="mobileMenuOpen" x-cloak x-transition class="md:hidden bg-white border-t border-gray-200">
				<div class="px-2 pt-2 pb-3 space-y-1">
					<a href="/" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Home</a>
					<a href="/products" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Products</a>
					<a href="/contact" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Contact us</a>
				</div>
			</div>
		</div>
	</nav>

	<!-- Product Section -->
	<section class="pt-24 pb-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
				<!-- Left: Image Gallery -->
                @php $photoUrls = $item->photos->pluck('url')->filter()->values(); @endphp
                <div x-data="{photos: {{ $photoUrls->isNotEmpty() ? $photoUrls->toJson() : json_encode([$item->photo_url]) }}, selected: 0}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 md:p-6">
                    <div class="relative w-full aspect-square bg-gray-50 rounded-lg overflow-hidden flex items-center justify-center">
                        @if(($item->status ?? null) === 'back_order')
                            <span class="absolute top-3 left-3 inline-flex items-center px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">Back-Order</span>
                        @endif
						<template x-if="photos && photos.length">
							<img :src="photos[selected]" alt="{{ $item->name }}" class="w-full h-full object-cover"/>
						</template>
					</div>
					<!-- Thumbnails -->
					<div class="mt-4 grid grid-cols-5 gap-3">
						<template x-for="(p, idx) in photos" :key="idx">
							<button type="button" @click="selected = idx" :class="{'ring-2 ring-[#c59d5f]': selected === idx}" class="relative rounded-md overflow-hidden border border-gray-200 hover:border-gray-300 focus:outline-none">
								<img :src="p" alt="Thumbnail" class="aspect-square w-full object-cover"/>
							</button>
						</template>
					</div>
				</div>

				<!-- Right: Details -->
				<div>
					<h1 class="text-2xl md:text-3xl font-semibold text-gray-900">{{ $item->name }}</h1>
					<p class="mt-2 text-[#c59d5f] text-xl font-semibold">₱{{ number_format($item->price, 2) }}</p>
                    <p class="mt-4 text-gray-700 max-w-xl leading-relaxed">{{ $item->description }}</p>
                    @if(($item->status ?? null) === 'back_order')
                        <p class="mt-2 text-sm text-blue-700">Available on Back Order</p>
                        @if($item->restock_date)
                            <p class="text-xs text-blue-600">Restocking soon  Ships after {{ $item->restock_date->format('M d, Y') }}</p>
                        @endif
                    @endif

					<!-- Stock status -->
					@php
						$stock = (int) ($item->stock ?? 0);
						$isBackOrder = ($item->status ?? null) === 'back_order';
						if ($isBackOrder) {
							$statusText = 'Available for Back Order';
							$statusColor = 'text-blue-600';
						} else {
							$statusText = $stock > 10 ? 'In Stock (' . $stock . ' available)' : ($stock > 0 ? 'Low Stock (' . $stock . ' left)' : 'Out of Stock — Available for Back Order');
							$statusColor = $stock > 10 ? 'text-green-600' : ($stock > 0 ? 'text-amber-600' : 'text-blue-600');
						}
					@endphp
					<p class="mt-3 text-sm font-medium {{ $statusColor }}">{{ $statusText }}</p>

					@if($stock <= 0 || $isBackOrder)
						<div class="mt-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
							<p class="text-sm text-blue-800">This item is available for back order. We'll ship it as soon as it's back in stock.</p>
							@if($item->restock_date)
								<p class="mt-1 text-xs text-blue-700">Expected restock date: {{ $item->restock_date->format('M d, Y') }}</p>
							@endif
						</div>
					@elseif($stock <= 5)
						<div class="mt-3 p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
							<p class="text-sm text-yellow-800">Only {{ $stock }} items left in stock. Order soon!</p>
						</div>
					@else
						<div class="mt-3 p-3 bg-green-50 border border-green-100 rounded-lg">
							<p class="text-sm text-green-800">{{ $stock }} items available. Plenty of stock!</p>
						</div>
					@endif

					<!-- Quantity selector -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <div class="inline-flex items-center border border-gray-300 rounded-md overflow-hidden">
                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="px-3 py-2 text-gray-600 hover:bg-gray-50">-</button>
                            <input type="number" 
                                x-model.number="qty" 
                                min="1" 
                                @change="qty = {{ $isBackOrder ? 'Math.max(1, qty)' : 'Math.min(qty, Math.max(1, '.$stock.'))' }}" 
                                class="w-14 text-center border-0 focus:ring-0"
                            />
                            <button type="button" 
                                @click="qty = {{ $isBackOrder ? 'qty + 1' : 'Math.min(qty + 1, Math.max(1, '.$stock.'))' }}" 
                                class="px-3 py-2 text-gray-600 hover:bg-gray-50"
                            >+</button>
                        </div>
                        @if($isBackOrder || $stock <= 0)
                            <p class="mt-1 text-xs text-blue-600">No quantity limit for back orders</p>
                        @endif

                    </div>

					<!-- Add to cart (standard form submission, avoids API/fetch) -->
					<div class="mt-6">
						<form action="/api/v1/cart/add" method="POST" x-ref="addForm" @submit.prevent="adding=true; $refs.addForm.submit();">
							@csrf
							<input type="hidden" name="item_id" value="{{ $item->id }}" />
							<input type="hidden" name="quantity" x-model.number="qty" />
							<button type="submit" :disabled="adding" class="inline-flex items-center justify-center px-6 py-3 rounded-md text-white font-medium shadow-sm hover:shadow transition disabled:opacity-60 disabled:cursor-not-allowed" style="background:#c59d5f;">
								<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m2.6 8L6 5H3m4 8a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z"/></svg>
								<span>
									{{ ($item->status ?? null) === 'back_order' ? 'Order Now (Back Order)' : 'Add to cart' }}
								</span>
							</button>
						</form>
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


