<?php
/**
 * Register Page - Creative Mobile First Design
 * Step-by-step registration with smooth UX
 */
$title = $title ?? 'Register - Time2Eat';
$page = $page ?? 'register';
$errors = $errors ?? [];
$old = $old ?? [];
$error = $error ?? '';

// Load auth helpers to read admin settings
require_once __DIR__ . '/../../helpers/auth_helpers.php';
// Determine whether email verification is required from admin settings
$emailVerificationRequired = isEmailVerificationRequired();
// Check if registration is enabled
$registrationEnabled = isRegistrationEnabled();

// Include African patterns component
require_once __DIR__ . '/../components/african-patterns.php';
?>

<div class="tw-min-h-screen tw-bg-gradient-to-br tw-from-orange-500 tw-via-red-600 tw-to-pink-600 tw-flex tw-items-center tw-justify-center tw-py-8 tw-px-4 tw-relative tw-overflow-hidden">
    <!-- African Art Background -->
    <div class="tw-absolute tw-inset-0 african-pattern-zigzag tw-opacity-10"></div>
    <div class="tw-absolute tw-inset-0" aria-hidden="true">
        <div class="african-corner-tl" style="opacity: 0.2;"></div>
        <div class="african-corner-br" style="opacity: 0.2;"></div>
        <!-- Floating Symbols -->
        <div class="tw-absolute tw-top-20 tw-right-10 tw-w-16 tw-h-16 tw-text-yellow-300 african-symbol-float" style="opacity: 0.2;">
            <svg class="tw-w-full tw-h-full"><use href="#african-diamond"/></svg>
        </div>
        <div class="tw-absolute tw-bottom-32 tw-left-10 tw-w-20 tw-h-20 tw-text-white african-symbol-float" style="opacity: 0.2; animation-delay: 1.5s;">
            <svg class="tw-w-full tw-h-full"><use href="#african-spider"/></svg>
        </div>
    </div>

    <div class="tw-max-w-lg tw-w-full tw-relative tw-z-10">
        <!-- Header with Logo -->
        <div class="tw-text-center tw-mb-6">
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
            <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-text-white tw-mb-2">Join Our Community!</h1>
            <p class="tw-text-sm sm:tw-text-base tw-text-white/90">Start your food journey in Bamenda</p>
        </div>

        <!-- Registration Form Card -->
        <div class="tw-bg-white tw-rounded-3xl tw-shadow-2xl tw-p-6 sm:tw-p-8 tw-mb-6">
            <form class="tw-space-y-5" method="POST" action="<?= url('/register') ?>" id="registerForm">
                <?= csrf_field() ?>
                
                <!-- Hidden Referral Code Field -->
                <?php if (!empty($_GET['ref'])): ?>
                    <input type="hidden" name="referral_code" value="<?= e($_GET['ref']) ?>" id="referral-code-field">
                <?php endif; ?>
                
                <!-- Referral Success Message -->
                <?php if (!empty($_GET['ref'])): ?>
                    <div class="tw-bg-gradient-to-r tw-from-green-50 tw-to-emerald-50 tw-border-l-4 tw-border-green-500 tw-rounded-lg tw-p-4 tw-mb-4">
                        <div class="tw-flex tw-items-start tw-gap-3">
                            <svg class="tw-w-5 tw-h-5 tw-text-green-500 tw-flex-shrink-0 tw-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="tw-text-sm tw-font-semibold tw-text-green-800 tw-mb-1">🎉 You were referred by a friend!</h3>
                                <p class="tw-text-sm tw-text-green-700">
                                    You'll both get special benefits when you place your first order. Welcome to Time2Eat!
                                </p>
                                <div class="tw-mt-2 tw-text-xs tw-text-green-600 tw-font-medium">
                                    Referral Code: <span class="tw-bg-green-100 tw-px-2 tw-py-1 tw-rounded tw-font-mono"><?= e($_GET['ref']) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Referral Code Validation Status -->
                        <div id="referral-status" class="tw-hidden tw-mt-3 tw-bg-gray-50 tw-border tw-border-gray-300 tw-rounded-lg tw-p-3">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <svg class="tw-w-4 tw-h-4 tw-text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="tw-text-sm tw-font-medium">Validating referral code...</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Registration Disabled Message -->
                <?php if (!$registrationEnabled): ?>
                    <div class="tw-bg-gradient-to-r tw-from-amber-50 tw-to-orange-50 tw-border-l-4 tw-border-amber-500 tw-rounded-lg tw-p-5 tw-mb-4">
                        <div class="tw-flex tw-items-start tw-gap-4">
                            <div class="tw-flex-shrink-0 tw-pt-0.5">
                                <svg class="tw-w-6 tw-h-6 tw-text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="tw-flex-1">
                                <h3 class="tw-text-base tw-font-bold tw-text-amber-900 tw-mb-2">Registration Temporarily Closed</h3>
                                <p class="tw-text-sm tw-text-amber-800 tw-mb-3">
                                    We're currently not accepting new registrations at the moment. This is temporary, and we'll be back soon!
                                </p>
                                <div class="tw-space-y-2 tw-text-sm tw-text-amber-700">
                                    <p class="tw-flex tw-items-center tw-gap-2">
                                        <svg class="tw-w-4 tw-h-4 tw-flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>Check back soon for updates</span>
                                    </p>
                                    <p class="tw-flex tw-items-center tw-gap-2">
                                        <svg class="tw-w-4 tw-h-4 tw-flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>Contact our support team for more information</span>
                                    </p>
                                </div>
                                <div class="tw-mt-4 tw-flex tw-gap-3">
                                    <a href="<?= url('/') ?>" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-amber-600 tw-text-white tw-rounded-lg tw-font-medium tw-text-sm tw-hover:bg-amber-700 tw-transition-colors">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 019-9 9.75 9.75 0 016.74 2.74L21 8"></path>
                                        </svg>
                                        Back to Home
                                    </a>
                                    <a href="<?= url('/contact') ?>" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-white tw-text-amber-700 tw-border tw-border-amber-300 tw-rounded-lg tw-font-medium tw-text-sm tw-hover:bg-amber-50 tw-transition-colors">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Contact Support
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hide the form when registration is disabled -->
                    <style>
                        #registerForm { display: none; }
                    </style>
                <?php endif; ?>

                <!-- Error Messages -->
                <?php if ($error && $registrationEnabled): ?>
                    <div class="tw-bg-red-50 tw-border-l-4 tw-border-red-500 tw-rounded-lg tw-p-4">
                        <div class="tw-flex tw-items-start tw-gap-3">
                            <svg class="tw-w-5 tw-h-5 tw-text-red-500 tw-flex-shrink-0 tw-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="tw-text-sm tw-text-red-800 tw-font-medium"><?= e($error) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Role Selection - First & Most Important -->
                <div>
                    <label class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-3">
                        <svg class="tw-w-5 tw-h-5 tw-inline tw-mr-2 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        I want to join as
                    </label>
                    <div class="tw-grid tw-grid-cols-3 tw-gap-3">
                        <!-- Customer -->
                        <label class="tw-relative tw-cursor-pointer">
                            <input type="radio" name="role" value="customer" class="tw-peer tw-sr-only" <?= ($old['role'] ?? 'customer') === 'customer' ? 'checked' : '' ?>>
                            <div class="tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-center tw-transition-all peer-checked:tw-border-orange-500 peer-checked:tw-bg-orange-50 peer-checked:tw-shadow-lg hover:tw-border-orange-300">
                                <svg class="tw-w-8 tw-h-8 tw-mx-auto tw-mb-2 tw-text-gray-400 peer-checked:tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="tw-block tw-text-xs tw-font-bold tw-text-gray-900">Customer</span>
                                <span class="tw-block tw-text-[10px] tw-text-gray-500 tw-mt-1">Order food</span>
                            </div>
                        </label>

                        <!-- Restaurant -->
                        <label class="tw-relative tw-cursor-pointer">
                            <input type="radio" name="role" value="vendor" class="tw-peer tw-sr-only" <?= ($old['role'] ?? '') === 'vendor' ? 'checked' : '' ?>>
                            <div class="tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-center tw-transition-all peer-checked:tw-border-orange-500 peer-checked:tw-bg-orange-50 peer-checked:tw-shadow-lg hover:tw-border-orange-300">
                                <svg class="tw-w-8 tw-h-8 tw-mx-auto tw-mb-2 tw-text-gray-400 peer-checked:tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="tw-block tw-text-xs tw-font-bold tw-text-gray-900">Restaurant</span>
                                <span class="tw-block tw-text-[10px] tw-text-gray-500 tw-mt-1">Sell food</span>
                            </div>
                        </label>

                        <!-- Rider -->
                        <label class="tw-relative tw-cursor-pointer">
                            <input type="radio" name="role" value="rider" class="tw-peer tw-sr-only" <?= ($old['role'] ?? '') === 'rider' ? 'checked' : '' ?>>
                            <div class="tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-center tw-transition-all peer-checked:tw-border-orange-500 peer-checked:tw-bg-orange-50 peer-checked:tw-shadow-lg hover:tw-border-orange-300">
                                <svg class="tw-w-8 tw-h-8 tw-mx-auto tw-mb-2 tw-text-gray-400 peer-checked:tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                </svg>
                                <span class="tw-block tw-text-xs tw-font-bold tw-text-gray-900">Rider</span>
                                <span class="tw-block tw-text-[10px] tw-text-gray-500 tw-mt-1">Deliver</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Name Fields - Side by Side on Mobile -->
                <div class="tw-grid tw-grid-cols-2 tw-gap-3">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">
                            First Name
                        </label>
                        <input 
                            id="first_name" 
                            name="first_name" 
                            type="text" 
                            required
                            value="<?= e($old['first_name'] ?? '') ?>"
                            class="tw-block tw-w-full tw-px-4 tw-py-3.5 tw-border-2 <?= isset($errors['first_name']) ? 'tw-border-red-400' : 'tw-border-gray-200' ?> tw-rounded-xl tw-text-base tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all"
                            placeholder="John"
                        >
                        <?php if (isset($errors['first_name'])): ?>
                            <p class="tw-mt-1 tw-text-xs tw-text-red-600"><?= e($errors['first_name'][0]) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">
                            Last Name
                        </label>
                        <input 
                            id="last_name" 
                            name="last_name" 
                            type="text" 
                            required
                            value="<?= e($old['last_name'] ?? '') ?>"
                            class="tw-block tw-w-full tw-px-4 tw-py-3.5 tw-border-2 <?= isset($errors['last_name']) ? 'tw-border-red-400' : 'tw-border-gray-200' ?> tw-rounded-xl tw-text-base tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all"
                            placeholder="Doe"
                        >
                        <?php if (isset($errors['last_name'])): ?>
                            <p class="tw-mt-1 tw-text-xs tw-text-red-600"><?= e($errors['last_name'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Email Field with Verification -->
                <div>
                    <label for="email" class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">
                        Email Address
                        <span id="emailVerifiedBadge" class="tw-hidden tw-ml-2 tw-inline-flex tw-items-center tw-gap-1 tw-px-2 tw-py-0.5 tw-bg-green-100 tw-text-green-700 tw-text-xs tw-font-semibold tw-rounded-full">
                            <svg class="tw-w-3 tw-h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>
                    </label>
                    <div class="tw-flex tw-gap-2">
                        <div class="tw-flex-1 tw-relative">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                value="<?= e($old['email'] ?? '') ?>"
                                class="tw-block tw-w-full tw-pl-12 tw-pr-4 tw-py-3.5 tw-border-2 <?= isset($errors['email']) ? 'tw-border-red-400' : 'tw-border-gray-200' ?> tw-rounded-xl tw-text-base tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all"
                                placeholder="you@example.com"
                            >
                        </div>
                        <?php if ($emailVerificationRequired): ?>
                        <button
                            type="button"
                            id="verifyEmailBtn"
                            class="tw-px-4 tw-py-3.5 tw-bg-orange-600 tw-text-white tw-text-sm tw-font-bold tw-rounded-xl hover:tw-bg-orange-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 tw-transition-all disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-flex tw-items-center tw-gap-2 tw-whitespace-nowrap tw-shadow-md hover:tw-shadow-lg"
                        >
                            <svg id="verifyEmailIcon" class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span id="verifyEmailBtnText">Verify</span>
                            <svg id="verifyEmailSpinner" class="tw-hidden tw-animate-spin tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Email Error Message (below input) -->
                    <div id="emailErrorStatus" class="tw-hidden tw-mt-2 tw-flex tw-items-center tw-gap-2 tw-text-red-600 tw-text-sm tw-font-medium">
                        <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span id="emailErrorText"></span>
                    </div>

                    <?php if (isset($errors['email'])): ?>
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600 tw-flex tw-items-center tw-gap-1">
                            <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <?= e($errors['email'][0]) ?>
                        </p>
                    <?php endif; ?>

                    <!-- Email Verification Code Section -->
                    <?php if ($emailVerificationRequired): ?>
                    <div id="emailVerificationSection" class="tw-hidden tw-mt-4 tw-p-4 tw-bg-gradient-to-br tw-from-blue-50 tw-to-indigo-50 tw-border-2 tw-border-blue-200 tw-rounded-2xl tw-shadow-inner">
                        <div class="tw-flex tw-items-center tw-gap-2 tw-mb-3">
                            <div class="tw-w-8 tw-h-8 tw-bg-blue-600 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                                <svg class="tw-w-5 tw-h-5 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div class="tw-flex-1">
                                <h4 class="tw-text-sm tw-font-bold tw-text-blue-900">Verify Your Email</h4>
                                <p class="tw-text-xs tw-text-blue-700">
                                    Code sent to <span id="verificationEmail" class="tw-font-semibold"></span>
                                </p>
                            </div>
                        </div>

                        <div class="tw-flex tw-gap-2">
                            <input
                                id="verificationCode"
                                name="verification_code"
                                type="text"
                                inputmode="numeric"
                                maxlength="6"
                                autocomplete="one-time-code"
                                class="tw-flex-1 tw-px-4 tw-py-3 tw-border-2 tw-border-blue-300 tw-rounded-xl tw-text-center tw-text-lg tw-font-bold tw-tracking-widest tw-bg-white focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                placeholder="000000"
                            >
                            <button
                                type="button"
                                id="verifyCodeBtn"
                                class="tw-px-5 tw-py-3 tw-bg-blue-600 tw-text-white tw-text-sm tw-font-bold tw-rounded-xl hover:tw-bg-blue-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 tw-transition-all disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-shadow-md hover:tw-shadow-lg"
                            >
                                Check
                            </button>
                        </div>

                        <div class="tw-mt-3 tw-flex tw-justify-between tw-items-center">
                            <button
                                type="button"
                                id="resendCodeBtn"
                                class="tw-text-sm tw-text-blue-700 tw-font-semibold hover:tw-text-blue-800 tw-underline tw-underline-offset-2 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed disabled:tw-no-underline tw-transition-colors"
                            >
                                Resend Code
                            </button>
                            <span id="resendTimer" class="tw-hidden tw-text-xs tw-text-blue-600 tw-font-medium tw-bg-blue-100 tw-px-3 tw-py-1 tw-rounded-full">
                                <span id="countdown">60</span>s
                            </span>
                        </div>

                        <!-- Verification Messages -->
                        <div id="verificationMessages" class="tw-mt-3">
                            <div id="verificationError" class="tw-hidden tw-flex tw-items-center tw-gap-2 tw-p-3 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg">
                                <svg class="tw-w-4 tw-h-4 tw-text-red-600 tw-flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span id="verificationErrorText" class="tw-text-sm tw-text-red-800 tw-font-medium"></span>
                            </div>
                            <div id="verificationSuccess" class="tw-hidden tw-flex tw-items-center tw-gap-2 tw-p-3 tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-lg">
                                <svg class="tw-w-4 tw-h-4 tw-text-green-600 tw-flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="tw-text-sm tw-text-green-800 tw-font-medium">Code verified! ✨</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Phone Field -->
                <div>
                    <label for="phone" class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">
                        Phone Number
                    </label>
                    <div class="tw-relative">
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                            <svg class="tw-w-5 tw-h-5 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <input 
                            id="phone" 
                            name="phone" 
                            type="tel" 
                            required
                            value="<?= e($old['phone'] ?? '') ?>"
                            class="tw-block tw-w-full tw-pl-12 tw-pr-4 tw-py-3.5 tw-border-2 <?= isset($errors['phone']) ? 'tw-border-red-400' : 'tw-border-gray-200' ?> tw-rounded-xl tw-text-base tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all"
                            placeholder="+237 6XX XXX XXX"
                        >
                    </div>
                    <?php if (isset($errors['phone'])): ?>
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600 tw-flex tw-items-center tw-gap-1">
                            <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <?= e($errors['phone'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Password Fields -->
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-3">
                    <!-- Password -->
                    <div>
                        <label for="password" class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">
                            Password
                        </label>
                        <div class="tw-relative">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                required
                                class="tw-block tw-w-full tw-pl-12 tw-pr-12 tw-py-3.5 tw-border-2 <?= isset($errors['password']) ? 'tw-border-red-400' : 'tw-border-gray-200' ?> tw-rounded-xl tw-text-base tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all"
                                placeholder="Min. 8 characters"
                            >
                            <button
                                type="button"
                                class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-4 tw-flex tw-items-center tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors"
                                onclick="togglePassword('password', 'eye-icon-1')"
                                aria-label="Toggle password visibility"
                            >
                                <svg id="eye-icon-1" class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <p class="tw-mt-1 tw-text-xs tw-text-red-600"><?= e($errors['password'][0]) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">
                            Confirm
                        </label>
                        <div class="tw-relative">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                required
                                class="tw-block tw-w-full tw-pl-12 tw-pr-12 tw-py-3.5 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-base tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all"
                                placeholder="Repeat password"
                            >
                            <button
                                type="button"
                                class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-4 tw-flex tw-items-center tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors"
                                onclick="togglePassword('password_confirmation', 'eye-icon-2')"
                                aria-label="Toggle password visibility"
                            >
                                <svg id="eye-icon-2" class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="tw-flex tw-items-start tw-gap-3 tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                    <input
                        id="terms"
                        name="terms"
                        type="checkbox"
                        required
                        class="tw-mt-1 tw-h-5 tw-w-5 tw-text-orange-600 focus:tw-ring-orange-500 tw-border-gray-300 tw-rounded tw-cursor-pointer tw-flex-shrink-0"
                    >
                    <label for="terms" class="tw-text-sm tw-text-gray-700 tw-cursor-pointer">
                        I agree to the <a href="<?= url('/terms') ?>" class="tw-font-semibold tw-text-orange-600 hover:tw-text-orange-700 tw-underline">Terms & Conditions</a> and <a href="<?= url('/privacy') ?>" class="tw-font-semibold tw-text-orange-600 hover:tw-text-orange-700 tw-underline">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="tw-pt-2">
                    <button
                        type="submit"
                        class="tw-w-full tw-flex tw-items-center tw-justify-center tw-gap-2 tw-py-4 tw-px-4 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-text-base tw-font-bold tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl hover:tw-from-orange-600 hover:tw-to-red-700 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-orange-300 tw-transition-all tw-duration-200 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed hover:tw-scale-[1.02] active:tw-scale-[0.98]"
                        id="registerButton"
                        <?= $emailVerificationRequired ? '' : 'data-verification-not-required="1"' ?>
                    >
                        <svg id="registerIcon" class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        <span id="registerButtonText">Create Account</span>
                        <svg id="registerSpinner" class="tw-hidden tw-animate-spin tw-w-5 tw-h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Sign In Link -->
            <div class="tw-mt-6 tw-text-center tw-pt-6 tw-border-t tw-border-gray-200">
                <p class="tw-text-sm tw-text-gray-600">
                    Already have an account?
                    <a href="<?= url('/login') ?>" class="tw-font-bold tw-text-orange-600 hover:tw-text-orange-700 tw-transition-colors tw-underline tw-decoration-2 tw-underline-offset-2">
                        Sign in
                    </a>
                </p>
            </div>
        </div>

        <!-- Back to Home Link -->
        <div class="tw-text-center">
            <a href="<?= url('/') ?>" class="tw-inline-flex tw-items-center tw-gap-2 tw-text-white tw-text-sm tw-font-semibold hover:tw-text-yellow-300 tw-transition-colors">
                <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
        </div>
    </div>
</div>

<script>
// Config from server
const EMAIL_VERIFICATION_REQUIRED = <?= $emailVerificationRequired ? 'true' : 'false' ?>;
// Email verification state
let isEmailVerified = EMAIL_VERIFICATION_REQUIRED ? false : true;
let resendCountdown = 0;
let countdownTimer = null;

// Referral code validation
let referralCodeValid = false;
let referralValidationTimeout = null;

// Toggle password visibility
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);

    if (field.type === 'password') {
        field.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
    } else {
        field.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Get CSRF token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
           document.querySelector('input[name="csrf_token"]')?.value ||
           document.querySelector('input[name="_token"]')?.value;
}

// Send verification code
function sendVerificationCode(email) {
    const verifyBtn = document.getElementById('verifyEmailBtn');
    const btnText = document.getElementById('verifyEmailBtnText');
    const btnIcon = document.getElementById('verifyEmailIcon');
    const spinner = document.getElementById('verifyEmailSpinner');

    // Show loading state
    verifyBtn.disabled = true;
    btnText.textContent = 'Sending...';
    btnIcon.classList.add('tw-hidden');
    spinner.classList.remove('tw-hidden');
    hideEmailMessages();

    const csrfToken = getCsrfToken();
    if (!csrfToken) {
        showEmailError('Security token missing. Please refresh the page.');
        resetVerifyButton();
        return;
    }

    fetch('<?= url('/api/send-verification-code') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showVerificationSection(email);
            startResendCountdown();
            btnText.textContent = 'Resend';
            verifyBtn.classList.remove('tw-bg-orange-600', 'hover:tw-bg-orange-700');
            verifyBtn.classList.add('tw-bg-blue-600', 'hover:tw-bg-blue-700');
        } else {
            showEmailError(data.message || 'Failed to send code. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error sending verification code:', error);
        showEmailError('Network error. Please check your connection.');
    })
    .finally(() => {
        verifyBtn.disabled = false;
        btnIcon.classList.remove('tw-hidden');
        spinner.classList.add('tw-hidden');
    });
}

// Verify email code
function verifyEmailCode(email, code) {
    const verifyBtn = document.getElementById('verifyCodeBtn');
    const originalText = verifyBtn.textContent;

    // Show loading state
    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Checking...';
    hideVerificationMessages();

    const csrfToken = getCsrfToken();
    if (!csrfToken) {
        showVerificationError('Security token missing. Please refresh the page.');
        verifyBtn.disabled = false;
        verifyBtn.textContent = originalText;
        return;
    }

    fetch('<?= url('/api/verify-email-code') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ email: email, code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showVerificationSuccess();
            enableRegistration();
            // Hide verification section after 2 seconds
            setTimeout(() => {
                document.getElementById('emailVerificationSection').classList.add('tw-hidden');
                // Verification complete - badge and form are already updated
            }, 2000);
        } else {
            showVerificationError(data.message || 'Invalid code. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error verifying code:', error);
        showVerificationError('Network error. Please try again.');
    })
    .finally(() => {
        verifyBtn.disabled = false;
        verifyBtn.textContent = originalText;
    });
}

// UI Helper Functions
function showVerificationSection(email) {
    document.getElementById('verificationEmail').textContent = email;
    document.getElementById('emailVerificationSection').classList.remove('tw-hidden');
    document.getElementById('verificationCode').focus();
}



function hideVerificationSection() {
    document.getElementById('emailVerificationSection').classList.add('tw-hidden');
    document.getElementById('verificationCode').value = '';
}

function showEmailError(message) {
    document.getElementById('emailErrorText').textContent = message;
    document.getElementById('emailErrorStatus').classList.remove('tw-hidden');
}

function hideEmailMessages() {
    document.getElementById('emailErrorStatus').classList.add('tw-hidden');
}

function showVerificationError(message) {
    document.getElementById('verificationErrorText').textContent = message;
    document.getElementById('verificationError').classList.remove('tw-hidden');
    document.getElementById('verificationSuccess').classList.add('tw-hidden');
}

function showVerificationSuccess() {
    document.getElementById('verificationSuccess').classList.remove('tw-hidden');
    document.getElementById('verificationError').classList.add('tw-hidden');
}

function hideVerificationMessages() {
    document.getElementById('verificationError').classList.add('tw-hidden');
    document.getElementById('verificationSuccess').classList.add('tw-hidden');
}

// ============================================================================
// REFERRAL CODE VALIDATION
// ============================================================================

// Validate referral code on page load if present
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const refCode = urlParams.get('ref');
    
    if (refCode) {
        validateReferralCode(refCode);
    }
});

// Validate referral code via API
async function validateReferralCode(code) {
    if (!code || code.length < 3) {
        return false;
    }
    
    try {
        const response = await fetch(`<?= url('/api/validate-referral') ?>?code=${encodeURIComponent(code)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.valid) {
            referralCodeValid = true;
            showReferralCodeStatus('valid', data.referrer_name || 'Valid Referral Code');
            return true;
        } else {
            referralCodeValid = false;
            showReferralCodeStatus('invalid', data.message || 'Invalid referral code');
            return false;
        }
    } catch (error) {
        console.error('Error validating referral code:', error);
        referralCodeValid = false;
        showReferralCodeStatus('error', 'Unable to validate referral code');
        return false;
    }
}

// Show referral code validation status
function showReferralCodeStatus(status, message) {
    const statusElement = document.getElementById('referral-status');
    if (!statusElement) return;
    
    // Remove existing status classes
    statusElement.classList.remove('tw-bg-green-50', 'tw-border-green-500', 'tw-text-green-800');
    statusElement.classList.remove('tw-bg-red-50', 'tw-border-red-500', 'tw-text-red-800');
    statusElement.classList.remove('tw-bg-yellow-50', 'tw-border-yellow-500', 'tw-text-yellow-800');
    
    const iconElement = statusElement.querySelector('svg');
    const textElement = statusElement.querySelector('p');
    
    if (status === 'valid') {
        statusElement.classList.add('tw-bg-green-50', 'tw-border-green-500', 'tw-text-green-800');
        iconElement.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        textElement.textContent = `✅ ${message}`;
    } else if (status === 'invalid') {
        statusElement.classList.add('tw-bg-red-50', 'tw-border-red-500', 'tw-text-red-800');
        iconElement.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        textElement.textContent = `❌ ${message}`;
    } else if (status === 'error') {
        statusElement.classList.add('tw-bg-yellow-50', 'tw-border-yellow-500', 'tw-text-yellow-800');
        iconElement.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        textElement.textContent = `⚠️ ${message}`;
    }
    
    statusElement.classList.remove('tw-hidden');
}

function enableRegistration() {
    isEmailVerified = true;
    const emailField = document.getElementById('email');
    const registerBtn = document.getElementById('registerButton');

    // Update email field styling
    emailField.classList.remove('tw-border-gray-200', 'tw-border-red-400');
    emailField.classList.add('tw-border-green-400');
    emailField.readOnly = true;

    // Show verified badge next to email label
    document.getElementById('emailVerifiedBadge').classList.remove('tw-hidden');

    // Hide any error messages
    hideEmailMessages();

    // Enable register button with enhanced styling
    registerBtn.disabled = false;
    registerBtn.classList.remove('tw-opacity-50', 'tw-cursor-not-allowed');
    registerBtn.classList.add('tw-animate-pulse');

    // Add a subtle glow effect
    registerBtn.style.boxShadow = '0 0 20px rgba(34, 197, 94, 0.3)';
}

function resetEmailVerification() {
    isEmailVerified = false;
    const emailField = document.getElementById('email');
    const registerBtn = document.getElementById('registerButton');
    const verifyBtn = document.getElementById('verifyEmailBtn');
    const btnText = document.getElementById('verifyEmailBtnText');

    // Reset email field
    emailField.classList.remove('tw-border-green-400');
    emailField.classList.add('tw-border-gray-200');
    emailField.readOnly = false;

    // Hide verified badge
    document.getElementById('emailVerifiedBadge').classList.add('tw-hidden');

    // Hide messages
    hideEmailMessages();
    hideVerificationSection();

    // Reset verify button
    verifyBtn.disabled = false;
    btnText.textContent = 'Verify';
    verifyBtn.classList.remove('tw-bg-blue-600', 'hover:tw-bg-blue-700');
    verifyBtn.classList.add('tw-bg-orange-600', 'hover:tw-bg-orange-700');

    // Disable register button
    registerBtn.disabled = true;
}

function resetVerifyButton() {
    const verifyBtn = document.getElementById('verifyEmailBtn');
    const btnText = document.getElementById('verifyEmailBtnText');
    const btnIcon = document.getElementById('verifyEmailIcon');
    const spinner = document.getElementById('verifyEmailSpinner');

    verifyBtn.disabled = false;
    btnIcon.classList.remove('tw-hidden');
    spinner.classList.add('tw-hidden');
}

function startResendCountdown() {
    resendCountdown = 60;
    const resendBtn = document.getElementById('resendCodeBtn');
    const timerDisplay = document.getElementById('resendTimer');
    const countdownSpan = document.getElementById('countdown');

    resendBtn.disabled = true;
    timerDisplay.classList.remove('tw-hidden');

    if (countdownTimer) {
        clearInterval(countdownTimer);
    }

    countdownTimer = setInterval(() => {
        resendCountdown--;
        countdownSpan.textContent = resendCountdown;

        if (resendCountdown <= 0) {
            clearInterval(countdownTimer);
            resendBtn.disabled = false;
            timerDisplay.classList.add('tw-hidden');
        }
    }, 1000);
}

// Event Listeners
document.getElementById('email').addEventListener('input', function() {
    const email = this.value.trim();
    if (isEmailVerified && email !== document.getElementById('verificationEmail').textContent) {
        resetEmailVerification();
    }
});

// Only add listeners if email verification is required
if (EMAIL_VERIFICATION_REQUIRED) {
    const verifyEmailBtn = document.getElementById('verifyEmailBtn');
    if (verifyEmailBtn) {
        verifyEmailBtn.addEventListener('click', function() {
            const email = document.getElementById('email').value.trim();

            if (!email || !isValidEmail(email)) {
                showEmailError('Please enter a valid email address');
                return;
            }

            sendVerificationCode(email);
        });
    }

    const verifyCodeBtn = document.getElementById('verifyCodeBtn');
    if (verifyCodeBtn) {
        verifyCodeBtn.addEventListener('click', function() {
            const email = document.getElementById('email').value.trim();
            const code = document.getElementById('verificationCode').value.trim();

            if (!code || code.length !== 6) {
                showVerificationError('Please enter the 6-digit code');
                return;
            }

            verifyEmailCode(email, code);
        });
    }

    const resendCodeBtn = document.getElementById('resendCodeBtn');
    if (resendCodeBtn) {
        resendCodeBtn.addEventListener('click', function() {
            const email = document.getElementById('email').value.trim();
            if (email && isValidEmail(email)) {
                sendVerificationCode(email);
            }
        });
    }
}

// Auto-verify when 6 digits are entered
document.getElementById('verificationCode').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, ''); // Only allow digits
    if (this.value.length === 6) {
        const email = document.getElementById('email').value.trim();
        if (email) {
            verifyEmailCode(email, this.value);
        }
    }
});

// Real-time password match validation
const password = document.getElementById('password');
const passwordConfirm = document.getElementById('password_confirmation');

passwordConfirm.addEventListener('input', function() {
    if (password.value && passwordConfirm.value) {
        if (password.value === passwordConfirm.value) {
            passwordConfirm.classList.remove('tw-border-red-400');
            passwordConfirm.classList.add('tw-border-green-400');
        } else {
            passwordConfirm.classList.remove('tw-border-green-400');
            passwordConfirm.classList.add('tw-border-red-400');
        }
    }
});

// Form submission with validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    if (EMAIL_VERIFICATION_REQUIRED && !isEmailVerified) {
        e.preventDefault();
        showEmailError('Please verify your email address first');
        document.getElementById('email').focus();
        return;
    }

    const button = document.getElementById('registerButton');
    const buttonText = document.getElementById('registerButtonText');
    const icon = document.getElementById('registerIcon');
    const spinner = document.getElementById('registerSpinner');

    button.disabled = true;
    buttonText.textContent = 'Creating account...';
    icon.classList.add('tw-hidden');
    spinner.classList.remove('tw-hidden');
});

// Initialize: Disable register button until email is verified
document.addEventListener('DOMContentLoaded', function() {
    // If verification is not required, keep button enabled; otherwise disable until verified
    document.getElementById('registerButton').disabled = EMAIL_VERIFICATION_REQUIRED ? true : false;

    // Clean up timer on page unload
    window.addEventListener('beforeunload', function() {
        if (countdownTimer) {
            clearInterval(countdownTimer);
        }
    });
});
</script>

