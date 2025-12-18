<?php
/**
 * Global Helper Functions for Time2Eat
 * Common utility functions used throughout the application
 */

/**
 * Generate URL for the application
 * Works in both development (WAMP) and production environments
 */
function url($path = '') {
    // Load environment helper if not already loaded
    if (!function_exists('environmentUrl')) {
        require_once __DIR__ . '/environment.php';
    }

    return environmentUrl($path);
}

// Legacy functions removed - now using environment-aware URL generation
// See src/helpers/environment.php for the new implementation

/**
 * Generate asset URL
 */
function asset($path) {
    return url('public/' . ltrim($path, '/'));
}

/**
 * Generate image URL with proper path handling
 * Handles both absolute URLs (http/https) and relative paths
 */
function imageUrl($path, $fallback = null) {
    // Return fallback if path is empty
    if (empty($path)) {
        return $fallback;
    }

    // If it's already an absolute URL, return as is
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }

    // If path starts with /public/, use it directly with url()
    if (strpos($path, '/public/') === 0) {
        return url($path);
    }

    // If path starts with /uploads/ or /images/, prepend with public/
    if (strpos($path, '/uploads/') === 0 || strpos($path, '/images/') === 0) {
        return url('public' . $path);
    }

    // Otherwise, assume it's a relative path and prepend public/
    return url('public/' . ltrim($path, '/'));
}

/**
 * Escape HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency (XAF)
 */
function formatCurrency($amount) {
    return number_format($amount, 0, '.', ',') . ' XAF';
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M j, Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Get system setting from database
 * Checks both site_settings and system_settings tables
 */
function getSystemSetting($key, $default = null) {
    static $settings = null;

    // Allow cache clearing
    if ($key === '__clear_cache__') {
        $settings = null;
        return null;
    }

    if ($settings === null) {
        try {
            // Get database connection (config already loaded)
            if (!function_exists('dbConnection')) {
                $configPath = __DIR__ . '/../../config/config.php';
                $dbPath = __DIR__ . '/../../config/database.php';

                if (file_exists($configPath)) {
                    require_once $configPath;
                }
                if (file_exists($dbPath)) {
                    require_once $dbPath;
                }
            }

            $db = dbConnection();
            $settings = [];

            // Load from site_settings table (primary)
            try {
                $stmt = $db->query("SELECT `key`, `value` FROM site_settings");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $settings[$row['key']] = $row['value'];
                }
            } catch (Exception $e) {
                // site_settings table might not exist
            }

            // Load from system_settings table (fallback/legacy)
            try {
                $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Only add if not already set from site_settings
                    if (!isset($settings[$row['setting_key']])) {
                        $settings[$row['setting_key']] = $row['setting_value'];
                    }
                }
            } catch (Exception $e) {
                // system_settings table might not exist
            }
        } catch (Exception $e) {
            error_log('Failed to load system settings: ' . $e->getMessage());
            $settings = [];
        }
    }

    return $settings[$key] ?? $default;
}

/**
 * Set system setting in database
 * Saves to site_settings table (primary)
 */
function setSystemSetting($key, $value, $group = 'general') {
    try {
        // Get database connection (config already loaded)
        if (!function_exists('dbConnection')) {
            $configPath = __DIR__ . '/../../config/config.php';
            $dbPath = __DIR__ . '/../../config/database.php';

            if (file_exists($configPath)) {
                require_once $configPath;
            }
            if (file_exists($dbPath)) {
                require_once $dbPath;
            }
        }

        $db = dbConnection();

        // Try to update site_settings first
        try {
            $stmt = $db->prepare("
                INSERT INTO site_settings (`key`, `value`, `group`, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE `value` = ?, updated_at = NOW()
            ");
            $result = $stmt->execute([$key, $value, $group, $value]);

            // Clear static cache in getSystemSetting
            getSystemSetting('__clear_cache__');

            return $result;
        } catch (Exception $e) {
            // Fallback to system_settings if site_settings doesn't exist
            $stmt = $db->prepare("
                INSERT INTO system_settings (setting_key, setting_value, updated_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()
            ");
            return $stmt->execute([$key, $value, $value]);
        }
    } catch (Exception $e) {
        error_log('Failed to set system setting: ' . $e->getMessage());
        return false;
    }
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    if (!$datetime) return '';
    
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate UUID v4
 */
function generateUuid() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Cameroon format)
 */
function isValidPhone($phone) {
    // Remove spaces and special characters
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Check Cameroon phone number patterns
    $patterns = [
        '/^(\+237|237)?[26][0-9]{8}$/', // MTN, Orange
        '/^(\+237|237)?[67][0-9]{8}$/'  // Other operators
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $phone)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Format phone number for display
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) === 9) {
        return '+237 ' . substr($phone, 0, 1) . ' ' . substr($phone, 1, 2) . ' ' . substr($phone, 3, 2) . ' ' . substr($phone, 5, 2) . ' ' . substr($phone, 7, 2);
    }
    
    return $phone;
}

/**
 * Slugify string for URLs
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    return empty($text) ? 'n-a' : $text;
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get file size in human readable format
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Check if string starts with
 */
function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Check if string ends with
 */
function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Get current user
 */
function currentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    static $user = null;
    
    if ($user === null) {
        try {
            $db = \Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Error loading current user: " . $e->getMessage());
            $user = false;
        }
    }
    
    return $user ?: null;
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && currentUser() !== null;
}

/**
 * Check if user has role
 */
function hasRole($role) {
    $user = currentUser();
    return $user && $user['role'] === $role;
}

/**
 * Get flash message
 */
function getFlash($type = null) {
    if ($type) {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Calculate distance between two coordinates (Haversine formula)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

/**
 * Generate order number
 */
function generateOrderNumber() {
    return 'T2E' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Get order status badge class
 */
function getOrderStatusClass($status) {
    $classes = [
        'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
        'confirmed' => 'tw-bg-blue-100 tw-text-blue-800',
        'preparing' => 'tw-bg-orange-100 tw-text-orange-800',
        'ready' => 'tw-bg-purple-100 tw-text-purple-800',
        'picked_up' => 'tw-bg-indigo-100 tw-text-indigo-800',
        'delivered' => 'tw-bg-green-100 tw-text-green-800',
        'cancelled' => 'tw-bg-red-100 tw-text-red-800'
    ];
    
    return $classes[$status] ?? 'tw-bg-gray-100 tw-text-gray-800';
}

/**
 * Get payment status badge class
 */
function getPaymentStatusClass($status) {
    $classes = [
        'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
        'paid' => 'tw-bg-green-100 tw-text-green-800',
        'failed' => 'tw-bg-red-100 tw-text-red-800',
        'refunded' => 'tw-bg-blue-100 tw-text-blue-800'
    ];
    
    return $classes[$status] ?? 'tw-bg-gray-100 tw-text-gray-800';
}

/**
 * Debug helper
 */
if (!function_exists('dd')) {
    function dd($data) {
        if (APP_DEBUG) {
            echo '<pre>';
            var_dump($data);
            echo '</pre>';
            die();
        }
    }
}

/**
 * Log helper
 */
function logInfo($message, $context = []) {
    $logMessage = date('Y-m-d H:i:s') . ' [INFO] ' . $message;
    if (!empty($context)) {
        $logMessage .= ' ' . json_encode($context);
    }
    error_log($logMessage);
}

/**
 * Cache helper
 */
function cache($key, $callback = null, $duration = null) {
    if (!CACHE_ENABLED) {
        return $callback ? $callback() : null;
    }
    
    $duration = $duration ?? CACHE_DURATION;
    $cacheFile = ROOT_PATH . '/storage/cache/' . md5($key) . '.cache';
    
    // Get from cache
    if ($callback === null) {
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $duration) {
            return unserialize(file_get_contents($cacheFile));
        }
        return null;
    }
    
    // Set cache
    $data = $callback();
    
    // Ensure cache directory exists
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    file_put_contents($cacheFile, serialize($data));
    return $data;
}

/**
 * Generate CSRF token
 * Creates a secure token for CSRF protection
 */
function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF field
 * Returns HTML input field with CSRF token
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}
