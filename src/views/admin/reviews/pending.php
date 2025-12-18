<?php
$currentPage = 'reviews';
?>

<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Pending Reviews</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">Review and moderate customer reviews awaiting approval</p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <a href="<?= url('/admin/reviews') ?>" class="tw-bg-white tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-flex tw-items-center">
                <i data-feather="arrow-left" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                All Reviews
            </a>
            <button onclick="exportPendingReviews()" class="tw-bg-white tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-flex tw-items-center">
                <i data-feather="download" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                Export
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-lg">
                <i data-feather="clock" class="tw-w-6 tw-h-6 tw-text-yellow-600"></i>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Pending Reviews</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['pending_reviews']) ?></p>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-lg">
                <i data-feather="trending-up" class="tw-w-6 tw-h-6 tw-text-blue-600"></i>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">This Week</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['reviews_this_week']) ?></p>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-lg">
                <i data-feather="check-circle" class="tw-w-6 tw-h-6 tw-text-green-600"></i>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Approved</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['approved_reviews']) ?></p>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-3 tw-bg-red-100 tw-rounded-lg">
                <i data-feather="x-circle" class="tw-w-6 tw-h-6 tw-text-red-600"></i>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Rejected</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['rejected_reviews']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4 tw-mb-6 tw-hidden" id="bulkActionsPanel">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center tw-space-x-4">
            <span class="tw-text-sm tw-text-gray-600" id="selectedCount">0 reviews selected</span>
            <select id="bulkAction" class="tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                <option value="">Select Action</option>
                <option value="approve">Approve Selected</option>
                <option value="reject">Reject Selected</option>
                <option value="hide">Hide Selected</option>
            </select>
            <button onclick="executeBulkAction()" class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                Execute
            </button>
        </div>
        <button onclick="clearSelection()" class="tw-text-gray-500 hover:tw-text-gray-700">
            <i data-feather="x" class="tw-w-4 tw-h-4"></i>
        </button>
    </div>
</div>

<!-- Pending Reviews Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="tw-rounded tw-border-gray-300 tw-text-blue-600 focus:tw-ring-blue-500">
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Review</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Customer</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Restaurant</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Rating</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                    <th class="tw-px-6 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <tr class="hover:tw-bg-gray-50">
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <input type="checkbox" class="review-checkbox tw-rounded tw-border-gray-300 tw-text-blue-600 focus:tw-ring-blue-500" value="<?= $review['id'] ?>" onchange="updateSelection()">
                            </td>
                            <td class="tw-px-6 tw-py-4">
                                <div class="tw-max-w-xs">
                                    <p class="tw-text-sm tw-text-gray-900 tw-truncate"><?= htmlspecialchars($review['comment'] ?: 'No comment') ?></p>
                                    <?php if ($review['order_number']): ?>
                                        <p class="tw-text-xs tw-text-gray-500">Order: <?= htmlspecialchars($review['order_number']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <div class="tw-w-8 tw-h-8 tw-bg-gray-200 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                        <span class="tw-text-xs tw-font-medium tw-text-gray-600">
                                            <?= strtoupper(substr($review['customer_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="tw-text-sm tw-font-medium tw-text-gray-900"><?= htmlspecialchars($review['customer_name']) ?></p>
                                        <p class="tw-text-xs tw-text-gray-500"><?= htmlspecialchars($review['customer_email']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <p class="tw-text-sm tw-text-gray-900"><?= htmlspecialchars($review['restaurant_name'] ?: 'Unknown') ?></p>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg class="tw-w-4 tw-h-4 <?= $i <= $review['rating'] ? 'tw-text-yellow-400' : 'tw-text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    <?php endfor; ?>
                                    <span class="tw-ml-1 tw-text-sm tw-text-gray-600"><?= $review['rating'] ?></span>
                                </div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                <?= date('M j, Y', strtotime($review['created_at'])) ?>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-text-sm tw-font-medium">
                                <div class="tw-flex tw-justify-end tw-space-x-2">
                                    <button onclick="viewReview(<?= $review['id'] ?>)" class="tw-text-blue-600 hover:tw-text-blue-900 tw-transition-colors" title="View Details">
                                        <i data-feather="eye" class="tw-w-4 tw-h-4"></i>
                                    </button>
                                    <button onclick="approveReview(<?= $review['id'] ?>)" class="tw-text-green-600 hover:tw-text-green-900 tw-transition-colors" title="Approve">
                                        <i data-feather="check" class="tw-w-4 tw-h-4"></i>
                                    </button>
                                    <button onclick="rejectReview(<?= $review['id'] ?>)" class="tw-text-red-600 hover:tw-text-red-900 tw-transition-colors" title="Reject">
                                        <i data-feather="x" class="tw-w-4 tw-h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                            <i data-feather="check-circle" class="tw-w-12 tw-h-12 tw-mx-auto tw-mb-4 tw-text-green-300"></i>
                            <p class="tw-text-lg tw-font-medium">No pending reviews</p>
                            <p class="tw-text-sm">All reviews have been processed!</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="tw-bg-white tw-px-4 tw-py-3 tw-border-t tw-border-gray-200 tw-sm:tw-px-6">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex-1 tw-flex tw-justify-between tw-sm:hidden">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="tw-ml-3 tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
                <div class="tw-hidden tw-sm:tw-flex-1 tw-sm:tw-flex tw-sm:tw-items-center tw-sm:tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-text-gray-700">
                            Showing <span class="tw-font-medium"><?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?></span>
                            to <span class="tw-font-medium"><?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?></span>
                            of <span class="tw-font-medium"><?= $pagination['total'] ?></span> results
                        </p>
                    </div>
                    <div>
                        <nav class="tw-relative tw-z-0 tw-inline-flex tw-rounded-md tw-shadow-sm -tw-space-x-px">
                            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                <a href="?page=<?= $i ?>" class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-text-sm tw-font-medium <?= $i === $pagination['current_page'] ? 'tw-bg-blue-50 tw-border-blue-500 tw-text-blue-600' : 'tw-bg-white tw-border-gray-300 tw-text-gray-500 hover:tw-bg-gray-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Review Details Modal -->
<div id="reviewModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 md:tw-w-2/3 tw-shadow-lg tw-rounded-2xl tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Review Details</h3>
            <button onclick="closeReviewModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-6 tw-w-6"></i>
            </button>
        </div>
        <div id="reviewDetails" class="tw-space-y-4">
            <!-- Review details will be loaded here -->
        </div>
    </div>
</div>

<!-- Action Modal -->
<div id="actionModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-2xl tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 id="actionModalTitle" class="tw-text-lg tw-font-semibold tw-text-gray-900">Action</h3>
            <button onclick="closeActionModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-6 tw-w-6"></i>
            </button>
        </div>
        <form id="actionForm" class="tw-space-y-4">
            <input type="hidden" id="actionReviewId" name="review_id">
            <input type="hidden" id="actionType" name="action">
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Admin Notes</label>
                <textarea id="actionNotes" name="admin_notes" rows="3" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" placeholder="Add notes about your decision..."></textarea>
            </div>
            
            <div id="rejectionReason" class="tw-hidden">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Rejection Reason</label>
                <select id="rejectionReasonSelect" name="reason" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    <option value="">Select reason</option>
                    <option value="inappropriate_content">Inappropriate Content</option>
                    <option value="fake_review">Fake Review</option>
                    <option value="spam">Spam</option>
                    <option value="off_topic">Off Topic</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="tw-flex tw-space-x-3 tw-pt-4">
                <button type="button" onclick="closeActionModal()" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-font-medium hover:tw-bg-gray-200 tw-transition-colors">
                    Cancel
                </button>
                <button type="submit" id="actionSubmitBtn" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let selectedReviews = new Set();
let currentReviewId = null;
let currentAction = null;

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.review-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        if (selectAll.checked) {
            selectedReviews.add(checkbox.value);
        } else {
            selectedReviews.delete(checkbox.value);
        }
    });
    
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    const selectAll = document.getElementById('selectAll');
    const bulkPanel = document.getElementById('bulkActionsPanel');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedReviews.clear();
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            selectedReviews.add(checkbox.value);
        }
    });
    
    const count = selectedReviews.size;
    selectedCount.textContent = `${count} review${count !== 1 ? 's' : ''} selected`;
    
    if (count > 0) {
        bulkPanel.classList.remove('tw-hidden');
    } else {
        bulkPanel.classList.add('tw-hidden');
    }
    
    selectAll.checked = count === checkboxes.length && count > 0;
    selectAll.indeterminate = count > 0 && count < checkboxes.length;
}

function clearSelection() {
    selectedReviews.clear();
    document.querySelectorAll('.review-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    document.getElementById('bulkActionsPanel').classList.add('tw-hidden');
}

function executeBulkAction() {
    const action = document.getElementById('bulkAction').value;
    if (!action || selectedReviews.size === 0) return;
    
    const reviewIds = Array.from(selectedReviews);
    const adminNotes = prompt('Admin notes (optional):') || '';
    
    fetch('<?= url('/admin/reviews/bulk-action') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: action,
            review_ids: reviewIds,
            admin_notes: adminNotes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            clearSelection();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function viewReview(reviewId) {
    fetch(`<?= url('/admin/reviews/') ?>${reviewId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayReviewDetails(data.review);
                document.getElementById('reviewModal').classList.remove('tw-hidden');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to load review details', 'error');
        });
}

function displayReviewDetails(review) {
    const detailsDiv = document.getElementById('reviewDetails');
    detailsDiv.innerHTML = `
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
            <div>
                <h4 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Customer Information</h4>
                <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                    <p class="tw-text-sm"><strong>Name:</strong> ${review.customer_name}</p>
                    <p class="tw-text-sm"><strong>Email:</strong> ${review.customer_email}</p>
                    <p class="tw-text-sm"><strong>Phone:</strong> ${review.customer_phone || 'N/A'}</p>
                </div>
            </div>
            
            <div>
                <h4 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Review Information</h4>
                <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                    <p class="tw-text-sm"><strong>Restaurant:</strong> ${review.restaurant_name}</p>
                    <p class="tw-text-sm"><strong>Rating:</strong> ${review.rating}/5 stars</p>
                    <p class="tw-text-sm"><strong>Status:</strong> <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full tw-bg-yellow-100 tw-text-yellow-800">Pending</span></p>
                    <p class="tw-text-sm"><strong>Date:</strong> ${new Date(review.created_at).toLocaleDateString()}</p>
                </div>
            </div>
        </div>
        
        <div>
            <h4 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Review Content</h4>
            <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                <p class="tw-text-sm tw-text-gray-700">${review.comment || 'No comment provided'}</p>
            </div>
        </div>
        
        ${review.order_number ? `
        <div>
            <h4 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Order Information</h4>
            <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                <p class="tw-text-sm"><strong>Order Number:</strong> ${review.order_number}</p>
                <p class="tw-text-sm"><strong>Order Date:</strong> ${new Date(review.order_date).toLocaleDateString()}</p>
                <p class="tw-text-sm"><strong>Order Total:</strong> ${review.total_amount} XAF</p>
            </div>
        </div>
        ` : ''}
    `;
}

function approveReview(reviewId) {
    currentReviewId = reviewId;
    currentAction = 'approve';
    document.getElementById('actionModalTitle').textContent = 'Approve Review';
    document.getElementById('actionType').value = 'approve';
    document.getElementById('actionSubmitBtn').textContent = 'Approve';
    document.getElementById('actionSubmitBtn').className = 'tw-flex-1 tw-px-4 tw-py-2 tw-bg-green-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-green-700 tw-transition-colors';
    document.getElementById('rejectionReason').classList.add('tw-hidden');
    document.getElementById('actionModal').classList.remove('tw-hidden');
}

function rejectReview(reviewId) {
    currentReviewId = reviewId;
    currentAction = 'reject';
    document.getElementById('actionModalTitle').textContent = 'Reject Review';
    document.getElementById('actionType').value = 'reject';
    document.getElementById('actionSubmitBtn').textContent = 'Reject';
    document.getElementById('actionSubmitBtn').className = 'tw-flex-1 tw-px-4 tw-py-2 tw-bg-red-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-red-700 tw-transition-colors';
    document.getElementById('rejectionReason').classList.remove('tw-hidden');
    document.getElementById('actionModal').classList.remove('tw-hidden');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('tw-hidden');
}

function closeActionModal() {
    document.getElementById('actionModal').classList.add('tw-hidden');
    document.getElementById('actionForm').reset();
    currentReviewId = null;
    currentAction = null;
}

function exportPendingReviews() {
    window.open('<?= url('/admin/reviews/export') ?>?status=pending', '_blank');
}

document.getElementById('actionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        admin_notes: formData.get('admin_notes'),
        reason: formData.get('reason')
    };
    
    fetch(`<?= url('/admin/reviews/') ?>${currentReviewId}/${currentAction}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeActionModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-lg tw-text-white tw-z-50 ${
        type === 'success' ? 'tw-bg-green-500' : 'tw-bg-red-500'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

feather.replace();
</script>
