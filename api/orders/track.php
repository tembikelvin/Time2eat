<?php
/**
 * Real-time Order Tracking API Endpoint
 * Provides live order status and tracking information
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session and load dependencies
session_start();
require_once __DIR__ . '/../../bootstrap/app.php';
require_once __DIR__ . '/../../src/models/Order.php';
require_once __DIR__ . '/../../src/services/OrderStatusValidationService.php';

use models\Order;
use services\OrderStatusValidationService;

try {
    $orderId = (int)($_GET['order_id'] ?? 0);
    $userRole = $_SESSION['user_role'] ?? '';
    $userId = $_SESSION['user_id'] ?? 0;
    
    if (!$orderId || !$userRole || !$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters',
            'error_code' => 'MISSING_PARAMETERS'
        ]);
        exit;
    }
    
    $orderModel = new Order();
    $validationService = new OrderStatusValidationService();
    
    // Get order details
    $order = $orderModel->getOrderDetails($orderId);
    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found',
            'error_code' => 'ORDER_NOT_FOUND'
        ]);
        exit;
    }
    
    // Validate user has access to this order
    $validation = $validationService->canUpdateStatus($order['status'], $order['status'], $userRole, $orderId);
    if (!$validation['valid']) {
        echo json_encode([
            'success' => false,
            'message' => 'Access denied to this order',
            'error_code' => 'ACCESS_DENIED'
        ]);
        exit;
    }
    
    // Get order status history
    $statusHistory = $orderModel->getOrderStatusHistory($orderId);
    
    // Get rider information if order is being delivered
    $rider = null;
    if (in_array($order['status'], ['picked_up', 'on_the_way']) && $order['rider_id']) {
        $rider = $orderModel->getOrderRider($orderId);
    }
    
    // Get available status transitions for current user
    $availableTransitions = $validationService->getAvailableTransitions($order['status'], $userRole);
    
    // Calculate estimated delivery time
    $estimatedDeliveryTime = $this->calculateEstimatedDeliveryTime($order);
    
    // Get order items
    $orderItems = $orderModel->getOrderItems($orderId);
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'order' => [
                'id' => $order['id'],
                'order_number' => $order['order_number'],
                'status' => $order['status'],
                'status_label' => $this->getStatusLabel($order['status']),
                'total_amount' => $order['total_amount'],
                'delivery_fee' => $order['delivery_fee'],
                'delivery_address' => $order['delivery_address'],
                'delivery_instructions' => $order['delivery_instructions'],
                'created_at' => $order['created_at'],
                'updated_at' => $order['updated_at'],
                'estimated_delivery_time' => $estimatedDeliveryTime
            ],
            'restaurant' => [
                'id' => $order['restaurant_id'],
                'name' => $order['restaurant_name'],
                'address' => $order['restaurant_address'],
                'phone' => $order['restaurant_phone'],
                'image' => $order['restaurant_image']
            ],
            'customer' => [
                'id' => $order['customer_id'],
                'name' => $order['customer_name'],
                'phone' => $order['customer_phone']
            ],
            'rider' => $rider,
            'items' => $orderItems,
            'status_history' => $statusHistory,
            'available_transitions' => $availableTransitions,
            'can_update_status' => !empty($availableTransitions)
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Order tracking error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching order details',
        'error_code' => 'INTERNAL_ERROR'
    ]);
}

/**
 * Calculate estimated delivery time
 */
function calculateEstimatedDeliveryTime($order) {
    $baseTime = strtotime($order['created_at']);
    $status = $order['status'];
    
    // Add time based on current status
    switch ($status) {
        case 'pending':
        case 'confirmed':
            return date('Y-m-d H:i:s', $baseTime + (30 * 60)); // 30 minutes
        case 'preparing':
            return date('Y-m-d H:i:s', $baseTime + (45 * 60)); // 45 minutes
        case 'ready':
            return date('Y-m-d H:i:s', $baseTime + (60 * 60)); // 60 minutes
        case 'picked_up':
            return date('Y-m-d H:i:s', $baseTime + (75 * 60)); // 75 minutes
        case 'on_the_way':
            return date('Y-m-d H:i:s', $baseTime + (90 * 60)); // 90 minutes
        default:
            return null;
    }
}

/**
 * Get status label
 */
function getStatusLabel($status) {
    $labels = [
        'pending' => 'Pending Confirmation',
        'confirmed' => 'Confirmed',
        'preparing' => 'Preparing',
        'ready' => 'Ready for Pickup',
        'picked_up' => 'Picked Up',
        'on_the_way' => 'On the Way',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled'
    ];
    
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}
