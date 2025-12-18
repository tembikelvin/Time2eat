<?php
/**
 * Time2Eat Configuration File
 * Loads environment variables and sets application constants
 */

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
        } else {
            continue; // Skip lines without '='
        }
        
        // Remove quotes if present
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}

// Load environment file based on intelligent detection
$rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);

// Detect environment first (before loading .env)
function detectEnvironmentForConfig() {
    // Check for explicit environment override
    if (isset($_SERVER['APP_ENV'])) {
        return $_SERVER['APP_ENV'];
    }

    // Check for development indicators
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    $developmentHosts = ['localhost', '127.0.0.1', '::1', '.local', '.test', '.dev'];
    $developmentPaths = ['/wamp', '/xampp', '/mamp', '/laragon', '/eat'];

    // Check host
    foreach ($developmentHosts as $devHost) {
        if (strpos($host, $devHost) !== false) {
            return 'development';
        }
    }

    // Check script path
    foreach ($developmentPaths as $devPath) {
        if (strpos($scriptName, $devPath) !== false) {
            return 'development';
        }
    }

    // Check for production indicators
    if (strpos($host, 'time2eat.org') !== false || strpos($host, 'time2eat.com') !== false) {
        return 'production';
    }

    // Default to development for local development
    return 'development';
}

$detectedEnv = detectEnvironmentForConfig();

// Load appropriate environment file
if ($detectedEnv === 'production' && file_exists($rootPath . '/.env.production')) {
    loadEnv($rootPath . '/.env.production');
} else {
    // Load .env for development or fallback
    loadEnv($rootPath . '/.env');
}

// Helper function to get environment variables with default values
function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'time2eat'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// Application Settings
define('APP_NAME', env('APP_NAME', 'Time2Eat'));

// Set APP_URL with production fallback
$appUrl = env('APP_URL');
if (empty($appUrl)) {
    // Auto-detect production URL
    if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'www.time2eat.org') {
        $appUrl = 'https://www.time2eat.org';
    } else {
        $appUrl = 'http://localhost/eat';
    }
}
define('APP_URL', $appUrl);
define('BASE_URL', APP_URL); // Alias for compatibility with url() helper

// Set APP_ENV with production fallback
$appEnv = env('APP_ENV');
if (empty($appEnv)) {
    // Auto-detect production environment
    if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'www.time2eat.org') {
        $appEnv = 'production';
    } else {
        $appEnv = 'development';
    }
}
define('APP_ENV', $appEnv);

define('APP_DEBUG', env('APP_DEBUG', 'true') === 'true');
define('APP_KEY', env('APP_KEY', 'default-secret-key'));

// Define APP_PATH for production
if (!defined('APP_PATH')) {
    define('APP_PATH', '');
}

// Define storage path
if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', $rootPath . '/storage');
}

// Security
define('JWT_SECRET', env('JWT_SECRET', 'default-jwt-secret'));

// Map Configuration
define('MAP_API_KEY', env('MAP_API_KEY', ''));
define('MAP_PROVIDER', env('MAP_PROVIDER', 'google'));

// Payment Configuration
define('STRIPE_PUBLIC_KEY', env('STRIPE_PUBLIC_KEY', ''));
define('STRIPE_SECRET_KEY', env('STRIPE_SECRET_KEY', ''));
define('PAYPAL_CLIENT_ID', env('PAYPAL_CLIENT_ID', ''));
define('PAYPAL_CLIENT_SECRET', env('PAYPAL_CLIENT_SECRET', ''));

// Tranzack payout gateway configuration
define('TRANZACK_MODE', env('TRANZACK_MODE', (env('APP_ENV', 'development') === 'production' ? 'production' : 'sandbox')));
define('TRANZACK_API_KEY', env('TRANZACK_API_KEY', ''));
define('TRANZACK_BASE_URL_SANDBOX', env('TRANZACK_BASE_URL_SANDBOX', 'https://sandbox-api.tranzack.com'));
define('TRANZACK_BASE_URL_PRODUCTION', env('TRANZACK_BASE_URL_PRODUCTION', 'https://api.tranzack.com'));

// Mobile Money Configuration
define('MTN_MOMO_API_KEY', env('MTN_MOMO_API_KEY', ''));
define('ORANGE_MONEY_API_KEY', env('ORANGE_MONEY_API_KEY', ''));

// Email Configuration
define('MAIL_HOST', env('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', env('MAIL_PORT', 587));
define('MAIL_USERNAME', env('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', env('MAIL_PASSWORD', ''));
define('MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls'));
define('MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'noreply@time2eat.com'));
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'Time2Eat'));

// SMS Configuration
define('TWILIO_SID', env('TWILIO_SID', ''));
define('TWILIO_TOKEN', env('TWILIO_TOKEN', ''));
define('TWILIO_FROM', env('TWILIO_FROM', ''));

// Push Notifications
define('ONESIGNAL_APP_ID', env('ONESIGNAL_APP_ID', ''));
define('ONESIGNAL_REST_API_KEY', env('ONESIGNAL_REST_API_KEY', ''));

// File Upload Settings
define('MAX_FILE_SIZE', (int)env('MAX_FILE_SIZE', 5242880)); // 5MB
define('ALLOWED_IMAGE_TYPES', explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,webp,gif')));

// Affiliate Settings
define('DEFAULT_AFFILIATE_RATE', (float)env('DEFAULT_AFFILIATE_RATE', 0.05));
define('WITHDRAWAL_THRESHOLD', (int)env('WITHDRAWAL_THRESHOLD', 10000));

// Delivery Settings
define('BASE_DELIVERY_FEE', (int)env('BASE_DELIVERY_FEE', 500));
define('DELIVERY_FEE_PER_KM', (int)env('DELIVERY_FEE_PER_KM', 100));

// Cache Settings
define('CACHE_ENABLED', env('CACHE_ENABLED', 'true') === 'true');
define('CACHE_DURATION', (int)env('CACHE_DURATION', 3600));

// Session Settings
define('SESSION_LIFETIME', (int)env('SESSION_LIFETIME', 7200));
define('SESSION_SECURE', env('SESSION_SECURE', 'false') === 'true');

// Rate Limiting
define('RATE_LIMIT_ENABLED', env('RATE_LIMIT_ENABLED', 'true') === 'true');
define('MAX_LOGIN_ATTEMPTS', (int)env('MAX_LOGIN_ATTEMPTS', 5));
define('RATE_LIMIT_WINDOW', (int)env('RATE_LIMIT_WINDOW', 900));

// Set timezone
date_default_timezone_set('Africa/Douala');

// Session configuration is handled in bootstrap/app.php before session_start()
// to avoid "session already active" warnings

// Error handling - enable display when APP_DEBUG is true, even in production
if (!APP_DEBUG) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', $rootPath . '/logs/error.log');
} else {
    // When APP_DEBUG is true, show errors even in production
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', $rootPath . '/logs/error.log');
}
