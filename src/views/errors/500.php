<?php
/**
 * 500 Error Page
 */
?>

<div class="tw-min-h-screen tw-flex tw-items-center tw-justify-center tw-bg-gradient-to-br tw-from-red-50 tw-to-orange-50">
    <div class="tw-container tw-mx-auto tw-px-4 tw-text-center">
        <div class="tw-max-w-2xl tw-mx-auto">
            <!-- Error Illustration -->
            <div class="tw-mb-8">
                <div class="tw-w-32 tw-h-32 tw-mx-auto tw-mb-6 tw-bg-gradient-to-br tw-from-red-500 tw-to-orange-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="alert-triangle" class="tw-w-16 tw-h-16 tw-text-white"></i>
                </div>
            </div>
            
            <!-- Error Message -->
            <h1 class="tw-text-6xl tw-font-bold tw-text-gray-800 tw-mb-4">500</h1>
            <h2 class="tw-text-3xl tw-font-bold tw-text-gray-700 tw-mb-6">Internal Server Error</h2>
            <p class="tw-text-xl tw-text-gray-600 tw-mb-8">
                Oops! Something went wrong on our end. 
                Our team has been notified and is working to fix the issue.
            </p>
            
            <!-- Action Buttons -->
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-justify-center">
                <a href="/" class="tw-btn-primary tw-text-lg tw-px-8 tw-py-4">
                    <i data-feather="home" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Go Home
                </a>
                <button onclick="window.location.reload()" class="tw-btn-outline tw-text-lg tw-px-8 tw-py-4">
                    <i data-feather="refresh-cw" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Try Again
                </button>
            </div>
            
            <!-- Contact Support -->
            <div class="tw-mt-12 tw-p-6 tw-bg-white tw-rounded-lg tw-shadow-lg">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-800 tw-mb-2">Need Help?</h3>
                <p class="tw-text-gray-600 tw-mb-4">If the problem persists, please contact our support team.</p>
                <a href="mailto:support@time2eat.com" class="tw-text-primary-600 hover:tw-text-primary-700 tw-font-medium">
                    <i data-feather="mail" class="tw-w-4 tw-h-4 tw-mr-1 tw-inline"></i>
                    support@time2eat.com
                </a>
            </div>
        </div>
    </div>
</div>
