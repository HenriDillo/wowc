@extends('layouts.employee')

@section('page_title', 'Order #'.$order->id)

@section('content')

    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Order #{{ $order->id }}</h1>
                <div class="mt-1 text-sm text-gray-600">Placed {{ $order->created_at?->format('M d, Y') }} • <span class="capitalize">{{ $order->order_type }}</span></div>
            </div>
            <form method="POST" action="{{ route('employee.orders.update', $order->id) }}" class="flex items-center gap-3">
                @csrf
                @method('PUT')
                <select name="status" class="rounded-md border-gray-300">
                    @php $statuses = ['pending','processing','ready_for_delivery','ready_to_ship','shipped','delivered','in_design','in_production','completed','cancelled']; @endphp
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected($order->status===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button class="px-4 py-2 rounded-md text-white" style="background:#c59d5f;">Update</button>
            </form>
        </div>

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
                                        $notes = [];
                                        if (($oi->is_preorder ?? false) && $oi->item?->release_date) {
                                            $notes[] = 'Preorder — releases '.$oi->item->release_date->format('M d, Y');
                                        }
                                        if (($oi->is_backorder ?? false)) {
                                            $notes[] = 'Backorder'.($oi->item?->restock_date ? ' — restock '.$oi->item->restock_date->format('M d, Y') : '');
                                        }
                                        if ($oi->item && !$oi->item->isPreorder() && !$oi->item->isBackorder()) {
                                            $notes[] = 'In stock: '.$oi->item->stock;
                                        }
                                    @endphp
                                    @if(!empty($notes))
                                        <div class="text-xs text-amber-700 mt-1">{{ implode(' • ', $notes) }}</div>
                                    @endif
                                </div>
                                <div class="text-sm font-medium">₱{{ number_format($oi->subtotal, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900">Employee Notes</h2>
                    <div class="mt-3">
                        <textarea class="w-full rounded-md border-gray-300" rows="4" placeholder="Add internal remarks..." disabled></textarea>
                        <p class="mt-2 text-xs text-gray-500">Notes persistence not implemented yet.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900">Payment</h2>
                    <div class="mt-3 text-sm text-gray-700 space-y-1">
                        <div>Method: {{ $order->payment_method ?? '—' }}</div>
                        <div>Status: {{ $order->payment_status ?? '—' }}</div>
                        <div class="pt-2 border-t mt-2 font-medium">Total: ₱{{ number_format($order->total_amount, 2) }}</div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900">Delivery</h2>
                    <div class="mt-3 text-sm text-gray-700">
                        <div>Shipping Method: Standard</div>
                        <div>ETA: {{ now()->addDays(3)->format('M d, Y') }}</div>
                        <div>Tracking: —</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


