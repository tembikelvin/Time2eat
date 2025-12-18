<?php
$title = $title ?? 'Payment Failed - Time2Eat';
$orderId = $_GET['order_id'] ?? null;
$error = $_GET['error'] ?? 'Payment was not completed';
$status = $_GET['status'] ?? 'failed';
?>

<!-- Payment Failure Page -->
<div class="tw-min-h-screen tw-bg-gray-50 tw-flex tw-items-center tw-justify-center tw-px-4">
    <div class="tw-max-w-md tw-w-full tw-bg-white tw-rounded-lg tw-shadow-lg tw-p-8">
        <!-- Error Icon -->
        <div class="tw-text-center tw-mb-6">
            <div class="tw-mx-auto tw-w-16 tw-h-16 tw-bg-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                <svg class="tw-w-8 tw-h-8 tw-text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Payment Failed</h1>
            <p class="tw-text-gray-600 tw-mt-2">We couldn't process your payment. Please try again.</p>
        </div>

        <!-- Error Details -->
        <div class="tw-bg-red-50 tw-rounded-lg tw-p-4 tw-mb-6">
            <h3 class="tw-text-sm tw-font-medium tw-text-red-900 tw-mb-2">What happened?</h3>
            <p class="tw-text-sm tw-text-red-800"><?= htmlspecialchars($error) ?></p>
        </div>

        <!-- Order Details -->
        <?php if ($orderId): ?>
        <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-mb-6">
            <h3 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Order Details</h3>
            <div class="tw-space-y-1 tw-text-sm tw-text-gray-600">
                <div class="tw-flex tw-justify-between">
                    <span>Order ID:</span>
                    <span class="tw-font-medium">#<?= htmlspecialchars($orderId) ?></span>
                </div>
                <div class="tw-flex tw-justify-between">
                    <span>Status:</span>
                    <span class="tw-font-medium tw-text-red-600">Payment Pending</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Common Solutions -->
        <div class="tw-bg-yellow-50 tw-rounded-lg tw-p-4 tw-mb-6">
            <h3 class="tw-text-sm tw-font-medium tw-text-yellow-900 tw-mb-2">Common Solutions</h3>
            <ul class="tw-text-sm tw-text-yellow-800 tw-space-y-1">
                <li>• Check your internet connection</li>
                <li>• Ensure you have sufficient funds</li>
                <li>• Try a different payment method</li>
                <li>• Contact your bank if using a card</li>
                <li>• Check your mobile money balance</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="tw-space-y-3">
            <a href="<?= url('/checkout') ?>" 
               class="tw-w-full tw-bg-orange-600 tw-text-white tw-py-3 tw-px-4 tw-rounded-lg tw-font-medium tw-text-center tw-block hover:tw-bg-orange-700 tw-transition-colors">
                Try Payment Again
            </a>
            <a href="<?= url('/customer/orders') ?>" 
               class="tw-w-full tw-bg-gray-100 tw-text-gray-700 tw-py-3 tw-px-4 tw-rounded-lg tw-font-medium tw-text-center tw-block hover:tw-bg-gray-200 tw-transition-colors">
                View My Orders
            </a>
            <a href="<?= url('/browse') ?>" 
               class="tw-w-full tw-bg-gray-100 tw-text-gray-700 tw-py-3 tw-px-4 tw-rounded-lg tw-font-medium tw-text-center tw-block hover:tw-bg-gray-200 tw-transition-colors">
                Continue Shopping
            </a>
        </div>

        <!-- Support Info -->
        <div class="tw-mt-6 tw-text-center">
            <p class="tw-text-xs tw-text-gray-500">
                Still having trouble? Contact us at 
                <a href="mailto:support@time2eat.org" class="tw-text-orange-600 hover:tw-text-orange-700">
                    support@time2eat.org
                </a>
            </p>
        </div>
    </div>
</div>
