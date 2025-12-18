<?php

namespace Time2Eat\Controllers\Admin;

use core\BaseController;
use PDO;

class DashboardApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Check if user is authenticated and is admin
     */
    private function checkAuth(): bool
    {
        // Debug logging
        error_log("Broadcast Auth Check - Session: " . json_encode($_SESSION ?? []));
        error_log("Broadcast Auth Check - User: " . json_encode($this->getCurrentUser()));
        
        if (!$this->isAuthenticated()) {
            error_log("Broadcast Auth Check - Not authenticated");
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            return false;
        }
        
        if (!$this->isAdmin()) {
            error_log("Broadcast Auth Check - Not admin");
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            return false;
        }
        
        error_log("Broadcast Auth Check - Success");
        return true;
    }

    /**
     * Get live delivery data for the dashboard monitor
     */
    public function liveDeliveries()
    {
        try {
            $pdo = $this->getDb();
            
            // Get active deliveries with rider and customer info
            $stmt = $pdo->prepare("
                SELECT 
                    o.id,
                    o.order_id,
                    o.status,
                    o.created_at,
                    o.estimated_delivery_time,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    r.name as restaurant_name,
                    CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                    o.delivery_address,
                    CASE 
                        WHEN o.status = 'preparing' THEN 'Preparing Order'
                        WHEN o.status = 'ready_for_pickup' THEN 'Ready for Pickup'
                        WHEN o.status = 'picked_up' THEN 'Picked Up'
                        WHEN o.status = 'out_for_delivery' THEN 'Out for Delivery'
                        WHEN o.status = 'delivered' THEN 'Delivered'
                        ELSE 'Unknown Status'
                    END as status_display,
                    CASE 
                        WHEN o.estimated_delivery_time > NOW() THEN CONCAT(TIMESTAMPDIFF(MINUTE, NOW(), o.estimated_delivery_time), ' min')
                        ELSE 'Overdue'
                    END as estimated_time,
                    '2.5 km' as distance
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users rider ON o.rider_id = rider.id
                WHERE o.status IN ('preparing', 'ready_for_pickup', 'picked_up', 'out_for_delivery')
                ORDER BY o.created_at DESC
                LIMIT 10
            ");
            
            $stmt->execute();
            $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get delivery statistics
            $statsStmt = $pdo->prepare("
                SELECT 
                    SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
                    SUM(CASE WHEN status = 'ready_for_pickup' THEN 1 ELSE 0 END) as pickup,
                    SUM(CASE WHEN status IN ('picked_up', 'out_for_delivery') THEN 1 ELSE 0 END) as delivery,
                    (SELECT COUNT(*) FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()) as completed_today
                FROM orders 
                WHERE status IN ('preparing', 'ready_for_pickup', 'picked_up', 'out_for_delivery')
            ");
            
            $statsStmt->execute();
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'deliveries' => $deliveries,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            error_log("Dashboard API Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch delivery data'
            ]);
        }
    }

    /**
     * Get dashboard counts for quick actions
     */
    public function getCounts()
    {
        try {
            $pdo = $this->getDb();
            
            // Get urgent orders (orders older than estimated time)
            $urgentStmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM orders 
                WHERE status IN ('preparing', 'ready_for_pickup', 'picked_up', 'out_for_delivery')
                AND (estimated_delivery_time < NOW() OR created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR))
            ");
            $urgentStmt->execute();
            $urgentOrders = $urgentStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Get pending approvals (restaurants and riders)
            $approvalsStmt = $pdo->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM restaurants WHERE status = 'pending') +
                    (SELECT COUNT(*) FROM users WHERE role = 'rider' AND status = 'pending') as count
            ");
            $approvalsStmt->execute();
            $pendingApprovals = $approvalsStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'success' => true,
                'urgent_orders' => $urgentOrders,
                'pending_approvals' => $pendingApprovals
            ]);
            
        } catch (Exception $e) {
            error_log("Dashboard Counts Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch counts'
            ]);
        }
    }

    /**
     * Send broadcast notification to all users
     */
    public function broadcastNotification()
    {
        try {
            // Check authentication first
            if (!$this->checkAuth()) {
                return;
            }
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON input: ' . json_last_error_msg());
            }
            
            $message = trim($input['message'] ?? '');
            
            if (empty($message)) {
                throw new \Exception('Message cannot be empty');
            }
            
            if (strlen($message) > 500) {
                throw new \Exception('Message too long (max 500 characters)');
            }
            
            // Get all active users
            $users = $this->fetchAll("SELECT id FROM users WHERE status = 'active' AND deleted_at IS NULL");
            
            if (empty($users)) {
                throw new \Exception('No active users found');
            }
            
            $pdo = $this->getDb();
            $pdo->beginTransaction();
            
            try {
                // Insert notification for each user
                $notificationStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, title, message, type, priority, status, created_at) 
                    VALUES (?, 'System Announcement', ?, 'system_alert', 'high', 'pending', NOW())
                ");
                
                $successCount = 0;
                foreach ($users as $user) {
                    if ($notificationStmt->execute([$user['id'], $message])) {
                        $successCount++;
                    }
                }
                
                $pdo->commit();
                
                // Log the broadcast
                error_log("Broadcast notification sent to {$successCount} users: " . substr($message, 0, 100));
                
                echo json_encode([
                    'success' => true,
                    'message' => "Broadcast sent to {$successCount} users successfully",
                    'count' => $successCount
                ]);
                
            } catch (\Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            error_log("Broadcast Error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenance()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $pdo = $this->getDb();
            
            // Get current maintenance mode status
            $stmt = $pdo->prepare("SELECT `value` FROM site_settings WHERE `key` = 'maintenance_mode'");
            $stmt->execute();
            $currentStatus = $stmt->fetchColumn();
            
            $newStatus = ($currentStatus === 'true') ? 'false' : 'true';
            
            // Update maintenance mode
            $updateStmt = $pdo->prepare("
                UPDATE site_settings 
                SET `value` = ?, updated_at = NOW() 
                WHERE `key` = 'maintenance_mode'
            ");
            $updateStmt->execute([$newStatus]);
            
            // If maintenance mode doesn't exist, create it
            if ($updateStmt->rowCount() === 0) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO site_settings (`key`, `value`, `type`, `group`, `description`, created_at, updated_at) 
                    VALUES ('maintenance_mode', ?, 'boolean', 'system', 'Enable maintenance mode', NOW(), NOW())
                ");
                $insertStmt->execute([$newStatus]);
            }
            
            echo json_encode([
                'success' => true,
                'enabled' => $newStatus === 'true',
                'message' => 'Maintenance mode ' . ($newStatus === 'true' ? 'enabled' : 'disabled')
            ]);
            
        } catch (Exception $e) {
            error_log("Maintenance Toggle Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Export dashboard data
     */
    public function exportData()
    {
        try {
            $pdo = $this->getDb();
            
            // Get comprehensive dashboard data
            $data = [
                'export_date' => date('Y-m-d H:i:s'),
                'orders' => [],
                'users' => [],
                'restaurants' => [],
                'revenue' => []
            ];
            
            // Get recent orders
            $ordersStmt = $pdo->prepare("
                SELECT o.*, u.first_name, u.last_name, r.name as restaurant_name
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                ORDER BY o.created_at DESC
                LIMIT 1000
            ");
            $ordersStmt->execute();
            $data['orders'] = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="dashboard_export_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Write orders data
            fputcsv($output, ['Order ID', 'Customer', 'Restaurant', 'Status', 'Total', 'Date']);
            foreach ($data['orders'] as $order) {
                fputcsv($output, [
                    $order['order_id'],
                    $order['first_name'] . ' ' . $order['last_name'],
                    $order['restaurant_name'],
                    $order['status'],
                    $order['total_amount'],
                    $order['created_at']
                ]);
            }
            
            fclose($output);
            
        } catch (Exception $e) {
            error_log("Export Error: " . $e->getMessage());
            echo "Error exporting data: " . $e->getMessage();
        }
    }
}
