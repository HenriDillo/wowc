@extends('layouts.employee')

@section('page_title', 'Item Management')

@section('content')

    <form action="{{ route('employee.items.store') }}" method="POST" class="bg-white p-4 rounded-md border border-gray-200 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        @csrf
        <input type="text" name="name" placeholder="Item name" class="border rounded px-3 py-2" required>
        <input type="number" name="stock" placeholder="Stock" class="border rounded px-3 py-2" min="0" required>
        <input type="number" step="0.01" name="price" placeholder="Price" class="border rounded px-3 py-2" min="0" required>
        <button class="bg-[#c49b6e] text-white px-4 py-2 rounded hover:bg-[#b08a5c]">Add Item</button>
    </form>

    <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product List</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visible</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($items as $i)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $i->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $i->stock }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">₱{{ number_format($i->price, 2) }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($i->visible)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700">Shown</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Hidden</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <form action="{{ route('employee.items.update', $i) }}" method="POST" class="inline-flex items-center space-x-2">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $i->name }}" class="border rounded px-2 py-1 w-40">
                            <input type="number" name="stock" value="{{ $i->stock }}" class="border rounded px-2 py-1 w-20" min="0">
                            <input type="number" step="0.01" name="price" value="{{ $i->price }}" class="border rounded px-2 py-1 w-24" min="0">
                            <button class="text-blue-600 hover:underline">Save</button>
                        </form>
                        <form action="{{ route('employee.items.toggle', $i) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button class="ml-3 text-gray-700 hover:underline">{{ $i->visible ? 'Hide' : 'Unhide' }}</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>
        {{ $items->links() }}
    </div>

@endsection


