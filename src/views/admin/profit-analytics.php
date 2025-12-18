<?php
/**
 * Admin Profit Analytics Page
 * Shows platform commission and profit breakdown
 */

// Set current page for sidebar highlighting
$currentPage = 'profit-analytics';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Profit Analytics</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Platform commission tracking and restaurant profit management
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <select id="period-filter" class="tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month" selected>This Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
            </select>
            <button onclick="exportProfitReport()" 
                    class="tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Export Report
            </button>
        </div>
    </div>
</div>

<!-- Profit Overview Cards -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Commission</p>
                <p class="tw-text-3xl tw-font-bold tw-text-green-600" id="total-commission">
                    <?= number_format($analytics['total_commission'] ?? 0) ?> XAF
                </p>
                <p class="tw-text-sm tw-text-gray-500">
                    <?= number_format($analytics['commission_percentage'] ?? 0, 1) ?>% of revenue
                </p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Restaurant Earnings</p>
                <p class="tw-text-3xl tw-font-bold tw-text-blue-600" id="restaurant-earnings">
                    <?= number_format($analytics['total_restaurant_earnings'] ?? 0) ?> XAF
                </p>
                <p class="tw-text-sm tw-text-gray-500">
                    <?= number_format(100 - ($analytics['commission_percentage'] ?? 0), 1) ?>% of revenue
                </p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="store" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Commission Rate</p>
                <p class="tw-text-3xl tw-font-bold tw-text-purple-600" id="avg-commission-rate">
                    <?= number_format($analytics['avg_commission_rate'] ?? 15, 1) ?>%
                </p>
                <p class="tw-text-sm tw-text-gray-500">
                    Across <?= $analytics['active_restaurants'] ?? 0 ?> restaurants
                </p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="percent" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Orders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-orange-600" id="total-orders">
                    <?= number_format($analytics['total_orders'] ?? 0) ?>
                </p>
                <p class="tw-text-sm tw-text-gray-500">
                    Delivered orders
                </p>
            </div>
            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                <i data-feather="shopping-cart" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Commission Breakdown Chart -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Revenue Breakdown</h2>
        </div>
        <div class="tw-p-6">
            <canvas id="revenue-chart" width="400" height="200"></canvas>
        </div>
    </div>

    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Commission Trends</h2>
        </div>
        <div class="tw-p-6">
            <canvas id="commission-chart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Restaurant Commission Rates Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Restaurant Commission Rates</h2>
            <button onclick="bulkUpdateCommissions()" 
                    class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-flex tw-items-center tw-justify-center">
                <i data-feather="edit" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Bulk Update
            </button>
        </div>
    </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Restaurant</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Commission Rate</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Orders</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Revenue</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Commission Earned</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="commission-table-body">
                <?php if (empty($restaurantCommissions)): ?>
                <tr>
                    <td colspan="6" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                        <div class="tw-flex tw-flex-col tw-items-center">
                            <i data-feather="bar-chart" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mb-4"></i>
                            <p class="tw-text-lg tw-font-medium">No commission data found</p>
                            <p class="tw-text-sm">Commission data will appear once orders are delivered.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($restaurantCommissions as $restaurant): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                                <i data-feather="coffee" class="tw-h-5 tw-w-5 tw-text-white"></i>
                            </div>
                            <div class="tw-ml-4">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($restaurant['name']) ?></div>
                                <div class="tw-text-sm tw-text-gray-500"><?= e($restaurant['cuisine_type'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-900">
                                <?= number_format(($restaurant['commission_rate'] ?? 0.15) * 100, 1) ?>%
                            </span>
                            <button class="tw-text-blue-600 hover:tw-text-blue-900 tw-text-xs" 
                                    onclick="editCommission(<?= $restaurant['id'] ?>, <?= ($restaurant['commission_rate'] ?? 0.15) * 100 ?>)"
                                    title="Edit Commission Rate">
                                <i data-feather="edit-2" class="tw-h-3 tw-w-3"></i>
                            </button>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                        <?= number_format($restaurant['total_orders'] ?? 0) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                        <?= number_format($restaurant['total_revenue'] ?? 0) ?> XAF
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-text-sm tw-font-medium tw-text-green-600">
                            <?= number_format(($restaurant['total_revenue'] ?? 0) * ($restaurant['commission_rate'] ?? 0.15)) ?> XAF
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-2">
                            <button class="tw-text-blue-600 hover:tw-text-blue-900" onclick="viewRestaurantProfit(<?= $restaurant['id'] ?>)">
                                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <button class="tw-text-green-600 hover:tw-text-green-900" onclick="generatePayoutReport(<?= $restaurant['id'] ?>)">
                                <i data-feather="file-text" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Initialize charts
    initializeCharts();
    
    // Set up period filter
    document.getElementById('period-filter').addEventListener('change', function() {
        updateAnalytics(this.value);
    });
});

function initializeCharts() {
    // Revenue breakdown pie chart
    const revenueCtx = document.getElementById('revenue-chart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'doughnut',
        data: {
            labels: ['Platform Commission', 'Restaurant Earnings'],
            datasets: [{
                data: [<?= $analytics['total_commission'] ?? 0 ?>, <?= $analytics['total_restaurant_earnings'] ?? 0 ?>],
                backgroundColor: ['#10B981', '#3B82F6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function updateAnalytics(period) {
    // Show loading state
    showLoading();
    
    fetch(`<?= url('/admin/profit-analytics/data') ?>?period=${period}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateDashboard(data.analytics);
        } else {
            showNotification('Error loading analytics: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading analytics', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

function showLoading() {
    // Add loading overlay
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay';
    overlay.className = 'tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50';
    overlay.innerHTML = '<div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-lg"><div class="tw-animate-spin tw-h-8 tw-w-8 tw-border-4 tw-border-primary-500 tw-border-t-transparent tw-rounded-full tw-mx-auto"></div><p class="tw-mt-4 tw-text-gray-600">Loading analytics...</p></div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        document.body.removeChild(overlay);
    }
}

function exportProfitReport() {
    const period = document.getElementById('period-filter').value;
    window.open(`<?= url('/admin/export/profit-report') ?>?period=${period}`, '_blank');
}

// Include commission editing functions from restaurants.php
function editCommission(restaurantId, currentRate) {
    const newRate = prompt(`Enter new commission rate for restaurant (current: ${currentRate}%):`, currentRate);
    
    if (newRate === null) return;
    
    const rate = parseFloat(newRate);
    if (isNaN(rate) || rate < 0 || rate > 100) {
        alert('Please enter a valid commission rate between 0 and 100');
        return;
    }
    
    fetch(`<?= url('/admin/restaurants/') ?>${restaurantId}/commission`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            commission_rate: rate / 100
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Commission rate updated successfully', 'success');
            // Refresh the page to show updated data
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error updating commission rate: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating commission rate. Please try again.');
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-z-50 tw-p-4 tw-rounded-lg tw-shadow-lg tw-transition-all tw-duration-300 ${
        type === 'success' ? 'tw-bg-green-500 tw-text-white' : 
        type === 'error' ? 'tw-bg-red-500 tw-text-white' : 
        'tw-bg-blue-500 tw-text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>
