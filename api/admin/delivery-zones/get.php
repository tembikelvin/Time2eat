<?php
/**
 * API: Get Delivery Zone Details
 * GET /api/admin/delivery-zones/{id}
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
$restaurantId = $_GET['id'] ?? null;

if (!$restaurantId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Restaurant ID is required']);
    exit;
}

try {
    $db = getDbConnection();
    
    $query = "SELECT 
                id,
                name,
                delivery_radius,
                delivery_fee,
                delivery_fee_per_extra_km,
                minimum_order
              FROM restaurants
              WHERE id = :id AND status != 'deleted'";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['id' => $restaurantId]);
    $zone = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$zone) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Restaurant not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'zone' => $zone
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching delivery zone: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>

