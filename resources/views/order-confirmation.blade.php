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
    <style>body{font-family:'Poppins','Inter',ui-sans-serif,system-ui;}</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body class="bg-white">
    <nav class="bg-white/90 backdrop-blur border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center">
                <img src="/images/logo.png" alt="WOW Carmen" class="w-8 h-8 mr-3"/>
                <a href="/" class="text-lg sm:text-xl font-semibold text-gray-900 tracking-wide">WOW Carmen</a>
            </div>
            <div class="hidden md:flex items-center space-x-8">
                <a href="/dashboard" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Home</a>
                <a href="/products" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Products</a>
            </div>
        </div>
    </nav>

    <section class="pt-12 pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">{{ request()->is('customer/*') ? 'Order Details' : 'Thank you for your order!' }}</h1>
                <p class="mt-2 text-gray-600">Order <span class="font-medium text-gray-900">#{{ $order->id }}</span> • {{ $order->created_at?->format('M d, Y') }}</p>
                <div class="mt-2 text-sm text-gray-700 flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded border border-gray-300 capitalize">Type: {{ $order->order_type }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded border border-gray-300 capitalize">Status: {{ $order->status }}</span>
                </div>

                @if(($hasBackorder ?? false))
                    <div class="mt-3 p-4 rounded-md border border-blue-200 bg-blue-50 text-blue-800">
                        This order contains back-ordered items. We’ll ship once restocked.
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900">Items</h2>
                        <div class="mt-4 divide-y">
                            @foreach($order->items as $oi)
                                <div class="py-4 flex items-center gap-4">
                                    <img src="{{ $oi->item?->photo_url }}" class="w-16 h-16 rounded object-cover bg-gray-100" alt="{{ $oi->item?->name }}"/>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">{{ $oi->item?->name }}</div>
                                        <div class="text-xs text-gray-500">Qty: {{ $oi->quantity }} • ₱{{ number_format($oi->price, 2) }}</div>
                                        @php
                                            $stockNote = null;
                                            if ($oi->is_backorder ?? false) {
                                                $rd = optional($oi->item?->restock_date)->format('M d');
                                                $stockNote = $rd ? "Backordered, restock expected $rd" : 'Backordered';
                                            }
                                        @endphp
                                        @if($stockNote)
                                            <div class="text-xs text-amber-700 mt-1">{{ $stockNote }}</div>
                                        @endif
                                    </div>
                                    <div class="text-sm font-medium">₱{{ number_format($oi->subtotal, 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

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
                        <div class="mt-3 text-sm text-gray-700 space-y-1">
                            <div>Method: {{ $order->payment_method ?? '—' }}</div>
                            <div>Status: {{ $order->payment_status ?? '—' }}</div>
                            <div class="pt-2 border-t mt-2 font-medium">Total: ₱{{ number_format($order->total_amount, 2) }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('customer.orders.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Back to My Orders</a>
                            <button class="inline-flex items-center px-4 py-2 rounded-md text-white hover:opacity-90" style="background:#c59d5f;">Download Invoice (PDF)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-[#1a1a1a] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-t" style="border-color:#c59d5f"></div>
            <div class="py-12 grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <h4 class="text-lg font-semibold mb-4">About</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Help</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Shipping</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Info</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="/contact" class="hover:text-[#c59d5f] transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-[#c59d5f] transition-colors">Terms &amp; Conditions</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>


