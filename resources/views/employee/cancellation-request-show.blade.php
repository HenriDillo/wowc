@extends('layouts.employee')

@section('page_title', 'Cancellation Request #'.$cancellationRequest->id)

@section('content')
    <div class="w-full">
        <!-- Back Button and Header -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('employee.cancellations.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    ← Back to Cancellation Requests
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Cancellation Request #{{ $cancellationRequest->id }}</h1>
                    <div class="mt-1 text-sm text-gray-600">Created {{ $cancellationRequest->created_at?->format('M d, Y') }} • Order #{{ $cancellationRequest->order_id }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $statusColor = match($cancellationRequest->status) {
                        'Cancellation Requested' => 'bg-yellow-100 text-yellow-800',
                        'Cancellation Approved' => 'bg-blue-100 text-blue-800',
                        'Cancellation Rejected' => 'bg-red-100 text-red-800',
                        'Refund Processing' => 'bg-indigo-100 text-indigo-800',
                        'Refund Completed' => 'bg-green-100 text-green-800',
                        'Cancelled' => 'bg-gray-100 text-gray-800',
                        'Refund Failed' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-medium {{ $statusColor }}">
                    {{ $cancellationRequest->getStatusLabel() }}
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
                <!-- Cancellation Request Details -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Cancellation Request Details</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Reason</div>
                            <div class="mt-1 text-gray-900 whitespace-pre-line">{{ $cancellationRequest->reason }}</div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Requested By</div>
                            <div class="mt-1 text-gray-900 capitalize">{{ $cancellationRequest->requested_by }}</div>
                        </div>

                        @if($cancellationRequest->handledBy)
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Handled By</div>
                                <div class="mt-1 text-gray-900">{{ $cancellationRequest->handledBy->name }}</div>
                                <div class="mt-1 text-sm text-gray-600">{{ $cancellationRequest->updated_at->format('M d, Y h:i A') }}</div>
                            </div>
                        @endif

                        @if($cancellationRequest->notes)
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Notes</div>
                                <div class="mt-1 text-gray-900 whitespace-pre-line">{{ $cancellationRequest->notes }}</div>
                            </div>
                        @endif

                        @if($cancellationRequest->refund_amount)
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Refund Amount</div>
                                <div class="mt-1 text-gray-900 font-semibold text-lg">₱{{ number_format($cancellationRequest->refund_amount, 2) }}</div>
                                @if($cancellationRequest->refund_method)
                                    <div class="mt-1 text-sm text-gray-600">Method: {{ ucfirst(str_replace('_', ' ', $cancellationRequest->refund_method)) }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Order Details -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Details</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order ID</div>
                            <a href="{{ route('employee.orders.show', $cancellationRequest->order_id) }}" class="mt-1 text-[#c59d5f] hover:underline">
                                View Order #{{ $cancellationRequest->order_id }}
                            </a>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Type</div>
                            <div class="mt-1 text-gray-900 capitalize">{{ $cancellationRequest->order->order_type }}</div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Status</div>
                            <div class="mt-1">
                                @php
                                    $orderStatusColor = match($cancellationRequest->order->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'shipped' => 'bg-purple-100 text-purple-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $orderStatusColor }}">
                                    {{ ucwords(str_replace('_', ' ', $cancellationRequest->order->status)) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Payment Status</div>
                            <div class="mt-1 text-gray-900">{{ $cancellationRequest->order->getPaymentStatusLabel() }}</div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Items</div>
                            <div class="mt-2 space-y-2">
                                @foreach($cancellationRequest->order->items as $item)
                                    <div class="flex items-center gap-3 p-2 bg-gray-50 rounded">
                                        @if($item->item && $item->item->photos->first())
                                            <img src="{{ $item->item->photos->first()->url }}" alt="{{ $item->item->name }}" class="w-12 h-12 rounded object-cover">
                                        @endif
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">{{ $item->item->name ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-600">Qty: {{ $item->quantity }} × ₱{{ number_format($item->price, 2) }}</div>
                                            @if($item->is_backorder)
                                                <div class="text-xs text-blue-600">Backorder</div>
                                            @endif
                                        </div>
                                        <div class="font-medium text-gray-900">₱{{ number_format($item->subtotal, 2) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Total</div>
                            <div class="mt-1 text-gray-900 font-semibold text-lg">₱{{ number_format($cancellationRequest->order->total_amount, 2) }}</div>
                        </div>

                        @if($cancellationRequest->order->order_type === 'backorder')
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="text-sm font-medium text-blue-900 mb-2">Backorder Status</div>
                                @php
                                    $hasStartedProcurement = $cancellationRequest->order->items()
                                        ->where('is_backorder', true)
                                        ->whereIn('backorder_status', [\App\Models\OrderItem::BO_IN_PROGRESS, \App\Models\OrderItem::BO_FULFILLED])
                                        ->exists();
                                @endphp
                                @if($hasStartedProcurement)
                                    <p class="text-sm text-red-800">⚠️ Procurement has already started. Cancellation may not be allowed.</p>
                                @else
                                    <p class="text-sm text-blue-800">✓ Procurement has not started. Cancellation is allowed.</p>
                                @endif
                            </div>
                        @endif

                        @if($cancellationRequest->order->order_type === 'custom')
                            @php
                                $customOrder = $cancellationRequest->order->customOrders->first();
                            @endphp
                            @if($customOrder)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="text-sm font-medium text-blue-900 mb-2">Custom Order Status</div>
                                    @if(in_array($customOrder->status, [\App\Models\CustomOrder::STATUS_IN_PRODUCTION, \App\Models\CustomOrder::STATUS_COMPLETED]))
                                        <p class="text-sm text-red-800">⚠️ Production has already started. Cancellation may not be allowed.</p>
                                    @else
                                        <p class="text-sm text-blue-800">✓ Production has not started. Cancellation is allowed.</p>
                                    @endif
                                </div>
                            @endif
                        @endif

                        @if($cancellationRequest->order->payments->count() > 0)
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Payments</div>
                                <div class="mt-2 space-y-2">
                                    @foreach($cancellationRequest->order->payments as $payment)
                                        <div class="p-2 bg-gray-50 rounded">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">₱{{ number_format($payment->amount, 2) }}</div>
                                                    <div class="text-xs text-gray-600">{{ ucfirst($payment->method) }} • {{ ucfirst($payment->status) }}</div>
                                                </div>
                                                @if($payment->isVerified())
                                                    <span class="text-xs text-green-600">✓ Verified</span>
                                                @elseif($payment->isPendingVerification())
                                                    <span class="text-xs text-yellow-600">Pending</span>
                                                @elseif($payment->isRejected())
                                                    <span class="text-xs text-red-600">Rejected</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
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
                            <div class="mt-1 text-gray-900">{{ $cancellationRequest->user->name ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500">Email</div>
                            <div class="mt-1 text-gray-900">{{ $cancellationRequest->user->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Actions</h2>
                    <div class="space-y-3">
                        @if($cancellationRequest->status === \App\Models\CancellationRequest::STATUS_REQUESTED)
                            <form action="{{ route('employee.cancellations.approve', $cancellationRequest->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this cancellation request? This will release inventory and may trigger refund processing.');">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
                                    Approve Cancellation
                                </button>
                            </form>
                            <form action="{{ route('employee.cancellations.reject', $cancellationRequest->id) }}" method="POST" x-data="{ showNotes: false }">
                                @csrf
                                <div class="space-y-2">
                                    <button type="button" @click="showNotes = !showNotes" class="w-full px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                                        Reject Cancellation
                                    </button>
                                    <div x-show="showNotes" class="space-y-2">
                                        <textarea name="notes" rows="3" placeholder="Rejection reason (optional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20"></textarea>
                                        <button type="submit" onclick="return confirm('Are you sure you want to reject this cancellation request?');" class="w-full px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                                            Confirm Rejection
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif

                        @if(in_array($cancellationRequest->status, [
                            \App\Models\CancellationRequest::STATUS_APPROVED,
                            \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING,
                            \App\Models\CancellationRequest::STATUS_REFUND_FAILED,
                        ]) && $cancellationRequest->refund_amount > 0)
                            <!-- Refund Form -->
                            <div class="border-t pt-4 mt-4">
                                <h3 class="font-medium text-gray-900 mb-3">Process Refund</h3>
                                <form action="{{ route('employee.cancellations.refund', $cancellationRequest->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to process this refund?');">
                                    @csrf
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Refund Amount</label>
                                            @php
                                                $order = $cancellationRequest->order;
                                                // For mixed orders, calculate refund from all child orders
                                                if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
                                                    $maxRefund = 0.0;
                                                    foreach ($order->childOrders as $childOrder) {
                                                        // Sum all verified/paid payments from child orders
                                                        // Include payments that are either paid or verified (approved)
                                                        $childPaidAmount = (float) $childOrder->payments()
                                                            ->where(function($query) {
                                                                $query->where('status', 'paid')
                                                                      ->orWhere('verification_status', 'approved');
                                                            })
                                                            ->sum('amount');
                                                        $maxRefund += $childPaidAmount;
                                                    }
                                                } else {
                                                    // For single orders, get verified/paid payments from the order itself
                                                    $maxRefund = (float) $order->payments()
                                                        ->where(function($query) {
                                                            $query->where('status', 'paid')
                                                                  ->orWhere('verification_status', 'approved');
                                                        })
                                                        ->sum('amount');
                                                }
                                                // Use the refund amount from cancellation request if available, otherwise use calculated max
                                                $defaultRefund = $cancellationRequest->refund_amount ?? $maxRefund;
                                            @endphp
                                            <input type="number" name="refund_amount" step="0.01" min="0.01" max="{{ $maxRefund > 0 ? $maxRefund : 999999 }}" value="{{ $defaultRefund }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm">
                                            <p class="text-xs text-gray-500 mt-1">Max: ₱{{ number_format($maxRefund, 2) }}</p>
                                            @if($cancellationRequest->refund_amount && $cancellationRequest->refund_amount > $maxRefund)
                                                <p class="text-xs text-yellow-600 mt-1">⚠️ Calculated refund amount (₱{{ number_format($cancellationRequest->refund_amount, 2) }}) exceeds available paid amount. Please verify.</p>
                                            @endif
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Refund Method</label>
                                            <select name="refund_method" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm">
                                                <option value="">Select Method</option>
                                                <option value="gcash" @selected($cancellationRequest->refund_method === 'gcash')>GCash</option>
                                                <option value="bank_transfer" @selected($cancellationRequest->refund_method === 'bank_transfer')>Bank Transfer</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID (Optional)</label>
                                            <input type="text" name="transaction_id" maxlength="100" placeholder="Enter transaction ID" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                            <textarea name="notes" rows="2" placeholder="Additional notes" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm"></textarea>
                                        </div>
                                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition-colors">
                                            Process Refund
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

