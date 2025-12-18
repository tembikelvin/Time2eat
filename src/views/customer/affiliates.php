<?php
/**
 * Customer Affiliate Page - Brand New Design
 */

$user = $user ?? null;
$affiliate = $affiliate ?? null;
$referrals = $referrals ?? [];
$withdrawals = $withdrawals ?? [];
$earnings = $earnings ?? [];
$stats = $stats ?? [];
$error = $error ?? '';

// Calculate stats
$totalReferrals = count($referrals);
$activeReferrals = count(array_filter($referrals, function($ref) { return $ref['order_count'] > 0; }));
$totalEarnings = $affiliate['total_earnings'] ?? 0;
$availableBalance = $affiliate['pending_earnings'] ?? 0;
$commissionRate = ($affiliate['commission_rate'] ?? 0.05) * 100;
$affiliateCode = $affiliate['affiliate_code'] ?? '';

// Helper function to get status badge
function getStatusBadge($status) {
    $badges = [
        'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
        'processing' => 'tw-bg-blue-100 tw-text-blue-800',
        'completed' => 'tw-bg-green-100 tw-text-green-800',
        'failed' => 'tw-bg-red-100 tw-text-red-800',
        'cancelled' => 'tw-bg-gray-100 tw-text-gray-800'
    ];
    return $badges[$status] ?? 'tw-bg-gray-100 tw-text-gray-800';
}
?>

<div class="tw-max-w-7xl tw-mx-auto">
    <!-- Page Header -->
    <div class="tw-mb-8">
        <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-mb-2">Affiliate Program</h1>
        <p class="tw-text-gray-600">Earn <?= $commissionRate ?>% commission on every order from your referrals</p>
    </div>

    <!-- Error Message -->
    <?php if ($error): ?>
        <div class="tw-mb-6 tw-p-4 tw-bg-red-50 tw-border-l-4 tw-border-red-500 tw-rounded-r-lg">
            <div class="tw-flex tw-items-center">
                <i data-feather="alert-circle" class="tw-w-5 tw-h-5 tw-text-red-600 tw-mr-3"></i>
                <span class="tw-text-red-800 tw-font-medium"><?= htmlspecialchars($error) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$affiliate): ?>
        <!-- Join Affiliate Program CTA -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
            <div class="tw-p-8 md:tw-p-12 tw-text-center">
                <div class="tw-w-20 tw-h-20 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
                    <i data-feather="gift" class="tw-w-10 tw-h-10 tw-text-orange-600"></i>
                </div>
                <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-3">Start Earning Today</h2>
                <p class="tw-text-gray-600 tw-mb-8 tw-max-w-2xl tw-mx-auto">
                    Join our affiliate program and earn <?= $commissionRate ?>% commission on every order placed by people you refer. 
                    It's free to join and you can start earning immediately!
                </p>
                <button onclick="joinAffiliateProgram()" class="tw-inline-flex tw-items-center tw-px-8 tw-py-4 tw-bg-orange-600 tw-text-white tw-font-semibold tw-rounded-xl hover:tw-bg-orange-700 tw-transition-colors tw-shadow-lg">
                    <i data-feather="star" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Join Affiliate Program
                </button>
            </div>
        </div>

        <!-- Benefits Section -->
        <div class="tw-mt-8 tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <div class="tw-w-12 tw-h-12 tw-bg-green-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4">
                    <i data-feather="dollar-sign" class="tw-w-6 tw-h-6 tw-text-green-600"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Earn Commission</h3>
                <p class="tw-text-gray-600 tw-text-sm">Get <?= $commissionRate ?>% of every order value from your referrals</p>
            </div>
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <div class="tw-w-12 tw-h-12 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4">
                    <i data-feather="users" class="tw-w-6 tw-h-6 tw-text-blue-600"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Unlimited Referrals</h3>
                <p class="tw-text-gray-600 tw-text-sm">No limit on how many people you can refer</p>
            </div>
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <div class="tw-w-12 tw-h-12 tw-bg-purple-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4">
                    <i data-feather="trending-up" class="tw-w-6 tw-h-6 tw-text-purple-600"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Lifetime Earnings</h3>
                <p class="tw-text-gray-600 tw-text-sm">Earn from every order your referrals make, forever</p>
            </div>
        </div>

    <?php else: ?>
        <!-- Affiliate Dashboard -->
        
        <!-- Stats Cards -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
            <!-- Total Earnings -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div class="tw-w-12 tw-h-12 tw-bg-green-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="dollar-sign" class="tw-w-6 tw-h-6 tw-text-green-600"></i>
                    </div>
                </div>
                <p class="tw-text-sm tw-text-gray-600 tw-mb-1">Total Earnings</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($totalEarnings, 0) ?> <span class="tw-text-base tw-text-gray-500">XAF</span></p>
            </div>

            <!-- Available Balance -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div class="tw-w-12 tw-h-12 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="credit-card" class="tw-w-6 tw-h-6 tw-text-blue-600"></i>
                    </div>
                </div>
                <p class="tw-text-sm tw-text-gray-600 tw-mb-1">Available Balance</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($availableBalance, 0) ?> <span class="tw-text-base tw-text-gray-500">XAF</span></p>
            </div>

            <!-- Total Referrals -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div class="tw-w-12 tw-h-12 tw-bg-purple-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="users" class="tw-w-6 tw-h-6 tw-text-purple-600"></i>
                    </div>
                </div>
                <p class="tw-text-sm tw-text-gray-600 tw-mb-1">Total Referrals</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $totalReferrals ?></p>
            </div>

            <!-- Active Referrals -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div class="tw-w-12 tw-h-12 tw-bg-orange-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="user-check" class="tw-w-6 tw-h-6 tw-text-orange-600"></i>
                    </div>
                </div>
                <p class="tw-text-sm tw-text-gray-600 tw-mb-1">Active Referrals</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $activeReferrals ?></p>
            </div>
        </div>

        <!-- Withdrawal Section -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6 tw-mb-8">
            <!-- Request Withdrawal Card -->
            <div class="<?= $availableBalance >= 10000 ? 'tw-bg-gradient-to-br tw-from-green-50 tw-to-emerald-50 tw-border-green-200' : 'tw-bg-gray-50 tw-border-gray-200' ?> tw-border tw-rounded-xl tw-p-6">
                <div class="tw-flex tw-items-start tw-gap-4 tw-mb-4">
                    <div class="tw-w-12 tw-h-12 <?= $availableBalance >= 10000 ? 'tw-bg-green-600' : 'tw-bg-gray-300' ?> tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                        <i data-feather="<?= $availableBalance >= 10000 ? 'check-circle' : 'clock' ?>" class="tw-w-6 tw-h-6 tw-text-white"></i>
                    </div>
                    <div class="tw-flex-1">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-1">
                            <?= $availableBalance >= 10000 ? 'Ready to Withdraw' : 'Keep Earning' ?>
                        </h3>
                        <p class="tw-text-sm tw-text-gray-600">
                            <?php if ($availableBalance >= 10000): ?>
                                You have <span class="tw-font-semibold tw-text-green-700"><?= number_format($availableBalance, 0) ?> XAF</span> available
                            <?php else: ?>
                                Need <span class="tw-font-semibold"><?= number_format(10000 - $availableBalance, 0) ?> XAF</span> more to withdraw
                            <?php endif; ?>
                        </p>
                        <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Minimum: 10,000 XAF</p>
                    </div>
                </div>
                <?php if ($availableBalance >= 10000): ?>
                    <button onclick="requestWithdrawal()" class="tw-w-full tw-inline-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-bg-green-600 tw-text-white tw-font-semibold tw-rounded-lg hover:tw-bg-green-700 tw-transition-colors tw-shadow-sm">
                        <i data-feather="download" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                        Request Withdrawal
                    </button>
                <?php else: ?>
                    <button disabled class="tw-w-full tw-inline-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-bg-gray-300 tw-text-gray-500 tw-font-semibold tw-rounded-lg tw-cursor-not-allowed">
                        <i data-feather="lock" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                        Insufficient Balance
                    </button>
                <?php endif; ?>
            </div>

            <!-- Withdrawal Stats Card -->
            <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-xl tw-p-6">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Withdrawal Stats</h3>
                <div class="tw-space-y-4">
                    <div class="tw-flex tw-items-center tw-justify-between">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-w-10 tw-h-10 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                                <i data-feather="trending-up" class="tw-w-5 tw-h-5 tw-text-blue-600"></i>
                            </div>
                            <div>
                                <p class="tw-text-xs tw-text-gray-500">Total Withdrawn</p>
                                <p class="tw-text-lg tw-font-bold tw-text-gray-900"><?= number_format($affiliate['paid_earnings'] ?? 0, 0) ?> XAF</p>
                            </div>
                        </div>
                    </div>
                    <div class="tw-flex tw-items-center tw-justify-between">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-w-10 tw-h-10 tw-bg-purple-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                                <i data-feather="file-text" class="tw-w-5 tw-h-5 tw-text-purple-600"></i>
                            </div>
                            <div>
                                <p class="tw-text-xs tw-text-gray-500">Total Requests</p>
                                <p class="tw-text-lg tw-font-bold tw-text-gray-900"><?= count($withdrawals) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="tw-flex tw-items-center tw-justify-between">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-w-10 tw-h-10 tw-bg-yellow-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                                <i data-feather="clock" class="tw-w-5 tw-h-5 tw-text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="tw-text-xs tw-text-gray-500">Pending</p>
                                <p class="tw-text-lg tw-font-bold tw-text-gray-900">
                                    <?= count(array_filter($withdrawals, function($w) { return $w['status'] === 'pending'; })) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawal History -->
        <?php if (!empty($withdrawals)): ?>
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6 tw-mb-8">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Withdrawal History</h3>
                    <span class="tw-text-sm tw-text-gray-500"><?= count($withdrawals) ?> request<?= count($withdrawals) !== 1 ? 's' : '' ?></span>
                </div>

                <div class="tw-overflow-x-auto">
                    <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                        <thead class="tw-bg-gray-50">
                            <tr>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Method</th>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Processed</th>
                            </tr>
                        </thead>
                        <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                            <?php foreach ($withdrawals as $withdrawal): ?>
                                <tr class="hover:tw-bg-gray-50 tw-transition-colors">
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                        <?= date('M j, Y', strtotime($withdrawal['created_at'])) ?>
                                        <div class="tw-text-xs tw-text-gray-500"><?= date('g:i A', strtotime($withdrawal['created_at'])) ?></div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap">
                                        <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= number_format($withdrawal['amount'], 0) ?> XAF</span>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-600">
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $withdrawal['method'] ?? 'N/A'))) ?>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap">
                                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium <?= getStatusBadge($withdrawal['status']) ?>">
                                            <?= ucfirst($withdrawal['status']) ?>
                                        </span>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-600">
                                        <?php if ($withdrawal['processed_at']): ?>
                                            <?= date('M j, Y', strtotime($withdrawal['processed_at'])) ?>
                                        <?php else: ?>
                                            <span class="tw-text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Referral Code Section -->
        <div class="tw-bg-gradient-to-br tw-from-orange-50 tw-to-amber-50 tw-rounded-xl tw-shadow-sm tw-border tw-border-orange-200 tw-p-6 tw-mb-8">
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-6">
                <div class="tw-w-10 tw-h-10 tw-bg-orange-600 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="gift" class="tw-w-5 tw-h-5 tw-text-white"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Share & Earn</h3>
            </div>

            <!-- Referral Code Display -->
            <div class="tw-bg-white tw-rounded-lg tw-p-6 tw-mb-6 tw-shadow-sm">
                <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4">
                    <div>
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-2 tw-font-medium">Your Unique Referral Code</p>
                        <p class="tw-text-3xl tw-font-bold tw-text-orange-600 tw-font-mono tw-tracking-wider" id="referral-code"><?= htmlspecialchars($affiliateCode) ?></p>
                    </div>
                    <button onclick="copyReferralCode()" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-orange-600 tw-text-white tw-font-semibold tw-rounded-lg hover:tw-bg-orange-700 tw-transition-all tw-shadow-md hover:tw-shadow-lg">
                        <i data-feather="copy" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                        Copy Code
                    </button>
                </div>
            </div>

            <!-- Referral Link -->
            <div class="tw-mb-6">
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Referral Link</label>
                <div class="tw-flex tw-gap-2">
                    <input type="text" id="referral-link" value="<?= htmlspecialchars(url('/register?ref=' . $affiliateCode)) ?>" class="tw-flex-1 tw-px-4 tw-py-3 tw-bg-white tw-border-2 tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-mono focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500" readonly>
                    <button onclick="copyReferralLink()" class="tw-px-6 tw-py-3 tw-bg-gray-900 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-gray-800 tw-transition-colors tw-shadow-sm">
                        <i data-feather="copy" class="tw-w-5 tw-h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Share Buttons -->
            <div>
                <p class="tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-3">Share via Social Media</p>
                <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-3">
                    <button onclick="shareWhatsApp()" class="tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-bg-green-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-green-700 tw-transition-all tw-shadow-sm hover:tw-shadow-md">
                        <i data-feather="message-circle" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                        WhatsApp
                    </button>
                    <button onclick="shareFacebook()" class="tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-bg-blue-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-blue-700 tw-transition-all tw-shadow-sm hover:tw-shadow-md">
                        <i data-feather="facebook" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                        Facebook
                    </button>
                    <button onclick="shareTwitter()" class="tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-bg-sky-500 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-sky-600 tw-transition-all tw-shadow-sm hover:tw-shadow-md">
                        <i data-feather="twitter" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                        Twitter
                    </button>
                    <button onclick="shareGeneric()" class="tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-bg-gray-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-gray-700 tw-transition-all tw-shadow-sm hover:tw-shadow-md">
                        <i data-feather="share-2" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                        More
                    </button>
                </div>
            </div>
        </div>

        <!-- Referrals List -->
        <?php if (!empty($referrals)): ?>
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6 tw-mb-8">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-bg-purple-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                            <i data-feather="users" class="tw-w-5 tw-h-5 tw-text-purple-600"></i>
                        </div>
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Your Referrals</h3>
                    </div>
                    <span class="tw-px-3 tw-py-1 tw-bg-purple-100 tw-text-purple-700 tw-text-sm tw-font-semibold tw-rounded-full">
                        <?= count($referrals) ?> Total
                    </span>
                </div>

                <div class="tw-space-y-3">
                    <?php foreach (array_slice($referrals, 0, 10) as $index => $referral): ?>
                        <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gradient-to-r tw-from-gray-50 tw-to-white tw-rounded-lg tw-border tw-border-gray-100 hover:tw-border-orange-200 tw-transition-all hover:tw-shadow-sm">
                            <div class="tw-flex tw-items-center tw-gap-4">
                                <div class="tw-relative">
                                    <div class="tw-w-12 tw-h-12 tw-bg-gradient-to-br tw-from-orange-500 tw-to-orange-600 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white tw-font-bold tw-text-lg tw-shadow-md">
                                        <?= strtoupper(substr($referral['first_name'], 0, 1)) ?>
                                    </div>
                                    <?php if ($referral['order_count'] > 0): ?>
                                        <div class="tw-absolute -tw-top-1 -tw-right-1 tw-w-5 tw-h-5 tw-bg-green-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-border-2 tw-border-white">
                                            <i data-feather="check" class="tw-w-3 tw-h-3 tw-text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="tw-font-semibold tw-text-gray-900"><?= htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']) ?></p>
                                    <div class="tw-flex tw-items-center tw-gap-3 tw-text-sm tw-text-gray-500 tw-mt-1">
                                        <span class="tw-flex tw-items-center tw-gap-1">
                                            <i data-feather="shopping-bag" class="tw-w-3.5 tw-h-3.5"></i>
                                            <?= $referral['order_count'] ?> orders
                                        </span>
                                        <span class="tw-text-gray-300">•</span>
                                        <span class="tw-flex tw-items-center tw-gap-1">
                                            <i data-feather="calendar" class="tw-w-3.5 tw-h-3.5"></i>
                                            <?= date('M j, Y', strtotime($referral['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="tw-text-right">
                                <p class="tw-text-lg tw-font-bold tw-text-green-600"><?= number_format($referral['commission_earned'], 0) ?> XAF</p>
                                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Commission</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($referrals) > 10): ?>
                    <div class="tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-200 tw-text-center">
                        <p class="tw-text-sm tw-text-gray-600">
                            Showing 10 of <span class="tw-font-semibold tw-text-gray-900"><?= count($referrals) ?></span> referrals
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-12 tw-mb-8 tw-text-center">
                <div class="tw-w-16 tw-h-16 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                    <i data-feather="users" class="tw-w-8 tw-h-8 tw-text-gray-400"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">No Referrals Yet</h3>
                <p class="tw-text-sm tw-text-gray-600 tw-mb-6">Start sharing your referral code to earn commissions</p>
                <button onclick="copyReferralCode()" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-orange-600 tw-text-white tw-font-semibold tw-rounded-lg hover:tw-bg-orange-700 tw-transition-colors">
                    <i data-feather="copy" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Copy Referral Code
                </button>
            </div>
        <?php endif; ?>

        <!-- How It Works -->
        <div class="tw-bg-gradient-to-br tw-from-blue-50 tw-to-indigo-50 tw-rounded-xl tw-shadow-sm tw-border tw-border-blue-200 tw-p-8">
            <div class="tw-text-center tw-mb-8">
                <h3 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-2">How It Works</h3>
                <p class="tw-text-gray-600">Start earning in 3 simple steps</p>
            </div>

            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-8">
                <div class="tw-text-center tw-relative">
                    <div class="tw-w-20 tw-h-20 tw-bg-gradient-to-br tw-from-orange-500 tw-to-orange-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-shadow-lg tw-transform tw-rotate-3">
                        <span class="tw-text-3xl tw-font-bold tw-text-white">1</span>
                    </div>
                    <div class="tw-absolute tw-top-10 tw-left-1/2 tw-w-full tw-h-0.5 tw-bg-orange-300 tw-hidden md:tw-block" style="transform: translateX(50%); width: calc(100% - 5rem);"></div>
                    <h4 class="tw-font-bold tw-text-gray-900 tw-mb-2 tw-text-lg">Share Your Code</h4>
                    <p class="tw-text-sm tw-text-gray-600">Share your unique referral code or link with friends, family, and on social media</p>
                </div>

                <div class="tw-text-center tw-relative">
                    <div class="tw-w-20 tw-h-20 tw-bg-gradient-to-br tw-from-blue-500 tw-to-blue-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-shadow-lg tw-transform -tw-rotate-3">
                        <span class="tw-text-3xl tw-font-bold tw-text-white">2</span>
                    </div>
                    <div class="tw-absolute tw-top-10 tw-left-1/2 tw-w-full tw-h-0.5 tw-bg-blue-300 tw-hidden md:tw-block" style="transform: translateX(50%); width: calc(100% - 5rem);"></div>
                    <h4 class="tw-font-bold tw-text-gray-900 tw-mb-2 tw-text-lg">They Sign Up</h4>
                    <p class="tw-text-sm tw-text-gray-600">When they register using your code, they become your referral and you're connected</p>
                </div>

                <div class="tw-text-center">
                    <div class="tw-w-20 tw-h-20 tw-bg-gradient-to-br tw-from-green-500 tw-to-green-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-shadow-lg tw-transform tw-rotate-3">
                        <span class="tw-text-3xl tw-font-bold tw-text-white">3</span>
                    </div>
                    <h4 class="tw-font-bold tw-text-gray-900 tw-mb-2 tw-text-lg">Earn Commission</h4>
                    <p class="tw-text-sm tw-text-gray-600">Earn <?= $commissionRate ?>% commission on every order they place - forever!</p>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="tw-mt-8 tw-pt-6 tw-border-t tw-border-blue-200">
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                    <div class="tw-flex tw-items-start tw-gap-3 tw-bg-white tw-rounded-lg tw-p-4">
                        <div class="tw-w-8 tw-h-8 tw-bg-green-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                            <i data-feather="check-circle" class="tw-w-5 tw-h-5 tw-text-green-600"></i>
                        </div>
                        <div>
                            <h5 class="tw-font-semibold tw-text-gray-900 tw-text-sm tw-mb-1">Lifetime Earnings</h5>
                            <p class="tw-text-xs tw-text-gray-600">Earn commission on all future orders from your referrals</p>
                        </div>
                    </div>
                    <div class="tw-flex tw-items-start tw-gap-3 tw-bg-white tw-rounded-lg tw-p-4">
                        <div class="tw-w-8 tw-h-8 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                            <i data-feather="zap" class="tw-w-5 tw-h-5 tw-text-blue-600"></i>
                        </div>
                        <div>
                            <h5 class="tw-font-semibold tw-text-gray-900 tw-text-sm tw-mb-1">Fast Withdrawals</h5>
                            <p class="tw-text-xs tw-text-gray-600">Withdraw your earnings once you reach 10,000 XAF</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
// Initialize Feather Icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

// Join Affiliate Program
function joinAffiliateProgram() {
    const button = event.target;
    const originalHTML = button.innerHTML;

    button.disabled = true;
    button.innerHTML = '<i data-feather="loader" class="tw-w-5 tw-h-5 tw-mr-2 tw-animate-spin"></i>Joining...';
    if (typeof feather !== 'undefined') feather.replace();

    fetch('<?= url('/customer/affiliates/join') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Successfully joined the affiliate program!', 'success');
            
            // Update the UI immediately without page reload
            updateAffiliateUI(data.data);
            
            // Hide the join button and show affiliate content
            const joinSection = document.getElementById('join-affiliate-section');
            const affiliateContent = document.getElementById('affiliate-content');
            
            if (joinSection) {
                joinSection.style.display = 'none';
            }
            if (affiliateContent) {
                affiliateContent.style.display = 'block';
            }
            
            // Update referral code if provided
            if (data.data && data.data.referral_code) {
                const referralCodeElement = document.getElementById('referral-code');
                if (referralCodeElement) {
                    referralCodeElement.textContent = data.data.referral_code;
                }
            }
            
            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        } else {
            showToast(data.message || 'Failed to join affiliate program', 'error');
            button.disabled = false;
            button.innerHTML = originalHTML;
            if (typeof feather !== 'undefined') feather.replace();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        button.disabled = false;
        button.innerHTML = originalHTML;
        if (typeof feather !== 'undefined') feather.replace();
    });
}

// Update affiliate UI with new data
function updateAffiliateUI(data) {
    // Update referral code
    if (data.referral_code) {
        const referralCodeElement = document.getElementById('referral-code');
        if (referralCodeElement) {
            referralCodeElement.textContent = data.referral_code;
        }
    }
    
    // Update affiliate status
    const statusElement = document.getElementById('affiliate-status');
    if (statusElement) {
        statusElement.textContent = 'Active';
        statusElement.className = 'tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-green-100 tw-text-green-800';
    }
    
    // Update earnings display
    const earningsElement = document.getElementById('total-earnings');
    if (earningsElement) {
        earningsElement.textContent = '0.00 XAF';
    }
    
    const pendingElement = document.getElementById('pending-earnings');
    if (pendingElement) {
        pendingElement.textContent = '0.00 XAF';
    }
    
    const paidElement = document.getElementById('paid-earnings');
    if (paidElement) {
        paidElement.textContent = '0.00 XAF';
    }
    
    // Update referrals count
    const referralsElement = document.getElementById('total-referrals');
    if (referralsElement) {
        referralsElement.textContent = '0';
    }
}

// Copy Referral Code
function copyReferralCode() {
    const code = document.getElementById('referral-code').textContent;
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;

    navigator.clipboard.writeText(code).then(() => {
        button.innerHTML = '<i data-feather="check" class="tw-w-5 tw-h-5 tw-mr-2"></i>Copied!';
        if (typeof feather !== 'undefined') feather.replace();
        showToast('Referral code copied to clipboard!', 'success');

        setTimeout(() => {
            button.innerHTML = originalHTML;
            if (typeof feather !== 'undefined') feather.replace();
        }, 2000);
    }).catch(() => {
        showToast('Failed to copy code', 'error');
    });
}

// Copy Referral Link
function copyReferralLink() {
    const link = document.getElementById('referral-link').value;
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;

    navigator.clipboard.writeText(link).then(() => {
        button.innerHTML = '<i data-feather="check" class="tw-w-5 tw-h-5"></i>';
        if (typeof feather !== 'undefined') feather.replace();
        showToast('Referral link copied to clipboard!', 'success');

        setTimeout(() => {
            button.innerHTML = originalHTML;
            if (typeof feather !== 'undefined') feather.replace();
        }, 2000);
    }).catch(() => {
        showToast('Failed to copy link', 'error');
    });
}

// Share on WhatsApp
function shareWhatsApp() {
    const code = document.getElementById('referral-code').textContent;
    const link = document.getElementById('referral-link').value;
    const message = `🍕 Join Time2Eat and get amazing food delivered!\n\nUse my referral code: ${code}\n\nRegister here: ${link}`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

// Share on Facebook
function shareFacebook() {
    const link = document.getElementById('referral-link').value;
    const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`;
    window.open(facebookUrl, '_blank', 'width=600,height=400');
}

// Share on Twitter
function shareTwitter() {
    const code = document.getElementById('referral-code').textContent;
    const link = document.getElementById('referral-link').value;
    const message = `🍕 Join Time2Eat using my referral code: ${code}\n\n${link}`;
    const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}`;
    window.open(twitterUrl, '_blank', 'width=600,height=400');
}

// Generic Share
function shareGeneric() {
    const code = document.getElementById('referral-code').textContent;
    const link = document.getElementById('referral-link').value;
    const message = `Join Time2Eat using my referral code: ${code}\n\n${link}`;

    if (navigator.share) {
        navigator.share({
            title: 'Join Time2Eat',
            text: message,
            url: link
        }).catch(err => console.log('Error sharing:', err));
    } else {
        navigator.clipboard.writeText(message).then(() => {
            showToast('Referral message copied to clipboard!', 'success');
        }).catch(() => {
            showToast('Unable to share', 'error');
        });
    }
}

// Request Withdrawal
function requestWithdrawal() {
    const button = event.target;
    const originalHTML = button.innerHTML;

    if (!confirm('Request withdrawal of your available balance?')) {
        return;
    }

    button.disabled = true;
    button.innerHTML = '<i data-feather="loader" class="tw-w-5 tw-h-5 tw-mr-2 tw-animate-spin"></i>Processing...';
    if (typeof feather !== 'undefined') feather.replace();

    fetch('<?= url('/customer/affiliates/withdraw') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Withdrawal request submitted successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to request withdrawal', 'error');
            button.disabled = false;
            button.innerHTML = originalHTML;
            if (typeof feather !== 'undefined') feather.replace();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        button.disabled = false;
        button.innerHTML = originalHTML;
        if (typeof feather !== 'undefined') feather.replace();
    });
}

// Toast Notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'tw-bg-green-600' :
                   type === 'error' ? 'tw-bg-red-600' :
                   'tw-bg-blue-600';

    toast.className = `tw-fixed tw-top-4 tw-right-4 tw-z-50 ${bgColor} tw-text-white tw-px-6 tw-py-4 tw-rounded-lg tw-shadow-lg tw-transform tw-translate-x-full tw-transition-transform tw-duration-300 tw-max-w-sm`;
    toast.innerHTML = `
        <div class="tw-flex tw-items-center tw-gap-3">
            <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="tw-w-5 tw-h-5"></i>
            <span class="tw-font-medium">${message}</span>
        </div>
    `;

    document.body.appendChild(toast);
    if (typeof feather !== 'undefined') feather.replace();

    setTimeout(() => {
        toast.classList.remove('tw-translate-x-full');
    }, 100);

    setTimeout(() => {
        toast.classList.add('tw-translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 4000);
}
</script>

