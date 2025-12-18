<?php
/**
 * Customer Orders Page - Hybrid Router Compatible
 */

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ' . url('/login'));
    exit;
}

// Load dependencies
require_once BASE_PATH . '/src/models/Order.php';
require_once BASE_PATH . '/src/models/User.php';

try {
    $db = dbConnection();
    $customerId = $_SESSION['user_id'];
    
    // Get user data
    $userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$customerId]);
    $user = $userStmt->fetch(PDO::FETCH_OBJ);
    
    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Get filter
    $status = $_GET['status'] ?? 'all';
    
    // Build query
    $whereClause = "o.customer_id = ?";
    $params = [$customerId];
    
    if ($status !== 'all') {
        $whereClause .= " AND o.status = ?";
        $params[] = $status;
    }
    
    // Get orders with pagination
    $ordersQuery = "
        SELECT o.*, r.name as restaurant_name, r.image as restaurant_image,
               rider.first_name as rider_first_name, rider.last_name as rider_last_name
        FROM orders o
        LEFT JOIN restaurants r ON o.restaurant_id = r.id
        LEFT JOIN users rider ON o.rider_id = rider.id
        WHERE {$whereClause}
        ORDER BY o.created_at DESC
        LIMIT {$limit} OFFSET {$offset}
    ";
    $ordersStmt = $db->prepare($ordersQuery);
    $ordersStmt->execute($params);
    $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM orders o WHERE {$whereClause}";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalOrders = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalOrders / $limit);
    
} catch (Exception $e) {
    error_log("Customer orders error: " . $e->getMessage());
    $orders = [];
    $totalOrders = 0;
    $totalPages = 0;
}

// Set page variables
$title = 'My Orders - Time2Eat';
$currentPage = 'orders';
$userRole = 'customer';

// Start output buffering to capture the view content
ob_start();

// Include the customer orders view
include BASE_PATH . '/src/views/customer/orders.php';

// Get the content
$content = ob_get_clean();

// Include layout
require_once BASE_PATH . '/src/views/layouts/dashboard.php';
?>
