<?php
<!-- Add Item Stock Modal -->
<div id="addStockModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-8 rounded-lg w-96">
        <h3 class="text-lg font-semibold mb-4">Add Stock</h3>
        <form id="addStockForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea name="remarks" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('addStockModal')" class="bg-gray-200 px-4 py-2 rounded-md">Cancel</button>
                <button type="submit" class="bg-[#A9793E] text-white px-4 py-2 rounded-md">Add Stock</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Raw Material Stock Modal -->
<div id="addRawStockModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-8 rounded-lg w-96">
        <h3 class="text-lg font-semibold mb-4">Add Raw Material Stock</h3>
        <form id="addRawStockForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea name="remarks" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('addRawStockModal')" class="bg-gray-200 px-4 py-2 rounded-md">Cancel</button>
                <button type="submit" class="bg-[#A9793E] text-white px-4 py-2 rounded-md">Add Stock</button>
            </div>
        </form>
    </div>
</div>

<!-- Reduce Raw Material Stock Modal -->
<div id="reduceRawStockModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-8 rounded-lg w-96">
        <h3 class="text-lg font-semibold mb-4">Reduce Raw Material Stock</h3>
        <form id="reduceRawStockForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea name="remarks" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('reduceRawStockModal')" class="bg-gray-200 px-4 py-2 rounded-md">Cancel</button>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md">Reduce Stock</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddStockModal(id) {
    document.getElementById('addStockForm').action = `/stock/items/${id}`;
    document.getElementById('addStockModal').classList.remove('hidden');
    document.getElementById('addStockModal').classList.add('flex');
}

function openAddRawStockModal(id) {
    document.getElementById('addRawStockForm').action = `/stock/raw-materials/${id}/add`;
    document.getElementById('addRawStockModal').classList.remove('hidden');
    document.getElementById('addRawStockModal').classList.add('flex');
}

function openReduceRawStockModal(id) {
    document.getElementById('reduceRawStockForm').action = `/stock/raw-materials/${id}/reduce`;
    document.getElementById('reduceRawStockModal').classList.remove('hidden');
    document.getElementById('reduceRawStockModal').classList.add('flex');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('flex');
    document.getElementById(modalId).classList.add('hidden');
}
</script>