<?php
$title = $title ?? 'Vendor Dashboard - Time2Eat';
$currentPage = $currentPage ?? 'dashboard';
$user = $user ?? null;
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Restaurant Dashboard</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Manage your restaurant and track your performance.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                <i data-feather="user" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                <?= $user ? ucfirst((is_object($user) ? ($user->status ?? 'Active') : ($user['status'] ?? 'Active'))) : 'Active' ?>
            </span>
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-green-100 tw-text-green-800">
                <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                <?= $user ? number_format((is_object($user) ? ($user->balance ?? 0) : ($user['balance'] ?? 0))) : 0 ?> XAF
            </span>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="tw-mb-8">
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-4">
        <a href="<?= url('/vendor/menu/create') ?>" class="tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-text-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group">
            <div class="tw-flex tw-items-start tw-space-x-3">
                <div class="tw-flex-shrink-0 tw-mt-0.5">
                    <i data-feather="plus" class="tw-h-6 tw-w-6 group-hover:tw-scale-110 tw-transition-transform"></i>
                </div>
                <div class="tw-flex-1">
                    <h3 class="tw-text-lg tw-font-semibold tw-leading-tight">Add Menu Item</h3>
                    <p class="tw-text-orange-100 tw-text-sm tw-mt-1">Expand your menu</p>
                </div>
            </div>
        </a>

        <a href="<?= url('/vendor/orders') ?>" class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-start tw-space-x-3">
                <div class="tw-flex-shrink-0 tw-mt-0.5">
                    <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-blue-500 group-hover:tw-scale-110 tw-transition-transform"></i>
                </div>
                <div class="tw-flex-1">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-leading-tight">Orders</h3>
                    <p class="tw-text-gray-500 tw-text-sm tw-mt-1">Manage orders</p>
                </div>
            </div>
        </a>

        <a href="<?= url('/vendor/menu') ?>" class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-start tw-space-x-3">
                <div class="tw-flex-shrink-0 tw-mt-0.5">
                    <i data-feather="menu" class="tw-h-6 tw-w-6 tw-text-green-500 group-hover:tw-scale-110 tw-transition-transform"></i>
                </div>
                <div class="tw-flex-1">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-leading-tight">Menu</h3>
                    <p class="tw-text-gray-500 tw-text-sm tw-mt-1">Manage items</p>
                </div>
            </div>
        </a>

        <a href="<?= url('/vendor/analytics') ?>" class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-start tw-space-x-3">
                <div class="tw-flex-shrink-0 tw-mt-0.5">
                    <i data-feather="bar-chart-2" class="tw-h-6 tw-w-6 tw-text-purple-500 group-hover:tw-scale-110 tw-transition-transform"></i>
                </div>
                <div class="tw-flex-1">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-leading-tight">Analytics</h3>
                    <p class="tw-text-gray-500 tw-text-sm tw-mt-1">View reports</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Today's Orders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= $stats['todayOrders'] ?></p>
                <p class="tw-text-sm tw-text-gray-500">This month: <?= $stats['monthOrders'] ?></p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Today's Revenue</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['todayRevenue']) ?> XAF</p>
                <p class="tw-text-sm tw-text-gray-500">This month: <?= number_format($stats['monthRevenue']) ?> XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Menu Items</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= $stats['totalMenuItems'] ?></p>
                <p class="tw-text-sm tw-text-gray-500"><?= count($lowStockItems) ?> low stock</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="menu" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Order Value</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['avgOrderValue']) ?> XAF</p>
                <p class="tw-text-sm tw-text-gray-500"><?= $restaurant['name'] ?? 'Restaurant' ?></p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders & Performance Chart -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
    <!-- Recent Orders -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Recent Orders</h2>
                <a href="<?= url('/vendor/orders') ?>" class="tw-text-orange-600 hover:tw-text-orange-500 tw-text-sm tw-font-medium">View all</a>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <?php if (!empty($recentOrders)): ?>
                <?php foreach ($recentOrders as $order): ?>
                    <?php
                    // Determine status color
                    $statusColors = [
                        'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
                        'confirmed' => 'tw-bg-blue-100 tw-text-blue-800',
                        'preparing' => 'tw-bg-orange-100 tw-text-orange-800',
                        'ready' => 'tw-bg-green-100 tw-text-green-800',
                        'picked_up' => 'tw-bg-purple-100 tw-text-purple-800',
                        'on_the_way' => 'tw-bg-indigo-100 tw-text-indigo-800',
                        'delivered' => 'tw-bg-green-100 tw-text-green-800',
                        'cancelled' => 'tw-bg-red-100 tw-text-red-800'
                    ];
                    $statusColor = $statusColors[$order['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                    
                    // Calculate time ago
                    $timeAgo = time() - strtotime($order['created_at']);
                    if ($timeAgo < 60) {
                        $timeText = $timeAgo . ' sec ago';
                    } elseif ($timeAgo < 3600) {
                        $timeText = floor($timeAgo / 60) . ' min ago';
                    } else {
                        $timeText = floor($timeAgo / 3600) . ' hr ago';
                    }
                    ?>
                    <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <div class="tw-h-10 tw-w-10 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-blue-600 tw-font-medium tw-text-xs">#<?= substr($order['order_number'], -3) ?></span>
                            </div>
                            <div>
                                <h3 class="tw-font-medium tw-text-gray-900"><?= e(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')) ?: 'Customer' ?></h3>
                                <p class="tw-text-sm tw-text-gray-500"><?= $timeText ?></p>
                            </div>
                        </div>
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium <?= $statusColor ?>">
                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                            </span>
                            <span class="tw-font-medium tw-text-gray-900"><?= number_format($order['total_amount']) ?> XAF</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="tw-text-center tw-py-8">
                    <i data-feather="shopping-bag" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                    <p class="tw-text-gray-500">No recent orders</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Performance Chart -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Sales Performance</h2>
        </div>
        <div class="tw-p-6">
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Live Orders & Low Stock Alert -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
    <!-- Live Orders -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-space-x-2">
                    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Live Orders</h2>
                    <span class="tw-flex tw-h-2 tw-w-2">
                        <span class="tw-animate-ping tw-absolute tw-inline-flex tw-h-2 tw-w-2 tw-rounded-full tw-bg-green-400 tw-opacity-75"></span>
                        <span class="tw-relative tw-inline-flex tw-rounded-full tw-h-2 tw-w-2 tw-bg-green-500"></span>
                    </span>
                </div>
                <a href="<?= url('/vendor/orders') ?>" class="tw-text-orange-600 hover:tw-text-orange-500 tw-text-sm tw-font-medium">View all</a>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <?php
            // Filter for active/live orders (not delivered or cancelled)
            $liveOrders = array_filter($recentOrders, function($order) {
                return !in_array($order['status'], ['delivered', 'completed', 'cancelled']);
            });
            ?>
            <?php if (!empty($liveOrders)): ?>
                <?php foreach (array_slice($liveOrders, 0, 5) as $order): ?>
                    <?php
                    // Determine status color
                    $statusColors = [
                        'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
                        'confirmed' => 'tw-bg-blue-100 tw-text-blue-800',
                        'preparing' => 'tw-bg-orange-100 tw-text-orange-800',
                        'ready' => 'tw-bg-green-100 tw-text-green-800',
                        'picked_up' => 'tw-bg-purple-100 tw-text-purple-800',
                        'on_the_way' => 'tw-bg-indigo-100 tw-text-indigo-800'
                    ];
                    $statusColor = $statusColors[$order['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';

                    // Calculate time ago
                    $timeAgo = time() - strtotime($order['created_at']);
                    if ($timeAgo < 60) {
                        $timeText = $timeAgo . ' sec ago';
                    } elseif ($timeAgo < 3600) {
                        $timeText = floor($timeAgo / 60) . ' min ago';
                    } else {
                        $timeText = floor($timeAgo / 3600) . ' hr ago';
                    }
                    ?>
                    <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-lg tw-border tw-border-gray-200 hover:tw-border-orange-300 tw-transition-colors">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <div class="tw-h-10 tw-w-10 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-orange-600 tw-font-bold tw-text-xs">#<?= substr($order['order_number'], -3) ?></span>
                            </div>
                            <div>
                                <h3 class="tw-font-medium tw-text-gray-900"><?= e(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')) ?: 'Customer' ?></h3>
                                <p class="tw-text-sm tw-text-gray-500"><?= $timeText ?> • <?= $order['item_count'] ?? 0 ?> items</p>
                            </div>
                        </div>
                        <div class="tw-flex tw-flex-col tw-items-end tw-space-y-1">
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium <?= $statusColor ?>">
                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                            </span>
                            <span class="tw-font-semibold tw-text-gray-900"><?= number_format($order['total_amount']) ?> XAF</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="tw-text-center tw-py-8">
                    <i data-feather="clock" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                    <p class="tw-text-gray-500">No active orders</p>
                    <p class="tw-text-sm tw-text-gray-400">Live orders will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Low Stock Alert</h2>
                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                    <?= count($lowStockItems) ?> Items
                </span>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <?php if (!empty($lowStockItems)): ?>
                <?php foreach ($lowStockItems as $item): ?>
                    <?php
                    $isOutOfStock = $item['stock_quantity'] <= 0 || !$item['is_available'];
                    $isLowStock = $item['stock_quantity'] <= 5 && $item['stock_quantity'] > 0;
                    $bgColor = $isOutOfStock ? 'tw-bg-red-50 tw-border-red-200' : 'tw-bg-yellow-50 tw-border-yellow-200';
                    $iconColor = $isOutOfStock ? 'tw-text-red-500' : 'tw-text-yellow-500';
                    $textColor = $isOutOfStock ? 'tw-text-red-600' : 'tw-text-yellow-600';
                    $buttonColor = $isOutOfStock ? 'tw-text-red-600 hover:tw-text-red-700' : 'tw-text-yellow-600 hover:tw-text-yellow-700';
                    ?>
                    <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-rounded-lg tw-border <?= $bgColor ?>">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <i data-feather="<?= $isOutOfStock ? 'alert-triangle' : 'alert-circle' ?>" class="tw-h-5 tw-w-5 <?= $iconColor ?>"></i>
                            <div>
                                <h3 class="tw-font-medium tw-text-gray-900"><?= e($item['name']) ?></h3>
                                <p class="tw-text-sm <?= $textColor ?>">
                                    <?php if ($isOutOfStock): ?>
                                        Out of stock
                                    <?php else: ?>
                                        Only <?= $item['stock_quantity'] ?> left
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <a href="<?= url('/vendor/menu/' . $item['id'] . '/edit') ?>" class="<?= $buttonColor ?> tw-text-sm tw-font-medium">
                            Restock
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="tw-text-center tw-py-8">
                    <i data-feather="check-circle" class="tw-h-12 tw-w-12 tw-text-green-400 tw-mx-auto tw-mb-4"></i>
                    <p class="tw-text-gray-500">All items are well stocked!</p>
                    <p class="tw-text-sm tw-text-gray-400">No low stock alerts</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Sales Chart with real data
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Get chart data from PHP
    const chartData = <?= json_encode($stats['chartData'] ?? ['labels' => [], 'orders' => [], 'revenue' => []]) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels.length > 0 ? chartData.labels : ['No Data'],
            datasets: [{
                label: 'Revenue (XAF)',
                data: chartData.revenue.length > 0 ? chartData.revenue : [0],
                borderColor: 'rgb(249, 115, 22)',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Orders',
                data: chartData.orders.length > 0 ? chartData.orders : [0],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' XAF';
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return value + ' orders';
                        }
                    }
                }
            }
        }
    });
});
</script>
