<x-guest-layout>
    <div class="min-h-screen w-full flex flex-col lg:flex-row">

        <!-- ========== LEFT PANEL (Image + Message) ========== -->
        <div class="w-full lg:w-1/2 hidden lg:flex items-center justify-center bg-cover bg-center relative"
             style="background-image: url('{{ asset('images/login-bg.jpg') }}');">

            <div class="absolute inset-0 bg-[#A9793E] bg-opacity-50"></div>

            <div class="relative z-10 text-white text-center px-10">
                <h2 class="text-4xl font-bold mb-4">Join WOW Carmen</h2>
                <p class="text-white text-opacity-90">
                    Create an account to explore our handcrafted products.
                </p>
            </div>
        </div>

        <!-- ========== RIGHT PANEL (Register Form) ========== -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md space-y-6">

                <h2 class="text-3xl font-semibold text-[#1F1F1F]">Create Account</h2>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                                   placeholder="First Name">
                            @error('first_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                                   placeholder="Last Name">
                            @error('last_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Address Line -->
                    <div>
                        <label for="address_line" class="block text-sm font-medium text-gray-700">Address</label>
                        <input id="address_line" type="text" name="address_line" value="{{ old('address_line') }}" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="Street / Barangay">
                        @error('address_line')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City / Province / Postal -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                            <input id="city" type="text" name="city" value="{{ old('city') }}" required
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]">
                        </div>
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700">Province</label>
                            <input id="province" type="text" name="province" value="{{ old('province') }}" required
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]">
                        </div>
                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                            <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code') }}" required
                                   class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]">
                        </div>
                    </div>

                    <!-- Contact Number (kept) -->
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number') }}" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                        @error('contact_number')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="Email">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" type="password" name="password" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="Password">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="w-full px-4 py-3 border rounded-full shadow-sm focus:ring-[#A9793E] focus:border-[#A9793E]"
                               placeholder="Confirm Password">
                    </div>

                    <!-- Submit -->
                    <div>
                        <button type="submit"
                                class="w-full py-3 bg-[#A9793E] hover:bg-[#8F6532] text-white font-semibold rounded-full transition">
                            Register
                        </button>
                    </div>

                    <!-- Already have an account -->
                    <p class="text-sm text-center text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-[#A9793E] hover:underline">
                            Sign In
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
