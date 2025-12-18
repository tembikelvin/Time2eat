<?php

declare(strict_types=1);

namespace Time2Eat\Models;

use core\Model;

/**
 * Analytics Model
 * Handles analytics data collection and reporting
 */
class Analytics extends Model
{
    protected $table = 'analytics';

    /**
     * Get user growth analytics
     */
    public function getUserGrowth(string $period): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as new_users,
                    role,
                    SUM(COUNT(*)) OVER (ORDER BY DATE(created_at)) as cumulative_users
                FROM users 
                WHERE deleted_at IS NULL {$dateCondition}
                GROUP BY DATE(created_at), role
                ORDER BY date ASC";

        return $this->fetchAll($sql);
    }

    /**
     * Get order trends
     */
    public function getOrderTrends(string $period): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    DATE(o.created_at) as date,
                    COUNT(*) as total_orders,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.total_amount) as avg_order_value,
                    COUNT(CASE WHEN o.status = 'delivered' THEN 1 END) as completed_orders,
                    COUNT(CASE WHEN o.status = 'cancelled' THEN 1 END) as cancelled_orders
                FROM orders o
                WHERE 1=1 {$dateCondition}
                GROUP BY DATE(o.created_at)
                ORDER BY date ASC";

        return $this->fetchAll($sql);
    }

    /**
     * Get revenue growth
     */
    public function getRevenueGrowth(string $period): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    DATE(o.created_at) as date,
                    SUM(o.total_amount) as revenue,
                    SUM(o.delivery_fee) as delivery_revenue,
                    SUM(o.total_amount * 0.15) as commission_revenue,
                    COUNT(*) as order_count
                FROM orders o
                WHERE o.status = 'delivered' {$dateCondition}
                GROUP BY DATE(o.created_at)
                ORDER BY date ASC";

        return $this->fetchAll($sql);
    }

    /**
     * Get revenue by source
     */
    public function getRevenueBySource(string $period): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    COALESCE(o.source, 'direct') as source,
                    SUM(o.total_amount) as revenue,
                    COUNT(*) as order_count,
                    AVG(o.total_amount) as avg_order_value
                FROM orders o
                WHERE o.status = 'delivered' {$dateCondition}
                GROUP BY COALESCE(o.source, 'direct')
                ORDER BY revenue DESC";

        return $this->fetchAll($sql);
    }

    /**
     * Get commission earnings
     */
    public function getCommissionEarnings(string $period): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    DATE(o.created_at) as date,
                    SUM(o.total_amount * 0.15) as commission_earned,
                    SUM(o.delivery_fee) as delivery_fees,
                    SUM(o.total_amount * 0.15 + o.delivery_fee) as total_platform_revenue
                FROM orders o
                WHERE o.status = 'delivered' {$dateCondition}
                GROUP BY DATE(o.created_at)
                ORDER BY date ASC";

        return $this->fetchAll($sql);
    }

    /**
     * Get top restaurants
     */
    public function getTopRestaurants(string $period, int $limit = 10): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    r.id,
                    r.name,
                    r.image,
                    COUNT(o.id) as total_orders,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.total_amount) as avg_order_value,
                    AVG(r.rating) as avg_rating,
                    COUNT(DISTINCT o.customer_id) as unique_customers
                FROM restaurants r
                LEFT JOIN orders o ON r.id = o.restaurant_id AND o.status = 'delivered' {$dateCondition}
                WHERE r.deleted_at IS NULL
                GROUP BY r.id, r.name, r.image
                ORDER BY total_revenue DESC
                LIMIT ?";

        return $this->fetchAll($sql, [$limit]);
    }

    /**
     * Get top customers
     */
    public function getTopCustomers(string $period, int $limit = 10): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    COUNT(o.id) as total_orders,
                    SUM(o.total_amount) as total_spent,
                    AVG(o.total_amount) as avg_order_value,
                    MAX(o.created_at) as last_order_date
                FROM users u
                INNER JOIN orders o ON u.id = o.customer_id
                WHERE u.role = 'customer' AND o.status = 'delivered' {$dateCondition}
                GROUP BY u.id, u.first_name, u.last_name, u.email
                ORDER BY total_spent DESC
                LIMIT ?";

        return $this->fetchAll($sql, [$limit]);
    }

    /**
     * Get orders by location
     */
    public function getOrdersByLocation(string $period): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    SUBSTRING_INDEX(o.delivery_address, ',', -1) as area,
                    COUNT(*) as order_count,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.delivery_time) as avg_delivery_time
                FROM orders o
                WHERE o.status = 'delivered' {$dateCondition}
                GROUP BY area
                ORDER BY order_count DESC";

        return $this->fetchAll($sql);
    }

    /**
     * Get delivery heatmap data
     */
    public function getDeliveryHeatmap(string $period): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    o.delivery_latitude as lat,
                    o.delivery_longitude as lng,
                    COUNT(*) as intensity
                FROM orders o
                WHERE o.status = 'delivered' 
                AND o.delivery_latitude IS NOT NULL 
                AND o.delivery_longitude IS NOT NULL
                {$dateCondition}
                GROUP BY o.delivery_latitude, o.delivery_longitude
                ORDER BY intensity DESC";

        return $this->fetchAll($sql);
    }

    /**
     * Get popular areas
     */
    public function getPopularAreas(string $period): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    TRIM(SUBSTRING_INDEX(o.delivery_address, ',', -1)) as area,
                    COUNT(*) as order_count,
                    COUNT(DISTINCT o.customer_id) as unique_customers,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.delivery_time) as avg_delivery_time
                FROM orders o
                WHERE o.status = 'delivered' {$dateCondition}
                GROUP BY area
                HAVING order_count >= 5
                ORDER BY order_count DESC
                LIMIT 20";

        return $this->fetchAll($sql);
    }

    /**
     * Get conversion rate
     */
    public function getConversionRate(string $period): float
    {
        $dateCondition = $this->getDateCondition($period);
        
        $sql = "SELECT 
                    COUNT(DISTINCT CASE WHEN o.id IS NOT NULL THEN u.id END) as customers_with_orders,
                    COUNT(DISTINCT u.id) as total_customers
                FROM users u
                LEFT JOIN orders o ON u.id = o.customer_id
                WHERE u.role = 'customer' AND u.deleted_at IS NULL {$dateCondition}";

        $result = $this->fetchOne($sql);
        
        if (!$result || $result['total_customers'] == 0) {
            return 0.0;
        }
        
        return round(($result['customers_with_orders'] / $result['total_customers']) * 100, 2);
    }

    /**
     * Get user retention
     */
    public function getUserRetention(string $period): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(u.created_at, '%Y-%m') as cohort_month,
                    COUNT(DISTINCT u.id) as cohort_size,
                    COUNT(DISTINCT CASE WHEN o.created_at >= DATE_ADD(u.created_at, INTERVAL 1 MONTH) THEN u.id END) as retained_month_1,
                    COUNT(DISTINCT CASE WHEN o.created_at >= DATE_ADD(u.created_at, INTERVAL 3 MONTH) THEN u.id END) as retained_month_3,
                    COUNT(DISTINCT CASE WHEN o.created_at >= DATE_ADD(u.created_at, INTERVAL 6 MONTH) THEN u.id END) as retained_month_6
                FROM users u
                LEFT JOIN orders o ON u.id = o.customer_id
                WHERE u.role = 'customer' 
                AND u.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(u.created_at, '%Y-%m')
                ORDER BY cohort_month DESC";

        return $this->fetchAll($sql);
    }

    /**
     * Get peak hours
     */
    public function getPeakHours(string $period): array
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT 
                    HOUR(o.created_at) as hour,
                    COUNT(*) as order_count,
                    SUM(o.total_amount) as revenue,
                    AVG(o.total_amount) as avg_order_value
                FROM orders o
                WHERE o.status = 'delivered' {$dateCondition}
                GROUP BY HOUR(o.created_at)
                ORDER BY hour ASC";

        return $this->fetchAll($sql);
    }

    /**
     * Get average delivery time
     */
    public function getAverageDeliveryTime(string $period): float
    {
        $dateCondition = $this->getDateCondition($period, 'o');
        
        $sql = "SELECT AVG(o.delivery_time) as avg_delivery_time
                FROM orders o
                WHERE o.status = 'delivered' 
                AND o.delivery_time IS NOT NULL {$dateCondition}";

        $result = $this->fetchOne($sql);
        return round((float)($result['avg_delivery_time'] ?? 0), 2);
    }

    /**
     * Get API response times
     */
    public function getApiResponseTimes(string $period): array
    {
        // This would require API logging implementation
        // For now, return mock data
        return [
            'avg_response_time' => 120,
            'p95_response_time' => 250,
            'p99_response_time' => 500,
            'slowest_endpoints' => [
                '/api/orders' => 180,
                '/api/restaurants' => 150,
                '/api/menu-items' => 120
            ]
        ];
    }

    /**
     * Get error rates
     */
    public function getErrorRates(string $period): array
    {
        // This would require error logging implementation
        // For now, return mock data
        return [
            'total_requests' => 10000,
            'error_count' => 45,
            'error_rate' => 0.45,
            'error_types' => [
                '500' => 20,
                '404' => 15,
                '400' => 10
            ]
        ];
    }

    /**
     * Get uptime
     */
    public function getUptime(string $period): float
    {
        // This would require uptime monitoring implementation
        // For now, return mock data
        return 99.8;
    }

    /**
     * Log admin action
     */
    public function logAction(array $data): bool
    {
        $sql = "INSERT INTO logs (user_id, action, details, ip_address, user_agent, timestamp) 
                VALUES (?, ?, ?, ?, ?, ?)";

        return $this->execute($sql, [
            $data['user_id'],
            $data['action'],
            $data['details'],
            $data['ip_address'],
            $data['user_agent'],
            $data['timestamp']
        ]) > 0;
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(int $limit): array
    {
        $sql = "SELECT l.*, u.first_name, u.last_name, u.email
                FROM logs l
                LEFT JOIN users u ON l.user_id = u.id
                ORDER BY l.timestamp DESC
                LIMIT ?";

        return $this->fetchAll($sql, [$limit]);
    }

    /**
     * Get pending payouts
     */
    public function getPendingPayouts(): array
    {
        $sql = "SELECT 
                    'restaurant' as type,
                    r.name as entity_name,
                    SUM(o.total_amount * 0.85) as amount_due,
                    COUNT(*) as order_count
                FROM orders o
                JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.status = 'delivered' 
                AND o.payout_status = 'pending'
                GROUP BY r.id, r.name
                
                UNION ALL
                
                SELECT 
                    'rider' as type,
                    CONCAT(u.first_name, ' ', u.last_name) as entity_name,
                    SUM(d.rider_fee) as amount_due,
                    COUNT(*) as delivery_count
                FROM deliveries d
                JOIN users u ON d.rider_id = u.id
                WHERE d.status = 'delivered' 
                AND d.payout_status = 'pending'
                GROUP BY u.id, u.first_name, u.last_name
                
                ORDER BY amount_due DESC";

        return $this->fetchAll($sql);
    }

    /**
     * Get date condition for queries
     */
    private function getDateCondition(string $period, string $alias = ''): string
    {
        $prefix = $alias ? $alias . '.' : '';
        
        switch ($period) {
            case 'today':
                return "AND DATE({$prefix}created_at) = CURDATE()";
            case 'yesterday':
                return "AND DATE({$prefix}created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            case 'week':
            case '7days':
                return "AND {$prefix}created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
            case '30days':
                return "AND {$prefix}created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90days':
                return "AND {$prefix}created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case 'year':
                return "AND {$prefix}created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "";
        }
    }
}
