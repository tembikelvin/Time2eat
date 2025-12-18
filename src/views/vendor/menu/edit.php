<?php
$title = $title ?? 'Edit Menu Item - Time2Eat';
$currentPage = $currentPage ?? 'menu';
$user = $user ?? null;
$item = $item ?? null;
$categories = $categories ?? [];
$restaurant = $restaurant ?? null;
$errors = $errors ?? [];
$isEdit = !empty($item);
$pageTitle = $isEdit ? 'Edit Menu Item' : 'Add Menu Item';
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $pageTitle ?></h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                <?= e($restaurant['name'] ?? 'Your Restaurant') ?>
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <a href="<?= url('/vendor/menu') ?>" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                <i data-feather="arrow-left" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Back to Menu
            </a>
        </div>
    </div>
</div>

<!-- Menu Item Form -->
        <form method="POST" action="<?= url('/vendor/menu/' . ($item['id'] ?? '')) ?>" enctype="multipart/form-data" class="tw-space-y-8">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>
            
            <!-- Basic Information -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-6">Basic Information</h2>
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                    <div class="md:tw-col-span-2">
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Item Name *</label>
                        <input type="text" name="name" value="<?= e($item['name'] ?? '') ?>" required class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500 <?= isset($errors['name']) ? 'tw-border-red-500' : '' ?>">
                        <?php if (isset($errors['name'])): ?>
                            <p class="tw-text-red-500 tw-text-sm tw-mt-1"><?= e($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Category *</label>
                        <select name="category_id" required class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500 <?= isset($errors['category_id']) ? 'tw-border-red-500' : '' ?>">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= ($item['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                    <?= e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category_id'])): ?>
                            <p class="tw-text-red-500 tw-text-sm tw-mt-1"><?= e($errors['category_id']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Price (XAF) *</label>
                        <input type="number" name="price" value="<?= e($item['price'] ?? '') ?>" min="0" step="1" required class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500 <?= isset($errors['price']) ? 'tw-border-red-500' : '' ?>">
                        <?php if (isset($errors['price'])): ?>
                            <p class="tw-text-red-500 tw-text-sm tw-mt-1"><?= e($errors['price']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="md:tw-col-span-2">
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Description *</label>
                        <textarea name="description" rows="4" required class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500 <?= isset($errors['description']) ? 'tw-border-red-500' : '' ?>"><?= e($item['description'] ?? '') ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <p class="tw-text-red-500 tw-text-sm tw-mt-1"><?= e($errors['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-6">Item Image</h2>
                
                <div class="tw-space-y-4">
                    <?php
                    $itemImage = $item['image'] ?? $item['image_url'] ?? null;
                    if ($isEdit && !empty($itemImage)):
                        $imageUrl = imageUrl($itemImage);
                    ?>
                        <div class="tw-flex tw-items-center tw-space-x-4">
                            <img src="<?= e($imageUrl) ?>" alt="Current image" class="tw-w-20 tw-h-20 tw-rounded-lg tw-object-cover">
                            <div>
                                <p class="tw-text-sm tw-text-gray-600">Current image</p>
                                <label class="tw-inline-flex tw-items-center tw-mt-2">
                                    <input type="checkbox" name="remove_image" value="1" class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-300 tw-focus:tw-ring tw-focus:tw-ring-orange-200 tw-focus:tw-ring-opacity-50">
                                    <span class="tw-ml-2 tw-text-sm tw-text-gray-600">Remove current image</span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                            <?= $isEdit ? 'Replace Image' : 'Upload Image' ?>
                        </label>
                        <div class="tw-mt-1 tw-flex tw-justify-center tw-px-6 tw-pt-5 tw-pb-6 tw-border-2 tw-border-gray-300 tw-border-dashed tw-rounded-lg">
                            <div class="tw-space-y-1 tw-text-center">
                                <i data-feather="upload" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400"></i>
                                <div class="tw-flex tw-text-sm tw-text-gray-600">
                                    <label for="image" class="tw-relative tw-cursor-pointer tw-bg-white tw-rounded-md tw-font-medium tw-text-orange-600 tw-hover:tw-text-orange-500 tw-focus-within:tw-outline-none tw-focus-within:tw-ring-2 tw-focus-within:tw-ring-offset-2 tw-focus-within:tw-ring-orange-500">
                                        <span>Upload a file</span>
                                        <input id="image" name="image" type="file" accept="image/*" class="tw-sr-only" onchange="previewImage(this)">
                                    </label>
                                    <p class="tw-pl-1">or drag and drop</p>
                                </div>
                                <p class="tw-text-xs tw-text-gray-500">PNG, JPG, WEBP up to 5MB</p>
                            </div>
                        </div>
                        <div id="imagePreview" class="tw-mt-4 tw-hidden">
                            <img id="previewImg" class="tw-w-32 tw-h-32 tw-rounded-lg tw-object-cover">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-6">Additional Details</h2>
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Preparation Time (minutes)</label>
                        <input type="number" name="preparation_time" value="<?= e($item['preparation_time'] ?? '15') ?>" min="1" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500">
                    </div>

                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Calories (optional)</label>
                        <input type="number" name="calories" value="<?= e($item['calories'] ?? '') ?>" min="0" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500">
                    </div>

                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Stock Quantity</label>
                        <input type="number" name="stock_quantity" value="<?= e($item['stock_quantity'] ?? '0') ?>" min="0" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500">
                    </div>

                    <div class="md:tw-col-span-2">
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Ingredients (optional)</label>
                        <textarea name="ingredients" rows="3" placeholder="List main ingredients..." class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500"><?= e($item['ingredients'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Min Stock Level</label>
                        <input type="number" name="min_stock_level" value="<?= e($item['min_stock_level'] ?? '5') ?>" min="0" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500">
                        <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Alert when stock falls below this level</p>
                    </div>

                    <div class="md:tw-col-span-3">
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Allergens (optional)</label>
                        <input type="text" name="allergens" value="<?= e($item['allergens'] ?? '') ?>" placeholder="e.g., Nuts, Dairy, Gluten" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500">
                    </div>
                </div>
            </div>

            <!-- Dietary Options -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-6">Dietary Options</h2>
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
                    <label class="tw-flex tw-items-center">
                        <input type="checkbox" name="is_vegetarian" value="1" <?= ($item['is_vegetarian'] ?? 0) ? 'checked' : '' ?> class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-300 tw-focus:tw-ring tw-focus:tw-ring-orange-200 tw-focus:tw-ring-opacity-50">
                        <span class="tw-ml-2 tw-text-sm tw-text-gray-700">Vegetarian</span>
                    </label>

                    <label class="tw-flex tw-items-center">
                        <input type="checkbox" name="is_vegan" value="1" <?= ($item['is_vegan'] ?? 0) ? 'checked' : '' ?> class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-300 tw-focus:tw-ring tw-focus:tw-ring-orange-200 tw-focus:tw-ring-opacity-50">
                        <span class="tw-ml-2 tw-text-sm tw-text-gray-700">Vegan</span>
                    </label>

                    <label class="tw-flex tw-items-center">
                        <input type="checkbox" name="is_gluten_free" value="1" <?= ($item['is_gluten_free'] ?? 0) ? 'checked' : '' ?> class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-300 tw-focus:tw-ring tw-orange-200 tw-focus:tw-ring-opacity-50">
                        <span class="tw-ml-2 tw-text-sm tw-text-gray-700">Gluten Free</span>
                    </label>
                </div>
            </div>

            <!-- Customization Options -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-6">Customization Options</h2>
                
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Available Customizations</label>
                    <textarea name="customization_options" rows="3" placeholder="e.g., Extra cheese (+500 XAF), Spicy level, No onions" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500"><?= e($item['customization_options'] ?? '') ?></textarea>
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Enter each option on a new line. Use format: "Option name (+price XAF)" for paid options</p>
                </div>
            </div>

            <!-- Availability -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-6">Availability</h2>
                
                <label class="tw-flex tw-items-center">
                    <input type="checkbox" name="is_available" value="1" <?= ($item['is_available'] ?? 1) ? 'checked' : '' ?> class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-shadow-sm tw-focus:tw-border-orange-300 tw-focus:tw-ring tw-focus:tw-ring-orange-200 tw-focus:tw-ring-opacity-50">
                    <span class="tw-ml-2 tw-text-sm tw-text-gray-700">Item is available for ordering</span>
                </label>
            </div>

            <!-- Submit Buttons -->
            <div class="tw-flex tw-justify-end tw-space-x-4">
                <a href="<?= url('/vendor/menu') ?>" class="tw-px-6 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white tw-hover:tw-bg-gray-50 tw-transition-colors">
                    Cancel
                </a>
                <button type="submit" class="tw-px-6 tw-py-3 tw-bg-orange-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium tw-hover:tw-bg-orange-600 tw-transition-colors">
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

// Auto-resize textareas
document.querySelectorAll('textarea').forEach(textarea => {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});

// Handle form submission via AJAX
document.addEventListener('DOMContentLoaded', function() {
    const menuForm = document.querySelector('form[action*="/vendor/menu/"]');
    if (!menuForm) {
        console.error('Menu form not found!');
        return;
    }
    
    menuForm.addEventListener('submit', async function(e) {
        e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="tw-inline-block tw-animate-spin tw-mr-2">⏳</span> Updating...';
    
    // Clear previous error messages
    document.querySelectorAll('.tw-text-red-500').forEach(el => el.remove());
    document.querySelectorAll('.tw-bg-red-100').forEach(el => el.remove());
    document.querySelectorAll('.tw-bg-emerald-100').forEach(el => el.remove());
    
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
            successMsg.textContent = data.message || 'Menu item updated successfully!';
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

            // Show general error message
            const errorMsg = document.createElement('div');
            errorMsg.className = 'tw-bg-red-100 tw-text-red-800 tw-rounded-lg tw-p-4 tw-mb-6';
            errorMsg.innerHTML = `<div class="tw-font-bold">${data.message || 'Failed to update menu item'}</div>`;
            form.insertBefore(errorMsg, form.firstChild);

            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            // Scroll to top to show error
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } catch (error) {
        console.error('Menu item update error:', error);
        
        // Show error message
        const errorMsg = document.createElement('div');
        errorMsg.className = 'tw-bg-red-100 tw-text-red-800 tw-rounded-lg tw-p-4 tw-mb-6 tw-text-center tw-font-medium';
        errorMsg.textContent = 'An error occurred while updating the menu item. Please try again.';
        form.insertBefore(errorMsg, form.firstChild);
        
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
        
        // Scroll to top to show error
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    });
});
</script>
