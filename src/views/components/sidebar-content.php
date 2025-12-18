<?php
// Ensure helper functions are available
if (!function_exists('url')) {
    require_once __DIR__ . '/../../helpers/functions.php';
}

$user = $user ?? null;
$currentPage = $currentPage ?? '';

// Debug: Log current page for troubleshooting
if (defined('APP_DEBUG') && APP_DEBUG) {
    error_log("Sidebar Debug - Current Page: " . $currentPage . ", User Role: " . ($userRole ?? 'unknown'));
}

// Fallback: If user is null, try to get it from session (production fix)
if (is_null($user) && isset($_SESSION['user_id'])) {
    try {
        // Check if Database class exists
        if (class_exists('\\Database')) {
            // Try to get user from database
            $db = \Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$_SESSION['user_id']]);
            $userData = $stmt->fetch();
            
            if ($userData) {
                $user = [
                    'id' => $userData['id'],
                    'email' => $userData['email'] ?? '',
                    'first_name' => $userData['first_name'] ?? ($userData['name'] ?? 'User'),
                    'last_name' => $userData['last_name'] ?? '',
                    'name' => $userData['name'] ?? ($userData['first_name'] ?? 'User'),
                    'role' => $userData['role'] ?? 'customer',
                    'status' => $userData['status'] ?? 'active'
                ];
            }
        }
    } catch (Exception $e) {
        // Silent fallback - user remains null
    }
}

// Debug: Log sidebar loading for production troubleshooting
if (defined('APP_ENV') && APP_ENV === 'production') {
    error_log("Sidebar loaded for user role: " . ($userRole ?? 'unknown') . " at " . date('Y-m-d H:i:s'));
}

// Ensure user role is properly set with fallback
if (is_object($user)) {
    $userRole = $user->role ?? 'customer';
} elseif (is_array($user)) {
    $userRole = $user['role'] ?? 'customer';
} else {
    $userRole = 'customer';
}


// Debug: Log user role for production troubleshooting
if (defined('APP_ENV') && APP_ENV === 'production') {
    error_log("User role determined: " . $userRole . " for user: " . (is_array($user) ? json_encode($user) : 'object'));
}

// Define sidebar items based on user role
$sidebarItems = [];

switch ($userRole) {
    case 'customer':
        $sidebarItems = [
            ['icon' => 'home', 'label' => 'Dashboard', 'url' => '/customer/dashboard', 'page' => 'dashboard'],
            ['icon' => 'shopping-bag', 'label' => 'My Orders', 'url' => '/customer/orders', 'page' => 'orders'],
            ['icon' => 'heart', 'label' => 'Favorites', 'url' => '/customer/favorites', 'page' => 'favorites'],
            ['icon' => 'alert-triangle', 'label' => 'My Disputes', 'url' => '/customer/disputes', 'page' => 'disputes'],
            ['icon' => 'map-pin', 'label' => 'Addresses', 'url' => '/customer/addresses', 'page' => 'addresses'],
            ['icon' => 'credit-card', 'label' => 'Payment Methods', 'url' => '/customer/payments', 'page' => 'payments'],
            ['icon' => 'dollar-sign', 'label' => 'Affiliate Program', 'url' => '/customer/affiliates', 'page' => 'affiliates'],
            ['icon' => 'user-plus', 'label' => 'Become Vendor/Rider', 'url' => '/customer/role-request', 'page' => 'role-request'],
            ['icon' => 'user', 'label' => 'Profile', 'url' => '/customer/profile', 'page' => 'profile']
        ];
        break;
        
    case 'vendor':
        $sidebarItems = [
            ['icon' => 'home', 'label' => 'Dashboard', 'url' => '/vendor/dashboard', 'page' => 'dashboard'],
            ['icon' => 'coffee', 'label' => 'Restaurant Profile', 'url' => '/vendor/restaurant', 'page' => 'restaurant'],
            ['icon' => 'menu', 'label' => 'Menu Items', 'url' => '/vendor/menu', 'page' => 'menu'],
            ['icon' => 'folder', 'label' => 'Categories', 'url' => '/vendor/categories', 'page' => 'categories'],
            ['icon' => 'shopping-bag', 'label' => 'Orders', 'url' => '/vendor/orders', 'page' => 'orders'],
            ['icon' => 'package', 'label' => 'Inventory', 'url' => '/vendor/menu?view=inventory', 'page' => 'inventory'],
            ['icon' => 'bar-chart-2', 'label' => 'Analytics', 'url' => '/vendor/analytics', 'page' => 'analytics'],
            ['icon' => 'dollar-sign', 'label' => 'Earnings', 'url' => '/vendor/earnings', 'page' => 'earnings'],
            ['icon' => 'star', 'label' => 'Reviews', 'url' => '/vendor/reviews', 'page' => 'reviews'],
            ['icon' => 'alert-triangle', 'label' => 'Disputes', 'url' => '/vendor/disputes', 'page' => 'disputes'],
            ['icon' => 'message-circle', 'label' => 'Messages', 'url' => '/vendor/messages', 'page' => 'messages'],
            ['icon' => 'settings', 'label' => 'Settings', 'url' => '/vendor/settings', 'page' => 'settings']
        ];
        break;
        
    case 'rider':
        $sidebarItems = [
            ['icon' => 'home', 'label' => 'Dashboard', 'url' => '/rider/dashboard', 'page' => 'dashboard'],
            ['icon' => 'truck', 'label' => 'Active Deliveries', 'url' => '/rider/deliveries', 'page' => 'deliveries'],
            ['icon' => 'map', 'label' => 'Available Orders', 'url' => '/rider/available', 'page' => 'available'],
            ['icon' => 'clock', 'label' => 'Schedule', 'url' => '/rider/schedule', 'page' => 'schedule'],
            ['icon' => 'dollar-sign', 'label' => 'Earnings', 'url' => '/rider/earnings', 'page' => 'earnings'],
            ['icon' => 'bar-chart-2', 'label' => 'Performance', 'url' => '/rider/performance', 'page' => 'performance'],
            ['icon' => 'user', 'label' => 'Profile', 'url' => '/rider/profile', 'page' => 'profile'],
            ['icon' => 'message-circle', 'label' => 'Messages', 'url' => '/rider/messages', 'page' => 'messages'],
            ['icon' => 'alert-triangle', 'label' => 'Report Issue', 'url' => '/rider/report-issue', 'page' => 'reports']
        ];
        break;
        
    case 'admin':
        $sidebarItems = [
            ['icon' => 'home', 'label' => 'Dashboard', 'url' => '/admin/dashboard', 'page' => 'dashboard'],
            ['icon' => 'users', 'label' => 'User Management', 'url' => '/admin/users', 'page' => 'users'],
            ['icon' => 'user-check', 'label' => 'Role Requests', 'url' => '/admin/user-management/role-requests', 'page' => 'role-requests'],
            ['icon' => 'activity', 'label' => 'User Activity', 'url' => '/admin/user-management/activity', 'page' => 'user-activity'],
            ['icon' => 'bar-chart', 'label' => 'User Analytics', 'url' => '/admin/user-management/analytics', 'page' => 'user-analytics'],
            ['icon' => 'message-square', 'label' => 'Messages', 'url' => '/admin/messages', 'page' => 'messages'],
            ['icon' => 'coffee', 'label' => 'Restaurants', 'url' => '/admin/restaurants', 'page' => 'restaurants'],
            ['icon' => 'shopping-bag', 'label' => 'Orders', 'url' => '/admin/orders', 'page' => 'orders'],
            ['icon' => 'star', 'label' => 'Reviews', 'url' => '/admin/reviews', 'page' => 'reviews'],
            ['icon' => 'trending-up', 'label' => 'Profit Analytics', 'url' => '/admin/profit-analytics', 'page' => 'profit-analytics'],
            ['icon' => 'truck', 'label' => 'Deliveries', 'url' => '/admin/deliveries', 'page' => 'deliveries'],
            ['icon' => 'map-pin', 'label' => 'Delivery Zones', 'url' => '/admin/deliveries/zones', 'page' => 'delivery-zones'],
            ['icon' => 'user-plus', 'label' => 'Rider Management', 'url' => '/admin/riders', 'page' => 'riders'],
            ['icon' => 'alert-triangle', 'label' => 'Disputes', 'url' => '/admin/disputes', 'page' => 'disputes'],
            ['icon' => 'bar-chart-2', 'label' => 'Analytics', 'url' => '/admin/analytics', 'page' => 'analytics'],
            ['icon' => 'dollar-sign', 'label' => 'Financial', 'url' => '/admin/financial', 'page' => 'financial'],
            [
                'icon' => 'percent',
                'label' => 'Affiliates',
                'url' => '/admin/affiliate/dashboard',
                'page' => 'affiliates',
                'submenu' => [
                    ['icon' => 'users', 'label' => 'Dashboard', 'url' => '/admin/affiliate/dashboard', 'page' => 'affiliates'],
                    ['icon' => 'credit-card', 'label' => 'Withdrawals', 'url' => '/admin/affiliate/withdrawals', 'page' => 'affiliate-withdrawals'],
                    ['icon' => 'dollar-sign', 'label' => 'Payouts', 'url' => '/admin/affiliate/payouts', 'page' => 'affiliate-payouts'],
                    ['icon' => 'bar-chart-2', 'label' => 'Analytics', 'url' => '/admin/affiliate/analytics', 'page' => 'affiliate-analytics'],
                    ['icon' => 'message-circle', 'label' => 'Communication', 'url' => '/admin/affiliate/communication', 'page' => 'affiliate-communication']
                ]
            ],
            ['icon' => 'bell', 'label' => 'Notifications', 'url' => '/admin/notifications', 'page' => 'notifications'],
            ['icon' => 'folder', 'label' => 'Categories', 'url' => '/admin/categories', 'page' => 'categories'],
            ['icon' => 'database', 'label' => 'Data Management', 'url' => '/admin/data', 'page' => 'data'],
            ['icon' => 'database', 'label' => 'Backups', 'url' => '/admin/tools/backups', 'page' => 'backups'],
            ['icon' => 'shield', 'label' => 'Security Logs', 'url' => '/admin/logs', 'page' => 'logs'],
            ['icon' => 'credit-card', 'label' => 'Payment Settings', 'url' => '/admin/payment-settings', 'page' => 'payment-settings'],
            ['icon' => 'dollar-sign', 'label' => 'Withdrawals', 'url' => '/admin/withdrawals', 'page' => 'withdrawals'],
            ['icon' => 'settings', 'label' => 'Settings', 'url' => '/admin/tools/settings', 'page' => 'settings']
        ];
        
        // Debug: Log admin sidebar items for production troubleshooting
        if (defined('APP_ENV') && APP_ENV === 'production') {
            error_log("Admin sidebar items loaded: " . count($sidebarItems) . " items");
        }
        break;
        
    default:
        // Fallback: If user role is not properly detected, default to admin sidebar
        // This ensures the sidebar always shows admin items in production
        if (defined('APP_ENV') && APP_ENV === 'production') {
            error_log("Unknown user role: " . $userRole . ", defaulting to admin sidebar");
        }
        
        $sidebarItems = [
            ['icon' => 'home', 'label' => 'Dashboard', 'url' => '/admin/dashboard', 'page' => 'dashboard'],
            ['icon' => 'users', 'label' => 'User Management', 'url' => '/admin/users', 'page' => 'users'],
            ['icon' => 'user-check', 'label' => 'Role Requests', 'url' => '/admin/user-management/role-requests', 'page' => 'role-requests'],
            ['icon' => 'activity', 'label' => 'User Activity', 'url' => '/admin/user-management/activity', 'page' => 'user-activity'],
            ['icon' => 'bar-chart', 'label' => 'User Analytics', 'url' => '/admin/user-management/analytics', 'page' => 'user-analytics'],
            ['icon' => 'message-square', 'label' => 'Messages', 'url' => '/admin/messages', 'page' => 'messages'],
            ['icon' => 'coffee', 'label' => 'Restaurants', 'url' => '/admin/restaurants', 'page' => 'restaurants'],
            ['icon' => 'shopping-bag', 'label' => 'Orders', 'url' => '/admin/orders', 'page' => 'orders'],
            ['icon' => 'trending-up', 'label' => 'Profit Analytics', 'url' => '/admin/profit-analytics', 'page' => 'profit-analytics'],
            ['icon' => 'truck', 'label' => 'Deliveries', 'url' => '/admin/deliveries', 'page' => 'deliveries'],
            ['icon' => 'map-pin', 'label' => 'Delivery Zones', 'url' => '/admin/deliveries/zones', 'page' => 'delivery-zones'],
            ['icon' => 'user-plus', 'label' => 'Rider Management', 'url' => '/admin/riders', 'page' => 'riders'],
            ['icon' => 'alert-triangle', 'label' => 'Disputes', 'url' => '/admin/disputes', 'page' => 'disputes'],
            ['icon' => 'bar-chart-2', 'label' => 'Analytics', 'url' => '/admin/analytics', 'page' => 'analytics'],
            ['icon' => 'dollar-sign', 'label' => 'Financial', 'url' => '/admin/financial', 'page' => 'financial'],
            [
                'icon' => 'percent',
                'label' => 'Affiliates',
                'url' => '/admin/affiliate/dashboard',
                'page' => 'affiliates',
                'submenu' => [
                    ['icon' => 'users', 'label' => 'Dashboard', 'url' => '/admin/affiliate/dashboard', 'page' => 'affiliates'],
                    ['icon' => 'credit-card', 'label' => 'Withdrawals', 'url' => '/admin/affiliate/withdrawals', 'page' => 'affiliate-withdrawals'],
                    ['icon' => 'dollar-sign', 'label' => 'Payouts', 'url' => '/admin/affiliate/payouts', 'page' => 'affiliate-payouts'],
                    ['icon' => 'bar-chart-2', 'label' => 'Analytics', 'url' => '/admin/affiliate/analytics', 'page' => 'affiliate-analytics'],
                    ['icon' => 'message-circle', 'label' => 'Communication', 'url' => '/admin/affiliate/communication', 'page' => 'affiliate-communication']
                ]
            ],
            ['icon' => 'bell', 'label' => 'Notifications', 'url' => '/admin/notifications', 'page' => 'notifications'],
            ['icon' => 'folder', 'label' => 'Categories', 'url' => '/admin/categories', 'page' => 'categories'],
            ['icon' => 'database', 'label' => 'Data Management', 'url' => '/admin/data', 'page' => 'data'],
            ['icon' => 'database', 'label' => 'Backups', 'url' => '/admin/tools/backups', 'page' => 'backups'],
            ['icon' => 'shield', 'label' => 'Security Logs', 'url' => '/admin/logs', 'page' => 'logs'],
            ['icon' => 'credit-card', 'label' => 'Payment Settings', 'url' => '/admin/payment-settings', 'page' => 'payment-settings'],
            ['icon' => 'settings', 'label' => 'Settings', 'url' => '/admin/tools/settings', 'page' => 'settings']
        ];
        break;
}
?>

<div class="tw-flex tw-flex-col tw-h-0 tw-flex-1 tw-pt-5 tw-pb-4 tw-overflow-y-auto">
    <!-- Logo -->
    <div class="tw-flex tw-items-center tw-flex-shrink-0 tw-px-4">
        <div class="tw-flex tw-items-center">
            <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                <i data-feather="zap" class="tw-h-5 tw-w-5 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
            </div>
            <h1 class="tw-ml-3 tw-text-xl tw-font-bold tw-text-gray-900">Time2Eat</h1>
        </div>
    </div>
    
    <!-- User info -->
    <div class="tw-mt-5 tw-px-4">
        <div class="tw-flex tw-items-center tw-p-3 tw-bg-gray-50 tw-rounded-lg">
            <div class="tw-h-10 tw-w-10 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                <span class="tw-text-white tw-font-semibold tw-text-sm">
                    <?php
                    $firstName = 'U';
                    if ($user && (is_object($user) || is_array($user))) {
                        if (is_object($user)) {
                            $firstName = $user->first_name ?? ($user->name ?? 'U');
                        } else {
                            $firstName = $user['first_name'] ?? ($user['name'] ?? 'U');
                        }
                    }
                    echo strtoupper(substr($firstName, 0, 1));
                    ?>
                </span>
            </div>
            <div class="tw-ml-3 tw-flex-1 tw-min-w-0">
                <p class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">
                    <?php
                    $firstName = 'User';
                    $lastName = '';
                    if ($user && (is_object($user) || is_array($user))) {
                        if (is_object($user)) {
                            $firstName = $user->first_name ?? ($user->name ?? 'User');
                            $lastName = $user->last_name ?? '';
                        } else {
                            $firstName = $user['first_name'] ?? ($user['name'] ?? 'User');
                            $lastName = $user['last_name'] ?? '';
                        }
                    }
                    echo e($firstName) . ' ' . e($lastName);
                    ?>
                </p>
                <p class="tw-text-xs tw-text-gray-500 tw-truncate">
                    <?= ucfirst($userRole) ?>
                    <?php 
                    $affiliateCode = '';
                    if ($user && (is_object($user) || is_array($user))) {
                        if (is_object($user)) {
                            $affiliateCode = property_exists($user, 'affiliate_code') ? $user->affiliate_code : '';
                        } else {
                            $affiliateCode = $user['affiliate_code'] ?? '';
                        }
                    }
                    if ($userRole === 'customer' && !empty($affiliateCode)): 
                    ?>
                        • Affiliate
                    <?php endif; ?>
                </p>
            </div>
            <div class="tw-flex-shrink-0">
                <?php if ($userRole === 'customer'): ?>
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                        <?php 
                        $balance = 0;
                        if ($user && (is_object($user) || is_array($user))) {
                            if (is_object($user)) {
                                $balance = property_exists($user, 'balance') ? $user->balance : 0;
                            } else {
                                $balance = $user['balance'] ?? 0;
                            }
                        }
                        echo number_format($balance);
                        ?> XAF
                    </span>
                <?php elseif ($userRole === 'vendor'): ?>
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                        <?php 
                        $status = 'active';
                        if ($user && (is_object($user) || is_array($user))) {
                            if (is_object($user)) {
                                $status = property_exists($user, 'status') ? $user->status : 'active';
                            } else {
                                $status = $user['status'] ?? 'active';
                            }
                        }
                        echo ($status === 'approved') ? 'Approved' : 'Active';
                        ?>
                    </span>
                <?php elseif ($userRole === 'rider'): ?>
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800">
                        <?php 
                        $isAvailable = false;
                        if ($user && (is_object($user) || is_array($user))) {
                            if (is_object($user)) {
                                $isAvailable = property_exists($user, 'is_available') ? $user->is_available : false;
                            } else {
                                $isAvailable = $user['is_available'] ?? false;
                            }
                        }
                        echo $isAvailable ? 'Available' : 'Offline';
                        ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="tw-mt-5 tw-flex-1 tw-px-2 tw-space-y-1">
        <?php foreach ($sidebarItems as $item): ?>
            <?php 
            $isActive = $currentPage === $item['page'];
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Sidebar Item: " . $item['page'] . " - Current: " . $currentPage . " - Active: " . ($isActive ? 'YES' : 'NO'));
            }
            ?>
            <a href="<?= url($item['url']) ?>"
               class="sidebar-item tw-text-gray-600 <?= $isActive ? 'active' : '' ?>"
               data-page="<?= e($item['page']) ?>">
                <i data-feather="<?= e($item['icon']) ?>" class="tw-mr-3 tw-flex-shrink-0 tw-h-5 tw-w-5" style="display: flex; align-items: center; justify-content: center;"></i>
                <?= e($item['label']) ?>

                <?php if ($item['page'] === 'orders' && $userRole === 'vendor'): ?>
                    <?php if (isset($stats) && ($stats['pendingOrders'] ?? 0) > 0): ?>
                    <span class="tw-ml-auto tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                        <?= $stats['pendingOrders'] ?>
                    </span>
                    <?php endif; ?>
                <?php elseif ($item['page'] === 'deliveries' && $userRole === 'rider'): ?>
                    <?php if (isset($stats) && ($stats['pendingDeliveries'] ?? 0) > 0): ?>
                    <span class="tw-ml-auto tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                        <?= $stats['pendingDeliveries'] ?>
                    </span>
                    <?php endif; ?>
                <?php elseif ($item['page'] === 'messages'): ?>
                    <?php if (isset($stats) && ($stats['unreadMessages'] ?? 0) > 0): ?>
                    <span class="tw-ml-auto tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                        <?= $stats['unreadMessages'] ?>
                    </span>
                    <?php endif; ?>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>

        <?php if ($userRole === 'admin'): ?>
            <!-- Spacer div under settings for admin -->
            <div class="tw-h-4"></div>
        <?php elseif ($userRole === 'vendor'): ?>
            <!-- Spacer div under settings for vendor -->
            <div class="tw-h-4"></div>
        <?php endif; ?>
    </nav>

    <!-- Quick actions -->
    <div class="tw-flex-shrink-0 tw-px-2 tw-pb-4">
        <div class="tw-space-y-2">
            <?php if ($userRole === 'customer'): ?>
                <a href="<?= url('/browse') ?>" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg hover:tw-from-orange-600 hover:tw-to-red-600 tw-transition-all tw-duration-200">
                    <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    Order Food
                </a>
            <?php elseif ($userRole === 'vendor'): ?>
                <a href="<?= url('/vendor/menu/create') ?>" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg hover:tw-from-orange-600 hover:tw-to-red-600 tw-transition-all tw-duration-200">
                    <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    Add Menu Item
                </a>
            <?php elseif ($userRole === 'rider'): ?>
                <?php
                $isAvailable = false;
                if ($user && (is_object($user) || is_array($user))) {
                    if (is_object($user)) {
                        $isAvailable = property_exists($user, 'is_available') ? $user->is_available : false;
                    } else {
                        $isAvailable = $user['is_available'] ?? false;
                    }
                }
                ?>
                <button onclick="toggleAvailability()" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-rounded-lg tw-transition-all tw-duration-200 <?= $isAvailable ? 'tw-bg-gradient-to-r tw-from-red-500 tw-to-red-600 hover:tw-from-red-600 hover:tw-to-red-700' : 'tw-bg-gradient-to-r tw-from-green-500 tw-to-blue-500 hover:tw-from-green-600 hover:tw-to-blue-600' ?>" id="availability-toggle">
                    <i data-feather="power" class="tw-h-4 tw-w-4 tw-mr-2 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    <?= $isAvailable ? 'Go Offline' : 'Go Online' ?>
                </button>
            <?php elseif ($userRole === 'admin'): ?>
                <a href="<?= url('/admin/notifications') ?>" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-bg-gradient-to-r tw-from-purple-500 tw-to-pink-500 tw-rounded-lg hover:tw-from-purple-600 hover:tw-to-pink-600 tw-transition-all tw-duration-200">
                    <i data-feather="bell" class="tw-h-4 tw-w-4 tw-mr-2 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    Send Notification
                </a>

                <!-- Separator Line -->
                <div class="tw-border-t tw-border-gray-200 tw-my-3"></div>

                <a href="<?= url('/') ?>" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-bg-gradient-to-r tw-from-blue-500 tw-to-indigo-500 tw-rounded-lg hover:tw-from-blue-600 hover:tw-to-indigo-600 tw-transition-all tw-duration-200">
                    <i data-feather="home" class="tw-h-4 tw-w-4 tw-mr-2 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    Back to Home
                </a>
            <?php endif; ?>

            <!-- Help/Support -->
            <a href="<?= url('/contact') ?>" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-gray-100 tw-rounded-lg hover:tw-bg-gray-200 tw-transition-colors tw-duration-200">
                <i data-feather="help-circle" class="tw-h-4 tw-w-4 tw-mr-2 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                Help & Support
            </a>

            <!-- Logout -->
            <form method="POST" action="<?= url('/logout') ?>" class="tw-w-full tw-m-0">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-bg-red-600 tw-rounded-lg hover:tw-bg-red-700 tw-transition-colors tw-duration-200 tw-border-0 tw-cursor-pointer">
                    <i data-feather="log-out" class="tw-h-4 tw-w-4 tw-mr-2 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Rider availability toggle
function toggleAvailability() {
    const btn = document.getElementById('availability-toggle');
    const isOnline = btn.textContent.includes('Go Offline');
    
    // Get CSRF token
    const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    
    // Show loading state
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Updating...';
    btn.disabled = true;
    feather.replace();
    
    fetch('<?= url('/rider/toggle-availability') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({ 
            available: !isOnline,
            csrf_token: csrfToken
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update button appearance
            if (isOnline) {
                btn.innerHTML = '<i data-feather="power" class="tw-h-4 tw-w-4 tw-mr-2"></i>Go Online';
                btn.className = btn.className.replace('tw-bg-gradient-to-r tw-from-red-500 tw-to-red-600 hover:tw-from-red-600 hover:tw-to-red-700', 'tw-bg-gradient-to-r tw-from-green-500 tw-to-blue-500 hover:tw-from-green-600 hover:tw-to-blue-600');
            } else {
                btn.innerHTML = '<i data-feather="power" class="tw-h-4 tw-w-4 tw-mr-2"></i>Go Offline';
                btn.className = btn.className.replace('tw-bg-gradient-to-r tw-from-green-500 tw-to-blue-500 hover:tw-from-green-600 hover:tw-to-blue-600', 'tw-bg-gradient-to-r tw-from-red-500 tw-to-red-600 hover:tw-from-red-600 hover:tw-to-red-700');
            }
            feather.replace();
            
            // Show success notification
            showSidebarNotification(data.message || 'Status updated successfully', 'success');
        } else {
            // Restore original button state
            btn.innerHTML = originalText;
            btn.disabled = false;
            feather.replace();
            showSidebarNotification(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error toggling availability:', error);
        // Restore original button state
        btn.innerHTML = originalText;
        btn.disabled = false;
        feather.replace();
        showSidebarNotification('Failed to update status. Please try again.', 'error');
    });
}

// Sidebar notification function
function showSidebarNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-z-50 tw-transition-all tw-duration-300 ${
        type === 'success' ? 'tw-bg-green-500 tw-text-white' : 
        type === 'error' ? 'tw-bg-red-500 tw-text-white' : 
        'tw-bg-blue-500 tw-text-white'
    }`;
    notification.innerHTML = `
        <div class="tw-flex tw-items-center tw-space-x-2">
            <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="tw-w-5 tw-h-5"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    feather.replace();
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Update notification badges (disabled - API endpoint not available)
function updateNotificationBadges() {
    // Disabled: API endpoint /api/notifications/counts not available
    console.log('Badge update disabled - API endpoint not available');
}

// Update badges every 30 seconds (disabled)
// setInterval(updateNotificationBadges, 30000);
</script>
