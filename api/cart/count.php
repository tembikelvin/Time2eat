<?php
/**
 * Cart Count API
 * Returns the number of items in the user's cart
 */

// Start session FIRST
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
require_once __DIR__ . '/../../src/helpers/functions.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to view cart count']);
    exit;
}

// Get user role from session or database
$userRole = $_SESSION['user_role'] ?? null;

// If role not in session, fetch from database
if (!$userRole) {
    try {
        $db = dbConnection();
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userRole = $user['role'];
            $_SESSION['user_role'] = $userRole; // Update session
        }
    } catch (Exception $e) {
        error_log("Failed to fetch user role: " . $e->getMessage());
    }
}

// Check if user is a customer (only customers can have cart)
if ($userRole !== 'customer') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Only customers can have cart items',
        'role_error' => true,
        'your_role' => $userRole ?? 'unknown'
    ]);
    exit;
}

try {
    $db = dbConnection();
    $userId = $_SESSION['user_id'];
    
    // Get cart item count
    $countStmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM cart_items
        WHERE user_id = ?
    ");
    $countStmt->execute([$userId]);
    $result = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    $count = (int)($result['count'] ?? 0);
    
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
    
} catch (Exception $e) {
    error_log("Cart count error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get cart count'
    ]);
}
?>
