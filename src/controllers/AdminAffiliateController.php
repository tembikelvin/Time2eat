<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';

use core\BaseController;

class AdminAffiliateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
    }

    public function dashboard(): void
    {
        // Check authentication and role
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get search and filter parameters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';
        $sort = $_GET['sort'] ?? 'earnings';
        $order = $_GET['order'] ?? 'desc';

        // Get comprehensive affiliate statistics
        $stats = $this->getAffiliateStats();
        
        // Get real affiliate data with search and sort
        $affiliates = $this->getAllAffiliates($search, $status, $type, $sort, $order);

        // Get pending withdrawals for sidebar
        $withdrawals = $this->fetchAll("
            SELECT w.*, u.first_name, u.last_name, u.email
            FROM withdrawals w
            JOIN users u ON w.user_id = u.id
            WHERE w.status = 'pending' AND w.withdrawal_type = 'affiliate'
            ORDER BY w.created_at DESC
            LIMIT 5
        ");

        $this->renderDashboard('admin/affiliates', [
            'title' => 'Affiliate Management - Admin Dashboard',
            'user' => $userData,
            'stats' => $stats,
            'affiliates' => $affiliates,
            'withdrawals' => $withdrawals,
            'totalAffiliates' => count($affiliates),
            'currentPage' => 'affiliates',
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'sort' => $sort,
            'order' => $order
        ]);
    }

    public function affiliates(): void
    {
        // Redirect to main dashboard for now
        $this->redirect(url('/admin/affiliate/dashboard'));
    }

    public function withdrawals(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get withdrawal requests with filters
        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';

        $withdrawals = $this->getWithdrawalRequests($status, $search, $dateFrom, $dateTo);
        $withdrawalStats = $this->getWithdrawalStats();

        $this->renderDashboard('admin/affiliate-withdrawals', [
            'title' => 'Affiliate Withdrawals - Admin Dashboard',
            'user' => $userData,
            'withdrawals' => $withdrawals,
            'stats' => $withdrawalStats,
            'currentPage' => 'affiliate-withdrawals',
            'filters' => [
                'status' => $status,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]
        ]);
    }

    public function payouts(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get payout history and pending payouts
        $payouts = $this->getPayoutHistory();
        $payoutStats = $this->getPayoutStats();

        $this->renderDashboard('admin/affiliate-payouts', [
            'title' => 'Affiliate Payouts - Admin Dashboard',
            'user' => $userData,
            'payouts' => $payouts,
            'stats' => $payoutStats,
            'currentPage' => 'affiliate-payouts'
        ]);
    }

    public function affiliateDetails(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        $affiliateId = $_GET['id'] ?? null;
        if (!$affiliateId) {
            $this->redirect(url('/admin/affiliate/dashboard'));
            return;
        }

        $user = $this->getCurrentUser();
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get detailed affiliate information
        $affiliate = $this->getAffiliateDetails($affiliateId);
        if (!$affiliate) {
            $this->redirect(url('/admin/affiliate/dashboard'));
            return;
        }

        $this->renderDashboard('admin/affiliate-details', [
            'title' => 'Affiliate Details - Admin Dashboard',
            'user' => $userData,
            'affiliate' => $affiliate,
            'currentPage' => 'affiliate-details'
        ]);
    }

    public function approveWithdrawal(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            // Get input data (JSON or POST)
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $withdrawalId = $input['withdrawal_id'] ?? null;
            $adminNotes = $input['admin_notes'] ?? '';

            if (!$withdrawalId) {
                echo json_encode(['success' => false, 'message' => 'Withdrawal ID required']);
                return;
            }

            // Get withdrawal details
            $withdrawal = $this->fetchOne(
                "SELECT w.*, w.user_id, u.first_name, u.last_name, u.email
                 FROM withdrawals w
                 JOIN users u ON w.user_id = u.id
                 WHERE w.id = ? AND w.withdrawal_type = 'affiliate'",
                [$withdrawalId]
            );

            if (!$withdrawal) {
                echo json_encode(['success' => false, 'message' => 'Withdrawal not found']);
                return;
            }

            if ($withdrawal['status'] !== 'pending') {
                echo json_encode(['success' => false, 'message' => 'Withdrawal already processed']);
                return;
            }

            $this->beginTransaction();

            // Update withdrawal status
            $this->query(
                "UPDATE withdrawals SET
                 status = 'approved',
                 processed_at = NOW(),
                 admin_notes = ?,
                 processed_by = ?
                 WHERE id = ? AND withdrawal_type = 'affiliate'",
                [$adminNotes, $this->getCurrentUser()->id, $withdrawalId]
            );

            // Update affiliate balance
            $this->query(
                "UPDATE affiliates SET
                 pending_earnings = pending_earnings - ?,
                 paid_earnings = paid_earnings + ?,
                 updated_at = NOW()
                 WHERE id = ?",
                [$withdrawal['amount'], $withdrawal['amount'], $withdrawal['affiliate_id']]
            );

            // Create payout record
            $this->query(
                "INSERT INTO affiliate_payouts (withdrawal_id, affiliate_id, amount, payment_method, payment_details, status, processed_at, created_at)
                 VALUES (?, ?, ?, ?, ?, 'processing', NOW(), NOW())",
                [
                    $withdrawalId,
                    $withdrawal['affiliate_id'],
                    $withdrawal['amount'],
                    $withdrawal['payment_method'],
                    $withdrawal['payment_details']
                ]
            );

            // Log the action
            $this->logAdminAction('affiliate_withdrawal_approved', [
                'withdrawal_id' => $withdrawalId,
                'affiliate_id' => $withdrawal['affiliate_id'],
                'amount' => $withdrawal['amount'],
                'admin_notes' => $adminNotes
            ]);

            $this->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Withdrawal approved successfully',
                'withdrawal' => [
                    'id' => $withdrawalId,
                    'status' => 'approved',
                    'processed_at' => date('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            $this->rollback();
            error_log("Error approving withdrawal: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to approve withdrawal']);
        }
    }

    public function rejectWithdrawal(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            // Get input data (JSON or POST)
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $withdrawalId = $input['withdrawal_id'] ?? null;
            $reason = $input['reason'] ?? 'Withdrawal rejected by admin';

            if (!$withdrawalId) {
                echo json_encode(['success' => false, 'message' => 'Withdrawal ID required']);
                return;
            }

            // Get withdrawal details
            $withdrawal = $this->fetchOne(
                "SELECT w.*, w.user_id
                 FROM withdrawals w
                 WHERE w.id = ? AND w.withdrawal_type = 'affiliate'",
                [$withdrawalId]
            );

            if (!$withdrawal) {
                echo json_encode(['success' => false, 'message' => 'Withdrawal not found']);
                return;
            }

            if ($withdrawal['status'] !== 'pending') {
                echo json_encode(['success' => false, 'message' => 'Withdrawal already processed']);
                return;
            }

            // Update withdrawal status
            $this->query(
                "UPDATE affiliate_withdrawals SET
                 status = 'rejected',
                 processed_at = NOW(),
                 admin_notes = ?,
                 updated_at = NOW()
                 WHERE id = ?",
                [$reason, $withdrawalId]
            );

            // Log the action
            $this->logAdminAction('affiliate_withdrawal_rejected', [
                'withdrawal_id' => $withdrawalId,
                'affiliate_id' => $withdrawal['affiliate_id'],
                'amount' => $withdrawal['amount'],
                'reason' => $reason
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Withdrawal rejected successfully',
                'withdrawal' => [
                    'id' => $withdrawalId,
                    'status' => 'rejected',
                    'processed_at' => date('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Error rejecting withdrawal: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to reject withdrawal']);
        }
    }

    public function updateCommissionRate(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Feature not implemented yet']);
    }

    /**
     * View individual affiliate details
     */
    public function viewAffiliate(int $id): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        $affiliate = $this->getAffiliateById($id);
        if (!$affiliate) {
            $this->redirect(url('/admin/affiliate/dashboard'));
            return;
        }

        $userData = $this->getCurrentUserData();
        
        $this->renderDashboard('admin/affiliate-details', [
            'title' => 'Affiliate Details - Admin Dashboard',
            'user' => $userData,
            'affiliate' => $affiliate,
            'currentPage' => 'affiliates'
        ]);
    }

    // Removed editAffiliate method - now using inline editing

    /**
     * Suspend affiliate
     */
    public function suspendAffiliate(int $id): void
    {
        header('Content-Type: application/json');
        
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $success = $this->updateAffiliateStatus($id, 'suspended');
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Affiliate suspended successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to suspend affiliate']);
            }
        } catch (\Exception $e) {
            error_log("Error suspending affiliate: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Activate affiliate
     */
    public function activateAffiliate(int $id): void
    {
        header('Content-Type: application/json');
        
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $success = $this->updateAffiliateStatus($id, 'active');
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Affiliate activated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to activate affiliate']);
            }
        } catch (\Exception $e) {
            error_log("Error activating affiliate: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Update affiliate information
     */
    public function updateAffiliate(int $id): void
    {
        header('Content-Type: application/json');
        
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            // Handle PUT request body
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            
            if (!$input || !is_array($input)) {
                error_log("Invalid PUT request data for affiliate {$id}: " . $rawInput);
                echo json_encode(['success' => false, 'message' => 'Invalid input data. Expected JSON.']);
            return;
        }

            // Validate commission_rate if provided
            if (isset($input['commission_rate'])) {
                $rate = floatval($input['commission_rate']);
                if ($rate < 0 || $rate > 1) {
                    echo json_encode(['success' => false, 'message' => 'Commission rate must be between 0 and 1 (0% to 100%)']);
                    return;
                }
            }

            $success = $this->updateAffiliateData($id, $input);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Affiliate updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update affiliate. No changes made.']);
            }
        } catch (\Exception $e) {
            error_log("Error updating affiliate {$id}: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get comprehensive affiliate statistics
     */
    private function getAffiliateStats(): array
    {
        $stats = [
            'total_affiliates' => 0,
            'active_affiliates' => 0,
            'total_earnings' => 0,
            'total_withdrawals' => 0,
            'pending_withdrawals_count' => 0,
            'conversion_rate' => 0
        ];

        try {
            // Get affiliate counts
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM affiliates");
            $stats['total_affiliates'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM affiliates WHERE status = 'active'");
            $stats['active_affiliates'] = $result['count'] ?? 0;

            // Get total earnings (commissions)
            $result = $this->fetchOne("SELECT SUM(total_earnings) as total FROM affiliates");
            $stats['total_commissions'] = $result['total'] ?? 0;

            // Get total withdrawals
            $result = $this->fetchOne("SELECT SUM(total_withdrawals) as total FROM affiliates");
            $stats['total_withdrawals'] = $result['total'] ?? 0;

            // Get pending withdrawals
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'pending' AND withdrawal_type = 'affiliate'");
            $stats['pending_withdrawals_count'] = $result['count'] ?? 0;

            // Calculate conversion rate (simplified)
            $totalReferrals = $this->fetchOne("SELECT SUM(total_referrals) as total FROM affiliates");
            $totalAffiliates = $stats['total_affiliates'];
            if ($totalAffiliates > 0) {
                $stats['conversion_rate'] = round(($totalReferrals['total'] ?? 0) / $totalAffiliates, 1);
            }

        } catch (\Exception $e) {
            error_log("Error getting affiliate stats: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Get all affiliates with user information
     */
    private function getAllAffiliates(string $search = '', string $status = '', string $type = '', string $sort = 'earnings', string $order = 'desc'): array
    {
        try {
            $sql = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone, u.status as user_status
                    FROM affiliates a
                    JOIN users u ON a.user_id = u.id";
            
            $whereConditions = [];
            $params = [];
            
            // Search functionality
            if (!empty($search)) {
                $whereConditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR a.affiliate_code LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            // Status filter
            if (!empty($status)) {
                $whereConditions[] = "a.status = ?";
                $params[] = $status;
            }
            
            // Type filter (based on user role or other criteria)
            if (!empty($type)) {
                // For now, we'll use user role as type
                $whereConditions[] = "u.role = ?";
                $params[] = $type;
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            
            // Sorting
            $validSorts = ['earnings', 'referrals', 'name', 'created_at', 'status'];
            $validOrders = ['asc', 'desc'];
            
            if (!in_array($sort, $validSorts)) {
                $sort = 'earnings';
            }
            if (!in_array($order, $validOrders)) {
                $order = 'desc';
            }
            
            switch ($sort) {
                case 'earnings':
                    $sortColumn = 'a.total_earnings';
                    break;
                case 'referrals':
                    $sortColumn = 'a.total_referrals';
                    break;
                case 'name':
                    $sortColumn = 'u.first_name';
                    break;
                case 'created_at':
                    $sortColumn = 'a.created_at';
                    break;
                case 'status':
                    $sortColumn = 'a.status';
                    break;
                default:
                    $sortColumn = 'a.total_earnings';
            }
            
            $sql .= " ORDER BY {$sortColumn} {$order}";
            
            return $this->fetchAll($sql, $params);
        } catch (\Exception $e) {
            error_log("Error getting affiliates: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get affiliate by ID with user information
     */
    private function getAffiliateById(int $affiliateId): ?array
    {
        try {
            $sql = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone, u.status as user_status
                    FROM affiliates a
                    JOIN users u ON a.user_id = u.id
                    WHERE a.id = ?";
            
            return $this->fetchOne($sql, [$affiliateId]);
        } catch (\Exception $e) {
            error_log("Error getting affiliate by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update affiliate status
     */
    private function updateAffiliateStatus(int $affiliateId, string $status): bool
    {
        try {
            // Get affiliate info first
            $affiliate = $this->fetchOne("SELECT user_id, affiliate_code FROM affiliates WHERE id = ?", [$affiliateId]);
            if (!$affiliate) {
                return false;
            }

            // Update affiliates table
            $sql = "UPDATE affiliates SET status = ?, updated_at = NOW() WHERE id = ?";
            $this->query($sql, [$status, $affiliateId]);

            // Sync with users table
            if ($status === 'active') {
                // Restore affiliate_code in users table
                $userSql = "UPDATE users SET affiliate_code = ?, updated_at = NOW() WHERE id = ?";
                $this->query($userSql, [$affiliate['affiliate_code'], $affiliate['user_id']]);
            } else {
                // Clear affiliate_code in users table when suspended/inactive
                $userSql = "UPDATE users SET affiliate_code = NULL, updated_at = NOW() WHERE id = ?";
                $this->query($userSql, [$affiliate['user_id']]);
            }

            return true;
        } catch (\Exception $e) {
            error_log("Error updating affiliate status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update affiliate data
     */
    private function updateAffiliateData(int $affiliateId, array $data): bool
    {
        try {
            $allowedFields = ['commission_rate', 'status'];
            $updateFields = [];
            $values = [];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "{$field} = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                return false;
            }

            $updateFields[] = "updated_at = NOW()";
            $values[] = $affiliateId;

            $sql = "UPDATE affiliates SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $this->query($sql, $values);
            return true;
        } catch (\Exception $e) {
            error_log("Error updating affiliate data: " . $e->getMessage());
            return false;
        }
    }


    // Removed commissionSettings and updateCommissionSettings methods - commissions are now edited inline from the table


    /**
     * Get current user data as array
     */
    private function getCurrentUserData(): array
    {
        $user = $this->getCurrentUser();
        return [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];
    }

    /**
     * Render dashboard with proper layout
     */
    protected function renderDashboard(string $view, array $data = []): void
    {
        // Get current user and convert to array for view compatibility
        $currentUser = $this->getCurrentUser();
        if ($currentUser) {
            $userData = [
                'id' => $currentUser->id,
                'email' => $currentUser->email,
                'first_name' => $currentUser->first_name,
                'last_name' => $currentUser->last_name,
                'role' => $currentUser->role,
                'status' => $currentUser->status ?? 'active'
            ];
            
            // Add user data to view data if not already set
            if (!isset($data['user'])) {
                $data['user'] = $userData;
            }
            if (!isset($data['userRole'])) {
                $data['userRole'] = $currentUser->role;
            }
        }
        
        // Start output buffering to capture the dashboard content
        ob_start();

        // Extract data for the view
        extract($data);

        // Include the specific dashboard view using correct relative path
        $viewPath = __DIR__ . "/../views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("Dashboard view not found: {$view}");
        }
        include $viewPath;

        // Get the content
        $content = ob_get_clean();

        // Render with dashboard layout using correct relative path
        $layoutPath = __DIR__ . "/../views/components/dashboard-layout.php";
        if (!file_exists($layoutPath)) {
            throw new \Exception("Dashboard layout not found: dashboard-layout.php");
        }
        include $layoutPath;
    }

    /**
     * Get withdrawal requests with filters
     */
    private function getWithdrawalRequests(string $status = 'all', string $search = '', string $dateFrom = '', string $dateTo = ''): array
    {
        try {
            $sql = "SELECT w.*, u.first_name, u.last_name, u.email
                    FROM withdrawals w
                    JOIN users u ON w.user_id = u.id
                    WHERE w.withdrawal_type = 'affiliate'";

            $whereConditions = [];
            $params = [];

            if ($status !== 'all') {
                $sql .= " AND w.status = ?";
                $params[] = $status;
            }

            if (!empty($search)) {
                $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($dateFrom)) {
                $sql .= " AND DATE(w.created_at) >= ?";
                $params[] = $dateFrom;
            }

            if (!empty($dateTo)) {
                $sql .= " AND DATE(w.created_at) <= ?";
                $params[] = $dateTo;
            }

            $sql .= " ORDER BY w.created_at DESC";

            return $this->fetchAll($sql, $params);
        } catch (\Exception $e) {
            error_log("Error getting withdrawal requests: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get withdrawal statistics
     */
    private function getWithdrawalStats(): array
    {
        try {
            $stats = [
                'total_requests' => 0,
                'pending_requests' => 0,
                'approved_requests' => 0,
                'rejected_requests' => 0,
                'total_amount' => 0,
                'pending_amount' => 0,
                'approved_amount' => 0
            ];

            // Get total requests
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM withdrawals WHERE withdrawal_type = 'affiliate'");
            $stats['total_requests'] = $result['count'] ?? 0;

            // Get pending requests
            $result = $this->fetchOne("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as amount FROM withdrawals WHERE status = 'pending' AND withdrawal_type = 'affiliate'");
            $stats['pending_requests'] = $result['count'] ?? 0;
            $stats['pending_amount'] = $result['amount'] ?? 0;

            // Get approved requests
            $result = $this->fetchOne("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as amount FROM withdrawals WHERE status = 'approved' AND withdrawal_type = 'affiliate'");
            $stats['approved_requests'] = $result['count'] ?? 0;
            $stats['approved_amount'] = $result['amount'] ?? 0;

            // Get rejected requests
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'rejected' AND withdrawal_type = 'affiliate'");
            $stats['rejected_requests'] = $result['count'] ?? 0;

            // Get total amount
            $result = $this->fetchOne("SELECT COALESCE(SUM(amount), 0) as amount FROM withdrawals WHERE withdrawal_type = 'affiliate'");
            $stats['total_amount'] = $result['amount'] ?? 0;

            return $stats;
        } catch (\Exception $e) {
            error_log("Error getting withdrawal stats: " . $e->getMessage());
            return [
                'total_requests' => 0,
                'pending_requests' => 0,
                'approved_requests' => 0,
                'rejected_requests' => 0,
                'total_amount' => 0,
                'pending_amount' => 0,
                'approved_amount' => 0
            ];
        }
    }

    /**
     * Get payout history
     */
    private function getPayoutHistory(): array
    {
        try {
            $sql = "SELECT ap.*, aw.requested_at, a.affiliate_code, u.first_name, u.last_name, u.email
                    FROM affiliate_payouts ap
                    JOIN affiliate_withdrawals aw ON ap.withdrawal_id = aw.id
                    JOIN affiliates a ON ap.affiliate_id = a.id
                    JOIN users u ON a.user_id = u.id
                    ORDER BY ap.processed_at DESC";

            return $this->fetchAll($sql);
        } catch (\Exception $e) {
            error_log("Error getting payout history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payout statistics
     */
    private function getPayoutStats(): array
    {
        try {
            $stats = [
                'total_payouts' => 0,
                'processing_payouts' => 0,
                'completed_payouts' => 0,
                'failed_payouts' => 0,
                'total_amount' => 0,
                'processing_amount' => 0,
                'completed_amount' => 0
            ];

            // Get total payouts
            $result = $this->fetchOne("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as amount FROM affiliate_payouts");
            $stats['total_payouts'] = $result['count'] ?? 0;
            $stats['total_amount'] = $result['amount'] ?? 0;

            // Get processing payouts
            $result = $this->fetchOne("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as amount FROM affiliate_payouts WHERE status = 'processing'");
            $stats['processing_payouts'] = $result['count'] ?? 0;
            $stats['processing_amount'] = $result['amount'] ?? 0;

            // Get completed payouts
            $result = $this->fetchOne("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as amount FROM affiliate_payouts WHERE status = 'completed'");
            $stats['completed_payouts'] = $result['count'] ?? 0;
            $stats['completed_amount'] = $result['amount'] ?? 0;

            // Get failed payouts
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM affiliate_payouts WHERE status = 'failed'");
            $stats['failed_payouts'] = $result['count'] ?? 0;

            return $stats;
        } catch (\Exception $e) {
            error_log("Error getting payout stats: " . $e->getMessage());
            return [
                'total_payouts' => 0,
                'processing_payouts' => 0,
                'completed_payouts' => 0,
                'failed_payouts' => 0,
                'total_amount' => 0,
                'processing_amount' => 0,
                'completed_amount' => 0
            ];
        }
    }

    /**
     * Get detailed affiliate information
     */
    private function getAffiliateDetails(int $affiliateId): ?array
    {
        try {
            // Get basic affiliate info
            $affiliate = $this->fetchOne(
                "SELECT a.*, u.first_name, u.last_name, u.email, u.phone, u.created_at as user_created_at
                 FROM affiliates a
                 JOIN users u ON a.user_id = u.id
                 WHERE a.id = ?",
                [$affiliateId]
            );

            if (!$affiliate) {
                return null;
            }

            // Get referral history
            $affiliate['referrals'] = $this->fetchAll(
                "SELECT ar.*, o.order_number, u.first_name as referred_first_name, u.last_name as referred_last_name
                 FROM affiliate_referrals ar
                 LEFT JOIN orders o ON ar.order_id = o.id
                 LEFT JOIN users u ON ar.referred_user_id = u.id
                 WHERE ar.affiliate_id = ?
                 ORDER BY ar.created_at DESC
                 LIMIT 20",
                [$affiliateId]
            );

            // Get withdrawal history
            $affiliate['withdrawals'] = $this->fetchAll(
                "SELECT * FROM affiliate_withdrawals
                 WHERE affiliate_id = ?
                 ORDER BY requested_at DESC
                 LIMIT 10",
                [$affiliateId]
            );

            // Get performance metrics
            $affiliate['metrics'] = $this->fetchOne(
                "SELECT
                    COUNT(ar.id) as total_referrals,
                    COUNT(CASE WHEN ar.status = 'confirmed' THEN 1 END) as successful_referrals,
                    COALESCE(SUM(ar.commission_amount), 0) as total_commissions,
                    COALESCE(AVG(ar.commission_amount), 0) as avg_commission
                 FROM affiliate_referrals ar
                 WHERE ar.affiliate_id = ?",
                [$affiliateId]
            );

            return $affiliate;
        } catch (\Exception $e) {
            error_log("Error getting affiliate details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Process bulk payouts
     */
    public function processPayouts(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            // Get input data (JSON or POST)
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $withdrawalIds = $input['withdrawal_ids'] ?? [];

            if (empty($withdrawalIds)) {
                echo json_encode(['success' => false, 'message' => 'No withdrawals selected']);
                return;
            }

            $this->beginTransaction();

            $processedCount = 0;
            $errors = [];

            foreach ($withdrawalIds as $withdrawalId) {
                try {
                    // Get withdrawal details
                    $withdrawal = $this->fetchOne(
                        "SELECT aw.*, a.user_id
                         FROM affiliate_withdrawals aw
                         JOIN affiliates a ON aw.affiliate_id = a.id
                         WHERE aw.id = ? AND aw.status = 'pending'",
                        [$withdrawalId]
                    );

                    if (!$withdrawal) {
                        $errors[] = "Withdrawal ID {$withdrawalId} not found or already processed";
                        continue;
                    }

                    // Update withdrawal status to approved
                    $this->query(
                        "UPDATE affiliate_withdrawals SET
                         status = 'approved',
                         processed_at = NOW(),
                         admin_notes = 'Bulk processed',
                         updated_at = NOW()
                         WHERE id = ?",
                        [$withdrawalId]
                    );

                    // Update affiliate balance
                    $this->query(
                        "UPDATE affiliates SET
                         pending_earnings = pending_earnings - ?,
                         paid_earnings = paid_earnings + ?,
                         updated_at = NOW()
                         WHERE id = ?",
                        [$withdrawal['amount'], $withdrawal['amount'], $withdrawal['affiliate_id']]
                    );

                    // Create payout record
                    $this->query(
                        "INSERT INTO affiliate_payouts (withdrawal_id, affiliate_id, amount, payment_method, payment_details, status, processed_at, created_at)
                         VALUES (?, ?, ?, ?, ?, 'processing', NOW(), NOW())",
                        [
                            $withdrawalId,
                            $withdrawal['affiliate_id'],
                            $withdrawal['amount'],
                            $withdrawal['payment_method'],
                            $withdrawal['payment_details']
                        ]
                    );

                    $processedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Error processing withdrawal ID {$withdrawalId}: " . $e->getMessage();
                }
            }

            // Log the bulk action
            $this->logAdminAction('affiliate_bulk_payout_processed', [
                'processed_count' => $processedCount,
                'total_requested' => count($withdrawalIds),
                'errors' => $errors
            ]);

            $this->commit();

            $message = "Successfully processed {$processedCount} payouts";
            if (!empty($errors)) {
                $message .= " with " . count($errors) . " errors";
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'processed_count' => $processedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            $this->rollback();
            error_log("Error processing bulk payouts: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to process payouts']);
        }
    }

    /**
     * Update payout status
     */
    public function updatePayoutStatus(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            // Get input data (JSON or POST)
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $payoutId = $input['payout_id'] ?? null;
            $status = $input['status'] ?? null;
            $transactionId = $input['transaction_id'] ?? null;
            $notes = $input['notes'] ?? null;

            if (!$payoutId || !$status) {
                echo json_encode(['success' => false, 'message' => 'Payout ID and status required']);
                return;
            }

            $validStatuses = ['processing', 'completed', 'failed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                return;
            }

            // Build update query
            $updateFields = ['status = ?', 'updated_at = NOW()'];
            $params = [$status];

            if ($transactionId) {
                $updateFields[] = 'transaction_id = ?';
                $params[] = $transactionId;
            }

            if ($status === 'completed') {
                $updateFields[] = 'completed_at = NOW()';
            }

            $params[] = $payoutId;

            $sql = "UPDATE affiliate_payouts SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $this->query($sql, $params);

            // Log the action
            $this->logAdminAction('affiliate_payout_status_updated', [
                'payout_id' => $payoutId,
                'status' => $status,
                'transaction_id' => $transactionId,
                'notes' => $notes
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Payout status updated successfully'
            ]);

        } catch (\Exception $e) {
            error_log("Error updating payout status: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to update payout status']);
        }
    }

    // Removed analytics, communication, sendMessage, and createCampaign methods - functionality consolidated into main page

    /**
     * Get affiliate analytics data
     */
    private function getAffiliateAnalytics(): array
    {
        try {
            // Performance metrics
            $performanceMetrics = $this->fetchAll(
                "SELECT
                    DATE(ae.created_at) as date,
                    COUNT(ae.id) as total_referrals,
                    SUM(ae.commission_amount) as total_commissions,
                    COUNT(DISTINCT ae.affiliate_id) as active_affiliates
                 FROM affiliate_earnings ae
                 WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY DATE(ae.created_at)
                 ORDER BY date DESC"
            );

            // Top performing affiliates
            $topPerformers = $this->fetchAll(
                "SELECT
                    a.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    a.affiliate_code,
                    COUNT(ae.id) as total_referrals,
                    SUM(ae.commission_amount) as total_earnings,
                    AVG(ae.commission_amount) as avg_commission
                 FROM affiliates a
                 JOIN users u ON a.user_id = u.id
                 LEFT JOIN affiliate_earnings ae ON a.id = ae.affiliate_id
                 WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY a.id
                 ORDER BY total_earnings DESC
                 LIMIT 10"
            );

            // Conversion rates by source
            $conversionRates = $this->fetchAll(
                "SELECT
                    a.referral_source,
                    COUNT(DISTINCT a.id) as total_affiliates,
                    COUNT(ae.id) as total_referrals,
                    ROUND((COUNT(ae.id) / COUNT(DISTINCT a.id)) * 100, 2) as conversion_rate
                 FROM affiliates a
                 LEFT JOIN affiliate_earnings ae ON a.id = ae.affiliate_id
                 GROUP BY a.referral_source
                 ORDER BY conversion_rate DESC"
            );

            return [
                'performance_metrics' => $performanceMetrics,
                'top_performers' => $topPerformers,
                'conversion_rates' => $conversionRates
            ];

        } catch (\Exception $e) {
            error_log("Error getting affiliate analytics: " . $e->getMessage());
            return [
                'performance_metrics' => [],
                'top_performers' => [],
                'conversion_rates' => []
            ];
        }
    }

    /**
     * Get affiliate messages
     */
    private function getAffiliateMessages(): array
    {
        try {
            return $this->fetchAll(
                "SELECT
                    am.*,
                    u.first_name,
                    u.last_name,
                    u.email,
                    a.affiliate_code
                 FROM affiliate_messages am
                 JOIN affiliates a ON am.affiliate_id = a.id
                 JOIN users u ON a.user_id = u.id
                 WHERE am.sender_type = 'admin'
                 ORDER BY am.created_at DESC
                 LIMIT 50"
            );
        } catch (\Exception $e) {
            error_log("Error getting affiliate messages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get message templates
     */
    private function getMessageTemplates(): array
    {
        return [
            [
                'id' => 'welcome',
                'name' => 'Welcome Message',
                'subject' => 'Welcome to our Affiliate Program!',
                'content' => 'Welcome to our affiliate program! We\'re excited to have you on board.'
            ],
            [
                'id' => 'milestone',
                'name' => 'Milestone Achievement',
                'subject' => 'Congratulations on your milestone!',
                'content' => 'Congratulations on reaching a new milestone in your affiliate journey!'
            ],
            [
                'id' => 'payout',
                'name' => 'Payout Notification',
                'subject' => 'Your payout has been processed',
                'content' => 'Your affiliate payout has been successfully processed.'
            ]
        ];
    }

    /**
     * Log admin actions for audit trail
     */
    private function logAdminAction(string $action, array $details): void
    {
        try {
            $user = $this->getCurrentUser();
            $this->query(
                "INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $user->id,
                    $action,
                    json_encode($details),
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            error_log("Error logging admin action: " . $e->getMessage());
        }
    }
}
