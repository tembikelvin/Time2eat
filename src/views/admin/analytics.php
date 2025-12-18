<?php
/**
 * Analytics Dashboard Content
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'analytics';

$title = $title ?? 'Analytics Dashboard - Time2Eat';
$analyticsData = $analyticsData ?? [];
$currentPeriod = $currentPeriod ?? '30days';
$error = $error ?? null;

// Extract analytics data
$platform = $analyticsData['platform'] ?? [];
$orders = $analyticsData['orders'] ?? [];
$revenue = $analyticsData['revenue'] ?? [];
$users = $analyticsData['users'] ?? [];
$restaurants = $analyticsData['restaurants'] ?? [];
$riders = $analyticsData['riders'] ?? [];
?>

<!-- Page Header -->
<div class="tw-mb-8">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Analytics Dashboard</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Comprehensive platform insights and performance metrics
            </p>
                </div>
        <div class="tw-flex tw-space-x-2 sm:tw-space-x-3">
                    <!-- Period Selector -->
                    <select id="periodSelector" onchange="changePeriod()" 
            class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md">
                        <option value="today" <?= $currentPeriod === 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="week" <?= $currentPeriod === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="30days" <?= $currentPeriod === '30days' ? 'selected' : '' ?>>Last 30 Days</option>
                        <option value="90days" <?= $currentPeriod === '90days' ? 'selected' : '' ?>>Last 90 Days</option>
                        <option value="year" <?= $currentPeriod === 'year' ? 'selected' : '' ?>>Last Year</option>
                    </select>
                    
                    <!-- Export Button -->
                    <button onclick="exportAnalytics()" 
            class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
            title="Export Report">
                <i data-feather="download" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Export Report</span>
                    </button>
                    
                    <!-- Refresh Button -->
                    <button onclick="refreshAnalytics()" 
            class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
            title="Refresh Data">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Refresh</span>
                    </button>
            </div>
        </div>
    </div>

        <?php if ($error): ?>
<!-- Error Message -->
<div class="tw-mb-6 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg tw-p-4">
                <div class="tw-flex">
        <div class="tw-flex-shrink-0">
                    <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-red-400"></i>
        </div>
                    <div class="tw-ml-3">
                        <h3 class="tw-text-sm tw-font-medium tw-text-red-800">Error Loading Analytics</h3>
            <div class="tw-mt-2 tw-text-sm tw-text-red-700">
                <p><?= e($error) ?></p>
            </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

<!-- Platform Overview Stats -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <!-- Total Users -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Users</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($platform['total_users'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600"><?= number_format($platform['active_customers'] ?? 0) ?> active</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- Total Restaurants -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Restaurants</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($platform['total_restaurants'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600"><?= number_format($platform['active_vendors'] ?? 0) ?> active</p>
                    </div>
                    <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="home" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Orders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($platform['total_orders'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-purple-600"><?= number_format($platform['period_orders'] ?? 0) ?> this period</p>
                    </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="shopping-cart" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
                    </div>
                </div>
            </div>

    <!-- Total Revenue -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Revenue</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($platform['total_revenue'] ?? 0, 0) ?> XAF</p>
                <p class="tw-text-sm tw-text-yellow-600"><?= number_format($revenue['total_revenue'] ?? 0, 0) ?> this period</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
                    </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6 tw-mb-8">
    <!-- Active Riders -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Riders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($platform['active_riders'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-cyan-600">Available for delivery</p>
            </div>
            <div class="tw-p-3 tw-bg-cyan-100 tw-rounded-full">
                <i data-feather="truck" class="tw-h-6 tw-w-6 tw-text-cyan-600"></i>
                    </div>
                </div>
            </div>

            <!-- Average Order Value -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Order Value</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($revenue['avg_order_value'] ?? 0, 0) ?> XAF</p>
                <p class="tw-text-sm tw-text-indigo-600">Per order</p>
            </div>
            <div class="tw-p-3 tw-bg-indigo-100 tw-rounded-full">
                <i data-feather="shopping-cart" class="tw-h-6 tw-w-6 tw-text-indigo-600"></i>
            </div>
        </div>
    </div>

    <!-- Order Completion Rate -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Completion Rate</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format(($orders['status_breakdown']['delivered']['count'] ?? 0) / max(($platform['period_orders'] ?? 1), 1) * 100, 1) ?>%</p>
                <p class="tw-text-sm tw-text-emerald-600">Orders delivered</p>
            </div>
            <div class="tw-p-3 tw-bg-emerald-100 tw-rounded-full">
                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-emerald-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Order Status Breakdown -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Order Status Breakdown</h2>
            <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                <i data-feather="bar-chart-2" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="tw-p-6">
        <?php if (!empty($orders['status_breakdown'])): ?>
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4">
                <?php foreach ($orders['status_breakdown'] as $status): ?>
                    <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-lg hover:tw-bg-gray-100 tw-transition-colors">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-w-4 tw-h-4 tw-rounded-full tw-mr-4 tw-bg-<?= $status['status'] === 'delivered' ? 'green' : ($status['status'] === 'pending' ? 'yellow' : 'red') ?>-500 tw-shadow-sm"></div>
                            <span class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-capitalize"><?= e($status['status']) ?></span>
                        </div>
                        <div class="tw-text-right">
                            <div class="tw-text-lg tw-font-bold tw-text-gray-900"><?= number_format($status['count'] ?? 0) ?></div>
                            <div class="tw-text-xs tw-text-gray-600 tw-font-medium"><?= number_format($status['revenue'] ?? 0, 0) ?> XAF</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="tw-text-center tw-py-8">
                <i data-feather="inbox" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                <p class="tw-text-gray-500">No order data available for the selected period.</p>
            </div>
        <?php endif; ?>
                    </div>
</div>

<!-- Revenue Analytics -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Revenue Analytics</h2>
            <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg">
                <i data-feather="trending-up" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                </div>
            </div>
        </div>

    <div class="tw-p-6">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
            <div class="tw-flex tw-items-center tw-p-4 tw-bg-green-50 tw-rounded-lg">
                <div class="tw-p-3 tw-bg-green-100 tw-rounded-lg tw-mr-4">
                    <i data-feather="dollar-sign" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Revenue</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-green-700"><?= number_format($revenue['total_revenue'] ?? 0, 0) ?> XAF</p>
                </div>
                    </div>
            
            <div class="tw-flex tw-items-center tw-p-4 tw-bg-blue-50 tw-rounded-lg">
                <div class="tw-p-3 tw-bg-blue-100 tw-rounded-lg tw-mr-4">
                    <i data-feather="shopping-cart" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Order Value</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-blue-700"><?= number_format($revenue['avg_order_value'] ?? 0, 0) ?> XAF</p>
                </div>
            </div>

            <div class="tw-flex tw-items-center tw-p-4 tw-bg-purple-50 tw-rounded-lg">
                <div class="tw-p-3 tw-bg-purple-100 tw-rounded-lg tw-mr-4">
                    <i data-feather="shopping-cart" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Orders</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-purple-700"><?= number_format($revenue['total_orders'] ?? 0) ?></p>
                </div>
            </div>
                </div>
            </div>
        </div>

<!-- Restaurant Performance -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Top Restaurants</h2>
            <div class="tw-p-2 tw-bg-orange-100 tw-rounded-lg">
                <i data-feather="award" class="tw-h-5 tw-w-5 tw-text-orange-600"></i>
            </div>
        </div>
                </div>
    
                <div class="tw-overflow-x-auto">
                    <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                        <thead class="tw-bg-gray-50">
                            <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Restaurant</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Cuisine</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Orders</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Revenue</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Rating</th>
                            </tr>
                        </thead>
                        <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (!empty($restaurants)): ?>
                    <?php foreach ($restaurants as $index => $restaurant): ?>
                        <tr class="hover:tw-bg-gray-50">
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <div class="tw-w-8 tw-h-8 tw-bg-gradient-to-br tw-from-orange-500 tw-to-red-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                        <span class="tw-text-white tw-font-bold tw-text-sm"><?= $index + 1 ?></span>
                                    </div>
                                    <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= e($restaurant['name']) ?></span>
                                </div>
                                    </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                                    <?= e($restaurant['cuisine_type']) ?>
                                </span>
                                    </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-semibold tw-text-gray-900"><?= number_format($restaurant['total_orders'] ?? 0) ?></td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-semibold tw-text-green-600"><?= number_format($restaurant['revenue'] ?? 0, 0) ?> XAF</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <i data-feather="star" class="tw-h-4 tw-w-4 tw-text-yellow-400 tw-mr-1"></i>
                                    <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= number_format($restaurant['rating'] ?? 0, 1) ?></span>
                                </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="tw-px-6 tw-py-12 tw-text-center">
                            <i data-feather="inbox" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                            <p class="tw-text-gray-500">No restaurant data available for the selected period.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<!-- Rider Performance -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Top Riders</h2>
            <div class="tw-p-2 tw-bg-cyan-100 tw-rounded-lg">
                <i data-feather="truck" class="tw-h-5 tw-w-5 tw-text-cyan-600"></i>
            </div>
        </div>
                </div>
    
                <div class="tw-overflow-x-auto">
                    <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                        <thead class="tw-bg-gray-50">
                            <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Rider</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Deliveries</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Earnings</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Avg Time</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Active Days</th>
                            </tr>
                        </thead>
                        <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (!empty($riders)): ?>
                    <?php foreach ($riders as $index => $rider): ?>
                        <tr class="hover:tw-bg-gray-50">
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <div class="tw-w-8 tw-h-8 tw-bg-gradient-to-br tw-from-cyan-500 tw-to-blue-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                        <span class="tw-text-white tw-font-bold tw-text-sm"><?= $index + 1 ?></span>
                                    </div>
                                    <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= e($rider['name']) ?></span>
                                </div>
                                    </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-semibold tw-text-gray-900"><?= number_format($rider['total_deliveries'] ?? 0) ?></td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-semibold tw-text-green-600"><?= number_format($rider['total_earnings'] ?? 0, 0) ?> XAF</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <i data-feather="clock" class="tw-h-4 tw-w-4 tw-text-blue-500 tw-mr-1"></i>
                                    <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= number_format($rider['avg_delivery_time'] ?? 0, 0) ?> min</span>
                                </div>
                                    </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <i data-feather="calendar" class="tw-h-4 tw-w-4 tw-text-purple-500 tw-mr-1"></i>
                                    <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= number_format($rider['active_days'] ?? 0) ?></span>
                                </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="tw-px-6 tw-py-12 tw-text-center">
                            <i data-feather="inbox" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                            <p class="tw-text-gray-500">No rider data available for the selected period.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                        </tbody>
                    </table>
    </div>
</div>

<script>
// Analytics JavaScript functions
function changePeriod() {
    const period = document.getElementById('periodSelector').value;
    window.location.href = `/admin/analytics?period=${period}`;
}

function exportAnalytics() {
    const period = document.getElementById('periodSelector').value;
    window.open(`/admin/analytics/export?period=${period}`, '_blank');
}

function refreshAnalytics() {
    window.location.reload();
}

// Feather icons are initialized by the dashboard layout
</script>