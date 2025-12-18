<?php
$title = $title ?? 'Withdrawal Management - Time2Eat';
$withdrawals = $withdrawals ?? [];
$stats = $stats ?? [];
$filters = $filters ?? [];
?>

<!-- Withdrawal Management Page -->
<div class="tw-p-6 tw-space-y-6">
    <!-- Header -->
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Withdrawal Management</h1>
            <p class="tw-text-gray-600 tw-mt-1">Manage withdrawal requests from affiliates, riders, and restaurants</p>
        </div>
        <div class="tw-flex tw-gap-3">
            <button onclick="exportWithdrawals()" class="tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg hover:tw-bg-gray-200 tw-transition-colors tw-flex tw-items-center tw-gap-2">
                <i data-feather="download" class="tw-h-4 tw-w-4"></i>
                Export
            </button>
            <button onclick="refreshStats()" class="tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-gap-2">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4"></i>
                Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6">
        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-p-6 tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Pending</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-orange-600"><?= $stats['total_pending'] ?? 0 ?></p>
                    <p class="tw-text-xs tw-text-gray-500"><?= number_format($stats['total_amount_pending'] ?? 0) ?> XAF</p>
                </div>
                <div class="tw-w-12 tw-h-12 tw-bg-orange-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-p-6 tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Approved</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-green-600"><?= $stats['total_approved'] ?? 0 ?></p>
                    <p class="tw-text-xs tw-text-gray-500"><?= number_format($stats['total_amount_approved'] ?? 0) ?> XAF</p>
                </div>
                <div class="tw-w-12 tw-h-12 tw-bg-green-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-p-6 tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Rejected</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-red-600"><?= $stats['total_rejected'] ?? 0 ?></p>
                    <p class="tw-text-xs tw-text-gray-500"><?= number_format($stats['total_amount_rejected'] ?? 0) ?> XAF</p>
                </div>
                <div class="tw-w-12 tw-h-12 tw-bg-red-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="x-circle" class="tw-h-6 tw-w-6 tw-text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-p-6 tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Amount</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-blue-600"><?= number_format(($stats['total_amount_approved'] ?? 0) + ($stats['total_amount_pending'] ?? 0)) ?></p>
                    <p class="tw-text-xs tw-text-gray-500">XAF processed</p>
                </div>
                <div class="tw-w-12 tw-h-12 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-p-6 tw-border tw-border-gray-200">
        <form method="GET" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Status</label>
                <select name="status" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    <option value="">All Status</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Type</label>
                <select name="withdrawal_type" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    <option value="">All Types</option>
                    <option value="affiliate" <?= ($filters['withdrawal_type'] ?? '') === 'affiliate' ? 'selected' : '' ?>>Affiliate</option>
                    <option value="rider" <?= ($filters['withdrawal_type'] ?? '') === 'rider' ? 'selected' : '' ?>>Rider</option>
                    <option value="restaurant" <?= ($filters['withdrawal_type'] ?? '') === 'restaurant' ? 'selected' : '' ?>>Restaurant</option>
                </select>
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">From Date</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">To Date</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
            </div>

            <div class="tw-flex tw-items-end">
                <button type="submit" class="tw-w-full tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg hover:tw-bg-blue-700 tw-transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Withdrawals Table -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Withdrawal Requests</h3>
        </div>

        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-divide-y tw-divide-gray-200">
                <thead class="tw-bg-gray-50">
                    <tr>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">User</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Type</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Payment Method</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                        <th class="tw-px-6 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                    <?php if (empty($withdrawals)): ?>
                    <tr>
                        <td colspan="7" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                            <div class="tw-flex tw-flex-col tw-items-center">
                                <i data-feather="inbox" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-4"></i>
                                <p class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No withdrawals found</p>
                                <p class="tw-text-gray-500">No withdrawal requests match your current filters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($withdrawals as $withdrawal): ?>
                    <tr class="hover:tw-bg-gray-50">
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <div class="tw-flex tw-items-center">
                                <div class="tw-flex-shrink-0 tw-h-10 tw-w-10">
                                    <div class="tw-h-10 tw-w-10 tw-rounded-full tw-bg-gray-300 tw-flex tw-items-center tw-justify-center">
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-700">
                                            <?= strtoupper(substr($withdrawal['first_name'], 0, 1) . substr($withdrawal['last_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="tw-ml-4">
                                    <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                        <?= htmlspecialchars($withdrawal['first_name'] . ' ' . $withdrawal['last_name']) ?>
                                    </div>
                                    <div class="tw-text-sm tw-text-gray-500"><?= htmlspecialchars($withdrawal['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <span class="tw-inline-flex tw-px-2 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full tw-bg-blue-100 tw-text-blue-800">
                                <?= ucfirst($withdrawal['withdrawal_type']) ?>
                            </span>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                <?= number_format($withdrawal['amount'], 2) ?> XAF
                            </div>
                            <div class="tw-text-xs tw-text-gray-500"><?= htmlspecialchars($withdrawal['withdrawal_reference']) ?></div>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                            <?= htmlspecialchars($withdrawal['payment_method']) ?>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <?php
                            $statusColors = [
                                'pending' => 'tw-bg-orange-100 tw-text-orange-800',
                                'approved' => 'tw-bg-green-100 tw-text-green-800',
                                'rejected' => 'tw-bg-red-100 tw-text-red-800',
                                'processing' => 'tw-bg-blue-100 tw-text-blue-800'
                            ];
                            $statusColor = $statusColors[$withdrawal['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                            ?>
                            <span class="tw-inline-flex tw-px-2 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full <?= $statusColor ?>">
                                <?= ucfirst($withdrawal['status']) ?>
                            </span>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                            <?= date('M j, Y', strtotime($withdrawal['created_at'])) ?>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-text-sm tw-font-medium">
                            <div class="tw-flex tw-justify-end tw-gap-2">
                                <button onclick="viewWithdrawal(<?= $withdrawal['id'] ?>)" class="tw-text-blue-600 hover:tw-text-blue-900 tw-transition-colors" title="View Details">
                                    <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <?php if ($withdrawal['status'] === 'pending'): ?>
                                <button onclick="processWithdrawal(<?= $withdrawal['id'] ?>, 'approve')" class="tw-text-green-600 hover:tw-text-green-900 tw-transition-colors" title="Approve">
                                    <i data-feather="check" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <button onclick="processWithdrawal(<?= $withdrawal['id'] ?>, 'reject')" class="tw-text-red-600 hover:tw-text-red-900 tw-transition-colors" title="Reject">
                                    <i data-feather="x" class="tw-h-4 tw-w-4"></i>
                                </button>
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
</div>

<!-- Withdrawal Details Modal -->
<div id="withdrawalModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 md:tw-w-2/3 tw-shadow-lg tw-rounded-2xl tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Withdrawal Details</h3>
            <button onclick="closeWithdrawalModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-6 tw-w-6"></i>
            </button>
        </div>
        <div id="withdrawalDetails" class="tw-space-y-4">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

<!-- Process Withdrawal Modal -->
<div id="processModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 md:tw-w-96 tw-shadow-lg tw-rounded-2xl tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900" id="processModalTitle">Process Withdrawal</h3>
            <button onclick="closeProcessModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-6 tw-w-6"></i>
            </button>
        </div>
        <form id="processForm" class="tw-space-y-4">
            <input type="hidden" id="processWithdrawalId" name="withdrawal_id">
            <input type="hidden" id="processAction" name="action">
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Reason/Notes</label>
                <textarea id="processReason" name="reason" rows="3" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" placeholder="Enter reason for approval/rejection..."></textarea>
            </div>
            
            <div class="tw-flex tw-space-x-3 tw-pt-4">
                <button type="button" onclick="closeProcessModal()" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-font-medium hover:tw-bg-gray-200 tw-transition-colors">
                    Cancel
                </button>
                <button type="submit" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// View withdrawal details
function viewWithdrawal(withdrawalId) {
    fetch(`<?= url('/admin/withdrawals/details') ?>?id=${withdrawalId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayWithdrawalDetails(data.withdrawal);
                document.getElementById('withdrawalModal').classList.remove('tw-hidden');
            } else {
                alert('Failed to load withdrawal details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load withdrawal details');
        });
}

// Display withdrawal details
function displayWithdrawalDetails(withdrawal) {
    const detailsDiv = document.getElementById('withdrawalDetails');
    const accountDetails = JSON.parse(withdrawal.account_details || '{}');
    
    detailsDiv.innerHTML = `
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
            <div>
                <h4 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-3">User Information</h4>
                <div class="tw-space-y-2">
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-sm tw-text-gray-600">Name:</span>
                        <span class="tw-text-sm tw-font-medium">${withdrawal.first_name} ${withdrawal.last_name}</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-sm tw-text-gray-600">Email:</span>
                        <span class="tw-text-sm tw-font-medium">${withdrawal.email}</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-sm tw-text-gray-600">Phone:</span>
                        <span class="tw-text-sm tw-font-medium">${withdrawal.phone || 'N/A'}</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-3">Withdrawal Information</h4>
                <div class="tw-space-y-2">
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-sm tw-text-gray-600">Type:</span>
                        <span class="tw-text-sm tw-font-medium">${withdrawal.withdrawal_type.charAt(0).toUpperCase() + withdrawal.withdrawal_type.slice(1)}</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-sm tw-text-gray-600">Amount:</span>
                        <span class="tw-text-sm tw-font-medium">${parseFloat(withdrawal.amount).toLocaleString()} XAF</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-sm tw-text-gray-600">Payment Method:</span>
                        <span class="tw-text-sm tw-font-medium">${withdrawal.payment_method}</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-sm tw-text-gray-600">Status:</span>
                        <span class="tw-text-sm tw-font-medium">${withdrawal.status.charAt(0).toUpperCase() + withdrawal.status.slice(1)}</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-sm tw-text-gray-600">Reference:</span>
                        <span class="tw-text-sm tw-font-medium">${withdrawal.withdrawal_reference}</span>
                    </div>
                </div>
            </div>
        </div>
        
        ${Object.keys(accountDetails).length > 0 ? `
            <div class="tw-mt-6">
                <h4 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-3">Account Details</h4>
                <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                    ${Object.entries(accountDetails).map(([key, value]) => `
                        <div class="tw-flex tw-justify-between tw-mb-2">
                            <span class="tw-text-sm tw-text-gray-600">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</span>
                            <span class="tw-text-sm tw-font-medium">${value}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        ` : ''}
        
        <div class="tw-mt-6">
            <h4 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-3">Timeline</h4>
            <div class="tw-space-y-2">
                <div class="tw-flex tw-justify-between">
                    <span class="tw-text-sm tw-text-gray-600">Created:</span>
                    <span class="tw-text-sm tw-font-medium">${new Date(withdrawal.created_at).toLocaleString()}</span>
                </div>
                ${withdrawal.processed_at ? `
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-sm tw-text-gray-600">Processed:</span>
                        <span class="tw-text-sm tw-font-medium">${new Date(withdrawal.processed_at).toLocaleString()}</span>
                    </div>
                ` : ''}
                ${withdrawal.admin_notes ? `
                    <div class="tw-mt-3">
                        <span class="tw-text-sm tw-text-gray-600">Admin Notes:</span>
                        <p class="tw-text-sm tw-text-gray-900 tw-mt-1">${withdrawal.admin_notes}</p>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
}

// Process withdrawal
function processWithdrawal(withdrawalId, action) {
    document.getElementById('processWithdrawalId').value = withdrawalId;
    document.getElementById('processAction').value = action;
    document.getElementById('processModalTitle').textContent = action === 'approve' ? 'Approve Withdrawal' : 'Reject Withdrawal';
    document.getElementById('processForm').querySelector('button[type="submit"]').textContent = action === 'approve' ? 'Approve' : 'Reject';
    document.getElementById('processForm').querySelector('button[type="submit"]').className = action === 'approve' 
        ? 'tw-flex-1 tw-px-4 tw-py-2 tw-bg-green-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-green-700 tw-transition-colors'
        : 'tw-flex-1 tw-px-4 tw-py-2 tw-bg-red-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-red-700 tw-transition-colors';
    
    document.getElementById('processModal').classList.remove('tw-hidden');
}

// Close modals
function closeWithdrawalModal() {
    document.getElementById('withdrawalModal').classList.add('tw-hidden');
}

function closeProcessModal() {
    document.getElementById('processModal').classList.add('tw-hidden');
    document.getElementById('processForm').reset();
}

// Handle process form submission
document.getElementById('processForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('<?= url('/admin/withdrawals/process') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeProcessModal();
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to process withdrawal');
    });
});

// Export withdrawals
function exportWithdrawals() {
    const params = new URLSearchParams(window.location.search);
    window.open('<?= url('/admin/withdrawals/export') ?>?' + params.toString(), '_blank');
}

// Refresh stats
function refreshStats() {
    location.reload();
}

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
