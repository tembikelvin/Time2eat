<?php

/**
 * Push Subscription Monitor
 * 
 * This script monitors push notification subscriptions in the database.
 * Run with: php scripts/monitor-subscriptions.php
 */

require_once __DIR__ . '/../src/traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class SubscriptionMonitor {
    use DatabaseTrait;

    public function __construct() {
        // Database connection is handled by the trait
    }

    /**
     * Get subscription statistics
     */
    public function getStats() {
        $stats = [];

        // Total subscriptions
        $result = $this->query("SELECT COUNT(*) as total FROM push_subscriptions");
        $stats['total'] = $result[0]['total'] ?? 0;

        // Active subscriptions
        $result = $this->query("SELECT COUNT(*) as active FROM push_subscriptions WHERE is_active = 1");
        $stats['active'] = $result[0]['active'] ?? 0;

        // Subscriptions by user role
        $result = $this->query("
            SELECT u.role, COUNT(ps.id) as count
            FROM push_subscriptions ps
            JOIN users u ON ps.user_id = u.id
            WHERE ps.is_active = 1
            GROUP BY u.role
        ");
        $stats['by_role'] = $result;

        // Recent subscriptions (last 7 days)
        $result = $this->query("
            SELECT COUNT(*) as recent
            FROM push_subscriptions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stats['recent'] = $result[0]['recent'] ?? 0;

        return $stats;
    }

    /**
     * Get detailed subscription list
     */
    public function getSubscriptions($limit = 20, $offset = 0) {
        $sql = "
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
            LIMIT ? OFFSET ?
        ";

        return $this->query($sql, [$limit, $offset]);
    }

    /**
     * Clean up inactive subscriptions
     */
    public function cleanupInactive($days = 30) {
        $sql = "
            UPDATE push_subscriptions 
            SET is_active = 0 
            WHERE updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND is_active = 1
        ";

        $this->query($sql, [$days]);
        return $this->getLastAffectedRows();
    }

    /**
     * Test subscription endpoint
     */
    public function testEndpoint($subscriptionId) {
        $sql = "
            SELECT endpoint, p256dh_key, auth_key
            FROM push_subscriptions
            WHERE id = ? AND is_active = 1
        ";

        $result = $this->query($sql, [$subscriptionId]);
        return $result[0] ?? null;
    }

    /**
     * Display monitoring dashboard
     */
    public function displayDashboard() {
        echo "🔔 Push Subscription Monitor\n";
        echo "============================\n\n";

        $stats = $this->getStats();

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
        $subscriptions = $this->getSubscriptions(10);
        
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
    }

    /**
     * Export subscriptions to CSV
     */
    public function exportToCSV($filename = 'subscriptions.csv') {
        $subscriptions = $this->getSubscriptions(1000); // Get up to 1000 records
        
        $file = fopen($filename, 'w');
        
        // CSV header
        fputcsv($file, [
            'ID', 'User ID', 'Name', 'Email', 'Role', 
            'Endpoint', 'Active', 'Created At', 'Updated At'
        ]);

        // CSV data
        foreach ($subscriptions as $sub) {
            fputcsv($file, [
                $sub['id'],
                $sub['user_id'],
                $sub['first_name'] . ' ' . $sub['last_name'],
                $sub['email'],
                $sub['role'],
                $sub['endpoint'],
                $sub['is_active'] ? 'Yes' : 'No',
                $sub['created_at'],
                $sub['updated_at']
            ]);
        }

        fclose($file);
        echo "📄 Exported {$filename}\n";
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $monitor = new SubscriptionMonitor();
    
    $command = $argv[1] ?? 'dashboard';
    
    switch ($command) {
        case 'dashboard':
            $monitor->displayDashboard();
            break;
            
        case 'stats':
            $stats = $monitor->getStats();
            echo json_encode($stats, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'cleanup':
            $days = $argv[2] ?? 30;
            $cleaned = $monitor->cleanupInactive($days);
            echo "🧹 Cleaned up {$cleaned} inactive subscriptions (older than {$days} days)\n";
            break;
            
        case 'export':
            $filename = $argv[2] ?? 'subscriptions.csv';
            $monitor->exportToCSV($filename);
            break;
            
        case 'test':
            $id = $argv[2] ?? null;
            if (!$id) {
                echo "❌ Please provide subscription ID: php scripts/monitor-subscriptions.php test <id>\n";
                exit(1);
            }
            $subscription = $monitor->testEndpoint($id);
            if ($subscription) {
                echo "✅ Subscription found:\n";
                echo "Endpoint: {$subscription['endpoint']}\n";
            } else {
                echo "❌ Subscription not found or inactive\n";
            }
            break;
            
        default:
            echo "Usage: php scripts/monitor-subscriptions.php [command]\n";
            echo "Commands:\n";
            echo "  dashboard  - Show monitoring dashboard (default)\n";
            echo "  stats      - Show statistics in JSON format\n";
            echo "  cleanup    - Clean up inactive subscriptions\n";
            echo "  export     - Export subscriptions to CSV\n";
            echo "  test <id>  - Test specific subscription\n";
            break;
    }
}
