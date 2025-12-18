<?php
$title = $title ?? 'Order Not Found - Time2Eat';
$tracking_code = $tracking_code ?? '';
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
    <!-- Header -->
    <header class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-flex tw-justify-between tw-items-center tw-py-4">
                <div class="tw-flex tw-items-center">
                    <a href="/" class="tw-flex tw-items-center">
                        <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                            <i data-feather="zap" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <h1 class="tw-ml-3 tw-text-xl tw-font-bold tw-text-gray-900">Time2Eat</h1>
                    </a>
                </div>
                <nav class="tw-flex tw-items-center tw-space-x-4">
                    <a href="/track" class="tw-text-gray-600 hover:tw-text-gray-900 tw-transition-colors">
                        <i data-feather="arrow-left" class="tw-h-5 tw-w-5 tw-inline tw-mr-1"></i>
                        Back to Tracking
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="tw-max-w-4xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-12">
        <!-- Not Found Section -->
        <div class="tw-text-center tw-mb-12">
            <div class="tw-mx-auto tw-h-24 tw-w-24 tw-bg-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-6">
                <i data-feather="search" class="tw-h-12 tw-w-12 tw-text-red-500"></i>
            </div>
            <h1 class="tw-text-4xl tw-font-bold tw-text-gray-900 tw-mb-4">Order Not Found</h1>
            <p class="tw-text-xl tw-text-gray-600 tw-max-w-2xl tw-mx-auto tw-mb-6">
                We couldn't find an order with the tracking code you provided.
            </p>
            
            <?php if (!empty($tracking_code)): ?>
                <div class="tw-bg-gray-100 tw-rounded-lg tw-p-4 tw-inline-block tw-mb-6">
                    <p class="tw-text-sm tw-text-gray-600 tw-mb-1">Tracking Code Searched:</p>
                    <p class="tw-text-lg tw-font-mono tw-text-gray-900"><?= e($tracking_code) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Possible Reasons -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-8 tw-mb-8">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-6">Possible Reasons</h2>
            
            <div class="tw-space-y-4">
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0">
                        <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-yellow-500 tw-mt-0.5"></i>
                    </div>
                    <div>
                        <h3 class="tw-font-medium tw-text-gray-900">Incorrect Tracking Code</h3>
                        <p class="tw-text-sm tw-text-gray-600">Please double-check the tracking code. It should be in the format TRK followed by 11 digits (e.g., TRK24011200123).</p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0">
                        <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-blue-500 tw-mt-0.5"></i>
                    </div>
                    <div>
                        <h3 class="tw-font-medium tw-text-gray-900">Order Too Recent</h3>
                        <p class="tw-text-sm tw-text-gray-600">If you just placed your order, it may take a few minutes for tracking information to become available.</p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0">
                        <i data-feather="calendar" class="tw-h-5 tw-w-5 tw-text-green-500 tw-mt-0.5"></i>
                    </div>
                    <div>
                        <h3 class="tw-font-medium tw-text-gray-900">Order Too Old</h3>
                        <p class="tw-text-sm tw-text-gray-600">Tracking information is only available for orders placed within the last 30 days.</p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0">
                        <i data-feather="x-circle" class="tw-h-5 tw-w-5 tw-text-red-500 tw-mt-0.5"></i>
                    </div>
                    <div>
                        <h3 class="tw-font-medium tw-text-gray-900">Cancelled Order</h3>
                        <p class="tw-text-sm tw-text-gray-600">If your order was cancelled, tracking information may no longer be available.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Try Again Form -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-8 tw-mb-8">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-6">Try Again</h2>
            
            <form id="retry-form" class="tw-space-y-4">
                <div>
                    <label for="tracking-code" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Enter Tracking Code
                    </label>
                    <div class="tw-relative">
                        <input 
                            type="text" 
                            id="tracking-code" 
                            name="tracking_code"
                            placeholder="TRK24011200123"
                            value="<?= e($tracking_code) ?>"
                            class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent"
                            required
                        >
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-pr-3">
                            <i data-feather="search" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="tw-w-full tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-text-white tw-py-3 tw-px-6 tw-rounded-lg tw-font-medium hover:tw-from-orange-600 hover:tw-to-red-600 tw-transition-all tw-duration-200"
                >
                    <i data-feather="search" class="tw-h-5 tw-w-5 tw-inline tw-mr-2"></i>
                    Search Again
                </button>
            </form>
        </div>

        <!-- Alternative Actions -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
            <!-- Check Orders -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                <div class="tw-flex tw-items-center tw-mb-4">
                    <div class="tw-h-10 tw-w-10 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                        <i data-feather="list" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                    </div>
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Check Your Orders</h3>
                </div>
                <p class="tw-text-gray-600 tw-mb-4">
                    If you have an account, you can view all your orders and their tracking information in your dashboard.
                </p>
                <a href="/login" class="tw-inline-flex tw-items-center tw-text-blue-600 hover:tw-text-blue-700 tw-font-medium">
                    Login to Your Account
                    <i data-feather="arrow-right" class="tw-h-4 tw-w-4 tw-ml-1"></i>
                </a>
            </div>

            <!-- Contact Support -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                <div class="tw-flex tw-items-center tw-mb-4">
                    <div class="tw-h-10 tw-w-10 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                        <i data-feather="help-circle" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                    </div>
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Need Help?</h3>
                </div>
                <p class="tw-text-gray-600 tw-mb-4">
                    Our support team is here to help you track your order and answer any questions you may have.
                </p>
                <div class="tw-space-y-2">
                    <a href="/contact" class="tw-block tw-text-green-600 hover:tw-text-green-700 tw-font-medium">
                        <i data-feather="message-circle" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Contact Support
                    </a>
                    <a href="tel:+237123456789" class="tw-block tw-text-green-600 hover:tw-text-green-700 tw-font-medium">
                        <i data-feather="phone" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Call: +237 123 456 789
                    </a>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="tw-mt-12 tw-bg-white tw-rounded-lg tw-shadow-md tw-p-8">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-6">Frequently Asked Questions</h2>
            
            <div class="tw-space-y-6">
                <div>
                    <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Where can I find my tracking code?</h3>
                    <p class="tw-text-gray-600">Your tracking code is sent to you via SMS and email when your order is confirmed. You can also find it in your order history if you have an account.</p>
                </div>

                <div>
                    <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">How long does tracking information stay available?</h3>
                    <p class="tw-text-gray-600">Tracking information is available for 30 days after your order is placed. After that, the information is archived.</p>
                </div>

                <div>
                    <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">What if my order shows as delivered but I didn't receive it?</h3>
                    <p class="tw-text-gray-600">Please contact our support team immediately. We'll investigate the issue and work with you to resolve it quickly.</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize Feather icons
        feather.replace();

        // Handle retry form submission
        document.getElementById('retry-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const trackingCode = document.getElementById('tracking-code').value.trim();
            
            if (!trackingCode) {
                alert('Please enter a tracking code');
                return;
            }

            // Redirect to tracking page
            window.location.href = `/track?code=${encodeURIComponent(trackingCode)}`;
        });

        // Auto-format tracking code input
        document.getElementById('tracking-code').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Format as TRK + 8 digits + 3 digits
            if (value.length > 3 && !value.startsWith('TRK')) {
                value = 'TRK' + value.substring(3);
            }
            
            if (value.length > 14) {
                value = value.substring(0, 14);
            }
            
            e.target.value = value;
        });

        // Auto-focus on tracking code input
        document.getElementById('tracking-code').focus();
        document.getElementById('tracking-code').select();
    </script>
</body>
</html>
