<?php

declare(strict_types=1);

namespace Time2Eat\Controllers\Admin;

require_once __DIR__ . '/../../../core/BaseController.php';

use core\BaseController;
use models\User;

/**
 * Admin Restaurant Management Controller
 * Handles CRUD operations for restaurant management in admin panel
 */
class RestaurantController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Display restaurants list with filtering and pagination
     */
    public function index(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect('/login');
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
            $this->redirect('/login');
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
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Validate required fields
            $requiredFields = ['name', 'user_id', 'phone', 'address'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                    return;
                }
            }

            // Create restaurant data
            $restaurantData = [
                'user_id' => $_POST['user_id'],
                'name' => $_POST['name'],
                'slug' => $this->generateSlug($_POST['name']),
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

            $restaurantId = $this->insert('restaurants', $restaurantData);

            if ($restaurantId) {
                $this->json([
                    'success' => true, 
                    'message' => 'Restaurant created successfully',
                    'restaurant_id' => $restaurantId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to create restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error creating restaurant: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while creating restaurant'], 500);
        }
    }

    /**
     * Show restaurant details
     */
    public function show(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $restaurant = $this->getRestaurantById($id);
        if (!$restaurant) {
            $this->redirect('/admin/restaurants?error=Restaurant not found');
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
            $this->redirect('/login');
            return;
        }

        $restaurant = $this->getRestaurantById($id);
        if (!$restaurant) {
            $this->redirect('/admin/restaurants?error=Restaurant not found');
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
     * Update restaurant (renamed to avoid conflict with BaseController::update)
     */
    public function updateRestaurant(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $restaurant = $this->getRestaurantById($id);
            if (!$restaurant) {
                $this->json(['success' => false, 'message' => 'Restaurant not found'], 404);
                return;
            }

            // Get input data (JSON or POST)
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            // Prepare update data
            $updateData = [];
            $allowedFields = [
                'name', 'description', 'phone', 'email', 'address', 'city', 'state', 
                'postal_code', 'cuisine_type', 'status', 'delivery_fee', 
                'minimum_order', 'delivery_time', 'latitude', 'longitude'
            ];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateData[$field] = $input[$field];
                }
            }

            // Handle special fields
            if (isset($input['name']) && $input['name'] !== $restaurant['name']) {
                $updateData['slug'] = $this->generateSlug($input['name']);
            }

            $updateData['is_active'] = isset($input['is_active']) && $input['is_active'] ? 1 : 0;
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            // Handle JSON fields
            if (isset($input['opening_hours'])) {
                $updateData['opening_hours'] = is_string($input['opening_hours']) ? $input['opening_hours'] : json_encode($input['opening_hours']);
            }
            if (isset($input['payment_methods'])) {
                $updateData['payment_methods'] = is_string($input['payment_methods']) ? $input['payment_methods'] : json_encode($input['payment_methods']);
            }

            // Update restaurant in database
            $stmt = $this->getDb()->prepare("UPDATE restaurants SET " . 
                implode(', ', array_map(fn($k) => "$k = ?", array_keys($updateData))) . 
                " WHERE id = ?");
            
            $params = array_values($updateData);
            $params[] = $id;
            $success = $stmt->execute($params);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Restaurant updated successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error updating restaurant: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while updating restaurant'], 500);
        }
    }

    /**
     * Delete restaurant (soft delete)
     */
    public function destroy(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $restaurant = $this->getRestaurantById($id);
            if (!$restaurant) {
                $this->json(['success' => false, 'message' => 'Restaurant not found'], 404);
                return;
            }

            // Soft delete restaurant
            $success = $this->update('restaurants', [
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]) > 0;

            if ($success) {
                $this->json(['success' => true, 'message' => 'Restaurant deleted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error deleting restaurant: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while deleting restaurant'], 500);
        }
    }

    // Helper methods
    private function getRestaurants(string $status, string $search, string $category, int $limit, int $offset): array
    {
        $conditions = ['r.deleted_at IS NULL'];
        $params = [];

        if ($status !== 'all') {
            $conditions[] = 'r.status = ?';
            $params[] = $status;
        }

        if (!empty($search)) {
            $conditions[] = '(r.name LIKE ? OR r.description LIKE ? OR r.cuisine_type LIKE ?)';
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if ($category !== 'all') {
            $conditions[] = 'r.cuisine_type = ?';
            $params[] = $category;
        }

        $whereClause = implode(' AND ', $conditions);
        $params[] = $limit;
        $params[] = $offset;

        $sql = "SELECT r.*, u.first_name, u.last_name, u.email as owner_email
                FROM restaurants r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE {$whereClause}
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";

        return $this->fetchAll($sql, $params);
    }

    private function countRestaurants(string $status, string $search, string $category): int
    {
        $conditions = ['deleted_at IS NULL'];
        $params = [];

        if ($status !== 'all') {
            $conditions[] = 'status = ?';
            $params[] = $status;
        }

        if (!empty($search)) {
            $conditions[] = '(name LIKE ? OR description LIKE ? OR cuisine_type LIKE ?)';
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if ($category !== 'all') {
            $conditions[] = 'cuisine_type = ?';
            $params[] = $category;
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT COUNT(*) as count FROM restaurants WHERE {$whereClause}";
        
        $result = $this->fetchRow($sql, $params);
        return $result ? (int)$result['count'] : 0;
    }

    private function getRestaurantStats(): array
    {
        try {
            $stats = [];

            // Total restaurants
            $result = $this->fetchRow("SELECT COUNT(*) as count FROM restaurants WHERE deleted_at IS NULL");
            $stats['total_restaurants'] = $result['count'] ?? 0;

            // Active restaurants
            $result = $this->fetchRow("SELECT COUNT(*) as count FROM restaurants WHERE status = 'approved' AND is_active = 1 AND deleted_at IS NULL");
            $stats['active_restaurants'] = $result['count'] ?? 0;

            // Pending restaurants
            $result = $this->fetchRow("SELECT COUNT(*) as count FROM restaurants WHERE status = 'pending' AND deleted_at IS NULL");
            $stats['pending_restaurants'] = $result['count'] ?? 0;

            // Average rating
            $result = $this->fetchRow("SELECT AVG(rating) as avg_rating FROM restaurants WHERE deleted_at IS NULL AND rating > 0");
            $stats['avg_rating'] = $result['avg_rating'] ?? 0;

            return $stats;

        } catch (\Exception $e) {
            error_log("Error getting restaurant stats: " . $e->getMessage());
            return [
                'total_restaurants' => 0,
                'active_restaurants' => 0,
                'pending_restaurants' => 0,
                'avg_rating' => 0
            ];
        }
    }

    private function getRestaurantById(int $id): ?array
    {
        $sql = "SELECT r.*, u.first_name, u.last_name, u.email as owner_email
                FROM restaurants r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.id = ? AND r.deleted_at IS NULL";
        
        return $this->fetchRow($sql, [$id]);
    }

    private function getRestaurantDetailStats(int $restaurantId): array
    {
        try {
            $stats = [];

            // Total orders
            $result = $this->fetchRow("SELECT COUNT(*) as count FROM orders WHERE restaurant_id = ?", [$restaurantId]);
            $stats['total_orders'] = $result['count'] ?? 0;

            // Total revenue
            $result = $this->fetchRow("SELECT SUM(total_amount) as revenue FROM orders WHERE restaurant_id = ? AND status = 'delivered'", [$restaurantId]);
            $stats['total_revenue'] = $result['revenue'] ?? 0;

            // Average order value
            if ($stats['total_orders'] > 0) {
                $stats['avg_order_value'] = $stats['total_revenue'] / $stats['total_orders'];
            } else {
                $stats['avg_order_value'] = 0;
            }

            // Menu items count
            $result = $this->fetchRow("SELECT COUNT(*) as count FROM menu_items WHERE restaurant_id = ? AND deleted_at IS NULL", [$restaurantId]);
            $stats['menu_items_count'] = $result['count'] ?? 0;

            return $stats;

        } catch (\Exception $e) {
            error_log("Error getting restaurant detail stats: " . $e->getMessage());
            return [];
        }
    }

    private function getCategories(): array
    {
        try {
            return $this->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
        } catch (\Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->fetchRow("SELECT id FROM restaurants WHERE slug = ?", [$slug])) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Approve restaurant
     */
    public function approve(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $stmt = $this->getDb()->prepare("UPDATE restaurants SET status = 'active', is_active = 1, updated_at = ? WHERE id = ?");
            $success = $stmt->execute([date('Y-m-d H:i:s'), $id]);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Restaurant approved successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to approve restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error approving restaurant: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Reject restaurant
     */
    public function reject(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $stmt = $this->getDb()->prepare("UPDATE restaurants SET status = 'rejected', is_active = 0, updated_at = ? WHERE id = ?");
            $success = $stmt->execute([date('Y-m-d H:i:s'), $id]);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Restaurant rejected successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to reject restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error rejecting restaurant: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Suspend restaurant
     */
    public function suspend(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $stmt = $this->getDb()->prepare("UPDATE restaurants SET status = 'suspended', is_active = 0, updated_at = ? WHERE id = ?");
            $success = $stmt->execute([date('Y-m-d H:i:s'), $id]);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Restaurant suspended successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to suspend restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error suspending restaurant: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Activate restaurant
     */
    public function activate(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $stmt = $this->getDb()->prepare("UPDATE restaurants SET status = 'active', is_active = 1, updated_at = ? WHERE id = ?");
            $success = $stmt->execute([date('Y-m-d H:i:s'), $id]);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Restaurant activated successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to activate restaurant'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error activating restaurant: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Toggle restaurant status (suspend/activate)
     */
    public function toggleStatus(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $restaurant = $this->getRestaurantById($id);
            if (!$restaurant) {
                $this->json(['success' => false, 'message' => 'Restaurant not found'], 404);
                return;
            }

            $newStatus = ($restaurant['status'] === 'active') ? 'suspended' : 'active';
            $newIsActive = ($newStatus === 'active') ? 1 : 0;

            $stmt = $this->getDb()->prepare("UPDATE restaurants SET status = ?, is_active = ?, updated_at = ? WHERE id = ?");
            $success = $stmt->execute([$newStatus, $newIsActive, date('Y-m-d H:i:s'), $id]);

            if ($success) {
                $this->json(['success' => true, 'message' => "Restaurant {$newStatus} successfully"]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update restaurant status'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error toggling restaurant status: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Fetch single row from database (wrapper for DatabaseTrait's fetchOne)
     */
    private function fetchRow(string $sql, array $params = []): ?array
    {
        return $this->fetchOne($sql, $params);
    }


    /**
     * Render admin dashboard view with dashboard layout
     */
    protected function renderDashboard(string $viewName, array $data = []): void
    {
        // Get current user and convert to array for view compatibility
        $currentUser = $this->getCurrentUser();
        if ($currentUser) {
            $userData = [
                'id' => $currentUser->id,
                'email' => $currentUser->email,
                'first_name' => $currentUser->first_name,
                'last_name' => $currentUser->last_name,
                'role' => $currentUser->role,
                'status' => $currentUser->status ?? 'active'
            ];
            
            // Add user data to view data if not already set
            if (!isset($data['user'])) {
                $data['user'] = $userData;
            }
            if (!isset($data['userRole'])) {
                $data['userRole'] = $currentUser->role;
            }
        }
        
        // Start output buffering to capture the dashboard content
        ob_start();

        // Extract data for the view
        extract($data);

        // Include the specific dashboard view
        // From: src/Time2Eat/Controllers/Admin/ go up 3 levels to src/, then views/
        $viewFile = __DIR__ . "/../../../views/{$viewName}.php";
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: {$viewName} (looked in: {$viewFile})");
        }

        // Get the content
        $content = ob_get_clean();

        // Render with dashboard layout
        $dashboardLayout = __DIR__ . '/../../../views/components/dashboard-layout.php';
        
        if (file_exists($dashboardLayout)) {
            include $dashboardLayout;
        } else {
            echo $content; // Fallback
        }
    }

}
