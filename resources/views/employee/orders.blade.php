@extends('layouts.employee')

@section('page_title', 'Order Management')

@section('content')

    <div class="space-y-4" x-data="ordersPage()" x-init="init()">
        <h1 class="text-2xl font-semibold text-gray-900">Order Management</h1>

        @if(session('success'))
            <div class="mt-4 p-3 rounded border border-green-200 bg-green-50 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif

        <!-- Filters -->
        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2 overflow-x-auto">
                @php $type = $activeType; @endphp
                @php $tabs = [
                    '' => 'All Orders',
                    'preorder' => 'Pre-Orders',
                    'backorder' => 'Back-Orders',
                    'custom' => 'Custom Orders',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]; @endphp
                @foreach($tabs as $t => $label)
                    <a href="{{ url('/employee/orders'.($t ? ('?type='.$t) : '')) }}" class="px-4 py-2 rounded-lg border text-sm {{ ($type === $t) ? 'bg-[#c49b6e] text-white border-[#c49b6e]' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">{{ $label }}</a>
                @endforeach
            </div>
            <form method="GET" action="{{ url('/employee/orders') }}" class="flex items-center gap-2">
                <input type="hidden" name="type" value="{{ $activeType }}"/>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by Order ID, name or status" class="w-64 rounded-lg border border-gray-300 focus:border-[#c59d5f] focus:ring-1 focus:ring-[#c59d5f]"/>
                <button class="px-4 py-2 rounded-lg text-white shadow-sm hover:shadow" style="background:#c49b6e;">Search</button>
            </form>
        </div>

        <!-- Table -->
        <div class="mt-6 bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="hidden md:grid grid-cols-12 text-xs font-medium text-gray-600 px-4 py-3 border-b bg-gray-50">
                <div class="col-span-2">Order ID</div>
                <div class="col-span-3">Customer</div>
                <div class="col-span-2">Order Type</div>
                <div class="col-span-1">Status</div>
                <div class="col-span-2">Date</div>
                <div class="col-span-1">Total</div>
                <div class="col-span-1 text-right">Actions</div>
            </div>
            <div>
                @forelse($orders as $o)
                    <div class="grid grid-cols-12 items-center px-4 py-4 border-b text-sm hover:bg-gray-50/50">
                        <div class="col-span-12 md:col-span-2">
                            #{{ $o->id }}
                        </div>
                        <div class="col-span-12 md:col-span-3 mt-2 md:mt-0">
                            <div class="font-medium text-gray-900">{{ $o->user->name ?? 'Guest' }}</div>
                            <div class="text-xs text-gray-500">{{ $o->user->email ?? '' }}</div>
                        </div>
                        <div class="col-span-6 md:col-span-2 mt-2 md:mt-0">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs border border-gray-300 bg-white">{{ ucfirst($o->order_type) }}</span>
                        </div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">
                            @php
                                $statusColor = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'backorder' => 'bg-indigo-100 text-indigo-800',
                                    'preorder' => 'bg-amber-100 text-amber-800',
                                ][$o->status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs capitalize {{ $statusColor }}">{{ $o->status }}</span>
                        </div>
                        <div class="col-span-6 md:col-span-2 mt-2 md:mt-0">{{ $o->created_at->format('M d, Y') }}</div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0">₱{{ number_format($o->total_amount ?? $o->total ?? 0, 2) }}</div>
                        <div class="col-span-6 md:col-span-1 mt-2 md:mt-0 text-right space-x-2">
                            <a href="{{ route('employee.orders.show', $o->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 focus:outline-none">View</a>
                            <button @click="view({{ $o->id }})" class="inline-flex items-center px-3 py-1.5 rounded-lg text-white shadow-sm hover:shadow" style="background:#c49b6e;">Update</button>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center text-gray-600">No orders found.</div>
                @endforelse
            </div>
            <div class="px-4 py-3">{{ $orders->links() }}</div>
        </div>

        <!-- View/Update Modal -->
        <div x-show="open" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg overflow-hidden" @click.outside="open=false">
                <div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
                    <h2 class="font-semibold">Order #<span x-text="order?.id"></span></h2>
                    <button @click="open=false" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-5 space-y-5">
                    <template x-if="order">
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm text-gray-600">Customer</div>
                                <div class="font-medium" x-text="order.user?.name ?? 'Guest'"></div>
                                <div class="text-sm text-gray-500" x-text="order.user?.email ?? ''"></div>
                                <div class="text-sm text-gray-500" x-text="order.user?.address?.address_line ?? ''"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Items</div>
                                <div class="mt-2 space-y-3">
                                    <template x-for="it in order.items" :key="it.id">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3">
                                                <img :src="(it.item?.photos?.[0]?.url) ?? ''" class="w-12 h-12 rounded object-cover bg-gray-100"/>
                                                <div>
                                                    <div class="text-sm font-medium" x-text="it.item?.name"></div>
                                                    <div class="text-xs text-gray-500">Qty: <span x-text="it.quantity"></span> • ₱<span x-text="Number(it.price).toFixed(2)"></span></div>
                                                </div>
                                            </div>
                                            <div class="text-sm">₱<span x-text="Number(it.subtotal).toFixed(2)"></span></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-600">Order Type</div>
                                    <div class="font-medium capitalize" x-text="order.order_type"></div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600">Status</div>
                                    <select x-model="status" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="pending">Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="backorder">Backorder</option>
                                        <option value="preorder">Preorder</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-3">
                                <button @click="confirmDelete()" class="px-4 py-2 rounded-lg border border-red-300 text-red-700 hover:bg-red-50">Delete</button>
                                <button @click="save()" class="px-4 py-2 rounded-lg text-white shadow-sm hover:shadow" style="background:#c49b6e;">Save</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
function ordersPage(){
    return {
        open: false,
        order: null,
        status: 'pending',
        async init(){},
        async view(id){
            const res = await fetch(`/employee/orders/${id}`, { headers: { 'Accept': 'application/json' } });
            if(!res.ok){ alert('Failed to load order'); return; }
            this.order = await res.json();
            this.status = this.order.status;
            this.open = true;
        },
        async save(){
            if(!this.order) return;
            const res = await fetch(`/employee/orders/${this.order.id}`, { method:'PUT', headers:{ 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With':'XMLHttpRequest' }, body: JSON.stringify({ status: this.status }) });
            if(res.ok){
                location.reload();
            } else {
                let msg = 'Failed to update order';
                try { const d = await res.json(); if(d?.message) msg = d.message; } catch(e){}
                alert(msg);
            }
        },
        async confirmDelete(){
            if(!this.order) return;
            if(!confirm('Are you sure you want to delete this order?')) return;
            const res = await fetch(`/employee/orders/${this.order.id}`, { method:'DELETE', headers:{ 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With':'XMLHttpRequest' } });
            if(res.ok){
                location.reload();
            } else {
                let msg = 'Failed to delete order';
                try { const d = await res.json(); if(d?.message) msg = d.message; } catch(e){}
                alert(msg);
            }
        }
    }
}
    </script>
    @endpush

@endsection


