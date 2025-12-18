<?php
/**
 * Admin User Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'users';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">User Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage all users, roles, and permissions across the platform
            </p>
        </div>
        <div class="tw-flex tw-space-x-2 sm:tw-space-x-3">
            <button class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Export Users">
                <i data-feather="download" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Export Users</span>
            </button>
            <a href="<?= url('/admin/users/create') ?>" 
               class="tw-bg-primary-600 tw-border tw-border-primary-500 tw-text-white tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
               title="Add User">
                <i data-feather="user-plus" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Add User</span>
            </a>
        </div>
    </div>
</div>

<!-- User Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Users</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_users'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">+12% this month</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Customers</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['customers'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500"><?= $stats['total_users'] > 0 ? round(($stats['customers'] / $stats['total_users']) * 100) : 0 ?>% of total</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="user" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Vendors</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['vendors'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-yellow-600"><?= $stats['pending_vendors'] ?? 0 ?> pending approval</p>
            </div>
            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Delivery Riders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['riders'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600"><?= $stats['online_riders'] ?? 0 ?> online now</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="truck" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-flex-col tw-md:tw-flex-row tw-md:tw-items-center tw-md:tw-justify-between tw-space-y-4 tw-md:tw-space-y-0">
            <!-- Search -->
            <div class="tw-flex-1 tw-max-w-lg">
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                        <i data-feather="search" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                    </div>
                    <input type="text" id="user-search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-leading-5 tw-bg-white tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-1 focus:tw-ring-primary-500 focus:tw-border-primary-500" placeholder="Search users by name, email, or ID..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="tw-flex tw-space-x-4">
                <select id="role-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Roles</option>
                    <option value="customer" <?= ($role ?? '') === 'customer' ? 'selected' : '' ?>>Customers</option>
                    <option value="vendor" <?= ($role ?? '') === 'vendor' ? 'selected' : '' ?>>Vendors</option>
                    <option value="rider" <?= ($role ?? '') === 'rider' ? 'selected' : '' ?>>Riders</option>
                    <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admins</option>
                    <option value="affiliate" <?= ($role ?? '') === 'affiliate' ? 'selected' : '' ?>>Affiliates</option>
                </select>
                
                <select id="status-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Status</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">All Users</h2>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <span class="tw-text-sm tw-text-gray-500">Showing 1-20 of <?= number_format($totalUsers ?? 2847) ?> users</span>
                <div class="tw-flex tw-space-x-1">
                    <button class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600">
                        <i data-feather="chevron-left" class="tw-h-4 tw-w-4"></i>
                    </button>
                    <button class="tw-p-1 tw-rounded tw-text-gray-400 hover:tw-text-gray-600">
                        <i data-feather="chevron-right" class="tw-h-4 tw-w-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        <input type="checkbox" class="tw-rounded tw-border-gray-300 tw-text-primary-600 focus:tw-ring-primary-500">
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">User</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Role</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Joined</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Last Active</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="users-table-body">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $userData): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <input type="checkbox" class="tw-rounded tw-border-gray-300 tw-text-primary-600 focus:tw-ring-primary-500" value="<?= $userData['id'] ?>">
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-white tw-font-semibold tw-text-sm">
                                    <?= strtoupper(substr($userData['first_name'] ?? 'U', 0, 1)) ?>
                                </span>
                            </div>
                            <div class="tw-ml-4">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '')) ?></div>
                                <div class="tw-text-sm tw-text-gray-500"><?= e($userData['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                            <?php 
                            switch($userData['role']) {
                                case 'admin': echo 'tw-bg-purple-100 tw-text-purple-800'; break;
                                case 'vendor': echo 'tw-bg-orange-100 tw-text-orange-800'; break;
                                case 'rider': echo 'tw-bg-blue-100 tw-text-blue-800'; break;
                                default: echo 'tw-bg-green-100 tw-text-green-800';
                            }
                            ?>">
                            <?= ucfirst($userData['role']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                            <?php 
                            switch($userData['status']) {
                                case 'active': echo 'tw-bg-green-100 tw-text-green-800'; break;
                                case 'inactive': echo 'tw-bg-gray-100 tw-text-gray-800'; break;
                                case 'suspended': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                case 'pending': echo 'tw-bg-yellow-100 tw-text-yellow-800'; break;
                                default: echo 'tw-bg-gray-100 tw-text-gray-800';
                            }
                            ?>">
                            <?= ucfirst($userData['status']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= date('M j, Y', strtotime($userData['joined'] ?? $userData['created_at'] ?? 'now')) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= $userData['last_active'] ?? 'Never' ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-2">
                            <a href="<?= url('/admin/users/' . $userData['id']) ?>" class="tw-text-primary-600 hover:tw-text-primary-900" title="View User">
                                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                            </a>
                            <a href="<?= url('/admin/users/' . $userData['id'] . '/edit') ?>" class="tw-text-blue-600 hover:tw-text-blue-900" title="Edit User">
                                <i data-feather="edit" class="tw-h-4 tw-w-4"></i>
                            </a>
                            <button class="tw-text-orange-600 hover:tw-text-orange-900" onclick="toggleUserStatus(<?= $userData['id'] ?>, '<?= $userData['status'] ?>')" title="Toggle Status">
                                <i data-feather="<?= $userData['status'] === 'active' ? 'user-x' : 'user-check' ?>" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <button class="tw-text-purple-600 hover:tw-text-purple-900" onclick="resetUserPassword(<?= $userData['id'] ?>)" title="Reset Password">
                                <i data-feather="key" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <button class="tw-text-red-600 hover:tw-text-red-900" onclick="deleteUser(<?= $userData['id'] ?>)" title="Delete User">
                                <i data-feather="trash-2" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" class="tw-px-6 tw-py-12 tw-text-center">
                        <div class="tw-flex tw-flex-col tw-items-center">
                            <i data-feather="users" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-4"></i>
                            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No Users Found</h3>
                            <p class="tw-text-sm tw-text-gray-500 tw-mb-4">There are currently no users to display.</p>
                            <button onclick="refreshUsers()" class="tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                Refresh
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="tw-bg-white tw-px-4 tw-py-3 tw-flex tw-items-center tw-justify-between tw-border-t tw-border-gray-200 tw-sm:tw-px-6">
        <div class="tw-flex-1 tw-flex tw-justify-between tw-sm:tw-hidden">
            <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                Previous
            </button>
            <button class="tw-ml-3 tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                Next
            </button>
        </div>
        <div class="tw-hidden tw-sm:tw-flex-1 tw-sm:tw-flex tw-sm:tw-items-center tw-sm:tw-justify-between">
            <div>
                <p class="tw-text-sm tw-text-gray-700">
                    Showing <span class="tw-font-medium">1</span> to <span class="tw-font-medium">20</span> of <span class="tw-font-medium"><?= number_format($totalUsers ?? 2847) ?></span> results
                </p>
            </div>
            <div>
                <nav class="tw-relative tw-z-0 tw-inline-flex tw-rounded-md tw-shadow-sm tw--space-x-px" aria-label="Pagination">
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-2 tw-py-2 tw-rounded-l-md tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-500 hover:tw-bg-gray-50">
                        <i data-feather="chevron-left" class="tw-h-5 tw-w-5"></i>
                    </button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">1</button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">2</button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">3</button>
                    <button class="tw-relative tw-inline-flex tw-items-center tw-px-2 tw-py-2 tw-rounded-r-md tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-500 hover:tw-bg-gray-50">
                        <i data-feather="chevron-right" class="tw-h-5 tw-w-5"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
// User management functions
function toggleUserStatus(userId, currentStatus) {
    const action = currentStatus === 'active' ? 'deactivate' : 'activate';
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        fetch(`<?= url('/admin/users') ?>/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating user status.', 'error');
        });
    }
}

function resetUserPassword(userId) {
    if (confirm('Are you sure you want to reset this user\'s password? A temporary password will be generated.')) {
        fetch(`<?= url('/admin/users') ?>/${userId}/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Password reset successfully. Temporary password: ' + data.temp_password, 'success');
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while resetting password.', 'error');
        });
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch(`<?= url('/admin/users') ?>/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting user.', 'error');
        });
    }
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-md tw-shadow-lg tw-z-50 ${
        type === 'success' ? 'tw-bg-green-500' : 'tw-bg-red-500'
    } tw-text-white tw-max-w-md`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Search and filter functionality
document.getElementById('user-search').addEventListener('input', function() {
    // Implement search functionality
    filterUsers();
});

document.getElementById('role-filter').addEventListener('change', function() {
    filterUsers();
});

document.getElementById('status-filter').addEventListener('change', function() {
    filterUsers();
});

function filterUsers() {
    const search = document.getElementById('user-search').value;
    const role = document.getElementById('role-filter').value;
    const status = document.getElementById('status-filter').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (role) params.append('role', role);
    if (status) params.append('status', status);
    
    // Update URL and reload content
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

function refreshUsers() {
    window.location.reload();
}

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
