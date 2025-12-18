<?php
$title = $title ?? 'Reviews & Ratings - Time2Eat';
$currentPage = $currentPage ?? 'reviews';
$user = $user ?? null;
$reviews = $reviews ?? [];
$reviewStats = $reviewStats ?? ['average_rating' => 0, 'total_reviews' => 0, 'distribution' => []];
$reviewTrends = $reviewTrends ?? ['this_week' => ['rating' => 0, 'change' => 0], 'this_month' => ['rating' => 0], 'response_rate' => 0];
$totalReviews = $totalReviews ?? 0;
$currentRating = $currentRating ?? '';
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Reviews & Ratings</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Monitor customer feedback and improve your service.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <select id="ratingFilter" onchange="filterReviews(this.value)" class="tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm tw-focus:ring-orange-500 tw-focus:border-orange-500">
                <option value="">All Ratings</option>
                <option value="5" <?= $currentRating === '5' ? 'selected' : '' ?>>5 Stars</option>
                <option value="4" <?= $currentRating === '4' ? 'selected' : '' ?>>4 Stars</option>
                <option value="3" <?= $currentRating === '3' ? 'selected' : '' ?>>3 Stars</option>
                <option value="2" <?= $currentRating === '2' ? 'selected' : '' ?>>2 Stars</option>
                <option value="1" <?= $currentRating === '1' ? 'selected' : '' ?>>1 Star</option>
            </select>
            <button type="button" onclick="exportReviews()" 
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Export
            </button>
        </div>
    </div>
</div>

<!-- Rating Overview -->
<div class="tw-mb-8">
        <!-- Rating Overview -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8 tw-mb-8">
            <!-- Overall Rating -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-text-center">
                    <div class="tw-text-4xl tw-font-bold tw-text-gray-900 tw-mb-2"><?= number_format($reviewStats['average_rating'], 1) ?></div>
                    <div class="tw-flex tw-justify-center tw-mb-2">
                        <?php 
                        $rating = $reviewStats['average_rating'];
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                        <i data-feather="star" class="tw-h-5 tw-w-5 <?= $i <= $rating ? 'tw-text-yellow-400 tw-fill-current' : 'tw-text-gray-300' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="tw-text-sm tw-text-gray-600">Based on <?= $reviewStats['total_reviews'] ?> reviews</p>
                </div>
            </div>
            
            <!-- Rating Distribution -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Rating Distribution</h3>
                <div class="tw-space-y-3">
                    <?php 
                    $distribution = $reviewStats['distribution'];
                    $total = array_sum($distribution);
                    ?>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <span class="tw-text-sm tw-font-medium tw-text-gray-700 tw-w-8"><?= $i ?> ★</span>
                        <div class="tw-flex-1 tw-bg-gray-200 tw-rounded-full tw-h-2">
                            <div class="tw-bg-yellow-400 tw-h-2 tw-rounded-full" 
                                 style="width: <?= $total > 0 ? (($distribution[$i] ?? 0) / $total) * 100 : 0 ?>%"></div>
                        </div>
                        <span class="tw-text-sm tw-text-gray-600 tw-w-8"><?= $distribution[$i] ?? 0 ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Recent Trends -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Recent Trends</h3>
                <div class="tw-space-y-4">
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <span class="tw-text-sm tw-text-gray-600">This Week</span>
                        <div class="tw-flex tw-items-center">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mr-2"><?= number_format($reviewTrends['this_week']['rating'], 1) ?></span>
                            <?php $change = $reviewTrends['this_week']['change']; ?>
                            <?php if ($change > 0): ?>
                                <span class="tw-text-xs tw-text-green-600">+<?= number_format($change, 1) ?></span>
                            <?php elseif ($change < 0): ?>
                                <span class="tw-text-xs tw-text-red-600"><?= number_format($change, 1) ?></span>
                            <?php else: ?>
                                <span class="tw-text-xs tw-text-gray-600">0</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <span class="tw-text-sm tw-text-gray-600">This Month</span>
                        <div class="tw-flex tw-items-center">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mr-2"><?= number_format($reviewTrends['this_month']['rating'], 1) ?></span>
                            <span class="tw-text-xs tw-text-gray-600"><?= $reviewTrends['this_month']['count'] ?? 0 ?> reviews</span>
                        </div>
                    </div>
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <span class="tw-text-sm tw-text-gray-600">Response Rate</span>
                        <span class="tw-text-sm tw-font-medium tw-text-gray-900"><?= $reviewTrends['response_rate'] ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900">Customer Reviews</h2>
            </div>
            
            <div class="tw-divide-y tw-divide-gray-200">
                <?php if (!empty($reviews['list'])): ?>
                    <?php foreach ($reviews['list'] as $review): ?>
                    <div class="tw-p-6">
                        <div class="tw-flex tw-items-start tw-space-x-4">
                            <div class="tw-flex-shrink-0">
                                <div class="tw-h-10 tw-w-10 tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">
                                        <?= strtoupper(substr($review['customer_name'] ?? 'C', 0, 1)) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="tw-flex-1">
                                <div class="tw-flex tw-items-center tw-justify-between">
                                    <div>
                                        <h4 class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($review['customer_name'] ?? 'Anonymous') ?></h4>
                                        <div class="tw-flex tw-items-center tw-mt-1">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i data-feather="star" class="tw-h-4 tw-w-4 <?= $i <= ($review['rating'] ?? 5) ? 'tw-text-yellow-400 tw-fill-current' : 'tw-text-gray-300' ?>"></i>
                                            <?php endfor; ?>
                                            <span class="tw-ml-2 tw-text-sm tw-text-gray-600">
                                                <?= date('M j, Y', strtotime($review['created_at'] ?? 'now')) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="tw-flex tw-space-x-2">
                                        <?php if (empty($review['response'])): ?>
                                        <button type="button" onclick="respondToReview(<?= $review['id'] ?? 0 ?>)" 
                                                class="tw-text-orange-600 hover:tw-text-orange-500 tw-text-sm">
                                            <i data-feather="message-circle" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" onclick="reportReview(<?= $review['id'] ?? 0 ?>)" 
                                                class="tw-text-gray-400 hover:tw-text-gray-600 tw-text-sm">
                                            <i data-feather="flag" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <?php if (!empty($review['order_items'])): ?>
                                <p class="tw-text-sm tw-text-gray-500 tw-mt-2">
                                    Order: <?= e($review['order_items']) ?>
                                </p>
                                <?php endif; ?>
                                
                                <p class="tw-text-sm tw-text-gray-700 tw-mt-2"><?= e($review['comment'] ?? '') ?></p>
                                
                                <?php if (!empty($review['response'])): ?>
                                <div class="tw-mt-4 tw-p-3 tw-bg-orange-50 tw-rounded-lg tw-border-l-4 tw-border-orange-400">
                                    <div class="tw-flex tw-items-center tw-mb-1">
                                        <i data-feather="user" class="tw-h-4 tw-w-4 tw-text-orange-600 tw-mr-2"></i>
                                        <span class="tw-text-sm tw-font-medium tw-text-orange-800">Your Response</span>
                                        <span class="tw-ml-2 tw-text-xs tw-text-orange-600">
                                            <?= date('M j, Y', strtotime($review['response_date'] ?? 'now')) ?>
                                        </span>
                                    </div>
                                    <p class="tw-text-sm tw-text-orange-700"><?= e($review['response']) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="tw-p-12 tw-text-center">
                    <i data-feather="message-square" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-4"></i>
                    <h3 class="tw-text-sm tw-font-medium tw-text-gray-900">No reviews yet</h3>
                    <p class="tw-text-sm tw-text-gray-500 tw-mt-1">Customer reviews will appear here once you start receiving orders.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div id="responseModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 tw-max-w-md tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Respond to Review</h3>
                <button type="button" onclick="closeResponseModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <form id="responseForm">
                <input type="hidden" id="reviewId" name="review_id">
                
                <div class="tw-mb-4">
                    <label for="responseText" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Your Response</label>
                    <textarea id="responseText" name="response" rows="4" required
                              class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500"
                              placeholder="Thank you for your feedback..."></textarea>
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Be professional and courteous in your response.</p>
                </div>
                
                <div class="tw-flex tw-justify-end tw-space-x-3">
                    <button type="button" onclick="closeResponseModal()" 
                            class="tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                        Send Response
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Filter reviews by rating
document.getElementById('ratingFilter').addEventListener('change', function() {
    const rating = this.value;
    const url = new URL(window.location);
    if (rating) {
        url.searchParams.set('rating', rating);
    } else {
        url.searchParams.delete('rating');
    }
    window.location = url;
});

// Export reviews
function exportReviews() {
    const rating = document.getElementById('ratingFilter').value;
    const params = rating ? `?rating=${rating}` : '';
    window.open(`<?= url('/vendor/reviews/export') ?>${params}`, '_blank');
}

// Respond to review
function respondToReview(reviewId) {
    document.getElementById('reviewId').value = reviewId;
    document.getElementById('responseModal').classList.remove('tw-hidden');
}

// Close response modal
function closeResponseModal() {
    document.getElementById('responseModal').classList.add('tw-hidden');
    document.getElementById('responseForm').reset();
}

// Report review
function reportReview(reviewId) {
    if (confirm('Are you sure you want to report this review as inappropriate?')) {
        fetch(`<?= url('/vendor/reviews') ?>/${reviewId}/report`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Review reported successfully. Our team will review it.');
            } else {
                alert('Error: ' + (data.message || 'Failed to report review'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while reporting the review');
        });
    }
}

// Form submission
document.getElementById('responseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/vendor/reviews/respond') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeResponseModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to send response'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending the response');
    });
});

// Close modal when clicking outside
document.getElementById('responseModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeResponseModal();
    }
});
</script>
