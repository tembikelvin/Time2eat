<?php
/**
 * Validate Referral Code API Endpoint
 * Checks if a referral code is valid and returns referrer information
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load configuration
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/src/helpers/functions.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Get referral code from query parameter
    $referralCode = $_GET['code'] ?? '';
    
    if (empty($referralCode)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'valid' => false,
            'message' => 'Referral code is required'
        ]);
        exit;
    }
    
    // Sanitize the referral code
    $referralCode = trim($referralCode);
    $referralCode = htmlspecialchars($referralCode, ENT_QUOTES, 'UTF-8');

    // Get database connection (PDO)
    $db = Database::getInstance()->getConnection();

    // Check if referral code exists and is valid
    $stmt = $db->prepare(
        "SELECT id, first_name, last_name, email, status
         FROM users
         WHERE affiliate_code = ? AND status = 'active'"
    );
    $stmt->execute([$referralCode]);
    $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($referrer) {
        // Valid referral code
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'valid' => true,
            'referrer_name' => $referrer['first_name'] . ' ' . $referrer['last_name'],
            'message' => 'Valid referral code'
        ]);
    } else {
        // Invalid or inactive referral code
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'valid' => false,
            'message' => 'Invalid or inactive referral code'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Referral validation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Unable to validate referral code'
    ]);
}

