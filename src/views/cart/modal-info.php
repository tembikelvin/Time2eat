<?php
// Load helper functions
require_once __DIR__ . '/../../helpers/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Time2Eat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'tw-',
        }
    </script>
</head>
<body class="tw-bg-gray-50 tw-min-h-screen tw-flex tw-items-center tw-justify-center tw-p-4">
    <div class="tw-max-w-md tw-w-full tw-bg-white tw-rounded-xl tw-shadow-lg tw-p-8 tw-text-center">
        <!-- Cart Icon -->
        <div class="tw-w-20 tw-h-20 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
            <svg class="tw-w-10 tw-h-10 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9m-9 0V19a2 2 0 002 2h7a2 2 0 002-2v-.5"></path>
            </svg>
        </div>
        
        <!-- Title -->
        <h1 class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mb-4">
            Your Cart is Now a Modal!
        </h1>
        
        <!-- Description -->
        <p class="tw-text-gray-600 tw-mb-6 tw-leading-relaxed">
            We've streamlined your shopping experience! Your cart is now accessible as a convenient sidebar modal from any page. No more separate cart pages to navigate.
        </p>
        
        <!-- Features -->
        <div class="tw-space-y-3 tw-mb-8">
            <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-700">
                <svg class="tw-w-5 tw-h-5 tw-text-green-500 tw-mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Access your cart from anywhere</span>
            </div>
            <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-700">
                <svg class="tw-w-5 tw-h-5 tw-text-green-500 tw-mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Faster checkout process</span>
            </div>
            <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-700">
                <svg class="tw-w-5 tw-h-5 tw-text-green-500 tw-mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Continue browsing while managing cart</span>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="tw-space-y-3">
            <button 
                onclick="openCartAndRedirect()" 
                class="tw-w-full tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-text-white tw-py-3 tw-px-6 tw-rounded-lg tw-font-semibold hover:tw-from-orange-600 hover:tw-to-red-600 tw-transition-all tw-duration-200"
            >
                Open Cart Modal
            </button>
            
            <a 
                href="<?= url('/browse') ?>" 
                class="tw-block tw-w-full tw-bg-gray-100 tw-text-gray-700 tw-py-3 tw-px-6 tw-rounded-lg tw-font-medium hover:tw-bg-gray-200 tw-transition-colors tw-duration-200"
            >
                Browse Restaurants
            </a>
        </div>
        
        <!-- Help Text -->
        <p class="tw-text-xs tw-text-gray-500 tw-mt-6">
            Look for the cart icon <svg class="tw-inline tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13"></path></svg> in the top navigation
        </p>
    </div>

    <script>
        function openCartAndRedirect() {
            // Redirect to browse page with cart modal open
            window.location.href = '<?= url('/browse?cart=open') ?>';
        }
        
        // Auto-redirect after 5 seconds if user doesn't interact
        setTimeout(() => {
            if (document.visibilityState === 'visible') {
                openCartAndRedirect();
            }
        }, 5000);
    </script>
</body>
</html>
