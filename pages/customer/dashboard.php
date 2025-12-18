<?php
/**
 * Customer Dashboard Page - Hybrid Router Compatible
 * Direct page access without complex routing
 */

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ' . url('/login'));
    exit;
}

// Load dependencies
require_once BASE_PATH . '/src/models/Order.php';
require_once BASE_PATH . '/src/models/User.php';
require_once BASE_PATH . '/src/models/Restaurant.php';

try {
    $db = dbConnection();
    $customerId = $_SESSION['user_id'];
    
    // Get user data
    $userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$customerId]);
    $user = $userStmt->fetch(PDO::FETCH_OBJ);
    
    // Get customer statistics
    $statsQuery = "
        SELECT
            COUNT(*) as totalOrders,
            COALESCE(SUM(total_amount), 0) as totalSpent,
            COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as monthlyOrders,
            COUNT(DISTINCT restaurant_id) as favoriteRestaurants
        FROM orders
        WHERE customer_id = ? AND status != 'cancelled'
    ";
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->execute([$customerId]);
    $statsResult = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $stats = [
        'totalOrders' => (int)($statsResult['totalOrders'] ?? 0),
        'totalSpent' => (float)($statsResult['totalSpent'] ?? 0),
        'monthlyOrders' => (int)($statsResult['monthlyOrders'] ?? 0),
        'favoriteRestaurants' => (int)($statsResult['favoriteRestaurants'] ?? 0)
    ];
    
    // Get recent orders (last 5)
    $recentOrdersQuery = "
        SELECT o.*, r.name as restaurant_name, r.image as restaurant_image
        FROM orders o
        LEFT JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.customer_id = ?
        ORDER BY o.created_at DESC
        LIMIT 5
    ";
    $recentOrdersStmt = $db->prepare($recentOrdersQuery);
    $recentOrdersStmt->execute([$customerId]);
    $recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get live orders (orders in progress)
    $liveOrdersQuery = "
        SELECT o.*, r.name as restaurant_name, r.image as restaurant_image,
               rider.first_name as rider_first_name, rider.last_name as rider_last_name
        FROM orders o
        LEFT JOIN restaurants r ON o.restaurant_id = r.id
        LEFT JOIN users rider ON o.rider_id = rider.id
        WHERE o.customer_id = ? 
        AND o.status IN ('pending', 'confirmed', 'preparing', 'ready', 'picked_up', 'out_for_delivery')
        ORDER BY o.created_at DESC
    ";
    $liveOrdersStmt = $db->prepare($liveOrdersQuery);
    $liveOrdersStmt->execute([$customerId]);
    $liveOrders = $liveOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get favorite restaurants (based on order frequency)
    $favoriteRestaurantsQuery = "
        SELECT r.*, COUNT(o.id) as order_count,
               COALESCE(AVG(rev.rating), 0) as avg_rating
        FROM restaurants r
        INNER JOIN orders o ON r.id = o.restaurant_id
        LEFT JOIN reviews rev ON r.id = rev.reviewable_id AND rev.reviewable_type = 'restaurant'
        WHERE o.customer_id = ? AND o.status = 'delivered'
        GROUP BY r.id
        ORDER BY order_count DESC
        LIMIT 3
    ";
    $favoriteRestaurantsStmt = $db->prepare($favoriteRestaurantsQuery);
    $favoriteRestaurantsStmt->execute([$customerId]);
    $favoriteRestaurants = $favoriteRestaurantsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Customer dashboard error: " . $e->getMessage());
    $stats = ['totalOrders' => 0, 'totalSpent' => 0, 'monthlyOrders' => 0, 'favoriteRestaurants' => 0];
    $recentOrders = [];
    $liveOrders = [];
    $favoriteRestaurants = [];
}

// Set page variables for layout
$title = 'Customer Dashboard - Time2Eat';
$currentPage = 'dashboard';
$userRole = 'customer';

// Start output buffering to capture the view content
ob_start();

// Include the customer dashboard view
include BASE_PATH . '/src/views/dashboard/customer.php';

// Get the content
$content = ob_get_clean();

// Include the dashboard layout which will render the content
require_once BASE_PATH . '/src/views/layouts/dashboard.php';
?>
