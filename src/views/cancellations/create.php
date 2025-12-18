<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Cancel Order - Time2Eat') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center space-x-2">
                    <i class="fas fa-utensils text-red-500 text-2xl"></i>
                    <span class="text-xl font-bold text-gray-800">Time2Eat</span>
                </a>
                
                <nav class="flex items-center space-x-6">
                    <a href="/customer/orders" class="text-gray-600 hover:text-red-500 transition-colors">My Orders</a>
                    <a href="/customer/dashboard" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">Dashboard</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-6">
                <a href="/customer/dashboard" class="hover:text-red-500">Dashboard</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="/customer/orders" class="hover:text-red-500">Orders</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-800">Cancel Order</span>
            </nav>

            <!-- Order Information -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Cancel Order</h1>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                        Order #<?= htmlspecialchars($order['order_number']) ?>
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-3">Order Details</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Restaurant:</span>
                                <span class="text-gray-800"><?= htmlspecialchars($order['restaurant_name']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Date:</span>
                                <span class="text-gray-800"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </div>
                            <div class="flex justify-between font-semibold">
                                <span class="text-gray-600">Total Amount:</span>
                                <span class="text-gray-800"><?= number_format($order['total_amount']) ?> XAF</span>
                            </div>
                        </div>
                    </div>

                    <?php if ($payment): ?>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-3">Payment Information</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Method:</span>
                                    <span class="text-gray-800"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Status:</span>
                                    <span class="px-2 py-1 rounded text-xs font-medium <?= $payment['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= ucfirst($payment['status']) ?>
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Amount Paid:</span>
                                    <span class="text-gray-800"><?= number_format($payment['amount']) ?> XAF</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Cancellation Policy -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-500 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-yellow-800 mb-2">Cancellation Policy</h4>
                            <div class="text-sm text-yellow-700 space-y-1">
                                <?php if ($cancellationInfo['auto_approve']): ?>
                                    <p><i class="fas fa-check text-green-500 mr-2"></i>Your cancellation will be processed immediately.</p>
                                <?php else: ?>
                                    <p><i class="fas fa-clock text-yellow-500 mr-2"></i>Your cancellation request will be reviewed by the restaurant or admin.</p>
                                <?php endif; ?>
                                
                                <?php if ($refundInfo['eligible']): ?>
                                    <p><i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                        You will receive a <?= $refundInfo['percentage'] ?>% refund (<?= number_format($refundInfo['amount']) ?> XAF).
                                    </p>
                                    <p class="text-xs">Refund will be processed to your original payment method within <?= $refundInfo['processing_time'] ?>.</p>
                                <?php else: ?>
                                    <p><i class="fas fa-times text-red-500 mr-2"></i>No refund available for this cancellation.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Form -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Cancellation Request</h2>
                
                <form method="POST" action="<?= url('/cancellations/store') ?>" id="cancellationForm">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    
                    <div class="space-y-6">
                        <!-- Cancellation Reason -->
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for cancellation <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="reason" 
                                id="reason" 
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            >
                                <option value="">Select a reason</option>
                                <option value="changed_mind">Changed my mind</option>
                                <option value="wrong_order">Ordered wrong items</option>
                                <option value="delivery_delay">Delivery taking too long</option>
                                <option value="restaurant_closed">Restaurant is closed</option>
                                <option value="payment_issue">Payment issue</option>
                                <option value="emergency">Personal emergency</option>
                                <option value="duplicate_order">Duplicate order</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Additional Details -->
                        <div>
                            <label for="details" class="block text-sm font-medium text-gray-700 mb-2">
                                Additional details (optional)
                            </label>
                            <textarea 
                                name="details" 
                                id="details"
                                rows="4"
                                maxlength="500"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="Please provide any additional information about your cancellation request..."
                            ></textarea>
                            <div class="text-xs text-gray-500 mt-1">
                                <span id="charCount">0</span>/500 characters
                            </div>
                        </div>

                        <!-- Confirmation Checkbox -->
                        <div class="flex items-start">
                            <input 
                                type="checkbox" 
                                id="confirm" 
                                name="confirm" 
                                required
                                class="mt-1 text-red-500 focus:ring-red-500 border-gray-300 rounded"
                            >
                            <label for="confirm" class="ml-3 text-sm text-gray-700">
                                I understand the cancellation policy and confirm that I want to cancel this order.
                                <?php if (!$cancellationInfo['auto_approve']): ?>
                                    <span class="block text-xs text-gray-500 mt-1">
                                        Note: This request will need to be approved and may take some time to process.
                                    </span>
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                        <a href="/customer/orders" class="text-gray-600 hover:text-gray-800 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Orders
                        </a>
                        
                        <div class="flex items-center space-x-4">
                            <button 
                                type="button" 
                                onclick="window.history.back()"
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                                id="submitBtn"
                            >
                                Submit Cancellation Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- FAQ Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Frequently Asked Questions</h3>
                
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">How long does it take to process a cancellation?</h4>
                        <p class="text-sm text-gray-600">
                            <?php if ($cancellationInfo['auto_approve']): ?>
                                Your cancellation will be processed immediately since your order is still in early stages.
                            <?php else: ?>
                                Cancellation requests are typically reviewed within 15-30 minutes during business hours.
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">When will I receive my refund?</h4>
                        <p class="text-sm text-gray-600">
                            <?php if ($refundInfo['eligible']): ?>
                                Refunds are processed within <?= $refundInfo['processing_time'] ?> to your original payment method.
                            <?php else: ?>
                                Unfortunately, no refund is available for orders at this stage.
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">Can I modify my order instead of cancelling?</h4>
                        <p class="text-sm text-gray-600">
                            Order modifications may be possible if the restaurant hasn't started preparing your food. 
                            Please contact customer support at <a href="tel:+237123456789" class="text-red-500 hover:underline">+237 123 456 789</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Character counter for details textarea
        const detailsTextarea = document.getElementById('details');
        const charCount = document.getElementById('charCount');
        
        detailsTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length;
            charCount.className = length > 450 ? 'text-red-500' : 'text-gray-500';
        });

        // Form submission handling
        document.getElementById('cancellationForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const reason = document.getElementById('reason').value;
            const confirm = document.getElementById('confirm').checked;
            
            if (!reason) {
                e.preventDefault();
                alert('Please select a reason for cancellation.');
                return;
            }
            
            if (!confirm) {
                e.preventDefault();
                alert('Please confirm that you understand the cancellation policy.');
                return;
            }
            
            // Show confirmation dialog
            const confirmMessage = <?= $cancellationInfo['auto_approve'] ? 'true' : 'false' ?> 
                ? 'Your order will be cancelled immediately. Are you sure you want to proceed?'
                : 'Your cancellation request will be submitted for review. Are you sure you want to proceed?';
                
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        });

        // Show/hide additional details based on reason
        document.getElementById('reason').addEventListener('change', function() {
            const detailsContainer = document.getElementById('details').parentElement;
            const label = detailsContainer.querySelector('label');
            
            if (this.value === 'other') {
                label.innerHTML = 'Please specify the reason <span class="text-red-500">*</span>';
                document.getElementById('details').required = true;
            } else {
                label.innerHTML = 'Additional details (optional)';
                document.getElementById('details').required = false;
            }
        });

        // Auto-focus on reason select
        document.getElementById('reason').focus();
    </script>
</body>
</html>
