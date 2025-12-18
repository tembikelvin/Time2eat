<?php
/**
 * Production Migration Script
 * Ensures order_items table has 'variants' column (not 'customizations')
 * 
 * This script is safe to run multiple times - it checks before making changes
 */

require_once __DIR__ . '/../config/database.php';

function output($message, $type = 'info') {
    $colors = [
        'info' => "\033[0;36m",    // Cyan
        'success' => "\033[0;32m", // Green
        'warning' => "\033[0;33m", // Yellow
        'error' => "\033[0;31m",   // Red
        'reset' => "\033[0m"
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    echo $color . $message . $colors['reset'] . "\n";
}

try {
    $db = dbConnection();
    
    output("=== ORDER ITEMS COLUMN FIX - PRODUCTION ===", 'info');
    output("");
    
    // Check current table structure
    output("1. Checking order_items table structure...", 'info');
    $stmt = $db->query('DESCRIBE order_items');
    $columns = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[$row['Field']] = $row;
    }
    
    $hasVariants = isset($columns['variants']);
    $hasCustomizations = isset($columns['customizations']);
    
    output("   - Has 'variants' column: " . ($hasVariants ? 'YES' : 'NO'), $hasVariants ? 'success' : 'warning');
    output("   - Has 'customizations' column: " . ($hasCustomizations ? 'YES' : 'NO'), $hasCustomizations ? 'warning' : 'success');
    output("");
    
    // If table already has correct structure, exit
    if ($hasVariants && !$hasCustomizations) {
        output("✓ Table structure is already correct!", 'success');
        output("  order_items has 'variants' column and no 'customizations' column", 'success');
        output("");
        output("No changes needed. Exiting.", 'info');
        exit(0);
    }
    
    // If table has customizations but not variants, we need to rename
    if ($hasCustomizations && !$hasVariants) {
        output("2. Renaming 'customizations' column to 'variants'...", 'warning');
        
        $db->exec("ALTER TABLE order_items CHANGE COLUMN customizations variants JSON DEFAULT NULL COMMENT 'Selected variants/customizations'");
        
        output("   ✓ Column renamed successfully!", 'success');
        output("");
    }
    
    // If table has neither, add variants column
    if (!$hasVariants && !$hasCustomizations) {
        output("2. Adding 'variants' column...", 'warning');
        
        $db->exec("ALTER TABLE order_items ADD COLUMN variants JSON DEFAULT NULL COMMENT 'Selected variants/customizations' AFTER total_price");
        
        output("   ✓ Column added successfully!", 'success');
        output("");
    }
    
    // If table has both (shouldn't happen, but just in case)
    if ($hasVariants && $hasCustomizations) {
        output("2. Removing duplicate 'customizations' column...", 'warning');
        output("   (keeping 'variants' column)", 'info');
        
        // First, copy any data from customizations to variants if variants is null
        $db->exec("UPDATE order_items SET variants = customizations WHERE variants IS NULL AND customizations IS NOT NULL");
        
        // Then drop the customizations column
        $db->exec("ALTER TABLE order_items DROP COLUMN customizations");
        
        output("   ✓ Duplicate column removed!", 'success');
        output("");
    }
    
    // Verify final structure
    output("3. Verifying final structure...", 'info');
    $stmt = $db->query('DESCRIBE order_items');
    $finalColumns = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $finalColumns[] = $row['Field'];
    }
    
    if (in_array('variants', $finalColumns) && !in_array('customizations', $finalColumns)) {
        output("   ✓ Structure verified successfully!", 'success');
        output("");
        output("=== MIGRATION COMPLETE ===", 'success');
        output("");
        output("Summary:", 'info');
        output("- order_items table now has 'variants' column ✓", 'success');
        output("- order_items table does NOT have 'customizations' column ✓", 'success');
        output("- CheckoutController.php maps cart customizations → order variants ✓", 'success');
        output("");
        output("Order placement should now work correctly!", 'success');
    } else {
        output("   ✗ Verification failed!", 'error');
        output("   Current columns: " . implode(', ', $finalColumns), 'error');
        exit(1);
    }
    
} catch (Exception $e) {
    output("ERROR: " . $e->getMessage(), 'error');
    output("Stack trace: " . $e->getTraceAsString(), 'error');
    exit(1);
}

