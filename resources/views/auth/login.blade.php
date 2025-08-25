<x-guest-layout>
    <!-- Full-screen container with responsive layout -->
    <div class="min-h-screen w-full flex flex-col lg:flex-row">

        <!-- ========== LEFT PANEL (Background + Welcome Text) ========== -->
        <div class="w-full lg:w-1/2 hidden lg:flex items-center justify-center bg-cover bg-center relative"
             style="background-image: url('{{ asset('images/register-bg.jpg') }}');">

            <!-- Optional overlay for readability -->
            <div class="absolute inset-0 bg-[#A9793E] bg-opacity-50"></div>

            <div class="relative z-10 text-white text-center px-10">
                <h2 class="text-4xl font-bold mb-4">Welcome back!</h2>
                <p class="text-white text-opacity-90">
                    You can sign in to access your existing account.
                </p>
            </div>
        </div>

        <!-- ========== RIGHT PANEL (Login Form) ========== -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md space-y-6">

                <!-- Heading -->
                <h2 class="text-3xl font-semibold text-[#1F1F1F]">Sign In</h2>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" type="email" name="email" required autofocus
                               value="{{ old('email') }}"
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

                    <!-- Remember Me + Forgot Password -->
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="remember" class="rounded border-gray-300">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div>
                        <button type="submit"
                                class="w-full py-3 bg-[#A9793E] hover:bg-[#8F6532] text-white font-semibold rounded-full transition">
                            Sign In
                        </button>
                    </div>

                    <!-- Register Link -->
                    <p class="text-sm text-center text-gray-600">
                        New here?
                        <a href="{{ route('register') }}" class="text-[#A9793E] hover:underline">
                            Create an Account
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
