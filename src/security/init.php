<?php

declare(strict_types=1);

/**
 * Security Initialization File
 * This file should be included at the very beginning of the application
 * to ensure all security features are properly initialized
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not allowed');
}

// Initialize security bootstrap
require_once __DIR__ . '/SecurityBootstrap.php';

try {
    // Initialize all security features
    SecurityBootstrap::initialize();
    
    // Log application start
    if (class_exists('SecurityManager')) {
        $security = SecurityManager::getInstance();
        $security->logSecurityEvent('application_start', 'Application security initialized', [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
        ], 'info');
    }
    
} catch (Exception $e) {
    // Log critical security initialization failure
    error_log("CRITICAL: Security initialization failed: " . $e->getMessage());
    
    // In production, show generic error page
    if (defined('APP_ENV') && APP_ENV === 'production') {
        http_response_code(503);
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Service Temporarily Unavailable - Time2Eat</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 flex items-center justify-center min-h-screen'>
    <div class='bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center'>
        <div class='text-orange-500 text-6xl mb-4'>🔧</div>
        <h1 class='text-2xl font-bold text-gray-800 mb-4'>Service Temporarily Unavailable</h1>
        <p class='text-gray-600 mb-6'>
            We're currently performing maintenance to improve your experience. Please try again in a few minutes.
        </p>
        <button onclick='location.reload()' class='bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 transition-colors'>
            Try Again
        </button>
    </div>
</body>
</html>";
        exit;
    } else {
        // In development, show detailed error
        throw $e;
    }
}

// Additional security checks
performSecurityChecks();

/**
 * Perform additional security checks
 */
function performSecurityChecks(): void
{
    // Check for required PHP extensions
    $requiredExtensions = ['openssl', 'hash', 'json', 'mbstring', 'filter'];
    $missingExtensions = [];
    
    foreach ($requiredExtensions as $extension) {
        if (!extension_loaded($extension)) {
            $missingExtensions[] = $extension;
        }
    }
    
    if (!empty($missingExtensions)) {
        error_log("WARNING: Missing required PHP extensions: " . implode(', ', $missingExtensions));
    }
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        error_log("WARNING: PHP version " . PHP_VERSION . " is not recommended. Please upgrade to PHP 8.0+");
    }
    
    // Check for dangerous PHP settings
    $dangerousSettings = [
        'register_globals' => 'off',
        'magic_quotes_gpc' => 'off',
        'allow_url_fopen' => 'off',
        'allow_url_include' => 'off'
    ];
    
    foreach ($dangerousSettings as $setting => $expectedValue) {
        $currentValue = ini_get($setting);
        if ($currentValue && $currentValue !== $expectedValue) {
            error_log("WARNING: Dangerous PHP setting detected: {$setting} = {$currentValue}");
        }
    }
    
    // Check file permissions
    checkFilePermissions();
    
    // Validate environment configuration
    validateEnvironmentConfig();
}

/**
 * Check critical file permissions
 */
function checkFilePermissions(): void
{
    $criticalPaths = [
        APP_ROOT . '/config',
        APP_ROOT . '/logs',
        APP_ROOT . '/.env'
    ];
    
    foreach ($criticalPaths as $path) {
        if (file_exists($path)) {
            $perms = fileperms($path);
            
            // Check if world-writable (dangerous)
            if ($perms & 0x0002) {
                error_log("WARNING: World-writable file/directory detected: {$path}");
            }
            
            // Check if world-readable for sensitive files
            if (basename($path) === '.env' && ($perms & 0x0004)) {
                error_log("WARNING: Environment file is world-readable: {$path}");
            }
        }
    }
}

/**
 * Validate environment configuration
 */
function validateEnvironmentConfig(): void
{
    // Check if running in production with debug enabled
    if (defined('APP_ENV') && APP_ENV === 'production') {
        if (ini_get('display_errors') === '1') {
            error_log("WARNING: display_errors is enabled in production");
        }
        
        if (defined('APP_DEBUG') && APP_DEBUG === true) {
            error_log("WARNING: Debug mode is enabled in production");
        }
    }
    
    // Check for default/weak secrets
    $secrets = ['APP_KEY', 'DB_PASSWORD', 'JWT_SECRET'];
    foreach ($secrets as $secret) {
        if (defined($secret)) {
            $value = constant($secret);
            if (empty($value) || in_array($value, ['secret', 'password', '123456', 'changeme'])) {
                error_log("WARNING: Weak or default secret detected for {$secret}");
            }
        }
    }
}

// Set up shutdown function for cleanup
register_shutdown_function('securityShutdown');

/**
 * Security cleanup on shutdown
 */
function securityShutdown(): void
{
    // Clear sensitive data from memory
    if (isset($_POST['password'])) {
        $_POST['password'] = str_repeat('*', strlen($_POST['password']));
    }
    
    if (isset($_POST['password_confirmation'])) {
        $_POST['password_confirmation'] = str_repeat('*', strlen($_POST['password_confirmation']));
    }
    
    // Log application shutdown
    try {
        if (class_exists('SecurityManager')) {
            $security = SecurityManager::getInstance();
            $security->logSecurityEvent('application_shutdown', 'Application shutdown', [
                'execution_time' => defined('REQUEST_START_TIME') ? round((microtime(true) - REQUEST_START_TIME) * 1000, 2) . 'ms' : 'unknown',
                'memory_peak' => memory_get_peak_usage(true),
                'memory_current' => memory_get_usage(true)
            ], 'info');
        }
    } catch (Exception $e) {
        error_log("Failed to log application shutdown: " . $e->getMessage());
    }
}

// Define security constants
if (!defined('SECURITY_INITIALIZED')) {
    define('SECURITY_INITIALIZED', true);
    define('SECURITY_VERSION', '1.0.0');
    define('SECURITY_INIT_TIME', microtime(true));
}
