<?php

namespace Time2Eat\Controllers\Admin;

use controllers\AdminBaseController;

class CategoryController extends AdminBaseController
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        try {
            $categories = $this->fetchAll("
                SELECT c.*, 
                       COUNT(r.id) as restaurant_count,
                       COUNT(CASE WHEN r.status = 'active' THEN 1 END) as active_restaurants
                FROM categories c
                LEFT JOIN restaurants r ON c.name = r.cuisine_type AND r.deleted_at IS NULL
                WHERE c.is_active = 1
                GROUP BY c.id
                ORDER BY c.name ASC
            ");

            return $this->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return $this->json(['error' => 'Failed to fetch categories'], 500);
        }
    }

    /**
     * Store a newly created category
     */
    public function store()
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        // Validate input
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['status']) ? ($_POST['status'] === 'active' ? 1 : 0) : 1;

        if (empty($name)) {
            return $this->json(['error' => 'Category name is required'], 400);
        }

        try {
            // Check if category already exists
            $existing = $this->fetchOne("SELECT id FROM categories WHERE name = ?", [$name]);
            if ($existing) {
                return $this->json(['error' => 'Category with this name already exists'], 400);
            }

            // Create category
            $this->execute("
                INSERT INTO categories (name, description, is_active, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ", [$name, $description, $isActive]);

            $categoryId = $this->getLastInsertId();

            return $this->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => ['id' => $categoryId]
            ]);
        } catch (\Exception $e) {
            error_log("Error creating category: " . $e->getMessage());
            return $this->json(['error' => 'Failed to create category'], 500);
        }
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        try {
            $category = $this->fetchOne("
                SELECT c.*, 
                       COUNT(r.id) as restaurant_count,
                       COUNT(CASE WHEN r.status = 'active' THEN 1 END) as active_restaurants
                FROM categories c
                LEFT JOIN restaurants r ON c.id = r.category_id AND r.deleted_at IS NULL
                WHERE c.id = ? AND c.deleted_at IS NULL
                GROUP BY c.id
            ", [$id]);

            if (!$category) {
                return $this->json(['error' => 'Category not found'], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $category
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching category: " . $e->getMessage());
            return $this->json(['error' => 'Failed to fetch category'], 500);
        }
    }

    /**
     * Update the specified category
     */
    public function update($id)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        // Get input data (support both PUT and POST)
        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $isActive = isset($input['status']) ? ($input['status'] === 'active' ? 1 : 0) : 1;

        if (empty($name)) {
            return $this->json(['error' => 'Category name is required'], 400);
        }

        try {
            // Check if category exists
            $category = $this->fetchOne("SELECT id FROM categories WHERE id = ?", [$id]);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], 404);
            }

            // Check if name is already taken by another category
            $existing = $this->fetchOne("SELECT id FROM categories WHERE name = ? AND id != ?", [$name, $id]);
            if ($existing) {
                return $this->json(['error' => 'Category with this name already exists'], 400);
            }

            // Update category
            $this->execute("
                UPDATE categories 
                SET name = ?, description = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ", [$name, $description, $isActive, $id]);

            return $this->json([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error updating category: " . $e->getMessage());
            return $this->json(['error' => 'Failed to update category'], 500);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        try {
            // Check if category exists
            $category = $this->fetchOne("SELECT id FROM categories WHERE id = ?", [$id]);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], 404);
            }

            // Check if category has active restaurants (using cuisine_type matching)
            $categoryName = $this->fetchOne("SELECT name FROM categories WHERE id = ?", [$id]);
            if ($categoryName) {
                $restaurantCount = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE cuisine_type = ? AND deleted_at IS NULL", [$categoryName['name']]);
                if ($restaurantCount['count'] > 0) {
                    return $this->json(['error' => 'Cannot delete category with active restaurants'], 400);
                }
            }

            // Soft delete category (set is_active to 0)
            $this->execute("UPDATE categories SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);

            return $this->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error deleting category: " . $e->getMessage());
            return $this->json(['error' => 'Failed to delete category'], 500);
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus($id)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        try {
            $category = $this->fetchOne("SELECT id, is_active FROM categories WHERE id = ?", [$id]);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], 404);
            }

            $newStatus = $category['is_active'] ? 0 : 1;
            
            $this->execute("UPDATE categories SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);

            return $this->json([
                'success' => true,
                'message' => 'Category status updated successfully',
                'data' => ['status' => $newStatus]
            ]);
        } catch (\Exception $e) {
            error_log("Error toggling category status: " . $e->getMessage());
            return $this->json(['error' => 'Failed to update category status'], 500);
        }
    }
}
