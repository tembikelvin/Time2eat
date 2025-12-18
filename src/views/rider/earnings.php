<?php
$title = $title ?? 'My Earnings - Time2Eat';
$user = $user ?? null;
$currentPage = $currentPage ?? 'earnings';
$earnings = $earnings ?? [];
$totalEarnings = $totalEarnings ?? 0;
$weeklyEarnings = $weeklyEarnings ?? 0;
$monthlyEarnings = $monthlyEarnings ?? 0;
$currentPeriod = $currentPeriod ?? '7days';
$currentPageNum = $currentPageNum ?? 1;
$totalPages = $totalPages ?? 1;
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">My Earnings</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Track your delivery earnings and payment history.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <select onchange="changePeriod(this.value)" class="tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm tw-focus:ring-orange-500 tw-focus:border-orange-500">
                <option value="7days" <?= $currentPeriod === '7days' ? 'selected' : '' ?>>Last 7 Days</option>
                <option value="30days" <?= $currentPeriod === '30days' ? 'selected' : '' ?>>Last 30 Days</option>
                <option value="90days" <?= $currentPeriod === '90days' ? 'selected' : '' ?>>Last 90 Days</option>
                <option value="all" <?= $currentPeriod === 'all' ? 'selected' : '' ?>>All Time</option>
            </select>
        </div>
    </div>
</div>

<!-- Earnings Overview -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <!-- Total Earnings Card -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Earnings</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">
                    <?= number_format($totalEarnings) ?> XAF
                </p>
                <p class="tw-text-sm tw-text-gray-500">All time</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <!-- Weekly Earnings Card -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">This Week</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">
                    <?= number_format($weeklyEarnings) ?> XAF
                </p>
                <p class="tw-text-sm tw-text-gray-500">Weekly earnings</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- Monthly Earnings Card -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">This Month</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">
                    <?= number_format($monthlyEarnings) ?> XAF
                </p>
                <p class="tw-text-sm tw-text-gray-500">Monthly earnings</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="calendar" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>

    <!-- Available Balance Card -->
    <div class="tw-bg-gradient-to-br tw-from-orange-500 tw-to-red-500 tw-p-6 tw-rounded-xl tw-shadow-lg tw-text-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-white tw-opacity-90">Available Balance</p>
                <p class="tw-text-3xl tw-font-bold">
                    <?= number_format($availableBalance ?? 0) ?> XAF
                </p>
                <?php if (($pendingWithdrawals ?? 0) > 0): ?>
                    <p class="tw-text-sm tw-text-white tw-opacity-75">
                        <?= number_format($pendingWithdrawals) ?> XAF pending
                    </p>
                <?php else: ?>
                    <p class="tw-text-sm tw-text-white tw-opacity-75">Ready to withdraw</p>
                <?php endif; ?>
            </div>
            <div class="tw-p-3 tw-bg-white tw-bg-opacity-20 tw-rounded-full">
                <i data-feather="credit-card" class="tw-h-6 tw-w-6"></i>
            </div>
        </div>
        <button onclick="openWithdrawalModal()" 
                class="tw-w-full tw-bg-white tw-text-orange-600 tw-py-2 tw-px-4 tw-rounded-lg tw-font-medium tw-text-sm hover:tw-bg-opacity-90 tw-transition-all tw-flex tw-items-center tw-justify-center"
                <?= ($availableBalance ?? 0) < 2000 ? 'disabled' : '' ?>>
            <i data-feather="arrow-up-circle" class="tw-h-4 tw-w-4 tw-mr-2"></i>
            <?= ($availableBalance ?? 0) < 2000 ? 'Minimum 2,000 XAF' : 'Request Withdrawal' ?>
        </button>
    </div>

    <!-- Average per Delivery Card -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Average per Delivery</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">
                    <?php 
                    $avgEarning = count($earnings) > 0 ? $totalEarnings / count($earnings) : 0;
                    echo number_format($avgEarning);
                    ?> XAF
                </p>
                <p class="tw-text-sm tw-text-gray-500">Per completed order</p>
            </div>
            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                <i data-feather="truck" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Earnings History -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Earnings History</h2>
            <button onclick="exportEarnings()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-orange-100 tw-text-orange-800 hover:tw-bg-orange-200 tw-transition-colors">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Export
            </button>
        </div>
    </div>
    
    <div class="tw-p-6">
        <?php if (empty($earnings)): ?>
            <div class="tw-text-center tw-py-12">
                <div class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400">
                    <i data-feather="dollar-sign" class="tw-h-12 tw-w-12"></i>
                </div>
                <h3 class="tw-mt-2 tw-text-sm tw-font-medium tw-text-gray-900">No earnings yet</h3>
                <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                    Complete some deliveries to start earning money.
                </p>
                <div class="tw-mt-6">
                    <a href="<?= url('/rider/available') ?>" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-shadow-sm tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                        <i data-feather="search" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Find Available Orders
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                    <thead class="tw-bg-gray-50">
                        <tr>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Date
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Order
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Restaurant
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Distance
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Earnings
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                        <?php foreach ($earnings as $earning): ?>
                            <tr class="hover:tw-bg-gray-50">
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                    <?= date('M j, Y', strtotime($earning['delivery_date'] ?? $earning['created_at'])) ?>
                                    <div class="tw-text-xs tw-text-gray-500">
                                        <?= date('H:i', strtotime($earning['delivery_time'] ?? $earning['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                        #<?= e($earning['order_number'] ?? $earning['id']) ?>
                                    </div>
                                    <div class="tw-text-sm tw-text-gray-500">
                                        <?= number_format($earning['total_amount'] ?? 0) ?> XAF order
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                    <?= e($earning['restaurant_name'] ?? 'Restaurant') ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                    <?= number_format($earning['distance_km'] ?? 0, 1) ?> km
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <div class="tw-text-sm tw-font-medium tw-text-green-600">
                                        +<?= number_format($earning['delivery_fee'] ?? 0) ?> XAF
                                    </div>
                                    <?php if (isset($earning['tip_amount']) && $earning['tip_amount'] > 0): ?>
                                        <div class="tw-text-xs tw-text-blue-600">
                                            +<?= number_format($earning['tip_amount']) ?> XAF tip
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                        <i data-feather="check" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                                        Completed
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
                    <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-500">
                        Showing page <?= $currentPageNum ?> of <?= $totalPages ?>
                    </div>
                    <div class="tw-flex tw-space-x-2">
                        <?php if ($currentPageNum > 1): ?>
                            <a href="<?= url('/rider/earnings?period=' . $currentPeriod . '&page=' . ($currentPageNum - 1)) ?>" 
                               class="tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($currentPageNum < $totalPages): ?>
                            <a href="<?= url('/rider/earnings?period=' . $currentPeriod . '&page=' . ($currentPageNum + 1)) ?>" 
                               class="tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Withdrawal Modal -->
<div id="withdrawalModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-z-50 tw-flex tw-items-center tw-justify-center">
    <div class="tw-bg-white tw-rounded-xl tw-shadow-2xl tw-max-w-md tw-w-full tw-mx-4">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h3 class="tw-text-xl tw-font-bold tw-text-gray-900">Request Withdrawal</h3>
                <button onclick="closeWithdrawalModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-6 tw-w-6"></i>
                </button>
            </div>
        </div>
        <form id="withdrawalForm" class="tw-p-6 tw-space-y-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Withdrawal Amount (XAF)
                </label>
                <input type="number" id="withdrawalAmount" name="amount" 
                       min="2000" max="<?= $availableBalance ?? 0 ?>" step="100"
                       class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent"
                       placeholder="Enter amount" required>
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">
                    Available: <?= number_format($availableBalance ?? 0) ?> XAF (Min: 2,000 XAF)
                </p>
            </div>
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Payment Method
                </label>
                <select id="paymentMethod" name="payment_method" 
                        class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent" required>
                    <option value="">Select payment method</option>
                    <option value="mobile_money">Mobile Money (MTN/Orange)</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            
            <div id="mobileMoneyField" class="tw-hidden">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Mobile Money Number
                </label>
                <input type="tel" id="mobileNumber" name="mobile_number"
                       class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent"
                       placeholder="6XXXXXXXX">
            </div>
            
            <div id="bankDetailsField" class="tw-hidden tw-space-y-3">
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Bank Name
                    </label>
                    <input type="text" id="bankName" name="bank_name"
                           class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent"
                           placeholder="Enter bank name">
                </div>
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Account Number
                    </label>
                    <input type="text" id="accountNumber" name="account_number"
                           class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent"
                           placeholder="Enter account number">
                </div>
            </div>
            
            <div class="tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg tw-p-4">
                <p class="tw-text-sm tw-text-yellow-800">
                    <i data-feather="info" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                    Withdrawals are processed within 1-3 business days
                </p>
            </div>
            
            <div class="tw-flex tw-space-x-3">
                <button type="button" onclick="closeWithdrawalModal()" 
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-700 hover:tw-bg-gray-50">
                    Cancel
                </button>
                <button type="submit" id="submitWithdrawal"
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-orange-600 tw-text-white tw-rounded-lg hover:tw-bg-orange-700 tw-flex tw-items-center tw-justify-center">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function changePeriod(period) {
    window.location.href = `/rider/earnings?period=${period}`;
}

function exportEarnings() {
    window.open('/rider/earnings/export?period=<?= $currentPeriod ?>', '_blank');
}

function openWithdrawalModal() {
    document.getElementById('withdrawalModal').classList.remove('tw-hidden');
    feather.replace();
}

function closeWithdrawalModal() {
    document.getElementById('withdrawalModal').classList.add('tw-hidden');
    document.getElementById('withdrawalForm').reset();
    document.getElementById('mobileMoneyField').classList.add('tw-hidden');
    document.getElementById('bankDetailsField').classList.add('tw-hidden');
}

// Show/hide payment method fields
document.getElementById('paymentMethod').addEventListener('change', function() {
    const mobileField = document.getElementById('mobileMoneyField');
    const bankField = document.getElementById('bankDetailsField');
    
    if (this.value === 'mobile_money') {
        mobileField.classList.remove('tw-hidden');
        bankField.classList.add('tw-hidden');
        document.getElementById('mobileNumber').required = true;
        document.getElementById('bankName').required = false;
        document.getElementById('accountNumber').required = false;
    } else if (this.value === 'bank_transfer') {
        mobileField.classList.add('tw-hidden');
        bankField.classList.remove('tw-hidden');
        document.getElementById('mobileNumber').required = false;
        document.getElementById('bankName').required = true;
        document.getElementById('accountNumber').required = true;
    } else {
        mobileField.classList.add('tw-hidden');
        bankField.classList.add('tw-hidden');
    }
});

// Handle withdrawal form submission
document.getElementById('withdrawalForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitWithdrawal');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Processing...';
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Add account details as JSON
    if (data.payment_method === 'mobile_money') {
        data.account_details = JSON.stringify({ mobile_number: data.mobile_number });
        delete data.mobile_number;
    } else if (data.payment_method === 'bank_transfer') {
        data.account_details = JSON.stringify({ 
            bank_name: data.bank_name,
            account_number: data.account_number
        });
        delete data.bank_name;
        delete data.account_number;
    }
    
    try {
        const response = await fetch('<?= url('/rider/request-withdrawal') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Withdrawal request submitted successfully!');
            window.location.reload();
        } else {
            alert(result.message || 'Failed to submit withdrawal request');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-feather="check" class="tw-h-4 tw-w-4 tw-mr-2"></i>Submit Request';
            feather.replace();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i data-feather="check" class="tw-h-4 tw-w-4 tw-mr-2"></i>Submit Request';
        feather.replace();
    }
});
</script>
