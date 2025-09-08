<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wow Carmen Handicrafts - Customer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false, mobileMenuOpen: false }" class="bg-white">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-[#c49b6e] rounded-full mr-3"></div>
                    <span class="text-xl font-semibold text-gray-800">Wow Carmen</span>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#" class="text-gray-700 hover:text-[#c49b6e] text-sm font-medium transition-colors">Home</a>
                    <a href="#products" class="text-gray-700 hover:text-[#c49b6e] text-sm font-medium transition-colors">Products</a>
                    <a href="#" class="text-gray-700 hover:text-[#c49b6e] text-sm font-medium transition-colors">Contact us</a>
                </div>

                <!-- Desktop Right Side -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- User Dropdown -->
                    <div class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 text-gray-700 hover:text-[#c49b6e] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </button>
                        
                        <div 
                            x-show="dropdownOpen" 
                            x-cloak 
                            x-transition 
                            @click.outside="dropdownOpen=false" 
                            class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded-lg shadow-lg border border-gray-200 z-50"
                        >
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">Welcome back!</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100 text-sm">Profile</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 text-sm">My Orders</a>
                            <div class="border-t border-gray-100"></div>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-red-50 text-red-600 rounded-b-lg text-sm">Logout</button>
                            </form>
                        </div>
                    </div>

                    <!-- Cart Icon -->
                    <button class="text-gray-700 hover:text-[#c49b6e] p-1 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m2.6 8L6 5H3m4 8a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-[#c49b6e] p-2">
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
                    <a href="#" class="block px-3 py-2 text-gray-700 hover:text-[#c49b6e] hover:bg-gray-50 rounded-md text-base font-medium">Home</a>
                    <a href="#products" class="block px-3 py-2 text-gray-700 hover:text-[#c49b6e] hover:bg-gray-50 rounded-md text-base font-medium">Products</a>
                    <a href="#" class="block px-3 py-2 text-gray-700 hover:text-[#c49b6e] hover:bg-gray-50 rounded-md text-base font-medium">Contact us</a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-5">
                        <div class="w-10 h-10 bg-[#c49b6e] rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                        </div>
                    </div>
                    <div class="mt-3 px-2 space-y-1">
                        <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">Profile</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">My Orders</a>
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
    <section class="bg-gray-50 py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-6xl font-bold text-gray-800 mb-8">Wow Carmen Handicrafts</h1>
            <button class="bg-[#c49b6e] text-white px-8 py-3 rounded text-sm font-medium hover:bg-[#b08a5c] transition-colors">
                Shop Now
            </button>
        </div>
    </section>



    <!-- Featured Products Section -->
    <section id="products" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-3">Featured Products</h2>
                <p class="text-gray-600">Order it for you or for your beloved ones</p>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Row 1 -->
                <div class="text-center">
                    <div class="bg-gray-100 h-48 mb-4 rounded"></div>
                    <h3 class="font-medium text-gray-800 mb-2">Product Name</h3>
                    <p class="text-[#c49b6e] font-medium">Price</p>
                </div>

                <div class="text-center">
                    <div class="bg-gray-100 h-48 mb-4 rounded"></div>
                    <h3 class="font-medium text-gray-800 mb-2">Product Name</h3>
                    <p class="text-[#c49b6e] font-medium">Price</p>
                </div>

                <div class="text-center">
                    <div class="bg-gray-100 h-48 mb-4 rounded"></div>
                    <h3 class="font-medium text-gray-800 mb-2">Product Name</h3>
                    <p class="text-[#c49b6e] font-medium">Price</p>
                </div>

                <div class="text-center">
                    <div class="bg-gray-100 h-48 mb-4 rounded"></div>
                    <h3 class="font-medium text-gray-800 mb-2">Product Name</h3>
                    <p class="text-[#c49b6e] font-medium">Price</p>
                </div>

                <!-- Row 2 -->
                <div class="text-center">
                    <div class="bg-gray-100 h-48 mb-4 rounded"></div>
                    <h3 class="font-medium text-gray-800 mb-2">Product Name</h3>
                    <p class="text-[#c49b6e] font-medium">Price</p>
                </div>

                <div class="text-center">
                    <div class="bg-gray-100 h-48 mb-4 rounded"></div>
                    <h3 class="font-medium text-gray-800 mb-2">Product Name</h3>
                    <p class="text-[#c49b6e] font-medium">Price</p>
                </div>

                <div class="text-center">
                    <div class="bg-gray-100 h-48 mb-4 rounded"></div>
                    <h3 class="font-medium text-gray-800 mb-2">Product Name</h3>
                    <p class="text-[#c49b6e] font-medium">Price</p>
                </div>

                <div class="text-center">
                    <div class="bg-gray-100 h-48 mb-4 rounded"></div>
                    <h3 class="font-medium text-gray-800 mb-2">Product Name</h3>
                    <p class="text-[#c49b6e] font-medium">Price</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-md mx-auto md:max-w-none">
                <!-- About Column -->
                <div>
                    <h4 class="text-lg font-medium mb-4">About</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-white transition-colors">Help</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Shipping</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                    </ul>
                </div>

                <!-- Info Column -->
                <div>
                    <h4 class="text-lg font-medium mb-4">Info</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policies</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms & Conditions</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>