<?php
/**
 * Update restaurant GPS coordinates with diverse locations in Bamenda
 * 
 * This script assigns realistic GPS coordinates to restaurants
 * spread across different areas of Bamenda, Cameroon
 */

require_once __DIR__ . '/../config/database.php';

// Define diverse locations across Bamenda
// Format: [latitude, longitude, area_name]
$bamendaLocations = [
    // Commercial Avenue (City Center)
    [5.9631, 10.1591, 'Commercial Avenue - City Center'],
    
    // Ntarikon Quarter
    [5.9597, 10.1463, 'Ntarikon Quarter'],
    
    // Up Station
    [5.9654, 10.1512, 'Up Station'],
    
    // Nkwen
    [5.9700, 10.1650, 'Nkwen'],
    
    // Mile 4 (Cow Street area)
    [5.9578, 10.1445, 'Mile 4 - Cow Street'],
    
    // Food Market
    [5.9612, 10.1478, 'Food Market Area'],
    
    // Azire
    [5.9520, 10.1380, 'Azire'],
    
    // Mulang
    [5.9680, 10.1720, 'Mulang'],
    
    // Mankon
    [5.9750, 10.1580, 'Mankon'],
    
    // Bamendankwe
    [5.9560, 10.1520, 'Bamendankwe'],
    
    // Old Town
    [5.9590, 10.1550, 'Old Town'],
    
    // Finance Junction
    [5.9640, 10.1600, 'Finance Junction'],
];

try {
    $db = dbConnection();
    
    // Get all restaurants
    $stmt = $db->query("
        SELECT id, name 
        FROM restaurants 
        WHERE status != 'deleted' 
        ORDER BY id
    ");
    
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($restaurants)) {
        echo "No restaurants found.\n";
        exit;
    }
    
    echo "=== UPDATING RESTAURANT GPS COORDINATES ===\n\n";
    echo "Found " . count($restaurants) . " restaurants to update.\n\n";
    
    // Shuffle locations to randomize assignment
    shuffle($bamendaLocations);
    
    $updateStmt = $db->prepare("
        UPDATE restaurants 
        SET latitude = :latitude, 
            longitude = :longitude,
            updated_at = NOW()
        WHERE id = :id
    ");
    
    $locationIndex = 0;
    foreach ($restaurants as $restaurant) {
        // Get location (cycle through if more restaurants than locations)
        $location = $bamendaLocations[$locationIndex % count($bamendaLocations)];
        
        // Add small random variation (±0.001 degrees ≈ ±100 meters)
        $lat = $location[0] + (mt_rand(-10, 10) / 10000);
        $lon = $location[1] + (mt_rand(-10, 10) / 10000);
        
        // Update database
        $updateStmt->execute([
            'latitude' => $lat,
            'longitude' => $lon,
            'id' => $restaurant['id']
        ]);
        
        printf(
            "✓ Updated: %s\n  Location: %s\n  GPS: %.6f, %.6f\n\n",
            $restaurant['name'],
            $location[2],
            $lat,
            $lon
        );
        
        $locationIndex++;
    }
    
    echo str_repeat('=', 60) . "\n";
    echo "✓ Successfully updated " . count($restaurants) . " restaurants!\n\n";
    
    // Display summary
    echo "=== VERIFICATION ===\n\n";
    $verifyStmt = $db->query("
        SELECT id, name, latitude, longitude 
        FROM restaurants 
        WHERE status != 'deleted' 
        ORDER BY id
    ");
    
    while ($row = $verifyStmt->fetch(PDO::FETCH_ASSOC)) {
        printf(
            "ID: %d | %s | GPS: %.6f, %.6f\n",
            $row['id'],
            $row['name'],
            $row['latitude'],
            $row['longitude']
        );
    }
    
    echo "\n✓ All coordinates updated successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

