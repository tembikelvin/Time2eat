<?php
/**
 * Registration Pending Approval Page
 * Shown when a user registers as vendor or rider and is awaiting admin approval
 */
$title = $title ?? 'Application Pending - Time2Eat';
$role = $role ?? 'vendor';
$email = $email ?? '';
$firstName = $firstName ?? '';

// Include African patterns component
require_once __DIR__ . '/../components/african-patterns.php';
?>

<div class="tw-min-h-screen tw-bg-gradient-to-br tw-from-orange-500 tw-via-red-600 tw-to-pink-600 tw-flex tw-items-center tw-justify-center tw-py-8 tw-px-4 tw-relative tw-overflow-hidden">
    <!-- African Art Background -->
    <div class="tw-absolute tw-inset-0 african-pattern-zigzag tw-opacity-10"></div>
    <div class="tw-absolute tw-inset-0" aria-hidden="true">
        <div class="african-corner-tl" style="opacity: 0.2;"></div>
        <div class="african-corner-br" style="opacity: 0.2;"></div>
    </div>

    <div class="tw-max-w-lg tw-w-full tw-relative tw-z-10">
        <!-- Header with Logo -->
        <div class="tw-text-center tw-mb-8">
            <a href="<?= url('/') ?>" class="tw-inline-flex tw-items-center tw-gap-3 tw-mb-4">
                <div class="tw-w-12 tw-h-12 sm:tw-w-14 sm:tw-h-14 tw-bg-white tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-shadow-2xl">
                    <svg class="tw-w-7 tw-h-7 sm:tw-w-8 sm:tw-h-8 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="tw-text-2xl sm:tw-text-3xl tw-font-black tw-text-white">
                    Time<span class="tw-text-yellow-300">2</span>Eat
                </span>
            </a>
        </div>

        <!-- Pending Approval Card -->
        <div class="tw-bg-white tw-rounded-3xl tw-shadow-2xl tw-p-6 sm:tw-p-8 tw-mb-6">
            <!-- Success Icon -->
            <div class="tw-text-center tw-mb-6">
                <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-16 tw-h-16 tw-bg-yellow-100 tw-rounded-full tw-mb-4">
                    <svg class="tw-w-8 tw-h-8 tw-text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-text-gray-900 tw-mb-2">
                    Application Received!
                </h1>
                <p class="tw-text-gray-600 tw-text-sm sm:tw-text-base">
                    Your <?= ucfirst($role) ?> application is pending admin review
                </p>
            </div>

            <!-- Details -->
            <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 sm:tw-p-6 tw-mb-6 tw-space-y-4">
                <div class="tw-flex tw-items-start tw-gap-3">
                    <div class="tw-flex-shrink-0 tw-mt-1">
                        <svg class="tw-w-5 tw-h-5 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900">What happens next?</p>
                        <p class="tw-text-sm tw-text-gray-600 tw-mt-1">
                            Our admin team will review your application and verify your information. This typically takes 1-2 business days.
                        </p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-gap-3">
                    <div class="tw-flex-shrink-0 tw-mt-1">
                        <svg class="tw-w-5 tw-h-5 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900">We'll notify you by email</p>
                        <p class="tw-text-sm tw-text-gray-600 tw-mt-1">
                            <?= htmlspecialchars($email) ?>
                        </p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-gap-3">
                    <div class="tw-flex-shrink-0 tw-mt-1">
                        <svg class="tw-w-5 tw-h-5 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900">Once approved</p>
                        <p class="tw-text-sm tw-text-gray-600 tw-mt-1">
                            You'll be able to log in and start using your <?= ucfirst($role) ?> account immediately.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Application Type Badge -->
            <div class="tw-mb-6">
                <p class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide tw-mb-2">Application Type</p>
                <div class="tw-inline-block tw-bg-gradient-to-r tw-from-orange-100 tw-to-red-100 tw-border tw-border-orange-300 tw-rounded-full tw-px-4 tw-py-2">
                    <span class="tw-text-sm tw-font-semibold tw-text-orange-700">
                        <?php if ($role === 'vendor'): ?>
                            🏪 Restaurant Owner
                        <?php elseif ($role === 'rider'): ?>
                            🚴 Delivery Rider
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="tw-space-y-3">
                <a href="<?= url('/') ?>" class="tw-block tw-w-full tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-font-semibold tw-py-3 tw-px-4 tw-rounded-xl tw-text-center tw-transition-all hover:tw-shadow-lg hover:tw-scale-105">
                    Return to Home
                </a>
                <a href="<?= url('/login') ?>" class="tw-block tw-w-full tw-bg-white tw-border-2 tw-border-gray-300 tw-text-gray-700 tw-font-semibold tw-py-3 tw-px-4 tw-rounded-xl tw-text-center tw-transition-all hover:tw-bg-gray-50">
                    Go to Login
                </a>
            </div>

            <!-- Help Text -->
            <div class="tw-mt-6 tw-pt-6 tw-border-t tw-border-gray-200">
                <p class="tw-text-xs tw-text-gray-500 tw-text-center">
                    Have questions? <a href="<?= url('/contact') ?>" class="tw-text-orange-600 tw-font-semibold hover:tw-underline">Contact our support team</a>
                </p>
            </div>
        </div>

        <!-- Info Box -->
        <div class="tw-bg-white/10 tw-backdrop-blur-sm tw-rounded-2xl tw-p-4 tw-border tw-border-white/20">
            <p class="tw-text-sm tw-text-white/90 tw-text-center">
                ✨ Thank you for joining Time2Eat! We're excited to have you on board.
            </p>
        </div>
    </div>
</div>

<script>
    // Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>

