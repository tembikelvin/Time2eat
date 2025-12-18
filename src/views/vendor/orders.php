<?php
$title = $title ?? 'Orders - Time2Eat';
$currentPage = $currentPage ?? 'orders';
$user = $user ?? null;
$orders = $orders ?? [];
$statusCounts = $statusCounts ?? [];
$currentStatus = $currentStatus ?? 'all';
$currentPageNum = $currentPageNum ?? 1;
$totalPages = $totalPages ?? 1;
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Orders</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Manage and track your restaurant orders.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <select id="statusFilter" onchange="filterOrders(this.value)" class="tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm tw-focus:ring-orange-500 tw-focus:border-orange-500">
                <option value="all" <?= $currentStatus === 'all' ? 'selected' : '' ?>>All Orders</option>
                <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $currentStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="preparing" <?= $currentStatus === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                <option value="ready" <?= $currentStatus === 'ready' ? 'selected' : '' ?>>Ready</option>
                <option value="picked_up" <?= $currentStatus === 'picked_up' ? 'selected' : '' ?>>Picked Up</option>
                <option value="delivered" <?= $currentStatus === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= $currentStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="button" onclick="refreshOrders()" 
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Refresh
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="tw-mb-8">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-blue-100">
                        <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Pending</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $statusCounts['pending'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-yellow-100">
                        <i data-feather="coffee" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Preparing</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $statusCounts['preparing'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-green-100">
                        <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Ready</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $statusCounts['ready'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-purple-100">
                        <i data-feather="truck" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Delivered</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $statusCounts['delivered'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900">Recent Orders</h2>
            </div>
            
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                    <thead class="tw-bg-gray-50">
                        <tr>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Order</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Customer</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Items</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Total</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Time</th>
                            <th class="tw-px-6 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                            <?php
                            $statusColors = [
                                'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
                                'confirmed' => 'tw-bg-blue-100 tw-text-blue-800',
                                'preparing' => 'tw-bg-orange-100 tw-text-orange-800',
                                'ready' => 'tw-bg-green-100 tw-text-green-800',
                                'picked_up' => 'tw-bg-purple-100 tw-text-purple-800',
                                'delivered' => 'tw-bg-green-100 tw-text-green-800',
                                'cancelled' => 'tw-bg-red-100 tw-text-red-800'
                            ];
                            $statusColor = $statusColors[$order['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                            ?>
                            <tr class="order-row" data-order-id="<?= $order['id'] ?>">
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <div class="tw-flex tw-items-center tw-space-x-2">
                                        <button type="button" onclick="toggleOrderDetails(<?= $order['id'] ?>)"
                                                class="tw-p-1 tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors"
                                                title="Toggle Details">
                                            <i data-feather="chevron-right" class="tw-h-4 tw-w-4 order-chevron-<?= $order['id'] ?>"></i>
                                        </button>
                                        <div>
                                            <div class="tw-text-sm tw-font-medium tw-text-gray-900">#<?= e($order['order_number']) ?></div>
                                            <div class="tw-text-sm tw-text-gray-500"><?= date('M j, Y', strtotime($order['created_at'])) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <div class="tw-flex tw-items-center">
                                        <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                            <span class="tw-text-white tw-font-semibold tw-text-xs">
                                                <?= strtoupper(substr($order['customer_first_name'] ?? 'C', 0, 1)) ?>
                                            </span>
                                        </div>
                                        <div class="tw-ml-3">
                                            <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                                <?= e(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')) ?>
                                            </div>
                                            <div class="tw-text-sm tw-text-gray-500"><?= e($order['customer_phone'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                    <?= $order['item_count'] ?? 0 ?> items
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">
                                    <?= number_format($order['total_amount']) ?> XAF
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <span class="tw-inline-flex tw-px-2 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full <?= $statusColor ?>">
                                        <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                    </span>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                    <?php
                                    $timeAgo = time() - strtotime($order['created_at']);
                                    if ($timeAgo < 60) {
                                        echo $timeAgo . ' sec ago';
                                    } elseif ($timeAgo < 3600) {
                                        echo floor($timeAgo / 60) . ' min ago';
                                    } else {
                                        echo floor($timeAgo / 3600) . ' hr ago';
                                    }
                                    ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-text-sm tw-font-medium">
                                    <div class="tw-flex tw-justify-end tw-space-x-2">
                                        <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                            <button type="button" onclick="startPreparing(<?= $order['id'] ?>)"
                                                    class="tw-px-3 tw-py-1 tw-bg-orange-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-lg hover:tw-bg-orange-700 tw-transition-colors tw-flex tw-items-center tw-space-x-1">
                                                <i data-feather="coffee" class="tw-h-3 tw-w-3"></i>
                                                <span>Preparing</span>
                                            </button>
                                            <button type="button" onclick="markReady(<?= $order['id'] ?>)"
                                                    class="tw-px-3 tw-py-1 tw-bg-green-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-lg hover:tw-bg-green-700 tw-transition-colors tw-flex tw-items-center tw-space-x-1">
                                                <i data-feather="check-circle" class="tw-h-3 tw-w-3"></i>
                                                <span>Ready</span>
                                            </button>
                                            <button type="button" onclick="cancelOrder(<?= $order['id'] ?>)"
                                                    class="tw-px-3 tw-py-1 tw-bg-red-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-lg hover:tw-bg-red-700 tw-transition-colors tw-flex tw-items-center tw-space-x-1">
                                                <i data-feather="x" class="tw-h-3 tw-w-3"></i>
                                                <span>Cancel</span>
                                            </button>
                                        <?php elseif ($order['status'] === 'preparing'): ?>
                                            <button type="button" onclick="markReady(<?= $order['id'] ?>)"
                                                    class="tw-px-3 tw-py-1 tw-bg-green-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-lg hover:tw-bg-green-700 tw-transition-colors tw-flex tw-items-center tw-space-x-1">
                                                <i data-feather="check-circle" class="tw-h-3 tw-w-3"></i>
                                                <span>Ready</span>
                                            </button>
                                            <button type="button" onclick="cancelOrder(<?= $order['id'] ?>)"
                                                    class="tw-px-3 tw-py-1 tw-bg-red-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-lg hover:tw-bg-red-700 tw-transition-colors tw-flex tw-items-center tw-space-x-1">
                                                <i data-feather="x" class="tw-h-3 tw-w-3"></i>
                                                <span>Cancel</span>
                                            </button>
                                        <?php elseif ($order['status'] === 'ready'): ?>
                                            <div class="tw-flex tw-items-center tw-space-x-2">
                                                <span class="tw-text-green-600 tw-text-xs tw-font-medium tw-flex tw-items-center tw-space-x-1">
                                                    <i data-feather="clock" class="tw-h-3 tw-w-3"></i>
                                                    <span>Waiting for Rider</span>
                                                </span>
                                            </div>
                                        <?php elseif ($order['status'] === 'picked_up'): ?>
                                            <span class="tw-text-blue-600 tw-text-xs tw-font-medium tw-flex tw-items-center tw-space-x-1">
                                                <i data-feather="truck" class="tw-h-3 tw-w-3"></i>
                                                <span>Out for Delivery</span>
                                            </span>
                                        <?php elseif ($order['status'] === 'delivered'): ?>
                                            <span class="tw-text-green-600 tw-text-xs tw-font-medium tw-flex tw-items-center tw-space-x-1">
                                                <i data-feather="check-circle" class="tw-h-3 tw-w-3"></i>
                                                <span>Delivered</span>
                                            </span>
                                        <?php elseif ($order['status'] === 'cancelled'): ?>
                                            <span class="tw-text-red-600 tw-text-xs tw-font-medium tw-flex tw-items-center tw-space-x-1">
                                                <i data-feather="x-circle" class="tw-h-3 tw-w-3"></i>
                                                <span>Cancelled</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <!-- Order Details Row (Hidden by default) -->
                            <tr id="order-details-<?= $order['id'] ?>" class="tw-hidden order-details-row">
                                <td colspan="7" class="tw-px-6 tw-py-4 tw-bg-gray-50">
                                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                                        <!-- Order Information -->
                                        <div class="tw-space-y-3">
                                            <h4 class="tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                                                <i data-feather="info" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                                Order Information
                                            </h4>
                                            <div class="tw-space-y-2 tw-text-sm">
                                                <div class="tw-flex tw-justify-between">
                                                    <span class="tw-text-gray-600">Order Number:</span>
                                                    <span class="tw-font-medium tw-text-gray-900">#<?= e($order['order_number']) ?></span>
                                                </div>
                                                <div class="tw-flex tw-justify-between">
                                                    <span class="tw-text-gray-600">Payment Method:</span>
                                                    <span class="tw-font-medium tw-text-gray-900"><?= ucfirst($order['payment_method'] ?? 'N/A') ?></span>
                                                </div>
                                                <div class="tw-flex tw-justify-between">
                                                    <span class="tw-text-gray-600">Payment Status:</span>
                                                    <span class="tw-font-medium tw-text-gray-900"><?= ucfirst($order['payment_status'] ?? 'N/A') ?></span>
                                                </div>
                                                <div class="tw-flex tw-justify-between">
                                                    <span class="tw-text-gray-600">Subtotal:</span>
                                                    <span class="tw-font-medium tw-text-gray-900"><?= number_format($order['subtotal'] ?? 0) ?> XAF</span>
                                                </div>
                                                <div class="tw-flex tw-justify-between">
                                                    <span class="tw-text-gray-600">Delivery Fee:</span>
                                                    <span class="tw-font-medium tw-text-gray-900"><?= number_format($order['delivery_fee'] ?? 0) ?> XAF</span>
                                                </div>
                                                <div class="tw-flex tw-justify-between tw-pt-2 tw-border-t tw-border-gray-300">
                                                    <span class="tw-text-gray-900 tw-font-semibold">Total:</span>
                                                    <span class="tw-font-bold tw-text-gray-900"><?= number_format($order['total_amount']) ?> XAF</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delivery Information -->
                                        <div class="tw-space-y-3">
                                            <h4 class="tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                                                <i data-feather="map-pin" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                                Delivery Information
                                            </h4>
                                            <div class="tw-space-y-2 tw-text-sm">
                                                <div>
                                                    <span class="tw-text-gray-600 tw-block tw-mb-1">Delivery Address:</span>
                                                    <span class="tw-font-medium tw-text-gray-900"><?= e($order['delivery_address'] ?? 'N/A') ?></span>
                                                </div>
                                                <?php if (!empty($order['delivery_instructions'])): ?>
                                                <div>
                                                    <span class="tw-text-gray-600 tw-block tw-mb-1">Delivery Instructions:</span>
                                                    <span class="tw-font-medium tw-text-gray-900"><?= e($order['delivery_instructions']) ?></span>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($order['special_instructions'])): ?>
                                                <div>
                                                    <span class="tw-text-gray-600 tw-block tw-mb-1">Special Instructions:</span>
                                                    <span class="tw-font-medium tw-text-gray-900"><?= e($order['special_instructions']) ?></span>
                                                </div>
                                                <?php endif; ?>
                                                <div class="tw-flex tw-justify-between">
                                                    <span class="tw-text-gray-600">Created:</span>
                                                    <span class="tw-font-medium tw-text-gray-900"><?= date('M j, Y H:i', strtotime($order['created_at'])) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Order Items (if available) -->
                                    <div class="tw-mt-4 tw-pt-4 tw-border-t tw-border-gray-300">
                                        <h4 class="tw-font-semibold tw-text-gray-900 tw-flex tw-items-center tw-mb-3">
                                            <i data-feather="shopping-bag" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                            Order Items
                                        </h4>
                                        <div id="order-items-<?= $order['id'] ?>" class="tw-text-sm tw-text-gray-600">
                                            <div class="tw-flex tw-items-center tw-space-x-2">
                                                <i data-feather="loader" class="tw-h-4 tw-w-4 tw-animate-spin"></i>
                                                <span>Loading items...</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                                <i data-feather="shopping-bag" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-4"></i>
                                <p>No orders found</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<script>
// Initialize Feather icons
document.addEventListener('DOMContentLoaded', function() {
    // Force re-initialization of Feather icons with delay
    setTimeout(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
            console.log('Orders page: Feather icons initialized');
            
            // Log which icons were found for debugging
            const iconElements = document.querySelectorAll('[data-feather]');
            console.log('Found ' + iconElements.length + ' icon elements on orders page');
            iconElements.forEach(function(element) {
                const iconName = element.getAttribute('data-feather');
                console.log('Icon: ' + iconName, element);
            });
        } else {
            console.error('Orders page: Feather icons not loaded');
        }
    }, 200);
});

// Also try to initialize immediately in case DOMContentLoaded already fired
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
            console.log('Orders page: Feather icons initialized (immediate)');
        }
    }, 100);
}

// Filter orders by status
document.getElementById('statusFilter').addEventListener('change', function() {
    const status = this.value;
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url;
});

// Refresh orders
function refreshOrders() {
    window.location.reload();
}

// Toggle order details dropdown
function toggleOrderDetails(orderId) {
    const detailsRow = document.getElementById(`order-details-${orderId}`);
    const chevron = document.querySelector(`.order-chevron-${orderId}`);
    const itemsContainer = document.getElementById(`order-items-${orderId}`);

    if (detailsRow.classList.contains('tw-hidden')) {
        // Show details
        detailsRow.classList.remove('tw-hidden');
        chevron.style.transform = 'rotate(90deg)';

        // Load order items if not already loaded
        if (itemsContainer && itemsContainer.innerHTML.includes('Loading')) {
            loadOrderItems(orderId);
        }
    } else {
        // Hide details
        detailsRow.classList.add('tw-hidden');
        chevron.style.transform = 'rotate(0deg)';
    }

    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Load order items
function loadOrderItems(orderId) {
    const itemsContainer = document.getElementById(`order-items-${orderId}`);

    console.log('Loading items for order:', orderId);

    fetch(`<?= url('/vendor/orders') ?>/${orderId}/items`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Order items data:', data);
        if (data.success && data.items) {
            let itemsHtml = '<div class="tw-space-y-2">';
            data.items.forEach(item => {
                const itemImage = item.image_url || item.image || '';
                itemsHtml += `
                    <div class="tw-flex tw-justify-between tw-items-center tw-py-2 tw-border-b tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            ${itemImage ? `<img src="${itemImage}" alt="${item.name}" class="tw-w-12 tw-h-12 tw-rounded tw-object-cover">` : '<div class="tw-w-12 tw-h-12 tw-rounded tw-bg-gray-200 tw-flex tw-items-center tw-justify-center"><i data-feather="image" class="tw-h-6 tw-w-6 tw-text-gray-400"></i></div>'}
                            <div>
                                <div class="tw-font-medium tw-text-gray-900">${item.name}</div>
                                <div class="tw-text-xs tw-text-gray-500">Qty: ${item.quantity}</div>
                            </div>
                        </div>
                        <div class="tw-text-right">
                            <div class="tw-font-medium tw-text-gray-900">${Number(item.price).toLocaleString()} XAF</div>
                            <div class="tw-text-xs tw-text-gray-500">Total: ${Number(item.quantity * item.price).toLocaleString()} XAF</div>
                        </div>
                    </div>
                `;
            });
            itemsHtml += '</div>';
            itemsContainer.innerHTML = itemsHtml;

            // Re-initialize feather icons for placeholder images
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        } else {
            itemsContainer.innerHTML = '<p class="tw-text-gray-500">No items found</p>';
        }
    })
    .catch(error => {
        console.error('Error loading items:', error);
        itemsContainer.innerHTML = `<p class="tw-text-red-500">Failed to load items: ${error.message}</p>`;
    });
}

// Update order status
function updateOrderStatus(orderId, status) {
    if (!status) return;

    fetch('<?= url('/vendor/orders/status') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            order_id: orderId, 
            status: status 
        }),
        credentials: 'same-origin'
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, try to parse as text first
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                }
            });
        }
    })
    .then(data => {
        if (data.success) {
            alert('Order status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update order status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the order status: ' + error.message);
    });
}

// Confirm order
function confirmOrder(orderId) {
    if (confirm('Are you sure you want to confirm this order?')) {
        updateOrderStatus(orderId, 'confirmed');
    }
}

// Start preparing order
function startPreparing(orderId) {
    if (confirm('Are you ready to start preparing this order?')) {
        updateOrderStatus(orderId, 'preparing');
    }
}

// Mark order as ready
function markReady(orderId) {
    if (confirm('Is this order ready for pickup?')) {
        updateOrderStatus(orderId, 'ready');
    }
}

// Cancel order
function cancelOrder(orderId) {
    const reason = prompt('Please provide a reason for cancellation:');
    if (reason && reason.trim()) {
        fetch('<?= url('/vendor/orders/status') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                order_id: orderId, 
                status: 'cancelled',
                notes: reason.trim()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to cancel order'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the order');
        });
    }
}


</script>
