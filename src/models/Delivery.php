<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class Delivery
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    protected $table = 'deliveries';
    protected $fillable = [
        'order_id', 'rider_id', 'status', 'pickup_time', 'delivery_time',
        'estimated_duration', 'actual_duration', 'pickup_address',
        'delivery_address', 'distance', 'delivery_fee',
        'rider_earnings', 'platform_commission', 'rider_notes', 
        'rating', 'review', 'delivery_proof', 'customer_signature',
        'cancellation_reason', 'tracking_data', 'updated_at'
    ];

    public function create(array $deliveryData): ?int
    {
        // Note: tracking_code column doesn't exist in database, so we skip it
        // The status will be set by the caller or default to 'assigned'
        if (!isset($deliveryData['status'])) {
            $deliveryData['status'] = 'assigned';
        }
        if (!isset($deliveryData['created_at'])) {
            $deliveryData['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($deliveryData['updated_at'])) {
            $deliveryData['updated_at'] = date('Y-m-d H:i:s');
        }

        // Filter to only fillable fields
        $deliveryData = $this->filterFillable($deliveryData);
        
        // Use insertRecord from DatabaseTrait
        return $this->insertRecord($this->table, $deliveryData);
    }

    public function generateTrackingCode(): string
    {
        $prefix = 'TRK';
        $timestamp = date('ymdHi');
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $trackingCode = $prefix . $timestamp . $random;
        
        // Ensure uniqueness
        while ($this->trackingCodeExists($trackingCode)) {
            $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
            $trackingCode = $prefix . $timestamp . $random;
        }
        
        return $trackingCode;
    }

    private function trackingCodeExists(string $trackingCode): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE tracking_code = ?";
        $result = $this->fetchOne($sql, [$trackingCode]);
        return $result['count'] > 0;
    }

    public function getDeliveryByOrder(int $orderId): ?array
    {
        $sql = "
            SELECT 
                d.*,
                o.order_number,
                o.customer_id,
                o.restaurant_id,
                o.total_amount,
                r.first_name as rider_first_name,
                r.last_name as rider_last_name,
                r.phone as rider_phone,
                r.profile_image as rider_image,
                c.first_name as customer_first_name,
                c.last_name as customer_last_name,
                c.phone as customer_phone,
                rest.name as restaurant_name,
                rest.address as restaurant_address,
                rest.latitude as restaurant_latitude,
                rest.longitude as restaurant_longitude
            FROM {$this->table} d
            JOIN orders o ON d.order_id = o.id
            LEFT JOIN users r ON d.rider_id = r.id
            JOIN users c ON o.customer_id = c.id
            JOIN restaurants rest ON o.restaurant_id = rest.id
            WHERE d.order_id = ?
        ";

        return $this->fetchOne($sql, [$orderId]);
    }

    public function getByOrderAndRider(int $orderId, int $riderId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ? AND rider_id = ? LIMIT 1";
        return $this->fetchOne($sql, [$orderId, $riderId]);
    }

    public function getDeliveryByTrackingCode(string $trackingCode): ?array
    {
        $sql = "
            SELECT 
                d.*,
                o.order_number,
                o.customer_id,
                o.restaurant_id,
                o.total_amount,
                o.delivery_address,
                r.first_name as rider_first_name,
                r.last_name as rider_last_name,
                r.phone as rider_phone,
                r.profile_image as rider_image,
                c.first_name as customer_first_name,
                c.last_name as customer_last_name,
                c.phone as customer_phone,
                rest.name as restaurant_name,
                rest.address as restaurant_address,
                rest.latitude as restaurant_latitude,
                rest.longitude as restaurant_longitude
            FROM {$this->table} d
            JOIN orders o ON d.order_id = o.id
            LEFT JOIN users r ON d.rider_id = r.id
            JOIN users c ON o.customer_id = c.id
            JOIN restaurants rest ON o.restaurant_id = rest.id
            WHERE d.tracking_code = ?
        ";

        return $this->fetchOne($sql, [$trackingCode]);
    }

    public function updateDeliveryStatus(int $deliveryId, string $status, array $additionalData = []): bool
    {
        $updateData = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        
        // Set timestamp based on status
        switch ($status) {
            case 'picked_up':
                $updateData['pickup_time'] = date('Y-m-d H:i:s');
                break;
            case 'on_the_way':
                // Calculate estimated duration (30 minutes from pickup)
                if (isset($additionalData['pickup_time'])) {
                    $pickupTime = strtotime($additionalData['pickup_time']);
                    $estimatedMinutes = 30;
                    $updateData['estimated_duration'] = $estimatedMinutes;
                }
                break;
            case 'delivered':
                $updateData['delivery_time'] = date('Y-m-d H:i:s');
                // Calculate actual duration if pickup_time exists
                if (isset($additionalData['pickup_time'])) {
                    $pickupTime = strtotime($additionalData['pickup_time']);
                    $deliveryTime = time();
                    $actualMinutes = round(($deliveryTime - $pickupTime) / 60);
                    $updateData['actual_duration'] = $actualMinutes;
                }
                break;
            case 'cancelled':
                if (isset($additionalData['rider_notes'])) {
                    $updateData['rider_notes'] = $additionalData['rider_notes'];
                }
                if (isset($additionalData['cancellation_reason'])) {
                    $updateData['cancellation_reason'] = $additionalData['cancellation_reason'];
                }
                break;
        }

        // Merge additional data
        $updateData = array_merge($updateData, $additionalData);

        return $this->updateRecord($this->table, $updateData, ['id' => $deliveryId]) > 0;
    }

    public function updateRiderLocation(int $deliveryId, float $latitude, float $longitude): bool
    {
        $locationData = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timestamp' => date('Y-m-d H:i:s'),
            'accuracy' => null,
            'speed' => null,
            'heading' => null
        ];

        return $this->updateRecord($this->table, [
            'tracking_data' => json_encode($locationData),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $deliveryId]) > 0;
    }

    public function getRiderLocation(int $deliveryId): ?array
    {
        $delivery = $this->getById($deliveryId);
        
        if (!$delivery || empty($delivery['tracking_data'])) {
            return null;
        }

        $location = json_decode($delivery['tracking_data'], true);
        return is_array($location) ? $location : null;
    }

    public function getActiveDeliveriesByRider(int $riderId): array
    {
        $sql = "
            SELECT 
                d.*,
                o.order_number,
                o.customer_id,
                o.total_amount,
                o.delivery_address,
                c.first_name as customer_first_name,
                c.last_name as customer_last_name,
                c.phone as customer_phone,
                rest.name as restaurant_name,
                rest.address as restaurant_address
            FROM {$this->table} d
            JOIN orders o ON d.order_id = o.id
            JOIN users c ON o.customer_id = c.id
            JOIN restaurants rest ON o.restaurant_id = rest.id
            WHERE d.rider_id = ? AND d.status IN ('assigned', 'picked_up', 'on_the_way')
            ORDER BY d.created_at ASC
        ";

        return $this->fetchAll($sql, [$riderId]);
    }

    public function getDeliveryHistory(int $riderId, int $limit = 20, int $offset = 0): array
    {
        $sql = "
            SELECT 
                d.*,
                o.order_number,
                o.total_amount,
                rest.name as restaurant_name
            FROM {$this->table} d
            JOIN orders o ON d.order_id = o.id
            JOIN restaurants rest ON o.restaurant_id = rest.id
            WHERE d.rider_id = ? AND d.status IN ('delivered', 'failed', 'cancelled')
            ORDER BY d.updated_at DESC
            LIMIT ? OFFSET ?
        ";

        return $this->fetchAll($sql, [$riderId, $limit, $offset]);
    }

    public function calculateDeliveryTime(int $deliveryId): ?int
    {
        $delivery = $this->getById($deliveryId);
        
        if (!$delivery || !$delivery['pickup_time'] || !$delivery['delivery_time']) {
            return null;
        }

        $pickupTime = strtotime($delivery['pickup_time']);
        $deliveryTime = strtotime($delivery['delivery_time']);
        
        return ($deliveryTime - $pickupTime) / 60; // Return minutes
    }

    public function getRiderEarnings(int $riderId, string $period = 'today'): array
    {
        $whereClause = "d.rider_id = ? AND d.status = 'delivered'";
        $params = [$riderId];

        switch ($period) {
            case 'today':
                $whereClause .= " AND DATE(d.delivery_time) = CURDATE()";
                break;
            case 'week':
                $whereClause .= " AND d.delivery_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $whereClause .= " AND MONTH(d.delivery_time) = MONTH(CURDATE()) AND YEAR(d.delivery_time) = YEAR(CURDATE())";
                break;
        }

        $sql = "
            SELECT 
                COUNT(*) as total_deliveries,
                COALESCE(SUM(d.rider_earnings), 0) as total_earnings,
                COALESCE(AVG(d.rider_earnings), 0) as avg_earning_per_delivery,
                COALESCE(AVG(d.rating), 0) as avg_rating
            FROM {$this->table} d
            WHERE {$whereClause}
        ";

        $result = $this->fetchOne($sql, $params);
        
        return [
            'total_deliveries' => (int)($result['total_deliveries'] ?? 0),
            'total_earnings' => (float)($result['total_earnings'] ?? 0),
            'avg_earning_per_delivery' => (float)($result['avg_earning_per_delivery'] ?? 0),
            'avg_rating' => (float)($result['avg_rating'] ?? 0)
        ];
    }

    public function getDeliveryStatistics(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_deliveries,
                COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned_deliveries,
                COUNT(CASE WHEN status = 'picked_up' THEN 1 END) as picked_up_deliveries,
                COUNT(CASE WHEN status = 'out_for_delivery' THEN 1 END) as out_for_delivery,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_deliveries,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_deliveries,
                COALESCE(AVG(CASE WHEN status = 'delivered' AND pickup_time IS NOT NULL AND delivery_time IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, pickup_time, delivery_time) END), 0) as avg_delivery_time_minutes,
                COALESCE(AVG(customer_rating), 0) as avg_customer_rating
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ";

        $result = $this->fetchOne($sql);
        
        return [
            'total_deliveries' => (int)($result['total_deliveries'] ?? 0),
            'assigned_deliveries' => (int)($result['assigned_deliveries'] ?? 0),
            'picked_up_deliveries' => (int)($result['picked_up_deliveries'] ?? 0),
            'out_for_delivery' => (int)($result['out_for_delivery'] ?? 0),
            'delivered_deliveries' => (int)($result['delivered_deliveries'] ?? 0),
            'failed_deliveries' => (int)($result['failed_deliveries'] ?? 0),
            'avg_delivery_time_minutes' => (float)($result['avg_delivery_time_minutes'] ?? 0),
            'avg_customer_rating' => (float)($result['avg_customer_rating'] ?? 0)
        ];
    }

    public function assignDeliveryToRider(int $orderId, int $riderId): ?int
    {
        // Get order details
        $order = $this->fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
        
        if (!$order) {
            return null;
        }

        // Get restaurant location
        $restaurant = $this->fetchOne("SELECT * FROM restaurants WHERE id = ?", [$order['restaurant_id']]);
        
        if (!$restaurant) {
            return null;
        }

        // Parse delivery address
        $deliveryAddress = json_decode($order['delivery_address'], true);
        
        $deliveryData = [
            'order_id' => $orderId,
            'rider_id' => $riderId,
            'pickup_location' => json_encode([
                'latitude' => $restaurant['latitude'],
                'longitude' => $restaurant['longitude'],
                'address' => $restaurant['address']
            ]),
            'delivery_location' => json_encode([
                'latitude' => $deliveryAddress['latitude'] ?? null,
                'longitude' => $deliveryAddress['longitude'] ?? null,
                'address' => $deliveryAddress['address_line_1'] ?? ''
            ]),
            'delivery_fee' => $order['delivery_fee']
        ];

        return $this->createDelivery($deliveryData);
    }

    public function rateDelivery(int $deliveryId, int $rating, string $feedback = ''): bool
    {
        if ($rating < 1 || $rating > 5) {
            return false;
        }

        return $this->updateRecord($this->table, [
            'customer_rating' => $rating,
            'customer_feedback' => $feedback,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $deliveryId]) > 0;
    }

    /**
     * Get available deliveries nearby for riders
     */
    public function getAvailableDeliveriesNearby(float $latitude, float $longitude, float $radiusKm, int $limit, int $offset): array
    {
        $sql = "SELECT d.*, o.order_number, o.total_amount, o.preparation_time,
                       r.name as restaurant_name, r.address as restaurant_address,
                       r.latitude as pickup_latitude, r.longitude as pickup_longitude,
                       o.delivery_address,
                       (6371 * acos(cos(radians(?)) * cos(radians(r.latitude)) *
                        cos(radians(r.longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(r.latitude)))) AS distance_to_pickup
                FROM {$this->table} d
                JOIN orders o ON d.order_id = o.id
                JOIN restaurants r ON o.restaurant_id = r.id
                WHERE d.status = 'assigned'
                AND d.rider_id IS NULL
                HAVING distance_to_pickup <= ?
                ORDER BY distance_to_pickup ASC, d.created_at ASC
                LIMIT ? OFFSET ?";

        return $this->fetchAll($sql, [$latitude, $longitude, $latitude, $radiusKm, $limit, $offset]);
    }

    /**
     * Accept delivery by rider
     */
    public function acceptDelivery(int $deliveryId, int $riderId): bool
    {
        $sql = "UPDATE {$this->table}
                SET rider_id = ?, status = 'accepted', updated_at = ?
                WHERE id = ? AND status = 'assigned' AND rider_id IS NULL";

        return $this->db->execute($sql, [$riderId, date('Y-m-d H:i:s'), $deliveryId]) > 0;
    }

    /**
     * Get delivery with full details
     */
    public function getDeliveryWithDetails(int $deliveryId): ?array
    {
        $sql = "SELECT d.*, o.order_number, o.customer_id, o.restaurant_id, o.total_amount,
                       o.delivery_address,
                       r.name as restaurant_name, r.address as restaurant_address,
                       r.latitude as pickup_latitude, r.longitude as pickup_longitude,
                       r.user_id as restaurant_vendor_id,
                       c.first_name as customer_first_name, c.last_name as customer_last_name,
                       c.phone as customer_phone,
                       rider.first_name as rider_first_name, rider.last_name as rider_last_name,
                       rider.phone as rider_phone
                FROM {$this->table} d
                JOIN orders o ON d.order_id = o.id
                JOIN restaurants r ON o.restaurant_id = r.id
                JOIN users c ON o.customer_id = c.id
                LEFT JOIN users rider ON d.rider_id = rider.id
                WHERE d.id = ?";

        return $this->fetchOne($sql, [$deliveryId]);
    }

    /**
     * Filter data to only fillable fields
     */
    private function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Update delivery
     */
    public function updateDelivery(int $deliveryId, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Filter fillable fields
        $data = $this->filterFillable($data);
        
        if (empty($data)) {
            error_log("No fillable fields to update for delivery ID: {$deliveryId}");
            return false;
        }
        
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";
        
        $params = array_values($data);
        $params[] = $deliveryId;
        
        try {
            $stmt = $this->getDb()->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("SQL Error updating delivery {$deliveryId}: " . json_encode($errorInfo));
                return false;
            }
            
            // Check if any rows were actually updated
            $rowsAffected = $stmt->rowCount();
            if ($rowsAffected === 0) {
                error_log("No rows updated for delivery ID: {$deliveryId}. Delivery may not exist or data unchanged.");
                // Check if delivery exists
                $existing = $this->getById($deliveryId);
                if (!$existing) {
                    error_log("Delivery ID {$deliveryId} does not exist in database");
                    return false;
                }
                // If delivery exists but no rows affected, it might be because data is the same
                // Return true in this case as the update was "successful" (no change needed)
                return true;
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Exception updating delivery {$deliveryId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log rider rejection
     */
    public function logRiderRejection(int $deliveryId, int $riderId, string $reason): bool
    {
        // This could be logged to a separate table for analytics
        // For now, we'll just log it
        error_log("Rider $riderId rejected delivery $deliveryId: $reason");
        return true;
    }

    /**
     * Get rider statistics for period
     */
    public function getRiderStats(int $riderId, string $period): array
    {
        $dateCondition = $this->getDateCondition($period);

        $sql = "SELECT
                    COUNT(*) as total_deliveries,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_deliveries,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_deliveries,
                    AVG(CASE WHEN status = 'delivered' AND pickup_time IS NOT NULL AND delivery_time IS NOT NULL
                        THEN TIMESTAMPDIFF(MINUTE, pickup_time, delivery_time) END) as avg_delivery_time,
                    AVG(CASE WHEN status = 'delivered' THEN customer_rating END) as avg_rating,
                    SUM(distance_km) as total_distance
                FROM {$this->table}
                WHERE rider_id = ? $dateCondition";

        return $this->fetchOne($sql, [$riderId]) ?: [];
    }

    /**
     * Get delivery by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->fetchOne($sql, [$id]);
    }

    /**
     * Get date condition for queries
     */
    private function getDateCondition(string $period): string
    {
        switch ($period) {
            case 'today':
                return "AND DATE(created_at) = CURDATE()";
            case 'week':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '30days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return "";
        }
    }

    /**
     * Count active deliveries for a rider
     */
    public function countActiveDeliveriesByRider(int $riderId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE rider_id = ? AND status IN ('assigned', 'picked_up', 'on_the_way')";
            $result = $this->fetchOne($sql, [$riderId]);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error counting active deliveries: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get today's earnings for a rider
     */
    public function getTodayEarnings(int $riderId): float
    {
        try {
            $sql = "SELECT COALESCE(SUM(rider_earnings), 0) as earnings FROM {$this->table} 
                    WHERE rider_id = ? AND status = 'delivered' AND DATE(created_at) = CURDATE()";
            $result = $this->fetchOne($sql, [$riderId]);
            return (float)($result['earnings'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error getting today's earnings: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get today's delivery count for a rider
     */
    public function getTodayDeliveryCount(int $riderId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE rider_id = ? AND status = 'delivered' AND DATE(created_at) = CURDATE()";
            $result = $this->fetchOne($sql, [$riderId]);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error getting today's delivery count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get average rating for a rider
     */
    public function getAverageRating(int $riderId): float
    {
        try {
            $sql = "SELECT AVG(customer_rating) as rating FROM {$this->table} 
                    WHERE rider_id = ? AND customer_rating IS NOT NULL";
            $result = $this->fetchOne($sql, [$riderId]);
            return round((float)($result['rating'] ?? 0), 1);
        } catch (\Exception $e) {
            error_log("Error getting average rating: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get total delivery count for a rider
     */
    public function getTotalDeliveryCount(int $riderId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE rider_id = ? AND status = 'delivered'";
            $result = $this->fetchOne($sql, [$riderId]);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error getting total delivery count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get weekly earnings for a rider
     */
    public function getWeeklyEarnings(int $riderId): float
    {
        try {
            $sql = "SELECT COALESCE(SUM(rider_earnings), 0) as earnings FROM {$this->table} 
                    WHERE rider_id = ? AND status = 'delivered' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $result = $this->fetchOne($sql, [$riderId]);
            return (float)($result['earnings'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error getting weekly earnings: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get monthly earnings for a rider
     */
    public function getMonthlyEarnings(int $riderId): float
    {
        try {
            $sql = "SELECT COALESCE(SUM(rider_earnings), 0) as earnings FROM {$this->table} 
                    WHERE rider_id = ? AND status = 'delivered' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $result = $this->fetchOne($sql, [$riderId]);
            return (float)($result['earnings'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error getting monthly earnings: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get total earnings for a rider (all time)
     */
    public function getTotalEarnings(int $riderId): float
    {
        try {
            $sql = "SELECT COALESCE(SUM(rider_earnings), 0) as earnings FROM {$this->table} 
                    WHERE rider_id = ? AND status = 'delivered'";
            $result = $this->fetchOne($sql, [$riderId]);
            return (float)($result['earnings'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error getting total earnings: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get completion rate for a rider
     */
    public function getCompletionRate(int $riderId): float
    {
        try {
            $totalSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE rider_id = ?";
            $completedSql = "SELECT COUNT(*) as count FROM {$this->table} 
                            WHERE rider_id = ? AND status = 'delivered'";
            
            $total = $this->fetchOne($totalSql, [$riderId]);
            $completed = $this->fetchOne($completedSql, [$riderId]);
            
            $totalCount = (int)($total['count'] ?? 0);
            $completedCount = (int)($completed['count'] ?? 0);
            
            if ($totalCount === 0) {
                return 0.0;
            }
            
            return round(($completedCount / $totalCount) * 100, 1);
        } catch (\Exception $e) {
            error_log("Error getting completion rate: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get recent activity for a rider
     */
    public function getRecentActivityByRider(int $riderId, int $limit = 10): array
    {
        try {
            $sql = "SELECT d.*, o.order_number, o.total_amount, 
                           r.name as restaurant_name,
                           CASE 
                               WHEN d.status = 'delivered' THEN 'Completed delivery'
                               WHEN d.status = 'picked_up' THEN 'Picked up order'
                               WHEN d.status = 'assigned' THEN 'Assigned to delivery'
                               ELSE CONCAT('Status: ', d.status)
                           END as activity_description
                    FROM {$this->table} d
                    JOIN orders o ON d.order_id = o.id
                    JOIN restaurants r ON o.restaurant_id = r.id
                    WHERE d.rider_id = ?
                    ORDER BY d.updated_at DESC
                    LIMIT ?";
            return $this->fetchAll($sql, [$riderId, $limit]);
        } catch (\Exception $e) {
            error_log("Error getting recent activity: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get deliveries by rider with filtering and pagination
     */
    public function getDeliveriesByRider(int $riderId, string $status = 'all', int $limit = 20, int $offset = 0): array
    {
        try {
            $whereClause = "d.rider_id = ?";
            $params = [$riderId];
            
            if ($status !== 'all') {
                if ($status === 'active') {
                    $whereClause .= " AND d.status IN ('assigned', 'picked_up', 'on_the_way')";
                } elseif ($status === 'completed') {
                    $whereClause .= " AND d.status = 'delivered'";
                } else {
                    $whereClause .= " AND d.status = ?";
                    $params[] = $status;
                }
            }
            
            $sql = "SELECT d.*, o.order_number, o.total_amount,
                           r.name as restaurant_name, r.address as pickup_address,
                           CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                           o.delivery_address,
                           c.phone as customer_phone
                    FROM {$this->table} d
                    JOIN orders o ON d.order_id = o.id
                    JOIN restaurants r ON o.restaurant_id = r.id
                    JOIN users c ON o.customer_id = c.id
                    WHERE {$whereClause}
                    ORDER BY d.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            return $this->fetchAll($sql, $params);
        } catch (\Exception $e) {
            error_log("Error getting deliveries by rider: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count deliveries by rider with filtering
     */
    public function countDeliveriesByRider(int $riderId, string $status = 'all'): int
    {
        try {
            $whereClause = "rider_id = ?";
            $params = [$riderId];
            
            if ($status !== 'all') {
                if ($status === 'active') {
                    $whereClause .= " AND status IN ('assigned', 'picked_up', 'on_the_way')";
                } elseif ($status === 'completed') {
                    $whereClause .= " AND status = 'delivered'";
                } else {
                    $whereClause .= " AND status = ?";
                    $params[] = $status;
                }
            }
            
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
            $result = $this->fetchOne($sql, $params);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error counting deliveries by rider: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get delivery status counts for a rider
     */
    public function getDeliveryStatusCounts(int $riderId): array
    {
        try {
            $sql = "SELECT 
                        COUNT(CASE WHEN status IN ('assigned', 'picked_up', 'on_the_way') THEN 1 END) as active,
                        COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed,
                        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                        COUNT(*) as total
                    FROM {$this->table} 
                    WHERE rider_id = ?";
            
            $result = $this->fetchOne($sql, [$riderId]);
            return [
                'active' => (int)($result['active'] ?? 0),
                'completed' => (int)($result['completed'] ?? 0),
                'cancelled' => (int)($result['cancelled'] ?? 0),
                'total' => (int)($result['total'] ?? 0)
            ];
        } catch (\Exception $e) {
            error_log("Error getting delivery status counts: " . $e->getMessage());
            return ['active' => 0, 'completed' => 0, 'cancelled' => 0, 'total' => 0];
        }
    }

    /**
     * Get earnings by rider with pagination
     */
    public function getEarningsByRider(int $riderId, string $period = '7days', int $limit = 20, int $offset = 0): array
    {
        try {
            $dateCondition = $this->getDateCondition($period);
            
            $sql = "SELECT d.*, o.order_number, o.total_amount,
                           r.name as restaurant_name,
                           DATE(d.delivery_time) as delivery_date
                    FROM {$this->table} d
                    JOIN orders o ON d.order_id = o.id
                    JOIN restaurants r ON o.restaurant_id = r.id
                    WHERE d.rider_id = ? AND d.status = 'delivered' {$dateCondition}
                    ORDER BY d.delivery_time DESC
                    LIMIT ? OFFSET ?";
            
            return $this->fetchAll($sql, [$riderId, $limit, $offset]);
        } catch (\Exception $e) {
            error_log("Error getting earnings by rider: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get performance metrics for a rider
     */
    public function getPerformanceMetrics(int $riderId, string $period = '30days'): array
    {
        try {
            $dateCondition = $this->getDateCondition($period);
            
            $sql = "SELECT 
                        COUNT(*) as total_deliveries,
                        COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_deliveries,
                        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_deliveries,
                        AVG(CASE WHEN status = 'delivered' AND pickup_time IS NOT NULL AND delivery_time IS NOT NULL
                            THEN TIMESTAMPDIFF(MINUTE, pickup_time, delivery_time) END) as avg_delivery_time,
                        AVG(CASE WHEN status = 'delivered' THEN customer_rating END) as avg_rating,
                        SUM(CASE WHEN status = 'delivered' THEN delivery_fee ELSE 0 END) as total_earnings
                    FROM {$this->table}
                    WHERE rider_id = ? {$dateCondition}";
            
            $result = $this->fetchOne($sql, [$riderId]);
            
            $totalDeliveries = (int)($result['total_deliveries'] ?? 0);
            $completedDeliveries = (int)($result['completed_deliveries'] ?? 0);
            
            return [
                'total_deliveries' => $totalDeliveries,
                'completed_deliveries' => $completedDeliveries,
                'cancelled_deliveries' => (int)($result['cancelled_deliveries'] ?? 0),
                'success_rate' => $totalDeliveries > 0 ? round(($completedDeliveries / $totalDeliveries) * 100, 1) : 0,
                'avg_delivery_time' => round((float)($result['avg_delivery_time'] ?? 0), 1),
                'avg_rating' => round((float)($result['avg_rating'] ?? 0), 1),
                'total_earnings' => (float)($result['total_earnings'] ?? 0)
            ];
        } catch (\Exception $e) {
            error_log("Error getting performance metrics: " . $e->getMessage());
            return [
                'total_deliveries' => 0,
                'completed_deliveries' => 0,
                'cancelled_deliveries' => 0,
                'success_rate' => 0,
                'avg_delivery_time' => 0,
                'avg_rating' => 0,
                'total_earnings' => 0
            ];
        }
    }

    /**
     * Get ratings by rider for a period
     */
    public function getRatingsByRider(int $riderId, string $period = '30days'): array
    {
        try {
            $dateCondition = $this->getDateCondition($period);
            
            $sql = "SELECT customer_rating, COUNT(*) as count
                    FROM {$this->table}
                    WHERE rider_id = ? AND customer_rating IS NOT NULL {$dateCondition}
                    GROUP BY customer_rating
                    ORDER BY customer_rating DESC";
            
            $results = $this->fetchAll($sql, [$riderId]);
            
            $ratings = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            foreach ($results as $result) {
                $rating = (int)$result['customer_rating'];
                if ($rating >= 1 && $rating <= 5) {
                    $ratings[$rating] = (int)$result['count'];
                }
            }
            
            return $ratings;
        } catch (\Exception $e) {
            error_log("Error getting ratings by rider: " . $e->getMessage());
            return [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        }
    }

    /**
     * Get delivery time analytics for a rider
     */
    public function getDeliveryTimeAnalytics(int $riderId, string $period = '30days'): array
    {
        try {
            $dateCondition = $this->getDateCondition($period);
            
            $sql = "SELECT 
                        COUNT(CASE WHEN TIMESTAMPDIFF(MINUTE, pickup_time, delivery_time) <= 20 THEN 1 END) as fast,
                        COUNT(CASE WHEN TIMESTAMPDIFF(MINUTE, pickup_time, delivery_time) BETWEEN 21 AND 40 THEN 1 END) as normal,
                        COUNT(CASE WHEN TIMESTAMPDIFF(MINUTE, pickup_time, delivery_time) > 40 THEN 1 END) as slow
                    FROM {$this->table}
                    WHERE rider_id = ? AND status = 'delivered' 
                    AND pickup_time IS NOT NULL AND delivery_time IS NOT NULL {$dateCondition}";
            
            $result = $this->fetchOne($sql, [$riderId]);
            
            return [
                'fast' => (int)($result['fast'] ?? 0),
                'normal' => (int)($result['normal'] ?? 0),
                'slow' => (int)($result['slow'] ?? 0)
            ];
        } catch (\Exception $e) {
            error_log("Error getting delivery time analytics: " . $e->getMessage());
            return ['fast' => 0, 'normal' => 0, 'slow' => 0];
        }
    }

    /**
     * Get daily performance data for chart
     */
    public function getDailyPerformanceData(int $riderId, string $period = '7days'): array
    {
        try {
            $days = $period === '7days' ? 7 : 30;
            
            $sql = "SELECT 
                        DATE(created_at) as date,
                        COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed,
                        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as failed
                    FROM {$this->table}
                    WHERE rider_id = ? 
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            
            $results = $this->fetchAll($sql, [$riderId, $days]);
            
            // Create array with all dates in range
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $data[$date] = ['completed' => 0, 'failed' => 0];
            }
            
            // Fill in actual data
            foreach ($results as $result) {
                $date = $result['date'];
                if (isset($data[$date])) {
                    $data[$date] = [
                        'completed' => (int)$result['completed'],
                        'failed' => (int)$result['failed']
                    ];
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            error_log("Error getting daily performance data: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available balance for withdrawal
     */
    public function getAvailableBalance(int $riderId): float
    {
        try {
            // Get total earnings from delivered orders
            $sql = "SELECT COALESCE(SUM(delivery_fee), 0) as total_earnings
                    FROM {$this->table}
                    WHERE rider_id = ? AND status = 'delivered'";
            $result = $this->fetchOne($sql, [$riderId]);
            $totalEarnings = (float)($result['total_earnings'] ?? 0);
            
            // Get total withdrawn amount
            $sql = "SELECT COALESCE(SUM(amount), 0) as total_withdrawn
                    FROM withdrawals
                    WHERE user_id = ? AND withdrawal_type = 'rider' 
                    AND status IN ('approved', 'processing')";
            $result = $this->fetchOne($sql, [$riderId]);
            $totalWithdrawn = (float)($result['total_withdrawn'] ?? 0);
            
            return $totalEarnings - $totalWithdrawn;
        } catch (\Exception $e) {
            error_log("Error getting available balance: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get pending withdrawals
     */
    public function getPendingWithdrawals(int $riderId): float
    {
        try {
            $sql = "SELECT COALESCE(SUM(amount), 0) as pending
                    FROM withdrawals
                    WHERE user_id = ? AND withdrawal_type = 'rider' AND status = 'pending'";
            $result = $this->fetchOne($sql, [$riderId]);
            return (float)($result['pending'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error getting pending withdrawals: " . $e->getMessage());
            return 0;
        }
    }
}
