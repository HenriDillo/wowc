<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Custom Order Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('customer.orders.index') }}" class="text-indigo-600 hover:text-indigo-500">
                            &larr; Back to Orders
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Order Details</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="font-medium text-gray-500">Product Name</dt>
                                    <dd class="mt-1">{{ $customOrder->custom_name }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1">{{ $customOrder->description }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500">Quantity</dt>
                                    <dd class="mt-1">{{ $customOrder->quantity }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($customOrder->status)
                                                @case('pending_review')
                                                    bg-yellow-100 text-yellow-800
                                                    @break
                                                @case('approved')
                                                    bg-green-100 text-green-800
                                                    @break
                                                @case('rejected')
                                                    bg-red-100 text-red-800
                                                    @break
                                                @case('in_production')
                                                    bg-blue-100 text-blue-800
                                                    @break
                                                @case('completed')
                                                    bg-gray-100 text-gray-800
                                                    @break
                                            @endswitch
                                        ">
                                            {{ str_replace('_', ' ', ucfirst($customOrder->status)) }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mb-4">Customization Details</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="font-medium text-gray-500">Color</dt>
                                    <dd class="mt-1">{{ $customOrder->customization_details['color'] }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500">Material</dt>
                                    <dd class="mt-1">{{ $customOrder->customization_details['material'] }}</dd>
                                </div>
                            </dl>

                            @if($customOrder->reference_image_path)
                                <div class="mt-6">
                                    <h4 class="font-medium text-gray-500 mb-2">Reference Image</h4>
                                    <img src="{{ Storage::url($customOrder->reference_image_path) }}" 
                                        alt="Reference Image" 
                                        class="max-w-full h-auto rounded-lg shadow-md">
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($customOrder->price_estimate)
                        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Price Estimate</h3>
                            <p class="text-2xl font-bold text-gray-900">${{ number_format($customOrder->price_estimate, 2) }}</p>
                            @if($customOrder->status === 'approved')
                                <div class="mt-4">
                                    <a href="{{ route('checkout.custom', $customOrder->id) }}" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Proceed to Checkout
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>