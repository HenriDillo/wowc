<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOW Carmen - Customer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false, mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">

    <!-- Navbar (Sticky) -->
    <nav :class="scrolled ? 'shadow-sm' : ''" class="fixed top-0 inset-x-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left: Logo -->
                <div class="flex items-center">
                    <img src="/images/logo.png" alt="WOW Carmen" class="w-8 h-8 mr-3"/>
                    <a href="{{ route('dashboard') }}" class="text-lg sm:text-xl font-semibold text-gray-900 tracking-wide">WOW Carmen</a>
                </div>

                <!-- Center: Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Home</a>
                    <a href="{{ route('products.index') }}" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Products</a>
                    <a href="{{ url('/custom-orders/create') }}" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Custom Order</a>
                </div>

                <!-- Right: Icons -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Search -->
                    <a href="#" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 110-16 8 8 0 010 16z"/></svg>
                    </a>

                    <!-- User -->
                    <div class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Account">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="dropdownOpen" x-cloak x-transition @click.outside="dropdownOpen=false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">Welcome back!</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">Profile</a>
                            <a href="{{ route('customer.orders.index') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">My Orders</a>
                            <div class="border-t border-gray-100"></div>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-b-lg text-sm">Logout</button>
                            </form>
                        </div>
                    </div>

                    <!-- Cart -->
                    <a href="{{ route('cart') }}" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Cart">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m2.6 8L6 5H3m4 8a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </a>
                </div>

                <!-- Mobile menu button -->
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
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Home</a>
                    <a href="{{ route('products.index') }}" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Products</a>
                    <a href="{{ url('/custom-orders/create') }}" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Custom Order</a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-5">
                        <div class="w-10 h-10 bg-[#c59d5f] rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                        </div>
                    </div>
                    <div class="mt-3 px-2 space-y-1">
                        <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">Profile</a>
                        <a href="{{ route('customer.orders.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">My Orders</a>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 text-red-600 hover:bg-red-50 rounded-md text-base font-medium">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-24">
        <!-- Background image -->
        <div class="absolute inset-0 -z-10 bg-center bg-cover" style="background-image: url('/images/welcome-bg.jpg');"></div>
        <div class="absolute inset-0 -z-10 bg-black/20"></div>

        <!-- Overlay box -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center py-24 md:py-40">
                <div class="bg-white/80 backdrop-blur rounded-xl shadow-lg p-8 md:p-12 text-center max-w-2xl">
                    <h1 class="text-3xl md:text-5xl font-semibold text-gray-900 mb-6">Wow Carmen Handicrafts</h1>
                    <a href="{{ route('products.index') }}" class="inline-block px-8 py-3 rounded-md text-white font-medium transition transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2" style="background:#c59d5f;">
                        Shop Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section id="products" class="py-16 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-14">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Featured Products</h2>
                <p class="text-gray-600 mt-2">Order it for you or for your beloved ones</p>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-8">
                @forelse(($products ?? []) as $product)
                    <!-- Product Card -->
                    <a href="{{ url('/products/'.$product->id) }}" class="group block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
                        <div class="aspect-[4/3] bg-gray-100 rounded-t-xl overflow-hidden">
                            <img src="{{ $product->photo_url ?? '' }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        </div>
                        <div class="p-4">
                            <h3 class="text-sm md:text-base font-medium text-gray-900 truncate">{{ $product->name }}</h3>
                            <p class="mt-1 text-[#c59d5f] font-semibold">₱{{ number_format($product->price, 2) }}</p>
                        </div>
                    </a>
                @empty
                    @for($i=0;$i<8;$i++)
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                            <div class="aspect-[4/3] bg-gray-100 rounded-t-xl"></div>
                            <div class="p-4 text-center">
                                <h3 class="text-sm md:text-base font-medium text-gray-800 mb-1">Product Name</h3>
                                <p class="text-[#c59d5f] font-semibold">Price</p>
                            </div>
                        </div>
                    @endfor
                @endforelse
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#1a1a1a] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-t" style="border-color:#c59d5f"></div>
            <div class="py-12 grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- Left column -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">About</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Help</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Shipping</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">FAQ</a></li>
                    </ul>
                </div>
                <!-- Right column -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Info</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="{{ url('/custom-orders/create') }}" class="hover:text-[#c59d5f] transition-colors">Custom Order</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Terms &amp; Conditions</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>