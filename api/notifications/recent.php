<?php
/**
 * API Endpoint: Get Recent Notifications
 * GET /api/notifications/recent
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type
header('Content-Type: application/json');

// Load dependencies
$controllerPath = __DIR__ . '/../../src/controllers/NotificationController.php';
if (!file_exists($controllerPath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Controller not found',
        'notifications' => [],
        'unread_count' => 0
    ]);
    exit;
}

require_once $controllerPath;

try {
    // Create controller instance and call the method
    $controller = new \controllers\NotificationController();
    $controller->getRecentNotifications();
} catch (Exception $e) {
    // Handle any errors - provide a simple fallback
    error_log("Notification API Error: " . $e->getMessage());
    
    // Simple fallback response
    echo json_encode([
        'success' => true,
        'notifications' => [],
        'unread_count' => 0
    ]);
}
