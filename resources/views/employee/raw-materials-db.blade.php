@extends('layouts.employee')

@section('page_title', 'Raw Materials')

@section('content')
<div x-data="{ openAdd: false, editId: null, addQtyId: null, hideId: null, search: '', searchLower: '' }" x-effect="searchLower = (search || '').toLowerCase()" class="space-y-6">

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
                <button @click="openAdd = true" class="inline-flex items-center gap-2 bg-[#c49b6e] text-white text-sm px-3 py-2 rounded-lg hover:bg-[#b08a5c]">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Material</span>
                </button>
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

        {{-- Main Materials Table --}}
        <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Product Name</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Stock</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Unit</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($materials as $m)
                <tr class="hover:bg-gray-50" x-show="searchLower === '' || '{{ strtolower($m->name) }}'.includes(searchLower) || '{{ strtolower($m->unit) }}'.includes(searchLower)">
                    <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $m->name }}</td>
                    <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $m->quantity }}</td>
                    <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $m->unit }}</td>
                    <td class="px-4 py-3 border-b text-sm">
                        @if($m->status == 'Available')
                            <span class="px-2 py-1 bg-green-500 text-white rounded">{{ $m->status }}</span>
                        @elseif($m->status == 'Low Stock')
                            <span class="px-2 py-1 bg-yellow-500 text-white rounded">{{ $m->status }}</span>
                        @else
                            <span class="px-2 py-1 bg-red-500 text-white rounded">{{ $m->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 border-b text-sm">
                        <div class="flex items-center gap-3">
                            <button @click="editId = {{ $m->id }}" class="text-blue-600 hover:text-blue-700 underline">Edit</button>
                            <span class="text-gray-300">|</span>
                            <button @click="addQtyId = {{ $m->id }}" class="text-green-600 hover:text-green-700 underline">Add Quantity</button>
                            <span class="text-gray-300">|</span>
                            <button @click="hideId = {{ $m->id }}" class="text-red-600 hover:text-red-700 underline">Hide</button>
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
                                <button type="button" @click="editId = null" class="px-4 py-2 border rounded-lg shadow-sm hover:bg-gray-50">Cancel</button>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-2 rounded-lg shadow">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Add Quantity Modal --}}
                <div x-show="addQtyId === {{ $m->id }}" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
                    <div @click.outside="addQtyId = null" class="bg-white p-6 rounded-lg w-96 shadow-xl">
                        <h2 class="text-xl font-semibold mb-4">Add Quantity</h2>
                        <form action="{{ route('employee.materials.update', $m) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="mode" value="add_quantity">
                            <div>
                                <input type="number" name="add_quantity" placeholder="Enter amount" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" step="any" required>
                                @php($bag = 'addQty_' . $m->id)
                                @if($errors->getBag($bag)->has('add_quantity'))
                                    <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('add_quantity') }}</p>
                                @endif
                            </div>
                            <div class="flex justify-end space-x-2 mt-4">
                                <button type="button" @click="addQtyId = null" class="px-4 py-2 border rounded-lg shadow-sm hover:bg-gray-50">Cancel</button>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-2 rounded-lg shadow">Add</button>
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
                            <button type="button" @click="hideId = null" class="px-4 py-2 border rounded">Cancel</button>
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-3 border rounded">Confirm</button>
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
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Stock</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Unit</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($hiddenMaterials as $m)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $m->name }}</td>
                    <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $m->quantity }}</td>
                    <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $m->unit }}</td>
                    <td class="px-4 py-3 border-b text-sm">
                        @if($m->status == 'Available')
                            <span class="px-2 py-1 bg-green-500 text-white rounded">{{ $m->status }}</span>
                        @elseif($m->status == 'Low Stock')
                            <span class="px-2 py-1 bg-yellow-500 text-white rounded">{{ $m->status }}</span>
                        @else
                            <span class="px-2 py-1 bg-red-500 text-white rounded">{{ $m->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 border-b text-sm">
                        <form action="{{ route('employee.materials.unhide', $m) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button class="text-blue-600 hover:text-blue-700 underline">Unhide</button>
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