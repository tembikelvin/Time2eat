<?php
/**
 * Admin Disputes Management Page - Enhanced Version
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'disputes';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Dispute Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Handle customer complaints and vendor disputes
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Export Report
            </button>
            <button class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Refresh
            </button>
        </div>
    </div>
</div>

<!-- Dispute Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Disputes</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_disputes'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">All time</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="alert-triangle" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Open Disputes</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['open_disputes'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-red-600">Needs attention</p>
            </div>
            <div class="tw-p-3 tw-bg-red-100 tw-rounded-full">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-red-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Resolved This Month</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['resolved_month'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">This month</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Resolution Time</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= $stats['avg_resolution_time'] ?? '0' ?></p>
                <p class="tw-text-sm tw-text-gray-500">days</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="trending-down" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Priority Disputes Alert -->
<?php if (($stats['urgent_disputes'] ?? 0) > 0): ?>
<div class="tw-bg-gradient-to-r tw-from-red-50 tw-to-orange-50 tw-border tw-border-red-200 tw-rounded-xl tw-p-6 tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-2 tw-bg-red-100 tw-rounded-lg">
                <i data-feather="alert-circle" class="tw-h-6 tw-w-6 tw-text-red-600"></i>
            </div>
            <div class="tw-ml-4">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Urgent Disputes</h3>
                <p class="tw-text-sm tw-text-gray-600"><?= $stats['urgent_disputes'] ?? 0 ?> disputes require immediate attention</p>
            </div>
        </div>
        <button onclick="showUrgentDisputes()" class="tw-bg-red-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium hover:tw-bg-red-700 tw-transition-colors">
            Review Now
        </button>
    </div>
</div>
<?php endif; ?>

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
                    <input type="text" id="dispute-search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-leading-5 tw-bg-white tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-1 focus:tw-ring-primary-500 focus:tw-border-primary-500" placeholder="Search disputes by ID, customer, or order...">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="tw-flex tw-space-x-4">
                <select id="status-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="investigating">Investigating</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
                
                <select id="priority-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Disputes Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">All Disputes</h2>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <span class="tw-text-sm tw-text-gray-500">Showing 1-20 of <?= number_format($totalDisputes ?? 247) ?> disputes</span>
                <div class="tw-flex tw-space-x-1">
                    <button class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600">
                        <i data-feather="chevron-left" class="tw-h-4 tw-w-4"></i>
                    </button>
                    <button class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600">
                        <i data-feather="chevron-right" class="tw-h-4 tw-w-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Dispute ID</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Order</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Customer</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Issue</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Priority</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Created</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="disputes-table-body">
                <?php if (!empty($disputes)): ?>
                    <?php foreach ($disputes as $dispute): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($dispute['id'] ?? 'N/A') ?></div>
                        <div class="tw-text-sm tw-text-gray-500">
                            <?php 
                            $createdDate = $dispute['created_at'] ?? $dispute['created'] ?? null;
                            echo $createdDate ? date('M j, H:i', strtotime($createdDate)) : 'N/A';
                            ?>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($dispute['order_id'] ?? 'N/A') ?></div>
                        <div class="tw-text-sm tw-text-gray-500"><?= e($dispute['restaurant_name'] ?? $dispute['restaurant'] ?? 'Unknown Restaurant') ?></div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <?php 
                            $customerName = $dispute['customer_name'] ?? $dispute['customer'] ?? 'Unknown Customer';
                            $customerEmail = $dispute['customer_email'] ?? 'No email';
                            ?>
                            <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-red-500 tw-to-orange-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-white tw-font-semibold tw-text-xs">
                                    <?= strtoupper(substr($customerName, 0, 1)) ?>
                                </span>
                            </div>
                            <div class="tw-ml-3">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($customerName) ?></div>
                                <div class="tw-text-sm tw-text-gray-500"><?= e($customerEmail) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($dispute['type'] ?? $dispute['issue'] ?? 'Unknown Issue') ?></div>
                        <div class="tw-text-sm tw-text-gray-500 tw-max-w-xs tw-truncate"><?= e($dispute['description'] ?? 'No description') ?></div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                            <?php 
                            $priority = $dispute['priority'] ?? 'unknown';
                            switch($priority) {
                                case 'urgent': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                case 'high': echo 'tw-bg-orange-100 tw-text-orange-800'; break;
                                case 'medium': echo 'tw-bg-yellow-100 tw-text-yellow-800'; break;
                                case 'low': echo 'tw-bg-green-100 tw-text-green-800'; break;
                                default: echo 'tw-bg-gray-100 tw-text-gray-800';
                            }
                            ?>">
                            <?= ucfirst($priority) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                            <?php 
                            $status = $dispute['status'] ?? 'unknown';
                            switch($status) {
                                case 'open': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                case 'investigating': echo 'tw-bg-blue-100 tw-text-blue-800'; break;
                                case 'in_progress': echo 'tw-bg-blue-100 tw-text-blue-800'; break;
                                case 'resolved': echo 'tw-bg-green-100 tw-text-green-800'; break;
                                case 'closed': echo 'tw-bg-gray-100 tw-text-gray-800'; break;
                                default: echo 'tw-bg-gray-100 tw-text-gray-800';
                            }
                            ?>">
                            <?= ucfirst($status) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?php 
                        $createdDate = $dispute['created_at'] ?? $dispute['created'] ?? null;
                        echo $createdDate ? date('M j, Y', strtotime($createdDate)) : 'N/A';
                        ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <?php 
                        $disputeId = $dispute['id'] ?? 'unknown';
                        $customerEmail = $dispute['customer_email'] ?? 'no-email@example.com';
                        $disputeStatus = $dispute['status'] ?? 'unknown';
                        ?>
                        <div class="tw-flex tw-space-x-2">
                            <button class="tw-text-primary-600 hover:tw-text-primary-900" onclick="viewDispute('<?= e($disputeId) ?>')" title="View Details">
                                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <?php if (in_array($disputeStatus, ['open', 'investigating', 'in_progress'])): ?>
                                <button class="tw-text-green-600 hover:tw-text-green-900" onclick="resolveDispute('<?= e($disputeId) ?>')" title="Resolve Dispute">
                                    <i data-feather="check" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <button class="tw-text-blue-600 hover:tw-text-blue-900" onclick="updateStatus('<?= e($disputeId) ?>')" title="Update Status">
                                    <i data-feather="edit" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                            <button class="tw-text-purple-600 hover:tw-text-purple-900" onclick="contactCustomer('<?= e($customerEmail) ?>')" title="Contact Customer">
                                <i data-feather="message-circle" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8" class="tw-px-6 tw-py-16 tw-text-center">
                        <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-min-h-[300px]">
                            <!-- Icon -->
                            <div class="tw-mb-6">
                                <div class="tw-w-20 tw-h-20 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto">
                                    <i data-feather="alert-triangle" class="tw-h-10 tw-w-10 tw-text-gray-400"></i>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="tw-text-center tw-max-w-md">
                                <h3 class="tw-text-xl tw-font-semibold tw-text-gray-900 tw-mb-3">No Disputes Found</h3>
                                <p class="tw-text-gray-500 tw-mb-8 tw-leading-relaxed">
                                    There are currently no disputes to display. All disputes will appear here when customers report issues.
                                </p>
                                
                                <!-- Action Buttons -->
                                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3 tw-justify-center">
                                    <button onclick="refreshDisputes()" 
                                            class="tw-bg-primary-600 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-flex tw-items-center tw-justify-center tw-shadow-sm">
                                        <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                        Refresh Page
                                    </button>
                                    <button onclick="window.location.reload()" 
                                            class="tw-bg-white tw-border tw-border-gray-300 tw-text-gray-700 tw-px-6 tw-py-3 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-50 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                        <i data-feather="external-link" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                        Reload
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="tw-bg-white tw-px-4 tw-py-3 tw-flex tw-items-center tw-justify-between tw-border-t tw-border-gray-200 tw-sm:tw-px-6">
        <div class="tw-flex-1 tw-flex tw-justify-between tw-sm:tw-hidden">
            <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                Previous
            </button>
            <button class="tw-ml-3 tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                Next
            </button>
        </div>
        <div class="tw-hidden tw-sm:tw-flex-1 tw-sm:tw-flex tw-sm:tw-items-center tw-sm:tw-justify-between">
            <div>
                <p class="tw-text-sm tw-text-gray-700">
                    Showing <span class="tw-font-medium">1</span> to <span class="tw-font-medium">20</span> of <span class="tw-font-medium"><?= number_format($totalDisputes ?? 247) ?></span> results
                </p>
            </div>
            <div>
                <nav class="tw-relative tw-z-0 tw-inline-flex tw-rounded-md tw-shadow-sm tw--space-x-px" aria-label="Pagination">
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-2 tw-py-2 tw-rounded-l-md tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-500 hover:tw-bg-gray-50">
                        <i data-feather="chevron-left" class="tw-h-5 tw-w-5"></i>
                    </button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">1</button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">2</button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">3</button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-2 tw-py-2 tw-rounded-r-md tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-500 hover:tw-bg-gray-50">
                        <i data-feather="chevron-right" class="tw-h-5 tw-w-5"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
// Dispute management functions
function viewDispute(disputeId) {
    window.location.href = `/admin/disputes/${disputeId}`;
}

function resolveDispute(disputeId) {
    const resolution = prompt('Please provide resolution details:');
    if (resolution) {
        fetch(`/admin/disputes/${disputeId}/resolve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ resolution: resolution })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error resolving dispute: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while resolving the dispute.');
        });
    }
}

function updateStatus(disputeId) {
    const status = prompt('Enter new status (open, investigating, resolved, closed):');
    if (status && ['open', 'investigating', 'resolved', 'closed'].includes(status)) {
        fetch(`/admin/disputes/${disputeId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    }
}

function contactCustomer(email) {
    window.location.href = `mailto:${email}?subject=Regarding your dispute with Time2Eat`;
}

function showUrgentDisputes() {
    document.getElementById('priority-filter').value = 'urgent';
    filterDisputes();
}

function refreshDisputes() {
    // Clear all filters
    document.getElementById('dispute-search').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('priority-filter').value = '';
    
    // Reload the page to get fresh data
    window.location.reload();
}

// Search and filter functionality
document.getElementById('dispute-search').addEventListener('input', function() {
    filterDisputes();
});

document.getElementById('status-filter').addEventListener('change', function() {
    filterDisputes();
});

document.getElementById('priority-filter').addEventListener('change', function() {
    filterDisputes();
});

function filterDisputes() {
    const search = document.getElementById('dispute-search').value;
    const status = document.getElementById('status-filter').value;
    const priority = document.getElementById('priority-filter').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (priority) params.append('priority', priority);
    
    // Update URL and reload content
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.history.pushState({}, '', newUrl);
    
    // In a real implementation, you would fetch filtered data via AJAX
    // For now, we'll just reload the page
    // location.reload();
}

function refreshDisputes() {
    window.location.reload();
}

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
