<?php
/**
 * Email Configuration for Time2Eat
 * Direct configuration - no fallbacks, fails clearly if misconfigured
 */

// Validate required environment variables
$requiredVars = ['MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS'];
$missingVars = [];

foreach ($requiredVars as $var) {
    if (empty($_ENV[$var])) {
        $missingVars[] = $var;
    }
}

if (!empty($missingVars)) {
    $errorMsg = "❌ EMAIL CONFIGURATION ERROR: Missing required environment variables: " . implode(', ', $missingVars);
    error_log($errorMsg);
    
    // In development, show detailed error
    if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
        die($errorMsg . "\n\nPlease set these variables in your .env file (development) or .env.production file (production)");
    }
    
    // In production, fail silently but log the error
    throw new Exception("Email configuration incomplete");
}

// Detect environment
$environment = $_ENV['APP_ENV'] ?? 'development';
$isProduction = $environment === 'production';

return [
    // SMTP Settings - REQUIRED
    'smtp' => [
        'host' => $_ENV['MAIL_HOST'],
        'port' => (int)$_ENV['MAIL_PORT'],
        'username' => $_ENV['MAIL_USERNAME'],
        'password' => $_ENV['MAIL_PASSWORD'],
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'auth' => true,
    ],
    
    // Email Settings - REQUIRED
    'from' => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'],
        'name' => $_ENV['MAIL_FROM_NAME'] ?? 'Time2Eat',
    ],
    
    // App Settings
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost',
    
    // Environment-specific settings
    'test_mode' => $isProduction ? false : ($_ENV['MAIL_TEST_MODE'] ?? false),
    'test_email' => $isProduction ? null : ($_ENV['MAIL_TEST_EMAIL'] ?? null),
    
    // Environment info
    'environment' => $environment,
    'debug' => !$isProduction,
    
    // Production settings
    'retry_attempts' => $isProduction ? 3 : 1,
    'retry_delay' => $isProduction ? 5 : 1,
    'logging_enabled' => true,
];
