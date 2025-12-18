<?php

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Affiliate.php';

use core\BaseController;
use models\Affiliate;

class AffiliateController extends BaseController
{
    private $affiliateModel;

    public function __construct()
    {
        parent::__construct();
        $this->affiliateModel = new Affiliate();
    }

    public function dashboard(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();

        // Get or create affiliate record
        $affiliate = $this->affiliateModel->getAffiliateByUserId($user->id);
        if (!$affiliate) {
            $affiliateId = $this->affiliateModel->createAffiliate(['user_id' => $user->id]);
            $affiliate = $this->affiliateModel->getById($affiliateId);
        }

        // Get affiliate statistics
        $stats = $this->affiliateModel->getAffiliateStats($affiliate['id']);
        
        // Get recent earnings
        $earningsData = $this->affiliateModel->getAffiliateEarnings($affiliate['id'], 1, 10);
        $recentEarnings = $earningsData['earnings'] ?? [];
        
        // Get recent withdrawals
        $withdrawalsData = $this->affiliateModel->getAffiliateWithdrawals($affiliate['id'], 1, 5);
        $recentWithdrawals = $withdrawalsData['withdrawals'] ?? [];

        $this->render('affiliate/dashboard', [
            'title' => 'Affiliate Dashboard - Time2Eat',
            'user' => $user,
            'affiliate' => $affiliate,
            'stats' => $stats,
            'recent_earnings' => $recentEarnings,
            'recent_withdrawals' => $recentWithdrawals
        ]);
    }

    public function earnings(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();

        $affiliate = $this->affiliateModel->getAffiliateByUserId($user->id);
        if (!$affiliate) {
            $this->redirect(url('/affiliate/dashboard'));
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'type' => $_GET['type'] ?? ''
        ];

        // Get earnings with pagination
        $earningsData = $this->affiliateModel->getAffiliateEarnings($affiliate['id'], $page, 20);

        $this->render('affiliate/earnings', [
            'title' => 'My Earnings - Time2Eat',
            'user' => $user,
            'affiliate' => $affiliate,
            'earnings_data' => $earningsData,
            'filters' => $filters
        ]);
    }

    public function withdrawals(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();

        $affiliate = $this->affiliateModel->getAffiliateByUserId($user->id);
        if (!$affiliate) {
            $this->redirect(url('/affiliate/dashboard'));
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        
        // Get withdrawals with pagination
        $withdrawalsData = $this->affiliateModel->getAffiliateWithdrawals($affiliate['id'], $page, 20);

        $this->render('affiliate/withdrawals', [
            'title' => 'My Withdrawals - Time2Eat',
            'user' => $user,
            'affiliate' => $affiliate,
            'withdrawals_data' => $withdrawalsData
        ]);
    }

    public function requestWithdrawal(): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processWithdrawalRequest();
            return;
        }

        $user = $this->getCurrentUser();
        $affiliate = $this->affiliateModel->getAffiliateByUserId($user->id);
        
        if (!$affiliate) {
            $this->redirect(url('/affiliate/dashboard'));
            return;
        }

        $this->render('affiliate/request-withdrawal', [
            'title' => 'Request Withdrawal - Time2Eat',
            'user' => $user,
            'affiliate' => $affiliate
        ]);
    }

    public function processWithdrawalRequest(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();

        $input = json_decode(file_get_contents('php://input'), true);
        
        // Basic validation
        if (empty($input['amount']) || !is_numeric($input['amount'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Valid amount is required'], 400);
            return;
        }
        
        if (empty($input['payment_method'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Payment method is required'], 400);
            return;
        }

        $affiliate = $this->affiliateModel->getAffiliateByUserId($user->id);
        if (!$affiliate) {
            $this->jsonResponse(['success' => false, 'message' => 'Affiliate account not found'], 404);
            return;
        }

        // Process withdrawal request
        $withdrawalId = $this->affiliateModel->processWithdrawal($affiliate['id'], $input['amount'], $input);

        if ($withdrawalId) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully',
                'withdrawal_id' => $withdrawalId
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => $this->affiliateModel->getLastError()
            ], 400);
        }
    }

    public function referrals(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();

        $affiliate = $this->affiliateModel->getAffiliateByUserId($user->id);
        if (!$affiliate) {
            $this->redirect(url('/affiliate/dashboard'));
            return;
        }

        // Get referred users from affiliate_referrals table
        $referredUsers = $this->affiliateModel->query("
            SELECT ar.*, u.first_name, u.last_name, u.email, u.phone
            FROM affiliate_referrals ar
            JOIN users u ON ar.referred_user_id = u.id
            WHERE ar.affiliate_id = ?
            ORDER BY ar.created_at DESC
        ", [$affiliate['id']]);
        
        // Get referral statistics
        $referralStats = $this->getReferralStats($affiliate['id']);

        $this->render('affiliate/referrals', [
            'title' => 'My Referrals - Time2Eat',
            'user' => $user,
            'affiliate' => $affiliate,
            'referred_users' => $referredUsers,
            'referral_stats' => $referralStats
        ]);
    }

    public function validateReferralCode(): void
    {
        $referralCode = $_GET['code'] ?? '';
        
        if (empty($referralCode)) {
            $this->jsonResponse(['valid' => false, 'message' => 'Referral code required']);
            return;
        }

        $affiliate = $this->affiliateModel->getAffiliateByReferralCode($referralCode);
        
        if ($affiliate && $affiliate['status'] === 'active') {
            $user = $this->affiliateModel->query("SELECT first_name, last_name FROM users WHERE id = ?", [$affiliate['user_id']]);
            $user = $user[0] ?? null;
            
            if ($user) {
                $this->jsonResponse([
                    'valid' => true,
                    'affiliate' => [
                        'name' => $user['first_name'] . ' ' . $user['last_name'],
                        'commission_rate' => $affiliate['commission_rate']
                    ]
                ]);
            } else {
                $this->jsonResponse(['valid' => false, 'message' => 'Affiliate user not found']);
            }
        } else {
            $this->jsonResponse(['valid' => false, 'message' => 'Invalid or inactive referral code']);
        }
    }

    public function getAffiliateStats(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();

        $affiliate = $this->affiliateModel->getAffiliateByUserId($user->id);
        if (!$affiliate) {
            $this->jsonResponse(['success' => false, 'message' => 'Affiliate not found'], 404);
            return;
        }

        $stats = $this->affiliateModel->getAffiliateStats($affiliate['id']);
        
        $this->jsonResponse([
            'success' => true,
            'stats' => $stats
        ]);
    }

    private function getReferralStats(int $affiliateId): array
    {
        // Get total referrals
        $totalReferrals = $this->affiliateModel->query("SELECT COUNT(*) as count FROM affiliate_referrals WHERE affiliate_id = ?", [$affiliateId]);
        $totalReferrals = $totalReferrals[0]['count'] ?? 0;
        
        // Get active referrals (users who have placed orders)
        $activeReferrals = $this->affiliateModel->query("
            SELECT COUNT(DISTINCT referred_user_id) as count 
            FROM affiliate_referrals 
            WHERE affiliate_id = ? AND order_id IS NOT NULL
        ", [$affiliateId]);
        $activeReferrals = $activeReferrals[0]['count'] ?? 0;
        
        // Get referrals this month
        $monthlyReferrals = $this->affiliateModel->query("
            SELECT COUNT(*) as count 
            FROM affiliate_referrals 
            WHERE affiliate_id = ? 
            AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ", [$affiliateId]);
        $monthlyReferrals = $monthlyReferrals[0]['count'] ?? 0;
        
        // Get conversion rate
        $conversionRate = $totalReferrals > 0 ? ($activeReferrals / $totalReferrals) * 100 : 0;

        return [
            'total_referrals' => $totalReferrals,
            'active_referrals' => $activeReferrals,
            'monthly_referrals' => $monthlyReferrals,
            'conversion_rate' => round($conversionRate, 2)
        ];
    }
}
