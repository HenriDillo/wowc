@extends('layouts.employee')

@section('page_title', 'Order Management')

@section('content')

    <div class="space-y-4" x-data="ordersPage()" x-init="init()">
        <h1 class="text-2xl font-semibold text-gray-900">Order Management</h1>

        @if(session('success'))
            <div class="mt-4 p-3 rounded border border-green-200 bg-green-50 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif

        <!-- Filters -->
        <div class="mt-6 space-y-4">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2">
                @php $type = $activeType ?? ''; @endphp
                @php $tabs = [
                    '' => 'All Orders',
                    'standard' => 'Standard Orders',
                    'backorder' => 'Back Orders',
                    'custom' => 'Custom Orders',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]; @endphp
                @foreach($tabs as $t => $label)
                    <a href="{{ url('/employee/orders'.($t ? ('?type='.$t) : '')) }}" class="px-4 py-2 rounded-lg border text-sm whitespace-nowrap font-medium transition-all {{ ($type === $t) ? 'bg-[#c49b6e] text-white border-[#c49b6e] shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400' }}">{{ $label }}</a>
                @endforeach
                <a href="{{ route('employee.returns.index') }}" class="px-4 py-2 rounded-lg border text-sm whitespace-nowrap font-medium transition-all {{ request()->routeIs('employee.returns.index') || request()->routeIs('employee.cancellations.index') ? 'bg-[#c49b6e] text-white border-[#c49b6e] shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400' }}">
                    Requests
                </a>
            </div>

            <!-- Search and Filters Section -->
            <form method="GET" action="{{ url('/employee/orders') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <input type="hidden" name="type" value="{{ $activeType }}"/>
                
                <div class="space-y-4">
                    <!-- Search Bar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Orders</label>
                        <div class="flex gap-2">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by Order ID (e.g., #123), customer name, or email..." class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all"/>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">🔍 Tip: Search by order ID, customer name, or email address</p>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Backorder Status</label>
                            <select name="backorder_status" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all">
                                <option value="">All Statuses</option>
                                <option value="pending_stock" @selected(request('backorder_status')==='pending_stock')>Pending Stock</option>
                                <option value="in_progress" @selected(request('backorder_status')==='in_progress')>In Progress</option>
                                <option value="fulfilled" @selected(request('backorder_status')==='fulfilled')>Fulfilled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all" />
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-white font-medium shadow-sm hover:shadow-md transition-all" style="background:#c49b6e;">
                                <span class="flex items-center justify-center gap-2">
                                    🔍 Search
                                </span>
                            </button>
                            @if(request('q') || request('from') || request('to') || request('backorder_status'))
                                <a href="{{ url('/employee/orders?type='.$activeType) }}" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-all">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    @if(request('q') || request('from') || request('to') || request('backorder_status'))
                        <div class="pt-2 border-t border-gray-100">
                            <div class="text-xs text-gray-600 mb-2">Active Filters:</div>
                            <div class="flex flex-wrap gap-2">
                                @if(request('q'))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                                        Search: {{ request('q') }}
                                    </span>
                                @endif
                                @if(request('from'))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                        From: {{ \Carbon\Carbon::parse(request('from'))->format('M d, Y') }}
                                    </span>
                                @endif
                                @if(request('to'))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                        To: {{ \Carbon\Carbon::parse(request('to'))->format('M d, Y') }}
                                    </span>
                                @endif
                                @if(request('backorder_status'))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-medium">
                                        BO Status: {{ ucwords(str_replace('_', ' ', request('backorder_status'))) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="mt-6 bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="hidden md:grid grid-cols-12 text-xs font-medium text-gray-600 px-4 py-3 border-b bg-gray-50">
                <div class="col-span-2">Order ID</div>
                <div class="col-span-2">Customer</div>
                <div class="col-span-1">Type</div>
                <div class="col-span-1">Status</div>
                <div class="col-span-1">Payment</div>
                <div class="col-span-2">Date</div>
                <div class="col-span-1">Total</div>
                <div class="col-span-1 text-right">Action</div>
            </div>
            <div>
                @forelse($orders as $o)
                    <!-- Order Row -->
                    <div class="grid grid-cols-12 items-center px-4 py-4 border-b text-sm hover:bg-gray-50/50 {{ $o->order_type === 'mixed' ? 'bg-purple-50' : '' }}">
                        <div class="col-span-12 md:col-span-2">
                            <div class="flex items-center gap-2">
                                {{ $o->order_type === 'mixed' ? '📦' : '' }} #{{ $o->id }}
                                @php
                                    $hasPendingCancellation = $o->cancellationRequests
                                        ->where('status', \App\Models\CancellationRequest::STATUS_REQUESTED)
                                        ->isNotEmpty();
                                    $hasActiveReturn = $o->returnRequests
                                        ->whereIn('status', [
                                            \App\Models\ReturnRequest::STATUS_REQUESTED,
                                            \App\Models\ReturnRequest::STATUS_APPROVED,
                                            \App\Models\ReturnRequest::STATUS_IN_TRANSIT,
                                        ])
                                        ->isNotEmpty();
                                @endphp
                                @if($hasPendingCancellation)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" title="Pending Cancellation Request">
                                        ⚠️ Cancel
                                    </span>
                                @endif
                                @if($hasActiveReturn)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" title="Active Return Request">
                                        🔄 Return
                                    </span>
                                @endif
                            </div>
                            @if($o->order_type === 'mixed' && $o->childOrders->isNotEmpty())
                                <div class="text-xs text-purple-700 mt-1">{{ $o->childOrders->count() }} sub-orders</div>
                            @endif
                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                            <div class="font-medium text-gray-900 truncate">{{ $o->user->name ?? 'Guest' }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $o->user->email ?? '' }}</div>
                        </div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs border 
                                @if($o->order_type === 'standard') border-green-300 bg-green-50 text-green-700
                                @elseif($o->order_type === 'backorder') border-blue-300 bg-blue-50 text-blue-700
                                @elseif($o->order_type === 'mixed') border-purple-300 bg-purple-50 text-purple-700
                                @else border-gray-300 bg-white 
                                @endif">
                                @if($o->order_type === 'standard')
                                    Standard
                                @elseif($o->order_type === 'backorder')
                                    Back Order
                                @else
                                    {{ ucfirst($o->order_type) }}
                                @endif
                            </span>
                        </div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                            @php
                                $statusColor = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'backorder' => 'bg-indigo-100 text-indigo-800',
                                ][$o->status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs capitalize {{ $statusColor }}">{{ $o->status }}</span>
                        </div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                            @php
                                $paymentStatus = $o->payment_status ?? 'unpaid';
                                $paymentColor = [
                                    'paid' => 'bg-green-100 text-green-800',
                                    'partially_paid' => 'bg-blue-100 text-blue-800',
                                    'unpaid' => 'bg-red-100 text-red-800',
                                    'pending_verification' => 'bg-yellow-100 text-yellow-800',
                                ][$paymentStatus] ?? 'bg-gray-100 text-gray-700';
                                $paymentLabel = [
                                    'paid' => 'Fully Paid ✓',
                                    'partially_paid' => 'Partially Paid',
                                    'unpaid' => 'Unpaid',
                                    'pending_verification' => 'Pending Verification',
                                ][$paymentStatus] ?? ucfirst($paymentStatus);
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $paymentColor }}">{{ $paymentLabel }}</span>
                            @if($paymentStatus === 'partially_paid' && ($o->remaining_balance ?? 0) > 0)
                                <div class="text-xs text-blue-700 mt-1 font-semibold">Remaining: ₱{{ number_format($o->remaining_balance, 2) }}</div>
                            @endif
                        </div>
                        <div class="col-span-6 md:col-span-2 mt-2 md:mt-0 text-gray-600">{{ $o->created_at->format('M d, Y') }}</div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 font-medium">₱{{ number_format($o->total_amount ?? 0, 2) }}</div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 text-right">
                            <a href="{{ route('employee.orders.show', $o->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">View</a>
                        </div>
                    </div>
                    
                    <!-- Sub-Orders Row (if mixed order) -->
                    @if($o->order_type === 'mixed' && $o->childOrders->isNotEmpty())
                        @foreach($o->childOrders as $childOrder)
                            <div class="grid grid-cols-12 items-center px-4 py-3 border-b bg-purple-25 text-sm">
                                <div class="col-span-12 md:col-span-2 pl-8 text-gray-600">
                                    └─ Sub-Order #{{ $childOrder->id }}
                                </div>
                                <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                                    <div class="text-xs text-gray-600">{{ ucfirst($childOrder->order_type) }} Items</div>
                                </div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs border 
                                        @if($childOrder->order_type === 'standard') border-green-300 bg-green-50 text-green-700
                                        @else border-blue-300 bg-blue-50 text-blue-700
                                        @endif">
                                        {{ ucfirst($childOrder->order_type) }}
                                    </span>
                                </div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                                    @php
                                        $childStatusColor = $statusColor ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs capitalize {{ $childStatusColor }}">{{ $childOrder->status }}</span>
                                </div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs 
                                        @php
                                            $childPaymentStatus = $childOrder->payment_status ?? 'unpaid';
                                            echo match($childPaymentStatus) {
                                                'paid' => 'bg-green-100 text-green-800',
                                                'partially_paid' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-red-100 text-red-800'
                                            };
                                        @endphp">
                                        {{ ucfirst($childPaymentStatus) }}
                                    </span>
                                </div>
                                <div class="col-span-6 md:col-span-2 mt-2 md:mt-0"></div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 font-medium text-purple-700">₱{{ number_format($childOrder->total_amount ?? 0, 2) }}</div>
                                <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 text-right">
                                    <a href="{{ route('employee.orders.show', $childOrder->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-purple-300 text-purple-700 hover:bg-purple-50 text-xs font-medium">View</a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @empty
                    <div class="px-4 py-10 text-center text-gray-600">No orders found.</div>
                @endforelse
            </div>
            <div class="px-4 py-3">{{ $orders->links() }}</div>
        </div>

    @push('scripts')
    <script>
        // Scripts removed - all order management done on order-show page
    </script>
    @endpush

@endsection


