@php
    $hasActiveReturn = $order->returnRequests
        ->whereIn('status', [
            \App\Models\ReturnRequest::STATUS_REQUESTED,
            \App\Models\ReturnRequest::STATUS_APPROVED,
            \App\Models\ReturnRequest::STATUS_IN_TRANSIT,
            \App\Models\ReturnRequest::STATUS_VERIFIED,
        ])
        ->isNotEmpty();
    
    $canRequestReturn = in_array($order->status, ['delivered', 'completed']) && !$hasActiveReturn;
@endphp

@if($canRequestReturn)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mt-6">
        <h2 class="font-semibold text-gray-900 mb-4">Request Return</h2>
        <form action="{{ route('returns.request', $order->id) }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Return <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="reason" 
                        name="reason" 
                        rows="4" 
                        required
                        minlength="10"
                        maxlength="1000"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all"
                        placeholder="Please provide a detailed reason for your return request (minimum 10 characters)..."
                    >{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="proof_image" class="block text-sm font-medium text-gray-700 mb-2">
                        Proof Image (Optional)
                    </label>
                    <input 
                        type="file" 
                        id="proof_image" 
                        name="proof_image" 
                        accept="image/jpeg,image/png,image/jpg"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all"
                    >
                    <p class="mt-1 text-xs text-gray-500">Accepted formats: JPG, PNG, JPEG. Max size: 5MB</p>
                    @error('proof_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button 
                        type="submit" 
                        :disabled="submitting"
                        class="w-full px-4 py-2.5 rounded-lg text-white font-medium shadow-sm hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background:#c59d5f;"
                    >
                        <span x-show="!submitting">Submit Return Request</span>
                        <span x-show="submitting">Submitting...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endif

@if($hasActiveReturn)
    @php
        $activeReturn = $order->returnRequests
                ->whereIn('status', [
                    \App\Models\ReturnRequest::STATUS_REQUESTED,
                    \App\Models\ReturnRequest::STATUS_APPROVED,
                    \App\Models\ReturnRequest::STATUS_IN_TRANSIT,
                    \App\Models\ReturnRequest::STATUS_VERIFIED,
                ])
            ->sortByDesc('created_at')
            ->first();
    @endphp
    
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mt-6">
        <h2 class="font-semibold text-gray-900 mb-4">Return Request Status</h2>
        <div class="space-y-4">
            @php
                $statusColor = match($activeReturn->status) {
                    \App\Models\ReturnRequest::STATUS_REQUESTED => 'bg-yellow-100 text-yellow-800',
                    \App\Models\ReturnRequest::STATUS_APPROVED => 'bg-blue-100 text-blue-800',
                    \App\Models\ReturnRequest::STATUS_IN_TRANSIT => 'bg-indigo-100 text-indigo-800',
                    \App\Models\ReturnRequest::STATUS_VERIFIED => 'bg-green-100 text-green-800',
                    \App\Models\ReturnRequest::STATUS_REJECTED => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800',
                };
            @endphp
            
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                    {{ $activeReturn->getStatusLabel() }}
                </span>
            </div>

            <div>
                <div class="text-sm font-medium text-gray-700">Reason:</div>
                <div class="mt-1 text-sm text-gray-900">{{ $activeReturn->reason }}</div>
            </div>

            @if($activeReturn->proof_image)
                <div>
                    <div class="text-sm font-medium text-gray-700">Proof Image:</div>
                    <img src="{{ Storage::url($activeReturn->proof_image) }}" alt="Return Proof" class="mt-2 max-w-xs rounded-lg border border-gray-200">
                </div>
            @endif

            @if($activeReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED && !$activeReturn->return_tracking_number)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-medium text-blue-900 mb-2">Submit Return Tracking Number</h3>
                    <form action="{{ route('returns.tracking', $activeReturn->id) }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <div class="space-y-3">
                            <input 
                                type="text" 
                                name="return_tracking_number" 
                                required
                                maxlength="100"
                                placeholder="Enter LBC tracking number"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-[#c59d5f] focus:ring-2 focus:ring-[#c59d5f]/20 text-sm transition-all"
                            >
                            <button 
                                type="submit" 
                                :disabled="submitting"
                                class="w-full px-4 py-2.5 rounded-lg text-white font-medium shadow-sm hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background:#c59d5f;"
                            >
                                <span x-show="!submitting">Submit Tracking Number</span>
                                <span x-show="submitting">Submitting...</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if($activeReturn->return_tracking_number)
                <div>
                    <div class="text-sm font-medium text-gray-700">Return Tracking Number:</div>
                    <div class="mt-1 text-sm text-gray-900 font-mono">{{ $activeReturn->return_tracking_number }}</div>
                </div>
            @endif

            @if($activeReturn->refund_amount)
                <div>
                    <div class="text-sm font-medium text-gray-700">Refund Amount:</div>
                    <div class="mt-1 text-sm text-gray-900 font-semibold">₱{{ number_format($activeReturn->refund_amount, 2) }}</div>
                    @if($activeReturn->refund_method)
                        <div class="mt-1 text-xs text-gray-500">Method: {{ ucfirst($activeReturn->refund_method) }}</div>
                    @endif
                </div>
            @endif

            {{-- Replacement orders removed: returns are refunds only --}}
        </div>
    </div>
@endif

