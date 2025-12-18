<?php
/**
 * Check and display restaurant GPS coordinates
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = dbConnection();
    
    $stmt = $db->query("
        SELECT id, name, latitude, longitude, address, city 
        FROM restaurants 
        WHERE status != 'deleted' 
        ORDER BY id
    ");
    
    echo "=== CURRENT RESTAURANT COORDINATES ===\n\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf(
            "ID: %d\nName: %s\nAddress: %s, %s\nLatitude: %s\nLongitude: %s\n\n",
            $row['id'],
            $row['name'],
            $row['address'] ?? 'N/A',
            $row['city'] ?? 'N/A',
            $row['latitude'] ?? 'NULL',
            $row['longitude'] ?? 'NULL'
        );
        echo str_repeat('-', 60) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

