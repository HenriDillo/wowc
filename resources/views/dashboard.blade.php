<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Employee Dashboard' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{ display: none !important; }</style>
</head>
<body x-data="{ dropdownOpen: false }">

<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="w-64 bg-[#c49b6e] flex flex-col shadow-lg">
        <div class="p-6 border-b border-[#b08a5c]">
            <span class="text-white font-semibold text-lg">Wow Carmen</span>
        </div>
        <nav class="flex-1 p-4 space-y-1">
            <div class="flex items-center space-x-3 p-3 text-white bg-[#b08a5c] rounded-lg">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2l8 6v9a1 1 0 01-1 1h-5v-5H8v5H3a1 1 0 01-1-1V8l8-6z"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </div>
            <a href="{{ route('employee.raw-materials') }}" class="flex items-center space-x-3 p-3 text-white hover:bg-[#b08a5c] rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3h12a1 1 0 011 1v3H3V4a1 1 0 011-1z"></path>
                    <path d="M3 8h14v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"></path>
                </svg>
                <span class="font-medium">Raw Materials</span>
            </a>
            <a href="{{ route('employee.items') }}" class="flex items-center space-x-3 p-3 text-white hover:bg-[#b08a5c] rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 3a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1V4a1 1 0 00-1-1H6z"></path>
                </svg>
                <span class="font-medium">Item Management</span>
            </a>
            <a href="{{ route('admin.stock.index') }}" class="flex items-center space-x-3 p-3 text-white hover:bg-[#b08a5c] rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3h12a1 1 0 011 1v3H3V4a1 1 0 011-1z"></path>
                    <path d="M3 8h14v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"></path>
                </svg>
                <span class="font-medium">Stock Management</span>
            </a>
            <a href="{{ route('employee.orders') }}" class="flex items-center space-x-3 p-3 text-white hover:bg-[#b08a5c] rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 3h14a1 1 0 011 1v3H2V4a1 1 0 011-1z"></path>
                    <path d="M2 8h16v8a1 1 0 01-1 1H3a1 1 0 01-1-1V8z"></path>
                </svg>
                <span class="font-medium">Order Management</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 bg-white">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800">Employee Dashboard</h1>
            <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800">
                    <span>Hello, {{ Auth::user()->name ?? 'Employee' }}</span>
                    <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                </button>
                <div x-show="dropdownOpen" x-cloak x-transition @click.outside="dropdownOpen=false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        Profile
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">Log Out</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="p-6 space-y-10">
            <!-- Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('employee.raw-materials') }}" class="block bg-white border rounded-xl shadow-sm hover:shadow transition">
                    <div class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white" style="background:#c49b6e;">RM</div>
                        <div>
                            <div class="text-sm text-gray-600">Total Raw Materials</div>
                            <div class="text-2xl font-semibold text-gray-900">{{ $totalRawMaterials ?? 0 }}</div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('employee.items') }}" class="block bg-white border rounded-xl shadow-sm hover:shadow transition">
                    <div class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white bg-blue-600">IT</div>
                        <div>
                            <div class="text-sm text-gray-600">Total Items</div>
                            <div class="text-2xl font-semibold text-gray-900">{{ $totalItems ?? 0 }}</div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.stock.index') }}" class="block bg-white border rounded-xl shadow-sm hover:shadow transition">
                    <div class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white bg-green-600">ST</div>
                        <div>
                            <div class="text-sm text-gray-600">Total Stock (Items/Materials)</div>
                            <div class="text-2xl font-semibold text-gray-900">{{ ($totalItemStock ?? 0) + ($totalMaterialStock ?? 0) }}</div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('employee.orders') }}" class="block bg-white border rounded-xl shadow-sm hover:shadow transition">
                    <div class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white bg-indigo-600">OR</div>
                        <div>
                            <div class="text-sm text-gray-600">Total Orders</div>
                            <div class="text-2xl font-semibold text-gray-900">{{ $totalOrders ?? 0 }}</div>
                            <div class="mt-1 text-xs text-gray-600">Pending: {{ $ordersPending ?? 0 }} • Completed: {{ $ordersCompleted ?? 0 }} • Cancelled: {{ $ordersCancelled ?? 0 }}</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="bg-white border rounded-xl shadow-sm">
                    <div class="px-4 py-3 border-b bg-gray-50 font-medium">Recent Raw Materials</div>
                    <div class="p-4 divide-y">
                        @forelse(($recentMaterials ?? []) as $mat)
                            <div class="py-2 text-sm flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $mat->name }}</div>
                                    <div class="text-xs text-gray-500">Added {{ $mat->created_at?->diffForHumans() }}</div>
                                </div>
                                <a href="{{ route('employee.raw-materials') }}" class="text-[#c49b6e] text-xs">Manage</a>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No recent materials</div>
                        @endforelse
                    </div>
                </div>
                <div class="bg-white border rounded-xl shadow-sm">
                    <div class="px-4 py-3 border-b bg-gray-50 font-medium">Recent Items</div>
                    <div class="p-4 divide-y">
                        @forelse(($recentItems ?? []) as $it)
                            <div class="py-2 text-sm flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $it->name }}</div>
                                    <div class="text-xs text-gray-500">Added {{ $it->created_at?->diffForHumans() }}</div>
                                </div>
                                <a href="{{ route('employee.items') }}" class="text-[#c49b6e] text-xs">Manage</a>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No recent items</div>
                        @endforelse
                    </div>
                </div>
                <div class="bg-white border rounded-xl shadow-sm">
                    <div class="px-4 py-3 border-b bg-gray-50 font-medium">Latest Orders</div>
                    <div class="p-4 divide-y">
                        @forelse(($recentOrders ?? []) as $ord)
                            <div class="py-2 text-sm flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">Order #{{ $ord->id }}</div>
                                    <div class="text-xs text-gray-500 capitalize">{{ $ord->status }} • {{ $ord->created_at?->diffForHumans() }}</div>
                                </div>
                                <a href="{{ route('employee.orders.show', $ord->id) }}" class="text-[#c49b6e] text-xs">View</a>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No recent orders</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>