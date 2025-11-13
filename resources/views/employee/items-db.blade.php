@extends('layouts.employee')

@section('page_title', 'Production Management')

@section('content')
<div x-data="{ openAdd: false, editId: null, addStockId: null, bulkAddOpen: false, search: '{{ $search ?? '' }}', searchLower: '', bulkItems: [] }" x-effect="searchLower = (search || '').toLowerCase()" class="space-y-6">

    @if (session('status'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-2 rounded">{{ session('status') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-b">
            <div class="flex items-center gap-3">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800">Items (Production)</h2>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" />
                        </svg>
                    </span>
                    <form method="GET" action="{{ route('employee.items') }}">
                        <input name="search" x-model="search" placeholder="Search name or category..." class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" @change="$el.form.submit()" />
                    </form>
                </div>
                <div class="flex gap-2">
                    <button @click="openAdd = true" class="inline-flex items-center gap-2 bg-[#c49b6e] text-white text-sm px-3 py-2 rounded-lg hover:bg-[#b08a5c]">
                        <span class="text-lg leading-none">+</span>
                        <span>Add Item</span>
                    </button>
                    <button @click="bulkAddOpen = true" class="inline-flex items-center gap-1 bg-green-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-green-700" title="Bulk add stock to multiple items">
                        <span class="text-sm">📦</span>
                        <span>Bulk Add</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Item Name</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Images</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Category</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Price</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Stock</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($visibleItems as $i)
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $i->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $photos = $i->photos ?? collect();
                                    $photoCount = $photos->count();
                                @endphp
                                @if($photoCount > 0)
                                    <div class="flex items-center gap-2">
                                        <div class="flex -space-x-2">
                                            @foreach($photos->take(3) as $photo)
                                                <img src="{{ $photo->url }}" alt="{{ $i->name }}" class="w-10 h-10 rounded border-2 border-white object-cover shadow-sm" title="{{ $i->name }}">
                                            @endforeach
                                        </div>
                                        @if($photoCount > 3)
                                            <span class="text-xs text-gray-500 font-medium">+{{ $photoCount - 3 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">No images</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $i->category }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱{{ number_format($i->price, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium">{{ $i->stock ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php $status = ($i->stock ?? 0) <= 0 ? 'Out of Stock' : (($i->stock ?? 0) < 5 ? 'Low' : 'OK'); @endphp
                                <span class="px-2 py-1 text-xs rounded {{ $status==='OK' ? 'bg-green-100 text-green-700' : ($status==='Low' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $status }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <button @click="addStockId = {{ $i->id }}" class="px-3 py-1.5 text-xs bg-green-600 text-white rounded hover:bg-green-700" title="Add stock to this item">Add Stock</button>
                                    <button @click="editId = {{ $i->id }}" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Edit</button>
                                    <form action="{{ route('employee.items.toggle', $i) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-1.5 text-xs rounded text-white {{ $i->visible ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }}">{{ $i->visible ? 'Hide' : 'Unhide' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <div x-show="editId === {{ $i->id }}" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
                            <div @click.outside="editId = null" class="bg-white p-6 rounded-lg w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
                                <h2 class="text-xl font-semibold mb-4">Edit Item</h2>
                                <form action="{{ route('employee.items.update', $i) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <input type="text" name="name" value="{{ $i->name }}" placeholder="Item name" class="w-full border rounded px-3 py-2" required>
                                            @php($bag = 'edit_' . $i->id)
                                            @if($errors->getBag($bag)->has('name'))
                                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('name') }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <select name="category" class="w-full border rounded px-3 py-2" required>
                                                <option value="Caddy" {{ $i->category === 'Caddy' ? 'selected' : '' }}>Caddy</option>
                                                <option value="Carpet" {{ $i->category === 'Carpet' ? 'selected' : '' }}>Carpet</option>
                                                <option value="Placemat" {{ $i->category === 'Placemat' ? 'selected' : '' }}>Placemat</option>
                                                <option value="Others" {{ $i->category === 'Others' ? 'selected' : '' }}>Others</option>
                                            </select>
                                            @if($errors->getBag($bag)->has('category'))
                                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('category') }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <input type="number" step="0.01" name="price" value="{{ $i->price }}" placeholder="Price" class="w-full border rounded px-3 py-2" min="0" required>
                                            @if($errors->getBag($bag)->has('price'))
                                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('price') }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <input type="text" name="description" value="{{ $i->description }}" placeholder="Description (optional)" class="w-full border rounded px-3 py-2">
                                            @if($errors->getBag($bag)->has('description'))
                                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('description') }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Existing Images --}}
                                    @php($itemPhotos = $i->photos ?? collect())
                                    @if($itemPhotos->count() > 0)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                                @foreach($itemPhotos as $photo)
                                                    <div class="relative group">
                                                        <img src="{{ $photo->url }}" alt="Product image" class="w-full h-24 object-cover rounded border border-gray-200">
                                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded flex items-center justify-center">
                                                            <label class="cursor-pointer text-white text-xs px-2 py-1 bg-red-600 rounded hover:bg-red-700">
                                                                <input type="checkbox" name="remove_photo_ids[]" value="{{ $photo->id }}" class="hidden" onchange="this.parentElement.parentElement.parentElement.style.opacity = this.checked ? '0.5' : '1'">
                                                                Remove
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">Check "Remove" on images you want to delete</p>
                                        </div>
                                    @endif

                                    {{-- Add New Images --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Add New Images (Multiple allowed)</label>
                                        <input type="file" name="photos[]" multiple accept="image/*" class="w-full border rounded px-3 py-2 text-sm">
                                        <p class="text-xs text-gray-500 mt-1">You can select multiple images. Accepted formats: JPG, PNG, GIF. Max size: 2MB per image.</p>
                                        @if($errors->getBag($bag)->has('photos.*'))
                                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('photos.*') }}</p>
                                        @endif
                                    </div>

                                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                                        <button type="button" @click="editId = null" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>
                                        <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Add Stock Modal --}}
                        <div x-show="addStockId === {{ $i->id }}" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
                            <div @click.outside="addStockId = null" class="bg-white p-6 rounded-lg w-96 shadow-xl">
                                <h2 class="text-xl font-semibold mb-4">Add Stock - {{ $i->name }}</h2>
                                <form action="{{ route('employee.items.add-stock', $i) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-sm text-gray-700 mb-1">Quantity</label>
                                        <input name="quantity" type="number" min="1" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 mb-1">Remarks (optional)</label>
                                        <textarea name="remarks" class="w-full border rounded px-3 py-2" rows="2"></textarea>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="addStockId = null" class="px-3 py-2 text-gray-700 border rounded">Cancel</button>
                                        <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add Stock</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">No items found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $visibleItems->links() }}
    </div>

    {{-- Bulk Add Stock Modal --}}
    <div x-show="bulkAddOpen" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div id="bulkAddModal" @click.outside="bulkAddOpen = false" class="bg-white p-6 rounded-lg max-w-2xl w-full max-h-96 shadow-xl overflow-y-auto">
            <h2 class="text-xl font-semibold mb-4">Bulk Add Stock</h2>

            <form id="bulkAddForm" action="{{ route('employee.items.bulk-add-stock') }}" method="POST" class="space-y-4" onsubmit="return false;">
                @csrf
                <input type="hidden" id="bulk_add_csrf" value="{{ csrf_token() }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Items & Quantities</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto border rounded p-3 bg-gray-50">
                        @foreach($visibleItems as $item)
                            <div class="flex items-center gap-3 p-2 bg-white rounded border bulk-row">
                                <input type="checkbox" data-index="{{ $loop->index }}" class="bulk-checkbox rounded cursor-pointer" value="{{ $item->id }}" />
                                <label class="flex-1 cursor-pointer">{{ $item->name }} ({{ $item->category }})</label>
                                <input type="number" data-index="{{ $loop->index }}" class="bulk-qty w-20 border rounded px-2 py-1 text-sm" placeholder="Qty" min="1" />
                                <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $loop->index }}][quantity]" value="" class="hidden-qty-{{ $loop->index }}">
                            </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (optional)</label>
                    <textarea id="bulkAddRemarks" name="remarks" class="w-full border rounded px-3 py-2 text-sm" rows="2" placeholder="e.g., New shipment received, Quality check passed..."></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="bulkAddOpen = false" class="px-4 py-2 border rounded-lg shadow-sm hover:bg-gray-50">Cancel</button>
                    <button type="button" x-on:click="(async function(){
                        const token = document.getElementById('bulk_add_csrf').value;
                        const rows = document.querySelectorAll('#bulkAddModal .bulk-row');
                        const items = [];
                        rows.forEach((row, idx) => {
                            const checkbox = row.querySelector('.bulk-checkbox');
                            const qtyInput = row.querySelector('.bulk-qty');
                            if(!checkbox || !qtyInput) return;
                            if(checkbox.checked){
                                const item_id = checkbox.value;
                                const quantity = parseInt(qtyInput.value) || 0;
                                if(quantity > 0){
                                    items.push({ item_id: item_id, quantity: quantity });
                                }
                            }
                        });

                        if(items.length === 0){
                            alert('Please select at least one item and enter a quantity.');
                            return;
                        }

                        const payload = { items: items, remarks: document.getElementById('bulkAddRemarks').value };
                        const url = '{{ route('employee.items.bulk-add-stock') }}';

                        try {
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(payload)
                            });

                            if(res.redirected){
                                window.location = res.url;
                                return;
                            }

                            if(res.ok){
                                window.location.reload();
                                return;
                            }

                            const errData = await res.json().catch(() => ({}));
                            alert(errData.message || 'Failed to add stock');
                        } catch(e) {
                            alert('Request failed: ' + e.message);
                        }
                    })()" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg shadow">Add Stock</button>
                </div>
            </form>
        </div>
    </div>


    {{-- Transaction History Section with Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mt-6">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800">📋 Stock Transaction History</h3>
            <span class="text-sm text-gray-500">Last 100 transactions</span>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="px-4 py-3 border-b bg-gray-50">
            <form method="GET" action="{{ route('employee.items') }}" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
                    <input type="text" name="employee_name" value="{{ $filters['employee_name'] ?? '' }}" placeholder="Employee name..." class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    <select name="type" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]">
                        <option value="">All Types</option>
                        <option value="in" {{ ($filters['type'] ?? '') === 'in' ? 'selected' : '' }}>Stock In</option>
                        <option value="out" {{ ($filters['type'] ?? '') === 'out' ? 'selected' : '' }}>Stock Out</option>
                    </select>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    <input type="text" name="remarks" value="{{ $filters['remarks'] ?? '' }}" placeholder="Remarks/Notes..." class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-[#c49b6e] text-white text-sm px-4 py-2 rounded hover:bg-[#b08a5c]">Search</button>
                    @if(($filters['employee_name'] ?? false) || ($filters['type'] ?? false) || ($filters['date_from'] ?? false) || ($filters['date_to'] ?? false) || ($filters['remarks'] ?? false))
                        <a href="{{ route('employee.items') }}" class="bg-gray-400 text-white text-sm px-4 py-2 rounded hover:bg-gray-500">Clear Filters</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Active Filter Badges --}}
        @if(($filters['employee_name'] ?? false) || ($filters['type'] ?? false) || ($filters['date_from'] ?? false) || ($filters['date_to'] ?? false) || ($filters['remarks'] ?? false))
            <div class="px-4 py-2 border-b bg-blue-50 flex flex-wrap gap-2">
                @if($filters['employee_name'] ?? false)
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">Employee: {{ $filters['employee_name'] }}</span>
                @endif
                @if($filters['type'] ?? false)
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">Type: {{ $filters['type'] === 'in' ? 'Stock In' : 'Stock Out' }}</span>
                @endif
                @if($filters['date_from'] ?? false)
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">From: {{ $filters['date_from'] }}</span>
                @endif
                @if($filters['date_to'] ?? false)
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">To: {{ $filters['date_to'] }}</span>
                @endif
                @if($filters['remarks'] ?? false)
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">Remarks: {{ $filters['remarks'] }}</span>
                @endif
            </div>
        @endif

        @if($recentTransactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Date & Time</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Employee</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Item</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Type</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Quantity</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach($recentTransactions as $trans)
                            <tr class="hover:bg-gray-50 border-b">
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $trans->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $trans->user?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $trans->item?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($trans->type === 'in')
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700 font-semibold">📥 Stock In</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700 font-semibold">📤 Stock Out</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $trans->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate" title="{{ $trans->remarks }}">{{ $trans->remarks ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                <p>No transactions found.</p>
            </div>
        @endif
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mt-6">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800">Hidden Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Item Name</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Category</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Price</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($hiddenItems as $h)
                    <tr class="hover:bg-gray-50 border-b">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $h->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $h->category }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">₱{{ number_format($h->price, 2) }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-gray-400 text-white rounded">Hidden</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <form action="{{ route('employee.items.toggle', $h) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button class="text-blue-600 hover:text-blue-700 underline">Unhide</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No hidden items</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            {{ $hiddenItems->links() }}
        </div>
    </div>

    {{-- Add Modal --}}
    <div x-show="openAdd" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div @click.outside="openAdd = false" class="bg-white p-6 rounded-lg w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-semibold mb-4">Add New Item</h2>
            <form action="{{ route('employee.items.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Item name" class="w-full border rounded px-3 py-2" required>
                        @if($errors->getBag('createItem')->has('name'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createItem')->first('name') }}</p>
                        @endif
                    </div>
                    <div>
                        <select name="category" class="w-full border rounded px-3 py-2" required>
                            <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select category</option>
                            <option value="Caddy" {{ old('category') === 'Caddy' ? 'selected' : '' }}>Caddy</option>
                            <option value="Carpet" {{ old('category') === 'Carpet' ? 'selected' : '' }}>Carpet</option>
                            <option value="Placemat" {{ old('category') === 'Placemat' ? 'selected' : '' }}>Placemat</option>
                            <option value="Others" {{ old('category') === 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                        @if($errors->getBag('createItem')->has('category'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createItem')->first('category') }}</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <input type="number" step="0.01" name="price" value="{{ old('price') }}" placeholder="Price" class="w-full border rounded px-3 py-2" min="0" required>
                        @if($errors->getBag('createItem')->has('price'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createItem')->first('price') }}</p>
                        @endif
                    </div>
                    <div>
                        <input type="text" name="description" value="{{ old('description') }}" placeholder="Description (optional)" class="w-full border rounded px-3 py-2">
                        @if($errors->getBag('createItem')->has('description'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createItem')->first('description') }}</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Images (Multiple allowed)</label>
                    <input type="file" name="photos[]" multiple accept="image/*" class="w-full border rounded px-3 py-2 text-sm">
                    <p class="text-xs text-gray-500 mt-1">You can select multiple images. Accepted formats: JPG, PNG, GIF. Max size: 2MB per image.</p>
                    @if($errors->getBag('createItem')->has('photos.*'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createItem')->first('photos.*') }}</p>
                    @endif
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="openAdd = false" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Create Item</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
