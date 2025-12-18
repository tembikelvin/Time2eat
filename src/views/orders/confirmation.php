<?php
/**
 * Order Confirmation Page
 * Displayed after successful order placement
 */

$order = $order ?? null;
$restaurant = $restaurant ?? null;
$orderItems = $orderItems ?? [];
$deliveryAddress = $deliveryAddress ?? [];

if (!$order) {
    header('Location: ' . url('/customer/orders'));
    exit;
}

// Calculate estimated delivery time (30-45 minutes from now)
$estimatedMinutes = 40;
$estimatedTime = date('g:i A', strtotime('+' . $estimatedMinutes . ' minutes'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Time2Eat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes checkmark {
            0% { transform: scale(0) rotate(45deg); }
            50% { transform: scale(1.2) rotate(45deg); }
            100% { transform: scale(1) rotate(45deg); }
        }
        .checkmark-circle {
            animation: checkmark 0.6s ease-in-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body class="tw-bg-gradient-to-br tw-from-green-50 tw-to-blue-50 tw-min-h-screen">
    
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-4xl">
        
        <!-- Success Animation -->
        <div class="tw-text-center tw-mb-8 fade-in-up">
            <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-24 tw-h-24 tw-bg-green-500 tw-rounded-full tw-shadow-2xl tw-mb-6 checkmark-circle">
                <i class="fas fa-check tw-text-white tw-text-5xl"></i>
            </div>
            <h1 class="tw-text-4xl tw-font-black tw-text-gray-900 tw-mb-3">Order Confirmed!</h1>
            <p class="tw-text-xl tw-text-gray-600">Thank you for your order</p>
        </div>

        <!-- Order Number Card -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-p-8 tw-mb-6 fade-in-up" style="animation-delay: 0.1s;">
            <div class="tw-text-center tw-border-b tw-border-gray-200 tw-pb-6 tw-mb-6">
                <p class="tw-text-sm tw-text-gray-600 tw-mb-2">Order Number</p>
                <p class="tw-text-3xl tw-font-black tw-text-primary-600">#<?= htmlspecialchars($order['order_number']) ?></p>
                <p class="tw-text-sm tw-text-gray-500 tw-mt-2">
                    <i class="far fa-clock tw-mr-1"></i>
                    Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?>
                </p>
            </div>

            <!-- Estimated Delivery Time -->
            <div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-indigo-50 tw-rounded-xl tw-p-6 tw-mb-6">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div class="tw-flex tw-items-center">
                        <div class="tw-bg-blue-500 tw-rounded-full tw-p-3 tw-mr-4">
                            <i class="fas fa-motorcycle tw-text-white tw-text-2xl"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-text-gray-600 tw-mb-1">Estimated Delivery</p>
                            <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $estimatedTime ?></p>
                            <p class="tw-text-xs tw-text-gray-500">Approximately <?= $estimatedMinutes ?> minutes</p>
                        </div>
                    </div>
                    <div class="tw-text-right">
                        <span class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-yellow-100 tw-text-yellow-800 tw-rounded-full tw-text-sm tw-font-semibold">
                            <i class="fas fa-clock tw-mr-2"></i>
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="tw-bg-gray-50 tw-rounded-xl tw-p-6 tw-mb-6">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-1">Payment Method</p>
                        <p class="tw-text-lg tw-font-semibold tw-text-gray-900">
                            <?php if ($order['payment_method'] === 'cash_on_delivery'): ?>
                                <i class="fas fa-money-bill-wave tw-mr-2 tw-text-green-600"></i>
                                Cash on Delivery
                            <?php else: ?>
                                <i class="fas fa-mobile-alt tw-mr-2 tw-text-blue-600"></i>
                                Tranzak Mobile Money
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="tw-text-right">
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-1">Payment Status</p>
                        <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-semibold
                            <?= $order['payment_status'] === 'paid' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-yellow-100 tw-text-yellow-800' ?>">
                            <?= ucfirst($order['payment_status']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Track Order Button -->
            <div class="tw-text-center">
                <a href="<?= url('/customer/orders/' . $order['id'] . '/track') ?>" 
                   class="tw-inline-flex tw-items-center tw-justify-center tw-bg-primary-600 tw-text-white tw-px-8 tw-py-4 tw-rounded-xl tw-font-bold tw-text-lg hover:tw-bg-primary-700 tw-transition-all tw-shadow-lg hover:tw-shadow-xl tw-transform hover:tw-scale-105">
                    <i class="fas fa-map-marker-alt tw-mr-3"></i>
                    Track Your Order
                </a>
            </div>
        </div>

        <!-- Restaurant & Delivery Info -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6 tw-mb-6 fade-in-up" style="animation-delay: 0.2s;">
            
            <!-- Restaurant Info -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-p-6">
                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                    <i class="fas fa-store tw-mr-2 tw-text-orange-500"></i>
                    Restaurant
                </h3>
                <div class="tw-flex tw-items-start">
                    <?php if (!empty($restaurant['image'])): ?>
                        <img src="<?= htmlspecialchars($restaurant['image']) ?>" 
                             alt="<?= htmlspecialchars($restaurant['name']) ?>"
                             class="tw-w-16 tw-h-16 tw-rounded-lg tw-object-cover tw-mr-4">
                    <?php else: ?>
                        <div class="tw-w-16 tw-h-16 tw-rounded-lg tw-bg-gray-200 tw-flex tw-items-center tw-justify-center tw-mr-4">
                            <i class="fas fa-utensils tw-text-gray-400 tw-text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="tw-font-semibold tw-text-gray-900 tw-mb-1"><?= htmlspecialchars($restaurant['name']) ?></p>
                        <p class="tw-text-sm tw-text-gray-600"><?= htmlspecialchars($restaurant['address'] ?? 'N/A') ?></p>
                        <?php if (!empty($restaurant['phone'])): ?>
                            <p class="tw-text-sm tw-text-gray-600 tw-mt-2">
                                <i class="fas fa-phone tw-mr-1"></i>
                                <?= htmlspecialchars($restaurant['phone']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Delivery Address -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-p-6">
                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                    <i class="fas fa-map-marker-alt tw-mr-2 tw-text-red-500"></i>
                    Delivery Address
                </h3>
                <div class="tw-text-gray-700">
                    <?php if (!empty($deliveryAddress['street_address'])): ?>
                        <p class="tw-font-medium tw-mb-1"><?= htmlspecialchars($deliveryAddress['street_address']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($deliveryAddress['neighborhood'])): ?>
                        <p class="tw-text-sm tw-text-gray-600"><?= htmlspecialchars($deliveryAddress['neighborhood']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($deliveryAddress['city'])): ?>
                        <p class="tw-text-sm tw-text-gray-600"><?= htmlspecialchars($deliveryAddress['city']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($deliveryAddress['landmark'])): ?>
                        <p class="tw-text-sm tw-text-gray-500 tw-mt-2">
                            <i class="fas fa-landmark tw-mr-1"></i>
                            Landmark: <?= htmlspecialchars($deliveryAddress['landmark']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-p-6 tw-mb-6 fade-in-up" style="animation-delay: 0.3s;">
            <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                <i class="fas fa-shopping-bag tw-mr-2 tw-text-green-500"></i>
                Order Items
            </h3>
            <div class="tw-space-y-4">
                <?php foreach ($orderItems as $item): ?>
                    <div class="tw-flex tw-items-center tw-justify-between tw-py-3 tw-border-b tw-border-gray-100 last:tw-border-0">
                        <div class="tw-flex tw-items-center tw-flex-1">
                            <div class="tw-bg-gray-100 tw-rounded-lg tw-w-12 tw-h-12 tw-flex tw-items-center tw-justify-center tw-mr-4">
                                <span class="tw-font-bold tw-text-primary-600"><?= $item['quantity'] ?>×</span>
                            </div>
                            <div>
                                <p class="tw-font-medium tw-text-gray-900"><?= htmlspecialchars($item['item_name']) ?></p>
                                <?php if (!empty($item['special_instructions'])): ?>
                                    <p class="tw-text-xs tw-text-gray-500 tw-mt-1">
                                        <i class="fas fa-sticky-note tw-mr-1"></i>
                                        <?= htmlspecialchars($item['special_instructions']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="tw-text-right">
                            <p class="tw-font-semibold tw-text-gray-900"><?= number_format($item['total_price']) ?> XAF</p>
                            <p class="tw-text-xs tw-text-gray-500"><?= number_format($item['unit_price']) ?> XAF each</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-p-6 tw-mb-6 fade-in-up" style="animation-delay: 0.4s;">
            <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                <i class="fas fa-receipt tw-mr-2 tw-text-purple-500"></i>
                Order Summary
            </h3>
            <div class="tw-space-y-3">
                <div class="tw-flex tw-justify-between tw-text-gray-700">
                    <span>Subtotal</span>
                    <span class="tw-font-medium"><?= number_format($order['subtotal']) ?> XAF</span>
                </div>
                <div class="tw-flex tw-justify-between tw-text-gray-700">
                    <span>Service Fee</span>
                    <span class="tw-font-medium"><?= number_format($order['service_fee']) ?> XAF</span>
                </div>
                <div class="tw-flex tw-justify-between tw-text-gray-700">
                    <span>Delivery Fee</span>
                    <span class="tw-font-medium"><?= number_format($order['delivery_fee']) ?> XAF</span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div class="tw-flex tw-justify-between tw-text-green-600">
                        <span>Discount</span>
                        <span class="tw-font-medium">-<?= number_format($order['discount_amount']) ?> XAF</span>
                    </div>
                <?php endif; ?>
                <div class="tw-border-t tw-border-gray-300 tw-pt-3 tw-flex tw-justify-between tw-text-xl tw-font-bold tw-text-gray-900">
                    <span>Total</span>
                    <span class="tw-text-primary-600"><?= number_format($order['total_amount']) ?> XAF</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4 tw-mb-6 fade-in-up" style="animation-delay: 0.5s;">
            <a href="<?= url('/customer/orders') ?>" 
               class="tw-bg-white tw-border-2 tw-border-gray-300 tw-text-gray-700 tw-px-6 tw-py-3 tw-rounded-xl tw-font-semibold tw-text-center hover:tw-bg-gray-50 tw-transition-colors">
                <i class="fas fa-list tw-mr-2"></i>
                View All Orders
            </a>
            <a href="<?= url('/browse') ?>" 
               class="tw-bg-white tw-border-2 tw-border-gray-300 tw-text-gray-700 tw-px-6 tw-py-3 tw-rounded-xl tw-font-semibold tw-text-center hover:tw-bg-gray-50 tw-transition-colors">
                <i class="fas fa-utensils tw-mr-2"></i>
                Order Again
            </a>
            <a href="<?= url('/') ?>" 
               class="tw-bg-white tw-border-2 tw-border-gray-300 tw-text-gray-700 tw-px-6 tw-py-3 tw-rounded-xl tw-font-semibold tw-text-center hover:tw-bg-gray-50 tw-transition-colors">
                <i class="fas fa-home tw-mr-2"></i>
                Go Home
            </a>
        </div>

        <!-- Help Section -->
        <div class="tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-600 tw-rounded-2xl tw-shadow-lg tw-p-6 tw-text-white tw-text-center fade-in-up" style="animation-delay: 0.6s;">
            <h3 class="tw-text-xl tw-font-bold tw-mb-2">Need Help?</h3>
            <p class="tw-mb-4 tw-opacity-90">Our support team is here to assist you</p>
            <div class="tw-flex tw-flex-wrap tw-justify-center tw-gap-4">
                <a href="tel:+237123456789" class="tw-bg-white tw-text-blue-600 tw-px-6 tw-py-2 tw-rounded-lg tw-font-semibold hover:tw-bg-blue-50 tw-transition-colors">
                    <i class="fas fa-phone tw-mr-2"></i>
                    Call Support
                </a>
                <a href="mailto:support@time2eat.cm" class="tw-bg-white tw-text-blue-600 tw-px-6 tw-py-2 tw-rounded-lg tw-font-semibold hover:tw-bg-blue-50 tw-transition-colors">
                    <i class="fas fa-envelope tw-mr-2"></i>
                    Email Us
                </a>
            </div>
        </div>

    </div>

    <script>
        // Auto-refresh page after 30 seconds to show updated status
        setTimeout(() => {
            window.location.href = '<?= url('/customer/orders/' . $order['id'] . '/track') ?>';
        }, 30000);
    </script>

</body>
</html>

