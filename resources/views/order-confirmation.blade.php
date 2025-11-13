<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
	<style>
body{font-family:'Poppins','Inter',ui-sans-serif,system-ui;}
[x-cloak]{display:none !important;}
	</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family:'Poppins','Inter',ui-sans-serif,system-ui;">
	@include('partials.customer-header')

	<section class="pt-24 pb-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
				<h1 class="text-2xl font-semibold text-gray-900">{{ request()->is('customer/*') ? 'Order Details' : 'Thank you for your order!' }}</h1>
				<p class="mt-2 text-gray-600">Order <span class="font-medium text-gray-900">#{{ $order->id }}</span> • {{ $order->created_at?->format('M d, Y') }}</p>
				@php
					$statusColor = [
						'pending' => 'bg-yellow-100 text-yellow-800',
						'processing' => 'bg-blue-100 text-blue-800',
						'completed' => 'bg-green-100 text-green-800',
						'cancelled' => 'bg-red-100 text-red-800',
						'backorder' => 'bg-indigo-100 text-indigo-800',
					][$order->status] ?? 'bg-gray-100 text-gray-800';
                    $hasBackorder = $order->items->contains(fn($oi) => ($oi->is_backorder ?? false));
                    $isCustomOrder = $order->order_type === 'custom';
                    $customOrder = $isCustomOrder ? $order->customOrders->first() : null;
				@endphp
                <div class="mt-2 text-sm flex flex-wrap gap-2">
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-100 text-gray-800">Type: {{ $order->order_type }}</span>
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ $statusColor }}">Status: {{ $order->status }}</span>
                </div>

                <!-- Parent-Sub Order Info -->
                @if($order->order_type === 'mixed' && $order->childOrders->isNotEmpty())
                    <div class="mt-4 p-4 rounded-md border border-purple-200 bg-purple-50">
                        <h3 class="font-medium text-purple-900">Mixed Order Details</h3>
                        <p class="text-sm text-purple-800 mt-1">Your order contains both standard and back order items. They will be processed and shipped separately for efficiency.</p>
                        <div class="mt-3 space-y-2">
                            @foreach($order->childOrders as $child)
                                <div class="flex items-center justify-between bg-white px-3 py-2 rounded border border-purple-100 text-sm">
                                    <span class="font-medium text-gray-900">{{ ucfirst($child->order_type) }} Sub-Order #{{ $child->id }}</span>
                                    <span class="text-purple-700">₱{{ number_format($child->total_amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 pt-3 border-t border-purple-200">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-purple-900">Total Amount</span>
                                <span class="text-lg font-bold text-purple-900">₱{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                @elseif($order->parent_order_id)
                    <!-- This is a sub-order -->
                    @php $parentOrder = $order->parentOrder; @endphp
                    <div class="mt-4 p-4 rounded-md border border-purple-200 bg-purple-50">
                        <h3 class="font-medium text-purple-900">Part of Mixed Order</h3>
                        <p class="text-sm text-purple-800 mt-1">This is a {{ ucfirst($order->order_type) }} sub-order from your parent mixed order.</p>
                        <div class="mt-2 text-sm">
                            <strong>Parent Order:</strong> #{{ $parentOrder->id }} (₱{{ number_format($parentOrder->total_amount, 2) }})
                        </div>
                    </div>
                @endif

                @if($hasBackorder && !$order->parent_order_id && $order->order_type !== 'mixed')
                    <div class="mt-4 p-4 rounded-md border border-blue-200 bg-blue-50">
                        <h3 class="font-medium text-blue-900">Order Status</h3>
                        <div class="mt-2 text-sm text-blue-800 space-y-1">
                            <p><strong>✓ Standard Items:</strong> Ready for processing and will ship soon</p>
                            <p><strong>⏳ Back Order Items:</strong> Awaiting stock - will ship separately once restocked</p>
                        </div>
                    </div>
                @endif                @if($isCustomOrder && $customOrder)
                    @php
                        $customStatusColor = match($customOrder->status) {
                            'pending_review' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'in_production' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                    @endphp
                    <div class="mt-4 p-4 rounded-md border border-yellow-200 bg-yellow-50">
                        <h3 class="font-medium text-yellow-900">Custom Order Status</h3>
                        <div class="mt-2 text-sm text-yellow-800 space-y-1">
                            <p><strong>Status:</strong> <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $customStatusColor }}">{{ str_replace('_', ' ', ucfirst($customOrder->status)) }}</span></p>
                            @if($customOrder->price_estimate)
                                <p><strong>Price Estimate:</strong> ₱{{ number_format((float)$customOrder->price_estimate, 2) }}</p>
                            @endif
                            @if($customOrder->estimated_completion_date)
                                <p><strong>Estimated Completion:</strong> {{ $customOrder->estimated_completion_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>			<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    @if($isCustomOrder && $customOrder)
                        <!-- Custom Order Details -->
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                            <h2 class="font-semibold text-gray-900">Custom Order Details</h2>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Product Name</div>
                                    <div class="mt-1 text-gray-900 font-semibold">{{ $customOrder->custom_name }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Description</div>
                                    <div class="mt-1 text-gray-900 whitespace-pre-line">{{ $customOrder->description }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Quantity</div>
                                    <div class="mt-1 text-gray-900">{{ $customOrder->quantity }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                            <h2 class="font-semibold text-gray-900">Reference Images</h2>
                            @php
                                $images = data_get($customOrder->customization_details, 'images', []);
                                // Fallback to single image for backward compatibility
                                if (empty($images) && $customOrder->reference_image_path) {
                                    $images = [$customOrder->reference_image_path];
                                }
                            @endphp
                            @if(!empty($images))
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($images as $imagePath)
                                        <div class="relative">
                                            <img src="{{ Storage::url($imagePath) }}" alt="Reference Image {{ $loop->iteration }}" class="w-full h-auto rounded-lg border border-gray-200 shadow-sm object-cover">
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 text-sm text-gray-500">No reference images provided.</p>
                            @endif
                        </div>

                        @if($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED && ($customOrder->order?->payment_status !== 'paid'))
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                                <h2 class="font-semibold text-gray-900">Payment</h2>
                                <p class="mt-3 text-sm text-gray-700">Your custom order has been confirmed. Please complete payment to begin production.</p>
                                <div class="mt-4">
                                    <a href="{{ route('checkout.page', ['order_id' => $customOrder->order?->id]) }}" class="inline-flex items-center px-4 py-2 rounded-md text-white" style="background:#c59d5f;">Proceed to Checkout</a>
                                </div>
                            </div>
                        @endif
                    @else
                        <!-- Standard/Back Order Items -->
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                            <h2 class="font-semibold text-gray-900">Items</h2>
                            
                            @php
                                $standardItems = $order->items->filter(fn($oi) => !($oi->is_backorder ?? false));
                                $backOrderItems = $order->items->filter(fn($oi) => ($oi->is_backorder ?? false));
                            @endphp

                            <!-- Standard Items Section -->
                            @if($standardItems->isNotEmpty())
                                <div class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="font-medium text-gray-900">Standard Items</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Ready for processing</span>
                                    </div>
                                    <div class="divide-y">
                                        @foreach($standardItems as $oi)
                                            <div class="py-4 flex items-center gap-4">
                                                @php $photo = optional(optional($oi->item?->photos)->first())->url; @endphp
                                                @if($photo)
                                                    <img src="{{ $photo }}" class="w-16 h-16 rounded object-cover bg-gray-100" alt="{{ $oi->item?->name }}"/>
                                                @else
                                                    <div class="w-16 h-16 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-900">{{ $oi->item?->name }}</div>
                                                    <div class="text-xs text-gray-500">Qty: {{ $oi->quantity }} • ₱{{ number_format($oi->price, 2) }}</div>
                                                </div>
                                                <div class="text-sm font-medium">₱{{ number_format($oi->subtotal, 2) }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Back Order Items Section -->
                            @if($backOrderItems->isNotEmpty())
                                <div class="mt-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="font-medium text-gray-900">Back Order Items</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Awaiting stock</span>
                                    </div>
                                    <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                        <p class="text-sm text-blue-700">These items will be shipped separately once they're back in stock.</p>
                                    </div>
                                    <div class="divide-y">
                                        @foreach($backOrderItems as $oi)
                                            <div class="py-4 flex items-center gap-4">
                                                @php $photo = optional(optional($oi->item?->photos)->first())->url; @endphp
                                                @if($photo)
                                                    <img src="{{ $photo }}" class="w-16 h-16 rounded object-cover bg-gray-100" alt="{{ $oi->item?->name }}"/>
                                                @else
                                                    <div class="w-16 h-16 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-900">{{ $oi->item?->name }}</div>
                                                    <div class="text-xs text-gray-500">Qty: {{ $oi->quantity }} • ₱{{ number_format($oi->price, 2) }}</div>
                                                    @if($oi->item?->restock_date)
                                                        <div class="text-xs text-blue-700 mt-1">Expected restock: {{ $oi->item->restock_date->format('M d, Y') }}</div>
                                                    @endif
                                                </div>
                                                <div class="text-sm font-medium">₱{{ number_format($oi->subtotal, 2) }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Delivery Information</h2>
                        <div class="mt-3 text-sm text-gray-700">
                            <div>Shipping Method: Standard</div>
                            <div>Estimated Delivery: {{ now()->addDays(5)->format('M d, Y') }}</div>
                            <div>Tracking Number: —</div>
                        </div>
                    </div>
                </div>

				<div class="space-y-6">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Customer Information</h2>
                        <div class="mt-3 text-sm text-gray-700">
                            <div class="font-medium">{{ $order->user?->name }}</div>
                            <div>{{ $order->user?->email }}</div>
                            <div class="mt-2">{{ $order->user?->address?->address_line }}</div>
                            <div>{{ $order->user?->address?->city }}, {{ $order->user?->address?->province }} {{ $order->user?->address?->postal_code }}</div>
                            <div>{{ $order->user?->address?->phone_number }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Payment</h2>
						@php 
							$latestPayment = optional($order->payments)->sortByDesc('id')->first();
							$method = $latestPayment?->method ?? $order->payment_method ?? '—';
							// Format method display
							if ($method === 'gcash' || $method === 'GCash') {
								$method = 'GCash';
							} elseif ($method === 'bank' || $method === 'Bank' || $method === 'Bank Transfer') {
								$method = 'Bank Transfer';
							}
						@endphp
						<div class="mt-3 text-sm text-gray-700 space-y-1">
							<div>Method: {{ $method }}</div>
							<div>
								Status: 
								@php
									$paymentStatus = $latestPayment?->status ?? $order->payment_status ?? '—';
									$statusBadgeClass = match($paymentStatus) {
										'paid' => 'bg-green-100 text-green-800',
										'pending_verification' => 'bg-yellow-100 text-yellow-800',
										'unpaid' => 'bg-red-100 text-red-800',
										default => 'bg-gray-100 text-gray-800',
									};
									$statusLabel = match($paymentStatus) {
										'paid' => 'Paid ✓',
										'pending_verification' => 'Pending Verification',
										'unpaid' => 'Unpaid',
										default => ucfirst(str_replace('_', ' ', $paymentStatus)),
									};
								@endphp
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
							</div>
							@if($paymentStatus === 'pending_verification')
								<div class="mt-2 p-2 bg-yellow-50 border border-yellow-100 rounded text-xs">
									<p class="text-yellow-800">Your bank transfer proof is being verified by our team. We'll confirm payment shortly.</p>
								</div>
							@endif
							@if(!empty($latestPayment?->transaction_id))
								<div>Reference: {{ $latestPayment->transaction_id }}</div>
							@endif
							@if(!empty($latestPayment?->proof_image))
								<div><a href="{{ Storage::url($latestPayment->proof_image) }}" target="_blank" class="text-[#c59d5f] hover:underline">View Bank Proof</a></div>
							@endif
							<div class="pt-2 border-t mt-2 font-medium">Total: ₱{{ number_format($order->total_amount, 2) }}</div>
						</div>
                    </div>

					<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
						<a href="{{ route('customer.orders.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Back to My Orders</a>
					</div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.customer-footer')
</body>
</html>


