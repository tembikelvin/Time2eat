<?php

declare(strict_types=1);

namespace controllers;

// Include required files directly
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../traits/DatabaseTrait.php';

use core\BaseController;

/**
 * Browse Controller
 * Handles restaurant browsing, search, and category filtering
 */
class BrowseController extends BaseController
{
    public function index()
    {
        try {
            // Get search and filter parameters
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';
            $sort = $_GET['sort'] ?? 'rating';
            $page = (int)($_GET['page'] ?? 1);
            $limit = 12;
            $offset = ($page - 1) * $limit;

            // Get featured restaurants
            $featured_restaurants = $this->getFeaturedRestaurants();

            // Get popular categories
            $categories = $this->getPopularCategories();

            // Get filtered restaurants with pagination
            $restaurants = $this->getFilteredRestaurants($search, $category, $sort, $limit, $offset);
            $total_restaurants = $this->getTotalFilteredRestaurants($search, $category);
            $total_pages = ceil($total_restaurants / $limit);

            $data = [
                'title' => 'Browse Restaurants - Time2Eat',
                'description' => 'Discover amazing restaurants and delicious food in Bamenda',
                'featured_restaurants' => $featured_restaurants,
                'categories' => $categories,
                'restaurants' => $restaurants,
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_restaurants' => $total_restaurants,
                'search' => $search,
                'category' => $category,
                'sort' => $sort
            ];

            $this->view('browse/index', $data);

        } catch (\Exception $e) {
            error_log("Browse index error: " . $e->getMessage());
            $this->view('errors/500', ['error' => 'Unable to load restaurants']);
        }
    }
    
    public function searchRestaurants()
    {
        $query = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');
        $location = trim($_GET['location'] ?? '');
        
        if (empty($query) && empty($category)) {
            return $this->index();
        }
        
        try {
            $restaurants = $this->performRestaurantSearch($query, $category, $location);
            $categories = $this->getPopularCategories();
            
            $data = [
                'title' => 'Search Results - Time2Eat',
                'description' => 'Search results for restaurants and food',
                'restaurants' => $restaurants,
                'categories' => $categories,
                'search_query' => $query,
                'selected_category' => $category,
                'selected_location' => $location,
                'results_count' => count($restaurants)
            ];
            
            $this->view('browse/search', $data);
            
        } catch (\Exception $e) {
            error_log("Browse search error: " . $e->getMessage());
            $this->view('errors/500', ['error' => 'Search temporarily unavailable']);
        }
    }
    
    public function category($slug)
    {
        try {
            $category = $this->getCategoryBySlug($slug);
            
            if (!$category) {
                http_response_code(404);
                $this->view('errors/404');
                return;
            }
            
            $restaurants = $this->getRestaurantsByCategory($category['id']);
            
            $data = [
                'title' => $category['name'] . ' Restaurants - Time2Eat',
                'description' => 'Find the best ' . $category['name'] . ' restaurants in Bamenda',
                'category' => $category,
                'restaurants' => $restaurants
            ];
            
            $this->view('browse/category', $data);
            
        } catch (\Exception $e) {
            error_log("Browse category error: " . $e->getMessage());
            $this->view('errors/500', ['error' => 'Unable to load category']);
        }
    }
    
    public function restaurant($id)
    {
        try {
            $restaurant = $this->getRestaurantById((int)$id);
            
            if (!$restaurant) {
                http_response_code(404);
                $this->view('errors/404');
                return;
            }
            
            $menu_items = $this->getRestaurantMenu($restaurant['id']);
            $reviews = $this->getRestaurantReviews($restaurant['id'], 5);
            
            $data = [
                'title' => $restaurant['name'] . ' - Time2Eat',
                'description' => $restaurant['description'] ?? 'Order from ' . $restaurant['name'],
                'restaurant' => $restaurant,
                'menu_items' => $menu_items,
                'reviews' => $reviews
            ];
            
            $this->view('browse/restaurant', $data);
            
        } catch (\Exception $e) {
            error_log("Browse restaurant error: " . $e->getMessage());
            $this->view('errors/500', ['error' => 'Unable to load restaurant']);
        }
    }
    
    // Helper methods
    public function getFeaturedRestaurants()
    {
        try {
            $stmt = $this->getDb()->prepare("
                SELECT r.*,
                       COALESCE(AVG(rv.rating), r.rating) as avg_rating,
                       COUNT(rv.id) as review_count
                FROM restaurants r
                LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant' AND rv.status = 'approved'
                WHERE r.status = 'active' AND r.deleted_at IS NULL AND r.is_featured = 1
                GROUP BY r.id
                ORDER BY avg_rating DESC, r.name ASC
                LIMIT 6
            ");
            $stmt->execute();
            $restaurants = $stmt->fetchAll();
            
            // Add missing fields for compatibility with view
            foreach ($restaurants as &$restaurant) {
                $restaurant['image'] = $restaurant['image'] ?? $restaurant['cover_image'] ?? '/public/images/fallback-food.jpg';
                $restaurant['rating'] = $restaurant['avg_rating'];
                $restaurant['featured_dishes'] = $this->getRestaurantFeaturedDishes($restaurant['id']);
            }
            
            return $restaurants;
        } catch (\Exception $e) {
            error_log("Error fetching featured restaurants: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPopularCategories()
    {
        try {
            $hasCategoryId = $this->hasCategoryId();
            
            if ($hasCategoryId) {
                // Use category_id if it exists
                $stmt = $this->getDb()->prepare("
                    SELECT c.*, COUNT(r.id) as restaurant_count
                    FROM categories c
                    LEFT JOIN restaurants r ON c.id = r.category_id AND r.status = 'active' AND r.deleted_at IS NULL
                    WHERE c.is_active = 1
                    GROUP BY c.id
                    HAVING restaurant_count > 0
                    ORDER BY restaurant_count DESC, c.name ASC
                    LIMIT 8
                ");
            } else {
                // Use cuisine_type if category_id doesn't exist
                $stmt = $this->getDb()->prepare("
                    SELECT DISTINCT cuisine_type as name, cuisine_type as slug, cuisine_type as id, COUNT(*) as restaurant_count
                    FROM restaurants 
                    WHERE status = 'active' AND deleted_at IS NULL AND cuisine_type IS NOT NULL AND cuisine_type != ''
                    GROUP BY cuisine_type
                    ORDER BY restaurant_count DESC, cuisine_type ASC
                    LIMIT 8
                ");
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }
    
    private function getRestaurants($limit = 12, $offset = 0)
    {
        try {
            $stmt = $this->getDb()->prepare("
                SELECT r.*,
                       COALESCE(AVG(rv.rating), r.rating) as avg_rating,
                       COUNT(rv.id) as review_count
                FROM restaurants r
                LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant' AND rv.status = 'approved'
                WHERE r.status = 'active' AND r.deleted_at IS NULL
                GROUP BY r.id
                ORDER BY r.is_featured DESC, avg_rating DESC, r.name ASC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $restaurants = $stmt->fetchAll();
            
            // Add missing fields for compatibility with view
            foreach ($restaurants as &$restaurant) {
                $restaurant['image'] = $restaurant['image'] ?? $restaurant['cover_image'] ?? '/public/images/fallback-food.jpg';
                $restaurant['rating'] = $restaurant['avg_rating'];
                $restaurant['featured_dishes'] = $this->getRestaurantFeaturedDishes($restaurant['id']);
            }
            
            return $restaurants;
        } catch (\Exception $e) {
            error_log("Error fetching restaurants: " . $e->getMessage());
            return [];
        }
    }

    public function getFilteredRestaurants($search = '', $category = '', $sort = 'rating', $limit = 12, $offset = 0)
    {
        try {
            $hasCategoryId = $this->hasCategoryId();
            
            $sql = "
                SELECT r.*,
                       COALESCE(AVG(rv.rating), r.rating) as avg_rating,
                       COUNT(rv.id) as review_count
                FROM restaurants r
                LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant' AND rv.status = 'approved'
                WHERE r.status = 'active' AND r.deleted_at IS NULL
            ";

            $params = [];

            // Add search filter
            if (!empty($search)) {
                $sql .= " AND (r.name LIKE ? OR r.description LIKE ? OR r.cuisine_type LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            // Add category filter
            if (!empty($category)) {
                if ($hasCategoryId) {
                    $sql .= " AND r.category_id = ?";
                } else {
                    $sql .= " AND r.cuisine_type = ?";
                }
                $params[] = $category;
            }

            $sql .= " GROUP BY r.id";

            // Add sorting
            switch ($sort) {
                case 'rating':
                    $sql .= " ORDER BY avg_rating DESC, r.name ASC";
                    break;
                case 'name':
                    $sql .= " ORDER BY r.name ASC";
                    break;
                case 'newest':
                    $sql .= " ORDER BY r.created_at DESC";
                    break;
                case 'popular':
                    $sql .= " ORDER BY review_count DESC, avg_rating DESC";
                    break;
                default:
                    $sql .= " ORDER BY r.is_featured DESC, avg_rating DESC, r.name ASC";
            }

            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute($params);
            $restaurants = $stmt->fetchAll();
            
            // Add missing fields for compatibility with view
            foreach ($restaurants as &$restaurant) {
                $restaurant['image'] = $restaurant['image'] ?? $restaurant['cover_image'] ?? '/public/images/fallback-food.jpg';
                $restaurant['rating'] = $restaurant['avg_rating'];
                $restaurant['featured_dishes'] = $this->getRestaurantFeaturedDishes($restaurant['id']);
            }
            
            return $restaurants;
        } catch (\Exception $e) {
            error_log("Error fetching filtered restaurants: " . $e->getMessage());
            return [];
        }
    }

    private function getTotalFilteredRestaurants($search = '', $category = '')
    {
        try {
            $hasCategoryId = $this->hasCategoryId();
            
            $sql = "
                SELECT COUNT(DISTINCT r.id) as total
                FROM restaurants r
                WHERE r.status = 'active' AND r.deleted_at IS NULL
            ";

            $params = [];

            // Add search filter
            if (!empty($search)) {
                $sql .= " AND (r.name LIKE ? OR r.description LIKE ? OR r.cuisine_type LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            // Add category filter
            if (!empty($category)) {
                if ($hasCategoryId) {
                    $sql .= " AND r.category_id = ?";
                } else {
                    $sql .= " AND r.cuisine_type = ?";
                }
                $params[] = $category;
            }

            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return (int)($result['total'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error counting filtered restaurants: " . $e->getMessage());
            return 0;
        }
    }
    
    private function getTotalRestaurants()
    {
        try {
            $stmt = $this->getDb()->prepare("SELECT COUNT(*) FROM restaurants WHERE status = 'active' AND deleted_at IS NULL");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("Error counting restaurants: " . $e->getMessage());
            return 0;
        }
    }
    
    private function performRestaurantSearch($query, $category = '', $location = '')
    {
        try {
            $sql = "
                SELECT DISTINCT r.*,
                       COALESCE(AVG(rv.rating), r.rating) as avg_rating,
                       COUNT(rv.id) as review_count
                FROM restaurants r
                LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant' AND rv.status = 'approved'
                LEFT JOIN menu_items mi ON r.id = mi.restaurant_id
                WHERE r.status = 'active' AND r.deleted_at IS NULL
            ";
            
            $params = [];
            
            if (!empty($query)) {
                $sql .= " AND (r.name LIKE ? OR r.description LIKE ? OR mi.name LIKE ?)";
                $searchTerm = "%$query%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($category)) {
                $sql .= " AND r.cuisine_type = ?";
                $params[] = $category;
            }
            
            if (!empty($location)) {
                $sql .= " AND r.city LIKE ?";
                $params[] = "%$location%";
            }
            
            $sql .= " GROUP BY r.id ORDER BY avg_rating DESC, r.name ASC LIMIT 20";
            
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute($params);
            $restaurants = $stmt->fetchAll();
            
            // Add missing fields for compatibility with view
            foreach ($restaurants as &$restaurant) {
                $restaurant['image'] = $restaurant['image'] ?? $restaurant['cover_image'] ?? '/public/images/fallback-food.jpg';
                $restaurant['rating'] = $restaurant['avg_rating'];
                $restaurant['featured_dishes'] = $this->getRestaurantFeaturedDishes($restaurant['id']);
            }
            
            return $restaurants;
        } catch (\Exception $e) {
            error_log("Error searching restaurants: " . $e->getMessage());
            return [];
        }
    }
    
    private function getCategoryBySlug($slug)
    {
        try {
            $stmt = $this->getDb()->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
            $stmt->execute([$slug]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Error fetching category: " . $e->getMessage());
            return null;
        }
    }
    
    private function getRestaurantsByCategory($categoryId)
    {
        try {
            $hasCategoryId = $this->hasCategoryId();
            
            if ($hasCategoryId) {
                // Use category_id if it exists
                $stmt = $this->getDb()->prepare("
                    SELECT r.*, 
                           COALESCE(AVG(rv.rating), r.rating) as avg_rating,
                           COUNT(rv.id) as review_count
                    FROM restaurants r
                    LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant' AND rv.status = 'approved'
                    WHERE r.category_id = ? AND r.status = 'active' AND r.deleted_at IS NULL
                    GROUP BY r.id
                    ORDER BY avg_rating DESC, r.name ASC
                ");
                $stmt->execute([$categoryId]);
            } else {
                // Use cuisine_type if category_id doesn't exist
                $stmt = $this->getDb()->prepare("
                    SELECT r.*, 
                           COALESCE(AVG(rv.rating), r.rating) as avg_rating,
                           COUNT(rv.id) as review_count
                    FROM restaurants r
                    LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant' AND rv.status = 'approved'
                    WHERE r.cuisine_type = ? AND r.status = 'active' AND r.deleted_at IS NULL
                    GROUP BY r.id
                    ORDER BY avg_rating DESC, r.name ASC
                ");
                $stmt->execute([$categoryId]);
            }
            
            $restaurants = $stmt->fetchAll();
            
            // Add missing fields for compatibility with view
            foreach ($restaurants as &$restaurant) {
                $restaurant['image'] = $restaurant['image'] ?? $restaurant['cover_image'] ?? '/public/images/fallback-food.jpg';
                $restaurant['rating'] = $restaurant['avg_rating'];
                $restaurant['featured_dishes'] = $this->getRestaurantFeaturedDishes($restaurant['id']);
            }
            
            return $restaurants;
        } catch (\Exception $e) {
            error_log("Error fetching restaurants by category: " . $e->getMessage());
            return [];
        }
    }
    
    private function getRestaurantById($id)
    {
        try {
            $stmt = $this->getDb()->prepare("
                SELECT r.*,
                       COALESCE(AVG(rv.rating), r.rating) as avg_rating,
                       COUNT(rv.id) as review_count
                FROM restaurants r
                LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant' AND rv.status = 'approved'
                WHERE r.id = ? AND r.status = 'active' AND r.deleted_at IS NULL
                GROUP BY r.id
            ");
            $stmt->execute([$id]);
            $restaurant = $stmt->fetch();
            
            if ($restaurant) {
                // Add missing fields for compatibility with view
                $restaurant['image'] = $restaurant['image'] ?? $restaurant['cover_image'] ?? '/public/images/fallback-food.jpg';
                $restaurant['rating'] = $restaurant['avg_rating'];
                $restaurant['featured_dishes'] = $this->getRestaurantFeaturedDishes($restaurant['id']);
            }
            
            return $restaurant;
        } catch (\Exception $e) {
            error_log("Error fetching restaurant: " . $e->getMessage());
            return null;
        }
    }
    
    private function getRestaurantMenu($restaurantId)
    {
        try {
            $stmt = $this->getDb()->prepare("
                SELECT mi.*, c.name as category_name
                FROM menu_items mi
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE mi.restaurant_id = ? AND mi.is_available = 1
                ORDER BY c.sort_order ASC, mi.sort_order ASC, mi.name ASC
            ");
            $stmt->execute([$restaurantId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error fetching menu: " . $e->getMessage());
            return [];
        }
    }
    
    private function getRestaurantReviews($restaurantId, $limit = 5)
    {
        try {
            $stmt = $this->getDb()->prepare("
                SELECT rv.*, u.name as customer_name
                FROM reviews rv
                LEFT JOIN users u ON rv.user_id = u.id
                WHERE rv.reviewable_id = ? AND rv.reviewable_type = 'restaurant' AND rv.status = 'approved'
                ORDER BY rv.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$restaurantId, $limit]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error fetching reviews: " . $e->getMessage());
            return [];
        }
    }
    
    private function getRestaurantFeaturedDishes($restaurantId)
    {
        try {
            $stmt = $this->getDb()->prepare("
                SELECT name
                FROM menu_items
                WHERE restaurant_id = ? AND is_featured = 1 AND is_available = 1
                ORDER BY sort_order ASC, name ASC
                LIMIT 5
            ");
            $stmt->execute([$restaurantId]);
            $dishes = $stmt->fetchAll();
            return array_column($dishes, 'name');
        } catch (\Exception $e) {
            error_log("Error fetching featured dishes: " . $e->getMessage());
            return ['Popular Dishes'];
        }
    }
    
    /**
     * Check if restaurants table has category_id column
     */
    private function hasCategoryId(): bool
    {
        try {
            $checkColumn = $this->getDb()->prepare("SHOW COLUMNS FROM restaurants LIKE 'category_id'");
            $checkColumn->execute();
            return (bool)$checkColumn->fetch();
        } catch (\Exception $e) {
            error_log("Error checking category_id column: " . $e->getMessage());
            return false;
        }
    }
}
