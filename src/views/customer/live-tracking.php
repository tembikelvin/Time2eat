<?php
/**
 * Live Order Tracking with Leaflet Map
 */

$user = $user ?? null;
$order = $order ?? null;
$rider = $rider ?? null;
$delivery = $delivery ?? null;
$error = $error ?? '';

require_once __DIR__ . '/../../helpers/MapHelper.php';
$mapHelper = new MapHelper();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?= e($order['order_number'] ?? 'N/A') ?> - Time2Eat</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Map Scripts -->
    <?= $mapHelper->getGlobalConfig() ?>
    <?= $mapHelper->getScripts() ?>
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <style>
        .tw-prefix { /* Tailwind prefix */ }
        
        #tracking-map {
            height: 400px;
            width: 100%;
            border-radius: 12px;
            z-index: 1;
        }
        
        @media (min-width: 768px) {
            #tracking-map {
                height: 500px;
            }
        }
        
        /* Custom marker styles */
        .custom-marker {
            background: none;
            border: none;
        }
        
        .pulse-marker {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }
        
        .status-badge {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="tw-bg-gray-50">
    <!-- Header -->
    <header class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200 tw-sticky tw-top-0 tw-z-50">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-flex tw-justify-between tw-items-center tw-py-4">
                <div class="tw-flex tw-items-center">
                    <a href="<?= url('/customer/orders') ?>" class="tw-text-gray-600 hover:tw-text-gray-900 tw-mr-4">
                        <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="tw-text-lg tw-font-bold tw-text-gray-900">Track Order</h1>
                        <p class="tw-text-sm tw-text-gray-600">#<?= e($order['order_number'] ?? 'N/A') ?></p>
                    </div>
                </div>
                <button onclick="refreshTracking()" class="tw-p-2 tw-text-gray-600 hover:tw-text-gray-900 hover:tw-bg-gray-100 tw-rounded-lg tw-transition-colors">
                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-6">
        <?php if ($error): ?>
            <!-- Error Message -->
            <div class="tw-mb-6 tw-p-4 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg">
                <div class="tw-flex tw-items-center">
                    <svg class="tw-w-5 tw-h-5 tw-text-red-600 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="tw-text-sm tw-font-medium tw-text-red-800"><?= e($error) ?></span>
                </div>
            </div>
        <?php elseif ($order): ?>
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
                <!-- Map Section -->
                <div class="lg:tw-col-span-2">
                    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4 tw-mb-6">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                            <h2 class="tw-text-lg tw-font-bold tw-text-gray-900">Live Tracking</h2>
                            <span id="last-updated" class="tw-text-xs tw-text-gray-500">Just now</span>
                        </div>
                        <div id="tracking-map" class="tw-rounded-xl tw-overflow-hidden tw-shadow-inner"></div>
                        
                        <!-- Map Legend -->
                        <div class="tw-mt-4 tw-flex tw-flex-wrap tw-gap-4 tw-text-sm">
                            <div class="tw-flex tw-items-center">
                                <div class="tw-w-4 tw-h-4 tw-bg-orange-500 tw-rounded-full tw-mr-2"></div>
                                <span class="tw-text-gray-700">Restaurant</span>
                            </div>
                            <div class="tw-flex tw-items-center">
                                <div class="tw-w-4 tw-h-4 tw-bg-blue-500 tw-rounded-full tw-mr-2"></div>
                                <span class="tw-text-gray-700">Rider</span>
                            </div>
                            <div class="tw-flex tw-items-center">
                                <div class="tw-w-4 tw-h-4 tw-bg-green-500 tw-rounded-full tw-mr-2"></div>
                                <span class="tw-text-gray-700">Your Location</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Status Timeline -->
                    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                        <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4">Order Status</h3>
                        <div id="status-timeline" class="tw-space-y-4">
                            <!-- Timeline will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Order Details Sidebar -->
                <div class="lg:tw-col-span-1">
                    <!-- Order Info Card -->
                    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6 tw-mb-6">
                        <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4">Order Details</h3>
                        
                        <div class="tw-space-y-4">
                            <div>
                                <p class="tw-text-sm tw-text-gray-600">Restaurant</p>
                                <p class="tw-font-semibold tw-text-gray-900"><?= e($order['restaurant_name'] ?? 'N/A') ?></p>
                            </div>
                            
                            <div>
                                <p class="tw-text-sm tw-text-gray-600">Order Number</p>
                                <p class="tw-font-mono tw-text-sm tw-font-semibold tw-text-gray-900"><?= e($order['order_number'] ?? 'N/A') ?></p>
                            </div>
                            
                            <div>
                                <p class="tw-text-sm tw-text-gray-600">Status</p>
                                <span id="order-status-badge" class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium status-badge">
                                    <?= e(ucfirst($order['status'] ?? 'pending')) ?>
                                </span>
                            </div>
                            
                            <div>
                                <p class="tw-text-sm tw-text-gray-600">Total Amount</p>
                                <p class="tw-text-xl tw-font-bold tw-text-gray-900"><?= number_format($order['total_amount'] ?? 0) ?> XAF</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rider Info Card -->
                    <div id="rider-info-card" class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6 tw-mb-6" style="display: none;">
                        <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4">Delivery Rider</h3>
                        
                        <div class="tw-flex tw-items-center tw-mb-4">
                            <div class="tw-w-12 tw-h-12 tw-bg-gray-200 tw-rounded-full tw-mr-3 tw-flex tw-items-center tw-justify-center">
                                <svg class="tw-w-6 tw-h-6 tw-text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p id="rider-name" class="tw-font-semibold tw-text-gray-900">-</p>
                                <p id="rider-phone" class="tw-text-sm tw-text-gray-600">-</p>
                            </div>
                        </div>
                        
                        <div class="tw-space-y-2">
                            <a href="#" id="call-rider-btn" class="tw-block tw-w-full tw-bg-green-500 hover:tw-bg-green-600 tw-text-white tw-font-medium tw-py-2 tw-px-4 tw-rounded-lg tw-text-center tw-transition-colors">
                                <svg class="tw-w-5 tw-h-5 tw-inline tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                Call Rider
                            </a>
                        </div>
                    </div>

                    <!-- Estimated Time Card -->
                    <div class="tw-bg-gradient-to-br tw-from-orange-500 tw-to-red-500 tw-rounded-xl tw-shadow-sm tw-p-6 tw-text-white">
                        <h3 class="tw-text-lg tw-font-bold tw-mb-2">Estimated Delivery</h3>
                        <p id="estimated-time" class="tw-text-3xl tw-font-bold">--:--</p>
                        <p class="tw-text-sm tw-opacity-90 tw-mt-2">We'll notify you when your order arrives</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Map Provider initialized via MapHelper -->
    
    <script>
        // Initialize Feather icons
        feather.replace();

        // Order data
        const orderData = <?= json_encode([
            'id' => $order['id'] ?? null,
            'order_number' => $order['order_number'] ?? null,
            'status' => $order['status'] ?? 'pending',
            'restaurant_lat' => $order['restaurant_latitude'] ?? 5.9631,
            'restaurant_lng' => $order['restaurant_longitude'] ?? 10.1591,
            'delivery_address' => json_decode($order['delivery_address'] ?? '{}', true)
        ]) ?>;

        let mapProviderInstance = null;
        let map = null;
        let updateInterval;

        // Initialize map
        async function initMap() {
            const defaultLat = parseFloat(orderData.restaurant_lat) || 5.9631;
            const defaultLng = parseFloat(orderData.restaurant_lng) || 10.1591;

            const mapConfig = {
                provider: window.MAP_CONFIG ? window.MAP_CONFIG.provider : 'leaflet',
                apiKey: window.MAP_CONFIG ? window.MAP_CONFIG.apiKey : '',
                container: 'tracking-map',
                center: [defaultLat, defaultLng],
                zoom: 13
            };

            mapProviderInstance = new MapProvider(mapConfig);
            map = await mapProviderInstance.init();

            // Add restaurant marker
            mapProviderInstance.addMarker('restaurant', defaultLat, defaultLng, {
                icon: 'restaurant',
                title: 'Restaurant',
                popup: '<b>Restaurant</b><br>Pickup Location'
            });

            // Add customer marker if delivery address exists
            if (orderData.delivery_address && orderData.delivery_address.latitude) {
                const custLat = parseFloat(orderData.delivery_address.latitude);
                const custLng = parseFloat(orderData.delivery_address.longitude);

                mapProviderInstance.addMarker('customer', custLat, custLng, {
                    icon: 'customer',
                    title: 'Your Location',
                    popup: '<b>Your Location</b><br>Delivery Address'
                });

                // Fit bounds to show both markers
                const bounds = [
                    [defaultLat, defaultLng],
                    [custLat, custLng]
                ];
                mapProviderInstance.fitBounds(bounds);
            }

            // Start tracking updates
            startTracking();
        }

        // Update rider location
        function updateRiderLocation(lat, lng) {
            // Update or add rider marker
            if (!mapProviderInstance.markers['rider']) {
                mapProviderInstance.addMarker('rider', lat, lng, {
                    icon: 'rider',
                    title: 'Delivery Rider',
                    popup: '<b>Delivery Rider</b><br>On the way!'
                });
            } else {
                mapProviderInstance.updateMarker('rider', lat, lng);
            }

            // Update route line
            // Remove existing route line if any
            mapProviderInstance.removePolyline('route');

            // Draw new route line if customer marker exists
            if (mapProviderInstance.markers['customer']) {
                const custPos = orderData.delivery_address;
                if (custPos && custPos.latitude) {
                    const routeCoords = [
                        [lat, lng],
                        [parseFloat(custPos.latitude), parseFloat(custPos.longitude)]
                    ];

                    mapProviderInstance.drawPolyline(routeCoords, {
                        id: 'route',
                        color: '#3B82F6',
                        weight: 3,
                        opacity: 0.7,
                        dashArray: '10, 10' // Note: dashArray might not work on Google Maps via MapProvider yet, but that's acceptable
                    });
                }
            }
        }

        // Start tracking
        function startTracking() {
            updateInterval = setInterval(refreshTracking, 5000); // Update every 5 seconds
            refreshTracking(); // Initial check
        }

        // Refresh tracking data
        function refreshTracking() {
            if (!orderData.id) return;

            fetch(`<?= url('/api/tracking/order/') ?>${orderData.id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.rider_location) {
                        updateRiderLocation(
                            parseFloat(data.rider_location.latitude),
                            parseFloat(data.rider_location.longitude)
                        );
                        
                        // Update last updated time
                        const lastUpdatedEl = document.getElementById('last-updated');
                        if (lastUpdatedEl) lastUpdatedEl.textContent = 'Just now';
                        
                        // Update rider info
                        if (data.rider) {
                            const riderCard = document.getElementById('rider-info-card');
                            if (riderCard) riderCard.style.display = 'block';
                            
                            const riderName = document.getElementById('rider-name');
                            if (riderName) riderName.textContent = data.rider.name;
                            
                            const riderPhone = document.getElementById('rider-phone');
                            if (riderPhone) riderPhone.textContent = data.rider.phone;
                            
                            const callBtn = document.getElementById('call-rider-btn');
                            if (callBtn) callBtn.href = `tel:${data.rider.phone}`;
                        }
                    }
                })
                .catch(error => console.error('Error fetching tracking data:', error));
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        });
    </script>
</body>
</html>

