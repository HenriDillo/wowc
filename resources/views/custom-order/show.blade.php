<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOW Carmen - Custom Order Details</title>
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
                    <a href="{{ route('dashboard') }}" class="text-lg sm:text-xl font-semibold text-gray-900 tracking-wide">WOW Carmen</a>
                </div>

                <!-- Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Home</a>
                    <a href="{{ route('products.index') }}" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Products</a>
                    <a href="{{ route('custom-order') }}" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Custom Order</a>
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
                    <a href="{{ route('cart') }}" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Cart">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m2.6 8L6 5H3m4 8a2 2 0 100 4 2 2 0 000-4z"/></svg>
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
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Home</a>
                    <a href="{{ route('products.index') }}" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Products</a>
                    <a href="{{ route('custom-order') }}" class="block px-3 py-2 text-gray-700 hover:text-[#c59d5f] hover:bg-gray-50 rounded-md text-base font-medium">Custom Order</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <section class="pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('customer.orders.index') }}" class="text-sm text-[#c59d5f] hover:underline">&larr; Back to Orders</a>

            <div class="mt-4 bg-white overflow-hidden border border-gray-100 rounded-xl shadow-sm p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Order Details</h3>
                        <dl class="grid grid-cols-1 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Product Name</dt>
                                <dd class="text-gray-900">{{ $customOrder->custom_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Description</dt>
                                <dd class="text-gray-900">{{ $customOrder->description }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Quantity</dt>
                                <dd class="text-gray-900">{{ $customOrder->quantity }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Status</dt>
                                <dd class="text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($customOrder->status)
                                            @case('pending_review')
                                                bg-yellow-100 text-yellow-800
                                                @break
                                            @case('approved')
                                                bg-green-100 text-green-800
                                                @break
                                            @case('rejected')
                                                bg-red-100 text-red-800
                                                @break
                                            @case('in_production')
                                                bg-blue-100 text-blue-800
                                                @break
                                            @case('completed')
                                                bg-gray-100 text-gray-800
                                                @break
                                        @endswitch
                                    ">
                                        {{ str_replace('_', ' ', ucfirst($customOrder->status)) }}
                                    </span>
                                </dd>
                            </div>
                            @if(!is_null($customOrder->price_estimate))
                            <div>
                                <dt class="text-gray-500">Price Estimate</dt>
                                <dd class="text-gray-900">₱{{ number_format((float)$customOrder->price_estimate, 2) }}</dd>
                            </div>
                            @endif
                            @if($customOrder->estimated_completion_date)
                            <div>
                                <dt class="text-gray-500">Estimated Completion</dt>
                                <dd class="text-gray-900">{{ $customOrder->estimated_completion_date->format('M d, Y') }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Customization Details</h3>
                        <dl class="grid grid-cols-1 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Dimensions</dt>
                                <dd class="text-gray-900">{{ data_get($customOrder->customization_details, 'dimensions', '—') }}</dd>
                            </div>
                            @if(data_get($customOrder->customization_details, 'additional_instructions'))
                            <div>
                                <dt class="text-gray-500">Additional Instructions</dt>
                                <dd class="text-gray-900">{{ data_get($customOrder->customization_details, 'additional_instructions') }}</dd>
                            </div>
                            @endif
                        </dl>

                        @if($customOrder->reference_image_path)
                            <div class="mt-6">
                                <h4 class="text-sm text-gray-500 mb-2">Reference Image</h4>
                                <img src="{{ Storage::url($customOrder->reference_image_path) }}" alt="Reference Image" class="max-w-full h-auto rounded-lg border border-gray-200 shadow-sm">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED && ($customOrder->order?->payment_status !== 'paid'))
            <div class="mt-6 bg-white overflow-hidden border border-gray-100 rounded-xl shadow-sm p-6 md:p-8">
                <h3 class="text-lg font-semibold mb-4">Payment</h3>
                <p class="text-sm text-gray-700">Your custom order has been confirmed. Please complete payment to begin production.</p>
                <div class="mt-4">
                    <a href="{{ route('checkout.page', ['order_id' => $customOrder->order?->id]) }}" class="inline-flex items-center px-4 py-2 rounded-md text-white" style="background:#c59d5f;">Proceed to Checkout</a>
                </div>
            </div>

            @endif
        </div>
    </section>

    <!-- Footer (same as customer pages) -->
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
                        <li><a href="{{ route('custom-order') }}" class="hover:text-[#c59d5f] transition-colors">Custom Order</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Terms &amp; Conditions</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script></script>
</body>
</html>