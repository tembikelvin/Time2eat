<?php

namespace controllers;

require_once __DIR__ . '/AdminBaseController.php';

use controllers\AdminBaseController;
use PDO;

class AdminPaymentSettingsController extends AdminBaseController
{
    /**
     * Display payment settings page
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        try {
            // Get all payment settings
            $settings = $this->getPaymentSettings();
            
            // Get payment statistics
            $stats = $this->getPaymentStatistics();

            $this->renderDashboard('admin/payment-settings', [
                'title' => 'Payment Settings',
                'currentPage' => 'payment-settings',
                'settings' => $settings,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            error_log("Error loading payment settings: " . $e->getMessage());
            $this->renderDashboard('admin/payment-settings', [
                'title' => 'Payment Settings',
                'currentPage' => 'payment-settings',
                'error' => 'Failed to load payment settings',
                'settings' => [],
                'stats' => []
            ]);
        }
    }

    /**
     * Get payment settings from database
     */
    private function getPaymentSettings(): array
    {
        $stmt = $this->db->prepare("
            SELECT `key`, value, type, is_public
            FROM site_settings
            WHERE `group` = 'payment'
            ORDER BY `key`
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['key']] = [
                'value' => $row['value'],
                'type' => $row['type'],
                'is_public' => $row['is_public']
            ];
        }

        // Ensure minimum order amount is set to 200 XAF
        if (!isset($settings['minimum_order_amount'])) {
            $settings['minimum_order_amount'] = [
                'value' => '200',
                'type' => 'integer',
                'is_public' => true
            ];
        } else {
            $settings['minimum_order_amount']['value'] = '200';
        }

        return $settings;
    }

    /**
     * Get payment statistics
     */
    private function getPaymentStatistics(): array
    {
        try {
            // Check if payments table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'payments'");
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                return [
                    'transactions' => [
                        'total_transactions' => 0,
                        'successful_transactions' => 0,
                        'failed_transactions' => 0,
                        'pending_transactions' => 0
                    ],
                    'methods' => [],
                    'recent' => []
                ];
            }

            // Total transactions - handle both table structures
            $stmt = $this->db->query("
                SELECT COUNT(*) as total_transactions,
                       SUM(CASE WHEN status = 'completed' OR status = 'success' THEN 1 ELSE 0 END) as successful_transactions,
                       SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_transactions,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_transactions
                FROM payments
            ");
            $transactionStats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Payment method breakdown - handle both table structures
            $stmt = $this->db->query("
                SELECT 
                    COALESCE(gateway, payment_method, payment_provider) as payment_method,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                FROM payments
                WHERE status = 'completed' OR status = 'success'
                GROUP BY COALESCE(gateway, payment_method, payment_provider)
            ");
            $methodBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Recent transactions - handle both table structures
            $stmt = $this->db->query("
                SELECT p.*, o.order_number, u.first_name, u.last_name
                FROM payments p
                LEFT JOIN orders o ON p.order_id = o.id
                LEFT JOIN users u ON o.user_id = u.id OR o.customer_id = u.id
                ORDER BY p.created_at DESC
                LIMIT 10
            ");
            $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'transactions' => $transactionStats,
                'methods' => $methodBreakdown,
                'recent' => $recentTransactions
            ];
        } catch (\Exception $e) {
            error_log("Error getting payment statistics: " . $e->getMessage());
            return [
                'transactions' => [
                    'total_transactions' => 0,
                    'successful_transactions' => 0,
                    'failed_transactions' => 0,
                    'pending_transactions' => 0
                ],
                'methods' => [],
                'recent' => []
            ];
        }
    }

    /**
     * Save payment settings
     */
    public function save(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            $this->db->beginTransaction();

            foreach ($_POST as $key => $value) {
                // Skip CSRF token and other non-setting fields
                if (in_array($key, ['csrf_token', '_method'])) {
                    continue;
                }

                // Handle checkbox values
                if (!isset($_POST[$key]) && $this->isCheckboxField($key)) {
                    $value = 'false';
                }

                // Update or insert setting
                $stmt = $this->db->prepare("
                    INSERT INTO site_settings (`key`, value, `group`, updated_at)
                    VALUES (?, ?, 'payment', NOW())
                    ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
                ");
                $stmt->execute([$key, $value, $value]);
            }

            $this->db->commit();

            $this->json([
                'success' => true,
                'message' => 'Payment settings saved successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error saving payment settings: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to save payment settings'
            ], 500);
        }
    }

    /**
     * Test payment gateway connection
     */
    public function testGateway(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $gateway = $_POST['gateway'] ?? '';

        try {
            $result = $this->testGatewayConnection($gateway);
            $this->json($result);
        } catch (\Exception $e) {
            error_log("Error testing gateway: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to test gateway connection'
            ], 500);
        }
    }

    /**
     * Test gateway connection
     */
    private function testGatewayConnection(string $gateway): array
    {
        switch ($gateway) {
            case 'stripe':
                return $this->testStripe();
            case 'paypal':
                return $this->testPayPal();
            case 'tranzak':
                return $this->testTranzak();
            case 'orange_money':
                return $this->testOrangeMoney();
            case 'mtn_momo':
                return $this->testMTNMomo();
            default:
                return ['success' => false, 'message' => 'Unknown gateway'];
        }
    }

    private function testStripe(): array
    {
        // Implement Stripe test
        return ['success' => true, 'message' => 'Stripe connection test not yet implemented'];
    }

    private function testPayPal(): array
    {
        // Implement PayPal test
        return ['success' => true, 'message' => 'PayPal connection test not yet implemented'];
    }

    private function testTranzak(): array
    {
        // Implement Tranzak test
        return ['success' => true, 'message' => 'Tranzak connection test not yet implemented'];
    }

    private function testOrangeMoney(): array
    {
        // Implement Orange Money test
        return ['success' => true, 'message' => 'Orange Money connection test not yet implemented'];
    }

    private function testMTNMomo(): array
    {
        // Implement MTN MoMo test
        return ['success' => true, 'message' => 'MTN MoMo connection test not yet implemented'];
    }

    /**
     * Check user COD eligibility
     */
    public function checkUserCODEligibility(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $userId = (int)($input['user_id'] ?? 0);

        if (!$userId) {
            $this->json(['success' => false, 'message' => 'User ID is required'], 400);
            return;
        }

        try {
            require_once __DIR__ . '/../services/UserTrustService.php';
            $trustService = new \Time2Eat\Services\UserTrustService();
            
            $eligibility = $trustService->isEligibleForCOD($userId);
            $summary = $trustService->getUserTrustSummary($userId);

            $this->json([
                'success' => true,
                'eligible' => $eligibility['eligible'],
                'trust_score' => $eligibility['trust_score'],
                'reason' => $eligibility['reason'],
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            error_log("Error checking COD eligibility: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to check eligibility'], 500);
        }
    }

    /**
     * Get COD trust statistics
     */
    public function getCODTrustStats(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        try {
            // Get total customers
            $stmt = $this->db->query("
                SELECT COUNT(*) as total_users
                FROM users
                WHERE role = 'customer'
            ");
            $totalUsers = $stmt->fetch();

            // Get users with delivered orders
            $stmt = $this->db->query("
                SELECT COUNT(DISTINCT customer_id) as users_with_orders
                FROM orders
                WHERE status = 'delivered'
            ");
            $usersWithOrders = $stmt->fetch();

            // Get users with 50+ orders
            $stmt = $this->db->query("
                SELECT COUNT(*) as users_50_plus_orders
                FROM (
                    SELECT customer_id, COUNT(*) as order_count
                    FROM orders
                    WHERE status = 'delivered'
                    GROUP BY customer_id
                    HAVING order_count >= 50
                ) as user_orders
            ");
            $users50Plus = $stmt->fetch();

            // Get recent COD orders
            $stmt = $this->db->query("
                SELECT COUNT(*) as cod_orders
                FROM orders 
                WHERE payment_method = 'cash_on_delivery' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $codStats = $stmt->fetch();

            $this->json([
                'success' => true,
                'stats' => [
                    'total_users' => (int)($totalUsers['total_users'] ?? 0),
                    'users_with_orders' => (int)($usersWithOrders['users_with_orders'] ?? 0),
                    'users_50_plus_orders' => (int)($users50Plus['users_50_plus_orders'] ?? 0),
                    'recent_cod_orders' => (int)($codStats['cod_orders'] ?? 0)
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Error getting COD trust stats: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to get statistics'], 500);
        }
    }

    /**
     * Check if field is a checkbox
     */
    private function isCheckboxField(string $key): bool
    {
        $checkboxFields = [
            'cash_on_delivery_enabled',
            'online_payment_enabled',
            'paypal_sandbox_mode',
            'tranzak_sandbox_mode',
            'stripe_test_mode',
            'auto_capture_payments',
            'require_payment_confirmation',
            'cod_trust_based_enabled',
            'cod_require_trust_score',
            'tranzak_enabled',
            'mtn_momo_enabled',
            'orange_money_enabled',
            'paypal_enabled',
            'mtn_momo_sandbox_mode',
            'orange_money_sandbox_mode'
        ];
        return in_array($key, $checkboxFields);
    }
}

