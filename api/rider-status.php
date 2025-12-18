<?php
/**
 * Rider Status API Endpoints
 * Handles rider online/offline status management
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
require_once __DIR__ . '/../src/services/RiderStatusService.php';

use Time2Eat\Services\RiderStatusService;

try {
    $riderStatusService = new RiderStatusService();
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));

    // Extract action from URL (e.g., /api/rider-status/toggle)
    $action = $pathParts[2] ?? '';

    switch ($action) {
        case 'toggle':
            if ($method === 'POST') {
                handleToggleStatus($riderStatusService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case 'status':
            if ($method === 'GET') {
                handleGetStatus($riderStatusService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case 'online':
            if ($method === 'POST') {
                handleSetOnline($riderStatusService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case 'offline':
            if ($method === 'POST') {
                handleSetOffline($riderStatusService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case 'list':
            if ($method === 'GET') {
                handleGetOnlineRiders($riderStatusService);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case 'stats':
            if ($method === 'GET') {
                handleGetStatistics($riderStatusService);
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
    error_log("Rider Status API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

/**
 * Handle toggle status request
 */
function handleToggleStatus(RiderStatusService $service): void
{
    // Check authentication
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rider') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $riderId = (int)$_SESSION['user_id'];
    
    $latitude = isset($input['latitude']) ? (float)$input['latitude'] : null;
    $longitude = isset($input['longitude']) ? (float)$input['longitude'] : null;

    $result = $service->toggleRiderStatus($riderId, $latitude, $longitude);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

/**
 * Handle get status request
 */
function handleGetStatus(RiderStatusService $service): void
{
    // Check authentication
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rider') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $riderId = (int)$_SESSION['user_id'];
    $result = $service->getRiderStatus($riderId);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

/**
 * Handle set online request
 */
function handleSetOnline(RiderStatusService $service): void
{
    // Check authentication
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rider') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $riderId = (int)$_SESSION['user_id'];
    
    $latitude = isset($input['latitude']) ? (float)$input['latitude'] : null;
    $longitude = isset($input['longitude']) ? (float)$input['longitude'] : null;

    $result = $service->setRiderOnline($riderId, $latitude, $longitude);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

/**
 * Handle set offline request
 */
function handleSetOffline(RiderStatusService $service): void
{
    // Check authentication
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rider') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $riderId = (int)$_SESSION['user_id'];
    $result = $service->setRiderOffline($riderId);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

/**
 * Handle get online riders request
 */
function handleGetOnlineRiders(RiderStatusService $service): void
{
    // Check authentication (admin or rider)
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'rider'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $riders = $service->getOnlineRiders($limit);
    
    echo json_encode([
        'success' => true,
        'riders' => $riders,
        'count' => count($riders)
    ]);
}

/**
 * Handle get statistics request
 */
function handleGetStatistics(RiderStatusService $service): void
{
    // Check authentication (admin only)
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $stats = $service->getRiderStatistics();
    
    echo json_encode([
        'success' => true,
        'statistics' => $stats
    ]);
}
