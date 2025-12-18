<?php

declare(strict_types=1);

namespace Time2Eat\Controllers\Admin;

require_once __DIR__ . '/../../../core/BaseController.php';

use core\BaseController;

/**
 * Admin Order Management Controller
 * Handles order management in admin panel
 */
class OrderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show order details
     */
    public function show(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $order = $this->getOrderById($id);
            if (!$order) {
            $this->redirect('/admin/orders?error=Order not found');
            return;
            }

            // Get order items
        $orderItems = $this->getOrderItems($id);

        $this->renderDashboard('admin/orders/show', [
            'title' => 'Order Details - Time2Eat Admin',
            'order' => $order,
            'orderItems' => $orderItems
        ]);
    }

    /**
     * Show order tracking
     */
    public function track(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $order = $this->getOrderById($id);
        if (!$order) {
            $this->redirect('/admin/orders?error=Order not found');
            return;
        }

        // Get order status history
        $statusHistory = $this->getOrderStatusHistory($id);

        $this->renderDashboard('admin/orders/track', [
            'title' => 'Track Order - Time2Eat Admin',
            'order' => $order,
            'statusHistory' => $statusHistory
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $order = $this->getOrderById($id);
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Order not found'], 404);
                return;
            }

            // Check if order can be cancelled
            if (!in_array($order['status'], ['pending', 'confirmed', 'preparing'])) {
                $this->json(['success' => false, 'message' => 'Order cannot be cancelled at this stage'], 400);
                return;
            }

            // Get input data
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $reason = $input['reason'] ?? 'Cancelled by admin';

            // Update order status
            $stmt = $this->getDb()->prepare("
                UPDATE orders 
                SET status = 'cancelled', 
                    cancellation_reason = ?,
                    cancelled_at = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $success = $stmt->execute([$reason, $id]);

            if ($success) {
                // Log status change
                $this->logOrderStatusChange($id, 'cancelled', $reason);
                
                $this->json(['success' => true, 'message' => 'Order cancelled successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to cancel order'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error cancelling order: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Process refund
     */
    public function refund(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $order = $this->getOrderById($id);
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Order not found'], 404);
                return;
            }

            // Get input data
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $amount = floatval($input['amount'] ?? 0);
            $reason = $input['reason'] ?? 'Refund by admin';

            if ($amount <= 0) {
                $this->json(['success' => false, 'message' => 'Refund amount must be greater than 0'], 400);
                return;
            }

            if ($amount > $order['total_amount']) {
                $this->json(['success' => false, 'message' => 'Refund amount cannot exceed order total'], 400);
                return;
            }

            $currentUser = $this->getCurrentUser();

            // Create refund record
            $stmt = $this->getDb()->prepare("
                INSERT INTO refunds (order_id, amount, reason, processed_by, status, created_at)
                VALUES (?, ?, ?, ?, 'processed', NOW())
            ");
            $stmt->execute([$id, $amount, $reason, $currentUser->id]);

            // Update order status if full refund
            if ($amount >= $order['total_amount']) {
                $stmt = $this->getDb()->prepare("
                    UPDATE orders 
                    SET payment_status = 'refunded', updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$id]);
            }

            $this->json(['success' => true, 'message' => 'Refund processed successfully']);

        } catch (\Exception $e) {
            error_log("Error processing refund: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $order = $this->getOrderById($id);
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Order not found'], 404);
                return;
            }

            // Get input data
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $status = $input['status'] ?? '';
            $notes = $input['notes'] ?? '';

            $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'picked_up', 'on_the_way', 'delivered', 'cancelled'];
            
            if (!in_array($status, $validStatuses)) {
                $this->json(['success' => false, 'message' => 'Invalid status'], 400);
                return;
            }

            // Update order status
            $stmt = $this->getDb()->prepare("
                UPDATE orders 
                SET status = ?, admin_notes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $success = $stmt->execute([$status, $notes, $id]);

            if ($success) {
                // Log status change
                $this->logOrderStatusChange($id, $status, $notes);
                
                $this->json(['success' => true, 'message' => 'Order status updated successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update status'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    // Helper methods
    private function getOrderById(int $id): ?array
    {
        $sql = "SELECT o.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       u.phone as customer_phone,
                       r.name as restaurant_name,
                       r.phone as restaurant_phone,
                       r.address as restaurant_address,
                       CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                       rider.phone as rider_phone
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users rider ON o.rider_id = rider.id
                WHERE o.id = ?";
        
        return $this->fetchOne($sql, [$id]);
    }

    private function getOrderItems(int $orderId): array
    {
        $sql = "SELECT oi.*, 
                       mi.name as item_name, 
                       mi.image as item_image
                FROM order_items oi
                LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id = ?
                ORDER BY oi.id";
        
        return $this->fetchAll($sql, [$orderId]);
    }

    private function getOrderStatusHistory(int $orderId): array
    {
        $sql = "SELECT osh.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as changed_by_name
                FROM order_status_history osh
                LEFT JOIN users u ON osh.changed_by = u.id
                WHERE osh.order_id = ?
                ORDER BY osh.created_at DESC";
        
        try {
            return $this->fetchAll($sql, [$orderId]);
        } catch (\Exception $e) {
            error_log("Error fetching order status history: " . $e->getMessage());
            return [];
        }
    }

    private function logOrderStatusChange(int $orderId, string $status, string $notes = ''): void
    {
        try {
            $currentUser = $this->getCurrentUser();
            $stmt = $this->getDb()->prepare("
                INSERT INTO order_status_history (order_id, status, changed_by, notes, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$orderId, $status, $currentUser->id, $notes]);
        } catch (\Exception $e) {
            error_log("Error logging order status change: " . $e->getMessage());
        }
    }


    /**
     * Render admin dashboard view with dashboard layout
     */
    protected function renderDashboard(string $viewName, array $data = []): void
    {
        // Get current user and convert to array for view compatibility
        $currentUser = $this->getCurrentUser();
        if ($currentUser) {
            $userData = [
                'id' => $currentUser->id,
                'email' => $currentUser->email,
                'first_name' => $currentUser->first_name,
                'last_name' => $currentUser->last_name,
                'role' => $currentUser->role,
                'status' => $currentUser->status ?? 'active'
            ];
            
            // Add user data to view data if not already set
            if (!isset($data['user'])) {
                $data['user'] = $userData;
            }
            if (!isset($data['userRole'])) {
                $data['userRole'] = $currentUser->role;
            }
        }
        
        // Start output buffering to capture the dashboard content
        ob_start();

        // Extract data for the view
        extract($data);

        // Include the specific dashboard view
        $viewFile = __DIR__ . "/../../../views/{$viewName}.php";
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: {$viewName} (looked in: {$viewFile})");
        }

        // Get the content
        $content = ob_get_clean();

        // Render with dashboard layout
        $dashboardLayout = __DIR__ . '/../../../views/components/dashboard-layout.php';
        
        if (file_exists($dashboardLayout)) {
            include $dashboardLayout;
        } else {
            echo $content; // Fallback
        }
    }
}
