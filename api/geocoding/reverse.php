<?php
/**
 * Geocoding Reverse Proxy API
 * Proxies requests to Nominatim OpenStreetMap API to avoid CORS issues
 */

require_once __DIR__ . '/../../config/config.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check method
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get parameters
$lat = $_GET['lat'] ?? null;
$lon = $_GET['lon'] ?? null;
$zoom = $_GET['zoom'] ?? 18;
$addressdetails = $_GET['addressdetails'] ?? 1;
$format = $_GET['format'] ?? 'json';

// Validate required parameters
if (!$lat || !$lon) {
    http_response_code(400);
    echo json_encode(['error' => 'Latitude and longitude are required']);
    exit;
}

// Validate latitude and longitude
if (!is_numeric($lat) || !is_numeric($lon)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid latitude or longitude']);
    exit;
}

if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    http_response_code(400);
    echo json_encode(['error' => 'Latitude must be between -90 and 90, longitude between -180 and 180']);
    exit;
}

// Enable error reporting for debugging
ini_set('display_errors', 0); // Don't display errors to client, log them
error_reporting(E_ALL);

// Check if cURL is enabled
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL extension is not enabled']);
    exit;
}

try {
    // Build Nominatim API URL
    $nominatimUrl = 'https://nominatim.openstreetmap.org/reverse?' . http_build_query([
        'lat' => $lat,
        'lon' => $lon,
        'zoom' => $zoom,
        'addressdetails' => $addressdetails,
        'format' => $format
    ]);
    
    // Set up cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $nominatimUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Time2Eat Food Delivery App (https://time2eat.org)');
    
    // Disable SSL verification for local development to avoid 500 errors
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-Language: en-US,en;q=0.9'
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception('cURL error: ' . $error);
    }
    
    if ($httpCode !== 200) {
        throw new Exception('Nominatim API returned HTTP ' . $httpCode);
    }
    
    if (!$response) {
        throw new Exception('Empty response from Nominatim API');
    }
    
    // Parse JSON response
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from Nominatim API: ' . json_last_error_msg());
    }
    
    // Return the data
    echo json_encode($data);
    
} catch (Exception $e) {
    error_log('Geocoding reverse proxy error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Geocoding service temporarily unavailable',
        'message' => $e->getMessage()
    ]);
}
?>
