<?php
$title = $title ?? 'Rider Dashboard - Time2Eat';
$currentPage = $currentPage ?? 'dashboard';
$user = $user ?? null;
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Rider Dashboard</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Manage your deliveries and track your earnings.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <button onclick="toggleAvailability()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-colors <?= ($user->is_available ?? false) ? 'tw-bg-red-100 tw-text-red-800 hover:tw-bg-red-200' : 'tw-bg-green-100 tw-text-green-800 hover:tw-bg-green-200' ?>" id="availability-btn">
                <i data-feather="power" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                <?= ($user->is_available ?? false) ? 'Go Offline' : 'Go Online' ?>
            </button>
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                <?= number_format($user->balance ?? 0) ?> XAF
            </span>
        </div>
    </div>
</div>

<!-- Status Cards -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Deliveries</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['activeDeliveries'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">In progress</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="truck" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Today's Earnings</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['todayEarnings'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">+<?= number_format($stats['todayDeliveries'] ?? 0) ?> deliveries</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Completed Today</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['todayDeliveries'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500">Deliveries</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Rating</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['averageRating'] ?? 0, 1) ?></p>
                <p class="tw-text-sm tw-text-green-600"><?= ($stats['averageRating'] ?? 0) >= 4.5 ? 'Excellent' : (($stats['averageRating'] ?? 0) >= 4.0 ? 'Good' : 'Needs Improvement') ?></p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="star" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Active Deliveries & Available Orders -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
    <!-- Active Deliveries -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Active Deliveries</h2>
                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                    <?= count($activeDeliveries ?? []) ?> Active
                </span>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <?php if (empty($activeDeliveries)): ?>
                <div class="tw-text-center tw-py-8">
                    <i data-feather="truck" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                    <p class="tw-text-gray-500 tw-text-sm">No active deliveries</p>
                    <p class="tw-text-gray-400 tw-text-xs tw-mt-1">Check available orders to start delivering</p>
                </div>
            <?php else: ?>
                <?php foreach ($activeDeliveries as $delivery): ?>
                    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-h-10 tw-w-10 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                    <span class="tw-text-blue-600 tw-font-medium tw-text-sm">#<?= str_pad($delivery['id'] ?? 0, 3, '0', STR_PAD_LEFT) ?></span>
                                </div>
                                <div>
                                    <h3 class="tw-font-medium tw-text-gray-900"><?= e($delivery['restaurant_name'] ?? 'Unknown Restaurant') ?></h3>
                                    <p class="tw-text-sm tw-text-gray-500"><?= ($delivery['item_count'] ?? 0) ?> items • <?= number_format($delivery['distance'] ?? 0, 1) ?> km</p>
                                </div>
                            </div>
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800">
                                <?= ucfirst(str_replace('_', ' ', $delivery['status'] ?? 'Unknown')) ?>
                            </span>
                        </div>
                        <div class="tw-flex tw-items-center tw-justify-between tw-text-sm">
                            <div class="tw-text-gray-600">
                                <p>Delivery: <?= e($delivery['delivery_address'] ?? 'Address not available') ?></p>
                                <p>Customer: <?= e($delivery['customer_name'] ?? 'Unknown Customer') ?></p>
                            </div>
                            <div class="tw-text-right">
                                <p class="tw-font-medium tw-text-gray-900"><?= number_format($delivery['rider_earnings'] ?? 0) ?> XAF</p>
                                <p class="tw-text-green-600">Earning</p>
                            </div>
                        </div>
                        <div class="tw-mt-3 tw-flex tw-space-x-2">
                            <button class="tw-flex-1 tw-bg-green-500 hover:tw-bg-green-600 tw-text-white tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                                <i data-feather="check" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                                Mark Delivered
                            </button>
                            <button class="tw-bg-blue-500 hover:tw-bg-blue-600 tw-text-white tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                                <i data-feather="map" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <button class="tw-bg-gray-500 hover:tw-bg-gray-600 tw-text-white tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                                <i data-feather="phone" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Available Orders -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Available Orders</h2>
                <button class="tw-text-orange-600 hover:tw-text-orange-500 tw-text-sm tw-font-medium" onclick="location.reload()">
                    <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                    Refresh
                </button>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <?php if (empty($availableOrders)): ?>
                <div class="tw-text-center tw-py-8">
                    <i data-feather="map" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                    <p class="tw-text-gray-500 tw-text-sm">No available orders</p>
                    <p class="tw-text-gray-400 tw-text-xs tw-mt-1">Check back later for new delivery opportunities</p>
                </div>
            <?php else: ?>
                <?php foreach ($availableOrders as $order): ?>
                    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 hover:tw-border-orange-300 tw-transition-colors">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-h-10 tw-w-10 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                    <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="tw-font-medium tw-text-gray-900"><?= e($order['restaurant_name'] ?? 'Unknown Restaurant') ?></h3>
                                    <p class="tw-text-sm tw-text-gray-500"><?= ($order['item_count'] ?? 0) ?> items • <?= number_format($order['distance'] ?? 0, 1) ?> km • <?= ($order['estimated_time'] ?? 0) ?> min</p>
                                </div>
                            </div>
                            <div class="tw-text-right">
                                <p class="tw-font-medium tw-text-gray-900"><?= number_format($order['delivery_fee'] ?? 0) ?> XAF</p>
                                <p class="tw-text-green-600 tw-text-sm">Earning</p>
                            </div>
                        </div>
                        <div class="tw-text-sm tw-text-gray-600 tw-mb-3">
                            <p>Pickup: <?= e($order['pickup_address'] ?? 'Address not available') ?></p>
                            <p>Delivery: <?= e($order['delivery_address'] ?? 'Address not available') ?></p>
                        </div>
                        <div class="tw-flex tw-space-x-2">
                            <button class="tw-flex-1 tw-bg-green-500 hover:tw-bg-green-600 tw-text-white tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                                Accept Order
                            </button>
                            <button class="tw-bg-gray-200 hover:tw-bg-gray-300 tw-text-gray-700 tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                                View Details
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Earnings Chart & Recent Activity -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
    <!-- Earnings Chart -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Weekly Earnings</h2>
        </div>
        <div class="tw-p-6">
            <canvas id="earningsChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Recent Activity</h2>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <?php if (empty($recentActivity)): ?>
                <div class="tw-text-center tw-py-8">
                    <i data-feather="activity" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                    <p class="tw-text-gray-500 tw-text-sm">No recent activity</p>
                    <p class="tw-text-gray-400 tw-text-xs tw-mt-1">Complete some deliveries to see activity here</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-h-8 tw-w-8 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                            <i data-feather="<?= $activity['icon'] ?? 'check' ?>" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                        </div>
                        <div class="tw-flex-1">
                            <p class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($activity['description'] ?? 'Activity') ?></p>
                            <p class="tw-text-xs tw-text-gray-500"><?= $activity['time_ago'] ?? 'Unknown time' ?><?= isset($activity['earnings']) ? ' • +' . number_format($activity['earnings']) . ' XAF' : '' ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-z-50 tw-transition-all tw-duration-300 ${
        type === 'success' ? 'tw-bg-green-500 tw-text-white' : 
        type === 'error' ? 'tw-bg-red-500 tw-text-white' : 
        'tw-bg-blue-500 tw-text-white'
    }`;
    notification.innerHTML = `
        <div class="tw-flex tw-items-center tw-space-x-2">
            <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="tw-w-5 tw-h-5"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    feather.replace();
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Availability toggle
function toggleAvailability() {
    const btn = document.getElementById('availability-btn');
    const isOnline = btn.textContent.includes('Go Offline');
    
    // Get CSRF token
    const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    
    fetch('<?= url('/rider/toggle-availability') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken,
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache'
        },
        cache: 'no-store',
        body: JSON.stringify({ 
            available: !isOnline,
            csrf_token: csrfToken
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (isOnline) {
                btn.innerHTML = '<i data-feather="power" class="tw-h-4 tw-w-4 tw-mr-2"></i>Go Online';
                btn.className = btn.className.replace('tw-bg-red-100 tw-text-red-800 hover:tw-bg-red-200', 'tw-bg-green-100 tw-text-green-800 hover:tw-bg-green-200');
            } else {
                btn.innerHTML = '<i data-feather="power" class="tw-h-4 tw-w-4 tw-mr-2"></i>Go Offline';
                btn.className = btn.className.replace('tw-bg-green-100 tw-text-green-800 hover:tw-bg-green-200', 'tw-bg-red-100 tw-text-red-800 hover:tw-bg-red-200');
            }
            feather.replace();
            
            // Show success message
            showNotification(data.message || 'Status updated successfully', 'success');
            
            // Force page refresh to show updated data (bypass cache)
            // Use setTimeout to allow notification to show first
            setTimeout(() => {
                // Force reload from server, bypassing cache
                // Add timestamp to force fresh request
                window.location.href = window.location.href.split('?')[0] + '?t=' + Date.now();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error toggling availability:', error);
        showNotification('Failed to update status. Please try again.', 'error');
    });
}

// Earnings Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('earningsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Earnings (XAF)',
                data: [12000, 15000, 18000, 14000, 16000, 22000, 15500],
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' XAF';
                        }
                    }
                }
            }
        }
    });
});

// Auto-refresh available orders
setInterval(function() {
    // Refresh available orders every 30 seconds
    fetch('<?= url('/api/rider/available-orders') ?>')
        .then(response => response.json())
        .then(data => {
            // Update available orders section
            console.log('Available orders updated');
        })
        .catch(error => console.log('Failed to refresh orders:', error));
}, 30000);
</script>
