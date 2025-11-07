<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">
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
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">Profile</a>
                            <a href="{{ route('customer.orders.index') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">My Orders</a>
                            <div class="border-t border-gray-100"></div>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-b-lg text-sm">Logout</button>
                            </form>
                        </div>
                    </div>
                    <a href="/cart" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Cart">
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

    <section class="pt-24 pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">My Orders</h1>
            <a href="/products" class="px-4 py-2 rounded-md bg-[#c59d5f] text-white hover:opacity-90">Continue Shopping</a>
        </div>

        @if(!empty($backOrders) && $backOrders->isNotEmpty())
            <div class="mb-6 bg-white border border-blue-50 rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-medium text-gray-900">Your Back Orders</h2>
                <p class="text-sm text-blue-700 mt-1">Items awaiting stock or fulfillment. We'll notify you when they're ready.</p>
                <div class="mt-3 divide-y">
                    @foreach($backOrders as $bo)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="{{ $bo->item?->photo_url }}" class="w-12 h-12 rounded object-cover bg-gray-100"/>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $bo->item?->name }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ $bo->quantity }} • Status: {{ $bo->backorder_status }}</div>
                                    @if($bo->order?->expected_restock_date)
                                        <div class="text-xs text-blue-700">Expected: {{ $bo->order->expected_restock_date->format('M d, Y') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-gray-700">Order #{{ $bo->order->id }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!empty($customOrders) && $customOrders->isNotEmpty())
            <div class="mb-6 bg-white border border-yellow-50 rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-medium text-gray-900">Your Custom Orders</h2>
                <p class="text-sm text-yellow-700 mt-1">Custom order requests currently under review or in production.</p>
                <div class="mt-3 divide-y">
                    @foreach($customOrders as $co)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if($co->reference_image_path)
                                    <img src="{{ Storage::url($co->reference_image_path) }}" class="w-12 h-12 rounded object-cover bg-gray-100"/>
                                @else
                                    <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $co->custom_name }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ $co->quantity }} • Status: {{ ucfirst(str_replace('_', ' ', $co->status)) }}</div>
                                    @if($co->price_estimate)
                                        <div class="text-xs text-yellow-700">Estimated Price: ₱{{ number_format($co->price_estimate, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <div class="text-sm text-gray-700">Order #{{ $co->order->id }}</div>
                                <a href="{{ route('custom-orders.show', $co->id) }}" class="text-xs text-yellow-700 hover:underline">View Details</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $order->created_at?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ $order->order_type }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ $order->status }}</td>
                            <td class="px-4 py-3 text-sm font-medium">₱{{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('customer.orders.show', $order->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $orders->links() }}</div>
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


