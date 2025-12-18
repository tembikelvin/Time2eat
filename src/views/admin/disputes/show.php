<?php
/**
 * Admin Dispute Details View
 */

$currentPage = 'disputes';
?>

<div class="tw-max-w-6xl tw-mx-auto tw-p-6">
    <!-- Header -->
    <div class="tw-mb-8">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div class="tw-flex tw-items-center">
                <a href="/admin/disputes" class="tw-text-blue-600 hover:tw-text-blue-800 tw-mr-4">
                    <i data-feather="arrow-left" class="tw-h-5 tw-w-5"></i>
                </a>
                <div>
                    <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Dispute Details</h1>
                    <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                        Order #<?= e($dispute['order_number'] ?? 'N/A') ?> - <?= e($dispute['subject']) ?>
                    </p>
                </div>
            </div>
            <div class="tw-flex tw-space-x-3">
                <?php if (in_array($dispute['status'], ['open', 'investigating'])): ?>
                    <button onclick="updateDisputeStatus('investigating')" class="tw-bg-blue-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-blue-700">
                        <i data-feather="search" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                        Start Investigation
                    </button>
                    <button onclick="showResolveModal()" class="tw-bg-green-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-green-700">
                        <i data-feather="check" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                        Resolve
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
        <!-- Main Content -->
        <div class="lg:tw-col-span-2 tw-space-y-6">
            <!-- Dispute Information -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Dispute Information</h2>
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-mb-6">
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Type</p>
                        <p class="tw-font-medium">
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
                        </p>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Priority</p>
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
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Status</p>
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
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Created</p>
                        <p class="tw-font-medium"><?= date('M j, Y g:i A', strtotime($dispute['created_at'])) ?></p>
                    </div>
                </div>

                <div class="tw-mb-6">
                    <h3 class="tw-text-md tw-font-medium tw-text-gray-900 tw-mb-2">Issue Description</h3>
                    <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4">
                        <h4 class="tw-font-medium tw-text-gray-900 tw-mb-2"><?= e($dispute['subject']) ?></h4>
                        <p class="tw-text-sm tw-text-gray-700 tw-whitespace-pre-wrap"><?= e($dispute['description']) ?></p>
                    </div>
                </div>

                <?php if ($dispute['resolution']): ?>
                    <div class="tw-mb-6">
                        <h3 class="tw-text-md tw-font-medium tw-text-gray-900 tw-mb-2">Resolution</h3>
                        <div class="tw-bg-green-50 tw-rounded-lg tw-p-4">
                            <p class="tw-text-sm tw-text-gray-700 tw-whitespace-pre-wrap"><?= e($dispute['resolution']) ?></p>
                            <?php if ($dispute['resolved_at']): ?>
                                <p class="tw-text-xs tw-text-gray-500 tw-mt-2">
                                    Resolved on <?= date('M j, Y g:i A', strtotime($dispute['resolved_at'])) ?>
                                    <?php if ($dispute['resolved_by_name']): ?>
                                        by <?= e($dispute['resolved_by_name']) ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($dispute['compensation_amount'] && $dispute['compensation_amount'] > 0): ?>
                    <div class="tw-mb-6">
                        <h3 class="tw-text-md tw-font-medium tw-text-gray-900 tw-mb-2">Compensation</h3>
                        <div class="tw-bg-blue-50 tw-rounded-lg tw-p-4">
                            <p class="tw-text-sm tw-text-gray-700">
                                Compensation Amount: <span class="tw-font-medium"><?= number_format($dispute['compensation_amount'], 0) ?> XAF</span>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Details -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Related Order</h2>
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Order Number</p>
                        <p class="tw-font-medium"><?= e($dispute['order_number'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Order Amount</p>
                        <p class="tw-font-medium"><?= number_format($dispute['order_amount'] ?? 0, 0) ?> XAF</p>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Restaurant</p>
                        <p class="tw-font-medium"><?= e($dispute['restaurant_name'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Order Date</p>
                        <p class="tw-font-medium"><?= $dispute['order_date'] ? date('M j, Y', strtotime($dispute['order_date'])) : 'N/A' ?></p>
                    </div>
                </div>

                <div class="tw-mt-4">
                    <a href="/admin/orders/<?= $dispute['order_id'] ?>" class="tw-text-blue-600 hover:tw-text-blue-800 tw-text-sm tw-font-medium">
                        <i data-feather="external-link" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        View Full Order Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="tw-space-y-6">
            <!-- Customer Information -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Customer Information</h3>
                
                <div class="tw-space-y-3">
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Name</p>
                        <p class="tw-font-medium"><?= e($dispute['initiator_name'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-600">Email</p>
                        <p class="tw-font-medium"><?= e($dispute['initiator_email'] ?? 'N/A') ?></p>
                    </div>
                    <?php if ($dispute['initiator_phone']): ?>
                        <div>
                            <p class="tw-text-sm tw-text-gray-600">Phone</p>
                            <p class="tw-font-medium"><?= e($dispute['initiator_phone']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tw-mt-4 tw-pt-4 tw-border-t tw-border-gray-200">
                    <button onclick="contactCustomer('<?= e($dispute['initiator_email']) ?>')" class="tw-w-full tw-bg-blue-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-blue-700">
                        <i data-feather="mail" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                        Contact Customer
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Quick Actions</h3>
                
                <div class="tw-space-y-3">
                    <?php if (in_array($dispute['status'], ['open', 'investigating'])): ?>
                        <button onclick="updateDisputeStatus('investigating')" class="tw-w-full tw-bg-blue-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-blue-700">
                            <i data-feather="search" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                            Start Investigation
                        </button>
                        <button onclick="updateDisputeStatus('escalated')" class="tw-w-full tw-bg-red-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-red-700">
                            <i data-feather="alert-triangle" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                            Escalate
                        </button>
                        <button onclick="showResolveModal()" class="tw-w-full tw-bg-green-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-green-700">
                            <i data-feather="check" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                            Resolve Dispute
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($dispute['status'] === 'resolved'): ?>
                        <button onclick="updateDisputeStatus('closed')" class="tw-w-full tw-bg-gray-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-gray-700">
                            <i data-feather="archive" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                            Close Dispute
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resolve Dispute Modal -->
<div id="resolveModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 tw-max-w-2xl tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Resolve Dispute</h3>
            <button onclick="closeResolveModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-6 tw-w-6"></i>
            </button>
        </div>
        
        <form id="resolveForm">
            <div class="tw-mb-4">
                <label for="resolution" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Resolution Details *
                </label>
                <textarea 
                    id="resolution" 
                    name="resolution" 
                    required 
                    rows="4"
                    placeholder="Describe how this dispute was resolved..."
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-border-green-500"
                ></textarea>
            </div>
            
            <div class="tw-mb-6">
                <label for="compensation" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Compensation Amount (XAF)
                </label>
                <input 
                    type="number" 
                    id="compensation" 
                    name="compensation_amount" 
                    min="0"
                    step="100"
                    placeholder="0"
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-border-green-500"
                >
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Optional compensation amount for the customer</p>
            </div>
            
            <div class="tw-flex tw-justify-end tw-space-x-4">
                <button type="button" onclick="closeResolveModal()" class="tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="tw-px-4 tw-py-2 tw-bg-green-600 tw-text-white tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-green-700">
                    Resolve Dispute
                </button>
            </div>
        </form>
    </div>
</div>

<script>
async function updateDisputeStatus(status) {
    try {
        const response = await fetch(`/admin/disputes/<?= $dispute['id'] ?>/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: status })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Dispute status updated successfully');
            location.reload();
        } else {
            alert(result.message || 'Failed to update dispute status');
        }
    } catch (error) {
        console.error('Error updating dispute status:', error);
        alert('An error occurred while updating the dispute status');
    }
}

function showResolveModal() {
    document.getElementById('resolveModal').classList.remove('tw-hidden');
}

function closeResolveModal() {
    document.getElementById('resolveModal').classList.add('tw-hidden');
    document.getElementById('resolveForm').reset();
}

document.getElementById('resolveForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        status: 'resolved',
        resolution: formData.get('resolution'),
        compensation_amount: formData.get('compensation_amount') || 0
    };
    
    try {
        const response = await fetch(`/admin/disputes/<?= $dispute['id'] ?>/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Dispute resolved successfully');
            location.reload();
        } else {
            alert(result.message || 'Failed to resolve dispute');
        }
    } catch (error) {
        console.error('Error resolving dispute:', error);
        alert('An error occurred while resolving the dispute');
    }
});

function contactCustomer(email) {
    window.location.href = `mailto:${email}`;
}

// Close modal when clicking outside
document.getElementById('resolveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeResolveModal();
    }
});

// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
