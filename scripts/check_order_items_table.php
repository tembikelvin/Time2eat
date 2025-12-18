<?php

require_once __DIR__ . '/../config/database.php';

try {
    $db = dbConnection();
    
    echo "=== ORDER_ITEMS TABLE STRUCTURE ===\n\n";
    
    $stmt = $db->query('DESCRIBE order_items');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

