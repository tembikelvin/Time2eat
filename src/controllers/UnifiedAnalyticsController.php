<?php

namespace controllers;

require_once __DIR__ . '/AdminBaseController.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Analytics.php';

use controllers\AdminBaseController;
use models\Order;
use Time2Eat\Models\Analytics;

class UnifiedAnalyticsController extends AdminBaseController
{
    private $orderModel;
    private $analyticsModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->analyticsModel = new Analytics();
    }

    /**
     * Unified Analytics Dashboard
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $user = $this->getCurrentUser();
        $period = $_GET['period'] ?? '30days';

        try {
            // Get comprehensive analytics data
            $analyticsData = $this->getUnifiedAnalyticsData($period);

            $this->renderDashboard('admin/analytics', [
                'title' => 'Unified Analytics Dashboard - Time2Eat',
                'user' => $user,
                'userRole' => 'admin',
                'analyticsData' => $analyticsData,
                'currentPeriod' => $period,
                'currentPage' => 'analytics'
            ]);

        } catch (\Exception $e) {
            $errorDetails = $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
            error_log("Error loading unified analytics: " . $errorDetails);
            
            $this->renderDashboard('admin/analytics', [
                'title' => 'Unified Analytics Dashboard - Time2Eat',
                'user' => $user,
                'userRole' => 'admin',
                'analyticsData' => $this->getEmptyAnalyticsData(),
                'currentPeriod' => $period,
                'currentPage' => 'analytics',
                'error' => 'Failed to load analytics data: ' . $errorDetails
            ]);
        }
    }

    /**
     * Get unified analytics data for all dashboards
     */
    private function getUnifiedAnalyticsData(string $period): array
    {
        // Test database connection first
        try {
            $db = $this->getDb();
            if (!$db) {
                throw new \Exception("Database connection is null");
            }
            
            // Test a simple query
            $testResult = $this->fetchOne("SELECT 1 as test");
            if (!$testResult) {
                throw new \Exception("Database query test failed");
            }
        } catch (\Exception $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
        
        $dateCondition = $this->getDateCondition($period);
        $dateConditionOrders = $this->getDateCondition($period, 'o');
        $dateConditionOrdersDirect = $this->getDateCondition($period, ''); // For direct orders table queries
        
        // Platform Overview
        $platformStats = $this->getPlatformStats($dateCondition, $dateConditionOrders, $dateConditionOrdersDirect);
        
        // Order Analytics
        $orderAnalytics = $this->getOrderAnalytics($dateConditionOrdersDirect);
        
        // Revenue Analytics
        $revenueAnalytics = $this->getRevenueAnalytics($dateCondition, $dateConditionOrdersDirect, $period);
        
        // User Analytics
        $userAnalytics = $this->getUserAnalytics($dateCondition);
        
        // Restaurant Performance
        $restaurantPerformance = $this->getRestaurantPerformance($dateCondition, $dateConditionOrders);
        
        // Rider Performance
        $riderPerformance = $this->getRiderPerformance($dateCondition, $dateConditionOrders);
        
        // Geographic Analytics
        $geographicData = $this->getGeographicAnalytics($dateCondition);
        
        // Time-based Analytics
        $timeAnalytics = $this->getTimeAnalytics($dateConditionOrdersDirect);

        return [
            'platform' => $platformStats,
            'orders' => $orderAnalytics,
            'revenue' => $revenueAnalytics,
            'users' => $userAnalytics,
            'restaurants' => $restaurantPerformance,
            'riders' => $riderPerformance,
            'geographic' => $geographicData,
            'time' => $timeAnalytics,
            'period' => $period
        ];
    }

    /**
     * Get platform overview statistics
     */
    private function getPlatformStats(string $dateCondition, string $dateConditionOrders, string $dateConditionOrdersDirect): array
    {
        // Total platform metrics
        $totalStats = $this->fetchOne("
            SELECT 
                (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total_users,
                (SELECT COUNT(*) FROM restaurants WHERE deleted_at IS NULL) as total_restaurants,
                (SELECT COUNT(*) FROM orders) as total_orders,
                (SELECT COUNT(*) FROM menu_items WHERE deleted_at IS NULL) as total_menu_items,
                (SELECT SUM(total_amount) FROM orders WHERE status = 'delivered') as total_revenue
        ");

        // Period-specific metrics - simplified approach
        $ordersDateCondition = str_replace('AND ', '', $dateConditionOrders);
        
        // Get active users by role
        $activeUsers = $this->fetchOne("
            SELECT 
                COUNT(DISTINCT CASE WHEN u.role = 'customer' THEN u.id END) as active_customers,
                COUNT(DISTINCT CASE WHEN u.role = 'vendor' THEN u.id END) as active_vendors,
                COUNT(DISTINCT CASE WHEN u.role = 'rider' THEN u.id END) as active_riders
            FROM users u
            WHERE u.deleted_at IS NULL {$dateCondition}
        ");
        
        // Get order metrics separately
        $orderMetrics = $this->fetchOne("
            SELECT 
                COUNT(*) as period_orders,
                SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as period_revenue,
                AVG(CASE WHEN status = 'delivered' THEN total_amount END) as avg_order_value
            FROM orders 
            WHERE 1=1 {$dateConditionOrdersDirect}
        ");
        
        $periodStats = array_merge($activeUsers ?? [], $orderMetrics ?? []);

        // Growth metrics
        $growthStats = $this->fetchOne("
            SELECT 
                COUNT(DISTINCT CASE WHEN u.role = 'customer' THEN u.id END) as new_customers,
                COUNT(DISTINCT CASE WHEN u.role = 'vendor' THEN u.id END) as new_vendors,
                COUNT(DISTINCT CASE WHEN u.role = 'rider' THEN u.id END) as new_riders
            FROM users u
            WHERE u.deleted_at IS NULL {$dateCondition}
        ");

        return array_merge($totalStats ?? [], $periodStats ?? [], $growthStats ?? []);
    }

    /**
     * Get order analytics
     */
    private function getOrderAnalytics(string $dateConditionOrdersDirect): array
    {
        // Order status breakdown
        $statusBreakdown = $this->fetchAll("
            SELECT 
                status,
                COUNT(*) as count,
                SUM(total_amount) as revenue
            FROM orders 
            WHERE 1=1 {$dateConditionOrdersDirect}
            GROUP BY status
            ORDER BY count DESC
        ");

        // Order trends by day
        $dailyTrends = $this->fetchAll("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as orders,
                SUM(total_amount) as revenue,
                AVG(total_amount) as avg_value
            FROM orders 
            WHERE 1=1 {$dateConditionOrdersDirect}
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");

        // Peak hours analysis
        $hourlyAnalysis = $this->fetchAll("
            SELECT 
                HOUR(created_at) as hour,
                COUNT(*) as orders,
                AVG(total_amount) as avg_value
            FROM orders 
            WHERE 1=1 {$dateConditionOrdersDirect}
            GROUP BY HOUR(created_at)
            ORDER BY hour ASC
        ");

        return [
            'status_breakdown' => $statusBreakdown,
            'daily_trends' => $dailyTrends,
            'hourly_analysis' => $hourlyAnalysis
        ];
    }

    /**
     * Get revenue analytics
     */
    private function getRevenueAnalytics(string $dateCondition, string $dateConditionOrdersDirect, string $period): array
    {
        // Revenue breakdown
        $revenueBreakdown = $this->fetchOne("
            SELECT 
                SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'delivered' THEN delivery_fee ELSE 0 END) as delivery_revenue,
                SUM(CASE WHEN status = 'delivered' THEN (total_amount - delivery_fee) ELSE 0 END) as food_revenue,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders,
                AVG(CASE WHEN status = 'delivered' THEN total_amount END) as avg_order_value
            FROM orders 
            WHERE 1=1 {$dateConditionOrdersDirect}
        ");

        // Commission analytics - use dynamic date condition
        $joinDateCondition = $this->getJoinDateCondition($period);
        
        // Debug: Log the join condition
        error_log("Join date condition: " . $joinDateCondition);
        
        $commissionData = $this->fetchAll("
            SELECT 
                r.name as restaurant_name,
                r.commission_rate,
                COUNT(o.id) as orders,
                SUM(CASE WHEN o.status = 'delivered' THEN o.total_amount ELSE 0 END) as revenue,
                SUM(CASE WHEN o.status = 'delivered' THEN (o.total_amount * r.commission_rate / 100) ELSE 0 END) as commission
            FROM restaurants r
            LEFT JOIN orders o ON r.id = o.restaurant_id AND {$joinDateCondition}
            WHERE r.deleted_at IS NULL
            GROUP BY r.id, r.name, r.commission_rate
            HAVING orders > 0
            ORDER BY commission DESC
            LIMIT 10
        ");

        return array_merge($revenueBreakdown ?? [], ['commission_data' => $commissionData]);
    }

    /**
     * Get user analytics
     */
    private function getUserAnalytics(string $dateCondition): array
    {
        // User growth by role
        $userGrowth = $this->fetchAll("
            SELECT 
                role,
                COUNT(*) as count,
                DATE(created_at) as date
            FROM users 
            WHERE 1=1 {$dateCondition}
            GROUP BY role, DATE(created_at)
            ORDER BY date ASC
        ");

        // User activity metrics
        $activityMetrics = $this->fetchOne("
            SELECT 
                COUNT(DISTINCT CASE WHEN last_login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN id END) as weekly_active,
                COUNT(DISTINCT CASE WHEN last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN id END) as monthly_active,
                COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN id END) as new_users_30d
            FROM users 
            WHERE deleted_at IS NULL
        ");

        return [
            'growth' => $userGrowth,
            'activity' => $activityMetrics ?? []
        ];
    }

    /**
     * Get restaurant performance analytics
     */
    private function getRestaurantPerformance(string $dateCondition, string $dateConditionOrders): array
    {
        // Get the period from the date condition to create proper JOIN condition
        $period = $this->extractPeriodFromDateCondition($dateCondition);
        $joinDateCondition = $this->getJoinDateCondition($period);
        
        return $this->fetchAll("
            SELECT 
                r.id,
                r.name,
                r.cuisine_type,
                r.rating,
                COUNT(o.id) as total_orders,
                SUM(CASE WHEN o.status = 'delivered' THEN o.total_amount ELSE 0 END) as revenue,
                AVG(CASE WHEN o.status = 'delivered' THEN o.total_amount END) as avg_order_value,
                COUNT(DISTINCT o.customer_id) as unique_customers,
                AVG(CASE 
                    WHEN o.status = 'delivered' AND o.actual_delivery_time IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, o.created_at, o.actual_delivery_time) 
                END) as avg_delivery_time
            FROM restaurants r
            LEFT JOIN orders o ON r.id = o.restaurant_id AND {$joinDateCondition}
            WHERE r.deleted_at IS NULL
            GROUP BY r.id, r.name, r.cuisine_type, r.rating
            HAVING total_orders > 0
            ORDER BY revenue DESC
            LIMIT 20
        ");
    }

    /**
     * Get rider performance analytics
     */
    private function getRiderPerformance(string $dateCondition, string $dateConditionOrders): array
    {
        // Get the period from the date condition to create proper JOIN condition
        $period = $this->extractPeriodFromDateCondition($dateCondition);
        $joinDateCondition = $this->getJoinDateCondition($period);
        
        return $this->fetchAll("
            SELECT 
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as name,
                COUNT(o.id) as total_deliveries,
                SUM(CASE WHEN o.status = 'delivered' THEN o.delivery_fee ELSE 0 END) as total_earnings,
                AVG(CASE 
                    WHEN o.status = 'delivered' AND o.actual_delivery_time IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, o.created_at, o.actual_delivery_time) 
                END) as avg_delivery_time,
                COUNT(DISTINCT DATE(o.created_at)) as active_days
            FROM users u
            LEFT JOIN orders o ON u.id = o.rider_id AND {$joinDateCondition}
            WHERE u.role = 'rider' AND u.deleted_at IS NULL
            GROUP BY u.id, u.first_name, u.last_name
            HAVING total_deliveries > 0
            ORDER BY total_earnings DESC
            LIMIT 20
        ");
    }

    /**
     * Get geographic analytics
     */
    private function getGeographicAnalytics(string $dateCondition): array
    {
        // This would require proper address/location data
        // For now, return mock data structure
        return [
            'top_areas' => [],
            'delivery_zones' => [],
            'coverage_map' => []
        ];
    }

    /**
     * Get time-based analytics
     */
    private function getTimeAnalytics(string $dateConditionOrdersDirect): array
    {
        // Peak times analysis
        $peakTimes = $this->fetchAll("
            SELECT 
                HOUR(created_at) as hour,
                DAYNAME(created_at) as day_name,
                COUNT(*) as orders
            FROM orders 
            WHERE 1=1 {$dateConditionOrdersDirect}
            GROUP BY HOUR(created_at), DAYNAME(created_at)
            ORDER BY orders DESC
        ");

        return [
            'peak_times' => $peakTimes
        ];
    }

    /**
     * Get date condition for SQL queries
     */
    private function getDateCondition(string $period, string $tableAlias = ''): string
    {
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        switch($period) {
            case 'today':
                return "AND DATE({$prefix}created_at) = CURDATE()";
            case 'week':
                return "AND {$prefix}created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30days':
                return "AND {$prefix}created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90days':
                return "AND {$prefix}created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case 'year':
                return "AND {$prefix}created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "AND {$prefix}created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }

    /**
     * Get date condition for JOIN clauses (without AND prefix)
     */
    private function getJoinDateCondition(string $period): string
    {
        switch($period) {
            case 'today':
                return "DATE(o.created_at) = CURDATE()";
            case 'week':
                return "o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30days':
                return "o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90days':
                return "o.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case 'year':
                return "o.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }

    /**
     * Extract period from date condition string
     */
    private function extractPeriodFromDateCondition(string $dateCondition): string
    {
        if (strpos($dateCondition, 'CURDATE()') !== false) {
            return 'today';
        } elseif (strpos($dateCondition, 'INTERVAL 7 DAY') !== false) {
            return 'week';
        } elseif (strpos($dateCondition, 'INTERVAL 30 DAY') !== false) {
            return '30days';
        } elseif (strpos($dateCondition, 'INTERVAL 90 DAY') !== false) {
            return '90days';
        } elseif (strpos($dateCondition, 'INTERVAL 1 YEAR') !== false) {
            return 'year';
        }
        return '30days'; // default
    }

    /**
     * Export analytics data as Excel
     */
    public function export(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $period = $_GET['period'] ?? '30days';

        try {
            $analyticsData = $this->getUnifiedAnalyticsData($period);

            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="unified_analytics_' . $period . '_' . date('Y-m-d') . '.xls"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Generate Excel content
            echo $this->generateExcelContent($analyticsData);

        } catch (\Exception $e) {
            error_log("Error exporting analytics: " . $e->getMessage());
            header('Content-Type: text/plain');
            echo "Error exporting analytics data.";
        }
    }

    /**
     * Generate Excel content for analytics export
     */
    private function generateExcelContent(array $data): string
    {
        $excel = "<!DOCTYPE html><html><head><meta charset='utf-8'></head><body>";

        // Platform Overview
        $excel .= "<h2>Platform Overview</h2>";
        $excel .= "<table border='1'>";
        $excel .= "<tr><th>Metric</th><th>Value</th></tr>";
        $excel .= "<tr><td>Total Revenue</td><td>" . number_format($data['platform']['total_revenue'] ?? 0) . " XAF</td></tr>";
        $excel .= "<tr><td>Total Orders</td><td>" . number_format($data['platform']['total_orders'] ?? 0) . "</td></tr>";
        $excel .= "<tr><td>Total Users</td><td>" . number_format($data['platform']['total_users'] ?? 0) . "</td></tr>";
        $excel .= "<tr><td>Total Restaurants</td><td>" . number_format($data['platform']['total_restaurants'] ?? 0) . "</td></tr>";
        $excel .= "<tr><td>Average Order Value</td><td>" . number_format($data['platform']['avg_order_value'] ?? 0) . " XAF</td></tr>";
        $excel .= "</table><br>";

        // Top Restaurants
        $excel .= "<h2>Top Performing Restaurants</h2>";
        $excel .= "<table border='1'>";
        $excel .= "<tr><th>Restaurant</th><th>Cuisine</th><th>Orders</th><th>Revenue</th><th>Avg Order Value</th></tr>";
        foreach ($data['restaurants'] as $restaurant) {
            $excel .= "<tr>";
            $excel .= "<td>" . htmlspecialchars($restaurant['name']) . "</td>";
            $excel .= "<td>" . htmlspecialchars($restaurant['cuisine_type']) . "</td>";
            $excel .= "<td>" . number_format($restaurant['total_orders']) . "</td>";
            $excel .= "<td>" . number_format($restaurant['revenue']) . " XAF</td>";
            $excel .= "<td>" . number_format($restaurant['avg_order_value']) . " XAF</td>";
            $excel .= "</tr>";
        }
        $excel .= "</table><br>";

        // Top Riders
        $excel .= "<h2>Top Performing Riders</h2>";
        $excel .= "<table border='1'>";
        $excel .= "<tr><th>Rider</th><th>Deliveries</th><th>Earnings</th><th>Avg Delivery Time</th><th>Active Days</th></tr>";
        foreach ($data['riders'] as $rider) {
            $excel .= "<tr>";
            $excel .= "<td>" . htmlspecialchars($rider['name']) . "</td>";
            $excel .= "<td>" . number_format($rider['total_deliveries']) . "</td>";
            $excel .= "<td>" . number_format($rider['total_earnings']) . " XAF</td>";
            $excel .= "<td>" . number_format($rider['avg_delivery_time']) . " min</td>";
            $excel .= "<td>" . number_format($rider['active_days']) . "</td>";
            $excel .= "</tr>";
        }
        $excel .= "</table><br>";

        // Order Status Breakdown
        $excel .= "<h2>Order Status Distribution</h2>";
        $excel .= "<table border='1'>";
        $excel .= "<tr><th>Status</th><th>Count</th><th>Revenue</th></tr>";
        foreach ($data['orders']['status_breakdown'] as $status) {
            $excel .= "<tr>";
            $excel .= "<td>" . htmlspecialchars($status['status']) . "</td>";
            $excel .= "<td>" . number_format($status['count']) . "</td>";
            $excel .= "<td>" . number_format($status['revenue']) . " XAF</td>";
            $excel .= "</tr>";
        }
        $excel .= "</table>";

        $excel .= "</body></html>";

        return $excel;
    }

    /**
     * Get empty analytics data structure
     */
    private function getEmptyAnalyticsData(): array
    {
        return [
            'platform' => [],
            'orders' => ['status_breakdown' => [], 'daily_trends' => [], 'hourly_analysis' => []],
            'revenue' => ['commission_data' => []],
            'users' => ['growth' => [], 'activity' => []],
            'restaurants' => [],
            'riders' => [],
            'geographic' => ['top_areas' => [], 'delivery_zones' => [], 'coverage_map' => []],
            'time' => ['peak_times' => []],
            'period' => '30days'
        ];
    }
}
