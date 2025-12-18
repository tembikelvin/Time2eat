<?php
$title = $title ?? 'Menu Items - Time2Eat';
$currentPage = $currentPage ?? 'menu';
$currentView = $currentView ?? 'menu';
$user = $user ?? null;
$items = $menuItems ?? $items ?? [];
$categories = $categories ?? [];
$restaurant = $restaurant ?? null;
$currentPageNum = $currentPageNum ?? 1;
$totalPages = $totalPages ?? 1;
$totalItems = $totalItems ?? 0;
$filters = $filters ?? [];
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">
                <?= $currentView === 'inventory' ? 'Inventory Management' : 'Menu Items' ?>
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                <?php if ($currentView === 'inventory'): ?>
                    Track stock levels and manage inventory for <?= e($restaurant['name'] ?? 'Your Restaurant') ?>
                <?php else: ?>
                    <?= $totalItems ?> items in <?= e($restaurant['name'] ?? 'Your Restaurant') ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <!-- View Toggle -->
            <div class="tw-flex tw-bg-gray-100 tw-rounded-lg tw-p-1">
                <a href="<?= url('/vendor/menu') ?>"
                   class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-text-sm tw-font-medium tw-rounded-md tw-transition-colors <?= $currentView === 'menu' ? 'tw-bg-white tw-text-gray-900 tw-shadow-sm' : 'tw-text-gray-500 hover:tw-text-gray-700' ?>">
                    <i data-feather="menu" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                    Menu
                </a>
                <a href="<?= url('/vendor/menu?view=inventory') ?>"
                   class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-text-sm tw-font-medium tw-rounded-md tw-transition-colors <?= $currentView === 'inventory' ? 'tw-bg-white tw-text-gray-900 tw-shadow-sm' : 'tw-text-gray-500 hover:tw-text-gray-700' ?>">
                    <i data-feather="package" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                    Inventory
                </a>
            </div>

            <a href="<?= url('/vendor/menu/create') ?>" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Add Item
            </a>
            <a href="<?= url('/vendor/menu/import') ?>" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                <i data-feather="upload" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Import
            </a>
        </div>
    </div>
</div>

<!-- Menu Content -->
        <!-- Filters -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-6">
            <form method="GET" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-4">
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Search</label>
                    <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search items..." class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500">
                </div>
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Category</label>
                    <select name="category" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category['name']) ?>" <?= ($filters['category'] ?? '') === $category['name'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Status</label>
                    <select name="status" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-focus:tw-ring-2 tw-focus:tw-ring-orange-500 tw-focus:tw-border-orange-500">
                        <option value="">All Status</option>
                        <option value="available" <?= ($filters['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="unavailable" <?= ($filters['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                        <option value="low_stock" <?= ($filters['status'] ?? '') === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                    </select>
                </div>
                <div class="tw-flex tw-items-end">
                    <button type="submit" class="tw-w-full tw-bg-orange-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-hover:tw-bg-orange-600 tw-transition-colors">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Items Grid -->
        <?php if (empty($items)): ?>
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-12 tw-text-center">
                <i data-feather="package" class="tw-w-16 tw-h-16 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No menu items found</h3>
                <p class="tw-text-gray-500 tw-mb-6">Get started by adding your first menu item</p>
                <a href="/eat/vendor/menu/create" class="tw-bg-orange-500 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-font-medium tw-hover:tw-bg-orange-600 tw-transition-colors tw-inline-flex tw-items-center">
                    <i data-feather="plus" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                    Add Menu Item
                </a>
            </div>
        <?php else: ?>
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
                <?php foreach ($items as $item): ?>
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-overflow-hidden tw-hover:tw-shadow-md tw-transition-shadow">
                        <!-- Item Image -->
                        <div class="tw-relative tw-h-48 tw-bg-gray-200">
                            <?php
                            $itemImage = $item['image'] ?? $item['image_url'] ?? null;
                            $imageUrl = imageUrl($itemImage, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80');
                            ?>
                            <?php if (!empty($itemImage)): ?>
                                <img src="<?= e($imageUrl) ?>" alt="<?= e($item['name']) ?>" class="tw-w-full tw-h-full tw-object-cover" onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80'">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80" alt="<?= e($item['name']) ?>" class="tw-w-full tw-h-full tw-object-cover">
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <div class="tw-absolute tw-top-3 tw-left-3">
                                <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-full <?= ($item['is_available'] ?? true) ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-red-100 tw-text-red-800' ?>">
                                    <?= ($item['is_available'] ?? true) ? 'Available' : 'Unavailable' ?>
                                </span>
                            </div>

                            <!-- Stock Badge -->
                            <?php if (($item['stock_quantity'] ?? 0) <= ($item['min_stock_level'] ?? 5)): ?>
                                <div class="tw-absolute tw-top-3 tw-right-3">
                                    <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-full tw-bg-yellow-100 tw-text-yellow-800">
                                        Low Stock
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Item Details -->
                        <div class="tw-p-6">
                            <div class="tw-flex tw-justify-between tw-items-start tw-mb-2">
                                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-truncate"><?= e($item['name']) ?></h3>
                                <span class="tw-text-lg tw-font-bold tw-text-orange-500"><?= number_format($item['price']) ?> XAF</span>
                            </div>
                            
                            <p class="tw-text-sm tw-text-gray-500 tw-mb-2"><?= e($item['category_name'] ?? 'Uncategorized') ?></p>
                            
                            <p class="tw-text-sm tw-text-gray-600 tw-line-clamp-2 tw-mb-4">
                                <?= e(substr($item['description'], 0, 100)) ?><?= strlen($item['description']) > 100 ? '...' : '' ?>
                            </p>

                            <!-- Stock Info -->
                            <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 tw-text-sm">
                                <span class="tw-text-gray-500">Stock: <?= $item['stock_quantity'] ?? 0 ?></span>
                                <span class="tw-text-gray-500">Prep: <?= $item['preparation_time'] ?? 0 ?>min</span>
                            </div>

                            <!-- Actions -->
                            <div class="tw-flex tw-space-x-2">
                                <a href="/eat/vendor/menu/<?= $item['id'] ?>/edit" class="tw-flex-1 tw-bg-orange-500 tw-text-white tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-hover:tw-bg-orange-600 tw-transition-colors tw-text-center">
                                    Edit
                                </a>
                                <button onclick="toggleAvailability(<?= $item['id'] ?>, <?= ($item['is_available'] ?? true) ? 'false' : 'true' ?>)" class="tw-flex-1 tw-bg-gray-100 tw-text-gray-700 tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-hover:tw-bg-gray-200 tw-transition-colors">
                                    <?= ($item['is_available'] ?? true) ? 'Disable' : 'Enable' ?>
                                </button>
                                <button onclick="updateStock(<?= $item['id'] ?>, <?= $item['stock_quantity'] ?? 0 ?>)" class="tw-bg-blue-100 tw-text-blue-700 tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-hover:tw-bg-blue-200 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                    <i data-feather="package" class="tw-w-4 tw-h-4"></i>
                                </button>
                                <button onclick="deleteItem(<?= $item['id'] ?>, '<?= e($item['name']) ?>')" class="tw-bg-red-100 tw-text-red-700 tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-hover:tw-bg-red-200 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                    <i data-feather="trash-2" class="tw-w-4 tw-h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="tw-mt-8 tw-flex tw-justify-center">
                    <nav class="tw-flex tw-space-x-2">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?= $currentPage - 1 ?>&<?= http_build_query($filters) ?>" class="tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-hover:tw-bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <a href="?page=<?= $i ?>&<?= http_build_query($filters) ?>" class="tw-px-3 tw-py-2 tw-text-sm tw-font-medium <?= $i === $currentPage ? 'tw-text-white tw-bg-orange-500 tw-border tw-border-orange-500' : 'tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-hover:tw-bg-gray-50' ?> tw-rounded-lg">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage + 1 ?>&<?= http_build_query($filters) ?>" class="tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-hover:tw-bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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

function toggleAvailability(itemId, newStatus) {
    fetch(`/eat/vendor/menu/toggle/${itemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Toggle response status:', response.status);
        return response.json().then(data => {
            return { data, status: response.status, ok: response.ok };
        }).catch(jsonError => {
            return response.text().then(text => {
                console.error('Response is not JSON:', text);
                throw new Error(`Server returned non-JSON response (Status ${response.status}): ${text.substring(0, 200)}`);
            });
        });
    })
    .then(({ data, status, ok }) => {
        console.log('Toggle response data:', data);

        if (data.success) {
            location.reload();
        } else {
            const errorMsg = data.message || 'Failed to update availability';
            const errorDetails = data.error || data.errors || '';
            const fullError = errorDetails ? `${errorMsg}\n\nDetails: ${JSON.stringify(errorDetails)}` : errorMsg;

            console.error('Toggle failed:', data);
            alert(`Error updating availability:\n\n${fullError}\n\nStatus: ${status}`);
        }
    })
    .catch(error => {
        console.error('Toggle error:', error);
        alert(`An error occurred while updating availability:\n\nError: ${error.message}\n\nCheck browser console (F12) for more details.`);
    });
}

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

    fetch(`/eat/vendor/menu/stock/${currentItemId}`, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ stock_quantity: quantity })
    })
    .then(response => {
        console.log('Stock update response status:', response.status);
        return response.json().then(data => {
            return { data, status: response.status, ok: response.ok };
        }).catch(jsonError => {
            return response.text().then(text => {
                console.error('Response is not JSON:', text);
                throw new Error(`Server returned non-JSON response (Status ${response.status}): ${text.substring(0, 200)}`);
            });
        });
    })
    .then(({ data, status, ok }) => {
        console.log('Stock update response data:', data);

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
            const errorMsg = data.message || 'Failed to update stock';
            const errorDetails = data.error || data.errors || '';
            const fullError = errorDetails ? `${errorMsg}\n\nDetails: ${JSON.stringify(errorDetails)}` : errorMsg;

            console.error('Stock update failed:', data);
            alert(`Error updating stock:\n\n${fullError}\n\nStatus: ${status}`);
        }
    })
    .catch(error => {
        console.error('Stock update error:', error);
        alert(`An error occurred while updating stock:\n\nError: ${error.message}\n\nCheck browser console (F12) for more details.`);
    });
}

function deleteItem(itemId, itemName) {
    if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
        fetch(`/eat/vendor/menu/${itemId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // Log response details for debugging
            console.log('Delete response status:', response.status);
            console.log('Delete response ok:', response.ok);

            // Clone response so we can read it multiple times if needed
            const clonedResponse = response.clone();

            // Try to parse JSON
            return response.json()
                .then(data => {
                    return { data, status: response.status, ok: response.ok };
                })
                .catch(jsonError => {
                    // If JSON parsing fails, try to get text from cloned response
                    return clonedResponse.text().then(text => {
                        console.error('Response is not JSON:', text);
                        throw new Error(`Server returned non-JSON response (Status ${response.status}): ${text.substring(0, 200)}`);
                    });
                });
        })
        .then(({ data, status, ok }) => {
            console.log('Delete response data:', data);

            if (data.success) {
                // Show success message
                const successMsg = document.createElement('div');
                successMsg.className = 'tw-fixed tw-top-4 tw-right-4 tw-bg-green-500 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-z-50';
                successMsg.textContent = 'Menu item deleted successfully!';
                document.body.appendChild(successMsg);

                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                // Show detailed error message
                const errorMsg = data.message || 'Failed to delete item';
                const errorDetails = data.error || data.errors || '';
                const fullError = errorDetails ? `${errorMsg}\n\nDetails: ${JSON.stringify(errorDetails)}` : errorMsg;

                console.error('Delete failed:', data);
                alert(`Error deleting item:\n\n${fullError}\n\nStatus: ${status}`);
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert(`An error occurred while deleting item:\n\nError: ${error.message}\n\nCheck browser console (F12) for more details.`);
        });
    }
}
</script>
