

<?php $__env->startSection('page_title', 'Production Management'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="{ openAdd: false, editId: null, addStockId: null, bulkAddOpen: false, search: '<?php echo e($search ?? ''); ?>', searchLower: '', bulkItems: [] }" x-effect="searchLower = (search || '').toLowerCase()" class="space-y-6">

    <?php if(session('status')): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-2 rounded"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

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
                    <form method="GET" action="<?php echo e(route('employee.items')); ?>">
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
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Stock</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php $__empty_1 = true; $__currentLoopData = $visibleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($i->name); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?php echo e($i->category); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱<?php echo e(number_format($i->price, 2)); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium"><?php echo e($i->stock ?? 0); ?></td>
                            <td class="px-4 py-3 text-sm">
                                <?php $status = ($i->stock ?? 0) <= 0 ? 'Out of Stock' : (($i->stock ?? 0) < 5 ? 'Low' : 'OK'); ?>
                                <span class="px-2 py-1 text-xs rounded <?php echo e($status==='OK' ? 'bg-green-100 text-green-700' : ($status==='Low' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')); ?>"><?php echo e($status); ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <button @click="addStockId = <?php echo e($i->id); ?>" class="px-3 py-1.5 text-xs bg-green-600 text-white rounded hover:bg-green-700" title="Add stock to this item">Add Stock</button>
                                    <button @click="editId = <?php echo e($i->id); ?>" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Edit</button>
                                    <form action="<?php echo e(route('employee.items.toggle', $i)); ?>" method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button class="px-3 py-1.5 text-xs rounded text-white <?php echo e($i->visible ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'); ?>"><?php echo e($i->visible ? 'Hide' : 'Unhide'); ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <?php echo e($visibleItems->links()); ?>

    </div>

    
    <div x-show="bulkAddOpen" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div id="bulkAddModal" @click.outside="bulkAddOpen = false" class="bg-white p-6 rounded-lg max-w-2xl w-full max-h-96 shadow-xl overflow-y-auto">
            <h2 class="text-xl font-semibold mb-4">Bulk Add Stock</h2>

            <form id="bulkAddForm" action="<?php echo e(route('employee.items.bulk-add-stock')); ?>" method="POST" class="space-y-4" onsubmit="return false;">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="bulk_add_csrf" value="<?php echo e(csrf_token()); ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Items & Quantities</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto border rounded p-3 bg-gray-50">
                        <?php $__currentLoopData = $visibleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-3 p-2 bg-white rounded border bulk-row">
                                <input type="checkbox" data-index="<?php echo e($loop->index); ?>" class="bulk-checkbox rounded cursor-pointer" value="<?php echo e($item->id); ?>" />
                                <label class="flex-1 cursor-pointer"><?php echo e($item->name); ?> (<?php echo e($item->category); ?>)</label>
                                <input type="number" data-index="<?php echo e($loop->index); ?>" class="bulk-qty w-20 border rounded px-2 py-1 text-sm" placeholder="Qty" min="1" />
                                <input type="hidden" name="items[<?php echo e($loop->index); ?>][item_id]" value="<?php echo e($item->id); ?>">
                                <input type="hidden" name="items[<?php echo e($loop->index); ?>][quantity]" value="" class="hidden-qty-<?php echo e($loop->index); ?>">
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (optional)</label>
                    <textarea id="bulkAddRemarks" name="remarks" class="w-full border rounded px-3 py-2 text-sm" rows="2" placeholder="e.g., New shipment received, Quality check passed..."></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="bulkAddOpen = false" class="px-4 py-2 border rounded-lg shadow-sm hover:bg-gray-50">Cancel</button>
                    <button type="button" x-on:click="(async function(){
                        const token = document.getElementById('bulk_add_csrf').value;
                        const rows = document.querySelectorAll('#bulkAddModal .bulk-row');
                        const items = [];
                        rows.forEach((row, idx) => {
                            const checkbox = row.querySelector('.bulk-checkbox');
                            const qtyInput = row.querySelector('.bulk-qty');
                            if(!checkbox || !qtyInput) return;
                            if(checkbox.checked){
                                const item_id = checkbox.value;
                                const quantity = parseInt(qtyInput.value) || 0;
                                if(quantity > 0){
                                    items.push({ item_id: item_id, quantity: quantity });
                                }
                            }
                        });

                        if(items.length === 0){
                            alert('Please select at least one item and enter a quantity.');
                            return;
                        }

                        const payload = { items: items, remarks: document.getElementById('bulkAddRemarks').value };
                        const url = '<?php echo e(route('employee.items.bulk-add-stock')); ?>';

                        try {
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(payload)
                            });

                            if(res.redirected){
                                window.location = res.url;
                                return;
                            }

                            if(res.ok){
                                window.location.reload();
                                return;
                            }

                            const errData = await res.json().catch(() => ({}));
                            alert(errData.message || 'Failed to add stock');
                        } catch(e) {
                            alert('Request failed: ' + e.message);
                        }
                    })()" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg shadow">Add Stock</button>
                </div>
            </form>
        </div>
    </div>

    
    <template x-if="addStockId">
        <div x-show="addStockId" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div @click.outside="addStockId = null" class="bg-white p-6 rounded-lg max-w-md w-full shadow-xl">
                <h2 class="text-xl font-semibold mb-4">Add Stock</h2>
                <form id="addStockForm" @submit.prevent="(async function(){
                    const quantity = document.getElementById('addStockQty').value;
                    const remarks = document.getElementById('addStockRemarks').value;
                    const itemId = addStockId;
                    const token = document.querySelector('input[name=_token]')?.value || document.querySelector('meta[name=csrf-token]')?.getAttribute('content');

                    if(!quantity || parseInt(quantity) <= 0){
                        alert('Please enter a valid quantity');
                        return;
                    }

                    const url = `/employee/items/${itemId}/add-stock`;

                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ quantity: parseInt(quantity), remarks: remarks })
                        });

                        if(res.redirected){
                            window.location = res.url;
                            return;
                        }

                        if(res.ok){
                            window.location.reload();
                            return;
                        }

                        const errData = await res.json().catch(() => ({}));
                        alert(errData.message || 'Failed to add stock');
                    } catch(e) {
                        alert('Request failed: ' + e.message);
                    }
                })()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" id="addStockQty" min="1" required class="w-full border rounded px-3 py-2 text-sm" placeholder="e.g., 10" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (optional)</label>
                        <textarea id="addStockRemarks" class="w-full border rounded px-3 py-2 text-sm" rows="2" placeholder="e.g., Stock received"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button type="button" @click="addStockId = null" class="px-4 py-2 border rounded-lg shadow-sm hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mt-6">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800">📋 Stock Transaction History</h3>
            <span class="text-sm text-gray-500">Last 100 transactions</span>
        </div>

        
        <div class="px-4 py-3 border-b bg-gray-50">
            <form method="GET" action="<?php echo e(route('employee.items')); ?>" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
                    <input type="text" name="employee_name" value="<?php echo e($filters['employee_name'] ?? ''); ?>" placeholder="Employee name..." class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    <select name="type" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]">
                        <option value="">All Types</option>
                        <option value="in" <?php echo e(($filters['type'] ?? '') === 'in' ? 'selected' : ''); ?>>Stock In</option>
                        <option value="out" <?php echo e(($filters['type'] ?? '') === 'out' ? 'selected' : ''); ?>>Stock Out</option>
                    </select>
                    <input type="date" name="date_from" value="<?php echo e($filters['date_from'] ?? ''); ?>" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    <input type="date" name="date_to" value="<?php echo e($filters['date_to'] ?? ''); ?>" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                    <input type="text" name="remarks" value="<?php echo e($filters['remarks'] ?? ''); ?>" placeholder="Remarks/Notes..." class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c49b6e]" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-[#c49b6e] text-white text-sm px-4 py-2 rounded hover:bg-[#b08a5c]">Search</button>
                    <?php if(($filters['employee_name'] ?? false) || ($filters['type'] ?? false) || ($filters['date_from'] ?? false) || ($filters['date_to'] ?? false) || ($filters['remarks'] ?? false)): ?>
                        <a href="<?php echo e(route('employee.items')); ?>" class="bg-gray-400 text-white text-sm px-4 py-2 rounded hover:bg-gray-500">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        
        <?php if(($filters['employee_name'] ?? false) || ($filters['type'] ?? false) || ($filters['date_from'] ?? false) || ($filters['date_to'] ?? false) || ($filters['remarks'] ?? false)): ?>
            <div class="px-4 py-2 border-b bg-blue-50 flex flex-wrap gap-2">
                <?php if($filters['employee_name'] ?? false): ?>
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">Employee: <?php echo e($filters['employee_name']); ?></span>
                <?php endif; ?>
                <?php if($filters['type'] ?? false): ?>
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">Type: <?php echo e($filters['type'] === 'in' ? 'Stock In' : 'Stock Out'); ?></span>
                <?php endif; ?>
                <?php if($filters['date_from'] ?? false): ?>
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">From: <?php echo e($filters['date_from']); ?></span>
                <?php endif; ?>
                <?php if($filters['date_to'] ?? false): ?>
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">To: <?php echo e($filters['date_to']); ?></span>
                <?php endif; ?>
                <?php if($filters['remarks'] ?? false): ?>
                    <span class="bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full">Remarks: <?php echo e($filters['remarks']); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($recentTransactions->count() > 0): ?>
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
                        <?php $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trans): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 border-b">
                                <td class="px-4 py-3 text-sm text-gray-700"><?php echo e($trans->created_at->format('M d, Y H:i')); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium"><?php echo e($trans->user?->name ?? 'N/A'); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($trans->item?->name ?? 'N/A'); ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <?php if($trans->type === 'in'): ?>
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700 font-semibold">📥 Stock In</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700 font-semibold">📤 Stock Out</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium"><?php echo e($trans->quantity); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate" title="<?php echo e($trans->remarks); ?>"><?php echo e($trans->remarks ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-6 text-center text-gray-500">
                <p>No transactions found.</p>
            </div>
        <?php endif; ?>
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
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Price</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php $__empty_1 = true; $__currentLoopData = $hiddenItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 border-b">
                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($h->name); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700"><?php echo e($h->category); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700">₱<?php echo e(number_format($h->price, 2)); ?></td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-gray-400 text-white rounded">Hidden</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <form action="<?php echo e(route('employee.items.toggle', $h)); ?>" method="POST" class="inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button class="text-blue-600 hover:text-blue-700 underline">Unhide</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No hidden items</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            <?php echo e($hiddenItems->links()); ?>

        </div>
    </div>

    
    <div x-show="openAdd" x-cloak x-transition class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div @click.outside="openAdd = false" class="bg-white p-6 rounded-lg w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-semibold mb-4">Add New Item</h2>
            <form action="<?php echo e(route('employee.items.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <input type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="Item name" class="w-full border rounded px-3 py-2" required>
                        <?php if($errors->getBag('createItem')->has('name')): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo e($errors->getBag('createItem')->first('name')); ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <select name="category" class="w-full border rounded px-3 py-2" required>
                            <option value="" disabled <?php echo e(old('category') ? '' : 'selected'); ?>>Select category</option>
                            <option value="Caddy" <?php echo e(old('category') === 'Caddy' ? 'selected' : ''); ?>>Caddy</option>
                            <option value="Carpet" <?php echo e(old('category') === 'Carpet' ? 'selected' : ''); ?>>Carpet</option>
                            <option value="Placemat" <?php echo e(old('category') === 'Placemat' ? 'selected' : ''); ?>>Placemat</option>
                            <option value="Others" <?php echo e(old('category') === 'Others' ? 'selected' : ''); ?>>Others</option>
                        </select>
                        <?php if($errors->getBag('createItem')->has('category')): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo e($errors->getBag('createItem')->first('category')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <input type="number" step="0.01" name="price" value="<?php echo e(old('price')); ?>" placeholder="Price" class="w-full border rounded px-3 py-2" min="0" required>
                        <?php if($errors->getBag('createItem')->has('price')): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo e($errors->getBag('createItem')->first('price')); ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <input type="text" name="description" value="<?php echo e(old('description')); ?>" placeholder="Description (optional)" class="w-full border rounded px-3 py-2">
                        <?php if($errors->getBag('createItem')->has('description')): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo e($errors->getBag('createItem')->first('description')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="openAdd = false" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Create Item</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.employee', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\wowc\resources\views/employee/items-db.blade.php ENDPATH**/ ?>