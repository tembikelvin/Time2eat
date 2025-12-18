<?php

declare(strict_types=1);

namespace services;

require_once __DIR__ . '/../traits/DatabaseTrait.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/NotificationService.php';

use traits\DatabaseTrait;
use models\Order;
use models\Message;
use services\NotificationService;

/**
 * Centralized Order Coordination Service
 * Manages order workflow across all dashboards and ensures proper communication
 */
class OrderCoordinationService
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    private Order $orderModel;
    private Message $messageModel;
    private NotificationService $notificationService;
    
    // Order status flow definition
    private const ORDER_STATUS_FLOW = [
        'pending' => ['next' => ['confirmed', 'preparing', 'cancelled'], 'role' => 'vendor'],
        'confirmed' => ['next' => ['preparing', 'cancelled'], 'role' => 'vendor'],
        'preparing' => ['next' => ['ready', 'cancelled'], 'role' => 'vendor'],
        'ready' => ['next' => ['assigned', 'cancelled'], 'role' => 'rider'],
        'assigned' => ['next' => ['picked_up', 'cancelled'], 'role' => 'rider'],
        'picked_up' => ['next' => ['on_the_way'], 'role' => 'rider'],
        'on_the_way' => ['next' => ['delivered', 'failed'], 'role' => 'rider'],
        'delivered' => ['next' => [], 'role' => 'completed'],
        'cancelled' => ['next' => [], 'role' => 'completed'],
        'failed' => ['next' => ['cancelled'], 'role' => 'admin']
    ];
    
    public function __construct()
    {
        $this->orderModel = new Order();
        $this->messageModel = new Message();
        $this->notificationService = new NotificationService();
    }
    
    /**
     * Update order status with proper coordination across all dashboards
     */
    public function updateOrderStatus(int $orderId, string $newStatus, int $updatedBy, string $notes = ''): array
    {
        try {
            $this->beginTransaction();
            
            // Get current order details
            $order = $this->orderModel->getOrderDetails($orderId);
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            $oldStatus = $order['status'];
            
            // Validate status transition
            if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
                throw new \Exception("Invalid status transition from {$oldStatus} to {$newStatus}");
            }
            
            // Update order status in database
            $success = $this->orderModel->updateOrderStatus($orderId, $newStatus, [
                'admin_notes' => $notes,
                'updated_by' => $updatedBy
            ]);
            
            if (!$success) {
                throw new \Exception('Failed to update order status');
            }
            
            // Log status change
            $this->logOrderStatusChange($orderId, $oldStatus, $newStatus, $updatedBy, $notes);
            
            // Handle status-specific actions
            $this->handleStatusSpecificActions($order, $newStatus, $updatedBy);
            
            // Send notifications to all relevant parties
            $this->sendStatusUpdateNotifications($order, $oldStatus, $newStatus);
            
            // Update real-time dashboard data
            $this->updateDashboardMetrics($order, $newStatus);
            
            $this->commit();
            
            return [
                'success' => true,
                'message' => 'Order status updated successfully',
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Order coordination error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'order_id' => $orderId
            ];
        }
    }
    
    /**
     * Validate if status transition is allowed
     */
    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        if (!isset(self::ORDER_STATUS_FLOW[$currentStatus])) {
            return false;
        }
        
        $allowedNext = self::ORDER_STATUS_FLOW[$currentStatus]['next'];
        return in_array($newStatus, $allowedNext) || $newStatus === 'cancelled'; // Admin can always cancel
    }
    
    /**
     * Handle status-specific actions
     */
    private function handleStatusSpecificActions(array $order, string $newStatus, int $updatedBy): void
    {
        switch ($newStatus) {
            case 'confirmed':
                $this->handleOrderConfirmation($order);
                break;

            case 'ready':
                $this->handleOrderReady($order);
                break;

            case 'assigned':
                $this->handleOrderAssigned($order);
                break;

            case 'picked_up':
                $this->handleOrderPickedUp($order);
                break;

            case 'delivered':
                $this->handleOrderDelivered($order);
                break;

            case 'cancelled':
                $this->handleOrderCancelled($order, $updatedBy);
                break;
        }
    }
    
    /**
     * Handle order confirmation
     */
    private function handleOrderConfirmation(array $order): void
    {
        // Calculate estimated preparation time
        $restaurant = $this->fetchOne(
            "SELECT preparation_time, delivery_time FROM restaurants WHERE id = ?",
            [$order['restaurant_id']]
        );
        
        if ($restaurant) {
            $estimatedTime = date('Y-m-d H:i:s', strtotime('+' . ($restaurant['preparation_time'] ?? 30) . ' minutes'));
            $this->execute(
                "UPDATE orders SET estimated_preparation_time = ? WHERE id = ?",
                [$estimatedTime, $order['id']]
            );
        }
        
        // Find available riders in the area (for future assignment)
        $this->findAvailableRiders($order);
    }
    
    /**
     * Handle order ready for pickup
     *
     * NOTE: Riders are NOT automatically assigned. Orders with status 'ready'
     * become visible to all available riders who can manually choose to accept them.
     */
    private function handleOrderReady(array $order): void
    {
        // DO NOT auto-assign riders - riders will manually accept orders
        // Orders with status 'ready' and rider_id = NULL are available for riders to accept

        // Calculate estimated delivery time
        $restaurant = $this->fetchOne(
            "SELECT delivery_time FROM restaurants WHERE id = ?",
            [$order['restaurant_id']]
        );

        if ($restaurant) {
            $estimatedTime = date('Y-m-d H:i:s', strtotime('+' . ($restaurant['delivery_time'] ?? 30) . ' minutes'));
            $this->execute(
                "UPDATE orders SET estimated_delivery_time = ? WHERE id = ?",
                [$estimatedTime, $order['id']]
            );
        }

        // Notify all available riders that a new order is ready for pickup
        $this->notifyAvailableRiders($order);
    }

    /**
     * Handle order assigned to rider
     * This is called when a rider manually accepts an order
     */
    private function handleOrderAssigned(array $order): void
    {
        if (!$order['rider_id']) {
            error_log("Order #{$order['order_number']} marked as assigned but has no rider_id");
            return;
        }

        // Notify customer that a rider has been assigned
        $this->notificationService->sendOrderStatusUpdate(
            ['id' => $order['customer_id']],
            $order,
            'ready',
            'assigned'
        );

        // Notify vendor that order has been assigned to a rider
        $this->notifyVendor($order, 'assigned');
    }

    /**
     * Handle order picked up
     */
    private function handleOrderPickedUp(array $order): void
    {
        // Update pickup timestamp
        $this->execute(
            "UPDATE orders SET picked_up_at = NOW() WHERE id = ?",
            [$order['id']]
        );
        
        // Start delivery tracking
        $this->startDeliveryTracking($order['id']);
    }
    
    /**
     * Handle order delivered
     */
    private function handleOrderDelivered(array $order): void
    {
        // Update delivery timestamp
        $this->execute(
            "UPDATE orders SET delivered_at = NOW(), payment_status = 'completed' WHERE id = ?",
            [$order['id']]
        );
        
        // Process affiliate commission
        $this->orderModel->processAffiliateCommission($order['id']);
        
        // Process restaurant commission
        $this->processRestaurantCommission($order);
        
        // Update rider earnings
        $this->updateRiderEarnings($order);
        
        // Request customer review
        $this->requestCustomerReview($order);
    }
    
    /**
     * Handle order cancellation
     */
    private function handleOrderCancelled(array $order, int $cancelledBy): void
    {
        // Process refund if payment was made
        if ($order['payment_status'] === 'completed') {
            $this->processOrderRefund($order, $cancelledBy);
        }
        
        // Release assigned rider
        if ($order['rider_id']) {
            $this->execute(
                "UPDATE orders SET rider_id = NULL WHERE id = ?",
                [$order['id']]
            );
        }
        
        // Update cancellation timestamp
        $this->execute(
            "UPDATE orders SET cancelled_at = NOW() WHERE id = ?",
            [$order['id']]
        );
    }
    
    /**
     * Send status update notifications to all relevant parties
     */
    private function sendStatusUpdateNotifications(array $order, string $oldStatus, string $newStatus): void
    {
        $statusMessages = [
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'preparing' => 'Your order is now being prepared by the restaurant.',
            'ready' => 'Your order is ready for pickup. A rider will collect it soon.',
            'assigned' => 'A rider has accepted your order and will pick it up shortly.',
            'picked_up' => 'Your order has been picked up and is on the way to you.',
            'on_the_way' => 'Your order is on the way! Expected delivery in 15-20 minutes.',
            'delivered' => 'Your order has been delivered. Enjoy your meal!',
            'cancelled' => 'Your order has been cancelled. You will receive a full refund.'
        ];
        
        $message = $statusMessages[$newStatus] ?? 'Your order status has been updated.';
        
        // Notify customer
        $this->notificationService->sendOrderStatusUpdate(
            ['id' => $order['customer_id']],
            $order,
            $oldStatus,
            $newStatus
        );
        
        // Notify vendor for certain statuses
        if (in_array($newStatus, ['picked_up', 'delivered', 'cancelled'])) {
            $this->notifyVendor($order, $newStatus);
        }
        
        // Notify rider for certain statuses
        if ($order['rider_id'] && in_array($newStatus, ['ready', 'cancelled'])) {
            $this->notifyRider($order, $newStatus);
        }
        
        // Notify admin for critical statuses
        if (in_array($newStatus, ['cancelled', 'failed'])) {
            $this->notifyAdmin($order, $newStatus);
        }
    }
    
    /**
     * Log order status change for audit trail
     */
    private function logOrderStatusChange(int $orderId, string $oldStatus, string $newStatus, int $updatedBy, string $notes): void
    {
        $this->execute("
            INSERT INTO order_status_history (
                order_id, old_status, new_status, changed_by, notes, created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ", [$orderId, $oldStatus, $newStatus, $updatedBy, $notes]);
    }
    
    /**
     * Update dashboard metrics in real-time
     */
    private function updateDashboardMetrics(array $order, string $newStatus): void
    {
        // This would integrate with a real-time system like WebSockets
        // For now, we'll update cached metrics
        
        $cacheKey = "dashboard_metrics_" . date('Y-m-d');
        $this->invalidateCache($cacheKey);
        
        // Update restaurant metrics
        $this->updateRestaurantMetrics($order['restaurant_id'], $newStatus);
        
        // Update rider metrics if assigned
        if ($order['rider_id']) {
            $this->updateRiderMetrics($order['rider_id'], $newStatus);
        }
    }
    
    /**
     * Get order coordination statistics for admin dashboard
     */
    public function getCoordinationStats(): array
    {
        try {
            $stats = $this->fetchOne("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status IN ('confirmed', 'preparing') THEN 1 ELSE 0 END) as active_orders,
                    SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready_orders,
                    SUM(CASE WHEN status IN ('picked_up', 'on_the_way') THEN 1 ELSE 0 END) as in_transit_orders,
                    SUM(CASE WHEN status = 'delivered' AND DATE(delivered_at) = CURDATE() THEN 1 ELSE 0 END) as delivered_today,
                    SUM(CASE WHEN status = 'cancelled' AND DATE(cancelled_at) = CURDATE() THEN 1 ELSE 0 END) as cancelled_today,
                    AVG(CASE WHEN status = 'delivered' THEN TIMESTAMPDIFF(MINUTE, created_at, delivered_at) END) as avg_delivery_time
                FROM orders 
                WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
            ");
            
            return $stats ?: [];
            
        } catch (\Exception $e) {
            error_log("Error getting coordination stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find available riders in the area
     */
    private function findAvailableRiders(array $order): array
    {
        return $this->fetchAll("
            SELECT u.id, u.first_name, u.last_name, u.phone,
                   rs.latitude, rs.longitude
            FROM users u
            JOIN rider_schedules rs ON u.id = rs.rider_id
            WHERE u.role = 'rider'
            AND u.status = 'active'
            AND rs.is_available = 1
            AND rs.day = DAYNAME(NOW())
            AND TIME(NOW()) BETWEEN rs.start_time AND rs.end_time
            ORDER BY (
                6371 * acos(
                    cos(radians(?)) * cos(radians(rs.latitude)) *
                    cos(radians(rs.longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(rs.latitude))
                )
            ) ASC
            LIMIT 10
        ", [
            $order['restaurant_latitude'] ?? 0,
            $order['restaurant_longitude'] ?? 0,
            $order['restaurant_latitude'] ?? 0
        ]);
    }

    /**
     * Assign rider to order
     */
    private function assignRiderToOrder(int $orderId): ?int
    {
        $order = $this->orderModel->getOrderDetails($orderId);
        if (!$order) return null;

        $availableRiders = $this->findAvailableRiders($order);

        foreach ($availableRiders as $rider) {
            // Check if rider is not already assigned to another order
            $activeDelivery = $this->fetchOne(
                "SELECT id FROM orders WHERE rider_id = ? AND status IN ('picked_up', 'on_the_way')",
                [$rider['id']]
            );

            if (!$activeDelivery) {
                return (int)$rider['id'];
            }
        }

        return null;
    }

    /**
     * Start delivery tracking
     */
    private function startDeliveryTracking(int $orderId): void
    {
        // Create delivery tracking record
        $this->execute("
            INSERT INTO delivery_tracking (
                order_id, status, started_at, created_at
            ) VALUES (?, 'in_transit', NOW(), NOW())
        ", [$orderId]);
    }

    /**
     * Process restaurant commission
     */
    private function processRestaurantCommission(array $order): void
    {
        // Get restaurant commission rate
        $restaurant = $this->fetchOne(
            "SELECT commission_rate FROM restaurants WHERE id = ?",
            [$order['restaurant_id']]
        );

        $commissionRate = $restaurant['commission_rate'] ?? 0.10; // Default 10%
        $commissionAmount = $order['total_amount'] * $commissionRate;

        // Record commission
        $this->execute("
            INSERT INTO restaurant_commissions (
                order_id, restaurant_id, commission_rate, commission_amount, created_at
            ) VALUES (?, ?, ?, ?, NOW())
        ", [$order['id'], $order['restaurant_id'], $commissionRate, $commissionAmount]);
    }

    /**
     * Update rider earnings (Standardized Calculation)
     */
    private function updateRiderEarnings(array $order): void
    {
        if (!$order['rider_id']) return;

        $distance = $order['delivery_distance'] ?? 5;

        // Standardized rider earnings calculation
        $baseEarnings = 350; // Fixed base earnings
        $distanceBonus = 0;

        if ($distance > 3) {
            $additionalDistance = $distance - 3;
            $distanceBonus = $additionalDistance * 70; // 70 XAF per km beyond 3km
        }

        $totalEarning = max($baseEarnings + $distanceBonus, 350); // Minimum 350 XAF

        // Record rider earning
        $this->execute("
            INSERT INTO rider_earnings (
                order_id, rider_id, base_fee, distance_fee, total_earning, created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ", [$order['id'], $order['rider_id'], $baseEarnings, $distanceBonus, $totalEarning]);

        // Update rider balance
        $this->execute(
            "UPDATE users SET balance = balance + ? WHERE id = ?",
            [$totalEarning, $order['rider_id']]
        );
    }

    /**
     * Request customer review
     */
    private function requestCustomerReview(array $order): void
    {
        // Send review request notification
        $message = "Your order has been delivered! Please take a moment to rate your experience.";

        $this->notificationService->sendReviewRequest(
            ['id' => $order['customer_id']],
            $order
        );
    }

    /**
     * Process order refund
     */
    private function processOrderRefund(array $order, int $processedBy): void
    {
        $refundAmount = $order['total_amount'];

        // Record refund
        $this->execute("
            INSERT INTO order_refunds (
                order_id, customer_id, refund_amount, processed_by, status, created_at
            ) VALUES (?, ?, ?, ?, 'processed', NOW())
        ", [$order['id'], $order['customer_id'], $refundAmount, $processedBy]);

        // Update customer balance
        $this->execute(
            "UPDATE users SET balance = balance + ? WHERE id = ?",
            [$refundAmount, $order['customer_id']]
        );
    }

    /**
     * Notify all available riders about a new order ready for pickup
     */
    private function notifyAvailableRiders(array $order): void
    {
        // Get all available riders
        $availableRiders = $this->fetchAll("
            SELECT id, first_name, last_name
            FROM users
            WHERE role = 'rider'
            AND status = 'active'
            AND is_available = 1
        ");

        if (empty($availableRiders)) {
            error_log("No available riders to notify for order #{$order['order_number']}");
            return;
        }

        // Notify each available rider
        foreach ($availableRiders as $rider) {
            try {
                $this->notificationService->sendRiderNotification(
                    $rider['id'],
                    "New order available! Order #{$order['order_number']} from {$order['restaurant_name']} is ready for pickup."
                );
            } catch (\Exception $e) {
                error_log("Failed to notify rider {$rider['id']}: " . $e->getMessage());
            }
        }
    }

    /**
     * Notify vendor about order status
     */
    private function notifyVendor(array $order, string $status): void
    {
        $messages = [
            'assigned' => 'A rider has accepted the order and will pick it up soon.',
            'picked_up' => 'Order has been picked up by the rider.',
            'delivered' => 'Order has been successfully delivered to the customer.',
            'cancelled' => 'Order has been cancelled.'
        ];

        $message = $messages[$status] ?? 'Order status updated.';

        // Send notification to vendor
        $this->notificationService->sendVendorNotification(
            $order['restaurant_id'],
            "Order #{$order['order_number']} - {$message}"
        );
    }

    /**
     * Notify rider about order status
     */
    private function notifyRider(array $order, string $status): void
    {
        if (!$order['rider_id']) return;

        $messages = [
            'ready' => 'New order ready for pickup!',
            'cancelled' => 'Order has been cancelled.'
        ];

        $message = $messages[$status] ?? 'Order status updated.';

        $this->notificationService->sendRiderNotification(
            $order['rider_id'],
            "Order #{$order['order_number']} - {$message}"
        );
    }

    /**
     * Notify admin about critical order events
     */
    private function notifyAdmin(array $order, string $status): void
    {
        $messages = [
            'cancelled' => 'Order has been cancelled and requires attention.',
            'failed' => 'Order delivery failed and requires intervention.'
        ];

        $message = $messages[$status] ?? 'Order requires admin attention.';

        $this->notificationService->sendAdminAlert(
            "Order #{$order['order_number']} - {$message}",
            $order
        );
    }

    /**
     * Update restaurant metrics
     */
    private function updateRestaurantMetrics(int $restaurantId, string $status): void
    {
        $cacheKey = "restaurant_metrics_{$restaurantId}";
        $this->invalidateCache($cacheKey);
    }

    /**
     * Update rider metrics
     */
    private function updateRiderMetrics(int $riderId, string $status): void
    {
        $cacheKey = "rider_metrics_{$riderId}";
        $this->invalidateCache($cacheKey);
    }

    /**
     * Invalidate cache
     */
    private function invalidateCache(string $key): void
    {
        // Implementation depends on caching system used
        // For now, just log the cache invalidation
        error_log("Cache invalidated: {$key}");
    }
}
