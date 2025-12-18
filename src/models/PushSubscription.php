<?php

declare(strict_types=1);

namespace Time2Eat\Models;

use core\Model;

/**
 * Push Subscription Model
 * Manages PWA push notification subscriptions
 */
class PushSubscription extends Model
{
    protected $table = 'push_subscriptions';

    /**
     * Create a new push subscription
     */
    public function createSubscription(
        int $userId,
        string $endpoint,
        ?string $p256dhKey,
        ?string $authKey,
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): ?int {
        // Check if subscription already exists
        $existing = $this->findExistingSubscription($userId, $endpoint);
        if ($existing) {
            // Update existing subscription
            return $this->updateSubscription($existing['id'], $p256dhKey, $authKey, $userAgent, $ipAddress);
        }

        $data = [
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'p256dh_key' => $p256dhKey,
            'auth_key' => $authKey,
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($data);
    }

    /**
     * Find existing subscription
     */
    public function findExistingSubscription(int $userId, string $endpoint): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND endpoint = ? 
                LIMIT 1";
        
        return $this->fetchOne($sql, [$userId, $endpoint]);
    }

    /**
     * Update existing subscription
     */
    public function updateSubscription(
        int $subscriptionId,
        ?string $p256dhKey,
        ?string $authKey,
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): int {
        $data = [
            'p256dh_key' => $p256dhKey,
            'auth_key' => $authKey,
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->update($subscriptionId, $data);
        return $subscriptionId;
    }

    /**
     * Remove subscription
     */
    public function removeSubscription(int $userId, string $endpoint): bool
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = ? AND endpoint = ?";
        
        return $this->execute($sql, [$userId, $endpoint]) > 0;
    }

    /**
     * Get user's active subscriptions
     */
    public function getUserSubscriptions(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND is_active = 1 
                ORDER BY created_at DESC";
        
        return $this->fetchAll($sql, [$userId]);
    }

    /**
     * Get all active subscriptions
     */
    public function getAllActiveSubscriptions(): array
    {
        $sql = "SELECT ps.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} ps
                JOIN users u ON ps.user_id = u.id
                WHERE ps.is_active = 1 AND u.status = 'active'
                ORDER BY ps.created_at DESC";
        
        return $this->fetchAll($sql);
    }

    /**
     * Get subscriptions by user role
     */
    public function getSubscriptionsByRole(string $role): array
    {
        $sql = "SELECT ps.*, u.first_name, u.last_name, u.email, u.role 
                FROM {$this->table} ps
                JOIN users u ON ps.user_id = u.id
                WHERE ps.is_active = 1 AND u.status = 'active' AND u.role = ?
                ORDER BY ps.created_at DESC";
        
        return $this->fetchAll($sql, [$role]);
    }

    /**
     * Mark subscription as inactive
     */
    public function markAsInactive(int $subscriptionId): bool
    {
        return $this->update($subscriptionId, [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed(int $subscriptionId): bool
    {
        return $this->update($subscriptionId, [
            'last_used_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Clean up old inactive subscriptions
     */
    public function cleanupOldSubscriptions(int $daysOld = 30): int
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE is_active = 0 
                AND updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        return $this->execute($sql, [$daysOld]);
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_subscriptions,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_subscriptions,
                    COUNT(CASE WHEN is_active = 0 THEN 1 END) as inactive_subscriptions,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_this_week,
                    COUNT(CASE WHEN last_used_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as used_this_week
                FROM {$this->table}";
        
        return $this->fetchOne($sql) ?: [];
    }

    /**
     * Get subscriptions by device type (based on user agent)
     */
    public function getSubscriptionsByDevice(): array
    {
        $sql = "SELECT 
                    CASE 
                        WHEN user_agent LIKE '%Mobile%' OR user_agent LIKE '%Android%' THEN 'Mobile'
                        WHEN user_agent LIKE '%iPad%' OR user_agent LIKE '%Tablet%' THEN 'Tablet'
                        WHEN user_agent LIKE '%iPhone%' THEN 'iPhone'
                        ELSE 'Desktop'
                    END as device_type,
                    COUNT(*) as count
                FROM {$this->table}
                WHERE is_active = 1 AND user_agent IS NOT NULL
                GROUP BY device_type
                ORDER BY count DESC";
        
        return $this->fetchAll($sql);
    }

    /**
     * Send notification to specific users
     */
    public function sendToUsers(array $userIds, array $notificationData): array
    {
        if (empty($userIds)) {
            return ['sent' => 0, 'failed' => 0];
        }

        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id IN ($placeholders) AND is_active = 1";
        
        $subscriptions = $this->fetchAll($sql, $userIds);
        
        $results = ['sent' => 0, 'failed' => 0];
        
        foreach ($subscriptions as $subscription) {
            // This would integrate with the actual push service
            // For now, just mark as sent
            $this->updateLastUsed($subscription['id']);
            $results['sent']++;
        }
        
        return $results;
    }

    /**
     * Send notification to all users with specific role
     */
    public function sendToRole(string $role, array $notificationData): array
    {
        $subscriptions = $this->getSubscriptionsByRole($role);
        
        $results = ['sent' => 0, 'failed' => 0];
        
        foreach ($subscriptions as $subscription) {
            // This would integrate with the actual push service
            // For now, just mark as sent
            $this->updateLastUsed($subscription['id']);
            $results['sent']++;
        }
        
        return $results;
    }

    /**
     * Get recent notification activity
     */
    public function getRecentActivity(int $limit = 50): array
    {
        $sql = "SELECT ps.*, u.first_name, u.last_name, u.email, u.role
                FROM {$this->table} ps
                JOIN users u ON ps.user_id = u.id
                WHERE ps.last_used_at IS NOT NULL
                ORDER BY ps.last_used_at DESC
                LIMIT ?";
        
        return $this->fetchAll($sql, [$limit]);
    }

    /**
     * Validate subscription endpoint
     */
    public function validateEndpoint(string $endpoint): bool
    {
        // Basic validation for push service endpoints
        $validDomains = [
            'fcm.googleapis.com',
            'updates.push.services.mozilla.com',
            'wns2-par02p.notify.windows.com',
            'notify.bugsnag.com'
        ];

        $parsedUrl = parse_url($endpoint);
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return false;
        }

        foreach ($validDomains as $domain) {
            if (strpos($parsedUrl['host'], $domain) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get subscription health metrics
     */
    public function getHealthMetrics(): array
    {
        $sql = "SELECT 
                    AVG(CASE WHEN last_used_at IS NOT NULL THEN 1 ELSE 0 END) * 100 as usage_rate,
                    AVG(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) * 100 as active_rate,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 END) as new_today,
                    COUNT(CASE WHEN last_used_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 END) as used_today
                FROM {$this->table}";
        
        return $this->fetchOne($sql) ?: [];
    }

    /**
     * Get active subscriptions for a user
     */
    public function getActiveSubscriptions(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = ? AND is_active = 1
                ORDER BY created_at DESC";

        return $this->fetchAll($sql, [$userId]);
    }
}
