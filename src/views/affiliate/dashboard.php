<?php
$title = $title ?? 'Affiliate Dashboard - Time2Eat';
$user = $user ?? [];
$affiliate = $affiliate ?? [];
$stats = $stats ?? [];
$recent_earnings = $recent_earnings ?? [];
$recent_withdrawals = $recent_withdrawals ?? [];
$earnings_growth = $earnings_growth ?? [];
?>

<!DOCTYPE html>
<html lang="en" class="tw-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'tw-',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fff7ed',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Chart.js - Using UMD build for compatibility -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="tw-min-h-full tw-bg-gray-50">
    <!-- Include Dashboard Layout -->
    <?php include __DIR__ . '/../components/dashboard-layout.php'; ?>

    <div class="tw-flex tw-h-screen tw-bg-gray-50">
        <!-- Sidebar -->
        <div class="tw-hidden md:tw-flex tw-flex-col tw-w-64 tw-bg-white tw-shadow-lg">
            <?php include __DIR__ . '/../components/sidebar-content.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="tw-flex-1 tw-flex tw-flex-col tw-overflow-hidden">
            <!-- Header -->
            <header class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center">
                        <button class="tw-p-2 tw-rounded-md tw-text-gray-400 hover:tw-text-gray-500 hover:tw-bg-gray-100 md:tw-hidden" id="mobile-menu-button">
                            <i data-feather="menu" class="tw-h-6 tw-w-6"></i>
                        </button>
                        <h1 class="tw-ml-4 tw-text-2xl tw-font-bold tw-text-gray-900">Affiliate Dashboard</h1>
                    </div>
                    <div class="tw-flex tw-items-center tw-space-x-4">
                        <span class="tw-text-sm tw-text-gray-600">Welcome back, <?= e($user['first_name']) ?>!</span>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="tw-flex-1 tw-overflow-y-auto tw-p-6">
                <!-- Stats Cards -->
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
                    <!-- Total Earnings -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div>
                                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Earnings</p>
                                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_earnings'] ?? 0) ?> XAF</p>
                            </div>
                            <div class="tw-h-12 tw-w-12 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Available Balance -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div>
                                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Available Balance</p>
                                <p class="tw-text-3xl tw-font-bold tw-text-blue-600"><?= number_format($stats['available_balance'] ?? 0) ?> XAF</p>
                            </div>
                            <div class="tw-h-12 tw-w-12 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <i data-feather="credit-card" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Referrals -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div>
                                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Referrals</p>
                                <p class="tw-text-3xl tw-font-bold tw-text-purple-600"><?= number_format($stats['total_referrals'] ?? 0) ?></p>
                            </div>
                            <div class="tw-h-12 tw-w-12 tw-bg-purple-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Earnings -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div>
                                <p class="tw-text-sm tw-font-medium tw-text-gray-600">This Month</p>
                                <p class="tw-text-3xl tw-font-bold tw-text-orange-600"><?= number_format($stats['monthly_earnings'] ?? 0) ?> XAF</p>
                            </div>
                            <div class="tw-h-12 tw-w-12 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6 tw-mb-8">
                    <!-- Referral Code Card -->
                    <div class="lg:tw-col-span-1">
                        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Your Referral Code</h3>
                            <div class="tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg tw-p-4 tw-text-center">
                                <p class="tw-text-white tw-text-2xl tw-font-bold tw-mb-2"><?= e($stats['referral_code'] ?? '') ?></p>
                                <p class="tw-text-orange-100 tw-text-sm">Share this code to earn <?= $stats['commission_rate'] ?? 5 ?>% commission</p>
                            </div>
                            <div class="tw-mt-4 tw-flex tw-space-x-2">
                                <button onclick="copyReferralCode()" class="tw-flex-1 tw-bg-gray-100 hover:tw-bg-gray-200 tw-text-gray-700 tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-transition-colors">
                                    <i data-feather="copy" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                    Copy Code
                                </button>
                                <button onclick="shareReferralLink()" class="tw-flex-1 tw-bg-primary-500 hover:tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-transition-colors">
                                    <i data-feather="share-2" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                    Share Link
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings Chart -->
                    <div class="lg:tw-col-span-2">
                        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Earnings Growth</h3>
                            <canvas id="earningsChart" class="tw-w-full tw-h-64"></canvas>
                        </div>
                    </div>
                </div>

                <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6">
                    <!-- Recent Earnings -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Recent Earnings</h3>
                            <a href="/affiliate/earnings" class="tw-text-primary-600 hover:tw-text-primary-700 tw-text-sm tw-font-medium">View All</a>
                        </div>
                        <div class="tw-space-y-4">
                            <?php if (!empty($recent_earnings)): ?>
                                <?php foreach (array_slice($recent_earnings, 0, 5) as $earning): ?>
                                    <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-gray-50 tw-rounded-lg">
                                        <div class="tw-flex tw-items-center">
                                            <div class="tw-h-8 tw-w-8 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                                <i data-feather="plus" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                                            </div>
                                            <div>
                                                <p class="tw-text-sm tw-font-medium tw-text-gray-900">
                                                    <?= ucfirst($earning['type']) ?> Commission
                                                </p>
                                                <p class="tw-text-xs tw-text-gray-500">
                                                    <?= date('M j, Y', strtotime($earning['earned_at'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <span class="tw-text-sm tw-font-semibold tw-text-green-600">
                                            +<?= number_format($earning['amount']) ?> XAF
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="tw-text-center tw-py-8">
                                    <i data-feather="dollar-sign" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                                    <p class="tw-text-gray-500">No earnings yet</p>
                                    <p class="tw-text-sm tw-text-gray-400">Start referring customers to earn commissions</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Withdrawals -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Recent Withdrawals</h3>
                            <a href="/affiliate/withdrawals" class="tw-text-primary-600 hover:tw-text-primary-700 tw-text-sm tw-font-medium">View All</a>
                        </div>
                        <div class="tw-space-y-4">
                            <?php if (!empty($recent_withdrawals)): ?>
                                <?php foreach (array_slice($recent_withdrawals, 0, 5) as $withdrawal): ?>
                                    <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-gray-50 tw-rounded-lg">
                                        <div class="tw-flex tw-items-center">
                                            <div class="tw-h-8 tw-w-8 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3 
                                                <?= $withdrawal['status'] === 'approved' ? 'tw-bg-green-100' : ($withdrawal['status'] === 'pending' ? 'tw-bg-yellow-100' : 'tw-bg-red-100') ?>">
                                                <i data-feather="<?= $withdrawal['status'] === 'approved' ? 'check' : ($withdrawal['status'] === 'pending' ? 'clock' : 'x') ?>" 
                                                   class="tw-h-4 tw-w-4 <?= $withdrawal['status'] === 'approved' ? 'tw-text-green-600' : ($withdrawal['status'] === 'pending' ? 'tw-text-yellow-600' : 'tw-text-red-600') ?>"></i>
                                            </div>
                                            <div>
                                                <p class="tw-text-sm tw-font-medium tw-text-gray-900">
                                                    <?= ucfirst(str_replace('_', ' ', $withdrawal['payment_method'])) ?>
                                                </p>
                                                <p class="tw-text-xs tw-text-gray-500">
                                                    <?= date('M j, Y', strtotime($withdrawal['requested_at'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="tw-text-right">
                                            <span class="tw-text-sm tw-font-semibold tw-text-gray-900">
                                                <?= number_format($withdrawal['amount']) ?> XAF
                                            </span>
                                            <p class="tw-text-xs tw-capitalize 
                                                <?= $withdrawal['status'] === 'approved' ? 'tw-text-green-600' : ($withdrawal['status'] === 'pending' ? 'tw-text-yellow-600' : 'tw-text-red-600') ?>">
                                                <?= str_replace('_', ' ', $withdrawal['status']) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="tw-text-center tw-py-8">
                                    <i data-feather="credit-card" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                                    <p class="tw-text-gray-500">No withdrawals yet</p>
                                    <p class="tw-text-sm tw-text-gray-400">Minimum withdrawal: 10,000 XAF</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (($stats['available_balance'] ?? 0) >= 10000): ?>
                            <div class="tw-mt-4 tw-pt-4 tw-border-t tw-border-gray-200">
                                <a href="/affiliate/request-withdrawal" class="tw-w-full tw-bg-primary-500 hover:tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-transition-colors tw-block tw-text-center">
                                    Request Withdrawal
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="tw-mt-8 tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Quick Actions</h3>
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-4">
                        <a href="/affiliate/referrals" class="tw-flex tw-items-center tw-p-4 tw-bg-blue-50 tw-rounded-lg hover:tw-bg-blue-100 tw-transition-colors">
                            <i data-feather="users" class="tw-h-8 tw-w-8 tw-text-blue-600 tw-mr-3"></i>
                            <div>
                                <p class="tw-font-medium tw-text-blue-900">My Referrals</p>
                                <p class="tw-text-sm tw-text-blue-600">View referred users</p>
                            </div>
                        </a>
                        
                        <a href="/affiliate/earnings" class="tw-flex tw-items-center tw-p-4 tw-bg-green-50 tw-rounded-lg hover:tw-bg-green-100 tw-transition-colors">
                            <i data-feather="dollar-sign" class="tw-h-8 tw-w-8 tw-text-green-600 tw-mr-3"></i>
                            <div>
                                <p class="tw-font-medium tw-text-green-900">Earnings History</p>
                                <p class="tw-text-sm tw-text-green-600">View all earnings</p>
                            </div>
                        </a>
                        
                        <a href="/affiliate/withdrawals" class="tw-flex tw-items-center tw-p-4 tw-bg-purple-50 tw-rounded-lg hover:tw-bg-purple-100 tw-transition-colors">
                            <i data-feather="credit-card" class="tw-h-8 tw-w-8 tw-text-purple-600 tw-mr-3"></i>
                            <div>
                                <p class="tw-font-medium tw-text-purple-900">Withdrawals</p>
                                <p class="tw-text-sm tw-text-purple-600">Manage withdrawals</p>
                            </div>
                        </a>
                        
                        <button onclick="shareReferralLink()" class="tw-flex tw-items-center tw-p-4 tw-bg-orange-50 tw-rounded-lg hover:tw-bg-orange-100 tw-transition-colors">
                            <i data-feather="share-2" class="tw-h-8 tw-w-8 tw-text-orange-600 tw-mr-3"></i>
                            <div>
                                <p class="tw-font-medium tw-text-orange-900">Share & Earn</p>
                                <p class="tw-text-sm tw-text-orange-600">Invite friends</p>
                            </div>
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize Feather icons
        feather.replace();

        // Earnings Chart
        const ctx = document.getElementById('earningsChart').getContext('2d');
        const earningsData = <?= json_encode($earnings_growth) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: earningsData.map(item => item.month),
                datasets: [{
                    label: 'Earnings (XAF)',
                    data: earningsData.map(item => item.earnings),
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' XAF';
                            }
                        }
                    }
                }
            }
        });

        // Copy referral code
        function copyReferralCode() {
            const code = '<?= e($stats['referral_code'] ?? '') ?>';
            navigator.clipboard.writeText(code).then(() => {
                showNotification('Referral code copied to clipboard!', 'success');
            });
        }

        // Share referral link
        function shareReferralLink() {
            const code = '<?= e($stats['referral_code'] ?? '') ?>';
            const url = `${window.location.origin}/register?ref=${code}`;
            
            if (navigator.share) {
                navigator.share({
                    title: 'Join Time2Eat with my referral code',
                    text: `Use my referral code ${code} to get started with Time2Eat!`,
                    url: url
                });
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    showNotification('Referral link copied to clipboard!', 'success');
                });
            }
        }

        // Show notification
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-lg tw-shadow-lg tw-z-50 ${
                type === 'success' ? 'tw-bg-green-500' : 'tw-bg-blue-500'
            } tw-text-white`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Mobile menu toggle
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            // Toggle mobile menu (implement based on your sidebar component)
        });
    </script>
</body>
</html>
