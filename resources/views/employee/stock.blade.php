@extends('layouts.employee')

@section('page_title', 'Stock Management')

@section('content')
<div x-data="{ tab: 'items', modals: {} }" class="space-y-4">

    <!-- Tabs -->
    <div class="flex space-x-2">
        <button @click="tab='items'" :class="tab==='items' ? 'bg-[#c49b6e] text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-lg border">Items</button>
        <button @click="tab='materials'" :class="tab==='materials' ? 'bg-[#c49b6e] text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-lg border">Raw Materials</button>
    </div>

    <!-- Items Tab -->
    <div x-show="tab==='items'" x-cloak class="bg-white rounded-lg border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach($items as $item)
                    <tr>
                        <td class="px-4 py-3">
                            @php $photo = $item->photos->first(); @endphp
                            @if($photo)
                                <img src="{{ asset('storage/'.$photo->path) }}" alt="{{ $item->name }}" class="h-10 w-10 rounded object-cover">
                            @else
                                <div class="h-10 w-10 rounded bg-gray-200"></div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $item->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $item->stock }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">pcs</td>
                        <td class="px-4 py-3">
                            @php $status = $item->stock <= 0 ? 'Out of Stock' : ($item->stock < 5 ? 'Low' : 'OK'); @endphp
                            <span class="px-2 py-1 text-xs rounded {{ $status==='OK' ? 'bg-green-100 text-green-700' : ($status==='Low' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="modals['add_item_{{ $item->id }}']=true" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Add Stock</button>
                        </td>
                    </tr>

                    <!-- Add Item Stock Modal -->
                    <div x-show="modals['add_item_{{ $item->id }}']" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-5" @click.outside="modals['add_item_{{ $item->id }}']=false">
                            <h3 class="text-lg font-semibold mb-3">Add Stock - {{ $item->name }}</h3>
                            <form method="POST" action="{{ route('stock.items.add', $item) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Quantity</label>
                                    <input name="quantity" type="number" min="1" required class="w-full border rounded px-3 py-2" />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Remarks (optional)</label>
                                    <textarea name="remarks" class="w-full border rounded px-3 py-2" rows="2"></textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="modals['add_item_{{ $item->id }}']=false" class="px-3 py-2 text-gray-700 border rounded">Cancel</button>
                                    <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Materials Tab -->
    <div x-show="tab==='materials'" x-cloak class="bg-white rounded-lg border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach($materials as $material)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $material->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $material->stock }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $material->unit }}</td>
                        <td class="px-4 py-3">
                            @php $status = ($material->stock ?? 0) <= 0 ? 'Out of Stock' : (($material->stock ?? 0) < 5 ? 'Low' : 'OK'); @endphp
                            <span class="px-2 py-1 text-xs rounded {{ $status==='OK' ? 'bg-green-100 text-green-700' : ($status==='Low' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button @click="modals['add_mat_{{ $material->id }}']=true" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Add Stock</button>
                            <button @click="modals['reduce_mat_{{ $material->id }}']=true" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">Reduce Stock</button>
                        </td>
                    </tr>

                    <!-- Add Material Stock Modal -->
                    <div x-show="modals['add_mat_{{ $material->id }}']" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-5" @click.outside="modals['add_mat_{{ $material->id }}']=false">
                            <h3 class="text-lg font-semibold mb-3">Add Stock - {{ $material->name }}</h3>
                            <form method="POST" action="{{ route('stock.materials.add', $material) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Quantity received</label>
                                    <input name="quantity" type="number" min="1" required class="w-full border rounded px-3 py-2" />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Remarks (optional)</label>
                                    <textarea name="remarks" class="w-full border rounded px-3 py-2" rows="2"></textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="modals['add_mat_{{ $material->id }}']=false" class="px-3 py-2 text-gray-700 border rounded">Cancel</button>
                                    <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Reduce Material Stock Modal -->
                    <div x-show="modals['reduce_mat_{{ $material->id }}']" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-5" @click.outside="modals['reduce_mat_{{ $material->id }}']=false">
                            <h3 class="text-lg font-semibold mb-3">Reduce Stock - {{ $material->name }}</h3>
                            <form method="POST" action="{{ route('stock.materials.reduce', $material) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Quantity used</label>
                                    <input name="quantity" type="number" min="1" required class="w-full border rounded px-3 py-2" />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Remarks (optional)</label>
                                    <textarea name="remarks" class="w-full border rounded px-3 py-2" rows="2"></textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="modals['reduce_mat_{{ $material->id }}']=false" class="px-3 py-2 text-gray-700 border rounded">Cancel</button>
                                    <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded">Reduce</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


