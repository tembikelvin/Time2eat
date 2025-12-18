<?php
$title = $title ?? 'My Earnings - Time2Eat';
$user = $user ?? [];
$affiliate = $affiliate ?? [];
$earnings_data = $earnings_data ?? [];
$filters = $filters ?? [];
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
                <a href="/affiliate/dashboard" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-lg tw-mb-2">
                    <i data-feather="home" class="tw-w-5 tw-h-5 tw-mr-3"></i>
                    Dashboard
                </a>
                <a href="/affiliate/earnings" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-bg-primary-100 tw-text-primary-700 tw-rounded-lg tw-mb-2">
                    <i data-feather="dollar-sign" class="tw-w-5 tw-h-5 tw-mr-3"></i>
                    Earnings
                </a>
                <a href="/affiliate/withdrawals" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-lg tw-mb-2">
                    <i data-feather="credit-card" class="tw-w-5 tw-h-5 tw-mr-3"></i>
                    Withdrawals
                </a>
                <a href="/affiliate/referrals" class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-lg tw-mb-2">
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
                            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">My Earnings</h1>
                            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">Track your affiliate commission earnings</p>
                        </div>
                        <div class="tw-flex tw-space-x-3">
                            <button class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                                <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                Export
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="tw-flex-1 tw-overflow-y-auto tw-p-6">
                <!-- Filters -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6 tw-mb-6">
                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Filter Earnings</h3>
                    <form method="GET" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Start Date</label>
                            <input type="date" name="start_date" value="<?= e($filters['start_date'] ?? '') ?>" 
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">End Date</label>
                            <input type="date" name="end_date" value="<?= e($filters['end_date'] ?? '') ?>" 
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Type</label>
                            <select name="type" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-primary-500">
                                <option value="">All Types</option>
                                <option value="referral" <?= ($filters['type'] ?? '') === 'referral' ? 'selected' : '' ?>>Referral</option>
                                <option value="bonus" <?= ($filters['type'] ?? '') === 'bonus' ? 'selected' : '' ?>>Bonus</option>
                                <option value="commission" <?= ($filters['type'] ?? '') === 'commission' ? 'selected' : '' ?>>Commission</option>
                            </select>
                        </div>
                        <div class="tw-flex tw-items-end">
                            <button type="submit" class="tw-w-full tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md hover:tw-bg-primary-700 tw-transition-colors">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Earnings Table -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
                    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Earnings History</h3>
                    </div>
                    
                    <?php if (!empty($earnings_data['earnings'])): ?>
                        <div class="tw-overflow-x-auto">
                            <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                                <thead class="tw-bg-gray-50">
                                    <tr>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Order</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Customer</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                                    <?php foreach ($earnings_data['earnings'] as $earning): ?>
                                        <tr>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                <?= date('M j, Y', strtotime($earning['created_at'])) ?>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                #<?= e($earning['order_number'] ?? $earning['order_id']) ?>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                                <?= e($earning['first_name'] ?? '') ?> <?= e($earning['last_name'] ?? '') ?>
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-green-600">
                                                <?= number_format($earning['commission_amount'], 0) ?> XAF
                                            </td>
                                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                                <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-full tw-bg-green-100 tw-text-green-800">
                                                    <?= ucfirst($earning['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($earnings_data['pages'] > 1): ?>
                            <div class="tw-px-6 tw-py-4 tw-border-t tw-border-gray-200">
                                <div class="tw-flex tw-items-center tw-justify-between">
                                    <div class="tw-text-sm tw-text-gray-700">
                                        Showing page <?= $earnings_data['page'] ?> of <?= $earnings_data['pages'] ?>
                                    </div>
                                    <div class="tw-flex tw-space-x-2">
                                        <?php if ($earnings_data['page'] > 1): ?>
                                            <a href="?page=<?= $earnings_data['page'] - 1 ?>" class="tw-px-3 tw-py-1 tw-text-sm tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">Previous</a>
                                        <?php endif; ?>
                                        <?php if ($earnings_data['page'] < $earnings_data['pages']): ?>
                                            <a href="?page=<?= $earnings_data['page'] + 1 ?>" class="tw-px-3 tw-py-1 tw-text-sm tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">Next</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="tw-p-12 tw-text-center">
                            <i data-feather="dollar-sign" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400"></i>
                            <h3 class="tw-mt-2 tw-text-sm tw-font-medium tw-text-gray-900">No earnings yet</h3>
                            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Start referring customers to earn commissions.</p>
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
