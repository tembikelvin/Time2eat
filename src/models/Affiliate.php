<?php

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class Affiliate
{
    use DatabaseTrait;

    protected ?\PDO $db = null;
    protected $table = 'affiliates';
    protected $fillable = [
        'user_id', 'affiliate_code', 'commission_rate', 'total_earnings', 
        'pending_earnings', 'paid_earnings', 'total_referrals',
        'status', 'payment_details'
    ];

    public function createAffiliate(array $data): ?int
    {
        $validation = $this->validateAffiliateData($data);
        if (!$validation['isValid']) {
            $this->setErrors($validation['errors']);
            return null;
        }

        $affiliateData = [
            'user_id' => $data['user_id'],
            'affiliate_code' => $this->generateUniqueReferralCode(),
            'commission_rate' => $data['commission_rate'] ?? $this->getDefaultCommissionRate(),
            'total_earnings' => 0,
            'pending_earnings' => 0,
            'paid_earnings' => 0,
            'total_referrals' => 0,
            'status' => 'active',
            'payment_details' => null
        ];

        return $this->create($affiliateData);
    }

    public function generateUniqueReferralCode(): string
    {
        do {
            $code = 'REF' . strtoupper(substr(uniqid(), -8));
            $exists = $this->findByColumn('affiliate_code', $code);
        } while ($exists);

        return $code;
    }

    public function getAffiliateByUserId(int $userId): ?array
    {
        return $this->findByColumn('user_id', $userId);
    }

    public function getAffiliateByReferralCode(string $referralCode): ?array
    {
        return $this->findByColumn('affiliate_code', $referralCode);
    }

    public function addEarning(int $affiliateId, float $amount, int $orderId, int $customerId, string $type = 'referral'): bool
    {
        $this->beginTransaction();

        try {
            // Update affiliate balance
            $sql = "UPDATE {$this->table} SET
                    total_earnings = total_earnings + ?,
                    pending_earnings = pending_earnings + ?,
                    updated_at = NOW()
                    WHERE id = ?";

            $this->query($sql, [$amount, $amount, $affiliateId]);

            // Record the earning in affiliate_earnings table
            $earningData = [
                'affiliate_id' => $affiliateId,
                'order_id' => $orderId,
                'customer_id' => $customerId,
                'amount' => $amount,
                'type' => $type,
                'status' => 'confirmed',
                'earned_at' => date('Y-m-d H:i:s')
            ];

            $this->insertRecord('affiliate_earnings', $earningData);

            // Update affiliate_referrals table with commission
            $sql = "UPDATE affiliate_referrals
                    SET order_id = ?,
                        commission_amount = commission_amount + ?,
                        status = 'confirmed',
                        updated_at = NOW()
                    WHERE affiliate_id = ? AND referred_user_id = ?
                    LIMIT 1";

            $this->query($sql, [$orderId, $amount, $affiliateId, $customerId]);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->setError('Failed to add earning: ' . $e->getMessage());
            error_log("Affiliate addEarning error: " . $e->getMessage());
            return false;
        }
    }

    public function processWithdrawal(int $affiliateId, float $amount, array $withdrawalData): ?int
    {
        $affiliate = $this->getById($affiliateId);
        if (!$affiliate) {
            $this->setError('Affiliate not found');
            return null;
        }

        // Check minimum withdrawal threshold (10,000 XAF)
        $minWithdrawal = 10000;
        if ($amount < $minWithdrawal) {
            $this->setError("Minimum withdrawal amount is {$minWithdrawal} XAF");
            return null;
        }

        // Check available balance (pending_earnings)
        if ($affiliate['pending_earnings'] < $amount) {
            $this->setError('Insufficient balance');
            return null;
        }

        $this->beginTransaction();

        try {
            // Update affiliate balance
            $sql = "UPDATE {$this->table} SET 
                    pending_earnings = pending_earnings - ?,
                    paid_earnings = paid_earnings + ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $this->query($sql, [$amount, $amount, $affiliateId]);

            // Create withdrawal request
            $withdrawalRequestData = [
                'affiliate_id' => $affiliateId,
                'amount' => $amount,
                'method' => $withdrawalData['payment_method'],
                'reference' => 'WTH' . time(),
                'status' => 'pending',
                'notes' => json_encode($withdrawalData['payment_details'])
            ];

            $withdrawalId = $this->insertRecord('affiliate_payouts', $withdrawalRequestData);

            $this->commit();
            return $withdrawalId;
        } catch (\Exception $e) {
            $this->rollback();
            $this->setError('Failed to process withdrawal: ' . $e->getMessage());
            return null;
        }
    }

    public function getAffiliateStats(int $affiliateId): array
    {
        $affiliate = $this->getById($affiliateId);
        if (!$affiliate) {
            return [];
        }

        // Get referral count
        $referralCount = $this->query("SELECT COUNT(*) as count FROM affiliate_referrals WHERE affiliate_id = ?", [$affiliateId]);
        $referralCount = $referralCount[0]['count'] ?? 0;

        // Get earnings this month
        $monthlyEarnings = $this->query("
            SELECT SUM(commission_amount) as total 
            FROM affiliate_referrals 
            WHERE affiliate_id = ? 
            AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ", [$affiliateId]);
        $monthlyEarnings = $monthlyEarnings[0]['total'] ?? 0;

        // Get pending withdrawals
        $pendingWithdrawals = $this->query("
            SELECT SUM(amount) as total 
            FROM affiliate_payouts 
            WHERE affiliate_id = ? 
            AND status = 'pending'
        ", [$affiliateId]);
        $pendingWithdrawals = $pendingWithdrawals[0]['total'] ?? 0;

        return [
            'total_earnings' => $affiliate['total_earnings'],
            'pending_earnings' => $affiliate['pending_earnings'],
            'paid_earnings' => $affiliate['paid_earnings'],
            'total_referrals' => $referralCount,
            'monthly_earnings' => $monthlyEarnings,
            'pending_withdrawals' => $pendingWithdrawals,
            'commission_rate' => $affiliate['commission_rate'],
            'affiliate_code' => $affiliate['affiliate_code']
        ];
    }

    public function getTopAffiliates(int $limit = 10): array
    {
        $sql = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone
                FROM {$this->table} a
                JOIN users u ON a.user_id = u.id
                WHERE a.status = 'active'
                ORDER BY a.total_earnings DESC
                LIMIT ?";
        
        return $this->query($sql, [$limit]);
    }

    public function updateCommissionRate(int $affiliateId, float $rate): bool
    {
        if ($rate < 0 || $rate > 100) {
            $this->setError('Commission rate must be between 0 and 100');
            return false;
        }

        $sql = "UPDATE {$this->table} SET commission_rate = ?, updated_at = NOW() WHERE id = ?";
        return $this->query($sql, [$rate, $affiliateId]);
    }

    public function getAffiliateEarnings(int $affiliateId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT ar.*, o.order_number, u.first_name, u.last_name
                FROM affiliate_referrals ar
                LEFT JOIN orders o ON ar.order_id = o.id
                LEFT JOIN users u ON ar.referred_user_id = u.id
                WHERE ar.affiliate_id = ?
                ORDER BY ar.created_at DESC
                LIMIT ? OFFSET ?";
        
        $earnings = $this->query($sql, [$affiliateId, $limit, $offset]);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM affiliate_referrals WHERE affiliate_id = ?";
        $totalResult = $this->query($countSql, [$affiliateId]);
        $total = $totalResult[0]['total'] ?? 0;
        
        return [
            'earnings' => $earnings,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    public function getAffiliateWithdrawals(int $affiliateId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM affiliate_payouts 
                WHERE affiliate_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        
        $withdrawals = $this->query($sql, [$affiliateId, $limit, $offset]);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM affiliate_payouts WHERE affiliate_id = ?";
        $totalResult = $this->query($countSql, [$affiliateId]);
        $total = $totalResult[0]['total'] ?? 0;
        
        return [
            'withdrawals' => $withdrawals,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    private function getDefaultCommissionRate(): float
    {
        // Get from settings or return default
        return 5.0; // 5% default commission
    }

    private function validateAffiliateData(array $data): array
    {
        $errors = [];
        
        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            $errors[] = 'User ID is required and must be numeric';
        }
        
        if (isset($data['commission_rate']) && ($data['commission_rate'] < 0 || $data['commission_rate'] > 100)) {
            $errors[] = 'Commission rate must be between 0 and 100';
        }
        
        return [
            'isValid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    private function setError(string $message): void
    {
        $this->lastError = $message;
    }
    
    private function setErrors(array $errors): void
    {
        $this->lastError = implode(', ', $errors);
    }
    
    public function getLastError(): ?string
    {
        return $this->lastError ?? null;
    }
}
