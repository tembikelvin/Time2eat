<?php
$title = $title ?? 'Live Tracking - Time2Eat';
$delivery = $delivery ?? null;
$pickup_location = $pickup_location ?? null;
$delivery_location = $delivery_location ?? null;
$rider_location = $rider_location ?? null;

// Load global map configuration
// Ensure database connection is available
if (!function_exists('dbConnection')) {
    $dbConfigPath = __DIR__ . '/../../../config/database.php';
    if (file_exists($dbConfigPath)) {
        require_once $dbConfigPath;
    }
}

require_once __DIR__ . '/../../helpers/MapHelper.php';
$db = function_exists('dbConnection') ? dbConnection() : null;
$mapHelper = \helpers\MapHelper::getInstance($db);
?>

<!DOCTYPE html>
<html lang="en" class="tw-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'tw-',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fff7ed',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons@4.29.0/dist/feather.min.js"></script>
    
    <!-- Map Integration -->
    <?php
    echo $mapHelper->getGlobalConfig();
    echo $mapHelper->getScripts();
    ?>
</head>
<body class="tw-min-h-full tw-bg-gray-50">
    <!-- Header -->
    <header class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-flex tw-justify-between tw-items-center tw-py-4">
                <div class="tw-flex tw-items-center">
                    <a href="/" class="tw-flex tw-items-center">
                        <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                            <i data-feather="zap" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <h1 class="tw-ml-3 tw-text-xl tw-font-bold tw-text-gray-900">Time2Eat</h1>
                    </a>
                </div>
                <div class="tw-flex tw-items-center tw-space-x-4">
                    <span class="tw-text-sm tw-text-gray-600">Tracking: <?= e($delivery['tracking_code']) ?></span>
                </div>
            </div>
        </div>
    </header>

    <main class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-6">
        <!-- Order Status Header -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6 tw-mb-6">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Order #<?= e($delivery['order_number']) ?></h1>
                <div class="tw-flex tw-items-center tw-space-x-2">
                    <div class="tw-h-3 tw-w-3 tw-bg-green-500 tw-rounded-full tw-animate-pulse" id="status-indicator"></div>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-900" id="status-text"><?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?></span>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
                <div class="tw-flex tw-items-center tw-space-x-4 tw-w-full">
                    <!-- Order Placed -->
                    <div class="tw-flex tw-items-center">
                        <div class="tw-h-8 tw-w-8 tw-bg-green-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                            <i data-feather="check" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <span class="tw-ml-2 tw-text-sm tw-font-medium tw-text-gray-900">Placed</span>
                    </div>
                    <div class="tw-flex-1 tw-h-px tw-bg-gray-300" id="progress-1"></div>

                    <!-- Picked Up -->
                    <div class="tw-flex tw-items-center">
                        <div class="tw-h-8 tw-w-8 tw-rounded-full tw-flex tw-items-center tw-justify-center" id="pickup-step">
                            <span class="tw-text-sm tw-font-medium">2</span>
                        </div>
                        <span class="tw-ml-2 tw-text-sm tw-font-medium tw-text-gray-600">Picked Up</span>
                    </div>
                    <div class="tw-flex-1 tw-h-px tw-bg-gray-300" id="progress-2"></div>

                    <!-- Out for Delivery -->
                    <div class="tw-flex tw-items-center">
                        <div class="tw-h-8 tw-w-8 tw-rounded-full tw-flex tw-items-center tw-justify-center" id="delivery-step">
                            <span class="tw-text-sm tw-font-medium">3</span>
                        </div>
                        <span class="tw-ml-2 tw-text-sm tw-font-medium tw-text-gray-600">Out for Delivery</span>
                    </div>
                    <div class="tw-flex-1 tw-h-px tw-bg-gray-300" id="progress-3"></div>

                    <!-- Delivered -->
                    <div class="tw-flex tw-items-center">
                        <div class="tw-h-8 tw-w-8 tw-rounded-full tw-flex tw-items-center tw-justify-center" id="delivered-step">
                            <span class="tw-text-sm tw-font-medium">4</span>
                        </div>
                        <span class="tw-ml-2 tw-text-sm tw-font-medium tw-text-gray-600">Delivered</span>
                    </div>
                </div>
            </div>

            <!-- Estimated Time -->
            <?php if ($delivery['estimated_delivery_time']): ?>
                <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-600">
                    <i data-feather="clock" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    <span>Estimated delivery: <?= date('g:i A', strtotime($delivery['estimated_delivery_time'])) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
            <!-- Map -->
            <div class="lg:tw-col-span-2">
                <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-overflow-hidden">
                    <div class="tw-p-4 tw-border-b tw-border-gray-200">
                        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Live Tracking</h2>
                    </div>
                    <div id="map" class="tw-h-96 lg:tw-h-[500px]"></div>
                </div>
            </div>

            <!-- Delivery Details -->
            <div class="lg:tw-col-span-1">
                <div class="tw-space-y-6">
                    <!-- Rider Info -->
                    <?php if ($delivery['rider_id']): ?>
                        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Your Rider</h3>
                            <div class="tw-flex tw-items-center tw-space-x-4">
                                <img src="<?= e($delivery['rider_image'] ?: '/images/default-avatar.png') ?>" alt="Rider" class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover">
                                <div class="tw-flex-1">
                                    <h4 class="tw-font-medium tw-text-gray-900"><?= e($delivery['rider_first_name'] . ' ' . $delivery['rider_last_name']) ?></h4>
                                    <p class="tw-text-sm tw-text-gray-600"><?= e($delivery['rider_phone']) ?></p>
                                </div>
                                <a href="tel:<?= e($delivery['rider_phone']) ?>" class="tw-p-2 tw-bg-primary-500 tw-text-white tw-rounded-lg hover:tw-bg-primary-600 tw-transition-colors">
                                    <i data-feather="phone" class="tw-h-4 tw-w-4"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Restaurant Info -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Restaurant</h3>
                        <div class="tw-space-y-2">
                            <h4 class="tw-font-medium tw-text-gray-900"><?= e($delivery['restaurant_name']) ?></h4>
                            <p class="tw-text-sm tw-text-gray-600"><?= e($delivery['restaurant_address']) ?></p>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Delivery Address</h3>
                        <div class="tw-space-y-2">
                            <?php 
                            $deliveryAddr = json_decode($delivery['delivery_address'], true);
                            if ($deliveryAddr):
                            ?>
                                <p class="tw-text-sm tw-text-gray-900"><?= e($deliveryAddr['address_line_1']) ?></p>
                                <?php if (!empty($deliveryAddr['address_line_2'])): ?>
                                    <p class="tw-text-sm tw-text-gray-600"><?= e($deliveryAddr['address_line_2']) ?></p>
                                <?php endif; ?>
                                <p class="tw-text-sm tw-text-gray-600">
                                    <?= e($deliveryAddr['city']) ?>, <?= e($deliveryAddr['state']) ?> <?= e($deliveryAddr['postal_code']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Connection Status -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Connection Status</h3>
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <div class="tw-h-3 tw-w-3 tw-bg-gray-400 tw-rounded-full" id="connection-indicator"></div>
                            <span class="tw-text-sm tw-text-gray-600" id="connection-status">Connecting...</span>
                        </div>
                        <p class="tw-text-xs tw-text-gray-500 tw-mt-2">Real-time updates via WebSocket</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize Feather icons
        feather.replace();

        // Delivery data
        const deliveryData = <?= json_encode([
            'id' => $delivery['id'],
            'status' => $delivery['status'],
            'tracking_code' => $delivery['tracking_code'],
            'pickup_location' => $pickup_location,
            'delivery_location' => $delivery_location,
            'rider_location' => $rider_location
        ]) ?>;

        let mapProvider;
        let map;
        let markers = {};
        let websocket;

        // Initialize map
        async function initMap() {
            const defaultCenter = deliveryData.pickup_location || { latitude: 5.9631, longitude: 10.1591 }; // Bamenda coordinates
            
            mapProvider = new MapProvider({
                container: 'map',
                center: [parseFloat(defaultCenter.latitude), parseFloat(defaultCenter.longitude)],
                zoom: 13,
                provider: window.MAP_CONFIG ? window.MAP_CONFIG.provider : 'leaflet',
                apiKey: window.MAP_CONFIG ? window.MAP_CONFIG.apiKey : ''
            });

            map = await mapProvider.init();

            // Add markers
            if (deliveryData.pickup_location) {
                markers.pickup = mapProvider.addMarker('pickup', 
                    parseFloat(deliveryData.pickup_location.latitude), 
                    parseFloat(deliveryData.pickup_location.longitude),
                    {
                        title: 'Restaurant',
                        icon: 'restaurant',
                        popup: '<b>Restaurant</b><br>' + (deliveryData.pickup_location.address || '')
                    }
                );
            }

            if (deliveryData.delivery_location) {
                markers.delivery = mapProvider.addMarker('delivery',
                    parseFloat(deliveryData.delivery_location.latitude),
                    parseFloat(deliveryData.delivery_location.longitude),
                    {
                        title: 'Delivery Address',
                        icon: 'customer',
                        popup: '<b>Delivery Address</b><br>' + (deliveryData.delivery_location.address || '')
                    }
                );
            }

            if (deliveryData.rider_location) {
                markers.rider = mapProvider.addMarker('rider',
                    parseFloat(deliveryData.rider_location.latitude),
                    parseFloat(deliveryData.rider_location.longitude),
                    {
                        title: 'Rider Location',
                        icon: 'rider',
                        popup: '<b>Rider</b><br>On the way'
                    }
                );
            }

            // Fit map to show all markers
            fitMapToMarkers();
        }

        function fitMapToMarkers() {
            const markersList = [];
            if (markers.pickup) markersList.push(markers.pickup);
            if (markers.delivery) markersList.push(markers.delivery);
            if (markers.rider) markersList.push(markers.rider);
            
            if (markersList.length > 0) {
                mapProvider.fitBounds(markersList);
            }
        }

        // Update progress steps
        function updateProgressSteps(status) {
            const steps = ['assigned', 'picked_up', 'out_for_delivery', 'delivered'];
            const currentIndex = steps.indexOf(status);

            // Reset all steps
            document.getElementById('pickup-step').className = 'tw-h-8 tw-w-8 tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center';
            document.getElementById('delivery-step').className = 'tw-h-8 tw-w-8 tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center';
            document.getElementById('delivered-step').className = 'tw-h-8 tw-w-8 tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center';

            // Update based on current status
            if (currentIndex >= 1) {
                document.getElementById('pickup-step').className = 'tw-h-8 tw-w-8 tw-bg-green-500 tw-rounded-full tw-flex tw-items-center tw-justify-center';
                document.getElementById('pickup-step').innerHTML = '<i data-feather="check" class="tw-h-5 tw-w-5 tw-text-white"></i>';
                document.getElementById('progress-1').className = 'tw-flex-1 tw-h-px tw-bg-green-500';
            }

            if (currentIndex >= 2) {
                document.getElementById('delivery-step').className = 'tw-h-8 tw-w-8 tw-bg-green-500 tw-rounded-full tw-flex tw-items-center tw-justify-center';
                document.getElementById('delivery-step').innerHTML = '<i data-feather="check" class="tw-h-5 tw-w-5 tw-text-white"></i>';
                document.getElementById('progress-2').className = 'tw-flex-1 tw-h-px tw-bg-green-500';
            }

            if (currentIndex >= 3) {
                document.getElementById('delivered-step').className = 'tw-h-8 tw-w-8 tw-bg-green-500 tw-rounded-full tw-flex tw-items-center tw-justify-center';
                document.getElementById('delivered-step').innerHTML = '<i data-feather="check" class="tw-h-5 tw-w-5 tw-text-white"></i>';
                document.getElementById('progress-3').className = 'tw-flex-1 tw-h-px tw-bg-green-500';
            }

            feather.replace();
        }

        // Update rider location on map
        function updateRiderLocation(latitude, longitude) {
            if (markers.rider) {
                mapProvider.updateMarker('rider', parseFloat(latitude), parseFloat(longitude));
            } else {
                markers.rider = mapProvider.addMarker('rider',
                    parseFloat(latitude),
                    parseFloat(longitude),
                    {
                        title: 'Rider Location',
                        icon: 'rider',
                        popup: '<b>Rider</b><br>On the way'
                    }
                );
            }
        }

        // WebSocket connection
        function initWebSocket() {
            const wsUrl = `ws://localhost:8080`;
            websocket = new WebSocket(wsUrl);

            websocket.onopen = function() {
                console.log('WebSocket connected');
                updateConnectionStatus('connected');
                
                // Authenticate
                websocket.send(JSON.stringify({
                    type: 'authenticate',
                    token: btoa(JSON.stringify({ user_id: <?= $_SESSION['user_id'] ?? 'null' ?> }))
                }));
            };

            websocket.onmessage = function(event) {
                const data = JSON.parse(event.data);
                console.log('WebSocket message:', data);

                switch (data.type) {
                    case 'authenticated':
                        // Subscribe to delivery updates
                        websocket.send(JSON.stringify({
                            type: 'subscribe_delivery',
                            delivery_id: deliveryData.id
                        }));
                        break;

                    case 'location_update':
                        updateRiderLocation(data.latitude, data.longitude);
                        break;

                    case 'status_update':
                        document.getElementById('status-text').textContent = data.status.replace('_', ' ');
                        updateProgressSteps(data.status);
                        break;

                    case 'error':
                        console.error('WebSocket error:', data.message);
                        break;
                }
            };

            websocket.onclose = function() {
                console.log('WebSocket disconnected');
                updateConnectionStatus('disconnected');
                
                // Attempt to reconnect after 5 seconds
                setTimeout(initWebSocket, 5000);
            };

            websocket.onerror = function(error) {
                console.error('WebSocket error:', error);
                updateConnectionStatus('error');
            };
        }

        function updateConnectionStatus(status) {
            const indicator = document.getElementById('connection-indicator');
            const statusText = document.getElementById('connection-status');

            switch (status) {
                case 'connected':
                    indicator.className = 'tw-h-3 tw-w-3 tw-bg-green-500 tw-rounded-full';
                    statusText.textContent = 'Connected';
                    break;
                case 'disconnected':
                    indicator.className = 'tw-h-3 tw-w-3 tw-bg-red-500 tw-rounded-full';
                    statusText.textContent = 'Disconnected';
                    break;
                case 'error':
                    indicator.className = 'tw-h-3 tw-w-3 tw-bg-yellow-500 tw-rounded-full';
                    statusText.textContent = 'Connection Error';
                    break;
            }
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            updateProgressSteps(deliveryData.status);
            initWebSocket();
        });

        // Fallback polling if WebSocket fails
        function startPolling() {
            setInterval(function() {
                fetch(`/api/tracking/order/${deliveryData.id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.tracking) {
                            const tracking = data.tracking;
                            
                            if (tracking.status !== deliveryData.status) {
                                document.getElementById('status-text').textContent = tracking.status.replace('_', ' ');
                                updateProgressSteps(tracking.status);
                                deliveryData.status = tracking.status;
                            }

                            if (tracking.rider_location) {
                                updateRiderLocation(tracking.rider_location.latitude, tracking.rider_location.longitude);
                            }
                        }
                    })
                    .catch(error => console.error('Polling error:', error));
            }, 10000); // Poll every 10 seconds
        }

        // Start polling as fallback
        setTimeout(startPolling, 30000); // Start after 30 seconds if WebSocket hasn't connected
    </script>
</body>
</html>
