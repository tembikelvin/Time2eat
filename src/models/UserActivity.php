<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

/**
 * User Activity Tracking Model
 * Tracks user activities and behavior across the platform
 */
class UserActivity
{
    use DatabaseTrait;

    protected ?\PDO $db = null;
    protected $table = 'user_activities';

    /**
     * Log user activity
     */
    public function logActivity(array $data): bool
    {
        $activityData = [
            'user_id' => $data['user_id'],
            'activity_type' => $data['activity_type'],
            'activity_description' => $data['activity_description'] ?? '',
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->insertRecord($this->table, $activityData) > 0;
    }

    /**
     * Get user activities with pagination
     */
    public function getUserActivities(int $userId, int $limit = 50, int $offset = 0): array
    {
        $sql = "
            SELECT 
                ua.*,
                u.first_name,
                u.last_name,
                u.email
            FROM {$this->table} ua
            JOIN users u ON ua.user_id = u.id
            WHERE ua.user_id = ?
            ORDER BY ua.created_at DESC
            LIMIT ? OFFSET ?
        ";

        return $this->fetchAll($sql, [$userId, $limit, $offset]);
    }

    /**
     * Get all activities with user details
     */
    public function getAllActivitiesWithUsers(string $activityType = '', int $limit = 100, int $offset = 0): array
    {
        $whereClause = '';
        $params = [];

        if (!empty($activityType)) {
            $whereClause = "WHERE ua.activity_type = ?";
            $params[] = $activityType;
        }

        $sql = "
            SELECT 
                ua.*,
                u.first_name,
                u.last_name,
                u.email,
                u.role
            FROM {$this->table} ua
            JOIN users u ON ua.user_id = u.id
            {$whereClause}
            ORDER BY ua.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        return $this->fetchAll($sql, $params);
    }

    /**
     * Get user activity statistics
     */
    public function getUserActivityStats(int $userId): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_activities,
                COUNT(DISTINCT activity_type) as unique_activity_types,
                COUNT(DISTINCT DATE(created_at)) as active_days,
                MAX(created_at) as last_activity,
                MIN(created_at) as first_activity,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as activities_last_7_days,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as activities_last_30_days
            FROM {$this->table}
            WHERE user_id = ?
        ";

        $result = $this->fetchOne($sql, [$userId]);
        return $result ?: [
            'total_activities' => 0,
            'unique_activity_types' => 0,
            'active_days' => 0,
            'last_activity' => null,
            'first_activity' => null,
            'activities_last_7_days' => 0,
            'activities_last_30_days' => 0
        ];
    }

    /**
     * Get platform-wide activity statistics
     */
    public function getPlatformActivityStats(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_activities,
                COUNT(DISTINCT user_id) as active_users,
                COUNT(DISTINCT activity_type) as activity_types,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as activities_today,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as activities_this_week,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as activities_this_month
            FROM {$this->table}
        ";

        $result = $this->fetchOne($sql);
        return $result ?: [
            'total_activities' => 0,
            'active_users' => 0,
            'activity_types' => 0,
            'activities_today' => 0,
            'activities_this_week' => 0,
            'activities_this_month' => 0
        ];
    }

    /**
     * Get activity breakdown by type
     */
    public function getActivityBreakdown(int $days = 30): array
    {
        $sql = "
            SELECT 
                activity_type,
                COUNT(*) as count,
                COUNT(DISTINCT user_id) as unique_users
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY activity_type
            ORDER BY count DESC
        ";

        return $this->fetchAll($sql, [$days]);
    }

    /**
     * Get most active users
     */
    public function getMostActiveUsers(int $days = 30, int $limit = 10): array
    {
        $sql = "
            SELECT 
                ua.user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.role,
                COUNT(*) as activity_count,
                COUNT(DISTINCT ua.activity_type) as activity_types,
                MAX(ua.created_at) as last_activity
            FROM {$this->table} ua
            JOIN users u ON ua.user_id = u.id
            WHERE ua.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY ua.user_id, u.first_name, u.last_name, u.email, u.role
            ORDER BY activity_count DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$days, $limit]);
    }

    /**
     * Get user login history
     */
    public function getUserLoginHistory(int $userId, int $limit = 20): array
    {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE user_id = ? AND activity_type = 'login'
            ORDER BY created_at DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Clean old activities (for performance)
     */
    public function cleanOldActivities(int $daysToKeep = 90): int
    {
        $sql = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->query($sql, [$daysToKeep]);
        return $stmt->rowCount();
    }

    /**
     * Create user_activities table if it doesn't exist
     */
    public function createTableIfNotExists(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                activity_type VARCHAR(100) NOT NULL,
                activity_description TEXT,
                entity_type VARCHAR(100),
                entity_id BIGINT UNSIGNED,
                metadata JSON,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_activity_type (activity_type),
                INDEX idx_created_at (created_at),
                INDEX idx_entity (entity_type, entity_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $this->query($sql);
    }

    /**
     * Common activity types for easy reference
     */
    public static function getActivityTypes(): array
    {
        return [
            'login' => 'User Login',
            'logout' => 'User Logout',
            'profile_update' => 'Profile Updated',
            'password_change' => 'Password Changed',
            'order_placed' => 'Order Placed',
            'order_cancelled' => 'Order Cancelled',
            'payment_made' => 'Payment Made',
            'review_posted' => 'Review Posted',
            'message_sent' => 'Message Sent',
            'role_change_requested' => 'Role Change Requested',
            'restaurant_created' => 'Restaurant Created',
            'menu_updated' => 'Menu Updated',
            'delivery_accepted' => 'Delivery Accepted',
            'delivery_completed' => 'Delivery Completed',
            'dispute_created' => 'Dispute Created',
            'account_suspended' => 'Account Suspended',
            'account_activated' => 'Account Activated'
        ];
    }
}
