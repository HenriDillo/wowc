@php
    $hasPendingCancellation = $order->hasPendingCancellationRequest();
    $latestCancellation = $order->getLatestCancellationRequest();
    $canRequestCancellation = $order->canBeCancelled() && !$hasPendingCancellation;
@endphp

@if($canRequestCancellation)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mt-6">
        <h2 class="font-semibold text-gray-900 mb-4">Cancel Order</h2>
        <form action="{{ route('customer.orders.cancel', $order->id) }}" method="POST" x-data="{ submitting: false, showConfirm: false }" @submit="submitting = true">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Cancellation <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="reason" 
                        name="reason" 
                        rows="4" 
                        required
                        minlength="10"
                        maxlength="1000"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all"
                        placeholder="Please provide a detailed reason for cancellation (minimum 10 characters)..."
                    >{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($order->payment_status === 'paid' || $order->payment_status === 'partially_paid')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-900">Payment Made</p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    @if($order->order_type === 'backorder' || $order->order_type === 'custom')
                                        If your cancellation is approved, you will receive a refund of your down payment (50% of order total). Refunds may take 3-5 business days to process.
                                    @else
                                        If your cancellation is approved, you will receive a full refund. Refunds may take 3-5 business days to process.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <label class="flex items-start gap-2">
                        <input 
                            type="checkbox" 
                            name="confirm" 
                            required
                            class="mt-1 rounded border-gray-300 text-[#c59d5f] focus:ring-[#c59d5f]"
                        >
                        <span class="text-sm text-gray-700">
                            I understand that cancellation is subject to approval and that I may not be eligible for a full refund if production/procurement has already started.
                        </span>
                    </label>
                </div>

                <div>
                    <button 
                        type="submit" 
                        :disabled="submitting"
                        class="w-full px-4 py-2.5 rounded-lg text-white font-medium shadow-sm hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background:#c59d5f;"
                    >
                        <span x-show="!submitting">Submit Cancellation Request</span>
                        <span x-show="submitting">Submitting...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@elseif(!$order->canBeCancelled() && !$hasPendingCancellation)
    @php
        $denialReason = $order->getCancellationDenialReason();
    @endphp
    @if($denialReason)
        <div class="bg-white rounded-xl border border-red-100 shadow-sm p-5 mt-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-red-900 mb-2">Cancellation Not Available</h3>
                    <p class="text-sm text-red-800">{{ $denialReason }}</p>
                </div>
            </div>
        </div>
    @endif
@endif

@if($hasPendingCancellation && $latestCancellation)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mt-6">
        <h2 class="font-semibold text-gray-900 mb-4">Cancellation Request Status</h2>
        <div class="space-y-4">
            @php
                $statusColor = match($latestCancellation->status) {
                    \App\Models\CancellationRequest::STATUS_REQUESTED => 'bg-yellow-100 text-yellow-800',
                    \App\Models\CancellationRequest::STATUS_APPROVED => 'bg-blue-100 text-blue-800',
                    \App\Models\CancellationRequest::STATUS_REJECTED => 'bg-red-100 text-red-800',
                    \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING => 'bg-indigo-100 text-indigo-800',
                    \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED => 'bg-green-100 text-green-800',
                    \App\Models\CancellationRequest::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
                    \App\Models\CancellationRequest::STATUS_REFUND_FAILED => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800',
                };
            @endphp
            
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                    {{ $latestCancellation->getStatusLabel() }}
                </span>
            </div>

            <div>
                <div class="text-sm font-medium text-gray-700">Reason:</div>
                <div class="mt-1 text-sm text-gray-900">{{ $latestCancellation->reason }}</div>
            </div>

            @if($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REJECTED && $latestCancellation->notes)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="text-sm font-medium text-red-900 mb-1">Rejection Reason:</div>
                    <div class="text-sm text-red-800 whitespace-pre-line">{{ $latestCancellation->notes }}</div>
                </div>
            @endif

            @if($latestCancellation->refund_amount)
                <div>
                    <div class="text-sm font-medium text-gray-700">Refund Amount:</div>
                    <div class="mt-1 text-sm text-gray-900 font-semibold">₱{{ number_format($latestCancellation->refund_amount, 2) }}</div>
                    @if($latestCancellation->refund_method)
                        <div class="mt-1 text-xs text-gray-500">Method: {{ ucfirst(str_replace('_', ' ', $latestCancellation->refund_method)) }}</div>
                    @endif
                </div>
            @endif

            @if($latestCancellation->handledBy)
                <div>
                    <div class="text-sm font-medium text-gray-700">Handled By:</div>
                    <div class="mt-1 text-sm text-gray-900">{{ $latestCancellation->handledBy->name }}</div>
                    <div class="mt-1 text-xs text-gray-500">{{ $latestCancellation->updated_at->format('M d, Y h:i A') }}</div>
                </div>
            @endif
        </div>
    </div>
@endif

