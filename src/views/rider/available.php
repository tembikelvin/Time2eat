<?php
$title = $title ?? 'Available Orders - Time2Eat';
$user = $user ?? null;
$availableOrders = $availableOrders ?? [];
$currentPage = $currentPage ?? 'available';
$paginationPage = $paginationPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalOrders = $totalOrders ?? 0;
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Available Orders</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Browse and accept orders ready for delivery.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <div class="tw-flex tw-items-center tw-space-x-2">
                <span class="tw-text-sm tw-text-gray-500">Status:</span>
                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium <?= ($user->is_available ?? false) ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-red-100 tw-text-red-800' ?>">
                    <i data-feather="power" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                    <?= ($user->is_available ?? false) ? 'Online' : 'Offline' ?>
                </span>
            </div>
            <button onclick="refreshOrders()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-blue-100 tw-text-blue-800 hover:tw-bg-blue-200 tw-transition-colors">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Refresh
            </button>
        </div>
    </div>
</div>

<!-- Availability Notice -->
<?php if (!($user->is_available ?? false)): ?>
    <div class="tw-mb-6 tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg tw-p-4">
        <div class="tw-flex">
            <div class="tw-flex-shrink-0">
                <i data-feather="alert-triangle" class="tw-h-5 tw-w-5 tw-text-yellow-400"></i>
            </div>
            <div class="tw-ml-3">
                <h3 class="tw-text-sm tw-font-medium tw-text-yellow-800">
                    You're currently offline
                </h3>
                <div class="tw-mt-2 tw-text-sm tw-text-yellow-700">
                    <p>You need to go online to see and accept available orders.</p>
                </div>
                <div class="tw-mt-4">
                    <button onclick="toggleAvailability()" class="tw-bg-yellow-100 tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-yellow-800 hover:tw-bg-yellow-200">
                        Go Online Now
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Available Orders -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Available Orders</h2>
            <div class="tw-flex tw-items-center tw-space-x-4">
                <span class="tw-text-sm tw-text-gray-500">
                    <?= $totalOrders ?> orders available
                </span>
                <div class="tw-flex tw-items-center tw-space-x-2">
                    <span class="tw-h-2 tw-w-2 tw-bg-green-400 tw-rounded-full"></span>
                    <span class="tw-text-xs tw-text-gray-500">Auto-refresh every 30s</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tw-p-6">
        <?php if (empty($availableOrders)): ?>
            <div class="tw-text-center tw-py-12">
                <div class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400">
                    <i data-feather="search" class="tw-h-12 tw-w-12"></i>
                </div>
                <h3 class="tw-mt-2 tw-text-sm tw-font-medium tw-text-gray-900">No available orders</h3>
                <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                    <?php if (!($user->is_available ?? false)): ?>
                        Go online to see available orders in your area.
                    <?php else: ?>
                        There are no orders available for delivery at the moment. Check back soon!
                    <?php endif; ?>
                </p>
                <div class="tw-mt-6">
                    <button onclick="refreshOrders()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-shadow-sm tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                        <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Refresh Orders
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="tw-space-y-4">
                <?php foreach ($availableOrders as $order): ?>
                    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 hover:tw-border-orange-300 tw-transition-colors" id="order-<?= $order['id'] ?>">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-h-10 tw-w-10 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                    <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="tw-font-medium tw-text-gray-900">
                                        <?= e($order['restaurant_name'] ?? 'Restaurant') ?>
                                    </h3>
                                    <p class="tw-text-sm tw-text-gray-500">
                                        <?= $order['item_count'] ?? 1 ?> items • 
                                        <?= number_format($order['distance_km'] ?? 0, 1) ?> km • 
                                        <?= $order['estimated_time'] ?? '15' ?> min
                                    </p>
                                </div>
                            </div>
                            <div class="tw-text-right">
                                <p class="tw-font-medium tw-text-gray-900">
                                    <?= number_format($order['delivery_fee'] ?? 0) ?> XAF
                                </p>
                                <p class="tw-text-green-600 tw-text-sm">Earning</p>
                            </div>
                        </div>
                        
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-text-sm tw-text-gray-600 tw-mb-3">
                            <div>
                                <p><strong>Pickup:</strong> <?= e($order['restaurant_address'] ?? 'N/A') ?></p>
                                <p><strong>Customer:</strong> <?= e($order['customer_first_name'] ?? '') ?> <?= e($order['customer_last_name'] ?? '') ?></p>
                            </div>
                            <div>
                                <p><strong>Delivery:</strong> <?= e($order['delivery_address'] ?? 'N/A') ?></p>
                                <p><strong>Order Total:</strong> <?= number_format($order['total_amount'] ?? 0) ?> XAF</p>
                            </div>
                        </div>
                        
                        <div class="tw-flex tw-items-center tw-justify-between tw-text-xs tw-text-gray-500 tw-mb-3">
                            <span>Order #<?= e($order['order_number'] ?? $order['id']) ?></span>
                            <span>Ready <?= date('H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                        
                        <div class="tw-flex tw-space-x-2">
                            <button onclick="acceptOrder(<?= $order['id'] ?>)" 
                                    class="tw-flex-1 tw-bg-green-500 hover:tw-bg-green-600 tw-text-white tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                                <i data-feather="check" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                                Accept Order
                            </button>
                            <button onclick="viewOrderDetails(<?= $order['id'] ?>)" 
                                    class="tw-bg-gray-200 hover:tw-bg-gray-300 tw-text-gray-700 tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                                <i data-feather="eye" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                                Details
                            </button>
                            <button onclick="openMaps(<?= $order['restaurant_latitude'] ?? 0 ?>, <?= $order['restaurant_longitude'] ?? 0 ?>)" 
                                    class="tw-bg-blue-500 hover:tw-bg-blue-600 tw-text-white tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                                <i data-feather="map" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
                    <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-500">
                        Showing page <?= $currentPage ?> of <?= $totalPages ?>
                    </div>
                    <div class="tw-flex tw-space-x-2">
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= url('/rider/available?page=' . ($currentPage - 1)) ?>" 
                               class="tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= url('/rider/available?page=' . ($currentPage + 1)) ?>" 
                               class="tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 tw-max-w-md tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Order Details</h3>
                <button onclick="closeOrderModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            <div id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
let autoRefreshInterval;

function refreshOrders() {
    window.location.reload();
}

function acceptOrder(orderId) {
    if (!confirm('Are you sure you want to accept this order?')) {
        return;
    }
    
    const orderElement = document.getElementById(`order-${orderId}`);
    if (orderElement) {
        orderElement.style.opacity = '0.5';
        orderElement.style.pointerEvents = 'none';
    }
    
    fetch('<?= url('/rider/accept-order') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            order_id: orderId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order accepted successfully!');
            window.location.href = '<?= url('/rider/deliveries') ?>';
        } else {
            alert('Failed to accept order: ' + (data.message || 'Unknown error'));
            if (orderElement) {
                orderElement.style.opacity = '1';
                orderElement.style.pointerEvents = 'auto';
            }
        }
    })
    .catch(error => {
        console.error('Error accepting order:', error);
        alert('Failed to accept order. Please try again.');
        if (orderElement) {
            orderElement.style.opacity = '1';
            orderElement.style.pointerEvents = 'auto';
        }
    });
}

function viewOrderDetails(orderId) {
    // Show modal
    document.getElementById('orderModal').classList.remove('tw-hidden');
    
    // Load order details
    document.getElementById('orderDetailsContent').innerHTML = '<div class="tw-text-center tw-py-4">Loading...</div>';
    
    fetch(`/api/orders/${orderId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderDetails(data.order);
            } else {
                document.getElementById('orderDetailsContent').innerHTML = '<div class="tw-text-center tw-py-4 tw-text-red-600">Failed to load order details</div>';
            }
        })
        .catch(error => {
            console.error('Error loading order details:', error);
            document.getElementById('orderDetailsContent').innerHTML = '<div class="tw-text-center tw-py-4 tw-text-red-600">Failed to load order details</div>';
        });
}

function displayOrderDetails(order) {
    const content = `
        <div class="tw-space-y-4">
            <div>
                <h4 class="tw-font-medium tw-text-gray-900">Restaurant</h4>
                <p class="tw-text-sm tw-text-gray-600">${order.restaurant_name}</p>
                <p class="tw-text-sm tw-text-gray-500">${order.restaurant_address}</p>
            </div>
            <div>
                <h4 class="tw-font-medium tw-text-gray-900">Customer</h4>
                <p class="tw-text-sm tw-text-gray-600">${order.customer_name}</p>
                <p class="tw-text-sm tw-text-gray-500">${order.delivery_address}</p>
            </div>
            <div>
                <h4 class="tw-font-medium tw-text-gray-900">Order Items</h4>
                <div class="tw-text-sm tw-text-gray-600">
                    ${order.items ? order.items.map(item => `<p>• ${item.name} x${item.quantity}</p>`).join('') : '<p>Items not available</p>'}
                </div>
            </div>
            <div class="tw-flex tw-justify-between tw-pt-4 tw-border-t">
                <span class="tw-font-medium">Delivery Fee:</span>
                <span class="tw-font-medium">${order.delivery_fee} XAF</span>
            </div>
        </div>
    `;
    document.getElementById('orderDetailsContent').innerHTML = content;
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.add('tw-hidden');
}

function openMaps(lat, lng) {
    if (lat && lng) {
        const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
        window.open(url, '_blank');
    } else {
        alert('Location coordinates not available');
    }
}

function toggleAvailability() {
    // Get CSRF token
    const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    
    fetch('<?= url('/rider/toggle-availability') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to update availability: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error toggling availability:', error);
        alert('Failed to update availability. Please try again.');
    });
}

// Auto-refresh orders every 30 seconds
document.addEventListener('DOMContentLoaded', function() {
    autoRefreshInterval = setInterval(function() {
        if (<?= ($user->is_available ?? false) ? 'true' : 'false' ?>) {
            refreshOrders();
        }
    }, 30000);
});

// Close modal when clicking outside
document.getElementById('orderModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeOrderModal();
    }
});
</script>
