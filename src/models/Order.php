<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class Order
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    protected $table = 'orders';
    
    protected $fillable = [
        'order_number', 'customer_id', 'restaurant_id', 'rider_id', 'affiliate_id', 'status',
        'subtotal', 'delivery_fee', 'service_fee', 'tax_amount', 'discount_amount', 'total_amount',
        'payment_method', 'payment_status', 'coupon_code', 'affiliate_commission',
        'delivery_address', 'delivery_instructions', 'currency',
        'estimated_delivery_time', 'actual_delivery_time', 'preparation_time', 'delivery_distance',
        'special_instructions', 'rating', 'review', 'cancellation_reason',
        'refund_amount', 'refund_reason', 'tracking_data', 'metadata'
    ];

    public function createOrder(array $orderData): ?int
    {
        // Generate unique order number
        $orderData['order_number'] = $this->generateOrderNumber();
        $orderData['created_at'] = date('Y-m-d H:i:s');
        $orderData['updated_at'] = date('Y-m-d H:i:s');

        return $this->insertRecord($this->table, $orderData);
    }

    public function generateOrderNumber(): string
    {
        $prefix = 'T2E';
        $timestamp = date('ymd');
        $random = str_pad((string)mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $orderNumber = $prefix . $timestamp . $random;

        // Ensure uniqueness
        while ($this->orderNumberExists($orderNumber)) {
            $random = str_pad((string)mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $orderNumber = $prefix . $timestamp . $random;
        }
        
        return $orderNumber;
    }

    private function orderNumberExists(string $orderNumber): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE order_number = ?";
        $result = $this->fetchOne($sql, [$orderNumber]);
        return ($result['count'] ?? 0) > 0;
    }

    public function getOrdersByCustomer(int $customerId, int $limit = 20, int $offset = 0): array
    {
        $sql = "
            SELECT
                o.*,
                r.name as restaurant_name,
                r.image as restaurant_image,
                r.phone as restaurant_phone,
                COUNT(oi.id) as item_count
            FROM {$this->table} o
            JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.customer_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ";

        return $this->fetchAll($sql, [$customerId, $limit, $offset]);
    }

    public function getRecentOrdersByCustomer(int $customerId, int $limit = 5): array
    {
        $sql = "
            SELECT
                o.*,
                r.name as restaurant_name,
                r.image as restaurant_image,
                r.logo as restaurant_logo,
                COUNT(oi.id) as item_count,
                SUM(oi.quantity) as total_items
            FROM {$this->table} o
            JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.customer_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$customerId, $limit]);
    }

    public function getOrderDetails(int $orderId, int $customerId = null): ?array
    {
        $sql = "
            SELECT 
                o.*,
                r.name as restaurant_name,
                r.image as restaurant_image,
                r.phone as restaurant_phone,
                r.address as restaurant_address,
                u.first_name as customer_first_name,
                u.last_name as customer_last_name,
                u.phone as customer_phone,
                u.email as customer_email,
                rider.first_name as rider_first_name,
                rider.last_name as rider_last_name,
                rider.phone as rider_phone
            FROM {$this->table} o
            JOIN restaurants r ON o.restaurant_id = r.id
            JOIN users u ON o.customer_id = u.id
            LEFT JOIN users rider ON o.rider_id = rider.id
            WHERE o.id = ?
        ";

        $params = [$orderId];
        
        if ($customerId) {
            $sql .= " AND o.customer_id = ?";
            $params[] = $customerId;
        }

        return $this->fetchOne($sql, $params);
    }

    /**
     * Get order by ID with customer details
     */
    public function getOrderById(int $orderId): ?array
    {
        $sql = "
            SELECT 
                o.*,
                u.first_name as customer_first_name,
                u.last_name as customer_last_name,
                u.phone as customer_phone,
                u.email as customer_email,
                r.name as restaurant_name
            FROM {$this->table} o
            JOIN users u ON o.customer_id = u.id
            JOIN restaurants r ON o.restaurant_id = r.id
            WHERE o.id = ?
            LIMIT 1
        ";

        $result = $this->fetchOne($sql, [$orderId]);
        return $result;
    }

    /**
     * Get order by ID (simple version without joins)
     * Alias for getOrderById for consistency with other models
     */
    public function getById(int $orderId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->fetchOne($sql, [$orderId]);
    }

    public function getOrderItems(int $orderId): array
    {
        $sql = "
            SELECT 
                oi.*,
                mi.name as item_name,
                mi.description as item_description,
                mi.image as item_image,
                c.name as category_name
            FROM order_items oi
            JOIN menu_items mi ON oi.menu_item_id = mi.id
            JOIN categories c ON mi.category_id = c.id
            WHERE oi.order_id = ?
            ORDER BY oi.id
        ";

        return $this->fetchAll($sql, [$orderId]);
    }

    public function updateOrderStatus(int $orderId, string $status, array $additionalData = []): bool
    {
        $updateData = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];

        // Set additional data based on status
        switch ($status) {
            case 'ready':
                // Calculate estimated delivery time
                $order = $this->getOrderById($orderId);
                if ($order) {
                    $restaurant = $this->fetchOne("SELECT delivery_time FROM restaurants WHERE id = ?", [$order['restaurant_id']]);
                    if ($restaurant && !empty($restaurant['delivery_time'])) {
                        // Extract numeric value from delivery_time (e.g., "30-45 minutes" -> 30)
                        $deliveryTime = $restaurant['delivery_time'];
                        if (preg_match('/(\d+)/', $deliveryTime, $matches)) {
                            $minutes = (int)$matches[1];
                            $timestamp = strtotime('+' . $minutes . ' minutes');
                            if ($timestamp !== false) {
                                $updateData['estimated_delivery_time'] = date('Y-m-d H:i:s', $timestamp);
                            }
                        }
                    }
                }
                break;
            case 'delivered':
                $updateData['actual_delivery_time'] = date('Y-m-d H:i:s');
                $updateData['payment_status'] = 'paid';
                // Process affiliate commission when order is delivered
                // Wrap in try-catch to prevent affiliate processing from breaking order status update
                try {
                    $this->processAffiliateCommission($orderId);
                } catch (\Exception $e) {
                    // Log error but don't fail the order status update
                    error_log("Failed to process affiliate commission for order {$orderId}: " . $e->getMessage());
                }
                break;
            case 'cancelled':
                if (isset($additionalData['cancellation_reason'])) {
                    $updateData['cancellation_reason'] = $additionalData['cancellation_reason'];
                }
                break;
        }

        // Merge additional data
        $updateData = array_merge($updateData, $additionalData);

        try {
            $rowsAffected = $this->updateRecord($this->table, $updateData, ['id' => $orderId]);
            
            if ($rowsAffected > 0) {
                error_log("Successfully updated order ID: {$orderId} to status: {$status}");
                return true;
            } else {
                // Check if order exists
                $existing = $this->getById($orderId);
                if (!$existing) {
                    error_log("Order ID {$orderId} does not exist in database");
                    return false;
                }
                // If order exists but no rows affected, data might be the same
                error_log("No rows updated for order ID: {$orderId}. Status may already be '{$status}' or data unchanged.");
                // Return true as the order is already in the desired state
                return true;
            }
        } catch (\Exception $e) {
            error_log("Exception updating order status for order ID {$orderId}: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function canCancelOrder(int $orderId, int $customerId): bool
    {
        $order = $this->getOrderDetails($orderId, $customerId);
        
        if (!$order) {
            return false;
        }

        // Can cancel if order is pending or confirmed and not yet prepared
        $cancellableStatuses = ['pending', 'confirmed'];
        return in_array($order['status'], $cancellableStatuses);
    }

    public function cancelOrder(int $orderId, int $customerId, string $reason = ''): bool
    {
        if (!$this->canCancelOrder($orderId, $customerId)) {
            return false;
        }

        return $this->updateOrderStatus($orderId, 'cancelled', [
            'cancellation_reason' => $reason ?: 'Cancelled by customer'
        ]);
    }

    public function getRecentOrdersByRestaurant(int $restaurantId, int $limit = 10): array
    {
        $sql = "
            SELECT 
                o.*,
                u.first_name as customer_first_name,
                u.last_name as customer_last_name,
                u.phone as customer_phone,
                COUNT(oi.id) as item_count
            FROM {$this->table} o
            JOIN users u ON o.customer_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.restaurant_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$restaurantId, $limit]);
    }

    public function getOrdersByRestaurant(int $restaurantId, string $status = 'all', int $limit = 20, int $offset = 0): array
    {
        $sql = "
            SELECT 
                o.*,
                u.first_name as customer_first_name,
                u.last_name as customer_last_name,
                u.phone as customer_phone,
                COUNT(oi.id) as item_count
            FROM {$this->table} o
            JOIN users u ON o.customer_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.restaurant_id = ?
        ";

        $params = [$restaurantId];

        if ($status !== 'all') {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }

        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->fetchAll($sql, $params);
    }

    public function getAvailableOrdersForRider(int $riderId, int $limit = 10, int $offset = 0): array
    {
        $sql = "
            SELECT 
                o.*,
                r.name as restaurant_name,
                r.address as restaurant_address,
                r.latitude as restaurant_latitude,
                r.longitude as restaurant_longitude,
                u.first_name as customer_first_name,
                u.last_name as customer_last_name,
                COUNT(oi.id) as item_count
            FROM {$this->table} o
            JOIN restaurants r ON o.restaurant_id = r.id
            JOIN users u ON o.customer_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.status = 'ready' AND o.rider_id IS NULL
            GROUP BY o.id
            ORDER BY o.created_at ASC
            LIMIT ? OFFSET ?
        ";

        return $this->fetchAll($sql, [$limit, $offset]);
    }

    public function countAvailableOrdersForRider(int $riderId): int
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM {$this->table} o
            WHERE o.status = 'ready' AND o.rider_id IS NULL
        ";

        $result = $this->fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }

    public function assignRider(int $orderId, int $riderId): bool
    {
        return $this->updateRecord($this->table, [
            'rider_id' => $riderId,
            'status' => 'assigned',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $orderId]) > 0;
    }

    /**
     * Unassign rider from order (make it available again)
     * Useful for recovery when order is stuck in assigned state without delivery record
     */
    public function unassignRider(int $orderId): bool
    {
        return $this->updateRecord($this->table, [
            'rider_id' => null,
            'status' => 'ready',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $orderId]) > 0;
    }

    public function calculateAffiliateCommission(float $orderTotal, float $commissionRate): float
    {
        return round($orderTotal * ($commissionRate / 100), 2);
    }

    /**
     * Calculate platform commission for an order
     */
    public function calculatePlatformCommission(int $orderId): array
    {
        try {
            $sql = "
                SELECT
                    o.id,
                    o.subtotal,
                    o.total_amount,
                    o.restaurant_id,
                    r.name as restaurant_name,
                    r.commission_rate,
                    (o.subtotal * r.commission_rate) as commission_amount,
                    (o.subtotal - (o.subtotal * r.commission_rate)) as restaurant_earnings
                FROM {$this->table} o
                JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.id = ?
            ";

            $result = $this->fetchOne($sql, [$orderId]);

            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            return [
                'success' => true,
                'order_id' => $result['id'],
                'subtotal' => (float)$result['subtotal'],
                'total_amount' => (float)$result['total_amount'],
                'restaurant_name' => $result['restaurant_name'],
                'commission_rate' => (float)$result['commission_rate'],
                'commission_amount' => round((float)$result['commission_amount'], 2),
                'restaurant_earnings' => round((float)$result['restaurant_earnings'], 2)
            ];

        } catch (\Exception $e) {
            error_log("Error calculating platform commission: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error calculating commission'
            ];
        }
    }

    /**
     * Get profit analytics for admin dashboard
     */
    public function getProfitAnalytics(string $period = 'month', int $restaurantId = null): array
    {
        try {
            $dateCondition = $this->getDateCondition($period);
            $restaurantCondition = $restaurantId ? "AND o.restaurant_id = {$restaurantId}" : "";

            $sql = "
                SELECT
                    COUNT(o.id) as total_orders,
                    SUM(o.subtotal) as total_subtotal,
                    SUM(o.total_amount) as total_revenue,
                    SUM(o.subtotal * r.commission_rate) as total_commission,
                    SUM(o.subtotal - (o.subtotal * r.commission_rate)) as total_restaurant_earnings,
                    AVG(r.commission_rate * 100) as avg_commission_rate,
                    COUNT(DISTINCT o.restaurant_id) as active_restaurants
                FROM {$this->table} o
                JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.status = 'delivered' {$dateCondition} {$restaurantCondition}
            ";

            $result = $this->fetchOne($sql);

            return [
                'total_orders' => (int)($result['total_orders'] ?? 0),
                'total_subtotal' => round((float)($result['total_subtotal'] ?? 0), 2),
                'total_revenue' => round((float)($result['total_revenue'] ?? 0), 2),
                'total_commission' => round((float)($result['total_commission'] ?? 0), 2),
                'total_restaurant_earnings' => round((float)($result['total_restaurant_earnings'] ?? 0), 2),
                'avg_commission_rate' => round((float)($result['avg_commission_rate'] ?? 15), 2),
                'active_restaurants' => (int)($result['active_restaurants'] ?? 0),
                'commission_percentage' => $result['total_subtotal'] > 0 ?
                    round(((float)($result['total_commission'] ?? 0) / (float)($result['total_subtotal'] ?? 1)) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            error_log("Error getting profit analytics: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'total_subtotal' => 0,
                'total_revenue' => 0,
                'total_commission' => 0,
                'total_restaurant_earnings' => 0,
                'avg_commission_rate' => 15,
                'active_restaurants' => 0,
                'commission_percentage' => 0
            ];
        }
    }

    public function processAffiliateCommission(int $orderId): bool
    {
        $order = $this->getById($orderId);

        if (!$order || empty($order['affiliate_code']) || ($order['affiliate_commission'] ?? 0) > 0) {
            return false;
        }

        // Get affiliate from affiliates table
        $affiliateCode = $order['affiliate_code'] ?? null;
        if (empty($affiliateCode)) {
            return false;
        }
        
        $affiliateSQL = "SELECT * FROM affiliates WHERE affiliate_code = ? AND status = 'active'";
        $affiliateResult = $this->query($affiliateSQL, [$affiliateCode]);
        $affiliate = $affiliateResult->fetch(\PDO::FETCH_ASSOC) ?: null;

        if (!$affiliate) {
            return false;
        }

        $commission = $this->calculateAffiliateCommission($order['subtotal'], $affiliate['commission_rate']);

        try {
            $this->beginTransaction();

            // Update order with commission
            $this->query("UPDATE orders SET affiliate_commission = ? WHERE id = ?", [$commission, $orderId]);

            // Add commission to affiliate balance using Affiliate model
            require_once __DIR__ . '/Affiliate.php';
            $affiliateModel = new \models\Affiliate();
            // Pass customer_id as the 4th parameter
            $affiliateModel->addEarning($affiliate['id'], $commission, $orderId, $order['customer_id'], 'referral');

            $this->commit();
            return true;

        } catch (\Exception $e) {
            $this->rollback();
            error_log("Affiliate commission processing error: " . $e->getMessage());
            return false;
        }
    }

    public function getOrderStatistics(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_orders,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_orders,
                COUNT(CASE WHEN status = 'preparing' THEN 1 END) as preparing_orders,
                COUNT(CASE WHEN status = 'ready' THEN 1 END) as ready_orders,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(AVG(total_amount), 0) as average_order_value
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ";

        return $this->fetchOne($sql) ?: [];
    }

    public function getTodayOrderCount(int $restaurantId): int
    {
        $sql = "
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE restaurant_id = ? AND DATE(created_at) = CURDATE()
        ";
        
        $result = $this->fetchOne($sql, [$restaurantId]);
        return (int)($result['count'] ?? 0);
    }

    public function getTodayRevenue(int $restaurantId): float
    {
        $sql = "
            SELECT COALESCE(SUM(subtotal), 0) as revenue 
            FROM {$this->table} 
            WHERE restaurant_id = ? AND DATE(created_at) = CURDATE() AND status != 'cancelled'
        ";
        
        $result = $this->fetchOne($sql, [$restaurantId]);
        return (float)($result['revenue'] ?? 0);
    }

    public function getMonthlyOrderCount(int $restaurantId): int
    {
        $sql = "
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE restaurant_id = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
        ";
        
        $result = $this->fetchOne($sql, [$restaurantId]);
        return (int)($result['count'] ?? 0);
    }

    public function getMonthlyRevenue(int $restaurantId): float
    {
        $sql = "
            SELECT COALESCE(SUM(subtotal), 0) as revenue 
            FROM {$this->table} 
            WHERE restaurant_id = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status != 'cancelled'
        ";
        
        $result = $this->fetchOne($sql, [$restaurantId]);
        return (float)($result['revenue'] ?? 0);
    }

    public function countOrdersByCustomer(int $customerId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE customer_id = ?";
        $result = $this->fetchOne($sql, [$customerId]);
        return (int)($result['count'] ?? 0);
    }

    public function getTotalSpentByCustomer(int $customerId): float
    {
        $sql = "
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM {$this->table}
            WHERE customer_id = ? AND status = 'delivered'
        ";

        $result = $this->fetchOne($sql, [$customerId]);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get total order count
     */
    public function getTotalCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue(string $period = 'all'): float
    {
        $dateCondition = $this->getDateCondition($period);

        $sql = "SELECT COALESCE(SUM(total_amount), 0) as total
                FROM {$this->table}
                WHERE status = 'delivered' {$dateCondition}";

        $result = $this->fetchOne($sql);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValue(string $period = 'all'): float
    {
        $dateCondition = $this->getDateCondition($period);

        $sql = "SELECT AVG(total_amount) as avg_value
                FROM {$this->table}
                WHERE status = 'delivered' {$dateCondition}";

        $result = $this->fetchOne($sql);
        return round((float)($result['avg_value'] ?? 0), 2);
    }

    /**
     * Get orders by status for a period
     */
    public function getOrdersByStatus(string $period = 'all'): array
    {
        $dateCondition = $this->getDateCondition($period);

        $sql = "SELECT status, COUNT(*) as count
                FROM {$this->table}
                WHERE 1=1 {$dateCondition}
                GROUP BY status";

        $results = $this->fetchAll($sql);
        $counts = [];

        foreach ($results as $result) {
            $counts[$result['status']] = (int)$result['count'];
        }

        return $counts;
    }

    /**
     * Get active order count
     */
    public function getActiveOrderCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE status IN ('pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery')";
        $result = $this->fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get monthly revenue (global)
     */
    public function getGlobalMonthlyRevenue(): float
    {
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as total
                FROM {$this->table}
                WHERE status = 'delivered'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $result = $this->fetchOne($sql);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get monthly order count (global)
     */
    public function getGlobalMonthlyOrderCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get orders with filtering and pagination
     */
    public function getOrders(string $status = 'all', string $date = '', string $search = '', int $limit = 20, int $offset = 0): array
    {
        $conditions = ['1=1'];
        $params = [];

        if ($status !== 'all') {
            $conditions[] = 'o.status = ?';
            $params[] = $status;
        }

        if (!empty($date)) {
            $conditions[] = 'DATE(o.created_at) = ?';
            $params[] = $date;
        }

        if (!empty($search)) {
            $conditions[] = '(o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR r.name LIKE ?)';
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = implode(' AND ', $conditions);
        $params[] = $limit;
        $params[] = $offset;

        $sql = "SELECT o.*,
                       c.first_name as customer_first_name, c.last_name as customer_last_name, c.email as customer_email,
                       r.name as restaurant_name,
                       rd.first_name as rider_first_name, rd.last_name as rider_last_name
                FROM {$this->table} o
                LEFT JOIN users c ON o.customer_id = c.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users rd ON o.rider_id = rd.id
                WHERE {$whereClause}
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Count orders with filtering
     */
    public function countOrders(string $status = 'all', string $date = '', string $search = ''): int
    {
        $conditions = ['1=1'];
        $params = [];

        if ($status !== 'all') {
            $conditions[] = 'o.status = ?';
            $params[] = $status;
        }

        if (!empty($date)) {
            $conditions[] = 'DATE(o.created_at) = ?';
            $params[] = $date;
        }

        if (!empty($search)) {
            $conditions[] = '(o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR r.name LIKE ?)';
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} o
                LEFT JOIN users c ON o.customer_id = c.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                WHERE {$whereClause}";

        $result = $this->fetchOne($sql, $params);
        return (int)($result['count'] ?? 0);
    }



    /**
     * Get date condition for queries
     */
    private function getDateCondition(string $period): string
    {
        switch ($period) {
            case 'today':
                return "AND DATE(created_at) = CURDATE()";
            case 'yesterday':
                return "AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            case 'week':
            case '7days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
            case '30days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case 'year':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "";
        }
    }

    /**
     * Count orders by restaurant with optional status filter
     */
    public function countOrdersByRestaurant(int $restaurantId, string $status = 'all'): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $params = [$restaurantId];

        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $result = $this->fetchOne($sql, $params);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get order status counts for a restaurant
     */
    public function getOrderStatusCounts(int $restaurantId): array
    {
        $sql = "
            SELECT 
                status,
                COUNT(*) as count
            FROM {$this->table} 
            WHERE restaurant_id = ?
            GROUP BY status
        ";
        
        $results = $this->fetchAll($sql, [$restaurantId]);
        
        // Convert to associative array with status as key
        $statusCounts = [
            'pending' => 0,
            'confirmed' => 0,
            'preparing' => 0,
            'ready' => 0,
            'picked_up' => 0,
            'out_for_delivery' => 0,
            'delivered' => 0,
            'cancelled' => 0
        ];
        
        foreach ($results as $result) {
            $statusCounts[$result['status']] = (int)$result['count'];
        }
        
        return $statusCounts;
    }

    /**
     * Get sales analytics for a restaurant
     */
    public function getSalesAnalytics(int $restaurantId, string $period = '7days'): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        try {
            // Get daily sales data
            $sql = "
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as order_count,
                    SUM(total_amount) as revenue,
                    AVG(total_amount) as avg_order_value
                FROM {$this->table} 
                WHERE restaurant_id = ? AND status IN ('delivered', 'completed') {$dateCondition}
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ";
            
            $dailyData = $this->fetchAll($sql, [$restaurantId]);
            
            // Get summary statistics
            $summarySQL = "
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value,
                    COUNT(DISTINCT customer_id) as unique_customers
                FROM {$this->table} 
                WHERE restaurant_id = ? AND status IN ('delivered', 'completed') {$dateCondition}
            ";
            
            $summary = $this->fetchOne($summarySQL, [$restaurantId]);
            
            return [
                'daily_data' => $dailyData,
                'summary' => [
                    'total_orders' => (int)($summary['total_orders'] ?? 0),
                    'total_revenue' => (float)($summary['total_revenue'] ?? 0),
                    'avg_order_value' => (float)($summary['avg_order_value'] ?? 0),
                    'unique_customers' => (int)($summary['unique_customers'] ?? 0)
                ]
            ];
        } catch (\Exception $e) {
            error_log("Error getting sales analytics: " . $e->getMessage());
            return [
                'daily_data' => [],
                'summary' => [
                    'total_orders' => 0,
                    'total_revenue' => 0,
                    'avg_order_value' => 0,
                    'unique_customers' => 0
                ]
            ];
        }
    }

    /**
     * Get customer analytics for a restaurant
     */
    public function getCustomerAnalytics(int $restaurantId, string $period = '7days'): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        try {
            // Get new vs returning customers
            $sql = "
                SELECT 
                    customer_id,
                    COUNT(*) as order_count,
                    MIN(created_at) as first_order,
                    MAX(created_at) as last_order,
                    SUM(total_amount) as total_spent
                FROM {$this->table} 
                WHERE restaurant_id = ? AND status IN ('delivered', 'completed') {$dateCondition}
                GROUP BY customer_id
            ";
            
            $customerData = $this->fetchAll($sql, [$restaurantId]);
            
            $newCustomers = 0;
            $returningCustomers = 0;
            $totalCustomers = count($customerData);
            
            foreach ($customerData as $customer) {
                if ($customer['order_count'] == 1) {
                    $newCustomers++;
                } else {
                    $returningCustomers++;
                }
            }
            
            // Get top customers
            $topCustomersSQL = "
                SELECT 
                    u.first_name,
                    u.last_name,
                    u.email,
                    COUNT(o.id) as order_count,
                    SUM(o.total_amount) as total_spent
                FROM {$this->table} o
                JOIN users u ON o.customer_id = u.id
                WHERE o.restaurant_id = ? AND o.status IN ('delivered', 'completed') {$dateCondition}
                GROUP BY o.customer_id
                ORDER BY total_spent DESC
                LIMIT 10
            ";
            
            $topCustomers = $this->fetchAll($topCustomersSQL, [$restaurantId]);
            
            return [
                'total_customers' => $totalCustomers,
                'new_customers' => $newCustomers,
                'returning_customers' => $returningCustomers,
                'retention_rate' => $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 2) : 0,
                'top_customers' => $topCustomers
            ];
        } catch (\Exception $e) {
            error_log("Error getting customer analytics: " . $e->getMessage());
            return [
                'total_customers' => 0,
                'new_customers' => 0,
                'returning_customers' => 0,
                'retention_rate' => 0,
                'top_customers' => []
            ];
        }
    }

    /**
     * Get revenue breakdown for a restaurant
     */
    public function getRevenueBreakdown(int $restaurantId, string $period = '7days'): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        try {
            // Get revenue by payment method
            $paymentSQL = "
                SELECT 
                    payment_method,
                    COUNT(*) as order_count,
                    SUM(total_amount) as revenue
                FROM {$this->table} 
                WHERE restaurant_id = ? AND status IN ('delivered', 'completed') {$dateCondition}
                GROUP BY payment_method
            ";
            
            $paymentBreakdown = $this->fetchAll($paymentSQL, [$restaurantId]);
            
            // Get revenue by hour of day
            $hourlySQL = "
                SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as order_count,
                    SUM(total_amount) as revenue
                FROM {$this->table} 
                WHERE restaurant_id = ? AND status IN ('delivered', 'completed') {$dateCondition}
                GROUP BY HOUR(created_at)
                ORDER BY hour ASC
            ";
            
            $hourlyBreakdown = $this->fetchAll($hourlySQL, [$restaurantId]);
            
            // Get revenue by day of week
            $weeklySQL = "
                SELECT 
                    DAYOFWEEK(created_at) as day_of_week,
                    COUNT(*) as order_count,
                    SUM(total_amount) as revenue
                FROM {$this->table} 
                WHERE restaurant_id = ? AND status IN ('delivered', 'completed') {$dateCondition}
                GROUP BY DAYOFWEEK(created_at)
                ORDER BY day_of_week ASC
            ";
            
            $weeklyBreakdown = $this->fetchAll($weeklySQL, [$restaurantId]);
            
            return [
                'payment_methods' => $paymentBreakdown,
                'hourly_breakdown' => $hourlyBreakdown,
                'weekly_breakdown' => $weeklyBreakdown
            ];
        } catch (\Exception $e) {
            error_log("Error getting revenue breakdown: " . $e->getMessage());
            return [
                'payment_methods' => [],
                'hourly_breakdown' => [],
                'weekly_breakdown' => []
            ];
        }
    }

    /**
     * Get earnings by restaurant with pagination
     */
    public function getEarningsByRestaurant(int $restaurantId, int $limit = 20, int $offset = 0): array
    {
        try {
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.total_amount,
                    o.subtotal,
                    o.delivery_fee,
                    o.tax_amount,
                    o.platform_fee,
                    o.restaurant_earnings,
                    o.status,
                    o.payment_method,
                    o.payment_status,
                    o.created_at,
                    o.delivered_at,
                    u.first_name as customer_first_name,
                    u.last_name as customer_last_name
                FROM {$this->table} o
                LEFT JOIN users u ON o.customer_id = u.id
                WHERE o.restaurant_id = ? 
                AND o.status IN ('delivered', 'completed')
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            return $this->fetchAll($sql, [$restaurantId, $limit, $offset]);
        } catch (\Exception $e) {
            error_log("Error getting earnings by restaurant: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total earnings for a restaurant
     */
    public function getTotalEarnings(int $restaurantId): array
    {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    SUM(restaurant_earnings) as total_earnings,
                    SUM(platform_fee) as total_platform_fees,
                    AVG(total_amount) as avg_order_value
                FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND status IN ('delivered', 'completed')
            ";
            
            $result = $this->fetchOne($sql, [$restaurantId]);
            
            return [
                'total_orders' => (int)($result['total_orders'] ?? 0),
                'total_revenue' => (float)($result['total_revenue'] ?? 0),
                'total_earnings' => (float)($result['total_earnings'] ?? 0),
                'total_platform_fees' => (float)($result['total_platform_fees'] ?? 0),
                'avg_order_value' => (float)($result['avg_order_value'] ?? 0)
            ];
        } catch (\Exception $e) {
            error_log("Error getting total earnings: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'total_revenue' => 0,
                'total_earnings' => 0,
                'total_platform_fees' => 0,
                'avg_order_value' => 0
            ];
        }
    }

    /**
     * Get monthly earnings for a restaurant
     */
    public function getMonthlyEarnings(int $restaurantId): array
    {
        try {
            $sql = "
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as order_count,
                    SUM(total_amount) as revenue,
                    SUM(restaurant_earnings) as earnings,
                    SUM(platform_fee) as platform_fees
                FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND status IN ('delivered', 'completed')
                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
            ";
            
            return $this->fetchAll($sql, [$restaurantId]);
        } catch (\Exception $e) {
            error_log("Error getting monthly earnings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending payouts for a restaurant
     */
    public function getPendingPayouts(int $restaurantId): array
    {
        try {
            // Get orders that are completed but not yet paid out to restaurant
            $sql = "
                SELECT 
                    COUNT(*) as pending_count,
                    SUM(restaurant_earnings) as pending_amount,
                    MIN(delivered_at) as oldest_pending,
                    MAX(delivered_at) as newest_pending
                FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND status IN ('delivered', 'completed')
                AND (payout_status IS NULL OR payout_status = 'pending')
                AND delivered_at IS NOT NULL
            ";
            
            $result = $this->fetchOne($sql, [$restaurantId]);
            
            // Get weekly pending amounts
            $weeklySQL = "
                SELECT 
                    WEEK(delivered_at) as week_number,
                    YEAR(delivered_at) as year,
                    COUNT(*) as order_count,
                    SUM(restaurant_earnings) as earnings
                FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND status IN ('delivered', 'completed')
                AND (payout_status IS NULL OR payout_status = 'pending')
                AND delivered_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
                GROUP BY YEAR(delivered_at), WEEK(delivered_at)
                ORDER BY year DESC, week_number DESC
            ";
            
            $weeklyPending = $this->fetchAll($weeklySQL, [$restaurantId]);
            
            return [
                'pending_count' => (int)($result['pending_count'] ?? 0),
                'pending_amount' => (float)($result['pending_amount'] ?? 0),
                'oldest_pending' => $result['oldest_pending'] ?? null,
                'newest_pending' => $result['newest_pending'] ?? null,
                'weekly_breakdown' => $weeklyPending
            ];
        } catch (\Exception $e) {
            error_log("Error getting pending payouts: " . $e->getMessage());
            return [
                'pending_count' => 0,
                'pending_amount' => 0,
                'oldest_pending' => null,
                'newest_pending' => null,
                'weekly_breakdown' => []
            ];
        }
    }
}
