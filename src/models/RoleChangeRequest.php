<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

/**
 * Role Change Request Model
 * Handles user role change requests and approvals
 */
class RoleChangeRequest
{
    use DatabaseTrait;

    protected ?\PDO $db = null;
    protected $table = 'role_change_requests';

    /**
     * Create a new role change request
     */
    public function createRequest(array $data): bool
    {
        // Check if user already has a pending request
        $existingRequest = $this->getPendingRequestByUserId($data['user_id']);
        if ($existingRequest) {
            return false; // User already has a pending request
        }

        $requestData = [
            'user_id' => $data['user_id'],
            'current_role' => $data['current_role'],
            'requested_role' => $data['requested_role'],
            'reason' => $data['reason'] ?? '',
            'documents' => isset($data['documents']) ? json_encode($data['documents']) : null,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insertRecord($this->table, $requestData) > 0;
    }

    /**
     * Get request by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $results = $this->fetchAll($sql, [$id]);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Get pending request by user ID
     */
    public function getPendingRequestByUserId(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1";
        $results = $this->fetchAll($sql, [$userId]);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Get all role change requests with user details
     */
    public function getAllRequestsWithUserDetails(string $status = '', int $limit = 50, int $offset = 0): array
    {
        $whereClause = '';
        $params = [];

        if (!empty($status)) {
            $whereClause = "WHERE rcr.status = ?";
            $params[] = $status;
        }

        $sql = "
            SELECT 
                rcr.*,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.created_at as user_created_at,
                admin.first_name as admin_first_name,
                admin.last_name as admin_last_name
            FROM {$this->table} rcr
            JOIN users u ON rcr.user_id = u.id
            LEFT JOIN users admin ON rcr.reviewed_by = admin.id
            {$whereClause}
            ORDER BY rcr.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        return $this->fetchAll($sql, $params);
    }

    /**
     * Approve role change request
     */
    public function approveRequest(int $requestId, int $adminId, string $adminNotes = ''): bool
    {
        try {
            $this->beginTransaction();

            // Get the request
            $request = $this->getById($requestId);
            if (!$request || $request['status'] !== 'pending') {
                $this->rollback();
                return false;
            }

            // Update user role
            $userUpdateSql = "UPDATE users SET role = ?, updated_at = ? WHERE id = ?";
            $this->query($userUpdateSql, [
                $request['requested_role'],
                date('Y-m-d H:i:s'),
                $request['user_id']
            ]);

            // Update request status
            $requestUpdateData = [
                'status' => 'approved',
                'reviewed_by' => $adminId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'admin_notes' => $adminNotes,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->updateRecord($this->table, $requestUpdateData, ['id' => $requestId]);

            // Log the role change
            $this->logRoleChange($request['user_id'], $request['current_role'], $request['requested_role'], $adminId);

            $this->commit();
            return true;

        } catch (\Exception $e) {
            $this->rollback();
            error_log("Error approving role change request: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject role change request
     */
    public function rejectRequest(int $requestId, int $adminId, string $adminNotes = ''): bool
    {
        $updateData = [
            'status' => 'rejected',
            'reviewed_by' => $adminId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'admin_notes' => $adminNotes,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->updateRecord($this->table, $updateData, ['id' => $requestId]) > 0;
    }

    /**
     * Get request statistics
     */
    public function getRequestStats(): array
    {
        $sql = "
            SELECT
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as requests_this_month
            FROM {$this->table}
        ";

        $results = $this->fetchAll($sql);
        $result = !empty($results) ? $results[0] : null;
        return $result ?: [
            'total_requests' => 0,
            'pending_requests' => 0,
            'approved_requests' => 0,
            'rejected_requests' => 0,
            'requests_this_month' => 0
        ];
    }

    /**
     * Log role change for audit trail
     */
    private function logRoleChange(int $userId, string $oldRole, string $newRole, int $adminId): void
    {
        try {
            // Create role_change_logs table if it doesn't exist
            $createTableSql = "
                CREATE TABLE IF NOT EXISTS role_change_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    old_role VARCHAR(50) NOT NULL,
                    new_role VARCHAR(50) NOT NULL,
                    changed_by BIGINT UNSIGNED NOT NULL,
                    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    INDEX idx_user_id (user_id),
                    INDEX idx_changed_by (changed_by),
                    INDEX idx_changed_at (changed_at),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $this->query($createTableSql);

            // Insert log entry
            $logData = [
                'user_id' => $userId,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'changed_by' => $adminId,
                'changed_at' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];

            $this->insertRecord('role_change_logs', $logData);

        } catch (\Exception $e) {
            error_log("Error logging role change: " . $e->getMessage());
        }
    }

    /**
     * Create role_change_requests table if it doesn't exist
     */
    public function createTableIfNotExists(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                current_role VARCHAR(50) NOT NULL,
                requested_role VARCHAR(50) NOT NULL,
                reason TEXT,
                documents JSON,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                reviewed_by BIGINT UNSIGNED NULL,
                reviewed_at TIMESTAMP NULL,
                admin_notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $this->query($sql);
    }
}
