<?php
/**
 * Production Migration Runner: Fix Restaurant Coordinates
 * 
 * This script runs the restaurant coordinates migration on production databases.
 * It reads environment variables and is safe to run multiple times.
 */

// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("Environment file not found: $path");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Try to load environment file
$envFiles = ['.env.production', '.env', 'config/.env.production', 'config/.env'];
$envLoaded = false;

foreach ($envFiles as $envFile) {
    if (file_exists($envFile)) {
        try {
            loadEnv($envFile);
            $envLoaded = true;
            echo "✅ Loaded environment from: $envFile\n";
        } catch (Exception $e) {
            echo "⚠️  Could not load $envFile: " . $e->getMessage() . "\n";
        }
    }
}

if (!$envLoaded) {
    echo "⚠️  No environment file found. Using default values.\n";
}

// Configuration with environment variable fallbacks
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

echo "🚀 Starting Production Migration: Fix Restaurant Coordinates\n";
echo "============================================================\n\n";

echo "📋 Configuration:\n";
echo "   Host: {$config['host']}\n";
echo "   Database: {$config['database']}\n";
echo "   Username: {$config['username']}\n";
echo "   Password: " . (empty($config['password']) ? '(empty)' : str_repeat('*', strlen($config['password']))) . "\n\n";

try {
    // Create database connection
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
    ]);
    
    echo "✅ Database connection established\n";
    
    // Check current column status
    $stmt = $pdo->query("
        SELECT 
            COLUMN_NAME,
            IS_NULLABLE,
            DATA_TYPE,
            COLUMN_DEFAULT
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'restaurants' 
        AND COLUMN_NAME IN ('latitude', 'longitude')
        ORDER BY COLUMN_NAME
    ");
    
    $columns = $stmt->fetchAll();
    echo "📊 Current column status:\n";
    foreach ($columns as $column) {
        echo "   {$column['COLUMN_NAME']}: {$column['DATA_TYPE']}, Nullable: {$column['IS_NULLABLE']}, Default: {$column['COLUMN_DEFAULT']}\n";
    }
    echo "\n";
    
    // Check if migration is needed
    $latitudeNullable = false;
    $longitudeNullable = false;
    
    foreach ($columns as $column) {
        if ($column['COLUMN_NAME'] === 'latitude' && $column['IS_NULLABLE'] === 'YES') {
            $latitudeNullable = true;
        }
        if ($column['COLUMN_NAME'] === 'longitude' && $column['IS_NULLABLE'] === 'YES') {
            $longitudeNullable = true;
        }
    }
    
    if ($latitudeNullable && $longitudeNullable) {
        echo "ℹ️  Columns are already nullable. No changes needed.\n";
    } else {
        echo "🔧 Making columns nullable...\n";
        
        // Make latitude nullable
        if (!$latitudeNullable) {
            $pdo->exec("ALTER TABLE `restaurants` MODIFY COLUMN `latitude` decimal(10,8) NULL DEFAULT NULL");
            echo "✅ Made latitude nullable\n";
        } else {
            echo "ℹ️  Latitude already nullable\n";
        }
        
        // Make longitude nullable
        if (!$longitudeNullable) {
            $pdo->exec("ALTER TABLE `restaurants` MODIFY COLUMN `longitude` decimal(11,8) NULL DEFAULT NULL");
            echo "✅ Made longitude nullable\n";
        } else {
            echo "ℹ️  Longitude already nullable\n";
        }
    }
    
    // Update restaurants with NULL coordinates
    echo "🔄 Updating restaurants with NULL coordinates...\n";
    
    $stmt = $pdo->prepare("UPDATE `restaurants` 
        SET `latitude` = 5.9631, `longitude` = 10.1591 
        WHERE `latitude` IS NULL OR `longitude` IS NULL");
    $stmt->execute();
    
    $updatedCount = $stmt->rowCount();
    echo "✅ Updated $updatedCount restaurants with default coordinates\n";
    
    // Get final statistics
    $restaurantCount = $pdo->query("SELECT COUNT(*) FROM restaurants WHERE latitude IS NOT NULL AND longitude IS NOT NULL")->fetchColumn();
    $totalRestaurants = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
    
    echo "📊 Final statistics:\n";
    echo "   - Restaurants with coordinates: $restaurantCount\n";
    echo "   - Total restaurants: $totalRestaurants\n";
    
    // Log the migration
    try {
        $pdo->exec("
            INSERT INTO `logs` (`level`, `message`, `context`, `created_at`) 
            VALUES ('info', 'Restaurant coordinates migration completed', 
                    JSON_OBJECT(
                        'migration', '021_fix_restaurant_coordinates_nullable_production',
                        'restaurants_updated', $updatedCount,
                        'total_restaurants', $totalRestaurants,
                        'restaurants_with_coordinates', $restaurantCount,
                        'latitude_nullable', " . ($latitudeNullable ? 'true' : 'false') . ",
                        'longitude_nullable', " . ($longitudeNullable ? 'true' : 'false') . ",
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
    echo "Restaurant profile creation should now work without errors.\n";
    echo "All restaurants have valid coordinates for mapping.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
