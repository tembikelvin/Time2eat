<?php

declare(strict_types=1);

namespace traits;

/**
 * Restaurant Query Trait
 * Provides common database query methods for restaurant-related operations
 */
trait RestaurantQueryTrait
{
    /**
     * Get restaurants with filtering and pagination
     */
    protected function getRestaurants(string $status, string $search, string $category, int $limit, int $offset): array
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
            $conditions[] = 'r.category_id = ?';
            $params[] = $category;
        }

        $whereClause = implode(' AND ', $conditions);
        $params[] = $limit;
        $params[] = $offset;

        $sql = "SELECT r.*, u.first_name, u.last_name, u.email as owner_email, c.name as category_name
                FROM restaurants r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN categories c ON r.category_id = c.id
                WHERE {$whereClause}
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Count restaurants with filtering
     */
    protected function countRestaurants(string $status, string $search, string $category): int
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
            $conditions[] = 'category_id = ?';
            $params[] = $category;
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT COUNT(*) as count FROM restaurants WHERE {$whereClause}";
        
        $result = $this->fetchOne($sql, $params);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Get restaurant statistics
     */
    protected function getRestaurantStats(): array
    {
        try {
            $stats = [];

            // Total restaurants
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE deleted_at IS NULL");
            $stats['total_restaurants'] = $result['count'] ?? 0;

            // Active restaurants
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE status = 'approved' AND is_active = 1 AND deleted_at IS NULL");
            $stats['active_restaurants'] = $result['count'] ?? 0;

            // Pending restaurants
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE status = 'pending' AND deleted_at IS NULL");
            $stats['pending_restaurants'] = $result['count'] ?? 0;

            // Average rating
            $result = $this->fetchOne("SELECT AVG(rating) as avg_rating FROM restaurants WHERE deleted_at IS NULL AND rating > 0");
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

    /**
     * Get restaurant by ID with user and category information
     */
    protected function getRestaurantById(int $id): ?array
    {
        $sql = "SELECT r.*, u.first_name, u.last_name, u.email as owner_email, c.name as category_name
                FROM restaurants r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN categories c ON r.category_id = c.id
                WHERE r.id = ? AND r.deleted_at IS NULL";
        
        return $this->fetchOne($sql, [$id]);
    }

    /**
     * Get detailed statistics for a specific restaurant
     */
    protected function getRestaurantDetailStats(int $restaurantId): array
    {
        try {
            $stats = [];

            // Total orders
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE restaurant_id = ?", [$restaurantId]);
            $stats['total_orders'] = $result['count'] ?? 0;

            // Total revenue
            $result = $this->fetchOne("SELECT SUM(total_amount) as revenue FROM orders WHERE restaurant_id = ? AND status = 'delivered'", [$restaurantId]);
            $stats['total_revenue'] = $result['revenue'] ?? 0;

            // Average order value
            if ($stats['total_orders'] > 0) {
                $stats['avg_order_value'] = $stats['total_revenue'] / $stats['total_orders'];
            } else {
                $stats['avg_order_value'] = 0;
            }

            // Menu items count
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM menu_items WHERE restaurant_id = ? AND deleted_at IS NULL", [$restaurantId]);
            $stats['menu_items_count'] = $result['count'] ?? 0;

            return $stats;

        } catch (\Exception $e) {
            error_log("Error getting restaurant detail stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all active categories
     */
    protected function getCategories(): array
    {
        try {
            return $this->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
        } catch (\Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate unique slug for restaurant
     */
    protected function generateRestaurantSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->fetchOne("SELECT id FROM restaurants WHERE slug = ?", [$slug])) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}

