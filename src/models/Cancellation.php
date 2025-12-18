<?php

namespace Time2Eat\Models;

use core\Model;

class Cancellation extends Model
{
    protected $table = 'order_cancellations';
    protected $fillable = [
        'order_id', 'user_id', 'user_type', 'reason', 'details', 
        'status', 'reviewed_by', 'admin_notes', 'rejection_reason'
    ];

    /**
     * Get paginated cancellations with filters
     */
    public function getPaginated(int $page = 1, int $limit = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $whereClause = "WHERE 1=1";
        $params = ['limit' => $limit, 'offset' => $offset];

        // Apply filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $whereClause .= " AND c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['restaurant_id'])) {
            $whereClause .= " AND o.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        if (!empty($filters['user_type'])) {
            $whereClause .= " AND c.user_type = :user_type";
            $params['user_type'] = $filters['user_type'];
        }

        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(c.requested_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(c.requested_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql = "
            SELECT c.*, 
                   o.order_number, o.total_amount, o.status as order_status,
                   o.created_at as order_created_at,
                   r.name as restaurant_name,
                   u.first_name, u.last_name, u.email,
                   reviewer.first_name as reviewer_first_name,
                   reviewer.last_name as reviewer_last_name
            FROM {$this->table} c
            INNER JOIN orders o ON c.order_id = o.id
            INNER JOIN restaurants r ON o.restaurant_id = r.id
            INNER JOIN users u ON c.user_id = u.id
            LEFT JOIN users reviewer ON c.reviewed_by = reviewer.id
            {$whereClause}
            ORDER BY c.requested_at DESC
            LIMIT :limit OFFSET :offset
        ";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get cancellation statistics
     */
    public function getStats(array $filters = []): array
    {
        $whereClause = "WHERE 1=1";
        $params = [];

        // Apply same filters as getPaginated
        if (!empty($filters['restaurant_id'])) {
            $whereClause .= " AND o.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(c.requested_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(c.requested_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql = "
            SELECT 
                COUNT(*) as total_cancellations,
                COUNT(CASE WHEN c.status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN c.status = 'approved' THEN 1 END) as approved_count,
                COUNT(CASE WHEN c.status = 'rejected' THEN 1 END) as rejected_count,
                COUNT(CASE WHEN c.user_type = 'customer' THEN 1 END) as customer_initiated,
                COUNT(CASE WHEN c.user_type = 'vendor' THEN 1 END) as vendor_initiated,
                COUNT(CASE WHEN c.user_type = 'admin' THEN 1 END) as admin_initiated,
                AVG(TIMESTAMPDIFF(MINUTE, c.requested_at, c.reviewed_at)) as avg_review_time_minutes
            FROM {$this->table} c
            INNER JOIN orders o ON c.order_id = o.id
            {$whereClause}
        ";

        $stats = $this->db->query($sql, $params)->fetch();
        
        if (!$stats) {
            return [
                'total_cancellations' => 0,
                'pending_count' => 0,
                'approved_count' => 0,
                'rejected_count' => 0,
                'customer_initiated' => 0,
                'vendor_initiated' => 0,
                'admin_initiated' => 0,
                'avg_review_time_minutes' => 0
            ];
        }

        return $stats;
    }

    /**
     * Get cancellation by order ID
     */
    public function getByOrderId(int $orderId): ?array
    {
        $sql = "
            SELECT c.*, 
                   u.first_name, u.last_name, u.email,
                   reviewer.first_name as reviewer_first_name,
                   reviewer.last_name as reviewer_last_name
            FROM {$this->table} c
            INNER JOIN users u ON c.user_id = u.id
            LEFT JOIN users reviewer ON c.reviewed_by = reviewer.id
            WHERE c.order_id = :order_id
            ORDER BY c.requested_at DESC
            LIMIT 1
        ";

        $result = $this->db->query($sql, ['order_id' => $orderId])->fetch();
        return $result ?: null;
    }

    /**
     * Get cancellations by user
     */
    public function getByUser(int $userId, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "
            SELECT c.*, 
                   o.order_number, o.total_amount, o.status as order_status,
                   r.name as restaurant_name
            FROM {$this->table} c
            INNER JOIN orders o ON c.order_id = o.id
            INNER JOIN restaurants r ON o.restaurant_id = r.id
            WHERE c.user_id = :user_id
            ORDER BY c.requested_at DESC
            LIMIT :limit OFFSET :offset
        ";

        return $this->db->query($sql, [
            'user_id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ])->fetchAll();
    }

    /**
     * Get pending cancellations count
     */
    public function getPendingCount(array $filters = []): int
    {
        $whereClause = "WHERE c.status = 'pending'";
        $params = [];

        if (!empty($filters['restaurant_id'])) {
            $whereClause .= " AND o.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        $sql = "
            SELECT COUNT(*) as count
            FROM {$this->table} c
            INNER JOIN orders o ON c.order_id = o.id
            {$whereClause}
        ";

        $result = $this->db->query($sql, $params)->fetch();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get cancellation reasons analytics
     */
    public function getReasonAnalytics(array $filters = []): array
    {
        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($filters['restaurant_id'])) {
            $whereClause .= " AND o.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(c.requested_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(c.requested_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql = "
            SELECT 
                c.reason,
                COUNT(*) as count,
                COUNT(CASE WHEN c.status = 'approved' THEN 1 END) as approved_count,
                COUNT(CASE WHEN c.status = 'rejected' THEN 1 END) as rejected_count
            FROM {$this->table} c
            INNER JOIN orders o ON c.order_id = o.id
            {$whereClause}
            GROUP BY c.reason
            ORDER BY count DESC
        ";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get cancellation trends (daily/weekly/monthly)
     */
    public function getTrends(string $period = 'daily', int $days = 30, array $filters = []): array
    {
        $whereClause = "WHERE c.requested_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        $params = ['days' => $days];

        if (!empty($filters['restaurant_id'])) {
            $whereClause .= " AND o.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        $dateFormat = match($period) {
            'hourly' => '%Y-%m-%d %H:00:00',
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $sql = "
            SELECT 
                DATE_FORMAT(c.requested_at, '{$dateFormat}') as period,
                COUNT(*) as total_cancellations,
                COUNT(CASE WHEN c.status = 'approved' THEN 1 END) as approved_cancellations,
                COUNT(CASE WHEN c.status = 'rejected' THEN 1 END) as rejected_cancellations,
                COUNT(CASE WHEN c.user_type = 'customer' THEN 1 END) as customer_cancellations
            FROM {$this->table} c
            INNER JOIN orders o ON c.order_id = o.id
            {$whereClause}
            GROUP BY period
            ORDER BY period ASC
        ";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get recent cancellations for dashboard
     */
    public function getRecent(int $limit = 10, array $filters = []): array
    {
        $whereClause = "WHERE 1=1";
        $params = ['limit' => $limit];

        if (!empty($filters['restaurant_id'])) {
            $whereClause .= " AND o.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        $sql = "
            SELECT c.*, 
                   o.order_number, o.total_amount,
                   r.name as restaurant_name,
                   u.first_name, u.last_name
            FROM {$this->table} c
            INNER JOIN orders o ON c.order_id = o.id
            INNER JOIN restaurants r ON o.restaurant_id = r.id
            INNER JOIN users u ON c.user_id = u.id
            {$whereClause}
            ORDER BY c.requested_at DESC
            LIMIT :limit
        ";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get cancellation rate for a restaurant
     */
    public function getCancellationRate(int $restaurantId, int $days = 30): array
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT o.id) as total_orders,
                COUNT(DISTINCT c.order_id) as cancelled_orders,
                ROUND((COUNT(DISTINCT c.order_id) / COUNT(DISTINCT o.id)) * 100, 2) as cancellation_rate
            FROM orders o
            LEFT JOIN {$this->table} c ON o.id = c.order_id AND c.status = 'approved'
            WHERE o.restaurant_id = :restaurant_id
              AND o.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
        ";

        $result = $this->db->query($sql, [
            'restaurant_id' => $restaurantId,
            'days' => $days
        ])->fetch();

        return [
            'total_orders' => (int)($result['total_orders'] ?? 0),
            'cancelled_orders' => (int)($result['cancelled_orders'] ?? 0),
            'cancellation_rate' => (float)($result['cancellation_rate'] ?? 0)
        ];
    }

    /**
     * Auto-approve eligible cancellations
     */
    public function autoApproveEligible(): int
    {
        // Get pending cancellations that are eligible for auto-approval
        $sql = "
            SELECT c.id, c.order_id, o.status, o.created_at
            FROM {$this->table} c
            INNER JOIN orders o ON c.order_id = o.id
            WHERE c.status = 'pending'
              AND (
                  (o.status = 'pending' AND TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) <= 5) OR
                  (o.status = 'confirmed' AND TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) <= 5)
              )
        ";

        $eligibleCancellations = $this->db->query($sql)->fetchAll();
        $approvedCount = 0;

        foreach ($eligibleCancellations as $cancellation) {
            try {
                $this->update($cancellation['id'], [
                    'status' => 'approved',
                    'reviewed_by' => null, // System approval
                    'reviewed_at' => date('Y-m-d H:i:s'),
                    'admin_notes' => 'Auto-approved - eligible for immediate cancellation'
                ]);

                // Update order status
                $this->db->query(
                    "UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = :order_id",
                    ['order_id' => $cancellation['order_id']]
                );

                $approvedCount++;
            } catch (\Exception $e) {
                // Log error but continue with other cancellations
                error_log("Auto-approval failed for cancellation {$cancellation['id']}: " . $e->getMessage());
            }
        }

        return $approvedCount;
    }

    /**
     * Get cancellation summary for reporting
     */
    public function getSummary(array $filters = []): array
    {
        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($filters['restaurant_id'])) {
            $whereClause .= " AND o.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(c.requested_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(c.requested_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql = "
            SELECT 
                COUNT(*) as total_cancellations,
                COUNT(CASE WHEN c.status = 'approved' THEN 1 END) as approved_count,
                COUNT(CASE WHEN c.status = 'rejected' THEN 1 END) as rejected_count,
                COUNT(CASE WHEN c.status = 'pending' THEN 1 END) as pending_count,
                SUM(CASE WHEN c.status = 'approved' THEN o.total_amount ELSE 0 END) as total_refund_amount,
                AVG(CASE WHEN c.reviewed_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, c.requested_at, c.reviewed_at) END) as avg_review_time_minutes,
                COUNT(CASE WHEN c.user_type = 'customer' THEN 1 END) as customer_initiated,
                COUNT(CASE WHEN c.user_type = 'vendor' THEN 1 END) as vendor_initiated,
                COUNT(CASE WHEN c.user_type = 'admin' THEN 1 END) as admin_initiated
            FROM {$this->table} c
            INNER JOIN orders o ON c.order_id = o.id
            {$whereClause}
        ";

        $result = $this->db->query($sql, $params)->fetch();
        
        return $result ?: [
            'total_cancellations' => 0,
            'approved_count' => 0,
            'rejected_count' => 0,
            'pending_count' => 0,
            'total_refund_amount' => 0,
            'avg_review_time_minutes' => 0,
            'customer_initiated' => 0,
            'vendor_initiated' => 0,
            'admin_initiated' => 0
        ];
    }
}
