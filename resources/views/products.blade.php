<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>WOW Carmen - Products</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false, mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">

	<!-- Navbar (same as customer.blade.php) -->
	<nav :class="scrolled ? 'shadow-sm' : ''" class="fixed top-0 inset-x-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex items-center justify-between h-16">
				<!-- Logo -->
				<div class="flex items-center">
					<img src="/images/logo.png" alt="WOW Carmen" class="w-8 h-8 mr-3"/>
					<a href="/" class="text-lg sm:text-xl font-semibold text-gray-900 tracking-wide">WOW Carmen</a>
				</div>

				<!-- Links -->
				<div class="hidden md:flex items-center space-x-8">
					<a href="/dashboard" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Home</a>
					<a href="/products" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Products</a>
					<a href="/contact" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Contact us</a>
				</div>

				<!-- Right icons -->
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

	<!-- Page Header -->
	<section class="pt-24 pb-6">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<h1 class="text-2xl md:text-3xl font-bold text-gray-900">Products</h1>
		</div>
	</section>

	<!-- Filters + Search -->
	<section class="pb-4">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="bg-white border border-gray-100 rounded-xl shadow-sm p-4 md:p-5">
				<form method="GET" action="{{ url('/products') }}" x-data class="grid grid-cols-1 sm:grid-cols-3 gap-3">
					<!-- Category Filter (expects $categories) -->
					<div>
						<label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
						<div class="relative">
							<select name="category" @change="$root.submit()" class="w-full rounded-md border border-gray-300 hover:border-gray-400 focus:border-[#c59d5f] focus:ring-1 focus:ring-[#c59d5f] bg-white pr-10">
								<option value="">All</option>
								@foreach(($categories ?? []) as $category)
									<option value="{{ $category->id }}" {{ (string)$category->id === (string)request('category') ? 'selected' : '' }}>{{ $category->name }}</option>
								@endforeach
							</select>
							<span class="pointer-events-none absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
								<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 12a1 1 0 01-.707-.293l-3-3a1 1 0 111.414-1.414L10 9.586l2.293-2.293a1 1 0 111.414 1.414l-3 3A1 1 0 0110 12z" clip-rule="evenodd"/></svg>
							</span>
						</div>
					</div>

					<!-- Search -->
					<div class="sm:col-span-2">
						<label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
						<div class="relative">
							<input type="text" name="q" value="{{ request('q') }}" placeholder="Search item" x-on:input.debounce.400ms="$root.submit()" class="w-full rounded-md border border-gray-300 hover:border-gray-400 pl-10 focus:border-[#c59d5f] focus:ring-1 focus:ring-[#c59d5f]"/>
							<span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 110-16 8 8 0 010 16z"/></svg>
							</span>
						</div>
					</div>
				</form>
			</div>
		</div>
	</section>

	<!-- Products Grid -->
	<section class="py-8">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			@if(($items ?? collect())->count() === 0)
				<!-- Empty state -->
				<div class="text-center py-20">
					<p class="text-gray-600">No products found. Please adjust your filters or try another search.</p>
				</div>
			@else
				<div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-8">
					@foreach($items as $item)
                        <a href="{{ url('/products/'.$item->id) }}" class="group block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
                            <div class="relative aspect-[4/3] bg-gray-100 rounded-t-xl overflow-hidden">
                                <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @if(($item->status ?? null) === 'pre_order')
                                    <span class="absolute top-2 left-2 inline-flex items-center px-2 py-1 text-[11px] font-semibold rounded bg-amber-100 text-amber-800">Pre-Order</span>
                                @elseif(($item->status ?? null) === 'back_order')
                                    <span class="absolute top-2 left-2 inline-flex items-center px-2 py-1 text-[11px] font-semibold rounded bg-blue-100 text-blue-800">Back-Order</span>
                                @endif
							</div>
							<div class="p-4">
								<h3 class="text-sm md:text-base font-medium text-gray-900 truncate">{{ $item->name }}</h3>
								<p class="mt-1 text-[#c59d5f] font-semibold">₱{{ number_format($item->price, 2) }}</p>
							</div>
						</a>
					@endforeach
				</div>

				<!-- Pagination -->
				<div class="mt-10">
					{{ method_exists($items, 'links') ? $items->withQueryString()->links() : '' }}
				</div>
			@endif
		</div>
	</section>

	<!-- Footer (same as customer.blade.php) -->
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


