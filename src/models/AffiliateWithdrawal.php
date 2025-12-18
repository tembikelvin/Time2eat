<?php

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class AffiliateWithdrawal
{
    use DatabaseTrait;

    protected ?\PDO $db = null;
    protected $table = 'affiliate_withdrawals';
    protected $fillable = [
        'affiliate_id', 'amount', 'payment_method', 'payment_details', 
        'status', 'requested_at', 'processed_at', 'admin_notes'
    ];

    public function createWithdrawalRequest(array $data): ?int
    {
        $validation = $this->validateWithdrawalData($data);
        if (!$validation['isValid']) {
            $this->setErrors($validation['errors']);
            return null;
        }

        $withdrawalData = [
            'affiliate_id' => $data['affiliate_id'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_details' => json_encode($data['payment_details']),
            'status' => 'pending',
            'requested_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($withdrawalData);
    }

    public function getPendingWithdrawals(int $affiliateId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE affiliate_id = ? AND status = 'pending'
                ORDER BY requested_at DESC";
        
        return $this->fetchAll($sql, [$affiliateId]);
    }

    public function getAllPendingWithdrawals(): array
    {
        $sql = "SELECT aw.*, a.referral_code, u.first_name, u.last_name, u.email, u.phone
                FROM {$this->table} aw
                JOIN affiliates a ON aw.affiliate_id = a.id
                JOIN users u ON a.user_id = u.id
                WHERE aw.status = 'pending'
                ORDER BY aw.requested_at ASC";
        
        return $this->fetchAll($sql);
    }

    public function getWithdrawalsByAffiliate(int $affiliateId, array $filters = []): array
    {
        $conditions = ['affiliate_id = ?'];
        $params = [$affiliateId];

        // Add status filter
        if (!empty($filters['status'])) {
            $conditions[] = 'status = ?';
            $params[] = $filters['status'];
        }

        // Add date filters
        if (!empty($filters['start_date'])) {
            $conditions[] = 'requested_at >= ?';
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $conditions[] = 'requested_at <= ?';
            $params[] = $filters['end_date'];
        }

        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT * FROM {$this->table}
                WHERE {$whereClause}
                ORDER BY requested_at DESC";

        return $this->fetchAll($sql, $params);
    }

    public function approveWithdrawal(int $withdrawalId, int $adminId, ?string $notes = null): bool
    {
        $withdrawal = $this->getById($withdrawalId);
        if (!$withdrawal) {
            $this->setError('Withdrawal not found');
            return false;
        }

        if ($withdrawal['status'] !== 'pending') {
            $this->setError('Withdrawal is not pending');
            return false;
        }

        $this->beginTransaction();

        try {
            // Update withdrawal status
            $updateData = [
                'status' => 'approved',
                'processed_at' => date('Y-m-d H:i:s'),
                'processed_by' => $adminId,
                'admin_notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->update($withdrawalId, $updateData);

            // Create payout record
            $payoutData = [
                'withdrawal_id' => $withdrawalId,
                'affiliate_id' => $withdrawal['affiliate_id'],
                'amount' => $withdrawal['amount'],
                'payment_method' => $withdrawal['payment_method'],
                'payment_details' => $withdrawal['payment_details'],
                'status' => 'processing',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $payoutModel = new AffiliatePayout();
            $payoutModel->create($payoutData);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->setError('Failed to approve withdrawal: ' . $e->getMessage());
            return false;
        }
    }

    public function rejectWithdrawal(int $withdrawalId, int $adminId, string $reason): bool
    {
        $withdrawal = $this->getById($withdrawalId);
        if (!$withdrawal) {
            $this->setError('Withdrawal not found');
            return false;
        }

        if ($withdrawal['status'] !== 'pending') {
            $this->setError('Withdrawal is not pending');
            return false;
        }

        $this->beginTransaction();

        try {
            // Update withdrawal status
            $updateData = [
                'status' => 'rejected',
                'processed_at' => date('Y-m-d H:i:s'),
                'processed_by' => $adminId,
                'admin_notes' => $reason,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->update($withdrawalId, $updateData);

            // Refund the amount to affiliate balance
            $affiliateModel = new Affiliate();
            $sql = "UPDATE affiliates SET 
                    available_balance = available_balance + ?,
                    total_withdrawals = total_withdrawals - ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $this->execute($sql, [$withdrawal['amount'], $withdrawal['amount'], $withdrawal['affiliate_id']]);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->setError('Failed to reject withdrawal: ' . $e->getMessage());
            return false;
        }
    }

    public function getWithdrawalStats(int $affiliateId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) as total_approved,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as total_pending,
                    COALESCE(SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END), 0) as total_rejected,
                    COALESCE(AVG(CASE WHEN status = 'approved' THEN amount ELSE NULL END), 0) as average_withdrawal
                FROM {$this->table}
                WHERE affiliate_id = ?";
        
        $stats = $this->fetchOne($sql, [$affiliateId]);
        
        // Get recent withdrawals
        $recentSql = "SELECT * FROM {$this->table}
                      WHERE affiliate_id = ?
                      ORDER BY requested_at DESC
                      LIMIT 5";
        
        $recentWithdrawals = $this->fetchAll($recentSql, [$affiliateId]);
        
        return [
            'stats' => $stats,
            'recent' => $recentWithdrawals
        ];
    }

    public function getMonthlyWithdrawals(int $affiliateId, ?string $month = null): float
    {
        $month = $month ?? date('Y-m');
        
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM {$this->table}
                WHERE affiliate_id = ? 
                AND DATE_FORMAT(requested_at, '%Y-%m') = ?
                AND status IN ('approved', 'processing', 'completed')";
        
        $result = $this->fetchOne($sql, [$affiliateId, $month]);
        return (float)($result['total'] ?? 0);
    }

    public function canRequestWithdrawal(int $affiliateId, float $amount): array
    {
        $affiliateModel = new Affiliate();
        $affiliate = $affiliateModel->getById($affiliateId);
        
        if (!$affiliate) {
            return ['can_withdraw' => false, 'reason' => 'Affiliate not found'];
        }

        // Check minimum withdrawal amount (10,000 XAF)
        $minWithdrawal = 10000;
        if ($amount < $minWithdrawal) {
            return [
                'can_withdraw' => false, 
                'reason' => "Minimum withdrawal amount is {$minWithdrawal} XAF"
            ];
        }

        // Check available balance
        if ($affiliate['available_balance'] < $amount) {
            return [
                'can_withdraw' => false, 
                'reason' => 'Insufficient balance'
            ];
        }

        // Check for pending withdrawals
        $pendingCount = $this->countByCondition('affiliate_id = ? AND status = ?', [$affiliateId, 'pending']);
        if ($pendingCount >= 3) {
            return [
                'can_withdraw' => false, 
                'reason' => 'Maximum 3 pending withdrawals allowed'
            ];
        }

        // Check daily withdrawal limit (optional)
        $dailyLimit = 100000; // 100,000 XAF per day
        $todayWithdrawals = $this->getTodayWithdrawals($affiliateId);
        if (($todayWithdrawals + $amount) > $dailyLimit) {
            return [
                'can_withdraw' => false, 
                'reason' => "Daily withdrawal limit of {$dailyLimit} XAF exceeded"
            ];
        }

        return ['can_withdraw' => true];
    }

    public function getTodayWithdrawals(int $affiliateId): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM {$this->table}
                WHERE affiliate_id = ? 
                AND DATE(requested_at) = CURDATE()
                AND status IN ('pending', 'approved', 'processing', 'completed')";
        
        $result = $this->fetchOne($sql, [$affiliateId]);
        return (float)($result['total'] ?? 0);
    }

    public function getWithdrawalHistory(int $affiliateId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM {$this->table}
                WHERE affiliate_id = ?
                ORDER BY requested_at DESC
                LIMIT ? OFFSET ?";
        
        $withdrawals = $this->fetchAll($sql, [$affiliateId, $limit, $offset]);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE affiliate_id = ?";
        $totalResult = $this->fetchOne($countSql, [$affiliateId]);
        $total = $totalResult['total'] ?? 0;
        
        return [
            'withdrawals' => $withdrawals,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    private function validateWithdrawalData(array $data): array
    {
        $rules = [
            'affiliate_id' => 'required|integer',
            'amount' => 'required|numeric|min:10000', // Minimum 10,000 XAF
            'payment_method' => 'required|string|in:mobile_money,bank_transfer,orange_money,mtn_momo',
            'payment_details' => 'required|array'
        ];

        return $this->validate($data, $rules);
    }
}
