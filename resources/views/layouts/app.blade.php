<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">
    <div class="min-h-screen flex flex-col">

        <!-- Navigation -->
        <nav class="bg-[#c49b6e] text-white shadow sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="h-16 flex items-center justify-between">
                    <!-- Brand -->
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-8 rounded-full bg-white/20 p-0.5">
                        <span class="font-semibold text-lg tracking-wide">Wow Carmen</span>
                    </a>

                    <!-- Right side -->
                    <div class="flex items-center gap-4">
                        <span class="hidden sm:inline text-sm">{{ Auth::user()->name ?? 'User' }}</span>

                        <!-- User menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="h-9 w-9 rounded-full bg-white/20 flex items-center justify-center ring-0 hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/60">
                                <span class="sr-only">Open user menu</span>
                                <span class="text-sm font-semibold">
                                    {{ isset(Auth::user()->name) ? strtoupper(substr(Auth::user()->name,0,1)) : 'U' }}
                                </span>
                            </button>
                            <div x-show="open" @click.outside="open=false" x-cloak x-transition
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? 'User' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Profile</a>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">Log Out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-1 p-6">
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot }}
            @endif
        </main>
    </div>
</body>
</html>


