<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../services/OrderCoordinationService.php';
require_once __DIR__ . '/../models/Order.php';

use core\BaseController;
use services\OrderCoordinationService;
use models\Order;

/**
 * Unified Order Controller
 * Handles order management across all dashboards with proper coordination
 */
class UnifiedOrderController extends BaseController
{
    private OrderCoordinationService $coordinationService;
    private Order $orderModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->coordinationService = new OrderCoordinationService();
        $this->orderModel = new Order();
    }
    
    /**
     * Update order status (unified endpoint for all dashboards)
     */
    public function updateStatus(): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $validation = $this->validateRequest([
            'order_id' => 'required|integer',
            'status' => 'required|string',
            'notes' => 'string'
        ]);
        
        if (!$validation['isValid']) {
            $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $data = $validation['data'];
        
        // Verify user has permission to update this order
        if (!$this->canUpdateOrderStatus($data['order_id'], $user->id, $user->role)) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized to update this order'], 403);
            return;
        }
        
        // Use coordination service to update status
        $result = $this->coordinationService->updateOrderStatus(
            $data['order_id'],
            $data['status'],
            $user->id,
            $data['notes'] ?? ''
        );
        
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * Get unified order details (accessible by all authorized roles)
     */
    public function getOrderDetails(int $orderId): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        
        // Verify user has permission to view this order
        if (!$this->canViewOrder($orderId, $user->id, $user->role)) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized to view this order'], 403);
            return;
        }
        
        $order = $this->orderModel->getOrderDetails($orderId);
        if (!$order) {
            $this->jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
            return;
        }
        
        // Get order items
        $orderItems = $this->orderModel->getOrderItems($orderId);
        
        // Get status history
        $statusHistory = $this->getOrderStatusHistory($orderId);
        
        // Get coordination stats for this order
        $coordinationStats = $this->getOrderCoordinationStats($orderId);
        
        $this->jsonResponse([
            'success' => true,
            'order' => $order,
            'items' => $orderItems,
            'status_history' => $statusHistory,
            'coordination_stats' => $coordinationStats
        ]);
    }
    
    /**
     * Get real-time order tracking data
     */
    public function getOrderTracking(int $orderId): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        
        if (!$this->canViewOrder($orderId, $user->id, $user->role)) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }
        
        try {
            $trackingData = $this->fetchOne("
                SELECT 
                    o.id,
                    o.order_number,
                    o.status,
                    o.created_at,
                    o.estimated_delivery_time,
                    o.delivery_address,
                    r.name as restaurant_name,
                    r.address as restaurant_address,
                    r.latitude as restaurant_lat,
                    r.longitude as restaurant_lng,
                    CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                    rider.phone as rider_phone,
                    rl.latitude as rider_lat,
                    rl.longitude as rider_lng,
                    rl.updated_at as rider_location_updated
                FROM orders o
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users rider ON o.rider_id = rider.id
                LEFT JOIN rider_locations rl ON rider.id = rl.rider_id
                WHERE o.id = ?
            ", [$orderId]);
            
            if (!$trackingData) {
                $this->jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
                return;
            }
            
            // Get delivery progress
            $progress = $this->getDeliveryProgress($trackingData['status']);
            
            $this->jsonResponse([
                'success' => true,
                'tracking' => $trackingData,
                'progress' => $progress,
                'estimated_time' => $this->calculateEstimatedTime($trackingData)
            ]);
            
        } catch (\Exception $e) {
            error_log("Error getting order tracking: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to get tracking data'], 500);
        }
    }
    
    /**
     * Get orders for specific dashboard with proper filtering
     */
    public function getOrdersForDashboard(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $role = $user->role;
        
        // Get filter parameters
        $status = $_GET['status'] ?? '';
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        $offset = (int)($_GET['offset'] ?? 0);
        $search = $_GET['search'] ?? '';
        
        try {
            $orders = [];
            $totalCount = 0;
            
            switch ($role) {
                case 'admin':
                    [$orders, $totalCount] = $this->getAdminOrders($status, $limit, $offset, $search);
                    break;
                    
                case 'customer':
                    [$orders, $totalCount] = $this->getCustomerOrders($user->id, $status, $limit, $offset);
                    break;
                    
                case 'vendor':
                    $restaurant = $this->getVendorRestaurant($user->id);
                    if ($restaurant) {
                        [$orders, $totalCount] = $this->getVendorOrders($restaurant['id'], $status, $limit, $offset);
                    }
                    break;
                    
                case 'rider':
                    [$orders, $totalCount] = $this->getRiderOrders($user->id, $status, $limit, $offset);
                    break;
            }
            
            $this->jsonResponse([
                'success' => true,
                'orders' => $orders,
                'total_count' => $totalCount,
                'has_more' => ($offset + $limit) < $totalCount
            ]);
            
        } catch (\Exception $e) {
            error_log("Error getting dashboard orders: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to get orders'], 500);
        }
    }
    
    /**
     * Get platform-wide order statistics (admin only)
     */
    public function getPlatformStats(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');
        
        try {
            $stats = $this->coordinationService->getCoordinationStats();
            
            // Add additional platform metrics
            $platformMetrics = $this->fetchOne("
                SELECT 
                    COUNT(DISTINCT customer_id) as active_customers,
                    COUNT(DISTINCT restaurant_id) as active_restaurants,
                    COUNT(DISTINCT rider_id) as active_riders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value
                FROM orders 
                WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAYS)
            ");
            
            $this->jsonResponse([
                'success' => true,
                'coordination_stats' => $stats,
                'platform_metrics' => $platformMetrics
            ]);
            
        } catch (\Exception $e) {
            error_log("Error getting platform stats: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to get statistics'], 500);
        }
    }
    
    /**
     * Cancel order with proper coordination
     */
    public function cancelOrder(int $orderId): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->getCurrentUser();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $reason = $input['reason'] ?? 'Cancelled by user';
        
        // Verify user can cancel this order
        if (!$this->canCancelOrder($orderId, $user->id, $user->role)) {
            $this->jsonResponse(['success' => false, 'message' => 'Cannot cancel this order'], 403);
            return;
        }
        
        // Use coordination service to cancel
        $result = $this->coordinationService->updateOrderStatus(
            $orderId,
            'cancelled',
            $user->id,
            $reason
        );
        
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * Check if user can update order status
     */
    private function canUpdateOrderStatus(int $orderId, int $userId, string $role): bool
    {
        $order = $this->orderModel->getOrderDetails($orderId);
        if (!$order) return false;
        
        switch ($role) {
            case 'admin':
                return true; // Admin can update any order
                
            case 'vendor':
                $restaurant = $this->getVendorRestaurant($userId);
                return $restaurant && $order['restaurant_id'] == $restaurant['id'];
                
            case 'rider':
                return $order['rider_id'] == $userId;
                
            case 'customer':
                return $order['customer_id'] == $userId && 
                       in_array($order['status'], ['pending', 'confirmed']); // Can only cancel early
                
            default:
                return false;
        }
    }
    
    /**
     * Check if user can view order
     */
    private function canViewOrder(int $orderId, int $userId, string $role): bool
    {
        $order = $this->orderModel->getOrderDetails($orderId);
        if (!$order) return false;
        
        switch ($role) {
            case 'admin':
                return true;
                
            case 'customer':
                return $order['customer_id'] == $userId;
                
            case 'vendor':
                $restaurant = $this->getVendorRestaurant($userId);
                return $restaurant && $order['restaurant_id'] == $restaurant['id'];
                
            case 'rider':
                return $order['rider_id'] == $userId;
                
            default:
                return false;
        }
    }
    
    /**
     * Check if user can cancel order
     */
    private function canCancelOrder(int $orderId, int $userId, string $role): bool
    {
        $order = $this->orderModel->getOrderDetails($orderId);
        if (!$order) return false;

        // Check if order is in cancellable state
        $cancellableStatuses = ['pending', 'confirmed', 'preparing'];
        if (!in_array($order['status'], $cancellableStatuses) && $role !== 'admin') {
            return false;
        }

        return $this->canViewOrder($orderId, $userId, $role);
    }

    /**
     * Get vendor's restaurant
     */
    private function getVendorRestaurant(int $vendorId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM restaurants WHERE user_id = ? AND status = 'active'",
            [$vendorId]
        );
    }

    /**
     * Get admin orders with comprehensive data
     */
    private function getAdminOrders(string $status, int $limit, int $offset, string $search): array
    {
        $whereConditions = [];
        $params = [];

        if ($status) {
            $whereConditions[] = "o.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $whereConditions[] = "(o.order_number LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR r.name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $orders = $this->fetchAll("
            SELECT
                o.*,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                u.email as customer_email,
                r.name as restaurant_name,
                CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN users u ON o.customer_id = u.id
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN users rider ON o.rider_id = rider.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            {$whereClause}
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($params, [$limit, $offset]));

        $totalCount = $this->fetchOne("
            SELECT COUNT(DISTINCT o.id) as count
            FROM orders o
            LEFT JOIN users u ON o.customer_id = u.id
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            {$whereClause}
        ", $params)['count'] ?? 0;

        return [$orders, (int)$totalCount];
    }

    /**
     * Get customer orders
     */
    private function getCustomerOrders(int $customerId, string $status, int $limit, int $offset): array
    {
        $whereClause = "WHERE o.customer_id = ?";
        $params = [$customerId];

        if ($status) {
            $whereClause .= " AND o.status = ?";
            $params[] = $status;
        }

        $orders = $this->fetchAll("
            SELECT
                o.*,
                r.name as restaurant_name,
                r.image as restaurant_image,
                CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN users rider ON o.rider_id = rider.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            {$whereClause}
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($params, [$limit, $offset]));

        $totalCount = $this->fetchOne("
            SELECT COUNT(*) as count FROM orders o {$whereClause}
        ", $params)['count'] ?? 0;

        return [$orders, (int)$totalCount];
    }

    /**
     * Get vendor orders
     */
    private function getVendorOrders(int $restaurantId, string $status, int $limit, int $offset): array
    {
        $whereClause = "WHERE o.restaurant_id = ?";
        $params = [$restaurantId];

        if ($status) {
            $whereClause .= " AND o.status = ?";
            $params[] = $status;
        }

        $orders = $this->fetchAll("
            SELECT
                o.*,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                u.phone as customer_phone,
                CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN users u ON o.customer_id = u.id
            LEFT JOIN users rider ON o.rider_id = rider.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            {$whereClause}
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($params, [$limit, $offset]));

        $totalCount = $this->fetchOne("
            SELECT COUNT(*) as count FROM orders o {$whereClause}
        ", $params)['count'] ?? 0;

        return [$orders, (int)$totalCount];
    }

    /**
     * Get rider orders
     */
    private function getRiderOrders(int $riderId, string $status, int $limit, int $offset): array
    {
        $whereClause = "WHERE (o.rider_id = ? OR o.status = 'ready')";
        $params = [$riderId];

        if ($status) {
            $whereClause .= " AND o.status = ?";
            $params[] = $status;
        }

        $orders = $this->fetchAll("
            SELECT
                o.*,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                u.phone as customer_phone,
                r.name as restaurant_name,
                r.address as restaurant_address,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN users u ON o.customer_id = u.id
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            {$whereClause}
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($params, [$limit, $offset]));

        $totalCount = $this->fetchOne("
            SELECT COUNT(*) as count FROM orders o {$whereClause}
        ", $params)['count'] ?? 0;

        return [$orders, (int)$totalCount];
    }

    /**
     * Get order status history
     */
    private function getOrderStatusHistory(int $orderId): array
    {
        return $this->fetchAll("
            SELECT
                osh.*,
                CONCAT(u.first_name, ' ', u.last_name) as changed_by_name,
                u.role as changed_by_role
            FROM order_status_history osh
            LEFT JOIN users u ON osh.changed_by = u.id
            WHERE osh.order_id = ?
            ORDER BY osh.created_at ASC
        ", [$orderId]);
    }

    /**
     * Get order coordination stats
     */
    private function getOrderCoordinationStats(int $orderId): array
    {
        $order = $this->orderModel->getOrderDetails($orderId);
        if (!$order) return [];

        $stats = [];

        // Calculate time spent in each status
        $statusHistory = $this->getOrderStatusHistory($orderId);
        $timeInStatus = [];

        for ($i = 0; $i < count($statusHistory); $i++) {
            $current = $statusHistory[$i];
            $next = $statusHistory[$i + 1] ?? null;

            $startTime = strtotime($current['created_at']);
            $endTime = $next ? strtotime($next['created_at']) : time();

            $timeInStatus[$current['new_status']] = $endTime - $startTime;
        }

        $stats['time_in_status'] = $timeInStatus;
        $stats['total_processing_time'] = array_sum($timeInStatus);

        return $stats;
    }

    /**
     * Get delivery progress percentage
     */
    private function getDeliveryProgress(string $status): array
    {
        $progressMap = [
            'pending' => ['percentage' => 10, 'step' => 1, 'total_steps' => 7],
            'confirmed' => ['percentage' => 20, 'step' => 2, 'total_steps' => 7],
            'preparing' => ['percentage' => 40, 'step' => 3, 'total_steps' => 7],
            'ready' => ['percentage' => 60, 'step' => 4, 'total_steps' => 7],
            'picked_up' => ['percentage' => 75, 'step' => 5, 'total_steps' => 7],
            'on_the_way' => ['percentage' => 90, 'step' => 6, 'total_steps' => 7],
            'delivered' => ['percentage' => 100, 'step' => 7, 'total_steps' => 7],
            'cancelled' => ['percentage' => 0, 'step' => 0, 'total_steps' => 7],
        ];

        return $progressMap[$status] ?? ['percentage' => 0, 'step' => 0, 'total_steps' => 7];
    }

    /**
     * Calculate estimated delivery time
     */
    private function calculateEstimatedTime(array $trackingData): ?string
    {
        if ($trackingData['status'] === 'delivered') {
            return null;
        }

        if ($trackingData['estimated_delivery_time']) {
            $estimatedTime = strtotime($trackingData['estimated_delivery_time']);
            $now = time();

            if ($estimatedTime > $now) {
                $minutes = ceil(($estimatedTime - $now) / 60);
                return "{$minutes} minutes";
            } else {
                return "Overdue";
            }
        }

        return null;
    }

    /**
     * Get order flow status for dashboard
     */
    public function getOrderFlowStatus(): void
    {
        $this->requireAuth();

        $user = $this->getCurrentUser();

        try {
            $whereClause = '';
            $params = [];

            // Filter based on user role
            switch ($user->role) {
                case 'vendor':
                    $restaurant = $this->getVendorRestaurant($user->id);
                    if (!$restaurant) {
                        $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found'], 404);
                        return;
                    }
                    $whereClause = 'WHERE restaurant_id = ?';
                    $params[] = $restaurant['id'];
                    break;

                case 'rider':
                    $whereClause = 'WHERE (rider_id = ? OR status = "ready")';
                    $params[] = $user->id;
                    break;

                case 'customer':
                    $whereClause = 'WHERE customer_id = ?';
                    $params[] = $user->id;
                    break;

                case 'admin':
                    // No filter for admin - see all orders
                    break;
            }

            $flowStatus = $this->fetchOne("
                SELECT
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
                    SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready,
                    SUM(CASE WHEN status = 'picked_up' THEN 1 ELSE 0 END) as picked_up,
                    SUM(CASE WHEN status = 'on_the_way' THEN 1 ELSE 0 END) as on_the_way,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered
                FROM orders
                {$whereClause}
                AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
            ", $params);

            $this->jsonResponse([
                'success' => true,
                'flow_status' => $flowStatus ?: []
            ]);

        } catch (\Exception $e) {
            error_log("Error getting flow status: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to get flow status'], 500);
        }
    }

    /**
     * Get live updates for real-time dashboard
     */
    public function getLiveUpdates(): void
    {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $lastUpdate = $_GET['last_update'] ?? date('Y-m-d H:i:s', strtotime('-1 minute'));

        try {
            $whereClause = 'WHERE o.updated_at > ?';
            $params = [$lastUpdate];

            // Filter based on user role
            switch ($user->role) {
                case 'vendor':
                    $restaurant = $this->getVendorRestaurant($user->id);
                    if (!$restaurant) {
                        $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found'], 404);
                        return;
                    }
                    $whereClause .= ' AND o.restaurant_id = ?';
                    $params[] = $restaurant['id'];
                    break;

                case 'rider':
                    $whereClause .= ' AND (o.rider_id = ? OR o.status = "ready")';
                    $params[] = $user->id;
                    break;

                case 'customer':
                    $whereClause .= ' AND o.customer_id = ?';
                    $params[] = $user->id;
                    break;
            }

            $updates = $this->fetchAll("
                SELECT
                    o.id,
                    o.order_number,
                    o.status,
                    o.updated_at,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    r.name as restaurant_name
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                {$whereClause}
                ORDER BY o.updated_at DESC
                LIMIT 50
            ", $params);

            $this->jsonResponse([
                'success' => true,
                'updates' => $updates,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            error_log("Error getting live updates: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to get live updates'], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $validation = $this->validateRequest([
            'notification_id' => 'required|integer'
        ]);

        if (!$validation['isValid']) {
            $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }

        $user = $this->getCurrentUser();
        $notificationId = $validation['data']['notification_id'];

        try {
            $success = $this->execute(
                "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?",
                [$notificationId, $user->id]
            );

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Notification not found'], 404);
            }

        } catch (\Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to mark notification as read'], 500);
        }
    }
}
