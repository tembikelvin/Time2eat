<?php
// Ensure helper functions are available
if (!function_exists('url')) {
    require_once __DIR__ . '/../../helpers/functions.php';
}

// Get user role for dynamic navigation - define early
// Check if userRole is explicitly passed first, then check user object
if (!isset($userRole)) {
    $userRole = 'customer'; // default
    if (isset($user) && $user) {
        if (is_object($user)) {
            $userRole = $user->role ?? 'customer';
        } elseif (is_array($user)) {
            $userRole = $user['role'] ?? 'customer';
        }
    }
}

// Debug: Temporarily show user role (remove this after testing)
// echo "<!-- DEBUG: User Role: " . $userRole . " -->";
?>
<!DOCTYPE html>
<html lang="en" class="tw-h-full tw-bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= e($title ?? 'Dashboard - Time2Eat') ?></title>
    
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
    
    <!-- Notifications -->
    <script src="<?= url('/public/js/notifications.js') ?>"></script>
    
    <!-- Chart.js for analytics - Using UMD build for compatibility -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <?php
    // Load global map configuration
    require_once __DIR__ . '/../../helpers/MapHelper.php';

    // Ensure database connection is available for MapHelper to load settings
    if (!function_exists('dbConnection')) {
        $dbConfigPath = __DIR__ . '/../../../config/database.php';
        if (file_exists($dbConfigPath)) {
            require_once $dbConfigPath;
        }
    }

    // Pass database connection to MapHelper
    $db = function_exists('dbConnection') ? dbConnection() : null;
    $mapHelper = \helpers\MapHelper::getInstance($db);
    echo $mapHelper->getGlobalConfig();
    echo $mapHelper->getScripts();
    ?>
    
    <!-- Custom Dashboard Styles -->
    <style>
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            color: #6b7280;
        }
        
        .sidebar-item:hover {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        .sidebar-item.active {
            background-color: #fef3c7;
            color: #d97706;
            font-weight: 600;
            border-right: 4px solid #f97316;
            position: relative;
        }
        
        .sidebar-item.active i {
            color: #f97316;
        }
        
        /* Ensure Feather Icons display properly */
        [data-feather] {
            display: inline-block;
            vertical-align: middle;
        }
        
        /* Fix icon alignment issues */
        .tw-h-4, .tw-h-5, .tw-h-6, .tw-h-8 {
            display: inline-block;
            vertical-align: middle;
        }
        
        /* Ensure proper icon sizing */
        svg {
            display: inline-block;
            vertical-align: middle;
        }
        
        .sidebar-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: #f97316;
        }
    </style>
</head>
<body class="tw-h-full">
    <div class="tw-min-h-full">
        <div class="tw-flex tw-h-screen tw-bg-gray-50">
            <!-- Mobile backdrop -->
            <div id="sidebar-backdrop" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-z-40 md:tw-hidden"></div>
            
            <!-- Sidebar -->
            <div id="sidebar" class="tw-hidden md:tw-flex tw-flex-col tw-w-64 tw-bg-white tw-shadow-lg tw-fixed tw-inset-y-0 tw-left-0 tw-z-50 md:tw-static md:tw-z-auto">
                <?php include __DIR__ . '/../components/sidebar-content.php'; ?>
            </div>

            <!-- Main content -->
            <div class="tw-flex tw-flex-col tw-w-0 tw-flex-1 tw-overflow-hidden">
                <!-- Top bar -->
                <div class="tw-relative tw-z-10 tw-flex-shrink-0 tw-flex tw-h-16 tw-bg-white tw-shadow">
                    <!-- Mobile menu button -->
                    <button type="button" id="mobile-menu-button" class="tw-px-4 tw-border-r tw-border-gray-200 tw-text-gray-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-inset focus:tw-ring-orange-500 md:tw-hidden tw-flex tw-items-center tw-justify-center">
                        <span class="tw-sr-only">Open sidebar</span>
                        <i data-feather="menu" class="tw-h-6 tw-w-6 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    </button>
                    
                    <div class="tw-flex-1 tw-px-4 tw-flex tw-justify-between">
                        <div class="tw-flex-1 tw-flex">
                            <!-- Page title will be inserted here by individual views -->
                        </div>
                        <div class="tw-ml-4 tw-flex tw-items-center md:tw-ml-6">
                            <!-- Notifications -->
                            <div class="tw-relative">
                                <button type="button" id="notification-bell" onclick="toggleNotificationDropdown()"
                                        class="tw-bg-white tw-p-1 tw-rounded-full tw-text-gray-400 hover:tw-text-gray-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-orange-500 tw-relative tw-flex tw-items-center tw-justify-center">
                                    <span class="tw-sr-only">View notifications</span>
                                    <i data-feather="bell" class="tw-h-6 tw-w-6 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <!-- Notification Badge -->
                                    <span id="notification-badge" class="tw-absolute tw--top-1 tw--right-1 tw-inline-flex tw-items-center tw-justify-center tw-px-2 tw-py-1 tw-text-xs tw-font-bold tw-leading-none tw-text-white tw-bg-red-500 tw-rounded-full tw-hidden">
                                        0
                                    </span>
                                </button>

                                <!-- Notification Dropdown -->
                                <div id="notification-dropdown" class="tw-hidden tw-absolute tw-right-0 tw-mt-2 tw-w-80 tw-bg-white tw-rounded-lg tw-shadow-lg tw-border tw-border-gray-200 tw-z-50">
                                    <div class="tw-p-4 tw-border-b tw-border-gray-200">
                                        <div class="tw-flex tw-items-center tw-justify-between">
                                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Notifications</h3>
                                            <button onclick="markAllNotificationsRead()" class="tw-text-sm tw-text-blue-600 hover:tw-text-blue-800">
                                                Mark all read
                                            </button>
                                        </div>
                                    </div>
                                    <div id="notification-list" class="tw-max-h-96 tw-overflow-y-auto">
                                        <div class="tw-p-4 tw-text-center tw-text-gray-500">
                                            <i data-feather="bell" class="tw-h-8 tw-w-8 tw-mx-auto tw-mb-2 tw-text-gray-300 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                            <p>No notifications</p>
                                        </div>
                                    </div>
                                    <div class="tw-p-4 tw-border-t tw-border-gray-200">
                                        <a href="<?= url('/' . $userRole . '/notifications') ?>" class="tw-block tw-text-center tw-text-sm tw-text-blue-600 hover:tw-text-blue-800">
                                            View all notifications
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Balance display -->
                            <div class="tw-ml-3">
                                <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-green-100 tw-text-green-800">
                                    <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-mr-1 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <?php
                                    $balance = 0;
                                    if ($user) {
                                        if (is_object($user)) {
                                            $balance = $user->balance ?? 0;
                                        } elseif (is_array($user)) {
                                            $balance = $user['balance'] ?? 0;
                                        }
                                    }
                                    echo number_format($balance);
                                    ?> XAF
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page content -->
                <main class="tw-flex-1 tw-relative tw-overflow-y-auto focus:tw-outline-none">
                    <div class="tw-py-6">
                        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 md:tw-px-8">
                            <?= $content ?>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Initialize Feather Icons -->
    <script>
        // Wait for both DOM and Feather to be ready
        function initializeFeatherIcons() {
            // Check if feather is loaded and has the replace method
            if (typeof feather !== 'undefined' && typeof feather.replace === 'function') {
                try {
                    // Get all icon elements
                    const iconElements = document.querySelectorAll('[data-feather]');
                    console.log('Found ' + iconElements.length + ' icon elements');
                    
                    // Try to replace icons one by one to handle invalid names gracefully
                    let successCount = 0;
                    let errorCount = 0;
                    
                    iconElements.forEach(function(element, index) {
                        const iconName = element.getAttribute('data-feather');
                        try {
                            // Check if icon exists in feather.icons
                            if (feather.icons[iconName]) {
                                const svg = feather.icons[iconName].toSvg({
                                    'class': element.className,
                                    'width': element.getAttribute('width') || '24',
                                    'height': element.getAttribute('height') || '24'
                                });
                                element.innerHTML = svg;
                                element.removeAttribute('data-feather'); // Remove to prevent re-processing
                                successCount++;
                            } else {
                                console.warn('Icon not found:', iconName);
                                element.innerHTML = '[' + iconName + ']';
                                element.style.fontSize = '12px';
                                element.style.color = '#6b7280';
                                element.style.display = 'inline-block';
                                element.style.verticalAlign = 'middle';
                                errorCount++;
                            }
                        } catch (error) {
                            console.warn('Error processing icon:', iconName, error);
                            element.innerHTML = '[' + iconName + ']';
                            element.style.fontSize = '12px';
                            element.style.color = '#6b7280';
                            element.style.display = 'inline-block';
                            element.style.verticalAlign = 'middle';
                            errorCount++;
                        }
                    });
                    
                    console.log('Feather icons processed: ' + successCount + ' success, ' + errorCount + ' errors');
                    
                    // Log any remaining invalid icons for debugging
                    if (errorCount > 0) {
                        console.warn('Some icons could not be processed. Check the console for details.');
                    }
                    
                    return true;
                } catch (error) {
                    console.error('Error initializing Feather icons:', error);
                    showIconFallbacks();
                    return false;
                }
            } else {
                console.log('Feather not loaded yet, retrying...');
                setTimeout(initializeFeatherIcons, 100);
                return false;
            }
        }
        
        // Fallback function to show text if icons fail
        function showIconFallbacks() {
            const iconElements = document.querySelectorAll('[data-feather]');
            iconElements.forEach(function(element) {
                if (element.innerHTML.trim() === '') {
                    const iconName = element.getAttribute('data-feather');
                    element.innerHTML = '[' + iconName + ']';
                    element.style.fontSize = '12px';
                    element.style.color = '#6b7280';
                    element.style.display = 'inline-block';
                    element.style.verticalAlign = 'middle';
                }
            });
        }
        
        // Function to validate all icons and log invalid ones
        function validateIcons() {
            if (typeof feather !== 'undefined' && feather.icons) {
                const iconElements = document.querySelectorAll('[data-feather]');
                const invalidIcons = [];
                
                iconElements.forEach(function(element) {
                    const iconName = element.getAttribute('data-feather');
                    if (!feather.icons[iconName]) {
                        invalidIcons.push(iconName);
                    }
                });
                
                if (invalidIcons.length > 0) {
                    console.warn('Invalid Feather Icons found:', invalidIcons);
                    console.warn('Available icons:', Object.keys(feather.icons).slice(0, 20) + '...');
                }
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for external scripts to load
            setTimeout(function() {
                // Validate icons first
                validateIcons();
                
                // Initialize Feather Icons
                initializeFeatherIcons();
                
                // Fallback timeout in case Feather Icons never loads
                setTimeout(function() {
                    if (typeof feather === 'undefined' || typeof feather.replace !== 'function') {
                        console.warn('Feather Icons failed to load, showing fallbacks');
                        showIconFallbacks();
                    }
                }, 2000);
            }, 100);
            
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            function showSidebar() {
                sidebar.classList.remove('tw-hidden');
                sidebar.classList.add('tw-flex', 'tw-flex-col');
                backdrop.classList.remove('tw-hidden');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
                console.log('Sidebar shown');
            }
            
            function hideSidebar() {
                sidebar.classList.add('tw-hidden');
                sidebar.classList.remove('tw-flex', 'tw-flex-col');
                backdrop.classList.add('tw-hidden');
                document.body.style.overflow = ''; // Restore scrolling
                console.log('Sidebar hidden');
            }
            
            if (mobileMenuButton && sidebar && backdrop) {
                console.log('Mobile menu components found');
                
                mobileMenuButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Mobile menu button clicked');
                    
                    // Toggle sidebar visibility on mobile
                    if (sidebar.classList.contains('tw-hidden')) {
                        showSidebar();
                    } else {
                        hideSidebar();
                    }
                });
                
                // Close sidebar when clicking backdrop
                backdrop.addEventListener('click', function() {
                    hideSidebar();
                });
                
                // Close sidebar on window resize if desktop
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 768) {
                        hideSidebar();
                    }
                });
            } else {
                console.log('Mobile menu components not found:', {
                    button: !!mobileMenuButton,
                    sidebar: !!sidebar,
                    backdrop: !!backdrop
                });
            }
            
            // Re-initialize icons after a short delay to ensure all content is loaded
            setTimeout(function() {
                if (!initializeFeatherIcons()) {
                    // If Feather still isn't loaded after 2 seconds, show fallbacks
                    setTimeout(showIconFallbacks, 2000);
                }
            }, 500);

            // Initialize notification system
            initializeNotificationSystem();
        });

        // Notification System Functions
        let notificationDropdownOpen = false;

        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notification-dropdown');
            notificationDropdownOpen = !notificationDropdownOpen;

            if (notificationDropdownOpen) {
                dropdown.classList.remove('tw-hidden');
                loadNotifications();
            } else {
                dropdown.classList.add('tw-hidden');
            }
        }

        function initializeNotificationSystem() {
            // Close notification dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const bell = document.getElementById('notification-bell');
                const dropdown = document.getElementById('notification-dropdown');

                if (bell && dropdown && !bell.contains(event.target) && !dropdown.contains(event.target)) {
                    dropdown.classList.add('tw-hidden');
                    notificationDropdownOpen = false;
                }
            });

            // Load initial notifications
            loadNotifications();

            // Auto-refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        }

        async function loadNotifications() {
            try {
                const response = await fetch('<?= url('/api/notifications/recent') ?>', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.success) {
                    displayNotifications(data.notifications || []);
                    updateNotificationBadge(data.unread_count || 0);
                }
            } catch (error) {
                console.error('Failed to load notifications:', error);
            }
        }

        function displayNotifications(notifications) {
            const list = document.getElementById('notification-list');
            if (!list) return;

            if (notifications.length === 0) {
                list.innerHTML = `
                    <div class="tw-p-4 tw-text-center tw-text-gray-500">
                        <i data-feather="bell" class="tw-h-8 tw-w-8 tw-mx-auto tw-mb-2 tw-text-gray-300"></i>
                        <p>No notifications</p>
                    </div>
                `;
                feather.replace();
                return;
            }

            list.innerHTML = notifications.map(notification => `
                <div class="tw-p-4 tw-border-b tw-border-gray-100 hover:tw-bg-gray-50 tw-cursor-pointer ${!notification.is_read ? 'tw-bg-blue-50' : ''}"
                     onclick="markNotificationRead(${notification.id})">
                    <div class="tw-flex tw-items-start tw-space-x-3">
                        <div class="tw-flex-shrink-0">
                            <div class="tw-w-8 tw-h-8 tw-rounded-full tw-flex tw-items-center tw-justify-center ${getNotificationIconClass(notification.type)}">
                                <i data-feather="${getNotificationIcon(notification.type)}" class="tw-h-4 tw-w-4"></i>
                            </div>
                        </div>
                        <div class="tw-flex-1 tw-min-w-0">
                            <p class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">
                                ${notification.title}
                            </p>
                            <p class="tw-text-sm tw-text-gray-500 tw-truncate">
                                ${notification.message}
                            </p>
                            <p class="tw-text-xs tw-text-gray-400 tw-mt-1">
                                ${formatNotificationTime(notification.created_at)}
                            </p>
                        </div>
                        ${!notification.is_read ? '<div class="tw-w-2 tw-h-2 tw-bg-blue-500 tw-rounded-full tw-flex-shrink-0"></div>' : ''}
                    </div>
                </div>
            `).join('');

            feather.replace();
        }

        function getNotificationIcon(type) {
            switch (type) {
                case 'order_update': return 'shopping-bag';
                case 'system_alert': return 'alert-triangle';
                case 'promotion': return 'gift';
                case 'user_action': return 'user';
                case 'message': return 'message-circle';
                default: return 'bell';
            }
        }

        function getNotificationIconClass(type) {
            switch (type) {
                case 'order_update': return 'tw-bg-green-100 tw-text-green-600';
                case 'system_alert': return 'tw-bg-red-100 tw-text-red-600';
                case 'promotion': return 'tw-bg-purple-100 tw-text-purple-600';
                case 'user_action': return 'tw-bg-blue-100 tw-text-blue-600';
                case 'message': return 'tw-bg-yellow-100 tw-text-yellow-600';
                default: return 'tw-bg-gray-100 tw-text-gray-600';
            }
        }

        function formatNotificationTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;

            if (diff < 60000) return 'Just now';
            if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
            if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
            return Math.floor(diff / 86400000) + 'd ago';
        }

        function updateNotificationBadge(count) {
            const badge = document.getElementById('notification-badge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('tw-hidden');
                } else {
                    badge.classList.add('tw-hidden');
                }
            }
        }

        async function markNotificationRead(notificationId) {
            try {
                await fetch('<?= url('/api/notifications/mark-read') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'include',
                    body: JSON.stringify({ notification_id: notificationId })
                });

                // Reload notifications to update the display
                loadNotifications();
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        }

        async function markAllNotificationsRead() {
            try {
                await fetch('<?= url('/api/notifications/mark-all-read') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'include'
                });

                // Reload notifications to update the display
                loadNotifications();
            } catch (error) {
                console.error('Failed to mark all notifications as read:', error);
            }
        }
    </script>
</body>
</html>
