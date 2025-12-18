<?php
$title = $title ?? 'Categories - Time2Eat';
$currentPage = $currentPage ?? 'categories';
$user = $user ?? null;
$categories = $categories ?? [];
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Categories</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Organize your menu items into categories.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <button type="button" onclick="openAddCategoryModal()" 
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Add Category
            </button>
        </div>
    </div>
</div>

<!-- Categories Content -->
        <!-- Categories Grid -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                <div class="tw-bg-white tw-rounded-lg tw-shadow tw-overflow-hidden">
                    <div class="tw-p-6">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900"><?= e($category['name']) ?></h3>
                            <div class="tw-flex tw-space-x-2">
                                <button type="button" onclick="editCategory(<?= $category['id'] ?>)" 
                                        class="tw-text-gray-400 hover:tw-text-gray-600">
                                    <i data-feather="edit-2" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <button type="button" onclick="deleteCategory(<?= $category['id'] ?>)" 
                                        class="tw-text-gray-400 hover:tw-text-red-600">
                                    <i data-feather="trash-2" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </div>
                        </div>
                        
                        <?php if (!empty($category['description'])): ?>
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-4"><?= e($category['description']) ?></p>
                        <?php endif; ?>
                        
                        <div class="tw-flex tw-items-center tw-justify-between tw-text-sm tw-text-gray-500">
                            <span><?= $category['item_count'] ?? 0 ?> items</span>
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium <?= ($category['is_active'] ?? true) ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-gray-100 tw-text-gray-800' ?>">
                                <?= ($category['is_active'] ?? true) ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="tw-col-span-full tw-text-center tw-py-12">
                <i data-feather="folder" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400"></i>
                <h3 class="tw-mt-2 tw-text-sm tw-font-medium tw-text-gray-900">No categories</h3>
                <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Get started by creating your first category.</p>
                <div class="tw-mt-6">
                    <button type="button" onclick="openAddCategoryModal()" 
                            class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-shadow-sm tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                        <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Add Category
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900" id="modalTitle">Add Category</h3>
                <button type="button" onclick="closeCategoryModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <form id="categoryForm">
                <input type="hidden" id="categoryId" name="id">
                
                <div class="tw-mb-4">
                    <label for="categoryName" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Category Name</label>
                    <input type="text" id="categoryName" name="name" required
                           class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500">
                </div>
                
                <div class="tw-mb-4">
                    <label for="categoryDescription" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Description</label>
                    <textarea id="categoryDescription" name="description" rows="3"
                              class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500"
                              placeholder="Optional description..."></textarea>
                </div>
                
                <div class="tw-mb-6">
                    <label class="tw-flex tw-items-center">
                        <input type="checkbox" id="categoryActive" name="is_active" value="1" checked
                               class="tw-rounded tw-border-gray-300 tw-text-orange-600 focus:tw-ring-orange-500">
                        <span class="tw-ml-2 tw-text-sm tw-text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="tw-flex tw-justify-end tw-space-x-3">
                    <button type="button" onclick="closeCategoryModal()" 
                            class="tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Modal functions
function openAddCategoryModal() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryModal').classList.remove('tw-hidden');
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('tw-hidden');
}

function editCategory(categoryId) {
    // Fetch category data and populate modal
    fetch(`/vendor/categories/${categoryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'Edit Category';
                document.getElementById('categoryId').value = data.category.id;
                document.getElementById('categoryName').value = data.category.name;
                document.getElementById('categoryDescription').value = data.category.description || '';
                document.getElementById('categoryActive').checked = data.category.is_active ?? true;
                document.getElementById('categoryModal').classList.remove('tw-hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load category data');
        });
}

function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
        fetch(`/vendor/categories/${categoryId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete category'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the category');
        });
    }
}

// Form submission
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const categoryId = formData.get('id');
    const url = categoryId ? `/vendor/categories/${categoryId}` : '/vendor/categories';
    const method = categoryId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCategoryModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save category'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the category');
    });
});

// Close modal when clicking outside
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCategoryModal();
    }
});
</script>
