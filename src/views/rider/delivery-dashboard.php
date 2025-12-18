<?php
/**
 * Rider Delivery Dashboard
 * Main interface for riders to accept deliveries and manage navigation
 */

$pageTitle = 'Delivery Dashboard';
$currentPage = 'rider-deliveries';

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Time2Eat</title>
    <link href="/css/tailwind.css" rel="stylesheet">
    <?= $mapHelper->getGlobalConfig() ?>
    <?= $mapHelper->getScripts() ?>
    <style>
        .tw-delivery-card {
            transition: all 0.3s ease;
        }
        .tw-delivery-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .tw-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .tw-status-available {
            background-color: #dcfce7;
            color: #166534;
        }
        .tw-status-accepted {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .tw-status-picked-up {
            background-color: #fef3c7;
            color: #92400e;
        }
        .tw-status-on-the-way {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        .tw-map-container {
            height: 300px;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .tw-earnings-card {
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
    </style>
</head>
<body class="tw-bg-gray-50">
    <!-- Navigation -->
    <nav class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-flex tw-justify-between tw-h-16">
                <div class="tw-flex tw-items-center">
                    <img src="/images/logo.png" alt="Time2Eat" class="tw-h-8 tw-w-auto">
                    <h1 class="tw-ml-4 tw-text-xl tw-font-semibold tw-text-gray-900">Delivery Dashboard</h1>
                </div>
                <div class="tw-flex tw-items-center tw-space-x-4">
                    <!-- GPS Accuracy Badge -->
                    <div id="gpsAccuracyBadge" class="tw-px-3 tw-py-1 tw-rounded-full tw-text-xs tw-text-white tw-bg-gray-400 tw-font-medium">
                        GPS: Initializing...
                    </div>

                    <!-- Online Status Toggle -->
                    <div class="tw-flex tw-items-center tw-space-x-2">
                        <span class="tw-text-sm tw-text-gray-600">Status:</span>
                        <button id="statusToggle" class="tw-relative tw-inline-flex tw-h-6 tw-w-11 tw-items-center tw-rounded-full tw-bg-gray-200 tw-transition-colors focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-ring-offset-2">
                            <span class="tw-inline-block tw-h-4 tw-w-4 tw-transform tw-rounded-full tw-bg-white tw-transition-transform"></span>
                        </button>
                        <span id="statusText" class="tw-text-sm tw-font-medium tw-text-gray-600">Offline</span>
                    </div>
                    <!-- Profile Menu -->
                    <div class="tw-relative">
                        <button class="tw-flex tw-items-center tw-space-x-2 tw-text-gray-700 hover:tw-text-gray-900">
                            <img src="/images/default-avatar.png" alt="Profile" class="tw-h-8 tw-w-8 tw-rounded-full">
                            <span class="tw-text-sm tw-font-medium"><?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'Rider') ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-6">
        <!-- Stats Cards -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
            <!-- Today's Earnings -->
            <div class="tw-earnings-card tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-white tw-text-sm tw-opacity-90">Today's Earnings</p>
                        <p id="todayEarnings" class="tw-text-2xl tw-font-bold tw-text-white">0 XAF</p>
                    </div>
                    <div class="tw-bg-white tw-bg-opacity-20 tw-p-3 tw-rounded-full">
                        <svg class="tw-w-6 tw-h-6 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Deliveries -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-gray-600 tw-text-sm">Active Deliveries</p>
                        <p id="activeDeliveries" class="tw-text-2xl tw-font-bold tw-text-gray-900">0</p>
                    </div>
                    <div class="tw-bg-blue-100 tw-p-3 tw-rounded-full">
                        <svg class="tw-w-6 tw-h-6 tw-text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Completed Today -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-gray-600 tw-text-sm">Completed Today</p>
                        <p id="completedToday" class="tw-text-2xl tw-font-bold tw-text-gray-900">0</p>
                    </div>
                    <div class="tw-bg-green-100 tw-p-3 tw-rounded-full">
                        <svg class="tw-w-6 tw-h-6 tw-text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Average Rating -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-gray-600 tw-text-sm">Average Rating</p>
                        <div class="tw-flex tw-items-center tw-space-x-1">
                            <p id="averageRating" class="tw-text-2xl tw-font-bold tw-text-gray-900">0.0</p>
                            <div class="tw-flex tw-text-yellow-400">
                                <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="tw-bg-yellow-100 tw-p-3 tw-rounded-full">
                        <svg class="tw-w-6 tw-h-6 tw-text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
            <!-- Available Deliveries -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow">
                <div class="tw-p-6 tw-border-b tw-border-gray-200">
                    <div class="tw-flex tw-items-center tw-justify-between">
                        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Available Deliveries</h2>
                        <button id="refreshDeliveries" class="tw-text-indigo-600 hover:tw-text-indigo-800 tw-text-sm tw-font-medium">
                            <svg class="tw-w-4 tw-h-4 tw-inline tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
                <div id="availableDeliveries" class="tw-p-6">
                    <div class="tw-text-center tw-py-8">
                        <div class="tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-indigo-600 tw-mx-auto"></div>
                        <p class="tw-text-gray-500 tw-mt-2">Loading available deliveries...</p>
                    </div>
                </div>
            </div>

            <!-- Active Deliveries -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow">
                <div class="tw-p-6 tw-border-b tw-border-gray-200">
                    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">My Active Deliveries</h2>
                </div>
                <div id="myActiveDeliveries" class="tw-p-6">
                    <div class="tw-text-center tw-py-8">
                        <svg class="tw-w-12 tw-h-12 tw-text-gray-400 tw-mx-auto tw-mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <p class="tw-text-gray-500">No active deliveries</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Modal -->
        <div id="navigationModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-hidden tw-z-50">
            <div class="tw-flex tw-items-center tw-justify-center tw-min-h-screen tw-p-4">
                <div class="tw-bg-white tw-rounded-lg tw-shadow-xl tw-max-w-4xl tw-w-full tw-max-h-screen tw-overflow-y-auto">
                    <div class="tw-p-6 tw-border-b tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Navigation</h3>
                            <button id="closeNavigation" class="tw-text-gray-400 hover:tw-text-gray-600">
                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="tw-p-6">
                        <div id="navigationMap" class="tw-map-container tw-mb-4"></div>
                        <div id="navigationDetails" class="tw-space-y-4">
                            <!-- Navigation details will be populated here -->
                        </div>
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
        let isOnline = false;
        let currentLocation = null;
        let navigationMap = null;
        let availableDeliveriesInterval = null;
        let locationUpdateInterval = null;
        let gpsTracker = null;

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeStatusToggle();
            initializeHighAccuracyGPS();
            loadDashboardData();
            setupEventListeners();

            // Start periodic updates
            startPeriodicUpdates();
        });

        // Initialize status toggle
        function initializeStatusToggle() {
            const statusToggle = document.getElementById('statusToggle');
            const statusText = document.getElementById('statusText');
            
            statusToggle.addEventListener('click', function() {
                isOnline = !isOnline;
                updateOnlineStatus(isOnline);
            });
        }

        // Update online status
        function updateOnlineStatus(online) {
            const statusToggle = document.getElementById('statusToggle');
            const statusText = document.getElementById('statusText');
            
            if (online) {
                statusToggle.classList.add('tw-bg-indigo-600');
                statusToggle.classList.remove('tw-bg-gray-200');
                statusToggle.querySelector('span').classList.add('tw-translate-x-5');
                statusText.textContent = 'Online';
                statusText.classList.add('tw-text-green-600');
                statusText.classList.remove('tw-text-gray-600');
                
                // Start location tracking
                startLocationTracking();
                loadAvailableDeliveries();
            } else {
                statusToggle.classList.remove('tw-bg-indigo-600');
                statusToggle.classList.add('tw-bg-gray-200');
                statusToggle.querySelector('span').classList.remove('tw-translate-x-5');
                statusText.textContent = 'Offline';
                statusText.classList.remove('tw-text-green-600');
                statusText.classList.add('tw-text-gray-600');
                
                // Stop location tracking
                stopLocationTracking();
                clearAvailableDeliveries();
            }
        }

        // Initialize location tracking
        function initializeLocationTracking() {
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        currentLocation = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        };
                    },
                    function(error) {
                        console.error('Error getting location:', error);
                        showNotification('Location access required for delivery services', 'error');
                    }
                );
            }
        }

        // Initialize high-accuracy GPS tracking
        function initializeHighAccuracyGPS() {
            gpsTracker = new HighAccuracyGPS({
                desiredAccuracy: 5, // 5 meters
                maxAttempts: 10,
                updateInterval: 3000 // Update every 3 seconds
            });

            // Handle location updates
            gpsTracker.onUpdate((location) => {
                currentLocation = location;

                // Only send to server if rider is online
                if (isOnline) {
                    sendLocationToServer(location);
                }

                // Update UI with accuracy info
                updateLocationDisplay(location);
            });

            // Handle errors
            gpsTracker.onError((error) => {
                console.error('GPS Error:', error.message);
            });

            console.log('🎯 High-accuracy GPS initialized');
        }

        // Start location tracking
        function startLocationTracking() {
            if (gpsTracker && !gpsTracker.isTracking) {
                gpsTracker.startTracking();
                console.log('📍 GPS tracking started');
            }
        }

        // Stop location tracking
        function stopLocationTracking() {
            if (gpsTracker && gpsTracker.isTracking) {
                gpsTracker.stopTracking();
                console.log('⏹️ GPS tracking stopped');
            }
        }

        // Send location to server
        function sendLocationToServer(location) {
            // Get battery level if available
            let batteryLevel = null;
            if (navigator.getBattery) {
                navigator.getBattery().then(battery => {
                    batteryLevel = Math.round(battery.level * 100);
                });
            }

            const locationData = {
                latitude: location.latitude,
                longitude: location.longitude,
                accuracy: location.accuracy,
                speed: location.speed,
                heading: location.heading,
                battery_level: batteryLevel
            };

            fetch('<?= url('/api/tracking/rider/location') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(locationData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`📍 Location updated: ±${location.accuracy.toFixed(1)}m`);
                } else {
                    console.error('Failed to update location on server');
                }
            })
            .catch(error => {
                console.error('Error sending location to server:', error);
            });
        }

        // Update location display in UI
        function updateLocationDisplay(location) {
            // You can add a location accuracy indicator in the UI
            const accuracyBadge = document.getElementById('gpsAccuracyBadge');
            if (accuracyBadge) {
                const accuracy = location.accuracy;
                let badgeClass = 'tw-bg-green-500';
                let badgeText = 'Excellent';

                if (accuracy > 20) {
                    badgeClass = 'tw-bg-yellow-500';
                    badgeText = 'Good';
                }
                if (accuracy > 50) {
                    badgeClass = 'tw-bg-orange-500';
                    badgeText = 'Fair';
                }
                if (accuracy > 100) {
                    badgeClass = 'tw-bg-red-500';
                    badgeText = 'Poor';
                }

                accuracyBadge.className = `tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-text-white ${badgeClass}`;
                accuracyBadge.textContent = `GPS: ${badgeText} (±${accuracy.toFixed(0)}m)`;
            }
        }

        // Load dashboard data
        function loadDashboardData() {
            // Load earnings and stats
            fetch('<?= url('/api/rider/earnings?period=today') ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDashboardStats(data.earnings, data.stats);
                    }
                })
                .catch(error => console.error('Error loading dashboard data:', error));
            
            // Load active deliveries
            loadActiveDeliveries();
        }

        // Update dashboard stats
        function updateDashboardStats(earnings, stats) {
            document.getElementById('todayEarnings').textContent = `${earnings.total_earnings || 0} XAF`;
            document.getElementById('completedToday').textContent = earnings.completed_deliveries || 0;
            document.getElementById('averageRating').textContent = (stats.avg_rating || 0).toFixed(1);
        }

        // Load available deliveries
        function loadAvailableDeliveries() {
            if (!isOnline) return;
            
            const params = new URLSearchParams({
                radius: 10, // 10km radius
                limit: 10
            });
            
            fetch(`/api/rider/available-deliveries?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayAvailableDeliveries(data.available_deliveries);
                    }
                })
                .catch(error => console.error('Error loading available deliveries:', error));
        }

        // Display available deliveries
        function displayAvailableDeliveries(deliveries) {
            const container = document.getElementById('availableDeliveries');
            
            if (deliveries.length === 0) {
                container.innerHTML = `
                    <div class="tw-text-center tw-py-8">
                        <svg class="tw-w-12 tw-h-12 tw-text-gray-400 tw-mx-auto tw-mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <p class="tw-text-gray-500">No available deliveries nearby</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = deliveries.map(delivery => `
                <div class="tw-delivery-card tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-mb-4">
                    <div class="tw-flex tw-justify-between tw-items-start tw-mb-3">
                        <div>
                            <h4 class="tw-font-semibold tw-text-gray-900">${delivery.restaurant_name}</h4>
                            <p class="tw-text-sm tw-text-gray-600">Order #${delivery.order_number}</p>
                        </div>
                        <span class="tw-status-badge tw-status-available">Available</span>
                    </div>
                    
                    <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-mb-4 tw-text-sm">
                        <div>
                            <span class="tw-text-gray-500">Distance:</span>
                            <span class="tw-font-medium">${delivery.distance_to_pickup.toFixed(1)} km</span>
                        </div>
                        <div>
                            <span class="tw-text-gray-500">Earnings:</span>
                            <span class="tw-font-medium tw-text-green-600">${delivery.estimated_earnings} XAF</span>
                        </div>
                        <div>
                            <span class="tw-text-gray-500">Time:</span>
                            <span class="tw-font-medium">${delivery.estimated_time} min</span>
                        </div>
                        <div>
                            <span class="tw-text-gray-500">Amount:</span>
                            <span class="tw-font-medium">${delivery.total_amount} XAF</span>
                        </div>
                    </div>
                    
                    <div class="tw-flex tw-space-x-2">
                        <button onclick="acceptDelivery(${delivery.id})" 
                                class="tw-flex-1 tw-bg-green-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-green-700 tw-transition-colors">
                            Accept
                        </button>
                        <button onclick="rejectDelivery(${delivery.id})" 
                                class="tw-flex-1 tw-bg-gray-300 tw-text-gray-700 tw-px-4 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-gray-400 tw-transition-colors">
                            Skip
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Clear available deliveries
        function clearAvailableDeliveries() {
            const container = document.getElementById('availableDeliveries');
            container.innerHTML = `
                <div class="tw-text-center tw-py-8">
                    <svg class="tw-w-12 tw-h-12 tw-text-gray-400 tw-mx-auto tw-mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <p class="tw-text-gray-500">Go online to see available deliveries</p>
                </div>
            `;
        }

        // Accept delivery
        function acceptDelivery(deliveryId) {
            fetch('<?= url('/api/rider/accept-delivery') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ delivery_id: deliveryId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Delivery accepted successfully!', 'success');
                    loadAvailableDeliveries();
                    loadActiveDeliveries();
                } else {
                    showNotification(data.message || 'Failed to accept delivery', 'error');
                }
            })
            .catch(error => {
                console.error('Error accepting delivery:', error);
                showNotification('Failed to accept delivery', 'error');
            });
        }

        // Reject delivery
        function rejectDelivery(deliveryId) {
            const reason = prompt('Reason for rejection (optional):') || '';
            
            fetch('<?= url('/api/rider/reject-delivery') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    delivery_id: deliveryId,
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadAvailableDeliveries();
                } else {
                    showNotification(data.message || 'Failed to reject delivery', 'error');
                }
            })
            .catch(error => {
                console.error('Error rejecting delivery:', error);
            });
        }

        // Load active deliveries
        function loadActiveDeliveries() {
            // This would load the rider's current active deliveries
            // Implementation would be similar to available deliveries
        }

        // Setup event listeners
        function setupEventListeners() {
            document.getElementById('refreshDeliveries').addEventListener('click', loadAvailableDeliveries);
            document.getElementById('closeNavigation').addEventListener('click', closeNavigationModal);
        }

        // Start periodic updates
        function startPeriodicUpdates() {
            // Refresh available deliveries every 30 seconds when online
            availableDeliveriesInterval = setInterval(() => {
                if (isOnline) {
                    loadAvailableDeliveries();
                }
            }, 30000);
        }

        // Show notification
        function showNotification(message, type = 'info') {
            // Simple notification implementation
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

        // Close navigation modal
        function closeNavigationModal() {
            document.getElementById('navigationModal').classList.add('tw-hidden');
        }
    </script>
</body>
</html>
