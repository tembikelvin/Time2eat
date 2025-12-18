<?php
/**
 * Order Status Update API Endpoint
 * Handles order status updates with proper validation and role-based permissions
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Start session and load dependencies
session_start();
require_once __DIR__ . '/../../bootstrap/app.php';
require_once __DIR__ . '/../../src/services/OrderStatusValidationService.php';
require_once __DIR__ . '/../../src/models/Order.php';

use services\OrderStatusValidationService;
use models\Order;

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $orderId = (int)($input['order_id'] ?? 0);
    $newStatus = trim($input['status'] ?? '');
    $notes = trim($input['notes'] ?? '');
    $userRole = $_SESSION['user_role'] ?? '';
    $userId = $_SESSION['user_id'] ?? 0;
    
    // Validate required fields
    if (!$orderId || !$newStatus || !$userRole || !$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters',
            'error_code' => 'MISSING_PARAMETERS'
        ]);
        exit;
    }
    
    // Initialize services
    $validationService = new OrderStatusValidationService();
    $orderModel = new Order();
    
    // Get current order status
    $order = $orderModel->getOrderDetails($orderId);
    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found',
            'error_code' => 'ORDER_NOT_FOUND'
        ]);
        exit;
    }
    
    $currentStatus = $order['status'];
    
    // Validate status transition
    $validation = $validationService->canUpdateStatus($currentStatus, $newStatus, $userRole, $orderId);
    
    if (!$validation['valid']) {
        echo json_encode([
            'success' => false,
            'message' => $validation['message'],
            'error_code' => $validation['error_code']
        ]);
        exit;
    }
    
    // Update order status
    $updateData = [
        'status' => $newStatus,
        'updated_by' => $userId,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($notes) {
        $updateData['admin_notes'] = $notes;
    }
    
    // Add role-specific fields
    switch ($userRole) {
        case 'rider':
            if ($newStatus === 'picked_up') {
                $updateData['picked_up_at'] = date('Y-m-d H:i:s');
            } elseif ($newStatus === 'on_the_way') {
                $updateData['out_for_delivery_at'] = date('Y-m-d H:i:s');
            }
            break;
            
        case 'customer':
            if ($newStatus === 'delivered') {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
                $updateData['delivery_confirmed_by_customer'] = 1;
            }
            break;
    }
    
    $success = $orderModel->updateOrderStatus($orderId, $newStatus, $updateData);
    
    if (!$success) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update order status',
            'error_code' => 'UPDATE_FAILED'
        ]);
        exit;
    }
    
    // Log status change
    $this->logOrderStatusChange($orderId, $currentStatus, $newStatus, $userId, $notes);
    
    // Send notifications (if needed)
    $this->sendStatusUpdateNotifications($order, $currentStatus, $newStatus);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'data' => [
            'order_id' => $orderId,
            'old_status' => $currentStatus,
            'new_status' => $newStatus,
            'updated_at' => $updateData['updated_at']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Order status update error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating order status',
        'error_code' => 'INTERNAL_ERROR'
    ]);
}

/**
 * Log order status change
 */
function logOrderStatusChange($orderId, $oldStatus, $newStatus, $userId, $notes = '') {
    try {
        $db = dbConnection();
        $stmt = $db->prepare("
            INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, notes, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$orderId, $oldStatus, $newStatus, $userId, $notes]);
    } catch (Exception $e) {
        error_log("Failed to log status change: " . $e->getMessage());
    }
}

/**
 * Send status update notifications
 */
function sendStatusUpdateNotifications($order, $oldStatus, $newStatus) {
    // This would integrate with your notification system
    // For now, just log the notification
    error_log("Status update notification: Order {$order['id']} changed from {$oldStatus} to {$newStatus}");
}
