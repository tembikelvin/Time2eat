<?php
/**
 * Production Environment Configuration
 * 
 * This file contains production-specific settings that override
 * the default configuration in config.php
 * 
 * Upload this file to: /home/user/web/time2eat.org/public_html/config/
 */

// Production Base URL
define('BASE_URL', 'https://www.time2eat.org');

// Production Environment Flag
define('ENVIRONMENT', 'production');

// Error Reporting (disabled in production for security)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Log errors to file instead
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Production Security Settings
ini_set('session.cookie_secure', '1');        // HTTPS only
ini_set('session.cookie_httponly', '1');      // Prevent XSS
ini_set('session.cookie_samesite', 'Lax');    // CSRF protection
ini_set('session.use_strict_mode', '1');      // Prevent session fixation

// Production Performance Settings - OPcache only (simple and reliable)
// Note: OPcache only caches PHP code, not data - this is safe
ini_set('opcache.enable', '1');               // Enable OPcache for PHP code
ini_set('opcache.revalidate_freq', '0');      // Always check for changes (safe for production)

// Production Timezone
date_default_timezone_set('Africa/Douala');

// Production Debug Mode (disabled)
define('DEBUG_MODE', false);

// Production Cache Settings - DISABLED to prevent issues
define('CACHE_ENABLED', false);               // Disable data caching to prevent stale data

// Production File Upload Settings
define('MAX_UPLOAD_SIZE', 5242880);           // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Production Email Settings (if different from config.php)
// define('SMTP_HOST', 'smtp.time2eat.org');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'noreply@time2eat.org');
// define('SMTP_PASSWORD', 'your-smtp-password');
// define('SMTP_FROM_EMAIL', 'noreply@time2eat.org');
// define('SMTP_FROM_NAME', 'Time2Eat');

// Production Payment Gateway Settings (if different)
// define('PAYMENT_MODE', 'live');
// define('PAYMENT_PUBLIC_KEY', 'your-live-public-key');
// define('PAYMENT_SECRET_KEY', 'your-live-secret-key');

// Production SMS Settings (if different)
// define('SMS_API_KEY', 'your-production-sms-api-key');
// define('SMS_SENDER_ID', 'Time2Eat');

// Production Google Maps API (if different)
// define('GOOGLE_MAPS_API_KEY', 'your-production-google-maps-key');

// Production Social Media Links
define('FACEBOOK_URL', 'https://facebook.com/time2eat');
define('TWITTER_URL', 'https://twitter.com/time2eat');
define('INSTAGRAM_URL', 'https://instagram.com/time2eat');

// Production Support Contact
define('SUPPORT_EMAIL', 'support@time2eat.org');
define('SUPPORT_PHONE', '+237 XXX XXX XXX');

// Production Maintenance Mode
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'We are currently performing scheduled maintenance. Please check back soon.');

// Production Logging Level
define('LOG_LEVEL', 'error');                 // Only log errors in production

// Production Session Settings
define('SESSION_LIFETIME', 86400);            // 24 hours
define('SESSION_NAME', 'time2eat_session');

// Production CORS Settings (if needed for API)
define('CORS_ALLOWED_ORIGINS', [
    'https://www.time2eat.org',
    'https://time2eat.org'
]);

// Production CDN Settings (if using CDN)
// define('CDN_URL', 'https://cdn.time2eat.org');
// define('CDN_ENABLED', true);

// Production Database Backup Settings
define('DB_BACKUP_ENABLED', true);
define('DB_BACKUP_FREQUENCY', 'daily');       // daily, weekly, monthly

// Production Security Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(self), microphone=(), camera=()');

// Production Content Security Policy (adjust as needed)
// header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;");

// Production Feature Flags
define('FEATURE_PWA_ENABLED', true);
define('FEATURE_PUSH_NOTIFICATIONS', true);
define('FEATURE_AFFILIATE_SYSTEM', true);
define('FEATURE_LIVE_TRACKING', true);
define('FEATURE_CHAT_SUPPORT', false);        // Enable when ready

// Production Rate Limiting
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 100);           // Per minute
define('RATE_LIMIT_WINDOW', 60);              // Seconds

// Production Monitoring (if using monitoring service)
// define('MONITORING_ENABLED', true);
// define('MONITORING_API_KEY', 'your-monitoring-api-key');

// Production Backup Settings
define('BACKUP_PATH', __DIR__ . '/../backups/');
define('BACKUP_RETENTION_DAYS', 30);

// Load environment-specific database config if exists
if (file_exists(__DIR__ . '/database.production.php')) {
    require_once __DIR__ . '/database.production.php';
}

// Production initialization complete
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log('[Config] Production configuration loaded successfully');
}

