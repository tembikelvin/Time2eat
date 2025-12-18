<?php

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Message.php';

use core\BaseController;
use models\Message;

class NotificationController extends BaseController
{
    private $messageModel;

    public function __construct()
    {
        parent::__construct();
        $this->messageModel = new Message();
    }

    /**
     * Get unread message count for current user
     */
    public function getUnreadCount(): void
    {
        header('Content-Type: application/json');
        
        try {
            $this->requireAuth();
            $user = $this->getCurrentUser();
            
            $stats = $this->messageModel->getMessageStats($user->id);
            
            $this->jsonResponse([
                'success' => true,
                'count' => $stats['unread'] ?? 0,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get unread count',
                'count' => 0
            ]);
        }
    }

    /**
     * Subscribe to push notifications
     */
    public function subscribe(): void
    {
        header('Content-Type: application/json');
        
        try {
            $this->requireAuth();
            $user = $this->getCurrentUser();
            
            $input = json_decode(file_get_contents('php://input'), true);
            $subscription = $input['subscription'] ?? null;
            
            if (!$subscription) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Subscription data required'
                ]);
                return;
            }

            // Store subscription in database
            $this->storeSubscription($user->id, $subscription);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Subscription saved successfully'
            ]);
            
        } catch (\Exception $e) {
            error_log("Notification subscription error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to save subscription'
            ]);
        }
    }

    /**
     * Get VAPID public key
     */
    public function getVapidKey(): void
    {
        header('Content-Type: application/json');
        
        try {
            // Load VAPID configuration
            $vapidConfig = require __DIR__ . '/../../config/vapid.php';
            
            // Get environment (development/production)
            $environment = $_ENV['APP_ENV'] ?? 'development';
            
            // Use environment-specific key if available, otherwise fallback to default
            $publicKey = $vapidConfig['environment'][$environment]['public_key'] ?? $vapidConfig['public_key'];
            
            $this->jsonResponse([
                'success' => true,
                'publicKey' => $publicKey,
                'subject' => $vapidConfig['environment'][$environment]['subject'] ?? $vapidConfig['subject']
            ]);
            
        } catch (\Exception $e) {
            error_log("VAPID key error: " . $e->getMessage());
            
            // Fallback to hardcoded key for development
            $this->jsonResponse([
                'success' => true,
                'publicKey' => 'BEl62iUYgUivxIkv69yViEuiBIa40HI0DLLuxazjqAKFxgf-UMxRzKlK9pADNPj8fPYQ',
                'subject' => 'mailto:admin@time2eat.com'
            ]);
        }
    }

    /**
     * Get recent notifications for current user
     */
    public function getRecentNotifications(): void
    {
        header('Content-Type: application/json');

        try {
            $this->requireAuth();
            $user = $this->getCurrentUser();

            // Get recent notifications from multiple sources
            $notifications = [];
            $unreadCount = 0;

            // Get popup notifications
            $popupNotifications = $this->fetchAll("
                SELECT
                    id,
                    title,
                    message,
                    type,
                    priority,
                    created_at,
                    is_read,
                    action_url
                FROM popup_notifications
                WHERE (target_user_id = ? OR target_user_id IS NULL)
                AND is_dismissed = 0
                AND (expires_at IS NULL OR expires_at > NOW())
                AND deleted_at IS NULL
                ORDER BY priority DESC, created_at DESC
                LIMIT 10
            ", [$user->id]);

            foreach ($popupNotifications as $notification) {
                $notifications[] = [
                    'id' => $notification['id'],
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'type' => $notification['type'],
                    'created_at' => $notification['created_at'],
                    'is_read' => (bool)$notification['is_read'],
                    'action_url' => $notification['action_url']
                ];

                if (!$notification['is_read']) {
                    $unreadCount++;
                }
            }

            // Get unread message count
            $messageStats = $this->messageModel->getMessageStats($user->id);
            $unreadMessages = $messageStats['unread'] ?? 0;

            if ($unreadMessages > 0) {
                $notifications[] = [
                    'id' => 'messages',
                    'title' => 'New Messages',
                    'message' => "You have {$unreadMessages} unread message" . ($unreadMessages > 1 ? 's' : ''),
                    'type' => 'message',
                    'created_at' => date('Y-m-d H:i:s'),
                    'is_read' => false,
                    'action_url' => "/{$user->role}/messages"
                ];
                $unreadCount += $unreadMessages;
            }

            // Sort by created_at desc
            usort($notifications, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            $this->jsonResponse([
                'success' => true,
                'notifications' => array_slice($notifications, 0, 10),
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            error_log("Error getting recent notifications: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get notifications',
                'notifications' => [],
                'unread_count' => 0
            ]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(): void
    {
        header('Content-Type: application/json');

        try {
            $this->requireAuth();
            $user = $this->getCurrentUser();

            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $notificationId = $input['notification_id'] ?? null;

            if (!$notificationId) {
                $this->jsonResponse(['success' => false, 'message' => 'Notification ID required'], 400);
                return;
            }

            // Update notification as read
            $result = $this->update('popup_notifications',
                ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
                ['id' => $notificationId, 'target_user_id' => $user->id]
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ]);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(): void
    {
        header('Content-Type: application/json');

        try {
            $this->requireAuth();
            $user = $this->getCurrentUser();

            // Update all unread notifications for this user
            $this->query("
                UPDATE popup_notifications
                SET is_read = 1, read_at = NOW()
                WHERE (target_user_id = ? OR target_user_id IS NULL)
                AND is_read = 0
            ", [$user->id]);

            // Mark all messages as read
            $this->messageModel->markAllMessagesAsRead($user->id);

            $this->jsonResponse([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ]);
        }
    }

    private function storeSubscription(int $userId, array $subscription): void
    {
        $sql = "
            INSERT INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key, created_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            p256dh_key = VALUES(p256dh_key),
            auth_key = VALUES(auth_key),
            updated_at = NOW()
        ";

        $keys = $subscription['keys'] ?? [];

        $this->query($sql, [
            $userId,
            $subscription['endpoint'] ?? '',
            $keys['p256dh'] ?? '',
            $keys['auth'] ?? ''
        ]);
    }
}
