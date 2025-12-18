#!/usr/bin/env php
<?php
/**
 * Order Columns Migration Runner
 * 
 * This script adds missing payment and confirmation columns to the orders table.
 * Safe to run in both development and production environments.
 * 
 * Usage:
 *   php database/run_order_columns_migration.php
 * 
 * Or via web:
 *   http://yoursite.com/database/run_order_columns_migration.php
 */

// Determine if running from CLI or web
$isCLI = php_sapi_name() === 'cli';

// Define BASE_PATH
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load database configuration
require_once BASE_PATH . '/config/database.php';

// Output helper functions
function output($message, $type = 'info') {
    global $isCLI;
    
    $colors = [
        'success' => $isCLI ? "\033[32m" : '<span style="color: green;">',
        'error' => $isCLI ? "\033[31m" : '<span style="color: red;">',
        'warning' => $isCLI ? "\033[33m" : '<span style="color: orange;">',
        'info' => $isCLI ? "\033[36m" : '<span style="color: blue;">',
        'reset' => $isCLI ? "\033[0m" : '</span>',
    ];
    
    $prefix = [
        'success' => '✅ ',
        'error' => '❌ ',
        'warning' => '⚠️  ',
        'info' => 'ℹ️  ',
    ];
    
    if ($isCLI) {
        echo $colors[$type] . $prefix[$type] . $message . $colors['reset'] . PHP_EOL;
    } else {
        echo '<div style="padding: 10px; margin: 5px 0; border-left: 4px solid ' . 
             ($type === 'success' ? 'green' : ($type === 'error' ? 'red' : ($type === 'warning' ? 'orange' : 'blue'))) . 
             '; background: #f9f9f9;">' . 
             $prefix[$type] . htmlspecialchars($message) . 
             '</div>';
    }
}

function outputHeader($message) {
    global $isCLI;
    
    if ($isCLI) {
        echo PHP_EOL;
        echo "============================================================================" . PHP_EOL;
        echo "  " . $message . PHP_EOL;
        echo "============================================================================" . PHP_EOL;
    } else {
        echo '<h2 style="margin-top: 20px; padding: 10px; background: #333; color: white; border-radius: 5px;">' . 
             htmlspecialchars($message) . 
             '</h2>';
    }
}

// Start output
if (!$isCLI) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Order Columns Migration</title>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #333; color: white; }
            tr:hover { background: #f5f5f5; }
            code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        </style>
    </head>
    <body>
    <div class="container">';
    
    echo '<h1>🔧 Order Columns Migration</h1>';
    echo '<p><strong>Date:</strong> ' . date('Y-m-d H:i:s') . '</p>';
    echo '<p><strong>Environment:</strong> ' . (getenv('APP_ENV') ?: 'development') . '</p>';
}

outputHeader('Starting Migration');

try {
    // Connect to database
    output('Connecting to database...', 'info');
    $db = dbConnection();
    output('Database connection successful', 'success');
    
    // Get current database name
    $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
    output('Database: ' . $dbName, 'info');
    
    // Read migration file
    $migrationFile = BASE_PATH . '/database/migrations/add_missing_order_columns.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception('Migration file not found: ' . $migrationFile);
    }
    
    output('Reading migration file...', 'info');
    $sql = file_get_contents($migrationFile);
    output('Migration file loaded (' . strlen($sql) . ' bytes)', 'success');
    
    // Execute migration
    outputHeader('Executing Migration');
    
    output('Running SQL statements...', 'info');
    
    // Execute the entire migration as one script
    // This handles the prepared statements correctly
    $db->exec($sql);
    
    output('Migration executed successfully', 'success');
    
    // Verify columns were added
    outputHeader('Verification');
    
    $stmt = $db->query("
        SELECT 
            COLUMN_NAME, 
            DATA_TYPE, 
            IS_NULLABLE, 
            COLUMN_DEFAULT,
            COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = '$dbName'
          AND TABLE_NAME = 'orders'
          AND COLUMN_NAME IN ('payment_method', 'payment_status', 'payment_transaction_id', 'customer_confirmed', 'customer_confirmed_at')
        ORDER BY ORDINAL_POSITION
    ");
    
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns) === 5) {
        output('All 5 columns verified successfully!', 'success');
        
        if (!$isCLI) {
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>Column Name</th>';
            echo '<th>Data Type</th>';
            echo '<th>Nullable</th>';
            echo '<th>Default Value</th>';
            echo '<th>Comment</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            
            foreach ($columns as $col) {
                echo '<tr>';
                echo '<td><code>' . htmlspecialchars($col['COLUMN_NAME']) . '</code></td>';
                echo '<td>' . htmlspecialchars($col['DATA_TYPE']) . '</td>';
                echo '<td>' . htmlspecialchars($col['IS_NULLABLE']) . '</td>';
                echo '<td>' . htmlspecialchars($col['COLUMN_DEFAULT'] ?? 'NULL') . '</td>';
                echo '<td>' . htmlspecialchars($col['COLUMN_COMMENT'] ?? '') . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo PHP_EOL;
            printf("%-30s %-15s %-10s %-20s %s\n", 'Column Name', 'Data Type', 'Nullable', 'Default', 'Comment');
            echo str_repeat('-', 120) . PHP_EOL;
            
            foreach ($columns as $col) {
                printf(
                    "%-30s %-15s %-10s %-20s %s\n",
                    $col['COLUMN_NAME'],
                    $col['DATA_TYPE'],
                    $col['IS_NULLABLE'],
                    $col['COLUMN_DEFAULT'] ?? 'NULL',
                    $col['COLUMN_COMMENT'] ?? ''
                );
            }
            echo PHP_EOL;
        }
    } else {
        output('Warning: Expected 5 columns, found ' . count($columns), 'warning');
    }
    
    // Check indexes
    $stmt = $db->query("
        SELECT 
            INDEX_NAME,
            COLUMN_NAME,
            SEQ_IN_INDEX
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = '$dbName'
          AND TABLE_NAME = 'orders'
          AND INDEX_NAME IN ('idx_payment_status', 'idx_customer_confirmed')
        ORDER BY INDEX_NAME, SEQ_IN_INDEX
    ");
    
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($indexes) > 0) {
        output('Indexes created: ' . count($indexes) . ' index columns', 'success');
    }
    
    // Count existing orders
    $orderCount = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    output('Total orders in database: ' . $orderCount, 'info');
    
    // Update existing orders
    if ($orderCount > 0) {
        output('Updating existing orders with default values...', 'info');
        $updated = $db->exec("
            UPDATE orders 
            SET 
                payment_method = COALESCE(payment_method, 'cash_on_delivery'),
                payment_status = COALESCE(payment_status, 'pending')
            WHERE payment_method IS NULL OR payment_status IS NULL
        ");
        output('Updated ' . $updated . ' existing orders', 'success');
    }
    
    outputHeader('Migration Complete');
    output('All columns have been added successfully!', 'success');
    output('The orders table is now ready for checkout operations.', 'success');
    
    if (!$isCLI) {
        echo '<div style="margin-top: 30px; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">';
        echo '<h3 style="color: #155724; margin-top: 0;">✅ Migration Successful!</h3>';
        echo '<p style="color: #155724;">You can now:</p>';
        echo '<ul style="color: #155724;">';
        echo '<li>Test checkout functionality</li>';
        echo '<li>Place orders with payment methods</li>';
        echo '<li>Use customer receipt confirmation</li>';
        echo '</ul>';
        echo '<p><a href="../public/test-checkout-debug.php" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">Go to Checkout Debug Tool</a></p>';
        echo '</div>';
    }
    
} catch (PDOException $e) {
    output('Database error: ' . $e->getMessage(), 'error');
    output('Error code: ' . $e->getCode(), 'error');
    
    if (!$isCLI) {
        echo '<div style="margin-top: 20px; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;">';
        echo '<h3 style="color: #721c24; margin-top: 0;">❌ Migration Failed</h3>';
        echo '<p style="color: #721c24;"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p style="color: #721c24;"><strong>Code:</strong> ' . htmlspecialchars($e->getCode()) . '</p>';
        echo '</div>';
    }
    
    exit(1);
    
} catch (Exception $e) {
    output('Error: ' . $e->getMessage(), 'error');
    
    if (!$isCLI) {
        echo '<div style="margin-top: 20px; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;">';
        echo '<h3 style="color: #721c24; margin-top: 0;">❌ Migration Failed</h3>';
        echo '<p style="color: #721c24;">' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
    
    exit(1);
}

if (!$isCLI) {
    echo '</div></body></html>';
}

exit(0);

