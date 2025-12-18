<?php
$title = $title ?? 'Track Your Order - Time2Eat';
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
                    <a href="<?= url('/') ?>" class="tw-flex tw-items-center">
                        <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                            <i data-feather="zap" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <h1 class="tw-ml-3 tw-text-xl tw-font-bold tw-text-gray-900">Time2Eat</h1>
                    </a>
                </div>
                <nav class="tw-flex tw-items-center tw-space-x-4">
                    <a href="<?= url('/browse') ?>" class="tw-text-gray-600 hover:tw-text-gray-900 tw-transition-colors">Browse</a>
                    <a href="<?= url('/orders') ?>" class="tw-text-gray-600 hover:tw-text-gray-900 tw-transition-colors">Orders</a>
                    <a href="<?= url('/login') ?>" class="tw-bg-primary-500 hover:tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-transition-colors">Login</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="tw-max-w-4xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-12">
        <!-- Hero Section -->
        <div class="tw-text-center tw-mb-12">
            <div class="tw-mx-auto tw-h-24 tw-w-24 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-6">
                <i data-feather="map-pin" class="tw-h-12 tw-w-12 tw-text-white"></i>
            </div>
            <h1 class="tw-text-4xl tw-font-bold tw-text-gray-900 tw-mb-4">Track Your Order</h1>
            <p class="tw-text-xl tw-text-gray-600 tw-max-w-2xl tw-mx-auto">
                Enter your tracking code to see real-time updates on your order's journey from restaurant to your doorstep.
            </p>
        </div>

        <!-- Tracking Form -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-8 tw-mb-8">
            <form id="tracking-form" class="tw-space-y-6">
                <div>
                    <label for="tracking-code" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Tracking Code
                    </label>
                    <div class="tw-relative">
                        <input 
                            type="text" 
                            id="tracking-code" 
                            name="tracking_code"
                            placeholder="Enter your tracking code (e.g., TRK240112001)"
                            class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent"
                            required
                        >
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-pr-3">
                            <i data-feather="search" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                    <p class="tw-text-sm tw-text-gray-500 tw-mt-2">
                        You can find your tracking code in your order confirmation email or SMS.
                    </p>
                </div>

                <button 
                    type="submit" 
                    class="tw-w-full tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-text-white tw-py-3 tw-px-6 tw-rounded-lg tw-font-medium tw-text-lg hover:tw-from-orange-600 hover:tw-to-red-600 tw-transition-all tw-duration-200 tw-disabled:opacity-50 tw-disabled:cursor-not-allowed"
                    id="track-button"
                >
                    <i data-feather="map-pin" class="tw-h-5 tw-w-5 tw-inline tw-mr-2"></i>
                    Track Order
                </button>
            </form>

            <!-- Error Message -->
            <div id="error-message" class="tw-mt-4 tw-p-4 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg tw-hidden">
                <div class="tw-flex tw-items-center">
                    <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-red-500 tw-mr-2"></i>
                    <span class="tw-text-red-700" id="error-text"></span>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-8">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-6 tw-text-center">How Order Tracking Works</h2>
            
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6">
                <!-- Step 1 -->
                <div class="tw-text-center">
                    <div class="tw-mx-auto tw-h-16 tw-w-16 tw-bg-primary-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <i data-feather="shopping-bag" class="tw-h-8 tw-w-8 tw-text-primary-600"></i>
                    </div>
                    <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Order Placed</h3>
                    <p class="tw-text-sm tw-text-gray-600">Your order is confirmed and sent to the restaurant</p>
                </div>

                <!-- Step 2 -->
                <div class="tw-text-center">
                    <div class="tw-mx-auto tw-h-16 tw-w-16 tw-bg-primary-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <i data-feather="chef-hat" class="tw-h-8 tw-w-8 tw-text-primary-600"></i>
                    </div>
                    <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Preparing</h3>
                    <p class="tw-text-sm tw-text-gray-600">The restaurant is preparing your delicious meal</p>
                </div>

                <!-- Step 3 -->
                <div class="tw-text-center">
                    <div class="tw-mx-auto tw-h-16 tw-w-16 tw-bg-primary-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <i data-feather="truck" class="tw-h-8 tw-w-8 tw-text-primary-600"></i>
                    </div>
                    <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Out for Delivery</h3>
                    <p class="tw-text-sm tw-text-gray-600">Your rider is on the way with your order</p>
                </div>

                <!-- Step 4 -->
                <div class="tw-text-center">
                    <div class="tw-mx-auto tw-h-16 tw-w-16 tw-bg-primary-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <i data-feather="check-circle" class="tw-h-8 tw-w-8 tw-text-primary-600"></i>
                    </div>
                    <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Delivered</h3>
                    <p class="tw-text-sm tw-text-gray-600">Enjoy your meal! Don't forget to rate your experience</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="tw-mt-12 tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-8">
            <div class="tw-text-center">
                <div class="tw-mx-auto tw-h-12 tw-w-12 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                    <i data-feather="zap" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                </div>
                <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Real-Time Updates</h3>
                <p class="tw-text-sm tw-text-gray-600">Get live updates on your order status and rider location</p>
            </div>

            <div class="tw-text-center">
                <div class="tw-mx-auto tw-h-12 tw-w-12 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                    <i data-feather="map" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                </div>
                <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Live Map Tracking</h3>
                <p class="tw-text-sm tw-text-gray-600">Watch your rider's journey on an interactive map</p>
            </div>

            <div class="tw-text-center">
                <div class="tw-mx-auto tw-h-12 tw-w-12 tw-bg-purple-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                    <i data-feather="phone" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
                </div>
                <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Direct Contact</h3>
                <p class="tw-text-sm tw-text-gray-600">Call your rider directly if you need to coordinate delivery</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="tw-bg-white tw-border-t tw-border-gray-200 tw-mt-16">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-8">
            <div class="tw-text-center tw-text-gray-600">
                <p>&copy; 2024 Time2Eat. All rights reserved.</p>
                <p class="tw-mt-2">Need help? <a href="<?= url('/contact') ?>" class="tw-text-primary-600 hover:tw-text-primary-700">Contact Support</a></p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Feather icons
        feather.replace();

        // Handle form submission
        document.getElementById('tracking-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const trackingCode = document.getElementById('tracking-code').value.trim();
            const button = document.getElementById('track-button');
            const errorDiv = document.getElementById('error-message');
            
            if (!trackingCode) {
                showError('Please enter a tracking code');
                return;
            }

            // Validate tracking code format
            if (!/^TRK\d{8}\d{3}$/.test(trackingCode)) {
                showError('Invalid tracking code format. Please check and try again.');
                return;
            }

            // Show loading state
            button.disabled = true;
            button.innerHTML = '<i data-feather="loader" class="tw-h-5 tw-w-5 tw-inline tw-mr-2 tw-animate-spin"></i>Tracking...';
            feather.replace();
            hideError();

            // Redirect to tracking page
            window.location.href = `/track?code=${encodeURIComponent(trackingCode)}`;
        });

        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            
            errorText.textContent = message;
            errorDiv.classList.remove('tw-hidden');
            feather.replace();
        }

        function hideError() {
            document.getElementById('error-message').classList.add('tw-hidden');
        }

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
    </script>
</body>
</html>
