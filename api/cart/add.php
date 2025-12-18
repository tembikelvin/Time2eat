<?php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
});

set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server exception']);
    exit;
});

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
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
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
    echo json_encode(['success' => false, 'message' => 'Only customers can add items to cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$menuItemId = $input['menu_item_id'] ?? null;
$quantity = $input['quantity'] ?? 1;
$customizations = $input['customizations'] ?? [];
$specialInstructions = $input['special_instructions'] ?? '';

if (!$menuItemId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Menu item ID is required']);
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

    $menuItemStmt = $db->prepare("
        SELECT m.*, r.id as restaurant_id, r.name as restaurant_name, r.status as restaurant_status
        FROM menu_items m
        INNER JOIN restaurants r ON m.restaurant_id = r.id
        WHERE m.id = ? AND m.is_available = 1
    ");
    $menuItemStmt->execute([$menuItemId]);
    $menuItem = $menuItemStmt->fetch(PDO::FETCH_ASSOC);

    if (!$menuItem) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Menu item not found or not available']);
        exit;
    }

    if ($menuItem['restaurant_status'] !== 'active') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Restaurant is currently closed']);
        exit;
    }

    $existingCartStmt = $db->prepare("
        SELECT DISTINCT r.id as restaurant_id
        FROM cart_items c
        INNER JOIN menu_items m ON c.menu_item_id = m.id
        INNER JOIN restaurants r ON m.restaurant_id = r.id
        WHERE c.user_id = ?
    ");
    $existingCartStmt->execute([$userId]);
    $existingRestaurants = $existingCartStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($existingRestaurants) && !in_array($menuItem['restaurant_id'], $existingRestaurants)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You can only order from one restaurant at a time. Please clear your cart first.']);
        exit;
    }

    $unitPrice = (float)$menuItem['price'];
    $customizationsJson = json_encode($customizations);

    $existingItemStmt = $db->prepare("
        SELECT * FROM cart_items
        WHERE user_id = ? AND menu_item_id = ? AND customizations = ?
    ");
    $existingItemStmt->execute([$userId, $menuItemId, $customizationsJson]);
    $existingItem = $existingItemStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        $newQuantity = min($existingItem['quantity'] + $quantity, 10);
        $newTotalPrice = $newQuantity * $unitPrice;

        $updateStmt = $db->prepare("
            UPDATE cart_items
            SET quantity = ?, total_price = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$newQuantity, $newTotalPrice, $existingItem['id']]);
        $cartItemId = $existingItem['id'];
    } else {
        $totalPrice = $quantity * $unitPrice;

        $insertStmt = $db->prepare("
            INSERT INTO cart_items (user_id, menu_item_id, quantity, unit_price, total_price, customizations, special_instructions, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $insertStmt->execute([$userId, $menuItemId, $quantity, $unitPrice, $totalPrice, $customizationsJson, $specialInstructions]);
        $cartItemId = $db->lastInsertId();
    }

    $totalsStmt = $db->prepare("
        SELECT COUNT(*) as item_count, SUM(total_price) as subtotal
        FROM cart_items
        WHERE user_id = ?
    ");
    $totalsStmt->execute([$userId]);
    $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully',
        'cart_item_id' => $cartItemId,
        'cart_totals' => [
            'item_count' => (int)$totals['item_count'],
            'subtotal' => (float)($totals['subtotal'] ?? 0)
        ]
    ]);

} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding to cart']);
}
