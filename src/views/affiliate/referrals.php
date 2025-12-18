<?php
$title = $title ?? 'My Referrals - Time2Eat';
$user = $user ?? [];
$affiliate = $affiliate ?? [];
$referred_users = $referred_users ?? [];
$referral_stats = $referral_stats ?? [];
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
</head>
<body class="tw-min-h-full tw-bg-gray-50">
    <!-- Include Dashboard Layout -->
    <?php include __DIR__ . '/../components/dashboard-layout.php'; ?>

    <div class="tw-flex tw-h-screen tw-bg-gray-50">
        <!-- Sidebar -->
        <div class="tw-hidden md:tw-flex tw-flex-col tw-w-64 tw-bg-white tw-shadow-lg">
            <div class="tw-p-6">
                <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">Affiliate Dashboard</h2>
            </div>
            <nav class="tw-flex-1 tw-px-4 tw-pb-4">
                <a href="<?= url('/affiliate/dashboard') ?>" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-lg tw-mb-2">
                    <i data-feather="home" class="tw-w-5 tw-h-5 tw-mr-3"></i>
                    Dashboard
                </a>
                <a href="<?= url('/affiliate/earnings') ?>" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-lg tw-mb-2">
                    <i data-feather="dollar-sign" class="tw-w-5 tw-h-5 tw-mr-3"></i>
                    Earnings
                </a>
                <a href="<?= url('/affiliate/withdrawals') ?>" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-lg tw-mb-2">
                    <i data-feather="credit-card" class="tw-w-5 tw-h-5 tw-mr-3"></i>
                    Withdrawals
                </a>
                <a href="<?= url('/affiliate/referrals') ?>" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-bg-primary-100 tw-text-primary-700 tw-rounded-lg tw-mb-2">
                    <i data-feather="users" class="tw-w-5 tw-h-5 tw-mr-3"></i>
                    Referrals
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="tw-flex-1 tw-flex tw-flex-col tw-overflow-hidden">
            <!-- Header -->
            <header class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200">
                <div class="tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-justify-between">
                        <div>
                            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">My Referrals</h1>
                            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">Track your referred customers and their activity</p>
                        </div>
                        <div class="tw-flex tw-space-x-3">
                            <button onclick="copyReferralLink()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                                <i data-feather="copy" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="tw-flex-1 tw-overflow-y-auto tw-p-6">
                <!-- Referral Stats -->
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-6">
                    <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                            </div>
                            <div class="tw-ml-4">
                                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Referrals</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $referral_stats['total_referrals'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                            </div>
                            <div class="tw-ml-4">
                                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Referrals</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $referral_stats['active_referrals'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                                <i data-feather="calendar" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
                            </div>
                            <div class="tw-ml-4">
                                <p class="tw-text-sm tw-font-medium tw-text-gray-600">This Month</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $referral_stats['monthly_referrals'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
                            </div>
                            <div class="tw-ml-4">
                                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Conversion Rate</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $referral_stats['conversion_rate'] ?? 0 ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referral Code -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6 tw-mb-6">
                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Your Referral Code</h3>
                    <div class="tw-flex tw-items-center tw-space-x-4">
                        <div class="tw-flex-1">
                            <input type="text" id="referralCode" value="<?= e($affiliate['affiliate_code'] ?? '') ?>" 
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-bg-gray-50" readonly>
                        </div>
                        <button onclick="copyReferralCode()" class="tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md hover:tw-bg-primary-700">
                            <i data-feather="copy" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                            Copy Code
                        </button>
                    </div>
                    <p class="tw-mt-2 tw-text-sm tw-text-gray-600">
                        Share this code with friends to earn commissions when they sign up and place orders.
                    </p>
                </div>

                <!-- Referrals Table -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
                    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Referred Customers</h3>
                    </div>
                    
                    <?php if (!empty($referred_users)): ?>
                        <div class="tw-overflow-x-auto">
                            <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                                <thead class="tw-bg-gray-50">
                                    <tr>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Customer</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Email</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Phone</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Commission</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                                    <?php foreach ($referred_users as $referral): ?>
                                        <tr>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                                <div class="tw-flex tw-items-center">
                                                    <div class="tw-h-10 tw-w-10 tw-flex-shrink-0">
                                                        <div class="tw-h-10 tw-w-10 tw-rounded-full tw-bg-gray-300 tw-flex tw-items-center tw-justify-center">
                                                            <span class="tw-text-sm tw-font-medium tw-text-gray-700">
                                                                <?= strtoupper(substr($referral['first_name'] ?? 'U', 0, 1)) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="tw-ml-4">
                                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                                            <?= e($referral['first_name'] ?? '') ?> <?= e($referral['last_name'] ?? '') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                <?= e($referral['email'] ?? '') ?>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                <?= e($referral['phone'] ?? 'N/A') ?>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-green-600">
                                                <?= number_format($referral['commission_amount'], 0) ?> XAF
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                                <?php
                                                $statusColors = [
                                                    'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
                                                    'confirmed' => 'tw-bg-green-100 tw-text-green-800',
                                                    'paid' => 'tw-bg-blue-100 tw-text-blue-800',
                                                    'cancelled' => 'tw-bg-red-100 tw-text-red-800'
                                                ];
                                                $statusColor = $statusColors[$referral['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                                                ?>
                                                <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-full <?= $statusColor ?>">
                                                    <?= ucfirst($referral['status']) ?>
                                                </span>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                <?= date('M j, Y', strtotime($referral['created_at'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="tw-p-12 tw-text-center">
                            <i data-feather="users" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400"></i>
                            <h3 class="tw-mt-2 tw-text-sm tw-font-medium tw-text-gray-900">No referrals yet</h3>
                            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Start sharing your referral code to earn commissions.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        feather.replace();
        
        function copyReferralCode() {
            const referralCode = document.getElementById('referralCode');
            referralCode.select();
            referralCode.setSelectionRange(0, 99999);
            document.execCommand('copy');
            
            // Show success message
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i data-feather="check" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>Copied!';
            feather.replace();
            
            setTimeout(() => {
                button.innerHTML = originalText;
                feather.replace();
            }, 2000);
        }
        
        function copyReferralLink() {
            const referralCode = '<?= e($affiliate['affiliate_code'] ?? '') ?>';
            const referralLink = `${window.location.origin}/register?ref=${referralCode}`;
            
            navigator.clipboard.writeText(referralLink).then(() => {
                // Show success message
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i data-feather="check" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>Copied!';
                feather.replace();
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    feather.replace();
                }, 2000);
            });
        }
    </script>
</body>
</html>
