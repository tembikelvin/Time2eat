<?php
/**
 * Rider Status API Endpoint
 * GET /api/rider/status - Get current rider status
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate, private');
header('Pragma: no-cache');
header('Expires: 0');

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rider') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Load dependencies
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/models/User.php';
require_once __DIR__ . '/../../src/services/RiderStatusService.php';

use Time2Eat\Services\RiderStatusService;

try {
    $riderId = (int)$_SESSION['user_id'];
    
    // Get rider status service
    $riderStatusService = new RiderStatusService();
    
    // Get comprehensive status
    $status = $riderStatusService->getRiderStatus($riderId);
    
    if ($status['success']) {
        // Get user model for role
        $userModel = new \models\User();
        $user = $userModel->findById($riderId);
        
        echo json_encode([
            'success' => true,
            'is_available' => $status['is_available'],
            'is_online' => $status['is_online'],
            'account_status' => $status['account_status'],
            'overall_status' => $status['overall_status'],
            'last_location' => $status['last_location'],
            'schedule_status' => $status['schedule_status'],
            'role' => $user['role'] ?? 'rider'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $status['message'] ?? 'Failed to get rider status'
        ]);
    }
} catch (Exception $e) {
    error_log("Rider Status API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}

