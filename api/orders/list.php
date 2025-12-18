<?php
/**
 * API: List Orders
 * Direct file - no routing complexity
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure this is an API call
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate, private');
header('Pragma: no-cache');
header('Expires: 0');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Load dependencies
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../src/models/Order.php';
    
    $orderModel = new Time2Eat\Models\Order();
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'] ?? $_SESSION['role'] ?? null;
    
    // Get query parameters
    $status = $_GET['status'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Build query based on user role
    $query = "SELECT o.*, 
              u.first_name, u.last_name, u.email,
              r.name as restaurant_name,
              rider.first_name as rider_first_name, rider.last_name as rider_last_name
              FROM orders o
              LEFT JOIN users u ON o.customer_id = u.id
              LEFT JOIN restaurants r ON o.restaurant_id = r.id
              LEFT JOIN users rider ON o.rider_id = rider.id
              WHERE 1=1";
    
    $params = [];
    
    // Filter by role
    switch ($userRole) {
        case 'customer':
            $query .= " AND o.customer_id = ?";
            $params[] = $userId;
            break;
            
        case 'vendor':
            // Get vendor's restaurant
            $restaurant = $orderModel->fetchOne(
                "SELECT id FROM restaurants WHERE user_id = ?",
                [$userId]
            );
            if ($restaurant) {
                $query .= " AND o.restaurant_id = ?";
                $params[] = $restaurant['id'];
            } else {
                // No restaurant found
                echo json_encode(['success' => true, 'orders' => [], 'total' => 0]);
                exit;
            }
            break;
            
        case 'rider':
            $query .= " AND o.rider_id = ?";
            $params[] = $userId;
            break;
            
        case 'admin':
            // Admin sees all orders
            break;
            
        default:
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
    }
    
    // Filter by status if provided
    if ($status) {
        $query .= " AND o.status = ?";
        $params[] = $status;
    }
    
    // Get total count
    $countQuery = str_replace('SELECT o.*,', 'SELECT COUNT(*) as total,', $query);
    $countQuery = preg_replace('/LEFT JOIN.*/', '', $countQuery);
    $totalResult = $orderModel->fetchOne($countQuery, $params);
    $total = $totalResult['total'] ?? 0;
    
    // Add ordering and pagination
    $query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Execute query
    $orders = $orderModel->fetchAll($query, $params);
    
    // Format orders
    $formattedOrders = array_map(function($order) {
        return [
            'id' => (int)$order['id'],
            'order_number' => $order['order_number'],
            'customer' => [
                'name' => $order['first_name'] . ' ' . $order['last_name'],
                'email' => $order['email']
            ],
            'restaurant' => [
                'name' => $order['restaurant_name']
            ],
            'rider' => $order['rider_first_name'] ? [
                'name' => $order['rider_first_name'] . ' ' . $order['rider_last_name']
            ] : null,
            'status' => $order['status'],
            'subtotal' => (float)$order['subtotal'],
            'delivery_fee' => (float)$order['delivery_fee'],
            'total_amount' => (float)$order['total_amount'],
            'payment_method' => $order['payment_method'],
            'delivery_address' => $order['delivery_address'],
            'created_at' => $order['created_at'],
            'updated_at' => $order['updated_at']
        ];
    }, $orders);
    
    // Return response
    echo json_encode([
        'success' => true,
        'orders' => $formattedOrders,
        'pagination' => [
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total
        ]
    ]);
    
} catch (Exception $e) {
    error_log("API Error (orders/list): " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}

