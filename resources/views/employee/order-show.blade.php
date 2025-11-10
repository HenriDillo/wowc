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
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Quantity</div>
                                                    <div class="mt-1 text-gray-900">{{ $customOrder->quantity }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Dimensions</div>
                                                    <div class="mt-1 text-gray-900">{{ data_get($customOrder->customization_details, 'dimensions', '—') }}</div>
                                                </div>
                                            </div>
                                            @if(data_get($customOrder->customization_details, 'additional_instructions'))
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Additional Instructions</div>
                                                    <div class="mt-1 text-gray-900 whitespace-pre-line">{{ data_get($customOrder->customization_details, 'additional_instructions') }}</div>
                                                </div>
                                            @endif
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
                                            @if($customOrder->reference_image_path)
                                                <div>
                                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Reference Image</div>
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($customOrder->reference_image_path) }}" alt="Reference Image" class="rounded-lg border border-gray-200 shadow-sm max-h-80 object-contain w-full bg-gray-50">
                                                </div>
                                            @endif

                                            <form method="POST" action="{{ route('employee.custom-orders.update', $customOrder->id) }}" class="space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label for="price_estimate_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Final Price</label>
                                                    <input type="number" min="0" step="0.01" id="price_estimate_{{ $customOrder->id }}" name="price_estimate" value="{{ old('price_estimate', $customOrder->price_estimate) }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                    @error('price_estimate')
                                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label for="admin_notes_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Internal Notes</label>
                                                    <textarea id="admin_notes_{{ $customOrder->id }}" name="admin_notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]">{{ old('admin_notes', $customOrder->admin_notes) }}</textarea>
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
                                                        <input type="number" min="0" step="0.01" id="confirm_price_{{ $customOrder->id }}" name="price_estimate" value="{{ old('price_estimate', $customOrder->price_estimate) }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                    </div>
                                                    @error('price_estimate')
                                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                    <div>
                                                        <label for="estimated_completion_date_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Estimated Completion Date</label>
                                                        <input type="date" id="estimated_completion_date_{{ $customOrder->id }}" name="estimated_completion_date" value="{{ old('estimated_completion_date', optional($customOrder->estimated_completion_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
                                                    </div>
                                                </div>
                                                @error('estimated_completion_date')
                                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                                @enderror
                                                <div>
                                                    <label for="confirm_admin_notes_{{ $customOrder->id }}" class="block text-sm font-medium text-gray-700">Internal Notes (Optional)</label>
                                                    <textarea id="confirm_admin_notes_{{ $customOrder->id }}" name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]">{{ old('admin_notes', $customOrder->admin_notes) }}</textarea>
                                                </div>
                                                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 w-full md:w-auto" style="background:#2f855a;">
                                                    Confirm &amp; Start Production
                                                </button>
                                                <p class="text-xs text-gray-500">Confirmation sets status to In Progress and updates dashboards.</p>
                                            </form>
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
                            <div class="py-4 flex items-center gap-4">
                                <img src="{{ $oi->item?->photo_url }}" class="w-16 h-16 rounded object-cover bg-gray-100" alt="{{ $oi->item?->name }}"/>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $oi->item?->name }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ $oi->quantity }} • ₱{{ number_format($oi->price, 2) }}</div>
                                    <div class="mt-1 text-xs">
                                        @if(($oi->is_backorder ?? false))
                                            <span class="inline-flex px-2 py-0.5 rounded text-[12px] bg-blue-100 text-blue-800">Backorder</span>
                                            <div class="text-xs text-blue-700">Status: {{ $oi->backorder_status ?? 'pending_stock' }}</div>
                                            @if($oi->backorder_status === \App\Models\OrderItem::BO_PENDING)
                                                <div class="mt-2 flex items-center gap-2">
                                                    <button onclick="updateItem({{ $order->id }}, {{ $oi->id }}, 'in_progress')" class="px-3 py-1 rounded bg-yellow-100 text-yellow-800 text-xs">Mark In Progress</button>
                                                    <button onclick="updateItem({{ $order->id }}, {{ $oi->id }}, 'fulfilled')" class="px-3 py-1 rounded bg-green-100 text-green-800 text-xs">Mark Fulfilled</button>
                                                </div>
                                            @elseif($oi->backorder_status === \App\Models\OrderItem::BO_IN_PROGRESS)
                                                <div class="mt-2 flex items-center gap-2">
                                                    <button onclick="updateItem({{ $order->id }}, {{ $oi->id }}, 'fulfilled')" class="px-3 py-1 rounded bg-green-100 text-green-800 text-xs">Mark Fulfilled</button>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-xs text-amber-700 mt-1">In stock: {{ $oi->item->stock ?? 0 }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-sm font-medium">₱{{ number_format($oi->subtotal, 2) }}</div>
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


