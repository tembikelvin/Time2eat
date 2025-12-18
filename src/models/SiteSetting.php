<?php

declare(strict_types=1);

namespace Time2Eat\Models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use core\Model;
use traits\DatabaseTrait;

/**
 * Site Setting Model
 * Manages application configuration and settings
 */
class SiteSetting extends Model
{
    use DatabaseTrait;

    protected $table = 'site_settings';

    /**
     * Execute a query and return affected rows
     */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Get a setting value by key
     */
    public function get(string $key, $default = null)
    {
        $sql = "SELECT value, type FROM {$this->table} WHERE `key` = ?";
        $result = $this->fetchOne($sql, [$key]);

        if (!$result) {
            return $default;
        }

        return $this->castValue($result['value'], $result['type']);
    }

    /**
     * Set a setting value
     */
    public function set(string $key, $value, string $type = 'string', string $group = 'general', string $description = null): bool
    {
        $sql = "INSERT INTO {$this->table} (`key`, `value`, `type`, `group`, `description`, `updated_at`) 
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                `value` = VALUES(`value`), 
                `type` = VALUES(`type`),
                `group` = VALUES(`group`),
                `description` = VALUES(`description`),
                `updated_at` = NOW()";

        $params = [$key, $this->prepareValue($value, $type), $type, $group, $description];
        return $this->execute($sql, $params) > 0;
    }

    /**
     * Update an existing setting
     */
    public function updateSetting(string $key, $value): bool
    {
        // First get the current type
        $current = $this->fetchOne("SELECT type FROM {$this->table} WHERE `key` = ?", [$key]);
        if (!$current) {
            return false;
        }

        $sql = "UPDATE {$this->table} 
                SET `value` = ?, `updated_at` = NOW() 
                WHERE `key` = ?";

        return $this->execute($sql, [$this->prepareValue($value, $current['type']), $key]) > 0;
    }

    /**
     * Delete a setting
     */
    public function deleteSetting(string $key): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE `key` = ?";
        return $this->execute($sql, [$key]) > 0;
    }

    /**
     * Get all settings
     */
    public function getAllSettings(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY `group`, `key`";
        $results = $this->fetchAll($sql);

        $settings = [];
        foreach ($results as $result) {
            $settings[$result['key']] = [
                'value' => $this->castValue($result['value'], $result['type']),
                'type' => $result['type'],
                'group' => $result['group'],
                'description' => $result['description'],
                'is_public' => (bool)$result['is_public'],
                'updated_at' => $result['updated_at']
            ];
        }

        return $settings;
    }

    /**
     * Get settings grouped by category
     */
    public function getAllSettingsGrouped(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY `group`, `key`";
        $results = $this->fetchAll($sql);

        $grouped = [];
        foreach ($results as $result) {
            $group = $result['group'] ?: 'general';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }

            $grouped[$group][$result['key']] = [
                'value' => $this->castValue($result['value'], $result['type']),
                'type' => $result['type'],
                'description' => $result['description'],
                'is_public' => (bool)$result['is_public'],
                'updated_at' => $result['updated_at']
            ];
        }

        return $grouped;
    }

    /**
     * Get settings by group
     */
    public function getByGroup(string $group): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE `group` = ? ORDER BY `key`";
        $results = $this->fetchAll($sql, [$group]);

        $settings = [];
        foreach ($results as $result) {
            $settings[$result['key']] = [
                'value' => $this->castValue($result['value'], $result['type']),
                'type' => $result['type'],
                'description' => $result['description'],
                'is_public' => (bool)$result['is_public'],
                'updated_at' => $result['updated_at']
            ];
        }

        return $settings;
    }

    /**
     * Get public settings (for frontend)
     */
    public function getPublicSettings(): array
    {
        $sql = "SELECT `key`, `value`, `type` FROM {$this->table} WHERE `is_public` = 1";
        $results = $this->fetchAll($sql);

        $settings = [];
        foreach ($results as $result) {
            $settings[$result['key']] = $this->castValue($result['value'], $result['type']);
        }

        return $settings;
    }

    /**
     * Get contact settings
     */
    public function getContactSettings(): array
    {
        return $this->getByGroup('contact');
    }

    /**
     * Get business settings
     */
    public function getBusinessSettings(): array
    {
        return $this->getByGroup('business');
    }

    /**
     * Get email settings
     */
    public function getEmailSettings(): array
    {
        return $this->getByGroup('email');
    }

    /**
     * Get payment settings
     */
    public function getPaymentSettings(): array
    {
        return $this->getByGroup('payment');
    }

    /**
     * Bulk update settings
     */
    public function bulkUpdate(array $settings): bool
    {
        $this->getDb()->beginTransaction();

        try {
            foreach ($settings as $key => $value) {
                if (!$this->updateSetting($key, $value)) {
                    throw new \Exception("Failed to update setting: {$key}");
                }
            }

            $this->getDb()->commit();
            return true;

        } catch (\Exception $e) {
            $this->getDb()->rollback();
            return false;
        }
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults(): bool
    {
        $defaults = [
            // General settings
            ['site_name', 'Time2Eat', 'string', 'general', 'Website name', true],
            ['site_description', 'Bamenda Food Delivery Platform', 'string', 'general', 'Website description', true],
            ['site_logo', '/images/logo.png', 'string', 'general', 'Site logo path', true],
            ['site_favicon', '/images/favicon.ico', 'string', 'general', 'Site favicon path', true],
            ['timezone', 'Africa/Douala', 'string', 'general', 'Default timezone', false],
            ['language', 'en', 'string', 'general', 'Default language', true],

            // Contact settings
            ['contact_email', 'info@time2eat.cm', 'string', 'contact', 'Contact email address', true],
            ['contact_phone', '+237 6XX XXX XXX', 'string', 'contact', 'Contact phone number', true],
            ['contact_address', 'Bamenda, North West Region, Cameroon', 'string', 'contact', 'Physical address', true],
            ['support_email', 'support@time2eat.cm', 'string', 'contact', 'Support email address', false],
            ['whatsapp_number', '+237 6XX XXX XXX', 'string', 'contact', 'WhatsApp support number', true],

            // Business settings
            ['delivery_fee', '500', 'integer', 'business', 'Default delivery fee in XAF', false],
            ['commission_rate', '0.15', 'float', 'business', 'Platform commission rate (15%)', false],
            ['currency', 'XAF', 'string', 'business', 'Default currency', true],
            ['currency_symbol', 'FCFA', 'string', 'business', 'Currency symbol', true],
            ['minimum_order', '2000', 'integer', 'business', 'Minimum order amount in XAF', true],
            ['max_delivery_distance', '15', 'integer', 'business', 'Maximum delivery distance in KM', false],
            ['order_timeout', '30', 'integer', 'business', 'Order timeout in minutes', false],

            // Email settings
            ['smtp_host', '', 'string', 'email', 'SMTP server host', false],
            ['smtp_port', '587', 'integer', 'email', 'SMTP server port', false],
            ['smtp_username', '', 'string', 'email', 'SMTP username', false],
            ['smtp_password', '', 'string', 'email', 'SMTP password', false],
            ['smtp_encryption', 'tls', 'string', 'email', 'SMTP encryption (tls/ssl)', false],
            ['mail_from_address', 'noreply@time2eat.cm', 'string', 'email', 'From email address', false],
            ['mail_from_name', 'Time2Eat', 'string', 'email', 'From name', false],

            // Payment settings
            ['payment_methods', '["cash","mobile_money","card"]', 'json', 'payment', 'Enabled payment methods', false],
            ['mobile_money_providers', '["mtn","orange"]', 'json', 'payment', 'Mobile money providers', false],
            ['stripe_public_key', '', 'string', 'payment', 'Stripe public key', false],
            ['stripe_secret_key', '', 'string', 'payment', 'Stripe secret key', false],

            // Social media
            ['facebook_url', '', 'string', 'social', 'Facebook page URL', true],
            ['twitter_url', '', 'string', 'social', 'Twitter profile URL', true],
            ['instagram_url', '', 'string', 'social', 'Instagram profile URL', true],
            ['linkedin_url', '', 'string', 'social', 'LinkedIn profile URL', true],

            // System settings
            ['maintenance_mode', '0', 'boolean', 'system', 'Maintenance mode enabled', false],
            ['registration_enabled', '1', 'boolean', 'system', 'User registration enabled', false],
            ['email_verification_required', '1', 'boolean', 'system', 'Email verification required', false],
            ['auto_backup_enabled', '1', 'boolean', 'system', 'Automatic backups enabled', false],
            ['backup_frequency', 'daily', 'string', 'system', 'Backup frequency', false],
            ['backup_retention_days', '30', 'integer', 'system', 'Backup retention in days', false],

            // Analytics
            ['google_analytics_id', '', 'string', 'analytics', 'Google Analytics tracking ID', false],
            ['facebook_pixel_id', '', 'string', 'analytics', 'Facebook Pixel ID', false],
        ];

        $this->getDb()->beginTransaction();

        try {
            $sql = "INSERT IGNORE INTO {$this->table} (`key`, `value`, `type`, `group`, `description`, `is_public`)
                    VALUES (?, ?, ?, ?, ?, ?)";

            foreach ($defaults as $setting) {
                $this->execute($sql, $setting);
            }

            $this->getDb()->commit();
            return true;

        } catch (\Exception $e) {
            $this->getDb()->rollback();
            return false;
        }
    }

    /**
     * Cast value to appropriate type
     */
    private function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool)$value;
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'json':
                return json_decode($value, true);
            case 'text':
            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Prepare value for storage
     */
    private function prepareValue($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            case 'integer':
            case 'float':
            case 'text':
            case 'string':
            default:
                return (string)$value;
        }
    }

    /**
     * Check if setting exists
     */
    public function exists(string $key): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE `key` = ?";
        $result = $this->fetchOne($sql, [$key]);
        return (int)($result['count'] ?? 0) > 0;
    }

    /**
     * Get setting with metadata
     */
    public function getWithMetadata(string $key): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE `key` = ?";
        $result = $this->fetchOne($sql, [$key]);

        if (!$result) {
            return null;
        }

        $result['value'] = $this->castValue($result['value'], $result['type']);
        $result['is_public'] = (bool)$result['is_public'];

        return $result;
    }

    /**
     * Search settings by key or description
     */
    public function searchSettings(string $query): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE `key` LIKE ? OR `description` LIKE ?
                ORDER BY `group`, `key`";

        $searchTerm = "%{$query}%";
        $results = $this->fetchAll($sql, [$searchTerm, $searchTerm]);

        $settings = [];
        foreach ($results as $result) {
            $settings[$result['key']] = [
                'value' => $this->castValue($result['value'], $result['type']),
                'type' => $result['type'],
                'group' => $result['group'],
                'description' => $result['description'],
                'is_public' => (bool)$result['is_public'],
                'updated_at' => $result['updated_at']
            ];
        }

        return $settings;
    }

    /**
     * Get settings count by group
     */
    public function getCountByGroup(): array
    {
        $sql = "SELECT `group`, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY `group`
                ORDER BY `group`";

        $results = $this->fetchAll($sql);
        $counts = [];

        foreach ($results as $result) {
            $counts[$result['group'] ?: 'general'] = (int)$result['count'];
        }

        return $counts;
    }

    /**
     * Export settings to array
     */
    public function exportSettings(): array
    {
        $sql = "SELECT `key`, `value`, `type`, `group`, `description`, `is_public` 
                FROM {$this->table} 
                ORDER BY `group`, `key`";

        return $this->fetchAll($sql);
    }

    /**
     * Import settings from array
     */
    public function importSettings(array $settings): bool
    {
        $this->getDb()->beginTransaction();

        try {
            $sql = "INSERT INTO {$this->table} (`key`, `value`, `type`, `group`, `description`, `is_public`)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    `value` = VALUES(`value`),
                    `type` = VALUES(`type`),
                    `group` = VALUES(`group`),
                    `description` = VALUES(`description`),
                    `is_public` = VALUES(`is_public`),
                    `updated_at` = NOW()";

            foreach ($settings as $setting) {
                $params = [
                    $setting['key'],
                    $setting['value'],
                    $setting['type'],
                    $setting['group'],
                    $setting['description'],
                    $setting['is_public']
                ];
                $this->execute($sql, $params);
            }

            $this->getDb()->commit();
            return true;

        } catch (\Exception $e) {
            $this->getDb()->rollback();
            return false;
        }
    }
}
