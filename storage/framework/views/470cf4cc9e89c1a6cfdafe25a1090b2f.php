<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOW Carmen - Custom Order</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen: false, mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">

    <?php echo $__env->make('partials.customer-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Content -->
    <section class="pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden border border-gray-100 rounded-xl shadow-sm">
                <div class="p-6 md:p-8 text-gray-900">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Custom Order Request</h1>
                    <form id="customOrderForm" method="POST" action="<?php echo e(route('custom-orders.store')); ?>" enctype="multipart/form-data" class="space-y-6">
                        <?php echo csrf_field(); ?>

                        <div>
                            <label for="custom_name" class="block text-sm font-medium text-gray-700 mb-2">Product Name/Title</label>
                            <input id="custom_name" name="custom_name" type="text" class="mt-1 block w-full rounded-md border-2 border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f] focus:outline-none" value="<?php echo e(old('custom_name')); ?>" required autofocus placeholder="Enter product name or title" />
                            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('custom_name'),'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('custom_name')),'class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                            <p id="custom_name_error" class="mt-2 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description/Special Instructions</label>
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-2 border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f] focus:outline-none" required placeholder="Describe your custom order and any special instructions..."><?php echo e(old('description')); ?></textarea>
                            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('description'),'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('description')),'class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                            <p id="description_error" class="mt-2 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full rounded-md border-2 border-gray-300 px-3 py-2 focus:border-[#c59d5f] focus:ring-[#c59d5f] focus:outline-none" value="<?php echo e(old('quantity', 1)); ?>" required />
                            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('quantity'),'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('quantity')),'class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                            <p id="quantity_error" class="mt-2 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="reference_images" class="block text-sm font-medium text-gray-700 mb-2">Reference Images</label>
                            <input type="file" id="reference_images" name="reference_images[]" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border-2 border-gray-300 rounded-md px-3 py-2" accept=".jpg,.jpeg,.png" />
                            <p class="mt-2 text-sm text-gray-600">
                                <strong>Please take pictures from different angles.</strong> You can upload up to 4 images (JPG/PNG, max 5MB each).
                            </p>
                            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('reference_images.*'),'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('reference_images.*')),'class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                            <p id="reference_images_error" class="mt-2 text-sm text-red-600"></p>
                        </div>

                        <div id="imagePreview" class="mt-4"></div>

                        <div class="mt-6 flex items-center justify-end gap-x-6">
                            <a href="<?php echo e(route('products.index')); ?>" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                            <button type="submit" class="rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" style="background:#c59d5f;">
                                Submit Custom Order
                            </button>
                        </div>
                        <script>
                        (function(){
                            const form = document.getElementById('customOrderForm');
                            const refInput = document.getElementById('reference_images');
                            const preview = document.getElementById('imagePreview');
                            const MAX_MB = 5;
                            const MAX_BYTES = MAX_MB * 1024 * 1024;
                            const MAX_IMAGES = 4;
                            const ALLOWED = ['image/jpeg','image/png','image/jpg'];

                            function setError(id, message){
                                const el = document.getElementById(id);
                                if(el){ el.textContent = message || ''; }
                            }

                            refInput.addEventListener('change', function(event) {
                                const input = event.target;
                                preview.innerHTML = '';
                                setError('reference_images_error', '');
                                
                                if (!input.files || input.files.length === 0) {
                                    return;
                                }

                                if (input.files.length > MAX_IMAGES) {
                                    setError('reference_images_error', 'You can only upload up to ' + MAX_IMAGES + ' images.');
                                    input.value = '';
                                    return;
                                }

                                const files = Array.from(input.files);
                                let hasError = false;

                                files.forEach((file, index) => {
                                    if(!ALLOWED.includes(file.type)){
                                        setError('reference_images_error', 'All files must be JPG or PNG images.');
                                        hasError = true;
                                        return;
                                    }
                                    if(file.size > MAX_BYTES){
                                        setError('reference_images_error', 'Each image must be less than ' + MAX_MB + 'MB.');
                                        hasError = true;
                                        return;
                                    }

                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        const container = document.createElement('div');
                                        container.className = 'inline-block mr-4 mb-4';
                                        
                                        const img = document.createElement('img');
                                        img.src = e.target.result;
                                        img.className = 'max-h-40 rounded shadow border border-gray-200';
                                        
                                        container.appendChild(img);
                                        preview.appendChild(container);
                                    };
                                    reader.readAsDataURL(file);
                                });

                                if(hasError){
                                    input.value = '';
                                }
                            });

                            form.addEventListener('submit', function(e){
                                let hasError = false;
                                setError('custom_name_error','');
                                setError('description_error','');
                                setError('quantity_error','');
                                setError('reference_images_error','');

                                const name = document.getElementById('custom_name').value.trim();
                                const desc = document.getElementById('description').value.trim();
                                const qty = parseInt(document.getElementById('quantity').value, 10);
                                const files = refInput.files;

                                if(!name){ setError('custom_name_error','Product name is required.'); hasError = true; }
                                if(!desc){ setError('description_error','Description is required.'); hasError = true; }
                                if(!qty || qty < 1){ setError('quantity_error','Quantity must be at least 1.'); hasError = true; }
                                
                                if(!files || files.length === 0){
                                    setError('reference_images_error','At least one reference image is required.');
                                    hasError = true;
                                } else {
                                    if(files.length > MAX_IMAGES){
                                        setError('reference_images_error','You can only upload up to ' + MAX_IMAGES + ' images.');
                                        hasError = true;
                                    } else {
                                        const fileArray = Array.from(files);
                                        for(let i = 0; i < fileArray.length; i++){
                                            const file = fileArray[i];
                                            if(!ALLOWED.includes(file.type)){
                                                setError('reference_images_error','All files must be JPG or PNG images.');
                                                hasError = true;
                                                break;
                                            }
                                            if(file.size > MAX_BYTES){
                                                setError('reference_images_error','Each image must be less than ' + MAX_MB + 'MB.');
                                                hasError = true;
                                                break;
                                            }
                                        }
                                    }
                                }

                                if(hasError){ e.preventDefault(); }
                            });
                        })();
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php echo $__env->make('partials.customer-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html><?php /**PATH C:\xampp\htdocs\wowc\resources\views/custom-order/create.blade.php ENDPATH**/ ?>