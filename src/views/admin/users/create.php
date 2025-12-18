<?php
/**
 * Admin Create User View
 */
?>

<div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
    <!-- Page Header -->
    <div class="tw-mb-8">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Create New User</h1>
                <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                    Add a new user to the Time2Eat platform
                </p>
            </div>
            <a href="<?= url('/admin/users') ?>" class="tw-bg-gray-500 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-gray-600">
                <i data-feather="arrow-left" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Back to Users
            </a>
        </div>
    </div>

    <!-- Create User Form -->
    <div class="tw-bg-white tw-shadow tw-rounded-lg">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">User Information</h3>
        </div>
        
        <form id="createUserForm" class="tw-p-8">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-8">
                <!-- Username -->
                <div class="tw-space-y-2">
                    <label for="username" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="user" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-text-gray-500"></i>
                        Username <span class="tw-text-red-500">*</span>
                    </label>
                    <div class="tw-relative">
                        <input type="text" id="username" name="username" required
                               placeholder="Enter username"
                               class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-900 tw-placeholder-gray-500 tw-bg-white tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400">
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-500 tw-opacity-0" id="username-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="tw-space-y-2">
                    <label for="email" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="mail" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-text-gray-500"></i>
                        Email Address <span class="tw-text-red-500">*</span>
                    </label>
                    <div class="tw-relative">
                        <input type="email" id="email" name="email" required
                               placeholder="Enter email address"
                               class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-900 tw-placeholder-gray-500 tw-bg-white tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400">
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-500 tw-opacity-0" id="email-check"></i>
                        </div>
                    </div>
                </div>

                <!-- First Name -->
                <div class="tw-space-y-2">
                    <label for="first_name" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="user" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-text-gray-500"></i>
                        First Name <span class="tw-text-red-500">*</span>
                    </label>
                    <div class="tw-relative">
                        <input type="text" id="first_name" name="first_name" required
                               placeholder="Enter first name"
                               class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-900 tw-placeholder-gray-500 tw-bg-white tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400">
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-500 tw-opacity-0" id="first_name-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Last Name -->
                <div class="tw-space-y-2">
                    <label for="last_name" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="user" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-text-gray-500"></i>
                        Last Name <span class="tw-text-red-500">*</span>
                    </label>
                    <div class="tw-relative">
                        <input type="text" id="last_name" name="last_name" required
                               placeholder="Enter last name"
                               class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-900 tw-placeholder-gray-500 tw-bg-white tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400">
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-500 tw-opacity-0" id="last_name-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Phone -->
                <div class="tw-space-y-2">
                    <label for="phone" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="phone" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-text-gray-500"></i>
                        Phone Number
                    </label>
                    <div class="tw-relative">
                        <input type="tel" id="phone" name="phone"
                               placeholder="Enter phone number"
                               class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-900 tw-placeholder-gray-500 tw-bg-white tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400">
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-500 tw-opacity-0" id="phone-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Role -->
                <div class="tw-space-y-2">
                    <label for="role" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="shield" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-text-gray-500"></i>
                        User Role <span class="tw-text-red-500">*</span>
                    </label>
                    <div class="tw-relative">
                        <select id="role" name="role" required
                                class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-900 tw-bg-white tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400 tw-appearance-none tw-bg-no-repeat tw-bg-right tw-pr-10"
                                style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 4 5\"><path fill=\"%23666\" d=\"M2 0L0 2h4zm0 5L0 3h4z\"/></svg>'); background-position: right 12px center; background-size: 12px;">
                            <option value="">Select a role</option>
                            <option value="customer">Customer</option>
                            <option value="vendor">Vendor</option>
                            <option value="rider">Rider</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-500 tw-opacity-0" id="role-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="tw-space-y-2">
                    <label for="status" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="activity" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-text-gray-500"></i>
                        Account Status
                    </label>
                    <div class="tw-relative">
                        <select id="status" name="status"
                                class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-900 tw-bg-white tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400 tw-appearance-none tw-bg-no-repeat tw-bg-right tw-pr-10"
                                style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 4 5\"><path fill=\"%23666\" d=\"M2 0L0 2h4zm0 5L0 3h4z\"/></svg>'); background-position: right 12px center; background-size: 12px;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-500 tw-opacity-0" id="status-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="tw-space-y-2 md:tw-col-span-2">
                    <label for="password" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="lock" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-text-gray-500"></i>
                        Password <span class="tw-text-red-500">*</span>
                    </label>
                    <div class="tw-relative">
                        <input type="password" id="password" name="password" required
                               placeholder="Enter secure password"
                               class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-900 tw-placeholder-gray-500 tw-bg-white tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400">
                        <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-500 tw-opacity-0" id="password-check"></i>
                        </div>
                    </div>
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Password must be at least 8 characters long</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="tw-mt-10 tw-pt-6 tw-border-t tw-border-gray-200 tw-flex tw-flex-col sm:tw-flex-row tw-justify-end tw-space-y-3 sm:tw-space-y-0 sm:tw-space-x-4">
                <a href="<?= url('/admin/users') ?>" 
                   class="tw-inline-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-semibold tw-text-gray-700 tw-bg-white tw-shadow-sm hover:tw-bg-gray-50 tw-transition-colors tw-duration-200">
                    <i data-feather="x" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Cancel
                </a>
                <button type="submit" 
                        class="tw-inline-flex tw-items-center tw-justify-center tw-px-8 tw-py-3 tw-border tw-border-transparent tw-rounded-lg tw-text-sm tw-font-semibold tw-text-white tw-bg-primary-600 tw-shadow-sm hover:tw-bg-primary-700 tw-transition-colors tw-duration-200 tw-focus:tw-outline-none tw-focus:tw-ring-2 tw-focus:tw-ring-primary-500 tw-focus:tw-ring-offset-2">
                    <i data-feather="user-plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation and visual feedback
document.addEventListener('DOMContentLoaded', function() {
    // Initialize feather icons
    feather.replace();
    
    // Get all form inputs
    const inputs = document.querySelectorAll('input[required], select[required]');
    
    // Add real-time validation
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            validateField(this);
        });
        
        input.addEventListener('blur', function() {
            validateField(this);
        });
    });
    
    // Validation function
    function validateField(field) {
        const checkIcon = document.getElementById(field.id + '-check');
        const isValid = field.checkValidity() && field.value.trim() !== '';
        
        if (isValid) {
            field.classList.remove('tw-border-red-300', 'tw-ring-red-500');
            field.classList.add('tw-border-green-300', 'tw-ring-green-500');
            if (checkIcon) {
                checkIcon.classList.remove('tw-opacity-0');
                checkIcon.classList.add('tw-opacity-100');
            }
        } else {
            field.classList.remove('tw-border-green-300', 'tw-ring-green-500');
            field.classList.add('tw-border-red-300', 'tw-ring-red-500');
            if (checkIcon) {
                checkIcon.classList.add('tw-opacity-0');
                checkIcon.classList.remove('tw-opacity-100');
            }
        }
    }
    
    // Email validation
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('input', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isValid = emailRegex.test(this.value);
        
        if (this.value && !isValid) {
            this.setCustomValidity('Please enter a valid email address');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Password validation
    const passwordInput = document.getElementById('password');
    passwordInput.addEventListener('input', function() {
        const isValid = this.value.length >= 8;
        
        if (this.value && !isValid) {
            this.setCustomValidity('Password must be at least 8 characters long');
        } else {
            this.setCustomValidity('');
        }
    });
});

// Form submission
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Validate all required fields
    const requiredFields = this.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.checkValidity() || field.value.trim() === '') {
            isValid = false;
            field.classList.add('tw-border-red-300', 'tw-ring-red-500');
        }
    });
    
    if (!isValid) {
        showNotification('Please fill in all required fields correctly', 'error');
        return;
    }
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-animate-spin"></i>Creating User...';
    
    fetch('<?= url('/admin/users') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('User created successfully!', 'success');
            
            // Redirect to users list after a short delay
            setTimeout(() => {
                window.location.href = '<?= url('/admin/users') ?>';
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to create user', 'error');
            
            // Reset button
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while creating the user', 'error');
        
        // Reset button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-md tw-shadow-lg tw-z-50 ${
        type === 'success' ? 'tw-bg-green-500' : 'tw-bg-red-500'
    } tw-text-white`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
