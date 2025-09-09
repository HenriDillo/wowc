@extends('layouts.employee')

@section('page_title', 'Raw Materials')

@section('content')
<div x-data="{ openAdd: false, editId: null }" class="space-y-6">

    {{-- Add Material Button --}}
    <div class="flex justify-end mb-4">
        <button @click="openAdd = true" class="bg-[#c49b6e] text-white px-4 py-2 rounded hover:bg-[#b08a5c]">
            + Add Material
        </button>
    </div>

    {{-- Add Material Modal --}}
    <div x-show="openAdd" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div @click.outside="openAdd = false" class="bg-white p-6 rounded-lg w-96 shadow-xl">
            <h2 class="text-xl font-semibold mb-4">Add Material</h2>
            <form action="{{ route('employee.materials.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="Material Name" class="w-full border rounded px-3 py-2" required>
                <input type="number" name="quantity" placeholder="Quantity" class="w-full border rounded px-3 py-2" min="0" required>
                <input type="text" name="unit" placeholder="Unit (pcs, kg, etc.)" class="w-full border rounded px-3 py-2" required>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="openAdd = false" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-[#c49b6e] text-white rounded hover:bg-[#b08a5c]">Add</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Materials Table --}}
    <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($materials as $m)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $m->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $m->quantity }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $m->unit }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($m->status == 'Available')
                            <span class="px-2 py-1 bg-green-500 text-white rounded">{{ $m->status }}</span>
                        @elseif($m->status == 'Low Stock')
                            <span class="px-2 py-1 bg-yellow-500 text-white rounded">{{ $m->status }}</span>
                        @else
                            <span class="px-2 py-1 bg-red-500 text-white rounded">{{ $m->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm space-x-2">

                        {{-- Edit Button --}}
                        <button @click="editId = {{ $m->id }}" class="text-blue-600 hover:underline">Edit</button>

                        {{-- Hide / Unhide Form --}}
                        <form action="{{ $m->is_hidden ? route('employee.materials.unhide', $m) : route('employee.materials.hide', $m) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button class="text-gray-700 hover:underline">{{ $m->is_hidden ? 'Unhide' : 'Hide' }}</button>
                        </form>

                    </td>
                </tr>

                {{-- Edit Modal --}}
                <div x-show="editId === {{ $m->id }}" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
                    <div @click.outside="editId = null" class="bg-white p-6 rounded-lg w-96 shadow-xl">
                        <h2 class="text-xl font-semibold mb-4">Edit Material</h2>
                        <form action="{{ route('employee.materials.update', $m) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $m->name }}" class="w-full border rounded px-3 py-2" required>
                            <input type="number" name="quantity" value="{{ $m->quantity }}" class="w-full border rounded px-3 py-2" min="0" required>
                            <input type="text" name="unit" value="{{ $m->unit }}" class="w-full border rounded px-3 py-2" required>
                            <div class="flex justify-end space-x-2 mt-4">
                                <button type="button" @click="editId = null" class="px-4 py-2 border rounded">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-[#c49b6e] text-white rounded hover:bg-[#b08a5c]">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $materials->links() }}
    </div>

    {{-- Hidden Materials Table --}}
    <h2 class="text-xl font-semibold mt-6 mb-2">Hidden Materials</h2>
    <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($hiddenMaterials as $m)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $m->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $m->quantity }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $m->unit }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($m->status == 'Available')
                            <span class="px-2 py-1 bg-green-500 text-white rounded">{{ $m->status }}</span>
                        @elseif($m->status == 'Low Stock')
                            <span class="px-2 py-1 bg-yellow-500 text-white rounded">{{ $m->status }}</span>
                        @else
                            <span class="px-2 py-1 bg-red-500 text-white rounded">{{ $m->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <form action="{{ route('employee.materials.unhide', $m) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button class="text-gray-700 hover:underline">Unhide</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection