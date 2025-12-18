<?php
/**
 * Order Tracking Page
 */

$user = $user ?? null;
$order = $order ?? null;
$rider = $rider ?? null;
$tracking = $tracking ?? [];
$error = $error ?? '';
?>

<!-- Page Header -->
<div class="tw-mb-6">
    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Track Order</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Real-time tracking of your order delivery
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-3">
            <button onclick="refreshTracking()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-flex tw-items-center">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>
</div>

<?php if ($error): ?>
    <!-- Error Message -->
    <div class="tw-mb-6 tw-p-4 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg">
        <div class="tw-flex tw-items-center">
            <svg class="tw-w-5 tw-h-5 tw-text-red-600 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="tw-text-sm tw-font-medium tw-text-red-800"><?= htmlspecialchars($error) ?></span>
        </div>
    </div>
<?php elseif ($order): ?>
        <!-- Order Info -->
        <div class="tw-px-4 tw-py-4">
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4 tw-mb-4">
                <div class="tw-flex tw-items-center tw-space-x-3 tw-mb-3">
                    <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-overflow-hidden tw-bg-gray-100">
                        <?php if (!empty($order['restaurant_image'])): ?>
                            <img src="<?= htmlspecialchars($order['restaurant_image']) ?>" alt="<?= htmlspecialchars($order['restaurant_name']) ?>" class="tw-w-full tw-h-full tw-object-cover">
                        <?php else: ?>
                            <div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center">
                                <i data-feather="home" class="tw-w-6 tw-h-6 tw-text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="tw-flex-1">
                        <h3 class="tw-font-semibold tw-text-gray-900"><?= htmlspecialchars($order['restaurant_name']) ?></h3>
                        <p class="tw-text-sm tw-text-gray-600">Order #<?= htmlspecialchars($order['order_number']) ?></p>
                    </div>
                    <div class="tw-text-right">
                        <div class="tw-font-bold tw-text-gray-900"><?= number_format($order['total_amount'], 0) ?> XAF</div>
                        <div class="tw-text-sm tw-text-gray-600"><?= date('M j, H:i', strtotime($order['created_at'])) ?></div>
                    </div>
                </div>

                <!-- Current Status -->
                <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-blue-50 tw-rounded-lg">
                    <div class="tw-flex tw-items-center tw-space-x-2">
                        <div class="tw-w-3 tw-h-3 tw-bg-blue-600 tw-rounded-full tw-animate-pulse"></div>
                        <span class="tw-font-medium tw-text-blue-900" id="current-status">
                            <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                        </span>
                    </div>
                    <div class="tw-text-sm tw-text-blue-700" id="status-time">
                        <?= date('H:i', strtotime($order['updated_at'])) ?>
                    </div>
                </div>
            </div>

            <!-- Delivery Map -->
            <?php if (in_array(strtolower($order['status']), ['picked_up', 'on_the_way']) && $rider): ?>
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-mb-4 tw-overflow-hidden">
                    <div class="tw-p-4 tw-border-b tw-border-gray-100">
                        <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Live Tracking</h4>
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <div class="tw-w-10 tw-h-10 tw-rounded-full tw-overflow-hidden tw-bg-gray-100">
                                <?php if (!empty($rider['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars($rider['profile_image']) ?>" alt="<?= htmlspecialchars($rider['name']) ?>" class="tw-w-full tw-h-full tw-object-cover">
                                <?php else: ?>
                                    <div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center">
                                        <i data-feather="user" class="tw-w-5 tw-h-5 tw-text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="tw-flex-1">
                                <div class="tw-font-medium tw-text-gray-900"><?= htmlspecialchars($rider['name']) ?></div>
                                <div class="tw-text-sm tw-text-gray-600">Your delivery rider</div>
                            </div>
                            <div class="tw-flex tw-space-x-2">
                                <button onclick="callRider()" class="tw-p-2 tw-bg-green-100 tw-text-green-600 tw-rounded-lg hover:tw-bg-green-200 tw-transition-colors">
                                    <i data-feather="phone" class="tw-w-4 tw-h-4"></i>
                                </button>
                                <button onclick="messageRider()" class="tw-p-2 tw-bg-blue-100 tw-text-blue-600 tw-rounded-lg hover:tw-bg-blue-200 tw-transition-colors">
                                    <i data-feather="message-circle" class="tw-w-4 tw-h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Map Container -->
                    <div id="tracking-map" class="tw-h-64 tw-bg-gray-100 tw-relative">
                        <div class="tw-absolute tw-inset-0 tw-flex tw-items-center tw-justify-center">
                            <div class="tw-text-center">
                                <i data-feather="map-pin" class="tw-w-8 tw-h-8 tw-text-gray-400 tw-mx-auto tw-mb-2"></i>
                                <div class="tw-text-sm tw-text-gray-600">Loading map...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ETA -->
                    <div class="tw-p-4 tw-bg-green-50">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <i data-feather="clock" class="tw-w-4 tw-h-4 tw-text-green-600"></i>
                                <span class="tw-text-sm tw-font-medium tw-text-green-900">Estimated Arrival</span>
                            </div>
                            <span class="tw-font-bold tw-text-green-900" id="eta-time">
                                <?= $order['estimated_delivery_time'] ? date('H:i', strtotime($order['estimated_delivery_time'])) : '15-20 mins' ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Order Progress Timeline -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4 tw-mb-4">
                <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-4">Order Progress</h4>
                
                <div class="tw-space-y-4" id="progress-timeline">
                    <?php
                    $statuses = [
                        'pending' => ['icon' => 'clock', 'title' => 'Order Placed', 'desc' => 'Your order has been received'],
                        'confirmed' => ['icon' => 'check-circle', 'title' => 'Order Confirmed', 'desc' => 'Restaurant confirmed your order'],
                        'preparing' => ['icon' => 'chef-hat', 'title' => 'Preparing Food', 'desc' => 'Your food is being prepared'],
                        'ready' => ['icon' => 'package', 'title' => 'Ready for Pickup', 'desc' => 'Food is ready, waiting for rider'],
                        'picked_up' => ['icon' => 'truck', 'title' => 'Picked Up', 'desc' => 'Rider has collected your order'],
                        'on_the_way' => ['icon' => 'navigation', 'title' => 'On the Way', 'desc' => 'Rider is heading to your location'],
                        'delivered' => ['icon' => 'check-circle-2', 'title' => 'Delivered', 'desc' => 'Order delivered successfully']
                    ];
                    
                    $currentStatus = strtolower($order['status']);
                    $statusOrder = array_keys($statuses);
                    $currentIndex = array_search($currentStatus, $statusOrder);
                    
                    foreach ($statuses as $status => $info):
                        $statusIndex = array_search($status, $statusOrder);
                        $isCompleted = $statusIndex <= $currentIndex;
                        $isCurrent = $status === $currentStatus;
                        $iconClass = $isCompleted ? 'tw-text-green-600' : 'tw-text-gray-400';
                        $lineClass = $isCompleted ? 'tw-bg-green-600' : 'tw-bg-gray-300';
                        $textClass = $isCompleted ? 'tw-text-gray-900' : 'tw-text-gray-500';
                    ?>
                        <div class="tw-flex tw-items-start tw-space-x-3">
                            <div class="tw-flex tw-flex-col tw-items-center">
                                <div class="tw-w-8 tw-h-8 tw-rounded-full tw-flex tw-items-center tw-justify-center <?= $isCompleted ? 'tw-bg-green-100' : 'tw-bg-gray-100' ?>">
                                    <i data-feather="<?= $info['icon'] ?>" class="tw-w-4 tw-h-4 <?= $iconClass ?>"></i>
                                </div>
                                <?php if ($status !== 'delivered'): ?>
                                    <div class="tw-w-0.5 tw-h-8 tw-mt-2 <?= $lineClass ?>"></div>
                                <?php endif; ?>
                            </div>
                            <div class="tw-flex-1 tw-pb-8">
                                <div class="tw-font-medium <?= $textClass ?> <?= $isCurrent ? 'tw-text-blue-600' : '' ?>">
                                    <?= $info['title'] ?>
                                    <?php if ($isCurrent): ?>
                                        <span class="tw-inline-block tw-w-2 tw-h-2 tw-bg-blue-600 tw-rounded-full tw-ml-2 tw-animate-pulse"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="tw-text-sm tw-text-gray-600"><?= $info['desc'] ?></div>
                                <?php if ($isCompleted && isset($tracking[$status])): ?>
                                    <div class="tw-text-xs tw-text-gray-500 tw-mt-1">
                                        <?= date('H:i', strtotime($tracking[$status]['timestamp'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Delivery Confirmation -->
            <?php if (strtolower($order['status']) === 'on_the_way'): ?>
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4 tw-mb-4">
                    <div class="tw-text-center">
                        <div class="tw-w-16 tw-h-16 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i data-feather="truck" class="tw-w-8 tw-h-8 tw-text-green-600"></i>
                        </div>
                        <h4 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Your Order is On the Way!</h4>
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-4">
                            Your rider is heading to your location. You'll be able to confirm delivery once they arrive.
                        </p>
                        <div class="tw-bg-blue-50 tw-rounded-lg tw-p-3 tw-mb-4">
                            <div class="tw-flex tw-items-center tw-justify-center tw-space-x-2">
                                <i data-feather="clock" class="tw-w-4 tw-h-4 tw-text-blue-600"></i>
                                <span class="tw-text-sm tw-font-medium tw-text-blue-900">Estimated Arrival: <?= $order['estimated_delivery_time'] ? date('H:i', strtotime($order['estimated_delivery_time'])) : '15-20 mins' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif (strtolower($order['status']) === 'delivered'): ?>
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4 tw-mb-4">
                    <div class="tw-text-center">
                        <div class="tw-w-16 tw-h-16 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i data-feather="check-circle" class="tw-w-8 tw-h-8 tw-text-green-600"></i>
                        </div>
                        <h4 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Order Delivered Successfully!</h4>
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-4">
                            Thank you for choosing Time2Eat. We hope you enjoyed your meal!
                        </p>
                        <div class="tw-flex tw-space-x-3 tw-justify-center">
                            <button onclick="rateOrder(<?= $order['id'] ?>)" 
                                    class="tw-px-6 tw-py-2 tw-bg-orange-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-orange-600 tw-transition-colors">
                                <i data-feather="star" class="tw-w-4 tw-h-4 tw-inline tw-mr-1"></i>
                                Rate Order
                            </button>
                            <button onclick="reorderItems(<?= $order['id'] ?>)" 
                                    class="tw-px-6 tw-py-2 tw-bg-blue-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-blue-600 tw-transition-colors">
                                <i data-feather="repeat" class="tw-w-4 tw-h-4 tw-inline tw-mr-1"></i>
                                Reorder
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Delivery Address -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4 tw-mb-4">
                <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-3">Delivery Address</h4>
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <i data-feather="map-pin" class="tw-w-5 tw-h-5 tw-text-gray-400 tw-mt-0.5"></i>
                    <div class="tw-flex-1">
                        <div class="tw-text-sm tw-text-gray-900"><?= htmlspecialchars($order['delivery_address'] ?? 'Not specified') ?></div>
                        <?php if (!empty($order['delivery_instructions'])): ?>
                            <div class="tw-text-xs tw-text-gray-600 tw-mt-1">
                                <strong>Instructions:</strong> <?= htmlspecialchars($order['delivery_instructions']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4">
                <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-3">Order Items</h4>
                <div class="tw-space-y-3" id="order-items">
                    <div class="tw-text-sm tw-text-gray-600">Loading items...</div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Message Rider Modal -->
<div id="message-modal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-p-4 tw-hidden tw-z-50">
    <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-w-full tw-max-w-md">
        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Message Rider</h3>
        <div class="tw-space-y-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Message</label>
                <textarea id="rider-message" rows="3" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" placeholder="Type your message..."></textarea>
            </div>
            <div class="tw-flex tw-space-x-3">
                <button onclick="closeMessageModal()" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-200 tw-text-gray-800 tw-rounded-lg tw-font-medium hover:tw-bg-gray-300">
                    Cancel
                </button>
                <button onclick="sendMessage()" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-blue-700">
                    Send Message
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Load map scripts dynamically based on provider setting
require_once __DIR__ . '/../../helpers/MapHelper.php';
$mapHelper = \helpers\MapHelper::getInstance();
echo $mapHelper->getScripts();
?>

<script>
// Get CSRF token from session
const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
const orderId = <?= $order['id'] ?? 'null' ?>;
let trackingInterval;
let map;
let riderMarker;
let customerMarker;

// Initialize Feather Icons
function initializeFeatherIcons() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    } else {
        setTimeout(initializeFeatherIcons, 100);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initializeFeatherIcons();
    
    if (orderId) {
        loadOrderItems();
        initializeMap();
        startRealTimeTracking();
    }
});

// Load order items
function loadOrderItems() {
    fetch(`<?= url('/api/orders/') ?>${orderId}/items`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items) {
                let html = '';
                data.items.forEach(item => {
                    const imageUrl = item.image || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80';
                    html += `
                        <div class="tw-flex tw-items-center tw-justify-between tw-py-3 tw-border-b tw-border-gray-100 last:tw-border-0">
                            <div class="tw-flex tw-items-center tw-space-x-3 tw-flex-1">
                                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 tw-flex-shrink-0">
                                    <img src="${imageUrl}" alt="${item.name}" class="tw-w-full tw-h-full tw-object-cover" onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80'">
                                </div>
                                <div class="tw-flex-1">
                                    <div class="tw-flex tw-items-center tw-gap-2">
                                        <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-6 tw-h-6 tw-bg-orange-100 tw-text-orange-700 tw-rounded-full tw-text-xs tw-font-bold">${item.quantity}</span>
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-900">${item.name}</span>
                                    </div>
                                    ${item.special_instructions ? `<div class="tw-text-xs tw-text-gray-600 tw-mt-1">${item.special_instructions}</div>` : ''}
                                </div>
                            </div>
                            <div class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-ml-3">
                                ${parseInt(item.total_price).toLocaleString()} XAF
                            </div>
                        </div>
                    `;
                });
                document.getElementById('order-items').innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error loading order items:', error);
            document.getElementById('order-items').innerHTML = '<div class="tw-text-sm tw-text-red-600">Error loading items</div>';
        });
}

// Initialize map with MapProvider (supports both Google Maps and Leaflet)
async function initializeMap() {
    const mapElement = document.getElementById('tracking-map');
    if (!mapElement) return;

    try {
        // Initialize MapProvider
        const mapConfig = {
            provider: '<?= $mapHelper->getProvider() ?>',
            apiKey: '<?= $mapHelper->getGoogleMapsApiKey() ?>',
            container: 'tracking-map',
            center: [3.848, 11.502], // Default to Yaoundé
            zoom: 15
        };

        // Check if MapProvider class is available
        if (typeof MapProvider !== 'undefined') {
            const mapProvider = new MapProvider(mapConfig);
            map = await mapProvider.init();

            // Add customer location marker
            mapProvider.addMarker('customer', 3.848, 11.502, {
                title: 'Delivery Location',
                icon: 'customer'
            });

            // Store mapProvider for later use
            window.mapProvider = mapProvider;
        } else {
             throw new Error('MapProvider library not loaded');
        }
    } catch (error) {
        console.error('Error initializing map:', error);
        mapElement.innerHTML = `
            <div class="tw-absolute tw-inset-0 tw-flex tw-items-center tw-justify-center tw-bg-gray-100">
                <div class="tw-text-center">
                    <i data-feather="alert-circle" class="tw-w-8 tw-h-8 tw-text-red-400 tw-mx-auto tw-mb-2"></i>
                    <div class="tw-text-sm tw-text-gray-600">Error loading map</div>
                    <div class="tw-text-xs tw-text-gray-500">Tracking via status updates</div>
                </div>
            </div>
        `;
        initializeFeatherIcons();
    }
}

// Start real-time tracking
function startRealTimeTracking() {
    // Update every 30 seconds
    trackingInterval = setInterval(refreshTracking, 30000);
}

// Refresh tracking data
function refreshTracking() {
    if (!orderId) return;

    fetch(`<?= url('/api/orders/') ?>${orderId}/tracking`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateOrderStatus(data.order);
                if (data.rider && data.rider.location) {
                    updateRiderLocation(data.rider);
                }
                if (data.eta) {
                    updateETA(data.eta);
                }
            }
        })
        .catch(error => {
            console.error('Error refreshing tracking:', error);
        });
}

// Update order status
function updateOrderStatus(order) {
    const statusElement = document.getElementById('current-status');
    const timeElement = document.getElementById('status-time');
    
    if (statusElement) {
        statusElement.textContent = order.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    if (timeElement) {
        timeElement.textContent = new Date(order.updated_at).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// Update rider location on map (supports both Google Maps and Leaflet)
function updateRiderLocation(rider) {
    if (!map || !rider.location) return;

    const lat = parseFloat(rider.location.latitude);
    const lng = parseFloat(rider.location.longitude);

    try {
        // Use MapProvider if available
        if (window.mapProvider && typeof window.mapProvider.addMarker === 'function') {
            if (riderMarker) {
                window.mapProvider.updateMarker('rider', lat, lng);
            } else {
                window.mapProvider.addMarker('rider', lat, lng, {
                    title: 'Delivery Rider',
                    color: '#3B82F6',
                    icon: 'truck'
                });
                riderMarker = true; // Mark as created
            }

            // Fit bounds to show both markers
            window.mapProvider.fitBounds([
                [lat, lng],
                [3.848, 11.502] // Customer location
            ]);
        }
        // Google Maps fallback
        else if (typeof google !== 'undefined' && google.maps) {
            const position = { lat, lng };

            if (riderMarker && riderMarker.setPosition) {
                riderMarker.setPosition(position);
            } else {
                riderMarker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: 'Delivery Rider',
                    icon: {
                        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="16" cy="16" r="12" fill="#3B82F6" stroke="white" stroke-width="4"/>
                                <path d="M16 10l4 6h-8l4-6z" fill="white"/>
                            </svg>
                        `),
                        scaledSize: new google.maps.Size(32, 32)
                    }
                });
            }

            // Center map between rider and customer
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(position);
            if (customerMarker && customerMarker.getPosition) {
                bounds.extend(customerMarker.getPosition());
            }
            map.fitBounds(bounds);
        }
        // Leaflet fallback
        else if (typeof L !== 'undefined') {
            if (riderMarker && riderMarker.setLatLng) {
                riderMarker.setLatLng([lat, lng]);
            } else {
                const riderIcon = L.divIcon({
                    className: 'custom-marker',
                    html: '<div style="background: #3B82F6; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                riderMarker = L.marker([lat, lng], {
                    icon: riderIcon,
                    title: 'Delivery Rider'
                }).addTo(map);
            }

            // Fit bounds to show both markers
            const bounds = L.latLngBounds([
                [lat, lng],
                [3.848, 11.502] // Customer location
            ]);
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    } catch (error) {
        console.error('Error updating rider location:', error);
    }
}

// Update ETA
function updateETA(eta) {
    const etaElement = document.getElementById('eta-time');
    if (etaElement) {
        etaElement.textContent = eta;
    }
}

// Rider communication functions
function callRider() {
    <?php if ($rider && !empty($rider['phone'])): ?>
        window.location.href = 'tel:<?= htmlspecialchars($rider['phone']) ?>';
    <?php else: ?>
        alert('Rider contact not available');
    <?php endif; ?>
}

function messageRider() {
    document.getElementById('message-modal').classList.remove('tw-hidden');
}

function closeMessageModal() {
    document.getElementById('message-modal').classList.add('tw-hidden');
    document.getElementById('rider-message').value = '';
}

function sendMessage() {
    const message = document.getElementById('rider-message').value.trim();
    if (!message) {
        alert('Please enter a message');
        return;
    }

    fetch(`<?= url('/api/orders/') ?>${orderId}/message-rider`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeMessageModal();
            alert('Message sent to rider');
        } else {
            alert(data.message || 'Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message');
    });
}

// Rate order function
function rateOrder(orderId) {
    const rating = prompt('Please rate your order from 1 to 5 stars:');
    if (rating && rating >= 1 && rating <= 5) {
        fetch(`<?= url('/api/orders/') ?>${orderId}/rate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ rating: parseInt(rating) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thank you for rating your order!');
            } else {
                alert(data.message || 'Failed to submit rating');
            }
        })
        .catch(error => {
            console.error('Error rating order:', error);
            alert('Failed to submit rating');
        });
    }
}

// Reorder items function
function reorderItems(orderId) {
    if (confirm('Would you like to reorder the same items from this restaurant?')) {
        fetch(`<?= url('/api/orders/') ?>${orderId}/reorder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Items added to cart! Redirecting to checkout...');
                window.location.href = '<?= url('/cart') ?>';
            } else {
                alert(data.message || 'Failed to reorder items');
            }
        })
        .catch(error => {
            console.error('Error reordering items:', error);
            alert('Failed to reorder items');
        });
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (trackingInterval) {
        clearInterval(trackingInterval);
    }
});
</script>

<!-- Google Maps API (you'll need to add your API key) -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initializeMap"></script>
