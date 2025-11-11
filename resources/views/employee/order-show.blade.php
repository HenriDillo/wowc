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
                    $canProcess = $paymentStatus === 'paid';
                @endphp
                <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-medium
                    @if($paymentStatus === 'paid') bg-green-100 text-green-800
                    @elseif($paymentStatus === 'pending_verification') bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800
                    @endif">
                    @if($paymentStatus === 'paid') ✓ Payment Confirmed
                    @elseif($paymentStatus === 'pending_verification') ⏳ Payment Pending Verification
                    @else ✗ Payment Unpaid
                    @endif
                </span>
            </div>
        </div>

        <!-- Payment Alert (if not paid) -->
        @if(!$canProcess)
            <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="text-red-700 font-semibold">⚠ Payment Required</div>
                    <div class="text-sm text-red-700 flex-1">
                        @if($paymentStatus === 'pending_verification')
                            This order is awaiting admin verification of bank transfer proof. Until verified, processing actions are limited.
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

        <!-- Status Update Form (only if paid) -->
        @if($canProcess)
            <form method="POST" action="{{ route('employee.orders.update', $order->id) }}" class="mb-6 flex items-center gap-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                @csrf
                @method('PUT')
                <label class="text-sm font-medium text-blue-900">Update Status:</label>
                <select name="status" class="rounded-md border border-gray-300 text-sm px-3 py-2">
                    @php 
                        $statuses = match($order->order_type) {
                            'standard' => ['pending', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled'],
                            'backorder' => ['pending', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled'],
                            'custom' => ['pending', 'in_design', 'in_production', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled'],
                            default => ['pending', 'processing', 'completed', 'cancelled']
                        };
                    @endphp
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected($order->status===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-md text-white font-medium" style="background:#c59d5f;">Update</button>
            </form>
        @else
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-sm text-gray-600">Status update available after payment confirmation.</p>
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
                                            @if($canProcess)
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

                <!-- Tracking & Shipping Section (for Standard & Back Orders when ready to ship) -->
                @if($order->order_type !== 'custom' && in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed']))
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
                                    <select name="carrier" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f]">
                                        <option value="">Select Carrier</option>
                                        <option value="lalamove" @selected(($order->carrier ?? '') === 'lalamove')>Lalamove</option>
                                        <option value="jnt" @selected(($order->carrier ?? '') === 'jnt')>J&T Express</option>
                                        <option value="ninjavan" @selected(($order->carrier ?? '') === 'ninjavan')>Ninja Van</option>
                                        <option value="2go" @selected(($order->carrier ?? '') === '2go')>2GO</option>
                                        <option value="pickup" @selected(($order->carrier ?? '') === 'pickup')>Customer Pickup</option>
                                    </select>
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
                <!-- Payment Card -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Payment Details</h2>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Method:</span>
                            <span class="font-medium">
                                @if($order->payment_method === 'gcash')
                                    GCash
                                @elseif($order->payment_method === 'bank')
                                    Bank Transfer
                                @else
                                    {{ ucfirst($order->payment_method ?? 'N/A') }}
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium inline-flex px-2 py-0.5 rounded-full text-xs
                                @if($paymentStatus === 'paid') bg-green-100 text-green-800
                                @elseif($paymentStatus === 'pending_verification') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                @if($paymentStatus === 'paid') ✓ Paid
                                @elseif($paymentStatus === 'pending_verification') ⏳ Pending Verification
                                @else ✗ Unpaid
                                @endif
                            </span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold">
                            <span>Total:</span>
                            <span>₱{{ number_format($order->total_amount, 2) }}</span>
                        </div>
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
                        @if($order->carrier)
                            <div>
                                <div class="text-gray-600 mb-1">Carrier</div>
                                <div class="font-medium capitalize">{{ str_replace('_', ' ', $order->carrier) }}</div>
                            </div>
                        @endif
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

@endsection


