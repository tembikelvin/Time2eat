<?php

// Simple diagnostics: check if email verification is required

// Ensure errors are visible during testing
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Project root resolution
$projectRoot = dirname(__DIR__, 2);

// Include helper which reads the admin setting via SiteSetting
require_once $projectRoot . '/src/helpers/auth_helpers.php';

header('Content-Type: application/json');

try {
    $required = isEmailVerificationRequired();
    echo json_encode([
        'success' => true,
        'email_verification_required' => (bool)$required,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}



