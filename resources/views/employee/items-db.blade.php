@extends('layouts.employee')@extends('layouts.employee')



@section('page_title', 'Production Management')@section('page_title', 'Production Management')



@section('content')@section('content')



    @php    @php

        $editOpenId = null;        $editOpenId = null;

    @endphp    @endphp

    @isset($visibleItems)    @isset($visibleItems)

        @foreach($visibleItems as $vi)        @foreach($visibleItems as $vi)

            @if($errors->getBag('edit_' . $vi->id)->any())            @if($errors->getBag('edit_' . $vi->id)->any())

                @php                @php

                    $editOpenId = $vi->id;                    $editOpenId = $vi->id;

                @endphp                @endphp

                @break                @break

            @endif            @endif

        @endforeach        @endforeach

    @endisset    @endisset



    <div x-data="{     <div x-data="{ 

        openAdd: {{ $errors->getBag('createItem')->any() ? 'true' : 'false' }},         openAdd: {{ $errors->getBag('createItem')->any() ? 'true' : 'false' }}, 

        editId: {{ $editOpenId ? $editOpenId : 'null' }},         editId: {{ $editOpenId ? $editOpenId : 'null' }}, 

        addStockId: null,        addStockId: null,

        bulkAddOpen: false,        search:'{{ $search ?? '' }}', 

        bulkReduceOpen: false,        sLower:'',

        search:'{{ $search ?? '' }}',         lightboxOpen: false,

        sLower:'',        lightboxImages: [],

        lightboxOpen: false,        lightboxIndex: 0,

        lightboxImages: [],        dragOver: false

        lightboxIndex: 0,    }" x-effect="sLower = (search||'').toLowerCase()" class="space-y-6">

        dragOver: false

    }" x-effect="sLower = (search||'').toLowerCase()" class="space-y-6">        @if(session('status'))

            <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-2 rounded">{{ session('status') }}</div>

        @if(session('status'))        @endif

            <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-2 rounded">{{ session('status') }}</div>

        @endif        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 border-b">

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">                <h2 class="text-lg font-semibold text-gray-800">Items</h2>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 border-b">                <div class="flex items-center gap-3 w-full sm:w-auto">

                <h2 class="text-lg font-semibold text-gray-800">Items (Production)</h2>                    <div class="relative w-full sm:w-64">

                <div class="flex items-center gap-3 w-full sm:w-auto">                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">

                    <div class="relative w-full sm:w-64">                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" /></svg>

                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">                        </span>

                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" /></svg>                        <form method="GET" action="{{ route('employee.items') }}">

                        </span>                            <input name="search" x-model="search" placeholder="Search name or category..." class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" @change="$el.form.submit()" />

                        <form method="GET" action="{{ route('employee.items') }}">                        </form>

                            <input name="search" x-model="search" placeholder="Search name or category..." class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" @change="$el.form.submit()" />                    </div>

                        </form>                    

                    </div>                    <button @click="openAdd = true" class="inline-flex items-center gap-2 bg-[#c49b6e] text-white text-sm px-3 py-2 rounded-lg hover:bg-[#b08a5c]">

                                            <span class="text-lg leading-none">+</span>

                    <div class="flex gap-2">                        <span>Add Item</span>

                        <button @click="openAdd = true" class="inline-flex items-center gap-2 bg-[#c49b6e] text-white text-sm px-3 py-2 rounded-lg hover:bg-[#b08a5c]">                    </button>

                            <span class="text-lg leading-none">+</span>                </div>

                            <span>Add Item</span>            </div>

                        </button>

                        <button @click="bulkAddOpen = true" class="inline-flex items-center gap-1 bg-green-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-green-700" title="Bulk add stock to multiple items">            <div class="overflow-x-auto">

                            <span class="text-sm">📦</span>                <table class="w-full border-collapse">

                            <span>Bulk Add</span>                    <thead class="bg-gray-50">

                        </button>                        <tr>

                        <button @click="bulkReduceOpen = true" class="inline-flex items-center gap-1 bg-orange-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-orange-700" title="Bulk reduce stock from multiple items">                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Item Name</th>

                            <span class="text-sm">📤</span>                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Category</th>

                            @extends('layouts.employee')

                            @section('page_title', 'Production Management')

                            @section('content')
                            <div x-data="{ openAdd: false, editId: null, addStockId: null, bulkAddOpen: false, bulkReduceOpen: false, search: '{{ $search ?? '' }}', searchLower: '' }" x-effect="searchLower = (search || '').toLowerCase()" class="space-y-6">

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
                                                <button @click="bulkReduceOpen = true" class="inline-flex items-center gap-1 bg-orange-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-orange-700" title="Bulk reduce stock from multiple items">
                                                    <span class="text-sm">📤</span>
                                                    <span>Bulk Reduce</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full border-collapse">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Item Name</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Category</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Price</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Current Stock</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Stock Status</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Photos</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Visibility</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white">
                                                @foreach($visibleItems as $i)
                                                    <tr class="hover:bg-gray-50" x-show="searchLower === '' || '{{ strtolower($i->name) }}'.includes(searchLower) || '{{ strtolower($i->category) }}'.includes(searchLower)">
                                                        <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $i->name }}</td>
                                                        <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $i->category }}</td>
                                                        <td class="px-4 py-3 border-b text-sm text-gray-700">₱{{ number_format($i->price, 2) }}</td>
                                                        <td class="px-4 py-3 border-b text-sm text-gray-700 font-medium">{{ $i->stock ?? 0 }}</td>
                                                        <td class="px-4 py-3 border-b text-sm">
                                                            @php $status = ($i->stock ?? 0) <= 0 ? 'Out of Stock' : (($i->stock ?? 0) < 5 ? 'Low' : 'OK'); @endphp
                                                            <span class="px-2 py-1 text-xs rounded {{ $status==='OK' ? 'bg-green-100 text-green-700' : ($status==='Low' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $status }}</span>
                                                        </td>
                                                        <td class="px-4 py-3 border-b text-sm">
                                                            @if($i->photos->count() > 0)
                                                                <div class="flex gap-2">
                                                                    @foreach($i->photos->take(3) as $p)
                                                                        <img src="{{ $p->url }}" alt="Item Photo" class="w-[48px] h-[48px] object-cover rounded-lg border border-gray-300" />
                                                                    @endforeach
                                                                    @if($i->photos->count() > 3)
                                                                        <div class="w-[48px] h-[48px] bg-gray-100 rounded-lg border border-gray-300 flex items-center justify-center text-xs font-medium text-gray-600">+{{ $i->photos->count() - 3 }}</div>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <span class="text-sm text-gray-500">No photos</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 border-b text-sm">
                                                            @if($i->visible)
                                                                <span class="px-2 py-1 bg-green-500 text-white rounded">Shown</span>
                                                            @else
                                                                <span class="px-2 py-1 bg-gray-400 text-white rounded">Hidden</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 border-b text-sm">
                                                            <div class="flex items-center gap-2">
                                                                <button @click="editId = {{ $i->id }}" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Edit</button>
                                                                <button @click="addStockId = {{ $i->id }}" class="px-3 py-1.5 text-xs bg-green-600 text-white rounded hover:bg-green-700">Add</button>
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
                                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                                                        <input type="text" name="name" value="{{ old('name', $i->name) }}" class="w-full border rounded px-3 py-2" required>
                                                                        @php($bag = 'edit_' . $i->id)
                                                                        @if($errors->getBag($bag)->has('name'))
                                                                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('name') }}</p>
                                                                        @endif
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                                                        @php $currentCat = old('category', $i->category); @endphp
                                                                        <select name="category" class="w-full border rounded px-3 py-2" required>
                                                                            <option value="Caddy" {{ $currentCat === 'Caddy' ? 'selected' : '' }}>Caddy</option>
                                                                            <option value="Carpet" {{ $currentCat === 'Carpet' ? 'selected' : '' }}>Carpet</option>
                                                                            <option value="Placemat" {{ $currentCat === 'Placemat' ? 'selected' : '' }}>Placemat</option>
                                                                            <option value="Others" {{ $currentCat === 'Others' ? 'selected' : '' }}>Others</option>
                                                                        </select>
                                                                        @if($errors->getBag($bag)->has('category'))
                                                                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('category') }}</p>
                                                                        @endif
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                                                                        <input type="number" step="0.01" name="price" value="{{ old('price', $i->price) }}" class="w-full border rounded px-3 py-2" required>
                                                                        @if($errors->getBag($bag)->has('price'))
                                                                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('price') }}</p>
                                                                        @endif
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                                                        <input type="text" name="description" value="{{ old('description', $i->description) }}" class="w-full border rounded px-3 py-2" placeholder="Description (optional)">
                                                                        @if($errors->getBag($bag)->has('description'))
                                                                            <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('description') }}</p>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Photos (optional)</label>
                                                                    <input type="file" name="photos[]" multiple class="w-full" />
                                                                    @if($errors->getBag($bag)->has('photos'))
                                                                        <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('photos') }}</p>
                                                                    @endif
                                                                </div>

                                                                <div class="flex justify-end gap-2">
                                                                    <button type="button" @click="editId = null" class="px-3 py-2 text-gray-700 border rounded">Cancel</button>
                                                                    <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    {{ $visibleItems->links() }}
                                </div>

                                <div class="bg-white border border-gray-200 rounded-xl shadow-sm mt-6">
                                    <div class="flex items-center justify-between px-4 py-3 border-b">
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-800">📋 Stock Transaction History</h3>
                                        <span class="text-sm text-gray-500">Last 30 transactions</span>
                                    </div>
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
                                            <p>No transactions yet. Start managing stock to see transaction history.</p>
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
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Visibility</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white">
                                                @foreach($hiddenItems as $i)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $i->name }}</td>
                                                        <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $i->category }}</td>
                                                        <td class="px-4 py-3 border-b text-sm">
                                                            <span class="px-2 py-1 bg-gray-500 text-white rounded">Hidden</span>
                                                        </td>
                                                        <td class="px-4 py-3 border-b text-sm">
                                                            <form action="{{ route('employee.items.toggle', $i) }}" method="POST" class="inline">
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

                                        <button type="button" @click="editId = null" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>                                            </div>

                                        <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>                                            <p class="text-xs text-gray-500 mt-2">PNG, JPG up to 2MB each</p>

                                    </div>                                        </div>

                                </form>

                            </div>                                        {{-- New Photo Previews --}}

                        </div>                                        <div x-show="newPreviews.length > 0" class="mt-4">

                        @endforeach                                            <p class="text-xs text-gray-600 mb-2">New Photos to Upload</p>

                    </tbody>                                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">

                </table>                                                <template x-for="(src, index) in newPreviews" :key="index">

            </div>                                                    <div class="relative">

        </div>                                                        <img :src="src" class="h-20 w-20 object-cover rounded-lg border-2 border-green-300" />

                                                        <div class="absolute -top-1 -right-1 bg-green-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold">

        <div>                                                            +

            {{ $visibleItems->links() }}                                                        </div>

        </div>                                                    </div>

                                                </template>

        {{-- Stock Transaction History Section --}}                                            </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mt-6">                                        </div>

            <div class="flex items-center justify-between px-4 py-3 border-b">                                    </div>

                <h3 class="text-base sm:text-lg font-semibold text-gray-800">📋 Stock Transaction History</h3>

                <span class="text-sm text-gray-500">Last 30 transactions</span>                                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">

            </div>                                        <button type="button" @click="editId = null" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>

            @if($recentTransactions->count() > 0)                                        <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>

                <div class="overflow-x-auto">                                    </div>

                    <table class="w-full border-collapse">                                </form>

                        <thead class="bg-gray-50">                            </div>

                            <tr>                        </div>

                                <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Date & Time</th>                        @endforeach

                                <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Employee</th>                    </tbody>

                                <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Item</th>                </table>

                                <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Type</th>            </div>

                                <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Quantity</th>        </div>

                                <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Remarks</th>

                            </tr>        {{-- Add Modal --}}

                        </thead>        <div x-show="openAdd" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">

                        <tbody class="bg-white">            <div @click.outside="openAdd = false" class="bg-white p-6 rounded-lg w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto" 

                            @foreach($recentTransactions as $trans)                 x-data="{ 

                                <tr class="hover:bg-gray-50 border-b">                     previews: [], 

                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $trans->created_at->format('M d, Y H:i') }}</td>                     dragOver: false,

                                    <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $trans->user?->name ?? 'N/A' }}</td>                     uploadProgress: 0

                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $trans->item?->name ?? 'N/A' }}</td>                 }">

                                    <td class="px-4 py-3 text-sm">                <h2 class="text-xl font-semibold mb-4">Add New Item</h2>

                                        @if($trans->type === 'in')                <form action="{{ route('employee.items.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">

                                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700 font-semibold">📥 Stock In</span>                    @csrf

                                        @else                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                            <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700 font-semibold">📤 Stock Out</span>                        <div>

                                        @endif                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Item name" class="w-full border rounded px-3 py-2" required>

                                    </td>                            @if($errors->getBag('createItem')->has('name'))

                                    <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $trans->quantity }}</td>                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createItem')->first('name') }}</p>

                                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate" title="{{ $trans->remarks }}">{{ $trans->remarks ?? '-' }}</td>                            @endif

                                </tr>                        </div>

                            @endforeach                        <div>

                        </tbody>                            <select name="category" class="w-full border rounded px-3 py-2" required>

                    </table>                                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select category</option>

                </div>                                <option value="Caddy" {{ old('category') === 'Caddy' ? 'selected' : '' }}>Caddy</option>

            @else                                <option value="Carpet" {{ old('category') === 'Carpet' ? 'selected' : '' }}>Carpet</option>

                <div class="p-6 text-center text-gray-500">                                <option value="Placemat" {{ old('category') === 'Placemat' ? 'selected' : '' }}>Placemat</option>

                    <p>No transactions yet. Start managing stock to see transaction history.</p>                                <option value="Others" {{ old('category') === 'Others' ? 'selected' : '' }}>Others</option>

                </div>                            </select>

            @endif                            @if($errors->getBag('createItem')->has('category'))

        </div>                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createItem')->first('category') }}</p>

                            @endif

        {{-- Card: Hidden Items --}}                        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mt-6">                    </div>

            <div class="flex items-center justify-between px-4 py-3 border-b">                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Hidden Items</h3>                        <div>

            </div>                            <input type="number" step="0.01" name="price" value="{{ old('price') }}" placeholder="Price" class="w-full border rounded px-3 py-2" min="0" required>

            <div class="overflow-x-auto">                            @if($errors->getBag('createItem')->has('price'))

                <table class="w-full border-collapse">                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createItem')->first('price') }}</p>

                    <thead class="bg-gray-50">                            @endif

                        <tr>                        </div>

                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Item Name</th>                        <div>

                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Category</th>                            <input type="text" name="description" value="{{ old('description') }}" placeholder="Description (optional)" class="w-full border rounded px-3 py-2">

                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>                            @if($errors->getBag('createItem')->has('description'))

                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag('createItem')->first('description') }}</p>

                        </tr>                            @endif

                    </thead>                        </div>

                    <tbody class="bg-white">                    </div>

                        @foreach($hiddenItems as $h)                    

                        <tr class="hover:bg-gray-50">                    {{-- Photo Upload Section --}}

                            <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $h->name }}</td>                    <div class="border-t pt-4">

                            <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $h->category }}</td>                        <p class="text-sm font-medium text-gray-700 mb-3">Add Photos</p>

                            <td class="px-4 py-3 border-b text-sm">                        

                                <span class="px-2 py-1 bg-gray-400 text-white rounded">Hidden</span>                        {{-- Drag and Drop Upload Area --}}

                            </td>                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-colors duration-200"

                            <td class="px-4 py-3 border-b text-sm">                             :class="{ 'border-[#c49b6e] bg-[#c49b6e]/5': dragOver }"

                                <form action="{{ route('employee.items.toggle', $h) }}" method="POST" class="inline">                             @dragover.prevent="dragOver = true"

                                    @csrf                             @dragleave.prevent="dragOver = false"

                                    @method('PATCH')                             @drop.prevent="

                                    <button class="text-blue-600 hover:text-blue-700 underline">Unhide</button>                                 dragOver = false;

                                </form>                                 const files = Array.from($event.dataTransfer.files).filter(f => f.type.startsWith('image/'));

                            </td>                                 if (files.length > 0) {

                        </tr>                                     const fileInput = $refs.fileInput;

                        @endforeach                                     fileInput.files = new DataTransfer().files;

                    </tbody>                                     files.forEach(f => {

                </table>                                         const dt = new DataTransfer();

            </div>                                         dt.items.add(f);

            <div class="px-4 py-3 border-t">                                         fileInput.files = dt.files;

                {{ $hiddenItems->links() }}                                     });

            </div>                                     previews = files.map(f => URL.createObjectURL(f));

        </div>                                 }

                             ">

        {{-- Lightbox Gallery --}}                            <svg class="mx-auto h-16 w-16 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">

        <div x-show="lightboxOpen" x-cloak x-transition class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center"                                 <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

             @click.self="lightboxOpen = false"                            </svg>

             @keydown.escape.window="lightboxOpen = false">                            <div class="mt-4">

            <div class="relative max-w-4xl max-h-[90vh] w-full mx-4">                                <label for="photos_add" class="cursor-pointer">

                {{-- Close Button --}}                                    <span class="mt-2 block text-lg font-medium text-gray-900">Upload photos</span>

                <button @click="lightboxOpen = false"                                     <span class="mt-1 block text-sm text-gray-500">or drag and drop images here</span>

                        class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors z-10">                                </label>

                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">                                <input type="file" 

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>                                       id="photos_add"

                    </svg>                                       x-ref="fileInput"

                </button>                                       name="photos[]" 

                                       multiple 

                {{-- Navigation Arrows --}}                                       accept="image/png,image/jpeg,image/jpg" 

                <button x-show="lightboxImages.length > 1"                                        class="sr-only"

                        @click="lightboxIndex = lightboxIndex > 0 ? lightboxIndex - 1 : lightboxImages.length - 1"                                       @change="previews = Array.from($event.target.files).map(f => URL.createObjectURL(f))">

                        class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors z-10">                            </div>

                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">                            <p class="text-xs text-gray-500 mt-3">PNG, JPG up to 2MB each • Multiple files supported</p>

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>                            @if($errors->getBag('createItem')->has('photos.*'))

                    </svg>                                <p class="text-red-500 text-sm mt-2">{{ $errors->getBag('createItem')->first('photos.*') }}</p>

                </button>                            @endif

                                        </div>

                <button x-show="lightboxImages.length > 1" 

                        @click="lightboxIndex = lightboxIndex < lightboxImages.length - 1 ? lightboxIndex + 1 : 0"                        {{-- Photo Previews --}}

                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors z-10">                        <div x-show="previews.length > 0" class="mt-6">

                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">                            <p class="text-sm font-medium text-gray-700 mb-3">Selected Photos</p>

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">

                    </svg>                                <template x-for="(src, index) in previews" :key="index">

                </button>                                    <div class="relative group">

                                        <img :src="src" class="h-20 w-20 object-cover rounded-lg border-2 border-green-300" />

                {{-- Main Image --}}                                        <div class="absolute -top-1 -right-1 bg-green-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold">

                <div class="flex items-center justify-center h-full">                                            +

                    <img :src="lightboxImages[lightboxIndex]"                                         </div>

                         class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"                                        <button type="button" 

                         @click.stop>                                                @click="previews.splice(index, 1)"

                </div>                                                class="absolute inset-0 bg-red-500 bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all duration-200 flex items-center justify-center">

                                            <svg class="w-4 h-4 text-red-600 opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                {{-- Thumbnail Strip --}}                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>

                <div x-show="lightboxImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2">                                            </svg>

                    <div class="flex space-x-2 bg-black bg-opacity-50 rounded-lg p-2">                                        </button>

                        <template x-for="(src, index) in lightboxImages" :key="index">                                    </div>

                            <button @click="lightboxIndex = index"                                </template>

                                    class="w-12 h-12 rounded overflow-hidden border-2 transition-all duration-200"                            </div>

                                    :class="lightboxIndex === index ? 'border-white' : 'border-transparent hover:border-gray-400'">                        </div>

                                <img :src="src" class="w-full h-full object-cover">                    </div>

                            </button>

                        </template>                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">

                    </div>                        <button type="button" @click="openAdd = false" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>

                </div>                        <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Create Item</button>

                    </div>

                {{-- Image Counter --}}                </form>

                <div x-show="lightboxImages.length > 1" class="absolute top-4 left-4 text-white bg-black bg-opacity-50 rounded px-3 py-1 text-sm">            </div>

                    <span x-text="lightboxIndex + 1"></span> / <span x-text="lightboxImages.length"></span>        </div>

                </div>

            </div>        <div>

        </div>            {{ $visibleItems->links() }}

    </div>        </div>



@endsection        {{-- Card: Hidden Items --}}

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mt-6">

    @push('scripts')            <div class="flex items-center justify-between px-4 py-3 border-b">

    <script>                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Hidden Items</h3>

        window.removePhoto = async function(photoId) {            </div>

            try {            <div class="overflow-x-auto">

                const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');                <table class="w-full border-collapse">

                const response = await fetch(`{{ url('/employee/items/photos') }}/${photoId}`, {                    <thead class="bg-gray-50">

                    method: 'DELETE',                        <tr>

                    headers: {                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Item Name</th>

                        'X-Requested-With': 'XMLHttpRequest',                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Category</th>

                        'X-CSRF-TOKEN': token,                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Price</th>

                        'Accept': 'application/json'                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Photos</th>

                    }                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>

                });                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>

                const result = await response.json().catch(() => ({}));                        </tr>

                if (!response.ok || !result.success) {                    </thead>

                    alert(result.message || 'Failed to delete photo.');                    <tbody class="bg-white">

                    return;                        @foreach($hiddenItems as $h)

                }                        <tr class="hover:bg-gray-50">

                            <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $h->name }}</td>

                const el = document.querySelector(`[data-photo-id="${photoId}"]`);                            <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $h->category }}</td>

                if (el) {                            <td class="px-4 py-3 border-b text-sm text-gray-700">₱{{ number_format($h->price, 2) }}</td>

                    el.remove();                            <td class="px-4 py-3 border-b text-sm">

                }                                <div class="flex items-center gap-2">

            } catch (e) {                                    @if($h->photos->count() > 0)

                alert('Failed to delete photo.');                                        @foreach($h->photos->take(3) as $p)

            }                                            <img src="{{ asset('storage/' . $p->path) }}" alt="Item Photo" class="w-[60px] h-[60px] object-cover rounded-lg border border-gray-300" />

        }                                        @endforeach

    </script>                                        @if($h->photos->count() > 3)

    @endpush                                            <div class="w-[60px] h-[60px] bg-gray-100 rounded-lg border border-gray-300 flex items-center justify-center text-xs font-medium text-gray-600">

                                                +{{ $h->photos->count() - 3 }}
                                            </div>
                                        @endif
                                    @else
                                        <div class="w-[60px] h-[60px] bg-gray-100 rounded-lg border border-dashed border-gray-300 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 border-b text-sm">
                                <span class="px-2 py-1 bg-gray-400 text-white rounded">Hidden</span>
                            </td>
                            <td class="px-4 py-3 border-b text-sm">
                                <form action="{{ route('employee.items.toggle', $h) }}" method="POST" class="inline">
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
            <div class="px-4 py-3 border-t">
                {{ $hiddenItems->links() }}
            </div>
        </div>

        {{-- Lightbox Gallery --}}
        <div x-show="lightboxOpen" x-cloak x-transition class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center" 
             @click.self="lightboxOpen = false"
             @keydown.escape.window="lightboxOpen = false">
            <div class="relative max-w-4xl max-h-[90vh] w-full mx-4">
                {{-- Close Button --}}
                <button @click="lightboxOpen = false" 
                        class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                {{-- Navigation Arrows --}}
                <button x-show="lightboxImages.length > 1" 
                        @click="lightboxIndex = lightboxIndex > 0 ? lightboxIndex - 1 : lightboxImages.length - 1"
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <button x-show="lightboxImages.length > 1" 
                        @click="lightboxIndex = lightboxIndex < lightboxImages.length - 1 ? lightboxIndex + 1 : 0"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                {{-- Main Image --}}
                <div class="flex items-center justify-center h-full">
                    <img :src="lightboxImages[lightboxIndex]" 
                         class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                         @click.stop>
                </div>

                {{-- Thumbnail Strip --}}
                <div x-show="lightboxImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2">
                    <div class="flex space-x-2 bg-black bg-opacity-50 rounded-lg p-2">
                        <template x-for="(src, index) in lightboxImages" :key="index">
                            <button @click="lightboxIndex = index"
                                    class="w-12 h-12 rounded overflow-hidden border-2 transition-all duration-200"
                                    :class="lightboxIndex === index ? 'border-white' : 'border-transparent hover:border-gray-400'">
                                <img :src="src" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Image Counter --}}
                <div x-show="lightboxImages.length > 1" class="absolute top-4 left-4 text-white bg-black bg-opacity-50 rounded px-3 py-1 text-sm">
                    <span x-text="lightboxIndex + 1"></span> / <span x-text="lightboxImages.length"></span>
                </div>
            </div>
        </div>
    </div>

@endsection

    @push('scripts')
    <script>
        window.removePhoto = async function(photoId) {
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                const response = await fetch(`{{ url('/employee/items/photos') }}/${photoId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok || !result.success) {
                    alert(result.message || 'Failed to delete photo.');
                    return;
                }

                const el = document.querySelector(`[data-photo-id="${photoId}"]`);
                if (el) {
                    el.remove();
                }
            } catch (e) {
                alert('Failed to delete photo.');
            }
        }
    </script>
    @endpush


