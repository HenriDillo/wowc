<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{ display: none !important; }</style>
</head>
<body x-data="{ dropdownOpen: false, activeTab: 'sales' }">

<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="w-64 bg-[#c49b6e] flex flex-col shadow-lg">
        <div class="p-6 border-b border-[#b08a5c]">
            <span class="text-white font-semibold text-lg">Wow Carmen</span>
        </div>
        <nav class="flex-1 p-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('dashboard') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2l8 6v9a1 1 0 01-1 1h-5v-5H8v5H3a1 1 0 01-1-1V8l8-6z"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="{{ route('employee.raw-materials') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.raw-materials') || request()->routeIs('employee.materials.*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3h12a1 1 0 011 1v3H3V4a1 1 0 011-1z"></path>
                    <path d="M3 8h14v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"></path>
                </svg>
                <span class="font-medium">Raw Materials</span>
            </a>
            <a href="{{ route('employee.items') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.items*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 3a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1V4a1 1 0 00-1-1H6z"></path>
                </svg>
                <span class="font-medium">Production</span>
            </a>
            <a href="{{ route('employee.orders') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.orders*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 3h14a1 1 0 011 1v3H2V4a1 1 0 011-1z"></path>
                    <path d="M2 8h16v8a1 1 0 01-1 1H3a1 1 0 01-1-1V8z"></path>
                </svg>
                <span class="font-medium">Orders</span>
            </a>
            <a href="{{ route('employee.reports') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.reports*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                </svg>
                <span class="font-medium">Reports</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 bg-white">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800">Reports</h1>
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

        <!-- Report Content -->
        <div class="p-6 space-y-6">
            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-200 space-x-1">
                <button @click="activeTab = 'sales'" :class="activeTab === 'sales' ? 'border-b-2 border-[#c49b6e] text-[#c49b6e]' : 'text-gray-600'" class="px-4 py-3 font-medium transition-colors">
                    📊 Sales Report
                </button>
                <button @click="activeTab = 'inventory'" :class="activeTab === 'inventory' ? 'border-b-2 border-[#c49b6e] text-[#c49b6e]' : 'text-gray-600'" class="px-4 py-3 font-medium transition-colors">
                    📦 Inventory Report
                </button>
                <button @click="activeTab = 'calendar'" :class="activeTab === 'calendar' ? 'border-b-2 border-[#c49b6e] text-[#c49b6e]' : 'text-gray-600'" class="px-4 py-3 font-medium transition-colors">
                    📅 Calendar
                </button>
            </div>

            <!-- SALES REPORT SECTION -->
            <div x-show="activeTab === 'sales'" x-transition class="space-y-6">
                <div class="bg-white border rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4">Sales Filters</h2>
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                            <select name="period" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-[#c49b6e]">
                                <option value="day" {{ request('period') == 'day' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('period') == 'month' ? 'selected' : '' }} selected>This Month</option>
                                <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                        @if(request('period') == 'custom')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" name="custom_start" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="{{ request('custom_start') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input type="date" name="custom_end" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="{{ request('custom_end') }}">
                            </div>
                        @endif
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-[#c49b6e] text-white rounded-lg hover:bg-[#b08a5c] transition">Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Sales Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6">
                        <div class="text-sm font-medium text-blue-600 mb-1">Total Sales</div>
                        <div class="text-3xl font-bold text-blue-900">₱{{ number_format($totalSales ?? 0, 2) }}</div>
                        <div class="text-xs text-blue-700 mt-2">From {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-6">
                        <div class="text-sm font-medium text-green-600 mb-1">Total Orders</div>
                        <div class="text-3xl font-bold text-green-900">{{ $totalOrders ?? 0 }}</div>
                        <div class="text-xs text-green-700 mt-2">Completed Orders</div>
                    </div>
                </div>

                <!-- Order Type Breakdown -->
                <div class="bg-white border rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Orders by Type</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($orderTypeBreakdown ?? [] as $type)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm text-gray-600 capitalize">{{ $type->order_type ?? 'N/A' }}</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $type->count ?? 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">₱{{ number_format($type->total ?? 0, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Payment Method Breakdown -->
                <div class="bg-white border rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Orders by Payment Method</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($paymentMethodBreakdown ?? [] as $method)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $method->payment_method ?? 'N/A') }}</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $method->count ?? 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">₱{{ number_format($method->total ?? 0, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white border rounded-xl shadow-sm p-6 overflow-x-auto">
                    <h3 class="text-lg font-semibold mb-4">Top Products</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Product</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-700">Qty Sold</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-700">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productSales ?? [] as $product)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-900">{{ $product->name }}</td>
                                    <td class="text-right py-3 px-4 text-gray-700">{{ $product->total_quantity }}</td>
                                    <td class="text-right py-3 px-4 font-medium text-gray-900">₱{{ number_format($product->total_sales, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-gray-500">No sales data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Daily Sales Trend -->
                <div class="bg-white border rounded-xl shadow-sm p-6 overflow-x-auto">
                    <h3 class="text-lg font-semibold mb-4">Daily Sales Trend</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-700">Orders</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-700">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailySales ?? [] as $day)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-900">{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                                    <td class="text-right py-3 px-4 text-gray-700">{{ $day->orders }}</td>
                                    <td class="text-right py-3 px-4 font-medium text-gray-900">₱{{ number_format($day->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-gray-500">No sales data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INVENTORY REPORT SECTION -->
            <div x-show="activeTab === 'inventory'" x-transition class="space-y-6">
                <!-- Low Stock Alert -->
                @php
                    $totalLowStock = (($lowStockMaterials ?? collect())->count() + ($lowStockItems ?? collect())->count());
                @endphp
                @if($totalLowStock > 0)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">⚠️</span>
                            <div>
                                <h3 class="font-semibold text-red-900">Low Stock Alert</h3>
                                <p class="text-sm text-red-700 mt-1">{{ $totalLowStock }} items below reorder level</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Production Items Stock -->
                <div class="bg-white border rounded-xl shadow-sm p-6 overflow-x-auto">
                    <h3 class="text-lg font-semibold mb-4">Production Items Stock</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Item Name</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-700">Current Stock</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-700">Reorder Level</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($itemsInventory ?? [] as $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-900">{{ $item->name }}</td>
                                    <td class="text-right py-3 px-4 text-gray-700">{{ $item->stock }}</td>
                                    <td class="text-right py-3 px-4 text-gray-700">{{ $item->reorder_level ?? 'N/A' }}</td>
                                    <td class="text-center py-3 px-4">
                                        @if($item->stock <= ($item->reorder_level ?? 0))
                                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Low Stock</span>
                                        @elseif($item->stock <= (($item->reorder_level ?? 0) * 1.5))
                                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Moderate</span>
                                        @else
                                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Healthy</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-gray-500">No items</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Raw Materials Stock -->
                <div class="bg-white border rounded-xl shadow-sm p-6 overflow-x-auto">
                    <h3 class="text-lg font-semibold mb-4">Raw Materials Stock</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Material Name</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-700">Current Stock</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-700">Reorder Level</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($materialsInventory ?? [] as $material)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-900">{{ $material->name }}</td>
                                    <td class="text-right py-3 px-4 text-gray-700">{{ $material->stock }}</td>
                                    <td class="text-right py-3 px-4 text-gray-700">{{ $material->reorder_level ?? 'N/A' }}</td>
                                    <td class="text-center py-3 px-4">
                                        @if($material->stock <= ($material->reorder_level ?? 0))
                                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Low Stock</span>
                                        @elseif($material->stock <= (($material->reorder_level ?? 0) * 1.5))
                                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Moderate</span>
                                        @else
                                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Healthy</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-gray-500">No materials</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Recent Stock Movements -->
                <div class="bg-white border rounded-xl shadow-sm p-6 overflow-x-auto">
                    <h3 class="text-lg font-semibold mb-4">Recent Stock Movements</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Item/Material</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Type</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-700">Quantity</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Remarks</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMovements ?? [] as $movement)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-900 font-medium">{{ $movement->name }}</td>
                                    <td class="text-center py-3 px-4">
                                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                            @if($movement->type === 'stock_in' || $movement->type === 'in')
                                                bg-green-100 text-green-800
                                            @elseif($movement->type === 'stock_out' || $movement->type === 'out')
                                                bg-red-100 text-red-800
                                            @else
                                                bg-gray-100 text-gray-800
                                            @endif
                                        ">
                                            {{ str_replace('_', ' ', ucfirst($movement->type)) }}
                                        </span>
                                    </td>
                                    <td class="text-right py-3 px-4 text-gray-700 font-medium">{{ $movement->quantity }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $movement->remarks ?? '-' }}</td>
                                    <td class="py-3 px-4 text-gray-500">{{ $movement->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">No movements recorded</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CALENDAR SECTION -->
            <div x-show="activeTab === 'calendar'" x-transition class="space-y-6">
                <div x-data="{ calendarMonth: {{ $month ?? now()->month }}, calendarYear: {{ $year ?? now()->year }} }">
                    <div class="bg-white border rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-gray-900" x-text="`${new Date(calendarYear, calendarMonth - 1).toLocaleString('default', { month: 'long', year: 'numeric' })}`"></h2>
                            <div class="flex gap-2">
                                <button @click="calendarMonth = calendarMonth === 1 ? 12 : calendarMonth - 1; if(calendarMonth === 12) calendarYear--" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                <a href="?tab=calendar&month={{ now()->month }}&year={{ now()->year }}" class="px-3 py-1 text-xs font-medium text-[#c49b6e] hover:bg-amber-50 rounded-lg">Today</a>
                                <button @click="calendarMonth = calendarMonth === 12 ? 1 : calendarMonth + 1; if(calendarMonth === 1) calendarYear++" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-1 mb-4">
                            <div class="text-center text-xs font-semibold text-gray-600 py-2">Sun</div>
                            <div class="text-center text-xs font-semibold text-gray-600 py-2">Mon</div>
                            <div class="text-center text-xs font-semibold text-gray-600 py-2">Tue</div>
                            <div class="text-center text-xs font-semibold text-gray-600 py-2">Wed</div>
                            <div class="text-center text-xs font-semibold text-gray-600 py-2">Thu</div>
                            <div class="text-center text-xs font-semibold text-gray-600 py-2">Fri</div>
                            <div class="text-center text-xs font-semibold text-gray-600 py-2">Sat</div>
                        </div>

                        @php
                            $calendarMonth = $month ?? now()->month;
                            $calendarYear = $year ?? now()->year;
                            $firstDay = \Carbon\Carbon::createFromDate($calendarYear, $calendarMonth, 1);
                            $lastDay = $firstDay->copy()->endOfMonth();
                            $startDate = $firstDay->copy()->startOfWeek();
                            $endDate = $lastDay->copy()->endOfWeek();
                        @endphp

                        <div class="grid grid-cols-7 gap-1">
                            @for($date = $startDate; $date <= $endDate; $date->addDay())
                                @php
                                    $dateString = $date->format('Y-m-d');
                                    $restockCount = $events['restock'][$dateString]->count ?? 0;
                                    $deadlineCount = $events['deadlines'][$dateString]->count ?? 0;
                                    $customCount = $events['customs'][$dateString]->count ?? 0;
                                    $deliveryCount = $events['deliveries'][$dateString]->count ?? 0;
                                    $totalEvents = $restockCount + $deadlineCount + $customCount + $deliveryCount;
                                    $hasEvent = $totalEvents > 0;
                                @endphp
                                <div class="h-16 flex flex-col items-center justify-start text-sm border rounded-lg p-1
                                    {{ $date->month != $calendarMonth ? 'bg-gray-50 text-gray-400' : 'bg-white text-gray-700' }}
                                    {{ $date->isToday() ? 'bg-[#c49b6e] text-white font-bold ring-2 ring-[#b08a5c]' : '' }}
                                    {{ $hasEvent ? 'border-[#c49b6e] border-2' : '' }}"
                                    title="{{ $hasEvent ? "📦: $restockCount | ⏰: $deadlineCount | ✏️: $customCount | 🚚: $deliveryCount" : '' }}">
                                    <div class="font-semibold">{{ $date->day }}</div>
                                    @if($hasEvent)
                                        <div class="flex flex-wrap gap-0.5 mt-1 justify-center w-full">
                                            @if($restockCount > 0)
                                                <span class="w-1.5 h-1.5 bg-purple-500 rounded-full" title="Restock: {{ $restockCount }}"></span>
                                            @endif
                                            @if($deadlineCount > 0)
                                                <span class="w-1.5 h-1.5 bg-orange-500 rounded-full" title="Deadline: {{ $deadlineCount }}"></span>
                                            @endif
                                            @if($customCount > 0)
                                                <span class="w-1.5 h-1.5 bg-pink-500 rounded-full" title="Custom: {{ $customCount }}"></span>
                                            @endif
                                            @if($deliveryCount > 0)
                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full" title="Delivery: {{ $deliveryCount }}"></span>
                                            @endif
                                        </div>
                                        <span class="text-xs mt-0.5 font-medium text-[#c49b6e]">{{ $totalEvents }}</span>
                                    @endif
                                </div>
                            @endfor
                        </div>

                        <!-- Event Legend -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6 pt-6 border-t">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                                <span class="text-sm text-gray-600">Restock</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-orange-500 rounded-full"></span>
                                <span class="text-sm text-gray-600">Deadline</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-pink-500 rounded-full"></span>
                                <span class="text-sm text-gray-600">Custom</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                <span class="text-sm text-gray-600">Delivery</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
