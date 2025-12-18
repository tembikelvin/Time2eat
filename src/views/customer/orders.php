<?php
/**
 * Customer Orders Page - Mobile-First Redesign
 * Shows order history with rider communication and tracking
 */

$user = $user ?? null;
$orders = $orders ?? [];
$totalOrders = $totalOrders ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
?>

<!-- Page Header -->
<div class="tw-mb-6">
    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">My Orders</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Track and manage your food orders
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-3">
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-gray-100 tw-text-gray-700">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <?= $totalOrders ?> Orders
            </span>
            <button onclick="refreshOrders()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-flex tw-items-center">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="tw-mb-6">
    <div class="tw-border-b tw-border-gray-200">
        <nav class="tw--mb-px tw-flex tw-space-x-8 tw-overflow-x-auto" aria-label="Tabs">
            <button class="filter-tab tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-orange-500 tw-font-medium tw-text-sm tw-text-orange-600" data-status="all">
                All Orders
            </button>
            <button class="filter-tab tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" data-status="pending">
                Pending
            </button>
            <button class="filter-tab tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" data-status="confirmed">
                Confirmed
            </button>
            <button class="filter-tab tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" data-status="preparing">
                Preparing
            </button>
            <button class="filter-tab tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" data-status="picked_up">
                Picked Up
            </button>
            <button class="filter-tab tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" data-status="delivered">
                Delivered
            </button>
            <button class="filter-tab tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" data-status="cancelled">
                Cancelled
            </button>
        </nav>
    </div>
</div>

<!-- Orders List -->
<div class="tw-space-y-4" id="orders-container">
    <?php if (empty($orders)): ?>
        <!-- Empty State -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-12 tw-text-center">
            <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                <svg class="tw-w-8 tw-h-8 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No Orders Yet</h3>
            <p class="tw-text-sm tw-text-gray-500 tw-mb-6">Start ordering from your favorite restaurants!</p>
            <a href="<?= url('/') ?>" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-orange-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-orange-700 tw-transition-colors">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Browse Restaurants
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order):
            $statusColor = match(strtolower($order['status'])) {
                'pending' => 'yellow',
                'confirmed' => 'blue',
                'preparing' => 'purple',
                'ready' => 'orange',
                'assigned' => 'teal',
                'picked_up', 'on_the_way' => 'indigo',
                'delivered' => 'green',
                'cancelled' => 'red',
                default => 'gray'
            };
            $hasRider = !empty($order['rider_id']);
        ?>
            <div class="order-card tw-bg-white tw-rounded-xl tw-shadow-md tw-overflow-hidden tw-border tw-border-gray-200 hover:tw-shadow-lg tw-transition-shadow" data-status="<?= strtolower($order['status']) ?>" data-order-id="<?= $order['id'] ?>">
                <!-- Order Header -->
                <div class="tw-p-6 tw-bg-gradient-to-r tw-from-gray-50 tw-to-white">
                    <div class="tw-flex tw-items-start tw-justify-between tw-gap-6">
                        <div class="tw-flex tw-items-start tw-space-x-5 tw-flex-1">
                            <div class="tw-w-18 tw-h-18 tw-rounded-xl tw-overflow-hidden tw-bg-gray-100 tw-flex-shrink-0 tw-shadow-md tw-ring-2 tw-ring-white">
                                <?php if (!empty($order['restaurant_image'])): ?>
                                    <img src="<?= e($order['restaurant_image']) ?>" alt="<?= e($order['restaurant_name']) ?>" class="tw-w-full tw-h-full tw-object-cover" onerror="this.parentElement.innerHTML='<div class=\'tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-bg-gradient-to-br tw-from-orange-100 tw-to-orange-200\'><svg class=\'tw-w-8 tw-h-8 tw-text-orange-600\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6\'></path></svg></div>'">
                                <?php else: ?>
                                    <div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-bg-gradient-to-br tw-from-orange-100 tw-to-orange-200">
                                        <svg class="tw-w-8 tw-h-8 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-2"><?= e($order['restaurant_name']) ?></h3>
                                <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-3 tw-text-sm tw-text-gray-600">
                                    <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                        </svg>
                                        <?= e($order['order_number']) ?>
                                    </span>
                                    <span class="tw-text-gray-300">•</span>
                                    <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <?= date('M j, Y \a\t g:i A', strtotime($order['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="tw-text-right tw-flex-shrink-0">
                            <div class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-3"><?= number_format($order['total_amount'], 0) ?> <span class="tw-text-sm tw-font-medium tw-text-gray-500">XAF</span></div>
                            <span class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-semibold tw-bg-<?= $statusColor ?>-100 tw-text-<?= $statusColor ?>-800 tw-shadow-sm">
                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                            </span>
                        </div>
                    </div>


                    <!-- Rider Info (if assigned) -->
                    <?php if ($hasRider): ?>
                    <div class="tw-px-6 tw-py-4 tw-bg-blue-50 tw-border-t tw-border-blue-100 tw-mt-4">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center tw-space-x-4">
                                <div class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-blue-500 tw-flex tw-items-center tw-justify-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="tw-text-sm tw-text-blue-600 tw-font-medium">Delivery Rider</p>
                                    <p class="tw-text-base tw-font-semibold tw-text-gray-900" id="rider-name-<?= $order['id'] ?>">Loading...</p>
                                </div>
                            </div>
                            <button onclick="messageRider(<?= $order['id'] ?>, <?= $order['rider_id'] ?>)" class="tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-shadow-sm">
                                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                Message
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Quick Actions -->
                    <div class="tw-px-6 tw-py-5 tw-bg-white tw-border-t tw-border-gray-200">
                        <div class="tw-flex tw-flex-wrap tw-gap-3">
                            <button onclick="viewOrderDetails(<?= $order['id'] ?>)" class="tw-flex-1 tw-min-w-[150px] tw-px-5 tw-py-3 tw-bg-white tw-border-2 tw-border-gray-300 tw-text-gray-700 tw-rounded-lg tw-text-sm tw-font-semibold hover:tw-bg-gray-50 hover:tw-border-gray-400 tw-transition-all tw-flex tw-items-center tw-justify-center tw-shadow-sm">
                                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Details
                            </button>

                            <?php if ($hasRider && in_array(strtolower($order['status']), ['assigned', 'picked_up', 'on_the_way'])): ?>
                            <button onclick="trackOrder(<?= $order['id'] ?>)" class="tw-flex-1 tw-min-w-[150px] tw-px-5 tw-py-3 tw-bg-gradient-to-r tw-from-orange-600 tw-to-orange-700 tw-text-white tw-rounded-lg tw-text-sm tw-font-semibold hover:tw-from-orange-700 hover:tw-to-orange-800 tw-transition-all tw-flex tw-items-center tw-justify-center tw-shadow-md hover:tw-shadow-lg">
                                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Track Order
                            </button>
                            <?php endif; ?>

                            <?php if (strtolower($order['status']) === 'delivered'): ?>
                            <button onclick="rateOrder(<?= $order['id'] ?>)" class="tw-flex-1 tw-min-w-[150px] tw-px-5 tw-py-3 tw-bg-gradient-to-r tw-from-yellow-500 tw-to-yellow-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-semibold hover:tw-from-yellow-600 hover:tw-to-yellow-700 tw-transition-all tw-flex tw-items-center tw-justify-center tw-shadow-md hover:tw-shadow-lg">
                                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                                Rate Order
                            </button>
                            <?php endif; ?>
                        </div>

                        <!-- Expandable Details Section -->
                        <div id="order-details-<?= $order['id'] ?>" class="tw-hidden tw-mt-6 tw-pt-6 tw-border-t tw-border-gray-200 tw-space-y-5">
                            <!-- Order Items -->
                            <div class="tw-bg-white tw-rounded-lg tw-border tw-border-gray-200 tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-3 tw-mb-4">
                                    <svg class="tw-w-5 tw-h-5 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    <h4 class="tw-text-base tw-font-semibold tw-text-gray-900">Order Items</h4>
                                </div>
                                <div id="order-items-<?= $order['id'] ?>" class="tw-space-y-3">
                                    <div class="tw-text-center tw-py-6 tw-text-sm tw-text-gray-500">
                                        <div class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-6 tw-w-6 tw-border-b-2 tw-border-orange-600"></div>
                                        <p class="tw-mt-3">Loading items...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Address -->
                            <div class="tw-bg-white tw-rounded-lg tw-border tw-border-gray-200 tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-3 tw-mb-4">
                                    <svg class="tw-w-5 tw-h-5 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <h4 class="tw-text-base tw-font-semibold tw-text-gray-900">Delivery Address</h4>
                                </div>
                                <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                                    <p class="tw-text-sm tw-text-gray-700 tw-leading-relaxed"><?= e($order['delivery_address'] ?? 'Not specified') ?></p>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="tw-bg-gradient-to-br tw-from-orange-50 tw-to-amber-50 tw-rounded-lg tw-border tw-border-orange-200 tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-3 tw-mb-4">
                                    <svg class="tw-w-5 tw-h-5 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <h4 class="tw-text-base tw-font-semibold tw-text-gray-900">Order Summary</h4>
                                </div>
                                <div class="tw-bg-white tw-p-5 tw-rounded-lg tw-space-y-3 tw-text-sm tw-shadow-sm">
                                    <div class="tw-flex tw-justify-between tw-items-center">
                                        <span class="tw-text-gray-600">Subtotal</span>
                                        <span class="tw-font-medium tw-text-gray-900"><?= number_format($order['subtotal'] ?? 0, 0) ?> XAF</span>
                                    </div>
                                    <?php if (isset($order['service_fee']) && $order['service_fee'] > 0): ?>
                                    <div class="tw-flex tw-justify-between tw-items-center">
                                        <span class="tw-text-gray-600">Service Fee</span>
                                        <span class="tw-font-medium tw-text-gray-900"><?= number_format($order['service_fee'], 0) ?> XAF</span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                                    <div class="tw-flex tw-justify-between tw-items-center">
                                        <span class="tw-text-gray-600">Tax</span>
                                        <span class="tw-font-medium tw-text-gray-900"><?= number_format($order['tax_amount'], 0) ?> XAF</span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="tw-flex tw-justify-between tw-items-center">
                                        <span class="tw-text-gray-600">Delivery Fee</span>
                                        <span class="tw-font-medium tw-text-gray-900"><?= number_format($order['delivery_fee'] ?? 0, 0) ?> XAF</span>
                                    </div>
                                    <div class="tw-flex tw-justify-between tw-items-center tw-pt-3 tw-border-t-2 tw-border-orange-200">
                                        <span class="tw-font-bold tw-text-gray-900 tw-text-base">Total</span>
                                        <span class="tw-font-bold tw-text-orange-600 tw-text-lg"><?= number_format($order['total_amount'], 0) ?> XAF</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                                <?php if (in_array(strtolower($order['status']), ['pending', 'confirmed'])): ?>
                                <button onclick="cancelOrder(<?= $order['id'] ?>)" class="tw-w-full tw-px-5 tw-py-3 tw-bg-gradient-to-r tw-from-red-500 tw-to-red-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-semibold hover:tw-from-red-600 hover:tw-to-red-700 tw-transition-all tw-flex tw-items-center tw-justify-center tw-shadow-md hover:tw-shadow-lg">
                                    <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel Order
                                </button>
                                <?php endif; ?>

                                <?php if (strtolower($order['status']) === 'delivered'): ?>
                                <button onclick="reorderItems(<?= $order['id'] ?>)" class="tw-w-full tw-px-5 tw-py-3 tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-semibold hover:tw-from-blue-600 hover:tw-to-blue-700 tw-transition-all tw-flex tw-items-center tw-justify-center tw-shadow-md hover:tw-shadow-lg">
                                    <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Reorder
                                </button>
                                <?php endif; ?>

                                <button onclick="contactSupport(<?= $order['id'] ?>)" class="tw-w-full tw-px-5 tw-py-3 tw-bg-white tw-border-2 tw-border-gray-300 tw-text-gray-700 tw-rounded-lg tw-text-sm tw-font-semibold hover:tw-bg-gray-50 hover:tw-border-gray-400 tw-transition-all tw-flex tw-items-center tw-justify-center tw-shadow-sm">
                                    <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    Contact Support
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="tw-px-4 tw-py-4">
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-4">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div class="tw-text-sm tw-text-gray-600">
                        Page <?= $page ?> of <?= $totalPages ?>
                    </div>
                    <div class="tw-flex tw-items-center tw-space-x-2">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-200 tw-transition-colors">
                            Previous
                        </a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="tw-px-4 tw-py-2 tw-bg-orange-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-orange-600 tw-transition-colors">
                            Next
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Message Rider Modal -->
<div id="message-rider-modal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-end md:tw-items-center tw-justify-center tw-p-0 md:tw-p-4 tw-hidden tw-z-50">
    <div class="tw-bg-white tw-rounded-t-2xl md:tw-rounded-2xl tw-w-full md:tw-max-w-lg tw-max-h-[90vh] tw-overflow-hidden tw-shadow-2xl">
        <!-- Modal Header -->
        <div class="tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 tw-p-4 tw-flex tw-items-center tw-justify-between">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-white tw-bg-opacity-20 tw-flex tw-items-center tw-justify-center">
                    <svg class="tw-w-6 tw-h-6 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="tw-text-lg tw-font-semibold tw-text-white">Message Rider</h3>
                    <p class="tw-text-sm tw-text-blue-100" id="rider-name-modal">Loading...</p>
                </div>
            </div>
            <button onclick="closeMessageModal()" class="tw-p-2 tw-rounded-lg tw-bg-white tw-bg-opacity-20 hover:tw-bg-opacity-30 tw-transition-colors">
                <svg class="tw-w-5 tw-h-5 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Message Thread -->
        <div id="message-thread" class="tw-p-4 tw-space-y-3 tw-max-h-96 tw-overflow-y-auto tw-bg-gray-50">
            <div class="tw-text-center tw-py-8 tw-text-sm tw-text-gray-500">
                Start a conversation with your rider
            </div>
        </div>

        <!-- Message Input -->
        <div class="tw-p-4 tw-border-t tw-border-gray-200 tw-bg-white">
            <form id="message-form" class="tw-flex tw-space-x-2">
                <input type="hidden" id="message-order-id">
                <input type="hidden" id="message-rider-id">
                <textarea
                    id="message-input"
                    rows="2"
                    placeholder="Type your message..."
                    class="tw-flex-1 tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 tw-resize-none"
                ></textarea>
                <button type="submit" class="tw-px-4 tw-py-2 tw-bg-blue-500 tw-text-white tw-rounded-lg hover:tw-bg-blue-600 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div id="rating-modal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-end md:tw-items-center tw-justify-center tw-p-0 md:tw-p-4 tw-hidden tw-z-50">
    <div class="tw-bg-white tw-rounded-t-2xl md:tw-rounded-2xl tw-w-full md:tw-max-w-lg tw-p-6 tw-shadow-2xl">
        <div class="tw-text-center tw-mb-6">
            <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-bg-yellow-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                <svg class="tw-w-8 tw-h-8 tw-text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </div>
            <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-2">Rate Your Order</h3>
            <p class="tw-text-sm tw-text-gray-600">How was your experience?</p>
        </div>

        <div class="tw-space-y-4">
            <div class="tw-text-center">
                <div class="tw-flex tw-justify-center tw-space-x-2" id="star-rating">
                    <button class="star tw-text-3xl tw-text-gray-300 hover:tw-text-yellow-400 tw-transition-colors" data-rating="1">★</button>
                    <button class="star tw-text-3xl tw-text-gray-300 hover:tw-text-yellow-400 tw-transition-colors" data-rating="2">★</button>
                    <button class="star tw-text-3xl tw-text-gray-300 hover:tw-text-yellow-400 tw-transition-colors" data-rating="3">★</button>
                    <button class="star tw-text-3xl tw-text-gray-300 hover:tw-text-yellow-400 tw-transition-colors" data-rating="4">★</button>
                    <button class="star tw-text-3xl tw-text-gray-300 hover:tw-text-yellow-400 tw-transition-colors" data-rating="5">★</button>
                </div>
                <p id="rating-text" class="tw-text-sm tw-text-gray-500 tw-mt-2">Tap a star to rate</p>
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Your Review (Optional)</label>
                <textarea id="review-text" rows="3" class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-resize-none focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500" placeholder="Share your experience..."></textarea>
            </div>

            <div class="tw-flex tw-space-x-3">
                <button onclick="closeRatingModal()" class="tw-flex-1 tw-px-4 tw-py-2.5 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-font-medium hover:tw-bg-gray-200 tw-transition-colors">
                    Cancel
                </button>
                <button onclick="submitRating()" class="tw-flex-1 tw-px-4 tw-py-2.5 tw-bg-orange-500 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-orange-600 tw-transition-colors">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Refresh orders
function refreshOrders() {
    location.reload();
}

// Filter functionality
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.filter-tab').forEach(t => {
            t.classList.remove('tw-border-orange-500', 'tw-text-orange-600');
            t.classList.add('tw-border-transparent', 'tw-text-gray-500');
        });
        this.classList.remove('tw-border-transparent', 'tw-text-gray-500');
        this.classList.add('tw-border-orange-500', 'tw-text-orange-600');

        // Filter orders
        const status = this.dataset.status;
        filterOrders(status);
    });
});

function filterOrders(status) {
    const orders = document.querySelectorAll('.order-card');
    orders.forEach(order => {
        if (status === 'all' || order.dataset.status === status) {
            order.style.display = 'block';
        } else {
            order.style.display = 'none';
        }
    });
}

// View order details
function viewOrderDetails(orderId) {
    const details = document.getElementById(`order-details-${orderId}`);
    const isHidden = details.classList.contains('tw-hidden');

    if (isHidden) {
        details.classList.remove('tw-hidden');
        loadOrderItems(orderId);
    } else {
        details.classList.add('tw-hidden');
    }
}

// Load order items
function loadOrderItems(orderId) {
    const container = document.getElementById(`order-items-${orderId}`);

    fetch(`<?= url('/api/orders/') ?>${orderId}/items`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items) {
                let html = '';
                data.items.forEach(item => {
                    const imageUrl = item.image || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80';
                    html += `
                        <div class="tw-flex tw-items-center tw-justify-between tw-py-3 tw-border-b tw-border-gray-100 last:tw-border-0">
                            <div class="tw-flex tw-items-center tw-space-x-3 tw-flex-1">
                                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 tw-flex-shrink-0">
                                    <img src="${imageUrl}" alt="${item.name}" class="tw-w-full tw-h-full tw-object-cover" onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80'">
                                </div>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <div class="tw-flex tw-items-center tw-gap-2">
                                        <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-6 tw-h-6 tw-bg-orange-100 tw-text-orange-700 tw-rounded-full tw-text-xs tw-font-bold">${item.quantity}</span>
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">${item.name}</span>
                                    </div>
                                    ${item.special_instructions ? `<p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-truncate">${item.special_instructions}</p>` : ''}
                                </div>
                            </div>
                            <span class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-ml-3 tw-flex-shrink-0">${parseInt(item.total_price).toLocaleString()} XAF</span>
                        </div>
                    `;
                });
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="tw-text-center tw-py-4 tw-text-sm tw-text-gray-600">No items found</div>';
            }
        })
        .catch(error => {
            console.error('Error loading order items:', error);
            container.innerHTML = '<div class="tw-text-center tw-py-4 tw-text-sm tw-text-red-600">Error loading items</div>';
        });
}

// Load rider info for orders with riders
document.querySelectorAll('[id^="rider-name-"]').forEach(el => {
    const orderId = el.id.replace('rider-name-', '');
    const orderCard = document.querySelector(`[data-order-id="${orderId}"]`);
    if (orderCard) {
        const riderId = orderCard.dataset.riderId;
        if (riderId) {
            fetch(`<?= url('/api/users/') ?>${riderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.user) {
                        el.textContent = data.user.name || 'Rider';
                    }
                })
                .catch(error => console.error('Error loading rider info:', error));
        }
    }
});

// Message rider
let currentConversationId = null;

function messageRider(orderId, riderId) {
    document.getElementById('message-order-id').value = orderId;
    document.getElementById('message-rider-id').value = riderId;
    document.getElementById('message-rider-modal').classList.remove('tw-hidden');

    // Load rider name
    fetch(`<?= url('/api/users/') ?>${riderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user) {
                document.getElementById('rider-name-modal').textContent = data.user.name || 'Rider';
            }
        });

    // Load existing messages
    loadMessages(orderId, riderId);
}

function closeMessageModal() {
    document.getElementById('message-rider-modal').classList.add('tw-hidden');
    document.getElementById('message-input').value = '';
}

function loadMessages(orderId, riderId) {
    const thread = document.getElementById('message-thread');

    fetch(`<?= url('/customer/messages/conversation') ?>?order_id=${orderId}&rider_id=${riderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                currentConversationId = data.conversation_id;
                let html = '';
                data.messages.forEach(msg => {
                    const isCustomer = msg.sender_id == <?= $user->id ?? 0 ?>;
                    html += `
                        <div class="tw-flex ${isCustomer ? 'tw-justify-end' : 'tw-justify-start'}">
                            <div class="tw-max-w-[75%] tw-px-4 tw-py-2 tw-rounded-lg ${isCustomer ? 'tw-bg-blue-500 tw-text-white' : 'tw-bg-white tw-text-gray-900 tw-border tw-border-gray-200'}">
                                <p class="tw-text-sm">${msg.message}</p>
                                <p class="tw-text-xs tw-mt-1 ${isCustomer ? 'tw-text-blue-100' : 'tw-text-gray-500'}">${new Date(msg.created_at).toLocaleTimeString()}</p>
                            </div>
                        </div>
                    `;
                });
                thread.innerHTML = html;
                thread.scrollTop = thread.scrollHeight;
            } else {
                thread.innerHTML = '<div class="tw-text-center tw-py-8 tw-text-sm tw-text-gray-500">Start a conversation with your rider</div>';
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
            thread.innerHTML = '<div class="tw-text-center tw-py-8 tw-text-sm tw-text-red-600">Error loading messages</div>';
        });
}

// Send message
document.getElementById('message-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const orderId = document.getElementById('message-order-id').value;
    const riderId = document.getElementById('message-rider-id').value;
    const message = document.getElementById('message-input').value.trim();

    if (!message) return;

    const formData = new FormData();
    formData.append('rider_id', riderId);
    formData.append('order_id', orderId);
    formData.append('message', message);
    formData.append('subject', `Order #${orderId}`);

    fetch('<?= url('/customer/messages/compose-to-rider') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('message-input').value = '';
            loadMessages(orderId, riderId);
        } else {
            alert(data.message || 'Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message');
    });
});

// Cancel order
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        fetch(`<?= url('/api/orders/') ?>${orderId}/cancel`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to cancel order');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to cancel order');
        });
    }
}

// Reorder
function reorderItems(orderId) {
    fetch(`<?= url('/api/orders/') ?>${orderId}/reorder`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?= url('/cart') ?>';
        } else {
            alert(data.message || 'Failed to reorder');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to reorder');
    });
}

// Track order
function trackOrder(orderId) {
    window.location.href = `<?= url('/customer/orders/') ?>${orderId}/track`;
}

// Contact support
function contactSupport(orderId) {
    window.location.href = `<?= url('/customer/messages') ?>?order_id=${orderId}`;
}

// Rating functionality
let currentOrderId = null;
let selectedRating = 0;

function rateOrder(orderId) {
    currentOrderId = orderId;
    selectedRating = 0;
    document.getElementById('review-text').value = '';
    updateStarDisplay();
    document.getElementById('rating-modal').classList.remove('tw-hidden');
}

function closeRatingModal() {
    document.getElementById('rating-modal').classList.add('tw-hidden');
    currentOrderId = null;
    selectedRating = 0;
}

document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        selectedRating = parseInt(this.dataset.rating);
        updateStarDisplay();
    });
});

function updateStarDisplay() {
    const ratingTexts = ['Tap a star to rate', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

    document.querySelectorAll('.star').forEach((star, index) => {
        if (index < selectedRating) {
            star.classList.remove('tw-text-gray-300');
            star.classList.add('tw-text-yellow-400');
        } else {
            star.classList.remove('tw-text-yellow-400');
            star.classList.add('tw-text-gray-300');
        }
    });

}

function submitRating() {
    if (selectedRating === 0) {
        alert('Please select a rating');
        return;
    }

    const reviewText = document.getElementById('review-text').value;

    fetch(`<?= url('/api/orders/') ?>${currentOrderId}/rate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            rating: selectedRating,
            review: reviewText
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeRatingModal();
            alert('Thank you for your rating!');
            location.reload();
        } else {
            alert(data.message || 'Failed to submit rating');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to submit rating');
    });
}
</script>
