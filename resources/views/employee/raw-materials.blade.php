<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raw Materials</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak]{ display: none !important; }</style>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" href="/favicon.ico" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 text-gray-800">

<div class="min-h-screen">
    <div class="px-6 py-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Raw Materials</h1>
        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">Back to Dashboard</a>
    </div>

    <div class="px-6">
        <div class="flex items-center justify-between mb-4">
            <button class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-[#c49b6e] text-white hover:bg-[#b08a5c]">
                <span class="mr-2">+</span> Add Material
            </button>
        </div>
        <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($materials as $m)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $m['name'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $m['stock'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">₱{{ number_format($m['price'], 2) }}</td>
                        <td class="px-4 py-3 text-sm">
                            <a href="#" class="text-blue-600 hover:underline mr-3">Edit</a>
                            <a href="#" class="text-rose-600 hover:underline">Delete</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>


