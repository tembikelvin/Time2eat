<?php
/**
 * Admin Edit User View
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'users';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Edit User</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Update information for <?= e($user['first_name'] ?? 'User') ?> <?= e($user['last_name'] ?? '') ?>
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <a href="<?= url('/admin/users/' . $user['id']) ?>" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                <i data-feather="arrow-left" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Back to Details
            </a>
            <a href="<?= url('/admin/users') ?>" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                <i data-feather="list" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                All Users
            </a>
        </div>
    </div>
</div>

    <!-- Edit User Form -->
    <div class="tw-bg-white tw-shadow tw-rounded-lg">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">User Information</h3>
        </div>
        
        <form id="editUserForm" class="tw-p-6">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                <!-- First Name -->
                <div>
                    <label for="first_name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">First Name *</label>
                    <input type="text" id="first_name" name="first_name" value="<?= e($user['first_name'] ?? '') ?>" required
                           class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required
                           class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Email *</label>
                    <input type="email" id="email" name="email" value="<?= e($user['email'] ?? '') ?>" required
                           class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>"
                           class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Role *</label>
                    <select id="role" name="role" required
                            class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                        <option value="customer" <?= ($user['role'] ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="vendor" <?= ($user['role'] ?? '') === 'vendor' ? 'selected' : '' ?>>Vendor</option>
                        <option value="rider" <?= ($user['role'] ?? '') === 'rider' ? 'selected' : '' ?>>Rider</option>
                        <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Status *</label>
                    <select id="status" name="status" required
                            class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                        <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="suspended" <?= ($user['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>

                <!-- Balance -->
                <div>
                    <label for="balance" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Balance (XAF)</label>
                    <input type="number" id="balance" name="balance" value="<?= $user['balance'] ?? 0 ?>" step="0.01" min="0"
                           class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                </div>

                <!-- Date of Birth -->
                <div>
                    <label for="date_of_birth" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?= $user['date_of_birth'] ?? '' ?>"
                           class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                </div>
            </div>

            <!-- Address Section -->
            <div class="tw-mt-8">
                <h4 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Address Information</h4>
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                    <!-- Address -->
                    <div class="md:tw-col-span-2">
                        <label for="address" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Address</label>
                        <input type="text" id="address" name="address" value="<?= e($user['address'] ?? '') ?>"
                               class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">City</label>
                        <input type="text" id="city" name="city" value="<?= e($user['city'] ?? '') ?>"
                               class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                    </div>

                    <!-- State -->
                    <div>
                        <label for="state" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">State</label>
                        <input type="text" id="state" name="state" value="<?= e($user['state'] ?? '') ?>"
                               class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Country</label>
                        <input type="text" id="country" name="country" value="<?= e($user['country'] ?? 'Cameroon') ?>"
                               class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label for="postal_code" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?= e($user['postal_code'] ?? '') ?>"
                               class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Password Section -->
            <div class="tw-mt-8">
                <h4 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Password (Leave blank to keep current password)</h4>
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                    <!-- New Password -->
                    <div>
                        <label for="password" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">New Password</label>
                        <input type="password" id="password" name="password" minlength="8"
                               class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                               class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="tw-mt-8 tw-flex tw-items-center tw-justify-end tw-space-x-4">
                <button type="button" onclick="window.history.back()" 
                        class="tw-bg-gray-300 tw-text-gray-700 tw-rounded-md tw-px-6 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-gray-400">
                    Cancel
                </button>
                <button type="submit" 
                        class="tw-bg-blue-600 tw-text-white tw-rounded-md tw-px-6 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-blue-700">
                    <i data-feather="save" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Form submission
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate password confirmation
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    
    if (password && password !== passwordConfirmation) {
        alert('Passwords do not match');
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-animate-spin"></i>Updating...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    const userId = formData.get('user_id');
    
    fetch(`<?= url('/admin/users') ?>/${userId}`, {
        method: 'PUT',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User updated successfully!');
            window.location.href = `<?= url('/admin/users') ?>/${userId}`;
        } else {
            alert('Error: ' + (data.message || 'Failed to update user'));
            if (data.errors) {
                console.log('Validation errors:', data.errors);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the user');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
});
</script>
