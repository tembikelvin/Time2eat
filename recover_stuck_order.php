<?php
/**
 * Recovery script to fix stuck orders
 * Orders that are assigned to a rider but have no delivery record
 * 
 * Usage: php recover_stuck_order.php [order_number]
 * If no order number provided, will list all stuck orders
 */

require_once __DIR__ . '/bootstrap/app.php';

$orderNumber = $argv[1] ?? null;

try {
    $db = getDb();
    
    if ($orderNumber) {
        // Fix specific order
        $stmt = $db->prepare("
            SELECT o.*, r.name as restaurant_name
            FROM orders o
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            WHERE o.order_number = ?
        ");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo "Order {$orderNumber} not found.\n";
            exit(1);
        }
        
        // Check if order is stuck (has rider_id but no delivery record)
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM deliveries WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $deliveryCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($deliveryCount > 0) {
            echo "Order {$orderNumber} has a delivery record. Not stuck.\n";
            exit(0);
        }
        
        if (empty($order['rider_id'])) {
            echo "Order {$orderNumber} is not assigned to a rider. Not stuck.\n";
            exit(0);
        }
        
        if ($order['status'] !== 'assigned') {
            echo "Order {$orderNumber} status is '{$order['status']}', not 'assigned'. May not be stuck.\n";
            echo "Do you want to reset it anyway? (y/n): ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            if (trim($line) !== 'y') {
                echo "Aborted.\n";
                exit(0);
            }
        }
        
        // Reset the order
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                UPDATE orders 
                SET rider_id = NULL, 
                    status = 'ready',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$order['id']]);
            
            $db->commit();
            echo "✓ Order {$orderNumber} has been reset and is now available again.\n";
            echo "  Status: ready\n";
            echo "  Rider ID: NULL\n";
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        
    } else {
        // List all stuck orders
        echo "Finding stuck orders (assigned but no delivery record)...\n\n";
        
        $stmt = $db->prepare("
            SELECT 
                o.id,
                o.order_number,
                o.status,
                o.rider_id,
                o.created_at,
                o.updated_at,
                r.name as restaurant_name,
                u.first_name as rider_first_name,
                u.last_name as rider_last_name,
                (SELECT COUNT(*) FROM deliveries WHERE order_id = o.id) as delivery_count
            FROM orders o
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN users u ON o.rider_id = u.id
            WHERE o.rider_id IS NOT NULL
            AND o.status IN ('assigned', 'ready')
            AND (SELECT COUNT(*) FROM deliveries WHERE order_id = o.id) = 0
            ORDER BY o.updated_at DESC
            LIMIT 50
        ");
        $stmt->execute();
        $stuckOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($stuckOrders)) {
            echo "No stuck orders found.\n";
            exit(0);
        }
        
        echo "Found " . count($stuckOrders) . " stuck order(s):\n\n";
        echo str_pad("Order Number", 20) . " | " . 
             str_pad("Status", 12) . " | " . 
             str_pad("Rider", 25) . " | " . 
             str_pad("Restaurant", 20) . " | " . 
             "Updated\n";
        echo str_repeat("-", 120) . "\n";
        
        foreach ($stuckOrders as $order) {
            $riderName = ($order['rider_first_name'] ?? '') . ' ' . ($order['rider_last_name'] ?? '');
            if (empty(trim($riderName))) {
                $riderName = "ID: {$order['rider_id']}";
            }
            
            echo str_pad($order['order_number'], 20) . " | " . 
                 str_pad($order['status'], 12) . " | " . 
                 str_pad($riderName, 25) . " | " . 
                 str_pad($order['restaurant_name'] ?? 'N/A', 20) . " | " . 
                 $order['updated_at'] . "\n";
        }
        
        echo "\nTo fix a specific order, run:\n";
        echo "php recover_stuck_order.php <order_number>\n\n";
        echo "To fix all stuck orders at once, run:\n";
        echo "php recover_stuck_order.php --fix-all\n";
    }
    
    // Handle --fix-all flag
    if (isset($argv[1]) && $argv[1] === '--fix-all') {
        echo "Fixing all stuck orders...\n\n";
        
        $stmt = $db->prepare("
            SELECT o.id, o.order_number
            FROM orders o
            WHERE o.rider_id IS NOT NULL
            AND o.status IN ('assigned', 'ready')
            AND (SELECT COUNT(*) FROM deliveries WHERE order_id = o.id) = 0
        ");
        $stmt->execute();
        $stuckOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($stuckOrders)) {
            echo "No stuck orders to fix.\n";
            exit(0);
        }
        
        $db->beginTransaction();
        try {
            $fixed = 0;
            foreach ($stuckOrders as $order) {
                $stmt = $db->prepare("
                    UPDATE orders 
                    SET rider_id = NULL, 
                        status = 'ready',
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$order['id']]);
                $fixed++;
                echo "✓ Fixed order {$order['order_number']}\n";
            }
            
            $db->commit();
            echo "\n✓ Fixed {$fixed} stuck order(s).\n";
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

