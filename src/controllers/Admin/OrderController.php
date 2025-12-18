<?php

namespace Time2Eat\Controllers\Admin;

use controllers\AdminBaseController;

class OrderController extends AdminBaseController
{
    /**
     * Display a listing of orders
     */
    public function index()
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        try {
            $orders = $this->fetchAll("
                SELECT o.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       u.phone as customer_phone,
                       r.name as restaurant_name,
                       CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                       rider.phone as rider_phone
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users rider ON o.rider_id = rider.id
                ORDER BY o.created_at DESC 
                LIMIT 100
            ");

            return $this->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching orders: " . $e->getMessage());
            return $this->json(['error' => 'Failed to fetch orders'], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        try {
            $order = $this->fetchOne("
                SELECT o.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       u.phone as customer_phone,
                       r.name as restaurant_name,
                       r.phone as restaurant_phone,
                       CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                       rider.phone as rider_phone
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users rider ON o.rider_id = rider.id
                WHERE o.id = ?
            ", [$id]);

            if (!$order) {
                return $this->json(['error' => 'Order not found'], 404);
            }

            // Get order items
            $orderItems = $this->fetchAll("
                SELECT oi.*, mi.name as item_name, mi.price as item_price
                FROM order_items oi
                LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id = ?
            ", [$id]);

            $order['items'] = $orderItems;

            return $this->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching order: " . $e->getMessage());
            return $this->json(['error' => 'Failed to fetch order'], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus($id)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        // Get input data
        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        $status = $input['status'] ?? '';
        $notes = trim($input['notes'] ?? '');

        $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'picked_up', 'on_the_way', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return $this->json(['error' => 'Invalid status'], 400);
        }

        try {
            // Check if order exists
            $order = $this->fetchOne("SELECT id, status FROM orders WHERE id = ?", [$id]);
            if (!$order) {
                return $this->json(['error' => 'Order not found'], 404);
            }

            // Update order status
            $this->execute("
                UPDATE orders 
                SET status = ?, admin_notes = ?, updated_at = NOW()
                WHERE id = ?
            ", [$status, $notes, $id]);
            
            // Process affiliate commission if order is delivered
            if ($status === 'delivered') {
                require_once __DIR__ . '/../../models/Order.php';
                $orderModel = new \models\Order();
                $orderModel->processAffiliateCommission($id);
            }

            // Log status change
            $this->execute("
                INSERT INTO order_status_history (order_id, status, changed_by, notes, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ", [$id, $status, $user->id, $notes]);

            return $this->json([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return $this->json(['error' => 'Failed to update order status'], 500);
        }
    }

    /**
     * Assign rider to order
     */
    public function assignRider($id)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        $riderId = $input['rider_id'] ?? '';

        if (empty($riderId)) {
            return $this->json(['error' => 'Rider ID is required'], 400);
        }

        try {
            // Check if order exists
            $order = $this->fetchOne("SELECT id, status FROM orders WHERE id = ?", [$id]);
            if (!$order) {
                return $this->json(['error' => 'Order not found'], 404);
            }

            // Check if rider exists and is active
            $rider = $this->fetchOne("SELECT id, first_name, last_name FROM users WHERE id = ? AND role = 'rider' AND status = 'active'", [$riderId]);
            if (!$rider) {
                return $this->json(['error' => 'Rider not found or inactive'], 404);
            }

            // Assign rider to order
            $this->execute("
                UPDATE orders 
                SET rider_id = ?, updated_at = NOW()
                WHERE id = ?
            ", [$riderId, $id]);

            // Create rider assignment record
            $this->execute("
                INSERT INTO rider_assignments (order_id, rider_id, assigned_by, assigned_at)
                VALUES (?, ?, ?, NOW())
            ", [$id, $riderId, $user->id]);

            // Create delivery record if it doesn't exist
            $existingDelivery = $this->fetchOne("SELECT id FROM deliveries WHERE order_id = ?", [$id]);
            if (!$existingDelivery) {
                // Get order details for delivery record
                $orderDetails = $this->fetchOne("SELECT delivery_address, delivery_fee, restaurant_id FROM orders WHERE id = ?", [$id]);
                
                require_once __DIR__ . '/../../models/Delivery.php';
                $deliveryModel = new \models\Delivery();
                $deliveryModel->setDb($this->getConnection());
                
                $deliveryData = [
                    'order_id' => $id,
                    'rider_id' => $riderId,
                    'pickup_address' => json_encode([
                        'restaurant_id' => $orderDetails['restaurant_id'],
                        'address' => 'Restaurant Address' // Will be updated with actual data
                    ]),
                    'delivery_address' => $orderDetails['delivery_address'], // Already JSON
                    'delivery_fee' => $orderDetails['delivery_fee'] ?? 0,
                    'rider_earnings' => ($orderDetails['delivery_fee'] ?? 0) * 0.8,
                    'platform_commission' => ($orderDetails['delivery_fee'] ?? 0) * 0.2,
                    'status' => 'assigned',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $deliveryId = $deliveryModel->create($deliveryData);
                error_log("Created delivery record for order {$id}, delivery ID: {$deliveryId}");
            }

            return $this->json([
                'success' => true,
                'message' => 'Rider assigned successfully',
                'data' => [
                    'rider_name' => $rider['first_name'] . ' ' . $rider['last_name']
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error assigning rider: " . $e->getMessage());
            return $this->json(['error' => 'Failed to assign rider'], 500);
        }
    }

    /**
     * Process refund for order
     */
    public function refund($id)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        $amount = floatval($input['amount'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if ($amount <= 0) {
            return $this->json(['error' => 'Refund amount must be greater than 0'], 400);
        }

        if (empty($reason)) {
            return $this->json(['error' => 'Refund reason is required'], 400);
        }

        try {
            // Check if order exists
            $order = $this->fetchOne("SELECT id, total_amount, status FROM orders WHERE id = ?", [$id]);
            if (!$order) {
                return $this->json(['error' => 'Order not found'], 404);
            }

            if ($amount > $order['total_amount']) {
                return $this->json(['error' => 'Refund amount cannot exceed order total'], 400);
            }

            // Create refund record
            $this->execute("
                INSERT INTO refunds (order_id, amount, reason, processed_by, status, created_at)
                VALUES (?, ?, ?, ?, 'processed', NOW())
            ", [$id, $amount, $reason, $user->id]);

            // Update order status if full refund
            if ($amount >= $order['total_amount']) {
                $this->execute("UPDATE orders SET status = 'refunded', updated_at = NOW() WHERE id = ?", [$id]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Refund processed successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error processing refund: " . $e->getMessage());
            return $this->json(['error' => 'Failed to process refund'], 500);
        }
    }

    /**
     * Get order statistics
     */
    public function statistics()
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        try {
            $stats = [];

            // Total orders
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders");
            $stats['total_orders'] = $result['count'] ?? 0;

            // Orders by status
            $statusCounts = $this->fetchAll("
                SELECT status, COUNT(*) as count 
                FROM orders 
                GROUP BY status
            ");
            
            foreach ($statusCounts as $status) {
                $stats['status_' . $status['status']] = $status['count'];
            }

            // Revenue statistics
            $result = $this->fetchOne("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'delivered'");
            $stats['total_revenue'] = $result['total_revenue'] ?? 0;

            $result = $this->fetchOne("SELECT SUM(total_amount) as today_revenue FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()");
            $stats['today_revenue'] = $result['today_revenue'] ?? 0;

            return $this->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching order statistics: " . $e->getMessage());
            return $this->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }
}
