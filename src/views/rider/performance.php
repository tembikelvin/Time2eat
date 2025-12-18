<?php
$title = $title ?? 'Performance Metrics - Time2Eat';
$user = $user ?? null;
$currentPage = $currentPage ?? 'performance';
$performance = $performance ?? [];
$ratings = $ratings ?? [];
$deliveryTimes = $deliveryTimes ?? [];
$currentPeriod = $currentPeriod ?? '30days';
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Performance Metrics</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Track your delivery performance and customer satisfaction.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <select onchange="changePeriod(this.value)" class="tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm tw-focus:ring-orange-500 tw-focus:border-orange-500">
                <option value="7days" <?= $currentPeriod === '7days' ? 'selected' : '' ?>>Last 7 Days</option>
                <option value="30days" <?= $currentPeriod === '30days' ? 'selected' : '' ?>>Last 30 Days</option>
                <option value="90days" <?= $currentPeriod === '90days' ? 'selected' : '' ?>>Last 90 Days</option>
                <option value="all" <?= $currentPeriod === 'all' ? 'selected' : '' ?>>All Time</option>
            </select>
        </div>
    </div>
</div>

<!-- Performance Overview -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Deliveries</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">
                    <?= number_format($performance['total_deliveries'] ?? 0) ?>
                </p>
                <p class="tw-text-sm tw-text-blue-600">Completed</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="truck" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Success Rate</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">
                    <?= number_format($performance['success_rate'] ?? 0, 1) ?>%
                </p>
                <p class="tw-text-sm tw-text-green-600">Delivered</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Rating</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">
                    <?= number_format($performance['avg_rating'] ?? 0, 1) ?>
                </p>
                <div class="tw-flex tw-items-center tw-text-sm tw-text-yellow-600">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i data-feather="star" class="tw-h-3 tw-w-3 <?= $i <= ($performance['avg_rating'] ?? 0) ? 'tw-fill-current' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="star" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Delivery Time</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">
                    <?= number_format($performance['avg_delivery_time'] ?? 0) ?>
                </p>
                <p class="tw-text-sm tw-text-purple-600">Minutes</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
    <!-- Delivery Performance Chart -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Delivery Performance</h2>
            <p class="tw-text-sm tw-text-gray-500">Daily delivery completion over time</p>
        </div>
        <div class="tw-p-6">
            <canvas id="performanceChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Rating Distribution -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Rating Distribution</h2>
            <p class="tw-text-sm tw-text-gray-500">Customer ratings breakdown</p>
        </div>
        <div class="tw-p-6">
            <div class="tw-space-y-4">
                <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                    <?php 
                    $count = $ratings[$rating] ?? 0;
                    $total = array_sum($ratings ?: []);
                    $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                    ?>
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-flex tw-items-center tw-w-16">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-900"><?= $rating ?></span>
                            <i data-feather="star" class="tw-h-4 tw-w-4 tw-text-yellow-400 tw-ml-1"></i>
                        </div>
                        <div class="tw-flex-1 tw-bg-gray-200 tw-rounded-full tw-h-2">
                            <div class="tw-bg-yellow-400 tw-h-2 tw-rounded-full" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <div class="tw-w-12 tw-text-right">
                            <span class="tw-text-sm tw-text-gray-600"><?= $count ?></span>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Metrics -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
    <!-- Delivery Time Analysis -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Delivery Time Analysis</h2>
            <p class="tw-text-sm tw-text-gray-500">Breakdown of delivery performance</p>
        </div>
        <div class="tw-p-6">
            <div class="tw-space-y-4">
                <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-green-50 tw-rounded-lg">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-p-2 tw-bg-green-100 tw-rounded-full">
                            <i data-feather="zap" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-font-medium tw-text-gray-900">Fast Deliveries</p>
                            <p class="tw-text-xs tw-text-gray-500">Under 20 minutes</p>
                        </div>
                    </div>
                    <div class="tw-text-right">
                        <p class="tw-text-lg tw-font-bold tw-text-green-600">
                            <?= $deliveryTimes['fast'] ?? 0 ?>
                        </p>
                        <p class="tw-text-xs tw-text-gray-500">
                            <?= $performance['total_deliveries'] > 0 ? number_format((($deliveryTimes['fast'] ?? 0) / $performance['total_deliveries']) * 100, 1) : 0 ?>%
                        </p>
                    </div>
                </div>

                <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-yellow-50 tw-rounded-lg">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-p-2 tw-bg-yellow-100 tw-rounded-full">
                            <i data-feather="clock" class="tw-h-4 tw-w-4 tw-text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-font-medium tw-text-gray-900">Normal Deliveries</p>
                            <p class="tw-text-xs tw-text-gray-500">20-40 minutes</p>
                        </div>
                    </div>
                    <div class="tw-text-right">
                        <p class="tw-text-lg tw-font-bold tw-text-yellow-600">
                            <?= $deliveryTimes['normal'] ?? 0 ?>
                        </p>
                        <p class="tw-text-xs tw-text-gray-500">
                            <?= $performance['total_deliveries'] > 0 ? number_format((($deliveryTimes['normal'] ?? 0) / $performance['total_deliveries']) * 100, 1) : 0 ?>%
                        </p>
                    </div>
                </div>

                <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-red-50 tw-rounded-lg">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-p-2 tw-bg-red-100 tw-rounded-full">
                            <i data-feather="alert-triangle" class="tw-h-4 tw-w-4 tw-text-red-600"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-font-medium tw-text-gray-900">Slow Deliveries</p>
                            <p class="tw-text-xs tw-text-gray-500">Over 40 minutes</p>
                        </div>
                    </div>
                    <div class="tw-text-right">
                        <p class="tw-text-lg tw-font-bold tw-text-red-600">
                            <?= $deliveryTimes['slow'] ?? 0 ?>
                        </p>
                        <p class="tw-text-xs tw-text-gray-500">
                            <?= $performance['total_deliveries'] > 0 ? number_format((($deliveryTimes['slow'] ?? 0) / $performance['total_deliveries']) * 100, 1) : 0 ?>%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tips -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Performance Tips</h2>
            <p class="tw-text-sm tw-text-gray-500">Suggestions to improve your metrics</p>
        </div>
        <div class="tw-p-6">
            <div class="tw-space-y-4">
                <?php if (($performance['avg_rating'] ?? 0) < 4.5): ?>
                    <div class="tw-flex tw-items-start tw-space-x-3 tw-p-3 tw-bg-blue-50 tw-rounded-lg">
                        <div class="tw-p-1 tw-bg-blue-100 tw-rounded-full tw-mt-1">
                            <i data-feather="star" class="tw-h-3 tw-w-3 tw-text-blue-600"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-font-medium tw-text-gray-900">Improve Customer Rating</p>
                            <p class="tw-text-xs tw-text-gray-600">
                                Communicate with customers, handle food carefully, and be punctual to boost your rating.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (($performance['avg_delivery_time'] ?? 0) > 30): ?>
                    <div class="tw-flex tw-items-start tw-space-x-3 tw-p-3 tw-bg-yellow-50 tw-rounded-lg">
                        <div class="tw-p-1 tw-bg-yellow-100 tw-rounded-full tw-mt-1">
                            <i data-feather="clock" class="tw-h-3 tw-w-3 tw-text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-font-medium tw-text-gray-900">Reduce Delivery Time</p>
                            <p class="tw-text-xs tw-text-gray-600">
                                Plan your routes efficiently and use GPS navigation to reduce delivery times.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (($performance['success_rate'] ?? 0) < 95): ?>
                    <div class="tw-flex tw-items-start tw-space-x-3 tw-p-3 tw-bg-red-50 tw-rounded-lg">
                        <div class="tw-p-1 tw-bg-red-100 tw-rounded-full tw-mt-1">
                            <i data-feather="alert-triangle" class="tw-h-3 tw-w-3 tw-text-red-600"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-font-medium tw-text-gray-900">Improve Success Rate</p>
                            <p class="tw-text-xs tw-text-gray-600">
                                Double-check addresses and contact customers if you can't find the location.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="tw-flex tw-items-start tw-space-x-3 tw-p-3 tw-bg-green-50 tw-rounded-lg">
                    <div class="tw-p-1 tw-bg-green-100 tw-rounded-full tw-mt-1">
                        <i data-feather="trending-up" class="tw-h-3 tw-w-3 tw-text-green-600"></i>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900">Stay Active</p>
                        <p class="tw-text-xs tw-text-gray-600">
                            Work during peak hours (lunch and dinner) to maximize your earnings and deliveries.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changePeriod(period) {
    window.location.href = `/rider/performance?period=${period}`;
}

// Performance Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    // Real data from server
    const dailyData = <?= json_encode($dailyPerformance ?? []) ?>;
    
    // Prepare chart data
    const labels = [];
    const completedData = [];
    const failedData = [];
    
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    Object.keys(dailyData).forEach(date => {
        const dateObj = new Date(date);
        labels.push(dayNames[dateObj.getDay()]);
        completedData.push(dailyData[date].completed);
        failedData.push(dailyData[date].failed);
    });
    
    const chartData = {
        labels: labels,
        datasets: [{
            label: 'Completed Deliveries',
            data: completedData,
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 2,
            fill: true
        }, {
            label: 'Failed Deliveries',
            data: failedData,
            backgroundColor: 'rgba(239, 68, 68, 0.8)',
            borderColor: 'rgb(239, 68, 68)',
            borderWidth: 2,
            fill: true
        }]
    };
    
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            elements: {
                line: {
                    tension: 0.4
                }
            }
        }
    });
});
</script>
