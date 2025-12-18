<?php

declare(strict_types=1);

namespace Time2Eat\Controllers\Admin;

require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../traits/RestaurantQueryTrait.php';

use core\BaseController;
use Time2Eat\Models\User;
use traits\RestaurantQueryTrait;

/**
 * Admin Restaurant Management Controller
 * Handles CRUD operations for restaurant management in admin panel
 */
class RestaurantController extends BaseController
{
    use RestaurantQueryTrait;
    
    private User $userModel;

    public function __construct()
    {
        $this->layout = 'dashboard'; // Use dashboard layout for admin pages
        parent::__construct();
        $this->userModel = new User();

        // Require admin authentication
        $this->requireAuth();
        $this->requireRole('admin');
    }

    /**
     * Display restaurants list with filtering and pagination
     */
    public function index(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'all';
        $category = $_GET['category'] ?? 'all';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get restaurants with filtering
        $restaurants = $this->getRestaurants($status, $search, $category, $limit, $offset);
        $totalRestaurants = $this->countRestaurants($status, $search, $category);
        $totalPages = ceil($totalRestaurants / $limit);

        // Get statistics
        $stats = $this->getRestaurantStats();

        $this->render('admin/restaurants', [
            'title' => 'Restaurant Management - Time2Eat Admin',
            'restaurants' => $restaurants,
            'stats' => $stats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRestaurants' => $totalRestaurants,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'category' => $category
            ]
        ]);
    }

    /**
     * Show create restaurant form
     */
    public function create(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        // Get categories for dropdown
        $categories = $this->getCategories();
        
        // Get vendors for assignment
        $vendors = $this->userModel->getUsers('vendor', 'active');

        $this->render('admin/restaurants/create', [
            'title' => 'Create Restaurant - Time2Eat Admin',
            'categories' => $categories,
            'vendors' => $vendors
        ]);
    }

    /**
     * Store new restaurant
     */
    public function store(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Validate required fields
            $requiredFields = ['name', 'user_id', 'phone', 'address'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->jsonResponse(['success' => false, 'message' => "Field {$field} is required"], 400);
                    return;
                }
            }

            // Create restaurant data
            $restaurantData = [
                'user_id' => $_POST['user_id'],
                'name' => $_POST['name'],
                'slug' => $this->generateRestaurantSlug($_POST['name']),
                'description' => $_POST['description'] ?? null,
                'phone' => $_POST['phone'],
                'email' => $_POST['email'] ?? null,
                'address' => $_POST['address'],
                'city' => $_POST['city'] ?? 'Bamenda',
                'state' => $_POST['state'] ?? 'Northwest',
                'country' => 'Cameroon',
                'postal_code' => $_POST['postal_code'] ?? null,
                'latitude' => !empty($_POST['latitude']) ? (float)$_POST['latitude'] : 5.9631,
                'longitude' => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : 10.1591,
                'cuisine_type' => $_POST['cuisine_type'] ?? null,
                'category_id' => $_POST['category_id'] ?? null,
                'status' => $_POST['status'] ?? 'pending',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'delivery_fee' => $_POST['delivery_fee'] ?? 0,
                'minimum_order' => $_POST['minimum_order'] ?? 0,
                'delivery_time' => $_POST['delivery_time'] ?? 30,
                'opening_hours' => json_encode($_POST['opening_hours'] ?? []),
                'payment_methods' => json_encode($_POST['payment_methods'] ?? ['cash']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $restaurantId = $this->insertRecord('restaurants', $restaurantData);

            if ($restaurantId) {
                $this->jsonResponse([
                    'success' => true, 
                    'message' => 'Restaurant created successfully',
                    'restaurant_id' => $restaurantId
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to create restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error creating restaurant: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while creating restaurant'], 500);
        }
    }

    /**
     * Show restaurant details
     */
    public function show(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        $restaurant = $this->getRestaurantById($id);
        if (!$restaurant) {
            $this->redirect(url('/admin/restaurants?error=Restaurant not found'));
            return;
        }

        // Get restaurant statistics
        $stats = $this->getRestaurantDetailStats($id);

        $this->renderDashboard('admin/restaurants/show', [
            'title' => 'Restaurant Details - Time2Eat Admin',
            'restaurant' => $restaurant,
            'stats' => $stats
        ]);
    }

    /**
     * Show edit restaurant form
     */
    public function edit(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        $restaurant = $this->getRestaurantById($id);
        if (!$restaurant) {
            $this->redirect(url('/admin/restaurants?error=Restaurant not found'));
            return;
        }

        // Get categories and vendors
        $categories = $this->getCategories();
        $vendors = $this->userModel->getUsers('vendor', 'active');

        $this->renderDashboard('admin/restaurants/edit', [
            'title' => 'Edit Restaurant - Time2Eat Admin',
            'restaurant' => $restaurant,
            'categories' => $categories,
            'vendors' => $vendors
        ]);
    }

    /**
     * Update restaurant
     */
    public function update(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $restaurant = $this->getRestaurantById($id);
            if (!$restaurant) {
                $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found'], 404);
                return;
            }

            // Prepare update data
            $updateData = [];
            $allowedFields = [
                'name', 'description', 'phone', 'email', 'address', 'city', 'state',
                'postal_code', 'cuisine_type', 'category_id', 'status', 'delivery_fee',
                'delivery_fee_per_extra_km', 'delivery_radius', 'minimum_order',
                'delivery_time', 'latitude', 'longitude', 'commission_rate'
            ];

            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $updateData[$field] = $_POST[$field];
                }
            }

            // Handle special fields
            if (isset($_POST['name']) && $_POST['name'] !== $restaurant['name']) {
                $updateData['slug'] = $this->generateRestaurantSlug($_POST['name']);
            }

            // Convert commission rate from percentage to decimal
            if (isset($_POST['commission_rate'])) {
                $updateData['commission_rate'] = floatval($_POST['commission_rate']) / 100;
            }

            $updateData['is_active'] = isset($_POST['is_active']) ? 1 : 0;
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            // Handle JSON fields
            if (isset($_POST['opening_hours'])) {
                $updateData['opening_hours'] = json_encode($_POST['opening_hours']);
            }
            if (isset($_POST['payment_methods'])) {
                $updateData['payment_methods'] = json_encode($_POST['payment_methods']);
            }

            // Update restaurant
            $success = $this->update('restaurants', $updateData, ['id' => $id]) > 0;

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Restaurant updated successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error updating restaurant: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while updating restaurant'], 500);
        }
    }

    /**
     * Delete restaurant (soft delete)
     */
    public function destroy(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        try {
            $restaurant = $this->getRestaurantById($id);
            if (!$restaurant) {
                $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found'], 404);
                return;
            }
            // Hard delete restaurant
            $success = $this->delete('restaurants', ['id' => $id]) > 0;
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Restaurant deleted successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to delete restaurant'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error deleting restaurant: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while deleting restaurant'], 500);
        }
    }

    /**
     * Approve restaurant
     */
    public function approve(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $success = $this->update('restaurants', [
                'status' => 'active',
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]) > 0;

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Restaurant approved successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to approve restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error approving restaurant: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Reject restaurant
     */
    public function reject(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $success = $this->update('restaurants', [
                'status' => 'rejected',
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]) > 0;

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Restaurant rejected successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to reject restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error rejecting restaurant: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Suspend restaurant
     */
    public function suspend(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $success = $this->update('restaurants', [
                'status' => 'suspended',
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]) > 0;

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Restaurant suspended successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to suspend restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error suspending restaurant: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Activate restaurant
     */
    public function activate(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $success = $this->update('restaurants', [
                'status' => 'active',
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]) > 0;

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Restaurant activated successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to activate restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error activating restaurant: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Toggle restaurant status (suspend/activate)
     */
    public function toggleStatus(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $restaurant = $this->getRestaurantById($id);
            if (!$restaurant) {
                $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found'], 404);
                return;
            }

            $newStatus = ($restaurant['status'] === 'active') ? 'suspended' : 'active';
            $newIsActive = ($newStatus === 'active') ? 1 : 0;

            $success = $this->update('restaurants', [
                'status' => $newStatus,
                'is_active' => $newIsActive,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]) > 0;

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => "Restaurant {$newStatus} successfully"]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update restaurant status'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error toggling restaurant status: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    // Helper methods are now provided by RestaurantQueryTrait

    /**
     * Update restaurant commission rate
     */
    public function updateCommission(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Get restaurant
            $restaurant = $this->getRestaurantById($id);
            if (!$restaurant) {
                $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found'], 404);
                return;
            }

            // Get input data (JSON or POST)
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            if (!isset($input['commission_rate'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Commission rate is required'], 400);
                return;
            }

            $commissionRate = floatval($input['commission_rate']);

            // Validate commission rate
            if ($commissionRate < 0 || $commissionRate > 1) {
                $this->jsonResponse(['success' => false, 'message' => 'Commission rate must be between 0 and 1 (0% to 100%)'], 400);
                return;
            }

            // Update commission rate
            $updateData = [
                'commission_rate' => $commissionRate,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $success = $this->update('restaurants', $updateData, ['id' => $id]) > 0;

            if ($success) {
                // Log the commission rate change
                $this->logCommissionChange($id, $restaurant['commission_rate'] ?? 0.15, $commissionRate);

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Commission rate updated successfully',
                    'commission_rate' => $commissionRate
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update commission rate'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error updating commission rate: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while updating commission rate'], 500);
        }
    }

    /**
     * Log commission rate changes for audit trail
     */
    private function logCommissionChange(int $restaurantId, float $oldRate, float $newRate): void
    {
        try {
            $logData = [
                'restaurant_id' => $restaurantId,
                'admin_id' => $this->user['id'],
                'old_rate' => $oldRate,
                'new_rate' => $newRate,
                'changed_at' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];

            // Insert into commission_changes log table (create if doesn't exist)
            $this->query("
                CREATE TABLE IF NOT EXISTS commission_changes (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    restaurant_id INT NOT NULL,
                    admin_id INT NOT NULL,
                    old_rate DECIMAL(5,4) NOT NULL,
                    new_rate DECIMAL(5,4) NOT NULL,
                    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ip_address VARCHAR(45),
                    INDEX idx_restaurant_id (restaurant_id),
                    INDEX idx_admin_id (admin_id),
                    INDEX idx_changed_at (changed_at),
                    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
                    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $this->insertRecord('commission_changes', $logData);
        } catch (\Exception $e) {
            error_log("Error logging commission change: " . $e->getMessage());
            // Don't fail the main operation if logging fails
        }
    }


    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
