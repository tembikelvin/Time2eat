<?php
/**
 * Admin Rider Management Page
 * Comprehensive rider administration interface
 */

// Helper function for status badge
function getStatusBadge($status) {
    $badges = [
        'active' => 'tw-bg-green-100 tw-text-green-800',
        'inactive' => 'tw-bg-gray-100 tw-text-gray-800',
        'suspended' => 'tw-bg-red-100 tw-text-red-800'
    ];
    return $badges[$status] ?? 'tw-bg-gray-100 tw-text-gray-800';
}

// Helper function for availability badge
function getAvailabilityBadge($isOnline) {
    return $isOnline ? 'tw-bg-green-500' : 'tw-bg-gray-400';
}
?>

<?php if (isset($error)): ?>
<!-- Error Message -->
<div class="tw-mb-6 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-p-4">
    <div class="tw-flex tw-items-center">
        <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-red-600 tw-mr-3"></i>
        <div>
            <h3 class="tw-text-sm tw-font-semibold tw-text-red-800">Error Loading Data</h3>
            <p class="tw-text-xs tw-text-red-600 tw-mt-1"><?= htmlspecialchars($error) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="tw-mb-6 md:tw-mb-8">
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-3 md:tw-p-4 tw-rounded-2xl tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 tw-mr-3 md:tw-mr-4 tw-shadow-lg">
                <i data-feather="truck" class="tw-h-6 tw-w-6 md:tw-h-8 md:tw-w-8 tw-text-white"></i>
            </div>
            <div>
                <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-gray-900">Rider Management</h1>
                <p class="tw-mt-1 tw-text-xs md:tw-text-sm tw-text-gray-500 tw-flex tw-items-center">
                    <i data-feather="users" class="tw-h-3 tw-w-3 md:tw-h-4 md:tw-w-4 tw-mr-1"></i>
                    Manage delivery riders and track performance
                </p>
            </div>
        </div>
        
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-2 md:tw-gap-3">
            <button onclick="exportRiders()" 
                    class="tw-inline-flex tw-items-center tw-justify-center tw-px-4 md:tw-px-6 tw-py-2 md:tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm tw-text-xs md:tw-text-sm tw-font-semibold tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 hover:tw-scale-105 active:tw-scale-95">
                <i data-feather="download" class="tw-h-4 tw-w-4 md:tw-h-5 md:tw-w-5 tw-mr-2"></i>
                <span class="tw-hidden md:tw-inline">Export Data</span>
                <span class="md:tw-hidden">Export</span>
            </button>
            <button onclick="refreshData()" 
                    class="tw-inline-flex tw-items-center tw-justify-center tw-px-4 md:tw-px-6 tw-py-2 md:tw-py-3 tw-border tw-border-transparent tw-rounded-xl tw-shadow-lg tw-text-xs md:tw-text-sm tw-font-semibold tw-text-white tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 hover:tw-from-orange-600 hover:tw-to-orange-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 hover:tw-scale-105 active:tw-scale-95">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 md:tw-h-5 md:tw-w-5 tw-mr-2"></i>
                <span class="tw-hidden md:tw-inline">Refresh</span>
                <span class="md:tw-hidden">Refresh</span>
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4 md:tw-gap-6 tw-mb-6 md:tw-mb-8">
    <!-- Total Riders -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-4 md:tw-p-6">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-gray-600">Total Riders</p>
                <p class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-gray-900 tw-mt-1"><?= number_format($stats['total_riders'] ?? 0) ?></p>
            </div>
            <div class="tw-p-2 md:tw-p-3 tw-rounded-xl tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600">
                <i data-feather="users" class="tw-h-5 tw-w-5 md:tw-h-6 md:tw-w-6 tw-text-white"></i>
            </div>
        </div>
    </div>

    <!-- Online Riders -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-4 md:tw-p-6">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-gray-600">Online Now</p>
                <p class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-gray-900 tw-mt-1"><?= number_format($stats['online_riders'] ?? 0) ?></p>
            </div>
            <div class="tw-p-2 md:tw-p-3 tw-rounded-xl tw-bg-gradient-to-r tw-from-green-500 tw-to-green-600">
                <i data-feather="radio" class="tw-h-5 tw-w-5 md:tw-h-6 md:tw-w-6 tw-text-white"></i>
            </div>
        </div>
    </div>

    <!-- Active Deliveries -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-4 md:tw-p-6">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-gray-600">Active Deliveries</p>
                <p class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-gray-900 tw-mt-1"><?= number_format($stats['active_deliveries'] ?? 0) ?></p>
            </div>
            <div class="tw-p-2 md:tw-p-3 tw-rounded-xl tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600">
                <i data-feather="truck" class="tw-h-5 tw-w-5 md:tw-h-6 md:tw-w-6 tw-text-white"></i>
            </div>
        </div>
    </div>

    <!-- Average Rating -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-4 md:tw-p-6">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-gray-600">Avg Rating</p>
                <p class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-gray-900 tw-mt-1"><?= number_format($stats['avg_rating'] ?? 0, 1) ?> ⭐</p>
            </div>
            <div class="tw-p-2 md:tw-p-3 tw-rounded-xl tw-bg-gradient-to-r tw-from-yellow-500 tw-to-yellow-600">
                <i data-feather="star" class="tw-h-5 tw-w-5 md:tw-h-6 md:tw-w-6 tw-text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Earnings Overview -->
<div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-4 md:tw-p-6 tw-mb-6 md:tw-mb-8">
    <h3 class="tw-text-base md:tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Earnings Overview</h3>
    <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4">
        <div class="tw-text-center tw-p-3 md:tw-p-4 tw-bg-gray-50 tw-rounded-xl">
            <p class="tw-text-xs tw-text-gray-600 tw-mb-1">Today</p>
            <p class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-green-600"><?= number_format($stats['today_earnings'] ?? 0) ?> XAF</p>
        </div>
        <div class="tw-text-center tw-p-3 md:tw-p-4 tw-bg-gray-50 tw-rounded-xl">
            <p class="tw-text-xs tw-text-gray-600 tw-mb-1">This Week</p>
            <p class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-blue-600"><?= number_format($stats['week_earnings'] ?? 0) ?> XAF</p>
        </div>
        <div class="tw-text-center tw-p-3 md:tw-p-4 tw-bg-gray-50 tw-rounded-xl">
            <p class="tw-text-xs tw-text-gray-600 tw-mb-1">This Month</p>
            <p class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-purple-600"><?= number_format($stats['month_earnings'] ?? 0) ?> XAF</p>
        </div>
        <div class="tw-text-center tw-p-3 md:tw-p-4 tw-bg-gray-50 tw-rounded-xl">
            <p class="tw-text-xs tw-text-gray-600 tw-mb-1">All Time</p>
            <p class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-orange-600"><?= number_format($stats['total_earnings'] ?? 0) ?> XAF</p>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-4 md:tw-p-6 tw-mb-6">
    <form method="GET" action="<?= url('/admin/riders') ?>" class="tw-space-y-4 md:tw-space-y-0 md:tw-grid md:tw-grid-cols-4 md:tw-gap-4">
        <!-- Search -->
        <div>
            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-700 tw-mb-2">Search</label>
            <div class="tw-relative">
                <input type="text" 
                       name="search" 
                       value="<?= htmlspecialchars($search ?? '') ?>"
                       placeholder="Name, email, phone..."
                       class="tw-w-full tw-pl-10 tw-pr-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-text-sm">
                <i data-feather="search" class="tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-h-4 tw-w-4 tw-text-gray-400"></i>
            </div>
        </div>

        <!-- Status Filter -->
        <div>
            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-700 tw-mb-2">Status</label>
            <select name="status" 
                    class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-text-sm">
                <option value="all" <?= ($status ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </div>

        <!-- Availability Filter -->
        <div>
            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-700 tw-mb-2">Availability</label>
            <select name="availability" 
                    class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-text-sm">
                <option value="all" <?= ($availability ?? 'all') === 'all' ? 'selected' : '' ?>>All</option>
                <option value="online" <?= ($availability ?? '') === 'online' ? 'selected' : '' ?>>Online</option>
                <option value="offline" <?= ($availability ?? '') === 'offline' ? 'selected' : '' ?>>Offline</option>
            </select>
        </div>

        <!-- Filter Button -->
        <div class="tw-flex tw-items-end">
            <button type="submit" 
                    class="tw-w-full tw-px-4 tw-py-2 tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 tw-text-white tw-rounded-xl tw-text-sm tw-font-semibold hover:tw-from-orange-600 hover:tw-to-orange-700 tw-transition-all tw-duration-200 hover:tw-scale-105 active:tw-scale-95">
                <i data-feather="filter" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Riders Table -->
<div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-overflow-hidden">
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Rider</th>
                    <th class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider tw-hidden md:tw-table-cell">Contact</th>
                    <th class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider tw-hidden lg:tw-table-cell">Deliveries</th>
                    <th class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider tw-hidden lg:tw-table-cell">Rating</th>
                    <th class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider tw-hidden xl:tw-table-cell">Earnings</th>
                    <th class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="riders-table-body">
                <?php if (!empty($riders)): ?>
                    <?php foreach ($riders as $rider): ?>
                    <tr class="hover:tw-bg-gray-50 tw-transition-colors" data-rider-id="<?= $rider['id'] ?>">
                        <td class="tw-px-4 md:tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <div class="tw-flex tw-items-center">
                                <div class="tw-relative tw-flex-shrink-0 tw-h-10 tw-w-10">
                                    <?php if (!empty($rider['profile_image'])):
                                        $profileImageUrl = imageUrl($rider['profile_image']);
                                    ?>
                                        <img class="tw-h-10 tw-w-10 tw-rounded-full tw-object-cover"
                                             src="<?= htmlspecialchars($profileImageUrl) ?>"
                                             alt="<?= htmlspecialchars($rider['first_name'] . ' ' . $rider['last_name']) ?>">
                                    <?php else: ?>
                                        <div class="tw-h-10 tw-w-10 tw-rounded-full tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 tw-flex tw-items-center tw-justify-center">
                                            <span class="tw-text-white tw-font-semibold tw-text-sm">
                                                <?= strtoupper(substr($rider['first_name'], 0, 1) . substr($rider['last_name'], 0, 1)) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="tw-absolute tw-bottom-0 tw-right-0 tw-block tw-h-3 tw-w-3 tw-rounded-full tw-ring-2 tw-ring-white <?= getAvailabilityBadge($rider['is_online'] ?? 0) ?>"></span>
                                </div>
                                <div class="tw-ml-3">
                                    <p class="tw-text-sm tw-font-medium tw-text-gray-900">
                                        <?= htmlspecialchars($rider['first_name'] . ' ' . $rider['last_name']) ?>
                                    </p>
                                    <p class="tw-text-xs tw-text-gray-500 md:tw-hidden">
                                        <?= htmlspecialchars($rider['email']) ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="tw-px-4 md:tw-px-6 tw-py-4 tw-whitespace-nowrap tw-hidden md:tw-table-cell">
                            <div class="tw-text-sm tw-text-gray-900"><?= htmlspecialchars($rider['email']) ?></div>
                            <div class="tw-text-xs tw-text-gray-500"><?= htmlspecialchars($rider['phone'] ?? 'N/A') ?></div>
                        </td>
                        <td class="tw-px-4 md:tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <div class="tw-flex tw-flex-col tw-gap-1">
                                <span class="tw-px-2 tw-py-1 tw-inline-flex tw-text-xs tw-leading-5 tw-font-semibold tw-rounded-full <?= getStatusBadge($rider['status']) ?>">
                                    <?= ucfirst($rider['status']) ?>
                                </span>
                                <?php if ($rider['is_available'] ?? false): ?>
                                    <span class="tw-px-2 tw-py-1 tw-inline-flex tw-text-xs tw-leading-5 tw-font-semibold tw-rounded-full tw-bg-green-100 tw-text-green-800">
                                        Available
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="tw-px-4 md:tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900 tw-hidden lg:tw-table-cell">
                            <div class="tw-flex tw-flex-col">
                                <span class="tw-font-semibold"><?= number_format($rider['completed_deliveries'] ?? 0) ?></span>
                                <span class="tw-text-xs tw-text-gray-500">of <?= number_format($rider['total_deliveries'] ?? 0) ?> total</span>
                            </div>
                        </td>
                        <td class="tw-px-4 md:tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900 tw-hidden lg:tw-table-cell">
                            <div class="tw-flex tw-items-center">
                                <i data-feather="star" class="tw-h-4 tw-w-4 tw-text-yellow-400 tw-mr-1"></i>
                                <span class="tw-font-semibold"><?= number_format($rider['avg_rating'] ?? 0, 1) ?></span>
                            </div>
                        </td>
                        <td class="tw-px-4 md:tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900 tw-hidden xl:tw-table-cell">
                            <div class="tw-flex tw-flex-col">
                                <span class="tw-font-semibold tw-text-green-600"><?= number_format($rider['total_earnings'] ?? 0) ?> XAF</span>
                                <span class="tw-text-xs tw-text-gray-500">Today: <?= number_format($rider['today_earnings'] ?? 0) ?> XAF</span>
                            </div>
                        </td>
                        <td class="tw-px-4 md:tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-text-sm tw-font-medium">
                            <div class="tw-flex tw-justify-end tw-gap-2">
                                <button onclick="toggleRiderStatus(<?= $rider['id'] ?>, '<?= $rider['status'] ?>')"
                                        class="tw-text-orange-600 hover:tw-text-orange-900 tw-transition-colors"
                                        title="Change Status">
                                    <i data-feather="settings" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <?php if (!empty($rider['latitude']) && !empty($rider['longitude'])): ?>
                                <button onclick="viewRiderLocation(<?= $rider['id'] ?>, <?= $rider['latitude'] ?>, <?= $rider['longitude'] ?>)"
                                        class="tw-text-green-600 hover:tw-text-green-900 tw-transition-colors"
                                        title="View Location">
                                    <i data-feather="map-pin" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <?php endif; ?>
                                <button onclick="sendMessageToRider(<?= $rider['id'] ?>)"
                                        class="tw-text-purple-600 hover:tw-text-purple-900 tw-transition-colors"
                                        title="Send Message">
                                    <i data-feather="message-circle" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <button onclick="deleteRider(<?= $rider['id'] ?>, '<?= htmlspecialchars($rider['first_name'] . ' ' . $rider['last_name']) ?>')"
                                        class="tw-text-red-600 hover:tw-text-red-900 tw-transition-colors"
                                        title="Delete Rider">
                                    <i data-feather="trash-2" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="tw-px-6 tw-py-12 tw-text-center">
                            <div class="tw-flex tw-flex-col tw-items-center tw-justify-center">
                                <i data-feather="inbox" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-3"></i>
                                <p class="tw-text-gray-500 tw-text-sm">No riders found</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Rider Confirmation Modal -->
<div id="deleteRiderModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 md:tw-w-96 tw-shadow-lg tw-rounded-2xl tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Delete Rider</h3>
            <button onclick="closeDeleteModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-6 tw-w-6"></i>
            </button>
        </div>
        <div class="tw-space-y-4">
            <div class="tw-flex tw-items-center tw-p-4 tw-bg-red-50 tw-rounded-xl">
                <i data-feather="alert-triangle" class="tw-h-8 tw-w-8 tw-text-red-600 tw-mr-3"></i>
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-red-800">Warning</p>
                    <p class="tw-text-sm tw-text-red-600">This action cannot be undone.</p>
                </div>
            </div>
            <p class="tw-text-gray-700">
                Are you sure you want to delete <span id="deleteRiderName" class="tw-font-semibold"></span>? 
                This will permanently remove the rider and all associated data.
            </p>
            <div class="tw-flex tw-space-x-3 tw-pt-4">
                <button onclick="closeDeleteModal()" 
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-font-medium hover:tw-bg-gray-200 tw-transition-colors">
                    Cancel
                </button>
                <button onclick="confirmDeleteRider()" 
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-red-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-red-700 tw-transition-colors">
                    Delete Rider
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div id="statusModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 md:tw-w-96 tw-shadow-lg tw-rounded-2xl tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Change Rider Status</h3>
            <button onclick="closeStatusModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-6 tw-w-6"></i>
            </button>
        </div>
        <form id="statusForm" class="tw-space-y-4">
            <input type="hidden" id="statusRiderId" name="rider_id">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">New Status</label>
                <select id="newStatus" name="status" class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div class="tw-flex tw-gap-3">
                <button type="button" onclick="closeStatusModal()"
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-font-semibold tw-text-gray-700 hover:tw-bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 tw-text-white tw-rounded-xl tw-text-sm tw-font-semibold hover:tw-from-orange-600 hover:tw-to-orange-700">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Delete rider
function deleteRider(riderId, riderName) {
    document.getElementById('deleteRiderName').textContent = riderName;
    document.getElementById('deleteRiderModal').setAttribute('data-rider-id', riderId);
    document.getElementById('deleteRiderModal').classList.remove('tw-hidden');
}

// Close delete modal
function closeDeleteModal() {
    document.getElementById('deleteRiderModal').classList.add('tw-hidden');
}

// Confirm delete rider
function confirmDeleteRider() {
    const modal = document.getElementById('deleteRiderModal');
    const riderId = modal.getAttribute('data-rider-id');
    
    if (!riderId) {
        alert('Error: Rider ID not found');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-animate-spin tw-mr-2"></i>Deleting...';
    
    fetch(`<?= url('/admin/riders/delete') ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ rider_id: riderId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
            closeDeleteModal();
            // Remove the row from the table
            const row = document.querySelector(`tr[data-rider-id="${riderId}"]`);
            if (row) {
                row.remove();
            }
            // Show success message
            alert('Rider deleted successfully');
            // Refresh the page to update statistics
            location.reload();
            } else {
            alert('Error: ' + (data.message || 'Failed to delete rider'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
        alert('An error occurred while deleting rider');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Toggle rider status
function toggleRiderStatus(riderId, currentStatus) {
    document.getElementById('statusRiderId').value = riderId;
    document.getElementById('newStatus').value = currentStatus;
    document.getElementById('statusModal').classList.remove('tw-hidden');
    feather.replace();
}

// Close status modal
function closeStatusModal() {
    document.getElementById('statusModal').classList.add('tw-hidden');
}

// Handle status form submission
document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;

    button.disabled = true;
    button.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-animate-spin tw-inline"></i> Updating...';
    feather.replace();

    fetch('<?= url('/admin/riders/update-status') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Rider status updated successfully!');
            location.reload();
        } else {
            alert('Failed to update status: ' + (data.message || 'Unknown error'));
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating status');
        button.disabled = false;
        button.innerHTML = originalText;
    });
});

// View rider location on map
function viewRiderLocation(riderId, latitude, longitude) {
    const mapUrl = `https://www.google.com/maps?q=${latitude},${longitude}&z=15`;
    window.open(mapUrl, '_blank');
}

// Send message to rider
function sendMessageToRider(riderId) {
    // For now, show a simple prompt. In production, this could open a messaging modal
    const message = prompt('Enter message to send to rider:');
    if (message && message.trim()) {
        alert('Message functionality will be implemented. Message: ' + message);
        // TODO: Implement actual messaging via API
        // fetch('/admin/riders/send-message', {
        //     method: 'POST',
        //     headers: {'Content-Type': 'application/json'},
        //     body: JSON.stringify({rider_id: riderId, message: message})
        // });
    }
}


// Export riders data
function exportRiders() {
    const riders = <?= json_encode($riders ?? []) ?>;

    const csvContent = [
        ['ID', 'Name', 'Email', 'Phone', 'Status', 'Available', 'Total Deliveries', 'Completed', 'Rating', 'Total Earnings'],
        ...riders.map(r => [
            r.id,
            `${r.first_name} ${r.last_name}`,
            r.email,
            r.phone || 'N/A',
            r.status,
            r.is_available ? 'Yes' : 'No',
            r.total_deliveries || 0,
            r.completed_deliveries || 0,
            parseFloat(r.avg_rating || 0).toFixed(1),
            r.total_earnings || 0
        ])
    ].map(row => row.join(',')).join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `riders-${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Refresh data
function refreshData() {
    location.reload();
}

// Close modals on outside click
window.onclick = function(event) {
    const deleteModal = document.getElementById('deleteRiderModal');
    const statusModal = document.getElementById('statusModal');

    if (event.target === deleteModal) {
        closeDeleteModal();
    }
    if (event.target === statusModal) {
        closeStatusModal();
    }
}
</script>

