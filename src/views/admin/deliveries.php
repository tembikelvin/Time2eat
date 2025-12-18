<?php
/**
 * Admin Deliveries Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'deliveries';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Delivery Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Track and manage all deliveries across the platform
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button onclick="openLiveMap()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors">
                <i data-feather="map" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Live Map
            </button>
            <button onclick="refreshDeliveries()" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Refresh
            </button>
        </div>
    </div>
</div>

<!-- Delivery Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Deliveries</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['active_deliveries'] ?? 67) ?></p>
                <p class="tw-text-sm tw-text-blue-600">Real-time</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="truck" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Online Riders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['online_riders'] ?? 89) ?></p>
                <p class="tw-text-sm tw-text-green-600">Available now</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Completed Today</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['completed_today'] ?? 234) ?></p>
                <p class="tw-text-sm tw-text-green-600">+12% vs yesterday</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Delivery Time</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= $stats['avg_delivery_time'] ?? '28' ?></p>
                <p class="tw-text-sm tw-text-gray-500">minutes</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Live Delivery Status -->
<div class="tw-bg-gradient-to-r tw-from-green-50 tw-to-blue-50 tw-border tw-border-green-200 tw-rounded-xl tw-p-6 tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg">
                <i data-feather="navigation" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
            <div class="tw-ml-4">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Live Delivery Tracking</h3>
                <p class="tw-text-sm tw-text-gray-600"><?= number_format($stats['active_deliveries'] ?? 0) ?> deliveries currently in progress</p>
            </div>
        </div>
        <div class="tw-flex tw-space-x-4">
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-orange-600"><?= number_format($stats['picked_up'] ?? 0) ?></div>
                <div class="tw-text-xs tw-text-gray-600">Picked Up</div>
            </div>
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-blue-600"><?= number_format($stats['en_route'] ?? 0) ?></div>
                <div class="tw-text-xs tw-text-gray-600">En Route</div>
            </div>
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-green-600"><?= number_format($stats['near_destination'] ?? 0) ?></div>
                <div class="tw-text-xs tw-text-gray-600">Near Destination</div>
            </div>
        </div>
    </div>
</div>

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
                    <input type="text" id="delivery-search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-leading-5 tw-bg-white tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-1 focus:tw-ring-primary-500 focus:tw-border-primary-500" placeholder="Search by delivery ID, rider, or customer...">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="tw-flex tw-space-x-4">
                <select id="status-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Status</option>
                    <option value="assigned">Assigned</option>
                    <option value="picked_up">Picked Up</option>
                    <option value="en_route">En Route</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                </select>
                
                <select id="rider-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Riders</option>
                    <option value="james">James Rider</option>
                    <option value="mary">Mary Rider</option>
                    <option value="paul">Paul Rider</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Deliveries Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">All Deliveries</h2>
            <div class="tw-flex tw-items-center tw-space-x-4">
                <span class="tw-text-sm tw-text-gray-500">Showing 1-20 of <?= number_format($totalDeliveries ?? 1247) ?> deliveries</span>
                <div class="tw-flex tw-items-center tw-space-x-2">
                    <button onclick="previousPage()" class="tw-p-2 tw-rounded-md tw-text-gray-400 hover:tw-text-gray-600 hover:tw-bg-gray-100 tw-transition-colors" title="Previous Page">
                        <i data-feather="chevron-left" class="tw-h-4 tw-w-4"></i>
                    </button>
                    <span class="tw-text-sm tw-text-gray-700 tw-px-2">Page 1 of 63</span>
                    <button onclick="nextPage()" class="tw-p-2 tw-rounded-md tw-text-gray-400 hover:tw-text-gray-600 hover:tw-bg-gray-100 tw-transition-colors" title="Next Page">
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
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Delivery ID</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Order</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Rider</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Distance</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Time</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="deliveries-table-body">
                <?php if (!empty($deliveries)): ?>
                    <?php foreach ($deliveries as $delivery): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($delivery['id'] ?? $delivery['order_id'] ?? 'N/A') ?></div>
                        <div class="tw-text-sm tw-text-gray-500">Fee: <?= number_format($delivery['delivery_fee'] ?? 0) ?> XAF</div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <?php 
                            $customerName = $delivery['customer_name'] ?? $delivery['customer'] ?? 'Unknown Customer';
                            $customerEmail = $delivery['customer_email'] ?? 'No email';
                            $restaurantName = $delivery['restaurant_name'] ?? $delivery['restaurant'] ?? 'Unknown Restaurant';
                            ?>
                            <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-green-500 tw-to-teal-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-white tw-font-semibold tw-text-xs">
                                    <?= strtoupper(substr($customerName, 0, 1)) ?>
                                </span>
                            </div>
                            <div class="tw-ml-3">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($customerName) ?></div>
                                <div class="tw-text-sm tw-text-gray-500"><?= e($customerEmail) ?></div>
                                <div class="tw-text-xs tw-text-gray-400"><?= e($restaurantName) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <?php 
                        $riderName = $delivery['rider_name'] ?? $delivery['rider'] ?? null;
                        $riderContact = $delivery['rider_email'] ?? $delivery['rider_phone'] ?? null;
                        ?>
                        <?php if (!empty($riderName)): ?>
                            <div class="tw-flex tw-items-center">
                                <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-purple-500 tw-to-pink-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                    <span class="tw-text-white tw-font-semibold tw-text-xs">
                                        <?= strtoupper(substr($riderName, 0, 1)) ?>
                                    </span>
                                </div>
                                <div class="tw-ml-3">
                                    <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($riderName) ?></div>
                                    <div class="tw-text-sm tw-text-gray-500"><?= e($riderContact ?? 'No contact') ?></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="tw-text-sm tw-text-gray-500">Not assigned</span>
                        <?php endif; ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                            <?php 
                            switch($delivery['status']) {
                                case 'assigned': echo 'tw-bg-yellow-100 tw-text-yellow-800'; break;
                                case 'picked_up': echo 'tw-bg-blue-100 tw-text-blue-800'; break;
                                case 'en_route': echo 'tw-bg-purple-100 tw-text-purple-800'; break;
                                case 'delivered': echo 'tw-bg-green-100 tw-text-green-800'; break;
                                case 'failed': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                default: echo 'tw-bg-gray-100 tw-text-gray-800';
                            }
                            ?>">
                            <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                        <?= !empty($delivery['distance']) ? number_format($delivery['distance'], 1) . ' km' : 'N/A' ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-text-gray-900"><?= e($delivery['estimated_time'] ?? 'N/A') ?></div>
                        <div class="tw-text-sm tw-text-gray-500">
                            Pickup: <?= !empty($delivery['pickup_time']) ? date('M j, H:i', strtotime($delivery['pickup_time'])) : 'Pending' ?>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-1">
                            <button class="tw-p-2 tw-rounded-md tw-text-primary-600 hover:tw-text-primary-900 hover:tw-bg-primary-50 tw-transition-colors" onclick="trackDelivery('<?= $delivery['id'] ?>')" title="Track Delivery">
                                <i data-feather="map-pin" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <?php 
                            $riderName = $delivery['rider_name'] ?? $delivery['rider'] ?? null;
                            $riderContact = $delivery['rider_email'] ?? $delivery['rider_phone'] ?? null;
                            ?>
                            <?php if (!empty($riderName) && !empty($riderContact)): ?>
                                <button class="tw-p-2 tw-rounded-md tw-text-green-600 hover:tw-text-green-900 hover:tw-bg-green-50 tw-transition-colors" onclick="callRider('<?= e($riderContact) ?>')" title="Call Rider">
                                    <i data-feather="phone" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                            <?php if (in_array($delivery['status'], ['assigned', 'picked_up', 'en_route'])): ?>
                                <button class="tw-p-2 tw-rounded-md tw-text-blue-600 hover:tw-text-blue-900 hover:tw-bg-blue-50 tw-transition-colors" onclick="reassignDelivery('<?= $delivery['id'] ?>')" title="Reassign Delivery">
                                    <i data-feather="user-check" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($delivery['status'] === 'failed'): ?>
                                <button class="tw-p-2 tw-rounded-md tw-text-orange-600 hover:tw-text-orange-900 hover:tw-bg-orange-50 tw-transition-colors" onclick="retryDelivery('<?= $delivery['id'] ?>')" title="Retry Delivery">
                                    <i data-feather="rotate-cw" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" class="tw-px-6 tw-py-16 tw-text-center">
                        <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-min-h-[300px]">
                            <!-- Icon -->
                            <div class="tw-mb-6">
                                <div class="tw-w-20 tw-h-20 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto">
                                    <i data-feather="truck" class="tw-h-10 tw-w-10 tw-text-gray-400"></i>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="tw-text-center tw-max-w-md">
                                <h3 class="tw-text-xl tw-font-semibold tw-text-gray-900 tw-mb-3">No Deliveries Found</h3>
                                <p class="tw-text-gray-500 tw-mb-8 tw-leading-relaxed">
                                    There are currently no deliveries to display. All active deliveries will appear here when orders are placed.
                                </p>
                                
                                <!-- Action Buttons -->
                                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3 tw-justify-center">
                                    <button onclick="refreshDeliveries()" 
                                            class="tw-bg-primary-600 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-flex tw-items-center tw-justify-center tw-shadow-sm">
                                        <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                        Refresh Page
                                    </button>
                                    <button onclick="window.location.reload()" 
                                            class="tw-bg-white tw-border tw-border-gray-300 tw-text-gray-700 tw-px-6 tw-py-3 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-50 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                        <i data-feather="external-link" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                        Reload
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
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
                    Showing <span class="tw-font-medium">1</span> to <span class="tw-font-medium">20</span> of <span class="tw-font-medium"><?= number_format($totalDeliveries ?? 1247) ?></span> results
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
// Delivery management functions
function trackDelivery(deliveryId) {
    window.location.href = `/admin/deliveries/${deliveryId}/track`;
}

function callRider(phone) {
    if (phone) {
        window.open(`tel:${phone}`, '_self');
    }
}

function reassignDelivery(deliveryId) {
    // Open modal to select new rider
    if (confirm('Do you want to reassign this delivery to another rider?')) {
        // In a real implementation, this would open a modal with available riders
        window.location.href = `/admin/deliveries/${deliveryId}/reassign`;
    }
}

function retryDelivery(deliveryId) {
    if (confirm('Are you sure you want to retry this delivery?')) {
        fetch(`/admin/deliveries/${deliveryId}/retry`, {
            method: 'POST',
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
                alert('Error retrying delivery: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while retrying the delivery.');
        });
    }
}

// Search and filter functionality
document.getElementById('delivery-search').addEventListener('input', function() {
    filterDeliveries();
});

document.getElementById('status-filter').addEventListener('change', function() {
    filterDeliveries();
});

document.getElementById('rider-filter').addEventListener('change', function() {
    filterDeliveries();
});

function filterDeliveries() {
    const search = document.getElementById('delivery-search').value;
    const status = document.getElementById('status-filter').value;
    const rider = document.getElementById('rider-filter').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (rider) params.append('rider', rider);
    
    // Update URL and reload content
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.history.pushState({}, '', newUrl);
    
    // In a real implementation, you would fetch filtered data via AJAX
    // For now, we'll just reload the page
    // location.reload();
}

function refreshDeliveries() {
    // Clear all filters
    document.getElementById('delivery-search').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('rider-filter').value = '';
    
    // Reload the page to get fresh data
    window.location.reload();
}

function previousPage() {
    // In a real implementation, you would navigate to the previous page
    console.log('Previous page clicked');
    // For now, just show an alert
    alert('Previous page functionality would be implemented here');
}

function nextPage() {
    // In a real implementation, you would navigate to the next page
    console.log('Next page clicked');
    // For now, just show an alert
    alert('Next page functionality would be implemented here');
}

function openLiveMap() {
    // In a real implementation, you would open a live map view
    console.log('Live map clicked');
    // For now, just show an alert
    alert('Live map functionality would be implemented here');
}

function trackDelivery(deliveryId) {
    console.log('Track delivery:', deliveryId);
    alert('Track delivery functionality would be implemented here for ID: ' + deliveryId);
}

function callRider(contact) {
    console.log('Call rider:', contact);
    // In a real implementation, you would initiate a call or open a contact app
    if (contact.includes('@')) {
        window.location.href = `mailto:${contact}`;
    } else {
        window.location.href = `tel:${contact}`;
    }
}

function reassignDelivery(deliveryId) {
    console.log('Reassign delivery:', deliveryId);
    alert('Reassign delivery functionality would be implemented here for ID: ' + deliveryId);
}

function retryDelivery(deliveryId) {
    console.log('Retry delivery:', deliveryId);
    alert('Retry delivery functionality would be implemented here for ID: ' + deliveryId);
}

// Auto-refresh for real-time updates
setInterval(function() {
    // In a real implementation, you would fetch updated delivery data
    // and update the table without full page reload
    console.log('Auto-refreshing delivery data...');
}, 15000); // Refresh every 15 seconds

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
