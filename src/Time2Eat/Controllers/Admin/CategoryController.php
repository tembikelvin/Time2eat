<?php

namespace Time2Eat\Controllers\Admin;

use core\Controller;

class CategoryController extends Controller
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
                LEFT JOIN restaurants r ON c.id = r.category_id AND r.deleted_at IS NULL
                WHERE c.deleted_at IS NULL
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
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            return $this->json(['error' => 'Category name is required'], 400);
        }

        try {
            // Check if category already exists
            $existing = $this->fetchOne("SELECT id FROM categories WHERE name = ? AND deleted_at IS NULL", [$name]);
            if ($existing) {
                return $this->json(['error' => 'Category with this name already exists'], 400);
            }

            // Create category
            $this->execute("
                INSERT INTO categories (name, description, status, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ", [$name, $description, $status]);

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
        $status = $input['status'] ?? 'active';

        if (empty($name)) {
            return $this->json(['error' => 'Category name is required'], 400);
        }

        try {
            // Check if category exists
            $category = $this->fetchOne("SELECT id FROM categories WHERE id = ? AND deleted_at IS NULL", [$id]);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], 404);
            }

            // Check if name is already taken by another category
            $existing = $this->fetchOne("SELECT id FROM categories WHERE name = ? AND id != ? AND deleted_at IS NULL", [$name, $id]);
            if ($existing) {
                return $this->json(['error' => 'Category with this name already exists'], 400);
            }

            // Update category
            $this->execute("
                UPDATE categories 
                SET name = ?, description = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ", [$name, $description, $status, $id]);

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
            $category = $this->fetchOne("SELECT id FROM categories WHERE id = ? AND deleted_at IS NULL", [$id]);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], 404);
            }

            // Check if category has active restaurants
            $restaurantCount = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE category_id = ? AND deleted_at IS NULL", [$id]);
            if ($restaurantCount['count'] > 0) {
                return $this->json(['error' => 'Cannot delete category with active restaurants'], 400);
            }

            // Soft delete category
            $this->execute("UPDATE categories SET deleted_at = NOW() WHERE id = ?", [$id]);

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
            $category = $this->fetchOne("SELECT id, status FROM categories WHERE id = ? AND deleted_at IS NULL", [$id]);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], 404);
            }

            $newStatus = $category['status'] === 'active' ? 'inactive' : 'active';
            
            $this->execute("UPDATE categories SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);

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
