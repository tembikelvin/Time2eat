<?php

declare(strict_types=1);

namespace Time2Eat\Controllers\Admin;

require_once __DIR__ . '/../../controllers/AdminBaseController.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/RoleChangeRequest.php';
require_once __DIR__ . '/../../models/UserActivity.php';

use controllers\AdminBaseController;
use models\User;
use models\RoleChangeRequest;
use models\UserActivity;

/**
 * Enhanced User Management Controller
 * Handles advanced user management features including role changes, activity tracking, and communication
 */
class UserManagementController extends AdminBaseController
{
    private User $userModel;
    private RoleChangeRequest $roleChangeModel;
    private UserActivity $activityModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->roleChangeModel = new RoleChangeRequest();
        $this->activityModel = new UserActivity();
        
        // Create tables if they don't exist
        $this->roleChangeModel->createTableIfNotExists();
        $this->activityModel->createTableIfNotExists();
    }

    /**
     * Role Change Requests Management (with integrated approvals)
     */
    public function roleChangeRequests(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? 'all'; // all, role_requests, user_applications, restaurant_applications
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get role change requests
        $requests = $this->roleChangeModel->getAllRequestsWithUserDetails($status, $limit, $offset);
        $stats = $this->roleChangeModel->getRequestStats();

        // Get pending vendors (restaurant owner applications)
        $pendingVendors = [];
        if ($type === 'all' || $type === 'vendor_applications') {
            $pendingVendors = $this->fetchAll("
                SELECT u.*, 'Vendor Application' as application_type
                FROM users u 
                WHERE u.status = 'pending' 
                AND u.role = 'vendor'
                ORDER BY u.created_at DESC 
                LIMIT 20
            ");
        }

        // Get pending riders (delivery rider applications)
        $pendingRiders = [];
        if ($type === 'all' || $type === 'rider_applications') {
            $pendingRiders = $this->fetchAll("
                SELECT u.*, 'Rider Application' as application_type
                FROM users u 
                WHERE u.status = 'pending' 
                AND u.role = 'rider'
                ORDER BY u.created_at DESC 
                LIMIT 20
            ");
        }

        // Get pending restaurants
        $pendingRestaurants = [];
        if ($type === 'all' || $type === 'restaurant_applications') {
            $pendingRestaurants = $this->fetchAll("
                SELECT r.*, u.first_name, u.last_name, u.email as owner_email
                FROM restaurants r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.status = 'pending' 
                ORDER BY r.created_at DESC 
                LIMIT 20
            ");
        }

        // Get combined stats
        $combinedStats = [
            'total_requests' => $stats['total_requests'] ?? 0,
            'pending_requests' => $stats['pending_requests'] ?? 0,
            'approved_requests' => $stats['approved_requests'] ?? 0,
            'rejected_requests' => $stats['rejected_requests'] ?? 0,
            'pending_vendors' => count($pendingVendors),
            'pending_riders' => count($pendingRiders),
            'pending_restaurants' => count($pendingRestaurants),
            'total_pending' => ($stats['pending_requests'] ?? 0) + count($pendingVendors) + count($pendingRiders) + count($pendingRestaurants)
        ];

        $user = $this->getCurrentUser();
        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        $this->renderDashboard('admin/user-management/role-requests', [
            'title' => 'Role Change Requests & Approvals - Time2Eat Admin',
            'user' => $userData,
            'requests' => $requests,
            'pendingVendors' => $pendingVendors,
            'pendingRiders' => $pendingRiders,
            'pendingRestaurants' => $pendingRestaurants,
            'stats' => $combinedStats,
            'currentPage' => 'role-requests',
            'status' => $status,
            'type' => $type,
            'page' => $page
        ]);
    }

    /**
     * Approve role change request
     */
    public function approveRoleChange(int $requestId): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $adminNotes = $input['admin_notes'] ?? '';

        $user = $this->getCurrentUser();
        $success = $this->roleChangeModel->approveRequest($requestId, $user->id, $adminNotes);

        if ($success) {
            // Log admin activity
            $this->activityModel->logActivity([
                'user_id' => $user->id,
                'activity_type' => 'role_change_approved',
                'activity_description' => "Approved role change request #$requestId",
                'entity_type' => 'role_change_request',
                'entity_id' => $requestId
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Role change request approved successfully'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to approve role change request'
            ], 500);
        }
    }

    /**
     * Reject role change request
     */
    public function rejectRoleChange(int $requestId): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $adminNotes = $input['admin_notes'] ?? '';

        $user = $this->getCurrentUser();
        $success = $this->roleChangeModel->rejectRequest($requestId, $user->id, $adminNotes);

        if ($success) {
            // Log admin activity
            $this->activityModel->logActivity([
                'user_id' => $user->id,
                'activity_type' => 'role_change_rejected',
                'activity_description' => "Rejected role change request #$requestId",
                'entity_type' => 'role_change_request',
                'entity_id' => $requestId
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Role change request rejected'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to reject role change request'
            ], 500);
        }
    }

    /**
     * Approve User Application
     */
    public function approveUser(): void
    {
        // Enhanced debugging
        error_log("🔍 DEBUG: approveUser() called");
        error_log("🔍 DEBUG: REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
        error_log("🔍 DEBUG: Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'unknown'));
        
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            error_log("❌ DEBUG: Authentication failed - isAuthenticated: " . ($this->isAuthenticated() ? 'true' : 'false') . ", isAdmin: " . ($this->isAdmin() ? 'true' : 'false'));
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized - Authentication failed'], 401);
            return;
        }

        error_log("✅ DEBUG: Authentication passed");

        try {
            // Get input data with better debugging
            $rawInput = file_get_contents('php://input');
            error_log("🔍 DEBUG: Raw input: " . $rawInput);
            
            $input = json_decode($rawInput, true) ?? $_POST;
            error_log("🔍 DEBUG: Parsed input: " . print_r($input, true));
            
            $userId = $input['user_id'] ?? null;
            error_log("🔍 DEBUG: User ID: " . ($userId ?? 'null'));
            
            if (!$userId) {
                error_log("❌ DEBUG: No user ID provided");
                $this->jsonResponse(['success' => false, 'message' => 'User ID required', 'debug' => 'No user_id in request data']);
                return;
            }

            // Get user details before updating
            error_log("🔍 DEBUG: Fetching user details for ID: $userId");
            $user = $this->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                error_log("❌ DEBUG: User not found in database for ID: $userId");
                $this->jsonResponse(['success' => false, 'message' => 'User not found', 'debug' => "No user found with ID: $userId"]);
                return;
            }

            error_log("✅ DEBUG: User found: " . print_r($user, true));

            // Check if user is already active
            if ($user['status'] === 'active') {
                error_log("⚠️ DEBUG: User is already active");
                $this->jsonResponse(['success' => false, 'message' => 'User is already approved', 'debug' => 'User status is already active']);
                return;
            }

            // Update user status to active
            error_log("🔍 DEBUG: Updating user status to active");
            $stmt = $this->query("UPDATE users SET status = 'active' WHERE id = ?", [$userId]);
            $result = $stmt->rowCount() > 0;
            error_log("🔍 DEBUG: Update result - rows affected: " . $stmt->rowCount());

            if ($result) {
                error_log("✅ DEBUG: User status updated successfully");
                
                // Create notification for the user
                try {
                    error_log("🔍 DEBUG: Creating approval notification");
                    $this->query("
                        INSERT INTO popup_notifications (title, message, type, target_audience, target_user_id, is_active, priority, created_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ", [
                        'Application Approved',
                        'Your ' . $user['role'] . ' application has been approved! You can now access your dashboard.',
                        'success',
                        $user['role'] . 's',
                        $userId,
                        1,
                        1,
                        $this->getCurrentUser()->id
                    ]);
                    error_log("✅ DEBUG: Notification created successfully");
                } catch (\Exception $e) {
                    error_log("⚠️ DEBUG: Error creating approval notification: " . $e->getMessage());
                    error_log("⚠️ DEBUG: Notification error trace: " . $e->getTraceAsString());
                }

                // Log admin activity
                try {
                    error_log("🔍 DEBUG: Logging admin activity");
                    $this->activityModel->logActivity([
                        'user_id' => $this->getCurrentUser()->id,
                        'activity_type' => 'user_approved',
                        'activity_description' => "Approved user application #$userId",
                        'entity_type' => 'user',
                        'entity_id' => $userId
                    ]);
                    error_log("✅ DEBUG: Activity logged successfully");
                } catch (\Exception $e) {
                    error_log("⚠️ DEBUG: Error logging activity: " . $e->getMessage());
                }

                error_log("✅ DEBUG: Approval completed successfully");
                $this->jsonResponse(['success' => true, 'message' => 'User approved successfully', 'debug' => 'User status updated to active']);
            } else {
                error_log("❌ DEBUG: Failed to update user status - no rows affected");
                $this->jsonResponse(['success' => false, 'message' => 'Failed to approve user - no changes made', 'debug' => 'UPDATE query did not affect any rows']);
            }
        } catch (\Exception $e) {
            error_log("❌ DEBUG: Exception in approveUser: " . $e->getMessage());
            error_log("❌ DEBUG: Exception file: " . $e->getFile() . " line: " . $e->getLine());
            error_log("❌ DEBUG: Exception trace: " . $e->getTraceAsString());
            $this->jsonResponse([
                'success' => false, 
                'message' => 'An error occurred while approving user',
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }

    /**
     * Reject User Application
     */
    public function rejectUser(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $userId = $input['user_id'] ?? null;
            $reason = $input['reason'] ?? 'Application rejected';
            
            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'User ID required']);
                return;
            }

            // Get user details before updating
            $user = $this->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'User not found']);
                return;
            }

            // Update user status to rejected
            $stmt = $this->query("UPDATE users SET status = 'rejected' WHERE id = ?", [$userId]);
            $result = $stmt->rowCount() > 0;

            if ($result) {
                // Create notification for the user
                try {
                    $this->query("
                        INSERT INTO popup_notifications (title, message, type, target_audience, target_user_id, is_active, priority, created_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ", [
                        'Application Rejected',
                        'Your ' . $user['role'] . ' application has been rejected. Reason: ' . $reason,
                        'error',
                        $user['role'] . 's',
                        $userId,
                        1,
                        1,
                        $this->getCurrentUser()->id
                    ]);
                } catch (\Exception $e) {
                    error_log("Error creating rejection notification: " . $e->getMessage());
                }

                // Log admin activity
                $this->activityModel->logActivity([
                    'user_id' => $this->getCurrentUser()->id,
                    'activity_type' => 'user_rejected',
                    'activity_description' => "Rejected user application #$userId: $reason",
                    'entity_type' => 'user',
                    'entity_id' => $userId
                ]);

                $this->jsonResponse(['success' => true, 'message' => 'User rejected successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to reject user']);
            }
        } catch (\Exception $e) {
            error_log("Error rejecting user: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while rejecting user']);
        }
    }

    /**
     * Approve Restaurant Application
     */
    public function approveRestaurant(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $restaurantId = $input['restaurant_id'] ?? null;
            
            if (!$restaurantId) {
                $this->jsonResponse(['success' => false, 'message' => 'Restaurant ID required']);
                return;
            }

            // Update restaurant status to active
            $stmt = $this->query("UPDATE restaurants SET status = 'active' WHERE id = ?", [$restaurantId]);
            $result = $stmt->rowCount() > 0;
            
            if ($result) {
                // Log admin activity
                $this->activityModel->logActivity([
                    'user_id' => $this->getCurrentUser()->id,
                    'activity_type' => 'restaurant_approved',
                    'activity_description' => "Approved restaurant application #$restaurantId",
                    'entity_type' => 'restaurant',
                    'entity_id' => $restaurantId
                ]);

                $this->jsonResponse(['success' => true, 'message' => 'Restaurant approved successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to approve restaurant']);
            }
        } catch (\Exception $e) {
            error_log("Error approving restaurant: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while approving restaurant']);
        }
    }

    /**
     * Reject Restaurant Application
     */
    public function rejectRestaurant(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $restaurantId = $input['restaurant_id'] ?? null;
            $reason = $input['reason'] ?? 'Application rejected';
            
            if (!$restaurantId) {
                $this->jsonResponse(['success' => false, 'message' => 'Restaurant ID required']);
                return;
            }

            // Update restaurant status to rejected
            $stmt = $this->query("UPDATE restaurants SET status = 'rejected' WHERE id = ?", [$restaurantId]);
            $result = $stmt->rowCount() > 0;
            
            if ($result) {
                // Log admin activity
                $this->activityModel->logActivity([
                    'user_id' => $this->getCurrentUser()->id,
                    'activity_type' => 'restaurant_rejected',
                    'activity_description' => "Rejected restaurant application #$restaurantId: $reason",
                    'entity_type' => 'restaurant',
                    'entity_id' => $restaurantId
                ]);

                $this->jsonResponse(['success' => true, 'message' => 'Restaurant rejected successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to reject restaurant']);
            }
        } catch (\Exception $e) {
            error_log("Error rejecting restaurant: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while rejecting restaurant']);
        }
    }

    /**
     * User Activity Monitoring
     */
    public function userActivity(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        $userId = intval($_GET['user_id'] ?? 0);
        $activityType = $_GET['activity_type'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        if ($userId > 0) {
            // Get specific user activities
            $activities = $this->activityModel->getUserActivities($userId, $limit, $offset);
            $userStats = $this->activityModel->getUserActivityStats($userId);
            $targetUser = $this->userModel->getById($userId);
        } else {
            // Get all activities
            $activities = $this->activityModel->getAllActivitiesWithUsers($activityType, $limit, $offset);
            $userStats = null;
            $targetUser = null;
        }

        $platformStats = $this->activityModel->getPlatformActivityStats();
        $activityBreakdown = $this->activityModel->getActivityBreakdown(30);
        $mostActiveUsers = $this->activityModel->getMostActiveUsers(30, 10);

        $user = $this->getCurrentUser();
        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        $this->renderDashboard('admin/user-management/activity', [
            'title' => 'User Activity Monitoring - Time2Eat Admin',
            'user' => $userData,
            'activities' => $activities,
            'userStats' => $userStats,
            'targetUser' => $targetUser,
            'platformStats' => $platformStats,
            'activityBreakdown' => $activityBreakdown,
            'mostActiveUsers' => $mostActiveUsers,
            'currentPage' => 'user-activity',
            'userId' => $userId,
            'activityType' => $activityType,
            'page' => $page,
            'activityTypes' => UserActivity::getActivityTypes()
        ]);
    }

    /**
     * User Analytics Dashboard
     */
    public function userAnalytics(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        // Get comprehensive user analytics
        $userStats = $this->getUserAnalyticsData();
        $roleDistribution = $this->getUserRoleDistribution();
        $userGrowth = $this->getUserGrowthData();
        $activityStats = $this->activityModel->getPlatformActivityStats();

        $user = $this->getCurrentUser();
        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        $this->renderDashboard('admin/user-management/analytics', [
            'title' => 'User Analytics - Time2Eat Admin',
            'user' => $userData,
            'userStats' => $userStats,
            'roleDistribution' => $roleDistribution,
            'userGrowth' => $userGrowth,
            'activityStats' => $activityStats,
            'currentPage' => 'user-analytics'
        ]);
    }

    /**
     * Custom renderDashboard method to ensure proper admin layout
     */
    protected function renderDashboard(string $view, array $data = []): void
    {
        // Start output buffering to capture the dashboard content
        ob_start();

        // Extract data for the view
        extract($data);

        // Include the specific dashboard view using correct relative path
        $viewPath = __DIR__ . "/../../views/{$view}.php";
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
        $layoutPath = __DIR__ . "/../../views/components/dashboard-layout.php";
        if (!file_exists($layoutPath)) {
            throw new \Exception("Dashboard layout not found: dashboard-layout.php");
        }
        include $layoutPath;
    }

    /**
     * Send message to user
     */
    public function sendMessage(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $userId = intval($input['user_id'] ?? 0);
        $subject = trim($input['subject'] ?? '');
        $message = trim($input['message'] ?? '');

        if ($userId <= 0 || empty($subject) || empty($message)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid input data'], 400);
            return;
        }

        // Here you would integrate with your messaging system
        // For now, we'll just log the activity
        $user = $this->getCurrentUser();
        $this->activityModel->logActivity([
            'user_id' => $user->id,
            'activity_type' => 'admin_message_sent',
            'activity_description' => "Sent message to user #$userId: $subject",
            'entity_type' => 'user',
            'entity_id' => $userId,
            'metadata' => [
                'subject' => $subject,
                'message' => $message
            ]
        ]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Message sent successfully'
        ]);
    }

    /**
     * Get user analytics data
     */
    private function getUserAnalyticsData(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_users,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_users,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_users_30_days,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_users_7_days,
                SUM(CASE WHEN last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_users_30_days
            FROM users
            WHERE deleted_at IS NULL
        ";

        $result = $this->fetchOne($sql);
        return $result ?: [];
    }

    /**
     * Get user role distribution
     */
    private function getUserRoleDistribution(): array
    {
        $sql = "
            SELECT 
                role,
                COUNT(*) as count,
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL), 2) as percentage
            FROM users
            WHERE deleted_at IS NULL
            GROUP BY role
            ORDER BY count DESC
        ";

        return $this->fetchAll($sql);
    }

    /**
     * Get user growth data
     */
    private function getUserGrowthData(): array
    {
        $sql = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_users,
                role
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND deleted_at IS NULL
            GROUP BY DATE(created_at), role
            ORDER BY date DESC
        ";

        return $this->fetchAll($sql);
    }
}
