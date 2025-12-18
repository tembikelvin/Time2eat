<?php
$title = $title ?? 'Customer Dashboard - Time2Eat';
$currentPage = $currentPage ?? 'dashboard';
$user = $user ?? null;
$stats = $stats ?? [];
$recentOrders = $recentOrders ?? [];
$liveOrders = $liveOrders ?? [];
$favoriteRestaurants = $favoriteRestaurants ?? [];
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Welcome back, <?= e($user->first_name ?? 'Customer') ?>!</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Here's what's happening with your orders today.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-green-100 tw-text-green-800">
                <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                <?= number_format($user->balance ?? 0) ?> XAF
            </span>
            <?php if (!empty($user->affiliate_code)): ?>
                <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-purple-100 tw-text-purple-800">
                    <i data-feather="users" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                    Affiliate: <?= e($user->affiliate_code) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Quick Actions -->
<div class="tw-mb-8">
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-4">
        <a href="<?= url('/browse') ?>" class="tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-text-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="search" class="tw-h-8 tw-w-8 group-hover:tw-scale-110 tw-transition-transform"></i>
                <div>
                    <h3 class="tw-text-lg tw-font-semibold">Browse Food</h3>
                    <p class="tw-text-orange-100 tw-text-sm">Find delicious meals</p>
                </div>
            </div>
        </a>

        <a href="<?= url('/customer/orders') ?>" class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="package" class="tw-h-8 tw-w-8 tw-text-blue-500 group-hover:tw-scale-110 tw-transition-transform"></i>
                <div>
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">My Orders</h3>
                    <p class="tw-text-gray-500 tw-text-sm">Track your orders</p>
                </div>
            </div>
        </a>

        <a href="<?= url('/customer/favorites') ?>" class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="heart" class="tw-h-8 tw-w-8 tw-text-pink-500 group-hover:tw-scale-110 tw-transition-transform"></i>
                <div>
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Favorites</h3>
                    <p class="tw-text-gray-500 tw-text-sm">Your saved items</p>
                </div>
            </div>
        </a>

        <a href="<?= url('/customer/profile') ?>" class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="user" class="tw-h-8 tw-w-8 tw-text-green-500 group-hover:tw-scale-110 tw-transition-transform"></i>
                <div>
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Profile</h3>
                    <p class="tw-text-gray-500 tw-text-sm">Manage account</p>
                </div>
            </div>
        </a>
    </div>
</div>

        <!-- Stats Cards -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6 tw-mb-8">
            <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Orders</p>
                        <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['totalOrders'] ?? 0) ?></p>
                        <p class="tw-text-sm tw-text-green-600">+<?= number_format($stats['monthlyOrders'] ?? 0) ?> this month</p>
                    </div>
                    <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                        <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Spent</p>
                        <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['totalSpent'] ?? 0) ?> XAF</p>
                        <p class="tw-text-sm tw-text-gray-500">Lifetime spending</p>
                    </div>
                    <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                        <i data-feather="credit-card" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Favorite Restaurants</p>
                        <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['favoriteRestaurants'] ?? 0) ?></p>
                        <p class="tw-text-sm tw-text-gray-500">Restaurants ordered from</p>
                    </div>
                    <div class="tw-p-3 tw-bg-pink-100 tw-rounded-full">
                        <i data-feather="heart" class="tw-h-6 tw-w-6 tw-text-pink-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders & Recommendations -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
            <!-- Recent Orders -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-p-6 tw-border-b tw-border-gray-200">
                    <div class="tw-flex tw-items-center tw-justify-between">
                        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Recent Orders</h2>
                        <a href="<?= url('/orders') ?>" class="tw-text-orange-600 hover:tw-text-orange-500 tw-text-sm tw-font-medium">View all</a>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-4">
                    <?php if (empty($recentOrders)): ?>
                        <div class="tw-text-center tw-py-8">
                            <i data-feather="package" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                            <p class="tw-text-gray-500">No orders yet</p>
                            <a href="<?= url('/browse') ?>" class="tw-text-orange-600 hover:tw-text-orange-500 tw-text-sm tw-font-medium">Start ordering</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <div class="tw-flex tw-items-center tw-space-x-4 tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                                <div class="tw-w-15 tw-h-15 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                                    <i data-feather="shopping-bag" class="tw-h-8 tw-w-8 tw-text-white"></i>
                                </div>
                                <div class="tw-flex-1">
                                    <h3 class="tw-font-medium tw-text-gray-900">Order #<?= e($order['order_number'] ?? $order['id']) ?></h3>
                                    <p class="tw-text-sm tw-text-gray-500"><?= e($order['restaurant_name']) ?> • <?= date('M j, Y', strtotime($order['created_at'])) ?></p>
                                    <p class="tw-text-sm tw-font-medium <?= $order['status'] === 'delivered' ? 'tw-text-green-600' : ($order['status'] === 'cancelled' ? 'tw-text-red-600' : 'tw-text-blue-600') ?>">
                                        <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                    </p>
                                </div>
                                <div class="tw-text-right">
                                    <p class="tw-font-medium tw-text-gray-900"><?= number_format($order['total_amount']) ?> XAF</p>
                                    <a href="<?= url('/customer/orders?order=' . $order['id']) ?>" class="tw-text-orange-600 hover:tw-text-orange-500 tw-text-sm">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Live Orders -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-p-6 tw-border-b tw-border-gray-200">
                    <div class="tw-flex tw-items-center tw-justify-between">
                        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Live Orders</h2>
                        <button onclick="refreshLiveOrders()" class="tw-text-orange-500 hover:tw-text-orange-600 tw-transition-colors">
                            <i data-feather="refresh-cw" class="tw-h-4 tw-w-4"></i>
                        </button>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-4" id="liveOrdersContainer">
                    <?php if (empty($liveOrders)): ?>
                        <div class="tw-text-center tw-py-8">
                            <i data-feather="package" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                            <p class="tw-text-gray-500">No active orders</p>
                            <p class="tw-text-sm tw-text-gray-400">Your live orders will appear here</p>
                            <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-mt-4 tw-px-4 tw-py-2 tw-bg-orange-500 tw-text-white tw-rounded-lg hover:tw-bg-orange-600 tw-transition-colors">
                                <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                Order Now
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($liveOrders as $order): ?>
                            <div class="tw-p-4 tw-bg-gray-50 tw-rounded-lg tw-border tw-border-gray-200">
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                                    <div class="tw-flex tw-items-center tw-space-x-3">
                                        <div class="tw-h-10 tw-w-10 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                            <i data-feather="package" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                        </div>
                                        <div>
                                            <h3 class="tw-font-medium tw-text-gray-900">Order #<?= e($order['order_number']) ?></h3>
                                            <p class="tw-text-sm tw-text-gray-500"><?= e($order['restaurant_name']) ?></p>
                                        </div>
                                    </div>
                                    <div class="tw-text-right">
                                        <p class="tw-font-medium tw-text-gray-900"><?= number_format($order['total_amount']) ?> XAF</p>
                                        <?php
                                        $statusColor = match($order['status']) {
                                            'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
                                            'confirmed' => 'tw-bg-blue-100 tw-text-blue-800',
                                            'preparing' => 'tw-bg-orange-100 tw-text-orange-800',
                                            'ready' => 'tw-bg-purple-100 tw-text-purple-800',
                                            'on_the_way' => 'tw-bg-green-100 tw-text-green-800',
                                            'delivered' => 'tw-bg-green-100 tw-text-green-800',
                                            'cancelled' => 'tw-bg-red-100 tw-text-red-800',
                                            default => 'tw-bg-gray-100 tw-text-gray-800'
                                        };
                                        ?>
                                        <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium <?= $statusColor ?>">
                                            <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4 tw-text-sm">
                                    <div>
                                        <p class="tw-text-gray-500">Ordered</p>
                                        <p class="tw-font-medium"><?= date('M j, g:i A', strtotime($order['created_at'])) ?></p>
                                    </div>
                                    <?php if ($order['estimated_delivery_time']): ?>
                                        <div>
                                            <p class="tw-text-gray-500">Estimated Delivery</p>
                                            <p class="tw-font-medium"><?= date('M j, g:i A', strtotime($order['estimated_delivery_time'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($order['rider_first_name']): ?>
                                        <div>
                                            <p class="tw-text-gray-500">Rider</p>
                                            <p class="tw-font-medium"><?= e($order['rider_first_name'] . ' ' . $order['rider_last_name']) ?></p>
                                    </div>
                                <?php endif; ?>
                                </div>
                                
                                <div class="tw-mt-3 tw-pt-3 tw-border-t tw-border-gray-200">
                                    <div class="tw-flex tw-items-center tw-justify-between">
                                        <p class="tw-text-sm tw-text-gray-500"><?= e($order['delivery_address']) ?></p>
                                        <a href="<?= url('/customer/orders/' . $order['id']) ?>" class="tw-text-orange-500 hover:tw-text-orange-600 tw-text-sm tw-font-medium">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

<script>
// Add interactivity for dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    feather.replace();

    // Animate stats on load
    const statNumbers = document.querySelectorAll('.tw-text-3xl');
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent.replace(/[^\d]/g, ''));
        if (finalValue > 0) {
            let currentValue = 0;
            const increment = finalValue / 20;

            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    currentValue = finalValue;
                    clearInterval(timer);
                }

                if (stat.textContent.includes('XAF')) {
                    stat.textContent = Math.floor(currentValue).toLocaleString() + ' XAF';
                } else {
                    stat.textContent = Math.floor(currentValue);
                }
            }, 50);
        }
    });

    // Auto-refresh live orders every 30 seconds
    setInterval(refreshLiveOrders, 30000);
});

// Refresh live orders
function refreshLiveOrders() {
    const container = document.getElementById('liveOrdersContainer');
    if (!container) return;

    // Show loading state
    const refreshBtn = document.querySelector('[onclick="refreshLiveOrders()"] i');
    if (refreshBtn) {
        refreshBtn.classList.add('tw-animate-spin');
    }

    fetch('<?= url('/customer/live-orders') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = data.html;
                feather.replace();
            }
        })
        .catch(error => {
            console.error('Error refreshing live orders:', error);
        })
        .finally(() => {
            // Remove loading state
            if (refreshBtn) {
                refreshBtn.classList.remove('tw-animate-spin');
            }
        });
}
</script>
