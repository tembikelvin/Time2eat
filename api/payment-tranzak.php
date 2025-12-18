<?php
/**
 * Tranzak Payment API Endpoints
 * Handles payment processing through Tranzak Cameroon
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load dependencies
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/services/TranzakPaymentService.php';

use Time2Eat\Services\TranzakPaymentService;

try {
    $tranzakService = new TranzakPaymentService();
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));

    // Extract action from URL (e.g., /api/payment-tranzak/initiate)
    $action = $pathParts[2] ?? '';

    switch ($action) {
        case 'initiate':
            if ($method === 'POST') {
                handleInitiatePayment($tranzakService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case 'verify':
            if ($method === 'GET') {
                handleVerifyPayment($tranzakService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case 'notify':
            if ($method === 'POST') {
                handlePaymentNotification($tranzakService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case 'methods':
            if ($method === 'GET') {
                handleGetPaymentMethods($tranzakService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case 'test':
            if ($method === 'GET') {
                handleTestConnection($tranzakService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            break;
    }

} catch (Exception $e) {
    error_log("Tranzak Payment API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

/**
 * Handle initiate payment request
 */
function handleInitiatePayment(TranzakPaymentService $service): void
{
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['order_id', 'amount', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            return;
        }
    }

    // Prepare payment data
    $paymentData = [
        'order_id' => $input['order_id'],
        'amount' => (int)$input['amount'],
        'currency' => 'XAF',
        'description' => $input['description'] ?? "Payment for Order #{$input['order_id']}",
        'return_url' => $input['return_url'] ?? getBaseUrl() . '/payment/return',
        'notify_url' => $input['notify_url'] ?? getBaseUrl() . '/api/payment-tranzak/notify',
        'customer_email' => $_SESSION['user_email'] ?? '',
        'customer_phone' => $_SESSION['user_phone'] ?? '',
        'customer_name' => ($_SESSION['user_first_name'] ?? '') . ' ' . ($_SESSION['user_last_name'] ?? ''),
        'payment_method' => $input['payment_method']
    ];

    $result = $service->createPaymentRequest($paymentData);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

/**
 * Handle verify payment request
 */
function handleVerifyPayment(TranzakPaymentService $service): void
{
    $transactionId = $_GET['transaction_id'] ?? '';

    if (empty($transactionId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Transaction ID required']);
        return;
    }

    $result = $service->verifyPayment($transactionId);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

/**
 * Handle Tranzak Payment Notification (TPN) webhook
 * Based on official Tranzak documentation: https://docs.developer.tranzak.me
 */
function handlePaymentNotification(TranzakPaymentService $service): void
{
    // Set proper headers
    header('Content-Type: application/json');
    
    try {
        // Get raw POST data
        $rawInput = file_get_contents('php://input');
        
        if (empty($rawInput)) {
            // Try to get from $_POST if raw input is empty
            $rawInput = json_encode($_POST);
        }

        // Log the raw webhook for debugging
        error_log("Tranzak Webhook Raw Input: " . $rawInput);

        // Parse JSON payload
        $tpnData = json_decode($rawInput, true);
        
        if (!$tpnData || json_last_error() !== JSON_ERROR_NONE) {
            error_log("Tranzak Webhook: Invalid JSON payload. Error: " . json_last_error_msg());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON payload',
                'error' => json_last_error_msg()
            ]);
            return;
        }

        // Log the parsed TPN data
        error_log("Tranzak Webhook TPN Data: " . json_encode($tpnData));

        // Process the TPN
        $result = $service->handlePaymentNotification($tpnData);
        
        if ($result['success']) {
            // Return 200 OK to acknowledge receipt
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => $result['message'] ?? 'TPN processed successfully',
                'event_type' => $tpnData['eventType'] ?? 'unknown',
                'resource_id' => $tpnData['resourceId'] ?? null
            ]);
        } else {
            // Return 400 Bad Request for invalid webhooks
            // Note: Don't return 401/403 as Tranzak may retry
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $result['message'] ?? 'TPN processing failed',
                'error' => $result['error'] ?? null
            ]);
        }

    } catch (\Exception $e) {
        error_log("Tranzak Webhook Exception: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        
        // Return 500 for unexpected errors
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error processing webhook',
            'error' => APP_ENV === 'development' ? $e->getMessage() : 'An error occurred'
        ]);
    }
}

/**
 * Handle get payment methods request
 */
function handleGetPaymentMethods(TranzakPaymentService $service): void
{
    $methods = $service->getAvailablePaymentMethods();
    
    echo json_encode([
        'success' => true,
        'payment_methods' => $methods
    ]);
}

/**
 * Handle test connection request
 */
function handleTestConnection(TranzakPaymentService $service): void
{
    // Check authentication (admin only)
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $result = $service->testConnection();
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

/**
 * Get base URL
 */
function getBaseUrl(): string
{
    return $_ENV['APP_URL'] ?? 'http://localhost';
}
