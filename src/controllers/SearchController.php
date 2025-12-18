<?php

namespace Time2Eat\Controllers;

use core\BaseController;
use Time2Eat\Models\Restaurant;
use Time2Eat\Models\MenuItem;
use Time2Eat\Models\Category;

class SearchController extends BaseController
{
    private Restaurant $restaurantModel;
    private MenuItem $menuItemModel;
    private Category $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->restaurantModel = new Restaurant();
        $this->menuItemModel = new MenuItem();
        $this->categoryModel = new Category();
    }

    /**
     * Display search page with filters
     */
    public function index(): void
    {
        $query = $_GET['q'] ?? '';
        $filters = $this->getFilters();
        
        $results = [];
        $totalResults = 0;
        
        if (!empty($query) || !empty(array_filter($filters))) {
            $results = $this->performSearch($query, $filters);
            $totalResults = count($results['restaurants']) + count($results['menu_items']);
        }

        $categories = $this->categoryModel->getActiveCategories();
        $cuisineTypes = $this->restaurantModel->getCuisineTypes();
        $priceRanges = $this->getPriceRanges();

        $this->render('search/index', [
            'query' => $query,
            'filters' => $filters,
            'results' => $results,
            'totalResults' => $totalResults,
            'categories' => $categories,
            'cuisineTypes' => $cuisineTypes,
            'priceRanges' => $priceRanges,
            'title' => 'Search - Time2Eat'
        ]);
    }

    /**
     * API endpoint for search suggestions
     */
    public function suggestions(): void
    {
        $query = $_GET['q'] ?? '';
        
        if (strlen($query) < 2) {
            $this->jsonResponse([
                'success' => true,
                'suggestions' => []
            ]);
            return;
        }

        $suggestions = $this->getSearchSuggestions($query);
        
        $this->jsonResponse([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * API endpoint for live search results
     */
    public function liveSearch(): void
    {
        $query = $_GET['q'] ?? '';
        $filters = $this->getFilters();
        
        if (strlen($query) < 2 && empty(array_filter($filters))) {
            $this->jsonResponse([
                'success' => true,
                'results' => [],
                'totalResults' => 0
            ]);
            return;
        }

        $results = $this->performSearch($query, $filters);
        $totalResults = count($results['restaurants']) + count($results['menu_items']);
        
        $this->jsonResponse([
            'success' => true,
            'results' => $results,
            'totalResults' => $totalResults
        ]);
    }

    /**
     * Perform comprehensive search
     */
    private function performSearch(string $query, array $filters): array
    {
        $restaurants = $this->searchRestaurants($query, $filters);
        $menuItems = $this->searchMenuItems($query, $filters);
        
        return [
            'restaurants' => $restaurants,
            'menu_items' => $menuItems
        ];
    }

    /**
     * Search restaurants with filters
     */
    private function searchRestaurants(string $query, array $filters): array
    {
        $sql = "
            SELECT DISTINCT r.*, 
                   AVG(rev.rating) as average_rating,
                   COUNT(rev.id) as review_count,
                   c.name as category_name,
                   CASE 
                       WHEN r.name LIKE :exact_query THEN 1
                       WHEN r.name LIKE :start_query THEN 2
                       WHEN r.description LIKE :query THEN 3
                       WHEN r.cuisine_type LIKE :query THEN 4
                       ELSE 5
                   END as relevance_score
            FROM restaurants r
            LEFT JOIN categories c ON r.category_id = c.id
            LEFT JOIN reviews rev ON rev.reviewable_type = 'restaurant' AND rev.reviewable_id = r.id
            WHERE r.is_active = 1 AND r.is_approved = 1
        ";

        $params = [];

        // Add search query conditions
        if (!empty($query)) {
            $sql .= " AND (
                r.name LIKE :query OR 
                r.description LIKE :query OR 
                r.cuisine_type LIKE :query OR
                r.address LIKE :query OR
                c.name LIKE :query
            )";
            $params['query'] = "%{$query}%";
            $params['exact_query'] = $query;
            $params['start_query'] = "{$query}%";
        }

        // Add filter conditions
        if (!empty($filters['category'])) {
            $sql .= " AND r.category_id = :category_id";
            $params['category_id'] = $filters['category'];
        }

        if (!empty($filters['cuisine'])) {
            $sql .= " AND r.cuisine_type = :cuisine_type";
            $params['cuisine_type'] = $filters['cuisine'];
        }

        if (!empty($filters['min_rating'])) {
            $sql .= " AND r.id IN (
                SELECT reviewable_id FROM reviews 
                WHERE reviewable_type = 'restaurant' 
                GROUP BY reviewable_id 
                HAVING AVG(rating) >= :min_rating
            )";
            $params['min_rating'] = $filters['min_rating'];
        }

        if (!empty($filters['delivery_fee_max'])) {
            $sql .= " AND r.delivery_fee <= :delivery_fee_max";
            $params['delivery_fee_max'] = $filters['delivery_fee_max'];
        }

        if (!empty($filters['location'])) {
            $sql .= " AND (r.address LIKE :location OR r.city LIKE :location)";
            $params['location'] = "%{$filters['location']}%";
        }

        $sql .= " GROUP BY r.id";
        
        // Add sorting
        $sortBy = $filters['sort'] ?? 'relevance';
        switch ($sortBy) {
            case 'rating':
                $sql .= " ORDER BY average_rating DESC, review_count DESC";
                break;
            case 'delivery_fee':
                $sql .= " ORDER BY r.delivery_fee ASC";
                break;
            case 'delivery_time':
                $sql .= " ORDER BY r.delivery_time ASC";
                break;
            case 'name':
                $sql .= " ORDER BY r.name ASC";
                break;
            default:
                $sql .= " ORDER BY relevance_score ASC, average_rating DESC";
        }

        $sql .= " LIMIT " . ($filters['limit'] ?? 20);

        return $this->getDb()->query($sql, $params)->fetchAll();
    }

    /**
     * Search menu items with filters
     */
    private function searchMenuItems(string $query, array $filters): array
    {
        $sql = "
            SELECT DISTINCT mi.*, 
                   r.name as restaurant_name,
                   r.slug as restaurant_slug,
                   r.delivery_fee,
                   r.delivery_time,
                   AVG(rev.rating) as average_rating,
                   COUNT(rev.id) as review_count,
                   c.name as category_name,
                   CASE 
                       WHEN mi.name LIKE :exact_query THEN 1
                       WHEN mi.name LIKE :start_query THEN 2
                       WHEN mi.description LIKE :query THEN 3
                       WHEN c.name LIKE :query THEN 4
                       ELSE 5
                   END as relevance_score
            FROM menu_items mi
            INNER JOIN restaurants r ON mi.restaurant_id = r.id
            LEFT JOIN categories c ON mi.category_id = c.id
            LEFT JOIN reviews rev ON rev.reviewable_type = 'menu_item' AND rev.reviewable_id = mi.id
            WHERE mi.is_available = 1 AND r.is_active = 1 AND r.is_approved = 1
        ";

        $params = [];

        // Add search query conditions
        if (!empty($query)) {
            $sql .= " AND (
                mi.name LIKE :query OR 
                mi.description LIKE :query OR 
                mi.ingredients LIKE :query OR
                c.name LIKE :query OR
                r.name LIKE :query
            )";
            $params['query'] = "%{$query}%";
            $params['exact_query'] = $query;
            $params['start_query'] = "{$query}%";
        }

        // Add filter conditions
        if (!empty($filters['category'])) {
            $sql .= " AND mi.category_id = :category_id";
            $params['category_id'] = $filters['category'];
        }

        if (!empty($filters['price_min'])) {
            $sql .= " AND mi.price >= :price_min";
            $params['price_min'] = $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $sql .= " AND mi.price <= :price_max";
            $params['price_max'] = $filters['price_max'];
        }

        if (!empty($filters['dietary'])) {
            $dietary = $filters['dietary'];
            if ($dietary === 'vegetarian') {
                $sql .= " AND mi.is_vegetarian = 1";
            } elseif ($dietary === 'vegan') {
                $sql .= " AND mi.is_vegan = 1";
            } elseif ($dietary === 'gluten_free') {
                $sql .= " AND mi.is_gluten_free = 1";
            }
        }

        if (!empty($filters['restaurant_id'])) {
            $sql .= " AND mi.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        $sql .= " GROUP BY mi.id";
        
        // Add sorting
        $sortBy = $filters['sort'] ?? 'relevance';
        switch ($sortBy) {
            case 'price_low':
                $sql .= " ORDER BY mi.price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY mi.price DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY average_rating DESC, review_count DESC";
                break;
            case 'name':
                $sql .= " ORDER BY mi.name ASC";
                break;
            default:
                $sql .= " ORDER BY relevance_score ASC, average_rating DESC";
        }

        $sql .= " LIMIT " . ($filters['limit'] ?? 20);

        return $this->getDb()->query($sql, $params)->fetchAll();
    }

    /**
     * Get search suggestions
     */
    private function getSearchSuggestions(string $query): array
    {
        $suggestions = [];

        // Restaurant suggestions
        $restaurantSql = "
            SELECT name, 'restaurant' as type, slug as url
            FROM restaurants 
            WHERE is_active = 1 AND is_approved = 1 AND name LIKE :query
            ORDER BY name ASC
            LIMIT 5
        ";
        $restaurants = $this->getDb()->query($restaurantSql, ['query' => "%{$query}%"])->fetchAll();
        $suggestions = array_merge($suggestions, $restaurants);

        // Menu item suggestions
        $menuSql = "
            SELECT mi.name, 'menu_item' as type, 
                   CONCAT('/restaurants/', r.slug, '#item-', mi.id) as url
            FROM menu_items mi
            INNER JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE mi.is_available = 1 AND r.is_active = 1 AND mi.name LIKE :query
            ORDER BY mi.name ASC
            LIMIT 5
        ";
        $menuItems = $this->getDb()->query($menuSql, ['query' => "%{$query}%"])->fetchAll();
        $suggestions = array_merge($suggestions, $menuItems);

        // Category suggestions
        $categorySql = "
            SELECT name, 'category' as type, CONCAT('/search?category=', id) as url
            FROM categories 
            WHERE is_active = 1 AND name LIKE :query
            ORDER BY name ASC
            LIMIT 3
        ";
        $categories = $this->getDb()->query($categorySql, ['query' => "%{$query}%"])->fetchAll();
        $suggestions = array_merge($suggestions, $categories);

        return array_slice($suggestions, 0, 10);
    }

    /**
     * Get search filters from request
     */
    private function getFilters(): array
    {
        return [
            'category' => $_GET['category'] ?? '',
            'cuisine' => $_GET['cuisine'] ?? '',
            'price_min' => $_GET['price_min'] ?? '',
            'price_max' => $_GET['price_max'] ?? '',
            'min_rating' => $_GET['min_rating'] ?? '',
            'delivery_fee_max' => $_GET['delivery_fee_max'] ?? '',
            'location' => $_GET['location'] ?? '',
            'dietary' => $_GET['dietary'] ?? '',
            'restaurant_id' => $_GET['restaurant_id'] ?? '',
            'sort' => $_GET['sort'] ?? 'relevance',
            'limit' => min(50, max(10, (int)($_GET['limit'] ?? 20)))
        ];
    }

    /**
     * Get price ranges for filters
     */
    private function getPriceRanges(): array
    {
        return [
            ['min' => 0, 'max' => 1000, 'label' => 'Under 1,000 XAF'],
            ['min' => 1000, 'max' => 2500, 'label' => '1,000 - 2,500 XAF'],
            ['min' => 2500, 'max' => 5000, 'label' => '2,500 - 5,000 XAF'],
            ['min' => 5000, 'max' => 10000, 'label' => '5,000 - 10,000 XAF'],
            ['min' => 10000, 'max' => null, 'label' => 'Over 10,000 XAF']
        ];
    }
}
