<?php
/**
 * Populate menu_categories table with default categories for each restaurant
 * 
 * This script creates restaurant-specific menu categories based on the global categories table
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = dbConnection();
    
    echo "=== POPULATING MENU_CATEGORIES TABLE ===\n\n";
    
    // Get all active restaurants
    $stmt = $db->query("
        SELECT id, name 
        FROM restaurants 
        WHERE status != 'deleted'
        ORDER BY id
    ");
    
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($restaurants)) {
        echo "❌ No restaurants found.\n";
        exit(1);
    }
    
    echo "Found " . count($restaurants) . " restaurants.\n\n";
    
    // Get all global categories
    $stmt = $db->query("
        SELECT id, name, description
        FROM categories
        ORDER BY id
    ");

    $globalCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($globalCategories)) {
        echo "❌ No global categories found.\n";
        exit(1);
    }

    echo "Found " . count($globalCategories) . " global categories.\n\n";

    // Prepare insert statement
    $insertStmt = $db->prepare("
        INSERT INTO menu_categories
        (restaurant_id, name, description, is_active, sort_order, created_at, updated_at)
        VALUES
        (:restaurant_id, :name, :description, 1, :sort_order, NOW(), NOW())
    ");
    
    $totalCreated = 0;
    $totalSkipped = 0;
    
    // For each restaurant, create menu categories
    foreach ($restaurants as $restaurant) {
        echo "Processing: {$restaurant['name']} (ID: {$restaurant['id']})\n";
        
        // Check if restaurant already has categories
        $checkStmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM menu_categories 
            WHERE restaurant_id = ?
        ");
        $checkStmt->execute([$restaurant['id']]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing['count'] > 0) {
            echo "  ⚠️  Already has {$existing['count']} categories - skipping\n\n";
            $totalSkipped++;
            continue;
        }
        
        // Create categories for this restaurant
        $sortOrder = 1;
        foreach ($globalCategories as $category) {
            try {
                $insertStmt->execute([
                    'restaurant_id' => $restaurant['id'],
                    'name' => $category['name'],
                    'description' => $category['description'] ?? null,
                    'sort_order' => $sortOrder++
                ]);

                echo "  ✓ Created: {$category['name']}\n";
                $totalCreated++;

            } catch (PDOException $e) {
                echo "  ❌ Failed to create {$category['name']}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    echo str_repeat('=', 60) . "\n";
    echo "✅ COMPLETED!\n";
    echo "  - Total categories created: $totalCreated\n";
    echo "  - Restaurants skipped: $totalSkipped\n\n";
    
    // Verify results
    echo "=== VERIFICATION ===\n\n";
    
    foreach ($restaurants as $restaurant) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM menu_categories 
            WHERE restaurant_id = ?
        ");
        $stmt->execute([$restaurant['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "{$restaurant['name']}: {$result['count']} categories\n";
    }
    
    echo "\n✓ Menu categories populated successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

