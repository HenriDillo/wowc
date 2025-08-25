<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <nav class="bg-[#c49b6e] text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-white/20 rounded-full"></div>
            <span class="font-bold text-lg">Wow Carmen</span>
        </div>
        <div class="flex items-center space-x-4">
            <span>Welcome, {{ Auth::user()->name ?? 'Employee' }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-white text-[#c49b6e] font-semibold rounded hover:bg-gray-100 transition">
                    Log Out
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Employee Dashboard</h1>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Orders Card -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                <h2 class="text-lg font-semibold mb-2">Orders</h2>
                <p class="text-gray-600">View and manage incoming customer orders.</p>
                <a href="#" class="mt-4 inline-block text-[#c49b6e] font-medium hover:underline">Go to Orders</a>
            </div>

            <!-- Inventory Card -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                <h2 class="text-lg font-semibold mb-2">Inventory</h2>
                <p class="text-gray-600">Check current stock and update quantities.</p>
                <a href="#" class="mt-4 inline-block text-[#c49b6e] font-medium hover:underline">Go to Inventory</a>
            </div>

            <!-- Profile Card -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                <h2 class="text-lg font-semibold mb-2">Profile</h2>
                <p class="text-gray-600">View or edit your personal information.</p>
                <a href="#" class="mt-4 inline-block text-[#c49b6e] font-medium hover:underline">Go to Profile</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-100 text-gray-600 text-center py-4 mt-auto">
        &copy; {{ date('Y') }} Wow Carmen. All rights reserved.
    </footer>

</body>
</html>
