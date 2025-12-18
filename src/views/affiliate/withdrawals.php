<?php
$title = $title ?? 'My Withdrawals - Time2Eat';
$user = $user ?? [];
$affiliate = $affiliate ?? [];
$withdrawals_data = $withdrawals_data ?? [];
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
                <a href="<?= url('/affiliate/withdrawals') ?>" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-bg-primary-100 tw-text-primary-700 tw-rounded-lg tw-mb-2">
                    <i data-feather="credit-card" class="tw-w-5 tw-h-5 tw-mr-3"></i>
                    Withdrawals
                </a>
                <a href="<?= url('/affiliate/referrals') ?>" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-lg tw-mb-2">
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
                            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">My Withdrawals</h1>
                            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">Track your withdrawal requests and payments</p>
                        </div>
                        <div class="tw-flex tw-space-x-3">
                            <a href="<?= url('/affiliate/request-withdrawal') ?>" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                                <i data-feather="plus" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                Request Withdrawal
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="tw-flex-1 tw-overflow-y-auto tw-p-6">
                <!-- Withdrawals Table -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
                    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Withdrawal History</h3>
                    </div>
                    
                    <?php if (!empty($withdrawals_data['withdrawals'])): ?>
                        <div class="tw-overflow-x-auto">
                            <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                                <thead class="tw-bg-gray-50">
                                    <tr>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Method</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Reference</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Processed</th>
                                    </tr>
                                </thead>
                                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                                    <?php foreach ($withdrawals_data['withdrawals'] as $withdrawal): ?>
                                        <tr>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                <?= date('M j, Y', strtotime($withdrawal['created_at'])) ?>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">
                                                <?= number_format($withdrawal['amount'], 0) ?> XAF
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                <?= ucfirst(str_replace('_', ' ', $withdrawal['method'])) ?>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                <?= e($withdrawal['reference'] ?? 'N/A') ?>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                                <?php
                                                $statusColors = [
                                                    'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
                                                    'processing' => 'tw-bg-blue-100 tw-text-blue-800',
                                                    'completed' => 'tw-bg-green-100 tw-text-green-800',
                                                    'failed' => 'tw-bg-red-100 tw-text-red-800',
                                                    'cancelled' => 'tw-bg-gray-100 tw-text-gray-800'
                                                ];
                                                $statusColor = $statusColors[$withdrawal['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                                                ?>
                                                <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-full <?= $statusColor ?>">
                                                    <?= ucfirst($withdrawal['status']) ?>
                                                </span>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                <?= $withdrawal['processed_at'] ? date('M j, Y', strtotime($withdrawal['processed_at'])) : 'N/A' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($withdrawals_data['pages'] > 1): ?>
                            <div class="tw-px-6 tw-py-4 tw-border-t tw-border-gray-200">
                                <div class="tw-flex tw-items-center tw-justify-between">
                                    <div class="tw-text-sm tw-text-gray-700">
                                        Showing page <?= $withdrawals_data['page'] ?> of <?= $withdrawals_data['pages'] ?>
                                    </div>
                                    <div class="tw-flex tw-space-x-2">
                                        <?php if ($withdrawals_data['page'] > 1): ?>
                                            <a href="?page=<?= $withdrawals_data['page'] - 1 ?>" class="tw-px-3 tw-py-1 tw-text-sm tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">Previous</a>
                                        <?php endif; ?>
                                        <?php if ($withdrawals_data['page'] < $withdrawals_data['pages']): ?>
                                            <a href="?page=<?= $withdrawals_data['page'] + 1 ?>" class="tw-px-3 tw-py-1 tw-text-sm tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">Next</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="tw-p-12 tw-text-center">
                            <i data-feather="credit-card" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400"></i>
                            <h3 class="tw-mt-2 tw-text-sm tw-font-medium tw-text-gray-900">No withdrawals yet</h3>
                            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Request your first withdrawal when you have available earnings.</p>
                            <div class="tw-mt-6">
                                <a href="/affiliate/request-withdrawal" class="tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md hover:tw-bg-primary-700">
                                    Request Withdrawal
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>
