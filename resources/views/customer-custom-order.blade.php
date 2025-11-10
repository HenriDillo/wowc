@extends('layouts.customer')

@section('content')
<div class="container mx-auto py-8">
    <h2 class="text-2xl font-bold mb-6">Request a Custom Order</h2>
    <form action="{{ route('customer.custom-order.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md max-w-lg mx-auto">
        @csrf
        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-semibold mb-2">Description <span class="text-red-500">*</span></label>
            <textarea id="description" name="description" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring" rows="3">{{ old('description') }}</textarea>
        </div>
        <div class="mb-4">
            <label for="quantity" class="block text-gray-700 font-semibold mb-2">Quantity <span class="text-red-500">*</span></label>
            <input type="number" id="quantity" name="quantity" min="1" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring" value="{{ old('quantity') }}">
        </div>
        <div class="mb-4">
            <label for="reference_image" class="block text-gray-700 font-semibold mb-2">Reference Image</label>
            <input type="file" id="reference_image" name="reference_image" accept="image/*" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring" onchange="previewImage(event)">
            <div id="imagePreview" class="mt-2"></div>
        </div>
        <div class="mb-4">
            <label for="dimensions" class="block text-gray-700 font-semibold mb-2">Dimensions <span class="text-red-500">*</span></label>
            <input type="text" id="dimensions" name="customization_details[dimensions]" required placeholder="e.g., 30cm x 20cm x 15cm" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring" value="{{ old('customization_details.dimensions') }}">
        </div>
        <div class="mb-4">
            <label for="additional_instructions" class="block text-gray-700 font-semibold mb-2">Additional Instructions</label>
            <textarea id="additional_instructions" name="customization_details[additional_instructions]" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring" rows="2">{{ old('customization_details.additional_instructions') }}</textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit Request</button>
    </form>
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'max-h-40 mt-2 rounded shadow';
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
