<?php
/**
 * Admin Affiliate Withdrawals Management Page
 * Enhanced withdrawal management with comprehensive features
 */

// Set current page for sidebar highlighting
$currentPage = 'affiliate-withdrawals';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Affiliate Withdrawals</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage affiliate withdrawal requests and payout processing
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button onclick="exportWithdrawals()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Export Data
            </button>
            <button onclick="processBulkPayouts()" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                <i data-feather="credit-card" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Process Payouts
            </button>
        </div>
    </div>
</div>

<!-- Withdrawal Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Requests</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_requests'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">All time</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="file-text" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Pending</p>
                <p class="tw-text-3xl tw-font-bold tw-text-orange-600"><?= number_format($stats['pending_requests'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500"><?= number_format($stats['pending_amount'] ?? 0) ?> XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Approved</p>
                <p class="tw-text-3xl tw-font-bold tw-text-green-600"><?= number_format($stats['approved_requests'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500"><?= number_format($stats['approved_amount'] ?? 0) ?> XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Amount</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_amount'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500">XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-5 tw-gap-4">
        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Status</label>
            <select id="statusFilter" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-primary-500">
                <option value="all" <?= ($filters['status'] ?? '') === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Search</label>
            <input type="text" id="searchFilter" placeholder="Search affiliates..." 
                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-primary-500">
        </div>
        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">From Date</label>
            <input type="date" id="dateFromFilter" 
                   value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-primary-500">
        </div>
        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">To Date</label>
            <input type="date" id="dateToFilter" 
                   value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-primary-500">
        </div>
        <div class="tw-flex tw-items-end">
            <button onclick="applyFilters()" class="tw-w-full tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                <i data-feather="filter" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Apply Filters
            </button>
        </div>
    </div>
</div>

<!-- Withdrawals Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Withdrawal Requests</h3>
    </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        <input type="checkbox" id="selectAll" class="tw-rounded tw-border-gray-300">
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Affiliate</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Amount</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Payment Method</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Requested</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (empty($withdrawals)): ?>
                <tr>
                    <td colspan="7" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                        <i data-feather="inbox" class="tw-h-12 tw-w-12 tw-mx-auto tw-mb-4 tw-text-gray-400"></i>
                        <p class="tw-text-lg tw-font-medium">No withdrawal requests found</p>
                        <p class="tw-text-sm">Withdrawal requests will appear here when affiliates request payouts.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($withdrawals as $withdrawal): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <input type="checkbox" class="withdrawal-checkbox tw-rounded tw-border-gray-300" value="<?= $withdrawal['id'] ?>">
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-flex-shrink-0">
                                <div class="tw-h-10 tw-w-10 tw-rounded-full tw-bg-primary-100 tw-flex tw-items-center tw-justify-center">
                                    <span class="tw-text-sm tw-font-medium tw-text-primary-600">
                                        <?= strtoupper(substr($withdrawal['first_name'], 0, 1) . substr($withdrawal['last_name'], 0, 1)) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="tw-ml-4">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                    <?= htmlspecialchars($withdrawal['first_name'] . ' ' . $withdrawal['last_name']) ?>
                                </div>
                                <div class="tw-text-sm tw-text-gray-500"><?= htmlspecialchars($withdrawal['email']) ?></div>
                                <div class="tw-text-xs tw-text-gray-400">Code: <?= htmlspecialchars($withdrawal['affiliate_code']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= number_format($withdrawal['amount']) ?> XAF</div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-text-gray-900"><?= ucfirst(str_replace('_', ' ', $withdrawal['payment_method'])) ?></div>
                        <?php if ($withdrawal['payment_details']): ?>
                        <div class="tw-text-xs tw-text-gray-500">
                            <?php 
                            $details = json_decode($withdrawal['payment_details'], true);
                            if ($details && isset($details['phone'])) {
                                echo htmlspecialchars($details['phone']);
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <?php
                        $statusClasses = [
                            'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
                            'approved' => 'tw-bg-green-100 tw-text-green-800',
                            'rejected' => 'tw-bg-red-100 tw-text-red-800',
                            'processing' => 'tw-bg-blue-100 tw-text-blue-800'
                        ];
                        $statusClass = $statusClasses[$withdrawal['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                        ?>
                        <span class="tw-inline-flex tw-px-2 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full <?= $statusClass ?>">
                            <?= ucfirst($withdrawal['status']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= date('M j, Y', strtotime($withdrawal['requested_at'])) ?>
                        <div class="tw-text-xs tw-text-gray-400"><?= date('H:i', strtotime($withdrawal['requested_at'])) ?></div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <?php if ($withdrawal['status'] === 'pending'): ?>
                        <div class="tw-flex tw-space-x-2">
                            <button onclick="approveWithdrawal(<?= $withdrawal['id'] ?>)" 
                                    class="tw-text-green-600 hover:tw-text-green-900 tw-text-xs tw-bg-green-100 tw-px-2 tw-py-1 tw-rounded">
                                <i data-feather="check" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>
                                Approve
                            </button>
                            <button onclick="rejectWithdrawal(<?= $withdrawal['id'] ?>)" 
                                    class="tw-text-red-600 hover:tw-text-red-900 tw-text-xs tw-bg-red-100 tw-px-2 tw-py-1 tw-rounded">
                                <i data-feather="x" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>
                                Reject
                            </button>
                        </div>
                        <?php else: ?>
                        <span class="tw-text-gray-400 tw-text-xs">
                            <?= $withdrawal['status'] === 'approved' ? 'Approved' : 'Rejected' ?>
                            <?php if ($withdrawal['processed_at']): ?>
                            <div class="tw-text-xs"><?= date('M j, Y', strtotime($withdrawal['processed_at'])) ?></div>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Approval/Rejection Modal -->
<div id="actionModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 id="modalTitle" class="tw-text-lg tw-font-medium tw-text-gray-900"></h3>
                <button onclick="closeModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>

            <div id="modalContent">
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Notes/Reason</label>
                    <textarea id="actionNotes" rows="3"
                              class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-primary-500"
                              placeholder="Enter notes or reason for this action..."></textarea>
                </div>
            </div>

            <div class="tw-flex tw-justify-end tw-space-x-3">
                <button onclick="closeModal()"
                        class="tw-px-4 tw-py-2 tw-bg-gray-300 tw-text-gray-700 tw-rounded-md hover:tw-bg-gray-400">
                    Cancel
                </button>
                <button id="confirmAction"
                        class="tw-px-4 tw-py-2 tw-bg-primary-600 tw-text-white tw-rounded-md hover:tw-bg-primary-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    feather.replace();

    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.withdrawal-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

let currentAction = null;
let currentWithdrawalId = null;

function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchFilter').value;
    const dateFrom = document.getElementById('dateFromFilter').value;
    const dateTo = document.getElementById('dateToFilter').value;

    const params = new URLSearchParams();
    if (status !== 'all') params.append('status', status);
    if (search) params.append('search', search);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);

    window.location.href = '<?= url('/admin/affiliate/withdrawals') ?>' + (params.toString() ? '?' + params.toString() : '');
}

function approveWithdrawal(withdrawalId) {
    currentAction = 'approve';
    currentWithdrawalId = withdrawalId;

    document.getElementById('modalTitle').textContent = 'Approve Withdrawal';
    document.getElementById('actionNotes').placeholder = 'Enter approval notes (optional)...';
    document.getElementById('confirmAction').textContent = 'Approve';
    document.getElementById('confirmAction').className = 'tw-px-4 tw-py-2 tw-bg-green-600 tw-text-white tw-rounded-md hover:tw-bg-green-700';

    document.getElementById('actionModal').classList.remove('tw-hidden');
}

function rejectWithdrawal(withdrawalId) {
    currentAction = 'reject';
    currentWithdrawalId = withdrawalId;

    document.getElementById('modalTitle').textContent = 'Reject Withdrawal';
    document.getElementById('actionNotes').placeholder = 'Enter rejection reason...';
    document.getElementById('confirmAction').textContent = 'Reject';
    document.getElementById('confirmAction').className = 'tw-px-4 tw-py-2 tw-bg-red-600 tw-text-white tw-rounded-md hover:tw-bg-red-700';

    document.getElementById('actionModal').classList.remove('tw-hidden');
}

function closeModal() {
    document.getElementById('actionModal').classList.add('tw-hidden');
    document.getElementById('actionNotes').value = '';
    currentAction = null;
    currentWithdrawalId = null;
}

document.getElementById('confirmAction').addEventListener('click', function() {
    if (!currentAction || !currentWithdrawalId) return;

    const notes = document.getElementById('actionNotes').value;
    const endpoint = currentAction === 'approve'
        ? '<?= url('/admin/affiliate/approve-withdrawal') ?>'
        : '<?= url('/admin/affiliate/reject-withdrawal') ?>';

    const data = {
        withdrawal_id: currentWithdrawalId,
        [currentAction === 'approve' ? 'admin_notes' : 'reason']: notes
    };

    // Show loading state
    this.disabled = true;
    this.textContent = 'Processing...';

    fetch(endpoint, {
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
        showNotification('An error occurred while processing the request', 'error');
    })
    .finally(() => {
        closeModal();
    });
});

function exportWithdrawals() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchFilter').value;
    const dateFrom = document.getElementById('dateFromFilter').value;
    const dateTo = document.getElementById('dateToFilter').value;

    const params = new URLSearchParams();
    params.append('export', 'withdrawals');
    if (status !== 'all') params.append('status', status);
    if (search) params.append('search', search);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);

    window.open('<?= url('/admin/tools/export') ?>?' + params.toString(), '_blank');
}

function processBulkPayouts() {
    const selectedWithdrawals = Array.from(document.querySelectorAll('.withdrawal-checkbox:checked')).map(cb => cb.value);

    if (selectedWithdrawals.length === 0) {
        showNotification('Please select withdrawals to process', 'warning');
        return;
    }

    if (confirm(`Process payouts for ${selectedWithdrawals.length} selected withdrawals?`)) {
        fetch('<?= url('/admin/affiliate/process-payouts') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'include',
            body: JSON.stringify({
                withdrawal_ids: selectedWithdrawals
            })
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
            showNotification('An error occurred while processing payouts', 'error');
        });
    }
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
