<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Custom Order Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('custom-orders.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="custom_name" :value="__('Product Name/Title')" />
                            <x-text-input id="custom_name" name="custom_name" type="text" class="mt-1 block w-full" 
                                :value="old('custom_name')" required autofocus />
                            <x-input-error :messages="$errors->get('custom_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description/Special Instructions')" />
                            <textarea id="description" name="description" rows="4" 
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="color" :value="__('Color')" />
                                <x-text-input id="color" name="customization_details[color]" type="text" 
                                    class="mt-1 block w-full" :value="old('customization_details.color')" required />
                                <x-input-error :messages="$errors->get('customization_details.color')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="material" :value="__('Material')" />
                                <x-text-input id="material" name="customization_details[material]" type="text" 
                                    class="mt-1 block w-full" :value="old('customization_details.material')" required />
                                <x-input-error :messages="$errors->get('customization_details.material')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="quantity" :value="__('Quantity')" />
                            <x-text-input id="quantity" name="quantity" type="number" min="1" 
                                class="mt-1 block w-full" :value="old('quantity', 1)" required />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="reference_image" :value="__('Reference Image')" />
                            <input type="file" id="reference_image" name="reference_image" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 
                                file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 
                                file:text-blue-700 hover:file:bg-blue-100" required
                                accept=".jpg,.jpeg,.png" />
                            <p class="mt-1 text-sm text-gray-500">Upload a reference image (JPEG, PNG up to 2MB)</p>
                            <x-input-error :messages="$errors->get('reference_image')" class="mt-2" />
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-x-6">
                            <a href="{{ route('products.index') }}" class="text-sm font-semibold leading-6 text-gray-900">
                                Cancel
                            </a>
                            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Submit Custom Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>