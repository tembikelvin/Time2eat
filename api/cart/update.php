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
    echo json_encode(['success' => false, 'message' => 'Only customers can update cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cartItemId = $input['cart_item_id'] ?? null;
$quantity = $input['quantity'] ?? null;

if (!$cartItemId || !$quantity) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cart item ID and quantity are required']);
    exit;
}

if ($quantity < 1 || $quantity > 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Quantity must be between 1 and 10']);
    exit;
}

try {
    $db = dbConnection();
    $userId = $_SESSION['user_id'];
    
    // Verify cart item belongs to user
    $cartItemStmt = $db->prepare("SELECT * FROM cart_items WHERE id = ? AND user_id = ?");
    $cartItemStmt->execute([$cartItemId, $userId]);
    $cartItem = $cartItemStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cartItem) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }
    
    // Update quantity
    $newTotalPrice = $quantity * $cartItem['unit_price'];
    
    $updateStmt = $db->prepare("
        UPDATE cart_items
        SET quantity = ?, total_price = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$quantity, $newTotalPrice, $cartItemId]);
    
    // Get updated cart totals
    $totalsStmt = $db->prepare("
        SELECT 
            COUNT(*) as item_count,
            SUM(total_price) as subtotal
        FROM cart_items
        WHERE user_id = ?
    ");
    $totalsStmt->execute([$userId]);
    $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC);
    
    $subtotal = (float)($totals['subtotal'] ?? 0);
    $serviceFee = $subtotal * 0.025;
    $tax = $subtotal * 0.1925;
    $deliveryFee = $subtotal >= 5000 ? 0 : 500;
    $total = $subtotal + $serviceFee + $tax + $deliveryFee;
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully',
        'cart_totals' => [
            'item_count' => (int)$totals['item_count'],
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'tax' => $tax,
            'delivery_fee' => $deliveryFee,
            'total' => $total
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Update cart error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating cart']);
}
?>
