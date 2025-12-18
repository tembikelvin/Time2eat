<?php
/**
 * Admin Financial Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'financial';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Financial Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage platform finances, payouts, and revenue tracking
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button onclick="exportPayments()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-flex tw-items-center tw-justify-center">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Export Payments
            </button>
            <button class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-flex tw-items-center tw-justify-center">
                <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Process Payout
            </button>
        </div>
    </div>
</div>

<!-- Financial Overview -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Revenue</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($financialData['total_revenue'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Platform Commission</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($financialData['platform_commission'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">10% of revenue</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="percent" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Pending Payouts</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($financialData['pending_payouts'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-yellow-600"><?= $financialData['pending_payout_count'] ?? 0 ?> requests</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Monthly Growth</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">+18%</p>
                <p class="tw-text-sm tw-text-green-600">vs last month</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="bar-chart-2" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Revenue Trends</h2>
            <div class="tw-flex tw-space-x-2">
                <select class="tw-text-sm tw-border-gray-300 tw-rounded-md">
                    <option>Last 7 days</option>
                    <option>Last 30 days</option>
                    <option>Last 3 months</option>
                    <option>Last year</option>
                </select>
            </div>
        </div>
    </div>
    <div class="tw-p-6">
        <canvas id="revenueChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Pending Payouts -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Pending Payouts</h2>
                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800">
                    <?= $financialData['pending_payout_count'] ?? 0 ?> Requests
                </span>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4 tw-max-h-96 tw-overflow-y-auto">
            <?php 
            $samplePayouts = [
                ['id' => 'PAY-001', 'vendor' => 'Mama Grace Kitchen', 'amount' => 450000, 'date' => '2024-02-15', 'type' => 'vendor'],
                ['id' => 'PAY-002', 'vendor' => 'James Rider', 'amount' => 125000, 'date' => '2024-02-15', 'type' => 'rider'],
                ['id' => 'PAY-003', 'vendor' => 'Continental Delights', 'amount' => 320000, 'date' => '2024-02-14', 'type' => 'vendor'],
                ['id' => 'PAY-004', 'vendor' => 'Mary Rider', 'amount' => 89000, 'date' => '2024-02-14', 'type' => 'rider'],
                ['id' => 'PAY-005', 'vendor' => 'Fast Bites', 'amount' => 275000, 'date' => '2024-02-13', 'type' => 'vendor']
            ];
            
            foreach ($pendingPayouts ?? $samplePayouts as $payout): ?>
            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-2 tw-bg-yellow-100 tw-rounded-lg">
                        <i data-feather="<?= $payout['type'] === 'vendor' ? 'shopping-bag' : 'truck' ?>" class="tw-h-5 tw-w-5 tw-text-yellow-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($payout['vendor']) ?></div>
                        <div class="tw-text-sm tw-text-gray-500"><?= e($payout['id']) ?> • <?= date('M j, Y', strtotime($payout['date'])) ?></div>
                    </div>
                </div>
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-text-right">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= number_format($payout['amount']) ?> XAF</div>
                        <div class="tw-text-xs tw-text-gray-500"><?= ucfirst($payout['type']) ?></div>
                    </div>
                    <div class="tw-flex tw-space-x-2">
                        <button onclick="approvePayout('<?= $payout['id'] ?>')" class="tw-text-green-600 hover:tw-text-green-900">
                            <i data-feather="check" class="tw-h-4 tw-w-4"></i>
                        </button>
                        <button onclick="rejectPayout('<?= $payout['id'] ?>')" class="tw-text-red-600 hover:tw-text-red-900">
                            <i data-feather="x" class="tw-h-4 tw-w-4"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Financial Settings -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Financial Settings</h2>
        </div>
        <div class="tw-p-6 tw-space-y-6">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Platform Commission (%)</label>
                <input type="number" value="10" min="0" max="100" class="tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500">
                <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Commission taken from each order</p>
            </div>
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Minimum Payout (XAF)</label>
                <input type="number" value="50000" min="1000" class="tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500">
                <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Minimum amount for payout requests</p>
            </div>
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Delivery Base Fee (XAF)</label>
                <input type="number" value="1000" min="100" class="tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500">
                <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Base delivery fee</p>
            </div>
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Per KM Rate (XAF)</label>
                <input type="number" value="200" min="50" class="tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500">
                <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Additional fee per kilometer</p>
            </div>
            
            <button class="tw-w-full tw-bg-primary-600 tw-text-white tw-py-2 tw-px-4 tw-rounded-md tw-font-medium hover:tw-bg-primary-700 tw-transition-colors">
                Save Settings
            </button>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Recent Transactions</h2>
            <button class="tw-text-primary-600 hover:tw-text-primary-700 tw-text-sm tw-font-medium">
                View All
            </button>
        </div>
    </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Transaction ID</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Type</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php 
                $sampleTransactions = [
                    ['id' => 'TXN-001', 'type' => 'Order Payment', 'amount' => 15500, 'status' => 'completed', 'date' => '2024-02-15 14:30:00'],
                    ['id' => 'TXN-002', 'type' => 'Vendor Payout', 'amount' => -450000, 'status' => 'completed', 'date' => '2024-02-15 14:25:00'],
                    ['id' => 'TXN-003', 'type' => 'Order Payment', 'amount' => 22000, 'status' => 'completed', 'date' => '2024-02-15 14:20:00'],
                    ['id' => 'TXN-004', 'type' => 'Rider Payout', 'amount' => -125000, 'status' => 'pending', 'date' => '2024-02-15 14:15:00'],
                    ['id' => 'TXN-005', 'type' => 'Order Payment', 'amount' => 8500, 'status' => 'completed', 'date' => '2024-02-15 14:10:00']
                ];
                
                foreach ($recentTransactions ?? $sampleTransactions as $transaction): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">
                        <?= e($transaction['id']) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                        <?= e($transaction['type']) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium <?= $transaction['amount'] > 0 ? 'tw-text-green-600' : 'tw-text-red-600' ?>">
                        <?= $transaction['amount'] > 0 ? '+' : '' ?><?= number_format($transaction['amount']) ?> XAF
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                            <?= $transaction['status'] === 'completed' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-yellow-100 tw-text-yellow-800' ?>">
                            <?= ucfirst($transaction['status']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= date('M j, Y H:i', strtotime($transaction['date'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Financial management functions
function approvePayout(payoutId) {
    if (confirm('Are you sure you want to approve this payout?')) {
        fetch(`/admin/financial/payouts/${payoutId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error approving payout: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the payout.');
        });
    }
}

function rejectPayout(payoutId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
        fetch(`/admin/financial/payouts/${payoutId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error rejecting payout: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while rejecting the payout.');
        });
    }
}

// Revenue Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Revenue (XAF)',
                data: [3200000, 3800000, 4100000, 3900000, 4300000, 4600000, 4575000],
                borderColor: 'rgb(249, 115, 22)',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
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

    function exportPayments() {
        // Show loading state
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="tw-animate-spin tw-h-4 tw-w-4 tw-inline tw-mr-2">⟳</i>Exporting...';
        button.disabled = true;

        // Create download link for payments export
        const link = document.createElement('a');
        link.href = '/admin/export/payments';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Reset button state after delay
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }

    feather.replace();
});
</script>
