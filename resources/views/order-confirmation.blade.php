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
					// Check for cancellation and return requests
					$latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
					$latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
					
					// Determine the primary status to display
					$displayStatus = null;
					$displayStatusColor = null;
					$displayStatusLabel = null;
					
					// Priority: Cancelled > Return > Cancellation Request > Return Request > Order Status
					if ($order->status === 'cancelled') {
						$displayStatus = 'cancelled';
						$displayStatusColor = 'bg-red-100 text-red-800';
						$displayStatusLabel = 'Cancelled';
					} elseif ($latestReturn && in_array($latestReturn->status, [
						\App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
						\App\Models\ReturnRequest::STATUS_COMPLETED,
					])) {
						$displayStatus = 'returned';
						$displayStatusColor = 'bg-purple-100 text-purple-800';
						$displayStatusLabel = 'Returned';
					} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED) {
						$displayStatus = 'return_verified';
						$displayStatusColor = 'bg-green-100 text-green-800';
						$displayStatusLabel = 'Return Verified';
					} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT) {
						$displayStatus = 'return_in_transit';
						$displayStatusColor = 'bg-indigo-100 text-indigo-800';
						$displayStatusLabel = 'Return In Transit';
					} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED) {
						$displayStatus = 'return_approved';
						$displayStatusColor = 'bg-blue-100 text-blue-800';
						$displayStatusLabel = 'Return Approved';
					} elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED) {
						$displayStatus = 'return_requested';
						$displayStatusColor = 'bg-yellow-100 text-yellow-800';
						$displayStatusLabel = 'Return Requested';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED) {
						$displayStatus = 'cancelled_refunded';
						$displayStatusColor = 'bg-green-100 text-green-800';
						$displayStatusLabel = 'Cancelled - Refunded';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING) {
						$displayStatus = 'cancellation_refund_processing';
						$displayStatusColor = 'bg-indigo-100 text-indigo-800';
						$displayStatusLabel = 'Cancellation - Refund Processing';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED) {
						$displayStatus = 'cancellation_approved';
						$displayStatusColor = 'bg-blue-100 text-blue-800';
						$displayStatusLabel = 'Cancellation Approved';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED) {
						$displayStatus = 'cancellation_requested';
						$displayStatusColor = 'bg-yellow-100 text-yellow-800';
						$displayStatusLabel = 'Cancellation Requested';
					} elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REJECTED) {
						$displayStatus = 'cancellation_rejected';
						$displayStatusColor = 'bg-red-100 text-red-800';
						$displayStatusLabel = 'Cancellation Rejected';
					} else {
						// Use normal order status
						$statusColor = [
							'pending' => 'bg-yellow-100 text-yellow-800',
							'processing' => 'bg-blue-100 text-blue-800',
							'ready_to_ship' => 'bg-indigo-100 text-indigo-800',
							'shipped' => 'bg-purple-100 text-purple-800',
							'delivered' => 'bg-green-100 text-green-800',
							'completed' => 'bg-green-100 text-green-800',
							'cancelled' => 'bg-red-100 text-red-800',
							'backorder' => 'bg-indigo-100 text-indigo-800',
							'in_design' => 'bg-blue-100 text-blue-800',
							'in_production' => 'bg-indigo-100 text-indigo-800',
							'ready_for_delivery' => 'bg-purple-100 text-purple-800',
						][$order->status] ?? 'bg-gray-100 text-gray-800';
						
						// Friendly status labels based on order type
						$statusLabels = [
							'standard' => [
								'pending' => 'Order Placed',
								'processing' => 'Processing',
								'ready_to_ship' => 'Ready to Ship',
								'shipped' => 'Shipped',
								'delivered' => 'Delivered',
								'completed' => 'Completed',
								'cancelled' => 'Cancelled',
							],
							'backorder' => [
								'pending' => 'Order Placed',
								'processing' => 'Awaiting Stock',
								'ready_to_ship' => 'Preparing to Ship',
								'shipped' => 'Shipped',
								'delivered' => 'Delivered',
								'completed' => 'Completed',
								'cancelled' => 'Cancelled',
							],
							'custom' => [
								'pending' => 'Awaiting Price',
								'in_design' => 'In Design',
								'in_production' => 'In Production',
								'ready_for_delivery' => 'Ready for Delivery',
								'ready_to_ship' => 'Ready to Ship',
								'shipped' => 'Shipped',
								'delivered' => 'Delivered',
								'completed' => 'Completed',
								'cancelled' => 'Cancelled',
							],
						];
						
						$orderType = $order->order_type;
						$currentStatus = $order->status;
						$displayStatusLabel = $statusLabels[$orderType][$currentStatus] ?? ucwords(str_replace('_', ' ', $currentStatus));
						$displayStatusColor = $statusColor;
					}
					
                    $hasBackorder = $order->items->contains(fn($oi) => ($oi->is_backorder ?? false));
                    $isCustomOrder = $order->order_type === 'custom';
                    $customOrder = $isCustomOrder ? $order->customOrders->first() : null;
				@endphp
                <div class="mt-2 text-sm flex flex-wrap gap-2">
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-100 text-gray-800">Type: {{ $order->order_type }}</span>
					<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $displayStatusColor }}">Status: {{ $displayStatusLabel }}</span>
					@if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED)
						<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⚠️ Cancellation Pending</span>
					@endif
					@if($latestReturn && in_array($latestReturn->status, [
						\App\Models\ReturnRequest::STATUS_REQUESTED,
						\App\Models\ReturnRequest::STATUS_APPROVED,
						\App\Models\ReturnRequest::STATUS_IN_TRANSIT,
					]))
						<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">🔄 Return In Progress</span>
					@endif
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
                    @php
                        $standardItems = $order->items->filter(fn($oi) => !($oi->is_backorder ?? false));
                        $backOrderItems = $order->items->filter(fn($oi) => ($oi->is_backorder ?? false));
                        $isOrderCancelled = $order->status === 'cancelled';
                        $isOrderCompleted = in_array($order->status, ['delivered', 'completed']);
                        $isOrderShipped = in_array($order->status, ['shipped', 'delivered', 'completed']);
                        $isOrderProcessing = in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed']);
                        $latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
                        $latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
                    @endphp
                    <div class="mt-4 p-4 rounded-md border {{ $isOrderCancelled ? 'border-red-200 bg-red-50' : 'border-blue-200 bg-blue-50' }}">
                        <h3 class="font-medium {{ $isOrderCancelled ? 'text-red-900' : 'text-blue-900' }}">Order Status</h3>
                        <div class="mt-2 text-sm {{ $isOrderCancelled ? 'text-red-800' : 'text-blue-800' }} space-y-1">
                            @if($isOrderCancelled)
                                <p><strong>✗ Order:</strong> Cancelled</p>
                                @if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED)
                                    <p><strong>✓ Refund:</strong> Completed</p>
                                @elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING)
                                    <p><strong>⏳ Refund:</strong> Processing</p>
                                @endif
                                
                                @if($latestCancellation && ($latestCancellation->refund_amount || $latestCancellation->refund_method))
                                    <div class="mt-3 pt-3 border-t border-red-200">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Refund Details</h4>
                                        <div class="space-y-1.5 text-xs">
                                            @if($latestCancellation->refund_amount)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Refund Amount:</span>
                                                    <span class="font-semibold text-gray-900">₱{{ number_format($latestCancellation->refund_amount, 2) }}</span>
                                                </div>
                                            @endif
                                            @if($latestCancellation->refund_method)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Refund Method:</span>
                                                    <span class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $latestCancellation->refund_method)) }}</span>
                                                </div>
                                            @endif
                                            @if($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Status:</span>
                                                    <span class="font-medium text-green-700">Completed</span>
                                                </div>
                                            @elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Status:</span>
                                                    <span class="font-medium text-indigo-700">Processing</span>
                                                </div>
                                            @elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_FAILED)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Status:</span>
                                                    <span class="font-medium text-red-700">Failed</span>
                                                </div>
                                            @endif
                                            @if($latestCancellation->handledBy)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Processed By:</span>
                                                    <span class="text-gray-900">{{ $latestCancellation->handledBy->name }}</span>
                                                </div>
                                            @endif
                                            @if($latestCancellation->updated_at)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Processed Date:</span>
                                                    <span class="text-gray-900">{{ $latestCancellation->updated_at->format('M d, Y h:i A') }}</span>
                                                </div>
                                            @endif
                                            @php
                                                // Extract transaction ID from notes if available
                                                $transactionId = null;
                                                if ($latestCancellation->notes) {
                                                    if (preg_match('/Transaction ID:\s*([^\n]+)/i', $latestCancellation->notes, $matches)) {
                                                        $transactionId = trim($matches[1]);
                                                    } elseif (preg_match('/Refund Processed:.*?Transaction ID:\s*([^\n]+)/i', $latestCancellation->notes, $matches)) {
                                                        $transactionId = trim($matches[1]);
                                                    }
                                                }
                                            @endphp
                                            @if($transactionId)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Transaction ID:</span>
                                                    <span class="text-gray-900 font-mono text-xs">{{ $transactionId }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @elseif($latestReturn && in_array($latestReturn->status, [
                                \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                \App\Models\ReturnRequest::STATUS_COMPLETED,
                            ]))
                                <p><strong>✓ Order:</strong> Returned</p>
                            @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED)
                                <p><strong>✓ Return:</strong> Verified - Refund processing</p>
                            @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT)
                                <p><strong>🔄 Return:</strong> In Transit</p>
                            @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED)
                                <p><strong>✓ Return:</strong> Approved - Please submit tracking number</p>
                            @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED)
                                <p><strong>⏳ Return:</strong> Request Pending Review</p>
                            @elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED)
                                <p><strong>⏳ Cancellation:</strong> Request Pending Review</p>
                            @elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED)
                                <p><strong>✓ Cancellation:</strong> Approved</p>
                            @else
                                @if($standardItems->isNotEmpty())
                                    @if($isOrderCompleted)
                                        <p><strong>✓ Standard Items:</strong> Delivered</p>
                                    @elseif($isOrderShipped)
                                        <p><strong>✓ Standard Items:</strong> Shipped</p>
                                    @elseif($isOrderProcessing)
                                        <p><strong>✓ Standard Items:</strong> Processing</p>
                                    @else
                                        <p><strong>✓ Standard Items:</strong> Ready for processing and will ship soon</p>
                                    @endif
                                @endif
                                @if($backOrderItems->isNotEmpty())
                                    @if($isOrderCompleted)
                                        <p><strong>✓ Back Order Items:</strong> Delivered</p>
                                    @elseif($isOrderShipped)
                                        <p><strong>✓ Back Order Items:</strong> Shipped</p>
                                    @elseif($order->status === 'ready_to_ship')
                                        <p><strong>✓ Back Order Items:</strong> Preparing to ship</p>
                                    @else
                                        <p><strong>⏳ Back Order Items:</strong> Awaiting stock - will ship separately once restocked</p>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                @if($isCustomOrder && $customOrder)
                    @php
                        $isOrderCancelled = $order->status === 'cancelled';
                        $latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
                        $isOrderReturned = $latestReturn && in_array($latestReturn->status, [
                            \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                            \App\Models\ReturnRequest::STATUS_COMPLETED,
                        ]);
                        $customStatusColor = match($customOrder->status) {
                            'pending_review' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'in_production' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                        $borderColor = $customOrder->status === 'rejected' || $isOrderCancelled || $isOrderReturned ? 'border-red-200 bg-red-50' : ($customOrder->status === 'approved' ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50');
                        $textColor = $customOrder->status === 'rejected' || $isOrderCancelled || $isOrderReturned ? 'text-red-900' : ($customOrder->status === 'approved' ? 'text-green-900' : 'text-yellow-900');
                        $contentColor = $customOrder->status === 'rejected' || $isOrderCancelled || $isOrderReturned ? 'text-red-800' : ($customOrder->status === 'approved' ? 'text-green-800' : 'text-yellow-800');
                    @endphp
                    <div class="mt-4 p-4 rounded-md border {{ $borderColor }}">
                        <h3 class="font-medium {{ $textColor }}">Custom Order Status</h3>
                        <div class="mt-2 text-sm {{ $contentColor }} space-y-2">
                            <p><strong>Status:</strong> <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $customStatusColor }}">{{ str_replace('_', ' ', ucfirst($customOrder->status)) }}</span></p>
                            
                            @if($isOrderCancelled)
                                <p class="italic">Your custom order has been cancelled.</p>
                            @elseif($isOrderReturned)
                                <p class="italic">Your custom order has been returned.</p>
                            @elseif($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED)
                                @php
                                    $orderStatus = $customOrder->order->status ?? 'pending';
                                    $isOrderCompleted = in_array($orderStatus, ['delivered', 'completed']);
                                    $isOrderShipped = in_array($orderStatus, ['shipped', 'delivered', 'completed']);
                                    $isOrderInProduction = in_array($orderStatus, ['in_production', 'ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed']);
                                @endphp
                                @if($customOrder->price_estimate)
                                    <p><strong>Price:</strong> ₱{{ number_format((float)$customOrder->price_estimate, 2) }}</p>
                                @endif
                                @if($customOrder->estimated_completion_date)
                                    <p><strong>Expected Completion Date:</strong> {{ $customOrder->estimated_completion_date->format('M d, Y') }}</p>
                                @endif
                                @if($isOrderCompleted)
                                    <p class="text-xs mt-2 italic text-green-700">✓ Your custom order has been delivered.</p>
                                @elseif($isOrderShipped)
                                    <p class="text-xs mt-2 italic text-purple-700">✓ Your custom order has been shipped.</p>
                                @elseif($isOrderInProduction)
                                    <p class="text-xs mt-2 italic text-blue-700">Your custom order is currently in production.</p>
                                @elseif(!$customOrder->order || !$customOrder->order->isFullyPaid())
                                    <p class="text-xs mt-2 italic">Your order has been accepted. Please proceed to payment to begin production.</p>
                                @else
                                    <p class="text-xs mt-2 italic text-blue-700">Payment received. Production will begin soon.</p>
                                @endif
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
                                @php
                                    $orderStatus = $customOrder->order->status ?? 'pending';
                                    $isOrderCompleted = in_array($orderStatus, ['delivered', 'completed']);
                                    $isOrderShipped = in_array($orderStatus, ['shipped', 'delivered', 'completed']);
                                    $isOrderInProduction = in_array($orderStatus, ['in_production', 'ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed']);
                                    // Check if first payment (50%) has been verified - if so, hide checkout button
                                    $hasFirstPaymentVerified = $customOrder->order && $customOrder->order->hasVerifiedPayment();
                                @endphp
                                @if($customOrder->order && $customOrder->order->isFullyPaid())
                                    @if($isOrderCompleted)
                                        <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm font-medium text-green-800">Fully Paid</span>
                                            </div>
                                            <p class="mt-2 text-xs text-green-700">✓ Your custom order has been delivered.</p>
                                        </div>
                                    @elseif($isOrderShipped)
                                        <div class="mt-3 p-3 bg-purple-50 border border-purple-200 rounded-md">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm font-medium text-purple-800">Fully Paid</span>
                                            </div>
                                            <p class="mt-2 text-xs text-purple-700">✓ Your custom order has been shipped.</p>
                                        </div>
                                    @elseif($isOrderInProduction)
                                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm font-medium text-blue-800">Fully Paid</span>
                                            </div>
                                            <p class="mt-2 text-xs text-blue-700">Your custom order is currently in production.</p>
                                        </div>
                                    @else
                                        <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm font-medium text-green-800">Fully Paid</span>
                                            </div>
                                            <p class="mt-2 text-xs text-green-700">Your payment has been completed. Production will begin soon.</p>
                                        </div>
                                    @endif
                                @elseif($hasFirstPaymentVerified)
                                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm font-medium text-blue-800">First Payment Verified</span>
                                        </div>
                                        <p class="mt-2 text-xs text-blue-700">Your 50% down payment has been verified. The remaining balance will be collected by the courier upon delivery.</p>
                                        @if($customOrder->order->remaining_balance > 0)
                                            <p class="mt-2 text-sm font-semibold text-blue-900">Remaining Balance: ₱{{ number_format($customOrder->order->remaining_balance, 2) }}</p>
                                        @endif
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
                                @php
                                    $isOrderCancelled = $order->status === 'cancelled';
                                    $isOrderCompleted = in_array($order->status, ['delivered', 'completed']);
                                    $isOrderShipped = in_array($order->status, ['shipped', 'delivered', 'completed']);
                                    $isOrderProcessing = in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed']);
                                    $latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
                                    $latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
                                    
                                    // Determine status based on cancellation/return first
                                    if ($isOrderCancelled) {
                                        $standardStatusLabel = 'Cancelled';
                                        $standardStatusColor = 'bg-red-100 text-red-800';
                                    } elseif ($latestReturn && in_array($latestReturn->status, [
                                        \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                        \App\Models\ReturnRequest::STATUS_COMPLETED,
                                    ])) {
                                        $standardStatusLabel = 'Returned';
                                        $standardStatusColor = 'bg-purple-100 text-purple-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED) {
                                        $standardStatusLabel = 'Return Verified';
                                        $standardStatusColor = 'bg-green-100 text-green-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT) {
                                        $standardStatusLabel = 'Return In Transit';
                                        $standardStatusColor = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED) {
                                        $standardStatusLabel = 'Return Approved';
                                        $standardStatusColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED) {
                                        $standardStatusLabel = 'Return Requested';
                                        $standardStatusColor = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED) {
                                        $standardStatusLabel = 'Cancelled - Refunded';
                                        $standardStatusColor = 'bg-green-100 text-green-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING) {
                                        $standardStatusLabel = 'Refund Processing';
                                        $standardStatusColor = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED) {
                                        $standardStatusLabel = 'Cancellation Approved';
                                        $standardStatusColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED) {
                                        $standardStatusLabel = 'Cancellation Requested';
                                        $standardStatusColor = 'bg-yellow-100 text-yellow-800';
                                    } else {
                                        $standardStatusLabel = $isOrderCompleted ? 'Delivered' : ($isOrderShipped ? 'Shipped' : ($order->status === 'ready_to_ship' ? 'Ready to Ship' : ($isOrderProcessing ? 'Processing' : 'Ready for processing')));
                                        $standardStatusColor = $isOrderCompleted ? 'bg-green-100 text-green-800' : ($isOrderShipped ? 'bg-purple-100 text-purple-800' : ($order->status === 'ready_to_ship' ? 'bg-indigo-100 text-indigo-800' : ($isOrderProcessing ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')));
                                    }
                                @endphp
                                <div class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="font-medium text-gray-900">Standard Items</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $standardStatusColor }}">{{ $standardStatusLabel }}</span>
                                    </div>
                                    @if($isOrderCancelled)
                                        <div class="p-3 mb-3 bg-red-50 border border-red-100 rounded-lg">
                                            <p class="text-sm text-red-700">✗ This order has been cancelled.</p>
                                            @if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED)
                                                <p class="text-sm text-green-700 mt-1">✓ Refund has been processed.</p>
                                            @elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING)
                                                <p class="text-sm text-indigo-700 mt-1">⏳ Refund is being processed.</p>
                                            @endif
                                            
                                            @if($latestCancellation && ($latestCancellation->refund_amount || $latestCancellation->refund_method))
                                                <div class="mt-3 pt-3 border-t border-red-200">
                                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Refund Details</h4>
                                                    <div class="space-y-1.5 text-xs">
                                                        @if($latestCancellation->refund_amount)
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Refund Amount:</span>
                                                                <span class="font-semibold text-gray-900">₱{{ number_format($latestCancellation->refund_amount, 2) }}</span>
                                                            </div>
                                                        @endif
                                                        @if($latestCancellation->refund_method)
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Refund Method:</span>
                                                                <span class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $latestCancellation->refund_method)) }}</span>
                                                            </div>
                                                        @endif
                                                        @if($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED)
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Status:</span>
                                                                <span class="font-medium text-green-700">Completed</span>
                                                            </div>
                                                        @elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING)
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Status:</span>
                                                                <span class="font-medium text-indigo-700">Processing</span>
                                                            </div>
                                                        @elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_FAILED)
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Status:</span>
                                                                <span class="font-medium text-red-700">Failed</span>
                                                            </div>
                                                        @endif
                                                        @if($latestCancellation->handledBy)
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Processed By:</span>
                                                                <span class="text-gray-900">{{ $latestCancellation->handledBy->name }}</span>
                                                            </div>
                                                        @endif
                                                        @if($latestCancellation->updated_at)
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Processed Date:</span>
                                                                <span class="text-gray-900">{{ $latestCancellation->updated_at->format('M d, Y h:i A') }}</span>
                                                            </div>
                                                        @endif
                                                        @php
                                                            // Extract transaction ID from notes if available
                                                            $transactionId = null;
                                                            if ($latestCancellation->notes) {
                                                                if (preg_match('/Transaction ID:\s*([^\n]+)/i', $latestCancellation->notes, $matches)) {
                                                                    $transactionId = trim($matches[1]);
                                                                } elseif (preg_match('/Refund Processed:.*?Transaction ID:\s*([^\n]+)/i', $latestCancellation->notes, $matches)) {
                                                                    $transactionId = trim($matches[1]);
                                                                }
                                                            }
                                                        @endphp
                                                        @if($transactionId)
                                                            <div class="flex justify-between">
                                                                <span class="text-gray-600">Transaction ID:</span>
                                                                <span class="text-gray-900 font-mono text-xs">{{ $transactionId }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($latestReturn && in_array($latestReturn->status, [
                                        \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                        \App\Models\ReturnRequest::STATUS_COMPLETED,
                                    ]))
                                        <div class="p-3 mb-3 bg-purple-50 border border-purple-100 rounded-lg">
                                            <p class="text-sm text-purple-700">✓ This order has been returned and processed.</p>
                                        </div>
                                    @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED)
                                        <div class="p-3 mb-3 bg-green-50 border border-green-100 rounded-lg">
                                            <p class="text-sm text-green-700">✓ Return has been verified. Refund is being processed.</p>
                                        </div>
                                    @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT)
                                        <div class="p-3 mb-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                                            <p class="text-sm text-indigo-700">🔄 Return is in transit. We'll verify once received.</p>
                                        </div>
                                    @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED)
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">✓ Return approved. Please submit your tracking number.</p>
                                        </div>
                                    @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED)
                                        <div class="p-3 mb-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                                            <p class="text-sm text-yellow-700">⏳ Return request is pending review.</p>
                                        </div>
                                    @elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED)
                                        <div class="p-3 mb-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                                            <p class="text-sm text-yellow-700">⏳ Cancellation request is pending review.</p>
                                        </div>
                                    @elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED)
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">✓ Cancellation approved. Processing refund.</p>
                                        </div>
                                    @elseif($isOrderCompleted)
                                        <div class="p-3 mb-3 bg-green-50 border border-green-100 rounded-lg">
                                            <p class="text-sm text-green-700">✓ These items have been delivered.</p>
                                        </div>
                                    @elseif($isOrderShipped)
                                        <div class="p-3 mb-3 bg-purple-50 border border-purple-100 rounded-lg">
                                            <p class="text-sm text-purple-700">✓ These items have been shipped.</p>
                                        </div>
                                    @elseif($order->status === 'ready_to_ship')
                                        <div class="p-3 mb-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                                            <p class="text-sm text-indigo-700">✓ These items are ready to ship.</p>
                                        </div>
                                    @elseif($isOrderProcessing)
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">These items are being processed.</p>
                                        </div>
                                    @endif
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
                                @php
                                    $isOrderCancelled = $order->status === 'cancelled';
                                    $isOrderCompleted = in_array($order->status, ['delivered', 'completed']);
                                    $isOrderShipped = in_array($order->status, ['shipped', 'delivered', 'completed']);
                                    $latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
                                    $latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
                                    
                                    // Determine status based on cancellation/return first
                                    if ($isOrderCancelled) {
                                        $backorderStatusLabel = 'Cancelled';
                                        $backorderStatusColor = 'bg-red-100 text-red-800';
                                    } elseif ($latestReturn && in_array($latestReturn->status, [
                                        \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                        \App\Models\ReturnRequest::STATUS_COMPLETED,
                                    ])) {
                                        $backorderStatusLabel = 'Returned';
                                        $backorderStatusColor = 'bg-purple-100 text-purple-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED) {
                                        $backorderStatusLabel = 'Return Verified';
                                        $backorderStatusColor = 'bg-green-100 text-green-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT) {
                                        $backorderStatusLabel = 'Return In Transit';
                                        $backorderStatusColor = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED) {
                                        $backorderStatusLabel = 'Return Approved';
                                        $backorderStatusColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED) {
                                        $backorderStatusLabel = 'Return Requested';
                                        $backorderStatusColor = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED) {
                                        $backorderStatusLabel = 'Cancelled - Refunded';
                                        $backorderStatusColor = 'bg-green-100 text-green-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING) {
                                        $backorderStatusLabel = 'Refund Processing';
                                        $backorderStatusColor = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED) {
                                        $backorderStatusLabel = 'Cancellation Approved';
                                        $backorderStatusColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED) {
                                        $backorderStatusLabel = 'Cancellation Requested';
                                        $backorderStatusColor = 'bg-yellow-100 text-yellow-800';
                                    } else {
                                        $backorderStatusLabel = $isOrderCompleted ? 'Delivered' : ($isOrderShipped ? 'Shipped' : ($order->status === 'ready_to_ship' ? 'Preparing to Ship' : 'Awaiting stock'));
                                        $backorderStatusColor = $isOrderCompleted ? 'bg-green-100 text-green-800' : ($isOrderShipped ? 'bg-purple-100 text-purple-800' : ($order->status === 'ready_to_ship' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800'));
                                    }
                                @endphp
                                <div class="mt-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="font-medium text-gray-900">Back Order Items</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backorderStatusColor }}">{{ $backorderStatusLabel }}</span>
                                    </div>
                                    @if($isOrderCancelled)
                                        <div class="p-3 mb-3 bg-red-50 border border-red-100 rounded-lg">
                                            <p class="text-sm text-red-700">✗ This order has been cancelled.</p>
                                            @if($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED)
                                                <p class="text-sm text-green-700 mt-1">✓ Refund has been processed.</p>
                                            @elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING)
                                                <p class="text-sm text-indigo-700 mt-1">⏳ Refund is being processed.</p>
                                            @endif
                                        </div>
                                    @elseif($latestReturn && in_array($latestReturn->status, [
                                        \App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
                                        \App\Models\ReturnRequest::STATUS_COMPLETED,
                                    ]))
                                        <div class="p-3 mb-3 bg-purple-50 border border-purple-100 rounded-lg">
                                            <p class="text-sm text-purple-700">✓ This order has been returned and processed.</p>
                                        </div>
                                    @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_VERIFIED)
                                        <div class="p-3 mb-3 bg-green-50 border border-green-100 rounded-lg">
                                            <p class="text-sm text-green-700">✓ Return has been verified. Refund is being processed.</p>
                                        </div>
                                    @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_IN_TRANSIT)
                                        <div class="p-3 mb-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                                            <p class="text-sm text-indigo-700">🔄 Return is in transit. We'll verify once received.</p>
                                        </div>
                                    @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_APPROVED)
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">✓ Return approved. Please submit your tracking number.</p>
                                        </div>
                                    @elseif($latestReturn && $latestReturn->status === \App\Models\ReturnRequest::STATUS_REQUESTED)
                                        <div class="p-3 mb-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                                            <p class="text-sm text-yellow-700">⏳ Return request is pending review.</p>
                                        </div>
                                    @elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REQUESTED)
                                        <div class="p-3 mb-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                                            <p class="text-sm text-yellow-700">⏳ Cancellation request is pending review.</p>
                                        </div>
                                    @elseif($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_APPROVED)
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">✓ Cancellation approved. Processing refund.</p>
                                        </div>
                                    @elseif(!$isOrderShipped)
                                        <div class="p-3 mb-3 bg-blue-50 border border-blue-100 rounded-lg">
                                            <p class="text-sm text-blue-700">These items will be shipped separately once they're back in stock.</p>
                                        </div>
                                    @elseif($isOrderCompleted)
                                        <div class="p-3 mb-3 bg-green-50 border border-green-100 rounded-lg">
                                            <p class="text-sm text-green-700">✓ These items have been delivered.</p>
                                        </div>
                                    @else
                                        <div class="p-3 mb-3 bg-purple-50 border border-purple-100 rounded-lg">
                                            <p class="text-sm text-purple-700">✓ These items have been shipped.</p>
                                        </div>
                                    @endif
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
                        <div class="mt-3 text-sm text-gray-700 space-y-2">
                            <div>Shipping Method: Standard</div>
                            <div>Estimated Delivery: {{ now()->addDays(5)->format('M d, Y') }}</div>
                            @if($order->order_type === 'mixed' && $order->childOrders->isNotEmpty())
                                <!-- For mixed orders, show tracking numbers for each child order -->
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <div class="font-medium text-gray-900 mb-2">Tracking Numbers by Sub-Order:</div>
                                    <div class="space-y-2">
                                        @foreach($order->childOrders as $child)
                                            <div class="bg-gray-50 p-2 rounded border border-gray-100">
                                                <div class="text-xs text-gray-600 mb-1">{{ ucfirst($child->order_type) }} Sub-Order #{{ $child->id }}</div>
                                                @if($child->tracking_number)
                                                    <div class="bg-blue-50 p-2 rounded border border-blue-100">
                                                        <div class="text-xs text-gray-600 mb-1">Tracking Number</div>
                                                        <div class="font-mono font-bold text-blue-900">{{ $child->tracking_number }}</div>
                                                    </div>
                                                @else
                                                    <div class="text-xs text-gray-500 italic">Tracking number not yet assigned</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <!-- For single orders (standard, backorder, custom) -->
                                @if($order->tracking_number)
                                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                        <div class="text-xs text-gray-600 mb-1">Tracking Number</div>
                                        <div class="font-mono font-bold text-blue-900">{{ $order->tracking_number }}</div>
                                    </div>
                                @else
                                    <div>Tracking Number: <span class="text-gray-500 italic">Not yet assigned</span></div>
                                @endif
                            @endif
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
									$hasFinalPaymentVerified = $order->final_payment_verified ?? false;
									$hasRemainingBalance = ($order->remaining_balance ?? 0) > 0;
									
									// Check order payment status for rejection
									if ($order->payment_status === 'payment_rejected' || $isRejected) {
										$statusBadgeClass = 'bg-red-100 text-red-800';
										$statusLabel = 'Payment Rejected';
									} else {
										// Check for refund status
										$latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
										$latestReturn = $order->returnRequests->sortByDesc('created_at')->first();
										$isRefunded = ($order->payment_status === 'refunded') || 
											($latestCancellation && $latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED) ||
											($latestReturn && in_array($latestReturn->status, [
												\App\Models\ReturnRequest::STATUS_REFUND_COMPLETED,
												\App\Models\ReturnRequest::STATUS_COMPLETED,
											]));
										
										if ($isRefunded) {
											$statusBadgeClass = 'bg-purple-100 text-purple-800';
											$statusLabel = 'Refunded';
										} else {
											// Handle final payment verification status
											if ($hasFinalPaymentVerified && $order->payment_status === 'paid') {
												$statusBadgeClass = 'bg-green-100 text-green-800';
												$statusLabel = 'Fully Paid ✓';
											} elseif ($order->payment_status === 'partially_paid' && $hasFinalPaymentVerified) {
												$statusBadgeClass = 'bg-green-100 text-green-800';
												$statusLabel = 'Fully Paid ✓';
											} elseif ($order->payment_status === 'partially_paid') {
												$statusBadgeClass = 'bg-blue-100 text-blue-800';
												$statusLabel = 'Partially Paid (50% Down)';
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
										}
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
							
							@php
								$hasRemainingBalance = ($order->remaining_balance ?? 0) > 0;
								$hasFinalPaymentVerified = $order->final_payment_verified ?? false;
								$requires50PercentUpfront = $order->order_type === 'backorder' || $order->order_type === 'custom' || ($order->order_type === 'mixed' && $hasRemainingBalance);
							@endphp
							@if($hasRemainingBalance && $requires50PercentUpfront)
								<div class="pt-3 border-t mt-3 space-y-2">
									@if($hasFinalPaymentVerified)
										<div class="flex justify-between items-center bg-green-50 p-3 rounded border border-green-200">
											<div>
												<span class="text-sm font-medium text-green-900">Final Payment Verified ✓</span>
												<p class="text-xs text-green-700 italic mt-1">Remaining balance has been collected</p>
											</div>
											<span class="text-sm font-semibold text-green-900">₱{{ number_format($order->remaining_balance, 2) }}</span>
										</div>
									@else
										<div class="flex justify-between items-center bg-blue-50 p-3 rounded border border-blue-200">
											<div>
												<span class="text-sm font-medium text-blue-900">Remaining Balance</span>
												<p class="text-xs text-blue-700 italic mt-1">To be collected by courier</p>
											</div>
											<span class="text-sm font-semibold text-blue-900">₱{{ number_format($order->remaining_balance, 2) }}</span>
										</div>
									@endif
								</div>
							@endif
						</div>
                    </div>

					@php
						$latestCancellation = $order->cancellationRequests->sortByDesc('created_at')->first();
					@endphp
					@if($latestCancellation && ($latestCancellation->refund_amount || $latestCancellation->refund_method || in_array($latestCancellation->status, [
						\App\Models\CancellationRequest::STATUS_REFUND_COMPLETED,
						\App\Models\CancellationRequest::STATUS_REFUND_PROCESSING,
						\App\Models\CancellationRequest::STATUS_REFUND_FAILED,
					])))
						<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
							<h2 class="font-semibold text-gray-900 mb-3">Refund Information</h2>
							<div class="space-y-3">
								@if($latestCancellation->refund_amount)
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Amount</span>
										<span class="text-lg font-semibold text-gray-900">₱{{ number_format($latestCancellation->refund_amount, 2) }}</span>
									</div>
								@endif
								
								@if($latestCancellation->refund_method)
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Method</span>
										<span class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $latestCancellation->refund_method)) }}</span>
									</div>
								@endif
								
								@if($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_COMPLETED)
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Status</span>
										<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
									</div>
								@elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_PROCESSING)
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Status</span>
										<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Processing</span>
									</div>
								@elseif($latestCancellation->status === \App\Models\CancellationRequest::STATUS_REFUND_FAILED)
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Refund Status</span>
										<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Failed</span>
									</div>
								@endif
								
								@php
									// Extract transaction ID from notes if available
									$transactionId = null;
									if ($latestCancellation->notes) {
										// Try multiple patterns to extract transaction ID
										$patterns = [
											'/Transaction ID:\s*([^\n\r]+)/i',
											'/Refund Processed:.*?Transaction ID:\s*([^\n\r]+)/i',
											'/Transaction ID[:\s]+([^\n\r]+)/i',
											'/Transaction[:\s]+([A-Za-z0-9]+)/i',
										];
										foreach ($patterns as $pattern) {
											if (preg_match($pattern, $latestCancellation->notes, $matches)) {
												$transactionId = trim($matches[1]);
												if ($transactionId && $transactionId !== 'N/A') {
													break;
												}
											}
										}
									}
								@endphp
								@if($transactionId)
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Transaction ID</span>
										<span class="text-sm font-mono text-gray-900">{{ $transactionId }}</span>
									</div>
								@endif
								
								@if($latestCancellation->handledBy)
									<div class="flex justify-between items-center py-2 border-b border-gray-100">
										<span class="text-sm text-gray-600">Processed By</span>
										<span class="text-sm text-gray-900">{{ $latestCancellation->handledBy->name }}</span>
									</div>
								@endif
								
								@if($latestCancellation->updated_at)
									<div class="flex justify-between items-center py-2">
										<span class="text-sm text-gray-600">Processed Date</span>
										<span class="text-sm text-gray-900">{{ $latestCancellation->updated_at->format('M d, Y h:i A') }}</span>
									</div>
								@endif
								
								@if($latestCancellation->notes && $transactionId === null)
									<div class="mt-3 pt-3 border-t border-gray-200">
										<p class="text-xs font-medium text-gray-600 mb-1">Notes</p>
										<p class="text-xs text-gray-700 whitespace-pre-line">{{ $latestCancellation->notes }}</p>
									</div>
								@endif
							</div>
						</div>
					@endif

					<!-- Order Status Timeline -->
					<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
						<h2 class="font-semibold text-gray-900 mb-3">Order Status</h2>
						<div class="space-y-3 text-sm">
							@php
								// Show cancelled status if order is cancelled
								if ($order->status === 'cancelled') {
									$statusFlow = ['cancelled' => ['label' => 'Cancelled', 'icon' => '✗', 'done' => true]];
								} else {
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
											'in_design' => ['label' => 'In Design', 'icon' => '🎨', 'done' => in_array($order->status, ['in_design', 'in_production', 'ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'in_production' => ['label' => 'In Production', 'icon' => '⚙️', 'done' => in_array($order->status, ['in_production', 'ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'ready_for_delivery' => ['label' => 'Ready for Delivery', 'icon' => '📦', 'done' => in_array($order->status, ['ready_for_delivery', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'ready_to_ship' => ['label' => 'Ready to Ship', 'icon' => '📦', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
											'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
											'delivered' => ['label' => 'Delivered', 'icon' => '✓', 'done' => in_array($order->status, ['delivered', 'completed'])],
										],
										default => []
									};
								}
							@endphp
							@foreach($statusFlow as $status => $info)
								@php
									$isCancelled = $order->status === 'cancelled';
									$isDone = $info['done'] ?? false;
								@endphp
								<div class="flex items-start gap-3">
									<div class="text-lg leading-none pt-0.5">{{ $info['icon'] }}</div>
									<div class="flex-1">
										<div class="text-xs font-medium {{ $isCancelled ? 'text-red-700' : ($isDone ? 'text-green-700' : 'text-gray-500') }}">
											{{ $info['label'] }}
										</div>
									</div>
									@if($isDone)
										<span class="{{ $isCancelled ? 'text-red-600' : 'text-green-600' }} text-xs font-bold">{{ $isCancelled ? '✗' : '✓' }}</span>
									@endif
								</div>
							@endforeach
						</div>
					</div>

					@include('partials.cancellation-request-form', ['order' => $order])
					@include('partials.return-request-form', ['order' => $order])

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


