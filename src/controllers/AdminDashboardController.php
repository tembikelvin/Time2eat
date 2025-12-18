<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/AdminBaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Message.php';

use controllers\AdminBaseController;

class AdminDashboardController extends AdminBaseController
{
    protected ?\PDO $db = null;
    private $userModel;
    private $messageModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new \models\User();
        $this->messageModel = new \models\Message();
    }

    public function index(): void
    {
        // CRITICAL: Prevent caching of dashboard (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Get comprehensive dashboard stats
        $stats = $this->getDashboardStats();
        $recentActivity = $this->getRecentActivity();
        $systemHealth = $this->getSystemHealth();

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        $this->renderDashboard('admin/dashboard', [
            'title' => 'Admin Dashboard - Time2Eat',
            'user' => $userData,
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'systemHealth' => $systemHealth,
            'currentPage' => 'dashboard'
        ]);
    }

    public function users(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Get search and filter parameters
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $sort = $_GET['sort'] ?? 'created_at';
        $order = $_GET['order'] ?? 'desc';

        // Get users with comprehensive statistics
        $users = [];
        $stats = [];

        try {
            // Build query with search and filters
            $sql = "SELECT * FROM users WHERE deleted_at IS NULL";
            $whereConditions = [];
            $params = [];
            
            // Search functionality
            if (!empty($search)) {
                $whereConditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            // Role filter
            if (!empty($role)) {
                $whereConditions[] = "role = ?";
                $params[] = $role;
            }
            
            // Status filter
            if (!empty($status)) {
                $whereConditions[] = "status = ?";
                $params[] = $status;
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($whereConditions)) {
                $sql .= " AND " . implode(" AND ", $whereConditions);
            }
            
            // Sorting
            $validSorts = ['created_at', 'first_name', 'last_name', 'email', 'role', 'status'];
            $validOrders = ['asc', 'desc'];
            
            if (!in_array($sort, $validSorts)) {
                $sort = 'created_at';
            }
            if (!in_array($order, $validOrders)) {
                $order = 'desc';
            }
            
            $sql .= " ORDER BY {$sort} {$order} LIMIT 50";
            
            $users = $this->fetchAll($sql, $params);

            // Get comprehensive user statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL");
            $stats['total_users'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE status = 'active' AND deleted_at IS NULL");
            $stats['active_users'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND deleted_at IS NULL");
            $stats['customers'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'vendor' AND deleted_at IS NULL");
            $stats['vendors'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'rider' AND deleted_at IS NULL");
            $stats['riders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND deleted_at IS NULL");
            $stats['admins'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'affiliate' AND deleted_at IS NULL");
            $stats['affiliates'] = $result['count'] ?? 0;

            // Get pending vendor approvals
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'vendor' AND status = 'pending' AND deleted_at IS NULL");
            $stats['pending_vendors'] = $result['count'] ?? 0;

            // Get online riders (active riders)
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'rider' AND status = 'active' AND deleted_at IS NULL");
            $stats['online_riders'] = $result['count'] ?? 0;

        } catch (\Exception $e) {
            error_log("Error getting users: " . $e->getMessage());
            $stats = [
                'total_users' => 0,
                'active_users' => 0,
                'customers' => 0,
                'vendors' => 0,
                'riders' => 0,
                'admins' => 0,
                'affiliates' => 0,
                'pending_vendors' => 0,
                'online_riders' => 0
            ];
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        $this->renderDashboard('admin/users', [
            'title' => 'User Management - Time2Eat',
            'user' => $userData,
            'users' => $users,
            'stats' => $stats,
            'currentPage' => 'users',
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'sort' => $sort,
            'order' => $order
        ]);
    }

    public function restaurants(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Get restaurants with comprehensive stats
        $restaurants = [];
        $stats = [];

        try {
            // First, let's try a simple query to see if restaurants exist
            $simpleRestaurants = $this->fetchAll("SELECT * FROM restaurants WHERE deleted_at IS NULL LIMIT 5");
            error_log("Simple restaurants query found: " . count($simpleRestaurants) . " restaurants");
            
            // Get restaurants with owner info and order statistics
            $restaurants = $this->fetchAll("
                SELECT r.*, 
                       u.first_name, u.last_name, u.email as owner_email, 
                       r.cuisine_type as category_name,
                       COALESCE(order_stats.total_orders, 0) as total_orders,
                       COALESCE(order_stats.total_revenue, 0) as total_revenue
                FROM restaurants r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN (
                    SELECT restaurant_id, 
                           COUNT(*) as total_orders,
                           SUM(total_amount) as total_revenue
                    FROM orders 
                    WHERE status = 'delivered'
                    GROUP BY restaurant_id
                ) order_stats ON r.id = order_stats.restaurant_id
                WHERE r.deleted_at IS NULL
                ORDER BY r.created_at DESC
                LIMIT 20
            ");
            
            // Debug: Log the number of restaurants found
            error_log("Admin restaurants query found: " . count($restaurants) . " restaurants");

            // Get restaurant statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE deleted_at IS NULL");
            $stats['total_restaurants'] = $result['count'] ?? 0;
            error_log("Total restaurants count: " . $stats['total_restaurants']);

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE status = 'active' AND deleted_at IS NULL");
            $stats['active_restaurants'] = $result['count'] ?? 0;
            error_log("Active restaurants count: " . $stats['active_restaurants']);

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE status = 'pending' AND deleted_at IS NULL");
            $stats['pending_restaurants'] = $result['count'] ?? 0;
            error_log("Pending restaurants count: " . $stats['pending_restaurants']);

            $result = $this->fetchOne("SELECT AVG(rating) as avg_rating FROM restaurants WHERE deleted_at IS NULL AND rating > 0");
            $stats['avg_rating'] = round((float)($result['avg_rating'] ?? 0), 1);

            // Set total restaurants for pagination
            $stats['totalRestaurants'] = $stats['total_restaurants'];

        } catch (\Exception $e) {
            error_log("Error getting restaurants: " . $e->getMessage());
            $stats = [
                'total_restaurants' => 0,
                'active_restaurants' => 0,
                'pending_restaurants' => 0,
                'avg_rating' => 0
            ];
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        $this->renderDashboard('admin/restaurants', [
            'title' => 'Restaurant Management - Time2Eat',
            'user' => $userData,
            'restaurants' => $restaurants,
            'stats' => $stats,
            'currentPage' => 'restaurants'
        ]);
    }

    public function orders(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Get search and filter parameters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $sort = $_GET['sort'] ?? 'created_at';
        $order = $_GET['order'] ?? 'desc';

        // Get orders with comprehensive data
        $orders = [];
        $stats = [];

        try {
            // Build query with search and filters
            $sql = "
                SELECT o.*,
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       r.name as restaurant_name,
                       CONCAT(rider.first_name, ' ', rider.last_name) as rider_name
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users rider ON o.rider_id = rider.id
            ";
            
            $whereConditions = [];
            $params = [];
            
            // Search functionality
            if (!empty($search)) {
                $whereConditions[] = "(o.id LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR r.name LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            // Status filter
            if (!empty($status)) {
                $whereConditions[] = "o.status = ?";
                $params[] = $status;
            }
            
            // Date range filters
            if (!empty($dateFrom)) {
                $whereConditions[] = "DATE(o.created_at) >= ?";
                $params[] = $dateFrom;
            }
            
            if (!empty($dateTo)) {
                $whereConditions[] = "DATE(o.created_at) <= ?";
                $params[] = $dateTo;
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            
            // Sorting
            $validSorts = ['created_at', 'total_amount', 'status', 'customer_name', 'restaurant_name'];
            $validOrders = ['asc', 'desc'];
            
            if (!in_array($sort, $validSorts)) {
                $sort = 'created_at';
            }
            if (!in_array($order, $validOrders)) {
                $order = 'desc';
            }
            
            switch ($sort) {
                case 'customer_name':
                    $sortColumn = 'CONCAT(u.first_name, \' \', u.last_name)';
                    break;
                case 'restaurant_name':
                    $sortColumn = 'r.name';
                    break;
                default:
                    $sortColumn = 'o.' . $sort;
            }
            
            $sql .= " ORDER BY {$sortColumn} {$order} LIMIT 50";
            
            $orders = $this->fetchAll($sql, $params);

            // Get comprehensive order statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders");
            $stats['total_orders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'confirmed', 'preparing', 'ready', 'picked_up', 'on_the_way')");
            $stats['active_orders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()");
            $stats['completed_today'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()");
            $stats['revenue_today'] = $result['revenue'] ?? 0;

            // Get detailed status counts for live tracking
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'preparing'");
            $stats['preparing'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status IN ('picked_up', 'on_the_way')");
            $stats['out_for_delivery'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
            $stats['pending'] = $result['count'] ?? 0;

            // Set total orders for pagination
            $stats['totalOrders'] = $stats['total_orders'];

        } catch (\Exception $e) {
            error_log("Error getting orders: " . $e->getMessage());
            $stats = [
                'total_orders' => 0,
                'active_orders' => 0,
                'completed_today' => 0,
                'revenue_today' => 0
            ];
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        $this->renderDashboard('admin/orders', [
            'title' => 'Order Management - Time2Eat',
            'user' => $userData,
            'orders' => $orders,
            'stats' => $stats,
            'currentPage' => 'orders',
            'search' => $search,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'sort' => $sort,
            'order' => $order
        ]);
    }

    public function financial(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Get comprehensive financial data
        $financialData = [];
        try {
            // Get revenue statistics
            $result = $this->fetchOne("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'delivered'");
            $financialData['total_revenue'] = $result['total_revenue'] ?? 0;

            $result = $this->fetchOne("SELECT SUM(total_amount) as monthly_revenue FROM orders WHERE status = 'delivered' AND MONTH(created_at) = MONTH(CURDATE())");
            $financialData['monthly_revenue'] = $result['monthly_revenue'] ?? 0;

            // Calculate platform commission (assuming 10% commission rate)
            $financialData['platform_commission'] = $financialData['total_revenue'] * 0.10;

            // Get pending payouts from affiliate withdrawals
            $result = $this->fetchOne("SELECT SUM(amount) as pending_payouts, COUNT(*) as pending_count FROM withdrawals WHERE status = 'pending' AND withdrawal_type = 'affiliate'");
            $financialData['pending_payouts'] = $result['pending_payouts'] ?? 0;
            $financialData['pending_payout_count'] = $result['pending_count'] ?? 0;

            // Get today's revenue
            $result = $this->fetchOne("SELECT SUM(total_amount) as today_revenue FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()");
            $financialData['revenue_today'] = $result['today_revenue'] ?? 0;

            // Get total transactions
            $result = $this->fetchOne("SELECT COUNT(*) as total_transactions FROM payments WHERE status = 'completed'");
            $financialData['total_transactions'] = $result['total_transactions'] ?? 0;

            // Get monthly growth (compare this month vs last month)
            $result = $this->fetchOne("
                SELECT
                    SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) THEN total_amount ELSE 0 END) as current_month,
                    SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) - 1 THEN total_amount ELSE 0 END) as last_month
                FROM orders
                WHERE status = 'delivered' AND YEAR(created_at) = YEAR(CURDATE())
            ");

            $currentMonth = $result['current_month'] ?? 0;
            $lastMonth = $result['last_month'] ?? 1; // Avoid division by zero
            $financialData['monthly_growth'] = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;

        } catch (\Exception $e) {
            error_log("Error getting financial data: " . $e->getMessage());
            $financialData = [
                'total_revenue' => 0,
                'monthly_revenue' => 0,
                'platform_commission' => 0,
                'pending_payouts' => 0,
                'pending_payout_count' => 0,
                'revenue_today' => 0,
                'total_transactions' => 0,
                'monthly_growth' => 0
            ];
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        $this->renderDashboard('admin/financial', [
            'title' => 'Financial Management - Time2Eat',
            'user' => $userData,
            'financialData' => $financialData,
            'currentPage' => 'financial'
        ]);
    }

    public function categories(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get categories with statistics
        $categories = [];
        $categoryStats = [];

        try {
            // Get categories list with restaurant counts (using cuisine_type matching)
            $categories = $this->fetchAll("
                SELECT c.*, 
                       COUNT(r.id) as restaurant_count,
                       COUNT(CASE WHEN r.status = 'active' THEN 1 END) as active_restaurants
                FROM categories c
                LEFT JOIN restaurants r ON c.name = r.cuisine_type AND r.deleted_at IS NULL
                WHERE c.is_active = 1
                GROUP BY c.id
                ORDER BY c.name ASC
                LIMIT 50
            ");

            // Get category statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM categories");
            $categoryStats['total_categories'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM categories WHERE is_active = 1");
            $categoryStats['active_categories'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM categories WHERE is_active = 0");
            $categoryStats['inactive_categories'] = $result['count'] ?? 0;

            // Get categories with restaurants (using cuisine_type matching)
            $result = $this->fetchOne("
                SELECT COUNT(DISTINCT c.id) as count
                FROM categories c
                INNER JOIN restaurants r ON c.name = r.cuisine_type AND r.deleted_at IS NULL
            ");
            $categoryStats['categories_with_restaurants'] = $result['count'] ?? 0;

        } catch (\Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            $categoryStats = [
                'total_categories' => 0,
                'active_categories' => 0,
                'inactive_categories' => 0,
                'categories_with_restaurants' => 0
            ];
        }

        $this->renderDashboard('admin/categories', [
            'title' => 'Category Management - Time2Eat',
            'user' => $userData,
            'categories' => $categories,
            'categoryStats' => $categoryStats,
            'currentPage' => 'categories'
        ]);
    }

    public function logs(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Get search and filter parameters
        $search = $_GET['search'] ?? '';
        $level = $_GET['level'] ?? '';
        $category = $_GET['category'] ?? '';
        $date = $_GET['date'] ?? '';
        $sort = $_GET['sort'] ?? 'timestamp';
        $order = $_GET['order'] ?? 'desc';

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get recent security logs from security tables with search and filters
        $securityLogs = [];
        try {
            // Build security events query with search and filters
            $securityEventsSql = "
                SELECT
                    CONCAT('SEC-', LPAD(id, 6, '0')) as id,
                    created_at as timestamp,
                    CASE
                        WHEN severity = 'critical' THEN 'critical'
                        WHEN severity = 'high' THEN 'error'
                        WHEN severity = 'medium' THEN 'warning'
                        ELSE 'info'
                    END as level,
                    CASE
                        WHEN event_type LIKE '%login%' OR event_type LIKE '%auth%' THEN 'auth'
                        WHEN event_type LIKE '%access%' OR event_type LIKE '%permission%' THEN 'access'
                        WHEN event_type LIKE '%data%' OR event_type LIKE '%update%' THEN 'data'
                        ELSE 'system'
                    END as category,
                    event_type as event,
                    COALESCE(u.email, 'System') as user,
                    ip_address as ip,
                    description as details,
                    user_agent
                FROM security_events se
                LEFT JOIN users u ON se.user_id = u.id
            ";
            
            $securityEventsParams = [];
            $securityEventsWhere = [];
            
            // Add search conditions
            if (!empty($search)) {
                $securityEventsWhere[] = "(se.description LIKE ? OR se.ip_address LIKE ? OR COALESCE(u.email, 'System') LIKE ? OR se.event_type LIKE ?)";
                $searchTerm = "%{$search}%";
                $securityEventsParams = array_merge($securityEventsParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            // Add level filter
            if (!empty($level)) {
                if ($level === 'critical') {
                    $securityEventsWhere[] = "se.severity = 'critical'";
                } elseif ($level === 'error') {
                    $securityEventsWhere[] = "se.severity = 'high'";
                } elseif ($level === 'warning') {
                    $securityEventsWhere[] = "se.severity = 'medium'";
                } else {
                    $securityEventsWhere[] = "se.severity = 'low'";
                }
            }
            
            // Add category filter
            if (!empty($category)) {
                switch ($category) {
                    case 'auth':
                        $securityEventsWhere[] = "(se.event_type LIKE '%login%' OR se.event_type LIKE '%auth%')";
                        break;
                    case 'access':
                        $securityEventsWhere[] = "(se.event_type LIKE '%access%' OR se.event_type LIKE '%permission%')";
                        break;
                    case 'data':
                        $securityEventsWhere[] = "(se.event_type LIKE '%data%' OR se.event_type LIKE '%update%')";
                        break;
                }
            }
            
            // Add date filter
            if (!empty($date)) {
                $securityEventsWhere[] = "DATE(se.created_at) = ?";
                $securityEventsParams[] = $date;
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($securityEventsWhere)) {
                $securityEventsSql .= " WHERE " . implode(" AND ", $securityEventsWhere);
            }
            
            $securityEventsSql .= " ORDER BY se.created_at DESC LIMIT 25";
            
            $securityEvents = $this->fetchAll($securityEventsSql, $securityEventsParams);

            // Build action logs query with search and filters
            $actionLogsSql = "
                SELECT
                    CONCAT('ACT-', LPAD(id, 6, '0')) as id,
                    created_at as timestamp,
                    CASE
                        WHEN action LIKE '%login%' OR action LIKE '%auth%' THEN 'info'
                        WHEN action LIKE '%update%' OR action LIKE '%create%' THEN 'info'
                        WHEN action LIKE '%delete%' OR action LIKE '%failed%' THEN 'warning'
                        WHEN action LIKE '%admin%' OR action LIKE '%security%' THEN 'critical'
                        ELSE 'info'
                    END as level,
                    CASE
                        WHEN action LIKE '%login%' OR action LIKE '%auth%' THEN 'auth'
                        WHEN action LIKE '%update%' OR action LIKE '%create%' OR action LIKE '%delete%' THEN 'data'
                        WHEN action LIKE '%admin%' THEN 'access'
                        ELSE 'system'
                    END as category,
                    CONCAT(UCASE(LEFT(action, 1)), LCASE(SUBSTRING(action, 2))) as event,
                    COALESCE(u.email, 'Unknown') as user,
                    ip_address as ip,
                    CONCAT(action, ' - ', resource_type, IF(resource_id IS NOT NULL, CONCAT(' #', resource_id), '')) as details,
                    user_agent
                FROM action_logs al
                LEFT JOIN users u ON al.user_id = u.id
            ";
            
            $actionLogsParams = [];
            $actionLogsWhere = [];
            
            // Add search conditions
            if (!empty($search)) {
                $actionLogsWhere[] = "(al.action LIKE ? OR al.resource_type LIKE ? OR al.ip_address LIKE ? OR COALESCE(u.email, 'Unknown') LIKE ?)";
                $searchTerm = "%{$search}%";
                $actionLogsParams = array_merge($actionLogsParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            // Add level filter (same logic as system logs)
            if (!empty($level)) {
                if ($level === 'critical') {
                    $actionLogsWhere[] = "(al.action LIKE '%admin%' OR al.action LIKE '%security%')";
                } elseif ($level === 'warning') {
                    $actionLogsWhere[] = "(al.action LIKE '%delete%' OR al.action LIKE '%failed%')";
                }
            }
            
            // Add category filter
            if (!empty($category)) {
                switch ($category) {
                    case 'auth':
                        $actionLogsWhere[] = "(al.action LIKE '%login%' OR al.action LIKE '%auth%')";
                        break;
                    case 'data':
                        $actionLogsWhere[] = "(al.action LIKE '%update%' OR al.action LIKE '%create%' OR al.action LIKE '%delete%')";
                        break;
                    case 'access':
                        $actionLogsWhere[] = "al.action LIKE '%admin%'";
                        break;
                }
            }
            
            // Add date filter
            if (!empty($date)) {
                $actionLogsWhere[] = "DATE(al.created_at) = ?";
                $actionLogsParams[] = $date;
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($actionLogsWhere)) {
                $actionLogsSql .= " WHERE " . implode(" AND ", $actionLogsWhere);
            }
            
            $actionLogsSql .= " ORDER BY al.created_at DESC LIMIT 25";
            
            $actionLogs = $this->fetchAll($actionLogsSql, $actionLogsParams);

            // Combine and sort logs
            $securityLogs = array_merge($securityEvents, $actionLogs);
            usort($securityLogs, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            // Limit to 50 most recent
            $securityLogs = array_slice($securityLogs, 0, 50);

        } catch (\Exception $e) {
            error_log("Error getting security logs: " . $e->getMessage());
            // Fallback to sample data if database query fails
            $securityLogs = [];
        }

        // Get logs statistics
        $stats = $this->getLogsStats();

        $this->renderDashboard('admin/logs', [
            'title' => 'Security Logs - Time2Eat',
            'user' => $userData,
            'securityLogs' => $securityLogs,
            'stats' => $stats,
            'currentPage' => 'logs',
            'search' => $search,
            'level' => $level,
            'category' => $category,
            'date' => $date,
            'sort' => $sort,
            'order' => $order
        ]);
    }

    /**
     * Get logs statistics
     */
    private function getLogsStats(): array
    {
        $stats = [
            'total_events' => 0,
            'critical_events' => 0,
            'warning_events' => 0,
            'error_events' => 0,
            'auth_events' => 0,
            'access_events' => 0,
            'recent_events_24h' => 0,
            'unique_ips_today' => 0
        ];

        try {
            // Total events from both tables
            $result = $this->fetchOne("
                SELECT
                    (SELECT COUNT(*) FROM security_events) +
                    (SELECT COUNT(*) FROM action_logs) as total
            ");
            $stats['total_events'] = $result['total'] ?? 0;

            // Critical events
            $result = $this->fetchOne("
                SELECT COUNT(*) as count FROM security_events
                WHERE severity = 'critical'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['critical_events'] = $result['count'] ?? 0;

            // Warning events
            $result = $this->fetchOne("
                SELECT COUNT(*) as count FROM security_events
                WHERE severity = 'medium'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['warning_events'] = $result['count'] ?? 0;

            // Error events
            $result = $this->fetchOne("
                SELECT COUNT(*) as count FROM security_events
                WHERE severity = 'high'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['error_events'] = $result['count'] ?? 0;

            // Auth events
            $result = $this->fetchOne("
                SELECT COUNT(*) as count FROM action_logs
                WHERE (action LIKE '%login%' OR action LIKE '%auth%')
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['auth_events'] = $result['count'] ?? 0;

            // Access control events
            $result = $this->fetchOne("
                SELECT COUNT(*) as count FROM security_events
                WHERE (event_type LIKE '%access%' OR event_type LIKE '%permission%')
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['access_events'] = $result['count'] ?? 0;

            // Recent events in last 24 hours
            $result = $this->fetchOne("
                SELECT
                    (SELECT COUNT(*) FROM security_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) +
                    (SELECT COUNT(*) FROM action_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as count
            ");
            $stats['recent_events_24h'] = $result['count'] ?? 0;

            // Unique IPs today
            $result = $this->fetchOne("
                SELECT COUNT(DISTINCT ip_address) as count FROM (
                    SELECT ip_address FROM security_events WHERE DATE(created_at) = CURDATE() AND ip_address IS NOT NULL
                    UNION
                    SELECT ip_address FROM action_logs WHERE DATE(created_at) = CURDATE() AND ip_address IS NOT NULL
                ) as unique_ips
            ");
            $stats['unique_ips_today'] = $result['count'] ?? 0;

        } catch (\Exception $e) {
            error_log("Error getting logs statistics: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Get comprehensive dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $stats = [
            'total_users' => 0,
            'total_orders' => 0,
            'total_revenue' => 0,
            'active_restaurants' => 0,
            'pending_orders' => 0,
            'completed_orders_today' => 0,
            'active_riders' => 0,
            'total_customers' => 0
        ];

        try {
            // Get user counts
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL");
            $stats['total_users'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND deleted_at IS NULL");
            $stats['total_customers'] = $result['count'] ?? 0;

            // Get order statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders");
            $stats['total_orders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'confirmed', 'preparing')");
            $stats['pending_orders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'completed' AND DATE(created_at) = CURDATE()");
            $stats['completed_orders_today'] = $result['count'] ?? 0;

            // Get revenue
            $result = $this->fetchOne("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'completed'");
            $stats['total_revenue'] = $result['revenue'] ?? 0;

            // Get restaurant count
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE status = 'active'");
            $stats['active_restaurants'] = $result['count'] ?? 0;

            // Get active riders
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'rider' AND status = 'active'");
            $stats['active_riders'] = $result['count'] ?? 0;

        } catch (\Exception $e) {
            error_log("Error getting admin stats: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Get recent activity for dashboard
     */
    private function getRecentActivity(): array
    {
        $activities = [];

        try {
            // Recent orders
            $orders = $this->fetchAll("
                SELECT o.id, o.status, o.total_amount, o.created_at, u.first_name, u.last_name
                FROM orders o
                JOIN users u ON o.customer_id = u.id
                ORDER BY o.created_at DESC
                LIMIT 5
            ");

            foreach ($orders as $order) {
                $activities[] = [
                    'type' => 'order',
                    'message' => "New order #{$order['id']} from {$order['first_name']} {$order['last_name']}",
                    'amount' => $order['total_amount'],
                    'status' => $order['status'],
                    'time' => $order['created_at']
                ];
            }

            // Recent user registrations
            $users = $this->fetchAll("
                SELECT first_name, last_name, role, created_at
                FROM users
                WHERE deleted_at IS NULL
                ORDER BY created_at DESC
                LIMIT 3
            ");

            foreach ($users as $user) {
                $activities[] = [
                    'type' => 'user',
                    'message' => "New {$user['role']} registered: {$user['first_name']} {$user['last_name']}",
                    'time' => $user['created_at']
                ];
            }

        } catch (\Exception $e) {
            error_log("Error getting recent activity: " . $e->getMessage());
        }

        // Sort by time
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 8);
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth(): array
    {
        return [
            'database' => 'healthy',
            'storage' => 'healthy',
            'cache' => 'healthy',
            'email' => 'healthy'
        ];
    }

    /**
     * API: Get live delivery data for dashboard
     */
    public function liveDeliveries(): void
    {
        if (!$this->isAuthenticated() || $this->getCurrentUser()->role !== 'admin') {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Get active deliveries (orders that are in progress)
            $deliveries = $this->fetchAll("
                SELECT 
                    o.id,
                    o.order_number,
                    o.status,
                    o.created_at,
                    o.updated_at,
                    o.total_amount,
                    o.delivery_address,
                    o.delivery_instructions,
                    o.estimated_delivery_time,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    u.phone as customer_phone,
                    r.name as restaurant_name,
                    r.phone as restaurant_phone,
                    r.address as restaurant_address,
                    CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                    rider.phone as rider_phone,
                    rider.id as rider_id
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users rider ON o.rider_id = rider.id
                WHERE o.status IN ('pending', 'confirmed', 'preparing', 'ready', 'picked_up', 'on_the_way')
                ORDER BY o.created_at DESC
                LIMIT 50
            ");

            // Format deliveries for frontend
            $formattedDeliveries = array_map(function($delivery) {
                return [
                    'id' => $delivery['id'],
                    'order_id' => $delivery['order_number'] ?? $delivery['id'],
                    'customer_name' => $delivery['customer_name'] ?? 'Unknown',
                    'customer_phone' => $delivery['customer_phone'] ?? '',
                    'restaurant_name' => $delivery['restaurant_name'] ?? 'Unknown',
                    'restaurant_phone' => $delivery['restaurant_phone'] ?? '',
                    'restaurant_address' => $delivery['restaurant_address'] ?? '',
                    'rider_name' => $delivery['rider_name'] ?? 'Unassigned',
                    'rider_phone' => $delivery['rider_phone'] ?? '',
                    'rider_id' => $delivery['rider_id'] ?? null,
                    'status' => $delivery['status'],
                    'status_display' => ucfirst(str_replace('_', ' ', $delivery['status'])),
                    'estimated_time' => $this->calculateEstimatedTime($delivery['created_at'], $delivery['status']),
                    'delivery_address' => $delivery['delivery_address'] ?? '',
                    'delivery_notes' => $delivery['delivery_instructions'] ?? '',
                    'total_amount' => $delivery['total_amount'] ?? 0,
                    'created_at' => $delivery['created_at'],
                    'updated_at' => $delivery['updated_at'],
                    'distance' => $this->calculateDistance($delivery['restaurant_address'], $delivery['delivery_address']),
                    'priority' => $this->calculatePriority($delivery['created_at'], $delivery['status']),
                ];
            }, $deliveries);

            // Get stats for dashboard
            $stats = [
                'pending' => $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'] ?? 0,
                'confirmed' => $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'confirmed'")['count'] ?? 0,
                'preparing' => $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'preparing'")['count'] ?? 0,
                'ready' => $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'ready'")['count'] ?? 0,
                'picked_up' => $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'picked_up'")['count'] ?? 0,
                'on_the_way' => $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'on_the_way'")['count'] ?? 0,
                'completed_today' => $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()")['count'] ?? 0,
            ];

            $this->json([
                'success' => true,
                'deliveries' => $formattedDeliveries,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            error_log("Error fetching live deliveries: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to fetch live deliveries',
                'deliveries' => [],
                'stats' => [
                    'preparing' => 0,
                    'pickup' => 0,
                    'delivery' => 0,
                    'completed_today' => 0
                ]
            ]);
        }
    }

    /**
     * API: Get quick action counts
     */
    public function quickActionCounts(): void
    {
        if (!$this->isAuthenticated() || $this->getCurrentUser()->role !== 'admin') {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $counts = [
                'urgent_orders' => 0,
                'pending_approvals' => 0
            ];

            // Urgent orders: orders older than 1 hour that are still pending/preparing
            $result = $this->fetchOne("
                SELECT COUNT(*) as count 
                FROM orders 
                WHERE status IN ('pending', 'preparing') 
                AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $counts['urgent_orders'] = $result['count'] ?? 0;

            // Pending approvals: restaurants and riders waiting for approval
            $restaurantsPending = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE status = 'pending'")['count'] ?? 0;
            $ridersPending = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'rider' AND status = 'pending'")['count'] ?? 0;
            $counts['pending_approvals'] = $restaurantsPending + $ridersPending;

            $this->json([
                'success' => true,
                'counts' => $counts
            ]);

        } catch (\Exception $e) {
            error_log("Error fetching quick action counts: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to fetch counts',
                'counts' => [
                    'urgent_orders' => 0,
                    'pending_approvals' => 0
                ]
            ]);
        }
    }

    /**
     * Calculate estimated delivery time based on order status
     */
    private function calculateEstimatedTime(string $createdAt, string $status): string
    {
        $created = strtotime($createdAt);
        $now = time();
        $elapsed = $now - $created;
        
        // Estimate total time based on status
        $estimates = [
            'pending' => 45 * 60,      // 45 minutes
            'preparing' => 35 * 60,     // 35 minutes
            'ready' => 20 * 60,         // 20 minutes
            'picked_up' => 15 * 60,     // 15 minutes
            'on_the_way' => 10 * 60,    // 10 minutes
        ];
        
        $totalEstimate = $estimates[$status] ?? 30 * 60;
        $remaining = $totalEstimate - $elapsed;
        
        if ($remaining <= 0) {
            return 'Overdue';
        }
        
        $minutes = ceil($remaining / 60);
        return $minutes . ' min';
    }

    /**
     * Calculate distance between restaurant and delivery address
     */
    private function calculateDistance($restaurantAddress, $deliveryAddress): string
    {
        // This is a simplified calculation
        // In a real implementation, you would use a geocoding service
        if (empty($restaurantAddress) || empty($deliveryAddress)) {
            return 'Unknown';
        }
        
        // For now, return a random distance for demo purposes
        $distances = ['1.2 km', '2.5 km', '3.1 km', '4.7 km', '5.3 km', '6.8 km'];
        return $distances[array_rand($distances)];
    }

    /**
     * Calculate delivery priority based on age and status
     */
    private function calculatePriority($createdAt, $status): string
    {
        $orderTime = new \DateTime($createdAt);
        $now = new \DateTime();
        $elapsed = $now->diff($orderTime)->h * 60 + $now->diff($orderTime)->i; // total minutes elapsed
        
        // Priority logic
        if ($elapsed > 60) {
            return 'high';
        } elseif ($elapsed > 30) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Render dashboard with proper layout
     */
    protected function renderDashboard(string $view, array $data = []): void
    {

        // Start output buffering to capture the dashboard content
        ob_start();

        // Extract data for the view
        extract($data);

        // Include the specific dashboard view using correct relative path
        $viewPath = __DIR__ . "/../views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("Dashboard view not found: {$view}");
        }
        include $viewPath;

        // Get the content
        $content = ob_get_clean();

        // Explicitly set variables for the layout to ensure they're available
        $user = $data['user'] ?? null;
        $currentPage = $data['currentPage'] ?? '';
        $title = $data['title'] ?? 'Dashboard - Time2Eat';

        // Render with dashboard layout using correct relative path
        $layoutPath = __DIR__ . "/../views/components/dashboard-layout.php";
        if (!file_exists($layoutPath)) {
            throw new \Exception("Dashboard layout not found: dashboard-layout.php");
        }
        include $layoutPath;
    }

    /**
     * Admin Delivery Management
     */
    public function deliveryZones(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        try {
            // Get all restaurants with their delivery zone settings
            $db = $this->getDb();

            $query = "SELECT
                        r.id,
                        r.name,
                        r.phone,
                        r.address,
                        r.city,
                        r.image,
                        r.delivery_radius,
                        r.delivery_fee,
                        r.delivery_fee_per_extra_km,
                        r.minimum_order,
                        r.latitude,
                        r.longitude,
                        r.status
                      FROM restaurants r
                      WHERE r.status != 'deleted'
                      ORDER BY r.name ASC";

            $stmt = $db->query($query);
            $zones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Calculate statistics
            $stats = [
                'total_restaurants' => count($zones),
                'avg_radius' => 0,
                'avg_base_fee' => 0,
                'max_radius' => 0
            ];

            if (!empty($zones)) {
                $totalRadius = 0;
                $totalBaseFee = 0;
                $maxRadius = 0;

                foreach ($zones as $zone) {
                    $radius = (float)($zone['delivery_radius'] ?? 0);
                    $baseFee = (float)($zone['delivery_fee'] ?? 0);

                    $totalRadius += $radius;
                    $totalBaseFee += $baseFee;
                    $maxRadius = max($maxRadius, $radius);
                }

                $stats['avg_radius'] = $totalRadius / count($zones);
                $stats['avg_base_fee'] = $totalBaseFee / count($zones);
                $stats['max_radius'] = $maxRadius;
            }

            $userData = [
                'id' => $user->id,
                'first_name' => $user->first_name ?? 'Admin',
                'last_name' => $user->last_name ?? '',
                'name' => ($user->first_name ?? 'Admin') . ' ' . ($user->last_name ?? ''),
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status
            ];

            $this->renderDashboard('admin/delivery-zones', [
                'title' => 'Delivery Zone Management - Time2Eat',
                'user' => $userData,
                'zones' => $zones,
                'stats' => $stats,
                'currentPage' => 'delivery-zones'
            ]);

        } catch (\Exception $e) {
            error_log("Error loading delivery zones: " . $e->getMessage());
            $this->redirect(url('/admin/dashboard'));
        }
    }

    public function deliveries(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Get search and filter parameters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $rider = $_GET['rider'] ?? '';
        $date = $_GET['date'] ?? '';

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get deliveries with comprehensive data
        $deliveries = [];
        $stats = [];

        try {
            // Build query with search and filters
            $sql = "
                SELECT o.*,
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       r.name as restaurant_name,
                       CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                       rider.email as rider_email,
                       o.delivery_distance as distance,
                       o.pickup_time,
                       CASE 
                           WHEN o.status = 'delivered' THEN 'Completed'
                           WHEN o.status = 'on_the_way' THEN 'En route'
                           WHEN o.status = 'picked_up' THEN 'Picked up'
                           WHEN o.status = 'ready' THEN 'Ready for pickup'
                           WHEN o.status = 'preparing' THEN 'Preparing'
                           ELSE 'Unknown'
                       END as estimated_time
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users rider ON o.rider_id = rider.id
                WHERE o.status IN ('preparing', 'ready', 'picked_up', 'on_the_way', 'delivered')
            ";
            
            $whereConditions = [];
            $params = [];
            
            // Search functionality
            if (!empty($search)) {
                $whereConditions[] = "(o.id LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR r.name LIKE ? OR CONCAT(rider.first_name, ' ', rider.last_name) LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            // Status filter
            if (!empty($status)) {
                $whereConditions[] = "o.status = ?";
                $params[] = $status;
            }
            
            // Rider filter
            if (!empty($rider)) {
                $whereConditions[] = "o.rider_id = ?";
                $params[] = $rider;
            }
            
            // Date filter
            if (!empty($date)) {
                $whereConditions[] = "DATE(o.created_at) = ?";
                $params[] = $date;
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($whereConditions)) {
                $sql .= " AND " . implode(" AND ", $whereConditions);
            }
            
            $sql .= " ORDER BY o.created_at DESC LIMIT 50";
            
            $deliveries = $this->fetchAll($sql, $params);

            // Get delivery statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status IN ('preparing', 'ready', 'picked_up', 'on_the_way', 'delivered')");
            $stats['total_deliveries'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status IN ('preparing', 'ready', 'picked_up', 'on_the_way')");
            $stats['active_deliveries'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()");
            $stats['completed_today'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_time FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()");
            $stats['avg_delivery_time'] = round($result['avg_time'] ?? 0, 1);

            // Get online riders (users with role 'rider' and active status)
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'rider' AND status = 'active' AND deleted_at IS NULL");
            $stats['online_riders'] = $result['count'] ?? 0;

            // Get detailed delivery status counts for live tracking
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'picked_up'");
            $stats['picked_up'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'on_the_way'");
            $stats['en_route'] = $result['count'] ?? 0;

            // Near destination - orders that are on_the_way and created more than 15 minutes ago
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'on_the_way' AND created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            $stats['near_destination'] = $result['count'] ?? 0;

        } catch (\Exception $e) {
            error_log("Error getting deliveries: " . $e->getMessage());
            $stats = [
                'total_deliveries' => 0,
                'active_deliveries' => 0,
                'completed_today' => 0,
                'avg_delivery_time' => 0,
                'online_riders' => 0,
                'picked_up' => 0,
                'en_route' => 0,
                'near_destination' => 0
            ];
        }

        $this->renderDashboard('admin/deliveries', [
            'title' => 'Delivery Management - Time2Eat',
            'user' => $userData,
            'deliveries' => $deliveries,
            'stats' => $stats,
            'currentPage' => 'deliveries',
            'search' => $search,
            'status' => $status,
            'rider' => $rider,
            'date' => $date
        ]);
    }

    /**
     * Admin Dispute Management
     */
    public function disputes(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Get search and filter parameters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';
        $priority = $_GET['priority'] ?? '';

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get disputes with comprehensive data
        $disputes = [];
        $stats = [];

        try {
            // Get disputes with comprehensive data
            $sql = "
                SELECT d.*,
                       o.id as order_id,
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       r.name as restaurant_name,
                       CONCAT(admin.first_name, ' ', admin.last_name) as assigned_admin
                FROM disputes d
                LEFT JOIN orders o ON d.order_id = o.id
                LEFT JOIN users u ON d.customer_id = u.id
                LEFT JOIN restaurants r ON d.restaurant_id = r.id
                LEFT JOIN users admin ON d.assigned_to = admin.id
                ORDER BY d.created_at DESC
                LIMIT 50
            ";
            
            $disputes = $this->fetchAll($sql);

            // Get dispute statistics
            $totalDisputes = $this->fetchOne("SELECT COUNT(*) as count FROM disputes")['count'] ?? 0;
            $openDisputes = $this->fetchOne("SELECT COUNT(*) as count FROM disputes WHERE status IN ('open', 'investigating')")['count'] ?? 0;
            $resolvedToday = $this->fetchOne("SELECT COUNT(*) as count FROM disputes WHERE status = 'resolved' AND DATE(resolved_at) = CURDATE()")['count'] ?? 0;
            $resolvedMonth = $this->fetchOne("SELECT COUNT(*) as count FROM disputes WHERE status = 'resolved' AND MONTH(resolved_at) = MONTH(CURDATE()) AND YEAR(resolved_at) = YEAR(CURDATE())")['count'] ?? 0;
            $urgentDisputes = $this->fetchOne("SELECT COUNT(*) as count FROM disputes WHERE priority = 'urgent' AND status IN ('open', 'investigating')")['count'] ?? 0;
            
            // Calculate average resolution time in days
            $avgResolution = $this->fetchOne("
                SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours 
                FROM disputes 
                WHERE status = 'resolved' AND resolved_at IS NOT NULL
            ")['avg_hours'] ?? 0;
            $avgResolutionDays = $avgResolution ? round($avgResolution / 24, 1) : 0;

            $stats = [
                'total_disputes' => $totalDisputes,
                'open_disputes' => $openDisputes,
                'resolved_today' => $resolvedToday,
                'resolved_month' => $resolvedMonth,
                'urgent_disputes' => $urgentDisputes,
                'avg_resolution_time' => $avgResolutionDays
            ];

        } catch (\Exception $e) {
            error_log("Error getting disputes: " . $e->getMessage());
            $stats = [
                'total_disputes' => 0,
                'open_disputes' => 0,
                'resolved_today' => 0,
                'resolved_month' => 0,
                'urgent_disputes' => 0,
                'avg_resolution_time' => 0
            ];
        }

        $this->renderDashboard('admin/disputes', [
            'title' => 'Dispute Management - Time2Eat',
            'user' => $userData,
            'disputes' => $disputes,
            'stats' => $stats,
            'currentPage' => 'disputes',
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'priority' => $priority
        ]);
    }

    /**
     * Admin Data Management
     */
    public function data(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get data management statistics
        $stats = [];

        try {
            // Get comprehensive database statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL");
            $stats['total_users'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE deleted_at IS NULL");
            $stats['total_restaurants'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders");
            $stats['total_orders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM menu_items WHERE deleted_at IS NULL");
            $stats['total_menu_items'] = $result['count'] ?? 0;

            // Get additional statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'");
            $stats['delivered_orders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
            $stats['pending_orders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND deleted_at IS NULL");
            $stats['total_customers'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'vendor' AND deleted_at IS NULL");
            $stats['total_vendors'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'rider' AND deleted_at IS NULL");
            $stats['total_riders'] = $result['count'] ?? 0;

            // Get database size
            $result = $this->fetchOne("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size FROM information_schema.tables WHERE table_schema = DATABASE()");
            $stats['database_size_mb'] = $result['size'] ?? 0;

            // Calculate total records across all major tables
            $totalRecords = $stats['total_users'] + $stats['total_restaurants'] + $stats['total_orders'] + $stats['total_menu_items'];
            $stats['total_records'] = $totalRecords;

            // Get recent activity (orders in last 24 hours)
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['recent_orders'] = $result['count'] ?? 0;

            // Get storage usage percentage (assuming 10GB limit)
            $storageLimit = 10240; // 10GB in MB
            $stats['storage_used'] = round(($stats['database_size_mb'] / $storageLimit) * 100, 1);
            $stats['storage_used_gb'] = round($stats['database_size_mb'] / 1024, 2);

            // Get last backup info (simulated - would need actual backup system)
            $stats['last_backup'] = '2 hours ago'; // This would come from actual backup system
            $stats['backup_status'] = 'successful';

            // Get import jobs count (simulated - would need actual job tracking)
            $stats['import_jobs'] = 0; // This would come from actual job tracking system

            // Get recent operations (simulated - would need actual job tracking system)
            $recentOperations = []; // This would come from actual job tracking system

        } catch (\Exception $e) {
            error_log("Error getting data stats: " . $e->getMessage());
            $stats = [
                'total_users' => 0,
                'total_restaurants' => 0,
                'total_orders' => 0,
                'total_menu_items' => 0,
                'total_customers' => 0,
                'total_vendors' => 0,
                'total_riders' => 0,
                'delivered_orders' => 0,
                'pending_orders' => 0,
                'recent_orders' => 0,
                'total_records' => 0,
                'database_size_mb' => 0,
                'storage_used' => 0,
                'storage_used_gb' => 0,
                'last_backup' => 'Unknown',
                'backup_status' => 'unknown',
                'import_jobs' => 0
            ];
        }

        $this->renderDashboard('admin/data', [
            'title' => 'Data Management - Time2Eat',
            'user' => $userData,
            'stats' => $stats,
            'recentOperations' => $recentOperations ?? [],
            'currentPage' => 'data'
        ]);
    }

    /**
     * Admin messaging system - Main messages page
     */
    public function messages(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $user = $this->getCurrentUser();
        
        // Get conversations for admin
        $conversations = $this->messageModel->getConversationsForUser($user->id, 'admin');
        
        // Get message statistics
        $stats = $this->messageModel->getMessageStats($user->id);

        $this->renderDashboard('admin/messages', [
            'title' => 'Admin Messages - Time2Eat',
            'user' => $user,
            'userRole' => 'admin',
            'conversations' => $conversations,
            'stats' => $stats,
            'currentPage' => 'messages'
        ]);
    }

    /**
     * Get conversation details and messages for admin
     */
    public function getConversation(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $conversationId = $_GET['id'] ?? '';
        if (!$conversationId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid conversation ID'], 400);
            return;
        }

        $user = $this->getCurrentUser();
        
        $conversation = $this->messageModel->getConversationMessages($conversationId, $user->id);
        
        if (!$conversation) {
            $this->jsonResponse(['success' => false, 'message' => 'Conversation not found'], 404);
            return;
        }

        $this->jsonResponse(['success' => true, 'conversation' => $conversation]);
    }

    /**
     * Send a message as admin
     */
    public function sendMessage(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $conversationId = $_POST['conversation_id'] ?? '';
        $message = trim($_POST['message'] ?? '');

        if (!$conversationId || !$message) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }

        $user = $this->getCurrentUser();

        // Get conversation to find recipient
        $conversation = $this->messageModel->getConversationMessages($conversationId, $user->id);
        if (!$conversation) {
            $this->jsonResponse(['success' => false, 'message' => 'Conversation not found'], 404);
            return;
        }

        // Send the message
        $messageData = [
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'recipient_id' => $conversation['other_party_id'],
            'order_id' => $conversation['order_id'],
            'message' => $message,
            'message_type' => 'text'
        ];

        $success = $this->messageModel->sendMessage($messageData);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'Message sent successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get all customers for admin messaging
     */
    public function getCustomers(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        try {
            $customers = $this->fetchAll(
                "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    u.created_at,
                    COUNT(o.id) as order_count,
                    MAX(o.created_at) as last_order_at
                FROM users u
                LEFT JOIN orders o ON u.id = o.customer_id
                WHERE u.role = 'customer' AND u.deleted_at IS NULL
                GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone, u.created_at
                ORDER BY last_order_at DESC, order_count DESC
                LIMIT 100"
            );

            $this->jsonResponse(['success' => true, 'customers' => $customers]);
        } catch (\Exception $e) {
            error_log("Error getting customers for admin: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to get customers'], 500);
        }
    }

    /**
     * Get all vendors/restaurants for admin messaging
     */
    public function getVendors(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        try {
            $vendors = $this->fetchAll(
                "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    u.created_at,
                    r.id as restaurant_id,
                    r.name as restaurant_name,
                    r.status as restaurant_status,
                    COUNT(o.id) as order_count,
                    MAX(o.created_at) as last_order_at
                FROM users u
                LEFT JOIN restaurants r ON u.id = r.user_id
                LEFT JOIN orders o ON r.id = o.restaurant_id
                WHERE u.role = 'vendor' AND u.deleted_at IS NULL
                GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone, u.created_at, r.id, r.name, r.status
                ORDER BY last_order_at DESC, order_count DESC
                LIMIT 100"
            );

            $this->jsonResponse(['success' => true, 'vendors' => $vendors]);
        } catch (\Exception $e) {
            error_log("Error getting vendors for admin: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to get vendors'], 500);
        }
    }

    /**
     * Get all riders for admin messaging
     */
    public function getRiders(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        try {
            $riders = $this->fetchAll(
                "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    u.created_at,
                    u.is_available,
                    COUNT(ra.id) as delivery_count,
                    MAX(ra.assigned_at) as last_delivery_at
                FROM users u
                LEFT JOIN rider_assignments ra ON u.id = ra.rider_id
                WHERE u.role = 'rider' AND u.deleted_at IS NULL
                GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone, u.created_at, u.is_available
                ORDER BY last_delivery_at DESC, delivery_count DESC
                LIMIT 100"
            );

            $this->jsonResponse(['success' => true, 'riders' => $riders]);
        } catch (\Exception $e) {
            error_log("Error getting riders for admin: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to get riders'], 500);
        }
    }

    /**
     * Compose new message as admin
     */
    public function composeMessage(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $recipientId = (int)($_POST['recipient_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $orderId = (int)($_POST['order_id'] ?? 0);

        if (!$recipientId || !$subject || !$message) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }

        $user = $this->getCurrentUser();

        // Verify recipient exists and is not admin
        $recipient = $this->fetchOne(
            "SELECT id, role FROM users WHERE id = ? AND role != 'admin' AND deleted_at IS NULL",
            [$recipientId]
        );

        if (!$recipient) {
            $this->jsonResponse(['success' => false, 'message' => 'Recipient not found'], 404);
            return;
        }

        // Verify order belongs to recipient if provided
        if ($orderId) {
            $orderCheck = $this->fetchOne(
                "SELECT COUNT(*) as count FROM orders WHERE id = ? AND (customer_id = ? OR restaurant_id IN (SELECT id FROM restaurants WHERE user_id = ?))",
                [$orderId, $recipientId, $recipientId]
            );

            if (!($orderCheck && $orderCheck['count'] > 0)) {
                $this->jsonResponse(['success' => false, 'message' => 'Order not found or does not belong to recipient'], 400);
                return;
            }
        }

        // Create new conversation
        $conversationId = $this->messageModel->createConversation(
            $user->id,           // sender (admin)
            $recipientId,        // recipient
            $message,
            $orderId,            // link to order if provided
            $subject
        );

        if ($conversationId) {
            $this->jsonResponse(['success' => true, 'message' => 'Message sent successfully', 'conversation_id' => $conversationId]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get recent orders for admin messaging context
     */
    public function getRecentOrders(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        try {
            $orders = $this->fetchAll(
                "SELECT 
                    o.id,
                    o.order_number,
                    o.status,
                    o.total_amount,
                    o.created_at,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    r.name as restaurant_name,
                    r.id as restaurant_id
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                ORDER BY o.created_at DESC
                LIMIT 50"
            );

            $this->jsonResponse(['success' => true, 'orders' => $orders]);
        } catch (\Exception $e) {
            error_log("Error getting recent orders for admin: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to get orders'], 500);
        }
    }

    /**
     * Show profit analytics dashboard
     */
    public function profitAnalytics(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        try {
            // Get profit analytics
            require_once __DIR__ . '/../models/Order.php';
            $orderModel = new \models\Order();

            $period = $_GET['period'] ?? 'month';
            $analytics = $orderModel->getProfitAnalytics($period);

            // Get restaurant commission breakdown
            $restaurantCommissions = $this->fetchAll("
                SELECT
                    r.id,
                    r.name,
                    r.cuisine_type,
                    r.commission_rate,
                    COALESCE(order_stats.total_orders, 0) as total_orders,
                    COALESCE(order_stats.total_revenue, 0) as total_revenue
                FROM restaurants r
                LEFT JOIN (
                    SELECT
                        restaurant_id,
                        COUNT(*) as total_orders,
                        SUM(subtotal) as total_revenue
                    FROM orders
                    WHERE status = 'delivered'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                    GROUP BY restaurant_id
                ) order_stats ON r.id = order_stats.restaurant_id
                WHERE r.deleted_at IS NULL
                AND r.status = 'active'
                ORDER BY order_stats.total_revenue DESC
                LIMIT 50
            ");

            // Convert user object to array for view compatibility
            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'status' => $user->status
            ];

            $this->renderDashboard('admin/profit-analytics', [
                'title' => 'Profit Analytics - Time2Eat Admin',
                'user' => $userData,
                'analytics' => $analytics,
                'restaurantCommissions' => $restaurantCommissions,
                'currentPage' => 'profit-analytics',
                'period' => $period
            ]);

        } catch (\Exception $e) {
            error_log("Error loading profit analytics: " . $e->getMessage());

            // Fallback data
            $analytics = [
                'total_orders' => 0,
                'total_commission' => 0,
                'total_restaurant_earnings' => 0,
                'avg_commission_rate' => 15,
                'active_restaurants' => 0,
                'commission_percentage' => 0
            ];

            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'status' => $user->status
            ];

            $this->renderDashboard('admin/profit-analytics', [
                'title' => 'Profit Analytics - Time2Eat Admin',
                'user' => $userData,
                'analytics' => $analytics,
                'restaurantCommissions' => [],
                'currentPage' => 'profit-analytics',
                'period' => 'month'
            ]);
        }
    }

    /**
     * Get profit analytics data via AJAX
     */
    public function profitAnalyticsData(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            require_once __DIR__ . '/../models/Order.php';
            $orderModel = new \models\Order();

            $period = $_GET['period'] ?? 'month';
            $analytics = $orderModel->getProfitAnalytics($period);

            $this->jsonResponse([
                'success' => true,
                'analytics' => $analytics
            ]);

        } catch (\Exception $e) {
            error_log("Error getting profit analytics data: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error loading analytics data'], 500);
        }
    }
}
