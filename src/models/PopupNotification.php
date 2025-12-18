<?php

declare(strict_types=1);

namespace Time2Eat\Models;

use core\Model;

/**
 * Popup Notification Model
 * Manages popup notifications for users and admin announcements
 */
class PopupNotification extends Model
{
    protected $table = 'popup_notifications';

    /**
     * Create a new popup notification
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO {$this->table} (
                    user_id, title, message, type, action_url, action_text, 
                    image, priority, expires_at, conditions, metadata, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['user_id'] ?? null,
            $data['title'],
            $data['message'],
            $data['type'],
            $data['action_url'] ?? null,
            $data['action_text'] ?? null,
            $data['image'] ?? null,
            $data['priority'],
            $data['expires_at'] ?? null,
            $data['conditions'] ?? null,
            $data['metadata'] ?? null,
            $data['created_by']
        ];

        return $this->execute($sql, $params) ? $this->db->lastInsertId() : null;
    }

    /**
     * Get active notifications for a user
     */
    public function getActiveNotificationsForUser(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (user_id = ? OR user_id IS NULL)
                AND is_dismissed = 0
                AND (expires_at IS NULL OR expires_at > NOW())
                AND deleted_at IS NULL
                ORDER BY priority DESC, created_at DESC";

        return $this->fetchAll($sql, [$userId]);
    }

    /**
     * Get all active notifications (admin view)
     */
    public function getActiveNotifications(): array
    {
        $sql = "SELECT pn.*, u.first_name, u.last_name, u.email as creator_email
                FROM {$this->table} pn
                LEFT JOIN users u ON pn.created_by = u.id
                WHERE pn.is_dismissed = 0
                AND (pn.expires_at IS NULL OR pn.expires_at > NOW())
                AND pn.deleted_at IS NULL
                ORDER BY pn.priority DESC, pn.created_at DESC";

        return $this->fetchAll($sql);
    }

    /**
     * Get scheduled notifications
     */
    public function getScheduledNotifications(): array
    {
        $sql = "SELECT pn.*, u.first_name, u.last_name, u.email as creator_email
                FROM {$this->table} pn
                LEFT JOIN users u ON pn.created_by = u.id
                WHERE pn.expires_at > NOW()
                AND pn.deleted_at IS NULL
                ORDER BY pn.expires_at ASC";

        return $this->fetchAll($sql);
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_notifications,
                    COUNT(CASE WHEN is_dismissed = 0 AND (expires_at IS NULL OR expires_at > NOW()) THEN 1 END) as active_notifications,
                    COUNT(CASE WHEN is_dismissed = 1 THEN 1 END) as dismissed_notifications,
                    COUNT(CASE WHEN expires_at IS NOT NULL AND expires_at <= NOW() THEN 1 END) as expired_notifications,
                    COUNT(CASE WHEN type = 'promotion' THEN 1 END) as promotional_notifications,
                    COUNT(CASE WHEN priority = 'urgent' THEN 1 END) as urgent_notifications,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_notifications
                FROM {$this->table}
                WHERE deleted_at IS NULL";

        return $this->fetchOne($sql) ?: [];
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, updated_at = NOW()
                WHERE id = ? AND (user_id = ? OR user_id IS NULL)";

        return $this->execute($sql, [$notificationId, $userId]) > 0;
    }

    /**
     * Dismiss notification
     */
    public function dismissNotification(int $notificationId, int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_dismissed = 1, updated_at = NOW()
                WHERE id = ? AND (user_id = ? OR user_id IS NULL)";

        return $this->execute($sql, [$notificationId, $userId]) > 0;
    }

    /**
     * Update notification
     */
    public function updateNotification(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $setParts = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = ?";
        
        return $this->execute($sql, $params) > 0;
    }

    /**
     * Delete notification (soft delete)
     */
    public function deleteNotification(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET deleted_at = NOW(), updated_at = NOW()
                WHERE id = ?";

        return $this->execute($sql, [$id]) > 0;
    }

    /**
     * Get notifications by type
     */
    public function getNotificationsByType(string $type, int $limit = 50): array
    {
        $sql = "SELECT pn.*, u.first_name, u.last_name
                FROM {$this->table} pn
                LEFT JOIN users u ON pn.created_by = u.id
                WHERE pn.type = ?
                AND pn.deleted_at IS NULL
                ORDER BY pn.created_at DESC
                LIMIT ?";

        return $this->fetchAll($sql, [$type, $limit]);
    }

    /**
     * Get notifications by priority
     */
    public function getNotificationsByPriority(string $priority, int $limit = 50): array
    {
        $sql = "SELECT pn.*, u.first_name, u.last_name
                FROM {$this->table} pn
                LEFT JOIN users u ON pn.created_by = u.id
                WHERE pn.priority = ?
                AND pn.deleted_at IS NULL
                ORDER BY pn.created_at DESC
                LIMIT ?";

        return $this->fetchAll($sql, [$priority, $limit]);
    }

    /**
     * Get expired notifications
     */
    public function getExpiredNotifications(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE expires_at IS NOT NULL 
                AND expires_at <= NOW()
                AND deleted_at IS NULL
                ORDER BY expires_at DESC";

        return $this->fetchAll($sql);
    }

    /**
     * Clean up expired notifications
     */
    public function cleanupExpiredNotifications(): int
    {
        $sql = "UPDATE {$this->table} 
                SET deleted_at = NOW(), updated_at = NOW()
                WHERE expires_at IS NOT NULL 
                AND expires_at <= DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND deleted_at IS NULL";

        return $this->execute($sql);
    }

    /**
     * Get notification engagement stats
     */
    public function getEngagementStats(int $notificationId): array
    {
        // This would require additional tracking tables in a full implementation
        // For now, return basic stats
        $sql = "SELECT 
                    COUNT(CASE WHEN is_read = 1 THEN 1 END) as read_count,
                    COUNT(CASE WHEN is_dismissed = 1 THEN 1 END) as dismissed_count,
                    COUNT(*) as total_delivered
                FROM {$this->table}
                WHERE id = ?";

        return $this->fetchOne($sql, [$notificationId]) ?: [];
    }

    /**
     * Create global notification for all users
     */
    public function createGlobalNotification(array $data): ?int
    {
        $data['user_id'] = null; // Global notification
        return $this->create($data);
    }

    /**
     * Create role-based notification
     */
    public function createRoleBasedNotification(array $data, string $role): ?int
    {
        $data['conditions'] = json_encode(['role' => $role]);
        $data['user_id'] = null; // Not user-specific
        return $this->create($data);
    }

    /**
     * Get notifications for specific role
     */
    public function getNotificationsForRole(string $role): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE (user_id IS NULL)
                AND (conditions IS NULL OR JSON_EXTRACT(conditions, '$.role') = ?)
                AND is_dismissed = 0
                AND (expires_at IS NULL OR expires_at > NOW())
                AND deleted_at IS NULL
                ORDER BY priority DESC, created_at DESC";

        return $this->fetchAll($sql, [$role]);
    }

    /**
     * Get notification by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT pn.*, u.first_name, u.last_name, u.email as creator_email
                FROM {$this->table} pn
                LEFT JOIN users u ON pn.created_by = u.id
                WHERE pn.id = ? AND pn.deleted_at IS NULL";

        return $this->fetchOne($sql, [$id]);
    }

    /**
     * Get recent notifications for admin dashboard
     */
    public function getRecentNotifications(int $limit = 10): array
    {
        $sql = "SELECT pn.*, u.first_name, u.last_name
                FROM {$this->table} pn
                LEFT JOIN users u ON pn.created_by = u.id
                WHERE pn.deleted_at IS NULL
                ORDER BY pn.created_at DESC
                LIMIT ?";

        return $this->fetchAll($sql, [$limit]);
    }

    /**
     * Count unread notifications for user
     */
    public function countUnreadForUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE (user_id = ? OR user_id IS NULL)
                AND is_read = 0
                AND is_dismissed = 0
                AND (expires_at IS NULL OR expires_at > NOW())
                AND deleted_at IS NULL";

        $result = $this->fetchOne($sql, [$userId]);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Bulk dismiss notifications
     */
    public function bulkDismiss(array $notificationIds, int $userId): bool
    {
        if (empty($notificationIds)) {
            return true;
        }

        $placeholders = str_repeat('?,', count($notificationIds) - 1) . '?';
        $params = array_merge($notificationIds, [$userId]);

        $sql = "UPDATE {$this->table} 
                SET is_dismissed = 1, updated_at = NOW()
                WHERE id IN ({$placeholders}) 
                AND (user_id = ? OR user_id IS NULL)";

        return $this->execute($sql, $params) > 0;
    }

    /**
     * Get notification delivery report
     */
    public function getDeliveryReport(string $period = '30days'): array
    {
        $dateCondition = $this->getDateCondition($period);

        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_sent,
                    COUNT(CASE WHEN is_read = 1 THEN 1 END) as total_read,
                    COUNT(CASE WHEN is_dismissed = 1 THEN 1 END) as total_dismissed,
                    AVG(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) * 100 as read_rate
                FROM {$this->table}
                WHERE deleted_at IS NULL {$dateCondition}
                GROUP BY DATE(created_at)
                ORDER BY date DESC";

        return $this->fetchAll($sql);
    }

    /**
     * Get date condition for queries
     */
    private function getDateCondition(string $period): string
    {
        switch ($period) {
            case 'today':
                return "AND DATE(created_at) = CURDATE()";
            case 'week':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '30days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return "";
        }
    }
}
