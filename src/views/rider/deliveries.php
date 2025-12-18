<?php
$title = $title ?? 'Active Deliveries - Time2Eat';
$user = $user ?? null;
$deliveries = $deliveries ?? [];
$statusCounts = $statusCounts ?? [];
$currentStatus = $currentStatus ?? 'active';
$currentPage = $currentPage ?? 'deliveries';
$paginationPage = $paginationPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalDeliveries = $totalDeliveries ?? 0;
$todayStats = $todayStats ?? [];
$recentActivity = $recentActivity ?? [];
?>

<!-- Mobile-First Header -->
<div class="tw-bg-gradient-to-r tw-from-orange-600 tw-to-red-600 tw-rounded-2xl tw-p-6 tw-mb-6 tw-text-white">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-mb-1">Active Deliveries</h1>
            <p class="tw-text-orange-100 tw-text-sm">Manage your current delivery assignments</p>
        </div>
        <div class="tw-p-3 tw-bg-white tw-bg-opacity-20 tw-backdrop-blur-sm tw-rounded-xl">
            <i data-feather="truck" class="tw-h-8 tw-w-8"></i>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="tw-grid tw-grid-cols-3 tw-gap-4">
        <div class="tw-bg-white tw-bg-opacity-10 tw-backdrop-blur-sm tw-rounded-xl tw-p-3 tw-text-center">
            <div class="tw-text-2xl tw-font-bold"><?= $statusCounts['active'] ?? 0 ?></div>
            <div class="tw-text-xs tw-text-orange-100">Active</div>
        </div>
        <div class="tw-bg-white tw-bg-opacity-10 tw-backdrop-blur-sm tw-rounded-xl tw-p-3 tw-text-center">
            <div class="tw-text-2xl tw-font-bold"><?= $statusCounts['completed'] ?? 0 ?></div>
            <div class="tw-text-xs tw-text-orange-100">Completed</div>
        </div>
        <div class="tw-bg-white tw-bg-opacity-10 tw-backdrop-blur-sm tw-rounded-xl tw-p-3 tw-text-center">
            <div class="tw-text-2xl tw-font-bold"><?= $statusCounts['total'] ?? 0 ?></div>
            <div class="tw-text-xs tw-text-orange-100">Total</div>
        </div>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-4 tw-mb-6">
    <div class="tw-flex tw-space-x-1 tw-bg-gray-100 tw-rounded-xl tw-p-1">
        <a href="<?= url('/rider/deliveries?status=active') ?>" 
           class="tw-flex-1 tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all tw-duration-200 <?php if ($currentStatus === 'active'): ?>tw-bg-orange-500 tw-text-white tw-shadow-sm<?php else: ?>tw-text-gray-600 hover:tw-text-gray-900<?php endif; ?>">
            <i data-feather="clock" class="tw-h-4 tw-w-4 tw-mr-2"></i>
            Active
            <?php if (isset($statusCounts['active']) && $statusCounts['active'] > 0): ?>
                <span class="tw-ml-2 tw-py-0.5 tw-px-2 tw-rounded-full tw-text-xs tw-font-medium <?php if ($currentStatus === 'active'): ?>tw-bg-white tw-bg-opacity-30 tw-text-white<?php else: ?>tw-bg-orange-100 tw-text-orange-600<?php endif; ?>">
                    <?= $statusCounts['active'] ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="<?= url('/rider/deliveries?status=completed') ?>" 
           class="tw-flex-1 tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all tw-duration-200 <?php if ($currentStatus === 'completed'): ?>tw-bg-green-500 tw-text-white tw-shadow-sm<?php else: ?>tw-text-gray-600 hover:tw-text-gray-900<?php endif; ?>">
            <i data-feather="check-circle" class="tw-h-4 tw-w-4 tw-mr-2"></i>
            Completed
            <?php if (isset($statusCounts['completed']) && $statusCounts['completed'] > 0): ?>
                <span class="tw-ml-2 tw-py-0.5 tw-px-2 tw-rounded-full tw-text-xs tw-font-medium <?php if ($currentStatus === 'completed'): ?>tw-bg-white tw-bg-opacity-30 tw-text-white<?php else: ?>tw-bg-green-100 tw-text-green-600<?php endif; ?>">
                    <?= $statusCounts['completed'] ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="<?= url('/rider/deliveries?status=all') ?>" 
           class="tw-flex-1 tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all tw-duration-200 <?php if ($currentStatus === 'all'): ?>tw-bg-blue-500 tw-text-white tw-shadow-sm<?php else: ?>tw-text-gray-600 hover:tw-text-gray-900<?php endif; ?>">
            <i data-feather="list" class="tw-h-4 tw-w-4 tw-mr-2"></i>
            All
            <?php if (isset($statusCounts['total']) && $statusCounts['total'] > 0): ?>
                <span class="tw-ml-2 tw-py-0.5 tw-px-2 tw-rounded-full tw-text-xs tw-font-medium <?php if ($currentStatus === 'all'): ?>tw-bg-white tw-bg-opacity-30 tw-text-white<?php else: ?>tw-bg-blue-100 tw-text-blue-600<?php endif; ?>">
                    <?= $statusCounts['total'] ?>
                </span>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- Today's Performance Summary -->
<?php if (!empty($todayStats)): ?>
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-6 tw-mb-6">
    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
        <i data-feather="trending-up" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-green-500"></i>
        Today's Performance
    </h2>
    <div class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-4 tw-gap-4">
        <div class="tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $todayStats['deliveries'] ?? 0 ?></div>
            <div class="tw-text-sm tw-text-gray-500">Deliveries</div>
        </div>
        <div class="tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($todayStats['earnings'] ?? 0) ?></div>
            <div class="tw-text-sm tw-text-gray-500">Earnings (XAF)</div>
        </div>
        <div class="tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $todayStats['rating'] ?? 0 ?></div>
            <div class="tw-text-sm tw-text-gray-500">Avg Rating</div>
        </div>
        <div class="tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $todayStats['hours'] ?? 0 ?></div>
            <div class="tw-text-sm tw-text-gray-500">Hours</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Deliveries List -->
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-mb-6">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                <i data-feather="package" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-orange-500"></i>
                <?= ucfirst($currentStatus) ?> Deliveries
            </h2>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <span class="tw-text-sm tw-text-gray-500"><?= $totalDeliveries ?> total</span>
                <button onclick="refreshDeliveries()" class="tw-p-2 tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors">
                    <i data-feather="refresh-cw" class="tw-h-4 tw-w-4"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="tw-p-6">
        <?php if (empty($deliveries)): ?>
            <!-- Empty State -->
            <div class="tw-text-center tw-py-12">
                <div class="tw-mx-auto tw-h-20 tw-w-20 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                    <i data-feather="truck" class="tw-h-10 tw-w-10 tw-text-gray-400"></i>
                </div>
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No deliveries found</h3>
                <p class="tw-text-sm tw-text-gray-500 tw-mb-6">
                    <?php if ($currentStatus === 'active'): ?>
                        You don't have any active deliveries at the moment.
                    <?php else: ?>
                        No deliveries found for the selected filter.
                    <?php endif; ?>
                </p>
                <?php if ($currentStatus === 'active'): ?>
                    <a href="<?= url('/rider/available') ?>" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-text-white tw-rounded-xl tw-text-sm tw-font-medium hover:tw-from-orange-600 hover:tw-to-red-600 tw-transition-all tw-duration-200 tw-shadow-lg">
                        <i data-feather="search" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Find Available Orders
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Deliveries Cards -->
            <div class="tw-space-y-4">
                <?php foreach ($deliveries as $delivery): ?>
                    <div class="tw-border tw-border-gray-200 tw-rounded-2xl tw-p-6 hover:tw-border-orange-300 tw-transition-all tw-duration-200 tw-shadow-sm hover:tw-shadow-md">
                        <!-- Delivery Header -->
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-h-12 tw-w-12 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                                    <span class="tw-text-white tw-font-bold tw-text-sm">
                                        #<?= str_pad($delivery['id'], 3, '0', STR_PAD_LEFT) ?>
                                    </span>
                                </div>
                                <div>
                                    <h3 class="tw-font-semibold tw-text-gray-900 tw-text-lg">
                                        <?= e($delivery['restaurant_name'] ?? 'Restaurant') ?>
                                    </h3>
                                    <p class="tw-text-sm tw-text-gray-500">
                                        Order #<?= e($delivery['order_number'] ?? 'N/A') ?>
                                    </p>
                                </div>
                            </div>
                            <div class="tw-text-right">
                                <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-shadow-sm
                                    <?php
                                    switch ($delivery['status']) {
                                        case 'assigned':
                                        case 'accepted':
                                            echo 'tw-bg-blue-100 tw-text-blue-800 tw-border tw-border-blue-200';
                                            break;
                                        case 'picked_up':
                                            echo 'tw-bg-yellow-100 tw-text-yellow-800 tw-border tw-border-yellow-200';
                                            break;
                                        case 'on_the_way':
                                            echo 'tw-bg-purple-100 tw-text-purple-800 tw-border tw-border-purple-200';
                                            break;
                                        case 'delivered':
                                            echo 'tw-bg-green-100 tw-text-green-800 tw-border tw-border-green-200';
                                            break;
                                        case 'cancelled':
                                            echo 'tw-bg-red-100 tw-text-red-800 tw-border tw-border-red-200';
                                            break;
                                        default:
                                            echo 'tw-bg-gray-100 tw-text-gray-800 tw-border tw-border-gray-200';
                                    }
                                    ?>">
                                    <i data-feather="<?php
                                        switch ($delivery['status']) {
                                            case 'assigned':
                                            case 'accepted': echo 'clock'; break;
                                            case 'picked_up': echo 'package'; break;
                                            case 'on_the_way': echo 'truck'; break;
                                            case 'delivered': echo 'check-circle'; break;
                                            case 'cancelled': echo 'x-circle'; break;
                                            default: echo 'help-circle';
                                        }
                                    ?>" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                                    <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
                                </span>
                                <div class="tw-mt-2">
                                    <p class="tw-font-bold tw-text-gray-900 tw-text-lg">
                                        <?= number_format($delivery['delivery_fee'] ?? 0) ?> XAF
                                    </p>
                                    <p class="tw-text-xs tw-text-gray-500">
                                        <?= date('M j, H:i', strtotime($delivery['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Delivery Details -->
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-mb-4">
                            <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4">
                                <div class="tw-flex tw-items-center tw-mb-2">
                                    <i data-feather="map-pin" class="tw-h-4 tw-w-4 tw-text-orange-500 tw-mr-2"></i>
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Pickup Location</span>
                                </div>
                                <p class="tw-text-sm tw-text-gray-600 tw-ml-6"><?= e($delivery['pickup_address'] ?? 'N/A') ?></p>
                            </div>
                            <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4">
                                <div class="tw-flex tw-items-center tw-mb-2">
                                    <i data-feather="home" class="tw-h-4 tw-w-4 tw-text-green-500 tw-mr-2"></i>
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Delivery Location</span>
                                </div>
                                <p class="tw-text-sm tw-text-gray-600 tw-ml-6"><?= e($delivery['delivery_address'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                        
                        <!-- Customer Info -->
                        <div class="tw-bg-blue-50 tw-rounded-xl tw-p-4 tw-mb-4">
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <div class="tw-flex tw-items-center tw-space-x-3">
                                    <div class="tw-h-10 tw-w-10 tw-bg-blue-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                        <i data-feather="user" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    </div>
                                    <div>
                                        <p class="tw-font-medium tw-text-gray-900"><?= e($delivery['customer_name'] ?? 'Customer') ?></p>
                                        <p class="tw-text-sm tw-text-gray-500"><?= e($delivery['customer_phone'] ?? 'No phone') ?></p>
                                    </div>
                                </div>
                                <div class="tw-text-right">
                                    <p class="tw-text-sm tw-font-medium tw-text-gray-700">Distance</p>
                                    <p class="tw-text-lg tw-font-bold tw-text-gray-900"><?= number_format($delivery['distance_km'] ?? 0, 1) ?> km</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <?php if (in_array($delivery['status'], ['assigned', 'accepted', 'picked_up', 'on_the_way'])): ?>
                            <div class="tw-flex tw-space-x-3">
                                <?php if (in_array($delivery['status'], ['assigned', 'accepted'])): ?>
                                    <button onclick="pickupOrder(<?= $delivery['id'] ?>)" 
                                            class="tw-flex-1 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 hover:tw-from-orange-600 hover:tw-to-red-600 tw-text-white tw-px-4 tw-py-3 tw-rounded-xl tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-shadow-lg tw-flex tw-items-center tw-justify-center">
                                        <i data-feather="package" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                        Pick Up Order
                                    </button>
                                <?php elseif ($delivery['status'] === 'picked_up'): ?>
                                    <button onclick="startDelivery(<?= $delivery['id'] ?>)" 
                                            class="tw-flex-1 tw-bg-gradient-to-r tw-from-purple-500 tw-to-indigo-500 hover:tw-from-purple-600 hover:tw-to-indigo-600 tw-text-white tw-px-4 tw-py-3 tw-rounded-xl tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-shadow-lg tw-flex tw-items-center tw-justify-center">
                                        <i data-feather="truck" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                        Start Delivery
                                    </button>
                                <?php elseif ($delivery['status'] === 'on_the_way'): ?>
                                    <button onclick="completeDelivery(<?= $delivery['id'] ?>)" 
                                            class="tw-flex-1 tw-bg-gradient-to-r tw-from-green-500 tw-to-emerald-500 hover:tw-from-green-600 hover:tw-to-emerald-600 tw-text-white tw-px-4 tw-py-3 tw-rounded-xl tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-shadow-lg tw-flex tw-items-center tw-justify-center">
                                        <i data-feather="check-circle" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                        Complete Delivery
                                    </button>
                                <?php endif; ?>
                                
                                <button onclick="openMaps(<?= $delivery['delivery_latitude'] ?? 0 ?>, <?= $delivery['delivery_longitude'] ?? 0 ?>)" 
                                        class="tw-px-4 tw-py-3 tw-bg-blue-500 hover:tw-bg-blue-600 tw-text-white tw-rounded-xl tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-shadow-lg"
                                        title="Open in Maps">
                                    <i data-feather="map" class="tw-h-4 tw-w-4"></i>
                                </button>
                                
                                <button onclick="callCustomer('<?= e($delivery['customer_phone'] ?? '') ?>')" 
                                        class="tw-px-4 tw-py-3 tw-bg-green-500 hover:tw-bg-green-600 tw-text-white tw-rounded-xl tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-shadow-lg"
                                        title="Call Customer">
                                    <i data-feather="phone" class="tw-h-4 tw-w-4"></i>
                                </button>
                                
                                <button onclick="callRestaurant('<?= e($delivery['restaurant_phone'] ?? '') ?>')" 
                                        class="tw-px-4 tw-py-3 tw-bg-orange-500 hover:tw-bg-orange-600 tw-text-white tw-rounded-xl tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-shadow-lg"
                                        title="Call Restaurant">
                                    <i data-feather="phone-call" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </div>
                        <?php elseif ($delivery['status'] === 'delivered'): ?>
                            <div class="tw-flex tw-items-center tw-justify-center tw-p-4 tw-bg-green-50 tw-rounded-xl">
                                <div class="tw-flex tw-items-center tw-space-x-2 tw-text-green-700">
                                    <i data-feather="check-circle" class="tw-h-5 tw-w-5"></i>
                                    <span class="tw-font-medium">Order Delivered Successfully</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="tw-mt-8 tw-flex tw-items-center tw-justify-between">
                    <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-500">
                        <span>Page <?= $paginationPage ?> of <?= $totalPages ?></span>
                    </div>
                    <div class="tw-flex tw-space-x-2">
                        <?php if ($paginationPage > 1): ?>
                            <a href="<?= url('/rider/deliveries?status=' . $currentStatus . '&page=' . ($paginationPage - 1)) ?>" 
                               class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg hover:tw-bg-gray-50 tw-transition-colors">
                                <i data-feather="chevron-left" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($paginationPage < $totalPages): ?>
                            <a href="<?= url('/rider/deliveries?status=' . $currentStatus . '&page=' . ($paginationPage + 1)) ?>" 
                               class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg hover:tw-bg-gray-50 tw-transition-colors">
                                Next
                                <i data-feather="chevron-right" class="tw-h-4 tw-w-4 tw-inline tw-ml-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Activity -->
<?php if (!empty($recentActivity)): ?>
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
            <i data-feather="activity" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-blue-500"></i>
            Recent Activity
        </h2>
    </div>
    <div class="tw-p-6">
        <div class="tw-space-y-3">
            <?php foreach ($recentActivity as $activity): ?>
                <div class="tw-flex tw-items-center tw-space-x-3 tw-p-3 tw-bg-gray-50 tw-rounded-xl">
                    <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                        <i data-feather="<?= $activity['icon'] ?? 'circle' ?>" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                    </div>
                    <div class="tw-flex-1">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($activity['message']) ?></p>
                        <p class="tw-text-xs tw-text-gray-500"><?= $activity['time'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Initialize Feather icons
feather.replace();

function refreshDeliveries() {
    // Show loading state
    const refreshBtn = document.querySelector('button[onclick="refreshDeliveries()"]');
    const originalContent = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-animate-spin"></i>';
    refreshBtn.disabled = true;
    feather.replace();
    
    // Reload page
    window.location.reload();
}

// Pick up order
function pickupOrder(orderId) {
    if (confirm('Are you sure you want to pick up this order?')) {
        updateOrderStatus(orderId, 'picked_up');
    }
}

// Start delivery
function startDelivery(orderId) {
    if (confirm('Are you ready to start delivery to the customer?')) {
        updateOrderStatus(orderId, 'on_the_way');
    }
}

// Complete delivery
function completeDelivery(orderId) {
    if (confirm('Have you successfully delivered the order to the customer?')) {
        updateOrderStatus(orderId, 'delivered');
    }
}

// Update order status
function updateOrderStatus(orderId, status) {
    // Show loading state
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Updating...';
    button.disabled = true;
    feather.replace();
    
    fetch('<?= url('/rider/delivery-status') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({
            order_id: orderId,
            status: status,
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(async response => {
        // Get response text first to check if it's valid JSON
        const responseText = await response.text();
        
        // Log the raw response for debugging
        console.log('Raw response:', responseText);
        console.log('Response status:', response.status);
        
        // Check if response is OK
        if (!response.ok) {
            // Try to parse as JSON even if status is not OK
            let errorData;
            try {
                errorData = JSON.parse(responseText);
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            } catch (parseError) {
                // If not JSON, show the raw response
                console.error('Response is not JSON:', responseText);
                throw new Error(`Server error (${response.status}): ${responseText.substring(0, 200)}`);
            }
        }
        
        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse JSON response:', parseError);
            console.error('Response text:', responseText);
            throw new Error('Invalid response from server. Please check the console for details.');
        }
        
        return data;
    })
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Order status updated successfully!', 'success');
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to update order status', 'error');
            // Restore button state
            button.innerHTML = originalContent;
            button.disabled = false;
            feather.replace();
        }
    })
    .catch(error => {
        console.error('Error updating order status:', error);
        showNotification('Failed to update order status. Please try again. Error: ' + error.message, 'error');
        // Restore button state
        button.innerHTML = originalContent;
        button.disabled = false;
        feather.replace();
    });
}

function openMaps(lat, lng) {
    if (lat && lng && lat !== 0 && lng !== 0) {
        const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
        window.open(url, '_blank');
    } else {
        showNotification('Location coordinates not available', 'warning');
    }
}

function callCustomer(phone) {
    if (phone && phone !== 'No phone') {
        window.open(`tel:${phone}`);
    } else {
        showNotification('Customer phone number not available', 'warning');
    }
}

function callRestaurant(phone) {
    if (phone && phone !== 'No phone') {
        window.open(`tel:${phone}`);
    } else {
        showNotification('Restaurant phone number not available', 'warning');
    }
}

// Mobile-optimized notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-left-4 tw-right-4 tw-px-4 tw-py-3 tw-rounded-xl tw-shadow-lg tw-z-50 tw-transition-all tw-duration-300 tw-transform tw-translate-y-0 ${
        type === 'success' ? 'tw-bg-green-500 tw-text-white' : 
        type === 'error' ? 'tw-bg-red-500 tw-text-white' : 
        type === 'warning' ? 'tw-bg-yellow-500 tw-text-white' :
        type === 'info' ? 'tw-bg-blue-500 tw-text-white' :
        'tw-bg-gray-500 tw-text-white'
    }`;
    
    notification.innerHTML = `
        <div class="tw-flex tw-items-center tw-space-x-3">
            <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : type === 'warning' ? 'alert-triangle' : 'info'}" class="tw-w-5 tw-h-5 tw-flex-shrink-0"></i>
            <span class="tw-text-sm tw-font-medium tw-flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="tw-text-white tw-opacity-70 hover:tw-opacity-100">
                <i data-feather="x" class="tw-w-4 tw-h-4"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    feather.replace();
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.transform = 'translateY(-100%)';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }, 4000);
}

// Add touch feedback to buttons
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('button, a');
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>