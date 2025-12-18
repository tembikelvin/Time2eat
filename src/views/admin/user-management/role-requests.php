<?php
/**
 * Admin Role Change Requests & Approvals Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'role-requests';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Role Change Requests & Approvals</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Review and manage user role change requests, user applications, and restaurant approvals
            </p>
        </div>
        <div class="tw-flex tw-space-x-2 sm:tw-space-x-3">
            <button onclick="exportRequests()" 
                    class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Export Requests">
                <i data-feather="download" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Export</span>
            </button>
            <button onclick="refreshRequests()" 
                    class="tw-bg-primary-600 tw-border tw-border-primary-500 tw-text-white tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Refresh">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Refresh</span>
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-blue-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="inbox" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Total Pending</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['total_pending'] ?? 0) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="tw-bg-gray-50 tw-px-5 tw-py-3">
            <div class="tw-text-sm">
                <span class="tw-font-medium tw-text-blue-600">All types</span>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-yellow-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Role Requests</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['pending_requests'] ?? 0) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="tw-bg-gray-50 tw-px-5 tw-py-3">
            <div class="tw-text-sm">
                <span class="tw-font-medium tw-text-yellow-600">Needs review</span>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-orange-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="users" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Vendor Applications</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['pending_vendors'] ?? 0) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="tw-bg-gray-50 tw-px-5 tw-py-3">
            <div class="tw-text-sm">
                <span class="tw-font-medium tw-text-orange-600">Vendor/Rider</span>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-purple-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Restaurant Apps</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['pending_restaurants'] ?? 0) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="tw-bg-gray-50 tw-px-5 tw-py-3">
            <div class="tw-text-sm">
                <span class="tw-font-medium tw-text-purple-600">Pending approval</span>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Tabs -->
<div class="tw-bg-white tw-shadow tw-rounded-lg tw-mb-8">
    <div class="tw-px-4 tw-py-5 tw-sm:tw-px-6 tw-border-b tw-border-gray-200">
        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Filters & Categories</h3>
        <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Filter and categorize different types of requests</p>
    </div>
    <div class="tw-p-6">
        <div class="tw-flex tw-flex-wrap tw-items-end tw-gap-4">
            <div class="tw-flex-1 tw-min-w-0 tw-sm:tw-w-48">
                <label for="type-filter" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Category</label>
                <select id="type-filter" class="tw-w-full tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All Requests</option>
                    <option value="role_requests" <?= $type === 'role_requests' ? 'selected' : '' ?>>Role Change Requests</option>
                    <option value="vendor_applications" <?= $type === 'vendor_applications' ? 'selected' : '' ?>>Vendor Applications</option>
                    <option value="rider_applications" <?= $type === 'rider_applications' ? 'selected' : '' ?>>Rider Applications</option>
                </select>
            </div>
            <div class="tw-flex-1 tw-min-w-0 tw-sm:tw-w-48">
                <label for="status-filter" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Status</label>
                <select id="status-filter" class="tw-w-full tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="tw-flex tw-items-end">
                <button onclick="applyFilters()" 
                        class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center">
                    <i data-feather="filter" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Role Change Requests Table -->
<?php if ($type === 'all' || $type === 'role_requests'): ?>
<div class="tw-bg-white tw-shadow tw-rounded-lg tw-mb-8 tw-overflow-hidden">
    <div class="tw-px-4 tw-py-5 tw-sm:tw-px-6 tw-border-b tw-border-gray-200">
        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Role Change Requests</h3>
        <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Manage user role change requests</p>
    </div>

    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">User</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Role Change</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Reason</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="6" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                        <i data-feather="inbox" class="tw-h-12 tw-w-12 tw-mx-auto tw-mb-4 tw-text-gray-400"></i>
                        <p class="tw-text-lg tw-font-medium">No role change requests found</p>
                        <p class="tw-text-sm">Role change requests will appear here when users submit them.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($requests as $request): ?>
                <tr class="hover:tw-bg-gray-50" data-request-id="<?= $request['id'] ?>">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-white tw-font-semibold tw-text-sm">
                                    <?= strtoupper(substr($request['first_name'] ?? 'U', 0, 1)) ?>
                                </span>
                            </div>
                            <div class="tw-ml-4">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                    <?= htmlspecialchars($request['first_name'] ?? '') ?> <?= htmlspecialchars($request['last_name'] ?? '') ?>
                                </div>
                                <div class="tw-text-sm tw-text-gray-500"><?= htmlspecialchars($request['email'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-gray-100 tw-text-gray-800 tw-capitalize">
                                <?= htmlspecialchars($request['current_role'] ?? '') ?>
                            </span>
                            <i data-feather="arrow-right" class="tw-h-4 tw-w-4 tw-text-gray-400"></i>
                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800 tw-capitalize">
                                <?= htmlspecialchars($request['requested_role'] ?? '') ?>
                            </span>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4">
                        <div class="tw-text-sm tw-text-gray-900 tw-max-w-xs tw-truncate" title="<?= htmlspecialchars($request['reason'] ?? '') ?>">
                            <?= htmlspecialchars($request['reason'] ?? 'No reason provided') ?>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <?php
                        $statusClass = match($request['status']) {
                            'pending' => 'tw-bg-yellow-100 tw-text-yellow-800',
                            'approved' => 'tw-bg-green-100 tw-text-green-800',
                            'rejected' => 'tw-bg-red-100 tw-text-red-800',
                            default => 'tw-bg-gray-100 tw-text-gray-800'
                        };
                        ?>
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium <?= $statusClass ?> tw-capitalize">
                            <?= htmlspecialchars($request['status'] ?? '') ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= date('M j, Y', strtotime($request['created_at'])) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <?php if ($request['status'] === 'pending'): ?>
                        <div class="tw-flex tw-space-x-2">
                            <button onclick="reviewRequest(<?= $request['id'] ?>, 'approve')" 
                                    class="tw-text-green-600 hover:tw-text-green-900 tw-transition-colors"
                                    title="Approve Request">
                                <i data-feather="check-circle" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <button onclick="reviewRequest(<?= $request['id'] ?>, 'reject')" 
                                    class="tw-text-red-600 hover:tw-text-red-900 tw-transition-colors"
                                    title="Reject Request">
                                <i data-feather="x-circle" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <span class="tw-text-gray-400 tw-text-xs">
                                <?php if ($request['admin_first_name']): ?>
                                    Reviewed by <?= htmlspecialchars($request['admin_first_name']) ?> <?= htmlspecialchars($request['admin_last_name']) ?>
                                <?php else: ?>
                                    System
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Vendor Applications Table -->
<?php if ($type === 'all' || $type === 'vendor_applications'): ?>
<div class="tw-bg-white tw-shadow tw-rounded-lg tw-mb-8 tw-overflow-hidden">
    <div class="tw-px-4 tw-py-5 tw-sm:tw-px-6 tw-border-b tw-border-gray-200">
        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Vendor Applications</h3>
        <p class="tw-mt-1 tw-text-sm tw-text-gray-500">New vendor applications (includes restaurant ownership)</p>
    </div>

    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">User</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Application Type</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Contact</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (empty($pendingVendors)): ?>
                <tr>
                    <td colspan="6" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                        <i data-feather="users" class="tw-h-12 tw-w-12 tw-mx-auto tw-mb-4 tw-text-gray-400"></i>
                        <p class="tw-text-lg tw-font-medium">No pending vendor applications</p>
                        <p class="tw-text-sm">New vendor applications will appear here when submitted.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($pendingVendors as $user): ?>
                <tr class="hover:tw-bg-gray-50" data-user-id="<?= $user['id'] ?>">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-white tw-font-semibold tw-text-sm">
                                    <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                                </span>
                            </div>
                            <div class="tw-ml-4">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                    <?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?>
                                </div>
                                <div class="tw-text-sm tw-text-gray-500"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-orange-100 tw-text-orange-800 tw-capitalize">
                            <?= htmlspecialchars($user['application_type'] ?? $user['role']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= htmlspecialchars($user['phone'] ?? 'No phone') ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800 tw-capitalize">
                            <?= htmlspecialchars($user['status'] ?? '') ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= date('M j, Y', strtotime($user['created_at'])) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-2">
                            <button onclick="approveUser(<?= $user['id'] ?>)" 
                                    class="tw-text-green-600 hover:tw-text-green-900 tw-transition-colors"
                                    title="Approve User">
                                <i data-feather="check-circle" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <button onclick="rejectUser(<?= $user['id'] ?>)" 
                                    class="tw-text-red-600 hover:tw-text-red-900 tw-transition-colors"
                                    title="Reject User">
                                <i data-feather="x-circle" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Rider Applications Table -->
<?php if ($type === 'all' || $type === 'rider_applications'): ?>
<div class="tw-bg-white tw-shadow tw-rounded-lg tw-mb-8 tw-overflow-hidden">
    <div class="tw-px-4 tw-py-5 tw-sm:tw-px-6 tw-border-b tw-border-gray-200">
        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Rider Applications</h3>
        <p class="tw-mt-1 tw-text-sm tw-text-gray-500">New delivery rider applications</p>
    </div>

    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">User</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Application Type</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Contact</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (empty($pendingRiders)): ?>
                <tr>
                    <td colspan="6" class="tw-px-6 tw-py-12 tw-text-center tw-text-gray-500">
                        <i data-feather="truck" class="tw-h-12 tw-w-12 tw-mx-auto tw-mb-4 tw-text-gray-400"></i>
                        <p class="tw-text-lg tw-font-medium">No pending rider applications</p>
                        <p class="tw-text-sm">New rider applications will appear here when submitted.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($pendingRiders as $user): ?>
                <tr class="hover:tw-bg-gray-50" data-user-id="<?= $user['id'] ?>">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-bg-gradient-to-r tw-from-blue-500 tw-to-cyan-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-white tw-font-semibold tw-text-sm">
                                    <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                                </span>
                            </div>
                            <div class="tw-ml-4">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                    <?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?>
                                </div>
                                <div class="tw-text-sm tw-text-gray-500"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800 tw-capitalize">
                            <?= htmlspecialchars($user['application_type'] ?? $user['role']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= htmlspecialchars($user['phone'] ?? 'No phone') ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800 tw-capitalize">
                            <?= htmlspecialchars($user['status'] ?? '') ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= date('M j, Y', strtotime($user['created_at'])) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-2">
                            <button onclick="approveUser(<?= $user['id'] ?>)" 
                                    class="tw-text-green-600 hover:tw-text-green-900 tw-transition-colors"
                                    title="Approve User">
                                <i data-feather="check-circle" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <button onclick="rejectUser(<?= $user['id'] ?>)" 
                                    class="tw-text-red-600 hover:tw-text-red-900 tw-transition-colors"
                                    title="Reject User">
                                <i data-feather="x-circle" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


<!-- Review Modal -->
<div id="reviewModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 id="reviewModalTitle" class="tw-text-lg tw-font-medium tw-text-gray-900">Review Request</h3>
                <button onclick="closeReviewModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <div class="tw-mb-4">
                <label for="adminNotes" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Admin Notes</label>
                <textarea id="adminNotes" 
                          rows="4" 
                          class="tw-w-full tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500"
                          placeholder="Add notes about your decision..."></textarea>
            </div>
            
            <div id="reviewError" class="tw-mb-4 tw-hidden">
                <div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-md tw-p-3">
                    <p class="tw-text-sm tw-text-red-600" id="reviewErrorMessage"></p>
                </div>
            </div>
            
            <div class="tw-flex tw-items-center tw-justify-end tw-pt-4 tw-border-t tw-border-gray-200 tw-space-x-3">
                <button onclick="closeReviewModal()" 
                        class="tw-px-4 tw-py-2 tw-bg-gray-300 tw-text-gray-700 tw-rounded-md hover:tw-bg-gray-400 tw-transition-colors">
                    Cancel
                </button>
                <button onclick="submitReview()" 
                        id="submitReviewBtn"
                        class="tw-px-4 tw-py-2 tw-text-white tw-rounded-md tw-transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Approval Modal (Mobile-First) -->
<div id="userApprovalModal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-min-h-screen tw-flex tw-items-center tw-justify-center tw-p-4">
        <div class="tw-relative tw-w-full tw-max-w-md tw-bg-white tw-rounded-lg tw-shadow-xl">
            <!-- Modal Header -->
            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-border-b tw-border-gray-200">
                <h3 id="userApprovalModalTitle" class="tw-text-lg tw-font-semibold tw-text-gray-900">Approve User</h3>
                <button onclick="closeUserApprovalModal()" class="tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="tw-p-4">
                <div class="tw-mb-4">
                    <div class="tw-flex tw-items-center tw-space-x-3 tw-mb-3">
                        <div id="userAvatar" class="tw-h-12 tw-w-12 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                            <span id="userInitials" class="tw-text-white tw-font-semibold tw-text-lg">U</span>
                        </div>
                        <div>
                            <h4 id="userName" class="tw-text-lg tw-font-medium tw-text-gray-900">User Name</h4>
                            <p id="userEmail" class="tw-text-sm tw-text-gray-500">user@example.com</p>
                        </div>
                    </div>
                    <div class="tw-bg-gray-50 tw-rounded-lg tw-p-3">
                        <p class="tw-text-sm tw-text-gray-600">
                            <span id="applicationType" class="tw-font-medium">Application Type:</span> 
                            <span id="applicationTypeValue" class="tw-ml-1">Vendor Application</span>
                        </p>
                        <p class="tw-text-sm tw-text-gray-600 tw-mt-1">
                            <span class="tw-font-medium">Status:</span> 
                            <span id="currentStatus" class="tw-ml-1 tw-px-2 tw-py-1 tw-bg-yellow-100 tw-text-yellow-800 tw-rounded-full tw-text-xs">Pending</span>
                        </p>
                    </div>
                </div>
                
                <div class="tw-mb-4">
                    <label for="approvalNotes" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Admin Notes (Optional)</label>
                    <textarea id="approvalNotes" 
                              rows="3" 
                              class="tw-w-full tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500"
                              placeholder="Add any notes about this approval..."></textarea>
                </div>
                
                <div id="approvalError" class="tw-mb-4 tw-hidden">
                    <div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-md tw-p-3">
                        <p class="tw-text-sm tw-text-red-600" id="approvalErrorMessage"></p>
                    </div>
                </div>
            </div>
            
            <!-- Debug Information -->
            <div id="approvalDebugInfo" class="tw-mb-4 tw-p-3 tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-md tw-hidden">
                <h4 class="tw-text-sm tw-font-medium tw-text-yellow-800 tw-mb-2">Debug Information:</h4>
                <div id="debugContent" class="tw-text-xs tw-text-yellow-700 tw-space-y-1">
                    <!-- Debug info will be populated here -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3 tw-p-4 tw-border-t tw-border-gray-200">
                <button onclick="closeUserApprovalModal()" 
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-md hover:tw-bg-gray-200 tw-transition-colors tw-font-medium">
                    Cancel
                </button>
                <button onclick="submitUserApproval()" 
                        id="submitUserApprovalBtn"
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-green-600 tw-text-white tw-rounded-md hover:tw-bg-green-700 tw-transition-colors tw-font-medium tw-cursor-pointer"
                        style="min-height: 40px; display: flex; align-items: center; justify-content: center;">
                    <span id="approvalButtonText">Approve User</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Rejection Modal (Mobile-First) -->
<div id="userRejectionModal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-min-h-screen tw-flex tw-items-center tw-justify-center tw-p-4">
        <div class="tw-relative tw-w-full tw-max-w-md tw-bg-white tw-rounded-lg tw-shadow-xl">
            <!-- Modal Header -->
            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-border-b tw-border-gray-200">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Reject User</h3>
                <button onclick="closeUserRejectionModal()" class="tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="tw-p-4">
                <div class="tw-mb-4">
                    <div class="tw-flex tw-items-center tw-space-x-3 tw-mb-3">
                        <div id="rejectUserAvatar" class="tw-h-12 tw-w-12 tw-bg-gradient-to-r tw-from-red-500 tw-to-pink-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                            <span id="rejectUserInitials" class="tw-text-white tw-font-semibold tw-text-lg">U</span>
                        </div>
                        <div>
                            <h4 id="rejectUserName" class="tw-text-lg tw-font-medium tw-text-gray-900">User Name</h4>
                            <p id="rejectUserEmail" class="tw-text-sm tw-text-gray-500">user@example.com</p>
                        </div>
                    </div>
                    <div class="tw-bg-red-50 tw-rounded-lg tw-p-3">
                        <p class="tw-text-sm tw-text-red-600">
                            <i data-feather="alert-triangle" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                            This action will reject the user's application and notify them of the rejection.
                        </p>
                    </div>
                </div>
                
                <div class="tw-mb-4">
                    <label for="rejectionReason" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Reason for Rejection <span class="tw-text-red-500">*</span></label>
                    <textarea id="rejectionReason" 
                              rows="3" 
                              class="tw-w-full tw-border tw-border-gray-300 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-500 focus:tw-border-red-500"
                              placeholder="Please provide a reason for rejection..."
                              required></textarea>
                </div>
                
                <div id="rejectionError" class="tw-mb-4 tw-hidden">
                    <div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-md tw-p-3">
                        <p class="tw-text-sm tw-text-red-600" id="rejectionErrorMessage"></p>
                    </div>
                </div>
            </div>
            
            <!-- Debug Information -->
            <div id="rejectionDebugInfo" class="tw-mb-4 tw-p-3 tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-md tw-hidden">
                <h4 class="tw-text-sm tw-font-medium tw-text-yellow-800 tw-mb-2">Debug Information:</h4>
                <div id="rejectionDebugContent" class="tw-text-xs tw-text-yellow-700 tw-space-y-1">
                    <!-- Debug info will be populated here -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3 tw-p-4 tw-border-t tw-border-gray-200">
                <button onclick="closeUserRejectionModal()" 
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-md hover:tw-bg-gray-200 tw-transition-colors tw-font-medium">
                    Cancel
                </button>
                <button onclick="submitUserRejection()" 
                        id="submitUserRejectionBtn"
                        class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-red-600 tw-text-white tw-rounded-md hover:tw-bg-red-700 tw-transition-colors tw-font-medium">
                    <span id="rejectionButtonText">Reject User</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Role requests and approvals page JavaScript
let currentRequestId = null;
let currentAction = null;

function applyFilters() {
    const type = document.getElementById('type-filter').value;
    const status = document.getElementById('status-filter').value;
    const url = new URL(window.location);
    
    if (type) {
        url.searchParams.set('type', type);
    } else {
        url.searchParams.delete('type');
    }
    
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    
    window.location.href = url.toString();
}

// Role change request functions
function reviewRequest(requestId, action) {
    currentRequestId = requestId;
    currentAction = action;
    
    const title = action === 'approve' ? 'Approve Role Change Request' : 'Reject Role Change Request';
    const btnClass = action === 'approve' ? 'tw-bg-green-600 hover:tw-bg-green-700' : 'tw-bg-red-600 hover:tw-bg-red-700';
    const btnText = action === 'approve' ? 'Approve' : 'Reject';
    
    document.getElementById('reviewModalTitle').textContent = title;
    document.getElementById('submitReviewBtn').className = `tw-px-4 tw-py-2 tw-text-white tw-rounded-md tw-transition-colors ${btnClass}`;
    document.getElementById('submitReviewBtn').textContent = btnText;
    document.getElementById('adminNotes').value = '';
    document.getElementById('reviewError').classList.add('tw-hidden');
    
    document.getElementById('reviewModal').classList.remove('tw-hidden');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('tw-hidden');
    currentRequestId = null;
    currentAction = null;
}

function submitReview() {
    if (!currentRequestId || !currentAction) {
        return;
    }
    
    const adminNotes = document.getElementById('adminNotes').value;
    const submitBtn = document.getElementById('submitReviewBtn');
    const errorDiv = document.getElementById('reviewError');
    const errorMessage = document.getElementById('reviewErrorMessage');
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    // Prepare data
    const data = {
        admin_notes: adminNotes
    };
    
    // Determine endpoint
    const endpoint = currentAction === 'approve' 
        ? `/admin/user-management/role-requests/${currentRequestId}/approve`
        : `/admin/user-management/role-requests/${currentRequestId}/reject`;
    
    // Make AJAX request
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeReviewModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            errorMessage.textContent = data.message || 'An error occurred';
            errorDiv.classList.remove('tw-hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorMessage.textContent = 'An error occurred while processing the request';
        errorDiv.classList.remove('tw-hidden');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = currentAction === 'approve' ? 'Approve' : 'Reject';
    });
}

// User approval functions
let currentUserId = null;
let currentUserData = null;

function approveUser(userId) {
    console.log('🔍 DEBUG: approveUser called with userId:', userId);
    
    // Get user data from the table row
    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
    console.log('🔍 DEBUG: Found row:', row);
    
    if (!row) {
        console.error('❌ DEBUG: User data not found for userId:', userId);
        showNotification('User data not found', 'error');
        return;
    }
    
    // Debug: Log all table cells
    const cells = row.querySelectorAll('td');
    console.log('🔍 DEBUG: Table cells found:', cells.length);
    cells.forEach((cell, index) => {
        console.log(`  Cell ${index}:`, cell.textContent.trim());
    });
    
    const nameElement = row.querySelector('td:first-child .tw-text-sm.tw-font-medium');
    const emailElement = row.querySelector('td:first-child .tw-text-sm.tw-text-gray-500');
    const applicationTypeElement = row.querySelector('td:nth-child(2) .tw-inline-flex');
    
    console.log('🔍 DEBUG: Name element:', nameElement);
    console.log('🔍 DEBUG: Email element:', emailElement);
    console.log('🔍 DEBUG: Application type element:', applicationTypeElement);
    
    if (!nameElement || !emailElement || !applicationTypeElement) {
        console.error('❌ DEBUG: Required elements not found');
        console.log('Available elements in first cell:', row.querySelector('td:first-child').innerHTML);
        showNotification('User data elements not found', 'error');
        return;
    }
    
    const name = nameElement.textContent.trim();
    const email = emailElement.textContent.trim();
    const applicationType = applicationTypeElement.textContent.trim();
    
    console.log('🔍 DEBUG: Extracted data:', { name, email, applicationType });
    
    currentUserId = userId;
    currentUserData = { name, email, applicationType };
    
    // Check if modal elements exist
    const modal = document.getElementById('userApprovalModal');
    const initialsElement = document.getElementById('userInitials');
    const nameElementModal = document.getElementById('userName');
    const emailElementModal = document.getElementById('userEmail');
    const applicationTypeElementModal = document.getElementById('applicationTypeValue');
    
    console.log('🔍 DEBUG: Modal elements:', {
        modal: !!modal,
        initialsElement: !!initialsElement,
        nameElementModal: !!nameElementModal,
        emailElementModal: !!emailElementModal,
        applicationTypeElementModal: !!applicationTypeElementModal
    });
    
    if (!modal) {
        console.error('❌ DEBUG: Modal not found');
        showNotification('Approval modal not found', 'error');
        return;
    }
    
    // Populate modal with user data
    if (initialsElement) initialsElement.textContent = name.split(' ').map(n => n[0]).join('').toUpperCase();
    if (nameElementModal) nameElementModal.textContent = name;
    if (emailElementModal) emailElementModal.textContent = email;
    if (applicationTypeElementModal) applicationTypeElementModal.textContent = applicationType;
    
    const notesElement = document.getElementById('approvalNotes');
    if (notesElement) notesElement.value = '';
    
    const errorElement = document.getElementById('approvalError');
    if (errorElement) errorElement.classList.add('tw-hidden');
    
    // Show debug information
    const debugInfo = document.getElementById('approvalDebugInfo');
    const debugContent = document.getElementById('debugContent');
    if (debugInfo && debugContent) {
        debugContent.innerHTML = `
            <div><strong>User ID:</strong> ${userId}</div>
            <div><strong>Name:</strong> ${name}</div>
            <div><strong>Email:</strong> ${email}</div>
            <div><strong>Application Type:</strong> ${applicationType}</div>
            <div><strong>Modal Found:</strong> ${!!modal}</div>
            <div><strong>Button Found:</strong> ${!!document.getElementById('submitUserApprovalBtn')}</div>
            <div><strong>Timestamp:</strong> ${new Date().toLocaleTimeString()}</div>
        `;
        debugInfo.classList.remove('tw-hidden');
    }
    
    // Show modal
    console.log('🔍 DEBUG: Showing modal');
    modal.classList.remove('tw-hidden');
    document.body.style.overflow = 'hidden';
    
    console.log('✅ DEBUG: Modal should now be visible');
}

function rejectUser(userId) {
    // Get user data from the table row
    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (!row) {
        showNotification('User data not found', 'error');
        return;
    }
    
    const name = row.querySelector('td:first-child .tw-text-sm.tw-font-medium').textContent.trim();
    const email = row.querySelector('td:first-child .tw-text-sm.tw-text-gray-500').textContent.trim();
    
    currentUserId = userId;
    currentUserData = { name, email };
    
    // Populate modal with user data
    document.getElementById('rejectUserInitials').textContent = name.split(' ').map(n => n[0]).join('').toUpperCase();
    document.getElementById('rejectUserName').textContent = name;
    document.getElementById('rejectUserEmail').textContent = email;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectionError').classList.add('tw-hidden');
    
    // Show modal
    document.getElementById('userRejectionModal').classList.remove('tw-hidden');
    document.body.style.overflow = 'hidden';
}

function closeUserApprovalModal() {
    document.getElementById('userApprovalModal').classList.add('tw-hidden');
    document.body.style.overflow = 'auto';
    currentUserId = null;
    currentUserData = null;
}

function closeUserRejectionModal() {
    document.getElementById('userRejectionModal').classList.add('tw-hidden');
    document.body.style.overflow = 'auto';
    currentUserId = null;
    currentUserData = null;
}

function submitUserApproval() {
    console.log('🔍 DEBUG: submitUserApproval called');
    console.log('🔍 DEBUG: currentUserId:', currentUserId);
    
    if (!currentUserId) {
        console.error('❌ DEBUG: No currentUserId set');
        return;
    }
    
    const notes = document.getElementById('approvalNotes').value;
    const submitBtn = document.getElementById('submitUserApprovalBtn');
    const errorDiv = document.getElementById('approvalError');
    const errorMessage = document.getElementById('approvalErrorMessage');
    
    console.log('🔍 DEBUG: Elements found:', {
        notes: notes,
        submitBtn: !!submitBtn,
        errorDiv: !!errorDiv,
        errorMessage: !!errorMessage
    });
    
    if (!submitBtn) {
        console.error('❌ DEBUG: Submit button not found');
        showNotification('Submit button not found', 'error');
        return;
    }
    
    // Disable button and show loading
    submitBtn.disabled = true;
    const buttonText = document.getElementById('approvalButtonText');
    if (buttonText) buttonText.textContent = 'Approving...';
    
    console.log('🔍 DEBUG: Making AJAX request to /admin/user-management/users/approve');
    console.log('🔍 DEBUG: Request data:', { 
        user_id: currentUserId,
        admin_notes: notes
    });
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    console.log('🔍 DEBUG: CSRF Token:', csrfToken);
    
    // Make AJAX request
        fetch('<?= url('/admin/user-management/users/approve') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            },
        body: JSON.stringify({ 
            user_id: currentUserId,
            admin_notes: notes,
            csrf_token: csrfToken
        })
    })
    .then(response => {
        console.log('🔍 DEBUG: Response received:', response.status, response.statusText);
        console.log('🔍 DEBUG: Response headers:', response.headers);
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        console.log('🔍 DEBUG: Content-Type:', contentType);
        
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, get the text to see what we're actually receiving
            return response.text().then(text => {
                console.error('❌ DEBUG: Expected JSON but got:', text.substring(0, 500));
                throw new Error('Server returned HTML instead of JSON. Check console for details.');
            });
        }
    })
        .then(data => {
        console.log('🔍 DEBUG: Response data:', data);
            if (data.success) {
            console.log('✅ DEBUG: Approval successful');
                showNotification(data.message, 'success');
            closeUserApprovalModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
            console.error('❌ DEBUG: Approval failed:', data.message);
            console.error('❌ DEBUG: Debug info:', data.debug);
            
            // Show detailed error information
            let errorText = data.message || 'An error occurred';
            if (data.debug) {
                if (typeof data.debug === 'string') {
                    errorText += '\n\nDebug: ' + data.debug;
                } else if (typeof data.debug === 'object') {
                    errorText += '\n\nDebug Details:';
                    if (data.debug.error) errorText += '\nError: ' + data.debug.error;
                    if (data.debug.file) errorText += '\nFile: ' + data.debug.file;
                    if (data.debug.line) errorText += '\nLine: ' + data.debug.line;
                }
            }
            
            if (errorMessage) {
                errorMessage.textContent = errorText;
                errorMessage.style.whiteSpace = 'pre-line';
            }
            if (errorDiv) errorDiv.classList.remove('tw-hidden');
            
            // Also show notification with debug info
            showNotification('Approval failed: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
        console.error('❌ DEBUG: AJAX error:', error);
        console.error('❌ DEBUG: Error details:', {
            name: error.name,
            message: error.message,
            stack: error.stack
        });
        
        let errorText = 'Network error occurred while approving user';
        if (error.message) {
            errorText += '\n\nError: ' + error.message;
        }
        
        if (errorMessage) {
            errorMessage.textContent = errorText;
            errorMessage.style.whiteSpace = 'pre-line';
        }
        if (errorDiv) errorDiv.classList.remove('tw-hidden');
        
        showNotification('Network error: ' + error.message, 'error');
    })
    .finally(() => {
        console.log('🔍 DEBUG: Resetting button state');
        if (submitBtn) submitBtn.disabled = false;
        if (buttonText) buttonText.textContent = 'Approve User';
    });
}

function submitUserRejection() {
    if (!currentUserId) return;
    
    const reason = document.getElementById('rejectionReason').value.trim();
    const submitBtn = document.getElementById('submitUserRejectionBtn');
    const errorDiv = document.getElementById('rejectionError');
    const errorMessage = document.getElementById('rejectionErrorMessage');
    
    if (!reason) {
        errorMessage.textContent = 'Please provide a reason for rejection';
        errorDiv.classList.remove('tw-hidden');
        return;
    }
    
    // Disable button and show loading
    submitBtn.disabled = true;
    document.getElementById('rejectionButtonText').textContent = 'Rejecting...';
    
    // Make AJAX request
        fetch('<?= url('/admin/user-management/users/reject') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
        body: JSON.stringify({ 
            user_id: currentUserId,
            reason: reason
        })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
            closeUserRejectionModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
            errorMessage.textContent = data.message || 'An error occurred';
            errorDiv.classList.remove('tw-hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        errorMessage.textContent = 'An error occurred while rejecting user';
        errorDiv.classList.remove('tw-hidden');
    })
    .finally(() => {
        submitBtn.disabled = false;
        document.getElementById('rejectionButtonText').textContent = 'Reject User';
    });
}

// Restaurant approval functions
function approveRestaurant(restaurantId) {
    if (confirm('Are you sure you want to approve this restaurant application?')) {
        fetch('<?= url('/admin/user-management/restaurants/approve') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ restaurant_id: restaurantId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while approving restaurant', 'error');
        });
    }
}

function rejectRestaurant(restaurantId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason !== null) {
        fetch('<?= url('/admin/user-management/restaurants/reject') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ restaurant_id: restaurantId, reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while rejecting restaurant', 'error');
        });
    }
}

function exportRequests() {
    console.log('Exporting requests...');
}

function refreshRequests() {
    window.location.reload();
}

// Debug function to check modal on page load
function debugModalOnLoad() {
    console.log('🔍 DEBUG: Checking modal elements on page load');
    
    const modal = document.getElementById('userApprovalModal');
    const button = document.getElementById('submitUserApprovalBtn');
    const debugInfo = document.getElementById('approvalDebugInfo');
    
    console.log('🔍 DEBUG: Modal elements found:', {
        modal: !!modal,
        button: !!button,
        debugInfo: !!debugInfo
    });
    
    if (modal) {
        console.log('🔍 DEBUG: Modal classes:', modal.className);
        console.log('🔍 DEBUG: Modal hidden:', modal.classList.contains('tw-hidden'));
    }
    
    if (button) {
        console.log('🔍 DEBUG: Button classes:', button.className);
        console.log('🔍 DEBUG: Button disabled:', button.disabled);
    }
}

// Initialize page when loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Page loaded, initializing role requests');
    // Initialize any necessary functionality here
});

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-px-6 tw-py-3 tw-rounded-md tw-shadow-lg tw-z-50 ${
        type === 'success' ? 'tw-bg-green-500 tw-text-white' : 
        type === 'error' ? 'tw-bg-red-500 tw-text-white' : 
        'tw-bg-blue-500 tw-text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize Feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>