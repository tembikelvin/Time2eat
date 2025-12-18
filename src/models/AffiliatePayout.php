<?php

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class AffiliatePayout
{
    use DatabaseTrait;

    protected ?\PDO $db = null;
    protected $table = 'affiliate_payouts';
    protected $fillable = [
        'withdrawal_id', 'affiliate_id', 'amount', 'payment_method', 
        'payment_details', 'transaction_id', 'status', 'processed_at', 'completed_at'
    ];

    public function createPayout(array $data): ?int
    {
        $validation = $this->validatePayoutData($data);
        if (!$validation['isValid']) {
            $this->setErrors($validation['errors']);
            return null;
        }

        $payoutData = [
            'withdrawal_id' => $data['withdrawal_id'],
            'affiliate_id' => $data['affiliate_id'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_details' => is_array($data['payment_details']) 
                ? json_encode($data['payment_details']) 
                : $data['payment_details'],
            'status' => 'processing',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($payoutData);
    }

    public function markAsCompleted(int $payoutId, string $transactionId): bool
    {
        $payout = $this->getById($payoutId);
        if (!$payout) {
            $this->setError('Payout not found');
            return false;
        }

        if ($payout['status'] === 'completed') {
            $this->setError('Payout already completed');
            return false;
        }

        return $this->update($payoutId, [
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markAsFailed(int $payoutId, string $reason): bool
    {
        $payout = $this->getById($payoutId);
        if (!$payout) {
            $this->setError('Payout not found');
            return false;
        }

        $this->beginTransaction();

        try {
            // Update payout status
            $this->update($payoutId, [
                'status' => 'failed',
                'failure_reason' => $reason,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Refund amount to affiliate balance
            $affiliateModel = new Affiliate();
            $sql = "UPDATE affiliates SET 
                    available_balance = available_balance + ?,
                    total_withdrawals = total_withdrawals - ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $this->execute($sql, [$payout['amount'], $payout['amount'], $payout['affiliate_id']]);

            // Update withdrawal status
            $withdrawalModel = new AffiliateWithdrawal();
            $withdrawalModel->update($payout['withdrawal_id'], [
                'status' => 'failed',
                'admin_notes' => $reason,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->setError('Failed to mark payout as failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getProcessingPayouts(): array
    {
        $sql = "SELECT ap.*, aw.requested_at, a.referral_code, 
                       u.first_name, u.last_name, u.email, u.phone
                FROM {$this->table} ap
                JOIN affiliate_withdrawals aw ON ap.withdrawal_id = aw.id
                JOIN affiliates a ON ap.affiliate_id = a.id
                JOIN users u ON a.user_id = u.id
                WHERE ap.status = 'processing'
                ORDER BY ap.created_at ASC";
        
        return $this->fetchAll($sql);
    }

    public function getPayoutsByAffiliate(int $affiliateId): array
    {
        $sql = "SELECT ap.*, aw.requested_at
                FROM {$this->table} ap
                JOIN affiliate_withdrawals aw ON ap.withdrawal_id = aw.id
                WHERE ap.affiliate_id = ?
                ORDER BY ap.created_at DESC";
        
        return $this->fetchAll($sql, [$affiliateId]);
    }

    public function getPayoutStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_payouts,
                    COALESCE(SUM(amount), 0) as total_amount,
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as completed_amount,
                    COALESCE(SUM(CASE WHEN status = 'processing' THEN amount ELSE 0 END), 0) as processing_amount,
                    COALESCE(SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END), 0) as failed_amount,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_count,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count
                FROM {$this->table}";
        
        $stats = $this->fetchOne($sql);
        
        // Get monthly breakdown
        $monthlySql = "SELECT 
                          DATE_FORMAT(created_at, '%Y-%m') as month,
                          COUNT(*) as count,
                          COALESCE(SUM(amount), 0) as amount
                       FROM {$this->table}
                       WHERE status = 'completed'
                       AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                       GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                       ORDER BY month DESC";
        
        $monthlyStats = $this->fetchAll($monthlySql);
        
        return [
            'overview' => $stats,
            'monthly' => $monthlyStats
        ];
    }

    public function getPayoutsByMethod(): array
    {
        $sql = "SELECT 
                    payment_method,
                    COUNT(*) as count,
                    COALESCE(SUM(amount), 0) as total_amount,
                    COALESCE(AVG(amount), 0) as average_amount
                FROM {$this->table}
                WHERE status = 'completed'
                GROUP BY payment_method
                ORDER BY total_amount DESC";
        
        return $this->fetchAll($sql);
    }

    public function processAutomaticPayouts(): array
    {
        $results = [];
        $processingPayouts = $this->getProcessingPayouts();
        
        foreach ($processingPayouts as $payout) {
            $result = $this->processPayoutByMethod($payout);
            $results[] = [
                'payout_id' => $payout['id'],
                'affiliate_id' => $payout['affiliate_id'],
                'amount' => $payout['amount'],
                'method' => $payout['payment_method'],
                'success' => $result['success'],
                'message' => $result['message'],
                'transaction_id' => $result['transaction_id'] ?? null
            ];
        }
        
        return $results;
    }

    private function processPayoutByMethod(array $payout): array
    {
        $paymentDetails = json_decode($payout['payment_details'], true);
        
        switch ($payout['payment_method']) {
            case 'mobile_money':
            case 'orange_money':
                return $this->processOrangeMoneyPayout($payout, $paymentDetails);
                
            case 'mtn_momo':
                return $this->processMTNMomoPayout($payout, $paymentDetails);
                
            case 'bank_transfer':
                return $this->processBankTransferPayout($payout, $paymentDetails);
                
            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported payment method'
                ];
        }
    }

    private function processOrangeMoneyPayout(array $payout, array $details): array
    {
        // Integrate with Orange Money API
        try {
            // Simulate API call - replace with actual Orange Money API integration
            $transactionId = 'OM' . time() . rand(1000, 9999);

            // Mark as completed
            $this->markAsCompleted($payout['id'], $transactionId);

            return [
                'success' => true,
                'message' => 'Orange Money payout completed',
                'transaction_id' => $transactionId
            ];
        } catch (\Exception $e) {
            $this->markAsFailed($payout['id'], $e->getMessage());

            return [
                'success' => false,
                'message' => 'Orange Money payout failed: ' . $e->getMessage()
            ];
        }
    }

    private function processMTNMomoPayout(array $payout, array $details): array
    {
        // Integrate with MTN MoMo API
        // This is a placeholder - implement actual MTN MoMo API integration
        
        try {
            // Simulate API call
            $transactionId = 'MTN' . time() . rand(1000, 9999);
            
            // Mark as completed
            $this->markAsCompleted($payout['id'], $transactionId);
            
            return [
                'success' => true,
                'message' => 'MTN MoMo payout completed',
                'transaction_id' => $transactionId
            ];
        } catch (\Exception $e) {
            $this->markAsFailed($payout['id'], $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'MTN MoMo payout failed: ' . $e->getMessage()
            ];
        }
    }

    private function processBankTransferPayout(array $payout, array $details): array
    {
        // Integrate with bank transfer API or manual processing
        // This is a placeholder - implement actual bank transfer processing
        
        try {
            // For bank transfers, typically requires manual processing
            // Mark as pending manual review
            $this->update($payout['id'], [
                'status' => 'pending_manual_review',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'message' => 'Bank transfer marked for manual processing'
            ];
        } catch (\Exception $e) {
            $this->markAsFailed($payout['id'], $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Bank transfer processing failed: ' . $e->getMessage()
            ];
        }
    }

    private function validatePayoutData(array $data): array
    {
        $rules = [
            'withdrawal_id' => 'required|integer',
            'affiliate_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_details' => 'required'
        ];

        return $this->validate($data, $rules);
    }
}
