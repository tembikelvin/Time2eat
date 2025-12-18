<?php
/**
 * Admin Categories Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'categories';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Category Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage food categories and menu organization
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-flex tw-items-center tw-justify-center">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Export Categories
            </button>
            <button onclick="addCategory()" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-flex tw-items-center tw-justify-center">
                <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Add Category
            </button>
        </div>
    </div>
</div>

<!-- Category Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Categories</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($categoryStats['total_categories'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">Active</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="folder" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Menu Items</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($categoryStats['categories_with_restaurants'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">Across all categories</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="grid" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Most Popular</p>
                <p class="tw-text-xl tw-font-bold tw-text-gray-900">African Cuisine</p>
                <p class="tw-text-sm tw-text-purple-600">456 items</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Empty Categories</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($categoryStats['inactive_categories'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-yellow-600">Need items</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="alert-triangle" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Categories Grid -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Food Categories</h2>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <button onclick="toggleView()" class="tw-p-2 tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="list" class="tw-h-4 tw-w-4" id="view-toggle-icon"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="tw-p-6">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-6" id="categories-grid">
            <?php 
            // Color mapping for categories
            $colors = ['blue', 'green', 'purple', 'orange', 'red', 'pink', 'indigo', 'yellow'];
            
            // Use real categories from database, fallback to empty array
            $displayCategories = $categories ?? [];
            
            foreach ($displayCategories as $index => $category): 
                $color = $colors[$index % count($colors)];
                $itemsCount = $category['restaurant_count'] ?? 0;
            ?>
            <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-xl tw-p-6 tw-hover:tw-shadow-lg tw-transition-shadow tw-duration-200">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div class="tw-p-3 tw-bg-<?= $color ?>-100 tw-rounded-lg">
                        <i data-feather="tag" class="tw-h-6 tw-w-6 tw-text-<?= $color ?>-600"></i>
                    </div>
                    <div class="tw-flex tw-space-x-2">
                        <button onclick="editCategory(<?= $category['id'] ?>)" class="tw-text-blue-600 hover:tw-text-blue-900">
                            <i data-feather="edit" class="tw-h-4 tw-w-4"></i>
                        </button>
                        <button onclick="deleteCategory(<?= $category['id'] ?>)" class="tw-text-red-600 hover:tw-text-red-900">
                            <i data-feather="trash-2" class="tw-h-4 tw-w-4"></i>
                        </button>
                    </div>
                </div>
                
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2"><?= e($category['name']) ?></h3>
                <p class="tw-text-sm tw-text-gray-600 tw-mb-4"><?= e($category['description']) ?></p>
                
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div class="tw-text-sm tw-text-gray-500">
                        <?= number_format($itemsCount) ?> restaurants
                    </div>
                    <button onclick="viewCategoryItems(<?= $category['id'] ?>)" class="tw-text-primary-600 hover:tw-text-primary-700 tw-text-sm tw-font-medium">
                        View Items →
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900" id="modalTitle">Add Category</h3>
                <button onclick="closeModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <form id="categoryForm" class="tw-space-y-4">
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Category Name</label>
                    <input type="text" id="categoryName" class="tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500" required>
                </div>
                
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Description</label>
                    <textarea id="categoryDescription" rows="3" class="tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500"></textarea>
                </div>
                
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Color Theme</label>
                    <select id="categoryColor" class="tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500">
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                        <option value="red">Red</option>
                        <option value="orange">Orange</option>
                        <option value="purple">Purple</option>
                        <option value="pink">Pink</option>
                        <option value="yellow">Yellow</option>
                    </select>
                </div>
                
                <div class="tw-flex tw-justify-end tw-space-x-3 tw-pt-4">
                    <button type="button" onclick="closeModal()" class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-gray-100 tw-rounded-md hover:tw-bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-bg-primary-600 tw-rounded-md hover:tw-bg-primary-700">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentCategoryId = null;

// Category management functions
function addCategory() {
    currentCategoryId = null;
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    document.getElementById('categoryColor').value = 'blue';
    document.getElementById('categoryModal').classList.remove('tw-hidden');
}

function editCategory(categoryId) {
    currentCategoryId = categoryId;
    document.getElementById('modalTitle').textContent = 'Edit Category';
    
    // Fetch category data from the API
    fetch(`/admin/categories/${categoryId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const category = data.data;
            document.getElementById('categoryName').value = category.name || '';
            document.getElementById('categoryDescription').value = category.description || '';
            document.getElementById('categoryColor').value = 'blue'; // Default color
        } else {
            alert('Error loading category: ' + data.message);
            return;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading the category.');
        return;
    });
    
    document.getElementById('categoryModal').classList.remove('tw-hidden');
}

function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
        fetch(`/admin/categories/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting category: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the category.');
        });
    }
}

function viewCategoryItems(categoryId) {
    window.location.href = `/admin/menu-items?category=${categoryId}`;
}

function closeModal() {
    document.getElementById('categoryModal').classList.add('tw-hidden');
}

function toggleView() {
    // Toggle between grid and list view
    const grid = document.getElementById('categories-grid');
    const icon = document.getElementById('view-toggle-icon');
    
    if (grid.classList.contains('tw-grid-cols-1')) {
        // Switch to grid view
        grid.className = 'tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-6';
        icon.setAttribute('data-feather', 'list');
    } else {
        // Switch to list view
        grid.className = 'tw-grid tw-grid-cols-1 tw-gap-4';
        icon.setAttribute('data-feather', 'grid');
    }
    feather.replace();
}

// Form submission
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('categoryName').value,
        description: document.getElementById('categoryDescription').value,
        color: document.getElementById('categoryColor').value
    };
    
    const url = currentCategoryId ? `/admin/categories/${currentCategoryId}` : '/admin/categories';
    const method = currentCategoryId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error saving category: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the category.');
    });
});

// Close modal when clicking outside
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
