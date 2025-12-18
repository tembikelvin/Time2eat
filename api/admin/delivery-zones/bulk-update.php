<?php
/**
 * API: Bulk Update Delivery Zones
 * POST /api/admin/delivery-zones/bulk-update
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$restaurantIds = $input['restaurant_ids'] ?? [];
$updates = $input['updates'] ?? [];

if (empty($restaurantIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No restaurants selected']);
    exit;
}

if (empty($updates)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No updates specified']);
    exit;
}

// Validate updates
$allowedFields = ['delivery_radius', 'delivery_fee', 'delivery_fee_per_extra_km', 'minimum_order'];
foreach ($updates as $field => $value) {
    if (!in_array($field, $allowedFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Invalid field: $field"]);
        exit;
    }
    
    // Validate values
    if ($field === 'delivery_radius' && ($value < 1 || $value > 50)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Delivery radius must be between 1 and 50 km']);
        exit;
    }
    
    if (in_array($field, ['delivery_fee', 'delivery_fee_per_extra_km', 'minimum_order']) && $value < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' cannot be negative']);
        exit;
    }
}

try {
    $db = getDbConnection();
    
    // Build UPDATE query
    $setParts = [];
    $params = [];
    
    foreach ($updates as $field => $value) {
        $setParts[] = "$field = :$field";
        $params[$field] = $value;
    }
    
    $setClause = implode(', ', $setParts);
    
    // Create placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));
    
    $updateQuery = "UPDATE restaurants 
                    SET $setClause, updated_at = NOW()
                    WHERE id IN ($placeholders) AND status != 'deleted'";
    
    $stmt = $db->prepare($updateQuery);
    
    // Bind update values
    $bindIndex = 1;
    foreach ($updates as $field => $value) {
        $stmt->bindValue(":$field", $value);
    }
    
    // Bind restaurant IDs
    foreach ($restaurantIds as $id) {
        $stmt->bindValue($bindIndex++, $id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $updatedCount = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully updated $updatedCount restaurants",
        'updated_count' => $updatedCount
    ]);
    
} catch (Exception $e) {
    error_log("Error bulk updating delivery zones: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

