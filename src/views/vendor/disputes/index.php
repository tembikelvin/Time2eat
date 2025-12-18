<?php
/**
 * Vendor Disputes List
 */

$currentPage = $currentPage ?? 'disputes';
?>

<div class="tw-max-w-6xl tw-mx-auto tw-p-6">
    <!-- Header -->
    <div class="tw-mb-8">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Restaurant Disputes</h1>
                <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                    Disputes related to your restaurant orders
                </p>
            </div>
            <a href="<?= url('/vendor/orders') ?>" class="tw-bg-green-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-green-700">
                <i data-feather="arrow-left" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Back to Orders
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
        <?php
        $totalDisputes = count($disputes);
        $openDisputes = count(array_filter($disputes, fn($d) => in_array($d['status'], ['open', 'investigating'])));
        $resolvedDisputes = count(array_filter($disputes, fn($d) => $d['status'] === 'resolved'));
        $urgentDisputes = count(array_filter($disputes, fn($d) => $d['priority'] === 'urgent' && in_array($d['status'], ['open', 'investigating'])));
        ?>
        
        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Disputes</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= $totalDisputes ?></p>
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
                    <p class="tw-text-3xl tw-font-bold tw-text-orange-600"><?= $openDisputes ?></p>
                </div>
                <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                    <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Resolved</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-green-600"><?= $resolvedDisputes ?></p>
                </div>
                <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                    <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Urgent</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-red-600"><?= $urgentDisputes ?></p>
                </div>
                <div class="tw-p-3 tw-bg-red-100 tw-rounded-full">
                    <i data-feather="alert-circle" class="tw-h-6 tw-w-6 tw-text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($disputes)): ?>
        <!-- Empty State -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-12 tw-text-center">
            <div class="tw-mx-auto tw-w-24 tw-h-24 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                <i data-feather="message-circle" class="tw-h-12 tw-w-12 tw-text-gray-400"></i>
            </div>
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No Disputes Found</h3>
            <p class="tw-text-gray-600 tw-mb-6">
                Great news! There are no disputes related to your restaurant orders.
            </p>
            <a href="<?= url('/vendor/orders') ?>" class="tw-bg-green-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-green-700">
                View Your Orders
            </a>
        </div>
    <?php else: ?>
        <!-- Disputes List -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                    <thead class="tw-bg-gray-50">
                        <tr>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Order & Issue
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Customer
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Type
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Status
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Priority
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Date
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                        <?php foreach ($disputes as $dispute): ?>
                            <tr class="hover:tw-bg-gray-50">
                                <td class="tw-px-6 tw-py-4">
                                    <div>
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                            Order #<?= e($dispute['order_number'] ?? 'N/A') ?>
                                        </div>
                                        <div class="tw-text-sm tw-text-gray-600 tw-mt-1">
                                            <?= e($dispute['subject']) ?>
                                        </div>
                                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">
                                            Amount: <?= number_format($dispute['order_amount'] ?? 0, 0) ?> XAF
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4">
                                    <div>
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                            <?= e($dispute['initiator_name'] ?? 'N/A') ?>
                                        </div>
                                        <div class="tw-text-sm tw-text-gray-500">
                                            <?= e($dispute['initiator_email'] ?? 'N/A') ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-gray-100 tw-text-gray-800">
                                        <?php
                                        $typeLabels = [
                                            'order_issue' => 'Order Issue',
                                            'quality_issue' => 'Quality Issue',
                                            'delivery_issue' => 'Delivery Issue',
                                            'payment_issue' => 'Payment Issue',
                                            'other' => 'Other'
                                        ];
                                        echo $typeLabels[$dispute['type']] ?? ucfirst($dispute['type']);
                                        ?>
                                    </span>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <?php
                                    $statusColors = [
                                        'open' => 'tw-bg-yellow-100 tw-text-yellow-800',
                                        'investigating' => 'tw-bg-blue-100 tw-text-blue-800',
                                        'resolved' => 'tw-bg-green-100 tw-text-green-800',
                                        'closed' => 'tw-bg-gray-100 tw-text-gray-800',
                                        'escalated' => 'tw-bg-red-100 tw-text-red-800'
                                    ];
                                    $statusClass = $statusColors[$dispute['status']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                                    ?>
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium <?= $statusClass ?>">
                                        <?= ucfirst($dispute['status']) ?>
                                    </span>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <?php
                                    $priorityColors = [
                                        'low' => 'tw-bg-gray-100 tw-text-gray-800',
                                        'medium' => 'tw-bg-yellow-100 tw-text-yellow-800',
                                        'high' => 'tw-bg-orange-100 tw-text-orange-800',
                                        'urgent' => 'tw-bg-red-100 tw-text-red-800'
                                    ];
                                    $priorityClass = $priorityColors[$dispute['priority']] ?? 'tw-bg-gray-100 tw-text-gray-800';
                                    ?>
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium <?= $priorityClass ?>">
                                        <?= ucfirst($dispute['priority']) ?>
                                    </span>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                    <?= date('M j, Y', strtotime($dispute['created_at'])) ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                                    <button 
                                        onclick="viewDispute(<?= $dispute['id'] ?>)"
                                        class="tw-text-green-600 hover:tw-text-green-900 tw-mr-3"
                                        title="View Details"
                                    >
                                        <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                                    </button>
                                    <button 
                                        onclick="contactCustomer('<?= e($dispute['initiator_email']) ?>')"
                                        class="tw-text-blue-600 hover:tw-text-blue-900"
                                        title="Contact Customer"
                                    >
                                        <i data-feather="mail" class="tw-h-4 tw-w-4"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Dispute Details Modal -->
<div id="disputeModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 tw-max-w-4xl tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Dispute Details</h3>
            <button onclick="closeDisputeModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-6 tw-w-6"></i>
            </button>
        </div>
        <div id="disputeContent" class="tw-space-y-4">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
async function viewDispute(disputeId) {
    const modal = document.getElementById('disputeModal');
    const content = document.getElementById('disputeContent');
    
    // Show modal with loading state
    content.innerHTML = '<div class="tw-text-center tw-py-8"><i data-feather="loader" class="tw-h-8 tw-w-8 tw-animate-spin tw-mx-auto tw-text-gray-400"></i></div>';
    modal.classList.remove('tw-hidden');
    feather.replace();
    
    try {
        const response = await fetch(`<?= url('/vendor/disputes') ?>/${disputeId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const dispute = await response.json();
            displayDisputeDetails(dispute);
        } else {
            content.innerHTML = '<div class="tw-text-center tw-py-8 tw-text-red-600">Failed to load dispute details</div>';
        }
    } catch (error) {
        console.error('Error loading dispute:', error);
        content.innerHTML = '<div class="tw-text-center tw-py-8 tw-text-red-600">An error occurred while loading dispute details</div>';
    }
}

function displayDisputeDetails(dispute) {
    const content = document.getElementById('disputeContent');
    
    const statusColors = {
        'open': 'tw-bg-yellow-100 tw-text-yellow-800',
        'investigating': 'tw-bg-blue-100 tw-text-blue-800',
        'resolved': 'tw-bg-green-100 tw-text-green-800',
        'closed': 'tw-bg-gray-100 tw-text-gray-800',
        'escalated': 'tw-bg-red-100 tw-text-red-800'
    };
    
    const priorityColors = {
        'low': 'tw-bg-gray-100 tw-text-gray-800',
        'medium': 'tw-bg-yellow-100 tw-text-yellow-800',
        'high': 'tw-bg-orange-100 tw-text-orange-800',
        'urgent': 'tw-bg-red-100 tw-text-red-800'
    };
    
    content.innerHTML = `
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
            <div>
                <h4 class="tw-font-medium tw-text-gray-900 tw-mb-2">Order Information</h4>
                <div class="tw-space-y-2 tw-text-sm">
                    <div><span class="tw-text-gray-600">Order Number:</span> ${dispute.order_number || 'N/A'}</div>
                    <div><span class="tw-text-gray-600">Order Amount:</span> ${dispute.order_amount ? Number(dispute.order_amount).toLocaleString() + ' XAF' : 'N/A'}</div>
                    <div><span class="tw-text-gray-600">Customer:</span> ${dispute.initiator_name || 'N/A'}</div>
                    <div><span class="tw-text-gray-600">Customer Email:</span> ${dispute.initiator_email || 'N/A'}</div>
                </div>
            </div>
            <div>
                <h4 class="tw-font-medium tw-text-gray-900 tw-mb-2">Dispute Status</h4>
                <div class="tw-space-y-2">
                    <div class="tw-flex tw-items-center tw-space-x-2">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium ${statusColors[dispute.status] || 'tw-bg-gray-100 tw-text-gray-800'}">
                            ${dispute.status.charAt(0).toUpperCase() + dispute.status.slice(1)}
                        </span>
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium ${priorityColors[dispute.priority] || 'tw-bg-gray-100 tw-text-gray-800'}">
                            ${dispute.priority.charAt(0).toUpperCase() + dispute.priority.slice(1)} Priority
                        </span>
                    </div>
                    <div class="tw-text-sm tw-text-gray-600">
                        Created: ${new Date(dispute.created_at).toLocaleDateString()}
                    </div>
                </div>
            </div>
        </div>
        
        <div>
            <h4 class="tw-font-medium tw-text-gray-900 tw-mb-2">Issue Details</h4>
            <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4">
                <h5 class="tw-font-medium tw-text-gray-900 tw-mb-2">${dispute.subject}</h5>
                <p class="tw-text-sm tw-text-gray-700 tw-whitespace-pre-wrap">${dispute.description}</p>
            </div>
        </div>
        
        ${dispute.resolution ? `
            <div>
                <h4 class="tw-font-medium tw-text-gray-900 tw-mb-2">Resolution</h4>
                <div class="tw-bg-green-50 tw-rounded-lg tw-p-4">
                    <p class="tw-text-sm tw-text-gray-700 tw-whitespace-pre-wrap">${dispute.resolution}</p>
                    ${dispute.resolved_at ? `<p class="tw-text-xs tw-text-gray-500 tw-mt-2">Resolved on ${new Date(dispute.resolved_at).toLocaleDateString()}</p>` : ''}
                </div>
            </div>
        ` : ''}
        
        <div class="tw-flex tw-justify-end tw-space-x-3">
            <button onclick="contactCustomer('${dispute.initiator_email}')" class="tw-bg-green-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-green-700">
                <i data-feather="mail" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Contact Customer
            </button>
        </div>
    `;
    
    feather.replace();
}

function closeDisputeModal() {
    document.getElementById('disputeModal').classList.add('tw-hidden');
}

function contactCustomer(email) {
    window.location.href = `mailto:${email}`;
}

// Close modal when clicking outside
document.getElementById('disputeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDisputeModal();
    }
});

// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
