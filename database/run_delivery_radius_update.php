#!/usr/bin/env php
<?php
/**
 * Update Delivery Radius for All Restaurants
 * Extends delivery zones to reach outskirts of the city
 */

// Determine if running from CLI or web
$isCLI = php_sapi_name() === 'cli';

// Define BASE_PATH
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load database configuration
require_once BASE_PATH . '/config/database.php';

// Output helper
function output($message, $isCLI) {
    if ($isCLI) {
        echo $message . PHP_EOL;
    } else {
        echo '<p>' . htmlspecialchars($message) . '</p>';
    }
}

// Start output
if (!$isCLI) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Delivery Radius Update</title>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
            th { background: #333; color: white; }
            .success { color: green; font-weight: bold; }
            .info { color: blue; }
        </style>
    </head>
    <body>
    <h1>🚚 Delivery Radius Update</h1>';
}

try {
    output('Connecting to database...', $isCLI);
    $db = dbConnection();
    output('✅ Database connection successful', $isCLI);
    
    // Get current settings
    output('', $isCLI);
    output('📊 Current Delivery Settings:', $isCLI);
    
    $stmt = $db->query("
        SELECT 
            id,
            name,
            delivery_radius,
            delivery_fee,
            delivery_fee_per_extra_km
        FROM restaurants 
        WHERE status = 'active' AND deleted_at IS NULL
    ");
    
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$isCLI) {
        echo '<table>';
        echo '<thead><tr>';
        echo '<th>Restaurant</th>';
        echo '<th>Current Radius (km)</th>';
        echo '<th>Current Max Distance (km)</th>';
        echo '<th>Base Fee (FCFA)</th>';
        echo '<th>Extra Fee/km (FCFA)</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($restaurants as $r) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($r['name']) . '</td>';
            echo '<td>' . number_format($r['delivery_radius'] ?? 10, 1) . '</td>';
            echo '<td>' . number_format(($r['delivery_radius'] ?? 10) * 2, 1) . '</td>';
            echo '<td>' . number_format($r['delivery_fee'] ?? 500) . '</td>';
            echo '<td>' . number_format($r['delivery_fee_per_extra_km'] ?? 100) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    } else {
        foreach ($restaurants as $r) {
            output(sprintf(
                "  %s: Radius=%.1f km, Max=%.1f km, Base=%d FCFA, Extra=%d FCFA/km",
                $r['name'],
                $r['delivery_radius'] ?? 10,
                ($r['delivery_radius'] ?? 10) * 2,
                $r['delivery_fee'] ?? 500,
                $r['delivery_fee_per_extra_km'] ?? 100
            ), $isCLI);
        }
    }
    
    // Update delivery radius
    output('', $isCLI);
    output('🔄 Updating delivery radius to 25 km (max distance: 50 km)...', $isCLI);
    
    $updated = $db->exec("
        UPDATE restaurants 
        SET 
            delivery_radius = 25.0,
            delivery_fee = 1000,
            delivery_fee_per_extra_km = 150
        WHERE status = 'active' AND deleted_at IS NULL
    ");
    
    output("✅ Updated {$updated} restaurants", $isCLI);
    
    // Show new settings
    output('', $isCLI);
    output('📊 New Delivery Settings:', $isCLI);
    
    $stmt = $db->query("
        SELECT 
            id,
            name,
            delivery_radius,
            delivery_fee,
            delivery_fee_per_extra_km
        FROM restaurants 
        WHERE status = 'active' AND deleted_at IS NULL
    ");
    
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$isCLI) {
        echo '<table>';
        echo '<thead><tr>';
        echo '<th>Restaurant</th>';
        echo '<th>New Radius (km)</th>';
        echo '<th>New Max Distance (km)</th>';
        echo '<th>Base Fee (FCFA)</th>';
        echo '<th>Extra Fee/km (FCFA)</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($restaurants as $r) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($r['name']) . '</td>';
            echo '<td class="success">' . number_format($r['delivery_radius'], 1) . '</td>';
            echo '<td class="success">' . number_format($r['delivery_radius'] * 2, 1) . '</td>';
            echo '<td>' . number_format($r['delivery_fee']) . '</td>';
            echo '<td>' . number_format($r['delivery_fee_per_extra_km']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    } else {
        foreach ($restaurants as $r) {
            output(sprintf(
                "  ✅ %s: Radius=%.1f km, Max=%.1f km, Base=%d FCFA, Extra=%d FCFA/km",
                $r['name'],
                $r['delivery_radius'],
                $r['delivery_radius'] * 2,
                $r['delivery_fee'],
                $r['delivery_fee_per_extra_km']
            ), $isCLI);
        }
    }
    
    output('', $isCLI);
    output('✅ Delivery radius update complete!', $isCLI);
    output('ℹ️  Restaurants can now deliver up to 50 km from their location', $isCLI);
    
    if (!$isCLI) {
        echo '<div style="margin-top: 30px; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">';
        echo '<h3 style="color: #155724;">✅ Update Successful!</h3>';
        echo '<p style="color: #155724;">All restaurants can now deliver to the outskirts of the city (up to 50 km).</p>';
        echo '<ul style="color: #155724;">';
        echo '<li>Free zone radius: 25 km</li>';
        echo '<li>Maximum delivery distance: 50 km</li>';
        echo '<li>Base delivery fee: 1,000 FCFA</li>';
        echo '<li>Extra fee beyond 25 km: 150 FCFA/km</li>';
        echo '</ul>';
        echo '</div>';
    }
    
} catch (PDOException $e) {
    output('❌ Database error: ' . $e->getMessage(), $isCLI);
    exit(1);
} catch (Exception $e) {
    output('❌ Error: ' . $e->getMessage(), $isCLI);
    exit(1);
}

if (!$isCLI) {
    echo '</body></html>';
}

exit(0);

