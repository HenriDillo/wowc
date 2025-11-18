@extends('layouts.employee')

@section('page_title', 'Order Management')

@section('content')

@php
// Ensure $items is available for the Add Order modal (fallback if controller didn't provide it)
$items = $items ?? \App\Models\Item::select('id','name','stock','price')->get();
@endphp

    <div class="space-y-4" x-data="ordersPage()" x-init="init()">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Order Management</h1>
            <div>
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-order' }))" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white font-medium shadow-sm hover:shadow-md transition-all" style="background:#c49b6e;">
                    ➕ Add Order
                </button>
            </div>
        </div>

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
                                    @php
                                        $childPaymentStatus = $childOrder->payment_status ?? 'unpaid';
                                        $childPaymentClasses = [
                                            'paid' => 'bg-green-100 text-green-800',
                                            'partially_paid' => 'bg-blue-100 text-blue-800',
                                            'unpaid' => 'bg-red-100 text-red-800',
                                        ];
                                        $childPaymentClass = $childPaymentClasses[$childPaymentStatus] ?? 'bg-red-100 text-red-800';
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $childPaymentClass }}">{{ ucfirst($childPaymentStatus) }}</span>
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
        // Existing order-page scripts kept elsewhere; modal scripts below
    </script>
    <!-- Add Order Modal -->
    <x-modal name="add-order" maxWidth="2xl" focusable>
        <div x-data="ordersModal()" x-init="updatePaymentMethod()" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Create Order</h2>
                <button type="button" class="text-sm text-gray-500" x-on:click="$dispatch('close-modal', 'add-order')">Close</button>
            </div>

            <!-- Error Messages -->
            <div x-show="hasErrors" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200">
                <p class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</p>
                <ul class="text-sm text-red-700 space-y-1">
                    <template x-for="(messages, field) in errors" :key="field">
                        <template x-for="msg in messages" :key="msg">
                            <li>• <span x-text="msg"></span></li>
                        </template>
                    </template>
                </ul>
            </div>

            <form id="employee-add-order-form" method="POST" action="{{ route('employee.orders.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <!-- Order Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Order Type <span class="text-red-600">*</span>
                    </label>
                    <select name="order_type" x-model="orderType" @change="updatePaymentMethod()" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="standard">Standard Order</option>
                        <option value="backorder">Back Order</option>
                        <option value="custom">Custom Order</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <span x-show="isStandard()">Standard: 100% payment required, COD available</span>
                        <span x-show="isBackorder()">Back Order: 50% upfront, remaining via courier, COD not available</span>
                        <span x-show="isCustom()">Custom Order: 50% upfront, remaining via courier, COD not available</span>
                    </p>
                </div>

                <!-- Customer Information Section -->
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Customer Information</h3>
                    
                    <!-- Option to use existing customer or enter new -->
                    <div class="mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="useExistingCustomer" class="rounded" />
                            <span class="text-sm font-medium text-gray-700">Use existing customer</span>
                        </label>
                    </div>

                    <!-- Existing Customer Selection -->
                    <div x-show="useExistingCustomer" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Customer <span class="text-red-600">*</span>
                        </label>
                        <select name="user_id" :required="useExistingCustomer" class="w-full rounded-lg border {{ $errors->has('user_id') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            <option value="">-- Select a customer --</option>
                            @foreach($users ?? [] as $u)
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} — {{ $u->email }}</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @else
                            <p class="text-xs text-gray-500 mt-1">Select an existing customer from the list</p>
                        @enderror
                    </div>

                    <!-- New Customer Information -->
                    <div x-show="!useExistingCustomer" x-cloak class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    First Name <span class="text-red-600">*</span>
                                </label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" :required="!useExistingCustomer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="First Name" />
                                @error('first_name')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Last Name <span class="text-red-600">*</span>
                                </label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" :required="!useExistingCustomer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Last Name" />
                                @error('last_name')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-600">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" :required="!useExistingCustomer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="email@example.com" />
                            @error('email')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Contact Number <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="contact_number" value="{{ old('contact_number') }}" :required="!useExistingCustomer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="09XXXXXXXXX or +639XXXXXXXXX" />
                            <p class="text-xs text-gray-500 mt-1">Format: 09XXXXXXXXX or +639XXXXXXXXX</p>
                            @error('contact_number')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Address <span class="text-red-600">*</span>
                            </label>
                            <textarea name="address" rows="2" :required="!useExistingCustomer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Complete address (street, city, province, postal code)">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select name="payment_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="NONE">None</option>
                            <option value="COD">COD</option>
                            <option value="GCASH">GCash</option>
                            <option value="BANK">Bank Transfer</option>
                        </select>
                        <p class="text-xs text-amber-600 mt-1" x-show="isBackorder() || isCustom()">
                            ⚠️ COD is not available for Back Orders and Custom Orders
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Shipping Fee</label>
                        <input type="number" step="0.01" name="shipping_fee" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Name</label>
                        <input type="text" name="recipient_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Phone</label>
                        <input type="text" name="recipient_phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <!-- Payment Status Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Status <span class="text-red-600">*</span>
                    </label>
                    <select name="payment_status" x-model="paymentStatus" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="unpaid">Pending Payment</option>
                        <option value="partially_paid" x-show="isBackorder() || isCustom()">Partially Paid (50% Down)</option>
                        <option value="paid" x-show="isStandard()">Fully Paid</option>
                        <option value="pending_cod" x-show="isStandard()">Pending COD</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <span x-show="isStandard()">Standard orders require 100% payment upfront</span>
                        <span x-show="isBackorder()">Back orders: Select "Partially Paid" if 50% down payment received</span>
                        <span x-show="isCustom()">Custom orders: Select "Partially Paid" if 50% down payment received (after quotation)</span>
                    </p>
                </div>

                <!-- Custom Order Fields -->
                <div x-show="isCustom()" x-cloak class="p-4 bg-purple-50 border border-purple-200 rounded-lg space-y-4">
                    <h3 class="text-sm font-semibold text-purple-900 mb-3">Custom Order Details</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Product Name/Title <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="custom_name" x-model="customName" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Enter product name or title" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Description / Special Instructions <span class="text-red-600">*</span>
                        </label>
                        <textarea name="custom_description" x-model="customDescription" required rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Describe your custom order and any special instructions..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Quantity <span class="text-red-600">*</span>
                            </label>
                            <input type="number" min="1" name="custom_quantity" x-model.number="customQuantity" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Quotation Amount <span class="text-red-600">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" name="custom_quotation" x-model.number="customQuotation" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="0.00" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Estimated Completion Date
                        </label>
                        <input type="date" name="estimated_completion_date" x-model="estimatedCompletionDate" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        <p class="text-xs text-gray-500 mt-1">Optional: Set expected completion date for this custom order</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Reference Images (1-4 images) <span class="text-red-600">*</span>
                        </label>
                        <input type="file" name="custom_reference_images[]" multiple accept="image/*" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        <p class="text-xs text-gray-500 mt-1">Upload 1-4 reference images (JPEG, PNG, max 5MB each)</p>
                    </div>
                    <!-- Payment Breakdown for Custom Order -->
                    <div class="p-3 bg-white border border-purple-200 rounded-lg space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Quotation:</span>
                            <span class="font-semibold">₱<span x-text="customQuotation.toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',')"></span></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Required Payment (50%):</span>
                            <span class="font-semibold text-blue-700">₱<span x-text="calculateRequiredPayment().toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',')"></span></span>
                        </div>
                        <div class="flex justify-between text-sm border-t pt-2">
                            <span class="text-gray-600">Remaining Balance:</span>
                            <span class="font-semibold text-amber-700">₱<span x-text="calculateRemainingBalance().toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',')"></span></span>
                        </div>
                        <p class="text-xs text-gray-500 italic mt-1">Remaining balance will be collected by courier upon delivery</p>
                    </div>
                </div>

                <!-- Standard/Backorder Items Section -->
                <div x-show="!isCustom()" x-cloak>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium">
                                Items 
                                <span class="text-gray-500 text-xs" x-show="isStandard()">(System will auto-detect backorder if stock insufficient)</span>
                                <span class="text-gray-500 text-xs" x-show="isBackorder()">(Back order items - stock will be allocated when available)</span>
                            </h3>
                            <button type="button" class="text-sm text-[#c49b6e]" x-on:click.prevent="addRow()">+ Add item</button>
                        </div>

                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-start mb-3 p-2 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="col-span-7">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Item</label>
                                <select :name="`items[${index}][item_id]`" x-model="row.item_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">-- Select item --</option>
                                        <template x-for="it in filteredItems" :key="it.id">
                                        <option :value="it.id" :selected="row.item_id == it.id">
                                            <span x-text="it.name + ' (' + (it.stock ?? 0) + ' in stock, ₱' + (it.price ?? 0) + ')'"></span>
                                        </option>
                                    </template>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Requesting more than stock will create a backorder</p>
                            </div>
                            <div class="col-span-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
                                <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="row.quantity" :max="isStandard() ? ( (items.find(i=>i.id==row.item_id)?.stock) || null ) : null" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div class="col-span-2 flex items-end justify-end">
                                <button type="button" x-on:click.prevent="removeRow(index)" class="text-sm text-red-600 hover:text-red-800 font-medium">✕ Remove</button>
                            </div>
                        </div>
                    </template>
                    </div>

                    <!-- Order Summary & Payment Breakdown -->
                    <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg space-y-2">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Order Summary</h4>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold">₱<span x-text="calculateSubtotal().toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',')"></span></span>
                        </div>
                        <div x-show="isBackorder()" class="space-y-2 pt-2 border-t">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Required Payment (50%):</span>
                                <span class="font-semibold text-blue-700">₱<span x-text="calculateRequiredPayment().toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',')"></span></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Remaining Balance:</span>
                                <span class="font-semibold text-amber-700">₱<span x-text="calculateRemainingBalance().toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',')"></span></span>
                            </div>
                            <p class="text-xs text-gray-500 italic">Remaining balance will be collected by courier</p>
                        </div>
                        <div x-show="isStandard()" class="pt-2 border-t">
                            <div class="flex justify-between text-sm font-semibold">
                                <span class="text-gray-900">Total Amount:</span>
                                <span class="text-gray-900">₱<span x-text="calculateSubtotal().toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',')"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Backorder Specific Fields -->
                    <div x-show="isBackorder()" class="mt-4 space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Expected Arrival Date
                            </label>
                            <input type="date" name="expected_restock_date" x-model="expectedRestockDate" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                            <p class="text-xs text-gray-500 mt-1">Optional: Set expected restock/arrival date for backorder items</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Info Notice -->
                <div x-show="isBackorder() || isCustom()" x-cloak class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-xs text-amber-800">
                        <strong>Payment Information:</strong> 
                        <span x-show="isBackorder()">This back order requires 50% upfront payment. The remaining 50% will be collected by LBC courier upon delivery.</span>
                        <span x-show="isCustom()">This custom order requires 50% upfront payment. The remaining 50% will be collected by LBC courier upon delivery.</span>
                    </p>
                    <p class="text-xs text-amber-700 mt-1 italic">
                        <strong>Note:</strong> Remaining balance will be collected by LBC courier upon delivery.
                    </p>
                </div>

                <div class="flex items-center gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('employee-add-order-form').submit();" class="px-4 py-2 rounded-lg bg-[#c49b6e] text-white font-medium">Create Order</button>
                    <button type="button" class="px-4 py-2 rounded-lg border" x-on:click="$dispatch('close-modal', 'add-order')">Cancel</button>
                </div>
            </form>
        </div>
    </x-modal>

    <script>
        function ordersModal() {
            const items = @js($items ?? []);
            const users = @js($users ?? []);
            const errors = @js($errors->messages() ?? []);
            const hasErrors = @js($errors->any() ?? false);

            return {
                rows: [{item_id: '', quantity: 1}],
                items: items,
                users: users,
                errors: errors,
                hasErrors: hasErrors,
                orderType: 'standard',
                paymentStatus: 'unpaid',
                useExistingCustomer: false,
                customName: '',
                customDescription: '',
                customQuantity: 1,
                customQuotation: 0,
                customReferenceImages: [],
                expectedRestockDate: '',
                estimatedCompletionDate: '',
                addRow() { this.rows.push({item_id: '', quantity: 1}) },
                removeRow(i) { if (this.rows.length > 1) this.rows.splice(i,1) },
                isBackorder() { return this.orderType === 'backorder'; },
                isCustom() { return this.orderType === 'custom'; },
                isStandard() { return this.orderType === 'standard'; },
                calculateSubtotal() {
                    let total = 0;
                    this.rows.forEach(row => {
                        if (row.item_id && row.quantity) {
                            const item = this.items.find(i => i.id == row.item_id);
                            if (item) {
                                total += (parseFloat(item.price || 0) * parseInt(row.quantity || 0));
                            }
                        }
                    });
                    return total;
                },
                calculateRequiredPayment() {
                    if (this.isStandard()) {
                        return this.calculateSubtotal();
                    } else if (this.isBackorder() || this.isCustom()) {
                        if (this.isCustom()) {
                            return parseFloat(this.customQuotation || 0) * 0.5;
                        }
                        return this.calculateSubtotal() * 0.5;
                    }
                    return 0;
                },
                calculateRemainingBalance() {
                    if (this.isStandard()) {
                        return 0;
                    } else if (this.isBackorder()) {
                        return this.calculateSubtotal() * 0.5;
                    } else if (this.isCustom()) {
                        return parseFloat(this.customQuotation || 0) * 0.5;
                    }
                    return 0;
                },
                updatePaymentMethod() {
                    const paymentSelect = document.querySelector('select[name="payment_method"]');
                    if (paymentSelect) {
                        if (this.isBackorder() || this.isCustom()) {
                            Array.from(paymentSelect.options).forEach(opt => {
                                if (opt.value === 'COD') {
                                    opt.disabled = true;
                                    if (opt.selected) paymentSelect.value = 'NONE';
                                } else {
                                    opt.disabled = false;
                                }
                            });
                        } else {
                            Array.from(paymentSelect.options).forEach(opt => {
                                opt.disabled = false;
                            });
                        }
                    }
                }
                ,
                get filteredItems() {
                    // Standard: show items with stock > 0
                    // Backorder: show items with stock <= 0 (out of stock)
                    try {
                        if (this.isStandard()) {
                            return this.items.filter(i => (Number(i.stock) || 0) > 0);
                        }
                        if (this.isBackorder()) {
                            return this.items.filter(i => (Number(i.stock) || 0) <= 0);
                        }
                        // default: show all
                        return this.items;
                    } catch (e) {
                        return this.items;
                    }
                }
            };
        }
    </script>

    <script>
        // Ensure the Add Order form submits even if a JS handler elsewhere prevents default.
        (function(){
            const form = document.getElementById('employee-add-order-form');
            if (!form) return;

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                const fd = new FormData(form);
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (res.redirected) {
                        // Laravel redirect - follow it
                        window.location.href = res.url;
                        return;
                    }

                    if (res.status === 200 || res.status === 201) {
                        // success - reload so order list reflects change
                        window.location.reload();
                        return;
                    }

                    if (res.status === 422) {
                        const data = await res.json().catch(()=>null);
                        let msgs = [];
                        if (data && data.errors) {
                            Object.values(data.errors).forEach(arr => msgs.push(...arr));
                        }
                        if (msgs.length === 0) msgs.push('Validation failed.');
                        alert(msgs.join('\n'));
                        if (submitBtn) submitBtn.disabled = false;
                        return;
                    }

                    // unexpected error
                    const text = await res.text().catch(()=>null);
                    console.error('Add order error', res.status, text);
                    alert('An error occurred while creating the order. Check server logs.');
                    if (submitBtn) submitBtn.disabled = false;
                } catch (err) {
                    console.error(err);
                    alert('Network error while creating order.');
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        })();
    </script>

    @endpush

@endsection


