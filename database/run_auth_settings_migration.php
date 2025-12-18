<?php
/**
 * Run Auth Settings Migration
 * Adds missing auth settings to production database
 * 
 * Usage:
 * - Local: php database/run_auth_settings_migration.php
 * - Production: php database/run_auth_settings_migration.php production
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Check if production environment is specified
$isProduction = isset($argv[1]) && $argv[1] === 'production';

if ($isProduction && file_exists(__DIR__ . '/../.env.production')) {
    // Load production environment
    $lines = file(__DIR__ . '/../.env.production', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
    echo "Running migration for PRODUCTION environment...\n";
} else {
    echo "Running migration for DEVELOPMENT environment...\n";
}

// Database connection parameters
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$dbname = $_ENV['DB_NAME'] ?? 'time2eat';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
    
    echo "✓ Connected to database: {$dbname}\n\n";
    
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
echo "Starting auth settings migration...\n";
echo "----------------------------------------\n\n";

try {
    // Check current auth settings
    echo "1. Checking current auth settings...\n";
    $stmt = $pdo->prepare("SELECT `key`, `value`, `type`, `description` FROM site_settings WHERE `group` = 'auth' ORDER BY `key`");
    $stmt->execute();
    $currentSettings = $stmt->fetchAll();
    
    echo "Current auth settings:\n";
    foreach ($currentSettings as $setting) {
        echo "  - {$setting['key']}: {$setting['value']} ({$setting['type']})\n";
    }
    echo "\n";
    
    // Add missing auth settings
    echo "2. Adding missing auth settings...\n";
    
    $missingSettings = [
        [
            'key' => 'auto_approve_customers',
            'value' => 'false',
            'type' => 'boolean',
            'group' => 'auth',
            'description' => 'Automatically approve customer accounts without admin review',
            'is_public' => false
        ],
        [
            'key' => 'email_verification_expiry',
            'value' => '2',
            'type' => 'integer',
            'group' => 'auth',
            'description' => 'Email verification token expiry in hours',
            'is_public' => false
        ],
        [
            'key' => 'email_verification_method',
            'value' => 'token',
            'type' => 'string',
            'group' => 'auth',
            'description' => 'Email verification method: token or code',
            'is_public' => false
        ],
        [
            'key' => 'registration_enabled',
            'value' => 'true',
            'type' => 'boolean',
            'group' => 'auth',
            'description' => 'Allow new user registrations',
            'is_public' => true
        ]
    ];
    
    $addedCount = 0;
    foreach ($missingSettings as $setting) {
        // Check if setting already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE `key` = ? AND `group` = ?");
        $stmt->execute([$setting['key'], $setting['group']]);
        $exists = $stmt->fetchColumn() > 0;
        
        if (!$exists) {
            $stmt = $pdo->prepare("
                INSERT INTO site_settings (`key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $setting['key'],
                $setting['value'],
                $setting['type'],
                $setting['group'],
                $setting['description'],
                $setting['is_public']
            ]);
            echo "  ✓ Added: {$setting['key']}\n";
            $addedCount++;
        } else {
            echo "  - Already exists: {$setting['key']}\n";
        }
    }
    
    echo "\n3. Updating existing auth settings descriptions...\n";
    
    // Update existing settings descriptions
    $updates = [
        ['key' => 'allow_registration', 'description' => 'Allow new user registration'],
        ['key' => 'email_verification_required', 'description' => 'Require email verification for new user registrations']
    ];
    
    foreach ($updates as $update) {
        $stmt = $pdo->prepare("
            UPDATE site_settings 
            SET `description` = ?, `updated_at` = NOW()
            WHERE `key` = ? AND `group` = 'auth'
        ");
        $stmt->execute([$update['description'], $update['key']]);
        echo "  ✓ Updated description for: {$update['key']}\n";
    }
    
    echo "\n4. Verifying final auth settings...\n";
    $stmt = $pdo->prepare("SELECT `key`, `value`, `type`, `description` FROM site_settings WHERE `group` = 'auth' ORDER BY `key`");
    $stmt->execute();
    $finalSettings = $stmt->fetchAll();
    
    echo "Final auth settings:\n";
    foreach ($finalSettings as $setting) {
        echo "  - {$setting['key']}: {$setting['value']} ({$setting['type']})\n";
        echo "    Description: {$setting['description']}\n";
    }
    
    echo "\n🎉 Migration completed successfully!\n";
    echo "Added {$addedCount} new auth settings.\n";
    echo "Total auth settings: " . count($finalSettings) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
