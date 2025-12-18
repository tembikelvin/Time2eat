<?php
/**
 * Admin Dashboard Page
 * Simple, direct approach - no complex routing
 */

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /eat/login');
    exit;
}

// Load dependencies
require_once BASE_PATH . '/src/models/Order.php';
require_once BASE_PATH . '/src/models/User.php';
require_once BASE_PATH . '/src/models/Restaurant.php';

try {
    // Get dashboard statistics
    $orderModel = new models\Order();
    $userModel = new models\User();
    $restaurantModel = new models\Restaurant();
    
    // Get stats
    $totalOrders = $orderModel->count();
    $totalUsers = $userModel->count();
    $totalRestaurants = $restaurantModel->count();
    
    // Get recent orders
    $recentOrders = $orderModel->query(
        "SELECT o.*, u.first_name, u.last_name, r.name as restaurant_name 
         FROM orders o 
         LEFT JOIN users u ON o.customer_id = u.id 
         LEFT JOIN restaurants r ON o.restaurant_id = r.id 
         ORDER BY o.created_at DESC 
         LIMIT 10"
    );
    
    // Get revenue stats
    $revenueToday = $orderModel->fetchOne(
        "SELECT COALESCE(SUM(total_amount), 0) as total 
         FROM orders 
         WHERE DATE(created_at) = CURDATE() 
         AND status IN ('delivered', 'completed')"
    )['total'] ?? 0;
    
    $revenueMonth = $orderModel->fetchOne(
        "SELECT COALESCE(SUM(total_amount), 0) as total 
         FROM orders 
         WHERE MONTH(created_at) = MONTH(CURDATE()) 
         AND YEAR(created_at) = YEAR(CURDATE())
         AND status IN ('delivered', 'completed')"
    )['total'] ?? 0;
    
    // Get pending orders count
    $pendingOrders = $orderModel->fetchOne(
        "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'"
    )['count'] ?? 0;
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalOrders = 0;
    $totalUsers = 0;
    $totalRestaurants = 0;
    $recentOrders = [];
    $revenueToday = 0;
    $revenueMonth = 0;
    $pendingOrders = 0;
}

// Page title
$pageTitle = 'Admin Dashboard';
$currentPage = 'dashboard';

// Include header
require_once BASE_PATH . '/src/views/layouts/dashboard.php';
?>

<!-- Dashboard Content -->
<div class="tw-p-6">
    <!-- Page Header -->
    <div class="tw-mb-6">
        <h1 class="tw-text-3xl tw-font-bold tw-text-gray-800">Dashboard</h1>
        <p class="tw-text-gray-600">Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?>!</p>
    </div>
    
    <!-- Stats Grid -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
        <!-- Total Orders -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-gray-500 tw-text-sm tw-font-medium">Total Orders</p>
                    <h3 class="tw-text-3xl tw-font-bold tw-text-gray-800 tw-mt-2"><?= number_format($totalOrders) ?></h3>
                </div>
                <div class="tw-bg-blue-100 tw-rounded-full tw-p-3">
                    <i data-feather="shopping-cart" class="tw-text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Users -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-gray-500 tw-text-sm tw-font-medium">Total Users</p>
                    <h3 class="tw-text-3xl tw-font-bold tw-text-gray-800 tw-mt-2"><?= number_format($totalUsers) ?></h3>
                </div>
                <div class="tw-bg-green-100 tw-rounded-full tw-p-3">
                    <i data-feather="users" class="tw-text-green-600"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Restaurants -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-gray-500 tw-text-sm tw-font-medium">Restaurants</p>
                    <h3 class="tw-text-3xl tw-font-bold tw-text-gray-800 tw-mt-2"><?= number_format($totalRestaurants) ?></h3>
                </div>
                <div class="tw-bg-purple-100 tw-rounded-full tw-p-3">
                    <i data-feather="home" class="tw-text-purple-600"></i>
                </div>
            </div>
        </div>
        
        <!-- Revenue Today -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-gray-500 tw-text-sm tw-font-medium">Revenue Today</p>
                    <h3 class="tw-text-3xl tw-font-bold tw-text-gray-800 tw-mt-2"><?= number_format($revenueToday) ?> XAF</h3>
                </div>
                <div class="tw-bg-yellow-100 tw-rounded-full tw-p-3">
                    <i data-feather="dollar-sign" class="tw-text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Orders Alert -->
    <?php if ($pendingOrders > 0): ?>
    <div class="tw-bg-orange-100 tw-border-l-4 tw-border-orange-500 tw-text-orange-700 tw-p-4 tw-mb-6 tw-rounded">
        <div class="tw-flex tw-items-center">
            <i data-feather="alert-circle" class="tw-mr-2"></i>
            <p>
                <strong>Attention:</strong> You have <?= $pendingOrders ?> pending order<?= $pendingOrders > 1 ? 's' : '' ?> waiting for processing.
                <a href="/eat/admin/orders" class="tw-underline tw-font-semibold">View Orders</a>
            </p>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recent Orders -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h2 class="tw-text-xl tw-font-bold tw-text-gray-800">Recent Orders</h2>
            <a href="/eat/admin/orders" class="tw-text-blue-600 tw-hover:tw-underline">View All</a>
        </div>
        
        <?php if (empty($recentOrders)): ?>
            <p class="tw-text-gray-500 tw-text-center tw-py-8">No orders yet</p>
        <?php else: ?>
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                    <thead class="tw-bg-gray-50">
                        <tr>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Order #</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Customer</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Restaurant</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Amount</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Status</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                        <?php foreach ($recentOrders as $order): ?>
                        <tr class="tw-hover:tw-bg-gray-50">
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">
                                #<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                <?= htmlspecialchars($order['restaurant_name']) ?>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                <?= number_format($order['total_amount']) ?> XAF
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <span class="tw-px-2 tw-inline-flex tw-text-xs tw-leading-5 tw-font-semibold tw-rounded-full 
                                    <?php
                                    switch($order['status']) {
                                        case 'pending': echo 'tw-bg-yellow-100 tw-text-yellow-800'; break;
                                        case 'confirmed': echo 'tw-bg-blue-100 tw-text-blue-800'; break;
                                        case 'preparing': echo 'tw-bg-purple-100 tw-text-purple-800'; break;
                                        case 'ready': echo 'tw-bg-indigo-100 tw-text-indigo-800'; break;
                                        case 'picked_up': echo 'tw-bg-orange-100 tw-text-orange-800'; break;
                                        case 'delivered': echo 'tw-bg-green-100 tw-text-green-800'; break;
                                        case 'cancelled': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                        default: echo 'tw-bg-gray-100 tw-text-gray-800';
                                    }
                                    ?>">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                <?= date('M d, Y H:i', strtotime($order['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>

<?php
// Include footer (if using layout system)
// require_once BASE_PATH . '/src/views/layouts/footer.php';
?>
