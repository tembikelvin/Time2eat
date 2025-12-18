<?php
/**
 * Admin User Details View
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'users';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">User Details</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                View detailed information about <?= e($user['first_name'] ?? 'User') ?> <?= e($user['last_name'] ?? '') ?>
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <a href="<?= url('/admin/users') ?>" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                <i data-feather="arrow-left" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Back to Users
            </a>
            <a href="<?= url('/admin/users/' . $user['id'] . '/edit') ?>" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                <i data-feather="edit" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Edit User
            </a>
        </div>
    </div>
</div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
        <!-- User Information -->
        <div class="lg:tw-col-span-2">
            <div class="tw-bg-white tw-shadow tw-rounded-lg">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">User Information</h3>
                </div>
                
                <div class="tw-p-6">
                    <dl class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Full Name</dt>
                            <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($user['first_name'] ?? '') ?> <?= e($user['last_name'] ?? '') ?></dd>
                        </div>
                        
                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Email</dt>
                            <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($user['email'] ?? '') ?></dd>
                        </div>
                        
                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Phone</dt>
                            <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= e($user['phone'] ?? 'Not provided') ?></dd>
                        </div>

                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Role</dt>
                            <dd class="tw-mt-1">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium
                                    <?= ($user['role'] ?? '') === 'admin' ? 'tw-bg-red-100 tw-text-red-800' :
                                        (($user['role'] ?? '') === 'vendor' ? 'tw-bg-blue-100 tw-text-blue-800' :
                                        (($user['role'] ?? '') === 'rider' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-gray-100 tw-text-gray-800')) ?>">
                                    <?= ucfirst($user['role'] ?? 'customer') ?>
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Status</dt>
                            <dd class="tw-mt-1">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium
                                    <?= ($user['status'] ?? '') === 'active' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-red-100 tw-text-red-800' ?>">
                                    <?= ucfirst($user['status'] ?? 'inactive') ?>
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Balance</dt>
                            <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= number_format($user['balance'] ?? 0) ?> XAF</dd>
                        </div>

                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Joined</dt>
                            <dd class="tw-mt-1 tw-text-sm tw-text-gray-900"><?= date('M j, Y', strtotime($user['created_at'] ?? '')) ?></dd>
                        </div>

                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Last Login</dt>
                            <dd class="tw-mt-1 tw-text-sm tw-text-gray-900">
                                <?= isset($user['last_login_at']) && $user['last_login_at'] ? date('M j, Y g:i A', strtotime($user['last_login_at'])) : 'Never' ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div>
            <div class="tw-bg-white tw-shadow tw-rounded-lg">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Statistics</h3>
                </div>
                
                <div class="tw-p-6">
                    <dl class="tw-space-y-4">
                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Total Orders</dt>
                            <dd class="tw-mt-1 tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($stats['total_orders'] ?? 0) ?></dd>
                        </div>
                        
                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Total Spent</dt>
                            <dd class="tw-mt-1 tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($stats['total_spent'] ?? 0) ?> XAF</dd>
                        </div>
                        
                        <?php if (($user['role'] ?? '') === 'vendor' || ($user['role'] ?? '') === 'rider'): ?>
                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Total Earnings</dt>
                            <dd class="tw-mt-1 tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($stats['total_earnings'] ?? 0) ?> XAF</dd>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (($user['role'] ?? '') === 'customer'): ?>
                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Referrals</dt>
                            <dd class="tw-mt-1 tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($stats['referrals_count'] ?? 0) ?></dd>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <dt class="tw-text-sm tw-font-medium tw-text-gray-500">Account Age</dt>
                            <dd class="tw-mt-1 tw-text-lg tw-font-medium tw-text-gray-900"><?= $stats['account_age_days'] ?? 0 ?> days</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="tw-mt-6 tw-bg-white tw-shadow tw-rounded-lg">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Quick Actions</h3>
                </div>
                
                <div class="tw-p-6 tw-space-y-3">
                    <button onclick="toggleUserStatus(<?= $user['id'] ?>)"
                        class="tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-rounded-md
                                <?= ($user['status'] ?? '') === 'active' ? 'tw-bg-red-100 tw-text-red-700 hover:tw-bg-red-200' : 'tw-bg-green-100 tw-text-green-700 hover:tw-bg-green-200' ?>">
                        <i data-feather="<?= ($user['status'] ?? '') === 'active' ? 'user-x' : 'user-check' ?>" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                        <?= ($user['status'] ?? '') === 'active' ? 'Deactivate User' : 'Activate User' ?>
                    </button>

                    <button onclick="resetUserPassword(<?= $user['id'] ?>)"
                        class="tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-blue-700 tw-bg-blue-100 tw-rounded-md hover:tw-bg-blue-200">
                        <i data-feather="key" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                        Reset Password
                    </button>

                    <button onclick="deleteUser(<?= $user['id'] ?>)"
                        class="tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-red-700 tw-bg-red-100 tw-rounded-md hover:tw-bg-red-200">
                        <i data-feather="trash-2" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                        Delete User
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

function toggleUserStatus(userId) {
    if (confirm('Are you sure you want to change this user\'s status?')) {
        fetch(`<?= url('/admin/users') ?>/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to update user status'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating user status');
        });
    }
}

function resetUserPassword(userId) {
    if (confirm('Are you sure you want to reset this user\'s password? They will receive an email with a new password.')) {
        fetch(`<?= url('/admin/users') ?>/${userId}/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Password reset successfully. The user will receive an email with their new password.');
            } else {
                alert('Error: ' + (data.message || 'Failed to reset password'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while resetting password');
        });
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch(`<?= url('/admin/users') ?>/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?= url('/admin/users') ?>';
            } else {
                alert('Error: ' + (data.message || 'Failed to delete user'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting user');
        });
    }
}
</script>
