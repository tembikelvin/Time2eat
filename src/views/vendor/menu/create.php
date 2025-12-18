<?php
$title = $title ?? 'Add Menu Item - Time2Eat';
$currentPage = $currentPage ?? 'menu';
$user = $user ?? null;
$item = $item ?? null;
$categories = $categories ?? [];
$restaurant = $restaurant ?? null;
$errors = $errors ?? [];
$isEdit = !empty($item);
$pageTitle = $isEdit ? 'Edit Menu Item' : 'Add Menu Item';
?>

<!-- Banner/Header -->
<div class="tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-xl tw-p-6 tw-mb-8 tw-flex tw-items-center tw-space-x-4 tw-shadow-md">
    <div class="tw-bg-white tw-rounded-full tw-p-4 tw-shadow">
        <i data-feather="plus-square" class="tw-text-orange-500 tw-h-7 tw-w-7"></i>
    </div>
    <div class="tw-flex-1">
        <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-white tw-mb-0.5">
            <?= $pageTitle ?>
        </h1>
        <p class="tw-text-orange-50 tw-text-base">
            <?= e($restaurant['name'] ?? 'Your Restaurant') ?> &ndash; Easily add a new item to your menu!
        </p>
    </div>
    <a href="<?= url('/vendor/menu') ?>" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-orange-400 tw-text-white hover:tw-bg-orange-700 tw-transition-colors">
        <i data-feather="arrow-left" class="tw-h-5 tw-w-5 tw-mr-2"></i> Back to Menu
    </a>
</div>

<!-- Step Indicator -->
<div class="tw-flex tw-items-center tw-justify-center tw-mb-8">
    <div class="tw-flex tw-items-center tw-gap-0.5 md:tw-gap-2">
        <span class="tw-flex tw-items-center tw-justify-center tw-h-7 tw-w-7 tw-bg-orange-500 tw-text-white tw-rounded-full tw-font-bold">1</span>
        <span class="tw-ml-2 tw-font-medium tw-text-orange-600">Basic Info</span>
        <span class="tw-mx-2 tw-text-gray-400">&rsaquo;</span>
        <span class="tw-flex tw-items-center tw-justify-center tw-h-7 tw-w-7 tw-bg-white tw-text-gray-400 tw-rounded-full tw-font-bold tw-border tw-border-gray-200">2</span>
        <span class="tw-ml-2 tw-font-medium tw-text-gray-400">Image</span>
        <span class="tw-mx-2 tw-text-gray-400">&rsaquo;</span>
        <span class="tw-flex tw-items-center tw-justify-center tw-h-7 tw-w-7 tw-bg-white tw-text-gray-400 tw-rounded-full tw-font-bold tw-border tw-border-gray-200">3</span>
        <span class="tw-ml-2 tw-font-medium tw-text-gray-400">Details</span>
        <span class="tw-mx-2 tw-text-gray-400">&rsaquo;</span>
        <span class="tw-flex tw-items-center tw-justify-center tw-h-7 tw-w-7 tw-bg-white tw-text-gray-400 tw-rounded-full tw-font-bold tw-border tw-border-gray-200">4</span>
        <span class="tw-ml-2 tw-font-medium tw-text-gray-400">Finish</span>
    </div>
</div>

<?php if (!empty($_SESSION['menu_success'])): ?>
<div class="tw-bg-emerald-100 tw-text-emerald-800 tw-rounded-lg tw-p-4 tw-mb-6 tw-text-center tw-font-medium">
    <?= e($_SESSION['menu_success']) ?>
    <?php unset($_SESSION['menu_success']); ?>
</div>
<?php endif; ?>

<form id="menuItemForm" method="POST" enctype="multipart/form-data" action="<?= url('/vendor/menu') ?>" class="tw-max-w-3xl tw-mx-auto tw-space-y-10">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <!-- Basic Information -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow tw-p-8 tw-mb-5">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Basic Information</h2>
        <p class="tw-text-sm tw-text-gray-500 tw-mb-6">Start with the essentials. Fields marked <span class='tw-text-orange-500'>*</span> must be filled.</p>
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-8">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Item Name <span class='tw-text-orange-500'>*</span></label>
                <input type="text" name="name" value="<?= e($item['name'] ?? '') ?>" required placeholder="e.g., Special Beef Burger" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-border-orange-500 tw-text-base tw-bg-orange-50 <?= isset($errors['name']) ? 'tw-border-red-500' : '' ?>">
                <?php if (isset($errors['name'])): ?><p class="tw-text-red-500 tw-text-xs tw-mt-1"><?= e($errors['name']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Category <span class='tw-text-orange-500'>*</span></label>
                <select name="category_id" required class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-orange-50 tw-focus:tw-border-orange-500 <?= isset($errors['category_id']) ? 'tw-border-red-500' : '' ?>">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= ($item['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?><p class="tw-text-red-500 tw-text-xs tw-mt-1"><?= e($errors['category_id']) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-8 tw-mt-6">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Price (XAF) <span class='tw-text-orange-500'>*</span></label>
                <input type="number" name="price" value="<?= e($item['price'] ?? '') ?>" min="0" step="1" required placeholder="e.g., 2500" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-border-orange-500 tw-bg-orange-50 <?= isset($errors['price']) ? 'tw-border-red-500' : '' ?>">
                <p class="tw-text-xs tw-text-gray-400">Enter a whole number (e.g. 2500)</p>
                <?php if (isset($errors['price'])): ?><p class="tw-text-red-500 tw-text-xs tw-mt-1"><?= e($errors['price']) ?></p><?php endif; ?>
            </div>
            <div class="md:tw-col-span-2">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Description <span class='tw-text-orange-500'>*</span></label>
                <textarea name="description" rows="3" required placeholder="Describe this dish..." class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-border-orange-500 tw-bg-orange-50 <?= isset($errors['description']) ? 'tw-border-red-500' : '' ?>"><?= e($item['description'] ?? '') ?></textarea>
                <p class="tw-text-xs tw-text-gray-400">What makes it special? Mention main features or flavor.</p>
                <?php if (isset($errors['description'])): ?><p class="tw-text-red-500 tw-text-xs tw-mt-1"><?= e($errors['description']) ?></p><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upload Image -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow tw-p-8 tw-mb-5">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Image</h2>
        <p class="tw-text-sm tw-text-gray-500 tw-mb-6">A great photo attracts orders! PNG, JPG, or WEBP, up to 5MB. Square shape works best.</p>

        <div class="tw-border-2 tw-border-dashed tw-border-orange-300 tw-rounded-xl tw-p-8 tw-bg-orange-50 tw-text-center tw-relative tw-flex tw-flex-col tw-items-center">
            <?php if ($isEdit && (!empty($item['image']) || !empty($item['image_url']))): ?>
                <img src="<?= e($item['image'] ?? $item['image_url'] ?? '') ?>" alt="Current image" class="tw-w-32 tw-h-32 tw-rounded-xl tw-object-cover tw-mx-auto tw-mb-2">
                <label class="tw-inline-flex tw-items-center tw-mt-2">
                    <input type="checkbox" name="remove_image" value="1" class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-300 tw-focus:tw-ring tw-focus:tw-ring-orange-200 tw-focus:tw-ring-opacity-50">
                    <span class="tw-ml-2 tw-text-sm tw-text-gray-600">Remove current image</span>
                </label>
            <?php endif; ?>
            <div class="tw-space-y-2 tw-mt-3 tw-w-full">
                <label for="image" class="tw-block tw-bg-orange-500 tw-text-white tw-px-5 tw-py-3 tw-rounded-lg tw-cursor-pointer tw-font-semibold hover:tw-bg-orange-600 tw-transition tw-w-full sm:tw-w-auto tw-mx-auto">
                    <span><i data-feather="upload" class="tw-h-5 tw-w-5 tw-mr-2 tw-inline"></i>Upload a file</span>
                    <input id="image" name="image" type="file" accept="image/*" class="tw-sr-only" onchange="previewImage(this)">
                </label>
                <div id="imagePreview" class="tw-mt-4 tw-hidden">
                    <img id="previewImg" class="tw-w-32 tw-h-32 tw-rounded-xl tw-object-cover tw-mx-auto">
                </div>
            </div>
            <span class="tw-text-xs tw-text-gray-400 tw-block tw-mt-2">You can also drag and drop an image here.</span>
        </div>
    </div>

    <!-- Details & Advanced -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow tw-p-8 tw-mb-5">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Additional Details</h2>
        <p class="tw-text-sm tw-text-gray-500 tw-mb-6">Give more info about the item for optimal customer experience.</p>
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-8">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Preparation Time (minutes)<span class="tw-text-gray-300"> (est.)</span></label>
                <input type="number" name="preparation_time" value="<?= e($item['preparation_time'] ?? '15') ?>" min="1" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-orange-50 tw-focus:tw-border-orange-500">
            </div>
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Calories <span class="tw-text-gray-400">(optional)</span></label>
                <input type="number" name="calories" value="<?= e($item['calories'] ?? '') ?>" min="0" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-orange-50 tw-focus:tw-border-orange-500">
            </div>
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Stock Quantity</label>
                <input type="number" name="stock_quantity" value="<?= e($item['stock_quantity'] ?? '0') ?>" min="0" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-orange-50 tw-focus:tw-border-orange-500">
                <p class="tw-text-xs tw-text-gray-400">How many are available now?</p>
            </div>
            <div class="md:tw-col-span-2">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Ingredients <span class="tw-text-gray-400">(optional)</span></label>
                <textarea name="ingredients" rows="2" placeholder="List main ingredients..." class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-orange-50 tw-focus:tw-border-orange-500"><?= e($item['ingredients'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Min Stock Level</label>
                <input type="number" name="min_stock_level" value="<?= e($item['min_stock_level'] ?? '5') ?>" min="0" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-orange-50 tw-focus:tw-border-orange-500">
                <p class="tw-text-xs tw-text-gray-400">Alert when dropping below this</p>
            </div>
            <div class="md:tw-col-span-3">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Allergens <span class="tw-text-gray-400">(optional)</span></label>
                <input type="text" name="allergens" value="<?= e($item['allergens'] ?? '') ?>" placeholder="e.g., Nuts, Dairy, Gluten" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-orange-50 tw-focus:tw-border-orange-500">
            </div>
        </div>
    </div>

    <!-- Dietary & Customization -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow tw-p-8">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Special Options</h2>
        <p class="tw-text-sm tw-text-gray-500 tw-mb-6">Tag for special diets and editable options for customers.</p>
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-6 tw-mb-6">
            <div class="tw-flex tw-items-start tw-space-x-2">
                <input type="checkbox" name="is_vegetarian" value="1" <?= ($item['is_vegetarian'] ?? 0) ? 'checked' : '' ?> class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-400 tw-focus:tw-ring-2 tw-focus:tw-ring-orange-200">
                <label class="tw-text-sm tw-text-gray-700">Vegetarian</label>
            </div>
            <div class="tw-flex tw-items-start tw-space-x-2">
                <input type="checkbox" name="is_vegan" value="1" <?= ($item['is_vegan'] ?? 0) ? 'checked' : '' ?> class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-400 tw-focus:tw-ring-2 tw-focus:tw-ring-orange-200">
                <label class="tw-text-sm tw-text-gray-700">Vegan</label>
            </div>
            <div class="tw-flex tw-items-start tw-space-x-2">
                <input type="checkbox" name="is_gluten_free" value="1" <?= ($item['is_gluten_free'] ?? 0) ? 'checked' : '' ?> class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-400 tw-focus:tw-ring-2 tw-focus:tw-ring-orange-200">
                <label class="tw-text-sm tw-text-gray-700">Gluten Free</label>
            </div>
        </div>
        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Customization Options</label>
            <textarea name="customization_options" rows="2" placeholder="e.g., Extra cheese (+500 XAF), Spicy level, No onions" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-orange-50 tw-focus:tw-border-orange-500"><?= e($item['customization_options'] ?? '') ?></textarea>
            <p class="tw-text-xs tw-text-gray-400">Enter each option on a new line. Use format: "Option name (+price XAF)" for paid options</p>
        </div>
        <div class="tw-mt-6">
            <label class="tw-flex tw-items-center">
                <input type="checkbox" name="is_available" value="1" <?= ($item['is_available'] ?? 1) ? 'checked' : '' ?> class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-300 tw-focus:tw-ring tw-focus:tw-ring-orange-200">
                <span class="tw-ml-2 tw-text-sm tw-text-gray-700">Item is available for ordering</span>
            </label>
        </div>
    </div>

    <!-- Action Bar: Save/Cancel -->
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-end tw-gap-4 tw-py-6">
        <a href="<?= url('/vendor/menu') ?>" class="tw-px-8 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-base tw-font-semibold tw-text-gray-700 tw-bg-white hover:tw-bg-orange-50 tw-transition-all">
            Cancel
        </a>
        <button type="submit" class="tw-px-8 tw-py-3 tw-bg-orange-500 tw-text-white tw-rounded-lg tw-font-semibold tw-text-base tw-shadow-sm hover:tw-bg-orange-600 tw-transition-all">
            <?= $isEdit ? 'Update Item' : 'Create Item' ?>
        </button>
    </div>
</form>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const img = document.getElementById('previewImg');
            img.src = e.target.result;
            preview.classList.remove('tw-hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
document.querySelectorAll('textarea').forEach(textarea => {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});

// Handle form submission via AJAX
document.getElementById('menuItemForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="tw-inline-block tw-animate-spin tw-mr-2">⏳</span> Processing...';
    
    // Clear previous error messages
    document.querySelectorAll('.tw-text-red-500').forEach(el => el.remove());
    
    try {
        // Create FormData from form
        const formData = new FormData(form);
        
        // Submit via AJAX
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response received:', text);
            throw new Error('Server returned non-JSON response. Status: ' + response.status);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'tw-bg-emerald-100 tw-text-emerald-800 tw-rounded-lg tw-p-4 tw-mb-6 tw-text-center tw-font-medium';
            successMsg.textContent = data.message || 'Menu item created successfully!';
            form.insertBefore(successMsg, form.firstChild);
            
            // Redirect after short delay
            setTimeout(() => {
                window.location.href = data.redirect || '<?= url('/vendor/menu') ?>';
            }, 1500);
        } else {
            // Show error messages
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        const errorMsg = document.createElement('p');
                        errorMsg.className = 'tw-text-red-500 tw-text-xs tw-mt-1';
                        errorMsg.textContent = data.errors[field];
                        input.parentElement.appendChild(errorMsg);
                        input.classList.add('tw-border-red-500');
                    }
                });
            }

            // Show general error message with detailed information
            const errorMsg = document.createElement('div');
            errorMsg.className = 'tw-bg-red-100 tw-text-red-800 tw-rounded-lg tw-p-4 tw-mb-6';

            let errorHtml = `<div class="tw-font-bold tw-mb-2">${data.message || 'Failed to create menu item'}</div>`;

            // Add detailed error info if available
            if (data.file) {
                errorHtml += `<div class="tw-text-sm tw-mt-2"><strong>File:</strong> ${data.file}</div>`;
            }
            if (data.line) {
                errorHtml += `<div class="tw-text-sm"><strong>Line:</strong> ${data.line}</div>`;
            }
            if (data.error && data.error !== data.message) {
                errorHtml += `<div class="tw-text-sm tw-mt-2"><strong>Details:</strong> ${data.error}</div>`;
            }
            if (data.trace && Array.isArray(data.trace)) {
                errorHtml += `<details class="tw-mt-2 tw-text-xs"><summary class="tw-cursor-pointer tw-font-semibold">Stack Trace</summary><pre class="tw-mt-2 tw-bg-red-200 tw-p-2 tw-rounded tw-overflow-auto">${data.trace.join('\n')}</pre></details>`;
            }

            errorMsg.innerHTML = errorHtml;
            form.insertBefore(errorMsg, form.firstChild);

            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            // Scroll to top to show error
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } catch (error) {
        console.error('Menu item creation error:', error);
        
        // Show error message
        const errorMsg = document.createElement('div');
        errorMsg.className = 'tw-bg-red-100 tw-text-red-800 tw-rounded-lg tw-p-4 tw-mb-6 tw-text-center tw-font-medium';
        errorMsg.textContent = 'An error occurred while creating the menu item. Please try again.';
        form.insertBefore(errorMsg, form.firstChild);
        
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
        
        // Scroll to top to show error
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>
