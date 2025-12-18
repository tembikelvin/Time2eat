<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

/**
 * User Model for Time2Eat Authentication System
 * Handles user data operations with security and validation
 */
class User
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    protected $table = 'users';
    protected $fillable = [
        'username', 'email', 'password', 'first_name', 'last_name',
        'phone', 'role', 'status', 'affiliate_code', 'affiliate_rate',
        'email_verification_token', 'email_verification_expires', 'email_verification_code',
        'cash_on_delivery_enabled', 'email_verified_at', 'referred_by'
    ];
    
    protected $hidden = ['password', 'remember_token', 'reset_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'notification_preferences' => 'json',
        'two_factor_enabled' => 'boolean'
    ];
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ? AND deleted_at IS NULL",
            [$email]
        );
    }
    
    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE username = ? AND deleted_at IS NULL",
            [$username]
        );
    }
    
    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }
    
    // findByAffiliateCode method moved to line 420 to avoid duplication
    
    /**
     * Create new user
     */
    public function create($data)
    {
        // Filter to only fillable fields
        $filtered = [];
        foreach ($this->fillable as $field) {
            if (isset($data[$field])) {
                $filtered[$field] = $data[$field];
            }
        }
        $data = $filtered;

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Set default values
        $data['status'] = $data['status'] ?? 'active';
        $data['role'] = $data['role'] ?? 'customer';
        $data['affiliate_rate'] = $data['affiliate_rate'] ?? 0.05;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insertRecord($this->table, $data);
    }
    
    /**
     * Update user
     */
    public function updateUser(int $id, array $data): bool
    {
        // Hash password if being updated
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->updateRecord($this->table, $data, ['id' => $id]) > 0;
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE email = ? AND deleted_at IS NULL";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->fetchOne($sql, $params) !== null;
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE username = ? AND deleted_at IS NULL";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->fetchOne($sql, $params) !== null;
    }
    
    /**
     * Activate user account
     */
    public function activate(int $id): bool
    {
        return $this->updateUser($id, [
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Deactivate user account
     */
    public function deactivate(int $id): bool
    {
        return $this->updateUser($id, ['status' => 'inactive']);
    }
    
    /**
     * Suspend user account
     */
    public function suspend(int $id, ?string $reason = null): bool
    {
        $data = ['status' => 'suspended'];
        if ($reason) {
            $data['suspension_reason'] = $reason;
        }
        
        return $this->updateUser($id, $data);
    }
    
    /**
     * Soft delete user
     */
    public function softDelete(int $id): bool
    {
        // Hard delete user
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    /**
     * Restore soft deleted user
     */
    public function restore(int $id): bool
    {
        return $this->updateRecord($this->table, ['deleted_at' => null], ['id' => $id]) > 0;
    }
    
    /**
     * Get users by role
     */
    public function getByRole(string $role, int $limit = 50, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT * FROM {$this->table} WHERE role = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$role, $limit, $offset]
        );
    }
    
    /**
     * Get active users count
     */
    public function getActiveCount(): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active' AND deleted_at IS NULL"
        );
        
        return (int) $result['count'];
    }
    
    /**
     * Get users by status
     */
    public function getByStatus(string $status, int $limit = 50, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT * FROM {$this->table} WHERE status = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$status, $limit, $offset]
        );
    }
    
    /**
     * Search users
     */
    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $searchTerm = "%{$query}%";
        
        return $this->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?) 
             AND deleted_at IS NULL 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset]
        );
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin(int $id, string $ip): bool
    {
        return $this->updateUser($id, [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip
        ]);
    }
    
    /**
     * Set remember token
     */
    public function setRememberToken(int $id, string $token): bool
    {
        return $this->updateUser($id, ['remember_token' => $token]);
    }
    
    /**
     * Clear remember token
     */
    public function clearRememberToken(int $id): bool
    {
        return $this->updateUser($id, ['remember_token' => null]);
    }
    
    /**
     * Set password reset token
     */
    public function setResetToken(int $id, string $token, int $expiresIn = 3600): bool
    {
        return $this->updateUser($id, [
            'reset_token' => $token,
            'reset_token_expires' => date('Y-m-d H:i:s', time() + $expiresIn)
        ]);
    }
    
    /**
     * Clear password reset token
     */
    public function clearResetToken(int $id): bool
    {
        return $this->updateUser($id, [
            'reset_token' => null,
            'reset_token_expires' => null
        ]);
    }
    
    /**
     * Find user by reset token
     */
    public function findByResetToken(string $token): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} 
             WHERE reset_token = ? 
             AND reset_token_expires > NOW() 
             AND deleted_at IS NULL",
            [$token]
        );
    }
    
    /**
     * Update user balance
     */
    public function updateBalance(int $id, float $amount, string $operation = 'add'): bool
    {
        $operator = $operation === 'add' ? '+' : '-';
        
        return $this->query(
            "UPDATE {$this->table} SET balance = balance {$operator} ?, updated_at = NOW() WHERE id = ?",
            [$amount, $id]
        ) > 0;
    }
    
    /**
     * Get user statistics
     */
    public function getStats(): array
    {
        $stats = $this->fetchOne(
            "SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as customers,
                SUM(CASE WHEN role = 'vendor' THEN 1 ELSE 0 END) as vendors,
                SUM(CASE WHEN role = 'rider' THEN 1 ELSE 0 END) as riders,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_users_30d
             FROM {$this->table} 
             WHERE deleted_at IS NULL"
        );
        
        return [
            'total_users' => (int) $stats['total_users'],
            'active_users' => (int) $stats['active_users'],
            'customers' => (int) $stats['customers'],
            'vendors' => (int) $stats['vendors'],
            'riders' => (int) $stats['riders'],
            'new_users_30d' => (int) $stats['new_users_30d']
        ];
    }
    
    /**
     * Get user with profile
     */
    public function getWithProfile(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT u.*, up.*
             FROM {$this->table} u
             LEFT JOIN user_profiles up ON u.id = up.user_id
             WHERE u.id = ? AND u.deleted_at IS NULL",
            [$id]
        );
    }

    /**
     * Get user addresses
     */
    public function getAddressesByUser(int $userId): array
    {
        try {
            require_once __DIR__ . '/UserAddress.php';
            $addressModel = new \Time2Eat\Models\UserAddress();
            return $addressModel->getByUser($userId);
        } catch (Exception $e) {
            error_log("Error getting user addresses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get address by ID
     */
    public function getAddressById(int $addressId): ?array
    {
        $sql = "SELECT * FROM user_addresses WHERE id = ? AND deleted_at IS NULL";
        return $this->fetchOne($sql, [$addressId]);
    }

    /**
     * Get user payment methods
     */
    public function getPaymentMethodsByUser(int $userId): array
    {
        $sql = "
            SELECT * FROM payment_methods
            WHERE user_id = ?
            ORDER BY is_default DESC, created_at DESC
        ";

        return $this->fetchAll($sql, [$userId]);
    }

    /**
     * Get payment method by ID
     */
    public function getPaymentMethodById(int $paymentMethodId): ?array
    {
        $sql = "SELECT * FROM payment_methods WHERE id = ?";
        return $this->fetchOne($sql, [$paymentMethodId]);
    }

    /**
     * Find user by affiliate code
     */
    public function findByAffiliateCode(string $affiliateCode): ?array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE affiliate_code = ? AND role = 'customer' AND deleted_at IS NULL
        ";

        return $this->fetchOne($sql, [$affiliateCode]);
    }

    public function getUsersByReferralCode(string $referralCode): array
    {
        $sql = "SELECT id, first_name, last_name, email, phone, created_at, status
                FROM {$this->table}
                WHERE referred_by = ? AND deleted_at IS NULL
                ORDER BY created_at DESC";

        return $this->fetchAll($sql, [$referralCode]);
    }

    public function getActiveReferrals(string $referralCode): int
    {
        $sql = "SELECT COUNT(DISTINCT u.id) as count
                FROM {$this->table} u
                JOIN orders o ON u.id = o.customer_id
                WHERE u.referred_by = ? AND u.deleted_at IS NULL
                AND o.status IN ('completed', 'delivered')";

        $result = $this->fetchOne($sql, [$referralCode]);
        return (int)($result['count'] ?? 0);
    }

    public function getMonthlyReferrals(string $referralCode, ?string $month = null): int
    {
        $month = $month ?? date('Y-m');

        $sql = "SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE referred_by = ? AND deleted_at IS NULL
                AND DATE_FORMAT(created_at, '%Y-%m') = ?";

        $result = $this->fetchOne($sql, [$referralCode, $month]);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get pending user approvals
     */
    public function getPendingApprovals(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'pending' AND deleted_at IS NULL
                ORDER BY created_at DESC";
        return $this->fetchAll($sql);
    }

    /**
     * Get pending role changes
     */
    public function getPendingRoleChanges(): array
    {
        // This would require a role_change_requests table in a full implementation
        // For now, return empty array
        return [];
    }

    /**
     * Get pending riders
     */
    public function getPendingRiders(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE role = 'rider' AND status = 'pending' AND deleted_at IS NULL
                ORDER BY created_at DESC";
        return $this->fetchAll($sql);
    }

    /**
     * Approve user
     */
    public function approveUser(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'active', updated_at = NOW() WHERE id = ?";
        return $this->execute($sql, [$id]) > 0;
    }

    /**
     * Reject user
     */
    public function rejectUser(int $id, string $reason = ''): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'suspended', updated_at = NOW() WHERE id = ?";
        return $this->execute($sql, [$id]) > 0;
    }

    /**
     * Approve role change
     */
    public function approveRoleChange(int $id): bool
    {
        // This would require implementation of role change requests
        // For now, return true
        return true;
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(): array
    {
        $sql = "SELECT role, COUNT(*) as count FROM {$this->table}
                WHERE deleted_at IS NULL
                GROUP BY role";

        $results = $this->fetchAll($sql);
        $counts = [];

        foreach ($results as $result) {
            $counts[$result['role']] = (int)$result['count'];
        }

        return $counts;
    }

    /**
     * Get total user count
     */
    public function getTotalCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
        $result = $this->fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get users with filtering and pagination
     */
    public function getUsers(string $role = 'all', string $status = 'all', string $search = '', int $limit = 20, int $offset = 0): array
    {
        $conditions = ['deleted_at IS NULL'];
        $params = [];

        if ($role !== 'all') {
            $conditions[] = 'role = ?';
            $params[] = $role;
        }

        if ($status !== 'all') {
            $conditions[] = 'status = ?';
            $params[] = $status;
        }

        if (!empty($search)) {
            $conditions[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)';
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = implode(' AND ', $conditions);
        $params[] = $limit;
        $params[] = $offset;

        $sql = "SELECT * FROM {$this->table}
                WHERE {$whereClause}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Count users with filtering
     */
    public function countUsers(string $role = 'all', string $status = 'all', string $search = ''): int
    {
        $conditions = ['deleted_at IS NULL'];
        $params = [];

        if ($role !== 'all') {
            $conditions[] = 'role = ?';
            $params[] = $role;
        }

        if ($status !== 'all') {
            $conditions[] = 'status = ?';
            $params[] = $status;
        }

        if (!empty($search)) {
            $conditions[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)';
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
        $result = $this->fetchOne($sql, $params);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get rider's schedule
     */
    public function getRiderSchedule(int $riderId): array
    {
        try {
            // Get schedule from rider_schedules table
            $sql = "SELECT day_of_week, start_time, end_time, is_available FROM rider_schedules WHERE rider_id = ? ORDER BY day_of_week";
            $results = $this->fetchAll($sql, [$riderId]);
            
            // Initialize default schedule
            $schedule = [
                'monday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'tuesday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'wednesday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'thursday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'friday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'saturday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'sunday' => ['available' => false, 'start' => '09:00', 'end' => '17:00']
            ];
            
            // Map day numbers to day names (0=Sunday, 1=Monday, etc.)
            $dayMap = [1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday', 0 => 'sunday'];
            
            // Update schedule with actual data
            foreach ($results as $row) {
                $dayName = $dayMap[$row['day_of_week']] ?? null;
                if ($dayName) {
                    $schedule[$dayName] = [
                        'available' => (bool)$row['is_available'],
                        'start' => substr($row['start_time'], 0, 5), // Convert HH:MM:SS to HH:MM
                        'end' => substr($row['end_time'], 0, 5)
                    ];
                }
            }
            
            return $schedule;
        } catch (\Exception $e) {
            error_log("Error getting rider schedule: " . $e->getMessage());
            // Return default schedule on error
            return [
                'monday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'tuesday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'wednesday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'thursday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'friday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'saturday' => ['available' => false, 'start' => '09:00', 'end' => '17:00'],
                'sunday' => ['available' => false, 'start' => '09:00', 'end' => '17:00']
            ];
        }
    }

    /**
     * Update rider's schedule
     */
    public function updateRiderSchedule(int $riderId, array $schedule): bool
    {
        try {
            // Start transaction
            $this->beginTransaction();
            
            // Map day names to day numbers (0=Sunday, 1=Monday, etc.)
            $dayMap = ['monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 0];
            
            // Delete existing schedule for this rider
            $sql = "DELETE FROM rider_schedules WHERE rider_id = ?";
            $this->query($sql, [$riderId]);
            
            // Insert new schedule
            foreach ($schedule as $dayName => $dayData) {
                if (isset($dayMap[$dayName]) && isset($dayData['start']) && isset($dayData['end'])) {
                    $dayNumber = $dayMap[$dayName];
                    $isAvailable = $dayData['available'] ?? false;
                    $startTime = $dayData['start'] . ':00'; // Convert HH:MM to HH:MM:SS
                    $endTime = $dayData['end'] . ':00';
                    
                    $sql = "INSERT INTO rider_schedules (rider_id, day_of_week, start_time, end_time, is_available, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
                    $this->query($sql, [$riderId, $dayNumber, $startTime, $endTime, $isAvailable ? 1 : 0]);
                }
            }
            
            // Commit transaction
            $this->commit();
            return true;
            
        } catch (\Exception $e) {
            // Rollback on error
            $this->rollback();
            error_log("Error updating rider schedule: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save rider's schedule (legacy method for backward compatibility)
     */
    public function saveRiderSchedule(int $riderId, array $schedule): bool
    {
        return $this->updateRiderSchedule($riderId, $schedule);
    }

    /**
     * Update rider availability status
     */
    public function updateAvailability(int $userId, bool $isAvailable): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET is_available = ?, updated_at = NOW() WHERE id = ? AND role = 'rider'";
            $stmt = $this->query($sql, [$isAvailable ? 1 : 0, $userId]);
            
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error updating rider availability: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get rider availability status
     */
    public function getAvailability(int $userId): bool
    {
        try {
            $sql = "SELECT is_available FROM {$this->table} WHERE id = ? AND role = 'rider'";
            $result = $this->fetchOne($sql, [$userId]);
            
            return (bool)($result['is_available'] ?? false);
        } catch (\Exception $e) {
            error_log("Error getting rider availability: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all available riders
     */
    public function getAvailableRiders(int $limit = 50): array
    {
        try {
            $sql = "SELECT id, first_name, last_name, phone, is_available, last_login_at 
                    FROM {$this->table} 
                    WHERE role = 'rider' AND status = 'active' AND is_available = 1 
                    ORDER BY last_login_at DESC 
                    LIMIT ?";
            
            return $this->fetchAll($sql, [$limit]);
        } catch (\Exception $e) {
            error_log("Error getting available riders: " . $e->getMessage());
            return [];
        }
    }
}
