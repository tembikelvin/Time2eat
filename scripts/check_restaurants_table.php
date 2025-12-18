<?php
require_once __DIR__ . '/../config/database.php';

$db = dbConnection();

echo "=== RESTAURANTS TABLE STRUCTURE ===\n\n";

$stmt = $db->query('DESCRIBE restaurants');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== SAMPLE RESTAURANT DATA ===\n\n";

$stmt = $db->query('SELECT * FROM restaurants LIMIT 1');
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if ($restaurant) {
    foreach ($restaurant as $key => $value) {
        echo "$key: $value\n";
    }
}

