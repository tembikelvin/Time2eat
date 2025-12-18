<?php
/**
 * API: Get Delivery Fee Estimate
 * POST /api/delivery-fee-estimate
 *
 * Calculates delivery fee based on customer location and restaurant
 */

// Disable error display to prevent JSON corruption
ini_set('display_errors', 0);

// Set JSON header FIRST before any output
header('Content-Type: application/json; charset=utf-8');

// Prevent caching of GPS-based calculations
header('Cache-Control: no-cache, no-store, must-revalidate, private');
header('Pragma: no-cache');
header('Expires: 0');

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Wrap everything in try-catch to ensure JSON response
try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../src/services/DeliveryFeeService.php';

    // Re-disable error display in case config.php enabled it (API should never output HTML errors)
    ini_set('display_errors', 0);

    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
} catch (\Throwable $e) {
    error_log('Error loading dependencies: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server configuration error', 'error' => $e->getMessage()]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$restaurantId = $input['restaurant_id'] ?? null;
$latitude = $input['latitude'] ?? null;
$longitude = $input['longitude'] ?? null;
$subtotal = $input['subtotal'] ?? 0;

if (!$restaurantId || $latitude === null || $longitude === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $db = dbConnection();

    // Get restaurant delivery settings
    $query = "SELECT id, name, latitude, longitude, delivery_radius, delivery_fee, delivery_fee_per_extra_km
              FROM restaurants
              WHERE id = :id AND status != 'deleted'";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['id' => $restaurantId]);
    $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$restaurant) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Restaurant not found']);
        exit;
    }
    
    if (!$restaurant['latitude'] || !$restaurant['longitude']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Restaurant location not available']);
        exit;
    }

    // Log GPS coordinates for debugging
    error_log('=== DELIVERY FEE ESTIMATE ===');
    error_log('Restaurant: ' . $restaurant['name'] . ' (ID: ' . $restaurant['id'] . ')');
    error_log('Restaurant GPS: Lat=' . $restaurant['latitude'] . ', Lon=' . $restaurant['longitude']);
    error_log('Customer GPS: Lat=' . $latitude . ', Lon=' . $longitude);

    // Initialize delivery fee service
    $deliveryFeeService = new \services\DeliveryFeeService();

    // Check delivery availability
    $availability = $deliveryFeeService->checkDeliveryAvailability(
        $restaurant,
        (float)$latitude,
        (float)$longitude
    );

    error_log('Distance calculated: ' . ($availability['distance'] ?? 'N/A') . ' km');
    error_log('Delivery available: ' . ($availability['available'] ? 'YES' : 'NO'));
    if (!$availability['available']) {
        error_log('Reason: ' . $availability['reason']);
    }
    
    if (!$availability['available']) {
        echo json_encode([
            'success' => false,
            'available' => false,
            'reason' => $availability['reason'],
            'distance' => $availability['distance'],
            'max_distance' => $availability['max_distance'] ?? (($restaurant['delivery_radius'] ?? 10) * 2),
            'restaurant_name' => $restaurant['name']
        ]);
        exit;
    }
    
    // Calculate delivery fee
    $feeCalculation = $deliveryFeeService->calculateDeliveryFee(
        $availability['distance'],
        $restaurant,
        $subtotal
    );
    
    // Round to nearest 50 XAF
    $roundedFee = ceil($feeCalculation['total_fee'] / 50) * 50;
    
    // Prepare detailed breakdown
    $breakdown = [
        'base_fee' => $feeCalculation['base_fee'],
        'extra_fee' => $feeCalculation['extra_fee'],
        'total_fee' => $roundedFee,
        'distance' => round($availability['distance'], 2),
        'free_zone_radius' => $feeCalculation['free_zone_radius'],
        'within_free_zone' => $feeCalculation['within_free_zone'],
        'extra_distance' => $feeCalculation['extra_distance'] ?? 0,
        'extra_fee_per_km' => $feeCalculation['extra_fee_per_km'] ?? 0,
        'is_free_delivery' => $feeCalculation['is_free_delivery'] ?? false,
        'free_delivery_reason' => $feeCalculation['free_delivery_reason'] ?? null,
        'savings' => $feeCalculation['savings'] ?? 0
    ];
    
    // Create user-friendly message
    $message = '';
    if ($breakdown['is_free_delivery']) {
        $message = "Free delivery! " . $breakdown['free_delivery_reason'];
    } elseif ($breakdown['within_free_zone']) {
        $message = sprintf(
            "Within %s km zone - base fee only",
            number_format($breakdown['free_zone_radius'], 1)
        );
    } else {
        $message = sprintf(
            "%s base + %s for %s km extra distance",
            number_format($breakdown['base_fee']) . ' FCFA',
            number_format($breakdown['extra_fee']) . ' FCFA',
            number_format($breakdown['extra_distance'], 1)
        );
    }
    
    echo json_encode([
        'success' => true,
        'available' => true,
        'breakdown' => $breakdown,
        'message' => $message,
        'restaurant_name' => $restaurant['name']
    ]);
    
} catch (\Throwable $e) {
    error_log("Error calculating delivery fee: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error calculating delivery fee',
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
