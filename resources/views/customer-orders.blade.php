<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">
    @include('partials.customer-header')

    <section class="pt-24 pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">My Orders</h1>
            <a href="/products" class="px-4 py-2 rounded-md bg-[#c59d5f] text-white hover:opacity-90">Continue Shopping</a>
        </div>

		

        @if(!empty($customOrders) && $customOrders->isNotEmpty())
            <div class="mb-6 bg-white border border-yellow-50 rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-medium text-gray-900">Your Custom Orders</h2>
                <p class="text-sm text-yellow-700 mt-1">Custom order requests currently under review or in production.</p>
                <div class="mt-3 divide-y">
                    @foreach($customOrders as $co)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @php
                                    $images = data_get($co->customization_details, 'images', []);
                                    $firstImage = !empty($images) ? $images[0] : ($co->reference_image_path ?? null);
                                @endphp
                                @if($firstImage)
                                    <img src="{{ Storage::url($firstImage) }}" class="w-12 h-12 rounded object-cover bg-gray-100"/>
                                @else
                                    <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $co->custom_name }}</div>
                                    @php
                                        $isOrderCancelled = $co->order->status === 'cancelled';
                                        $latestReturn = $co->order->returnRequests->sortByDesc('created_at')->first();
                                        $isOrderReturned = $latestReturn && in_array($latestReturn->status, [
                                            \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                            \App\Models\ReturnRequest::STATUS_COMPLETED,
                                        ]);
                                        $statusText = ucfirst(str_replace('_', ' ', $co->status));
                                        if ($isOrderCancelled) {
                                            $statusText .= ' (Cancelled)';
                                        } elseif ($isOrderReturned) {
                                            $statusText .= ' (Returned)';
                                        }
                                    @endphp
                                    <div class="text-xs text-gray-500">Qty: {{ $co->quantity }} • Status: {{ $statusText }}</div>
                                    @if($co->price_estimate)
                                        <div class="text-xs text-yellow-700">Estimated Price: ₱{{ number_format($co->price_estimate, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <div class="text-sm text-gray-700">Order #{{ $co->order->id }}</div>
                                @if($co->order)
                                    @php
                                        $paymentStatus = $co->order->payment_status ?? 'unpaid';
                                        $isFullyPaid = $co->order->isFullyPaid();
                                        $paymentBadgeClass = match($paymentStatus) {
                                            'paid' => 'bg-green-100 text-green-800',
                                            'partially_paid' => 'bg-blue-100 text-blue-800',
                                            'pending_verification' => 'bg-yellow-100 text-yellow-800',
                                            'pending_cod' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-red-100 text-red-800',
                                        };
                                        $paymentLabel = match($paymentStatus) {
                                            'paid' => 'Fully Paid',
                                            'partially_paid' => 'Partially Paid',
                                            'pending_verification' => 'Pending Verification',
                                            'pending_cod' => 'Pending COD',
                                            default => 'Unpaid',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $paymentBadgeClass }}">
                                        {{ $paymentLabel }}
                                    </span>
                                @endif
                                <a href="{{ route('customer.orders.show', $co->order->id) }}" class="text-xs text-yellow-700 hover:underline">View Details</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

		@php
			// Separate parent orders (mixed orders) from regular orders
			$parentOrders = $orders->filter(fn($o) => ($o->order_type ?? '') === 'mixed' && !$o->parent_order_id);
			$regularOrders = $orders->filter(fn($o) => ($o->order_type ?? '') !== 'mixed' && !$o->parent_order_id);
			$standardOrders = $regularOrders->filter(fn($o) => ($o->order_type ?? '') === 'standard');
			$backOrdersOrders = $regularOrders->filter(fn($o) => ($o->order_type ?? '') === 'backorder');
			
			$firstPhoto = function($order){
				$firstItem = optional($order->items)->first();
				return optional(optional($firstItem)->item?->photos?->first())->url;
			};
			
			$getStatusColor = function($status) {
				return match($status) {
					'pending' => 'bg-yellow-100 text-yellow-800',
					'processing', 'ready_to_ship', 'ready_for_delivery' => 'bg-blue-100 text-blue-800',
					'shipped', 'in_production' => 'bg-indigo-100 text-indigo-800',
					'delivered', 'completed' => 'bg-green-100 text-green-800',
					'cancelled' => 'bg-red-100 text-red-800',
					'backorder' => 'bg-orange-100 text-orange-800',
					default => 'bg-gray-100 text-gray-800',
				};
			};
			
			$getDisplayStatus = function($order) use ($getStatusColor) {
				$latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
				$latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
				
				// Priority: Cancelled > Return > Cancellation Request > Return Request > Order Status
				if ($order->status === 'cancelled') {
					return ['label' => 'Cancelled', 'color' => 'bg-red-100 text-red-800'];
				} elseif ($latestReturn && in_array($latestReturn->status, [
					\App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
					\App\Models\ReturnRequest::STATUS_COMPLETED,
				])) {
					return ['label' => 'Returned', 'color' => 'bg-purple-100 text-purple-800'];
				} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED) {
					return ['label' => 'Return Verified', 'color' => 'bg-green-100 text-green-800'];
				} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT) {
					return ['label' => 'Return In Transit', 'color' => 'bg-indigo-100 text-indigo-800'];
				} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED) {
					return ['label' => 'Return Approved', 'color' => 'bg-blue-100 text-blue-800'];
				} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED) {
					return ['label' => 'Return Requested', 'color' => 'bg-yellow-100 text-yellow-800'];
				} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED) {
					return ['label' => 'Cancelled - Refunded', 'color' => 'bg-green-100 text-green-800'];
				} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING) {
					return ['label' => 'Refund Processing', 'color' => 'bg-indigo-100 text-indigo-800'];
				} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED) {
					return ['label' => 'Cancellation Approved', 'color' => 'bg-blue-100 text-blue-800'];
				} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED) {
					return ['label' => 'Cancellation Requested', 'color' => 'bg-yellow-100 text-yellow-800'];
				} else {
					return ['label' => ucfirst($order->status), 'color' => $getStatusColor($order->status)];
				}
			};
		@endphp

		<!-- Mixed Orders (Parent Orders) Section -->
		@if($parentOrders->isNotEmpty())
			<div class="mb-6 bg-white border border-purple-100 rounded-xl shadow-sm p-4">
				<h2 class="text-lg font-medium text-gray-900">Your Mixed Orders</h2>
				<p class="text-sm text-purple-700 mt-1">Orders containing both standard and back order items.</p>
				<div class="mt-3 divide-y">
					@foreach($parentOrders as $parentOrder)
						<div class="py-4">
							<!-- Parent Order Header -->
							<div class="flex items-center justify-between mb-3">
								<div class="flex items-center gap-3 flex-1">
									@if($firstPhoto($parentOrder))
										<img src="{{ $firstPhoto($parentOrder) }}" class="w-12 h-12 rounded object-cover bg-gray-100"/>
									@else
										<div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center text-gray-400">
											<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
											</svg>
										</div>
									@endif
									<div class="flex-1">
										<div class="font-medium text-gray-900">Mixed Order #{{ $parentOrder->id }}</div>
										<div class="text-xs text-gray-500">Placed: {{ $parentOrder->created_at?->format('M d, Y') }}</div>
										<div class="text-xs text-purple-700 font-medium">Total: ₱{{ number_format($parentOrder->total_amount, 2) }} • Payment: {{ $parentOrder->getPaymentStatusLabel() }}</div>
									</div>
								</div>
								<div class="flex flex-col items-end gap-2">
									@php $displayStatus = $getDisplayStatus($parentOrder); @endphp
									<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $displayStatus['color'] }}">
										{{ $displayStatus['label'] }}
									</span>
									@php
										$latestCancellation = $parentOrder->cancellationRequests->sortByDesc('created_at')->first();
										$latestReturn = $parentOrder->returnRequests->sortByDesc('created_at')->first();
									@endphp
									@if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED)
										<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⚠️ Cancel Pending</span>
									@endif
									@if($latestReturn && in_array($latestReturn->status, [
										\App\Models\ReturnRequest::STATUS_REQUESTED,
										\App\Models\ReturnRequest::STATUS_APPROVED,
										\App\Models\ReturnRequest::STATUS_IN_TRANSIT,
									]))
										<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">🔄 Return</span>
									@endif
									<a href="{{ route('customer.orders.show', $parentOrder->id) }}" class="text-xs text-purple-700 hover:underline">View Details</a>
								</div>
							</div>
							
							<!-- Child Orders (Sub-Orders) -->
							@php $childOrders = $parentOrder->childOrders; @endphp
							@if($childOrders->isNotEmpty())
								<div class="ml-4 border-l-2 border-purple-200 pl-4 space-y-2">
									@foreach($childOrders as $childOrder)
										<div class="py-2 flex items-center justify-between bg-purple-50 px-3 rounded">
											<div class="flex-1">
												<div class="text-sm font-medium text-gray-900">
													{{ ucfirst($childOrder->order_type) }} Sub-Order #{{ $childOrder->id }}
												</div>
												<div class="text-xs text-gray-600">
													Amount: ₱{{ number_format($childOrder->total_amount, 2) }} • Status: {{ ucfirst($childOrder->status) }}
												</div>
											</div>
											<a href="{{ route('customer.orders.show', $childOrder->id) }}" class="text-xs text-purple-600 hover:underline">View</a>
										</div>
									@endforeach
								</div>
							@endif
						</div>
					@endforeach
				</div>
			</div>
		@endif

		@if($standardOrders->isNotEmpty())
			<div class="mb-6 bg-white border border-gray-100 rounded-xl shadow-sm p-4">
				<h2 class="text-lg font-medium text-gray-900">Your Standard Orders</h2>
				<p class="text-sm text-gray-700 mt-1">Orders fulfilled from available stock.</p>
				<div class="mt-3 divide-y">
					@foreach($standardOrders as $o)
						<div class="py-3 flex items-center justify-between">
							<div class="flex items-center gap-3">
								@if($firstPhoto($o))
									<img src="{{ $firstPhoto($o) }}" class="w-12 h-12 rounded object-cover bg-gray-100"/>
								@else
									<div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center text-gray-400">
										<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
										</svg>
									</div>
								@endif
								<div>
									@php $firstName = optional(optional($o->items)->first()?->item)->name; @endphp
									<div class="font-medium text-gray-900">{{ $firstName ?? ('Order #'.$o->id) }}</div>
									@php $displayStatus = $getDisplayStatus($o); @endphp
									<div class="text-xs text-gray-500">Placed: {{ $o->created_at?->format('M d, Y') }} • Status: {{ $displayStatus['label'] }}</div>
								</div>
							</div>
							<div class="flex flex-col items-end gap-2">
								<div class="text-sm text-gray-700">Order #{{ $o->id }}</div>
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $displayStatus['color'] }}">
									{{ $displayStatus['label'] }}
								</span>
								@php
									$latestCancellation = $o->cancellationRequests->sortByDesc('created_at')->first();
									$latestReturn = $o->returnRequests->sortByDesc('created_at')->first();
								@endphp
								@if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED)
									<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⚠️ Cancel Pending</span>
								@endif
								@if($latestReturn && in_array($latestReturn->status, [
									\App\Models\ReturnRequest::STATUS_REQUESTED,
									\App\Models\ReturnRequest::STATUS_APPROVED,
									\App\Models\ReturnRequest::STATUS_IN_TRANSIT,
								]))
									<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">🔄 Return</span>
								@endif
								<a href="{{ route('customer.orders.show', $o->id) }}" class="text-xs text-yellow-700 hover:underline">View Details</a>
							</div>
						</div>
					@endforeach
				</div>
			</div>
		@endif

		@if($backOrdersOrders->isNotEmpty())
			<div class="mb-6 bg-white border border-gray-100 rounded-xl shadow-sm p-4">
				<h2 class="text-lg font-medium text-gray-900">Your Back-Orders</h2>
				<p class="text-sm text-blue-700 mt-1">Orders awaiting stock; we’ll notify you when ready.</p>
				<div class="mt-3 divide-y">
					@foreach($backOrdersOrders as $o)
						<div class="py-3 flex items-center justify-between">
							<div class="flex items-center gap-3">
								@if($firstPhoto($o))
									<img src="{{ $firstPhoto($o) }}" class="w-12 h-12 rounded object-cover bg-gray-100"/>
								@else
									<div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center text-gray-400">
										<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
										</svg>
									</div>
								@endif
								<div>
									@php $firstName = optional(optional($o->items)->first()?->item)->name; @endphp
									<div class="font-medium text-gray-900">{{ $firstName ?? ('Order #'.$o->id) }}</div>
									@php $displayStatus = $getDisplayStatus($o); @endphp
									<div class="text-xs text-gray-500">Placed: {{ $o->created_at?->format('M d, Y') }} • Status: {{ $displayStatus['label'] }}</div>
									@if($o->expected_restock_date)
										<div class="text-xs text-blue-700">Expected: {{ $o->expected_restock_date->format('M d, Y') }}</div>
									@endif
								</div>
							</div>
							<div class="flex flex-col items-end gap-2">
								<div class="text-sm text-gray-700">Order #{{ $o->id }}</div>
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $displayStatus['color'] }}">
									{{ $displayStatus['label'] }}
								</span>
								@php
									$latestCancellation = $o->cancellationRequests->sortByDesc('created_at')->first();
									$latestReturn = $o->returnRequests->sortByDesc('created_at')->first();
								@endphp
								@if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED)
									<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⚠️ Cancel Pending</span>
								@endif
								@if($latestReturn && in_array($latestReturn->status, [
									\App\Models\ReturnRequest::STATUS_REQUESTED,
									\App\Models\ReturnRequest::STATUS_APPROVED,
									\App\Models\ReturnRequest::STATUS_IN_TRANSIT,
								]))
									<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">🔄 Return</span>
								@endif
								<a href="{{ route('customer.orders.show', $o->id) }}" class="text-xs text-yellow-700 hover:underline">View Details</a>
							</div>
						</div>
					@endforeach
				</div>
			</div>
		@endif

		@if($parentOrders->isEmpty() && $standardOrders->isEmpty() && $backOrdersOrders->isEmpty() && (empty($customOrders) || $customOrders->isEmpty()))
			<div class="px-4 py-6 bg-white border border-gray-100 rounded-xl shadow-sm text-center text-gray-500">No orders yet.</div>
		@endif
    </section>

    @include('partials.customer-footer')
</body>
</html>


