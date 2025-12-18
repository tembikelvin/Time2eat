<?php

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class MenuItem
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    protected $table = 'menu_items';
    
    protected $fillable = [
        'restaurant_id', 'category_id', 'name', 'description', 'price',
        'image', 'ingredients', 'allergens', 'nutritional_info',
        'preparation_time', 'is_available', 'is_featured', 'sort_order',
        'customization_options', 'tags', 'stock_quantity', 'low_stock_threshold',
        'calories', 'is_vegetarian', 'is_vegan', 'is_gluten_free', 'sku', 'barcode',
        'compare_price', 'cost_price', 'slug', 'images', 'spice_level', 'dietary_tags',
        'is_popular', 'meta_title', 'meta_description'
    ];

    public function getMenuItemsByRestaurant(int $restaurantId, int $categoryId = null, int $limit = 20, int $offset = 0): array
    {
        $sql = "
            SELECT 
                mi.*,
                c.name as category_name,
                c.slug as category_slug,
                r.name as restaurant_name
            FROM {$this->table} mi
            JOIN categories c ON mi.category_id = c.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE mi.restaurant_id = ? AND mi.is_available = 1
        ";

        $params = [$restaurantId];

        if ($categoryId) {
            $sql .= " AND mi.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY mi.sort_order ASC, mi.name ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->fetchAll($sql, $params);
    }

    /**
     * Count menu items by category
     */
    public function countByCategory(int $categoryId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE category_id = ? AND deleted_at IS NULL";
        $result = $this->fetchOne($sql, [$categoryId]);
        return (int)($result['count'] ?? 0);
    }

    public function getMenuItemDetails(int $itemId): ?array
    {
        $sql = "
            SELECT 
                mi.*,
                c.name as category_name,
                c.slug as category_slug,
                r.id as restaurant_id,
                r.name as restaurant_name,
                r.image as restaurant_image,
                r.delivery_fee,
                r.minimum_order,
                r.delivery_time,
                r.is_open
            FROM {$this->table} mi
            JOIN categories c ON mi.category_id = c.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE mi.id = ? AND mi.is_available = 1 AND r.status = 'active'
        ";

        return $this->fetchOne($sql, [$itemId]);
    }

    public function getFeaturedItems(int $limit = 10): array
    {
        $sql = "
            SELECT 
                mi.*,
                c.name as category_name,
                r.name as restaurant_name,
                r.image as restaurant_image
            FROM {$this->table} mi
            JOIN categories c ON mi.category_id = c.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE mi.is_featured = 1 AND mi.is_available = 1 AND r.status = 'active'
            ORDER BY RAND()
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$limit]);
    }

    public function searchMenuItems(string $query, int $limit = 20, int $offset = 0): array
    {
        $searchTerm = '%' . $query . '%';
        
        $sql = "
            SELECT 
                mi.*,
                c.name as category_name,
                r.name as restaurant_name,
                r.image as restaurant_image,
                MATCH(mi.name, mi.description, mi.tags) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM {$this->table} mi
            JOIN categories c ON mi.category_id = c.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE mi.is_available = 1 AND r.status = 'active'
            AND (
                MATCH(mi.name, mi.description, mi.tags) AGAINST(? IN NATURAL LANGUAGE MODE)
                OR mi.name LIKE ?
                OR mi.description LIKE ?
                OR mi.tags LIKE ?
            )
            ORDER BY relevance DESC, mi.name ASC
            LIMIT ? OFFSET ?
        ";

        return $this->fetchAll($sql, [
            $query, $query, $searchTerm, $searchTerm, $searchTerm, $limit, $offset
        ]);
    }

    public function getMenuItemsByCategory(int $categoryId, int $limit = 20, int $offset = 0): array
    {
        $sql = "
            SELECT 
                mi.*,
                c.name as category_name,
                r.name as restaurant_name,
                r.image as restaurant_image
            FROM {$this->table} mi
            JOIN categories c ON mi.category_id = c.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE mi.category_id = ? AND mi.is_available = 1 AND r.status = 'active'
            ORDER BY mi.sort_order ASC, mi.name ASC
            LIMIT ? OFFSET ?
        ";

        return $this->fetchAll($sql, [$categoryId, $limit, $offset]);
    }

    public function getPopularItems(int $limit = 10): array
    {
        $sql = "
            SELECT 
                mi.*,
                c.name as category_name,
                r.name as restaurant_name,
                r.image as restaurant_image,
                COUNT(oi.id) as order_count
            FROM {$this->table} mi
            JOIN categories c ON mi.category_id = c.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE mi.is_available = 1 AND r.status = 'active'
            AND (o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) OR o.id IS NULL)
            GROUP BY mi.id
            ORDER BY order_count DESC, mi.name ASC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$limit]);
    }

    public function getRelatedItems(int $itemId, int $limit = 4): array
    {
        $item = $this->getById($itemId);
        if (!$item) {
            return [];
        }

        $sql = "
            SELECT 
                mi.*,
                c.name as category_name,
                r.name as restaurant_name
            FROM {$this->table} mi
            JOIN categories c ON mi.category_id = c.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE mi.category_id = ? AND mi.id != ? AND mi.is_available = 1 AND r.status = 'active'
            ORDER BY RAND()
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$item['category_id'], $itemId, $limit]);
    }

    public function getCustomizationOptions(int $itemId): array
    {
        $item = $this->getById($itemId);
        if (!$item || !$item['customization_options']) {
            return [];
        }

        $options = json_decode($item['customization_options'], true);
        return is_array($options) ? $options : [];
    }

    public function validateCustomizations(int $itemId, array $customizations): array
    {
        $availableOptions = $this->getCustomizationOptions($itemId);
        $errors = [];
        $validCustomizations = [];
        $additionalCost = 0;

        foreach ($customizations as $optionId => $selectedValues) {
            $option = null;
            foreach ($availableOptions as $availableOption) {
                if ($availableOption['id'] === $optionId) {
                    $option = $availableOption;
                    break;
                }
            }

            if (!$option) {
                $errors[] = "Invalid customization option: {$optionId}";
                continue;
            }

            // Validate required options
            if ($option['required'] && empty($selectedValues)) {
                $errors[] = "{$option['name']} is required";
                continue;
            }

            // Validate selection limits
            if (!empty($selectedValues)) {
                if ($option['max_selections'] && count($selectedValues) > $option['max_selections']) {
                    $errors[] = "{$option['name']} allows maximum {$option['max_selections']} selections";
                    continue;
                }

                if ($option['min_selections'] && count($selectedValues) < $option['min_selections']) {
                    $errors[] = "{$option['name']} requires minimum {$option['min_selections']} selections";
                    continue;
                }

                // Validate individual values and calculate cost
                $validValues = [];
                foreach ($selectedValues as $value) {
                    $validValue = null;
                    foreach ($option['values'] as $availableValue) {
                        if ($availableValue['id'] === $value) {
                            $validValue = $availableValue;
                            break;
                        }
                    }

                    if ($validValue) {
                        $validValues[] = $validValue;
                        $additionalCost += $validValue['price'] ?? 0;
                    } else {
                        $errors[] = "Invalid value for {$option['name']}: {$value}";
                    }
                }

                if (!empty($validValues)) {
                    $validCustomizations[$optionId] = [
                        'option' => $option,
                        'values' => $validValues
                    ];
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'customizations' => $validCustomizations,
            'additional_cost' => $additionalCost
        ];
    }

    public function calculateItemPrice(int $itemId, array $customizations = []): float
    {
        $item = $this->getById($itemId);
        if (!$item) {
            return 0;
        }

        $basePrice = (float)$item['price'];
        $additionalCost = 0;

        if (!empty($customizations)) {
            $validation = $this->validateCustomizations($itemId, $customizations);
            if ($validation['valid']) {
                $additionalCost = $validation['additional_cost'];
            }
        }

        return $basePrice + $additionalCost;
    }

    public function getByRestaurant(int $restaurantId, int $limit = 20, int $offset = 0): array
    {
        $sql = "
            SELECT 
                mi.*,
                c.name as category_name
            FROM {$this->table} mi
            JOIN categories c ON mi.category_id = c.id
            WHERE mi.restaurant_id = ?
            ORDER BY mi.sort_order ASC, mi.name ASC
            LIMIT ? OFFSET ?
        ";

        return $this->fetchAll($sql, [$restaurantId, $limit, $offset]);
    }

    public function countByRestaurant(int $restaurantId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->fetchOne($sql, [$restaurantId]);
        return (int)($result['count'] ?? 0);
    }

    public function getCategoriesByRestaurant(int $restaurantId): array
    {
        $sql = "
            SELECT DISTINCT
                c.id,
                c.name,
                c.slug,
                COUNT(mi.id) as item_count
            FROM categories c
            JOIN {$this->table} mi ON c.id = mi.category_id
            WHERE mi.restaurant_id = ? AND mi.is_available = 1
            GROUP BY c.id, c.name, c.slug
            ORDER BY c.name ASC
        ";

        return $this->fetchAll($sql, [$restaurantId]);
    }

    public function getPopularItemsByRestaurant(int $restaurantId, int $limit = 5): array
    {
        $sql = "
            SELECT 
                mi.*,
                c.name as category_name,
                COUNT(oi.id) as order_count
            FROM {$this->table} mi
            JOIN categories c ON mi.category_id = c.id
            LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE mi.restaurant_id = ? AND mi.is_available = 1
            AND (o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) OR o.id IS NULL)
            GROUP BY mi.id
            ORDER BY order_count DESC, mi.name ASC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$restaurantId, $limit]);
    }


    public function countLowStockItems(int $restaurantId): int
    {
        $sql = "
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE restaurant_id = ? AND is_available = 0
        ";
        
        $result = $this->fetchOne($sql, [$restaurantId]);
        return (int)($result['count'] ?? 0);
    }

    public function updateStock(int $itemId, int $quantity): bool
    {
        // Update stock_quantity and auto-manage availability
        $updateData = [
            'stock_quantity' => max(0, $quantity),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Auto-disable if out of stock
        if ($quantity <= 0) {
            $updateData['is_available'] = 0;
        } else {
            // Enable if stock is available
            $updateData['is_available'] = 1;
        }

        return $this->updateRecord($this->table, $updateData, ['id' => $itemId]) > 0;
    }

    public function decreaseStock(int $itemId, int $quantity): bool
    {
        // Get current stock
        $item = $this->getById($itemId);
        if (!$item) {
            return false;
        }

        $currentStock = (int)($item['stock_quantity'] ?? 0);
        $newStock = max(0, $currentStock - $quantity);

        return $this->updateStock($itemId, $newStock);
    }

    public function increaseStock(int $itemId, int $quantity): bool
    {
        // Get current stock
        $item = $this->getById($itemId);
        if (!$item) {
            return false;
        }

        $currentStock = (int)($item['stock_quantity'] ?? 0);
        $newStock = $currentStock + $quantity;

        return $this->updateStock($itemId, $newStock);
    }

    /**
     * Get restaurant items with pagination and filters
     */
    public function getRestaurantItems(
        int $restaurantId,
        int $limit = 20,
        int $offset = 0,
        string $category = '',
        string $status = '',
        string $search = ''
    ): array {
        $sql = "SELECT mi.*, c.name as category_name
                FROM {$this->table} mi
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE mi.restaurant_id = ?";

        $params = [$restaurantId];

        if (!empty($category)) {
            $sql .= " AND c.name = ?";
            $params[] = $category;
        }

        if (!empty($status)) {
            if ($status === 'available') {
                $sql .= " AND mi.is_available = 1";
            } elseif ($status === 'unavailable') {
                $sql .= " AND mi.is_available = 0";
            } elseif ($status === 'low_stock') {
                $sql .= " AND mi.is_available = 0";
            }
        }

        if (!empty($search)) {
            $sql .= " AND (mi.name LIKE ? OR mi.description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY mi.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->fetchAll($sql, $params);
    }

    /**
     * Count restaurant items with filters
     */
    public function countRestaurantItems(
        int $restaurantId,
        string $category = '',
        string $status = '',
        string $search = ''
    ): int {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} mi
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE mi.restaurant_id = ?";

        $params = [$restaurantId];

        if (!empty($category)) {
            $sql .= " AND c.name = ?";
            $params[] = $category;
        }

        if (!empty($status)) {
            if ($status === 'available') {
                $sql .= " AND mi.is_available = 1";
            } elseif ($status === 'unavailable') {
                $sql .= " AND mi.is_available = 0";
            } elseif ($status === 'low_stock') {
                $sql .= " AND mi.is_available = 0";
            }
        }

        if (!empty($search)) {
            $sql .= " AND (mi.name LIKE ? OR mi.description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $result = $this->fetchOne($sql, $params);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get restaurant statistics
     */
    public function getRestaurantStats(int $restaurantId): array
    {
        $sql = "SELECT
                    COUNT(*) as total_items,
                    COUNT(CASE WHEN is_available = 1 THEN 1 END) as available_items,
                    COUNT(CASE WHEN is_available = 0 THEN 1 END) as unavailable_items,
                    COUNT(CASE WHEN is_available = 0 THEN 1 END) as low_stock_items,
                    COUNT(CASE WHEN is_available = 0 THEN 1 END) as out_of_stock_items,
                    AVG(price) as avg_price,
                    MIN(price) as min_price,
                    MAX(price) as max_price
                FROM {$this->table}
                WHERE restaurant_id = ?";

        return $this->fetchOne($sql, [$restaurantId]) ?: [];
    }

    /**
     * Get recent items
     */
    public function getRecentItems(int $restaurantId, int $limit = 10): array
    {
        $sql = "SELECT mi.*, c.name as category_name
                FROM {$this->table} mi
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE mi.restaurant_id = ?
                ORDER BY mi.id DESC
                LIMIT ?";

        return $this->fetchAll($sql, [$restaurantId, $limit]);
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems(int $restaurantId): array
    {
        $sql = "SELECT mi.*, c.name as category_name
                FROM {$this->table} mi
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE mi.restaurant_id = ? AND mi.is_available = 1
                ORDER BY mi.name ASC";

        return $this->fetchAll($sql, [$restaurantId]);
    }

    /**
     * Get menu item by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT 
                mi.*,
                c.name as category_name,
                r.name as restaurant_name
            FROM {$this->table} mi
            LEFT JOIN categories c ON mi.category_id = c.id
            LEFT JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE mi.id = ?
        ";
        
        return $this->fetchOne($sql, [$id]);
    }

    /**
     * Create menu item
     */
    public function createItem(array $data): ?int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insertRecord($this->table, $data);
    }

    /**
     * Update menu item
     */
    public function updateItem(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->updateRecord($this->table, $data, ['id' => $id]) > 0;
    }

    /**
     * Delete menu item (hard delete)
     */
    public function deleteItem(int $id): bool
    {
        // Hard delete menu item using DatabaseTrait method
        return $this->deleteRecord($this->table, ['id' => $id]) > 0;
    }

    /**
     * Get popular items analytics for a restaurant
     */
    public function getPopularItemsAnalytics(int $restaurantId, string $period = '7days'): array
    {
        try {
            $dateCondition = '';
            switch ($period) {
                case '7days':
                    $dateCondition = "AND oi.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case '30days':
                    $dateCondition = "AND oi.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case '90days':
                    $dateCondition = "AND oi.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
                    break;
                default:
                    $dateCondition = "";
            }
            
            $sql = "
                SELECT 
                    mi.id,
                    mi.name,
                    mi.price,
                    mi.category_id,
                    c.name as category_name,
                    COUNT(oi.id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.price) as total_revenue,
                    AVG(oi.price) as avg_price
                FROM {$this->table} mi
                LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
                LEFT JOIN orders o ON oi.order_id = o.id
                LEFT JOIN categories c ON mi.category_id = c.id
                WHERE mi.restaurant_id = ? 
                AND o.status IN ('delivered', 'completed')
                {$dateCondition}
                GROUP BY mi.id
                HAVING order_count > 0
                ORDER BY total_quantity DESC, total_revenue DESC
                LIMIT 20
            ";
            
            $results = $this->fetchAll($sql, [$restaurantId]);
            
            // Get category performance
            $categorySQL = "
                SELECT 
                    c.id,
                    c.name,
                    COUNT(oi.id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.price) as total_revenue
                FROM categories c
                LEFT JOIN {$this->table} mi ON c.id = mi.category_id
                LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE mi.restaurant_id = ? 
                AND o.status IN ('delivered', 'completed')
                {$dateCondition}
                GROUP BY c.id
                HAVING order_count > 0
                ORDER BY total_revenue DESC
            ";
            
            $categoryPerformance = $this->fetchAll($categorySQL, [$restaurantId]);
            
            return [
                'popular_items' => $results,
                'category_performance' => $categoryPerformance
            ];
        } catch (\Exception $e) {
            error_log("Error getting popular items analytics: " . $e->getMessage());
            return [
                'popular_items' => [],
                'category_performance' => []
            ];
        }
    }
}
