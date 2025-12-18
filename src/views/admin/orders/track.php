<?php
/**
 * Admin Order Tracking View
 * Shows order tracking and status progression
 */

// Set current page for sidebar highlighting
$currentPage = 'orders';

// Define order status flow
$statusFlow = [
    'pending' => ['label' => 'Pending', 'icon' => 'clock', 'color' => 'yellow'],
    'confirmed' => ['label' => 'Confirmed', 'icon' => 'check-circle', 'color' => 'blue'],
    'preparing' => ['label' => 'Preparing', 'icon' => 'shopping-bag', 'color' => 'orange'],
    'ready' => ['label' => 'Ready', 'icon' => 'package', 'color' => 'purple'],
    'picked_up' => ['label' => 'Picked Up', 'icon' => 'truck', 'color' => 'indigo'],
    'on_the_way' => ['label' => 'On The Way', 'icon' => 'navigation', 'color' => 'blue'],
    'delivered' => ['label' => 'Delivered', 'icon' => 'check', 'color' => 'green'],
];

$currentStatus = $order['status'] ?? 'pending';
$currentStatusIndex = array_search($currentStatus, array_keys($statusFlow));
?>

<!-- Enhanced Page Header -->
<div class="tw-mb-8 tw-px-2 sm:tw-px-0">
    <div class="tw-bg-gradient-to-r tw-from-green-600 tw-to-emerald-600 tw-rounded-2xl tw-p-6 tw-shadow-xl">
        <div class="tw-flex tw-flex-col lg:tw-flex-row tw-items-start lg:tw-items-center tw-justify-between tw-space-y-4 lg:tw-space-y-0">
            <div class="tw-flex-1 tw-min-w-0">
                <div class="tw-flex tw-items-center tw-space-x-3 tw-mb-3">
                    <a href="<?= url('/admin/orders') ?>" class="tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-p-2 tw-rounded-lg tw-transition-all tw-duration-200">
                    <i data-feather="arrow-left" class="tw-h-5 tw-w-5"></i>
                </a>
                    <div class="tw-min-w-0 tw-flex-1">
                        <h1 class="tw-text-2xl lg:tw-text-3xl tw-font-bold tw-text-white tw-truncate">
                            Track Order #<?= e($order['order_number'] ?? $order['id']) ?>
                        </h1>
                        <p class="tw-text-indigo-100 tw-text-sm tw-mt-1">
                Real-time order tracking and status updates
            </p>
        </div>
                </div>
                
                <!-- Order Status Badge -->
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <span class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-semibold tw-shadow-lg tw-bg-white/20 tw-text-white">
                        <div class="tw-h-2 tw-w-2 tw-rounded-full tw-bg-white tw-mr-2 tw-animate-pulse"></div>
                        <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                    </span>
                    
                    <!-- Order Value Badge -->
                    <span class="tw-bg-white/20 tw-text-white tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium">
                        <?= number_format($order['total_amount'] ?? 0) ?> XAF
                    </span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-space-y-2 sm:tw-space-y-0 sm:tw-space-x-3">
                <a href="<?= url('/admin/orders/' . $order['id']) ?>" 
                   class="tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-space-x-2 tw-shadow-lg">
                    <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                    <span>View Details</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content - Mobile Optimized -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-4 sm:tw-gap-6 lg:tw-gap-8 tw-px-2 sm:tw-px-0">
<!-- Map Section -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden tw-mb-6">
        <div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-indigo-50 tw-px-4 sm:tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                    <i data-feather="map" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                </div>
                <h2 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-gray-900">Live Map</h2>
            </div>
        </div>
        <div id="trackingMap" class="tw-h-[400px] tw-w-full tw-bg-gray-100"></div>
    </div>

    <!-- Enhanced Tracking Timeline -->
    <div class="tw-space-y-6 lg:tw-col-span-2">
        <!-- Mobile-First Horizontal Order Progress Section -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden">
            <div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-indigo-50 tw-px-4 sm:tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                            <i data-feather="activity" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                        </div>
                        <h2 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-gray-900">Order Progress</h2>
                    </div>
                    <div class="tw-text-right">
                        <div class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-blue-600"><?= $currentStatusIndex !== false ? round((($currentStatusIndex + 1) / count($statusFlow)) * 100) : 0 ?>%</div>
                        <div class="tw-text-xs sm:tw-text-sm tw-text-gray-500">Complete</div>
                    </div>
                </div>
            </div>
            
            <div class="tw-p-4 sm:tw-p-6">
            <?php if ($currentStatus !== 'cancelled'): ?>
                    <!-- Mobile-First Horizontal Progress Bar -->
                    <div class="tw-relative tw-mb-6">
                        <!-- Progress Line Background -->
                        <div class="tw-absolute tw-top-4 tw-left-0 tw-right-0 tw-h-1 tw-bg-gray-200 tw-rounded-full"></div>
                        <!-- Progress Line Fill -->
                        <div class="tw-absolute tw-top-4 tw-left-0 tw-h-1 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-transition-all tw-duration-1000 tw-ease-out" 
                             style="width: <?= $currentStatusIndex !== false ? (($currentStatusIndex + 1) / count($statusFlow)) * 100 : 0 ?>%"></div>
                        
                        <!-- Status Points - Horizontal Layout -->
                        <div class="tw-relative tw-flex tw-justify-between tw-items-center">
                        <?php foreach ($statusFlow as $statusKey => $statusData): 
                            $statusIndex = array_search($statusKey, array_keys($statusFlow));
                            $isCompleted = $currentStatusIndex !== false && $statusIndex <= $currentStatusIndex;
                            $isCurrent = $statusKey === $currentStatus;
                                $isNext = $statusIndex === $currentStatusIndex + 1;
                            ?>
                                <div class="tw-flex tw-flex-col tw-items-center tw-relative tw-min-w-0" style="flex: 1;">
                                    <!-- Status Circle -->
                                    <div class="tw-relative tw-mb-2">
                                        <div class="tw-h-8 tw-w-8 sm:tw-h-10 sm:tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-transition-all tw-duration-500 tw-shadow-lg tw-z-10
                                            <?= $isCompleted ? 'tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-text-white tw-scale-110' : 'tw-bg-white tw-border-2 tw-border-gray-200 tw-text-gray-400' ?>
                                            <?= $isCurrent ? 'tw-ring-4 tw-ring-blue-200 tw-animate-pulse' : '' ?>
                                            <?= $isNext ? 'tw-bg-blue-50 tw-border-2 tw-border-blue-200 tw-text-blue-600' : '' ?>">
                                            <i data-feather="<?= $statusData['icon'] ?>" class="tw-h-4 tw-w-4 sm:tw-h-5 sm:tw-w-5"></i>
                                        </div>
                                        <?php if ($isCompleted): ?>
                                            <div class="tw-absolute -tw-top-1 -tw-right-1 tw-h-4 tw-w-4 sm:tw-h-5 sm:tw-w-5 tw-bg-green-500 tw-rounded-full tw-border-2 tw-border-white tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                                                <i data-feather="check" class="tw-h-2 tw-w-2 sm:tw-h-3 sm:tw-w-3 tw-text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                </div>
                                    
                                    <!-- Status Label -->
                                    <div class="tw-text-center tw-min-w-0 tw-px-1">
                                        <div class="tw-text-xs sm:tw-text-sm tw-font-bold tw-text-center tw-leading-tight <?= $isCompleted ? 'tw-text-blue-600' : ($isCurrent ? 'tw-text-blue-600' : 'tw-text-gray-500') ?>">
                                    <?= $statusData['label'] ?>
                                        </div>
                                        <?php if ($isCurrent): ?>
                                            <div class="tw-text-xs tw-text-blue-500 tw-mt-1 tw-animate-pulse">Current</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Current Status Card -->
                    <div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-purple-50 tw-rounded-xl tw-p-4 tw-border tw-border-blue-200">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                                <i data-feather="<?= $statusFlow[$currentStatus]['icon'] ?? 'activity' ?>" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <h3 class="tw-text-sm sm:tw-text-base tw-font-bold tw-text-gray-900">Current Status</h3>
                                <p class="tw-text-sm tw-text-blue-700 tw-font-medium"><?= ucfirst(str_replace('_', ' ', $currentStatus)) ?></p>
                            </div>
                            <div class="tw-text-right">
                                <div class="tw-h-2 tw-w-2 tw-bg-blue-500 tw-rounded-full tw-animate-ping"></div>
                            </div>
                        </div>
                </div>
            <?php else: ?>
                    <!-- Enhanced Cancelled Status -->
                <div class="tw-text-center tw-py-8">
                        <div class="tw-h-16 tw-w-16 sm:tw-h-20 sm:tw-w-20 tw-rounded-full tw-bg-gradient-to-r tw-from-red-100 tw-to-pink-100 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-shadow-lg">
                            <i data-feather="x-circle" class="tw-h-8 tw-w-8 sm:tw-h-10 sm:tw-w-10 tw-text-red-600"></i>
                        </div>
                        <h3 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-red-900 tw-mb-2">Order Cancelled</h3>
                        <?php if (!empty($order['cancellation_reason'])): ?>
                            <div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-p-3 tw-max-w-sm tw-mx-auto">
                                <p class="tw-text-red-800 tw-font-medium tw-text-sm"><?= e($order['cancellation_reason']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
            </div>
        </div>

        <!-- Enhanced Status History -->
        <?php if (!empty($statusHistory)): ?>
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-purple-50 tw-to-pink-50 tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-p-2 tw-bg-purple-100 tw-rounded-lg">
                            <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
                        </div>
                        <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">Status History</h2>
                    </div>
                </div>
                <div class="tw-p-6">
                    <div class="tw-space-y-6">
                        <?php foreach ($statusHistory as $index => $history): ?>
                            <div class="tw-flex tw-items-start tw-space-x-4">
                                <div class="tw-flex-shrink-0">
                                    <div class="tw-h-10 tw-w-10 tw-rounded-full tw-bg-gradient-to-r tw-from-blue-100 tw-to-purple-100 tw-flex tw-items-center tw-justify-center tw-shadow-md">
                                        <i data-feather="<?= $statusFlow[$history['status']]['icon'] ?? 'activity' ?>" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                                        <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-2">
                                            <h3 class="tw-text-sm tw-font-bold tw-text-gray-900">
                                                Status changed to: 
                                            </h3>
                                            <span class="tw-bg-blue-100 tw-text-blue-800 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium">
                                                <?= ucfirst(str_replace('_', ' ', $history['status'])) ?>
                                            </span>
                                        </div>
                                        <div class="tw-flex tw-items-center tw-space-x-3 tw-mb-2">
                                        <?php if (!empty($history['changed_by_name'])): ?>
                                                <div class="tw-flex tw-items-center tw-space-x-1">
                                                    <i data-feather="user" class="tw-h-3 tw-w-3 tw-text-gray-500"></i>
                                                    <span class="tw-text-xs tw-text-gray-600">By: <?= e($history['changed_by_name']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="tw-flex tw-items-center tw-space-x-1">
                                                <i data-feather="clock" class="tw-h-3 tw-w-3 tw-text-gray-500"></i>
                                                <span class="tw-text-xs tw-text-gray-600">
                                                    <?= date('M j, Y g:i A', strtotime($history['created_at'])) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if (!empty($history['notes'])): ?>
                                            <div class="tw-mt-2 tw-p-2 tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg">
                                                <div class="tw-flex tw-items-start tw-space-x-2">
                                                    <i data-feather="message-square" class="tw-h-3 tw-w-3 tw-text-yellow-600 tw-mt-0.5 tw-flex-shrink-0"></i>
                                                    <p class="tw-text-xs tw-text-yellow-800"><?= e($history['notes']) ?></p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    </div>
                </div>
            <?php endif; ?>

        <!-- Enhanced Status Update Form -->
            <?php if ($currentStatus !== 'cancelled' && $currentStatus !== 'delivered'): ?>
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-gray-50 tw-to-slate-50 tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-p-2 tw-bg-gray-100 tw-rounded-lg">
                            <i data-feather="edit" class="tw-h-5 tw-w-5 tw-text-gray-600"></i>
                        </div>
                        <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">Update Order Status</h2>
                    </div>
                </div>
                <div class="tw-p-6">
                    <form id="statusUpdateForm" class="tw-space-y-6">
                        <div>
                            <label for="status" class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-3">Select New Status</label>
                            <select id="status" name="status" class="tw-block tw-w-full tw-rounded-xl tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500 tw-px-4 tw-py-3 tw-text-sm tw-font-medium">
                                <?php foreach ($statusFlow as $statusKey => $statusData): 
                                    if ($statusKey !== 'cancelled'): ?>
                                        <option value="<?= $statusKey ?>" <?= $statusKey === $currentStatus ? 'selected' : '' ?>>
                                            <?= $statusData['label'] ?>
                                        </option>
                                    <?php endif;
                                endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="notes" class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-3">Additional Notes (Optional)</label>
                            <textarea id="notes" name="notes" rows="4" class="tw-block tw-w-full tw-rounded-xl tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500 tw-px-4 tw-py-3 tw-text-sm" placeholder="Add any notes about this status change..."></textarea>
                        </div>
                        <button type="submit" class="tw-w-full tw-bg-gradient-to-r tw-from-blue-600 tw-to-purple-600 tw-text-white tw-rounded-xl tw-px-6 tw-py-3 tw-text-sm tw-font-bold hover:tw-from-blue-700 hover:tw-to-purple-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-blue-500 tw-transition-all tw-duration-200 tw-shadow-lg hover:tw-shadow-xl">
                            <div class="tw-flex tw-items-center tw-justify-center tw-space-x-2">
                                <i data-feather="check-circle" class="tw-h-4 tw-w-4"></i>
                                <span>Update Status</span>
                            </div>
                        </button>
                    </form>
                </div>
                </div>
            <?php endif; ?>
    </div>

    <!-- Enhanced Order Summary Sidebar -->
    <div class="tw-space-y-4 sm:tw-space-y-6 lg:tw-col-span-1">
        <!-- Order Summary Card -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden">
            <div class="tw-bg-gradient-to-r tw-from-orange-50 tw-to-red-50 tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-orange-100 tw-rounded-lg">
                        <i data-feather="package" class="tw-h-5 tw-w-5 tw-text-orange-600"></i>
            </div>
                    <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">Order Summary</h2>
                </div>
            </div>
            <div class="tw-p-6">
                <div class="tw-space-y-4">
                    <!-- Order Number -->
                    <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <div class="tw-p-2 tw-bg-gray-100 tw-rounded-lg">
                                <i data-feather="hash" class="tw-h-4 tw-w-4 tw-text-gray-600"></i>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <p class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wide">Order Number</p>
                                <p class="tw-text-sm tw-font-bold tw-text-gray-900 tw-truncate">#<?= e($order['order_number'] ?? $order['id']) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Amount -->
                    <div class="tw-bg-gradient-to-r tw-from-green-50 tw-to-emerald-50 tw-rounded-xl tw-p-4 tw-border tw-border-green-200">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg">
                                <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <p class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wide">Total Amount</p>
                                <p class="tw-text-lg tw-font-bold tw-text-green-700"><?= number_format($order['total_amount'] ?? 0) ?> XAF</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Status -->
                    <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <div class="tw-p-2 tw-bg-gray-100 tw-rounded-lg">
                                <i data-feather="credit-card" class="tw-h-4 tw-w-4 tw-text-gray-600"></i>
                </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <p class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wide">Payment Status</p>
                                <div class="tw-flex tw-items-center tw-space-x-2 tw-mt-1">
                                    <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-xs tw-font-bold
                        <?= ($order['payment_status'] ?? '') === 'paid' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-yellow-100 tw-text-yellow-800' ?>">
                                        <div class="tw-h-2 tw-w-2 tw-rounded-full tw-bg-current tw-mr-2"></div>
                        <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                    </span>
                </div>
                </div>
            </div>
        </div>

                    <!-- Payment Method -->
                    <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <div class="tw-p-2 tw-bg-gray-100 tw-rounded-lg">
                                <i data-feather="smartphone" class="tw-h-4 tw-w-4 tw-text-gray-600"></i>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <p class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wide">Payment Method</p>
                                <p class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= ucfirst($order['payment_method'] ?? 'N/A') ?></p>
            </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Timeline Card -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden">
            <div class="tw-bg-gradient-to-r tw-from-purple-50 tw-to-pink-50 tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-purple-100 tw-rounded-lg">
                        <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
            </div>
                    <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">Order Timeline</h2>
                </div>
            </div>
            <div class="tw-p-6">
                <div class="tw-space-y-4">
                    <!-- Order Created -->
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-h-8 tw-w-8 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                            <i data-feather="plus-circle" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                        </div>
                        <div class="tw-flex-1 tw-min-w-0">
                            <p class="tw-text-sm tw-font-bold tw-text-gray-900">Order Created</p>
                            <p class="tw-text-xs tw-text-gray-600"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($order['updated_at']) && $order['updated_at'] !== $order['created_at']): ?>
                        <!-- Last Updated -->
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <div class="tw-h-8 tw-w-8 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                                <i data-feather="edit" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <p class="tw-text-sm tw-font-bold tw-text-gray-900">Last Updated</p>
                                <p class="tw-text-xs tw-text-gray-600"><?= date('M j, Y g:i A', strtotime($order['updated_at'])) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                    <!-- Current Status -->
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-h-8 tw-w-8 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-flex-shrink-0 tw-animate-pulse">
                            <i data-feather="activity" class="tw-h-4 tw-w-4 tw-text-orange-600"></i>
                        </div>
                        <div class="tw-flex-1 tw-min-w-0">
                            <p class="tw-text-sm tw-font-bold tw-text-gray-900">Current Status</p>
                            <p class="tw-text-xs tw-text-orange-600 tw-font-medium tw-animate-pulse"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize map
document.addEventListener('DOMContentLoaded', async function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Initialize MapProvider
    const map = new MapProvider({
        container: 'trackingMap',
        provider: window.MAP_CONFIG.provider,
        apiKey: window.MAP_CONFIG.apiKey,
        center: [5.9631, 10.1591], // Default to Bamenda
        zoom: 13
    });

    await map.init();

    // Add markers
    const restaurantLat = <?= $order['restaurant_latitude'] ?? 0 ?>;
    const restaurantLng = <?= $order['restaurant_longitude'] ?? 0 ?>;
    const deliveryLat = <?= $order['delivery_latitude'] ?? 0 ?>;
    const deliveryLng = <?= $order['delivery_longitude'] ?? 0 ?>;
    
    const bounds = [];

    if (restaurantLat && restaurantLng) {
        map.addMarker({
            lat: restaurantLat,
            lng: restaurantLng,
            title: "<?= e($order['restaurant_name'] ?? 'Restaurant') ?>",
            iconType: 'restaurant',
            infoWindowContent: `
                <div class="tw-p-2">
                    <h4 class="tw-font-bold"><?= e($order['restaurant_name'] ?? 'Restaurant') ?></h4>
                    <p class="tw-text-xs">Pickup Location</p>
                </div>
            `
        });
        bounds.push([restaurantLat, restaurantLng]);
    }

    if (deliveryLat && deliveryLng) {
        map.addMarker({
            lat: deliveryLat,
            lng: deliveryLng,
            title: "<?= e($order['customer_name'] ?? 'Customer') ?>",
            iconType: 'customer',
            infoWindowContent: `
                <div class="tw-p-2">
                    <h4 class="tw-font-bold"><?= e($order['customer_name'] ?? 'Customer') ?></h4>
                    <p class="tw-text-xs">Delivery Location</p>
                </div>
            `
        });
        bounds.push([deliveryLat, deliveryLng]);
    }

    // Draw route if both points exist
    if (bounds.length === 2) {
        map.drawPolyline(bounds, {
            color: 'blue',
            weight: 4
        });
        map.fitBounds(bounds);
    } else if (bounds.length === 1) {
        map.map.setView(bounds[0], 15);
    }
});

// Handle status update form submission
document.getElementById('statusUpdateForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const status = document.getElementById('status').value;
    const notes = document.getElementById('notes').value;
    
    fetch('<?= url('/admin/orders/' . $order['id'] . '/status') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status, notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status.');
    });
});
</script>

