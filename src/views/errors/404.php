<?php
/**
 * 404 Error Page
 */
?>

<div class="tw-min-h-screen tw-flex tw-items-center tw-justify-center tw-bg-gradient-to-br tw-from-primary-50 tw-to-secondary-50">
    <div class="tw-container tw-mx-auto tw-px-4 tw-text-center">
        <div class="tw-max-w-2xl tw-mx-auto">
            <!-- Error Illustration -->
            <div class="tw-mb-8">
                <div class="tw-w-32 tw-h-32 tw-mx-auto tw-mb-6 tw-bg-gradient-to-br tw-from-primary-500 tw-to-secondary-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="search" class="tw-w-16 tw-h-16 tw-text-white"></i>
                </div>
            </div>
            
            <!-- Error Message -->
            <h1 class="tw-text-6xl tw-font-bold tw-text-gray-800 tw-mb-4">404</h1>
            <h2 class="tw-text-3xl tw-font-bold tw-text-gray-700 tw-mb-6">Page Not Found</h2>
            <p class="tw-text-xl tw-text-gray-600 tw-mb-8">
                Sorry, we couldn't find the page you're looking for. 
                The page might have been moved, deleted, or you entered the wrong URL.
            </p>
            
            <!-- Action Buttons -->
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-justify-center">
                <a href="<?= url('/') ?>" class="tw-btn-primary tw-text-lg tw-px-8 tw-py-4">
                    <i data-feather="home" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Go Home
                </a>
                <a href="<?= url('/browse') ?>" class="tw-btn-outline tw-text-lg tw-px-8 tw-py-4">
                    <i data-feather="utensils" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Browse Restaurants
                </a>
            </div>
            
            <!-- Search Suggestion -->
            <div class="tw-mt-12">
                <p class="tw-text-gray-600 tw-mb-4">Or search for what you're looking for:</p>
                <form action="<?= url('/browse') ?>" method="GET" class="tw-max-w-md tw-mx-auto">
                    <div class="tw-flex tw-gap-2">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search restaurants or dishes..." 
                            class="tw-flex-1 tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent"
                        >
                        <button type="submit" class="tw-btn-primary tw-px-6">
                            <i data-feather="search" class="tw-w-5 tw-h-5"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
