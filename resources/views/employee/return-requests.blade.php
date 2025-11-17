@extends('layouts.employee')

@section('page_title', 'Return & Cancellation Requests')

@section('content')
    <div class="space-y-4" x-data="requestsPage()" x-init="init()">
        <h1 class="text-2xl font-semibold text-gray-900">Return & Cancellation Requests</h1>

        <!-- Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-gray-200">
            <a href="{{ route('employee.returns.index') }}" class="px-4 py-2 rounded-lg border text-sm whitespace-nowrap font-medium transition-all {{ request()->routeIs('employee.returns.index') ? 'bg-[#c49b6e] text-white border-[#c49b6e] shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400' }}">
                Return Requests
            </a>
            <a href="{{ route('employee.cancellations.index') }}" class="px-4 py-2 rounded-lg border text-sm whitespace-nowrap font-medium transition-all {{ request()->routeIs('employee.cancellations.index') ? 'bg-[#c49b6e] text-white border-[#c49b6e] shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400' }}">
                Cancellation Requests
            </a>
        </div>

        @if(session('success'))
            <div class="mt-4 p-3 rounded border border-green-200 bg-green-50 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif

        <!-- Filters -->
        <div class="mt-6 space-y-4">
            <form method="GET" action="{{ route('employee.returns.index') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <div class="space-y-4">
                    <!-- Search Bar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Return Requests</label>
                        <div class="flex gap-2">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by Return ID, Order ID, customer name, or email..." class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all"/>
                        </div>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all">
                                <option value="">All Statuses</option>
                                <option value="Return Requested" @selected(request('status')==='Return Requested')>Return Requested</option>
                                <option value="Return Approved" @selected(request('status')==='Return Approved')>Return Approved</option>
                                <option value="Return Rejected" @selected(request('status')==='Return Rejected')>Return Rejected</option>
                                <option value="Return In Transit" @selected(request('status')==='Return In Transit')>Return In Transit</option>
                                <option value="Return Verified" @selected(request('status')==='Return Verified')>Return Verified</option>
                                <option value="Refund Completed" @selected(request('status')==='Refund Completed')>Refund Completed</option>
                                <option value="Replacement Shipped" @selected(request('status')==='Replacement Shipped')>Replacement Shipped</option>
                                <option value="Return Completed" @selected(request('status')==='Return Completed')>Return Completed</option>
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
                            @if(request('q') || request('from') || request('to') || request('status'))
                                <a href="{{ route('employee.returns.index') }}" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-all">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="mt-6 bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="hidden md:grid grid-cols-12 text-xs font-medium text-gray-600 px-4 py-3 border-b bg-gray-50">
                <div class="col-span-1">Return ID</div>
                <div class="col-span-1">Order ID</div>
                <div class="col-span-2">Customer</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-2">Date</div>
                <div class="col-span-2">Refund Amount</div>
                <div class="col-span-2 text-right">Action</div>
            </div>
            <div>
                @forelse($returnRequests as $rr)
                    <div class="grid grid-cols-12 items-center px-4 py-4 border-b text-sm hover:bg-gray-50/50">
                        <div class="col-span-12 md:col-span-1">
                            #{{ $rr->id }}
                        </div>
                        <div class="col-span-12 md:col-span-1 mt-2 md:mt-0">
                            <a href="{{ route('employee.orders.show', $rr->order_id) }}" class="text-[#c59d5f] hover:underline">#{{ $rr->order_id }}</a>
                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                            <div class="font-medium text-gray-900 truncate">{{ $rr->user->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $rr->user->email ?? '' }}</div>
                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                            @php
                                $statusColor = match($rr->status) {
                                    'Return Requested' => 'bg-yellow-100 text-yellow-800',
                                    'Return Approved' => 'bg-blue-100 text-blue-800',
                                    'Return Rejected' => 'bg-red-100 text-red-800',
                                    'Return In Transit' => 'bg-indigo-100 text-indigo-800',
                                    'Return Verified' => 'bg-green-100 text-green-800',
                                    'Refund Completed' => 'bg-green-100 text-green-800',
                                    'Replacement Shipped' => 'bg-purple-100 text-purple-800',
                                    'Return Completed' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $statusColor }}">{{ $rr->getStatusLabel() }}</span>
                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0 text-gray-600">{{ $rr->created_at->format('M d, Y') }}</div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0">
                            @if($rr->refund_amount)
                                <span class="font-medium">₱{{ number_format($rr->refund_amount, 2) }}</span>
                                @if($rr->refund_method)
                                    <div class="text-xs text-gray-500">{{ ucfirst($rr->refund_method) }}</div>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                        <div class="col-span-12 md:col-span-2 mt-2 md:mt-0 text-right">
                            <a href="{{ route('employee.returns.show', $rr->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">View</a>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-gray-500">
                        No return requests found.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $returnRequests->links() }}
        </div>
    </div>

    <script>
        function requestsPage() {
            return {
                init() {
                    // Initialize any JavaScript functionality if needed
                }
            }
        }
    </script>
@endsection

