<?php

namespace Time2Eat\Services;

require_once __DIR__ . '/../../config/database.php';

/**
 * Withdrawal Service
 * Handles withdrawal requests for affiliates, riders, and restaurants
 */
class WithdrawalService
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    /**
     * Create a withdrawal request
     */
    public function createWithdrawal(array $withdrawalData): array
    {
        try {
            $requiredFields = ['user_id', 'amount', 'withdrawal_type', 'payment_method'];
            foreach ($requiredFields as $field) {
                if (!isset($withdrawalData[$field])) {
                    return [
                        'success' => false,
                        'message' => "Missing required field: $field"
                    ];
                }
            }

            // Validate withdrawal type
            $validTypes = ['affiliate', 'rider', 'restaurant'];
            if (!in_array($withdrawalData['withdrawal_type'], $validTypes)) {
                return [
                    'success' => false,
                    'message' => 'Invalid withdrawal type'
                ];
            }

            // Check user balance
            $balance = $this->getUserBalance($withdrawalData['user_id'], $withdrawalData['withdrawal_type']);
            if ($balance < $withdrawalData['amount']) {
                return [
                    'success' => false,
                    'message' => 'Insufficient balance for withdrawal'
                ];
            }

            // Check minimum withdrawal amount
            $minAmount = $this->getMinimumWithdrawalAmount($withdrawalData['withdrawal_type']);
            if ($withdrawalData['amount'] < $minAmount) {
                return [
                    'success' => false,
                    'message' => "Minimum withdrawal amount is {$minAmount} XAF"
                ];
            }

            // Generate withdrawal reference
            $withdrawalRef = 'WD-' . strtoupper(uniqid());

            // Create withdrawal record
            $sql = "INSERT INTO withdrawals (
                user_id,
                withdrawal_type,
                amount,
                payment_method,
                account_details,
                status,
                withdrawal_reference,
                created_at
            ) VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $withdrawalData['user_id'],
                $withdrawalData['withdrawal_type'],
                $withdrawalData['amount'],
                $withdrawalData['payment_method'],
                json_encode($withdrawalData['account_details'] ?? []),
                $withdrawalRef
            ]);

            if ($result) {
                $withdrawalId = $this->db->lastInsertId();
                
                // Log withdrawal request
                $this->logWithdrawalActivity($withdrawalId, 'requested', 'Withdrawal request created');

                return [
                    'success' => true,
                    'withdrawal_id' => $withdrawalId,
                    'withdrawal_reference' => $withdrawalRef,
                    'message' => 'Withdrawal request submitted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create withdrawal request'
                ];
            }

        } catch (\Exception $e) {
            error_log("Withdrawal creation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create withdrawal request: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process a withdrawal (approve/reject)
     */
    public function processWithdrawal(int $withdrawalId, string $action, array $adminData = []): array
    {
        try {
            // Get withdrawal details
            $withdrawal = $this->getWithdrawalById($withdrawalId);
            if (!$withdrawal) {
                return [
                    'success' => false,
                    'message' => 'Withdrawal not found'
                ];
            }

            if ($withdrawal['status'] !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Withdrawal has already been processed'
                ];
            }

            $newStatus = $action === 'approve' ? 'approved' : 'rejected';
            $reason = $adminData['reason'] ?? '';

            // Update withdrawal status
            $sql = "UPDATE withdrawals 
                    SET status = ?, 
                        processed_by = ?, 
                        processed_at = NOW(),
                        admin_notes = ?
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $newStatus,
                $adminData['admin_id'] ?? null,
                $reason,
                $withdrawalId
            ]);

            if ($result) {
                // Log activity
                $this->logWithdrawalActivity($withdrawalId, $newStatus, $reason);

                // If approved, update user balance
                if ($action === 'approve') {
                    $this->updateUserBalance($withdrawal['user_id'], $withdrawal['withdrawal_type'], -$withdrawal['amount']);
                }

                return [
                    'success' => true,
                    'message' => "Withdrawal {$newStatus} successfully"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to process withdrawal'
                ];
            }

        } catch (\Exception $e) {
            error_log("Withdrawal processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process withdrawal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user balance for specific type
     */
    public function getUserBalance(int $userId, string $type): float
    {
        try {
            $sql = "SELECT balance FROM user_balances WHERE user_id = ? AND balance_type = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $type]);
            $result = $stmt->fetch();

            return $result ? (float)$result['balance'] : 0.0;
        } catch (\Exception $e) {
            error_log("Error getting user balance: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get minimum withdrawal amount for type
     */
    public function getMinimumWithdrawalAmount(string $type): int
    {
        $minimums = [
            'affiliate' => 5000,  // 5000 XAF
            'rider' => 2000,      // 2000 XAF
            'restaurant' => 10000 // 10000 XAF
        ];

        return $minimums[$type] ?? 1000;
    }

    /**
     * Get withdrawal by ID
     */
    public function getWithdrawalById(int $withdrawalId): ?array
    {
        try {
            $sql = "SELECT w.*, u.first_name, u.last_name, u.email, u.phone
                    FROM withdrawals w
                    JOIN users u ON w.user_id = u.id
                    WHERE w.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$withdrawalId]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Error getting withdrawal: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get withdrawals with filters
     */
    public function getWithdrawals(array $filters = []): array
    {
        try {
            $whereConditions = [];
            $params = [];

            if (!empty($filters['status'])) {
                $whereConditions[] = "w.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['withdrawal_type'])) {
                $whereConditions[] = "w.withdrawal_type = ?";
                $params[] = $filters['withdrawal_type'];
            }

            if (!empty($filters['user_id'])) {
                $whereConditions[] = "w.user_id = ?";
                $params[] = $filters['user_id'];
            }

            if (!empty($filters['date_from'])) {
                $whereConditions[] = "DATE(w.created_at) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $whereConditions[] = "DATE(w.created_at) <= ?";
                $params[] = $filters['date_to'];
            }

            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

            $sql = "SELECT w.*, u.first_name, u.last_name, u.email, u.phone,
                           a.first_name as admin_first_name, a.last_name as admin_last_name
                    FROM withdrawals w
                    JOIN users u ON w.user_id = u.id
                    LEFT JOIN users a ON w.processed_by = a.id
                    {$whereClause}
                    ORDER BY w.created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT " . (int)$filters['limit'];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error getting withdrawals: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user balance
     */
    private function updateUserBalance(int $userId, string $type, float $amount): void
    {
        try {
            $sql = "INSERT INTO user_balances (user_id, balance_type, balance, updated_at)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    balance = balance + ?, updated_at = NOW()";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $type, $amount, $amount]);
        } catch (\Exception $e) {
            error_log("Error updating user balance: " . $e->getMessage());
        }
    }

    /**
     * Log withdrawal activity
     */
    private function logWithdrawalActivity(int $withdrawalId, string $action, string $description): void
    {
        try {
            $sql = "INSERT INTO withdrawal_logs (withdrawal_id, action, description, created_at)
                    VALUES (?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$withdrawalId, $action, $description]);
        } catch (\Exception $e) {
            error_log("Error logging withdrawal activity: " . $e->getMessage());
        }
    }

    /**
     * Get withdrawal statistics
     */
    public function getWithdrawalStats(): array
    {
        try {
            $sql = "SELECT 
                        withdrawal_type,
                        status,
                        COUNT(*) as count,
                        SUM(amount) as total_amount
                    FROM withdrawals 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY withdrawal_type, status";

            $stmt = $this->db->query($sql);
            $results = $stmt->fetchAll();

            $stats = [
                'total_pending' => 0,
                'total_approved' => 0,
                'total_rejected' => 0,
                'total_amount_pending' => 0,
                'total_amount_approved' => 0,
                'by_type' => []
            ];

            foreach ($results as $row) {
                $type = $row['withdrawal_type'];
                $status = $row['status'];
                $count = (int)$row['count'];
                $amount = (float)$row['total_amount'];

                if (!isset($stats['by_type'][$type])) {
                    $stats['by_type'][$type] = [
                        'pending' => 0,
                        'approved' => 0,
                        'rejected' => 0,
                        'amount_pending' => 0,
                        'amount_approved' => 0
                    ];
                }

                $stats['by_type'][$type][$status] = $count;
                $stats['by_type'][$type]['amount_' . $status] = $amount;

                $stats['total_' . $status] += $count;
                $stats['total_amount_' . $status] += $amount;
            }

            return $stats;
        } catch (\Exception $e) {
            error_log("Error getting withdrawal stats: " . $e->getMessage());
            return [];
        }
    }
}
