<?php
$title = $title ?? 'Forgot Password - Time2Eat';
$page = $page ?? 'forgot-password';
$errors = $errors ?? [];
$old = $old ?? [];
$error = $error ?? '';
$success = $success ?? '';
?>

<?php include_once __DIR__ . '/../layouts/app.php'; ?>

<div class="tw-min-h-screen tw-bg-gradient-to-br tw-from-orange-50 tw-to-red-50 tw-flex tw-items-center tw-justify-center tw-py-12 tw-px-4 sm:tw-px-6 lg:tw-px-8">
    <div class="tw-max-w-md tw-w-full tw-space-y-8">
        <!-- Header -->
        <div class="tw-text-center">
            <div class="tw-mx-auto tw-h-16 tw-w-16 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                <i data-feather="key" class="tw-h-8 tw-w-8 tw-text-white"></i>
            </div>
            <h2 class="tw-mt-6 tw-text-3xl tw-font-bold tw-text-gray-900">Forgot password?</h2>
            <p class="tw-mt-2 tw-text-sm tw-text-gray-600">
                No worries! Enter your email and we'll send you reset instructions.
            </p>
        </div>

        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-lg tw-p-4">
                <div class="tw-flex">
                    <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-400"></i>
                    <div class="tw-ml-3">
                        <p class="tw-text-sm tw-text-green-800"><?= e($success) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg tw-p-4">
                <div class="tw-flex">
                    <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-red-400"></i>
                    <div class="tw-ml-3">
                        <p class="tw-text-sm tw-text-red-800"><?= e($error) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Forgot Password Form -->
        <form class="tw-mt-8 tw-space-y-6" method="POST" action="<?= url('/forgot-password') ?>" id="forgotPasswordForm">
            <?= csrf_field() ?>
            
            <div>
                <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Email Address
                </label>
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                        <i data-feather="mail" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                    </div>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required
                        value="<?= e($old['email'] ?? '') ?>"
                        class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-3 tw-border <?= isset($errors['email']) ? 'tw-border-red-300' : 'tw-border-gray-300' ?> tw-rounded-lg tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent tw-transition-colors"
                        placeholder="Enter your email address"
                    >
                </div>
                <?php if (isset($errors['email'])): ?>
                    <p class="tw-mt-1 tw-text-sm tw-text-red-600"><?= e($errors['email'][0]) ?></p>
                <?php endif; ?>
            </div>

            <!-- CAPTCHA -->
            <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg tw-border">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-bg-white tw-p-3 tw-rounded tw-border tw-font-mono tw-text-lg tw-tracking-wider tw-select-none" id="captcha-display">
                        <!-- CAPTCHA will be generated here -->
                    </div>
                    <button type="button" onclick="refreshCaptcha()" class="tw-p-2 tw-text-gray-500 hover:tw-text-gray-700 tw-transition-colors">
                        <i data-feather="refresh-cw" class="tw-h-5 tw-w-5"></i>
                    </button>
                </div>
                <input 
                    type="text" 
                    name="captcha" 
                    placeholder="Enter CAPTCHA" 
                    required
                    class="tw-mt-3 tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent"
                >
                <input type="hidden" name="captcha_token" id="captcha-token">
            </div>

            <!-- Submit Button -->
            <div>
                <button 
                    type="submit" 
                    class="tw-group tw-relative tw-w-full tw-flex tw-justify-center tw-py-3 tw-px-4 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-lg tw-text-white tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 hover:tw-from-orange-600 hover:tw-to-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-orange-500 tw-transition-all tw-duration-200 tw-shadow-lg hover:tw-shadow-xl disabled:tw-opacity-50 disabled:tw-cursor-not-allowed"
                    id="submitButton"
                >
                    <span class="tw-absolute tw-left-0 tw-inset-y-0 tw-flex tw-items-center tw-pl-3">
                        <i data-feather="send" class="tw-h-5 tw-w-5 group-hover:tw-text-orange-300"></i>
                    </span>
                    <span id="submitButtonText">Send Reset Instructions</span>
                    <div class="tw-hidden" id="submitSpinner">
                        <div class="tw-animate-spin tw-rounded-full tw-h-5 tw-w-5 tw-border-b-2 tw-border-white"></div>
                    </div>
                </button>
            </div>

            <!-- Back to Login -->
            <div class="tw-text-center">
                <a href="/login" class="tw-text-sm tw-text-orange-600 hover:tw-text-orange-500 tw-transition-colors">
                    ← Back to login
                </a>
            </div>
        </form>

        <!-- Help Section -->
        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">What happens next?</h3>
            
            <div class="tw-space-y-3">
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0">
                        <div class="tw-w-6 tw-h-6 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                            <span class="tw-text-orange-600 tw-font-semibold tw-text-xs">1</span>
                        </div>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-700">We'll send a password reset link to your email</p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0">
                        <div class="tw-w-6 tw-h-6 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                            <span class="tw-text-orange-600 tw-font-semibold tw-text-xs">2</span>
                        </div>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-700">Click the link in the email (valid for 1 hour)</p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0">
                        <div class="tw-w-6 tw-h-6 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                            <span class="tw-text-orange-600 tw-font-semibold tw-text-xs">3</span>
                        </div>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-text-gray-700">Create a new password and sign in</p>
                    </div>
                </div>
            </div>

            <div class="tw-mt-4 tw-pt-4 tw-border-t tw-border-gray-200">
                <p class="tw-text-xs tw-text-gray-500">
                    <i data-feather="shield" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                    For security, reset links expire after 1 hour and can only be used once.
                </p>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="tw-text-center tw-text-sm tw-text-gray-600">
            Still having trouble? 
            <a href="/contact" class="tw-text-orange-600 hover:tw-text-orange-500 tw-transition-colors">
                Contact support
            </a>
        </div>
    </div>
</div>

<script>
// CAPTCHA functionality
function generateCaptcha() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let captcha = '';
    for (let i = 0; i < 6; i++) {
        captcha += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('captcha-display').textContent = captcha;
    document.getElementById('captcha-token').value = btoa(captcha);
}

function refreshCaptcha() {
    generateCaptcha();
}

// Form submission with loading state
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    const button = document.getElementById('submitButton');
    const buttonText = document.getElementById('submitButtonText');
    const spinner = document.getElementById('submitSpinner');
    
    button.disabled = true;
    buttonText.classList.add('tw-hidden');
    spinner.classList.remove('tw-hidden');
});

// Rate limiting client-side
let resetAttempts = parseInt(localStorage.getItem('resetAttempts') || '0');
let lastResetAttempt = parseInt(localStorage.getItem('lastResetAttempt') || '0');

if (resetAttempts >= 3 && Date.now() - lastResetAttempt < 3600000) { // 1 hour
    const button = document.getElementById('submitButton');
    const timeLeft = Math.ceil((3600000 - (Date.now() - lastResetAttempt)) / 60000);
    button.disabled = true;
    button.textContent = `Try again in ${timeLeft} minutes`;
}

// Initialize CAPTCHA
document.addEventListener('DOMContentLoaded', function() {
    generateCaptcha();
});
</script>
