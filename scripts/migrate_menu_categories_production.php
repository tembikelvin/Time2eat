<?php
/**
 * Production-safe migration script for menu_categories
 * 
 * This script can be run on both development and production environments
 * It will populate the menu_categories table if it's empty
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = dbConnection();
    
    // Detect environment
    $isProduction = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'time2eat.org') !== false) 
                    || (php_sapi_name() === 'cli' && getenv('APP_ENV') === 'production');
    
    $envName = $isProduction ? 'PRODUCTION' : 'DEVELOPMENT';
    
    echo str_repeat('=', 70) . "\n";
    echo "MENU CATEGORIES MIGRATION SCRIPT\n";
    echo "Environment: $envName\n";
    echo str_repeat('=', 70) . "\n\n";
    
    // Check if menu_categories table exists
    $stmt = $db->query("SHOW TABLES LIKE 'menu_categories'");
    if ($stmt->rowCount() === 0) {
        echo "❌ ERROR: menu_categories table does not exist!\n";
        echo "Please create the table first.\n";
        exit(1);
    }
    
    echo "✓ menu_categories table exists\n\n";
    
    // Check current state
    $stmt = $db->query("SELECT COUNT(*) as count FROM menu_categories");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $existingCount = $result['count'];
    
    echo "Current menu_categories count: $existingCount\n\n";
    
    if ($existingCount > 0) {
        echo "⚠️  menu_categories table already has data.\n";
        echo "Do you want to continue and add categories for restaurants that don't have any? (y/n): ";
        
        if (php_sapi_name() === 'cli') {
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            $response = trim($line);
            fclose($handle);
            
            if (strtolower($response) !== 'y') {
                echo "Migration cancelled.\n";
                exit(0);
            }
        } else {
            echo "\nRunning in non-interactive mode. Proceeding with migration...\n";
        }
    }
    
    echo "\n" . str_repeat('-', 70) . "\n";
    echo "STARTING MIGRATION\n";
    echo str_repeat('-', 70) . "\n\n";
    
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
    $errors = [];
    
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
        $createdForRestaurant = 0;
        
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
                $createdForRestaurant++;
                
            } catch (PDOException $e) {
                $errorMsg = "Failed to create {$category['name']} for {$restaurant['name']}: " . $e->getMessage();
                echo "  ❌ $errorMsg\n";
                $errors[] = $errorMsg;
            }
        }
        
        echo "  → Created $createdForRestaurant categories\n\n";
    }
    
    echo str_repeat('=', 70) . "\n";
    echo "MIGRATION COMPLETED!\n";
    echo str_repeat('=', 70) . "\n\n";
    
    echo "Summary:\n";
    echo "  - Total categories created: $totalCreated\n";
    echo "  - Restaurants skipped: $totalSkipped\n";
    echo "  - Errors: " . count($errors) . "\n\n";
    
    if (!empty($errors)) {
        echo "Errors encountered:\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
        echo "\n";
    }
    
    // Verify results
    echo str_repeat('-', 70) . "\n";
    echo "VERIFICATION\n";
    echo str_repeat('-', 70) . "\n\n";
    
    foreach ($restaurants as $restaurant) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM menu_categories 
            WHERE restaurant_id = ?
        ");
        $stmt->execute([$restaurant['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $status = $result['count'] > 0 ? '✓' : '❌';
        echo "$status {$restaurant['name']}: {$result['count']} categories\n";
    }
    
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "✅ Migration completed successfully!\n";
    echo str_repeat('=', 70) . "\n";
    
} catch (Exception $e) {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "❌ MIGRATION FAILED!\n";
    echo str_repeat('=', 70) . "\n\n";
    echo "ERROR: " . $e->getMessage() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

