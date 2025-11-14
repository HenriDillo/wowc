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
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('info'))
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    {{ session('info') }}
                </div>
            @endif
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
                    <div class="mt-4 p-4 rounded-md border {{ $customOrder->status === 'rejected' ? 'border-red-200 bg-red-50' : ($customOrder->status === 'approved' ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50') }}">
                        <h3 class="font-medium {{ $customOrder->status === 'rejected' ? 'text-red-900' : ($customOrder->status === 'approved' ? 'text-green-900' : 'text-yellow-900') }}">Custom Order Status</h3>
                        <div class="mt-2 text-sm {{ $customOrder->status === 'rejected' ? 'text-red-800' : ($customOrder->status === 'approved' ? 'text-green-800' : 'text-yellow-800') }} space-y-2">
                            <p><strong>Status:</strong> <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $customStatusColor }}">{{ str_replace('_', ' ', ucfirst($customOrder->status)) }}</span></p>
                            
                            @if($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED)
                                @if($customOrder->price_estimate)
                                    <p><strong>Price:</strong> ₱{{ number_format((float)$customOrder->price_estimate, 2) }}</p>
                                @endif
                                @if($customOrder->estimated_completion_date)
                                    <p><strong>Expected Completion Date:</strong> {{ $customOrder->estimated_completion_date->format('M d, Y') }}</p>
                                @endif
                                <p class="text-xs mt-2 italic">Your order has been accepted. Please proceed to payment to begin production.</p>
                            @elseif($customOrder->status === \App\Models\CustomOrder::STATUS_REJECTED)
                                @if($customOrder->rejection_note)
                                    <div class="mt-2 p-3 bg-white border border-red-200 rounded-md">
                                        <p class="font-semibold text-red-900 mb-1">Rejection Reason:</p>
                                        <p class="text-red-800 whitespace-pre-line">{{ $customOrder->rejection_note }}</p>
                                    </div>
                                @endif
                            @elseif($customOrder->status === \App\Models\CustomOrder::STATUS_PENDING_REVIEW)
                                <p class="text-xs mt-2 italic">Your order is currently under review. We'll notify you once a decision has been made.</p>
                                @if($customOrder->price_estimate)
                                    <p><strong>Price Estimate:</strong> ₱{{ number_format((float)$customOrder->price_estimate, 2) }}</p>
                                @endif
                            @else
                                @if($customOrder->price_estimate)
                                    <p><strong>Price Estimate:</strong> ₱{{ number_format((float)$customOrder->price_estimate, 2) }}</p>
                                @endif
                                @if($customOrder->estimated_completion_date)
                                    <p><strong>Estimated Completion:</strong> {{ $customOrder->estimated_completion_date->format('M d, Y') }}</p>
                                @endif
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
                                
                                @if($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED)
                                    <div class="pt-4 border-t border-gray-200 space-y-2">
                                        <div>
                                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Price</div>
                                            <div class="mt-1 text-gray-900 font-semibold text-lg">₱{{ number_format((float)$customOrder->price_estimate, 2) }}</div>
                                        </div>
                                        @if($customOrder->estimated_completion_date)
                                            <div>
                                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Expected Completion Date</div>
                                                <div class="mt-1 text-gray-900">{{ $customOrder->estimated_completion_date->format('M d, Y') }}</div>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($customOrder->status === \App\Models\CustomOrder::STATUS_REJECTED && $customOrder->rejection_note)
                                    <div class="pt-4 border-t border-gray-200">
                                        <div class="p-3 bg-red-50 border border-red-200 rounded-md">
                                            <div class="text-xs font-medium text-red-900 uppercase tracking-wide mb-2">Rejection Reason</div>
                                            <div class="text-sm text-red-800 whitespace-pre-line">{{ $customOrder->rejection_note }}</div>
                                        </div>
                                    </div>
                                @endif
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

                        @if($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED)
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                                <h2 class="font-semibold text-gray-900">Payment</h2>
                                @if($customOrder->order && $customOrder->order->isFullyPaid())
                                    <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm font-medium text-green-800">Fully Paid</span>
                                        </div>
                                        <p class="mt-2 text-xs text-green-700">Your payment has been completed. Production will begin soon.</p>
                                    </div>
                                @else
                                    <p class="mt-3 text-sm text-gray-700">Your custom order has been confirmed. Please complete payment to begin production.</p>
                                    <div class="mt-4">
                                        <a href="{{ route('checkout.page', ['order_id' => $customOrder->order?->id]) }}" class="inline-flex items-center px-4 py-2 rounded-md text-white font-medium hover:opacity-95 transition-opacity" style="background:#c59d5f;">Proceed to Checkout</a>
                                    </div>
                                @endif
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
							} elseif ($method === 'COD') {
								$method = 'COD';
							}
							$isCod = $order->payment_method === 'COD';
						@endphp
						<div class="mt-3 text-sm text-gray-700 space-y-1">
							<div>Method: {{ $method }}</div>
							<div>
								Status: 
								@php
									$paymentStatus = $latestPayment?->status ?? $order->payment_status ?? '—';
									$isRejected = $latestPayment && $latestPayment->isRejected();
									$isPendingVerification = $latestPayment && $latestPayment->isPendingVerification();
									
									// Check order payment status for rejection
									if ($order->payment_status === 'payment_rejected' || $isRejected) {
										$statusBadgeClass = 'bg-red-100 text-red-800';
										$statusLabel = 'Payment Rejected';
									} else {
										$statusBadgeClass = match($paymentStatus) {
											'paid' => 'bg-green-100 text-green-800',
											'pending_verification' => 'bg-yellow-100 text-yellow-800',
											'pending_cod' => 'bg-blue-100 text-blue-800',
											'unpaid' => 'bg-red-100 text-red-800',
											default => 'bg-gray-100 text-gray-800',
										};
										$statusLabel = match($paymentStatus) {
											'paid' => 'Paid ✓',
											'pending_verification' => 'Pending Verification',
											'pending_cod' => 'Pending COD',
											'unpaid' => 'Unpaid',
											default => ucfirst(str_replace('_', ' ', $paymentStatus)),
										};
									}
								@endphp
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
							</div>
							@if($isPendingVerification || $paymentStatus === 'pending_verification')
								<div class="mt-2 p-2 bg-yellow-50 border border-yellow-100 rounded text-xs">
									<p class="text-yellow-800">Your bank transfer proof is being verified by our team. We'll confirm payment shortly.</p>
								</div>
							@endif
							@if($isRejected || $order->payment_status === 'payment_rejected')
								<div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
									<p class="text-red-800 font-medium text-sm mb-1">✗ Payment Rejected</p>
									@if($latestPayment && $latestPayment->verification_notes)
										<p class="text-red-700 text-xs mt-1">
											<strong>Reason:</strong> {{ $latestPayment->verification_notes }}
										</p>
									@else
										<p class="text-red-700 text-xs mt-1">Your payment was rejected. Please contact support for more information.</p>
									@endif
									<p class="text-red-600 text-xs mt-2">Please submit a new payment or contact our support team for assistance.</p>
								</div>
							@endif
							@if(!empty($latestPayment?->transaction_id))
								<div>Reference: {{ $latestPayment->transaction_id }}</div>
							@endif
							@if(!empty($latestPayment?->proof_image))
								<div><a href="{{ Storage::url($latestPayment->proof_image) }}" target="_blank" class="text-[#c59d5f] hover:underline">View Bank Proof</a></div>
							@endif
							
							@if($isCod)
								<div class="pt-2 border-t mt-2 space-y-2">
									@if($order->recipient_name)
										<div>
											<span class="text-gray-600">Recipient:</span>
											<span class="font-medium">{{ $order->recipient_name }}</span>
										</div>
										@if($order->recipient_phone)
											<div>
												<span class="text-gray-600">Contact:</span>
												<span class="font-medium">{{ $order->recipient_phone }}</span>
											</div>
										@endif
									@endif
									@if($order->shipping_fee > 0)
										<div class="flex justify-between">
											<span class="text-gray-600">Shipping Fee (LBC):</span>
											<span class="font-medium">₱{{ number_format($order->shipping_fee, 2) }}</span>
										</div>
									@endif
									@if($order->cod_fee > 0)
										<div class="flex justify-between">
											<span class="text-gray-600">COD Fee:</span>
											<span class="font-medium">₱{{ number_format($order->cod_fee, 2) }}</span>
										</div>
									@endif
									<div class="pt-2 border-t mt-2">
										<p class="text-xs text-blue-700 mb-1">💡 Pay the total amount (items + shipping + COD fee) to LBC upon delivery.</p>
									</div>
								</div>
							@endif
							
							<div class="pt-2 border-t mt-2 font-medium flex justify-between">
								<span>Total:</span>
								<span>₱{{ number_format($order->total_amount, 2) }}</span>
							</div>
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


