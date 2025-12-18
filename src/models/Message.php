<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . '/../core/Model.php';

use core\Model;
use PDO;

class Message extends Model
{
    protected $table = 'messages';

    /**
     * Get conversations for a user
     */
    public function getConversationsForUser(int $userId, string $userRole = 'customer'): array
    {
        try {
            $sql = "
                SELECT 
                    m.conversation_id,
                    m.order_id,
                    CASE 
                        WHEN m.sender_id = ? THEN m.recipient_id 
                        ELSE m.sender_id 
                    END as other_party_id,
                    CASE 
                        WHEN m.sender_id = ? THEN recipient_u.first_name 
                        ELSE sender_u.first_name 
                    END as other_party_first_name,
                    CASE 
                        WHEN m.sender_id = ? THEN recipient_u.last_name 
                        ELSE sender_u.last_name 
                    END as other_party_last_name,
                    CASE 
                        WHEN m.sender_id = ? THEN CONCAT(recipient_u.first_name, ' ', recipient_u.last_name)
                        ELSE CONCAT(sender_u.first_name, ' ', sender_u.last_name)
                    END as other_party_name,
                    CASE 
                        WHEN m.sender_id = ? THEN recipient_u.role 
                        ELSE sender_u.role 
                    END as other_party_role,
                    CASE 
                        WHEN m.sender_id = ? THEN recipient_u.phone 
                        ELSE sender_u.phone 
                    END as other_party_phone,
                    latest.message as last_message,
                    latest.created_at as last_message_at,
                    COALESCE(unread_counts.unread_count, 0) as unread_count,
                    CASE 
                        WHEN o.id IS NOT NULL THEN 'order'
                        WHEN (m.sender_id = ? AND recipient_u.role = 'vendor') OR (m.recipient_id = ? AND sender_u.role = 'vendor') THEN 'restaurant'
                        WHEN (m.sender_id = ? AND recipient_u.role = 'rider') OR (m.recipient_id = ? AND sender_u.role = 'rider') THEN 'rider'
                        WHEN (m.sender_id = ? AND recipient_u.role = 'admin') OR (m.recipient_id = ? AND sender_u.role = 'admin') THEN 'support'
                        ELSE 'general'
                    END as type,
                    r.name as restaurant_name
                FROM messages m
                LEFT JOIN users sender_u ON m.sender_id = sender_u.id
                LEFT JOIN users recipient_u ON m.recipient_id = recipient_u.id
                LEFT JOIN orders o ON m.order_id = o.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                INNER JOIN (
                    SELECT 
                        conversation_id,
                        MAX(created_at) as max_created_at
                    FROM messages 
                    WHERE (sender_id = ? OR recipient_id = ?)
                    GROUP BY conversation_id
                ) latest_msg ON m.conversation_id = latest_msg.conversation_id AND m.created_at = latest_msg.max_created_at
                LEFT JOIN (
                    SELECT 
                        m.message as message,
                        m.created_at,
                        m.conversation_id
                    FROM messages m
                    INNER JOIN (
                        SELECT 
                            conversation_id,
                            MAX(created_at) as max_created_at
                        FROM messages 
                        WHERE (sender_id = ? OR recipient_id = ?)
                        GROUP BY conversation_id
                    ) lm ON m.conversation_id = lm.conversation_id AND m.created_at = lm.max_created_at
                ) latest ON m.conversation_id = latest.conversation_id
                LEFT JOIN (
                    SELECT 
                        conversation_id,
                        COUNT(*) as unread_count
                    FROM messages 
                    WHERE recipient_id = ? AND is_read = 0
                    GROUP BY conversation_id
                ) unread_counts ON m.conversation_id = unread_counts.conversation_id
                WHERE (m.sender_id = ? OR m.recipient_id = ?)
                GROUP BY m.conversation_id
                ORDER BY latest.created_at DESC
            ";

            $params = array_fill(0, 19, $userId);
            
            return $this->query($sql, $params);
            
        } catch (\Exception $e) {
            error_log("Error getting conversations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get messages for a specific conversation
     */
    public function getConversationMessages(string $conversationId, int $userId): ?array
    {
        try {
            // First verify user has access to this conversation
            $accessCheck = $this->query(
                "SELECT COUNT(*) as count FROM messages 
                 WHERE conversation_id = ? AND (sender_id = ? OR recipient_id = ?)",
                [$conversationId, $userId, $userId]
            );
            $accessCheck = $accessCheck[0] ?? null;

            if (!$accessCheck || $accessCheck['count'] == 0) {
                return null;
            }

            // Get conversation details
            $conversationResult = $this->query("
                SELECT 
                    m.conversation_id,
                    m.order_id,
                    CASE 
                        WHEN m.sender_id = ? THEN m.recipient_id 
                        ELSE m.sender_id 
                    END as other_party_id,
                    CASE 
                        WHEN m.sender_id = ? THEN CONCAT(recipient_u.first_name, ' ', recipient_u.last_name)
                        ELSE CONCAT(sender_u.first_name, ' ', sender_u.last_name)
                    END as other_party_name,
                    CASE 
                        WHEN m.sender_id = ? THEN recipient_u.role 
                        ELSE sender_u.role 
                    END as other_party_role,
                    CASE 
                        WHEN m.sender_id = ? THEN recipient_u.phone 
                        ELSE sender_u.phone 
                    END as other_party_phone,
                    CASE 
                        WHEN o.id IS NOT NULL THEN 'order'
                        WHEN (m.sender_id = ? AND recipient_u.role = 'vendor') OR (m.recipient_id = ? AND sender_u.role = 'vendor') THEN 'restaurant'
                        WHEN (m.sender_id = ? AND recipient_u.role = 'rider') OR (m.recipient_id = ? AND sender_u.role = 'rider') THEN 'rider'
                        WHEN (m.sender_id = ? AND recipient_u.role = 'admin') OR (m.recipient_id = ? AND sender_u.role = 'admin') THEN 'support'
                        ELSE 'general'
                    END as type
                FROM messages m
                LEFT JOIN users sender_u ON m.sender_id = sender_u.id
                LEFT JOIN users recipient_u ON m.recipient_id = recipient_u.id
                LEFT JOIN orders o ON m.order_id = o.id
                WHERE m.conversation_id = ?
                LIMIT 1
            ", array_merge(array_fill(0, 10, $userId), [$conversationId]));
            
            $conversation = $conversationResult[0] ?? null;
            if (!$conversation) {
                return null;
            }

            // Get all messages in the conversation
            $messages = $this->query("
                SELECT 
                    m.id,
                    m.message,
                    m.message_type,
                    m.attachments,
                    m.is_read,
                    m.created_at,
                    CASE 
                        WHEN m.sender_id = ? THEN 'customer'
                        WHEN sender_u.role = 'vendor' THEN 'vendor'
                        WHEN sender_u.role = 'rider' THEN 'rider'
                        WHEN sender_u.role = 'admin' THEN 'support'
                        ELSE 'other'
                    END as sender_type,
                    CONCAT(sender_u.first_name, ' ', sender_u.last_name) as sender_name
                FROM messages m
                LEFT JOIN users sender_u ON m.sender_id = sender_u.id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC
            ", [$userId, $conversationId]);

            $conversation['messages'] = $messages;

            // Mark messages as read for this user
            $this->markConversationAsRead($conversationId, $userId);

            return $conversation;

        } catch (\Exception $e) {
            error_log("Error getting conversation messages: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a message
     */
    public function sendMessage(array $data): bool
    {
        try {
            $sql = "
                INSERT INTO messages (
                    conversation_id, sender_id, recipient_id, order_id, 
                    message, message_type, attachments, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ";

            $params = [
                $data['conversation_id'],
                $data['sender_id'],
                $data['recipient_id'],
                $data['order_id'] ?? null,
                $data['message'],
                $data['message_type'] ?? 'text',
                $data['attachments'] ?? null
            ];

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (\Exception $e) {
            error_log("Error sending message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new conversation
     */
    public function createConversation(int $senderId, int $recipientId, string $message, ?int $orderId = null, ?string $subject = null): ?string
    {
        try {
            // Generate conversation ID
            $conversationId = 'conv_' . time() . '_' . uniqid();

            // Insert the first message
            $messageData = [
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'order_id' => $orderId,
                'message' => $subject ? "$subject\n\n$message" : $message,
                'message_type' => 'text'
            ];

            if ($this->sendMessage($messageData)) {
                return $conversationId;
            }

            return null;

        } catch (\Exception $e) {
            error_log("Error creating conversation: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark conversation as read for a user
     */
    public function markConversationAsRead(string $conversationId, int $userId): bool
    {
        try {
            $sql = "
                UPDATE messages 
                SET is_read = 1, read_at = NOW() 
                WHERE conversation_id = ? AND recipient_id = ? AND is_read = 0
            ";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$conversationId, $userId]);

        } catch (\Exception $e) {
            error_log("Error marking conversation as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get message statistics for a user
     */
    public function getMessageStats(int $userId): array
    {
        try {
            // Get unread count
            $unreadResult = $this->query(
                "SELECT COUNT(*) as count FROM messages 
                 WHERE recipient_id = ? AND is_read = 0",
                [$userId]
            );
            $unreadResult = $unreadResult[0] ?? null;

            // Get active conversations count
            $activeResult = $this->query(
                "SELECT COUNT(DISTINCT conversation_id) as count FROM messages 
                 WHERE (sender_id = ? OR recipient_id = ?) 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                [$userId, $userId]
            );
            $activeResult = $activeResult[0] ?? null;

            // Calculate average response time (mock for now)
            $avgResponseTime = '5m'; // This would need more complex calculation

            return [
                'unread' => (int)($unreadResult['count'] ?? 0),
                'active' => (int)($activeResult['count'] ?? 0),
                'avgResponseTime' => $avgResponseTime
            ];

        } catch (\Exception $e) {
            error_log("Error getting message stats: " . $e->getMessage());
            return [
                'unread' => 0,
                'active' => 0,
                'avgResponseTime' => '5m'
            ];
        }
    }

    /**
     * Get restaurants that customer has ordered from (for compose message)
     */
    public function getCustomerRestaurants(int $customerId): array
    {
        try {
            $sql = "
                SELECT DISTINCT 
                    r.id,
                    r.name,
                    r.phone,
                    r.email,
                    COUNT(o.id) as order_count
                FROM restaurants r
                INNER JOIN orders o ON r.id = o.restaurant_id
                WHERE o.customer_id = ?
                GROUP BY r.id, r.name, r.phone, r.email
                ORDER BY order_count DESC, r.name ASC
            ";

            return $this->query($sql, [$customerId]);

        } catch (\Exception $e) {
            error_log("Error getting customer restaurants: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get customer's recent orders (for compose message)
     */
    public function getCustomerOrders(int $customerId, int $limit = 10): array
    {
        try {
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.status,
                    o.created_at,
                    r.name as restaurant_name,
                    r.id as restaurant_id
                FROM orders o
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.customer_id = ?
                ORDER BY o.created_at DESC
                LIMIT ?
            ";

            return $this->query($sql, [$customerId, $limit]);

        } catch (\Exception $e) {
            error_log("Error getting customer orders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get customers who have ordered from a specific restaurant (for vendor messaging)
     */
    public function getRestaurantCustomers(int $restaurantId): array
    {
        try {
            $sql = "
                SELECT DISTINCT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    COUNT(o.id) as order_count,
                    MAX(o.created_at) as last_order_at
                FROM users u
                INNER JOIN orders o ON u.id = o.customer_id
                WHERE o.restaurant_id = ? AND u.role = 'customer'
                GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone
                ORDER BY last_order_at DESC, order_count DESC
            ";

            return $this->query($sql, [$restaurantId]);

        } catch (\Exception $e) {
            error_log("Error getting restaurant customers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent orders from a restaurant (for vendor messaging)
     */
    public function getRestaurantOrders(int $restaurantId, int $limit = 10): array
    {
        try {
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.status,
                    o.created_at,
                    o.total_amount,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                WHERE o.restaurant_id = ?
                ORDER BY o.created_at DESC
                LIMIT ?
            ";

            return $this->query($sql, [$restaurantId, $limit]);

        } catch (\Exception $e) {
            error_log("Error getting restaurant orders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get vendor ID from restaurant
     */
    public function getVendorIdFromRestaurant(int $restaurantId): ?int
    {
        try {
            $result = $this->query(
                "SELECT user_id FROM restaurants WHERE id = ?",
                [$restaurantId]
            );
            $result = $result[0] ?? null;

            return $result ? (int)$result['user_id'] : null;

        } catch (\Exception $e) {
            error_log("Error getting vendor ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get admin user ID for support messages
     */
    public function getSupportUserId(): ?int
    {
        try {
            $result = $this->query(
                "SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1"
            );
            $result = $result[0] ?? null;

            return $result ? (int)$result['id'] : null;

        } catch (\Exception $e) {
            error_log("Error getting support user ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get riders who have delivered for a specific restaurant
     */
    public function getRestaurantRiders(int $restaurantId): array
    {
        try {
            $sql = "
                SELECT DISTINCT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    COUNT(ra.id) as delivery_count,
                    MAX(ra.assigned_at) as last_delivery_at,
                    CONCAT(u.first_name, ' ', u.last_name) as full_name
                FROM users u
                INNER JOIN rider_assignments ra ON u.id = ra.rider_id
                INNER JOIN orders o ON ra.order_id = o.id
                WHERE o.restaurant_id = ? AND u.role = 'rider'
                GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone
                ORDER BY last_delivery_at DESC, delivery_count DESC
            ";

            return $this->query($sql, [$restaurantId]);

        } catch (\Exception $e) {
            error_log("Error getting restaurant riders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get orders with assigned riders for a specific restaurant
     */
    public function getRestaurantOrdersWithRiders(int $restaurantId): array
    {
        try {
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.status,
                    o.created_at,
                    o.total_amount,
                    ra.rider_id,
                    u.first_name,
                    u.last_name,
                    u.phone,
                    CONCAT(u.first_name, ' ', u.last_name) as rider_name,
                    ra.status as assignment_status,
                    ra.assigned_at
                FROM orders o
                INNER JOIN rider_assignments ra ON o.id = ra.order_id
                INNER JOIN users u ON ra.rider_id = u.id
                WHERE o.restaurant_id = ? 
                AND o.status IN ('preparing', 'out_for_delivery', 'delivered')
                AND ra.status IN ('accepted', 'picked_up', 'delivered')
                ORDER BY o.created_at DESC
            ";

            return $this->query($sql, [$restaurantId]);

        } catch (\Exception $e) {
            error_log("Error getting restaurant orders with riders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riders assigned to a specific order
     */
    public function getOrderRiders(int $orderId): array
    {
        try {
            $sql = "
                SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    ra.status as assignment_status,
                    ra.assigned_at,
                    CONCAT(u.first_name, ' ', u.last_name) as full_name
                FROM rider_assignments ra
                INNER JOIN users u ON ra.rider_id = u.id
                WHERE ra.order_id = ? AND u.role = 'rider'
                ORDER BY ra.assigned_at DESC
            ";

            return $this->query($sql, [$orderId]);

        } catch (\Exception $e) {
            error_log("Error getting order riders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riders who have delivered for a specific customer
     */
    public function getCustomerRiders(int $customerId): array
    {
        try {
            $sql = "
                SELECT DISTINCT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    COUNT(ra.id) as delivery_count,
                    MAX(ra.assigned_at) as last_delivery_at,
                    CONCAT(u.first_name, ' ', u.last_name) as full_name
                FROM users u
                INNER JOIN rider_assignments ra ON u.id = ra.rider_id
                INNER JOIN orders o ON ra.order_id = o.id
                WHERE o.customer_id = ? AND u.role = 'rider'
                GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone
                ORDER BY last_delivery_at DESC, delivery_count DESC
            ";

            return $this->query($sql, [$customerId]);

        } catch (\Exception $e) {
            error_log("Error getting customer riders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get customer's orders with assigned riders
     */
    public function getCustomerOrdersWithRiders(int $customerId): array
    {
        try {
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.status,
                    o.created_at,
                    o.total_amount,
                    ra.rider_id,
                    u.first_name,
                    u.last_name,
                    u.phone,
                    CONCAT(u.first_name, ' ', u.last_name) as rider_name,
                    ra.status as assignment_status,
                    ra.assigned_at,
                    r.name as restaurant_name,
                    r.id as restaurant_id
                FROM orders o
                INNER JOIN rider_assignments ra ON o.id = ra.order_id
                INNER JOIN users u ON ra.rider_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.customer_id = ? 
                AND o.status IN ('preparing', 'out_for_delivery', 'delivered')
                AND ra.status IN ('accepted', 'picked_up', 'delivered')
                ORDER BY o.created_at DESC
            ";

            return $this->query($sql, [$customerId]);

        } catch (\Exception $e) {
            error_log("Error getting customer orders with riders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get customer's current order riders (active deliveries)
     */
    public function getCustomerCurrentRiders(int $customerId): array
    {
        try {
            $sql = "
                SELECT 
                    o.id as order_id,
                    o.order_number,
                    o.status as order_status,
                    ra.rider_id,
                    u.first_name,
                    u.last_name,
                    u.phone,
                    CONCAT(u.first_name, ' ', u.last_name) as rider_name,
                    ra.status as assignment_status,
                    ra.assigned_at,
                    r.name as restaurant_name
                FROM orders o
                INNER JOIN rider_assignments ra ON o.id = ra.order_id
                INNER JOIN users u ON ra.rider_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.customer_id = ? 
                AND o.status IN ('preparing', 'out_for_delivery')
                AND ra.status IN ('accepted', 'picked_up')
                ORDER BY o.created_at DESC
            ";

            return $this->query($sql, [$customerId]);

        } catch (\Exception $e) {
            error_log("Error getting customer current riders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark all messages as read for a user
     */
    public function markAllMessagesAsRead(int $userId): bool
    {
        try {
            $sql = "UPDATE {$this->table}
                    SET is_read = 1, read_at = NOW()
                    WHERE recipient_id = ? AND is_read = 0";

            return $this->query($sql, [$userId]);

        } catch (\Exception $e) {
            error_log("Error marking all messages as read: " . $e->getMessage());
            return false;
        }
    }
}
