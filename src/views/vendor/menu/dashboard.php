<?php
/**
 * Vendor Menu Dashboard
 * Overview of menu management with statistics and quick actions
 */

// Ensure user is authenticated and has vendor role
if (!isset($user) || $user['role'] !== 'vendor') {
    header('Location: /login');
    exit;
}

$stats = $stats ?? [];
$recentItems = $recentItems ?? [];
$lowStockItems = $lowStockItems ?? [];
$categories = $categories ?? [];
$restaurant = $restaurant ?? null;
?>

<div class="tw-min-h-screen tw-bg-gray-50">
    <!-- Header -->
    <div class="tw-bg-white tw-shadow-sm tw-border-b">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-flex tw-justify-between tw-items-center tw-py-6">
                <div>
                    <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Menu Management</h1>
                    <p class="tw-text-gray-600 tw-mt-1">
                        <?= e($restaurant['name'] ?? 'Your Restaurant') ?> - Manage your menu items and inventory
                    </p>
                </div>
                <div class="tw-flex tw-space-x-3">
                    <a href="<?= url('/vendor/menu/create') ?>" class="tw-bg-orange-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-hover:tw-bg-orange-600 tw-transition-colors tw-flex tw-items-center">
                        <i data-feather="plus" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                        Add Item
                    </a>
                    <a href="<?= url('/vendor/menu/import') ?>" class="tw-bg-blue-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-hover:tw-bg-blue-600 tw-transition-colors tw-flex tw-items-center">
                        <i data-feather="upload" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                        Import CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-8">
        <!-- Statistics Cards -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <div class="tw-flex tw-items-center">
                    <div class="tw-flex-shrink-0">
                        <div class="tw-w-8 tw-h-8 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                            <i data-feather="package" class="tw-w-5 tw-h-5 tw-text-blue-600"></i>
                        </div>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Items</p>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= (int)($stats['total_items'] ?? 0) ?></p>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <div class="tw-flex tw-items-center">
                    <div class="tw-flex-shrink-0">
                        <div class="tw-w-8 tw-h-8 tw-bg-green-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                            <i data-feather="check-circle" class="tw-w-5 tw-h-5 tw-text-green-600"></i>
                        </div>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Available</p>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= (int)($stats['available_items'] ?? 0) ?></p>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <div class="tw-flex tw-items-center">
                    <div class="tw-flex-shrink-0">
                        <div class="tw-w-8 tw-h-8 tw-bg-yellow-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                            <i data-feather="alert-triangle" class="tw-w-5 tw-h-5 tw-text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Low Stock</p>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= (int)($stats['low_stock_items'] ?? 0) ?></p>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
                <div class="tw-flex tw-items-center">
                    <div class="tw-flex-shrink-0">
                        <div class="tw-w-8 tw-h-8 tw-bg-red-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                            <i data-feather="x-circle" class="tw-w-5 tw-h-5 tw-text-red-600"></i>
                        </div>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Out of Stock</p>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= (int)($stats['out_of_stock_items'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
            <!-- Recent Items -->
            <div class="lg:tw-col-span-2">
                <div class="tw-bg-white tw-rounded-lg tw-shadow-sm">
                    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Recent Items</h3>
                            <a href="<?= url('/vendor/menu') ?>" class="tw-text-orange-500 tw-hover:tw-text-orange-600 tw-font-medium tw-text-sm">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="tw-divide-y tw-divide-gray-200">
                        <?php if (empty($recentItems)): ?>
                            <div class="tw-px-6 tw-py-8 tw-text-center">
                                <i data-feather="package" class="tw-w-12 tw-h-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                                <p class="tw-text-gray-500">No menu items yet</p>
                                <a href="<?= url('/vendor/menu/create') ?>" class="tw-text-orange-500 tw-hover:tw-text-orange-600 tw-font-medium">
                                    Add your first item
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentItems as $item): ?>
                                <div class="tw-px-6 tw-py-4 tw-hover:tw-bg-gray-50">
                                    <div class="tw-flex tw-items-center tw-justify-between">
                                        <div class="tw-flex tw-items-center tw-space-x-4">
                                            <div class="tw-flex-shrink-0">
                                                <?php
                                                $itemImage = $item['image'] ?? $item['image_url'] ?? null;
                                                $imageUrl = imageUrl($itemImage, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100&h=100&fit=crop&q=80');
                                                if (!empty($itemImage)): ?>
                                                    <img src="<?= e($imageUrl) ?>" alt="<?= e($item['name']) ?>" class="tw-w-12 tw-h-12 tw-rounded-lg tw-object-cover" onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100&h=100&fit=crop&q=80'">
                                                <?php else: ?>
                                                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100&h=100&fit=crop&q=80" alt="<?= e($item['name']) ?>" class="tw-w-12 tw-h-12 tw-rounded-lg tw-object-cover">
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h4 class="tw-font-medium tw-text-gray-900"><?= e($item['name']) ?></h4>
                                                <p class="tw-text-sm tw-text-gray-500"><?= e($item['category_name'] ?? 'Uncategorized') ?></p>
                                            </div>
                                        </div>
                                        <div class="tw-flex tw-items-center tw-space-x-4">
                                            <span class="tw-font-medium tw-text-gray-900"><?= number_format($item['price']) ?> XAF</span>
                                            <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-full <?= $item['is_available'] ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-red-100 tw-text-red-800' ?>">
                                                <?= $item['is_available'] ? 'Available' : 'Unavailable' ?>
                                            </span>
                                            <a href="<?= url('/vendor/menu/edit/' . $item['id']) ?>" class="tw-text-orange-500 tw-hover:tw-text-orange-600">
                                                <i data-feather="edit-2" class="tw-w-4 tw-h-4"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="tw-space-y-6">
                <!-- Low Stock Alert -->
                <?php if (!empty($lowStockItems)): ?>
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm">
                        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-flex tw-items-center">
                                <i data-feather="alert-triangle" class="tw-w-5 tw-h-5 tw-text-yellow-500 tw-mr-2"></i>
                                Low Stock Alert
                            </h3>
                        </div>
                        <div class="tw-divide-y tw-divide-gray-200">
                            <?php foreach (array_slice($lowStockItems, 0, 5) as $item): ?>
                                <div class="tw-px-6 tw-py-3">
                                    <div class="tw-flex tw-justify-between tw-items-center">
                                        <div>
                                            <p class="tw-font-medium tw-text-gray-900 tw-text-sm"><?= e($item['name']) ?></p>
                                            <p class="tw-text-xs tw-text-gray-500"><?= e($item['category_name'] ?? 'Uncategorized') ?></p>
                                        </div>
                                        <div class="tw-text-right">
                                            <p class="tw-text-sm tw-font-medium tw-text-red-600"><?= $item['stock_quantity'] ?> left</p>
                                            <button onclick="updateStock(<?= $item['id'] ?>, <?= $item['stock_quantity'] ?>)" class="tw-text-xs tw-text-orange-500 tw-hover:tw-text-orange-600">
                                                Update
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Categories -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-sm">
                    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Categories</h3>
                    </div>
                    <div class="tw-p-6">
                        <?php if (empty($categories)): ?>
                            <p class="tw-text-gray-500 tw-text-sm tw-mb-4">No categories yet</p>
                            <button onclick="createCategory()" class="tw-w-full tw-bg-orange-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-hover:tw-bg-orange-600 tw-transition-colors tw-text-sm">
                                Create Category
                            </button>
                        <?php else: ?>
                            <div class="tw-space-y-2 tw-mb-4">
                                <?php foreach ($categories as $category): ?>
                                    <div class="tw-flex tw-justify-between tw-items-center tw-py-2">
                                        <span class="tw-text-sm tw-text-gray-900"><?= e($category['name']) ?></span>
                                        <span class="tw-text-xs tw-text-gray-500"><?= $category['item_count'] ?? 0 ?> items</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button onclick="manageCategories()" class="tw-w-full tw-bg-gray-100 tw-text-gray-700 tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-hover:tw-bg-gray-200 tw-transition-colors tw-text-sm">
                                Manage Categories
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-sm">
                    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="tw-p-6 tw-space-y-3">
                        <a href="<?= url('/vendor/menu') ?>" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 tw-hover:tw-bg-gray-50 tw-rounded-lg tw-transition-colors tw-flex tw-items-center">
                            <i data-feather="list" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                            View All Items
                        </a>
                        <a href="<?= url('/vendor/menu/create') ?>" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 tw-hover:tw-bg-gray-50 tw-rounded-lg tw-transition-colors tw-flex tw-items-center">
                            <i data-feather="plus" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                            Add New Item
                        </a>
                        <a href="<?= url('/vendor/menu/import') ?>" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 tw-hover:tw-bg-gray-50 tw-rounded-lg tw-transition-colors tw-flex tw-items-center">
                            <i data-feather="upload" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                            Import CSV
                        </a>
                        <a href="<?= url('/vendor/menu/template') ?>" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 tw-hover:tw-bg-gray-50 tw-rounded-lg tw-transition-colors tw-flex tw-items-center">
                            <i data-feather="download" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                            Download Template
                        </a>
                        <button onclick="bulkActions()" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 tw-hover:tw-bg-gray-50 tw-rounded-lg tw-transition-colors tw-flex tw-items-center">
                            <i data-feather="settings" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                            Bulk Actions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Update Modal -->
<div id="stockModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-hidden tw-z-50">
    <div class="tw-flex tw-items-center tw-justify-center tw-min-h-screen tw-p-4">
        <div class="tw-bg-white tw-rounded-lg tw-shadow-xl tw-max-w-md tw-w-full">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Update Stock</h3>
            </div>
            <div class="tw-p-6">
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Stock Quantity</label>
                    <input type="number" id="stockQuantity" min="0" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500">
                </div>
                <div class="tw-flex tw-justify-end tw-space-x-3">
                    <button onclick="closeStockModal()" class="tw-px-4 tw-py-2 tw-text-gray-700 tw-bg-gray-100 tw-rounded-lg tw-hover:tw-bg-gray-200 tw-transition-colors">
                        Cancel
                    </button>
                    <button onclick="saveStock()" class="tw-px-4 tw-py-2 tw-bg-orange-500 tw-text-white tw-rounded-lg tw-hover:tw-bg-orange-600 tw-transition-colors">
                        Update
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentItemId = null;

function updateStock(itemId, currentStock) {
    currentItemId = itemId;
    document.getElementById('stockQuantity').value = currentStock;
    document.getElementById('stockModal').classList.remove('tw-hidden');
}

function closeStockModal() {
    document.getElementById('stockModal').classList.add('tw-hidden');
    currentItemId = null;
}

function saveStock() {
    if (!currentItemId) return;

    const quantity = parseInt(document.getElementById('stockQuantity').value);

    // Validate quantity
    if (isNaN(quantity) || quantity < 0) {
        alert('Please enter a valid stock quantity');
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                      document.querySelector('input[name="_token"]')?.value || '';

    const headers = {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    if (csrfToken) {
        headers['X-CSRF-Token'] = csrfToken;
    }

    fetch(`/vendor/menu/stock/${currentItemId}`, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ stock_quantity: quantity })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'tw-fixed tw-top-4 tw-right-4 tw-bg-green-500 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-z-50';
            successMsg.textContent = 'Stock updated successfully!';
            document.body.appendChild(successMsg);

            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert(data.message || 'Failed to update stock');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
    });
}

function createCategory() {
    const name = prompt('Enter category name:');
    if (name) {
        // Implement category creation
        alert('Category creation feature coming soon');
    }
}

function manageCategories() {
    window.location.href = '/vendor/categories';
}

function bulkActions() {
    window.location.href = '/vendor/menu?bulk=1';
}
</script>
