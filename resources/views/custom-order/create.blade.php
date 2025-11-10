<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOW Carmen - Custom Order</title>
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
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-5">
                        <div class="w-10 h-10 bg-[#c59d5f] rounded-full flex items-center justify-center text-white font-bold">
                            {{ Auth::check() ? strtoupper(substr(Auth::user()->name,0,1)) : 'G' }}
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name ?? 'Guest' }}</div>
                        </div>
                    </div>
                    <div class="mt-3 px-2 space-y-1">
                        @auth
                            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">Profile</a>
                            <a href="{{ route('customer.orders.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">My Orders</a>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-red-600 hover:bg-red-50 rounded-md text-base font-medium">Logout</button>
                            </form>
                        @else
                            <a href="/login" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">Login</a>
                            <a href="/register" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">Register</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <section class="pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden border border-gray-100 rounded-xl shadow-sm">
                <div class="p-6 md:p-8 text-gray-900">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Custom Order Request</h1>
                    <form id="customOrderForm" method="POST" action="{{ route('custom-orders.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label for="custom_name" class="block text-sm font-medium text-gray-700">Product Name/Title</label>
                            <input id="custom_name" name="custom_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" value="{{ old('custom_name') }}" required autofocus />
                            <x-input-error :messages="$errors->get('custom_name')" class="mt-2" />
                            <p id="custom_name_error" class="mt-2 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description/Special Instructions</label>
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            <p id="description_error" class="mt-2 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="dimensions" class="block text-sm font-medium text-gray-700">Dimensions</label>
                            <input id="dimensions" name="customization_details[dimensions]" type="text" placeholder="e.g., 30cm x 20cm x 15cm" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" value="{{ old('customization_details.dimensions') }}" required />
                            <x-input-error :messages="$errors->get('customization_details.dimensions')" class="mt-2" />
                            <p id="dimensions_error" class="mt-2 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" value="{{ old('quantity', 1) }}" required />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                            <p id="quantity_error" class="mt-2 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="reference_image" class="block text-sm font-medium text-gray-700">Reference Image</label>
                            <input type="file" id="reference_image" name="reference_image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required accept=".jpg,.jpeg,.png" />
                            <p class="mt-1 text-sm text-gray-500">Upload a reference image (JPG/PNG up to 5MB)</p>
                            <x-input-error :messages="$errors->get('reference_image')" class="mt-2" />
                            <p id="reference_image_error" class="mt-2 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="additional_instructions" class="block text-sm font-medium text-gray-700">Additional Instructions</label>
                            <textarea id="additional_instructions" name="customization_details[additional_instructions]" rows="2" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]">{{ old('customization_details.additional_instructions') }}</textarea>
                            <x-input-error :messages="$errors->get('customization_details.additional_instructions')" class="mt-2" />
                        </div>

                        <div id="imagePreview" class="mt-4"></div>

                        <div class="mt-6 flex items-center justify-end gap-x-6">
                            <a href="{{ route('products.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                            <button type="submit" class="rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" style="background:#c59d5f;">
                                Submit Custom Order
                            </button>
                        </div>
                        <script>
                        (function(){
                            const form = document.getElementById('customOrderForm');
                            const refInput = document.getElementById('reference_image');
                            const preview = document.getElementById('imagePreview');
                            const MAX_MB = 5;
                            const MAX_BYTES = MAX_MB * 1024 * 1024;
                            const ALLOWED = ['image/jpeg','image/png','image/jpg'];

                            function setError(id, message){
                                const el = document.getElementById(id);
                                if(el){ el.textContent = message || ''; }
                            }

                            refInput.addEventListener('change', function(event) {
                                const input = event.target;
                                preview.innerHTML = '';
                                setError('reference_image_error', '');
                                if (input.files && input.files[0]) {
                                    const file = input.files[0];
                                    if(!ALLOWED.includes(file.type)){
                                        setError('reference_image_error', 'Please upload a JPG or PNG image.');
                                        input.value = '';
                                        return;
                                    }
                                    if(file.size > MAX_BYTES){
                                        setError('reference_image_error', 'Image must be less than ' + MAX_MB + 'MB.');
                                        input.value = '';
                                        return;
                                    }
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        const img = document.createElement('img');
                                        img.src = e.target.result;
                                        img.className = 'max-h-40 mt-2 rounded shadow';
                                        preview.appendChild(img);
                                    };
                                    reader.readAsDataURL(file);
                                }
                            });

                            form.addEventListener('submit', function(e){
                                let hasError = false;
                                setError('custom_name_error','');
                                setError('description_error','');
                                setError('dimensions_error','');
                                setError('quantity_error','');
                                setError('reference_image_error','');

                                const name = document.getElementById('custom_name').value.trim();
                                const desc = document.getElementById('description').value.trim();
                                const dims = document.getElementById('dimensions').value.trim();
                                const qty = parseInt(document.getElementById('quantity').value, 10);
                                const file = refInput.files[0];

                                if(!name){ setError('custom_name_error','Product name is required.'); hasError = true; }
                                if(!desc){ setError('description_error','Description is required.'); hasError = true; }
                                if(!dims){ setError('dimensions_error','Dimensions are required.'); hasError = true; }
                                if(!qty || qty < 1){ setError('quantity_error','Quantity must be at least 1.'); hasError = true; }
                                if(!file){
                                    setError('reference_image_error','Reference image is required.');
                                    hasError = true;
                                } else {
                                    if(!ALLOWED.includes(file.type)){
                                        setError('reference_image_error','Please upload a JPG or PNG image.');
                                        hasError = true;
                                    }
                                    if(file.size > MAX_BYTES){
                                        setError('reference_image_error','Image must be less than ' + MAX_MB + 'MB.');
                                        hasError = true;
                                    }
                                }

                                if(hasError){ e.preventDefault(); }
                            });
                        })();
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer (same as customer products) -->
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

</body>
</html>