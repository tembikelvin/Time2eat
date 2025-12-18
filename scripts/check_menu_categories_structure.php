<?php
require_once __DIR__ . '/../config/database.php';

$db = dbConnection();

echo "=== MENU_CATEGORIES TABLE STRUCTURE ===\n\n";

$stmt = $db->query("DESCRIBE menu_categories");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $column) {
    echo "{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}\n";
}

