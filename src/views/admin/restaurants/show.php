<?php
/**
 * Admin Restaurant Details View
 * Shows detailed information about a restaurant
 */

// Set current page for sidebar highlighting
$currentPage = 'restaurants';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <div class="tw-flex tw-items-center tw-space-x-3 tw-mb-2">
                <a href="<?= url('/admin/restaurants') ?>" class="tw-text-gray-500 hover:tw-text-gray-700">
                    <i data-feather="arrow-left" class="tw-h-5 tw-w-5"></i>
                </a>
                <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= e($restaurant['name']) ?></h1>
                <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium 
                    <?php 
                    switch($restaurant['status']) {
                        case 'active': echo 'tw-bg-green-100 tw-text-green-800'; break;
                        case 'pending': echo 'tw-bg-yellow-100 tw-text-yellow-800'; break;
                        case 'suspended': echo 'tw-bg-red-100 tw-text-red-800'; break;
                        default: echo 'tw-bg-gray-100 tw-text-gray-800';
                    }
                    ?>">
                    <?= ucfirst($restaurant['status']) ?>
                </span>
            </div>
            <p class="tw-text-sm tw-text-gray-600">
                Restaurant ID: #<?= $restaurant['id'] ?> • Added <?= date('M j, Y', strtotime($restaurant['created_at'])) ?>
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <?php if ($restaurant['status'] === 'suspended'): ?>
                <button onclick="activateRestaurant(<?= $restaurant['id'] ?>)" class="tw-bg-green-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-green-700">
                    <i data-feather="play" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                    Activate
                </button>
            <?php elseif ($restaurant['status'] === 'active'): ?>
                <button onclick="suspendRestaurant(<?= $restaurant['id'] ?>)" class="tw-bg-yellow-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-yellow-700">
                    <i data-feather="pause" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                    Suspend
                </button>
            <?php endif; ?>
            <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/edit') ?>" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                <i data-feather="edit" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Edit Restaurant
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Orders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_orders'] ?? 0) ?></p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Revenue</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_revenue'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500">XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Order Value</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['avg_order_value'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500">XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Menu Items</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['menu_items_count'] ?? 0) ?></p>
            </div>
            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                <i data-feather="list" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Restaurant Details -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
    <!-- Main Information -->
    <div class="lg:tw-col-span-2 tw-space-y-6">
        <!-- Basic Info -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Basic Information</h2>
            </div>
            <div class="tw-p-6">
                <dl class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Restaurant Name</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($restaurant['name']) ?></dd>
                    </div>
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Cuisine Type</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($restaurant['cuisine_type'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Phone</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($restaurant['phone']) ?></dd>
                    </div>
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Email</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($restaurant['email'] ?? 'N/A') ?></dd>
                    </div>
                    <div class="sm:tw-col-span-2">
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Address</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($restaurant['address']) ?></dd>
                    </div>
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">City</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($restaurant['city'] ?? 'Bamenda') ?></dd>
                    </div>
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">State</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($restaurant['state'] ?? 'Northwest') ?></dd>
                    </div>
                    <?php if (!empty($restaurant['description'])): ?>
                    <div class="sm:tw-col-span-2">
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Description</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($restaurant['description']) ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Business Settings -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Business Settings</h2>
            </div>
            <div class="tw-p-6">
                <dl class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Delivery Fee</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= number_format($restaurant['delivery_fee'] ?? 0) ?> XAF</dd>
                    </div>
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Minimum Order</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= number_format($restaurant['minimum_order'] ?? 0) ?> XAF</dd>
                    </div>
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Delivery Time</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= $restaurant['delivery_time'] ?? 30 ?> minutes</dd>
                    </div>
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Rating</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900">
                            <?php if (($restaurant['rating'] ?? 0) > 0): ?>
                                <i data-feather="star" class="tw-h-4 tw-w-4 tw-text-yellow-400 tw-fill-current tw-inline"></i>
                                <?= number_format($restaurant['rating'], 1) ?> (<?= number_format($restaurant['total_reviews'] ?? 0) ?> reviews)
                            <?php else: ?>
                                No ratings yet
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="tw-space-y-6">
        <!-- Owner Information -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Owner Information</h2>
            </div>
            <div class="tw-p-6">
                <div class="tw-flex tw-items-center tw-space-x-3 tw-mb-4">
                    <div class="tw-h-12 tw-w-12 tw-bg-gradient-to-r tw-from-green-500 tw-to-teal-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <span class="tw-text-white tw-font-semibold">
                            <?= strtoupper(substr($restaurant['first_name'] ?? 'O', 0, 1)) ?>
                        </span>
                    </div>
                    <div>
                        <div class="tw-font-medium tw-text-gray-900"><?= e(($restaurant['first_name'] ?? '') . ' ' . ($restaurant['last_name'] ?? '')) ?></div>
                        <div class="tw-text-sm tw-text-gray-500">Restaurant Owner</div>
                    </div>
                </div>
                <dl class="tw-space-y-3">
                    <div>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Email</dt>
                        <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($restaurant['owner_email'] ?? 'N/A') ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Quick Actions</h2>
            </div>
            <div class="tw-p-6 tw-space-y-3">
                <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/edit') ?>" class="tw-block tw-w-full tw-text-center tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                    Edit Restaurant
                </a>
                <a href="<?= url('/vendor/menu?restaurant=' . $restaurant['id']) ?>" class="tw-block tw-w-full tw-text-center tw-bg-gray-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-gray-700">
                    View Menu
                </a>
                <?php if ($restaurant['status'] === 'active'): ?>
                    <button onclick="suspendRestaurant(<?= $restaurant['id'] ?>)" class="tw-block tw-w-full tw-text-center tw-bg-yellow-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-yellow-700">
                        Suspend Restaurant
                    </button>
                <?php elseif ($restaurant['status'] === 'suspended'): ?>
                    <button onclick="activateRestaurant(<?= $restaurant['id'] ?>)" class="tw-block tw-w-full tw-text-center tw-bg-green-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-green-700">
                        Activate Restaurant
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Timeline</h2>
            </div>
            <div class="tw-p-6 tw-space-y-3 tw-text-sm">
                <div>
                    <div class="tw-font-medium tw-text-gray-900">Created</div>
                    <div class="tw-text-gray-500"><?= date('M j, Y g:i A', strtotime($restaurant['created_at'])) ?></div>
                </div>
                <div>
                    <div class="tw-font-medium tw-text-gray-900">Last Updated</div>
                    <div class="tw-text-gray-500"><?= date('M j, Y g:i A', strtotime($restaurant['updated_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Restaurant action functions
function suspendRestaurant(restaurantId) {
    if (confirm('Are you sure you want to suspend this restaurant? This will make it unavailable to customers.')) {
        fetch('<?= url('/admin/restaurants/') ?>' + restaurantId + '/suspend', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
}

function activateRestaurant(restaurantId) {
    if (confirm('Are you sure you want to activate this restaurant?')) {
        fetch('<?= url('/admin/restaurants/') ?>' + restaurantId + '/activate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
}

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

