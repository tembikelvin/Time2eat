<?php

namespace controllers\Api;

use core\BaseController;
use traits\DatabaseTrait;

class OrderController extends BaseController
{
    use DatabaseTrait;

    protected ?\PDO $db = null;

    /**
     * Get order items for display
     */
    public function getOrderItems(): void
    {
        header('Content-Type: application/json');
        // CRITICAL: Prevent caching of order data (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $this->requireAuth();
            
            $orderId = (int)($_GET['id'] ?? 0);
            $user = $this->getCurrentUser();
            
            if (!$orderId) {
                $this->jsonError('Order ID is required');
                return;
            }

            // Verify order belongs to user
            $orderCheck = $this->fetchOne(
                "SELECT customer_id FROM orders WHERE id = ?", 
                [$orderId]
            );
            
            if (!$orderCheck || $orderCheck['customer_id'] != $user->id) {
                $this->jsonError('Order not found');
                return;
            }

            // Get order items
            $sql = "
                SELECT oi.*, mi.name, mi.description, mi.image
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id = ?
                ORDER BY oi.id
            ";

            $items = $this->fetchAll($sql, [$orderId]);

            $this->jsonSuccess('Order items retrieved', ['items' => $items]);

        } catch (\Exception $e) {
            error_log("Get order items error: " . $e->getMessage());
            $this->jsonError('Failed to load order items');
        }
    }

    /**
     * Get real-time tracking data
     */
    public function getTrackingData(): void
    {
        header('Content-Type: application/json');
        // CRITICAL: Prevent caching of tracking data (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $this->requireAuth();
            
            $orderId = (int)($_GET['id'] ?? 0);
            $user = $this->getCurrentUser();
            
            if (!$orderId) {
                $this->jsonError('Order ID is required');
                return;
            }

            // Verify order belongs to user
            $orderCheck = $this->fetchOne(
                "SELECT customer_id FROM orders WHERE id = ?", 
                [$orderId]
            );
            
            if (!$orderCheck || $orderCheck['customer_id'] != $user->id) {
                $this->jsonError('Order not found');
                return;
            }

            // Get current order status
            $order = $this->fetchOne(
                "SELECT status, updated_at, estimated_delivery_time FROM orders WHERE id = ?", 
                [$orderId]
            );

            // Get rider location if available
            $rider = null;
            if (in_array(strtolower($order['status']), ['picked_up', 'on_the_way'])) {
                $sql = "
                    SELECT u.id, u.first_name, u.last_name, u.phone,
                           CONCAT(u.first_name, ' ', u.last_name) as name,
                           rl.latitude, rl.longitude, rl.updated_at as location_updated_at
                    FROM rider_assignments ra
                    JOIN users u ON ra.rider_id = u.id
                    LEFT JOIN rider_locations rl ON u.id = rl.rider_id
                    WHERE ra.order_id = ? AND ra.status IN ('accepted', 'picked_up')
                    ORDER BY ra.assigned_at DESC
                    LIMIT 1
                ";

                $riderData = $this->fetchOne($sql, [$orderId]);
                
                if ($riderData && $riderData['latitude'] && $riderData['longitude']) {
                    $rider = [
                        'id' => $riderData['id'],
                        'name' => $riderData['name'],
                        'phone' => $riderData['phone'],
                        'location' => [
                            'latitude' => $riderData['latitude'],
                            'longitude' => $riderData['longitude'],
                            'updated_at' => $riderData['location_updated_at']
                        ]
                    ];
                }
            }

            // Calculate ETA
            $eta = null;
            if ($order['estimated_delivery_time']) {
                $eta = date('H:i', strtotime($order['estimated_delivery_time']));
            } elseif (in_array(strtolower($order['status']), ['picked_up', 'on_the_way'])) {
                // Estimate 15-20 minutes from pickup
                $eta = date('H:i', strtotime('+17 minutes'));
            }

            $this->jsonSuccess('Tracking data retrieved', [
                'order' => $order,
                'rider' => $rider,
                'eta' => $eta
            ]);

        } catch (\Exception $e) {
            error_log("Get tracking data error: " . $e->getMessage());
            $this->jsonError('Failed to load tracking data');
        }
    }

    /**
     * Send message to rider
     */
    public function messageRider(): void
    {
        header('Content-Type: application/json');
        // CRITICAL: Prevent caching of message response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $this->requireAuth();
            $this->verifyCsrfToken();
            
            $orderId = (int)($_GET['id'] ?? 0);
            $user = $this->getCurrentUser();
            $input = json_decode(file_get_contents('php://input'), true);
            $message = trim($input['message'] ?? '');
            
            if (!$orderId) {
                $this->jsonError('Order ID is required');
                return;
            }

            if (!$message) {
                $this->jsonError('Message is required');
                return;
            }

            // Verify order belongs to user
            $orderCheck = $this->fetchOne(
                "SELECT customer_id FROM orders WHERE id = ?", 
                [$orderId]
            );
            
            if (!$orderCheck || $orderCheck['customer_id'] != $user->id) {
                $this->jsonError('Order not found');
                return;
            }

            // Get rider for this order
            $rider = $this->fetchOne("
                SELECT ra.rider_id, u.first_name, u.last_name, u.phone
                FROM rider_assignments ra
                JOIN users u ON ra.rider_id = u.id
                WHERE ra.order_id = ? AND ra.status IN ('accepted', 'picked_up')
                ORDER BY ra.assigned_at DESC
                LIMIT 1
            ", [$orderId]);

            if (!$rider) {
                $this->jsonError('No rider assigned to this order');
                return;
            }

            // Store message in database
            $sql = "
                INSERT INTO order_messages (order_id, sender_id, recipient_id, message, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ";

            $this->query($sql, [$orderId, $user->id, $rider['rider_id'], $message]);

            // Here you could integrate with SMS/push notification service
            // For now, we'll just log it
            error_log("Message sent to rider {$rider['first_name']} {$rider['last_name']} for order {$orderId}: {$message}");

            $this->jsonSuccess('Message sent to rider');

        } catch (\Exception $e) {
            error_log("Message rider error: " . $e->getMessage());
            $this->jsonError('Failed to send message');
        }
    }

    /**
     * Cancel order
     */
    public function cancel(): void
    {
        header('Content-Type: application/json');
        // CRITICAL: Prevent caching of cancel response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $this->requireAuth();
            $this->verifyCsrfToken();
            
            $orderId = (int)($_GET['id'] ?? 0);
            $user = $this->getCurrentUser();
            
            if (!$orderId) {
                $this->jsonError('Order ID is required');
                return;
            }

            // Verify order belongs to user and can be cancelled
            $order = $this->fetchOne(
                "SELECT customer_id, status FROM orders WHERE id = ?", 
                [$orderId]
            );
            
            if (!$order || $order['customer_id'] != $user->id) {
                $this->jsonError('Order not found');
                return;
            }

            if (!in_array(strtolower($order['status']), ['pending', 'confirmed'])) {
                $this->jsonError('Order cannot be cancelled at this stage');
                return;
            }

            // Update order status
            $this->query(
                "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?",
                [$orderId]
            );

            // Add to status history
            $this->query(
                "INSERT INTO order_status_history (order_id, status, notes, created_at) VALUES (?, 'cancelled', 'Cancelled by customer', NOW())",
                [$orderId]
            );

            $this->jsonSuccess('Order cancelled successfully');

        } catch (\Exception $e) {
            error_log("Cancel order error: " . $e->getMessage());
            $this->jsonError('Failed to cancel order');
        }
    }

    /**
     * Reorder items
     */
    public function reorderItems(): void
    {
        header('Content-Type: application/json');
        // CRITICAL: Prevent caching of reorder response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $this->requireAuth();
            $this->verifyCsrfToken();
            
            $orderId = (int)($_GET['id'] ?? 0);
            $user = $this->getCurrentUser();
            
            if (!$orderId) {
                $this->jsonError('Order ID is required');
                return;
            }

            // Verify order belongs to user
            $orderCheck = $this->fetchOne(
                "SELECT customer_id FROM orders WHERE id = ?", 
                [$orderId]
            );
            
            if (!$orderCheck || $orderCheck['customer_id'] != $user->id) {
                $this->jsonError('Order not found');
                return;
            }

            // Get order items
            $items = $this->fetchAll(
                "SELECT menu_item_id, quantity, special_instructions FROM order_items WHERE order_id = ?",
                [$orderId]
            );

            if (empty($items)) {
                $this->jsonError('No items found in this order');
                return;
            }

            // Clear current cart
            $this->query("DELETE FROM cart_items WHERE user_id = ?", [$user->id]);

            // Add items to cart
            foreach ($items as $item) {
                $this->query(
                    "INSERT INTO cart_items (user_id, menu_item_id, quantity, special_instructions, created_at) VALUES (?, ?, ?, ?, NOW())",
                    [$user->id, $item['menu_item_id'], $item['quantity'], $item['special_instructions']]
                );
            }

            $this->jsonSuccess('Items added to cart');

        } catch (\Exception $e) {
            error_log("Reorder items error: " . $e->getMessage());
            $this->jsonError('Failed to reorder items');
        }
    }

    /**
     * Rate order
     */
    public function rateOrder(): void
    {
        header('Content-Type: application/json');
        // CRITICAL: Prevent caching of rating response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $this->requireAuth();
            $this->verifyCsrfToken();
            
            $orderId = (int)($_GET['id'] ?? 0);
            $user = $this->getCurrentUser();
            $input = json_decode(file_get_contents('php://input'), true);
            $rating = (int)($input['rating'] ?? 0);
            $review = trim($input['review'] ?? '');
            
            if (!$orderId) {
                $this->jsonError('Order ID is required');
                return;
            }

            if ($rating < 1 || $rating > 5) {
                $this->jsonError('Rating must be between 1 and 5');
                return;
            }

            // Verify order belongs to user and is delivered
            $order = $this->fetchOne(
                "SELECT customer_id, status, restaurant_id FROM orders WHERE id = ?", 
                [$orderId]
            );
            
            if (!$order || $order['customer_id'] != $user->id) {
                $this->jsonError('Order not found');
                return;
            }

            if (strtolower($order['status']) !== 'delivered') {
                $this->jsonError('Order must be delivered to rate');
                return;
            }

            // Check if already rated
            $existingRating = $this->fetchOne(
                "SELECT id FROM order_ratings WHERE order_id = ?",
                [$orderId]
            );

            if ($existingRating) {
                $this->jsonError('Order has already been rated');
                return;
            }

            // Insert rating
            $this->query(
                "INSERT INTO order_ratings (order_id, customer_id, restaurant_id, rating, review, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$orderId, $user->id, $order['restaurant_id'], $rating, $review]
            );

            // Update order with rating
            $this->query(
                "UPDATE orders SET rating = ?, review = ?, updated_at = NOW() WHERE id = ?",
                [$rating, $review, $orderId]
            );

            $this->jsonSuccess('Rating submitted successfully');

        } catch (\Exception $e) {
            error_log("Rate order error: " . $e->getMessage());
            $this->jsonError('Failed to submit rating');
        }
    }

    /**
     * Confirm order receipt by customer
     */
    public function confirmReceipt(): void
    {
        header('Content-Type: application/json');
        // CRITICAL: Prevent caching of confirmation response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            $this->requireAuth();
            $this->verifyCsrfToken();

            $orderId = (int)($_GET['id'] ?? 0);
            $user = $this->getCurrentUser();

            if (!$orderId) {
                $this->jsonError('Order ID is required');
                return;
            }

            // Verify order belongs to user and is delivered
            $order = $this->fetchOne(
                "SELECT customer_id, status FROM orders WHERE id = ?",
                [$orderId]
            );

            if (!$order || $order['customer_id'] != $user->id) {
                $this->jsonError('Order not found');
                return;
            }

            if (strtolower($order['status']) !== 'delivered') {
                $this->jsonError('Can only confirm delivered orders');
                return;
            }

            // Update order with confirmation
            $this->query(
                "UPDATE orders SET customer_confirmed = 1, customer_confirmed_at = NOW(), updated_at = NOW() WHERE id = ?",
                [$orderId]
            );

            // Add to status history
            $this->query(
                "INSERT INTO order_status_history (order_id, status, notes, created_at) VALUES (?, 'confirmed_by_customer', 'Customer confirmed receipt of order', NOW())",
                [$orderId]
            );

            $this->jsonSuccess('Order receipt confirmed');

        } catch (\Exception $e) {
            error_log("Confirm receipt error: " . $e->getMessage());
            $this->jsonError('Failed to confirm receipt');
        }
    }
}
