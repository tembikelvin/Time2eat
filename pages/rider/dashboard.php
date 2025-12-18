<?php
/**
 * Rider Dashboard Page - Hybrid Router Compatible
 * Direct page access without complex routing
 */

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rider') {
    header('Location: ' . url('/login'));
    exit;
}

// Load dependencies
require_once BASE_PATH . '/src/models/Order.php';
require_once BASE_PATH . '/src/models/User.php';
require_once BASE_PATH . '/src/models/Delivery.php';

try {
    $db = dbConnection();
    $riderId = $_SESSION['user_id'];
    
    // Get user data
    $userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$riderId]);
    $user = $userStmt->fetch(PDO::FETCH_OBJ);
    
    // Get rider statistics
    $statsQuery = "
        SELECT
            COUNT(*) as totalDeliveries,
            COUNT(CASE WHEN DATE(created_at) >= CURDATE() THEN 1 END) as todayDeliveries,
            COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as weekDeliveries,
            COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as monthDeliveries
        FROM orders
        WHERE rider_id = ? AND status = 'delivered'
    ";
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->execute([$riderId]);
    $statsResult = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get earnings statistics
    $earningsQuery = "
        SELECT
            COALESCE(SUM(CASE WHEN DATE(created_at) >= CURDATE() THEN delivery_fee * 0.7 END), 0) as todayEarnings,
            COALESCE(SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN delivery_fee * 0.7 END), 0) as weekEarnings,
            COALESCE(SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN delivery_fee * 0.7 END), 0) as monthEarnings,
            COALESCE(SUM(delivery_fee * 0.7), 0) as totalEarnings
        FROM orders
        WHERE rider_id = ? AND status = 'delivered'
    ";
    $earningsStmt = $db->prepare($earningsQuery);
    $earningsStmt->execute([$riderId]);
    $earningsResult = $earningsStmt->fetch(PDO::FETCH_ASSOC);
    
    $stats = [
        'totalDeliveries' => (int)($statsResult['totalDeliveries'] ?? 0),
        'todayDeliveries' => (int)($statsResult['todayDeliveries'] ?? 0),
        'weekDeliveries' => (int)($statsResult['weekDeliveries'] ?? 0),
        'monthDeliveries' => (int)($statsResult['monthDeliveries'] ?? 0),
        'todayEarnings' => (float)($earningsResult['todayEarnings'] ?? 0),
        'weekEarnings' => (float)($earningsResult['weekEarnings'] ?? 0),
        'monthEarnings' => (float)($earningsResult['monthEarnings'] ?? 0),
        'totalEarnings' => (float)($earningsResult['totalEarnings'] ?? 0),
        'currentBalance' => (float)($user->balance ?? 0)
    ];
    
    // Get active deliveries (orders currently being delivered)
    $activeDeliveriesQuery = "
        SELECT o.*, r.name as restaurant_name, r.address as restaurant_address,
               r.phone as restaurant_phone,
               c.first_name as customer_first_name, c.last_name as customer_last_name,
               c.phone as customer_phone
        FROM orders o
        LEFT JOIN restaurants r ON o.restaurant_id = r.id
        LEFT JOIN users c ON o.customer_id = c.id
        WHERE o.rider_id = ? 
        AND o.status IN ('picked_up', 'out_for_delivery')
        ORDER BY o.created_at ASC
    ";
    $activeDeliveriesStmt = $db->prepare($activeDeliveriesQuery);
    $activeDeliveriesStmt->execute([$riderId]);
    $activeDeliveries = $activeDeliveriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get available orders (ready for pickup, not yet assigned)
    $availableOrdersQuery = "
        SELECT o.*, r.name as restaurant_name, r.address as restaurant_address,
               r.phone as restaurant_phone, r.latitude, r.longitude
        FROM orders o
        LEFT JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.status IN ('ready', 'confirmed') 
        AND (o.rider_id IS NULL OR o.rider_id = 0)
        ORDER BY o.created_at ASC
        LIMIT 10
    ";
    $availableOrdersStmt = $db->prepare($availableOrdersQuery);
    $availableOrdersStmt->execute();
    $availableOrders = $availableOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activity (last 10 completed deliveries)
    $recentActivityQuery = "
        SELECT o.*, r.name as restaurant_name,
               c.first_name as customer_first_name, c.last_name as customer_last_name
        FROM orders o
        LEFT JOIN restaurants r ON o.restaurant_id = r.id
        LEFT JOIN users c ON o.customer_id = c.id
        WHERE o.rider_id = ? 
        AND o.status = 'delivered'
        ORDER BY o.updated_at DESC
        LIMIT 10
    ";
    $recentActivityStmt = $db->prepare($recentActivityQuery);
    $recentActivityStmt->execute([$riderId]);
    $recentActivity = $recentActivityStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Rider dashboard error: " . $e->getMessage());
    $stats = [
        'totalDeliveries' => 0,
        'todayDeliveries' => 0,
        'weekDeliveries' => 0,
        'monthDeliveries' => 0,
        'todayEarnings' => 0,
        'weekEarnings' => 0,
        'monthEarnings' => 0,
        'totalEarnings' => 0,
        'currentBalance' => 0
    ];
    $activeDeliveries = [];
    $availableOrders = [];
    $recentActivity = [];
}

// Set page variables for layout
$title = 'Rider Dashboard - Time2Eat';
$currentPage = 'dashboard';
$userRole = 'rider';

// Start output buffering to capture the view content
ob_start();

// Include the rider dashboard view
include BASE_PATH . '/src/views/dashboard/rider.php';

// Get the content
$content = ob_get_clean();

// Include the dashboard layout which will render the content
require_once BASE_PATH . '/src/views/layouts/dashboard.php';
?>
