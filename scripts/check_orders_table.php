<?php

require_once __DIR__ . '/../config/database.php';

try {
    $db = dbConnection();
    
    echo "=== ORDERS TABLE STRUCTURE ===\n\n";
    
    $stmt = $db->query('DESCRIBE orders');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

