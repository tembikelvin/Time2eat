<?php
/**
 * Admin Users List View
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
                Manage all users on the Time2Eat platform
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button onclick="exportUsers()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-flex tw-items-center tw-justify-center">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Export Users
            </button>
            <a href="<?= url('/admin/users/create') ?>" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-flex tw-items-center tw-justify-center">
                <i data-feather="user-plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Add User
            </a>
        </div>
    </div>
</div>

    <!-- Filters -->
    <div class="tw-mb-6 tw-bg-white tw-shadow tw-rounded-lg tw-p-6">
        <form method="GET" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-4">
            <div>
                <label for="search" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Search</label>
                <input type="text" id="search" name="search" value="<?= e($_GET['search'] ?? '') ?>" 
                       placeholder="Name, email, or phone..."
                       class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
            </div>
            
            <div>
                <label for="role" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Role</label>
                <select id="role" name="role" class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                    <option value="all" <?= ($_GET['role'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Roles</option>
                    <option value="customer" <?= ($_GET['role'] ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
                    <option value="vendor" <?= ($_GET['role'] ?? '') === 'vendor' ? 'selected' : '' ?>>Vendor</option>
                    <option value="rider" <?= ($_GET['role'] ?? '') === 'rider' ? 'selected' : '' ?>>Rider</option>
                    <option value="admin" <?= ($_GET['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            
            <div>
                <label for="status" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Status</label>
                <select id="status" name="status" class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-blue-500 focus:tw-ring-blue-500">
                    <option value="all" <?= ($_GET['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($_GET['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            
            <div class="tw-flex tw-items-end">
                <button type="submit" class="tw-w-full tw-bg-gray-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-gray-700 tw-flex tw-items-center tw-justify-center">
                    <i data-feather="search" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">
                Users (<?= number_format($totalUsers ?? 0) ?> total)
            </h3>
        </div>
        
        <?php if (!empty($users)): ?>
        <div class="tw-overflow-x-auto">
            <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                <thead class="tw-bg-gray-50">
                    <tr>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">User</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Role</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Balance</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Joined</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Last Login</th>
                        <th class="tw-px-6 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:tw-bg-gray-50">
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <div class="tw-flex tw-items-center">
                                <div class="tw-h-10 tw-w-10 tw-flex-shrink-0">
                                    <div class="tw-h-10 tw-w-10 tw-rounded-full tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-flex tw-items-center tw-justify-center">
                                        <span class="tw-text-white tw-font-semibold tw-text-sm">
                                            <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="tw-ml-4">
                                    <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                        <?= e($user['first_name'] ?? '') ?> <?= e($user['last_name'] ?? '') ?>
                                    </div>
                                    <div class="tw-text-sm tw-text-gray-500">
                                        <?= e($user['email'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                                <?= ($user['role'] ?? '') === 'admin' ? 'tw-bg-red-100 tw-text-red-800' : 
                                    (($user['role'] ?? '') === 'vendor' ? 'tw-bg-blue-100 tw-text-blue-800' : 
                                    (($user['role'] ?? '') === 'rider' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-gray-100 tw-text-gray-800')) ?>">
                                <?= ucfirst($user['role'] ?? 'customer') ?>
                            </span>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                                <?= ($user['status'] ?? '') === 'active' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-red-100 tw-text-red-800' ?>">
                                <?= ucfirst($user['status'] ?? 'inactive') ?>
                            </span>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                            <?= number_format($user['balance'] ?? 0) ?> XAF
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                            <?= date('M j, Y', strtotime($user['created_at'] ?? '')) ?>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                            <?= $user['last_login_at'] ? date('M j, Y', strtotime($user['last_login_at'])) : 'Never' ?>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-text-sm tw-font-medium">
                            <div class="tw-flex tw-items-center tw-justify-end tw-space-x-2">
                                <a href="<?= url('/admin/users/' . $user['id']) ?>" 
                                   class="tw-text-blue-600 hover:tw-text-blue-900" title="View Details">
                                    <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                                </a>
                                <a href="<?= url('/admin/users/' . $user['id'] . '/edit') ?>" 
                                   class="tw-text-green-600 hover:tw-text-green-900" title="Edit User">
                                    <i data-feather="edit" class="tw-h-4 tw-w-4"></i>
                                </a>
                                <button onclick="toggleUserStatus(<?= $user['id'] ?>)" 
                                        class="tw-text-yellow-600 hover:tw-text-yellow-900" title="Toggle Status">
                                    <i data-feather="<?= ($user['status'] ?? '') === 'active' ? 'user-x' : 'user-check' ?>" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <button onclick="deleteUser(<?= $user['id'] ?>)" 
                                        class="tw-text-red-600 hover:tw-text-red-900" title="Delete User">
                                    <i data-feather="trash-2" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="tw-bg-white tw-px-4 tw-py-3 tw-flex tw-items-center tw-justify-between tw-border-t tw-border-gray-200 sm:tw-px-6">
            <div class="tw-flex-1 tw-flex tw-justify-between sm:tw-hidden">
                <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>&<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY)) ?>" 
                   class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                    Previous
                </a>
                <?php endif; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>&<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY)) ?>" 
                   class="tw-ml-3 tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                    Next
                </a>
                <?php endif; ?>
            </div>
            
            <div class="tw-hidden sm:tw-flex-1 sm:tw-flex sm:tw-items-center sm:tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-text-gray-700">
                        Showing page <span class="tw-font-medium"><?= $currentPage ?></span> of <span class="tw-font-medium"><?= $totalPages ?></span>
                    </p>
                </div>
                <div>
                    <nav class="tw-relative tw-z-0 tw-inline-flex tw-rounded-md tw-shadow-sm -tw-space-x-px">
                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <a href="?page=<?= $i ?>&<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY)) ?>" 
                           class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-text-sm tw-font-medium 
                               <?= $i === $currentPage ? 'tw-z-10 tw-bg-blue-50 tw-border-blue-500 tw-text-blue-600' : 'tw-bg-white tw-border-gray-300 tw-text-gray-500 hover:tw-bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="tw-text-center tw-py-12">
            <i data-feather="users" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400"></i>
            <h3 class="tw-mt-2 tw-text-sm tw-font-medium tw-text-gray-900">No users found</h3>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Try adjusting your search criteria or create a new user.</p>
        </div>
        <?php endif; ?>
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
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete user'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting user');
        });
    }

    function exportUsers() {
        // Show loading state
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="tw-animate-spin tw-h-4 tw-w-4 tw-inline tw-mr-2">⟳</i>Exporting...';
        button.disabled = true;

        // Create download link for users export
        const link = document.createElement('a');
        link.href = '/admin/export/users';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Reset button state after delay
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }
}
</script>
