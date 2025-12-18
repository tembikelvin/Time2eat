<?php
$title = $title ?? 'Request Withdrawal - Time2Eat';
$user = $user ?? [];
$affiliate = $affiliate ?? [];
?>

<!DOCTYPE html>
<html lang="en" class="tw-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'tw-',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fff7ed',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="tw-min-h-full tw-bg-gray-50">
    <div class="tw-flex tw-h-screen tw-bg-gray-50">
        <!-- Sidebar -->
        <div class="tw-hidden md:tw-flex tw-flex-col tw-w-64 tw-bg-white tw-shadow-lg">
            <?php include __DIR__ . '/../components/sidebar-content.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="tw-flex-1 tw-flex tw-flex-col tw-overflow-hidden">
            <!-- Header -->
            <header class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center">
                        <button class="tw-p-2 tw-rounded-md tw-text-gray-400 hover:tw-text-gray-500 hover:tw-bg-gray-100 md:tw-hidden" id="mobile-menu-button">
                            <i data-feather="menu" class="tw-h-6 tw-w-6"></i>
                        </button>
                        <h1 class="tw-ml-4 tw-text-2xl tw-font-bold tw-text-gray-900">Request Withdrawal</h1>
                    </div>
                    <a href="/affiliate/dashboard" class="tw-text-primary-600 hover:tw-text-primary-700 tw-font-medium">
                        <i data-feather="arrow-left" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Back to Dashboard
                    </a>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="tw-flex-1 tw-overflow-y-auto tw-p-6">
                <div class="tw-max-w-2xl tw-mx-auto">
                    <!-- Balance Card -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6 tw-mb-6">
                        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Available Balance</h2>
                        <div class="tw-text-center tw-py-4">
                            <p class="tw-text-4xl tw-font-bold tw-text-green-600"><?= number_format($affiliate['available_balance'] ?? 0) ?> XAF</p>
                            <p class="tw-text-sm tw-text-gray-600 tw-mt-2">Minimum withdrawal: 10,000 XAF</p>
                        </div>
                    </div>

                    <!-- Withdrawal Form -->
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-6">Withdrawal Request</h2>
                        
                        <form id="withdrawal-form" class="tw-space-y-6">
                            <!-- Amount -->
                            <div>
                                <label for="amount" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                                    Withdrawal Amount (XAF)
                                </label>
                                <input 
                                    type="number" 
                                    id="amount" 
                                    name="amount"
                                    min="10000"
                                    max="<?= $affiliate['available_balance'] ?? 0 ?>"
                                    step="1000"
                                    class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent"
                                    placeholder="Enter amount (minimum 10,000 XAF)"
                                    required
                                >
                                <p class="tw-text-sm tw-text-gray-500 tw-mt-1">
                                    Available: <?= number_format($affiliate['available_balance'] ?? 0) ?> XAF
                                </p>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3">
                                    Payment Method
                                </label>
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                                    <label class="tw-flex tw-items-center tw-p-4 tw-border tw-border-gray-300 tw-rounded-lg tw-cursor-pointer hover:tw-bg-gray-50 payment-method-option">
                                        <input type="radio" name="payment_method" value="orange_money" class="tw-sr-only">
                                        <div class="tw-flex tw-items-center tw-w-full">
                                            <div class="tw-h-8 tw-w-8 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                                <i data-feather="smartphone" class="tw-h-4 tw-w-4 tw-text-orange-600"></i>
                                            </div>
                                            <span class="tw-font-medium tw-text-gray-900">Orange Money</span>
                                        </div>
                                    </label>

                                    <label class="tw-flex tw-items-center tw-p-4 tw-border tw-border-gray-300 tw-rounded-lg tw-cursor-pointer hover:tw-bg-gray-50 payment-method-option">
                                        <input type="radio" name="payment_method" value="mtn_momo" class="tw-sr-only">
                                        <div class="tw-flex tw-items-center tw-w-full">
                                            <div class="tw-h-8 tw-w-8 tw-bg-yellow-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                                <i data-feather="smartphone" class="tw-h-4 tw-w-4 tw-text-yellow-600"></i>
                                            </div>
                                            <span class="tw-font-medium tw-text-gray-900">MTN MoMo</span>
                                        </div>
                                    </label>

                                    <label class="tw-flex tw-items-center tw-p-4 tw-border tw-border-gray-300 tw-rounded-lg tw-cursor-pointer hover:tw-bg-gray-50 payment-method-option">
                                        <input type="radio" name="payment_method" value="mobile_money" class="tw-sr-only">
                                        <div class="tw-flex tw-items-center tw-w-full">
                                            <div class="tw-h-8 tw-w-8 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                                <i data-feather="smartphone" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                                            </div>
                                            <span class="tw-font-medium tw-text-gray-900">Mobile Money</span>
                                        </div>
                                    </label>

                                    <label class="tw-flex tw-items-center tw-p-4 tw-border tw-border-gray-300 tw-rounded-lg tw-cursor-pointer hover:tw-bg-gray-50 payment-method-option">
                                        <input type="radio" name="payment_method" value="bank_transfer" class="tw-sr-only">
                                        <div class="tw-flex tw-items-center tw-w-full">
                                            <div class="tw-h-8 tw-w-8 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                                <i data-feather="credit-card" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                                            </div>
                                            <span class="tw-font-medium tw-text-gray-900">Bank Transfer</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Payment Details -->
                            <div id="payment-details" class="tw-hidden">
                                <!-- Orange Money Details -->
                                <div id="orange-money-details" class="tw-payment-details tw-hidden">
                                    <h4 class="tw-font-medium tw-text-gray-900 tw-mb-3">Orange Money Details</h4>
                                    <div class="tw-space-y-4">
                                        <div>
                                            <label for="orange-phone" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                                Phone Number
                                            </label>
                                            <input 
                                                type="tel" 
                                                id="orange-phone" 
                                                name="orange_phone"
                                                class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                                placeholder="e.g., 237123456789"
                                            >
                                        </div>
                                        <div>
                                            <label for="orange-name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                                Account Name
                                            </label>
                                            <input 
                                                type="text" 
                                                id="orange-name" 
                                                name="orange_name"
                                                class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                                placeholder="Account holder name"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- MTN MoMo Details -->
                                <div id="mtn-momo-details" class="tw-payment-details tw-hidden">
                                    <h4 class="tw-font-medium tw-text-gray-900 tw-mb-3">MTN MoMo Details</h4>
                                    <div class="tw-space-y-4">
                                        <div>
                                            <label for="mtn-phone" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                                Phone Number
                                            </label>
                                            <input 
                                                type="tel" 
                                                id="mtn-phone" 
                                                name="mtn_phone"
                                                class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                                placeholder="e.g., 237123456789"
                                            >
                                        </div>
                                        <div>
                                            <label for="mtn-name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                                Account Name
                                            </label>
                                            <input 
                                                type="text" 
                                                id="mtn-name" 
                                                name="mtn_name"
                                                class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                                placeholder="Account holder name"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile Money Details -->
                                <div id="mobile-money-details" class="tw-payment-details tw-hidden">
                                    <h4 class="tw-font-medium tw-text-gray-900 tw-mb-3">Mobile Money Details</h4>
                                    <div class="tw-space-y-4">
                                        <div>
                                            <label for="mobile-phone" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                                Phone Number
                                            </label>
                                            <input 
                                                type="tel" 
                                                id="mobile-phone" 
                                                name="mobile_phone"
                                                class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                                placeholder="e.g., 237123456789"
                                            >
                                        </div>
                                        <div>
                                            <label for="mobile-provider" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                                Provider
                                            </label>
                                            <select 
                                                id="mobile-provider" 
                                                name="mobile_provider"
                                                class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                            >
                                                <option value="">Select provider</option>
                                                <option value="orange">Orange</option>
                                                <option value="mtn">MTN</option>
                                                <option value="camtel">Camtel</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Transfer Details -->
                                <div id="bank-transfer-details" class="tw-payment-details tw-hidden">
                                    <h4 class="tw-font-medium tw-text-gray-900 tw-mb-3">Bank Transfer Details</h4>
                                    <div class="tw-space-y-4">
                                        <div>
                                            <label for="bank-name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                                Bank Name
                                            </label>
                                            <input 
                                                type="text" 
                                                id="bank-name" 
                                                name="bank_name"
                                                class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                                placeholder="e.g., Afriland First Bank"
                                            >
                                        </div>
                                        <div>
                                            <label for="account-number" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                                Account Number
                                            </label>
                                            <input 
                                                type="text" 
                                                id="account-number" 
                                                name="account_number"
                                                class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                                placeholder="Account number"
                                            >
                                        </div>
                                        <div>
                                            <label for="account-name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                                Account Name
                                            </label>
                                            <input 
                                                type="text" 
                                                id="account-name" 
                                                name="account_name"
                                                class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                                placeholder="Account holder name"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="tw-flex tw-space-x-4">
                                <button 
                                    type="submit" 
                                    class="tw-flex-1 tw-bg-primary-500 hover:tw-bg-primary-600 tw-text-white tw-py-3 tw-px-6 tw-rounded-lg tw-font-medium tw-transition-colors tw-disabled:opacity-50 tw-disabled:cursor-not-allowed"
                                    id="submit-button"
                                >
                                    <i data-feather="send" class="tw-h-5 tw-w-5 tw-inline tw-mr-2"></i>
                                    Submit Withdrawal Request
                                </button>
                                <a href="/affiliate/dashboard" class="tw-flex-1 tw-bg-gray-300 hover:tw-bg-gray-400 tw-text-gray-700 tw-py-3 tw-px-6 tw-rounded-lg tw-font-medium tw-transition-colors tw-text-center">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Important Notes -->
                    <div class="tw-mt-6 tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg tw-p-4">
                        <h3 class="tw-font-medium tw-text-yellow-800 tw-mb-2">Important Notes:</h3>
                        <ul class="tw-text-sm tw-text-yellow-700 tw-space-y-1">
                            <li>• Minimum withdrawal amount is 10,000 XAF</li>
                            <li>• Withdrawals are processed within 1-3 business days</li>
                            <li>• You can have maximum 3 pending withdrawal requests</li>
                            <li>• All withdrawal requests require admin approval</li>
                            <li>• Processing fees may apply depending on payment method</li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize Feather icons
        feather.replace();

        // Payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Update visual selection
                document.querySelectorAll('.payment-method-option').forEach(option => {
                    option.classList.remove('tw-border-primary-500', 'tw-bg-primary-50');
                    option.classList.add('tw-border-gray-300');
                });
                
                this.closest('.payment-method-option').classList.remove('tw-border-gray-300');
                this.closest('.payment-method-option').classList.add('tw-border-primary-500', 'tw-bg-primary-50');
                
                // Show payment details
                showPaymentDetails(this.value);
            });
        });

        function showPaymentDetails(method) {
            // Hide all payment details
            document.querySelectorAll('.tw-payment-details').forEach(detail => {
                detail.classList.add('tw-hidden');
            });
            
            // Show payment details container
            document.getElementById('payment-details').classList.remove('tw-hidden');
            
            // Show specific payment details
            const detailsId = method.replace('_', '-') + '-details';
            const detailsElement = document.getElementById(detailsId);
            if (detailsElement) {
                detailsElement.classList.remove('tw-hidden');
            }
        }

        // Form submission
        document.getElementById('withdrawal-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Validate form
            if (!validateForm(data)) {
                return;
            }
            
            // Prepare payment details
            const paymentDetails = getPaymentDetails(data);
            
            const requestData = {
                amount: parseFloat(data.amount),
                payment_method: data.payment_method,
                payment_details: paymentDetails
            };
            
            // Submit request
            submitWithdrawalRequest(requestData);
        });

        function validateForm(data) {
            if (!data.amount || parseFloat(data.amount) < 10000) {
                showNotification('Minimum withdrawal amount is 10,000 XAF', 'error');
                return false;
            }
            
            if (!data.payment_method) {
                showNotification('Please select a payment method', 'error');
                return false;
            }
            
            return true;
        }

        function getPaymentDetails(data) {
            const method = data.payment_method;
            
            switch (method) {
                case 'orange_money':
                    return {
                        phone: data.orange_phone,
                        name: data.orange_name
                    };
                case 'mtn_momo':
                    return {
                        phone: data.mtn_phone,
                        name: data.mtn_name
                    };
                case 'mobile_money':
                    return {
                        phone: data.mobile_phone,
                        provider: data.mobile_provider
                    };
                case 'bank_transfer':
                    return {
                        bank_name: data.bank_name,
                        account_number: data.account_number,
                        account_name: data.account_name
                    };
                default:
                    return {};
            }
        }

        function submitWithdrawalRequest(data) {
            const submitButton = document.getElementById('submit-button');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i data-feather="loader" class="tw-h-5 tw-w-5 tw-inline tw-mr-2 tw-animate-spin"></i>Processing...';
            feather.replace();
            
            fetch('<?= url('/affiliate/request-withdrawal') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('Withdrawal request submitted successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '<?= url('/affiliate/withdrawals') ?>';
                    }, 2000);
                } else {
                    showNotification(result.message || 'Failed to submit withdrawal request', 'error');
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i data-feather="send" class="tw-h-5 tw-w-5 tw-inline tw-mr-2"></i>Submit Withdrawal Request';
                    feather.replace();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i data-feather="send" class="tw-h-5 tw-w-5 tw-inline tw-mr-2"></i>Submit Withdrawal Request';
                feather.replace();
            });
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-lg tw-shadow-lg tw-z-50 ${
                type === 'success' ? 'tw-bg-green-500' : type === 'error' ? 'tw-bg-red-500' : 'tw-bg-blue-500'
            } tw-text-white`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    </script>
</body>
</html>
