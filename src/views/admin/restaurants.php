<?php
/**
 * Admin Restaurant Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'restaurants';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Restaurant Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage restaurant approvals, profiles, and operations
            </p>
        </div>
        <div class="tw-flex tw-space-x-2 sm:tw-space-x-3">
            <button onclick="exportRestaurants()" 
                    class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Export Restaurants">
                <i data-feather="download" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Export Restaurants</span>
            </button>
            <button class="tw-bg-primary-600 tw-border tw-border-primary-500 tw-text-white tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Add Restaurant">
                <i data-feather="plus" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Add Restaurant</span>
            </button>
        </div>
    </div>
</div>

<!-- Restaurant Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Restaurants</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_restaurants'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">+8% this month</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Restaurants</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['active_restaurants'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500">88% of total</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Pending Approval</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['pending_restaurants'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-yellow-600">Needs review</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Average Rating</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['avg_rating'] ?? 0, 1) ?></p>
                <p class="tw-text-sm tw-text-green-600">
                    <i data-feather="star" class="tw-h-3 tw-w-3 tw-inline tw-fill-current"></i>
                    Excellent
                </p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="star" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions for Pending Approvals -->
<?php if (($stats['pending_restaurants'] ?? 0) > 0): ?>
<div class="tw-bg-gradient-to-r tw-from-yellow-50 tw-to-orange-50 tw-border tw-border-yellow-200 tw-rounded-xl tw-p-6 tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-2 tw-bg-yellow-100 tw-rounded-lg">
                <i data-feather="alert-circle" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
            <div class="tw-ml-4">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Pending Restaurant Approvals</h3>
                <p class="tw-text-sm tw-text-gray-600"><?= $stats['pending_restaurants'] ?? 0 ?> restaurants are waiting for approval</p>
            </div>
        </div>
        <button onclick="showPendingApprovals()" class="tw-bg-yellow-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium hover:tw-bg-yellow-700 tw-transition-colors">
            Review Now
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Filters and Search -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-flex-col tw-md:tw-flex-row tw-md:tw-items-center tw-md:tw-justify-between tw-space-y-4 tw-md:tw-space-y-0">
            <!-- Search -->
            <div class="tw-flex-1 tw-max-w-lg">
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                        <i data-feather="search" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                    </div>
                    <input type="text" id="restaurant-search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-leading-5 tw-bg-white tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-1 focus:tw-ring-primary-500 focus:tw-border-primary-500" placeholder="Search restaurants by name, cuisine, or location..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="tw-flex tw-space-x-4">
                <select id="status-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
                
                <select id="cuisine-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Cuisines</option>
                    <option value="african" <?= ($filters['category'] ?? '') === 'african' ? 'selected' : '' ?>>African</option>
                    <option value="continental" <?= ($filters['category'] ?? '') === 'continental' ? 'selected' : '' ?>>Continental</option>
                    <option value="fast-food" <?= ($filters['category'] ?? '') === 'fast-food' ? 'selected' : '' ?>>Fast Food</option>
                    <option value="local" <?= ($filters['category'] ?? '') === 'local' ? 'selected' : '' ?>>Local Dishes</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Restaurants Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">All Restaurants</h2>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <span class="tw-text-sm tw-text-gray-500">Showing 1-20 of <?= number_format($stats['totalRestaurants'] ?? 0) ?> restaurants</span>
                <div class="tw-flex tw-space-x-1">
                    <button class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600">
                        <i data-feather="chevron-left" class="tw-h-4 tw-w-4"></i>
                    </button>
                    <button class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600">
                        <i data-feather="chevron-right" class="tw-h-4 tw-w-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        <input type="checkbox" class="tw-rounded tw-border-gray-300 tw-text-primary-600 focus:tw-ring-primary-500">
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Restaurant</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Owner</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Commission</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Rating</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Orders</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Revenue</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="restaurants-table-body">
                <?php if (empty($restaurants)): ?>
                <tr>
                    <td colspan="9" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                        <div class="tw-flex tw-flex-col tw-items-center">
                            <i data-feather="shopping-bag" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mb-4"></i>
                            <p class="tw-text-lg tw-font-medium">No restaurants found</p>
                            <p class="tw-text-sm">No restaurants have been registered yet.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($restaurants as $restaurant): ?>
                <tr class="hover:tw-bg-gray-50" data-restaurant-id="<?= $restaurant['id'] ?>">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <input type="checkbox" class="tw-rounded tw-border-gray-300 tw-text-primary-600 focus:tw-ring-primary-500" value="<?= $restaurant['id'] ?>">
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-12 tw-w-12 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                                <i data-feather="coffee" class="tw-h-6 tw-w-6 tw-text-white"></i>
                            </div>
                            <div class="tw-ml-4">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($restaurant['name'] ?? 'Unknown') ?></div>
                                <div class="tw-text-sm tw-text-gray-500"><?= e($restaurant['category_name'] ?? 'N/A') ?> • <?= e($restaurant['address'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-green-500 tw-to-teal-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-white tw-font-semibold tw-text-xs">
                                    <?= strtoupper(substr($restaurant['first_name'] ?? 'O', 0, 1)) ?>
                                </span>
                            </div>
                            <div class="tw-ml-3">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e(($restaurant['first_name'] ?? '') . ' ' . ($restaurant['last_name'] ?? '')) ?></div>
                                <div class="tw-text-sm tw-text-gray-500"><?= e($restaurant['owner_email'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium
                            <?php
                            switch($restaurant['status']) {
                                case 'active': echo 'tw-bg-green-100 tw-text-green-800'; break;
                                case 'pending': echo 'tw-bg-yellow-100 tw-text-yellow-800'; break;
                                case 'suspended': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                case 'rejected': echo 'tw-bg-gray-100 tw-text-gray-800'; break;
                                default: echo 'tw-bg-gray-100 tw-text-gray-800';
                            }
                            ?>">
                            <?= ucfirst($restaurant['status']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-900 commission-rate">
                                <?= number_format(($restaurant['commission_rate'] ?? 0.15) * 100, 1) ?>%
                            </span>
                            <button class="tw-text-blue-600 hover:tw-text-blue-900 tw-text-xs"
                                    onclick="openCommissionModal(<?= $restaurant['id'] ?>, <?= ($restaurant['commission_rate'] ?? 0.15) * 100 ?>, '<?= htmlspecialchars($restaurant['name'], ENT_QUOTES) ?>')"
                                    title="Edit Commission Rate">
                                <i data-feather="edit-2" class="tw-h-3 tw-w-3"></i>
                            </button>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <?php if (($restaurant['rating'] ?? 0) > 0): ?>
                            <div class="tw-flex tw-items-center">
                                <i data-feather="star" class="tw-h-4 tw-w-4 tw-text-yellow-400 tw-fill-current"></i>
                                <span class="tw-ml-1 tw-text-sm tw-font-medium tw-text-gray-900"><?= number_format($restaurant['rating'], 1) ?></span>
                            </div>
                        <?php else: ?>
                            <span class="tw-text-sm tw-text-gray-500">No ratings</span>
                        <?php endif; ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                        <?= number_format($restaurant['total_orders'] ?? 0) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                        <?= number_format($restaurant['total_revenue'] ?? 0) ?> XAF
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-2">
                            <button class="tw-text-primary-600 hover:tw-text-primary-900" onclick="viewRestaurant(<?= $restaurant['id'] ?>)">
                                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <button class="tw-text-blue-600 hover:tw-text-blue-900" onclick="editRestaurant(<?= $restaurant['id'] ?>)">
                                <i data-feather="edit" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <?php if ($restaurant['status'] === 'pending'): ?>
                                <button class="tw-text-green-600 hover:tw-text-green-900" onclick="approveRestaurant(<?= $restaurant['id'] ?>)">
                                    <i data-feather="check" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <button class="tw-text-red-600 hover:tw-text-red-900" onclick="rejectRestaurant(<?= $restaurant['id'] ?>)">
                                    <i data-feather="x" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php elseif ($restaurant['status'] === 'active'): ?>
                                <button class="tw-text-yellow-600 hover:tw-text-yellow-900" onclick="suspendRestaurant(<?= $restaurant['id'] ?>)">
                                    <i data-feather="pause" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php elseif ($restaurant['status'] === 'suspended'): ?>
                                <button class="tw-text-green-600 hover:tw-text-green-900" onclick="activateRestaurant(<?= $restaurant['id'] ?>)">
                                    <i data-feather="play" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="tw-bg-white tw-px-4 tw-py-3 tw-flex tw-items-center tw-justify-between tw-border-t tw-border-gray-200 tw-sm:tw-px-6">
        <div class="tw-flex-1 tw-flex tw-justify-between tw-sm:tw-hidden">
            <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                Previous
            </button>
            <button class="tw-ml-3 tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                Next
            </button>
        </div>
        <div class="tw-hidden tw-sm:tw-flex-1 tw-sm:tw-flex tw-sm:tw-items-center tw-sm:tw-justify-between">
            <div>
                <p class="tw-text-sm tw-text-gray-700">
                    Showing <span class="tw-font-medium">1</span> to <span class="tw-font-medium">20</span> of <span class="tw-font-medium"><?= number_format($stats['totalRestaurants'] ?? 0) ?></span> results
                </p>
            </div>
            <div>
                <nav class="tw-relative tw-z-0 tw-inline-flex tw-rounded-md tw-shadow-sm tw--space-x-px" aria-label="Pagination">
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-2 tw-py-2 tw-rounded-l-md tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-500 hover:tw-bg-gray-50">
                        <i data-feather="chevron-left" class="tw-h-5 tw-w-5"></i>
                    </button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">1</button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">2</button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">3</button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-2 tw-py-2 tw-rounded-r-md tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-500 hover:tw-bg-gray-50">
                        <i data-feather="chevron-right" class="tw-h-5 tw-w-5"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
// Restaurant management functions
function viewRestaurant(restaurantId) {
    window.location.href = '<?= url('/admin/restaurants/') ?>' + restaurantId;
}

function editRestaurant(restaurantId) {
    window.location.href = '<?= url('/admin/restaurants/') ?>' + restaurantId + '/edit';
}

function approveRestaurant(restaurantId) {
    if (confirm('Are you sure you want to approve this restaurant?')) {
        fetch('<?= url('/admin/restaurants/') ?>' + restaurantId + '/approve', {
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
                alert('Error approving restaurant: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the restaurant.');
        });
    }
}

function rejectRestaurant(restaurantId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
        fetch('<?= url('/admin/restaurants/') ?>' + restaurantId + '/reject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error rejecting restaurant: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while rejecting the restaurant.');
        });
    }
}

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
                alert('Error suspending restaurant: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while suspending the restaurant.');
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
                alert('Error activating restaurant: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while activating the restaurant.');
        });
    }
}

function showPendingApprovals() {
    document.getElementById('status-filter').value = 'pending';
    filterRestaurants();
}

// Search and filter functionality
document.getElementById('restaurant-search').addEventListener('input', function() {
    filterRestaurants();
});

document.getElementById('status-filter').addEventListener('change', function() {
    filterRestaurants();
});

document.getElementById('cuisine-filter').addEventListener('change', function() {
    filterRestaurants();
});

function filterRestaurants() {
    const search = document.getElementById('restaurant-search').value;
    const status = document.getElementById('status-filter').value;
    const cuisine = document.getElementById('cuisine-filter').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (cuisine) params.append('category', cuisine);
    
    // Update URL and reload content
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
        console.log('Restaurants page: Feather icons initialized');
        
        // Force re-initialization after a short delay to ensure all icons are replaced
        setTimeout(function() {
    feather.replace();
            console.log('Restaurants page: Feather icons re-initialized');
        }, 100);
    } else {
        console.error('Feather icons not loaded');
    }
});

function exportRestaurants() {
    // Show loading state
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="tw-animate-spin tw-h-4 tw-w-4 tw-inline tw-mr-2">⟳</i>Exporting...';
    button.disabled = true;

    // Create download link for restaurants export
    const link = document.createElement('a');
    link.href = '<?= url('/admin/export/restaurants') ?>';
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Reset button state after delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

// Commission management functions
function editCommission(restaurantId, currentRate) {
    const newRate = prompt(`Enter new commission rate for restaurant (current: ${currentRate}%):`, currentRate);

    if (newRate === null) return; // User cancelled

    const rate = parseFloat(newRate);
    if (isNaN(rate) || rate < 0 || rate > 100) {
        alert('Please enter a valid commission rate between 0 and 100');
        return;
    }

    // Show loading state
    const button = event.target.closest('button');
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="tw-animate-spin tw-h-3 tw-w-3">⟳</i>';
    button.disabled = true;

    // Update commission rate
    fetch(`<?= url('/admin/restaurants/') ?>${restaurantId}/commission`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            commission_rate: rate / 100 // Convert percentage to decimal
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the display
            const rateSpan = button.parentElement.querySelector('span');
            rateSpan.textContent = `${rate}%`;

            // Update the onclick attribute
            button.setAttribute('onclick', `editCommission(${restaurantId}, ${rate})`);

            // Show success message
            showNotification('Commission rate updated successfully', 'success');
        } else {
            alert('Error updating commission rate: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating commission rate. Please try again.');
    })
    .finally(() => {
        // Reset button state
        button.innerHTML = originalIcon;
        button.disabled = false;
    });
}

// Utility function to show notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-z-50 tw-p-4 tw-rounded-lg tw-shadow-lg tw-transition-all tw-duration-300 ${
        type === 'success' ? 'tw-bg-green-500 tw-text-white' :
        type === 'error' ? 'tw-bg-red-500 tw-text-white' :
        'tw-bg-blue-500 tw-text-white'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Modal functions
let currentRestaurantId = null;
let currentRestaurantName = '';

function openCommissionModal(restaurantId, currentRate, restaurantName = '') {
    currentRestaurantId = restaurantId;
    currentRestaurantName = restaurantName;

    // Populate modal
    document.getElementById('modalRestaurantName').textContent = restaurantName || `Restaurant #${restaurantId}`;
    document.getElementById('modalCurrentRate').textContent = `${currentRate}%`;
    document.getElementById('newCommissionRate').value = currentRate;

    // Clear any previous errors
    document.getElementById('modalError').style.display = 'none';

    // Show modal
    document.getElementById('commissionModal').style.display = 'block';
    document.getElementById('newCommissionRate').focus();
}

function closeCommissionModal() {
    document.getElementById('commissionModal').style.display = 'none';
    currentRestaurantId = null;
    currentRestaurantName = '';
}

function saveCommissionRate() {
    const newRateInput = document.getElementById('newCommissionRate');
    const saveBtn = document.getElementById('saveCommissionBtn');
    const errorDiv = document.getElementById('modalError');

    const rate = parseFloat(newRateInput.value);

    // Validation
    if (isNaN(rate) || rate < 0 || rate > 100) {
        errorDiv.textContent = 'Please enter a valid commission rate between 0 and 100';
        errorDiv.style.display = 'block';
        return;
    }

    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    errorDiv.style.display = 'none';

    // Convert percentage to decimal for backend
    const rateDecimal = rate / 100;

    fetch(`<?= url('/admin/restaurants') ?>/${currentRestaurantId}/commission`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            commission_rate: rateDecimal
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Commission rate updated successfully', 'success');

            // Update the display in the table
            const rateElement = document.querySelector(`tr[data-restaurant-id="${currentRestaurantId}"] .commission-rate`);
            if (rateElement) {
                rateElement.textContent = `${rate}%`;
            }

            // Close modal
            closeCommissionModal();

            // Reload page to reflect changes
            setTimeout(() => location.reload(), 1000);
        } else {
            errorDiv.textContent = data.message || 'Error updating commission rate. Please try again.';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = 'Network error. Please check your connection and try again.';
        errorDiv.style.display = 'block';
    })
    .finally(() => {
        // Re-enable button
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Changes';
    });
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('commissionModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCommissionModal();
            }
        });
    }

    // Handle Enter key in input
    const input = document.getElementById('newCommissionRate');
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                saveCommissionRate();
            }
        });
    }
});
</script>

<!-- Commission Edit Modal -->
<div id="commissionModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50" style="display: none;">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <!-- Modal Header -->
            <div class="tw-flex tw-items-center tw-justify-between tw-pb-3 tw-border-b tw-border-gray-200">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Edit Commission Rate</h3>
                <button onclick="closeCommissionModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="tw-mt-4">
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Restaurant: <span id="modalRestaurantName" class="tw-font-semibold tw-text-gray-900"></span>
                    </label>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Current Rate: <span id="modalCurrentRate" class="tw-font-semibold tw-text-blue-600"></span>
                    </label>
                </div>

                <div class="tw-mb-4">
                    <label for="newCommissionRate" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        New Commission Rate (%)
                    </label>
                    <div class="tw-relative">
                        <input type="number"
                               id="newCommissionRate"
                               class="tw-w-full tw-px-3 tw-py-2 tw-pr-8 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500"
                               min="0"
                               max="100"
                               step="0.1"
                               placeholder="Enter commission rate">
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-pr-3 tw-pointer-events-none">
                            <span class="tw-text-gray-500 tw-text-sm">%</span>
                        </div>
                    </div>
                    <p class="tw-mt-1 tw-text-xs tw-text-gray-500">
                        Enter a value between 0 and 100 (e.g., 15.5 for 15.5%)
                    </p>
                </div>

                <div id="modalError" class="tw-mb-4 tw-p-3 tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-rounded" style="display: none;">
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="tw-flex tw-items-center tw-justify-end tw-pt-4 tw-border-t tw-border-gray-200 tw-space-x-3">
                <button onclick="closeCommissionModal()"
                        class="tw-px-4 tw-py-2 tw-bg-gray-300 tw-text-gray-700 tw-rounded-md hover:tw-bg-gray-400 tw-transition-colors">
                    Cancel
                </button>
                <button onclick="saveCommissionRate()"
                        id="saveCommissionBtn"
                        class="tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-md hover:tw-bg-blue-700 tw-transition-colors">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
