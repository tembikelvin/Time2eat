<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/MenuItem.php';
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/MenuCategory.php';

use core\BaseController;

/**
 * Vendor Menu Management Controller
 * Handles menu item CRUD, bulk operations, and inventory management
 */
class VendorMenuController extends BaseController
{
    private $menuItemModel;
    private $restaurantModel;
    private $userModel;
    private $categoryModel;

    public function __construct()
    {
        parent::__construct();
        
        // Set dashboard layout instead of public layout
        $this->layout = 'dashboard';
        
        $this->menuItemModel = new \models\MenuItem();
        $this->restaurantModel = new \models\Restaurant();
        $this->userModel = new \models\User();
        $this->categoryModel = new \models\MenuCategory();
    }

    /**
     * Display vendor menu dashboard
     */
    public function dashboard(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getCurrentUser();
        $restaurant = $this->restaurantModel->getByVendorId($user->id);

        if (!$restaurant) {
            $this->redirect(url('/vendor/profile/create'));
            return;
        }

        // Get menu statistics
        $stats = $this->menuItemModel->getRestaurantStats($restaurant['id']);
        $recentItems = $this->menuItemModel->getRecentItems($restaurant['id'], 10);
        $lowStockItems = $this->menuItemModel->getLowStockItems($restaurant['id']);
        $categories = $this->menuItemModel->getCategoriesByRestaurant($restaurant['id']);

        $this->render('vendor/menu/dashboard', [
            'title' => 'Menu Management - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'stats' => $stats,
            'recentItems' => $recentItems,
            'lowStockItems' => $lowStockItems,
            'categories' => $categories
        ]);
    }

    /**
     * Display menu items list
     */
    public function index(): void
    {
        try {
            error_log("VendorMenuController::index() - Starting");
            
            // Enhanced session handling for production
            if (session_status() === PHP_SESSION_NONE) {
                $this->startSession();
                error_log("VendorMenuController::index() - Session started");
            } else {
                error_log("VendorMenuController::index() - Session already active: " . session_id());
            }
            
            $this->requireAuth();
            error_log("VendorMenuController::index() - Auth check passed");
            
            $this->requireRole('vendor');
            error_log("VendorMenuController::index() - Role check passed");

            $user = $this->getCurrentUser();
            if (!$user) {
                error_log("VendorMenuController::index() - No current user found");
                $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
                $this->redirect(url('/login'));
                return;
            }
            error_log("VendorMenuController::index() - User found: {$user->id}");

            $restaurant = $this->restaurantModel->getByVendorId($user->id);
            if (!$restaurant) {
                error_log("VendorMenuController::index() - No restaurant found for user {$user->id}");
                $this->redirect(url('/vendor/profile/create'));
                return;
            }
            error_log("VendorMenuController::index() - Restaurant found: {$restaurant['id']}");

            // Get filters and pagination
            $page = (int)($_GET['page'] ?? 1);
            $view = $_GET['view'] ?? 'menu';
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $category = $_GET['category'] ?? '';
            $status = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';

            error_log("VendorMenuController::index() - Fetching menu items");
            
            // Get menu items with filters
            $items = $this->menuItemModel->getRestaurantItems(
                $restaurant['id'],
                $limit,
                $offset,
                $category,
                $status,
                $search
            );
            error_log("VendorMenuController::index() - Menu items fetched: " . count($items));

            $totalItems = $this->menuItemModel->countRestaurantItems(
                $restaurant['id'],
                $category,
                $status,
                $search
            );
            error_log("VendorMenuController::index() - Total items: {$totalItems}");

            $totalPages = ceil($totalItems / $limit);
            $categories = $this->categoryModel->getByRestaurant($restaurant['id']);
            error_log("VendorMenuController::index() - Categories fetched: " . count($categories));

            // Get pending orders count for sidebar
            $pendingOrdersQuery = "SELECT COUNT(*) as count FROM orders WHERE restaurant_id = ? AND status IN ('pending', 'confirmed', 'preparing')";
            $pendingOrdersResult = $this->fetchOne($pendingOrdersQuery, [$restaurant['id']]);
            $pendingOrders = (int)($pendingOrdersResult['count'] ?? 0);

            error_log("VendorMenuController::index() - Rendering view");
            
            // Determine current page based on view parameter
            $currentPage = ($view === 'inventory') ? 'inventory' : 'menu';

            $this->render('vendor/menu/index', [
                'title' => ($view === 'inventory') ? 'Inventory Management - Time2Eat' : 'Menu Items - Time2Eat',
                'user' => $user,
                'restaurant' => $restaurant,
                'items' => $items,
                'menuItems' => $items, // Also pass as menuItems for compatibility
                'categories' => $categories,
                'currentPage' => $currentPage,
                'currentView' => $view,
                'paginationPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'stats' => [
                    'pendingOrders' => $pendingOrders,
                    'restaurant_id' => $restaurant['id']
                ],
                'filters' => [
                    'category' => $category,
                    'status' => $status,
                    'search' => $search
                ]
            ]);
            
            error_log("VendorMenuController::index() - View rendered");
            
        } catch (\Exception $e) {
            error_log("VendorMenuController::index() - Error: " . $e->getMessage());
            error_log("VendorMenuController::index() - Trace: " . $e->getTraceAsString());
            
            if ($this->isAjaxRequest()) {
                $this->jsonError('Internal server error', 500);
            } else {
                $this->renderErrorPage(500, 'Internal Server Error');
            }
        }
    }

    /**
     * Show create menu item form
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getCurrentUser();
        $restaurant = $this->restaurantModel->getByVendorId($user->id);

        if (!$restaurant) {
            $this->redirect(url('/vendor/profile/create'));
            return;
        }

        $categories = $this->categoryModel->getByRestaurant($restaurant['id']);

        $this->render('vendor/menu/create', [
            'title' => 'Add Menu Item - Time2Eat',
            'user' => $user,
            'userRole' => 'vendor',
            'restaurant' => $restaurant,
            'categories' => $categories
        ]);
    }

    /**
     * Store new menu item
     */
    public function store(): void
    {
        // CRITICAL: Clear output buffer FIRST before any headers or output
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Start fresh output buffering for JSON response
        ob_start();

        // CRITICAL: Check if headers already sent before setting new ones
        if (!headers_sent()) {
            // Prevent caching of menu creation (user-specific, must be real-time)
            header('Cache-Control: no-cache, no-store, must-revalidate, private');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        // Manual CSRF validation for multipart/form-data AJAX requests
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $postToken = $_POST['csrf_token'] ?? null;
        
        if (!$sessionToken || !$postToken || !hash_equals($sessionToken, $postToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'CSRF token mismatch. Please refresh the page and try again.'], 403);
            return;
        }

        if (!$this->isAuthenticated() || !$this->hasRole('vendor')) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getCurrentUser();

        if (!$user) {
            error_log('VendorMenuController::store - No user found in session');
            $this->jsonResponse(['success' => false, 'message' => 'User not authenticated'], 401);
            return;
        }

        error_log('VendorMenuController::store - User ID: ' . $user->id);

        try {
            $restaurant = $this->restaurantModel->getByVendorId($user->id);
            error_log('VendorMenuController::store - Restaurant: ' . ($restaurant ? json_encode($restaurant) : 'NULL'));
        } catch (\Exception $e) {
            error_log('VendorMenuController::store - Error getting restaurant: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
            return;
        }

        if (!$restaurant) {
            $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found for this vendor. Please contact support.'], 404);
            return;
        }

        $input = $this->getInput();
        error_log('VendorMenuController::store - Input data: ' . json_encode($input));

        $validation = $this->validateMenuItemData($input);

        if (!$validation['valid']) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation['errors']
            ], 400);
            return;
        }

        try {
            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->handleImageUpload($_FILES['image']);
                if (!$imagePath) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'Image upload failed'
                    ], 400);
                    return;
                }
            }

            // Generate slug from name (required by database)
            $slug = $this->generateSlug($input['name'], $restaurant['id']);
            
            // Create menu item
            // Use 'image' column (which exists) instead of 'image_url'
            $itemData = [
                'restaurant_id' => $restaurant['id'],
                'category_id' => $input['category_id'],
                'name' => $input['name'],
                'slug' => $slug, // Required by database
                'description' => $input['description'],
                'price' => (float)$input['price'],
                'image' => $imagePath, // Use 'image' column which exists in database
                'is_available' => isset($input['is_available']) ? 1 : 0,
                'stock_quantity' => (int)($input['stock_quantity'] ?? 0),
                'min_stock_level' => (int)($input['min_stock_level'] ?? 5),
                'preparation_time' => (int)($input['preparation_time'] ?? 15),
                'calories' => !empty($input['calories']) ? (int)$input['calories'] : null,
                'ingredients' => !empty($input['ingredients']) ? $input['ingredients'] : null,
                'allergens' => !empty($input['allergens']) ? json_encode(explode(',', trim($input['allergens']))) : json_encode([]),
                'is_vegetarian' => isset($input['is_vegetarian']) ? 1 : 0,
                'is_vegan' => isset($input['is_vegan']) ? 1 : 0,
                'is_gluten_free' => isset($input['is_gluten_free']) ? 1 : 0,
                'customization_options' => !empty($input['customization_options'])
                    ? json_encode(explode(',', $input['customization_options']))
                    : json_encode([])
            ];
            
            // Also set image_url if column exists (for compatibility)
            if ($imagePath) {
                $itemData['image_url'] = $imagePath;
            }
            
            $itemId = $this->menuItemModel->createItem($itemData);

            if ($itemId) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Menu item created successfully',
                    'item_id' => $itemId,
                    'redirect' => url('/vendor/menu')
                ]);
            } else {
                // Clean output buffer before error response
                while (ob_get_level()) {
                    ob_end_clean();
                }
                ob_start();
                
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create menu item'
                ], 500);
            }
        } catch (\Exception $e) {
            // Clean output buffer before error response
            while (ob_get_level()) {
                ob_end_clean();
            }
            ob_start();

            error_log('Menu item creation failed: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('File: ' . $e->getFile() . ' Line: ' . $e->getLine());

            // Return detailed error information
            $errorDetails = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => str_replace('E:\\Server\\www\\eat\\', '', $e->getFile()),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ];

            $this->jsonResponse($errorDetails, 500);
        }
    }

    /**
     * Show edit menu item form
     */
    public function edit($id = null): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        // Get ID from route parameters or method parameter
        $id = (int)($id ?? $_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect(url('/vendor/menu'));
            return;
        }

        $user = $this->getCurrentUser();
        $restaurant = $this->restaurantModel->getByVendorId($user->id);

        if (!$restaurant) {
            $this->redirect(url('/vendor/profile/create'));
            return;
        }

        $item = $this->menuItemModel->getById($id);
        if (!$item || $item['restaurant_id'] !== $restaurant['id']) {
            $this->redirect(url('/vendor/menu'));
            return;
        }

        $categories = $this->categoryModel->getByRestaurant($restaurant['id']);

        $this->render('vendor/menu/edit', [
            'title' => 'Edit Menu Item - Time2Eat',
            'user' => $user,
            'userRole' => 'vendor',
            'restaurant' => $restaurant,
            'item' => $item,
            'categories' => $categories
        ]);
    }

    /**
     * Update menu item (Resource route method)
     */
    public function updateItem($id = null): void
    {
        // Manual CSRF validation for multipart/form-data AJAX requests
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $postToken = $_POST['csrf_token'] ?? null;
        
        if (!$sessionToken || !$postToken || !hash_equals($sessionToken, $postToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'CSRF token mismatch. Please refresh the page and try again.'], 403);
            return;
        }

        if (!$this->isAuthenticated() || !$this->hasRole('vendor')) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Get ID from route parameters or method parameter
        $id = (int)($id ?? $_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid item ID'], 400);
            return;
        }

        $user = $this->getCurrentUser();
        $restaurant = $this->restaurantModel->getByVendorId($user->id);

        if (!$restaurant) {
            $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found'], 404);
            return;
        }

        $item = $this->menuItemModel->getById($id);
        if (!$item || $item['restaurant_id'] !== $restaurant['id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Menu item not found'], 404);
            return;
        }

        $input = $this->getInput();
        $validation = $this->validateMenuItemData($input, $id);

        if (!$validation['valid']) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation['errors']
            ], 400);
            return;
        }

        try {
            // Handle image upload
            // Use 'image' column (which exists) - fallback to 'image_url' if not found
            $imagePath = $item['image'] ?? $item['image_url'] ?? null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $newImagePath = $this->handleImageUpload($_FILES['image']);
                if ($newImagePath) {
                    // Delete old image
                    if ($imagePath && file_exists(PUBLIC_PATH . $imagePath)) {
                        unlink(PUBLIC_PATH . $imagePath);
                    }
                    $imagePath = $newImagePath;
                }
            }

            // Update menu item
            $updateData = [
                'category_id' => $input['category_id'],
                'name' => $input['name'],
                'description' => $input['description'],
                'price' => (float)$input['price'],
                'image' => $imagePath, // Use 'image' column which exists in database
                'is_available' => isset($input['is_available']) ? 1 : 0,
                'stock_quantity' => (int)($input['stock_quantity'] ?? 0),
                'min_stock_level' => (int)($input['min_stock_level'] ?? 5),
                'preparation_time' => (int)($input['preparation_time'] ?? 15),
                'calories' => !empty($input['calories']) ? (int)$input['calories'] : null,
                'ingredients' => $input['ingredients'] ?? null,
                'allergens' => $input['allergens'] ?? null,
                'is_vegetarian' => isset($input['is_vegetarian']) ? 1 : 0,
                'is_vegan' => isset($input['is_vegan']) ? 1 : 0,
                'is_gluten_free' => isset($input['is_gluten_free']) ? 1 : 0,
                'customization_options' => !empty($input['customization_options']) 
                    ? json_encode(explode(',', $input['customization_options'])) 
                    : null
            ];
            
            // Generate slug from name if name changed (required by database)
            if (isset($input['name']) && $input['name'] !== $item['name']) {
                $updateData['slug'] = $this->generateSlug($input['name'], $restaurant['id']);
            }
            
            // Also set image_url if column exists (for compatibility)
            if ($imagePath) {
                $updateData['image_url'] = $imagePath;
            }
            
            $success = $this->menuItemModel->updateItem($id, $updateData);

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Menu item updated successfully',
                    'redirect' => url('/vendor/menu')
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update menu item'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logError('Menu item update failed', [
                'item_id' => $id,
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while updating the menu item'
            ], 500);
        }
    }

    /**
     * Delete menu item (Resource route method)
     */
    public function destroy($id = null): void
    {
        // Validate CSRF token for AJAX requests
        if (!$this->verifyCsrfToken()) {
            $this->jsonResponse(['success' => false, 'message' => 'CSRF token mismatch'], 403);
            return;
        }

        // Log the request for debugging
        error_log('DELETE request received - ID parameter: ' . var_export($id, true));
        error_log('DELETE request - GET params: ' . json_encode($_GET));
        error_log('DELETE request - POST params: ' . json_encode($_POST));
        error_log('DELETE request - REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);

        if (!$this->isAuthenticated() || !$this->hasRole('vendor')) {
            error_log('DELETE failed: Unauthorized');
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized', 'error' => 'User not authenticated or not a vendor'], 401);
            return;
        }

        // Get ID from route parameters or method parameter
        $id = (int)($id ?? $_GET['id'] ?? $_POST['id'] ?? 0);
        error_log('DELETE - Parsed ID: ' . $id);

        if (!$id) {
            error_log('DELETE failed: Invalid ID');
            $this->jsonResponse(['success' => false, 'message' => 'Invalid item ID', 'error' => 'ID is 0 or null'], 400);
            return;
        }

        $user = $this->getCurrentUser();
        $restaurant = $this->restaurantModel->getByVendorId($user->id);

        if (!$restaurant) {
            error_log('DELETE failed: Restaurant not found for vendor ID ' . $user->id);
            $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found', 'error' => 'No restaurant associated with this vendor'], 404);
            return;
        }

        $item = $this->menuItemModel->getById($id);
        error_log('DELETE - Item found: ' . ($item ? 'Yes (ID: ' . $item['id'] . ', Restaurant: ' . $item['restaurant_id'] . ')' : 'No'));

        if (!$item || $item['restaurant_id'] !== $restaurant['id']) {
            error_log('DELETE failed: Menu item not found or does not belong to restaurant');
            $this->jsonResponse(['success' => false, 'message' => 'Menu item not found', 'error' => 'Item does not exist or does not belong to your restaurant'], 404);
            return;
        }

        try {
            error_log('DELETE - Attempting to delete item ID: ' . $id);
            $success = $this->menuItemModel->deleteItem($id);
            error_log('DELETE - Delete result: ' . ($success ? 'Success' : 'Failed'));

            if ($success) {
                // Delete associated image (check both image and image_url columns)
                $imageToDelete = $item['image'] ?? $item['image_url'] ?? null;
                if ($imageToDelete && file_exists(PUBLIC_PATH . $imageToDelete)) {
                    unlink(PUBLIC_PATH . $imageToDelete);
                    error_log('DELETE - Image deleted: ' . $imageToDelete);
                }

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Menu item deleted successfully'
                ]);
            } else {
                error_log('DELETE failed: Database delete returned false');
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to delete menu item',
                    'error' => 'Database delete operation returned false'
                ], 500);
            }
        } catch (\Exception $e) {
            error_log('DELETE exception: ' . $e->getMessage());
            error_log('DELETE exception trace: ' . $e->getTraceAsString());

            $this->logError('Menu item deletion failed', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while deleting the menu item',
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }

    /**
     * Toggle item availability
     */
    public function toggleAvailability(int $id): void
    {
        // Validate CSRF token for AJAX requests
        if (!$this->verifyCsrfToken()) {
            $this->jsonResponse(['success' => false, 'message' => 'CSRF token mismatch'], 403);
            return;
        }

        error_log('TOGGLE request received - ID: ' . $id);

        if (!$this->isAuthenticated() || !$this->hasRole('vendor')) {
            error_log('TOGGLE failed: Unauthorized');
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized', 'error' => 'User not authenticated or not a vendor'], 401);
            return;
        }

        $user = $this->getCurrentUser();
        $restaurant = $this->restaurantModel->getByVendorId($user->id);

        if (!$restaurant) {
            error_log('TOGGLE failed: Restaurant not found for vendor ID ' . $user->id);
            $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found', 'error' => 'No restaurant associated with this vendor'], 404);
            return;
        }

        $item = $this->menuItemModel->getById($id);
        if (!$item || $item['restaurant_id'] !== $restaurant['id']) {
            error_log('TOGGLE failed: Menu item not found or does not belong to restaurant');
            $this->jsonResponse(['success' => false, 'message' => 'Menu item not found', 'error' => 'Item does not exist or does not belong to your restaurant'], 404);
            return;
        }

        try {
            $newStatus = $item['is_available'] ? 0 : 1;
            error_log('TOGGLE - Changing availability from ' . $item['is_available'] . ' to ' . $newStatus);
            $success = $this->menuItemModel->updateItem($id, ['is_available' => $newStatus]);
            error_log('TOGGLE - Update result: ' . ($success ? 'Success' : 'Failed'));

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Item availability updated',
                    'is_available' => $newStatus
                ]);
            } else {
                error_log('TOGGLE failed: Database update returned false');
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update availability',
                    'error' => 'Database update operation returned false'
                ], 500);
            }
        } catch (\Exception $e) {
            error_log('TOGGLE exception: ' . $e->getMessage());

            $this->logError('Toggle availability failed', [
                'item_id' => $id,
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }

    /**
     * Update stock quantity
     */
    public function updateStock(int $id): void
    {
        // Validate CSRF token for AJAX requests
        if (!$this->verifyCsrfToken()) {
            $this->jsonResponse(['success' => false, 'message' => 'CSRF token mismatch'], 403);
            return;
        }

        error_log('STOCK UPDATE request received - ID: ' . $id);

        if (!$this->isAuthenticated() || !$this->hasRole('vendor')) {
            error_log('STOCK UPDATE failed: Unauthorized');
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized', 'error' => 'User not authenticated or not a vendor'], 401);
            return;
        }

        $user = $this->getCurrentUser();
        $restaurant = $this->restaurantModel->getByVendorId($user->id);

        if (!$restaurant) {
            error_log('STOCK UPDATE failed: Restaurant not found for vendor ID ' . $user->id);
            $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found', 'error' => 'No restaurant associated with this vendor'], 404);
            return;
        }

        $item = $this->menuItemModel->getById($id);
        if (!$item || $item['restaurant_id'] !== $restaurant['id']) {
            error_log('STOCK UPDATE failed: Menu item not found or does not belong to restaurant');
            $this->jsonResponse(['success' => false, 'message' => 'Menu item not found', 'error' => 'Item does not exist or does not belong to your restaurant'], 404);
            return;
        }

        $input = $this->getJsonInput();
        $stockQuantity = (int)($input['stock_quantity'] ?? 0);
        error_log('STOCK UPDATE - Requested quantity: ' . $stockQuantity);

        if ($stockQuantity < 0) {
            error_log('STOCK UPDATE failed: Invalid stock quantity');
            $this->jsonResponse([
                'success' => false,
                'message' => 'Stock quantity cannot be negative',
                'error' => 'Stock quantity must be 0 or greater'
            ], 400);
            return;
        }

        try {
            $updateData = ['stock_quantity' => $stockQuantity];

            // Auto-disable if out of stock
            if ($stockQuantity === 0) {
                $updateData['is_available'] = 0;
                error_log('STOCK UPDATE - Auto-disabling item (stock = 0)');
            } elseif ($stockQuantity > 0 && !$item['is_available']) {
                $updateData['is_available'] = 1;
                error_log('STOCK UPDATE - Auto-enabling item (stock > 0)');
            }

            error_log('STOCK UPDATE - Update data: ' . json_encode($updateData));
            $success = $this->menuItemModel->updateItem($id, $updateData);
            error_log('STOCK UPDATE - Update result: ' . ($success ? 'Success' : 'Failed'));

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Stock updated successfully',
                    'stock_quantity' => $stockQuantity,
                    'is_available' => $updateData['is_available'] ?? $item['is_available']
                ]);
            } else {
                error_log('STOCK UPDATE failed: Database update returned false');
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update stock',
                    'error' => 'Database update operation returned false'
                ], 500);
            }
        } catch (\Exception $e) {
            error_log('STOCK UPDATE exception: ' . $e->getMessage());

            $this->logError('Stock update failed', [
                'item_id' => $id,
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }

    /**
     * Bulk CSV import
     */
    public function importCsv(): void
    {
        // Manual CSRF validation for multipart/form-data AJAX requests
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $postToken = $_POST['csrf_token'] ?? null;
        
        if (!$sessionToken || !$postToken || !hash_equals($sessionToken, $postToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'CSRF token mismatch. Please refresh the page and try again.'], 403);
            return;
        }

        if (!$this->isAuthenticated() || !$this->hasRole('vendor')) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getCurrentUser();
        $restaurant = $this->restaurantModel->getByVendorId($user->id);

        if (!$restaurant) {
            $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found'], 404);
            return;
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'No CSV file uploaded or upload error'
            ], 400);
            return;
        }

        try {
            $results = $this->processCsvImport($_FILES['csv_file'], $restaurant['id']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'CSV import completed',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            $this->logError('CSV import failed', [
                'restaurant_id' => $restaurant['id'],
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'CSV import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show bulk import form
     */
    public function showImport(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getCurrentUser();
        $restaurant = $this->restaurantModel->getByVendorId($user->id);

        if (!$restaurant) {
            $this->redirect(url('/vendor/profile/create'));
            return;
        }

        $categories = $this->categoryModel->getByRestaurant($restaurant['id']);

        $this->render('vendor/menu/import', [
            'title' => 'Import Menu Items - Time2Eat',
            'user' => $user,
            'userRole' => 'vendor',
            'restaurant' => $restaurant,
            'categories' => $categories
        ]);
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $csvContent = $this->generateCsvTemplate();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="menu_items_template.csv"');
        header('Content-Length: ' . strlen($csvContent));

        echo $csvContent;
        exit;
    }

    /**
     * Handle image upload with GD processing
     */
    private function handleImageUpload(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        if ($file['size'] > $maxSize) {
            return null;
        }

        $uploadDir = PUBLIC_PATH . '/images/menu/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('menu_') . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Create image resource
        switch ($file['type']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($file['tmp_name']);
                break;
            default:
                return null;
        }

        if (!$image) {
            return null;
        }

        // Resize image to max 800x600
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        $maxWidth = 800;
        $maxHeight = 600;

        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG
            if ($file['type'] === 'image/png') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
            }

            imagecopyresampled(
                $resizedImage, $image,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $originalWidth, $originalHeight
            );

            imagedestroy($image);
            $image = $resizedImage;
        }

        // Save optimized image
        $success = false;
        switch ($file['type']) {
            case 'image/jpeg':
                $success = imagejpeg($image, $filepath, 85);
                break;
            case 'image/png':
                $success = imagepng($image, $filepath, 6);
                break;
            case 'image/webp':
                $success = imagewebp($image, $filepath, 85);
                break;
        }

        imagedestroy($image);

        return $success ? '/images/menu/' . $filename : null;
    }

    /**
     * Validate menu item data
     */
    private function validateMenuItemData(array $data, ?int $itemId = null): array
    {
        $errors = [];

        // Required fields
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'Name must be less than 255 characters';
        }

        if (empty($data['description'])) {
            $errors['description'] = 'Description is required';
        }

        if (empty($data['price']) || !is_numeric($data['price']) || (float)$data['price'] <= 0) {
            $errors['price'] = 'Valid price is required';
        }

        if (empty($data['category_id']) || !is_numeric($data['category_id'])) {
            $errors['category_id'] = 'Category is required';
        }

        // Optional numeric fields
        if (!empty($data['calories']) && (!is_numeric($data['calories']) || (int)$data['calories'] < 0)) {
            $errors['calories'] = 'Calories must be a positive number';
        }

        if (!empty($data['preparation_time']) && (!is_numeric($data['preparation_time']) || (int)$data['preparation_time'] < 1)) {
            $errors['preparation_time'] = 'Preparation time must be at least 1 minute';
        }

        if (!empty($data['stock_quantity']) && (!is_numeric($data['stock_quantity']) || (int)$data['stock_quantity'] < 0)) {
            $errors['stock_quantity'] = 'Stock quantity must be a non-negative number';
        }

        if (!empty($data['min_stock_level']) && (!is_numeric($data['min_stock_level']) || (int)$data['min_stock_level'] < 0)) {
            $errors['min_stock_level'] = 'Minimum stock level must be a non-negative number';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Process CSV import
     */
    private function processCsvImport(array $file, int $restaurantId): array
    {
        $results = [
            'total' => 0,
            'imported' => 0,
            'errors' => []
        ];

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            throw new \Exception('Could not open CSV file');
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception('Invalid CSV format');
        }

        $expectedHeaders = [
            'name', 'description', 'price', 'category_name', 'stock_quantity',
            'preparation_time', 'calories', 'ingredients', 'allergens',
            'is_vegetarian', 'is_vegan', 'is_gluten_free', 'customization_options'
        ];

        // Validate headers
        foreach ($expectedHeaders as $expected) {
            if (!in_array($expected, $headers)) {
                fclose($handle);
                throw new \Exception("Missing required column: $expected");
            }
        }

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $results['total']++;

            try {
                $data = array_combine($headers, $row);

                // Find or create category
                $categoryId = $this->findOrCreateCategory($data['category_name'], $restaurantId);
                if (!$categoryId) {
                    $results['errors'][] = "Row $rowNumber: Invalid category";
                    continue;
                }

                // Generate slug from name (required by database)
                $slug = $this->generateSlug(trim($data['name']), $restaurantId);
                
                // Prepare item data
                $itemData = [
                    'restaurant_id' => $restaurantId,
                    'category_id' => $categoryId,
                    'name' => trim($data['name']),
                    'slug' => $slug, // Required by database
                    'description' => trim($data['description']),
                    'price' => (float)$data['price'],
                    'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
                    'preparation_time' => (int)($data['preparation_time'] ?? 15),
                    'calories' => !empty($data['calories']) ? (int)$data['calories'] : null,
                    'ingredients' => !empty($data['ingredients']) ? trim($data['ingredients']) : null,
                    'allergens' => !empty($data['allergens']) ? trim($data['allergens']) : null,
                    'is_vegetarian' => $this->parseBooleanValue($data['is_vegetarian'] ?? ''),
                    'is_vegan' => $this->parseBooleanValue($data['is_vegan'] ?? ''),
                    'is_gluten_free' => $this->parseBooleanValue($data['is_gluten_free'] ?? ''),
                    'customization_options' => !empty($data['customization_options'])
                        ? json_encode(array_map('trim', explode(',', $data['customization_options'])))
                        : null,
                    'is_available' => 1
                ];

                // Validate data
                $validation = $this->validateMenuItemData($itemData);
                if (!$validation['valid']) {
                    $results['errors'][] = "Row $rowNumber: " . implode(', ', $validation['errors']);
                    continue;
                }

                // Create item
                $itemId = $this->menuItemModel->createItem($itemData);
                if ($itemId) {
                    $results['imported']++;
                } else {
                    $results['errors'][] = "Row $rowNumber: Failed to create item";
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Row $rowNumber: " . $e->getMessage();
            }
        }

        fclose($handle);
        return $results;
    }

    /**
     * Find or create category
     */
    private function findOrCreateCategory(string $categoryName, int $restaurantId): ?int
    {
        $category = $this->categoryModel->findByName($categoryName, $restaurantId);

        if ($category) {
            return $category['id'];
        }

        // Create new category
        return $this->categoryModel->create([
            'restaurant_id' => $restaurantId,
            'name' => $categoryName,
            'description' => "Auto-created category for $categoryName",
            'is_active' => 1
        ]);
    }

    /**
     * Parse boolean value from CSV
     */
    private function parseBooleanValue(string $value): int
    {
        $value = strtolower(trim($value));
        return in_array($value, ['1', 'true', 'yes', 'y']) ? 1 : 0;
    }

    /**
     * Generate CSV template
     */
    private function generateCsvTemplate(): string
    {
        $headers = [
            'name', 'description', 'price', 'category_name', 'stock_quantity',
            'preparation_time', 'calories', 'ingredients', 'allergens',
            'is_vegetarian', 'is_vegan', 'is_gluten_free', 'customization_options'
        ];

        $sampleData = [
            'Jollof Rice', 'Delicious Nigerian jollof rice with chicken', '2500', 'Main Dishes', '50',
            '20', '450', 'Rice, Chicken, Tomatoes, Onions, Spices', 'None',
            'no', 'no', 'yes', 'Extra chicken, Spicy level'
        ];

        $csv = implode(',', $headers) . "\n";
        $csv .= implode(',', array_map(function($value) {
            return '"' . str_replace('"', '""', $value) . '"';
        }, $sampleData)) . "\n";

        return $csv;
    }
    
    /**
     * Generate unique slug for menu item
     */
    private function generateSlug(string $name, int $restaurantId): string
    {
        // Create base slug from name
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = preg_replace('/-+/', '-', $slug); // Replace multiple dashes with single
        $slug = trim($slug, '-'); // Remove leading/trailing dashes
        
        // Ensure slug is not empty
        if (empty($slug)) {
            $slug = 'menu-item-' . time();
        }
        
        // Check if slug exists for this restaurant and make it unique
        $originalSlug = $slug;
        $counter = 1;
        
        try {
            while (true) {
                $stmt = $this->getDb()->prepare("SELECT id FROM menu_items WHERE restaurant_id = ? AND slug = ?");
                $stmt->execute([$restaurantId, $slug]);
                $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$existing) {
                    break; // Slug is unique
                }
                
                // Slug exists, append counter
                $slug = $originalSlug . '-' . $counter;
                $counter++;
                
                // Safety limit
                if ($counter > 1000) {
                    $slug = $originalSlug . '-' . time();
                    break;
                }
            }
        } catch (\Exception $e) {
            // If check fails, use timestamp as fallback
            error_log('Slug generation error: ' . $e->getMessage());
            $slug = $originalSlug . '-' . time();
        }
        
        return $slug;
    }
}
