<?php
/**
 * Environment Detection and Configuration Helper
 * Automatically detects development vs production environments
 */

/**
 * Detect if we're running in a development environment
 */
function isDevelopmentEnvironment(): bool {
    // Check environment variable first
    $appEnv = $_ENV['APP_ENV'] ?? (defined('APP_ENV') ? APP_ENV : 'production');
    if ($appEnv === 'development') {
        return true;
    }
    if ($appEnv === 'production') {
        return false;
    }
    
    // Check for common development indicators
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $developmentHosts = [
        'localhost',
        '127.0.0.1',
        '::1',
        '.local',
        '.test',
        '.dev'
    ];
    
    foreach ($developmentHosts as $devHost) {
        if (strpos($host, $devHost) !== false) {
            return true;
        }
    }
    
    // Check for common development paths
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $developmentPaths = [
        '/wamp',
        '/xampp',
        '/mamp',
        '/laragon'
    ];
    
    foreach ($developmentPaths as $devPath) {
        if (strpos($scriptName, $devPath) !== false) {
            return true;
        }
    }
    
    // Check current working directory for development indicators
    if (php_sapi_name() === 'cli') {
        $cwd = getcwd();
        foreach ($developmentPaths as $devPath) {
            if (strpos(strtolower($cwd), strtolower($devPath)) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Get the correct application path for the current environment
 */
function getApplicationPath(): string {
    // If APP_URL is configured and not default, extract path from it
    $appUrl = defined('APP_URL') ? APP_URL : ($_ENV['APP_URL'] ?? '');
    if (!empty($appUrl) && $appUrl !== 'http://localhost' && $appUrl !== 'https://localhost') {
        $parsed = parse_url($appUrl);
        return $parsed['path'] ?? '';
    }
    
    // Auto-detect based on environment
    if (isDevelopmentEnvironment()) {
        return detectDevelopmentPath();
    } else {
        return detectProductionPath();
    }
}

/**
 * Detect application path in development environment
 */
function detectDevelopmentPath(): string {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Common development project folder names
    $commonNames = ['eat', 'time2eat', 'Time2Eat', 'food-delivery'];
    
    foreach ($commonNames as $name) {
        if (strpos($scriptName, "/$name/") !== false) {
            return "/$name";
        }
    }
    
    // Fallback: use directory of script
    $scriptDir = dirname($scriptName);
    if ($scriptDir !== '/' && $scriptDir !== '\\' && $scriptDir !== '.') {
        return $scriptDir;
    }
    
    return '';
}

/**
 * Detect application path in production environment
 */
function detectProductionPath(): string {
    // Check if we're in a subdirectory based on script name
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // If script is in a subdirectory, extract that path
    if (strpos($scriptName, '/') !== false && $scriptName !== '/index.php') {
        $path = dirname($scriptName);
        return $path === '/' ? '' : $path;
    }
    
    // Check for specific production domains
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'time2eat.org') !== false || strpos($host, 'time2eat.com') !== false) {
        // In production, files are moved out of /eat folder, so no prefix needed
        // The hybrid router handles API routes directly at /api/ without prefix
        return '';
    }
    
    // Check for common production hosting patterns
    $commonProductionPaths = ['/public_html', '/htdocs', '/www', '/html'];
    foreach ($commonProductionPaths as $prodPath) {
        if (strpos($scriptName, $prodPath) !== false) {
            // Extract the subdirectory after the common path
            $relativePath = str_replace($prodPath, '', $scriptName);
            $relativePath = dirname($relativePath);
            return $relativePath === '/' ? '' : $relativePath;
        }
    }
    
    // In production, usually at document root
    return '';
}

/**
 * Get the full base URL for the application
 */
function getEnvironmentAwareBaseUrl(): string {
    // Check for configured APP_URL first
    $appUrl = defined('APP_URL') ? APP_URL : ($_ENV['APP_URL'] ?? '');
    if (!empty($appUrl) && $appUrl !== 'http://localhost' && $appUrl !== 'https://localhost') {
        return rtrim($appUrl, '/');
    }
    
    // Build URL from server variables
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = getApplicationPath();
    
    return $protocol . $host . $path;
}

/**
 * Generate environment-aware URL
 */
function environmentUrl(string $path = ''): string {
    static $baseUrl = null;
    static $lastHost = null;
    static $lastScript = null;

    // Check if environment has changed (for testing scenarios)
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    $currentScript = $_SERVER['SCRIPT_NAME'] ?? '';

    if ($baseUrl === null || $lastHost !== $currentHost || $lastScript !== $currentScript) {
        $baseUrl = getEnvironmentAwareBaseUrl();
        $lastHost = $currentHost;
        $lastScript = $currentScript;
    }

    $path = ltrim($path, '/');

    if (empty($path)) {
        return $baseUrl;
    }

    return $baseUrl . '/' . $path;
}

/**
 * Get environment configuration summary
 */
function getEnvironmentInfo(): array {
    return [
        'is_development' => isDevelopmentEnvironment(),
        'app_env' => $_ENV['APP_ENV'] ?? (defined('APP_ENV') ? APP_ENV : 'unknown'),
        'host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown',
        'app_url_config' => defined('APP_URL') ? APP_URL : 'not defined',
        'detected_path' => getApplicationPath(),
        'base_url' => getEnvironmentAwareBaseUrl(),
        'sample_urls' => [
            'home' => environmentUrl('/'),
            'browse' => environmentUrl('/browse'),
            'customer_dashboard' => environmentUrl('/customer/dashboard')
        ]
    ];
}
