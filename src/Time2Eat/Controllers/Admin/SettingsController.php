<?php

namespace Time2Eat\Controllers\Admin;

use core\Controller;

class SettingsController extends Controller
{
    /**
     * Display settings management page
     */
    public function index()
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        if (!$this->user || $this->user['role'] !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        try {
            // Get all settings grouped by category (excluding payment settings)
            $stmt = $this->db->prepare("
                SELECT `key`, `value`, `type`, `group`, `description`, `is_public`
                FROM site_settings
                WHERE `group` != 'payment'
                ORDER BY `group`, `key`
            ");
            $stmt->execute();
            $allSettings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Group settings by category
            $settings = [];
            foreach ($allSettings as $setting) {
                $settings[$setting['group']][$setting['key']] = $setting;
            }

            // Convert user array for view compatibility
            $userData = [
                'id' => $this->user['id'],
                'email' => $this->user['email'],
                'first_name' => $this->user['first_name'] ?? '',
                'last_name' => $this->user['last_name'] ?? '',
                'role' => $this->user['role'],
                'status' => $this->user['status']
            ];

            $this->renderDashboard('admin/tools/settings', [
                'title' => 'Site Settings - Time2Eat Admin',
                'user' => $userData,
                'settings' => $settings,
                'currentPage' => 'settings'
            ]);

        } catch (\Exception $e) {
            error_log("Error loading settings: " . $e->getMessage());
            $this->redirect(url('/admin/dashboard'));
        }
    }

    /**
     * Save settings (alias for update method to match JavaScript calls)
     */
    public function save()
    {
        return $this->update();
    }

    /**
     * Update settings via API
     */
    public function update()
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if (!$this->user || $this->user['role'] !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        // Get input data
        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        if (empty($input)) {
            return $this->json(['error' => 'No data provided'], 400);
        }

        try {
            $updatedCount = 0;
            $errors = [];

            foreach ($input as $key => $value) {
                // Validate setting exists
                $stmt = $this->db->prepare("SELECT `key`, `type` FROM site_settings WHERE `key` = ?");
                $stmt->execute([$key]);
                $setting = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$setting) {
                    $errors[] = "Setting '$key' not found";
                    continue;
                }

                // Validate and convert value based on type
                $convertedValue = $this->convertSettingValue($value, $setting['type']);
                
                if ($convertedValue === false) {
                    $errors[] = "Invalid value for setting '$key'";
                    continue;
                }

                // Update setting
                $stmt = $this->db->prepare("
                    UPDATE site_settings
                    SET `value` = ?, `updated_at` = NOW()
                    WHERE `key` = ?
                ");
                $stmt->execute([$convertedValue, $key]);

                $updatedCount++;
            }

            if (!empty($errors)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Some settings could not be updated',
                    'errors' => $errors,
                    'updated_count' => $updatedCount
                ], 400);
            }

            return $this->json([
                'success' => true,
                'message' => "Successfully updated $updatedCount settings",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            error_log("Error updating settings: " . $e->getMessage());
            return $this->json(['error' => 'Failed to update settings'], 500);
        }
    }

    /**
     * Get settings by group via API
     */
    public function getByGroup($group)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if (!$this->user || $this->user['role'] !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        try {
            $stmt = $this->db->prepare("
                SELECT `key`, `value`, `type`, `description`, `is_public`
                FROM site_settings
                WHERE `group` = ?
                ORDER BY `key`
            ");
            $stmt->execute([$group]);
            $settings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->json([
                'success' => true,
                'data' => $settings
            ]);

        } catch (\Exception $e) {
            error_log("Error fetching settings: " . $e->getMessage());
            return $this->json(['error' => 'Failed to fetch settings'], 500);
        }
    }

    /**
     * Reset settings to default values
     */
    public function reset()
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if (!$this->user || $this->user['role'] !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        $group = $input['group'] ?? '';

        if (empty($group)) {
            return $this->json(['error' => 'Group parameter is required'], 400);
        }

        try {
            // Define default values for different groups
            $defaults = $this->getDefaultSettings();

            if (!isset($defaults[$group])) {
                return $this->json(['error' => 'Invalid group'], 400);
            }

            $resetCount = 0;
            foreach ($defaults[$group] as $key => $defaultValue) {
                $stmt = $this->db->prepare("
                    UPDATE site_settings
                    SET `value` = ?, `updated_at` = NOW()
                    WHERE `key` = ? AND `group` = ?
                ");
                $stmt->execute([$defaultValue, $key, $group]);
                $resetCount++;
            }

            return $this->json([
                'success' => true,
                'message' => "Reset $resetCount settings in '$group' group to defaults"
            ]);

        } catch (\Exception $e) {
            error_log("Error resetting settings: " . $e->getMessage());
            return $this->json(['error' => 'Failed to reset settings'], 500);
        }
    }

    /**
     * Reset settings group to default values (URL parameter version)
     */
    public function resetGroup($group)
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if (!$this->user || $this->user['role'] !== 'admin') {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        if (empty($group)) {
            return $this->json(['error' => 'Group parameter is required'], 400);
        }

        try {
            // Define default values for different groups
            $defaults = $this->getDefaultSettings();

            if (!isset($defaults[$group])) {
                return $this->json(['error' => 'Invalid group'], 400);
            }

            $resetCount = 0;
            foreach ($defaults[$group] as $key => $defaultValue) {
                $stmt = $this->db->prepare("
                    UPDATE site_settings
                    SET `value` = ?, `updated_at` = NOW()
                    WHERE `key` = ? AND `group` = ?
                ");
                $stmt->execute([$defaultValue, $key, $group]);
                $resetCount++;
            }

            return $this->json([
                'success' => true,
                'message' => "Reset $resetCount settings in '$group' group to defaults"
            ]);

        } catch (\Exception $e) {
            error_log("Error resetting settings: " . $e->getMessage());
            return $this->json(['error' => 'Failed to reset settings'], 500);
        }
    }

    /**
     * Convert setting value based on type
     */
    private function convertSettingValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value === 'true' || $value === '1' || $value === 1 ? 'true' : 'false';
            case 'integer':
                return is_numeric($value) ? (string)intval($value) : false;
            case 'float':
                return is_numeric($value) ? (string)floatval($value) : false;
            case 'json':
                $decoded = json_decode($value, true);
                return $decoded !== null ? json_encode($decoded) : false;
            case 'string':
            case 'text':
            default:
                return (string)$value;
        }
    }

    /**
     * Get default settings values
     */
    private function getDefaultSettings()
    {
        return [
            'general' => [
                'site_name' => 'Time2Eat',
                'site_description' => 'Bamenda Food Delivery Platform',
                'timezone' => 'Africa/Douala'
            ],
            'contact' => [
                'contact_email' => 'info@time2eat.cm',
                'contact_phone' => '+237 6XX XXX XXX',
                'contact_address' => 'Bamenda, North West Region, Cameroon',
                'contact_hours' => 'Mon-Sat: 8AM-10PM, Sun: 10AM-8PM',
                'support_email' => 'support@time2eat.cm',
                'emergency_contact' => '+237 6XX XXX XXX'
            ],
            'social' => [
                'facebook_url' => '',
                'twitter_url' => '',
                'instagram_url' => '',
                'youtube_url' => '',
                'linkedin_url' => '',
                'tiktok_url' => '',
                'whatsapp_number' => ''
            ],
            'business' => [
                'delivery_fee' => '500',
                'free_delivery_threshold' => '5000',
                'commission_rate' => '0.15',
                'tax_rate' => '0.1925',
                'currency' => 'XAF',
                'max_delivery_distance' => '15'
            ],
            'seo' => [
                'meta_keywords' => 'food delivery, Bamenda, restaurants, online ordering',
                'google_analytics_id' => '',
                'facebook_pixel_id' => ''
            ],
            'auth' => [
                'allow_registration' => 'true',
                'email_verification_required' => 'true'
            ],
            'system' => [
                'maintenance_mode' => 'false'
            ],
            'maps' => [
                'google_maps_api_key' => '',
                'mapbox_access_token' => '',
                'default_latitude' => '5.9631',
                'default_longitude' => '10.1591',
                'default_zoom_level' => '13',
                'enable_location_tracking' => 'true'
            ],
            'payment' => [
                'payment_methods_enabled' => '["cash","mobile_money","orange_money","mtn_momo"]',
                'cash_on_delivery_enabled' => 'true',
                'online_payment_enabled' => 'true',
                'stripe_publishable_key' => '',
                'stripe_secret_key' => '',
                'paypal_client_id' => '',
                'paypal_client_secret' => '',
                'paypal_sandbox_mode' => 'true',
                'orange_money_merchant_id' => '',
                'orange_money_api_key' => '',
                'mtn_momo_api_key' => '',
                'mtn_momo_user_id' => '',
                'payment_processing_fee' => '0.025',
                'minimum_order_amount' => '1000'
            ],
            'currency' => [
                'primary_currency' => 'XAF',
                'currency_symbol' => 'FCFA',
                'currency_position' => 'after',
                'decimal_places' => '0',
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'exchange_rate_usd' => '600',
                'auto_update_exchange_rates' => 'false'
            ]
        ];
    }

    /**
     * Render dashboard with proper layout
     */
    /**
     * Update email verification settings
     */
    public function updateEmailVerification()
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$this->user || $this->user['role'] !== 'admin') {
            return $this->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        // Get input data
        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        if (empty($input)) {
            return $this->json(['success' => false, 'message' => 'No data provided'], 400);
        }

        try {
            $updatedCount = 0;

            // Update email_verification_required
            if (isset($input['email_verification_required'])) {
                $value = $input['email_verification_required'] ? '1' : '0';
                $stmt = $this->db->prepare("
                    UPDATE site_settings
                    SET `value` = ?, `updated_at` = NOW()
                    WHERE `key` = 'email_verification_required'
                ");
                if ($stmt->execute([$value])) {
                    $updatedCount++;
                }
            }

            // Update registration_enabled
            if (isset($input['registration_enabled'])) {
                $value = $input['registration_enabled'] ? '1' : '0';
                $stmt = $this->db->prepare("
                    UPDATE site_settings
                    SET `value` = ?, `updated_at` = NOW()
                    WHERE `key` = 'registration_enabled'
                ");
                if ($stmt->execute([$value])) {
                    $updatedCount++;
                }
            }

            // Update auto_approve_customers
            if (isset($input['auto_approve_customers'])) {
                $value = $input['auto_approve_customers'] ? '1' : '0';
                $stmt = $this->db->prepare("
                    UPDATE site_settings
                    SET `value` = ?, `updated_at` = NOW()
                    WHERE `key` = 'auto_approve_customers'
                ");
                if ($stmt->execute([$value])) {
                    $updatedCount++;
                }
            }

            return $this->json([
                'success' => true,
                'message' => "Successfully updated $updatedCount settings"
            ]);

        } catch (\Exception $e) {
            error_log("Error updating email verification settings: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to update settings'], 500);
        }
    }

    private function renderDashboard(string $view, array $data = []): void
    {
        // Start output buffering to capture the dashboard content
        ob_start();

        // Extract data for the view
        extract($data);

        // Include the specific dashboard view using correct relative path
        $viewPath = __DIR__ . "/../../../views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("Dashboard view not found: {$view}");
        }
        include $viewPath;

        // Get the content
        $content = ob_get_clean();

        // Render with dashboard layout using correct relative path
        $layoutPath = __DIR__ . "/../../../views/components/dashboard-layout.php";
        if (!file_exists($layoutPath)) {
            throw new \Exception("Dashboard layout not found: dashboard-layout.php");
        }
        include $layoutPath;
    }
}
