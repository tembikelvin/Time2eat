<?php
$title = $title ?? 'Payment Successful - Time2Eat';
$orderId = $_GET['order_id'] ?? null;
$transactionId = $_GET['transaction_id'] ?? null;
$status = $_GET['status'] ?? 'success';
?>

<!-- Payment Success Page -->
<div class="tw-min-h-screen tw-bg-gray-50 tw-flex tw-items-center tw-justify-center tw-px-4">
    <div class="tw-max-w-md tw-w-full tw-bg-white tw-rounded-lg tw-shadow-lg tw-p-8">
        <!-- Success Icon -->
        <div class="tw-text-center tw-mb-6">
            <div class="tw-mx-auto tw-w-16 tw-h-16 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                <svg class="tw-w-8 tw-h-8 tw-text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Payment Successful!</h1>
            <p class="tw-text-gray-600 tw-mt-2">Your order has been confirmed and payment processed.</p>
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
                <?php if ($transactionId): ?>
                <div class="tw-flex tw-justify-between">
                    <span>Transaction ID:</span>
                    <span class="tw-font-medium tw-text-xs"><?= htmlspecialchars($transactionId) ?></span>
                </div>
                <?php endif; ?>
                <div class="tw-flex tw-justify-between">
                    <span>Status:</span>
                    <span class="tw-font-medium tw-text-green-600">Paid</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Next Steps -->
        <div class="tw-bg-blue-50 tw-rounded-lg tw-p-4 tw-mb-6">
            <h3 class="tw-text-sm tw-font-medium tw-text-blue-900 tw-mb-2">What's Next?</h3>
            <ul class="tw-text-sm tw-text-blue-800 tw-space-y-1">
                <li>• You'll receive a confirmation email shortly</li>
                <li>• The restaurant will start preparing your order</li>
                <li>• You can track your order in real-time</li>
                <li>• A rider will be assigned for delivery</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="tw-space-y-3">
            <a href="<?= url('/customer/orders') ?>" 
               class="tw-w-full tw-bg-orange-600 tw-text-white tw-py-3 tw-px-4 tw-rounded-lg tw-font-medium tw-text-center tw-block hover:tw-bg-orange-700 tw-transition-colors">
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
                Need help? Contact us at 
                <a href="mailto:support@time2eat.org" class="tw-text-orange-600 hover:tw-text-orange-700">
                    support@time2eat.org
                </a>
            </p>
        </div>
    </div>
</div>

<script>
// Auto-redirect to orders page after 10 seconds
setTimeout(function() {
    window.location.href = '<?= url('/customer/orders') ?>';
}, 10000);

// Show countdown
let countdown = 10;
const countdownElement = document.createElement('div');
countdownElement.className = 'tw-text-center tw-mt-4 tw-text-sm tw-text-gray-500';
countdownElement.innerHTML = `Redirecting to orders page in <span id="countdown">${countdown}</span> seconds...`;
document.querySelector('.tw-max-w-md').appendChild(countdownElement);

const countdownInterval = setInterval(function() {
    countdown--;
    document.getElementById('countdown').textContent = countdown;
    if (countdown <= 0) {
        clearInterval(countdownInterval);
    }
}, 1000);
</script>