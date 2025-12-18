<?php
/**
 * Login Page - Mobile First Design
 * Clean, minimal, and focused on core functionality
 */
$title = $title ?? 'Login - Time2Eat';
$page = $page ?? 'login';
$errors = $errors ?? [];
$old = $old ?? [];
$error = $error ?? '';

// Load auth helpers to check if registration is enabled
require_once __DIR__ . '/../../helpers/auth_helpers.php';
$registrationEnabled = isRegistrationEnabled();

// Include African patterns component
require_once __DIR__ . '/../components/african-patterns.php';
?>

<div class="tw-min-h-screen tw-bg-gradient-to-br tw-from-orange-500 tw-via-red-600 tw-to-pink-600 tw-flex tw-items-center tw-justify-center tw-py-8 tw-px-4 tw-relative tw-overflow-hidden">
    <!-- African Art Background -->
    <div class="tw-absolute tw-inset-0 african-pattern-geometric tw-opacity-10"></div>
    <div class="tw-absolute tw-inset-0" aria-hidden="true">
        <div class="african-corner-tl" style="opacity: 0.2;"></div>
        <div class="african-corner-br" style="opacity: 0.2;"></div>
    </div>

    <div class="tw-max-w-md tw-w-full tw-relative tw-z-10">
        <!-- Header with Logo -->
        <div class="tw-text-center tw-mb-8">
            <a href="<?= url('/') ?>" class="tw-inline-flex tw-items-center tw-gap-3 tw-mb-6">
                <div class="tw-w-14 tw-h-14 sm:tw-w-16 sm:tw-h-16 tw-bg-white tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-shadow-2xl">
                    <svg class="tw-w-8 tw-h-8 sm:tw-w-10 sm:tw-h-10 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="tw-text-3xl sm:tw-text-4xl tw-font-black tw-text-white">
                    Time<span class="tw-text-yellow-300">2</span>Eat
                </span>
            </a>
            <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-text-white tw-mb-2">Welcome Back!</h1>
            <p class="tw-text-base sm:tw-text-lg tw-text-white/90">Sign in to continue ordering</p>
        </div>

        <!-- Login Form Card -->
        <div class="tw-bg-white tw-rounded-3xl tw-shadow-2xl tw-p-6 sm:tw-p-8">
            <form class="tw-space-y-5" method="POST" action="<?= url('/login') ?>" id="loginForm">
                <?= csrf_field() ?>

                <!-- Flash Success Message -->
                <?php if (!empty($flash['success'])): ?>
                    <div class="tw-bg-green-50 tw-border-l-4 tw-border-green-500 tw-rounded-lg tw-p-4">
                        <div class="tw-flex tw-items-start tw-gap-3">
                            <svg class="tw-w-5 tw-h-5 tw-text-green-500 tw-flex-shrink-0 tw-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="tw-text-sm tw-text-green-800 tw-font-medium"><?= e($flash['success']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Error Messages -->
                <?php if ($error): ?>
                    <div class="tw-bg-red-50 tw-border-l-4 tw-border-red-500 tw-rounded-lg tw-p-4">
                        <div class="tw-flex tw-items-start tw-gap-3">
                            <svg class="tw-w-5 tw-h-5 tw-text-red-500 tw-flex-shrink-0 tw-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="tw-flex-1">
                                <p class="tw-text-sm tw-text-red-800 tw-font-medium"><?= e($error) ?></p>
                                <?php if (strpos($error, 'pending approval') !== false): ?>
                                    <p class="tw-text-xs tw-text-red-700 tw-mt-2">
                                        Your application is being reviewed. You'll receive an email once approved.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Email Field -->
                <div>
                    <label for="email" class="tw-block tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-2">
                        Email Address
                    </label>
                    <div class="tw-relative">
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
                    <?php if (isset($errors['email'])): ?>
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600 tw-flex tw-items-center tw-gap-1">
                            <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <?= e($errors['email'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Password Field -->
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
                            autocomplete="current-password"
                            required
                            class="tw-block tw-w-full tw-pl-12 tw-pr-12 tw-py-3.5 tw-border-2 <?= isset($errors['password']) ? 'tw-border-red-400' : 'tw-border-gray-200' ?> tw-rounded-xl tw-text-base tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all"
                            placeholder="Enter your password"
                        >
                        <button
                            type="button"
                            class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-4 tw-flex tw-items-center tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors"
                            onclick="togglePassword('password')"
                            aria-label="Toggle password visibility"
                        >
                            <svg id="eye-icon" class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600 tw-flex tw-items-center tw-gap-1">
                            <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <?= e($errors['password'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="tw-flex tw-items-center tw-justify-between tw-pt-1">
                    <div class="tw-flex tw-items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="tw-h-4 tw-w-4 tw-text-orange-600 focus:tw-ring-orange-500 tw-border-gray-300 tw-rounded tw-cursor-pointer"
                        >
                        <label for="remember" class="tw-ml-2 tw-block tw-text-sm tw-text-gray-700 tw-cursor-pointer">
                            Remember me
                        </label>
                    </div>
                    <div class="tw-text-sm">
                        <a href="<?= url('/forgot-password') ?>" class="tw-font-semibold tw-text-orange-600 hover:tw-text-orange-700 tw-transition-colors">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="tw-pt-2">
                    <button
                        type="submit"
                        class="tw-w-full tw-flex tw-items-center tw-justify-center tw-gap-2 tw-py-4 tw-px-4 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-text-base tw-font-bold tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl hover:tw-from-orange-600 hover:tw-to-red-700 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-orange-300 tw-transition-all tw-duration-200 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed hover:tw-scale-[1.02] active:tw-scale-[0.98]"
                        id="loginButton"
                    >
                        <svg id="loginIcon" class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        <span id="loginButtonText">Sign In</span>
                        <svg id="loginSpinner" class="tw-hidden tw-animate-spin tw-w-5 tw-h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Sign Up Link -->
            <div class="tw-mt-6 tw-text-center tw-pt-6 tw-border-t tw-border-gray-200">
                <?php if ($registrationEnabled): ?>
                    <p class="tw-text-sm tw-text-gray-600">
                        Don't have an account?
                        <a href="<?= url('/register') ?>" class="tw-font-bold tw-text-orange-600 hover:tw-text-orange-700 tw-transition-colors tw-underline tw-decoration-2 tw-underline-offset-2">
                            Sign up now
                        </a>
                    </p>
                <?php else: ?>
                    <div class="tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded-lg tw-p-3">
                        <p class="tw-text-xs tw-text-amber-800 tw-font-medium">
                            <svg class="tw-w-4 tw-h-4 tw-inline tw-mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13 16h-1v-4h-1m1-4h.01M9 2a1 1 0 000 2h.01a1 1 0 000-2H9zm0 5a1 1 0 000 2h.01a1 1 0 000-2H9zm7 0a1 1 0 000 2h.01a1 1 0 000-2h-.01zm0-5a1 1 0 000 2h.01a1 1 0 000-2h-.01zM9 13a1 1 0 000 2h.01a1 1 0 000-2H9z" clip-rule="evenodd"></path>
                            </svg>
                            Registration is temporarily closed. Check back soon!
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back to Home Link -->
        <div class="tw-mt-6 tw-text-center">
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
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('eye-icon');

    if (field.type === 'password') {
        field.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
    } else {
        field.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
}

// Form submission with loading state
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const button = document.getElementById('loginButton');
    const buttonText = document.getElementById('loginButtonText');
    const icon = document.getElementById('loginIcon');
    const spinner = document.getElementById('loginSpinner');

    button.disabled = true;
    buttonText.textContent = 'Signing in...';
    icon.classList.add('tw-hidden');
    spinner.classList.remove('tw-hidden');
});
</script>
