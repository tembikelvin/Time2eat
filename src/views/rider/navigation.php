<?php
/**
 * Rider Navigation View
 * Turn-by-turn navigation interface for delivery routes
 */

$pageTitle = 'Navigation';
$currentPage = 'rider-navigation';
$deliveryId = $_GET['delivery_id'] ?? 0;
$destination = $_GET['destination'] ?? 'pickup'; // pickup or delivery

require_once __DIR__ . '/../../helpers/MapHelper.php';
$mapHelper = new MapHelper();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Time2Eat</title>
    <link href="/css/tailwind.css" rel="stylesheet">
    <?= $mapHelper->getGlobalConfig() ?>
    <?= $mapHelper->getScripts() ?>
    <style>
        .tw-navigation-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .tw-map-container {
            flex: 1;
            position: relative;
        }
        .tw-navigation-panel {
            background: white;
            border-top: 1px solid #e5e7eb;
            max-height: 40vh;
            overflow-y: auto;
        }
        .tw-step-item {
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s;
        }
        .tw-step-item:hover {
            background-color: #f9fafb;
        }
        .tw-step-item.active {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        .tw-floating-controls {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
        }
        .tw-status-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .tw-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        .tw-direction-icon {
            width: 24px;
            height: 24px;
            display: inline-block;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        .tw-turn-left { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>'); }
        .tw-turn-right { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>'); }
        .tw-straight { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>'); }
    </style>
</head>
<body class="tw-bg-gray-50">
    <div class="tw-navigation-container">
        <!-- Status Bar -->
        <div class="tw-status-bar tw-p-4">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-space-x-4">
                    <button onclick="goBack()" class="tw-text-white hover:tw-text-gray-200">
                        <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <div>
                        <h1 class="tw-text-lg tw-font-semibold">Navigation</h1>
                        <p class="tw-text-sm tw-opacity-90" id="destinationType">
                            <?= $destination === 'pickup' ? 'To Restaurant' : 'To Customer' ?>
                        </p>
                    </div>
                </div>
                <div class="tw-text-right">
                    <div class="tw-text-lg tw-font-bold" id="remainingTime">--:--</div>
                    <div class="tw-text-sm tw-opacity-90" id="remainingDistance">-- km</div>
                </div>
            </div>
        </div>

        <!-- Map Container -->
        <div class="tw-map-container">
            <div id="navigationMap" style="height: 100%; width: 100%;"></div>
            
            <!-- Floating Controls -->
            <div class="tw-floating-controls tw-space-y-2">
                <button id="centerLocation" class="tw-bg-white tw-p-3 tw-rounded-full tw-shadow-lg hover:tw-bg-gray-50">
                    <svg class="tw-w-5 tw-h-5 tw-text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
                <button id="toggleVoice" class="tw-bg-white tw-p-3 tw-rounded-full tw-shadow-lg hover:tw-bg-gray-50">
                    <svg class="tw-w-5 tw-h-5 tw-text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Navigation Panel -->
        <div class="tw-navigation-panel">
            <!-- Current Instruction -->
            <div class="tw-bg-blue-50 tw-p-4 tw-border-b tw-border-blue-200">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div id="currentStepIcon" class="tw-direction-icon tw-straight tw-text-blue-600"></div>
                    <div class="tw-flex-1">
                        <div class="tw-text-lg tw-font-semibold tw-text-blue-900" id="currentInstruction">
                            Loading navigation...
                        </div>
                        <div class="tw-text-sm tw-text-blue-700" id="currentDistance">
                            Calculating route...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Steps -->
            <div id="upcomingSteps" class="tw-max-h-64 tw-overflow-y-auto">
                <!-- Steps will be populated here -->
            </div>

            <!-- Action Buttons -->
            <div class="tw-p-4 tw-bg-gray-50 tw-border-t tw-border-gray-200">
                <div class="tw-grid tw-grid-cols-2 tw-gap-3">
                    <button id="updateStatusBtn" class="tw-bg-indigo-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-font-medium hover:tw-bg-indigo-700 tw-transition-colors">
                        Update Status
                    </button>
                    <button id="callCustomerBtn" class="tw-bg-green-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-font-medium hover:tw-bg-green-700 tw-transition-colors">
                        Call Customer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-hidden tw-z-50">
        <div class="tw-flex tw-items-center tw-justify-center tw-min-h-screen tw-p-4">
            <div class="tw-bg-white tw-rounded-lg tw-shadow-xl tw-max-w-md tw-w-full">
                <div class="tw-p-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Update Delivery Status</h3>
                    <div class="tw-space-y-3">
                        <button onclick="updateDeliveryStatus('picked_up')" class="tw-w-full tw-bg-yellow-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-font-medium hover:tw-bg-yellow-600">
                            Mark as Picked Up
                        </button>
                        <button onclick="updateDeliveryStatus('on_the_way')" class="tw-w-full tw-bg-blue-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-font-medium hover:tw-bg-blue-600">
                            On the Way
                        </button>
                        <button onclick="updateDeliveryStatus('delivered')" class="tw-w-full tw-bg-green-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-font-medium hover:tw-bg-green-600">
                            Mark as Delivered
                        </button>
                        <button onclick="closeStatusModal()" class="tw-w-full tw-bg-gray-300 tw-text-gray-700 tw-px-4 tw-py-2 tw-rounded-md tw-font-medium hover:tw-bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= url('/public/js/high-accuracy-gps.js') ?>"></script>
    <script src="<?= url('/public/js/leaflet-map-utils.js') ?>"></script>
    <script>
        // Global variables
        let mapProviderInstance = null;
        let navigationMap = null;
        let currentRoute = null;
        let currentLocation = null;
        let deliveryData = null;
        let routePolyline = null;
        let currentStepIndex = 0;
        let voiceEnabled = true;
        let locationUpdateInterval = null;
        let gpsTracker = null;
        let currentLocationMarker = null;
        let destinationMarker = null;

        // Initialize navigation
        document.addEventListener('DOMContentLoaded', function() {
            initializeMap();
            loadNavigationRoute();
            setupEventListeners();
            startHighAccuracyTracking();
        });

        // Initialize map
        async function initializeMap() {
            const mapConfig = {
                provider: window.MAP_CONFIG ? window.MAP_CONFIG.provider : 'leaflet',
                apiKey: window.MAP_CONFIG ? window.MAP_CONFIG.apiKey : '',
                container: 'navigationMap',
                center: [5.9631, 10.1591],
                zoom: 15
            };

            mapProviderInstance = new MapProvider(mapConfig);
            navigationMap = await mapProviderInstance.init();
        }

        // Start high-accuracy GPS tracking
        function startHighAccuracyTracking() {
            gpsTracker = new HighAccuracyGPS({
                desiredAccuracy: 5, // 5 meters
                maxAttempts: 10,
                updateInterval: 2000 // Update every 2 seconds
            });

            // Handle location updates
            gpsTracker.onUpdate((location) => {
                currentLocation = location;
                updateCurrentLocationMarker(location);
                updateNavigationInfo(location);
                sendLocationToServer(location);
            });

            // Handle errors
            gpsTracker.onError((error) => {
                console.error('GPS Error:', error.message);
                showNotification('GPS Error: ' + error.message, 'error');
            });

            // Start tracking
            try {
                gpsTracker.startTracking();
                console.log('🎯 High-accuracy GPS tracking started');
            } catch (error) {
                console.error('Failed to start GPS tracking:', error);
                showNotification('Failed to start GPS tracking', 'error');
            }
        }

        // Update current location marker on map
        function updateCurrentLocationMarker(location) {
            if (!mapProviderInstance) return;

            // Update marker position via MapProvider
            // If marker doesn't exist, add it
            if (!mapProviderInstance.markers['current-location']) {
                currentLocationMarker = mapProviderInstance.addMarker('current-location', location.latitude, location.longitude, {
                    icon: 'rider',
                    title: 'Your Location',
                    popup: `<b>Your Location</b><br>Accuracy: ±${location.accuracy.toFixed(1)}m<br>Speed: ${location.speed ? (location.speed * 3.6).toFixed(1) + ' km/h' : 'N/A'}`
                });
            } else {
                mapProviderInstance.updateMarker('current-location', location.latitude, location.longitude);
                
                // Update popup content if possible (provider specific)
                if (mapProviderInstance.provider === 'leaflet') {
                    const marker = mapProviderInstance.markers['current-location'];
                    marker.setPopupContent(`<b>Your Location</b><br>Accuracy: ±${location.accuracy.toFixed(1)}m<br>Speed: ${location.speed ? (location.speed * 3.6).toFixed(1) + ' km/h' : 'N/A'}`);
                }
            }

            // Accuracy circle (Leaflet only for now)
            if (mapProviderInstance.provider === 'leaflet' && navigationMap) {
                const marker = mapProviderInstance.markers['current-location'];
                if (marker) {
                    if (!marker.accuracyCircle) {
                        marker.accuracyCircle = L.circle([location.latitude, location.longitude], {
                            radius: location.accuracy,
                            color: '#3B82F6',
                            fillColor: '#3B82F6',
                            fillOpacity: 0.1,
                            weight: 1
                        }).addTo(navigationMap);
                    } else {
                        marker.accuracyCircle.setLatLng([location.latitude, location.longitude]);
                        marker.accuracyCircle.setRadius(location.accuracy);
                    }
                }
            }
        }

        // Send location to server
        function sendLocationToServer(location) {
            fetch('<?= url('/api/tracking/rider/location') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    latitude: location.latitude,
                    longitude: location.longitude,
                    accuracy: location.accuracy,
                    speed: location.speed,
                    heading: location.heading
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to update location on server');
                }
            })
            .catch(error => {
                console.error('Error sending location to server:', error);
            });
        }

        // Update navigation info based on current location
        function updateNavigationInfo(location) {
            if (!deliveryData || !deliveryData.destination) return;

            const destLat = deliveryData.destination.latitude;
            const destLng = deliveryData.destination.longitude;

            // Calculate distance to destination
            const distance = HighAccuracyGPS.calculateDistance(
                location.latitude,
                location.longitude,
                destLat,
                destLng
            );

            // Update UI
            const distanceElement = document.getElementById('remainingDistance');
            if (distanceElement) {
                distanceElement.textContent = formatDistance(distance);
            }

            // Estimate time (assuming average speed of 30 km/h)
            const avgSpeed = location.speed || 8.33; // 30 km/h = 8.33 m/s
            const timeSeconds = distance / avgSpeed;
            const timeMinutes = Math.ceil(timeSeconds / 60);

            const timeElement = document.getElementById('remainingTime');
            if (timeElement) {
                timeElement.textContent = `${timeMinutes} min`;
            }
        }

        // Load navigation route
        function loadNavigationRoute() {
            const deliveryId = <?= (int)$deliveryId ?>;
            const destination = '<?= htmlspecialchars($destination) ?>';
            
            if (!deliveryId) {
                showError('Invalid delivery ID');
                return;
            }
            
            fetch(`/api/rider/navigation-route?delivery_id=${deliveryId}&destination=${destination}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentRoute = data.route;
                        deliveryData = data.delivery;
                        displayRoute(data);
                        displayNavigationSteps(data.route.steps);
                        updateRouteInfo(data.route);
                    } else {
                        showError(data.message || 'Failed to load navigation route');
                    }
                })
                .catch(error => {
                    console.error('Error loading navigation route:', error);
                    showError('Failed to load navigation route');
                });
        }

        // Display route on map
        function displayRoute(routeData) {
            const { route, origin, destination } = routeData;
            
            // Clear existing route
            if (routePolyline) {
                if (mapProviderInstance.provider === 'leaflet') {
                    navigationMap.removeLayer(routePolyline);
                } else if (mapProviderInstance.provider === 'google') {
                    routePolyline.setMap(null);
                }
            }
            
            // Decode polyline
            const routeCoords = decodePolyline(route.polyline);
            
            // Draw polyline using MapProvider
            routePolyline = mapProviderInstance.drawPolyline(routeCoords, {
                color: '#3b82f6',
                weight: 5,
                opacity: 0.8
            });
            
            // Add markers
            mapProviderInstance.addMarker('origin', origin.latitude, origin.longitude, {
                popup: 'Your Location'
            });
            
            mapProviderInstance.addMarker('destination', destination.latitude, destination.longitude, {
                popup: destination.address
            });
            
            // Fit bounds
            mapProviderInstance.fitBounds(routeCoords);
        }

        // Display navigation steps
        function displayNavigationSteps(steps) {
            const container = document.getElementById('upcomingSteps');
            
            if (!steps || steps.length === 0) {
                container.innerHTML = '<div class="tw-p-4 tw-text-gray-500">No navigation steps available</div>';
                return;
            }
            
            container.innerHTML = steps.map((step, index) => `
                <div class="tw-step-item tw-p-4 ${index === 0 ? 'active' : ''}" data-step="${index}">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-direction-icon ${getDirectionIcon(step.maneuver?.type)} tw-text-gray-600"></div>
                        <div class="tw-flex-1">
                            <div class="tw-font-medium tw-text-gray-900">${step.instruction || step.name}</div>
                            <div class="tw-text-sm tw-text-gray-600">${formatDistance(step.distance)} - ${formatDuration(step.duration)}</div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Update current instruction
            if (steps[0]) {
                updateCurrentInstruction(steps[0]);
            }
        }

        // Update current instruction
        function updateCurrentInstruction(step) {
            document.getElementById('currentInstruction').textContent = step.instruction || step.name;
            document.getElementById('currentDistance').textContent = `In ${formatDistance(step.distance)}`;
            
            const iconElement = document.getElementById('currentStepIcon');
            iconElement.className = `tw-direction-icon ${getDirectionIcon(step.maneuver?.type)} tw-text-blue-600`;
            
            // Speak instruction if voice is enabled
            if (voiceEnabled && 'speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(step.instruction || step.name);
                utterance.rate = 0.8;
                utterance.volume = 0.8;
                speechSynthesis.speak(utterance);
            }
        }

        // Update route info
        function updateRouteInfo(route) {
            document.getElementById('remainingTime').textContent = formatDuration(route.duration * 60); // Convert to seconds
            document.getElementById('remainingDistance').textContent = `${route.distance.toFixed(1)} km`;
        }

        // Get direction icon class
        function getDirectionIcon(maneuverType) {
            if (!maneuverType) return 'tw-straight';
            
            if (maneuverType.includes('left')) return 'tw-turn-left';
            if (maneuverType.includes('right')) return 'tw-turn-right';
            return 'tw-straight';
        }

        // Format distance
        function formatDistance(meters) {
            if (meters < 1000) {
                return `${Math.round(meters)} m`;
            }
            return `${(meters / 1000).toFixed(1)} km`;
        }

        // Format duration
        function formatDuration(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            
            if (minutes > 0) {
                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }
            return `0:${remainingSeconds.toString().padStart(2, '0')}`;
        }

        // Decode polyline (simplified implementation)
        function decodePolyline(encoded) {
            // This is a simplified implementation
            // In production, use a proper polyline decoding library
            return [[5.9631, 10.1591], [5.9641, 10.1601]]; // Placeholder coordinates
        }

        // Start location tracking
        function startLocationTracking() {
            if (locationUpdateInterval) {
                clearInterval(locationUpdateInterval);
            }
            
            locationUpdateInterval = setInterval(updateCurrentLocation, 3000); // Update every 3 seconds
        }

        // Update current location
        function updateCurrentLocation() {
            if (!navigator.geolocation) return;
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    currentLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    
                    // Update location on server
                    updateLocationOnServer(currentLocation);
                    
                    // Update map if needed
                    updateMapLocation(currentLocation);
                },
                function(error) {
                    console.error('Error getting location:', error);
                }
            );
        }

        // Update location on server
        function updateLocationOnServer(location) {
            fetch('<?= url('/api/rider/update-location') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(location)
            })
            .catch(error => console.error('Error updating location:', error));
        }

        // Update map location
        function updateMapLocation(location) {
            // Update rider marker on map
            updateCurrentLocationMarker(location);
        }

        // Setup event listeners
        function setupEventListeners() {
            document.getElementById('centerLocation').addEventListener('click', centerOnLocation);
            document.getElementById('toggleVoice').addEventListener('click', toggleVoiceNavigation);
            document.getElementById('updateStatusBtn').addEventListener('click', showStatusModal);
            document.getElementById('callCustomerBtn').addEventListener('click', callCustomer);
        }

        // Center on current location
        function centerOnLocation() {
            if (currentLocation && mapProviderInstance) {
                mapProviderInstance.setCenter(currentLocation.latitude, currentLocation.longitude, 16);
            }
        }

        // Toggle voice navigation
        function toggleVoiceNavigation() {
            voiceEnabled = !voiceEnabled;
            const button = document.getElementById('toggleVoice');
            
            if (voiceEnabled) {
                button.classList.add('tw-bg-blue-100');
                button.querySelector('svg').classList.add('tw-text-blue-600');
            } else {
                button.classList.remove('tw-bg-blue-100');
                button.querySelector('svg').classList.remove('tw-text-blue-600');
            }
        }

        // Show status modal
        function showStatusModal() {
            document.getElementById('statusModal').classList.remove('tw-hidden');
        }

        // Close status modal
        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('tw-hidden');
        }

        // Update delivery status
        function updateDeliveryStatus(status) {
            const deliveryId = <?= (int)$deliveryId ?>;
            
            fetch('<?= url('/api/rider/update-delivery-status') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    delivery_id: deliveryId,
                    status: status,
                    notes: ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`Status updated to ${status.replace('_', ' ')}`, 'success');
                    closeStatusModal();
                    
                    // Redirect based on status
                    if (status === 'delivered') {
                        setTimeout(() => {
                            window.location.href = '<?= url('/rider/dashboard') ?>';
                        }, 2000);
                    }
                } else {
                    showNotification(data.message || 'Failed to update status', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);
                showNotification('Failed to update status', 'error');
            });
        }

        // Call customer
        function callCustomer() {
            if (deliveryData && deliveryData.customer_phone) {
                window.location.href = `tel:${deliveryData.customer_phone}`;
            } else {
                showNotification('Customer phone number not available', 'error');
            }
        }

        // Go back
        function goBack() {
            if (confirm('Are you sure you want to exit navigation?')) {
                window.location.href = '<?= url('/rider/dashboard') ?>';
            }
        }

        // Show error
        function showError(message) {
            showNotification(message, 'error');
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-md tw-shadow-lg tw-z-50 ${
                type === 'success' ? 'tw-bg-green-500' : 
                type === 'error' ? 'tw-bg-red-500' : 'tw-bg-blue-500'
            } tw-text-white`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (locationUpdateInterval) {
                clearInterval(locationUpdateInterval);
            }
        });
    </script>
</body>
</html>
