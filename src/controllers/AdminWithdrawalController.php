<?php

namespace controllers;

require_once __DIR__ . '/AdminBaseController.php';
require_once __DIR__ . '/../services/WithdrawalService.php';

use controllers\AdminBaseController;
use Time2Eat\Services\WithdrawalService;

class AdminWithdrawalController extends AdminBaseController
{
    private $withdrawalService;

    public function __construct()
    {
        parent::__construct();
        $this->withdrawalService = new WithdrawalService();
    }

    /**
     * Display withdrawal management page
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        // Get filters
        $filters = [
            'status' => $_GET['status'] ?? '',
            'withdrawal_type' => $_GET['withdrawal_type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'limit' => 50
        ];

        // Get withdrawals
        $withdrawals = $this->withdrawalService->getWithdrawals($filters);

        // Get statistics
        $stats = $this->withdrawalService->getWithdrawalStats();

        $this->renderDashboard('admin/withdrawals', [
            'title' => 'Withdrawal Management',
            'currentPage' => 'withdrawals',
            'withdrawals' => $withdrawals,
            'stats' => $stats,
            'filters' => $filters
        ]);
    }

    /**
     * Process withdrawal (approve/reject)
     */
    public function process(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $withdrawalId = (int)($input['withdrawal_id'] ?? 0);
        $action = $input['action'] ?? '';
        $reason = $input['reason'] ?? '';

        if (!$withdrawalId || !in_array($action, ['approve', 'reject'])) {
            $this->json(['success' => false, 'message' => 'Invalid parameters'], 400);
            return;
        }

        $user = $this->getCurrentUser();
        $adminData = [
            'admin_id' => $user->id,
            'reason' => $reason
        ];

        $result = $this->withdrawalService->processWithdrawal($withdrawalId, $action, $adminData);

        if ($result['success']) {
            $this->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }

    /**
     * Get withdrawal details
     */
    public function details(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $withdrawalId = (int)($_GET['id'] ?? 0);

        if (!$withdrawalId) {
            $this->json(['success' => false, 'message' => 'Withdrawal ID required'], 400);
            return;
        }

        $withdrawal = $this->withdrawalService->getWithdrawalById($withdrawalId);

        if (!$withdrawal) {
            $this->json(['success' => false, 'message' => 'Withdrawal not found'], 404);
            return;
        }

        $this->json([
            'success' => true,
            'withdrawal' => $withdrawal
        ]);
    }

    /**
     * Get withdrawal statistics
     */
    public function stats(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $stats = $this->withdrawalService->getWithdrawalStats();

        $this->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Export withdrawals
     */
    public function export(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $filters = [
            'status' => $_GET['status'] ?? '',
            'withdrawal_type' => $_GET['withdrawal_type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $withdrawals = $this->withdrawalService->getWithdrawals($filters);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="withdrawals_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'ID',
            'User Name',
            'Email',
            'Phone',
            'Type',
            'Amount (XAF)',
            'Payment Method',
            'Status',
            'Reference',
            'Created At',
            'Processed At',
            'Admin Notes'
        ]);

        // CSV data
        foreach ($withdrawals as $withdrawal) {
            fputcsv($output, [
                $withdrawal['id'],
                $withdrawal['first_name'] . ' ' . $withdrawal['last_name'],
                $withdrawal['email'],
                $withdrawal['phone'],
                ucfirst($withdrawal['withdrawal_type']),
                number_format($withdrawal['amount'], 2),
                $withdrawal['payment_method'],
                ucfirst($withdrawal['status']),
                $withdrawal['withdrawal_reference'],
                $withdrawal['created_at'],
                $withdrawal['processed_at'] ?? '',
                $withdrawal['admin_notes'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }
}
