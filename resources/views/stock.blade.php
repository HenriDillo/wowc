<?php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Stock Management') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ activeTab: 'items' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-6">
                        <div class="flex space-x-8">
                            <button @click="activeTab = 'items'" 
                                    :class="{'border-b-2 border-[#A9793E] text-[#A9793E]': activeTab === 'items'}"
                                    class="py-4 px-1 font-medium">
                                Items
                            </button>
                            <button @click="activeTab = 'raw'" 
                                    :class="{'border-b-2 border-[#A9793E] text-[#A9793E]': activeTab === 'raw'}"
                                    class="py-4 px-1 font-medium">
                                Raw Materials
                            </button>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div x-show="activeTab === 'items'">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->stock }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($product->stock <= 0)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>
                                        @elseif($product->stock <= $product->low_stock_threshold)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button onclick="openAddStockModal('{{ $product->id }}')" class="text-[#A9793E] hover:text-[#8F6532]">Add Stock</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Raw Materials Table -->
                    <div x-show="activeTab === 'raw'">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($rawMaterials as $material)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $material->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $material->stock }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($material->stock <= 0)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>
                                        @elseif($material->stock <= $material->low_stock_threshold)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap space-x-4">
                                        <button onclick="openAddRawStockModal('{{ $material->id }}')" class="text-[#A9793E] hover:text-[#8F6532]">Add Stock</button>
                                        <button onclick="openReduceRawStockModal('{{ $material->id }}')" class="text-red-600 hover:text-red-800">Reduce Stock</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Modals -->
    @include('partials.stock-modals')
</x-app-layout>