<?php
$title = $title ?? 'Earnings - Time2Eat';
$currentPage = $currentPage ?? 'earnings';
$user = $user ?? null;
$earnings = $earnings ?? [];
$totalEarnings = $totalEarnings ?? [];
$monthlyEarnings = $monthlyEarnings ?? [];
$pendingPayouts = $pendingPayouts ?? [];
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Earnings</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Track your revenue and payouts.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <button type="button" onclick="requestPayout()" 
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                <i data-feather="credit-card" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Request Payout
            </button>
        </div>
    </div>
</div>

<!-- Earnings Content -->
        <!-- Earnings Summary -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-green-100">
                        <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Available Balance</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($earnings['availableBalance'] ?? 0) ?> XAF</p>
                        <p class="tw-text-sm tw-text-green-600">Ready for payout</p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-blue-100">
                        <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">This Month</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($earnings['thisMonth'] ?? 0) ?> XAF</p>
                        <p class="tw-text-sm tw-text-blue-600">+15% from last month</p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-yellow-100">
                        <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Pending</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($earnings['pending'] ?? 0) ?> XAF</p>
                        <p class="tw-text-sm tw-text-yellow-600">Processing orders</p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-purple-100">
                        <i data-feather="archive" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Earned</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($earnings['totalEarned'] ?? 0) ?> XAF</p>
                        <p class="tw-text-sm tw-text-purple-600">All time</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings Chart & Breakdown -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
            <!-- Earnings Chart -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Earnings Trend</h3>
                <canvas id="earningsChart" width="400" height="200"></canvas>
            </div>
            
            <!-- Earnings Breakdown -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Earnings Breakdown</h3>
                <div class="tw-space-y-4">
                    <div class="tw-flex tw-justify-between tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-w-3 tw-h-3 tw-bg-green-500 tw-rounded-full tw-mr-3"></div>
                            <span class="tw-text-sm tw-font-medium tw-text-gray-700">Food Sales</span>
                        </div>
                        <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= number_format($earnings['foodSales'] ?? 0) ?> XAF</span>
                    </div>
                    
                    <div class="tw-flex tw-justify-between tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-w-3 tw-h-3 tw-bg-blue-500 tw-rounded-full tw-mr-3"></div>
                            <span class="tw-text-sm tw-font-medium tw-text-gray-700">Delivery Fees</span>
                        </div>
                        <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= number_format($earnings['deliveryFees'] ?? 0) ?> XAF</span>
                    </div>
                    
                    <div class="tw-flex tw-justify-between tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-w-3 tw-h-3 tw-bg-red-500 tw-rounded-full tw-mr-3"></div>
                            <span class="tw-text-sm tw-font-medium tw-text-gray-700">Platform Fee</span>
                        </div>
                        <span class="tw-text-sm tw-font-semibold tw-text-red-600">-<?= number_format($earnings['platformFee'] ?? 0) ?> XAF</span>
                    </div>
                    
                    <div class="tw-flex tw-justify-between tw-items-center tw-p-4 tw-bg-orange-50 tw-rounded-lg tw-border tw-border-orange-200">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-w-3 tw-h-3 tw-bg-orange-500 tw-rounded-full tw-mr-3"></div>
                            <span class="tw-text-sm tw-font-medium tw-text-gray-700">Net Earnings</span>
                        </div>
                        <span class="tw-text-sm tw-font-bold tw-text-orange-600"><?= number_format($earnings['netEarnings'] ?? 0) ?> XAF</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payout History -->
        <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900">Payout History</h2>
            </div>
            
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                    <thead class="tw-bg-gray-50">
                        <tr>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Method</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                        <?php if (!empty($payouts)): ?>
                            <?php foreach ($payouts as $payout): ?>
                            <?php
                            $statusColors = [
                                'completed' => 'tw-bg-green-100 tw-text-green-800',
                                'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
                                'processing' => 'tw-bg-blue-100 tw-text-blue-800',
                                'failed' => 'tw-bg-red-100 tw-text-red-800'
                            ];
                            $statusColor = $statusColors[$payout['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                            ?>
                            <tr>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                    <?= date('M j, Y', strtotime($payout['created_at'])) ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">
                                    <?= number_format($payout['amount']) ?> XAF
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                    <?= ucfirst($payout['method']) ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <span class="tw-inline-flex tw-px-2 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full <?= $statusColor ?>">
                                        <?= ucfirst($payout['status']) ?>
                                    </span>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                    <?= e($payout['reference']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                                <i data-feather="credit-card" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-4"></i>
                                <p>No payout history yet</p>
                                <p class="tw-text-sm">Request your first payout when you have available balance</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<!-- Payout Request Modal -->
<div id="payoutModal" class="tw-fixed tw-inset-0 tw-bg-gray-900 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50 tw-transition-opacity tw-duration-300">
    <div class="tw-flex tw-items-center tw-justify-center tw-min-h-screen tw-p-4">
        <div class="tw-relative tw-bg-white tw-rounded-xl tw-shadow-2xl tw-max-w-md tw-w-full tw-transform tw-transition-all tw-duration-300">
            <!-- Modal Header -->
            <div class="tw-px-6 tw-py-5 tw-border-b tw-border-gray-200 tw-bg-gradient-to-r tw-from-orange-50 tw-to-orange-100">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-p-2 tw-bg-orange-500 tw-rounded-lg">
                            <i data-feather="credit-card" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <div>
                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Request Payout</h3>
                            <p class="tw-text-xs tw-text-gray-600 tw-mt-0.5">Via Tranzack Payment Gateway</p>
                        </div>
                    </div>
                    <button type="button" onclick="closePayoutModal()" 
                            class="tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors tw-p-1 hover:tw-bg-gray-100 tw-rounded-lg">
                        <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <form id="payoutForm" class="tw-p-6 tw-space-y-5">
                <!-- Amount Input -->
                <div>
                    <label for="payoutAmount" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Amount (XAF) <span class="tw-text-red-500">*</span>
                    </label>
                    <div class="tw-relative">
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <span class="tw-text-gray-500 tw-text-sm">₣</span>
                        </div>
                        <input type="number" 
                               id="payoutAmount" 
                               name="amount" 
                               min="1" 
                               max="<?= $earnings['availableBalance'] ?? 0 ?>" 
                               step="1"
                               required
                               class="tw-block tw-w-full tw-pl-8 tw-pr-3 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-base tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-colors tw-bg-white hover:tw-border-gray-400">
                    </div>
                    <div class="tw-mt-2 tw-flex tw-items-center tw-justify-between">
                        <p class="tw-text-xs tw-text-gray-500">
                            Available: <span class="tw-font-semibold tw-text-green-600"><?= number_format($earnings['availableBalance'] ?? 0) ?> XAF</span>
                        </p>
                        <button type="button" onclick="setMaxAmount()" 
                                class="tw-text-xs tw-text-orange-600 hover:tw-text-orange-700 tw-font-medium tw-transition-colors">
                            Use Max
                        </button>
                    </div>
                </div>

                <!-- Payout Method Selection -->
                <div>
                    <label for="payoutMethod" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Payout Method <span class="tw-text-red-500">*</span>
                    </label>
                    <select id="payoutMethod" 
                            name="method" 
                            required
                            onchange="updateDetailsFields()"
                            class="tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-base tw-text-gray-900 tw-bg-white focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-colors hover:tw-border-gray-400 tw-appearance-none tw-bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNNSA3LjVMIDEwIDEyLjVMIDE1IDcuNSIgc3Ryb2tlPSIjNkI3Mjc4IiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjwvc3ZnPg==')] tw-bg-[length:20px_20px] tw-bg-[right_12px_center] tw-bg-no-repeat">
                        <option value="">Select payout method</option>
                        <option value="mobile_money">Mobile Money (MTN/Orange)</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <!-- Dynamic Details Fields -->
                <div id="detailsContainer" class="tw-space-y-4 tw-hidden">
                    <!-- Mobile Money Fields -->
                    <div id="mobileMoneyFields" class="tw-space-y-4 tw-hidden">
                        <div>
                            <label for="mobilePhone" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                                Phone Number <span class="tw-text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   id="mobilePhone" 
                                   name="phone" 
                                   placeholder="e.g., 677123456"
                                   pattern="[0-9]{9,15}"
                                   class="tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-base tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-colors tw-bg-white hover:tw-border-gray-400">
                            <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Enter your mobile money phone number</p>
                        </div>
                        <div>
                            <label for="mobileProvider" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                                Provider <span class="tw-text-red-500">*</span>
                            </label>
                            <select id="mobileProvider" 
                                    name="provider" 
                                    class="tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-base tw-text-gray-900 tw-bg-white focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-colors hover:tw-border-gray-400 tw-appearance-none tw-bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNNSA3LjVMIDEwIDEyLjVMIDE1IDcuNSIgc3Ryb2tlPSIjNkI3Mjc4IiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjwvc3ZnPg==')] tw-bg-[length:20px_20px] tw-bg-[right_12px_center] tw-bg-no-repeat">
                                <option value="MTN">MTN</option>
                                <option value="Orange">Orange Money</option>
                            </select>
                        </div>
                    </div>

                    <!-- Bank Transfer Fields -->
                    <div id="bankTransferFields" class="tw-space-y-4 tw-hidden">
                        <div>
                            <label for="bankName" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                                Bank Name <span class="tw-text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="bankName" 
                                   name="bank_name" 
                                   placeholder="e.g., UBA, Ecobank, Afriland"
                                   class="tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-base tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-colors tw-bg-white hover:tw-border-gray-400">
                        </div>
                        <div>
                            <label for="accountNumber" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                                Account Number <span class="tw-text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="accountNumber" 
                                   name="account_number" 
                                   placeholder="Enter account number"
                                   class="tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-base tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-colors tw-bg-white hover:tw-border-gray-400">
                        </div>
                        <div>
                            <label for="accountName" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                                Account Name <span class="tw-text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="accountName" 
                                   name="account_name" 
                                   placeholder="Account holder name"
                                   class="tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-base tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-colors tw-bg-white hover:tw-border-gray-400">
                        </div>
                    </div>
                </div>

                <!-- Payment Gateway Info -->
                <div class="tw-p-3 tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-lg">
                    <div class="tw-flex tw-items-start">
                        <i data-feather="info" class="tw-h-4 tw-w-4 tw-text-blue-600 tw-mr-2 tw-mt-0.5"></i>
                        <div class="tw-flex-1">
                            <p class="tw-text-xs tw-font-medium tw-text-blue-900">Powered by Tranzack</p>
                            <p class="tw-text-xs tw-text-blue-700 tw-mt-1">Your payout will be processed securely through Tranzack payment gateway.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="tw-flex tw-justify-end tw-space-x-3 tw-pt-4 tw-border-t tw-border-gray-200">
                    <button type="button" 
                            onclick="closePayoutModal()" 
                            class="tw-px-5 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="tw-px-5 tw-py-2.5 tw-border tw-border-transparent tw-rounded-lg tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700 tw-transition-colors tw-flex tw-items-center tw-space-x-2">
                        <i data-feather="send" class="tw-h-4 tw-w-4"></i>
                        <span>Request Payout</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Earnings Chart
const earningsCtx = document.getElementById('earningsChart').getContext('2d');
const earningsChart = new Chart(earningsCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($earnings['chartLabels'] ?? ['Week 1', 'Week 2', 'Week 3', 'Week 4']) ?>,
        datasets: [{
            label: 'Earnings (XAF)',
            data: <?= json_encode($earnings['chartData'] ?? [35000, 42000, 38000, 51000]) ?>,
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
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

// Payout modal functions
function requestPayout() {
    const modal = document.getElementById('payoutModal');
    modal.classList.remove('tw-hidden');
    modal.style.display = 'block';
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

function closePayoutModal() {
    const modal = document.getElementById('payoutModal');
    modal.classList.add('tw-hidden');
    modal.style.display = 'none';
    // Reset form
    document.getElementById('payoutForm').reset();
    document.getElementById('detailsContainer').classList.add('tw-hidden');
    document.getElementById('mobileMoneyFields').classList.add('tw-hidden');
    document.getElementById('bankTransferFields').classList.add('tw-hidden');
}

function setMaxAmount() {
    const maxAmount = <?= $earnings['availableBalance'] ?? 0 ?>;
    document.getElementById('payoutAmount').value = maxAmount;
}

function updateDetailsFields() {
    const method = document.getElementById('payoutMethod').value;
    const detailsContainer = document.getElementById('detailsContainer');
    const mobileMoneyFields = document.getElementById('mobileMoneyFields');
    const bankTransferFields = document.getElementById('bankTransferFields');
    
    // Hide all fields first
    mobileMoneyFields.classList.add('tw-hidden');
    bankTransferFields.classList.add('tw-hidden');
    detailsContainer.classList.add('tw-hidden');
    
    // Show relevant fields based on method
    if (method === 'mobile_money') {
        detailsContainer.classList.remove('tw-hidden');
        mobileMoneyFields.classList.remove('tw-hidden');
        // Set required attributes
        document.getElementById('mobilePhone').required = true;
        document.getElementById('mobileProvider').required = true;
        // Remove required from bank fields
        document.getElementById('bankName').required = false;
        document.getElementById('accountNumber').required = false;
        document.getElementById('accountName').required = false;
    } else if (method === 'bank_transfer') {
        detailsContainer.classList.remove('tw-hidden');
        bankTransferFields.classList.remove('tw-hidden');
        // Set required attributes
        document.getElementById('bankName').required = true;
        document.getElementById('accountNumber').required = true;
        document.getElementById('accountName').required = true;
        // Remove required from mobile fields
        document.getElementById('mobilePhone').required = false;
        document.getElementById('mobileProvider').required = false;
    }
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Form submission
document.getElementById('payoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Disable button and show loading
    submitButton.disabled = true;
    submitButton.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-animate-spin"></i><span class="tw-ml-2">Processing...</span>';
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    const formData = new FormData(this);
    const method = formData.get('method');
    
    // Build details object based on method
    let details = {};
    if (method === 'mobile_money') {
        details = {
            phone: formData.get('phone'),
            provider: formData.get('provider')
        };
    } else if (method === 'bank_transfer') {
        details = {
            bank_name: formData.get('bank_name'),
            account_number: formData.get('account_number'),
            account_name: formData.get('account_name')
        };
    }
    
    // Add details as JSON string
    formData.delete('phone');
    formData.delete('provider');
    formData.delete('bank_name');
    formData.delete('account_number');
    formData.delete('account_name');
    formData.append('details', JSON.stringify(details));
    
    fetch('<?= url('/vendor/earnings/payout') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closePayoutModal();
            // Show success message
            alert('Payout request submitted successfully! Your payment will be processed via Tranzack.');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to submit payout request'));
            // Re-enable button
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the payout request. Please try again.');
        // Re-enable button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
});

// Close modal when clicking outside
document.getElementById('payoutModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePayoutModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('payoutModal');
        if (!modal.classList.contains('tw-hidden')) {
            closePayoutModal();
        }
    }
});
</script>
