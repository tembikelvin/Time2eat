<?php
/**
 * Database Migration Runner for Order Tracking Tables
 * Run this script to create the necessary tables for order tracking functionality
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Read the migration SQL file
    $migrationFile = __DIR__ . '/migrations/order_tracking_tables.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    if ($sql === false) {
        throw new Exception("Failed to read migration file");
    }
    
    // Connect to database
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'time2eat';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Connected to database successfully.\n";
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    echo "Found " . count($statements) . " SQL statements to execute.\n\n";
    
    // Execute each statement
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // Extract table name for better feedback
            if (preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✓ Created table: {$matches[1]}\n";
            } elseif (preg_match('/ALTER TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✓ Modified table: {$matches[1]}\n";
            } elseif (preg_match('/CREATE INDEX.*?ON.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✓ Created index on table: {$matches[1]}\n";
            } elseif (preg_match('/INSERT.*?INTO.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✓ Inserted data into table: {$matches[1]}\n";
            } else {
                echo "✓ Executed statement " . ($index + 1) . "\n";
            }
            
        } catch (PDOException $e) {
            $errorCount++;
            echo "✗ Error in statement " . ($index + 1) . ": " . $e->getMessage() . "\n";
            
            // Show first 100 characters of the problematic statement
            echo "   Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Migration completed!\n";
    echo "✓ Successful statements: $successCount\n";
    echo "✗ Failed statements: $errorCount\n";
    
    if ($errorCount === 0) {
        echo "\n🎉 All order tracking tables created successfully!\n";
        echo "You can now use the comprehensive order tracking features.\n";
    } else {
        echo "\n⚠️  Some statements failed. Please check the errors above.\n";
    }
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
