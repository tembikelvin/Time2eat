<?php

require_once __DIR__ . '/../config/database.php';

try {
    $db = dbConnection();
    
    echo "=== CHECKING ORDER RIDER ASSIGNMENTS ===\n\n";
    
    // Get all orders with their rider status
    $stmt = $db->query("
        SELECT 
            o.id,
            o.order_number,
            o.status,
            o.rider_id,
            o.created_at,
            r.name as restaurant_name,
            CONCAT(u.first_name, ' ', u.last_name) as rider_name
        FROM orders o
        LEFT JOIN restaurants r ON o.restaurant_id = r.id
        LEFT JOIN users u ON o.rider_id = u.id
        WHERE o.status NOT IN ('delivered', 'cancelled')
        ORDER BY o.created_at DESC
        LIMIT 20
    ");
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recent Active Orders:\n";
    echo str_repeat("-", 120) . "\n";
    printf("%-5s %-20s %-15s %-12s %-30s %-25s\n", "ID", "Order Number", "Status", "Has Rider?", "Restaurant", "Rider Name");
    echo str_repeat("-", 120) . "\n";
    
    $withRider = 0;
    $withoutRider = 0;
    
    foreach ($orders as $order) {
        $hasRider = !empty($order['rider_id']) ? 'YES' : 'NO';
        $riderName = $order['rider_name'] ?? 'Not assigned';
        
        if (!empty($order['rider_id'])) {
            $withRider++;
        } else {
            $withoutRider++;
        }
        
        printf(
            "%-5s %-20s %-15s %-12s %-30s %-25s\n",
            $order['id'],
            $order['order_number'],
            $order['status'],
            $hasRider,
            substr($order['restaurant_name'], 0, 28),
            substr($riderName, 0, 23)
        );
    }
    
    echo str_repeat("-", 120) . "\n";
    echo "\nSummary:\n";
    echo "- Orders WITH rider assigned: $withRider\n";
    echo "- Orders WITHOUT rider assigned: $withoutRider\n";
    echo "\n";
    
    // Check if there are any active riders
    $stmt = $db->query("
        SELECT COUNT(*) as count
        FROM users
        WHERE role = 'rider' AND status = 'active'
    ");
    $riderCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Active Riders in System: " . $riderCount['count'] . "\n";
    
    if ($riderCount['count'] == 0) {
        echo "\n⚠️  WARNING: No active riders in the system!\n";
        echo "   This is why orders don't have riders assigned.\n";
        echo "   You need to create rider accounts or activate existing ones.\n";
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

