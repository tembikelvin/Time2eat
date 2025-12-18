<?php
/**
 * Admin Orders Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'orders';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Order Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Monitor and manage all orders across the platform
            </p>
        </div>
        <div class="tw-flex tw-space-x-2 sm:tw-space-x-3">
            <button onclick="exportOrders()" 
                    class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Export Orders">
                <i data-feather="download" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Export Orders</span>
            </button>
            <button class="tw-bg-primary-600 tw-border tw-border-primary-500 tw-text-white tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Refresh">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Refresh</span>
            </button>
        </div>
    </div>
</div>

<!-- Order Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Orders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_orders'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">+15% this month</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Orders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['active_orders'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">Real-time</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Completed Today</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['completed_today'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">+8% vs yesterday</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Revenue Today</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['revenue_today'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Order Status -->
<div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-indigo-50 tw-border tw-border-blue-200 tw-rounded-xl tw-p-6 tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                <i data-feather="activity" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
            <div class="tw-ml-4">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Live Order Tracking</h3>
                <p class="tw-text-sm tw-text-gray-600"><?= $stats['active_orders'] ?? 0 ?> orders currently being processed</p>
            </div>
        </div>
        <div class="tw-flex tw-space-x-4">
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-orange-600"><?= $stats['preparing'] ?? 0 ?></div>
                <div class="tw-text-xs tw-text-gray-600">Preparing</div>
            </div>
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-blue-600"><?= $stats['out_for_delivery'] ?? 0 ?></div>
                <div class="tw-text-xs tw-text-gray-600">Out for Delivery</div>
            </div>
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-yellow-600"><?= $stats['pending'] ?? 0 ?></div>
                <div class="tw-text-xs tw-text-gray-600">Pending</div>
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
                    <input type="text" id="order-search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-leading-5 tw-bg-white tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-1 focus:tw-ring-primary-500 focus:tw-border-primary-500" placeholder="Search by order ID, customer, or restaurant..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="tw-flex tw-space-x-4">
                <select id="status-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Status</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= ($status ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="preparing" <?= ($status ?? '') === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                    <option value="ready" <?= ($status ?? '') === 'ready' ? 'selected' : '' ?>>Ready</option>
                    <option value="picked_up" <?= ($status ?? '') === 'picked_up' ? 'selected' : '' ?>>Picked Up</option>
                    <option value="on_the_way" <?= ($status ?? '') === 'on_the_way' ? 'selected' : '' ?>>On the Way</option>
                    <option value="delivered" <?= ($status ?? '') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                
                <select id="date-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">All Orders</h2>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <span class="tw-text-sm tw-text-gray-500">Showing 1-20 of <?= number_format($stats['totalOrders'] ?? 0) ?> orders</span>
                <div class="tw-flex tw-space-x-1">
                    <button class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600">
                        <i data-feather="chevron-left" class="tw-h-4 tw-w-4"></i>
                    </button>
                    <button class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600">
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
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Order ID</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Customer</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Restaurant</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Time</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="orders-table-body">
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                        <div class="tw-flex tw-flex-col tw-items-center">
                            <i data-feather="shopping-bag" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mb-4"></i>
                            <p class="tw-text-lg tw-font-medium">No orders found</p>
                            <p class="tw-text-sm">No orders have been placed yet.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($order['id']) ?></div>
                        <div class="tw-text-sm tw-text-gray-500"><?= date('M j, H:i', strtotime($order['created_at'] ?? 'now')) ?></div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-white tw-font-semibold tw-text-xs">
                                    <?= strtoupper(substr($order['customer_name'] ?? 'U', 0, 1)) ?>
                                </span>
                            </div>
                            <div class="tw-ml-3">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($order['customer_name'] ?? 'Unknown') ?></div>
                                <div class="tw-text-sm tw-text-gray-500"><?= e($order['customer_email'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($order['restaurant_name'] ?? 'Unknown') ?></div>
                        <?php if (!empty($order['rider_name'])): ?>
                            <div class="tw-text-sm tw-text-gray-500">Rider: <?= e($order['rider_name']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                            <?php 
                            switch($order['status']) {
                                case 'pending': echo 'tw-bg-yellow-100 tw-text-yellow-800'; break;
                                case 'confirmed': echo 'tw-bg-blue-100 tw-text-blue-800'; break;
                                case 'preparing': echo 'tw-bg-orange-100 tw-text-orange-800'; break;
                                case 'out_for_delivery': echo 'tw-bg-purple-100 tw-text-purple-800'; break;
                                case 'delivered': echo 'tw-bg-green-100 tw-text-green-800'; break;
                                case 'cancelled': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                default: echo 'tw-bg-gray-100 tw-text-gray-800';
                            }
                            ?>">
                            <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">
                        <?= number_format((float)($order['total_amount'] ?? 0)) ?> XAF
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= date('H:i', strtotime($order['created_at'] ?? 'now')) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-2">
                            <button class="tw-text-primary-600 hover:tw-text-primary-900" onclick="viewOrder('<?= $order['id'] ?>')">
                                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <button class="tw-text-blue-600 hover:tw-text-blue-900" onclick="trackOrder('<?= $order['id'] ?>')">
                                <i data-feather="map-pin" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <?php if (in_array($order['status'], ['pending', 'confirmed', 'preparing'])): ?>
                                <button class="tw-text-red-600 hover:tw-text-red-900" onclick="cancelOrder('<?= $order['id'] ?>')">
                                    <i data-feather="x-circle" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($order['status'] === 'delivered'): ?>
                                <button class="tw-text-green-600 hover:tw-text-green-900" onclick="refundOrder('<?= $order['id'] ?>')">
                                    <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
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
                    Showing <span class="tw-font-medium">1</span> to <span class="tw-font-medium">20</span> of <span class="tw-font-medium"><?= number_format($stats['totalOrders'] ?? 0) ?></span> results
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
// Order management functions
function viewOrder(orderId) {
    window.location.href = '<?= url('/admin/orders/') ?>' + orderId;
}

function trackOrder(orderId) {
    window.location.href = '<?= url('/admin/orders/') ?>' + orderId + '/track';
}

function cancelOrder(orderId) {
    const reason = prompt('Enter cancellation reason (optional):');
    if (reason !== null) { // User didn't click cancel
        fetch('<?= url('/admin/orders/') ?>' + orderId + '/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ reason: reason || 'Cancelled by admin' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error cancelling order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the order.');
        });
    }
}

function refundOrder(orderId) {
    const amount = prompt('Enter refund amount (XAF):');
    if (amount && !isNaN(amount) && parseFloat(amount) > 0) {
        const reason = prompt('Enter refund reason:');
        if (reason) {
            fetch('<?= url('/admin/orders/') ?>' + orderId + '/refund', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    amount: parseFloat(amount),
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error processing refund: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing the refund.');
            });
        }
    } else if (amount !== null) {
        alert('Please enter a valid amount greater than 0');
    }
}

// Search and filter functionality
document.getElementById('order-search').addEventListener('input', function() {
    filterOrders();
});

document.getElementById('status-filter').addEventListener('change', function() {
    filterOrders();
});

document.getElementById('date-filter').addEventListener('change', function() {
    filterOrders();
});

function filterOrders() {
    const search = document.getElementById('order-search').value;
    const status = document.getElementById('status-filter').value;
    const date = document.getElementById('date-filter').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (date) params.append('date', date);
    
    // Update URL and reload content
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

function exportOrders() {
    // Show loading state
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="tw-animate-spin tw-h-4 tw-w-4 tw-inline tw-mr-2">⟳</i>Exporting...';
    button.disabled = true;

    // Create download link for orders export
    const link = document.createElement('a');
    link.href = '<?= url('/admin/export/orders') ?>';
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Reset button state after delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

    // Auto-refresh for real-time updates
    setInterval(function() {
    // In a real implementation, you would fetch updated order data
    // and update the table without full page reload
    console.log('Auto-refreshing order data...');
}, 30000); // Refresh every 30 seconds

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
