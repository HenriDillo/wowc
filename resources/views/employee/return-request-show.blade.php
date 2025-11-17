@extends('layouts.employee')

@section('page_title', 'Return Request #'.$returnRequest->id)

@section('content')
    <div class="w-full">
        <!-- Back Button and Header -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('employee.returns.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    ← Back to Return Requests
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Return Request #{{ $returnRequest->id }}</h1>
                    <div class="mt-1 text-sm text-gray-600">Created {{ $returnRequest->created_at?->format('M d, Y') }} • Order #{{ $returnRequest->order_id }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $statusColor = match($returnRequest->status) {
                        'Return Requested' => 'bg-yellow-100 text-yellow-800',
                        'Return Approved' => 'bg-blue-100 text-blue-800',
                        'Return Rejected' => 'bg-red-100 text-red-800',
                        'Return In Transit' => 'bg-indigo-100 text-indigo-800',
                        'Return Verified' => 'bg-green-100 text-green-800',
                        'Refund Completed' => 'bg-green-100 text-green-800',
                        'Return Completed' => 'bg-gray-100 text-gray-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-medium {{ $statusColor }}">
                    {{ $returnRequest->getStatusLabel() }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Return Request Details -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Return Request Details</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Reason</div>
                            <div class="mt-1 text-gray-900 whitespace-pre-line">{{ $returnRequest->reason }}</div>
                        </div>

                        @if($returnRequest->proof_image)
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Proof Image</div>
                                <img src="{{ Storage::url($returnRequest->proof_image) }}" alt="Return Proof" class="mt-2 max-w-md rounded-lg border border-gray-200">
                            </div>
                        @endif

                        @if($returnRequest->return_tracking_number)
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Return Tracking Number</div>
                                <div class="mt-1 text-gray-900 font-mono">{{ $returnRequest->return_tracking_number }}</div>
                            </div>
                        @endif

                        @if($returnRequest->verified_at)
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Verified At</div>
                                <div class="mt-1 text-gray-900">{{ $returnRequest->verified_at->format('M d, Y h:i A') }}</div>
                            </div>
                        @endif

                        @if($returnRequest->refund_amount)
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Refund Amount</div>
                                <div class="mt-1 text-gray-900 font-semibold text-lg">₱{{ number_format($returnRequest->refund_amount, 2) }}</div>
                                @if($returnRequest->refund_method)
                                    <div class="mt-1 text-sm text-gray-600">Method: {{ ucfirst($returnRequest->refund_method) }}</div>
                                @endif
                            </div>
                        @endif

                        {{-- Replacement orders removed: returns are refunds only --}}
                    </div>
                </div>

                <!-- Order Details -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Original Order Details</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order ID</div>
                            <a href="{{ route('employee.orders.show', $returnRequest->order_id) }}" class="mt-1 text-[#c59d5f] hover:underline">
                                View Order #{{ $returnRequest->order_id }}
                            </a>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Items</div>
                            <div class="mt-2 space-y-2">
                                @foreach($returnRequest->order->items as $item)
                                    <div class="flex items-center gap-3 p-2 bg-gray-50 rounded">
                                        @if($item->item && $item->item->photos->first())
                                            <img src="{{ $item->item->photos->first()->url }}" alt="{{ $item->item->name }}" class="w-12 h-12 rounded object-cover">
                                        @endif
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">{{ $item->item->name ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-600">Qty: {{ $item->quantity }} × ₱{{ number_format($item->price, 2) }}</div>
                                        </div>
                                        <div class="font-medium text-gray-900">₱{{ number_format($item->subtotal, 2) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Total</div>
                            <div class="mt-1 text-gray-900 font-semibold text-lg">₱{{ number_format($returnRequest->order->total_amount, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Actions -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Customer Info -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Customer Information</h2>
                    <div class="space-y-2">
                        <div>
                            <div class="text-xs font-medium text-gray-500">Name</div>
                            <div class="mt-1 text-gray-900">{{ $returnRequest->user->name ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500">Email</div>
                            <div class="mt-1 text-gray-900">{{ $returnRequest->user->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Actions</h2>
                    <div class="space-y-3">
                        @if($returnRequest->status === \App\Models\ReturnRequest::STATUS_REQUESTED)
                            <form action="{{ route('employee.returns.approve', $returnRequest->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this return request?');">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
                                    Approve Return
                                </button>
                            </form>
                            <form action="{{ route('employee.returns.reject', $returnRequest->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this return request?');">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                                    Reject Return
                                </button>
                            </form>
                        @endif

                        @if($returnRequest->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT)
                            <form action="{{ route('employee.returns.verify', $returnRequest->id) }}" method="POST" onsubmit="return confirm('Have you physically received the returned item?');">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition-colors">
                                    Verify Return Received
                                </button>
                            </form>
                        @endif

                        @if($returnRequest->status === \App\Models\ReturnRequest::STATUS_VERIFIED)
                            <!-- Refund Form -->
                            <div class="border-t pt-4 mt-4">
                                <h3 class="font-medium text-gray-900 mb-3">Process Refund</h3>
                                <form action="{{ route('employee.returns.refund', $returnRequest->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to process this refund?');">
                                    @csrf
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Refund Amount</label>
                                            <input type="number" name="refund_amount" step="0.01" min="0.01" max="{{ $returnRequest->order->total_amount }}" value="{{ $returnRequest->order->total_amount }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Refund Method</label>
                                            <select name="refund_method" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm">
                                                <option value="">Select Method</option>
                                                <option value="gcash">GCash</option>
                                                <option value="bank">Bank Transfer</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition-colors">
                                            Process Refund
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Replacement orders removed: only refunds are supported now --}}
                        @endif

                        {{-- Replacement shipping removed --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

