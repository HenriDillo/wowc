@extends('layouts.employee')

@section('page_title', 'Item Management')

@section('content')

    @php($editOpenId = null)
    @isset($visibleItems)
        @foreach($visibleItems as $vi)
            @if($errors->getBag('edit_' . $vi->id)->any())
                @php($editOpenId = $vi->id)
                @break
            @endif
        @endforeach
    @endisset

    <div x-data="{ 
        openAdd: {{ $errors->getBag('createItem')->any() ? 'true' : 'false' }}, 
        editId: {{ $editOpenId ? $editOpenId : 'null' }}, 
        search:'{{ $search ?? '' }}', 
        sLower:'',
        lightboxOpen: false,
        lightboxImages: [],
        lightboxIndex: 0,
        dragOver: false
    }" x-effect="sLower = (search||'').toLowerCase()" class="space-y-6">

        @if(session('status'))
            <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-2 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Items</h2>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" /></svg>
                        </span>
                        <form method="GET" action="{{ route('employee.items') }}">
                            <input name="search" x-model="search" placeholder="Search name or category..." class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" @change="$el.form.submit()" />
                        </form>
                    </div>
                    
                    <button @click="openAdd = true" class="inline-flex items-center gap-2 bg-[#c49b6e] text-white text-sm px-3 py-2 rounded-lg hover:bg-[#b08a5c]">
                        <span class="text-lg leading-none">+</span>
                        <span>Add Item</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Item Name</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Category</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Price</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Photos</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach($visibleItems as $i)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $i->name }}</td>
                            <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $i->category }}</td>
                            <td class="px-4 py-3 border-b text-sm text-gray-700">₱{{ number_format($i->price, 2) }}</td>
                            <td class="px-4 py-3 border-b text-sm">
                                <div class="flex items-center gap-2">
                                    @if($i->photos->count() > 0)
                                        @foreach($i->photos->take(3) as $index => $p)
                                            <div class="relative group cursor-pointer" 
                                                 @click="lightboxImages = {{ $i->photos->pluck('url')->toJson() }}; lightboxIndex = {{ $index }}; lightboxOpen = true">
                                                <img src="{{ $p->url }}" alt="Item Photo" class="w-[60px] h-[60px] object-cover rounded-lg border border-gray-300 hover:border-[#c49b6e] transition-all duration-200 shadow-sm hover:shadow-md" />
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all duration-200 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($i->photos->count() > 3)
                                            <div class="w-[60px] h-[60px] bg-gray-100 rounded-lg border border-gray-300 flex items-center justify-center text-xs font-medium text-gray-600">
                                                +{{ $i->photos->count() - 3 }}
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
                                @if($i->visible)
                                    <span class="px-2 py-1 bg-green-500 text-white rounded">Shown</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-400 text-white rounded">Hidden</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border-b text-sm">
                                <div class="flex items-center gap-2">
                                    <button @click="editId = {{ $i->id }}" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Edit</button>
                                    <form action="{{ route('employee.items.toggle', $i) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-1.5 text-sm rounded text-white {{ $i->visible ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }}">{{ $i->visible ? 'Hide' : 'Unhide' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <div x-show="editId === {{ $i->id }}" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
                            <div @click.outside="editId = null" class="bg-white p-6 rounded-lg w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto" 
                                 x-data="{ 
                                     newPreviews: [], 
                                     removedPhotos: new Set(),
                                     dragOver: false,
                                     uploadProgress: 0
                                 }">
                                <h2 class="text-xl font-semibold mb-4">Edit Item</h2>
                                <form action="{{ route('employee.items.update', $i) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <input type="text" name="name" value="{{ old('name', $i->name) }}" class="w-full border rounded px-3 py-2" required>
                                            @php($bag = 'edit_' . $i->id)
                                            @if($errors->getBag($bag)->has('name'))
                                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('name') }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            @php($currentCat = old('category', $i->category))
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
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <input type="number" step="0.01" name="price" value="{{ old('price', $i->price) }}" class="w-full border rounded px-3 py-2" required>
                                            @if($errors->getBag($bag)->has('price'))
                                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('price') }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <input type="text" name="description" value="{{ old('description', $i->description) }}" class="w-full border rounded px-3 py-2" placeholder="Description (optional)">
                                            @if($errors->getBag($bag)->has('description'))
                                                <p class="text-red-500 text-sm mt-1">{{ $errors->getBag($bag)->first('description') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- Photo Management Section --}}
                                    <div class="border-t pt-4">
                                        <p class="text-sm font-medium text-gray-700 mb-3">Photo Management</p>
                                        
                                        {{-- Existing Photos --}}
                                        @if($i->photos->count() > 0)
                                            <div class="mb-4">
                                                <p class="text-xs text-gray-600 mb-2">Current Photos (click to remove)</p>
                                                <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                                                    @foreach($i->photos as $p)
                                                        <div class="relative inline-block" x-data="{ busy: false }" data-photo-id="{{ $p->id }}">
                                                            <img src="{{ $p->url }}" class="w-20 h-20 object-cover rounded border border-gray-200" alt="Item Photo">
                                                            <button type="button"
                                                                    @click="if (busy) return; if (confirm('Are you sure you want to remove this photo?')) { busy = true; window.removePhoto({{ $p->id }}).catch(() => { busy = false; }); }"
                                                                    class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full hover:bg-red-600 shadow">
                                                                ✕
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Upload New Photos --}}
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors duration-200"
                                             :class="{ 'border-[#c49b6e] bg-[#c49b6e]/5': dragOver }"
                                             @dragover.prevent="dragOver = true"
                                             @dragleave.prevent="dragOver = false"
                                             @drop.prevent="
                                                 dragOver = false;
                                                 const files = Array.from($event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                                                 if (files.length > 0) {
                                                     const fileInput = $refs.fileInput;
                                                     fileInput.files = new DataTransfer().files;
                                                     files.forEach(f => {
                                                         const dt = new DataTransfer();
                                                         dt.items.add(f);
                                                         fileInput.files = dt.files;
                                                     });
                                                     newPreviews = files.map(f => URL.createObjectURL(f));
                                                 }
                                             ">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="mt-2">
                                                <label for="photos_{{ $i->id }}" class="cursor-pointer">
                                                    <span class="mt-2 block text-sm font-medium text-gray-900">Add new photos</span>
                                                    <span class="mt-1 block text-sm text-gray-500">or drag and drop</span>
                                                </label>
                                                <input type="file" 
                                                       id="photos_{{ $i->id }}"
                                                       x-ref="fileInput"
                                                       name="photos[]" 
                                                       multiple 
                                                       accept="image/png,image/jpeg,image/jpg" 
                                                       class="sr-only"
                                                       @change="newPreviews = Array.from($event.target.files).map(f => URL.createObjectURL(f))">
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">PNG, JPG up to 2MB each</p>
                                        </div>

                                        {{-- New Photo Previews --}}
                                        <div x-show="newPreviews.length > 0" class="mt-4">
                                            <p class="text-xs text-gray-600 mb-2">New Photos to Upload</p>
                                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                                                <template x-for="(src, index) in newPreviews" :key="index">
                                                    <div class="relative">
                                                        <img :src="src" class="h-20 w-20 object-cover rounded-lg border-2 border-green-300" />
                                                        <div class="absolute -top-1 -right-1 bg-green-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold">
                                                            +
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                                    <button type="button" @click="editId = null" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>
                                    <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Add Modal --}}
        <div x-show="openAdd" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div @click.outside="openAdd = false" class="bg-white p-6 rounded-lg w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto" 
                 x-data="{ 
                     previews: [], 
                     dragOver: false,
                     uploadProgress: 0
                 }">
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
                    
                    {{-- Photo Upload Section --}}
                    <div class="border-t pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">Add Photos</p>
                        
                        {{-- Drag and Drop Upload Area --}}
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-colors duration-200"
                             :class="{ 'border-[#c49b6e] bg-[#c49b6e]/5': dragOver }"
                             @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="
                                 dragOver = false;
                                 const files = Array.from($event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                                 if (files.length > 0) {
                                     const fileInput = $refs.fileInput;
                                     fileInput.files = new DataTransfer().files;
                                     files.forEach(f => {
                                         const dt = new DataTransfer();
                                         dt.items.add(f);
                                         fileInput.files = dt.files;
                                     });
                                     previews = files.map(f => URL.createObjectURL(f));
                                 }
                             ">
                            <svg class="mx-auto h-16 w-16 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="mt-4">
                                <label for="photos_add" class="cursor-pointer">
                                    <span class="mt-2 block text-lg font-medium text-gray-900">Upload photos</span>
                                    <span class="mt-1 block text-sm text-gray-500">or drag and drop images here</span>
                                </label>
                                <input type="file" 
                                       id="photos_add"
                                       x-ref="fileInput"
                                       name="photos[]" 
                                       multiple 
                                       accept="image/png,image/jpeg,image/jpg" 
                                       class="sr-only"
                                       @change="previews = Array.from($event.target.files).map(f => URL.createObjectURL(f))">
                            </div>
                            <p class="text-xs text-gray-500 mt-3">PNG, JPG up to 2MB each • Multiple files supported</p>
                            @if($errors->getBag('createItem')->has('photos.*'))
                                <p class="text-red-500 text-sm mt-2">{{ $errors->getBag('createItem')->first('photos.*') }}</p>
                            @endif
                        </div>

                        {{-- Photo Previews --}}
                        <div x-show="previews.length > 0" class="mt-6">
                            <p class="text-sm font-medium text-gray-700 mb-3">Selected Photos</p>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                                <template x-for="(src, index) in previews" :key="index">
                                    <div class="relative group">
                                        <img :src="src" class="h-20 w-20 object-cover rounded-lg border-2 border-green-300" />
                                        <div class="absolute -top-1 -right-1 bg-green-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold">
                                            +
                                        </div>
                                        <button type="button" 
                                                @click="previews.splice(index, 1)"
                                                class="absolute inset-0 bg-red-500 bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all duration-200 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-red-600 opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                        <button type="button" @click="openAdd = false" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Create Item</button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            {{ $visibleItems->links() }}
        </div>

        {{-- Card: Hidden Items --}}
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
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Photos</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach($hiddenItems as $h)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 border-b text-sm text-gray-900">{{ $h->name }}</td>
                            <td class="px-4 py-3 border-b text-sm text-gray-700">{{ $h->category }}</td>
                            <td class="px-4 py-3 border-b text-sm text-gray-700">₱{{ number_format($h->price, 2) }}</td>
                            <td class="px-4 py-3 border-b text-sm">
                                <div class="flex items-center gap-2">
                                    @if($h->photos->count() > 0)
                                        @foreach($h->photos->take(3) as $p)
                                            <img src="{{ asset('storage/' . $p->path) }}" alt="Item Photo" class="w-[60px] h-[60px] object-cover rounded-lg border border-gray-300" />
                                        @endforeach
                                        @if($h->photos->count() > 3)
                                            <div class="w-[60px] h-[60px] bg-gray-100 rounded-lg border border-gray-300 flex items-center justify-center text-xs font-medium text-gray-600">
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


