<?php
/**
 * Admin Affiliates Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'affiliates';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Affiliate Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage affiliate programs, commissions, and referral tracking
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button onclick="exportAffiliates()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Export Data
            </button>
        </div>
    </div>
</div>

<!-- Affiliate Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Affiliates</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_affiliates'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">
                    <?php if (($stats['growth_rate'] ?? 0) >= 0): ?>
                        +<?= number_format($stats['growth_rate'] ?? 0, 1) ?>% this month
                    <?php else: ?>
                        <?= number_format($stats['growth_rate'] ?? 0, 1) ?>% this month
                    <?php endif; ?>
                </p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Affiliates</p>
                <p class="tw-text-3xl tw-font-bold tw-text-green-600"><?= number_format($stats['active_affiliates'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500">
                    <?php
                    $activeRate = ($stats['total_affiliates'] ?? 0) > 0 ?
                        (($stats['active_affiliates'] ?? 0) / ($stats['total_affiliates'] ?? 1)) * 100 : 0;
                    ?>
                    <?= number_format($activeRate, 1) ?>% active rate
                </p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="user-check" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Earnings</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_earnings'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Conversion Rate</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= $stats['conversion_rate'] ?? '12.5' ?>%</p>
                <p class="tw-text-sm tw-text-green-600">+2.1% vs last month</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Commission Settings -->
<div class="tw-bg-gradient-to-r tw-from-purple-50 tw-to-pink-50 tw-border tw-border-purple-200 tw-rounded-xl tw-p-6 tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-2 tw-bg-purple-100 tw-rounded-lg">
                <i data-feather="percent" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
            <div class="tw-ml-4">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Commission Structure</h3>
                <p class="tw-text-sm tw-text-gray-600">Edit commission rates directly from the table below</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-flex-col tw-md:tw-flex-row tw-md:tw-items-center tw-md:tw-justify-between tw-space-y-4 tw-md:tw-space-y-0">
            <!-- Search -->
            <div class="tw-flex-1 tw-max-w-lg">
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                        <i data-feather="search" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                    </div>
                    <input type="text" id="affiliate-search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-leading-5 tw-bg-white tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-1 focus:tw-ring-primary-500 focus:tw-border-primary-500" placeholder="Search affiliates by name, code, or email..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="tw-flex tw-space-x-4">
                <select id="status-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Status</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
                
                <select id="type-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Types</option>
                    <option value="customer" <?= ($type ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
                    <option value="vendor" <?= ($type ?? '') === 'vendor' ? 'selected' : '' ?>>Vendor</option>
                    <option value="influencer" <?= ($type ?? '') === 'influencer' ? 'selected' : '' ?>>Influencer</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Top Performers -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8 tw-mb-8">
    <div class="lg:tw-col-span-2">
        <!-- Affiliates Table -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-p-6 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">All Affiliates</h2>
                    <div class="tw-flex tw-items-center tw-space-x-2">
                        <span class="tw-text-sm tw-text-gray-500">Showing 1-10 of <?= number_format($totalAffiliates ?? 1247) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                    <thead class="tw-bg-gray-50">
                        <tr>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                <button onclick="sortTable('name')" class="tw-flex tw-items-center tw-space-x-1 hover:tw-text-gray-700">
                                    <span>Affiliate</span>
                                    <i data-feather="chevron-up" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Code</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                <button onclick="sortTable('referrals')" class="tw-flex tw-items-center tw-space-x-1 hover:tw-text-gray-700">
                                    <span>Referrals</span>
                                    <i data-feather="chevron-up" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                <button onclick="sortTable('commission')" class="tw-flex tw-items-center tw-space-x-1 hover:tw-text-gray-700">
                                    <span>Commission Rate</span>
                                    <i data-feather="chevron-up" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                <button onclick="sortTable('earnings')" class="tw-flex tw-items-center tw-space-x-1 hover:tw-text-gray-700">
                                    <span>Earnings</span>
                                    <i data-feather="chevron-up" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                <button onclick="sortTable('status')" class="tw-flex tw-items-center tw-space-x-1 hover:tw-text-gray-700">
                                    <span>Status</span>
                                    <i data-feather="chevron-up" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                        <?php 
                        // Use real affiliate data from controller
                        foreach ($affiliates as $affiliate): 
                            $affiliate['name'] = $affiliate['first_name'] . ' ' . $affiliate['last_name'];
                            $affiliate['code'] = $affiliate['affiliate_code'];
                            $affiliate['referrals'] = $affiliate['total_referrals'];
                            $affiliate['earnings'] = $affiliate['total_earnings'];
                            $affiliate['type'] = 'affiliate'; // Default type
                        ?>
                        <tr class="hover:tw-bg-gray-50" data-affiliate-id="<?= $affiliate['id'] ?>">
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-indigo-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                        <span class="tw-text-white tw-font-semibold tw-text-xs">
                                            <?= strtoupper(substr($affiliate['name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                    <div class="tw-ml-3">
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($affiliate['name']) ?></div>
                                        <div class="tw-text-sm tw-text-gray-500"><?= e($affiliate['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-text-sm tw-font-mono tw-font-medium tw-text-gray-900"><?= e($affiliate['code']) ?></div>
                                <div class="tw-text-xs tw-text-gray-500"><?= ucfirst($affiliate['type']) ?></div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                <?= number_format($affiliate['referrals']) ?>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center tw-space-x-2">
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-900 commission-rate">
                                        <?= number_format(($affiliate['commission_rate'] ?? 0.05) * 100, 1) ?>%
                                    </span>
                                    <button class="tw-text-blue-600 hover:tw-text-blue-900 tw-text-xs"
                                            onclick="openCommissionModal(<?= $affiliate['id'] ?>, <?= ($affiliate['commission_rate'] ?? 0.05) * 100 ?>, '<?= htmlspecialchars($affiliate['name'], ENT_QUOTES) ?>')"
                                            title="Edit Commission Rate">
                                        <i data-feather="edit-2" class="tw-h-3 tw-w-3"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">
                                <?= number_format($affiliate['earnings']) ?> XAF
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                                    <?php 
                                    switch($affiliate['status']) {
                                        case 'active': echo 'tw-bg-green-100 tw-text-green-800'; break;
                                        case 'inactive': echo 'tw-bg-gray-100 tw-text-gray-800'; break;
                                        case 'suspended': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                        default: echo 'tw-bg-gray-100 tw-text-gray-800';
                                    }
                                    ?>">
                                    <?= ucfirst($affiliate['status']) ?>
                                </span>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                                <div class="tw-flex tw-space-x-2">
                                    <button class="tw-text-primary-600 hover:tw-text-primary-900" onclick="viewAffiliate(<?= $affiliate['id'] ?>)"
                                            title="View Details">
                                        <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                                    </button>
                                    <?php if ($affiliate['status'] === 'active'): ?>
                                        <button class="tw-text-red-600 hover:tw-text-red-900" onclick="suspendAffiliate(<?= $affiliate['id'] ?>)"
                                                title="Suspend Affiliate">
                                            <i data-feather="pause" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                    <?php elseif ($affiliate['status'] === 'suspended'): ?>
                                        <button class="tw-text-green-600 hover:tw-text-green-900" onclick="activateAffiliate(<?= $affiliate['id'] ?>)"
                                                title="Activate Affiliate">
                                            <i data-feather="play" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Withdrawal Management Sidebar -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Pending Withdrawals</h2>
                <a href="<?= url('/admin/affiliate/withdrawals') ?>" class="tw-text-primary-600 hover:tw-text-primary-900 tw-text-sm tw-font-medium">
                    View All
                    <i data-feather="arrow-right" class="tw-h-4 tw-w-4 tw-inline tw-ml-1"></i>
                </a>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <?php 
            // Get pending withdrawals
            $pendingWithdrawals = $withdrawals ?? [];
            if (empty($pendingWithdrawals)): 
            ?>
            <div class="tw-text-center tw-py-8">
                <i data-feather="check-circle" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-2"></i>
                <p class="tw-text-sm tw-text-gray-500">No pending withdrawals</p>
            </div>
            <?php else: ?>
                <?php foreach (array_slice($pendingWithdrawals, 0, 5) as $withdrawal): 
                    $affiliateName = ($withdrawal['first_name'] ?? '') . ' ' . ($withdrawal['last_name'] ?? '');
                ?>
                <div class="tw-p-3 tw-bg-gray-50 tw-rounded-lg tw-border tw-border-gray-200">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                <div class="tw-flex tw-items-center">
                            <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-blue-500 tw-to-indigo-500 tw-text-white tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-bold">
                                <?= strtoupper(substr($affiliateName, 0, 1)) ?>
                            </div>
                            <div class="tw-ml-3">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($affiliateName) ?></div>
                                <div class="tw-text-xs tw-text-gray-500"><?= date('M d, Y', strtotime($withdrawal['created_at'] ?? 'now')) ?></div>
                            </div>
                        </div>
                        <div class="tw-text-right">
                            <div class="tw-text-sm tw-font-bold tw-text-gray-900"><?= number_format($withdrawal['amount'] ?? 0) ?> XAF</div>
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800">
                                Pending
                            </span>
                        </div>
                    </div>
                    <div class="tw-flex tw-space-x-2 tw-mt-3">
                        <button onclick="approveWithdrawal(<?= $withdrawal['id'] ?>)" 
                                class="tw-flex-1 tw-bg-green-600 tw-text-white tw-rounded-md tw-px-3 tw-py-1.5 tw-text-xs tw-font-medium hover:tw-bg-green-700">
                            <i data-feather="check" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>
                            Approve
                        </button>
                        <button onclick="rejectWithdrawal(<?= $withdrawal['id'] ?>)" 
                                class="tw-flex-1 tw-bg-red-600 tw-text-white tw-rounded-md tw-px-3 tw-py-1.5 tw-text-xs tw-font-medium hover:tw-bg-red-700">
                            <i data-feather="x" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>
                            Reject
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Commission Rate Modal -->
<div id="commissionModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Edit Commission Rate</h3>
                <button onclick="closeCommissionModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <div class="tw-mb-4">
                <p class="tw-text-sm tw-text-gray-600">
                    <strong>Affiliate:</strong> <span id="modalAffiliateName"></span>
                </p>
                <p class="tw-text-sm tw-text-gray-600">
                    <strong>Current Rate:</strong> <span id="modalCurrentRate"></span>
                </p>
            </div>
            
            <div class="tw-mb-4">
                <label for="newCommissionRate" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    New Commission Rate (%)
                </label>
                <input type="number" 
                       id="newCommissionRate" 
                       min="0" 
                       max="100" 
                       step="0.1"
                       class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-sm:text-sm"
                       placeholder="Enter commission rate">
                </div>
            
            <div id="modalError" class="tw-mb-4 tw-text-red-600 tw-text-sm tw-hidden"></div>
            
            <div class="tw-flex tw-justify-end tw-space-x-3">
                <button onclick="closeCommissionModal()" 
                        class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                    Cancel
                </button>
                <button id="saveCommissionBtn" 
                        onclick="saveCommissionRate()"
                        class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Affiliate management functions
function viewAffiliate(affiliateId) {
    window.location.href = '<?= url('/admin/affiliates/') ?>' + affiliateId;
}

// Removed editAffiliate function - now using inline editing

// Notification function
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-px-6 tw-py-3 tw-rounded-md tw-shadow-lg tw-z-50 ${
        type === 'success' ? 'tw-bg-green-500 tw-text-white' : 'tw-bg-red-500 tw-text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s ease';
    }, 2000);
    
    setTimeout(() => {
        document.body.removeChild(notification);
    }, 3000);
}

function suspendAffiliate(affiliateId) {
    if (confirm('Are you sure you want to suspend this affiliate?')) {
        fetch('<?= url('/admin/affiliates/') ?>' + affiliateId + '/suspend', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error suspending affiliate: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while suspending the affiliate.');
        });
    }
}

function activateAffiliate(affiliateId) {
    if (confirm('Are you sure you want to activate this affiliate?')) {
        fetch('<?= url('/admin/affiliates/') ?>' + affiliateId + '/activate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error activating affiliate: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while activating the affiliate.', 'error');
        });
    }
}

function approveWithdrawal(withdrawalId) {
    if (confirm('Are you sure you want to approve this withdrawal?')) {
        fetch('<?= url('/admin/affiliate/approve-withdrawal') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include',
            body: JSON.stringify({
                withdrawal_id: withdrawalId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error approving withdrawal: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while approving the withdrawal.', 'error');
        });
    }
}

function rejectWithdrawal(withdrawalId) {
    const reason = prompt('Please provide a reason for rejection:', '');
    if (reason === null) return; // User cancelled
    
    if (reason.trim() === '') {
        showNotification('Please provide a reason for rejection.', 'error');
        return;
    }
    
    fetch('<?= url('/admin/affiliate/reject-withdrawal') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        body: JSON.stringify({
            withdrawal_id: withdrawalId,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error rejecting withdrawal: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while rejecting the withdrawal.', 'error');
    });
}

// Removed editCommissionRates function - commissions are now edited inline

// Search and filter functionality
document.getElementById('affiliate-search').addEventListener('input', function() {
    filterAffiliates();
});

document.getElementById('status-filter').addEventListener('change', function() {
    filterAffiliates();
});

document.getElementById('type-filter').addEventListener('change', function() {
    filterAffiliates();
});

function filterAffiliates() {
    const search = document.getElementById('affiliate-search').value;
    const status = document.getElementById('status-filter').value;
    const type = document.getElementById('type-filter').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (type) params.append('type', type);
    
    // Preserve current sort parameters
    const currentUrl = new URL(window.location);
    const currentSort = currentUrl.searchParams.get('sort') || 'earnings';
    const currentOrder = currentUrl.searchParams.get('order') || 'desc';
    params.append('sort', currentSort);
    params.append('order', currentOrder);
    
    // Update URL and reload content
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

function sortTable(sortBy) {
    const currentUrl = new URL(window.location);
    const currentSort = currentUrl.searchParams.get('sort') || 'earnings';
    const currentOrder = currentUrl.searchParams.get('order') || 'desc';
    
    // Determine new order
    let newOrder = 'desc';
    if (currentSort === sortBy && currentOrder === 'desc') {
        newOrder = 'asc';
    }
    
    // Build query parameters
    const params = new URLSearchParams();
    params.append('sort', sortBy);
    params.append('order', newOrder);
    
    // Preserve current filter parameters
    const search = currentUrl.searchParams.get('search') || '';
    const status = currentUrl.searchParams.get('status') || '';
    const type = currentUrl.searchParams.get('type') || '';
    
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (type) params.append('type', type);
    
    // Update URL and reload content
    const newUrl = window.location.pathname + '?' + params.toString();
    window.location.href = newUrl;
}

function exportAffiliates() {
    const search = document.getElementById('affiliate-search').value;
    const status = document.getElementById('status-filter').value;
    const type = document.getElementById('type-filter').value;

    const params = new URLSearchParams();
    params.append('export', 'affiliates');
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (type) params.append('type', type);

    window.open('<?= url('/admin/tools/export') ?>?' + params.toString(), '_blank');
}

function createCampaign() {
    // For now, show a simple prompt - this could be enhanced with a modal
    const campaignName = prompt('Enter campaign name:');
    if (!campaignName) return;

    const campaignType = prompt('Enter campaign type (referral/bonus/seasonal):');
    if (!campaignType) return;

    fetch('<?= url('/admin/affiliate/create-campaign') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'include',
        body: JSON.stringify({
            name: campaignName,
            type: campaignType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Campaign created successfully!');
            location.reload();
        } else {
            alert('Error creating campaign: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the campaign.');
    });
}

// Commission modal functions
let currentAffiliateId = null;
let currentAffiliateName = '';

function openCommissionModal(affiliateId, currentRate, affiliateName = '') {
    currentAffiliateId = affiliateId;
    currentAffiliateName = affiliateName;

    // Populate modal
    document.getElementById('modalAffiliateName').textContent = affiliateName || `Affiliate #${affiliateId}`;
    document.getElementById('modalCurrentRate').textContent = `${currentRate}%`;
    document.getElementById('newCommissionRate').value = currentRate;

    // Clear any previous errors
    document.getElementById('modalError').style.display = 'none';

    // Show modal
    document.getElementById('commissionModal').style.display = 'block';
    document.getElementById('newCommissionRate').focus();
}

function closeCommissionModal() {
    document.getElementById('commissionModal').style.display = 'none';
    currentAffiliateId = null;
    currentAffiliateName = '';
}

function saveCommissionRate() {
    const newRateInput = document.getElementById('newCommissionRate');
    const saveBtn = document.getElementById('saveCommissionBtn');
    const errorDiv = document.getElementById('modalError');

    const rate = parseFloat(newRateInput.value);

    // Validation
    if (isNaN(rate) || rate < 0 || rate > 100) {
        errorDiv.textContent = 'Please enter a valid commission rate between 0 and 100';
        errorDiv.style.display = 'block';
        return;
    }

    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    errorDiv.style.display = 'none';

    // Convert percentage to decimal for backend
    const rateDecimal = rate / 100;

    fetch(`<?= url('/admin/affiliates') ?>/${currentAffiliateId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'include',
        body: JSON.stringify({
            commission_rate: rateDecimal
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Commission rate updated successfully', 'success');

            // Update the display in the table
            const rateElement = document.querySelector(`tr[data-affiliate-id="${currentAffiliateId}"] .commission-rate`);
            if (rateElement) {
                rateElement.textContent = `${rate}%`;
            }

            // Close modal
            closeCommissionModal();

            // Reload page to reflect changes
            setTimeout(() => location.reload(), 1000);
        } else {
            errorDiv.textContent = data.message || 'Error updating commission rate. Please try again.';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = 'Network error. Please check your connection and try again. Error: ' + error.message;
        errorDiv.style.display = 'block';
    })
    .finally(() => {
        // Re-enable button
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Changes';
    });
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('commissionModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCommissionModal();
            }
        });
    }
});

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
