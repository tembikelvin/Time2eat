<?php
/**
 * Admin Order Details View
 * Shows detailed information about an order
 */

// Set current page for sidebar highlighting
$currentPage = 'orders';
?>

<!-- Enhanced Page Header - Mobile Optimized -->
<div class="tw-mb-6 sm:tw-mb-8 tw-px-2 sm:tw-px-0">
    <div class="tw-bg-gradient-to-r tw-from-blue-600 tw-to-purple-600 tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-shadow-xl">
        <div class="tw-flex tw-flex-col lg:tw-flex-row tw-items-start lg:tw-items-center tw-justify-between tw-space-y-4 lg:tw-space-y-0">
            <div class="tw-flex-1 tw-min-w-0">
                <div class="tw-flex tw-items-center tw-space-x-3 tw-mb-3">
                    <a href="<?= url('/admin/orders') ?>" class="tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-p-2 tw-rounded-lg tw-transition-all tw-duration-200">
                        <i data-feather="arrow-left" class="tw-h-5 tw-w-5"></i>
                    </a>
                    <div class="tw-min-w-0 tw-flex-1">
                        <h1 class="tw-text-2xl lg:tw-text-3xl tw-font-bold tw-text-white tw-truncate">
                            Order #<?= e($order['order_number'] ?? $order['id']) ?>
                        </h1>
                        <p class="tw-text-blue-100 tw-text-sm tw-mt-1">
                            Placed <?= date('M j, Y \a\t H:i', strtotime($order['created_at'])) ?>
                        </p>
                    </div>
                </div>
                
                <!-- Status Badge with Animation -->
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <span class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-semibold tw-shadow-lg
                        <?php 
                        switch($order['status']) {
                            case 'pending': echo 'tw-bg-yellow-400 tw-text-yellow-900 tw-animate-pulse'; break;
                            case 'confirmed': echo 'tw-bg-blue-400 tw-text-blue-900'; break;
                            case 'preparing': echo 'tw-bg-orange-400 tw-text-orange-900 tw-animate-pulse'; break;
                            case 'ready': echo 'tw-bg-purple-400 tw-text-purple-900 tw-animate-pulse'; break;
                            case 'picked_up': echo 'tw-bg-indigo-400 tw-text-indigo-900 tw-animate-pulse'; break;
                            case 'on_the_way': echo 'tw-bg-blue-400 tw-text-blue-900 tw-animate-pulse'; break;
                            case 'delivered': echo 'tw-bg-green-400 tw-text-green-900'; break;
                            case 'cancelled': echo 'tw-bg-red-400 tw-text-red-900'; break;
                            default: echo 'tw-bg-gray-400 tw-text-gray-900';
                        }
                        ?>">
                        <div class="tw-h-2 tw-w-2 tw-rounded-full tw-bg-current tw-mr-2 tw-animate-pulse"></div>
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
                <a href="<?= url('/admin/orders/' . $order['id'] . '/track') ?>" 
                   class="tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-space-x-2 tw-shadow-lg">
                    <i data-feather="map-pin" class="tw-h-4 tw-w-4"></i>
                    <span>Track Order</span>
                </a>
                <?php if (in_array($order['status'], ['pending', 'confirmed', 'preparing'])): ?>
                    <button onclick="cancelOrder(<?= $order['id'] ?>)" 
                            class="tw-bg-red-500/80 hover:tw-bg-red-600/80 tw-text-white tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-space-x-2 tw-shadow-lg">
                        <i data-feather="x-circle" class="tw-h-4 tw-w-4"></i>
                        <span>Cancel Order</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Order Info Grid - Mobile Optimized -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-4 sm:tw-gap-6 lg:tw-gap-8 tw-px-2 sm:tw-px-0">
    <!-- Main Content - Mobile Optimized -->
    <div class="lg:tw-col-span-2 tw-space-y-4 sm:tw-space-y-6">
        <!-- Order Items -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden">
            <div class="tw-bg-gradient-to-r tw-from-gray-50 tw-to-gray-100 tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                        <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                    </div>
                    <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">Order Items</h2>
                    <span class="tw-bg-blue-100 tw-text-blue-800 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium">
                        <?= count($orderItems) ?> item<?= count($orderItems) !== 1 ? 's' : '' ?>
                    </span>
                </div>
            </div>
            <div class="tw-p-6">
                <?php if (!empty($orderItems)): ?>
                    <div class="tw-space-y-4">
                        <?php foreach ($orderItems as $index => $item): ?>
                            <div class="tw-group tw-bg-gradient-to-r tw-from-gray-50 tw-to-white tw-rounded-xl tw-p-4 tw-border tw-border-gray-200 hover:tw-border-blue-300 tw-transition-all tw-duration-200 hover:tw-shadow-md">
                                <div class="tw-flex tw-items-center tw-justify-between">
                                    <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0">
                                        <!-- Item Image -->
                                        <div class="tw-relative tw-flex-shrink-0">
                                            <?php if (!empty($item['item_image'])): ?>
                                                <img src="<?= e($item['item_image']) ?>" alt="<?= e($item['item_name']) ?>" 
                                                     class="tw-h-20 tw-w-20 tw-rounded-xl tw-object-cover tw-shadow-md">
                                            <?php else: ?>
                                                <div class="tw-h-20 tw-w-20 tw-rounded-xl tw-bg-gradient-to-br tw-from-gray-200 tw-to-gray-300 tw-flex tw-items-center tw-justify-center tw-shadow-md">
                                                    <i data-feather="image" class="tw-h-8 tw-w-8 tw-text-gray-500"></i>
                                                </div>
                                            <?php endif; ?>
                                            <!-- Item Number Badge -->
                                            <div class="tw-absolute -tw-top-2 -tw-left-2 tw-h-6 tw-w-6 tw-bg-blue-600 tw-text-white tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-bold">
                                                <?= $index + 1 ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Item Details -->
                                        <div class="tw-ml-4 tw-flex-1 tw-min-w-0">
                                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-truncate">
                                                <?= e($item['item_name'] ?? 'Item') ?>
                                            </h3>
                                            <div class="tw-flex tw-items-center tw-space-x-4 tw-mt-1">
                                                <span class="tw-bg-blue-100 tw-text-blue-800 tw-px-2 tw-py-1 tw-rounded-md tw-text-sm tw-font-medium">
                                                    Qty: <?= $item['quantity'] ?? 1 ?>
                                                </span>
                                                <span class="tw-text-gray-600 tw-text-sm">
                                                    @ <?= number_format($item['price'] ?? 0) ?> XAF each
                                                </span>
                                            </div>
                                            <?php if (!empty($item['special_instructions'])): ?>
                                                <div class="tw-mt-2 tw-p-2 tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg">
                                                    <div class="tw-flex tw-items-start tw-space-x-2">
                                                        <i data-feather="message-square" class="tw-h-4 tw-w-4 tw-text-yellow-600 tw-mt-0.5 tw-flex-shrink-0"></i>
                                                        <p class="tw-text-sm tw-text-yellow-800"><?= e($item['special_instructions']) ?></p>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Item Total -->
                                    <div class="tw-text-right tw-flex-shrink-0">
                                        <div class="tw-text-xl tw-font-bold tw-text-gray-900">
                                            <?= number_format(($item['quantity'] ?? 1) * ($item['price'] ?? 0)) ?> XAF
                                        </div>
                                        <div class="tw-text-sm tw-text-gray-500 tw-mt-1">
                                            Total
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Enhanced Order Summary -->
                    <div class="tw-mt-8 tw-pt-6 tw-border-t tw-border-gray-200">
                        <div class="tw-bg-gradient-to-r tw-from-gray-50 tw-to-blue-50 tw-rounded-xl tw-p-6">
                            <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center tw-space-x-2">
                                <i data-feather="calculator" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                                <span>Order Summary</span>
                            </h3>
                            
                            <div class="tw-space-y-3">
                                <div class="tw-flex tw-justify-between tw-items-center tw-py-2">
                                    <span class="tw-text-gray-600 tw-font-medium">Subtotal</span>
                                    <span class="tw-text-gray-900 tw-font-semibold"><?= number_format($order['subtotal'] ?? 0) ?> XAF</span>
                                </div>
                                
                                <div class="tw-flex tw-justify-between tw-items-center tw-py-2">
                                    <span class="tw-text-gray-600 tw-font-medium">Delivery Fee</span>
                                    <span class="tw-text-gray-900 tw-font-semibold"><?= number_format($order['delivery_fee'] ?? 0) ?> XAF</span>
                                </div>

                                <?php if (($order['discount'] ?? 0) > 0): ?>
                                    <div class="tw-flex tw-justify-between tw-items-center tw-py-2 tw-bg-green-50 tw-rounded-lg tw-px-3">
                                        <span class="tw-text-green-700 tw-font-medium tw-flex tw-items-center tw-space-x-2">
                                            <i data-feather="tag" class="tw-h-4 tw-w-4"></i>
                                            <span>Discount</span>
                                        </span>
                                        <span class="tw-text-green-700 tw-font-bold">-<?= number_format($order['discount']) ?> XAF</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="tw-border-t tw-border-gray-300 tw-pt-4 tw-mt-4">
                                    <div class="tw-flex tw-justify-between tw-items-center tw-py-2">
                                        <span class="tw-text-xl tw-font-bold tw-text-gray-900">Total Amount</span>
                                        <span class="tw-text-2xl tw-font-bold tw-text-blue-600"><?= number_format($order['total_amount'] ?? 0) ?> XAF</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="tw-text-sm tw-text-gray-500">No items found</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Enhanced Delivery Information -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden">
            <div class="tw-bg-gradient-to-r tw-from-green-50 tw-to-emerald-50 tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg">
                        <i data-feather="truck" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                    </div>
                    <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">Delivery Information</h2>
                </div>
            </div>
            <div class="tw-p-6">
                <div class="tw-space-y-6">
                    <!-- Delivery Address -->
                    <div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-indigo-50 tw-rounded-xl tw-p-4 tw-border tw-border-blue-200">
                        <div class="tw-flex tw-items-start tw-space-x-3">
                            <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg tw-flex-shrink-0">
                                <i data-feather="map-pin" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <h3 class="tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">Delivery Address</h3>
                                <div class="tw-text-sm tw-text-gray-700 tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-blue-200">
                                    <?php 
                                    $deliveryAddress = $order['delivery_address'] ?? 'N/A';
                                    if (is_string($deliveryAddress) && strpos($deliveryAddress, '{') === 0) {
                                        $addressData = json_decode($deliveryAddress, true);
                                        if ($addressData && isset($addressData['instructions'])) {
                                            echo e($addressData['instructions']);
                                        } else {
                                            echo e($deliveryAddress);
                                        }
                                    } else {
                                        echo e($deliveryAddress);
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($order['delivery_notes'])): ?>
                        <!-- Delivery Notes -->
                        <div class="tw-bg-gradient-to-r tw-from-yellow-50 tw-to-orange-50 tw-rounded-xl tw-p-4 tw-border tw-border-yellow-200">
                            <div class="tw-flex tw-items-start tw-space-x-3">
                                <div class="tw-p-2 tw-bg-yellow-100 tw-rounded-lg tw-flex-shrink-0">
                                    <i data-feather="message-square" class="tw-h-5 tw-w-5 tw-text-yellow-600"></i>
                                </div>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <h3 class="tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">Delivery Notes</h3>
                                    <div class="tw-text-sm tw-text-gray-700 tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-yellow-200">
                                        <?= e($order['delivery_notes']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($order['rider_name'])): ?>
                        <!-- Assigned Rider -->
                        <div class="tw-bg-gradient-to-r tw-from-purple-50 tw-to-pink-50 tw-rounded-xl tw-p-4 tw-border tw-border-purple-200">
                            <div class="tw-flex tw-items-start tw-space-x-3">
                                <div class="tw-p-2 tw-bg-purple-100 tw-rounded-lg tw-flex-shrink-0">
                                    <i data-feather="user" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
                                </div>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <h3 class="tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">Assigned Rider</h3>
                                    <div class="tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-purple-200">
                                        <div class="tw-flex tw-items-center tw-justify-between">
                                            <div>
                                                <div class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= e($order['rider_name']) ?></div>
                                                <?php if (!empty($order['rider_phone'])): ?>
                                                    <div class="tw-text-sm tw-text-gray-600 tw-mt-1">
                                                        <a href="tel:<?= e($order['rider_phone']) ?>" 
                                                           class="tw-text-blue-600 hover:tw-text-blue-800 tw-flex tw-items-center tw-space-x-1">
                                                            <i data-feather="phone" class="tw-h-4 tw-w-4"></i>
                                                            <span><?= e($order['rider_phone']) ?></span>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg">
                                                <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No Rider Assigned -->
                        <div class="tw-bg-gradient-to-r tw-from-gray-50 tw-to-slate-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-gray-100 tw-rounded-lg">
                                    <i data-feather="user-x" class="tw-h-5 tw-w-5 tw-text-gray-600"></i>
                                </div>
                                <div>
                                    <h3 class="tw-text-sm tw-font-bold tw-text-gray-900">No Rider Assigned</h3>
                                    <p class="tw-text-sm tw-text-gray-600">This order is waiting for a rider assignment.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Sidebar - Mobile Optimized -->
    <div class="tw-space-y-4 sm:tw-space-y-6">
        <!-- Customer Information -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden">
            <div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-indigo-50 tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                        <i data-feather="user" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                    </div>
                    <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">Customer</h2>
                </div>
            </div>
            <div class="tw-p-6">
                <div class="tw-flex tw-items-center tw-space-x-4 tw-mb-6">
                    <div class="tw-relative">
                        <div class="tw-h-16 tw-w-16 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                            <span class="tw-text-white tw-font-bold tw-text-xl">
                                <?= strtoupper(substr($order['customer_name'] ?? 'C', 0, 1)) ?>
                            </span>
                        </div>
                        <div class="tw-absolute -tw-bottom-1 -tw-right-1 tw-h-6 tw-w-6 tw-bg-green-500 tw-rounded-full tw-border-2 tw-border-white tw-flex tw-items-center tw-justify-center">
                            <i data-feather="check" class="tw-h-3 tw-w-3 tw-text-white"></i>
                        </div>
                    </div>
                    <div class="tw-flex-1 tw-min-w-0">
                        <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-truncate"><?= e($order['customer_name'] ?? 'Unknown') ?></h3>
                        <p class="tw-text-sm tw-text-gray-600">Verified Customer</p>
                    </div>
                </div>
                
                <div class="tw-space-y-4">
                    <?php if (!empty($order['customer_email'])): ?>
                        <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-gray-100 tw-rounded-lg">
                                    <i data-feather="mail" class="tw-h-4 tw-w-4 tw-text-gray-600"></i>
                                </div>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <p class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wide">Email</p>
                                    <a href="mailto:<?= e($order['customer_email']) ?>" 
                                       class="tw-text-sm tw-text-blue-600 hover:tw-text-blue-800 tw-font-medium tw-truncate tw-block">
                                        <?= e($order['customer_email']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($order['customer_phone'])): ?>
                        <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-gray-100 tw-rounded-lg">
                                    <i data-feather="phone" class="tw-h-4 tw-w-4 tw-text-gray-600"></i>
                                </div>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <p class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wide">Phone</p>
                                    <a href="tel:<?= e($order['customer_phone']) ?>" 
                                       class="tw-text-sm tw-text-blue-600 hover:tw-text-blue-800 tw-font-medium">
                                        <?= e($order['customer_phone']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Restaurant Information -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 tw-overflow-hidden">
            <div class="tw-bg-gradient-to-r tw-from-orange-50 tw-to-red-50 tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-orange-100 tw-rounded-lg">
                        <i data-feather="store" class="tw-h-5 tw-w-5 tw-text-orange-600"></i>
                    </div>
                    <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">Restaurant</h2>
                </div>
            </div>
            <div class="tw-p-6">
                <div class="tw-flex tw-items-center tw-space-x-4 tw-mb-6">
                    <div class="tw-relative">
                        <div class="tw-h-16 tw-w-16 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                            <i data-feather="store" class="tw-h-8 tw-w-8 tw-text-white"></i>
                        </div>
                        <div class="tw-absolute -tw-bottom-1 -tw-right-1 tw-h-6 tw-w-6 tw-bg-green-500 tw-rounded-full tw-border-2 tw-border-white tw-flex tw-items-center tw-justify-center">
                            <i data-feather="check" class="tw-h-3 tw-w-3 tw-text-white"></i>
                        </div>
                    </div>
                    <div class="tw-flex-1 tw-min-w-0">
                        <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-truncate"><?= e($order['restaurant_name'] ?? 'Unknown') ?></h3>
                        <p class="tw-text-sm tw-text-gray-600">Partner Restaurant</p>
                    </div>
                </div>
                
                <div class="tw-space-y-4">
                    <?php if (!empty($order['restaurant_phone'])): ?>
                        <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-gray-100 tw-rounded-lg">
                                    <i data-feather="phone" class="tw-h-4 tw-w-4 tw-text-gray-600"></i>
                                </div>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <p class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wide">Phone</p>
                                    <a href="tel:<?= e($order['restaurant_phone']) ?>" 
                                       class="tw-text-sm tw-text-blue-600 hover:tw-text-blue-800 tw-font-medium">
                                        <?= e($order['restaurant_phone']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($order['restaurant_address'])): ?>
                        <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                            <div class="tw-flex tw-items-start tw-space-x-3">
                                <div class="tw-p-2 tw-bg-gray-100 tw-rounded-lg tw-flex-shrink-0">
                                    <i data-feather="map-pin" class="tw-h-4 tw-w-4 tw-text-gray-600"></i>
                                </div>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <p class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wide">Address</p>
                                    <p class="tw-text-sm tw-text-gray-700 tw-mt-1"><?= e($order['restaurant_address']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
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
                <div class="tw-space-y-6">
                    <!-- Order Created -->
                    <div class="tw-flex tw-items-start tw-space-x-4">
                        <div class="tw-flex-shrink-0">
                            <div class="tw-h-10 tw-w-10 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <i data-feather="plus-circle" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                            </div>
                        </div>
                        <div class="tw-flex-1 tw-min-w-0">
                            <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-1">
                                <h3 class="tw-text-sm tw-font-bold tw-text-gray-900">Order Created</h3>
                                <span class="tw-bg-green-100 tw-text-green-800 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium">
                                    Initial
                                </span>
                            </div>
                            <p class="tw-text-sm tw-text-gray-600"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($order['updated_at']) && $order['updated_at'] !== $order['created_at']): ?>
                        <!-- Last Updated -->
                        <div class="tw-flex tw-items-start tw-space-x-4">
                            <div class="tw-flex-shrink-0">
                                <div class="tw-h-10 tw-w-10 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                    <i data-feather="edit" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                                </div>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-1">
                                    <h3 class="tw-text-sm tw-font-bold tw-text-gray-900">Last Updated</h3>
                                    <span class="tw-bg-blue-100 tw-text-blue-800 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium">
                                        Modified
                                    </span>
                                </div>
                                <p class="tw-text-sm tw-text-gray-600"><?= date('M j, Y g:i A', strtotime($order['updated_at'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($order['status'] === 'delivered' && !empty($order['delivered_at'])): ?>
                        <!-- Delivered -->
                        <div class="tw-flex tw-items-start tw-space-x-4">
                            <div class="tw-flex-shrink-0">
                                <div class="tw-h-10 tw-w-10 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                    <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                                </div>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-1">
                                    <h3 class="tw-text-sm tw-font-bold tw-text-green-900">Order Delivered</h3>
                                    <span class="tw-bg-green-100 tw-text-green-800 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium">
                                        Completed
                                    </span>
                                </div>
                                <p class="tw-text-sm tw-text-green-600 tw-font-medium"><?= date('M j, Y g:i A', strtotime($order['delivered_at'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($order['status'] === 'cancelled' && !empty($order['cancelled_at'])): ?>
                        <!-- Cancelled -->
                        <div class="tw-flex tw-items-start tw-space-x-4">
                            <div class="tw-flex-shrink-0">
                                <div class="tw-h-10 tw-w-10 tw-bg-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                    <i data-feather="x-circle" class="tw-h-5 tw-w-5 tw-text-red-600"></i>
                                </div>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-1">
                                    <h3 class="tw-text-sm tw-font-bold tw-text-red-900">Order Cancelled</h3>
                                    <span class="tw-bg-red-100 tw-text-red-800 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium">
                                        Cancelled
                                    </span>
                                </div>
                                <p class="tw-text-sm tw-text-red-600 tw-font-medium"><?= date('M j, Y g:i A', strtotime($order['cancelled_at'])) ?></p>
                                <?php if (!empty($order['cancellation_reason'])): ?>
                                    <div class="tw-mt-2 tw-p-2 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg">
                                        <p class="tw-text-xs tw-text-red-800 tw-font-medium">Reason: <?= e($order['cancellation_reason']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Current Status -->
                    <div class="tw-flex tw-items-start tw-space-x-4">
                        <div class="tw-flex-shrink-0">
                            <div class="tw-h-10 tw-w-10 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-animate-pulse">
                                <i data-feather="activity" class="tw-h-5 tw-w-5 tw-text-orange-600"></i>
                            </div>
                        </div>
                        <div class="tw-flex-1 tw-min-w-0">
                            <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-1">
                                <h3 class="tw-text-sm tw-font-bold tw-text-gray-900">Current Status</h3>
                                <span class="tw-bg-orange-100 tw-text-orange-800 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-animate-pulse">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </div>
                            <p class="tw-text-sm tw-text-gray-600">Order is currently <?= str_replace('_', ' ', $order['status']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelOrder(orderId) {
    const reason = prompt('Enter cancellation reason (optional):');
    if (reason !== null) {
        fetch('<?= url('/admin/orders/') ?>' + orderId + '/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ reason: reason || 'Cancelled by admin' })
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
            alert('An error occurred.');
        });
    }
}

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

