<?php

namespace Time2Eat\Models;

use core\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $fillable = [
        'order_id', 'user_id', 'payment_method_id', 'transaction_id', 'reference_number',
        'type', 'method', 'provider', 'amount', 'currency', 'fee', 'net_amount',
        'status', 'gateway_response', 'failure_reason', 'processed_at', 'refunded_at',
        'notes', 'metadata'
    ];

    /**
     * Get payment by transaction ID
     */
    public function getByTransactionId(string $transactionId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE transaction_id = ?";
        return $this->fetchOne($sql, [$transactionId]);
    }

    /**
     * Get payment by reference number
     */
    public function getByReferenceNumber(string $referenceNumber): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE reference_number = ?";
        return $this->fetchOne($sql, [$referenceNumber]);
    }

    /**
     * Get payments by order ID
     */
    public function getByOrderId(int $orderId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ? ORDER BY created_at DESC";
        return $this->fetchAll($sql, [$orderId]);
    }

    /**
     * Get payments by user ID
     */
    public function getByUserId(int $userId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT p.*, o.order_number, o.total_amount as order_total
                FROM {$this->table} p
                LEFT JOIN orders o ON p.order_id = o.id
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        
        return $this->fetchAll($sql, [$userId, $limit, $offset]);
    }

    /**
     * Get successful payments for a period
     */
    public function getSuccessfulPayments(string $period = '30days'): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'completed' AND type = 'payment' {$dateCondition}
                ORDER BY created_at DESC";
        
        return $this->fetchAll($sql);
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(string $period = '30days'): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        $sql = "SELECT 
                    COUNT(*) as total_payments,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_payments,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments,
                    COUNT(CASE WHEN type = 'refund' THEN 1 END) as refunds,
                    SUM(CASE WHEN status = 'completed' AND type = 'payment' THEN amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END) as total_refunds,
                    AVG(CASE WHEN status = 'completed' AND type = 'payment' THEN amount END) as average_payment
                FROM {$this->table} 
                WHERE 1=1 {$dateCondition}";
        
        return $this->fetchOne($sql) ?: [];
    }

    /**
     * Get payment methods usage statistics
     */
    public function getPaymentMethodStats(string $period = '30days'): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        $sql = "SELECT 
                    method,
                    COUNT(*) as count,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount,
                    AVG(CASE WHEN status = 'completed' THEN amount END) as average_amount
                FROM {$this->table} 
                WHERE type = 'payment' {$dateCondition}
                GROUP BY method
                ORDER BY count DESC";
        
        return $this->fetchAll($sql);
    }

    /**
     * Get failed payments for analysis
     */
    public function getFailedPayments(int $limit = 100): array
    {
        $sql = "SELECT p.*, o.order_number, u.email as customer_email
                FROM {$this->table} p
                LEFT JOIN orders o ON p.order_id = o.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.status = 'failed'
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        return $this->fetchAll($sql, [$limit]);
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments(): array
    {
        $sql = "SELECT p.*, o.order_number, u.email as customer_email
                FROM {$this->table} p
                LEFT JOIN orders o ON p.order_id = o.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.status = 'pending'
                ORDER BY p.created_at ASC";
        
        return $this->fetchAll($sql);
    }

    /**
     * Get revenue by period
     */
    public function getRevenueByPeriod(string $groupBy = 'day', int $days = 30): array
    {
        $groupByClause = match($groupBy) {
            'hour' => 'DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00")',
            'day' => 'DATE(created_at)',
            'week' => 'YEARWEEK(created_at)',
            'month' => 'DATE_FORMAT(created_at, "%Y-%m")',
            default => 'DATE(created_at)'
        };

        $sql = "SELECT 
                    {$groupByClause} as period,
                    COUNT(*) as payment_count,
                    SUM(amount) as total_revenue,
                    AVG(amount) as average_payment
                FROM {$this->table}
                WHERE status = 'completed' 
                AND type = 'payment'
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY {$groupByClause}
                ORDER BY period ASC";

        return $this->fetchAll($sql, [$days]);
    }

    /**
     * Get payment gateway performance
     */
    public function getGatewayPerformance(): array
    {
        $sql = "SELECT 
                    provider,
                    method,
                    COUNT(*) as total_attempts,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                    ROUND((COUNT(CASE WHEN status = 'completed' THEN 1 END) / COUNT(*)) * 100, 2) as success_rate,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_processed,
                    AVG(CASE WHEN status = 'completed' THEN 
                        TIMESTAMPDIFF(SECOND, created_at, processed_at) 
                    END) as avg_processing_time
                FROM {$this->table}
                WHERE type = 'payment'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY provider, method
                ORDER BY success_rate DESC, total_processed DESC";

        return $this->fetchAll($sql);
    }

    /**
     * Create payment with validation
     */
    public function createPayment(array $data): ?int
    {
        // Validate required fields
        $required = ['user_id', 'transaction_id', 'reference_number', 'type', 'method', 'amount', 'currency'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Set defaults
        $data['status'] = $data['status'] ?? 'pending';
        $data['currency'] = $data['currency'] ?? 'XAF';
        $data['fee'] = $data['fee'] ?? 0;
        $data['net_amount'] = $data['amount'] - $data['fee'];
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    /**
     * Update payment status
     */
    public function updateStatus(int $id, string $status, array $additionalData = []): bool
    {
        $updateData = array_merge($additionalData, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($status === 'completed' && !isset($additionalData['processed_at'])) {
            $updateData['processed_at'] = date('Y-m-d H:i:s');
        }

        if ($status === 'refunded' && !isset($additionalData['refunded_at'])) {
            $updateData['refunded_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($id, $updateData);
    }

    /**
     * Get total refunds for an order
     */
    public function getTotalRefunds(int $orderId): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total_refunds
                FROM {$this->table}
                WHERE order_id = ? AND type = 'refund' AND status = 'completed'";
        
        $result = $this->fetchOne($sql, [$orderId]);
        return (float)($result['total_refunds'] ?? 0);
    }

    /**
     * Check if payment can be refunded
     */
    public function canRefund(int $paymentId): array
    {
        $payment = $this->getById($paymentId);
        if (!$payment) {
            return ['can_refund' => false, 'reason' => 'Payment not found'];
        }

        if ($payment['status'] !== 'completed') {
            return ['can_refund' => false, 'reason' => 'Payment not completed'];
        }

        if ($payment['type'] !== 'payment') {
            return ['can_refund' => false, 'reason' => 'Not a payment transaction'];
        }

        // Check if already fully refunded
        $totalRefunds = $this->getTotalRefunds($payment['order_id']);
        if ($totalRefunds >= $payment['amount']) {
            return ['can_refund' => false, 'reason' => 'Already fully refunded'];
        }

        // Check refund time limit (e.g., 30 days)
        $paymentDate = new \DateTime($payment['created_at']);
        $now = new \DateTime();
        $daysDiff = $now->diff($paymentDate)->days;

        if ($daysDiff > 30) {
            return ['can_refund' => false, 'reason' => 'Refund period expired'];
        }

        return [
            'can_refund' => true,
            'max_refund_amount' => $payment['amount'] - $totalRefunds,
            'days_remaining' => 30 - $daysDiff
        ];
    }

    /**
     * Get payment summary for dashboard
     */
    public function getPaymentSummary(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN status = 'completed' AND type = 'payment' THEN amount ELSE 0 END) as total_revenue,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
                    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_payments,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() AND status = 'completed' AND type = 'payment' THEN amount ELSE 0 END) as today_revenue
                FROM {$this->table}";

        return $this->fetchOne($sql) ?: [];
    }

    /**
     * Get date condition for queries
     */
    private function getDateCondition(string $period): string
    {
        switch ($period) {
            case 'today':
                return "AND DATE(created_at) = CURDATE()";
            case 'yesterday':
                return "AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            case '7days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case 'year':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "";
        }
    }
}
