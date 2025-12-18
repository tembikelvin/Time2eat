<?php
/**
 * API: Export Delivery Zones to CSV
 * GET /api/admin/delivery-zones/export
 */

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/models/User.php';

// Check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Check if user is admin
$userModel = new User();
$user = $userModel->findById($_SESSION['user_id']);

if (!$user || $user->role !== 'admin') {
    header('Location: /login');
    exit;
}

try {
    $db = getDbConnection();
    
    $query = "SELECT 
                r.name AS 'Restaurant Name',
                r.phone AS 'Phone',
                r.address AS 'Address',
                r.city AS 'City',
                r.delivery_radius AS 'Delivery Radius (km)',
                r.delivery_fee AS 'Base Fee (XAF)',
                r.delivery_fee_per_extra_km AS 'Extra Fee per KM (XAF)',
                r.minimum_order AS 'Minimum Order (XAF)',
                CONCAT(r.delivery_radius * 2, ' km') AS 'Max Delivery Distance',
                r.status AS 'Status'
              FROM restaurants r
              WHERE r.status != 'deleted'
              ORDER BY r.name ASC";
    
    $stmt = $db->query($query);
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="delivery-zones-' . date('Y-m-d') . '.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write CSV header
    if (!empty($zones)) {
        fputcsv($output, array_keys($zones[0]));
        
        // Write data rows
        foreach ($zones as $zone) {
            fputcsv($output, $zone);
        }
    }
    
    fclose($output);
    
} catch (Exception $e) {
    error_log("Error exporting delivery zones: " . $e->getMessage());
    echo "Error exporting data";
}
?>

