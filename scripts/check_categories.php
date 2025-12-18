<?php
require_once __DIR__ . '/../config/database.php';

$db = dbConnection();

echo "=== CHECKING CATEGORY TABLES ===\n\n";

// Check for tables with 'categ' in name
$stmt = $db->query("SHOW TABLES LIKE '%categ%'");
$tables = $stmt->fetchAll(PDO::FETCH_NUM);

echo "Tables found:\n";
foreach ($tables as $table) {
    echo "  - {$table[0]}\n";
}
echo "\n";

// Check categories table
if ($db->query("SHOW TABLES LIKE 'categories'")->rowCount() > 0) {
    echo "=== CATEGORIES TABLE ===\n";
    $stmt = $db->query("SELECT id, name FROM categories LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  ID: {$row['id']} - {$row['name']}\n";
    }
    echo "\n";
}

// Check menu_categories table
if ($db->query("SHOW TABLES LIKE 'menu_categories'")->rowCount() > 0) {
    echo "=== MENU_CATEGORIES TABLE ===\n";
    $stmt = $db->query("SELECT id, name FROM menu_categories LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  ID: {$row['id']} - {$row['name']}\n";
    }
    echo "\n";
}

// Check foreign key constraint
echo "=== FOREIGN KEY CONSTRAINTS ON menu_items ===\n";
$stmt = $db->query("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'time2eat'
    AND TABLE_NAME = 'menu_items'
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['CONSTRAINT_NAME']}: {$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
}

