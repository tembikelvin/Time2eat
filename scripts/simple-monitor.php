<?php

/**
 * Simple Push Subscription Monitor
 * 
 * This script monitors push notification subscriptions in the database.
 * Run with: php scripts/simple-monitor.php
 */

// Database connection
$host = 'localhost';
$dbname = 'time2eat';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "🔔 Push Subscription Monitor\n";
echo "============================\n\n";

// Get statistics
$stats = [];

// Total subscriptions
$stmt = $pdo->query("SELECT COUNT(*) as total FROM push_subscriptions");
$stats['total'] = $stmt->fetch()['total'];

// Active subscriptions
$stmt = $pdo->query("SELECT COUNT(*) as active FROM push_subscriptions WHERE is_active = 1");
$stats['active'] = $stmt->fetch()['active'];

// Subscriptions by user role
$stmt = $pdo->query("
    SELECT u.role, COUNT(ps.id) as count
    FROM push_subscriptions ps
    JOIN users u ON ps.user_id = u.id
    WHERE ps.is_active = 1
    GROUP BY u.role
");
$stats['by_role'] = $stmt->fetchAll();

// Recent subscriptions (last 7 days)
$stmt = $pdo->query("
    SELECT COUNT(*) as recent
    FROM push_subscriptions
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stats['recent'] = $stmt->fetch()['recent'];

echo "📊 Statistics:\n";
echo "  Total Subscriptions: {$stats['total']}\n";
echo "  Active Subscriptions: {$stats['active']}\n";
echo "  Recent (7 days): {$stats['recent']}\n\n";

if (!empty($stats['by_role'])) {
    echo "👥 By Role:\n";
    foreach ($stats['by_role'] as $role) {
        echo "  {$role['role']}: {$role['count']}\n";
    }
    echo "\n";
}

echo "📋 Recent Subscriptions:\n";
$stmt = $pdo->query("
    SELECT 
        ps.id,
        ps.user_id,
        u.first_name,
        u.last_name,
        u.email,
        u.role,
        ps.endpoint,
        ps.is_active,
        ps.created_at,
        ps.updated_at
    FROM push_subscriptions ps
    JOIN users u ON ps.user_id = u.id
    ORDER BY ps.created_at DESC
    LIMIT 10
");

$subscriptions = $stmt->fetchAll();

if (empty($subscriptions)) {
    echo "  No subscriptions found.\n";
} else {
    foreach ($subscriptions as $sub) {
        $status = $sub['is_active'] ? '✅' : '❌';
        $endpoint = substr($sub['endpoint'], 0, 50) . '...';
        echo "  {$status} {$sub['first_name']} {$sub['last_name']} ({$sub['role']})\n";
        echo "    Endpoint: {$endpoint}\n";
        echo "    Created: {$sub['created_at']}\n\n";
    }
}

echo "✅ Monitoring complete!\n";
