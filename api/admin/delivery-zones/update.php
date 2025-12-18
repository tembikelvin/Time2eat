<?php
/**
 * API: Update Delivery Zone
 * POST /api/admin/delivery-zones/{id}
 */

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/models/User.php';

header('Content-Type: application/json');

// Check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user is admin
$userModel = new User();
$user = $userModel->findById($_SESSION['user_id']);

if (!$user || $user->role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Get restaurant ID from URL
$restaurantId = $_POST['restaurant_id'] ?? null;

if (!$restaurantId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Restaurant ID is required']);
    exit;
}

// Validate input
$deliveryRadius = $_POST['delivery_radius'] ?? null;
$deliveryFee = $_POST['delivery_fee'] ?? null;
$deliveryFeePerExtraKm = $_POST['delivery_fee_per_extra_km'] ?? null;
$minimumOrder = $_POST['minimum_order'] ?? null;

if ($deliveryRadius === null || $deliveryFee === null || $deliveryFeePerExtraKm === null || $minimumOrder === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate ranges
if ($deliveryRadius < 1 || $deliveryRadius > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Delivery radius must be between 1 and 50 km']);
    exit;
}

if ($deliveryFee < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Delivery fee cannot be negative']);
    exit;
}

if ($deliveryFeePerExtraKm < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Extra fee per km cannot be negative']);
    exit;
}

if ($minimumOrder < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Minimum order cannot be negative']);
    exit;
}

try {
    $db = getDbConnection();
    
    // Check if restaurant exists
    $checkQuery = "SELECT id FROM restaurants WHERE id = :id AND status != 'deleted'";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute(['id' => $restaurantId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Restaurant not found']);
        exit;
    }
    
    // Update delivery zone settings
    $updateQuery = "UPDATE restaurants 
                    SET delivery_radius = :delivery_radius,
                        delivery_fee = :delivery_fee,
                        delivery_fee_per_extra_km = :delivery_fee_per_extra_km,
                        minimum_order = :minimum_order,
                        updated_at = NOW()
                    WHERE id = :id";
    
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([
        'delivery_radius' => $deliveryRadius,
        'delivery_fee' => $deliveryFee,
        'delivery_fee_per_extra_km' => $deliveryFeePerExtraKm,
        'minimum_order' => $minimumOrder,
        'id' => $restaurantId
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Delivery zone updated successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error updating delivery zone: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>

