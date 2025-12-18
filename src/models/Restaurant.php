<?php

namespace models;

use traits\DatabaseTrait;

class Restaurant
{
    use DatabaseTrait;
    protected ?\PDO $db = null;
    protected $table = 'restaurants';
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'cuisine_type',
        'address', 'city', 'state', 'postal_code', 'country',
        'latitude', 'longitude', 'phone', 'email', 'website',
        'image', 'cover_image', 'logo', 'opening_hours',
        'delivery_fee', 'delivery_fee_per_extra_km', 'minimum_order', 'delivery_radius',
        'delivery_time', 'is_active', 'is_featured', 'is_open',
        'rating', 'total_reviews', 'total_orders'
    ];

    public function getById(int $id): ?array
    {
        $sql = "
            SELECT r.*, u.first_name as owner_first_name, u.last_name as owner_last_name, u.email as owner_email
            FROM {$this->table} r
            LEFT JOIN users u ON r.owner_id = u.id
            WHERE r.id = ? AND r.deleted_at IS NULL
        ";
        
        return $this->db->fetch($sql, [$id]);
    }

    public function getActiveRestaurants(int $limit = 20, int $offset = 0): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE is_active = 1 AND deleted_at IS NULL
            ORDER BY is_featured DESC, rating DESC, name ASC
            LIMIT ? OFFSET ?
        ";
        
        return $this->db->fetchAll($sql, [$limit, $offset]);
    }

    public function getFeaturedRestaurants(int $limit = 10): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE is_active = 1 AND is_featured = 1 AND deleted_at IS NULL
            ORDER BY rating DESC, name ASC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    public function searchRestaurants(string $query, int $limit = 20, int $offset = 0): array
    {
        $searchTerm = '%' . $query . '%';
        
        $sql = "
            SELECT *,
                MATCH(name, description, cuisine_type) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM {$this->table}
            WHERE is_active = 1 AND deleted_at IS NULL
            AND (
                MATCH(name, description, cuisine_type) AGAINST(? IN NATURAL LANGUAGE MODE)
                OR name LIKE ?
                OR description LIKE ?
                OR cuisine_type LIKE ?
            )
            ORDER BY relevance DESC, rating DESC, name ASC
            LIMIT ? OFFSET ?
        ";
        
        return $this->db->fetchAll($sql, [
            $query, $query, $searchTerm, $searchTerm, $searchTerm, $limit, $offset
        ]);
    }

    public function getRestaurantsByOwner(int $ownerId): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE owner_id = ? AND deleted_at IS NULL
            ORDER BY created_at DESC
        ";
        
        return $this->db->fetchAll($sql, [$ownerId]);
    }

    public function updateRating(int $restaurantId): bool
    {
        $sql = "
            UPDATE {$this->table} 
            SET 
                rating = (
                    SELECT COALESCE(AVG(rating), 0) 
                    FROM reviews 
                    WHERE restaurant_id = ? AND deleted_at IS NULL
                ),
                total_reviews = (
                    SELECT COUNT(*) 
                    FROM reviews 
                    WHERE restaurant_id = ? AND deleted_at IS NULL
                ),
                updated_at = NOW()
            WHERE id = ?
        ";
        
        return $this->db->execute($sql, [$restaurantId, $restaurantId, $restaurantId]);
    }

    public function incrementOrderCount(int $restaurantId): bool
    {
        $sql = "
            UPDATE {$this->table} 
            SET total_orders = total_orders + 1, updated_at = NOW()
            WHERE id = ?
        ";
        
        return $this->db->execute($sql, [$restaurantId]);
    }

    public function getRestaurantStats(int $restaurantId): array
    {
        $sql = "
            SELECT 
                COUNT(CASE WHEN o.status = 'delivered' THEN 1 END) as total_orders,
                COUNT(CASE WHEN DATE(o.created_at) = CURDATE() THEN 1 END) as today_orders,
                COALESCE(SUM(CASE WHEN o.status = 'delivered' THEN o.subtotal ELSE 0 END), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN DATE(o.created_at) = CURDATE() AND o.status != 'cancelled' THEN o.subtotal ELSE 0 END), 0) as today_revenue,
                COALESCE(AVG(CASE WHEN o.status = 'delivered' THEN o.subtotal END), 0) as avg_order_value,
                COUNT(DISTINCT o.customer_id) as unique_customers
            FROM orders o
            WHERE o.restaurant_id = ?
        ";
        
        $stats = $this->db->fetch($sql, [$restaurantId]);
        
        return [
            'total_orders' => (int)($stats['total_orders'] ?? 0),
            'today_orders' => (int)($stats['today_orders'] ?? 0),
            'total_revenue' => (float)($stats['total_revenue'] ?? 0),
            'today_revenue' => (float)($stats['today_revenue'] ?? 0),
            'avg_order_value' => (float)($stats['avg_order_value'] ?? 0),
            'unique_customers' => (int)($stats['unique_customers'] ?? 0)
        ];
    }

    public function getPopularRestaurants(int $limit = 10): array
    {
        $sql = "
            SELECT r.*, COUNT(o.id) as order_count
            FROM {$this->table} r
            LEFT JOIN orders o ON r.id = o.restaurant_id 
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND o.status = 'delivered'
            WHERE r.is_active = 1 AND r.deleted_at IS NULL
            GROUP BY r.id
            ORDER BY order_count DESC, r.rating DESC, r.name ASC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    public function getRestaurantsByLocation(float $latitude, float $longitude, float $radiusKm = 10, int $limit = 20): array
    {
        $sql = "
            SELECT *,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
            FROM {$this->table}
            WHERE is_active = 1 AND deleted_at IS NULL
            HAVING distance <= ?
            ORDER BY distance ASC, rating DESC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$latitude, $longitude, $latitude, $radiusKm, $limit]);
    }

    public function isOpen(int $restaurantId): bool
    {
        $restaurant = $this->getById($restaurantId);
        
        if (!$restaurant || !$restaurant['is_active'] || !$restaurant['is_open']) {
            return false;
        }

        // Check opening hours
        if (!empty($restaurant['opening_hours'])) {
            $openingHours = json_decode($restaurant['opening_hours'], true);
            if ($openingHours) {
                $currentDay = strtolower(date('l')); // monday, tuesday, etc.
                $currentTime = date('H:i');
                
                if (isset($openingHours[$currentDay])) {
                    $dayHours = $openingHours[$currentDay];
                    if ($dayHours['closed']) {
                        return false;
                    }
                    
                    return $currentTime >= $dayHours['open'] && $currentTime <= $dayHours['close'];
                }
            }
        }
        
        return true;
    }

    public function getOpeningHours(int $restaurantId): array
    {
        $restaurant = $this->getById($restaurantId);
        
        if (!$restaurant || empty($restaurant['opening_hours'])) {
            return [];
        }
        
        $openingHours = json_decode($restaurant['opening_hours'], true);
        return is_array($openingHours) ? $openingHours : [];
    }

    public function updateOpenStatus(int $restaurantId, bool $isOpen): bool
    {
        return $this->update($restaurantId, [
            'is_open' => $isOpen ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getRestaurantCategories(): array
    {
        $sql = "
            SELECT DISTINCT cuisine_type, COUNT(*) as restaurant_count
            FROM {$this->table}
            WHERE is_active = 1 AND deleted_at IS NULL AND cuisine_type IS NOT NULL
            GROUP BY cuisine_type
            ORDER BY restaurant_count DESC, cuisine_type ASC
        ";
        
        return $this->db->fetchAll($sql);
    }

    public function getRestaurantsByCuisine(string $cuisineType, int $limit = 20, int $offset = 0): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE cuisine_type = ? AND is_active = 1 AND deleted_at IS NULL
            ORDER BY rating DESC, name ASC
            LIMIT ? OFFSET ?
        ";
        
        return $this->db->fetchAll($sql, [$cuisineType, $limit, $offset]);
    }

    public function countActiveRestaurants(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_active = 1 AND deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        return (int)($result['count'] ?? 0);
    }

    public function getRecentRestaurants(int $limit = 5): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE is_active = 1 AND deleted_at IS NULL
            ORDER BY created_at DESC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Get restaurant by vendor ID
     */
    public function getByVendorId(int $vendorId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND deleted_at IS NULL LIMIT 1";
        return $this->fetchOne($sql, [$vendorId]);
    }

    /**
     * Update restaurant profile
     */
    public function updateProfile(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->updateRecord($this->table, $data, ['id' => $id]) > 0;
    }

    /**
     * Create restaurant profile
     */
    public function createProfile(array $data): ?int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    /**
     * Get pending restaurant approvals
     */
    public function getPendingApprovals(): array
    {
        $sql = "SELECT r.*, u.first_name as owner_first_name, u.last_name as owner_last_name, u.email as owner_email
                FROM {$this->table} r
                LEFT JOIN users u ON r.owner_id = u.id
                WHERE r.status = 'pending' AND r.deleted_at IS NULL
                ORDER BY r.created_at DESC";
        return $this->fetchAll($sql);
    }

    /**
     * Approve restaurant
     */
    public function approveRestaurant(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'active', is_active = 1, updated_at = NOW() WHERE id = ?";
        return $this->execute($sql, [$id]) > 0;
    }

    /**
     * Reject restaurant
     */
    public function rejectRestaurant(int $id, string $reason = ''): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'rejected', updated_at = NOW() WHERE id = ?";
        return $this->execute($sql, [$id]) > 0;
    }

    /**
     * Get total restaurant count
     */
    public function getTotalCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
        $result = $this->fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get restaurant statistics
     */
    public function getRestaurantStatistics(): array
    {
        $sql = "SELECT
                    COUNT(*) as total_restaurants,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_restaurants,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_restaurants,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_restaurants,
                    COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured_restaurants,
                    AVG(rating) as average_rating
                FROM {$this->table}
                WHERE deleted_at IS NULL";

        return $this->fetchOne($sql) ?: [];
    }

    /**
     * Get restaurants with filtering and pagination
     */
    public function getRestaurants(string $status = 'all', string $search = '', int $limit = 20, int $offset = 0): array
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

        $whereClause = implode(' AND ', $conditions);
        $params[] = $limit;
        $params[] = $offset;

        $sql = "SELECT r.*, u.first_name as owner_first_name, u.last_name as owner_last_name, u.email as owner_email
                FROM {$this->table} r
                LEFT JOIN users u ON r.owner_id = u.id
                WHERE {$whereClause}
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Count restaurants with filtering
     */
    public function countRestaurants(string $status = 'all', string $search = ''): int
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

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
        $result = $this->fetchOne($sql, $params);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get active restaurant count
     */
    public function getActiveRestaurantCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE status = 'active' AND is_active = 1 AND deleted_at IS NULL";
        $result = $this->fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }

    public function hardDelete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
}
