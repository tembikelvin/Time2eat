<?php

namespace controllers\Api;

use core\Controller;
use models\Restaurant;

/**
 * Restaurant API Controller
 * Handles public API endpoints for restaurants
 */
class RestaurantController extends Controller
{
    private $restaurantModel;

    public function __construct()
    {
        parent::__construct();
        $this->restaurantModel = new Restaurant();
    }

    /**
     * Get all active restaurants
     * GET /api/restaurants
     */
    public function index(): void
    {
        header('Content-Type: application/json');
        // No caching - always return fresh data
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(50, max(5, (int)($_GET['limit'] ?? 20)));
            $category = $_GET['category'] ?? null;
            $search = $_GET['search'] ?? null;

            // Get active restaurants
            $sql = "
                SELECT 
                    r.id,
                    r.name,
                    r.description,
                    r.image,
                    r.address,
                    r.phone,
                    r.rating,
                    r.total_reviews,
                    r.delivery_fee,
                    r.minimum_order,
                    r.delivery_time,
                    r.is_open,
                    r.cuisine_type,
                    GROUP_CONCAT(DISTINCT c.name) as categories
                FROM restaurants r
                LEFT JOIN menu_items mi ON r.id = mi.restaurant_id
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE r.status = 'active' AND r.is_active = 1
            ";

            $params = [];

            if ($category) {
                $sql .= " AND c.slug = ?";
                $params[] = $category;
            }

            if ($search) {
                $sql .= " AND (r.name LIKE ? OR r.description LIKE ? OR r.cuisine_type LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " GROUP BY r.id ORDER BY r.rating DESC, r.name ASC";

            // Add pagination
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $restaurants = $this->fetchAll($sql, $params);

            // Get total count
            $countSql = "
                SELECT COUNT(DISTINCT r.id) as total
                FROM restaurants r
                LEFT JOIN menu_items mi ON r.id = mi.restaurant_id
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE r.status = 'active' AND r.is_active = 1
            ";
            
            $countParams = [];
            if ($category) {
                $countSql .= " AND c.slug = ?";
                $countParams[] = $category;
            }
            if ($search) {
                $countSql .= " AND (r.name LIKE ? OR r.description LIKE ? OR r.cuisine_type LIKE ?)";
                $searchTerm = "%$search%";
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
            }

            $totalResult = $this->fetchOne($countSql, $countParams);
            $total = $totalResult['total'] ?? 0;

            $this->jsonResponse([
                'success' => true,
                'data' => $restaurants,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => (int)$total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Restaurant API error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch restaurants'
            ], 500);
        }
    }

    /**
     * Get single restaurant details
     * GET /api/restaurants/{id}
     */
    public function show(int $id): void
    {
        header('Content-Type: application/json');
        // No caching - always return fresh data
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $restaurant = $this->restaurantModel->getById($id);

            if (!$restaurant || $restaurant['status'] !== 'active') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Restaurant not found'
                ], 404);
                return;
            }

            // Get menu items count
            $menuCountSql = "SELECT COUNT(*) as count FROM menu_items WHERE restaurant_id = ? AND is_available = 1";
            $menuCount = $this->fetchOne($menuCountSql, [$id]);

            // Get categories
            $categoriesSql = "
                SELECT DISTINCT c.id, c.name, c.slug
                FROM categories c
                INNER JOIN menu_items mi ON c.id = mi.category_id
                WHERE mi.restaurant_id = ? AND mi.is_available = 1
                ORDER BY c.name
            ";
            $categories = $this->fetchAll($categoriesSql, [$id]);

            $restaurant['menu_items_count'] = (int)($menuCount['count'] ?? 0);
            $restaurant['categories'] = $categories;

            $this->jsonResponse([
                'success' => true,
                'data' => $restaurant
            ]);

        } catch (\Exception $e) {
            error_log("Restaurant API error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch restaurant'
            ], 500);
        }
    }

    /**
     * Get restaurant menu
     * GET /api/restaurants/{id}/menu
     */
    public function menu(int $id): void
    {
        header('Content-Type: application/json');
        // No caching - always return fresh data
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $category = $_GET['category'] ?? null;

            $sql = "
                SELECT 
                    mi.id,
                    mi.name,
                    mi.description,
                    mi.price,
                    mi.image,
                    mi.is_available,
                    mi.preparation_time,
                    c.name as category_name,
                    c.slug as category_slug
                FROM menu_items mi
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE mi.restaurant_id = ? AND mi.is_available = 1
            ";

            $params = [$id];

            if ($category) {
                $sql .= " AND c.slug = ?";
                $params[] = $category;
            }

            $sql .= " ORDER BY c.name, mi.name";

            $menuItems = $this->fetchAll($sql, $params);

            $this->jsonResponse([
                'success' => true,
                'data' => $menuItems
            ]);

        } catch (\Exception $e) {
            error_log("Restaurant menu API error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch menu'
            ], 500);
        }
    }
}

