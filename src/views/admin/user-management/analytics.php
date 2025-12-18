<?php
/**
 * Admin User Analytics Dashboard
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'user-analytics';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">User Analytics</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Comprehensive insights into user behavior and platform engagement
            </p>
        </div>
        <div class="tw-flex tw-space-x-2 sm:tw-space-x-3">
            <select id="periodFilter" onchange="updateAnalytics()" 
                    class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md">
                <option value="week">This Week</option>
                <option value="month" selected>This Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
            </select>
            <button onclick="exportAnalytics()" 
                    class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Export Report">
                <i data-feather="download" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Export Report</span>
            </button>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <!-- Total Users -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Users</p>
                <p id="totalUsers" class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($userStats['total_users'] ?? 0) ?></p>
                <p id="totalUsersChange" class="tw-text-sm tw-text-green-600"><?= ($userStats['new_users_30_days'] ?? 0) . ' this month' ?></p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- New Users -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">New Users</p>
                <p id="newUsers" class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($userStats['new_users_30_days'] ?? 0) ?></p>
                <p id="newUsersChange" class="tw-text-sm tw-text-green-600"><?= ($userStats['new_users_7_days'] ?? 0) . ' this week' ?></p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="user-plus" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <!-- Active Users -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Users</p>
                <p id="activeUsers" class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($userStats['active_users_30_days'] ?? 0) ?></p>
                <p id="activeUsersChange" class="tw-text-sm tw-text-orange-600"><?= ($userStats['active_users'] ?? 0) . ' total active' ?></p>
            </div>
            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                <i data-feather="activity" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
            </div>
        </div>
    </div>

    <!-- Retention Rate -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Retention Rate</p>
                <p id="retentionRate" class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= $userStats['active_users'] ? round(($userStats['active_users'] / $userStats['total_users']) * 100, 1) : 0 ?>%</p>
                <p id="retentionRateChange" class="tw-text-sm tw-text-purple-600"><?= $userStats['suspended_users'] ?? 0 ?> suspended</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="repeat" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

    <!-- User Growth Chart -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">User Growth</h2>
            <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                <i data-feather="trending-up" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="tw-p-6">
            <?php if (!empty($userGrowth)): ?>
                <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-mb-4">
                    <div class="tw-text-center tw-p-3 tw-bg-blue-50 tw-rounded-lg">
                        <p class="tw-text-2xl tw-font-bold tw-text-blue-600"><?= array_sum(array_column($userGrowth, 'new_users')) ?></p>
                    <p class="tw-text-sm tw-text-gray-600">Total New Users</p>
                    </div>
                    <div class="tw-text-center tw-p-3 tw-bg-green-50 tw-rounded-lg">
                    <p class="tw-text-2xl tw-font-bold tw-text-green-600"><?= array_sum(array_column($userGrowth, 'active_users')) ?></p>
                    <p class="tw-text-sm tw-text-gray-600">Total Active Users</p>
                </div>
            </div>
            <div id="userGrowthChart" class="tw-h-64">
                <!-- Chart will be rendered here -->
                <div class="tw-flex tw-items-center tw-justify-center tw-h-full tw-bg-gray-50 tw-rounded-lg">
                    <div class="tw-text-center">
                        <i data-feather="bar-chart" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                        <p class="tw-text-gray-500">User growth chart will be displayed here</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="tw-text-center tw-py-8">
                <i data-feather="bar-chart" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                <p class="tw-text-gray-500">No user growth data available for the selected period.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- User Demographics -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
    <!-- User Roles Distribution -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">User Roles Distribution</h2>
                <div class="tw-p-2 tw-bg-indigo-100 tw-rounded-lg">
                    <i data-feather="pie-chart" class="tw-h-5 tw-w-5 tw-text-indigo-600"></i>
                </div>
            </div>
        </div>
        
        <div class="tw-p-6">
            <?php if (!empty($userRoles)): ?>
                <div class="tw-space-y-4">
                    <?php foreach ($userRoles as $role): ?>
                        <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-gray-50 tw-rounded-lg">
                            <div class="tw-flex tw-items-center">
                                <div class="tw-w-4 tw-h-4 tw-rounded-full tw-mr-3 tw-bg-<?= $role['role'] === 'customer' ? 'blue' : ($role['role'] === 'vendor' ? 'green' : ($role['role'] === 'rider' ? 'purple' : 'orange')) ?>-500 tw-shadow-sm"></div>
                                <span class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-capitalize"><?= e($role['role']) ?></span>
                            </div>
                            <div class="tw-text-right">
                                <div class="tw-text-lg tw-font-bold tw-text-gray-900"><?= number_format($role['count']) ?></div>
                                <div class="tw-text-xs tw-text-gray-600 tw-font-medium"><?= $role['percentage'] ?>%</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="tw-text-center tw-py-8">
                    <i data-feather="users" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                    <p class="tw-text-gray-500">No role data available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- User Registration Trends -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Registration Trends</h2>
                <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg">
                    <i data-feather="trending-up" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                        </div>
    </div>
</div>

        <div class="tw-p-6">
                <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-mb-4">
                <div class="tw-text-center tw-p-3 tw-bg-green-50 tw-rounded-lg">
                    <p class="tw-text-2xl tw-font-bold tw-text-green-600"><?= $userStats['new_users_7_days'] ?? 0 ?></p>
                        <p class="tw-text-sm tw-text-gray-600">This Week</p>
                    </div>
                <div class="tw-text-center tw-p-3 tw-bg-blue-50 tw-rounded-lg">
                    <p class="tw-text-2xl tw-font-bold tw-text-blue-600"><?= $userStats['new_users_30_days'] ?? 0 ?></p>
                    <p class="tw-text-sm tw-text-gray-600">This Month</p>
                </div>
                    </div>
            <div id="registrationTrendsChart" class="tw-h-48">
                <!-- Chart will be rendered here -->
                <div class="tw-flex tw-items-center tw-justify-center tw-h-full tw-bg-gray-50 tw-rounded-lg">
                    <div class="tw-text-center">
                        <i data-feather="line-chart" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                        <p class="tw-text-gray-500">Registration trends chart will be displayed here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Engagement Metrics -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">User Engagement Metrics</h2>
            <div class="tw-p-2 tw-bg-yellow-100 tw-rounded-lg">
                <i data-feather="target" class="tw-h-5 tw-w-5 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-p-6">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
            <div class="tw-flex tw-items-center tw-p-4 tw-bg-blue-50 tw-rounded-lg">
                <div class="tw-p-3 tw-bg-blue-100 tw-rounded-lg tw-mr-4">
                    <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Session Duration</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-blue-700"><?= $userStats['avg_session_duration'] ?? '0' ?> min</p>
                </div>
            </div>
            
            <div class="tw-flex tw-items-center tw-p-4 tw-bg-green-50 tw-rounded-lg">
                <div class="tw-p-3 tw-bg-green-100 tw-rounded-lg tw-mr-4">
                    <i data-feather="refresh-cw" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Return Rate</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-green-700"><?= $userStats['return_rate'] ?? '0' ?>%</p>
                </div>
            </div>
            
            <div class="tw-flex tw-items-center tw-p-4 tw-bg-purple-50 tw-rounded-lg">
                <div class="tw-p-3 tw-bg-purple-100 tw-rounded-lg tw-mr-4">
                    <i data-feather="activity" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Daily Active Users</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-purple-700"><?= $userStats['daily_active_users'] ?? '0' ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Performing Users -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Top Performing Users</h2>
            <div class="tw-p-2 tw-bg-orange-100 tw-rounded-lg">
                <i data-feather="award" class="tw-h-5 tw-w-5 tw-text-orange-600"></i>
                            </div>
                            </div>
                        </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">User</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Role</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Activity Score</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Last Active</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (!empty($topUsers)): ?>
                    <?php foreach ($topUsers as $index => $user): ?>
                        <tr class="hover:tw-bg-gray-50">
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <div class="tw-w-8 tw-h-8 tw-bg-gradient-to-br tw-from-orange-500 tw-to-red-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                                        <span class="tw-text-white tw-font-bold tw-text-sm"><?= $index + 1 ?></span>
                        </div>
                                    <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= e($user['name']) ?></span>
                    </div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-<?= $user['role'] === 'customer' ? 'blue' : ($user['role'] === 'vendor' ? 'green' : ($user['role'] === 'rider' ? 'purple' : 'orange')) ?>-100 tw-text-<?= $user['role'] === 'customer' ? 'blue' : ($user['role'] === 'vendor' ? 'green' : ($user['role'] === 'rider' ? 'purple' : 'orange')) ?>-800 tw-capitalize"><?= e($user['role']) ?></span>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-semibold tw-text-gray-900"><?= number_format($user['activity_score'] ?? 0) ?></td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500"><?= $user['last_active'] ?? 'Never' ?></td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-<?= $user['status'] === 'active' ? 'green' : 'red' ?>-100 tw-text-<?= $user['status'] === 'active' ? 'green' : 'red' ?>-800 tw-capitalize"><?= e($user['status']) ?></span>
                            </td>
                        </tr>
                <?php endforeach; ?>
            <?php else: ?>
                    <tr>
                        <td colspan="5" class="tw-px-6 tw-py-12 tw-text-center">
                            <i data-feather="users" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                            <p class="tw-text-gray-500">No user performance data available.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
                    </div>

<!-- User Behavior Insights -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">User Behavior Insights</h2>
            <div class="tw-p-2 tw-bg-indigo-100 tw-rounded-lg">
                <i data-feather="eye" class="tw-h-5 tw-w-5 tw-text-indigo-600"></i>
                </div>
        </div>
    </div>

    <div class="tw-p-6">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
            <div class="tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Peak Usage Times</h3>
                <div class="tw-space-y-2">
                    <div class="tw-flex tw-justify-between tw-text-sm">
                        <span class="tw-text-gray-600">Morning (6-12 AM)</span>
                        <span class="tw-font-semibold tw-text-gray-900"><?= $userStats['peak_morning'] ?? '0' ?> users</span>
                            </div>
                    <div class="tw-flex tw-justify-between tw-text-sm">
                        <span class="tw-text-gray-600">Afternoon (12-6 PM)</span>
                        <span class="tw-font-semibold tw-text-gray-900"><?= $userStats['peak_afternoon'] ?? '0' ?> users</span>
                    </div>
                    <div class="tw-flex tw-justify-between tw-text-sm">
                        <span class="tw-text-gray-600">Evening (6-12 PM)</span>
                        <span class="tw-font-semibold tw-text-gray-900"><?= $userStats['peak_evening'] ?? '0' ?> users</span>
                    </div>
        </div>
    </div>

            <div class="tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">User Preferences</h3>
                <div class="tw-space-y-2">
                    <div class="tw-flex tw-justify-between tw-text-sm">
                        <span class="tw-text-gray-600">Mobile Users</span>
                        <span class="tw-font-semibold tw-text-gray-900"><?= $userStats['mobile_users'] ?? '0' ?>%</span>
                    </div>
                    <div class="tw-flex tw-justify-between tw-text-sm">
                        <span class="tw-text-gray-600">Desktop Users</span>
                        <span class="tw-font-semibold tw-text-gray-900"><?= $userStats['desktop_users'] ?? '0' ?>%</span>
                    </div>
                    <div class="tw-flex tw-justify-between tw-text-sm">
                        <span class="tw-text-gray-600">Tablet Users</span>
                        <span class="tw-font-semibold tw-text-gray-900"><?= $userStats['tablet_users'] ?? '0' ?>%</span>
                    </div>
                </div>
                </div>
        </div>
    </div>
</div>

<script>
// User Analytics JavaScript functions
function updateAnalytics() {
    const period = document.getElementById('periodFilter').value;
    window.location.href = `/admin/user-analytics?period=${period}`;
}

function exportAnalytics() {
    const period = document.getElementById('periodFilter').value;
    window.open(`/admin/user-analytics/export?period=${period}`, '_blank');
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if needed
    initializeCharts();
});

function initializeCharts() {
    // This would typically initialize Chart.js or other charting libraries
    // For now, we'll just log that charts should be initialized
    console.log('Charts should be initialized here');
}

// Feather icons are initialized by the dashboard layout
</script>