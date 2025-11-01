<x-app-layout>
    <!-- Page Header -->
    <header class="bg-white border-b">
        <div class="responsive-container py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900">@yield('page_title', 'Dashboard')</h1>
                @yield('page_actions')
            </div>
            @hasSection('page_subtitle')
                <p class="mt-2 text-sm text-gray-600">@yield('page_subtitle')</p>
            @endif
        </div>
    </header>

    <!-- Main Content -->
    <main class="responsive-container py-6">
        <!-- Status Messages -->
        @if(session('status'))
            <div class="alert alert-success" x-data="{ show: true }" x-show="show" x-transition>
                {{ session('status') }}
                <button @click="show = false" class="float-right text-green-800 hover:text-green-900">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error" x-data="{ show: true }" x-show="show" x-transition>
                {{ session('error') }}
                <button @click="show = false" class="float-right text-red-800 hover:text-red-900">&times;</button>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-auto">
        <div class="responsive-container py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <h4 class="text-lg font-semibold mb-4">About</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-primary transition-colors">Help</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-primary transition-colors">Shipping</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-primary transition-colors">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Info</h4>
                    <ul class="space-y-2">
                        <li><a href="/contact" class="text-gray-300 hover:text-primary transition-colors">Contact Us</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-primary transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-primary transition-colors">Terms &amp; Conditions</a></li>
                    </ul>
                </div>
                <div class="md:col-span-2">
                    <h4 class="text-lg font-semibold mb-4">Newsletter</h4>
                    <p class="text-gray-300 mb-4">Subscribe to our newsletter for updates and exclusive offers.</p>
                    <form class="flex gap-2">
                        <input type="email" placeholder="Enter your email" class="form-input bg-gray-800 border-gray-700 text-white placeholder-gray-400 flex-1">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} WOW Carmen. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Modal Container for Dynamic Content -->
    <div x-data="{ show: false, content: '' }" @show-modal.window="show = true; content = $event.detail" x-show="show" x-cloak class="modal-overlay">
        <div class="modal-container" @click.away="show = false">
            <div class="modal-content" x-html="content"></div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div x-data="{ loading: false }" @loading.window="loading = $event.detail" x-show="loading" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg shadow-lg flex items-center space-x-3">
            <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700">Loading...</span>
        </div>
    </div>

    <!-- Scripts -->
    @stack('scripts')
</x-app-layout>