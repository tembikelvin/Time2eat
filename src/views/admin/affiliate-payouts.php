<?php
/**
 * Admin Affiliate Payouts Management Page
 * Enhanced payout tracking and management
 */

// Set current page for sidebar highlighting
$currentPage = 'affiliate-payouts';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Affiliate Payouts</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Track and manage affiliate payout processing and history
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button onclick="exportPayouts()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Export Data
            </button>
            <button onclick="refreshPayouts()" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Refresh Status
            </button>
        </div>
    </div>
</div>

<!-- Payout Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Payouts</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_payouts'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500"><?= number_format($stats['total_amount'] ?? 0) ?> XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="credit-card" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Processing</p>
                <p class="tw-text-3xl tw-font-bold tw-text-orange-600"><?= number_format($stats['processing_payouts'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500"><?= number_format($stats['processing_amount'] ?? 0) ?> XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Completed</p>
                <p class="tw-text-3xl tw-font-bold tw-text-green-600"><?= number_format($stats['completed_payouts'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500"><?= number_format($stats['completed_amount'] ?? 0) ?> XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Failed</p>
                <p class="tw-text-3xl tw-font-bold tw-text-red-600"><?= number_format($stats['failed_payouts'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-red-500">Requires attention</p>
            </div>
            <div class="tw-p-3 tw-bg-red-100 tw-rounded-full">
                <i data-feather="alert-circle" class="tw-h-6 tw-w-6 tw-text-red-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Payouts Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Payout History</h3>
    </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Affiliate</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Payment Method</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Transaction ID</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Processed</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (empty($payouts)): ?>
                <tr>
                    <td colspan="7" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                        <i data-feather="credit-card" class="tw-h-12 tw-w-12 tw-mx-auto tw-mb-4 tw-text-gray-400"></i>
                        <p class="tw-text-lg tw-font-medium">No payouts processed yet</p>
                        <p class="tw-text-sm">Payout history will appear here when withdrawals are processed.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($payouts as $payout): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-flex-shrink-0">
                                <div class="tw-h-10 tw-w-10 tw-rounded-full tw-bg-primary-100 tw-flex tw-items-center tw-justify-center">
                                    <span class="tw-text-sm tw-font-medium tw-text-primary-600">
                                        <?= strtoupper(substr($payout['first_name'], 0, 1) . substr($payout['last_name'], 0, 1)) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="tw-ml-4">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                    <?= htmlspecialchars($payout['first_name'] . ' ' . $payout['last_name']) ?>
                                </div>
                                <div class="tw-text-sm tw-text-gray-500"><?= htmlspecialchars($payout['email']) ?></div>
                                <div class="tw-text-xs tw-text-gray-400">Code: <?= htmlspecialchars($payout['affiliate_code']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= number_format($payout['amount']) ?> XAF</div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-text-gray-900"><?= ucfirst(str_replace('_', ' ', $payout['payment_method'])) ?></div>
                        <?php if ($payout['payment_details']): ?>
                        <div class="tw-text-xs tw-text-gray-500">
                            <?php 
                            $details = json_decode($payout['payment_details'], true);
                            if ($details && isset($details['phone'])) {
                                echo htmlspecialchars($details['phone']);
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <?php if ($payout['transaction_id']): ?>
                        <div class="tw-text-sm tw-font-mono tw-text-gray-900"><?= htmlspecialchars($payout['transaction_id']) ?></div>
                        <?php else: ?>
                        <span class="tw-text-sm tw-text-gray-400">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <?php
                        $statusClasses = [
                            'processing' => 'tw-bg-blue-100 tw-text-blue-800',
                            'completed' => 'tw-bg-green-100 tw-text-green-800',
                            'failed' => 'tw-bg-red-100 tw-text-red-800',
                            'cancelled' => 'tw-bg-gray-100 tw-text-gray-800'
                        ];
                        $statusClass = $statusClasses[$payout['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                        ?>
                        <span class="tw-inline-flex tw-px-2 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full <?= $statusClass ?>">
                            <?= ucfirst($payout['status']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?php if ($payout['processed_at']): ?>
                        <?= date('M j, Y', strtotime($payout['processed_at'])) ?>
                        <div class="tw-text-xs tw-text-gray-400"><?= date('H:i', strtotime($payout['processed_at'])) ?></div>
                        <?php else: ?>
                        <span class="tw-text-gray-400">Not processed</span>
                        <?php endif; ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-2">
                            <?php if ($payout['status'] === 'processing'): ?>
                            <button onclick="markCompleted(<?= $payout['id'] ?>)" 
                                    class="tw-text-green-600 hover:tw-text-green-900 tw-text-xs tw-bg-green-100 tw-px-2 tw-py-1 tw-rounded">
                                <i data-feather="check" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>
                                Complete
                            </button>
                            <button onclick="markFailed(<?= $payout['id'] ?>)" 
                                    class="tw-text-red-600 hover:tw-text-red-900 tw-text-xs tw-bg-red-100 tw-px-2 tw-py-1 tw-rounded">
                                <i data-feather="x" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>
                                Failed
                            </button>
                            <?php elseif ($payout['status'] === 'failed'): ?>
                            <button onclick="retryPayout(<?= $payout['id'] ?>)" 
                                    class="tw-text-blue-600 hover:tw-text-blue-900 tw-text-xs tw-bg-blue-100 tw-px-2 tw-py-1 tw-rounded">
                                <i data-feather="refresh-cw" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>
                                Retry
                            </button>
                            <?php else: ?>
                            <span class="tw-text-gray-400 tw-text-xs">
                                <?= ucfirst($payout['status']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    feather.replace();
});

function exportPayouts() {
    window.open('<?= url('/admin/tools/export') ?>?export=payouts', '_blank');
}

function refreshPayouts() {
    window.location.reload();
}

function markCompleted(payoutId) {
    const transactionId = prompt('Enter transaction ID (optional):');
    
    updatePayoutStatus(payoutId, 'completed', transactionId);
}

function markFailed(payoutId) {
    const reason = prompt('Enter failure reason:');
    if (!reason) return;
    
    updatePayoutStatus(payoutId, 'failed', null, reason);
}

function retryPayout(payoutId) {
    if (confirm('Retry this payout? This will mark it as processing again.')) {
        updatePayoutStatus(payoutId, 'processing');
    }
}

function updatePayoutStatus(payoutId, status, transactionId = null, notes = null) {
    const data = {
        payout_id: payoutId,
        status: status
    };
    
    if (transactionId) data.transaction_id = transactionId;
    if (notes) data.notes = notes;
    
    fetch('<?= url('/admin/affiliate/update-payout-status') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'include',
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating payout status', 'error');
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-md tw-shadow-lg tw-z-50 tw-max-w-sm ${
        type === 'success' ? 'tw-bg-green-100 tw-text-green-800 tw-border tw-border-green-200' :
        type === 'error' ? 'tw-bg-red-100 tw-text-red-800 tw-border tw-border-red-200' :
        type === 'warning' ? 'tw-bg-yellow-100 tw-text-yellow-800 tw-border tw-border-yellow-200' :
        'tw-bg-blue-100 tw-text-blue-800 tw-border tw-border-blue-200'
    }`;
    
    notification.innerHTML = `
        <div class="tw-flex tw-items-center">
            <div class="tw-flex-shrink-0">
                <i data-feather="${
                    type === 'success' ? 'check-circle' :
                    type === 'error' ? 'x-circle' :
                    type === 'warning' ? 'alert-triangle' :
                    'info'
                }" class="tw-h-5 tw-w-5"></i>
            </div>
            <div class="tw-ml-3">
                <p class="tw-text-sm tw-font-medium">${message}</p>
            </div>
            <div class="tw-ml-auto tw-pl-3">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-4 tw-w-4"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    feather.replace();
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>
