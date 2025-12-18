<?php
/**
 * Geocoding Search Proxy API
 * Proxies requests to Nominatim OpenStreetMap API for address search
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
$q = $_GET['q'] ?? null;
$limit = $_GET['limit'] ?? 5;
$countrycodes = $_GET['countrycodes'] ?? 'cm'; // Default to Cameroon
$format = $_GET['format'] ?? 'json';

// Validate required parameters
if (!$q || trim($q) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Query parameter is required']);
    exit;
}

// Sanitize query
$q = trim($q);
if (strlen($q) < 2) {
    http_response_code(400);
    echo json_encode(['error' => 'Query must be at least 2 characters long']);
    exit;
}

try {
    // Build Nominatim API URL
    $nominatimUrl = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q' => $q,
        'limit' => $limit,
        'countrycodes' => $countrycodes,
        'format' => $format,
        'addressdetails' => 1
    ]);
    
    // Set up cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $nominatimUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Time2Eat Food Delivery App (https://time2eat.org)');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-Language: en-US,en;q=0.9'
    ]);
    
    // Disable SSL verification for local development
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
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
    error_log('Geocoding search proxy error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Geocoding service temporarily unavailable',
        'message' => $e->getMessage()
    ]);
}
?>
