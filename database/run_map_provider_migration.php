<?php
/**
 * Run Map Provider Migration
 * Adds map_provider setting to site_settings table
 * 
 * Usage:
 * - Local: php database/run_map_provider_migration.php
 * - Production: php database/run_map_provider_migration.php production
 */

// Determine environment
$environment = $argv[1] ?? 'local';
$isProduction = ($environment === 'production');

echo "========================================\n";
echo "Map Provider Migration Script\n";
echo "Environment: " . ($isProduction ? 'PRODUCTION' : 'LOCAL') . "\n";
echo "========================================\n\n";

// Load environment configuration
if ($isProduction) {
    echo "Loading production configuration...\n";
    $envFile = __DIR__ . '/../config/production.env';
    
    if (!file_exists($envFile)) {
        die("ERROR: Production environment file not found: $envFile\n");
    }
    
    // Parse .env file
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!empty($key)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
    
    $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
    $dbName = $_ENV['DB_NAME'] ?? 'time2eat';
    $dbUser = $_ENV['DB_USER'] ?? 'root';
    $dbPass = $_ENV['DB_PASS'] ?? '';
} else {
    echo "Loading local configuration...\n";

    // Load config.php first to define constants
    if (file_exists(__DIR__ . '/../config/config.php')) {
        require_once __DIR__ . '/../config/config.php';
    }

    // Then load database.php
    if (file_exists(__DIR__ . '/../config/database.php')) {
        require_once __DIR__ . '/../config/database.php';
    }

    $dbHost = defined('DB_HOST') ? DB_HOST : 'localhost';
    $dbName = defined('DB_NAME') ? DB_NAME : 'time2eat';
    $dbUser = defined('DB_USER') ? DB_USER : 'root';
    $dbPass = defined('DB_PASS') ? DB_PASS : '';
}

echo "Database: $dbName@$dbHost\n";
echo "User: $dbUser\n\n";

// Connect to database
try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✓ Connected to database successfully\n\n";
} catch (PDOException $e) {
    die("ERROR: Could not connect to database: " . $e->getMessage() . "\n");
}

// Check if site_settings table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
    if ($stmt->rowCount() === 0) {
        die("ERROR: site_settings table does not exist. Please run the main database setup first.\n");
    }
    echo "✓ site_settings table exists\n\n";
} catch (PDOException $e) {
    die("ERROR: Could not check for site_settings table: " . $e->getMessage() . "\n");
}

// Start migration
echo "Starting migration...\n";
echo "----------------------------------------\n\n";

try {
    $pdo->beginTransaction();
    
    // 1. Insert map_provider setting
    echo "1. Adding map_provider setting...\n";
    $stmt = $pdo->prepare("
        INSERT INTO site_settings (`key`, `value`, `type`, `group`, `description`, `created_at`, `updated_at`)
        VALUES (
            'map_provider',
            'leaflet',
            'string',
            'maps',
            'Map provider to use: \"google\" for Google Maps or \"leaflet\" for OpenStreetMap. Changing this will instantly switch all maps across the application.',
            NOW(),
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            `description` = 'Map provider to use: \"google\" for Google Maps or \"leaflet\" for OpenStreetMap. Changing this will instantly switch all maps across the application.',
            `updated_at` = NOW()
    ");
    $stmt->execute();
    echo "   ✓ map_provider setting added/updated\n\n";
    
    // 2. Update google_maps_api_key description
    echo "2. Updating google_maps_api_key description...\n";
    $stmt = $pdo->prepare("
        UPDATE site_settings
        SET `description` = 'Google Maps API key. Required if map_provider is set to \"google\". Get your API key from Google Cloud Console.',
            `updated_at` = NOW()
        WHERE `key` = 'google_maps_api_key'
    ");
    $stmt->execute();
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "   ✓ google_maps_api_key description updated\n\n";
    } else {
        echo "   ℹ google_maps_api_key setting not found (will be created when needed)\n\n";
    }
    
    // 3. Update mapbox_access_token description
    echo "3. Updating mapbox_access_token description...\n";
    $stmt = $pdo->prepare("
        UPDATE site_settings
        SET `description` = 'Mapbox access token (optional). Can be used for additional map features.',
            `updated_at` = NOW()
        WHERE `key` = 'mapbox_access_token'
    ");
    $stmt->execute();
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "   ✓ mapbox_access_token description updated\n\n";
    } else {
        echo "   ℹ mapbox_access_token setting not found (will be created when needed)\n\n";
    }
    
    // 4. Update default_latitude description
    echo "4. Updating default_latitude description...\n";
    $stmt = $pdo->prepare("
        UPDATE site_settings
        SET `description` = 'Default map center latitude (Bamenda, Cameroon: 5.9631)',
            `updated_at` = NOW()
        WHERE `key` = 'default_latitude'
    ");
    $stmt->execute();
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "   ✓ default_latitude description updated\n\n";
    } else {
        echo "   ℹ default_latitude setting not found (will be created when needed)\n\n";
    }
    
    // 5. Update default_longitude description
    echo "5. Updating default_longitude description...\n";
    $stmt = $pdo->prepare("
        UPDATE site_settings
        SET `description` = 'Default map center longitude (Bamenda, Cameroon: 10.1591)',
            `updated_at` = NOW()
        WHERE `key` = 'default_longitude'
    ");
    $stmt->execute();
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "   ✓ default_longitude description updated\n\n";
    } else {
        echo "   ℹ default_longitude setting not found (will be created when needed)\n\n";
    }
    
    // 6. Update default_zoom_level description
    echo "6. Updating default_zoom_level description...\n";
    $stmt = $pdo->prepare("
        UPDATE site_settings
        SET `description` = 'Default map zoom level (1-20, recommended: 13 for city view)',
            `updated_at` = NOW()
        WHERE `key` = 'default_zoom_level'
    ");
    $stmt->execute();
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "   ✓ default_zoom_level description updated\n\n";
    } else {
        echo "   ℹ default_zoom_level setting not found (will be created when needed)\n\n";
    }
    
    // 7. Update enable_location_tracking description
    echo "7. Updating enable_location_tracking description...\n";
    $stmt = $pdo->prepare("
        UPDATE site_settings
        SET `description` = 'Enable real-time GPS location tracking for riders and customers',
            `updated_at` = NOW()
        WHERE `key` = 'enable_location_tracking'
    ");
    $stmt->execute();
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "   ✓ enable_location_tracking description updated\n\n";
    } else {
        echo "   ℹ enable_location_tracking setting not found (will be created when needed)\n\n";
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "----------------------------------------\n";
    echo "✓ Migration completed successfully!\n\n";
    
    // Display current map settings
    echo "Current Map Settings:\n";
    echo "----------------------------------------\n";
    $stmt = $pdo->prepare("
        SELECT `key`, `value`, `type`, `description`
        FROM site_settings
        WHERE `group` = 'maps'
        ORDER BY 
            CASE `key`
                WHEN 'map_provider' THEN 1
                WHEN 'google_maps_api_key' THEN 2
                WHEN 'mapbox_access_token' THEN 3
                WHEN 'default_latitude' THEN 4
                WHEN 'default_longitude' THEN 5
                WHEN 'default_zoom_level' THEN 6
                WHEN 'enable_location_tracking' THEN 7
                ELSE 99
            END
    ");
    $stmt->execute();
    $settings = $stmt->fetchAll();
    
    if (empty($settings)) {
        echo "No map settings found in database.\n";
    } else {
        foreach ($settings as $setting) {
            echo "\n" . strtoupper($setting['key']) . ":\n";
            echo "  Value: " . ($setting['value'] ?: '(empty)') . "\n";
            echo "  Type: " . $setting['type'] . "\n";
            if (!empty($setting['description'])) {
                echo "  Description: " . $setting['description'] . "\n";
            }
        }
    }
    
    echo "\n----------------------------------------\n";
    echo "✓ Migration completed successfully!\n";
    echo "========================================\n\n";
    
    echo "Next Steps:\n";
    echo "1. Go to Admin Dashboard → Tools → Settings\n";
    echo "2. Scroll to Maps section\n";
    echo "3. Select your preferred map provider:\n";
    echo "   - Leaflet (OpenStreetMap) - Free, default\n";
    echo "   - Google Maps - Requires API key\n";
    echo "4. Click 'Save All'\n";
    echo "5. All maps will instantly switch!\n\n";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    die("\nERROR: Migration failed: " . $e->getMessage() . "\n");
}

