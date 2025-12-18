<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/AdminBaseController.php';

use controllers\AdminBaseController;

/**
 * Admin Rider Management Controller
 * Handles all rider administration functionalities
 */
class AdminRiderController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display riders management page
     */
    public function index(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'all';
        $availability = $_GET['availability'] ?? 'all';
        $sort = $_GET['sort'] ?? 'created_at';
        $order = $_GET['order'] ?? 'desc';

        try {
            // Get riders with statistics
            $riders = $this->getRidersWithStats($search, $status, $availability, $sort, $order);
            
            // Get overall statistics
            $stats = $this->getRiderStatistics();

            // Convert user object to array for view compatibility
            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'status' => $user->status
            ];

            $this->renderDashboard('admin/riders', [
                'title' => 'Rider Management - Time2Eat Admin',
                'user' => $userData,
                'riders' => $riders,
                'stats' => $stats,
                'currentPage' => 'riders',
                'search' => $search,
                'status' => $status,
                'availability' => $availability,
                'sort' => $sort,
                'order' => $order
            ]);

        } catch (\Exception $e) {
            error_log('Rider management error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'status' => $user->status
            ];

            $this->renderDashboard('admin/riders', [
                'title' => 'Rider Management - Time2Eat Admin',
                'user' => $userData,
                'riders' => [],
                'stats' => $this->getDefaultStats(),
                'currentPage' => 'riders',
                'error' => 'Failed to load rider data: ' . $e->getMessage(),
                'search' => $search ?? '',
                'status' => $status ?? 'all',
                'availability' => $availability ?? 'all',
                'sort' => $sort ?? 'created_at',
                'order' => $order ?? 'desc'
            ]);
        }
    }

    /**
     * Get riders with comprehensive statistics
     */
    private function getRidersWithStats(string $search, string $status, string $availability, string $sort, string $order): array
    {
        $sql = "
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.avatar as profile_image,
                u.status,
                u.created_at,
                u.last_login_at,
                COUNT(DISTINCT d.id) as total_deliveries,
                COUNT(DISTINCT CASE WHEN d.status = 'delivered' THEN d.id END) as completed_deliveries,
                COUNT(DISTINCT CASE WHEN d.status IN ('assigned', 'accepted', 'picked_up', 'on_the_way') THEN d.id END) as active_deliveries,
                COALESCE(AVG(CASE WHEN d.status = 'delivered' THEN d.rating END), 0) as avg_rating,
                COALESCE(SUM(CASE WHEN d.status = 'delivered' THEN d.rider_earnings END), 0) as total_earnings,
                COALESCE(SUM(CASE WHEN d.status = 'delivered' AND DATE(d.delivery_time) = CURDATE() THEN d.rider_earnings END), 0) as today_earnings,
                rl.is_online,
                rl.latitude,
                rl.longitude,
                rl.battery_level,
                rl.created_at as last_location_update,
                u.is_available
            FROM users u
            LEFT JOIN deliveries d ON u.id = d.rider_id
            LEFT JOIN (
                SELECT rider_id, is_online, latitude, longitude, battery_level, created_at,
                       ROW_NUMBER() OVER (PARTITION BY rider_id ORDER BY created_at DESC) as rn
                FROM rider_locations
            ) rl ON u.id = rl.rider_id AND rl.rn = 1
            WHERE u.role = 'rider'
        ";

        $params = [];

        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        // Add status filter
        if ($status !== 'all') {
            $sql .= " AND u.status = ?";
            $params[] = $status;
        }

        // Add availability filter
        if ($availability === 'online') {
            $sql .= " AND rl.is_online = 1";
        } elseif ($availability === 'offline') {
            $sql .= " AND (rl.is_online = 0 OR rl.is_online IS NULL)";
        }

        $sql .= " GROUP BY u.id";

        // Add sorting
        $allowedSorts = ['created_at', 'first_name', 'total_deliveries', 'avg_rating', 'total_earnings'];
        $allowedOrders = ['asc', 'desc'];
        
        if (in_array($sort, $allowedSorts) && in_array($order, $allowedOrders)) {
            $sql .= " ORDER BY {$sort} {$order}";
        } else {
            $sql .= " ORDER BY u.created_at DESC";
        }

        return $this->fetchAll($sql, $params);
    }

    /**
     * Get overall rider statistics
     */
    private function getRiderStatistics(): array
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT u.id) as total_riders,
                COUNT(DISTINCT CASE WHEN u.status = 'active' THEN u.id END) as active_riders,
                COUNT(DISTINCT CASE WHEN u.status = 'inactive' THEN u.id END) as inactive_riders,
                COUNT(DISTINCT CASE WHEN rl.is_online = 1 THEN u.id END) as online_riders,
                COUNT(DISTINCT d.id) as total_deliveries,
                COUNT(DISTINCT CASE WHEN d.status = 'delivered' THEN d.id END) as completed_deliveries,
                COUNT(DISTINCT CASE WHEN d.status IN ('assigned', 'accepted', 'picked_up', 'on_the_way') THEN d.id END) as active_deliveries,
                COALESCE(AVG(CASE WHEN d.status = 'delivered' THEN d.rating END), 0) as avg_rating,
                COALESCE(SUM(CASE WHEN d.status = 'delivered' THEN d.rider_earnings END), 0) as total_earnings,
                COALESCE(SUM(CASE WHEN d.status = 'delivered' AND DATE(d.delivery_time) = CURDATE() THEN d.rider_earnings END), 0) as today_earnings,
                COALESCE(SUM(CASE WHEN d.status = 'delivered' AND YEARWEEK(d.delivery_time, 1) = YEARWEEK(CURDATE(), 1) THEN d.rider_earnings END), 0) as week_earnings,
                COALESCE(SUM(CASE WHEN d.status = 'delivered' AND MONTH(d.delivery_time) = MONTH(CURDATE()) AND YEAR(d.delivery_time) = YEAR(CURDATE()) THEN d.rider_earnings END), 0) as month_earnings
            FROM users u
            LEFT JOIN deliveries d ON u.id = d.rider_id
            LEFT JOIN (
                SELECT rider_id, is_online,
                       ROW_NUMBER() OVER (PARTITION BY rider_id ORDER BY created_at DESC) as rn
                FROM rider_locations
            ) rl ON u.id = rl.rider_id AND rl.rn = 1
            WHERE u.role = 'rider'
        ";

        $result = $this->fetchOne($sql);
        return $result ?: $this->getDefaultStats();
    }

    /**
     * Get default statistics
     */
    private function getDefaultStats(): array
    {
        return [
            'total_riders' => 0,
            'active_riders' => 0,
            'inactive_riders' => 0,
            'online_riders' => 0,
            'total_deliveries' => 0,
            'completed_deliveries' => 0,
            'active_deliveries' => 0,
            'avg_rating' => 0,
            'total_earnings' => 0,
            'today_earnings' => 0,
            'week_earnings' => 0,
            'month_earnings' => 0
        ];
    }

    /**
     * Update rider status (activate/deactivate)
     */
    public function updateStatus(): void
    {
        if (!$this->isAuthenticated() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $riderId = (int)($_POST['rider_id'] ?? 0);
            $status = $_POST['status'] ?? '';

            if (!$riderId || !in_array($status, ['active', 'inactive', 'suspended'])) {
                $this->json(['success' => false, 'message' => 'Invalid parameters'], 400);
                return;
            }

            $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ? AND role = 'rider'";
            $this->execute($sql, [$status, $riderId]);

            $this->json([
                'success' => true,
                'message' => 'Rider status updated successfully'
            ]);

        } catch (\Exception $e) {
            error_log('Error updating rider status: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }

    /**
     * Delete rider
     */
    public function deleteRider(): void
    {
        if (!$this->isAuthenticated()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $riderId = (int)($input['rider_id'] ?? 0);

        if (!$riderId) {
            $this->json(['success' => false, 'message' => 'Rider ID is required'], 400);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Check if rider exists
            $rider = $this->db->fetchOne(
                "SELECT id, first_name, last_name, email FROM users WHERE id = ? AND role = 'rider'",
                [$riderId]
            );

            if (!$rider) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Rider not found'], 404);
                return;
            }

            // Check if rider has active deliveries
            $activeDeliveries = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM deliveries WHERE rider_id = ? AND status IN ('assigned', 'accepted', 'picked_up', 'on_the_way')",
                [$riderId]
            );

            if ($activeDeliveries['count'] > 0) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Cannot delete rider with active deliveries'], 400);
                return;
            }

            // Delete rider-related data
            $this->db->execute("DELETE FROM rider_locations WHERE rider_id = ?", [$riderId]);
            $this->db->execute("DELETE FROM rider_schedules WHERE rider_id = ?", [$riderId]);
            $this->db->execute("DELETE FROM user_profiles WHERE user_id = ?", [$riderId]);
            
            // Update deliveries to remove rider reference
            $this->db->execute("UPDATE deliveries SET rider_id = NULL WHERE rider_id = ?", [$riderId]);
            
            // Finally delete the user
            $this->db->execute("DELETE FROM users WHERE id = ?", [$riderId]);

            $this->db->commit();

            // Log the deletion
            error_log("Admin {$user->email} deleted rider {$rider['email']} (ID: {$riderId})");

            $this->json([
                'success' => true,
                'message' => 'Rider deleted successfully',
                'rider_name' => $rider['first_name'] . ' ' . $rider['last_name']
            ]);

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error deleting rider: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to delete rider'], 500);
        }
    }

    /**
     * Get rider details (API endpoint)
     */
    public function getRiderDetails(int $riderId): void
    {
        if (!$this->isAuthenticated()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $sql = "
                SELECT
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    u.avatar as profile_image,
                    u.status,
                    u.created_at,
                    u.last_login_at,
                    u.is_available,
                    up.address,
                    up.city,
                    up.date_of_birth,
                    rl.is_online,
                    rl.latitude,
                    rl.longitude,
                    rl.battery_level,
                    rl.created_at as last_location_update,
                    COUNT(DISTINCT d.id) as total_deliveries,
                    COUNT(DISTINCT CASE WHEN d.status = 'delivered' THEN d.id END) as completed_deliveries,
                    COUNT(DISTINCT CASE WHEN d.status IN ('assigned', 'accepted', 'picked_up', 'on_the_way') THEN d.id END) as active_deliveries,
                    COALESCE(AVG(CASE WHEN d.status = 'delivered' THEN d.rating END), 0) as avg_rating,
                    COALESCE(SUM(CASE WHEN d.status = 'delivered' THEN d.rider_earnings END), 0) as total_earnings,
                    COALESCE(SUM(CASE WHEN d.status = 'delivered' AND DATE(d.delivery_time) = CURDATE() THEN d.rider_earnings END), 0) as today_earnings,
                    COALESCE(SUM(CASE WHEN d.status = 'delivered' AND YEARWEEK(d.delivery_time, 1) = YEARWEEK(CURDATE(), 1) THEN d.rider_earnings END), 0) as week_earnings,
                    COALESCE(SUM(CASE WHEN d.status = 'delivered' AND MONTH(d.delivery_time) = MONTH(CURDATE()) AND YEAR(d.delivery_time) = YEAR(CURDATE()) THEN d.rider_earnings END), 0) as month_earnings
                FROM users u
                LEFT JOIN deliveries d ON u.id = d.rider_id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN (
                    SELECT rider_id, is_online, latitude, longitude, battery_level, created_at,
                           ROW_NUMBER() OVER (PARTITION BY rider_id ORDER BY created_at DESC) as rn
                    FROM rider_locations
                ) rl ON u.id = rl.rider_id AND rl.rn = 1
                WHERE u.id = ? AND u.role = 'rider'
                GROUP BY u.id
            ";

            $rider = $this->fetchOne($sql, [$riderId]);

            if ($rider) {
                $this->json([
                    'success' => true,
                    'rider' => $rider
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Rider not found'], 404);
            }

        } catch (\Exception $e) {
            error_log('Error fetching rider details: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to fetch rider details'], 500);
        }
    }
}

