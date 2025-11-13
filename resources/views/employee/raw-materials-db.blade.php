@extends('layouts.employee')

@section('page_title', 'Raw Materials Management')

@section('content')
<div x-data="{ openAdd: false, editId: null, hideId: null, addStockId: null, reduceStockId: null, bulkAddOpen: false, bulkReduceOpen: false, search: '', searchLower: '', bulkItems: [] }" x-effect="searchLower = (search || '').toLowerCase()" class="space-y-6">

    {{-- Success Feedback --}}
    @if (session('status'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-2 rounded">
            {{ session('status') }}
        </div>
    @endif

    {{-- Card: Visible Materials --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-b">
            <div class="flex items-center gap-3">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800">Raw Materials</h2>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" />
                        </svg>
                    </span>
                    <input type="text" x-model="search" placeholder="Search by name or unit..." class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                </div>
                <div class="flex gap-2">
                    <button @click="openAdd = true" class="inline-flex items-center gap-2 bg-[#c49b6e] text-white text-sm px-3 py-2 rounded-lg hover:bg-[#b08a5c]">
                        <span class="text-lg leading-none">+</span>
                        <span>Add Material</span>
                    </button>
                    <button @click="bulkAddOpen = true" class="inline-flex items-center gap-1 bg-green-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-green-700" title="Bulk add stock to multiple materials">
                        <span class="text-sm">📦</span>
                        <span>Bulk Add</span>
                    </button>
                    <button @click="bulkReduceOpen = true" class="inline-flex items-center gap-1 bg-orange-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-orange-700" title="Bulk reduce stock from multiple materials">
                        <span class="text-sm">📤</span>
                        <span>Bulk Reduce</span>
                    </button>
                </div>
            </div>
        </div>

    {{-- Add Material Modal --}}
    <div x-show="openAdd" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div @click.outside="openAdd = false" class="bg-white p-6 rounded-lg w-96 shadow-xl">
            <h2 class="text-xl font-semibold mb-4">Add Material</h2>
            <form action="{{ route('employee.materials.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <input type="text" name="name" placeholder="Material Name" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @if($errors->getBag('createMaterial')->has('name'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createMaterial')->first('name') }}</p>
                    @endif
                </div>
                <div>
                    <input type="text" name="unit" placeholder="Unit (pcs, kg, etc.)" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @if($errors->getBag('createMaterial')->has('unit'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createMaterial')->first('unit') }}</p>
                    @endif
                </div>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="openAdd = false" class="px-4 py-2 border rounded-lg shadow-sm hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-2 rounded-lg shadow">Add</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Add Stock Modal --}}
    <div x-show="bulkAddOpen" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div id="bulkAddModal" @click.outside="bulkAddOpen = false" class="bg-white p-6 rounded-lg max-w-2xl w-full max-h-96 shadow-xl overflow-y-auto">
            <h2 class="text-xl font-semibold mb-4">Bulk Add Stock</h2>

            {{-- We'll keep the form for progressive enhancement but submit via fetch to ensure a proper POST from the modal --}}
            <form id="bulkAddForm" action="{{ route('employee.materials.bulk-add-stock') }}" method="POST" class="space-y-4" onsubmit="return false;">
                @csrf
                {{-- explicit token for JS to read (redundant with @csrf but convenient) --}}
                <input type="hidden" id="bulk_add_csrf" value="{{ csrf_token() }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Materials & Quantities</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto border rounded p-3 bg-gray-50">
                        @foreach($materials as $m)
                            <div class="flex items-center gap-3 p-2 bg-white rounded border bulk-row">
                                <input type="checkbox" data-index="{{ $loop->index }}" class="bulk-checkbox rounded cursor-pointer" value="{{ $m->id }}" />
                                <label class="flex-1 cursor-pointer">{{ $m->name }} ({{ $m->unit }})</label>
                                <input type="number" data-index="{{ $loop->index }}" class="bulk-qty w-20 border rounded px-2 py-1 text-sm" placeholder="Qty" min="1" />
                                {{-- Hidden inputs kept for accessibility/fallback --}}
                                <input type="hidden" name="items[{{ $loop->index }}][material_id]" value="{{ $m->id }}">
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
                                const material_id = checkbox.value;
                                const quantity = parseInt(qtyInput.value) || 0;
                                if(quantity > 0){
                                    items.push({ material_id: material_id, quantity: quantity });
                                }
                            }
                        });

                        if(items.length === 0){
                            alert('Please select at least one material and enter a quantity.');
                            return;
                        }

                        const payload = { items: items, remarks: document.getElementById('bulkAddRemarks').value };
                        const url = '{{ route('employee.materials.bulk-add-stock') }}';

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
                                // reload to show updated history and status message
                                window.location.reload();
                                return;
                            }

                            const data = await res.json().catch(()=>null);
                            if(data && data.errors){
                                // find first error and show
                                const first = Object.values(data.errors)[0];
                                alert(first[0] || 'Validation error');
                                return;
                            }
                            alert('Failed to submit. Please try again.');
                        } catch(err){
                            console.error(err);
                            alert('Network error while submitting bulk add.');
                        }
                    })()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">Add Stock</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Reduce Stock Modal --}}
    <div x-show="bulkReduceOpen" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div @click.outside="bulkReduceOpen = false" class="bg-white p-6 rounded-lg max-w-2xl w-full max-h-96 shadow-xl overflow-y-auto">
            <h2 class="text-xl font-semibold mb-4">Bulk Reduce Stock</h2>
            <form action="{{ route('employee.materials.bulk-reduce-stock') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Materials & Quantities</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto border rounded p-3 bg-gray-50">
                        @foreach($materials as $m)
                            <div class="flex items-center gap-3 p-2 bg-white rounded border">
                                <input type="checkbox" name="items[{{ $loop->index }}][material_id]" value="{{ $m->id }}" class="rounded cursor-pointer">
                                <label class="flex-1 cursor-pointer">{{ $m->name }} (Stock: {{ $m->stock ?? 0 }} {{ $m->unit }})</label>
                                <input type="number" name="items[{{ $loop->index }}][quantity]" placeholder="Qty" min="1" class="w-20 border rounded px-2 py-1 text-sm" />
                            </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (optional)</label>
                    <textarea name="remarks" class="w-full border rounded px-3 py-2 text-sm" rows="2" placeholder="e.g., Used for production, Damage report..."></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="bulkReduceOpen = false" class="px-4 py-2 border rounded-lg shadow-sm hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg shadow">Reduce Stock</button>
                </div>
            </form>
        </div>
    </div>

        {{-- Main Materials Table --}}
        <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Product Name</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Unit</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Current Stock</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Visibility</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($materials as $m)
                <tr class="hover:bg-gray-50" x-show="searchLower === '' || '{{ strtolower($m->name) }}'.includes(searchLower) || '{{ strtolower($m->unit) }}'.includes(searchLower)">
                    <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $m->name }}</td>
                    <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $m->unit }}</td>
                    <td class="px-4 py-3 border-b text-sm text-gray-700 font-medium">{{ $m->stock ?? 0 }}</td>
                    <td class="px-4 py-3 border-b text-sm">
                        @php $status = ($m->stock ?? 0) <= 0 ? 'Out of Stock' : (($m->stock ?? 0) < 5 ? 'Low' : 'OK'); @endphp
                        <span class="px-2 py-1 text-xs rounded {{ $status==='OK' ? 'bg-green-100 text-green-700' : ($status==='Low' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $status }}</span>
                    </td>
                    <td class="px-4 py-3 border-b text-sm">
                        @if(!$m->is_hidden)
                            <span class="px-2 py-1 bg-green-500 text-white rounded">Shown</span>
                        @else
                            <span class="px-2 py-1 bg-gray-500 text-white rounded">Hidden</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 border-b text-sm">
                        <div class="flex items-center gap-2">
                            <button @click="editId = {{ $m->id }}" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Edit</button>
                            <button @click="addStockId = {{ $m->id }}" class="px-3 py-1.5 text-xs bg-green-600 text-white rounded hover:bg-green-700">Add</button>
                            <button @click="reduceStockId = {{ $m->id }}" class="px-3 py-1.5 text-xs bg-orange-600 text-white rounded hover:bg-orange-700">Reduce</button>
                            <button @click="hideId = {{ $m->id }}" class="px-3 py-1.5 text-xs bg-red-600 text-white rounded hover:bg-red-700">Hide</button>
                        </div>
                    </td>
                </tr>

                {{-- Edit Modal --}}
                <div x-show="editId === {{ $m->id }}" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
                    <div @click.outside="editId = null" class="bg-white p-6 rounded-lg w-96 shadow-xl">
                        <h2 class="text-xl font-semibold mb-4">Edit Material</h2>
                        <form action="{{ route('employee.materials.update', $m) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <input type="text" name="name" value="{{ $m->name }}" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @php($bag = 'edit_' . $m->id)
                                @if($errors->getBag($bag)->has('name'))
                                    <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('name') }}</p>
                                @endif
                            </div>
                            <div>
                                <input type="text" name="unit" value="{{ $m->unit }}" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @php($bag = 'edit_' . $m->id)
                                @if($errors->getBag($bag)->has('unit'))
                                    <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('unit') }}</p>
                                @endif
                            </div>
                            <div class="flex justify-end space-x-2 mt-4">
                            <button type="button" @click="editId = null" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Add Stock Modal --}}
                <div x-show="addStockId === {{ $m->id }}" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
                    <div @click.outside="addStockId = null" class="bg-white p-6 rounded-lg w-96 shadow-xl">
                        <h2 class="text-xl font-semibold mb-4">Add Stock - {{ $m->name }}</h2>
                        <form action="{{ route('employee.materials.add-stock', $m) }}" method="POST" class="space-y-3">
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

                {{-- Reduce Stock Modal --}}
                <div x-show="reduceStockId === {{ $m->id }}" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
                    <div @click.outside="reduceStockId = null" class="bg-white p-6 rounded-lg w-96 shadow-xl">
                        <h2 class="text-xl font-semibold mb-4">Reduce Stock - {{ $m->name }}</h2>
                        <form action="{{ route('employee.materials.reduce-stock', $m) }}" method="POST" class="space-y-3">
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
                                <button type="button" @click="reduceStockId = null" class="px-3 py-2 text-gray-700 border rounded">Cancel</button>
                                <button type="submit" class="px-3 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Reduce Stock</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Hide Confirm Modal --}}
                <div x-show="hideId === {{ $m->id }}" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
                    <div @click.outside="hideId = null" class="bg-white p-6 rounded-lg w-96 shadow-xl">
                        <h2 class="text-xl font-semibold mb-4">Confirm Hide</h2>
                        <p class="text-sm text-gray-700">Are you sure you want to hide "{{ $m->name }}"?</p>
                        @php($bag = 'hide_' . $m->id)
                        @if($errors->getBag($bag)->has('hide'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('hide') }}</p>
                        @endif
                        <form action="{{ route('employee.materials.hide', $m) }}" method="POST" class="mt-4 flex justify-end space-x-2">
                            @csrf
                            @method('PATCH')
                            <button type="button" @click="hideId = null" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">Confirm</button>
                        </form>
                    </div>
                </div>

                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $materials->links() }}
    </div>

    {{-- Transaction History Section --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mt-6">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800">📋 Stock Transaction History</h3>
            <span class="text-sm text-gray-500">Last 100 transactions</span>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="bg-gray-50 border-b p-4">
            <form method="GET" action="{{ route('employee.raw-materials') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                    {{-- Employee Name Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Employee Name</label>
                        <input type="text" name="employee_name" placeholder="Search employee..." 
                            value="{{ $filters['employee_name'] ?? '' }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    </div>

                    {{-- Transaction Type Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Type</label>
                        <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]">
                            <option value="">All Types</option>
                            <option value="in" @selected(($filters['type'] ?? '') === 'in')>Stock In</option>
                            <option value="out" @selected(($filters['type'] ?? '') === 'out')>Stock Out</option>
                        </select>
                    </div>

                    {{-- Date From Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">From Date</label>
                        <input type="date" name="date_from" 
                            value="{{ $filters['date_from'] ?? '' }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    </div>

                    {{-- Date To Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">To Date</label>
                        <input type="date" name="date_to" 
                            value="{{ $filters['date_to'] ?? '' }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    </div>

                    {{-- Remarks/Notes Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Remarks</label>
                        <input type="text" name="remarks" placeholder="Search notes..." 
                            value="{{ $filters['remarks'] ?? '' }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    </div>
                </div>

                {{-- Filter Actions --}}
                <div class="flex items-center gap-2 justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white font-medium bg-[#c49b6e] hover:bg-[#b08a5c] transition-colors">
                        🔍 Search
                    </button>
                    @if(request()->filled('employee_name') || request()->filled('type') || request()->filled('remarks') || request()->filled('date_from') || request()->filled('date_to'))
                        <a href="{{ route('employee.raw-materials') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium transition-colors">
                            ✕ Clear Filters
                        </a>
                    @endif
                </div>

                {{-- Active Filters Display --}}
                @if(request()->filled('employee_name') || request()->filled('type') || request()->filled('remarks') || request()->filled('date_from') || request()->filled('date_to'))
                    <div class="pt-2 border-t border-gray-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <span class="text-xs font-semibold text-gray-600">Active Filters:</span>
                            @if(request()->filled('employee_name'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                                    👤 Employee: {{ request('employee_name') }}
                                </span>
                            @endif
                            @if(request()->filled('type'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-medium">
                                    {{ request('type') === 'in' ? '📥 Stock In' : '📤 Stock Out' }}
                                </span>
                            @endif
                            @if(request()->filled('date_from'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                    📅 From: {{ \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') }}
                                </span>
                            @endif
                            @if(request()->filled('date_to'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                    📅 To: {{ \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') }}
                                </span>
                            @endif
                            @if(request()->filled('remarks'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-medium">
                                    📝 Notes: {{ request('remarks') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        </div>

        @if($recentTransactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Date & Time</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Employee</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Material</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Type</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Quantity</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Unit</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach($recentTransactions as $trans)
                            <tr class="hover:bg-gray-50 border-b">
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $trans->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $trans->user?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $trans->material?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($trans->type === 'in')
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700 font-semibold">📥 Stock In</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700 font-semibold">📤 Stock Out</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $trans->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $trans->material?->unit ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate" title="{{ $trans->remarks }}">{{ $trans->remarks ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                <p>@if(request()->filled('employee_name') || request()->filled('type') || request()->filled('remarks') || request()->filled('date_from') || request()->filled('date_to'))No transactions match your filter criteria.@else No transactions yet. Start managing stock to see transaction history.@endif</p>
            </div>
        @endif
    </div>

    {{-- Card: Hidden Materials --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mt-6">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800">Hidden Materials</h3>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Product Name</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Unit</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Visibility</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($hiddenMaterials as $m)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $m->name }}</td>
                    <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $m->unit }}</td>
                    <td class="px-4 py-3 border-b text-sm">
                        <span class="px-2 py-1 bg-gray-500 text-white rounded">Hidden</span>
                    </td>
                    <td class="px-4 py-3 border-b text-sm">
                        <form action="{{ route('employee.materials.unhide', $m) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Unhide</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

</div>
@endsection