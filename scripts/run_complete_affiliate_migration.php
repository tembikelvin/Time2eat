<?php
/**
 * Complete Affiliate Migration Script for Production
 * This script runs both missing affiliate records and mismatch fixes
 */

require_once __DIR__ . '/../config/config.php';

// Load production environment variables
if (file_exists(__DIR__ . '/../.env.production')) {
    $lines = file(__DIR__ . '/../.env.production', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Database configuration
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'time2eat';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "🔗 Connected to database: $dbname\n";
    echo "🚀 Starting complete affiliate migration...\n\n";

    // Step 1: Create migration log table
    echo "📋 Step 1: Creating migration log table...\n";
    $migrationLogSql = file_get_contents(__DIR__ . '/../database/migrations/create_migration_log_table.sql');
    $pdo->exec($migrationLogSql);
    echo "✅ Migration log table created\n\n";

    // Step 2: Fix missing affiliate records
    echo "📋 Step 2: Fixing missing affiliate records...\n";
    $missingRecordsSql = file_get_contents(__DIR__ . '/../database/migrations/fix_missing_affiliate_records_production.sql');
    $statements = array_filter(array_map('trim', explode(';', $missingRecordsSql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            if (strpos($statement, 'INSERT INTO affiliates') !== false) {
                echo "✅ Created missing affiliate records\n";
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Table') !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
                echo "⚠ Skipped (table doesn't exist): " . substr($statement, 0, 50) . "...\n";
                continue;
            }
            throw $e;
        }
    }

    // Step 3: Fix affiliate code mismatches
    echo "📋 Step 3: Fixing affiliate code mismatches...\n";
    $mismatchSql = file_get_contents(__DIR__ . '/../database/migrations/fix_affiliate_code_mismatches_production.sql');
    $statements = array_filter(array_map('trim', explode(';', $mismatchSql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            if (strpos($statement, 'UPDATE users') !== false) {
                echo "✅ Fixed affiliate code mismatches\n";
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Table') !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
                echo "⚠ Skipped (table doesn't exist): " . substr($statement, 0, 50) . "...\n";
                continue;
            }
            throw $e;
        }
    }

    echo "\n🎉 Complete affiliate migration finished successfully!\n";
    
    // Final verification
    echo "\n📊 Final Verification:\n";
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_affiliates,
            COUNT(CASE WHEN u.affiliate_code = a.affiliate_code THEN 1 END) as synced_affiliates,
            COUNT(CASE WHEN u.affiliate_code != a.affiliate_code THEN 1 END) as mismatched_affiliates,
            COUNT(CASE WHEN u.affiliate_code IS NOT NULL AND a.id IS NULL THEN 1 END) as missing_records
        FROM users u
        LEFT JOIN affiliates a ON u.id = a.user_id
        WHERE u.affiliate_code IS NOT NULL
          AND u.role = 'customer'
          AND u.deleted_at IS NULL
    ");
    
    $result = $stmt->fetch();
    echo "Total affiliates: " . $result['total_affiliates'] . "\n";
    echo "Synced affiliates: " . $result['synced_affiliates'] . "\n";
    echo "Mismatched affiliates: " . $result['mismatched_affiliates'] . "\n";
    echo "Missing records: " . $result['missing_records'] . "\n";

    if ($result['synced_affiliates'] == $result['total_affiliates'] && $result['mismatched_affiliates'] == 0 && $result['missing_records'] == 0) {
        echo "\n✅ All affiliate data is perfectly synchronized!\n";
    } else {
        echo "\n⚠ Some issues remain. Please check the data manually.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
