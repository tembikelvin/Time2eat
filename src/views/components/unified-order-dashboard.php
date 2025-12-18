<?php
/**
 * Unified Order Dashboard Component
 * Real-time order management interface for all user roles
 */

$userRole = $userRole ?? 'customer';
$currentPage = $currentPage ?? 'orders';
?>

<!-- Real-time Order Dashboard -->
<div class="tw-space-y-6" id="unified-order-dashboard">
    
    <!-- Dashboard Header -->
    <div class="tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center tw-space-x-4">
            <div class="tw-p-3 tw-rounded-xl tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-600 tw-shadow-lg">
                <i data-feather="activity" class="tw-h-8 tw-w-8 tw-text-white"></i>
            </div>
            <div>
                <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">
                    <?php
                    switch($userRole) {
                        case 'admin': echo 'Platform Order Management'; break;
                        case 'vendor': echo 'Restaurant Orders'; break;
                        case 'rider': echo 'Delivery Orders'; break;
                        default: echo 'My Orders'; break;
                    }
                    ?>
                </h1>
                <p class="tw-text-sm tw-text-gray-600">Real-time order coordination and tracking</p>
            </div>
        </div>
        
        <!-- Real-time Status Indicator -->
        <div class="tw-flex tw-items-center tw-space-x-3">
            <div class="tw-flex tw-items-center tw-space-x-2">
                <div class="tw-h-3 tw-w-3 tw-bg-green-500 tw-rounded-full tw-animate-pulse"></div>
                <span class="tw-text-sm tw-text-gray-600">Live Updates</span>
            </div>
            <button onclick="refreshDashboard()" 
                    class="tw-p-2 tw-rounded-lg tw-bg-white tw-border tw-border-gray-300 tw-text-gray-600 hover:tw-bg-gray-50 tw-transition-colors">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4"></i>
            </button>
        </div>
    </div>
    
    <!-- Order Statistics Cards -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6" id="order-stats">
        <!-- Stats will be loaded dynamically -->
    </div>
    
    <!-- Order Status Flow (for admin and vendors) -->
    <?php if (in_array($userRole, ['admin', 'vendor'])): ?>
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-6">
        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Order Flow Status</h3>
        <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 lg:tw-grid-cols-7 tw-gap-4" id="order-flow-status">
            <!-- Flow status will be loaded dynamically -->
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Order Filters and Search -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-flex-col tw-md:tw-flex-row tw-md:tw-items-center tw-md:tw-justify-between tw-space-y-4 tw-md:tw-space-y-0">
                <!-- Search -->
                <div class="tw-flex-1 tw-max-w-lg">
                    <div class="tw-relative">
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="search" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="order-search" 
                               class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-leading-5 tw-bg-white tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                               placeholder="Search orders by ID, customer, or restaurant...">
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="tw-flex tw-space-x-4">
                    <select id="status-filter" 
                            class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-rounded-lg">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready</option>
                        <option value="picked_up">Picked Up</option>
                        <option value="on_the_way">On the Way</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    
                    <select id="time-filter" 
                            class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-rounded-lg">
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
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Orders</h2>
                <div class="tw-flex tw-items-center tw-space-x-2">
                    <span class="tw-text-sm tw-text-gray-500" id="orders-count">Loading...</span>
                    <div class="tw-flex tw-space-x-1">
                        <button onclick="previousPage()" id="prev-btn" disabled
                                class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600 disabled:tw-opacity-50">
                            <i data-feather="chevron-left" class="tw-h-4 tw-w-4"></i>
                        </button>
                        <button onclick="nextPage()" id="next-btn" disabled
                                class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600 disabled:tw-opacity-50">
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
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Order</th>
                        <?php if ($userRole !== 'customer'): ?>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Customer</th>
                        <?php endif; ?>
                        <?php if ($userRole === 'admin' || $userRole === 'customer' || $userRole === 'rider'): ?>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Restaurant</th>
                        <?php endif; ?>
                        <?php if ($userRole === 'admin' || $userRole === 'customer' || $userRole === 'vendor'): ?>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Rider</th>
                        <?php endif; ?>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Time</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="orders-table-body">
                    <!-- Orders will be loaded dynamically -->
                </tbody>
            </table>
        </div>
        
        <!-- Loading State -->
        <div id="orders-loading" class="tw-p-8 tw-text-center">
            <div class="tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-blue-500 tw-mx-auto tw-mb-4"></div>
            <p class="tw-text-gray-500">Loading orders...</p>
        </div>
        
        <!-- Empty State -->
        <div id="orders-empty" class="tw-p-8 tw-text-center tw-hidden">
            <i data-feather="shopping-bag" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-4"></i>
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No orders found</h3>
            <p class="tw-text-gray-500">No orders match your current filters.</p>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="order-details-modal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 tw-max-w-4xl tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Order Details</h3>
            <button onclick="closeOrderModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-5 tw-w-5"></i>
            </button>
        </div>
        <div id="order-details-content">
            <!-- Order details will be loaded here -->
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="status-update-modal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Update Order Status</h3>
            <button onclick="closeStatusModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-5 tw-w-5"></i>
            </button>
        </div>
        <form id="status-update-form" class="tw-space-y-4">
            <input type="hidden" id="update-order-id" name="order_id">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">New Status</label>
                <select id="new-status" name="status" 
                        class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    <!-- Options will be populated based on current status -->
                </select>
            </div>
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Notes (Optional)</label>
                <textarea id="status-notes" name="notes" rows="3" 
                          class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500"
                          placeholder="Add any notes about this status change..."></textarea>
            </div>
            <div class="tw-flex tw-justify-end tw-space-x-3">
                <button type="button" onclick="closeStatusModal()" 
                        class="tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-blue-700">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Global variables
let currentPage = 0;
let currentFilters = {};
let userRole = '<?= $userRole ?>';
let refreshInterval;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    startRealTimeUpdates();
    setupEventListeners();
    feather.replace();
});

// Initialize dashboard
function initializeDashboard() {
    loadOrderStats();
    loadOrders();
    
    <?php if (in_array($userRole, ['admin', 'vendor'])): ?>
    loadOrderFlowStatus();
    <?php endif; ?>
}

// Start real-time updates
function startRealTimeUpdates() {
    // Update every 30 seconds
    refreshInterval = setInterval(() => {
        loadOrderStats();
        loadOrders(false); // Don't show loading state for auto-refresh
        
        <?php if (in_array($userRole, ['admin', 'vendor'])): ?>
        loadOrderFlowStatus();
        <?php endif; ?>
    }, 30000);
}

// Setup event listeners
function setupEventListeners() {
    // Search input
    document.getElementById('order-search').addEventListener('input', debounce(function() {
        currentFilters.search = this.value;
        currentPage = 0;
        loadOrders();
    }, 500));
    
    // Status filter
    document.getElementById('status-filter').addEventListener('change', function() {
        currentFilters.status = this.value;
        currentPage = 0;
        loadOrders();
    });
    
    // Time filter
    document.getElementById('time-filter').addEventListener('change', function() {
        currentFilters.time = this.value;
        currentPage = 0;
        loadOrders();
    });
    
    // Status update form
    document.getElementById('status-update-form').addEventListener('submit', function(e) {
        e.preventDefault();
        updateOrderStatus();
    });
}

// Load order statistics
function loadOrderStats() {
    fetch(`/api/unified-orders/stats?role=${userRole}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrderStats(data.stats);
            }
        })
        .catch(error => {
            console.error('Error loading order stats:', error);
        });
}

// Load orders
function loadOrders(showLoading = true) {
    if (showLoading) {
        document.getElementById('orders-loading').classList.remove('tw-hidden');
        document.getElementById('orders-empty').classList.add('tw-hidden');
    }

    const params = new URLSearchParams({
        role: userRole,
        limit: 20,
        offset: currentPage * 20,
        ...currentFilters
    });

    fetch(`/api/unified-orders/dashboard?${params}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('orders-loading').classList.add('tw-hidden');

            if (data.success) {
                renderOrders(data.orders);
                updatePagination(data.total_count, data.has_more);

                if (data.orders.length === 0) {
                    document.getElementById('orders-empty').classList.remove('tw-hidden');
                }
            } else {
                showAlert('Error loading orders: ' + data.message, 'error');
            }
        })
        .catch(error => {
            document.getElementById('orders-loading').classList.add('tw-hidden');
            console.error('Error loading orders:', error);
            showAlert('Error loading orders', 'error');
        });
}

// Load order flow status
function loadOrderFlowStatus() {
    fetch(`/api/unified-orders/flow-status?role=${userRole}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrderFlowStatus(data.flow_status);
            }
        })
        .catch(error => {
            console.error('Error loading flow status:', error);
        });
}

// Render order statistics
function renderOrderStats(stats) {
    const statsContainer = document.getElementById('order-stats');

    const statCards = [
        { key: 'total_orders', label: 'Total Orders', icon: 'shopping-bag', color: 'blue' },
        { key: 'active_orders', label: 'Active Orders', icon: 'clock', color: 'yellow' },
        { key: 'completed_today', label: 'Completed Today', icon: 'check-circle', color: 'green' },
        { key: 'revenue_today', label: 'Revenue Today', icon: 'dollar-sign', color: 'purple' }
    ];

    statsContainer.innerHTML = statCards.map(card => `
        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">${card.label}</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-gray-900">
                        ${card.key === 'revenue_today' ? formatCurrency(stats[card.key] || 0) : formatNumber(stats[card.key] || 0)}
                    </p>
                </div>
                <div class="tw-p-3 tw-bg-${card.color}-100 tw-rounded-full">
                    <i data-feather="${card.icon}" class="tw-h-6 tw-w-6 tw-text-${card.color}-600"></i>
                </div>
            </div>
        </div>
    `).join('');

    feather.replace();
}

// Render orders table
function renderOrders(orders) {
    const tbody = document.getElementById('orders-table-body');

    if (orders.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                    <div class="tw-flex tw-flex-col tw-items-center">
                        <i data-feather="shopping-bag" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mb-4"></i>
                        <p class="tw-text-lg tw-font-medium">No orders found</p>
                        <p class="tw-text-sm">No orders match your current filters.</p>
                    </div>
                </td>
            </tr>
        `;
        feather.replace();
        return;
    }

    tbody.innerHTML = orders.map(order => `
        <tr class="hover:tw-bg-gray-50" data-order-id="${order.id}">
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                <div class="tw-text-sm tw-font-medium tw-text-gray-900">#${order.order_number || order.id}</div>
                <div class="tw-text-sm tw-text-gray-500">${formatDateTime(order.created_at)}</div>
            </td>
            ${userRole !== 'customer' ? `
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                <div class="tw-flex tw-items-center">
                    <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <span class="tw-text-white tw-font-semibold tw-text-xs">
                            ${(order.customer_name || 'Unknown').charAt(0).toUpperCase()}
                        </span>
                    </div>
                    <div class="tw-ml-3">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">${order.customer_name || 'Unknown'}</div>
                        <div class="tw-text-sm tw-text-gray-500">${order.customer_email || 'N/A'}</div>
                    </div>
                </div>
            </td>
            ` : ''}
            ${['admin', 'customer', 'rider'].includes(userRole) ? `
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                <div class="tw-text-sm tw-font-medium tw-text-gray-900">${order.restaurant_name || 'Unknown'}</div>
            </td>
            ` : ''}
            ${['admin', 'customer', 'vendor'].includes(userRole) ? `
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                <div class="tw-text-sm tw-font-medium tw-text-gray-900">${order.rider_name || 'Not assigned'}</div>
            </td>
            ` : ''}
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium ${getStatusColor(order.status)}">
                    ${formatStatus(order.status)}
                </span>
            </td>
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">
                ${formatCurrency(order.total_amount)}
            </td>
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                ${formatTime(order.created_at)}
            </td>
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                <div class="tw-flex tw-space-x-2">
                    <button onclick="viewOrderDetails(${order.id})"
                            class="tw-text-blue-600 hover:tw-text-blue-900" title="View Details">
                        <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                    </button>
                    <button onclick="trackOrder(${order.id})"
                            class="tw-text-green-600 hover:tw-text-green-900" title="Track Order">
                        <i data-feather="map-pin" class="tw-h-4 tw-w-4"></i>
                    </button>
                    ${canUpdateStatus(order, userRole) ? `
                    <button onclick="openStatusModal(${order.id}, '${order.status}')"
                            class="tw-text-purple-600 hover:tw-text-purple-900" title="Update Status">
                        <i data-feather="edit" class="tw-h-4 tw-w-4"></i>
                    </button>
                    ` : ''}
                    ${canCancelOrder(order, userRole) ? `
                    <button onclick="cancelOrder(${order.id})"
                            class="tw-text-red-600 hover:tw-text-red-900" title="Cancel Order">
                        <i data-feather="x-circle" class="tw-h-4 tw-w-4"></i>
                    </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');

    feather.replace();
}

// Update pagination
function updatePagination(totalCount, hasMore) {
    const countElement = document.getElementById('orders-count');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');

    const start = currentPage * 20 + 1;
    const end = Math.min((currentPage + 1) * 20, totalCount);

    countElement.textContent = `Showing ${start}-${end} of ${formatNumber(totalCount)} orders`;

    prevBtn.disabled = currentPage === 0;
    nextBtn.disabled = !hasMore;

    prevBtn.classList.toggle('tw-opacity-50', currentPage === 0);
    nextBtn.classList.toggle('tw-opacity-50', !hasMore);
}

// View order details
function viewOrderDetails(orderId) {
    fetch(`/api/unified-orders/${orderId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrderDetailsModal(data);
                document.getElementById('order-details-modal').classList.remove('tw-hidden');
            } else {
                showAlert('Error loading order details: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading order details:', error);
            showAlert('Error loading order details', 'error');
        });
}

// Track order
function trackOrder(orderId) {
    window.open(`/tracking/${orderId}`, '_blank');
}

// Open status update modal
function openStatusModal(orderId, currentStatus) {
    document.getElementById('update-order-id').value = orderId;

    // Populate status options based on current status and user role
    const statusSelect = document.getElementById('new-status');
    const allowedStatuses = getAllowedStatusTransitions(currentStatus, userRole);

    statusSelect.innerHTML = allowedStatuses.map(status =>
        `<option value="${status.value}">${status.label}</option>`
    ).join('');

    document.getElementById('status-update-modal').classList.remove('tw-hidden');
}

// Update order status
function updateOrderStatus() {
    const formData = new FormData(document.getElementById('status-update-form'));

    fetch('/api/unified-orders/update-status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Order status updated successfully', 'success');
            closeStatusModal();
            loadOrders();
        } else {
            showAlert('Error updating status: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        showAlert('Error updating order status', 'error');
    });
}

// Cancel order
function cancelOrder(orderId) {
    const reason = prompt('Enter cancellation reason (optional):');
    if (reason !== null) {
        fetch(`/api/unified-orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason: reason || 'Cancelled by user' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Order cancelled successfully', 'success');
                loadOrders();
            } else {
                showAlert('Error cancelling order: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error cancelling order:', error);
            showAlert('Error cancelling order', 'error');
        });
    }
}

// Utility functions
function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'XAF',
        minimumFractionDigits: 0
    }).format(amount).replace('XAF', '') + ' XAF';
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

function formatStatus(status) {
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function getStatusColor(status) {
    const colors = {
        'pending': 'tw-bg-yellow-100 tw-text-yellow-800',
        'confirmed': 'tw-bg-blue-100 tw-text-blue-800',
        'preparing': 'tw-bg-orange-100 tw-text-orange-800',
        'ready': 'tw-bg-purple-100 tw-text-purple-800',
        'picked_up': 'tw-bg-indigo-100 tw-text-indigo-800',
        'on_the_way': 'tw-bg-blue-100 tw-text-blue-800',
        'delivered': 'tw-bg-green-100 tw-text-green-800',
        'cancelled': 'tw-bg-red-100 tw-text-red-800'
    };
    return colors[status] || 'tw-bg-gray-100 tw-text-gray-800';
}

function canUpdateStatus(order, userRole) {
    const updatableStatuses = {
        'admin': ['pending', 'confirmed', 'preparing', 'ready', 'picked_up', 'on_the_way'],
        'vendor': ['pending', 'confirmed', 'preparing', 'ready'],
        'rider': ['ready', 'picked_up', 'on_the_way'],
        'customer': []
    };

    return updatableStatuses[userRole]?.includes(order.status) || false;
}

function canCancelOrder(order, userRole) {
    const cancellableStatuses = ['pending', 'confirmed', 'preparing'];

    if (userRole === 'admin') {
        return !['delivered', 'cancelled'].includes(order.status);
    }

    if (userRole === 'customer') {
        return cancellableStatuses.includes(order.status);
    }

    return false;
}

function getAllowedStatusTransitions(currentStatus, userRole) {
    const transitions = {
        'pending': {
            'admin': [
                { value: 'confirmed', label: 'Confirm Order' },
                { value: 'cancelled', label: 'Cancel Order' }
            ],
            'vendor': [
                { value: 'confirmed', label: 'Confirm Order' },
                { value: 'cancelled', label: 'Cancel Order' }
            ]
        },
        'confirmed': {
            'admin': [
                { value: 'preparing', label: 'Start Preparing' },
                { value: 'cancelled', label: 'Cancel Order' }
            ],
            'vendor': [
                { value: 'preparing', label: 'Start Preparing' },
                { value: 'cancelled', label: 'Cancel Order' }
            ]
        },
        'preparing': {
            'admin': [
                { value: 'ready', label: 'Mark as Ready' },
                { value: 'cancelled', label: 'Cancel Order' }
            ],
            'vendor': [
                { value: 'ready', label: 'Mark as Ready' },
                { value: 'cancelled', label: 'Cancel Order' }
            ]
        },
        'ready': {
            'admin': [
                { value: 'picked_up', label: 'Mark as Picked Up' },
                { value: 'cancelled', label: 'Cancel Order' }
            ],
            'rider': [
                { value: 'picked_up', label: 'Pick Up Order' }
            ]
        },
        'picked_up': {
            'admin': [
                { value: 'on_the_way', label: 'On the Way' },
                { value: 'cancelled', label: 'Cancel Order' }
            ],
            'rider': [
                { value: 'on_the_way', label: 'On the Way' }
            ]
        },
        'on_the_way': {
            'admin': [
                { value: 'delivered', label: 'Mark as Delivered' },
                { value: 'failed', label: 'Mark as Failed' }
            ],
            'rider': [
                { value: 'delivered', label: 'Mark as Delivered' },
                { value: 'failed', label: 'Delivery Failed' }
            ]
        }
    };

    return transitions[currentStatus]?.[userRole] || [];
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-lg tw-shadow-lg tw-z-50 tw-max-w-sm ${
        type === 'success' ? 'tw-bg-green-100 tw-text-green-800 tw-border tw-border-green-200' :
        type === 'error' ? 'tw-bg-red-100 tw-text-red-800 tw-border tw-border-red-200' :
        'tw-bg-blue-100 tw-text-blue-800 tw-border tw-border-blue-200'
    }`;

    alertDiv.innerHTML = `
        <div class="tw-flex tw-items-center tw-justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="tw-ml-4 tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-4 tw-w-4"></i>
            </button>
        </div>
    `;

    document.body.appendChild(alertDiv);
    feather.replace();

    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Modal functions
function closeOrderModal() {
    document.getElementById('order-details-modal').classList.add('tw-hidden');
}

function closeStatusModal() {
    document.getElementById('status-update-modal').classList.add('tw-hidden');
    document.getElementById('status-update-form').reset();
}

// Pagination functions
function previousPage() {
    if (currentPage > 0) {
        currentPage--;
        loadOrders();
    }
}

function nextPage() {
    currentPage++;
    loadOrders();
}

// Refresh dashboard
function refreshDashboard() {
    loadOrderStats();
    loadOrders();

    <?php if (in_array($userRole, ['admin', 'vendor'])): ?>
    loadOrderFlowStatus();
    <?php endif; ?>

    showAlert('Dashboard refreshed', 'success');
}

// Render order details modal
function renderOrderDetailsModal(data) {
    const content = document.getElementById('order-details-content');
    const order = data.order;
    const items = data.items || [];
    const statusHistory = data.status_history || [];

    content.innerHTML = `
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6">
            <!-- Order Information -->
            <div class="tw-space-y-4">
                <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                    <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Order Information</h4>
                    <div class="tw-space-y-2 tw-text-sm">
                        <div class="tw-flex tw-justify-between">
                            <span class="tw-text-gray-600">Order Number:</span>
                            <span class="tw-font-medium">#${order.order_number || order.id}</span>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <span class="tw-text-gray-600">Status:</span>
                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium ${getStatusColor(order.status)}">
                                ${formatStatus(order.status)}
                            </span>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <span class="tw-text-gray-600">Total Amount:</span>
                            <span class="tw-font-medium">${formatCurrency(order.total_amount)}</span>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <span class="tw-text-gray-600">Order Date:</span>
                            <span class="tw-font-medium">${formatDateTime(order.created_at)}</span>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                    <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Order Items</h4>
                    <div class="tw-space-y-2">
                        ${items.map(item => `
                            <div class="tw-flex tw-justify-between tw-items-center tw-py-2 tw-border-b tw-border-gray-200 last:tw-border-b-0">
                                <div>
                                    <div class="tw-font-medium tw-text-sm">${item.name}</div>
                                    <div class="tw-text-xs tw-text-gray-500">Qty: ${item.quantity}</div>
                                </div>
                                <div class="tw-font-medium tw-text-sm">${formatCurrency(item.unit_price * item.quantity)}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>

            <!-- Status History -->
            <div class="tw-space-y-4">
                <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                    <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Status History</h4>
                    <div class="tw-space-y-3">
                        ${statusHistory.map(history => `
                            <div class="tw-flex tw-items-start tw-space-x-3">
                                <div class="tw-flex-shrink-0 tw-w-2 tw-h-2 tw-bg-blue-500 tw-rounded-full tw-mt-2"></div>
                                <div class="tw-flex-1">
                                    <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                        ${formatStatus(history.new_status)}
                                    </div>
                                    <div class="tw-text-xs tw-text-gray-500">
                                        ${formatDateTime(history.created_at)} by ${history.changed_by_name || 'System'}
                                    </div>
                                    ${history.notes ? `<div class="tw-text-xs tw-text-gray-600 tw-mt-1">${history.notes}</div>` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Render order flow status
function renderOrderFlowStatus(flowStatus) {
    const container = document.getElementById('order-flow-status');

    const statuses = [
        { key: 'pending', label: 'Pending', icon: 'clock' },
        { key: 'confirmed', label: 'Confirmed', icon: 'check' },
        { key: 'preparing', label: 'Preparing', icon: 'chef-hat' },
        { key: 'ready', label: 'Ready', icon: 'package' },
        { key: 'picked_up', label: 'Picked Up', icon: 'truck' },
        { key: 'on_the_way', label: 'On the Way', icon: 'navigation' },
        { key: 'delivered', label: 'Delivered', icon: 'check-circle' }
    ];

    container.innerHTML = statuses.map(status => `
        <div class="tw-text-center tw-p-4 tw-bg-white tw-rounded-lg tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-bg-blue-100 tw-rounded-full tw-mx-auto tw-mb-2">
                <i data-feather="${status.icon}" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
            <div class="tw-text-2xl tw-font-bold tw-text-gray-900">${formatNumber(flowStatus[status.key] || 0)}</div>
            <div class="tw-text-sm tw-text-gray-600">${status.label}</div>
        </div>
    `).join('');

    feather.replace();
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
