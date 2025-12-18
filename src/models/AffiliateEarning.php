<?php

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class AffiliateEarning
{
    use DatabaseTrait;

    protected ?\PDO $db = null;
    protected $table = 'affiliate_earnings';
    protected $fillable = [
        'affiliate_id', 'order_id', 'amount', 'type', 'status', 'earned_at'
    ];

    public function recordEarning(array $data): ?int
    {
        $validation = $this->validateEarningData($data);
        if (!$validation['isValid']) {
            $this->setErrors($validation['errors']);
            return null;
        }

        $earningData = [
            'affiliate_id' => $data['affiliate_id'],
            'order_id' => $data['order_id'],
            'amount' => $data['amount'],
            'type' => $data['type'] ?? 'referral',
            'status' => $data['status'] ?? 'confirmed',
            'earned_at' => $data['earned_at'] ?? date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($earningData);
    }

    public function getEarningsByAffiliate(int $affiliateId, array $filters = []): array
    {
        $conditions = ['affiliate_id = ?'];
        $params = [$affiliateId];

        // Add date filters
        if (!empty($filters['start_date'])) {
            $conditions[] = 'earned_at >= ?';
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $conditions[] = 'earned_at <= ?';
            $params[] = $filters['end_date'];
        }

        // Add type filter
        if (!empty($filters['type'])) {
            $conditions[] = 'type = ?';
            $params[] = $filters['type'];
        }

        // Add status filter
        if (!empty($filters['status'])) {
            $conditions[] = 'status = ?';
            $params[] = $filters['status'];
        }

        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT ae.*, o.order_number, o.total_amount as order_total,
                       u.first_name, u.last_name, u.email
                FROM {$this->table} ae
                LEFT JOIN orders o ON ae.order_id = o.id
                LEFT JOIN users u ON o.customer_id = u.id
                WHERE {$whereClause}
                ORDER BY ae.earned_at DESC";

        return $this->fetchAll($sql, $params);
    }

    public function getMonthlyEarnings(int $affiliateId, ?string $month = null): float
    {
        $month = $month ?? date('Y-m');
        
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM {$this->table}
                WHERE affiliate_id = ? 
                AND DATE_FORMAT(earned_at, '%Y-%m') = ?
                AND status = 'confirmed'";
        
        $result = $this->fetchOne($sql, [$affiliateId, $month]);
        return (float)($result['total'] ?? 0);
    }

    public function getDailyEarnings(int $affiliateId, ?string $date = null): float
    {
        $date = $date ?? date('Y-m-d');
        
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM {$this->table}
                WHERE affiliate_id = ? 
                AND DATE(earned_at) = ?
                AND status = 'confirmed'";
        
        $result = $this->fetchOne($sql, [$affiliateId, $date]);
        return (float)($result['total'] ?? 0);
    }

    public function getEarningsStats(int $affiliateId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_transactions,
                    COALESCE(SUM(amount), 0) as total_earnings,
                    COALESCE(AVG(amount), 0) as average_earning,
                    COALESCE(MAX(amount), 0) as highest_earning,
                    COALESCE(MIN(amount), 0) as lowest_earning
                FROM {$this->table}
                WHERE affiliate_id = ? AND status = 'confirmed'";
        
        $stats = $this->fetchOne($sql, [$affiliateId]);
        
        // Get monthly breakdown
        $monthlySql = "SELECT 
                          DATE_FORMAT(earned_at, '%Y-%m') as month,
                          COALESCE(SUM(amount), 0) as earnings,
                          COUNT(*) as transactions
                       FROM {$this->table}
                       WHERE affiliate_id = ? AND status = 'confirmed'
                       AND earned_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                       GROUP BY DATE_FORMAT(earned_at, '%Y-%m')
                       ORDER BY month DESC";
        
        $monthlyStats = $this->fetchAll($monthlySql, [$affiliateId]);
        
        return [
            'overview' => $stats,
            'monthly' => $monthlyStats
        ];
    }

    public function getTopEarningOrders(int $affiliateId, int $limit = 10): array
    {
        $sql = "SELECT ae.*, o.order_number, o.total_amount as order_total,
                       u.first_name, u.last_name, r.name as restaurant_name
                FROM {$this->table} ae
                LEFT JOIN orders o ON ae.order_id = o.id
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                WHERE ae.affiliate_id = ? AND ae.status = 'confirmed'
                ORDER BY ae.amount DESC
                LIMIT ?";
        
        return $this->fetchAll($sql, [$affiliateId, $limit]);
    }

    public function getEarningsByType(int $affiliateId): array
    {
        $sql = "SELECT 
                    type,
                    COUNT(*) as count,
                    COALESCE(SUM(amount), 0) as total_amount,
                    COALESCE(AVG(amount), 0) as average_amount
                FROM {$this->table}
                WHERE affiliate_id = ? AND status = 'confirmed'
                GROUP BY type
                ORDER BY total_amount DESC";
        
        return $this->fetchAll($sql, [$affiliateId]);
    }

    public function updateEarningStatus(int $earningId, string $status): bool
    {
        $validStatuses = ['pending', 'confirmed', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            $this->setError('Invalid status');
            return false;
        }

        return $this->update($earningId, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getTotalEarningsForPeriod(int $affiliateId, string $startDate, string $endDate): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM {$this->table}
                WHERE affiliate_id = ? 
                AND earned_at BETWEEN ? AND ?
                AND status = 'confirmed'";
        
        $result = $this->fetchOne($sql, [$affiliateId, $startDate, $endDate]);
        return (float)($result['total'] ?? 0);
    }

    public function getEarningsGrowth(int $affiliateId): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(earned_at, '%Y-%m') as month,
                    COALESCE(SUM(amount), 0) as earnings
                FROM {$this->table}
                WHERE affiliate_id = ? AND status = 'confirmed'
                AND earned_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(earned_at, '%Y-%m')
                ORDER BY month ASC";
        
        $monthlyData = $this->fetchAll($sql, [$affiliateId]);
        
        $growth = [];
        $previousEarnings = 0;
        
        foreach ($monthlyData as $data) {
            $currentEarnings = (float)$data['earnings'];
            $growthRate = $previousEarnings > 0 
                ? (($currentEarnings - $previousEarnings) / $previousEarnings) * 100 
                : 0;
            
            $growth[] = [
                'month' => $data['month'],
                'earnings' => $currentEarnings,
                'growth_rate' => round($growthRate, 2)
            ];
            
            $previousEarnings = $currentEarnings;
        }
        
        return $growth;
    }

    private function validateEarningData(array $data): array
    {
        $rules = [
            'affiliate_id' => 'required|integer',
            'order_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'type' => 'string|in:referral,bonus,commission',
            'status' => 'string|in:pending,confirmed,cancelled'
        ];

        return $this->validate($data, $rules);
    }
}
