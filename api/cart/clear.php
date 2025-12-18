<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CRITICAL: Prevent caching of cart data (user-specific, must be real-time)
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header_remove('ETag');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'customer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only customers can clear cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = dbConnection();
    $userId = $_SESSION['user_id'];

    $deleteStmt = $db->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $deleteStmt->execute([$userId]);

    echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);

} catch (Exception $e) {
    error_log("Clear cart error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while clearing cart']);
}
