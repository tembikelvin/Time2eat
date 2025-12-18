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
require_once __DIR__ . '/../../src/helpers/functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$userRole = $_SESSION['user_role'] ?? null;

if (!$userRole) {
    try {
        $db = dbConnection();
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userRole = $user['role'];
            $_SESSION['user_role'] = $userRole;
        }
    } catch (Exception $e) {
        error_log("Failed to fetch user role: " . $e->getMessage());
    }
}

if ($userRole !== 'customer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only customers can access cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = dbConnection();
    $userId = $_SESSION['user_id'];
    
    // Get cart items with full details
    $cartQuery = "
        SELECT 
            c.id,
            c.menu_item_id,
            c.quantity,
            c.unit_price,
            c.total_price,
            c.customizations,
            c.special_instructions,
            m.name as item_name,
            m.description as item_description,
            m.image as item_image,
            m.price as base_price,
            r.id as restaurant_id,
            r.name as restaurant_name,
            r.image as restaurant_image
        FROM cart_items c
        INNER JOIN menu_items m ON c.menu_item_id = m.id
        INNER JOIN restaurants r ON m.restaurant_id = r.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ";
    
    $stmt = $db->prepare($cartQuery);
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $subtotal = 0;
    $restaurantIds = [];
    foreach ($cartItems as $item) {
        $subtotal += (float)$item['total_price'];
        if (!in_array($item['restaurant_id'], $restaurantIds)) {
            $restaurantIds[] = $item['restaurant_id'];
        }
    }

    $serviceFee = $subtotal * 0.025;

    // Get estimated delivery fee from restaurants
    $estimatedDeliveryFee = 0;
    if (!empty($restaurantIds)) {
        $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));
        $restaurantQuery = "SELECT delivery_fee FROM restaurants WHERE id IN ($placeholders)";
        $stmt = $db->prepare($restaurantQuery);
        $stmt->execute($restaurantIds);
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($restaurants as $restaurant) {
            $estimatedDeliveryFee += (float)($restaurant['delivery_fee'] ?? 500);
        }
    }

    $total = $subtotal + $serviceFee + $estimatedDeliveryFee;

    echo json_encode([
        'success' => true,
        'items' => $cartItems,
        'cart_totals' => [
            'item_count' => count($cartItems),
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'delivery_fee' => $estimatedDeliveryFee,
            'delivery_fee_estimated' => true,
            'total' => $total
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get cart error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching cart']);
}
?>

