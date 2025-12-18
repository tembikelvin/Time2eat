<?php
/**
 * Production Migration Runner
 * 
 * This script runs the restaurant category_id migration on production databases.
 * It's designed to be safe and can be run multiple times without issues.
 */

// Configuration - Update these for your production environment
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_NAME'] ?? 'time2eat',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4'
];

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🚀 Starting Production Migration: Add Restaurant Category ID\n";
echo "============================================================\n\n";

try {
    // Create database connection
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
    ]);
    
    echo "✅ Database connection established\n";
    
    // Check if migration has already been run
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'restaurants' 
        AND COLUMN_NAME = 'category_id'
    ");
    $columnExists = $stmt->fetch()['count'] > 0;
    
    if ($columnExists) {
        echo "ℹ️  Column 'category_id' already exists in restaurants table\n";
    } else {
        echo "➕ Adding category_id column to restaurants table...\n";
        
        // Add the column and constraints
        $pdo->exec("
            ALTER TABLE `restaurants` 
            ADD COLUMN `category_id` bigint(20) UNSIGNED NULL DEFAULT NULL AFTER `cuisine_type`,
            ADD INDEX `idx_restaurants_category` (`category_id`),
            ADD CONSTRAINT `restaurants_category_id_foreign` 
                FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
        ");
        
        echo "✅ Column and constraints added successfully\n";
    }
    
    // Insert default categories
    echo "📂 Adding default categories...\n";
    
    $categories = [
        ['id' => 1, 'name' => 'Other', 'slug' => 'other', 'description' => 'Miscellaneous restaurants', 'featured' => 0, 'sort' => 100],
        ['id' => 2, 'name' => 'Cameroonian', 'slug' => 'cameroonian', 'description' => 'Traditional Cameroonian cuisine', 'featured' => 1, 'sort' => 1],
        ['id' => 3, 'name' => 'Fast Food', 'slug' => 'fast-food', 'description' => 'Quick service restaurants', 'featured' => 1, 'sort' => 2],
        ['id' => 4, 'name' => 'Pizza', 'slug' => 'pizza', 'description' => 'Pizza and Italian cuisine', 'featured' => 1, 'sort' => 3],
        ['id' => 5, 'name' => 'Chinese', 'slug' => 'chinese', 'description' => 'Chinese cuisine', 'featured' => 0, 'sort' => 4],
        ['id' => 6, 'name' => 'Indian', 'slug' => 'indian', 'description' => 'Indian cuisine', 'featured' => 0, 'sort' => 5],
        ['id' => 7, 'name' => 'Continental', 'slug' => 'continental', 'description' => 'Continental cuisine', 'featured' => 0, 'sort' => 6],
        ['id' => 8, 'name' => 'Bakery', 'slug' => 'bakery', 'description' => 'Bakery and pastry shops', 'featured' => 0, 'sort' => 7],
        ['id' => 9, 'name' => 'Beverages', 'slug' => 'beverages', 'description' => 'Drinks and beverages', 'featured' => 0, 'sort' => 8]
    ];
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO `categories` 
        (`id`, `name`, `slug`, `description`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`) 
        VALUES (?, ?, ?, ?, 1, ?, ?, NOW(), NOW())
    ");
    
    foreach ($categories as $category) {
        $stmt->execute([
            $category['id'],
            $category['name'],
            $category['slug'],
            $category['description'],
            $category['featured'],
            $category['sort']
        ]);
    }
    
    echo "✅ Default categories added\n";
    
    // Update existing restaurants with category mappings
    echo "🔄 Updating restaurant categories...\n";
    
    $mappings = [
        'Cameroonian' => ['%cameroon%', '%african%', '%traditional%'],
        'Fast Food' => ['%fast%', '%quick%'],
        'Pizza' => ['%pizza%', '%italian%'],
        'Chinese' => ['%chinese%', '%asian%'],
        'Indian' => ['%indian%', '%curry%'],
        'Continental' => ['%continental%', '%european%'],
        'Bakery' => ['%bakery%', '%pastry%', '%bread%'],
        'Beverages' => ['%beverage%', '%drink%', '%bar%']
    ];
    
    foreach ($mappings as $categoryName => $patterns) {
        $categoryId = $pdo->query("SELECT id FROM categories WHERE name = '$categoryName'")->fetchColumn();
        if ($categoryId) {
            foreach ($patterns as $pattern) {
                $stmt = $pdo->prepare("
                    UPDATE restaurants 
                    SET category_id = ? 
                    WHERE cuisine_type LIKE ? AND category_id IS NULL
                ");
                $stmt->execute([$categoryId, $pattern]);
            }
        }
    }
    
    // Assign remaining restaurants to "Other" category
    $otherId = $pdo->query("SELECT id FROM categories WHERE name = 'Other'")->fetchColumn();
    if ($otherId) {
        $stmt = $pdo->prepare("UPDATE restaurants SET category_id = ? WHERE category_id IS NULL");
        $stmt->execute([$otherId]);
    }
    
    // Get final statistics
    $restaurantCount = $pdo->query("SELECT COUNT(*) FROM restaurants WHERE category_id IS NOT NULL")->fetchColumn();
    $categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    
    echo "✅ Restaurant categories updated\n";
    echo "📊 Statistics:\n";
    echo "   - Restaurants with categories: $restaurantCount\n";
    echo "   - Total categories: $categoryCount\n";
    
    // Log the migration
    try {
        $pdo->exec("
            INSERT INTO `logs` (`level`, `message`, `context`, `created_at`) 
            VALUES ('info', 'Restaurant category_id migration completed', 
                    JSON_OBJECT(
                        'migration', '020_add_restaurant_category_id_production',
                        'restaurants_updated', $restaurantCount,
                        'categories_created', $categoryCount,
                        'timestamp', NOW()
                    ), 
                    NOW())
        ");
        echo "📝 Migration logged successfully\n";
    } catch (Exception $e) {
        echo "⚠️  Warning: Could not log migration (logs table may not exist): " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 Migration completed successfully!\n";
    echo "============================================================\n";
    echo "The restaurant category system is now fully functional.\n";
    echo "You can now use category-based filtering and browsing.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
