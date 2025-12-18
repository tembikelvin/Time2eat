<?php

namespace controllers\Api;

use core\Controller;

/**
 * Favorites API Controller
 * Handles user favorites (wishlist) API endpoints
 */
class FavoritesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get user's favorite items
     * GET /api/user/favorites
     */
    public function index(): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        // Check authentication
        if (!$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            return;
        }

        try {
            $user = $this->getCurrentUser();
            $userId = $user->id;

            // Get favorite menu items with restaurant info
            $sql = "
                SELECT 
                    w.id as wishlist_id,
                    w.created_at as added_at,
                    mi.id as menu_item_id,
                    mi.name as item_name,
                    mi.description as item_description,
                    mi.price,
                    mi.image as item_image,
                    mi.is_available,
                    r.id as restaurant_id,
                    r.name as restaurant_name,
                    r.image as restaurant_image,
                    r.rating as restaurant_rating,
                    r.delivery_fee,
                    r.minimum_order,
                    c.name as category_name
                FROM wishlists w
                INNER JOIN menu_items mi ON w.menu_item_id = mi.id
                INNER JOIN restaurants r ON mi.restaurant_id = r.id
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
            ";

            $favorites = $this->fetchAll($sql, [$userId]);

            // Group by restaurant
            $groupedFavorites = [];
            foreach ($favorites as $item) {
                $restaurantId = $item['restaurant_id'];
                
                if (!isset($groupedFavorites[$restaurantId])) {
                    $groupedFavorites[$restaurantId] = [
                        'restaurant_id' => $restaurantId,
                        'restaurant_name' => $item['restaurant_name'],
                        'restaurant_image' => $item['restaurant_image'],
                        'restaurant_rating' => $item['restaurant_rating'],
                        'delivery_fee' => $item['delivery_fee'],
                        'minimum_order' => $item['minimum_order'],
                        'items' => []
                    ];
                }
                
                $groupedFavorites[$restaurantId]['items'][] = [
                    'wishlist_id' => $item['wishlist_id'],
                    'menu_item_id' => $item['menu_item_id'],
                    'name' => $item['item_name'],
                    'description' => $item['item_description'],
                    'price' => $item['price'],
                    'image' => $item['item_image'],
                    'is_available' => (bool)$item['is_available'],
                    'category' => $item['category_name'],
                    'added_at' => $item['added_at']
                ];
            }

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'favorites' => array_values($groupedFavorites),
                    'total_items' => count($favorites),
                    'total_restaurants' => count($groupedFavorites)
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Favorites API error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch favorites'
            ], 500);
        }
    }

    /**
     * Add item to favorites
     * POST /api/user/favorites
     */
    public function store(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            return;
        }

        try {
            $user = $this->getCurrentUser();
            $userId = $user->id;

            $input = json_decode(file_get_contents('php://input'), true);
            $menuItemId = (int)($input['menu_item_id'] ?? 0);

            if (!$menuItemId) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Menu item ID is required'
                ], 400);
                return;
            }

            // Check if already in favorites
            $existsSql = "SELECT id FROM wishlists WHERE user_id = ? AND menu_item_id = ?";
            $exists = $this->fetchOne($existsSql, [$userId, $menuItemId]);

            if ($exists) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Item already in favorites'
                ], 400);
                return;
            }

            // Add to favorites
            $insertSql = "INSERT INTO wishlists (user_id, menu_item_id, created_at) VALUES (?, ?, NOW())";
            $this->query($insertSql, [$userId, $menuItemId]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Item added to favorites'
            ]);

        } catch (\Exception $e) {
            error_log("Add to favorites error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add to favorites'
            ], 500);
        }
    }

    /**
     * Remove item from favorites
     * DELETE /api/user/favorites/{id}
     */
    public function destroy(int $id): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            return;
        }

        try {
            $user = $this->getCurrentUser();
            $userId = $user->id;

            // Delete from favorites (verify ownership)
            $deleteSql = "DELETE FROM wishlists WHERE id = ? AND user_id = ?";
            $this->query($deleteSql, [$id, $userId]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Item removed from favorites'
            ]);

        } catch (\Exception $e) {
            error_log("Remove from favorites error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to remove from favorites'
            ], 500);
        }
    }

    /**
     * Check if item is in favorites
     * GET /api/user/favorites/check/{menu_item_id}
     */
    public function check(int $menuItemId): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => true,
                'is_favorite' => false
            ]);
            return;
        }

        try {
            $user = $this->getCurrentUser();
            $userId = $user->id;

            $sql = "SELECT id FROM wishlists WHERE user_id = ? AND menu_item_id = ?";
            $exists = $this->fetchOne($sql, [$userId, $menuItemId]);

            $this->jsonResponse([
                'success' => true,
                'is_favorite' => !empty($exists),
                'wishlist_id' => $exists['id'] ?? null
            ]);

        } catch (\Exception $e) {
            error_log("Check favorites error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to check favorites'
            ], 500);
        }
    }
}

