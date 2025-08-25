<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <header class="bg-[#c49b6e] text-white p-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Welcome, {{ Auth::user()->name }}</h1>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="bg-white text-[#c49b6e] px-4 py-2 rounded-lg hover:bg-gray-100">Logout</button>
        </form>
    </header>

    <main class="p-6">
        <div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Customer Dashboard</h2>
            <p>Welcome to the customer portal. Here you can browse products, manage your orders, and view your account information.</p>
            <div class="mt-6">
                <a href="#" class="px-4 py-2 bg-[#c49b6e] text-white rounded-lg hover:bg-[#b08a5c]">Browse Products</a>
                <a href="#" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">My Orders</a>
            </div>
        </div>
    </main>
</body>
</html>
