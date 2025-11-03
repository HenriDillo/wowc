<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name') }} - @yield('page_title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css'])
    @stack('styles')

    <!-- Scripts -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/js/app.js'])
    @stack('head-scripts')
</head>
<body class="h-full antialiased">
    <!-- Navigation -->
    <nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
        <!-- Primary Navigation Menu -->
        <div class="responsive-container">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-8 h-8">
                            <span class="text-xl font-semibold text-gray-900">{{ config('app.name') }}</span>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex md:items-center md:ml-10 space-x-8">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                            Dashboard
                        </a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'nav-link-active' : '' }}">
                                Users
                            </a>
                        @endif
                        @if(auth()->user()->isEmployee())
                            <a href="{{ route('employee.items') }}" class="nav-link {{ request()->routeIs('employee.items*') ? 'nav-link-active' : '' }}">
                                Items
                            </a>
                            <a href="{{ route('employee.orders') }}" class="nav-link {{ request()->routeIs('employee.orders*') ? 'nav-link-active' : '' }}">
                                Orders
                            </a>
                        @endif
                        <a href="{{ route('products') }}" class="nav-link {{ request()->routeIs('products*') ? 'nav-link-active' : '' }}">
                            Products
                        </a>
                        <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'nav-link-active' : '' }}">
                            Contact
                        </a>
                    </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden md:flex md:items-center md:space-x-6">
                    <a href="{{ route('cart') }}" class="relative nav-link">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        @if(Cart::count() > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ Cart::count() }}
                            </span>
                        @endif
                    </a>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-3">
                            <img src="{{ auth()->user()->avatar_url ?? asset('images/default-avatar.png') }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="w-8 h-8 rounded-full">
                            <span class="text-gray-700">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" 
                             x-transition
                             @click.away="open = false"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Profile
                            </a>
                            @if(auth()->user()->isCustomer())
                                <a href="{{ route('customer.orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    My Orders
                                </a>
                            @endif
                            <div class="border-t border-gray-200 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button @click="open = !open" class="text-gray-500 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="open" x-transition class="md:hidden border-t border-gray-200">
            <div class="space-y-1 px-2 py-3">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-gray-700 hover:text-primary' }}">
                    Dashboard
                </a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.users') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('admin.users*') ? 'text-primary' : 'text-gray-700 hover:text-primary' }}">
                        Users
                    </a>
                @endif
                @if(auth()->user()->isEmployee())
                    <a href="{{ route('employee.items') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('employee.items*') ? 'text-primary' : 'text-gray-700 hover:text-primary' }}">
                        Items
                    </a>
                    <a href="{{ route('employee.orders') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('employee.orders*') ? 'text-primary' : 'text-gray-700 hover:text-primary' }}">
                        Orders
                    </a>
                @endif
                <a href="{{ route('products') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('products*') ? 'text-primary' : 'text-gray-700 hover:text-primary' }}">
                    Products
                </a>
                <a href="{{ route('contact') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('contact') ? 'text-primary' : 'text-gray-700 hover:text-primary' }}">
                    Contact
                </a>

                <div class="border-t border-gray-200 my-3"></div>

                <a href="{{ route('cart') }}" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary">
                    Cart ({{ Cart::count() }})
                </a>
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary">
                    Profile
                </a>
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('customer.orders.index') }}" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary">
                        My Orders
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-base font-medium text-red-600 hover:text-red-700">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="min-h-screen bg-gray-50 flex flex-col">
        {{ $slot }}
    </div>

    <!-- Toast Notifications -->
    <div x-data="{ notifications: [] }"
         @notification.window="notifications.push($event.detail); setTimeout(() => { notifications.shift() }, 3000)"
         class="fixed bottom-4 right-4 z-50 space-y-2">
        <template x-for="(notification, index) in notifications" :key="index">
            <div x-show="notification"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-8"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-8"
                 class="max-w-sm bg-white border border-gray-200 rounded-lg shadow-lg p-4">
                <div class="flex items-start">
                    <!-- Success Icon -->
                    <template x-if="notification.type === 'success'">
                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <!-- Error Icon -->
                    <template x-if="notification.type === 'error'">
                        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </template>
                    <div class="ml-3">
                        <p x-text="notification.message" class="text-sm text-gray-900"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</body>
</html>


