<?php
/**
 * Tranzak Payment Webhook Endpoint
 * 
 * This endpoint receives Tranzak Payment Notifications (TPN) from Tranzak servers
 * Route: /api/payment/tranzak/notify
 * Method: POST
 * 
 * According to Tranzak documentation:
 * - Webhooks are sent as JSON POST requests
 * - Contains authKey for verification
 * - Event types: REQUEST.COMPLETED, REFUND.COMPLETED
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only POST requests are accepted.'
    ]);
    exit;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load dependencies
// Calculate root path: from api/payment/tranzak/ we need to go up 3 levels
$rootPath = dirname(dirname(dirname(__DIR__)));
require_once $rootPath . '/vendor/autoload.php';
require_once $rootPath . '/config/database.php';
require_once $rootPath . '/src/services/TranzakPaymentService.php';

use Time2Eat\Services\TranzakPaymentService;

try {
    // Get raw POST data
    $rawInput = file_get_contents('php://input');
    
    if (empty($rawInput)) {
        // Fallback to $_POST if raw input is empty
        $rawInput = json_encode($_POST);
    }

    error_log('Tranzak Webhook Raw Input: ' . $rawInput);

    // Parse JSON payload
    $tpnData = json_decode($rawInput, true);
    
    if (!$tpnData || json_last_error() !== JSON_ERROR_NONE) {
        error_log('Tranzak Webhook: Invalid JSON payload. Error: ' . json_last_error_msg());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON payload',
            'error' => json_last_error_msg()
        ]);
        exit;
    }

    error_log('Tranzak Webhook TPN Data: ' . json_encode($tpnData));

    // Initialize Tranzak service
    $tranzakService = new TranzakPaymentService();
    
    // Handle the payment notification
    $result = $tranzakService->handlePaymentNotification($tpnData);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $result['message'] ?? 'TPN processed successfully',
            'event_type' => $tpnData['eventType'] ?? 'unknown',
            'resource_id' => $tpnData['resourceId'] ?? null
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'TPN processing failed',
            'error' => $result['error'] ?? null
        ]);
    }

} catch (\Exception $e) {
    error_log('Tranzak Webhook Exception: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error processing webhook',
        'error' => (defined('APP_ENV') && (APP_ENV === 'development' || APP_ENV === 'local')) ? $e->getMessage() : 'An error occurred'
    ]);
}

