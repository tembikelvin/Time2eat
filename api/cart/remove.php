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
    echo json_encode(['success' => false, 'message' => 'Only customers can remove cart items']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cartItemId = $input['cart_item_id'] ?? null;

if (!$cartItemId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cart item ID is required']);
    exit;
}

try {
    $db = dbConnection();
    $userId = $_SESSION['user_id'];
    
    // Verify cart item belongs to user and delete
    $deleteStmt = $db->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    $deleteStmt->execute([$cartItemId, $userId]);
    
    if ($deleteStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }
    
    // Get updated cart totals
    $totalsStmt = $db->prepare("
        SELECT
            COUNT(*) as item_count,
            SUM(total_price) as subtotal,
            GROUP_CONCAT(DISTINCT m.restaurant_id) as restaurant_ids
        FROM cart_items c
        INNER JOIN menu_items m ON c.menu_item_id = m.id
        WHERE c.user_id = ?
    ");
    $totalsStmt->execute([$userId]);
    $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC);

    $subtotal = (float)($totals['subtotal'] ?? 0);
    $serviceFee = $subtotal * 0.025;

    // Get base delivery fee from restaurants
    $deliveryFee = 0;
    if (!empty($totals['restaurant_ids'])) {
        $restaurantIds = explode(',', $totals['restaurant_ids']);
        $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));
        $restaurantStmt = $db->prepare("SELECT delivery_fee FROM restaurants WHERE id IN ($placeholders)");
        $restaurantStmt->execute($restaurantIds);
        $restaurants = $restaurantStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($restaurants as $restaurant) {
            $deliveryFee += (float)($restaurant['delivery_fee'] ?? 500);
        }
    }

    $total = $subtotal + $serviceFee + $deliveryFee;

    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart',
        'cart_totals' => [
            'item_count' => (int)$totals['item_count'],
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'delivery_fee' => $deliveryFee,
            'delivery_fee_estimated' => true,
            'total' => $total
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Remove from cart error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while removing item']);
}
?>
