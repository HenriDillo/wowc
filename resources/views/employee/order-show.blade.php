@extends('layouts.employee')

@section('page_title', 'Order #'.$order->id)

@section('content')

    <div class="w-full">
        <!-- Back Button and Header -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('employee.orders') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    ← Back to Orders
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Order #{{ $order->id }}</h1>
                    <div class="mt-1 text-sm text-gray-600">Placed {{ $order->created_at?->format('M d, Y') }} • <span class="capitalize">{{ $order->order_type }}</span></div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php 
                    $paymentStatus = $order->payment_status ?? 'unpaid';
                    $hasVerifiedPayment = $order->hasVerifiedPayment();
                    $hasPendingVerification = $order->hasPendingPaymentVerification();
                    $isRejected = $order->payment_status === 'payment_rejected' || ($order->getLatestPayment() && $order->getLatestPayment()->isRejected());
                @endphp
                <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-medium
                    @if($paymentStatus === 'paid' && $hasVerifiedPayment) bg-green-100 text-green-800
                    @elseif($paymentStatus === 'partially_paid') bg-blue-100 text-blue-800
                    @elseif($paymentStatus === 'pending_verification' || $hasPendingVerification) bg-yellow-100 text-yellow-800
                    @elseif($paymentStatus === 'pending_cod') bg-blue-100 text-blue-800
                    @elseif($paymentStatus === 'payment_rejected' || $isRejected) bg-red-100 text-red-800
                    @else bg-red-100 text-red-800
                    @endif">
                    @if($paymentStatus === 'paid' && $hasVerifiedPayment) ✓ Paid — Verified
                    @elseif($paymentStatus === 'partially_paid') 💰 Partially Paid
                    @elseif($paymentStatus === 'pending_verification' || $hasPendingVerification) ⏳ Pending Payment Verification
                    @elseif($paymentStatus === 'pending_cod') 💰 Pending COD
                    @elseif($paymentStatus === 'payment_rejected' || $isRejected) ✗ Payment Rejected
                    @else ✗ Payment Unpaid
                    @endif
                </span>
            </div>
        </div>

        <!-- Parent-Sub Order Info (if applicable) -->
        @if($order->order_type === 'mixed' && $order->childOrders->isNotEmpty())
            <!-- This is a parent (mixed) order -->
            <div class="mb-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <h3 class="font-semibold text-purple-900">Mixed Order Structure</h3>
                <p class="text-sm text-purple-700 mt-1">This order contains both standard and back order items split into sub-orders below.</p>
                <div class="mt-3 space-y-2">
                    @php $totalAmount = $order->total_amount; @endphp
                    @foreach($order->childOrders as $child)
                        <div class="flex items-center justify-between bg-white px-3 py-2 rounded border border-purple-100">
                            <div class="text-sm">
                                <span class="font-medium text-gray-900">Sub-Order #{{ $child->id }}</span>
                                <span class="text-gray-600 ml-2">• {{ ucfirst($child->order_type) }} Items</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium 
                                    @if($child->order_type === 'standard') bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ ucfirst($child->status) }}
                                </span>
                                <span class="text-sm font-medium text-gray-700">₱{{ number_format($child->total_amount, 2) }}</span>
                                <a href="{{ route('employee.orders.show', $child->id) }}" class="text-xs text-purple-700 hover:underline">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 pt-3 border-t border-purple-200 flex items-center justify-between">
                    <span class="font-medium text-purple-900">Total Parent Order Amount</span>
                    <span class="text-lg font-semibold text-purple-900">₱{{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>
        @elseif($order->parent_order_id)
            <!-- This is a child order (sub-order) -->
            @php $parentOrder = $order->parentOrder; @endphp
            <div class="mb-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <h3 class="font-semibold text-purple-900">Part of Mixed Order</h3>
                <p class="text-sm text-purple-700 mt-1">This is a {{ ucfirst($order->order_type) }} sub-order linked to Parent Order #{{ $parentOrder->id }}.</p>
                <div class="mt-3">
                    <a href="{{ route('employee.orders.show', $parentOrder->id) }}" class="inline-flex items-center px-3 py-2 rounded-lg border border-purple-300 text-purple-700 hover:bg-purple-100 text-sm font-medium">
                        ← View Parent Order #{{ $parentOrder->id }}
                    </a>
                </div>
            </div>
        @endif
        @php
            $hasVerifiedPayment = $order->hasVerifiedPayment();
            $hasPendingVerification = $order->hasPendingPaymentVerification();
            $isCod = $order->payment_method === 'COD';
            // COD orders can be updated (except completed status if not paid), non-COD orders need verified payment
            $canUpdateStatus = $isCod || $hasVerifiedPayment || $order->payment_status === 'paid';
        @endphp
        @if(!$canUpdateStatus)
            <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="text-red-700 font-semibold">⚠ Payment Required</div>
                    <div class="text-sm text-red-700 flex-1">
                        @if($hasPendingVerification || $paymentStatus === 'pending_verification')
                            This order is awaiting admin verification of bank transfer proof. Until verified, processing actions are limited.
                        @elseif($paymentStatus === 'payment_rejected')
                            Payment was rejected. Please verify the payment or contact the customer.
                        @else
                            This order has not been paid yet. Customer must complete payment before processing can begin.
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Validation Errors Banner -->
        @if($errors->any())
            <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="text-red-700 font-semibold">✕ Validation Errors</div>
                    <div class="text-sm text-red-700 flex-1">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Status Update Form (COD orders can always update, non-COD need verified payment) -->
        @php
            $hasVerifiedPayment = $order->hasVerifiedPayment();
            $isCod = $order->payment_method === 'COD';
            // COD orders can be updated (except completed if not paid), non-COD orders need verified payment
            $canUpdateStatus = $isCod || $hasVerifiedPayment || $order->payment_status === 'paid';
        @endphp
        @if($canUpdateStatus)
            <form method="POST" action="{{ route('employee.orders.update', $order->id) }}" class="mb-6 flex items-center gap-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                @csrf
                @method('PUT')
                <label class="text-sm font-medium text-blue-900">Update Status:</label>
                <select name="status" class="rounded-md border border-gray-300 text-sm px-3 py-2">
                    @php 
                        // Get valid next statuses (forward-only flow)
                        $validNextStatuses = $order->getValidNextStatuses();
                        // Always include current status
                        $availableStatuses = array_unique(array_merge([$order->status], $validNextStatuses));
                    @endphp
                    @foreach($availableStatuses as $s)
                        @php
                            // Disable "completed" for COD orders if payment hasn't been collected
                            $disabled = ($s === 'completed' && $isCod && $order->payment_status === 'pending_cod');
                            $isCurrentStatus = ($s === $order->status);
                            
                            // Get friendly status labels for back orders
                            $statusLabels = [
                                'backorder' => [
                                    'pending' => 'Order Placed',
                                    'processing' => 'Awaiting Stock',
                                    'ready_to_ship' => 'Preparing to Ship',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ],
                            ];
                            
                            // Use friendly label for back orders, otherwise use default formatting
                            if ($order->order_type === 'backorder' && isset($statusLabels['backorder'][$s])) {
                                $statusDisplay = $statusLabels['backorder'][$s];
                            } else {
                                $statusDisplay = ucwords(str_replace('_',' ',$s));
                            }
                        @endphp
                        <option value="{{ $s }}" @selected($isCurrentStatus) @disabled($disabled)>
                            {{ $statusDisplay }}
                            @if($isCurrentStatus) (Current) @endif
                            @if($disabled) (Payment Required) @endif
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-md text-white font-medium" style="background:#c59d5f;">Update</button>
            </form>
        @else
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-sm text-gray-600">
                    @if($order->hasPendingPaymentVerification())
                        Status update available after payment verification is completed.
                    @elseif($order->payment_status === 'payment_rejected')
                        Status update unavailable. Payment was rejected.
                    @else
                        Status update available after payment confirmation.
                    @endif
                </p>
            </div>
        @endif

        @if(session('success'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900">Customer</h2>
                    <div class="mt-3 text-sm text-gray-700">
                        <div class="font-medium">{{ $order->user?->name }}</div>
                        <div>{{ $order->user?->email }}</div>
                        <div class="mt-2">{{ $order->user?->address?->address_line }}</div>
                        <div>{{ $order->user?->address?->city }}, {{ $order->user?->address?->province }} {{ $order->user?->address?->postal_code }}</div>
                        <div>{{ $order->user?->address?->phone_number }}</div>
                    </div>
                </div>

                @if($order->customOrders->isNotEmpty())
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Custom Order Details</h2>
                        <p class="mt-1 text-sm text-gray-500">Review customer specifications and set pricing. Saving keeps the status Pending.</p>

                        <div class="mt-5 space-y-6">
                            @foreach($order->customOrders as $customOrder)
                                <div class="border border-gray-100 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div class="space-y-3 text-sm text-gray-700">
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Product Name</div>
                                                <div class="mt-1 text-gray-900 font-semibold">{{ $customOrder->custom_name }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Customer</div>
                                                <div class="mt-1 text-gray-900">{{ $order->user?->name ?? '—' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Description</div>
                                                <div class="mt-1 text-gray-900 whitespace-pre-line">{{ $customOrder->description }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Quantity</div>
                                                <div class="mt-1 text-gray-900">{{ $customOrder->quantity }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</div>
                                                <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ str_replace('_',' ', ucfirst($customOrder->status)) }}
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Current Price</div>
                                                    <div class="mt-1 text-gray-900 font-semibold">
                                                        @if(!is_null($customOrder->price_estimate))
                                                            ₱{{ number_format((float)$customOrder->price_estimate, 2) }}
                                                        @else
                                                            —
                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Estimated Completion</div>
                                                    <div class="mt-1 text-gray-900">
                                                        {{ optional($customOrder->estimated_completion_date)->format('M d, Y') ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                            @if($customOrder->admin_notes)
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Internal Notes</div>
                                                    <div class="mt-1 text-gray-700 whitespace-pre-line">{{ $customOrder->admin_notes }}</div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="space-y-3">
                                            @php
                                                $images = data_get($customOrder->customization_details, 'images', []);
                                                // Fallback to single image for backward compatibility
                                                if (empty($images) && $customOrder->reference_image_path) {
                                                    $images = [$customOrder->reference_image_path];
                                                }
                                            @endphp
                                            @if(!empty($images))
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Reference Images</div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        @foreach($images as $imagePath)
                                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($imagePath) }}" alt="Reference Image {{ $loop->iteration }}" class="rounded-lg border border-gray-200 shadow-sm max-h-80 object-contain w-full bg-gray-50">
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($customOrder->status === \App\Models\CustomOrder::STATUS_PENDING_REVIEW)
                                                <!-- Accept Order Form -->
                                                <form method="POST" action="{{ route('employee.custom-orders.accept', $customOrder->id) }}" class="space-y-4 border-b border-gray-200 pb-4">
                                                    @csrf
                                                    <h4 class="text-sm font-semibold text-green-700 mb-2">Accept Order</h4>
                                                    
                                                    <div>
                                                        <label for="accept_price_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Price <span class="text-red-500">*</span></label>
                                                        <input type="number" min="0" step="0.01" id="accept_price_{{ $customOrder->id }}" name="price_estimate" value="{{ old('price_estimate', $customOrder->price_estimate) }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        @error('price_estimate')
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div>
                                                        <label for="accept_completion_date_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Expected Completion Date <span class="text-red-500">*</span></label>
                                                        <input type="date" id="accept_completion_date_{{ $customOrder->id }}" name="estimated_completion_date" value="{{ old('estimated_completion_date', optional($customOrder->estimated_completion_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        @error('estimated_completion_date')
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div>
                                                        <label for="accept_admin_notes_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Internal Notes (Optional)</label>
                                                        <textarea id="accept_admin_notes_{{ $customOrder->id }}" name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]">{{ old('admin_notes', $customOrder->admin_notes) }}</textarea>
                                                    </div>

                                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 w-full" style="background:#2f855a;">
                                                        ✓ Accept Order
                                                    </button>
                                                    <p class="text-xs text-gray-500 mt-1">Status will update to "Accepted / Pending Payment"</p>
                                                </form>

                                                <!-- Reject Order Form -->
                                                <form method="POST" action="{{ route('employee.custom-orders.reject', $customOrder->id) }}" class="space-y-4 mt-4">
                                                    @csrf
                                                    <h4 class="text-sm font-semibold text-red-700 mb-2">Reject Order</h4>
                                                    
                                                    <div>
                                                        <label for="rejection_note_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Rejection Reason <span class="text-red-500">*</span></label>
                                                        <textarea id="rejection_note_{{ $customOrder->id }}" name="rejection_note" rows="4" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-red-500 focus:ring-red-500" placeholder="Please explain why this order is being rejected..." required>{{ old('rejection_note', $customOrder->rejection_note) }}</textarea>
                                                        <p class="mt-1 text-xs text-gray-500">This note will be visible to the customer.</p>
                                                        @error('rejection_note')
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 w-full bg-red-600 hover:bg-red-700">
                                                        ✗ Reject Order
                                                    </button>
                                                    <p class="text-xs text-gray-500 mt-1">Status will update to "Rejected"</p>
                                                </form>
                                            @elseif($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED)
                                                <div class="p-4 bg-green-50 border border-green-200 rounded-md">
                                                    <p class="text-sm text-green-800 font-medium">✓ Order Accepted</p>
                                                    <p class="text-xs text-green-700 mt-1">This order has been accepted and is awaiting payment.</p>
                                                </div>
                                            @elseif($customOrder->status === \App\Models\CustomOrder::STATUS_REJECTED)
                                                <div class="p-4 bg-red-50 border border-red-200 rounded-md">
                                                    <p class="text-sm text-red-800 font-medium">✗ Order Rejected</p>
                                                    @if($customOrder->rejection_note)
                                                        <p class="text-xs text-red-700 mt-2"><strong>Reason:</strong> {{ $customOrder->rejection_note }}</p>
                                                    @endif
                                                </div>
                                            @else
                                                <!-- Legacy forms for other statuses -->
                                                <form method="POST" action="{{ route('employee.custom-orders.update', $customOrder->id) }}" class="space-y-4">
                                                    @csrf
                                                    @method('PUT')
                                                    <div>
                                                        <label for="price_estimate_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Final Price</label>
                                                        <input type="number" min="0" step="0.01" id="price_estimate_{{ $customOrder->id }}" name="price_estimate" value="{{ old('price_estimate', $customOrder->price_estimate) }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        @error('price_estimate')
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div>
                                                        <label for="admin_notes_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Internal Notes</label>
                                                        <textarea id="admin_notes_{{ $customOrder->id }}" name="admin_notes" rows="4" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]">{{ old('admin_notes', $customOrder->admin_notes) }}</textarea>
                                                        @error('admin_notes')
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 w-full md:w-auto" style="background:#c59d5f;">
                                                        Save Review (Keep Pending)
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('employee.custom-orders.confirm', $customOrder->id) }}" class="space-y-4 border-t border-gray-100 pt-4 mt-4">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <label for="confirm_price_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Confirmed Price</label>
                                                            <input type="number" min="0" step="0.01" id="confirm_price_{{ $customOrder->id }}" name="price_estimate" value="{{ old('price_estimate', $customOrder->price_estimate) }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        </div>
                                                        @error('price_estimate')
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                        <div>
                                                            <label for="estimated_completion_date_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Estimated Completion Date</label>
                                                            <input type="date" id="estimated_completion_date_{{ $customOrder->id }}" name="estimated_completion_date" value="{{ old('estimated_completion_date', optional($customOrder->estimated_completion_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                        </div>
                                                    </div>
                                                    @error('estimated_completion_date')
                                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                    <div>
                                                        <label for="confirm_admin_notes_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Internal Notes (Optional)</label>
                                                        <textarea id="confirm_admin_notes_{{ $customOrder->id }}" name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]">{{ old('admin_notes', $customOrder->admin_notes) }}</textarea>
                                                    </div>
                                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 w-full md:w-auto" style="background:#2f855a;">
                                                        Confirm &amp; Start Production
                                                    </button>
                                                    <p class="text-xs text-gray-500">Confirmation sets status to In Progress and updates dashboards.</p>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900">Items</h2>
                    <div class="mt-4 divide-y">
                        @foreach($order->items as $oi)
                            <div class="py-4 flex items-start gap-4">
                                <img src="{{ $oi->item?->photo_url }}" class="w-16 h-16 rounded object-cover bg-gray-100 flex-shrink-0" alt="{{ $oi->item?->name }}"/>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $oi->item?->name }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ $oi->quantity }} • ₱{{ number_format($oi->price, 2) }}</div>
                                    <div class="mt-1 text-xs">
                                        @if(($oi->is_backorder ?? false))
                                            <span class="inline-flex px-2 py-0.5 rounded text-[12px] bg-blue-100 text-blue-800">Backorder Item</span>
                                            <div class="text-xs text-blue-700 mt-1">
                                                <div class="font-semibold">Status: {{ str_replace('_', ' ', ucfirst($oi->backorder_status ?? 'pending_stock')) }}</div>
                                                @if($oi->item?->restock_date)
                                                    <div class="text-blue-600">Expected Restock: {{ \Carbon\Carbon::parse($oi->item->restock_date)->format('M d, Y') }}</div>
                                                @endif
                                            </div>
                                            @if($canUpdateStatus)
                                                @if($oi->backorder_status === \App\Models\OrderItem::BO_PENDING || !$oi->backorder_status)
                                                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                                                        <button onclick="updateItem({{ $order->id }}, {{ $oi->id }}, 'in_progress')" class="px-3 py-1 rounded bg-yellow-100 text-yellow-800 text-xs font-medium hover:bg-yellow-200">→ In Progress</button>
                                                        <button onclick="updateItem({{ $order->id }}, {{ $oi->id }}, 'fulfilled')" class="px-3 py-1 rounded bg-green-100 text-green-800 text-xs font-medium hover:bg-green-200">✓ Fulfilled</button>
                                                    </div>
                                                @elseif($oi->backorder_status === \App\Models\OrderItem::BO_IN_PROGRESS)
                                                    <div class="mt-2 flex items-center gap-2">
                                                        <button onclick="updateItem({{ $order->id }}, {{ $oi->id }}, 'fulfilled')" class="px-3 py-1 rounded bg-green-100 text-green-800 text-xs font-medium hover:bg-green-200">✓ Mark Fulfilled</button>
                                                    </div>
                                                @endif
                                            @endif
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded text-[12px] bg-green-100 text-green-800">Standard Item</span>
                                            <div class="text-xs text-amber-700 mt-1">In Stock: <strong>{{ $oi->item?->stock ?? 0 }} units</strong></div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-right flex-shrink-0">₱{{ number_format($oi->subtotal, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                    <script>
                        async function updateItem(orderId, itemId, status){
                            const token = document.querySelector('meta[name="csrf-token"]').content;
                            const res = await fetch(`/employee/orders/${orderId}/items/${itemId}/backorder`, {
                                method: 'POST',
                                headers: {'Content-Type':'application/json','X-CSRF-TOKEN': token, 'X-Requested-With':'XMLHttpRequest'},
                                body: JSON.stringify({ backorder_status: status })
                            });
                            if(res.ok){
                                location.reload();
                            } else {
                                const d = await res.json().catch(()=>({}));
                                alert(d.message || 'Failed to update backorder status');
                            }
                        }
                    </script>
                </div>

                <!-- Tracking & Shipping Section (for Standard, Back Orders, and Custom Orders when ready to ship) -->
                @if(in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed', 'ready_for_delivery']))
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Shipping & Tracking</h2>
                        <form method="POST" action="{{ route('employee.orders.update', $order->id) }}" id="shippingForm" class="mt-4 space-y-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" id="statusInput" value="{{ $order->status }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number</label>
                                    <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number ?? '') }}" placeholder="e.g., TRK123456789" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]">
                                    @error('tracking_number')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Carrier</label>
                                    <div class="w-full rounded-md border border-gray-300 px-3 py-2 bg-gray-50 text-gray-700">
                                        LBC (Automatically Set)
                                    </div>
                                    <input type="hidden" name="carrier" value="lbc">
                                </div>
                            </div>
                            @if($order->status === 'ready_to_ship')
                                <button type="button" onclick="submitWithStatus('shipped')" class="px-4 py-2 rounded-md text-white font-medium bg-blue-600 hover:bg-blue-700">Mark as Shipped</button>
                            @elseif($order->status === 'shipped' && !$order->delivered_at)
                                <button type="button" onclick="submitWithStatus('delivered')" class="px-4 py-2 rounded-md text-white font-medium bg-green-600 hover:bg-green-700">Mark as Delivered</button>
                            @else
                                <button type="submit" class="px-4 py-2 rounded-md text-white font-medium bg-gray-600 hover:bg-gray-700">Save Changes</button>
                            @endif
                        </form>
                        <script>
                            function submitWithStatus(status) {
                                document.getElementById('statusInput').value = status;
                                document.getElementById('shippingForm').submit();
                            }
                        </script>
                    </div>
                @endif

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900">Employee Notes</h2>
                    <div class="mt-3">
                        <textarea class="w-full rounded-md border border-gray-300 px-3 py-2" rows="4" placeholder="Add internal remarks..." disabled></textarea>
                        <p class="mt-2 text-xs text-gray-500">Notes persistence not implemented yet.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Payment Verification Card -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Payment Verification</h2>
                    @php
                        $latestPayment = $order->getLatestPayment();
                        $isRejected = $latestPayment && $latestPayment->isRejected();
                        
                        // Calculate required amount
                        $requiredAmount = 0;
                        if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
                            $requiredAmount = $order->calculateRequiredPaymentForMixedOrder();
                        } else {
                            $requiredAmount = $order->required_payment_amount ?? $order->calculateRequiredPaymentAmount();
                        }
                    @endphp
                    
                    <div class="space-y-4">
                        <!-- Payment Method & Status -->
                        <div class="space-y-3 text-sm text-gray-700">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Method:</span>
                                <span class="font-medium">
                                    @if($order->payment_method === 'gcash' || ($latestPayment && $latestPayment->method === 'gcash'))
                                        GCash
                                    @elseif($order->payment_method === 'bank' || ($latestPayment && $latestPayment->method === 'bank'))
                                        Bank Transfer
                                    @elseif($order->payment_method === 'COD')
                                        COD
                                    @else
                                        {{ ucfirst($order->payment_method ?? 'N/A') }}
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium inline-flex px-2 py-0.5 rounded-full text-xs
                                    @if($paymentStatus === 'paid' && $hasVerifiedPayment) bg-green-100 text-green-800
                                    @elseif($paymentStatus === 'partially_paid') bg-blue-100 text-blue-800
                                    @elseif($paymentStatus === 'pending_verification' || $hasPendingVerification) bg-yellow-100 text-yellow-800
                                    @elseif($paymentStatus === 'payment_rejected' || $isRejected) bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($paymentStatus === 'paid' && $hasVerifiedPayment) ✓ Paid — Verified
                                    @elseif($paymentStatus === 'partially_paid') 💰 Partially Paid
                                    @elseif($paymentStatus === 'pending_verification' || $hasPendingVerification) ⏳ Pending Payment Verification
                                    @elseif($paymentStatus === 'pending_cod') 💰 Pending COD
                                    @elseif($paymentStatus === 'payment_rejected' || $isRejected) ✗ Payment Rejected
                                    @else ✗ Unpaid
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if($latestPayment)
                            <!-- Payment Details -->
                            <div class="border-t border-gray-200 pt-3 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">Required Amount:</span>
                                    <span class="font-semibold text-gray-900">₱{{ number_format($requiredAmount, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">Customer Paid Amount:</span>
                                    <span class="font-semibold @if(abs($latestPayment->amount - $requiredAmount) < 0.01) text-green-700 @else text-red-700 @endif">
                                        ₱{{ number_format($latestPayment->amount, 2) }}
                                    </span>
                                </div>
                                @if($latestPayment->transaction_id)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Reference Number:</span>
                                        <span class="font-medium">{{ $latestPayment->transaction_id }}</span>
                                    </div>
                                @endif
                                @if($latestPayment->proof_image)
                                    <div>
                                        <span class="text-gray-600 block mb-2">Proof of Payment:</span>
                                        <a href="{{ Storage::url($latestPayment->proof_image) }}" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View/Download Proof
                                        </a>
                                    </div>
                                @endif
                                
                                @if($latestPayment->verifier)
                                    <div class="border-t border-gray-200 pt-3">
                                        <div class="text-xs text-gray-500">
                                            Verified by: <span class="font-medium text-gray-700">{{ $latestPayment->verifier->name ?? 'N/A' }}</span>
                                            @if($latestPayment->verified_at)
                                                on {{ $latestPayment->verified_at->format('M d, Y g:i A') }}
                                            @endif
                                        </div>
                                        @if($latestPayment->verification_notes)
                                            <div class="mt-2 text-xs">
                                                <span class="text-gray-500">Notes:</span>
                                                <p class="text-gray-700 mt-1">{{ $latestPayment->verification_notes }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <!-- COD Details -->
                        @php
                            $isCod = $order->payment_method === 'COD';
                        @endphp
                        @if($isCod)
                            <div class="border-t border-gray-200 pt-3 space-y-3">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <p class="text-sm font-medium text-blue-900 mb-2">📦 COD Order Details</p>
                                    @if($order->recipient_name)
                                        <div class="text-xs text-blue-800 mb-1">
                                            <span class="font-medium">Recipient:</span> {{ $order->recipient_name }}
                                        </div>
                                    @endif
                                    @if($order->recipient_phone)
                                        <div class="text-xs text-blue-800 mb-1">
                                            <span class="font-medium">Contact:</span> {{ $order->recipient_phone }}
                                        </div>
                                    @endif
                                    <div class="text-xs text-blue-800 mb-1">
                                        <span class="font-medium">Shipping Address:</span> {{ $order->user->address->address_line ?? 'N/A' }}, {{ $order->user->address->city ?? '' }}, {{ $order->user->address->province ?? '' }}
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Order Amount:</span>
                                        <span class="font-medium">₱{{ number_format($order->total_amount - ($order->shipping_fee ?? 0) - ($order->cod_fee ?? 0), 2) }}</span>
                                    </div>
                                    @if($order->shipping_fee > 0)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Shipping Fee (LBC):</span>
                                            <span class="font-medium">₱{{ number_format($order->shipping_fee, 2) }}</span>
                                        </div>
                                    @endif
                                    @if($order->cod_fee > 0)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">COD Fee:</span>
                                            <span class="font-medium">₱{{ number_format($order->cod_fee, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between text-sm font-semibold pt-2 border-t">
                                        <span class="text-gray-900">Total COD Amount:</span>
                                        <span class="text-blue-900">₱{{ number_format($order->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Verification Actions -->
                        @if($hasPendingVerification)
                            <div class="border-t border-gray-200 pt-4">
                                <form method="POST" action="{{ route('employee.orders.verify-payment', $order->id) }}" id="verifyPaymentForm" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Verification Action:</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="radio" name="action" value="approve" class="mr-2" required>
                                                <span class="text-sm text-green-700 font-medium">✓ Approve Payment</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="action" value="reject" class="mr-2" required>
                                                <span class="text-sm text-red-700 font-medium">✗ Reject Payment</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="rejectionNotes" class="hidden">
                                        <label for="verification_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                            Rejection Reason <span class="text-red-500">*</span>
                                        </label>
                                        <textarea 
                                            id="verification_notes" 
                                            name="verification_notes" 
                                            rows="3" 
                                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-red-500 focus:ring-red-500"
                                            placeholder="Please explain why the payment is being rejected..."
                                        ></textarea>
                                        <p class="mt-1 text-xs text-gray-500">This note will be visible to the customer.</p>
                                    </div>
                                    <button type="submit" class="w-full px-4 py-2 rounded-md text-white font-medium hover:opacity-95 transition-opacity" style="background:#c59d5f;">
                                        Verify Payment
                                    </button>
                                </form>
                            </div>
                        @elseif($hasVerifiedPayment)
                            <div class="border-t border-gray-200 pt-3">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="text-sm text-green-800 font-medium">✓ Payment Verified</p>
                                    <p class="text-xs text-green-700 mt-1">Payment has been verified and approved. Order can proceed to processing.</p>
                                </div>
                            </div>
                        @elseif($paymentStatus === 'pending_cod')
                            <div class="border-t border-gray-200 pt-4">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                    <p class="text-sm text-blue-800 font-medium">💰 COD Payment Pending</p>
                                    <p class="text-xs text-blue-700 mt-1">This order can proceed to processing. Payment will be collected upon delivery via LBC. Mark as collected once payment is received to complete the order.</p>
                                </div>
                                <form method="POST" action="{{ route('employee.orders.collect-cod', $order->id) }}" onsubmit="return confirm('Mark this COD order as collected? This will update the payment status to paid.');">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 rounded-md text-white font-medium hover:opacity-95 transition-opacity bg-green-600 hover:bg-green-700">
                                        ✓ Mark COD as Collected
                                    </button>
                                </form>
                            </div>
                        @elseif($isRejected)
                            <div class="border-t border-gray-200 pt-3">
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                    <p class="text-sm text-red-800 font-medium">✗ Payment Rejected</p>
                                    @if($latestPayment->verification_notes)
                                        <p class="text-xs text-red-700 mt-1"><strong>Reason:</strong> {{ $latestPayment->verification_notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @elseif(!$latestPayment)
                            <div class="border-t border-gray-200 pt-3">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <p class="text-sm text-gray-700">No payment has been submitted yet.</p>
                                </div>
                            </div>
                        @endif

                        <!-- Payment Summary -->
                        @if($paymentStatus === 'partially_paid')
                            <div class="border-t border-gray-200 pt-3 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Order Amount:</span>
                                    <span class="font-medium">₱{{ number_format($order->total_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Amount Paid (50%):</span>
                                    <span class="font-medium text-green-700">₱{{ number_format($order->required_payment_amount ?? ($order->total_amount * 0.5), 2) }}</span>
                                </div>
                                <div class="flex justify-between font-semibold bg-blue-50 p-2 rounded border border-blue-200">
                                    <span class="text-blue-900">Remaining Balance (50%):</span>
                                    <span class="text-blue-900">₱{{ number_format($order->remaining_balance ?? ($order->total_amount * 0.5), 2) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="border-t border-gray-200 pt-3 flex justify-between font-semibold">
                                <span>Total:</span>
                                <span>₱{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Delivery & Shipping Card -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Delivery Information</h2>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div>
                            <div class="text-gray-600 mb-1">Shipping Method</div>
                            <div class="font-medium">Standard Delivery</div>
                        </div>
                        <div>
                            <div class="text-gray-600 mb-1">Carrier</div>
                            <div class="font-medium">LBC</div>
                        </div>
                        @if($order->tracking_number)
                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                <div class="text-gray-600 text-xs mb-1">Tracking Number</div>
                                <div class="font-mono font-bold text-blue-900">{{ $order->tracking_number }}</div>
                            </div>
                        @endif
                        <div>
                            <div class="text-gray-600 mb-1">Estimated Delivery</div>
                            <div class="font-medium">{{ now()->addDays(3)->format('M d, Y') }}</div>
                        </div>
                        @if($order->delivered_at)
                            <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                                <div class="text-gray-600 text-xs mb-1">Delivered On</div>
                                <div class="font-medium text-green-900">{{ \Carbon\Carbon::parse($order->delivered_at)->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Order Status Timeline -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Status Timeline</h2>
                    <div class="space-y-3 text-sm">
                        @php
                            $statusFlow = match($order->order_type) {
                                'standard' => [
                                    'pending' => ['label' => 'Order Placed', 'icon' => '📋', 'done' => true],
                                    'processing' => ['label' => 'Processing', 'icon' => '⚙️', 'done' => in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'ready_to_ship' => ['label' => 'Ready to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
                                    'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
                                ],
                                'backorder' => [
                                    'pending' => ['label' => 'Order Placed', 'icon' => '📋', 'done' => true],
                                    'processing' => ['label' => 'Awaiting Stock', 'icon' => '⏳', 'done' => in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'ready_to_ship' => ['label' => 'Preparing to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
                                    'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
                                ],
                                'custom' => [
                                    'pending' => ['label' => 'Awaiting Price', 'icon' => '💰', 'done' => true],
                                    'in_design' => ['label' => 'In Design', 'icon' => '🎨', 'done' => in_array($order->status, ['in_design', 'in_production', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'in_production' => ['label' => 'In Production', 'icon' => '⚙️', 'done' => in_array($order->status, ['in_production', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'ready_to_ship' => ['label' => 'Ready to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
                                    'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
                                    'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
                                ],
                                default => []
                            };
                        @endphp
                        @foreach($statusFlow as $status => $info)
                            <div class="flex items-start gap-3">
                                <div class="text-lg leading-none pt-0.5">{{ $info['icon'] }}</div>
                                <div class="flex-1">
                                    <div class="text-xs font-medium {{ $info['done'] ? 'text-green-700' : 'text-gray-500' }}">
                                        {{ $info['label'] }}
                                    </div>
                                </div>
                                @if($info['done'])
                                    <span class="text-green-600 text-xs font-bold">✓</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle rejection notes field based on action selection
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('verifyPaymentForm');
            if (form) {
                const actionRadios = form.querySelectorAll('input[name="action"]');
                const rejectionNotes = document.getElementById('rejectionNotes');
                const notesTextarea = document.getElementById('verification_notes');
                
                actionRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'reject') {
                            rejectionNotes.classList.remove('hidden');
                            notesTextarea.setAttribute('required', 'required');
                        } else {
                            rejectionNotes.classList.add('hidden');
                            notesTextarea.removeAttribute('required');
                            notesTextarea.value = '';
                        }
                    });
                });
            }
        });
    </script>

@endsection


