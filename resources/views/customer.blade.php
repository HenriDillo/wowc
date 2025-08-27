<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false }" class="bg-gray-50">

    <!-- Header -->
    <header class="bg-[#c49b6e] text-white p-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Welcome, {{ Auth::user()->name }}</h1>
        
        <div class="relative">
            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 text-white hover:text-gray-200">
                <span>Account</span>
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-[#c49b6e] font-bold">
                    {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                </div>
            </button>
            
            <div 
                x-show="dropdownOpen" 
                x-cloak 
                x-transition 
                @click.outside="dropdownOpen=false" 
                class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded-lg shadow-lg border border-gray-200 z-50"
            >
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100 rounded-lg">Profile</a>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-red-50 text-red-600 rounded-lg">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="p-6">
        <div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Customer Dashboard</h2>
            <p>Welcome to the customer portal. Here you can browse products, manage your orders, and view your account information.</p>
            <div class="mt-6 flex space-x-4">
                <a href="#" class="px-4 py-2 bg-[#c49b6e] text-white rounded-lg hover:bg-[#b08a5c]">Browse Products</a>
                <a href="#" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">My Orders</a>
            </div>
        </div>
    </main>

</body>
</html>
