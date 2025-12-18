<?php
$title = $title ?? 'Analytics - Time2Eat';
$currentPage = $currentPage ?? 'analytics';
$user = $user ?? null;
$salesData = $salesData ?? [];
$popularItems = $popularItems ?? [];
$customerAnalytics = $customerAnalytics ?? [];
$revenueBreakdown = $revenueBreakdown ?? [];
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Analytics</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Track your restaurant's performance and insights.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <select id="periodFilter" onchange="updateAnalytics(this.value)" class="tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm tw-focus:ring-orange-500 tw-focus:border-orange-500">
                <option value="7days">Last 7 days</option>
                <option value="30days" selected>Last 30 days</option>
                <option value="90days">Last 90 days</option>
                <option value="year">Last year</option>
            </select>
            <button type="button" onclick="exportReport()" 
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Export
            </button>
        </div>
    </div>
</div>

<!-- Analytics Content -->
        <!-- Key Metrics -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-blue-100">
                        <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Orders</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $salesData['summary']['total_orders'] ?? 0 ?></p>
                        <p class="tw-text-sm tw-text-gray-500">Total orders</p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-green-100">
                        <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Revenue</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($salesData['summary']['total_revenue'] ?? 0) ?> XAF</p>
                        <p class="tw-text-sm tw-text-gray-500">Total revenue</p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-yellow-100">
                        <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Order Value</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($salesData['summary']['avg_order_value'] ?? 0) ?> XAF</p>
                        <p class="tw-text-sm tw-text-gray-500">Average per order</p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-purple-100">
                        <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Unique Customers</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $salesData['summary']['unique_customers'] ?? 0 ?></p>
                        <p class="tw-text-sm tw-text-gray-500">Total customers</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
            <!-- Revenue Chart -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Revenue Trend</h3>
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
            
            <!-- Orders Chart -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Orders Trend</h3>
                <canvas id="ordersChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Popular Items & Customer Insights -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
            <!-- Popular Items -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Popular Menu Items</h3>
                <div class="tw-space-y-4">
                    <?php if (!empty($popularItems['popular_items'])): ?>
                        <?php foreach ($popularItems['popular_items'] as $index => $item): ?>
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center">
                                <div class="tw-w-8 tw-h-8 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                    <span class="tw-text-sm tw-font-medium tw-text-orange-600"><?= $index + 1 ?></span>
                                </div>
                                <div>
                                    <p class="tw-font-medium tw-text-gray-900"><?= e($item['name']) ?></p>
                                    <p class="tw-text-sm tw-text-gray-500"><?= $item['total_quantity'] ?> sold</p>
                                </div>
                            </div>
                            <div class="tw-text-right">
                                <p class="tw-font-medium tw-text-gray-900"><?= number_format($item['total_revenue']) ?> XAF</p>
                                <p class="tw-text-sm tw-text-gray-500"><?= number_format($item['price']) ?> XAF each</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <p class="tw-text-gray-500 tw-text-center tw-py-8">No data available</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Customer Insights -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Customer Insights</h3>
                <div class="tw-space-y-6">
                    <!-- Peak Hours -->
                    <div>
                        <h4 class="tw-font-medium tw-text-gray-900 tw-mb-2">Peak Order Hours</h4>
                        <div class="tw-space-y-2">
                            <?php 
                            $hourlyData = $revenueBreakdown['hourly_breakdown'] ?? [];
                            // Sort by order count and take top 3
                            usort($hourlyData, function($a, $b) {
                                return $b['order_count'] - $a['order_count'];
                            });
                            $peakHours = array_slice($hourlyData, 0, 3);
                            ?>
                            <?php if (!empty($peakHours)): ?>
                                <?php foreach ($peakHours as $peak): ?>
                                <div class="tw-flex tw-justify-between tw-items-center">
                                    <span class="tw-text-sm tw-text-gray-600"><?= date('g:00 A', mktime($peak['hour'], 0)) ?></span>
                                    <div class="tw-flex tw-items-center">
                                        <div class="tw-w-20 tw-bg-gray-200 tw-rounded-full tw-h-2 tw-mr-2">
                                            <div class="tw-bg-orange-600 tw-h-2 tw-rounded-full" style="width: <?= min(100, ($peak['order_count'] / max(1, max(array_column($peakHours, 'order_count')))) * 100) ?>%"></div>
                                        </div>
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-900"><?= $peak['order_count'] ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="tw-text-gray-500 tw-text-sm">No hourly data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Order Status Distribution -->
                    <div>
                        <h4 class="tw-font-medium tw-text-gray-900 tw-mb-2">Order Status Distribution</h4>
                        <canvas id="statusChart" width="300" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Performance Metrics</h3>
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
                <div class="tw-text-center">
                    <div class="tw-text-3xl tw-font-bold tw-text-green-600"><?= number_format($analytics['completionRate'] ?? 95, 1) ?>%</div>
                    <p class="tw-text-sm tw-text-gray-600">Order Completion Rate</p>
                </div>
                <div class="tw-text-center">
                    <div class="tw-text-3xl tw-font-bold tw-text-blue-600"><?= $analytics['avgPrepTime'] ?? 25 ?> min</div>
                    <p class="tw-text-sm tw-text-gray-600">Average Prep Time</p>
                </div>
                <div class="tw-text-center">
                    <div class="tw-text-3xl tw-font-bold tw-text-purple-600"><?= number_format($analytics['customerSatisfaction'] ?? 4.5, 1) ?>/5</div>
                    <p class="tw-text-sm tw-text-gray-600">Customer Rating</p>
                </div>
            </div>
        </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($salesData['daily_data'] ?? [], 'date')) ?>,
        datasets: [{
            label: 'Revenue (XAF)',
            data: <?= json_encode(array_column($salesData['daily_data'] ?? [], 'revenue')) ?>,
            borderColor: 'rgb(249, 115, 22)',
            backgroundColor: 'rgba(249, 115, 22, 0.1)',
            tension: 0.4
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

// Orders Chart
const ordersCtx = document.getElementById('ordersChart').getContext('2d');
const ordersChart = new Chart(ordersCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($salesData['daily_data'] ?? [], 'date')) ?>,
        datasets: [{
            label: 'Orders',
            data: <?= json_encode(array_column($salesData['daily_data'] ?? [], 'order_count')) ?>,
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: 'rgb(59, 130, 246)',
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
                beginAtZero: true
            }
        }
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Delivered', 'Preparing', 'Cancelled', 'Pending'],
        datasets: [{
            data: [
                <?= $customerAnalytics['total_customers'] ?? 0 ?>,
                <?= $salesData['summary']['total_orders'] - ($customerAnalytics['total_customers'] ?? 0) ?>,
                0,
                0
            ],
            backgroundColor: [
                'rgba(34, 197, 94, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(156, 163, 175, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});

// Period filter change
document.getElementById('periodFilter').addEventListener('change', function() {
    const period = this.value;
    const url = new URL(window.location);
    url.searchParams.set('period', period);
    window.location = url;
});

// Export report
function exportReport() {
    const period = document.getElementById('periodFilter').value;
    window.open(`/vendor/analytics/export?period=${period}`, '_blank');
}
</script>
