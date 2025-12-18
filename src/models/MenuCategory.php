<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

/**
 * Menu Category Model
 * Manages restaurant menu categories
 */
class MenuCategory
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    protected $table = 'menu_categories';

    /**
     * Get category by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    /**
     * Get categories by restaurant
     */
    public function getByRestaurant(int $restaurantId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = 1 
                ORDER BY sort_order ASC, name ASC";
        
        return $this->fetchAll($sql, [$restaurantId]);
    }

    /**
     * Find category by name and restaurant
     */
    public function findByName(string $name, int $restaurantId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND name = ? 
                LIMIT 1";
        
        return $this->fetchOne($sql, [$restaurantId, $name]);
    }

    /**
     * Create new category
     */
    public function createCategory(array $data): ?int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }

    /**
     * Delete category (soft delete)
     */
    public function deleteCategory(int $id): bool
    {
        // Hard delete
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }

    /**
     * Get category with item count
     */
    public function getCategoryWithItemCount(int $restaurantId): array
    {
        $sql = "SELECT c.*, 
                       COUNT(mi.id) as item_count,
                       COUNT(CASE WHEN mi.is_available = 1 THEN 1 END) as available_count
                FROM {$this->table} c
                LEFT JOIN menu_items mi ON c.id = mi.category_id AND mi.deleted_at IS NULL
                WHERE c.restaurant_id = ? AND c.is_active = 1
                GROUP BY c.id
                ORDER BY c.sort_order ASC, c.name ASC";
        
        return $this->fetchAll($sql, [$restaurantId]);
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        return $this->update($id, [
            'sort_order' => $sortOrder,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get next sort order
     */
    public function getNextSortOrder(int $restaurantId): int
    {
        $sql = "SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order 
                FROM {$this->table} 
                WHERE restaurant_id = ?";
        
        $result = $this->fetchOne($sql, [$restaurantId]);
        return (int)($result['next_order'] ?? 1);
    }

    /**
     * Reorder categories
     */
    public function reorderCategories(int $restaurantId, array $categoryIds): bool
    {
        try {
            $this->beginTransaction();
            
            foreach ($categoryIds as $index => $categoryId) {
                $this->updateSortOrder((int)$categoryId, $index + 1);
            }
            
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Get categories for dropdown
     */
    public function getForDropdown(int $restaurantId): array
    {
        $sql = "SELECT id, name FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = 1 
                ORDER BY sort_order ASC, name ASC";
        
        return $this->fetchAll($sql, [$restaurantId]);
    }

    /**
     * Check if category has items
     */
    public function hasItems(int $categoryId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM menu_items 
                WHERE category_id = ? AND deleted_at IS NULL";
        
        $result = $this->fetchOne($sql, [$categoryId]);
        return (int)($result['count'] ?? 0) > 0;
    }

    /**
     * Get category statistics
     */
    public function getCategoryStats(int $categoryId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_items,
                    COUNT(CASE WHEN is_available = 1 THEN 1 END) as available_items,
                    COUNT(CASE WHEN stock_quantity = 0 THEN 1 END) as out_of_stock,
                    AVG(price) as avg_price,
                    MIN(price) as min_price,
                    MAX(price) as max_price
                FROM menu_items 
                WHERE category_id = ? AND deleted_at IS NULL";
        
        return $this->fetchOne($sql, [$categoryId]) ?: [];
    }

    /**
     * Search categories
     */
    public function searchCategories(int $restaurantId, string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = 1 
                AND (name LIKE ? OR description LIKE ?)
                ORDER BY name ASC";
        
        $searchTerm = "%$query%";
        return $this->fetchAll($sql, [$restaurantId, $searchTerm, $searchTerm]);
    }

    /**
     * Get popular categories (by order count)
     */
    public function getPopularCategories(int $restaurantId, int $limit = 5): array
    {
        $sql = "SELECT c.*, COUNT(oi.id) as order_count
                FROM {$this->table} c
                JOIN menu_items mi ON c.id = mi.category_id
                JOIN order_items oi ON mi.id = oi.menu_item_id
                JOIN orders o ON oi.order_id = o.id
                WHERE c.restaurant_id = ? AND c.is_active = 1
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY c.id
                ORDER BY order_count DESC
                LIMIT ?";
        
        return $this->fetchAll($sql, [$restaurantId, $limit]);
    }

    /**
     * Validate category data
     */
    public function validateCategoryData(array $data, ?int $categoryId = null): array
    {
        $errors = [];

        // Required fields
        if (empty($data['name'])) {
            $errors['name'] = 'Category name is required';
        } elseif (strlen($data['name']) > 100) {
            $errors['name'] = 'Category name must be less than 100 characters';
        }

        if (empty($data['restaurant_id'])) {
            $errors['restaurant_id'] = 'Restaurant ID is required';
        }

        // Check for duplicate name
        if (!empty($data['name']) && !empty($data['restaurant_id'])) {
            $existing = $this->findByName($data['name'], (int)$data['restaurant_id']);
            if ($existing && (!$categoryId || $existing['id'] !== $categoryId)) {
                $errors['name'] = 'Category name already exists';
            }
        }

        // Optional fields validation
        if (!empty($data['sort_order']) && (!is_numeric($data['sort_order']) || (int)$data['sort_order'] < 0)) {
            $errors['sort_order'] = 'Sort order must be a non-negative number';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get categories with recent activity
     */
    public function getCategoriesWithActivity(int $restaurantId): array
    {
        $sql = "SELECT c.*, 
                       COUNT(DISTINCT mi.id) as item_count,
                       COUNT(DISTINCT CASE WHEN mi.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN mi.id END) as new_items_week,
                       MAX(mi.updated_at) as last_item_update
                FROM {$this->table} c
                LEFT JOIN menu_items mi ON c.id = mi.category_id AND mi.deleted_at IS NULL
                WHERE c.restaurant_id = ? AND c.is_active = 1
                GROUP BY c.id
                ORDER BY c.sort_order ASC, c.name ASC";
        
        return $this->fetchAll($sql, [$restaurantId]);
    }

    /**
     * Bulk update category status
     */
    public function bulkUpdateStatus(array $categoryIds, int $status): bool
    {
        if (empty($categoryIds)) {
            return false;
        }

        $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
        $sql = "UPDATE {$this->table} 
                SET is_active = ?, updated_at = NOW() 
                WHERE id IN ($placeholders)";
        
        $params = array_merge([$status], $categoryIds);
        return $this->execute($sql, $params) > 0;
    }

    /**
     * Get category hierarchy (if implementing nested categories)
     */
    public function getCategoryHierarchy(int $restaurantId): array
    {
        $sql = "SELECT c.*, 
                       COALESCE(parent.name, '') as parent_name,
                       COUNT(mi.id) as item_count
                FROM {$this->table} c
                LEFT JOIN {$this->table} parent ON c.parent_id = parent.id
                LEFT JOIN menu_items mi ON c.id = mi.category_id AND mi.deleted_at IS NULL
                WHERE c.restaurant_id = ? AND c.is_active = 1
                GROUP BY c.id
                ORDER BY COALESCE(c.parent_id, c.id), c.sort_order ASC, c.name ASC";
        
        return $this->fetchAll($sql, [$restaurantId]);
    }
}
