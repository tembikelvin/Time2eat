<?php

namespace Time2Eat\Controllers\Admin;

use \core\BaseController;
use \models\User;

/**
 * Admin User Management Controller
 * Handles CRUD operations for user management in admin panel
 */
class UserController extends BaseController
{
    
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Display users list with filtering and pagination
     */
    public function index(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? 'all';
        $status = $_GET['status'] ?? 'all';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get users with filtering
        $users = $this->userModel->getUsers($role, $status, $search, $limit, $offset);
        $totalUsers = $this->userModel->countUsers($role, $status, $search);
        $totalPages = ceil($totalUsers / $limit);

        $this->renderDashboard('admin/users/index', [
            'title' => 'User Management - Time2Eat Admin',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status
            ]
        ]);
    }

    /**
     * Show create user form
     */
    public function create(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $this->renderDashboard('admin/users/create', [
            'title' => 'Create User - Time2Eat Admin'
        ]);
    }

    /**
     * Store new user
     */
    public function store(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Validate required fields
            $requiredFields = ['username', 'email', 'password', 'first_name', 'last_name', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                    return;
                }
            }

            // Check if username or email already exists
            if ($this->userModel->findByUsername($_POST['username'])) {
                $this->json(['success' => false, 'message' => 'Username already exists'], 400);
                return;
            }

            if ($this->userModel->findByEmail($_POST['email'])) {
                $this->json(['success' => false, 'message' => 'Email already exists'], 400);
                return;
            }

            // Create user
            $userData = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'phone' => $_POST['phone'] ?? null,
                'role' => $_POST['role'],
                'status' => $_POST['status'] ?? 'active'
            ];

            $userId = $this->userModel->create($userData);

            if ($userId) {
                $this->json([
                    'success' => true, 
                    'message' => 'User created successfully',
                    'user_id' => $userId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to create user'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while creating user'], 500);
        }
    }

    /**
     * Show user details
     */
    public function show(int $id): void
    {
        error_log("UserController::show() called with ID: $id");
        
        if (!$this->isAuthenticated()) {
            error_log("User not authenticated, redirecting to login");
            $this->redirect('/login');
            return;
        }
        
        if (!$this->isAdmin()) {
            error_log("User not admin, redirecting to login");
            $this->redirect('/login');
            return;
        }
        
        error_log("Authentication passed, looking for user with ID: $id");
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            error_log("User not found with ID: $id");
            $this->redirect('/admin/users?error=User not found');
            return;
        }
        
        error_log("User found: " . json_encode($user));

        // Get user statistics
        $stats = $this->getUserStats($id);

        $this->renderDashboard('admin/users/show', [
            'title' => 'User Details - Time2Eat Admin',
            'user' => $user,
            'stats' => $stats
        ]);
    }

    /**
     * Show edit user form
     */
    public function edit(int $id): void
    {
        error_log("UserController::edit() called with ID: $id");
        
        if (!$this->isAuthenticated()) {
            error_log("User not authenticated in edit, redirecting to login");
            $this->redirect('/login');
            return;
        }
        
        if (!$this->isAdmin()) {
            error_log("User not admin in edit, redirecting to login");
            $this->redirect('/login');
            return;
        }
        
        error_log("Authentication passed in edit, looking for user with ID: $id");
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            error_log("User not found in edit with ID: $id");
            $this->redirect('/admin/users?error=User not found');
            return;
        }
        
        error_log("User found in edit: " . json_encode($user));

        $this->renderDashboard('admin/users/edit', [
            'title' => 'Edit User - Time2Eat Admin',
            'user' => $user
        ]);
    }

    /**
     * Update user
     */
    public function updateUser(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $user = $this->userModel->findById($id);
            if (!$user) {
                $this->json(['success' => false, 'message' => 'User not found'], 404);
                return;
            }

            // Prepare update data
            $updateData = [];
            $allowedFields = ['username', 'email', 'first_name', 'last_name', 'phone', 'role', 'status'];

            foreach ($allowedFields as $field) {
                if (isset($_POST[$field]) && $_POST[$field] !== '') {
                    $updateData[$field] = $_POST[$field];
                }
            }

            // Handle password update
            if (!empty($_POST['password'])) {
                $updateData['password'] = $_POST['password'];
            }

            // Check for duplicate username/email (excluding current user)
            if (isset($updateData['username'])) {
                $existingUser = $this->userModel->findByUsername($updateData['username']);
                if ($existingUser && $existingUser['id'] != $id) {
                    $this->json(['success' => false, 'message' => 'Username already exists'], 400);
                    return;
                }
            }

            if (isset($updateData['email'])) {
                $existingUser = $this->userModel->findByEmail($updateData['email']);
                if ($existingUser && $existingUser['id'] != $id) {
                    $this->json(['success' => false, 'message' => 'Email already exists'], 400);
                    return;
                }
            }

            // Update user
            $success = $this->userModel->updateUser($id, $updateData);

            if ($success) {
                $this->json(['success' => true, 'message' => 'User updated successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update user'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while updating user'], 500);
        }
    }

    /**
     * Delete user (soft delete)
     */
    public function destroy(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $user = $this->userModel->findById($id);
            if (!$user) {
                $this->json(['success' => false, 'message' => 'User not found'], 404);
                return;
            }

            // Prevent admin from deleting themselves
            if ($this->user && $this->user->id == $id) {
                $this->json(['success' => false, 'message' => 'Cannot delete your own account'], 400);
                return;
            }

            // Soft delete user
            $success = $this->userModel->softDelete($id);

            if ($success) {
                $this->json(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete user'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while deleting user'], 500);
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $user = $this->userModel->findById($id);
            if (!$user) {
                $this->json(['success' => false, 'message' => 'User not found'], 404);
                return;
            }

            // Toggle status
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            $success = $this->userModel->updateUser($id, ['status' => $newStatus]);

            if ($success) {
                $this->json([
                    'success' => true, 
                    'message' => "User {$newStatus} successfully",
                    'new_status' => $newStatus
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update user status'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error toggling user status: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(int $id): void
    {
        // Middleware should handle authentication, but let's double-check
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $user = $this->userModel->findById($id);
            if (!$user) {
                $this->json(['success' => false, 'message' => 'User not found'], 404);
                return;
            }

            // Generate temporary password
            $tempPassword = bin2hex(random_bytes(8));
            $success = $this->userModel->updateUser($id, ['password' => $tempPassword]);

            if ($success) {
                // In a real application, you would send this via email
                $this->json([
                    'success' => true, 
                    'message' => 'Password reset successfully',
                    'temp_password' => $tempPassword
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to reset password'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error resetting password: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Get user statistics
     */
    private function getUserStats(int $userId): array
    {
        try {
            // Get basic stats
            $stats = [
                'total_orders' => 0,
                'total_spent' => 0,
                'total_earnings' => 0,
                'referrals_count' => 0,
                'last_login' => null,
                'account_age_days' => 0
            ];

            // Get order stats
            $orderStats = $this->fetchOne("
                SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_spent
                FROM orders 
                WHERE customer_id = ? AND status != 'cancelled'
            ", [$userId]);

            if ($orderStats) {
                $stats['total_orders'] = $orderStats['total_orders'];
                $stats['total_spent'] = $orderStats['total_spent'];
            }

            // Get affiliate stats if user is affiliate
            $affiliateStats = $this->fetchOne("
                SELECT total_earnings, total_referrals
                FROM affiliates 
                WHERE user_id = ?
            ", [$userId]);

            if ($affiliateStats) {
                $stats['total_earnings'] = $affiliateStats['total_earnings'];
                $stats['referrals_count'] = $affiliateStats['total_referrals'];
            }

            // Get account age
            $user = $this->userModel->findById($userId);
            if ($user && isset($user['created_at'])) {
                $createdAt = new \DateTime($user['created_at']);
                $now = new \DateTime();
                $stats['account_age_days'] = $now->diff($createdAt)->days;
            }

            return $stats;

        } catch (\Exception $e) {
            error_log("Error getting user stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Render dashboard view with dashboard layout
     */
    protected function renderDashboard(string $viewName, array $data = []): void
    {
        // Set dashboard layout
        $data['layout'] = 'dashboard';
        
        // Add current user and role to data
        $data['user'] = $this->user;
        $data['userRole'] = 'admin';
        
        // Use the base render method
        $this->render($viewName, $data);
    }

}
