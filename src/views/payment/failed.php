<?php
/**
 * Payment Failed Page
 * Shown when payment fails or is cancelled
 */

$transactionId = $transactionId ?? '';
$status = $status ?? '';
$error = $error ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Payment Failed - Time2Eat') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="tw-bg-gray-50 tw-min-h-screen tw-flex tw-items-center tw-justify-center">
    <div class="tw-max-w-md tw-w-full tw-mx-4">
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-p-8 tw-text-center">
            <!-- Error Icon -->
            <div class="tw-w-20 tw-h-20 tw-bg-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
                <i class="fas fa-times tw-text-3xl tw-text-red-600"></i>
            </div>

            <!-- Error Message -->
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-4">Payment Failed</h1>
            <p class="tw-text-gray-600 tw-mb-6">
                We're sorry, but your payment could not be processed. Please try again or contact support if the problem persists.
            </p>

            <!-- Error Details -->
            <?php if (!empty($error) || !empty($status)): ?>
            <div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-p-4 tw-mb-6">
                <?php if (!empty($error)): ?>
                <p class="tw-text-sm tw-text-red-600 tw-mb-1">Error</p>
                <p class="tw-text-sm tw-text-red-800"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($status)): ?>
                <p class="tw-text-sm tw-text-red-600 tw-mb-1">Status</p>
                <p class="tw-text-sm tw-text-red-800"><?= htmlspecialchars($status) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Transaction Details -->
            <?php if (!empty($transactionId)): ?>
            <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-mb-6">
                <p class="tw-text-sm tw-text-gray-500 tw-mb-1">Transaction ID</p>
                <p class="tw-font-mono tw-text-sm tw-text-gray-900"><?= htmlspecialchars($transactionId) ?></p>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="tw-space-y-3">
                <button onclick="retryPayment()" 
                        class="tw-w-full tw-bg-blue-600 tw-text-white tw-py-3 tw-px-6 tw-rounded-xl tw-font-semibold hover:tw-bg-blue-700 tw-transition-colors">
                    Try Again
                </button>
                
                <a href="<?= url('/customer/orders') ?>" 
                   class="tw-w-full tw-bg-gray-100 tw-text-gray-700 tw-py-3 tw-px-6 tw-rounded-xl tw-font-semibold hover:tw-bg-gray-200 tw-transition-colors tw-inline-block">
                    View My Orders
                </a>
                
                <a href="<?= url('/') ?>" 
                   class="tw-w-full tw-bg-gray-100 tw-text-gray-700 tw-py-3 tw-px-6 tw-rounded-xl tw-font-semibold hover:tw-bg-gray-200 tw-transition-colors tw-inline-block">
                    Continue Shopping
                </a>
            </div>

            <!-- Support Info -->
            <div class="tw-mt-8 tw-pt-6 tw-border-t tw-border-gray-200">
                <p class="tw-text-xs tw-text-gray-500 tw-mb-2">
                    Still having trouble? Our support team is here to help.
                </p>
                <div class="tw-flex tw-justify-center tw-space-x-4">
                    <a href="mailto:support@time2eat.org" 
                       class="tw-text-blue-600 hover:tw-underline tw-text-sm">
                        <i class="fas fa-envelope tw-mr-1"></i>
                        Email Support
                    </a>
                    <a href="tel:+237674460261" 
                       class="tw-text-blue-600 hover:tw-underline tw-text-sm">
                        <i class="fas fa-phone tw-mr-1"></i>
                        Call Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function retryPayment() {
            // Get the order ID from URL or session
            const urlParams = new URLSearchParams(window.location.search);
            const orderId = urlParams.get('order_id');
            
            if (orderId) {
                // Redirect to checkout page with order ID
                window.location.href = '<?= url('/checkout') ?>?order_id=' + orderId;
            } else {
                // Redirect to cart page
                window.location.href = '<?= url('/cart') ?>';
            }
        }

        // Auto-redirect to cart after 15 seconds
        setTimeout(function() {
            window.location.href = '<?= url('/cart') ?>';
        }, 15000);
    </script>
</body>
</html>
