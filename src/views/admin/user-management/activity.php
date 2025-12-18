<?php
/**
 * Admin User Activity Monitoring Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'user-activity';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">User Activity Monitoring</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Monitor user activities and behavior across the platform
            </p>
        </div>
        <div class="tw-flex tw-space-x-2 sm:tw-space-x-3">
            <button onclick="refreshActivities()" 
                    class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Refresh Activities">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Refresh</span>
            </button>
            <button onclick="exportActivities()" 
                    class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 sm:tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-flex tw-items-center tw-justify-center"
                    title="Export Activities">
                <i data-feather="download" class="tw-h-4 tw-w-4 sm:tw-mr-2"></i>
                <span class="tw-hidden sm:tw-inline">Export</span>
            </button>
        </div>
    </div>
</div>

<!-- Activity Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <!-- Total Activities -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Activities</p>
                <p id="totalActivities" class="tw-text-3xl tw-font-bold tw-text-gray-900">-</p>
                <p class="tw-text-sm tw-text-blue-600">All time</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="activity" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- Today's Activities -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Today's Activities</p>
                <p id="todayActivities" class="tw-text-3xl tw-font-bold tw-text-gray-900">-</p>
                <p class="tw-text-sm tw-text-green-600">Last 24 hours</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="calendar" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <!-- Active Users -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Users</p>
                <p id="activeUsers" class="tw-text-3xl tw-font-bold tw-text-gray-900">-</p>
                <p class="tw-text-sm tw-text-orange-600">Currently online</p>
            </div>
            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
            </div>
        </div>
    </div>

    <!-- Most Active User -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Most Active User</p>
                <p id="mostActiveUser" class="tw-text-lg tw-font-bold tw-text-gray-900">-</p>
                <p class="tw-text-sm tw-text-purple-600">Top performer</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="star" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
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
                    <input type="text" id="activity-search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-leading-5 tw-bg-white tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-1 focus:tw-ring-primary-500 focus:tw-border-primary-500" placeholder="Search activities by user, action, or description...">
    </div>
</div>

<!-- Filters -->
            <div class="tw-flex tw-space-x-4">
                <select id="userFilter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                <option value="">All Users</option>
                <!-- Users will be populated via AJAX -->
            </select>
        
                <select id="activityTypeFilter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                <option value="">All Types</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
                    <option value="order">Order</option>
                    <option value="payment">Payment</option>
                    <option value="profile_update">Profile Update</option>
                    <option value="password_change">Password Change</option>
                    <option value="email_verification">Email Verification</option>
                </select>
                
                <select id="dateRangeFilter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month" selected>This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
            </select>
            </div>
        </div>
    </div>
</div>

<!-- Activity Timeline -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Recent Activities</h2>
            <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="tw-p-6">
        <div id="activityTimeline" class="tw-space-y-4">
            <!-- Activities will be loaded here via AJAX -->
            <div class="tw-text-center tw-py-8">
                <i data-feather="activity" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                <p class="tw-text-gray-500">Loading activities...</p>
            </div>
        </div>
    </div>
        </div>
        
<!-- Activity Types Breakdown -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Activity Types Breakdown</h2>
            <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg">
                <i data-feather="pie-chart" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
            </div>
        </div>
        </div>
        
    <div class="tw-p-6">
        <div id="activityTypesChart" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4">
            <!-- Activity types will be loaded here via AJAX -->
            <div class="tw-text-center tw-py-8">
                <i data-feather="bar-chart" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                <p class="tw-text-gray-500">Loading activity breakdown...</p>
            </div>
        </div>
    </div>
</div>

<!-- Top Active Users -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Top Active Users</h2>
            <div class="tw-p-2 tw-bg-purple-100 tw-rounded-lg">
                <i data-feather="award" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">User</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Role</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Activities</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Last Active</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody id="topUsersTable" class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <!-- Top users will be loaded here via AJAX -->
                <tr>
                    <td colspan="5" class="tw-px-6 tw-py-12 tw-text-center">
                        <i data-feather="users" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                        <p class="tw-text-gray-500">Loading top users...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    </div>
    
<!-- Real-time Activity Feed -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Real-time Activity Feed</h2>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <div class="tw-w-3 tw-h-3 tw-bg-green-500 tw-rounded-full tw-animate-pulse"></div>
                <span class="tw-text-sm tw-text-gray-500">Live</span>
        </div>
    </div>
</div>

    <div class="tw-p-6">
        <div id="realtimeFeed" class="tw-space-y-3 tw-max-h-96 tw-overflow-y-auto">
            <!-- Real-time activities will be loaded here -->
            <div class="tw-text-center tw-py-8">
                <i data-feather="wifi" class="tw-h-12 tw-w-12 tw-text-gray-300 tw-mx-auto tw-mb-3"></i>
                <p class="tw-text-gray-500">Connecting to real-time feed...</p>
            </div>
        </div>
    </div>
</div>

<script>
// User Activity JavaScript functions
function refreshActivities() {
    // Reload the page to refresh all data
    window.location.reload();
}

function exportActivities() {
    // Get current filter values
    const userFilter = document.getElementById('userFilter').value;
    const activityTypeFilter = document.getElementById('activityTypeFilter').value;
    const dateRangeFilter = document.getElementById('dateRangeFilter').value;
    
    // Build export URL with filters
    const exportUrl = `/admin/user-activity/export?user=${userFilter}&type=${activityTypeFilter}&range=${dateRangeFilter}`;
    
    // Open export in new window
    window.open(exportUrl, '_blank');
}

function updateAnalytics() {
    // Get current filter values
    const userFilter = document.getElementById('userFilter').value;
    const activityTypeFilter = document.getElementById('activityTypeFilter').value;
    const dateRangeFilter = document.getElementById('dateRangeFilter').value;
    
    // Update URL with new filters
    const newUrl = `/admin/user-activity?user=${userFilter}&type=${activityTypeFilter}&range=${dateRangeFilter}`;
    window.location.href = newUrl;
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadActivityData();
    loadTopUsers();
    loadActivityTypes();
    
    // Set up real-time updates
    setupRealtimeFeed();
    
    // Set up search functionality
    setupSearch();
});

function loadActivityData() {
    // This would typically make an AJAX call to load activity data
    // For now, we'll show placeholder data
    document.getElementById('totalActivities').textContent = '1,247';
    document.getElementById('todayActivities').textContent = '89';
    document.getElementById('activeUsers').textContent = '23';
    document.getElementById('mostActiveUser').textContent = 'John Doe';
}

function loadTopUsers() {
    // This would typically make an AJAX call to load top users
    // For now, we'll show placeholder data
    const tableBody = document.getElementById('topUsersTable');
    tableBody.innerHTML = `
        <tr class="hover:tw-bg-gray-50">
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                <div class="tw-flex tw-items-center">
                    <div class="tw-w-8 tw-h-8 tw-bg-gradient-to-br tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                        <span class="tw-text-white tw-font-bold tw-text-sm">1</span>
                    </div>
                    <span class="tw-text-sm tw-font-semibold tw-text-gray-900">John Doe</span>
                </div>
            </td>
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">Customer</span>
            </td>
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-semibold tw-text-gray-900">156</td>
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">2 minutes ago</td>
            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">Online</span>
            </td>
        </tr>
    `;
}

function loadActivityTypes() {
    // This would typically make an AJAX call to load activity types
    // For now, we'll show placeholder data
    const chartContainer = document.getElementById('activityTypesChart');
    chartContainer.innerHTML = `
        <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-blue-50 tw-rounded-lg">
            <div class="tw-flex tw-items-center">
                <div class="tw-w-4 tw-h-4 tw-rounded-full tw-mr-4 tw-bg-blue-500 tw-shadow-sm"></div>
                <span class="tw-text-sm tw-font-semibold tw-text-gray-900">Login</span>
            </div>
            <div class="tw-text-right">
                <div class="tw-text-lg tw-font-bold tw-text-gray-900">456</div>
                <div class="tw-text-xs tw-text-gray-600 tw-font-medium">36.5%</div>
            </div>
            </div>
    `;
}

function setupRealtimeFeed() {
    // This would typically set up WebSocket or Server-Sent Events
    // For now, we'll show a placeholder
    const feedContainer = document.getElementById('realtimeFeed');
    feedContainer.innerHTML = `
        <div class="tw-flex tw-items-center tw-p-3 tw-bg-gray-50 tw-rounded-lg">
            <div class="tw-w-2 tw-h-2 tw-bg-green-500 tw-rounded-full tw-mr-3"></div>
            <div class="tw-flex-1">
                <p class="tw-text-sm tw-text-gray-900">John Doe logged in</p>
                <p class="tw-text-xs tw-text-gray-500">2 minutes ago</p>
            </div>
        </div>
    `;
}

function setupSearch() {
    const searchInput = document.getElementById('activity-search');
    searchInput.addEventListener('input', function() {
        // This would typically filter the activity list
        console.log('Searching for:', this.value);
    });
}

// Feather icons are initialized by the dashboard layout
</script>