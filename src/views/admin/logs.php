<?php
/**
 * Admin Security Logs Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'logs';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Security Logs</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Monitor system security events and audit trails
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Export Logs
            </button>
            <button onclick="refreshLogs()" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Refresh
            </button>
        </div>
    </div>
</div>

<!-- Security Overview -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Events</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_events'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">All time</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="activity" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Critical Events</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['critical_events'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-red-600">Last 30 days</p>
            </div>
            <div class="tw-p-3 tw-bg-red-100 tw-rounded-full">
                <i data-feather="alert-triangle" class="tw-h-6 tw-w-6 tw-text-red-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Auth Events</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['auth_events'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-yellow-600">Last 30 days</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="lock" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Access Events</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['access_events'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">Last 30 days</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="shield" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active IPs Today</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['unique_ips_today'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">Unique addresses</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="globe" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Security Alerts -->
<?php if (($stats['critical_events'] ?? 0) > 0): ?>
<div class="tw-bg-gradient-to-r tw-from-red-50 tw-to-orange-50 tw-border tw-border-red-200 tw-rounded-xl tw-p-6 tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-2 tw-bg-red-100 tw-rounded-lg">
                <i data-feather="alert-octagon" class="tw-h-6 tw-w-6 tw-text-red-600"></i>
            </div>
            <div class="tw-ml-4">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Critical Security Events</h3>
                <p class="tw-text-sm tw-text-gray-600"><?= number_format($stats['critical_events'] ?? 0) ?> critical events detected in the last 30 days</p>
            </div>
        </div>
        <button onclick="showCriticalAlerts()" class="tw-bg-red-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium hover:tw-bg-red-700 tw-transition-colors">
            Review Now
        </button>
    </div>
</div>
<?php endif; ?>

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
                    <input type="text" id="log-search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-leading-5 tw-bg-white tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-1 focus:tw-ring-primary-500 focus:tw-border-primary-500" placeholder="Search logs by IP, user, or event..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="tw-flex tw-space-x-4">
                <select id="level-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Levels</option>
                    <option value="info" <?= ($level ?? '') === 'info' ? 'selected' : '' ?>>Info</option>
                    <option value="warning" <?= ($level ?? '') === 'warning' ? 'selected' : '' ?>>Warning</option>
                    <option value="error" <?= ($level ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
                    <option value="critical" <?= ($level ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                </select>
                
                <select id="category-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md">
                    <option value="">All Categories</option>
                    <option value="auth" <?= ($category ?? '') === 'auth' ? 'selected' : '' ?>>Authentication</option>
                    <option value="access" <?= ($category ?? '') === 'access' ? 'selected' : '' ?>>Access Control</option>
                    <option value="data" <?= ($category ?? '') === 'data' ? 'selected' : '' ?>>Data Changes</option>
                    <option value="system" <?= ($category ?? '') === 'system' ? 'selected' : '' ?>>System Events</option>
                </select>
                
                <input type="date" id="date-filter" class="tw-block tw-w-full tw-pl-3 tw-pr-3 tw-py-2 tw-text-base tw-border-gray-300 focus:tw-outline-none focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-rounded-md" value="<?= htmlspecialchars($date ?? '') ?>">
            </div>
        </div>
    </div>
</div>

<!-- Security Logs Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Security Events</h2>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <span class="tw-text-sm tw-text-gray-500">Auto-refresh: <span id="refresh-countdown">30</span>s</span>
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
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Timestamp</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Level</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Event</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">User/IP</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Details</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="logs-table-body">
                <?php 
                $sampleLogs = [
                    ['id' => 'LOG-001', 'timestamp' => '2024-02-15 14:30:25', 'level' => 'critical', 'category' => 'auth', 'event' => 'Multiple failed login attempts', 'user' => 'Unknown', 'ip' => '192.168.1.100', 'details' => '15 failed attempts in 5 minutes', 'user_agent' => 'Mozilla/5.0...'],
                    ['id' => 'LOG-002', 'timestamp' => '2024-02-15 14:28:15', 'level' => 'warning', 'category' => 'access', 'event' => 'Unauthorized API access attempt', 'user' => 'john@example.com', 'ip' => '10.0.0.45', 'details' => 'Attempted to access admin endpoint', 'user_agent' => 'PostmanRuntime/7.28.4'],
                    ['id' => 'LOG-003', 'timestamp' => '2024-02-15 14:25:10', 'level' => 'info', 'category' => 'auth', 'event' => 'Successful admin login', 'user' => 'admin@time2eat.com', 'ip' => '192.168.1.50', 'details' => 'Admin dashboard access', 'user_agent' => 'Mozilla/5.0...'],
                    ['id' => 'LOG-004', 'timestamp' => '2024-02-15 14:20:45', 'level' => 'error', 'category' => 'system', 'event' => 'Database connection timeout', 'user' => 'System', 'ip' => 'localhost', 'details' => 'Connection pool exhausted', 'user_agent' => 'N/A'],
                    ['id' => 'LOG-005', 'timestamp' => '2024-02-15 14:18:30', 'level' => 'warning', 'category' => 'data', 'event' => 'Bulk data modification', 'user' => 'admin@time2eat.com', 'ip' => '192.168.1.50', 'details' => 'Modified 150 user records', 'user_agent' => 'Mozilla/5.0...']
                ];
                
                foreach ($securityLogs ?? $sampleLogs as $log): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                        <?= date('M j, H:i:s', strtotime($log['timestamp'])) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                            <?php 
                            switch($log['level']) {
                                case 'critical': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                case 'error': echo 'tw-bg-orange-100 tw-text-orange-800'; break;
                                case 'warning': echo 'tw-bg-yellow-100 tw-text-yellow-800'; break;
                                case 'info': echo 'tw-bg-blue-100 tw-text-blue-800'; break;
                                default: echo 'tw-bg-gray-100 tw-text-gray-800';
                            }
                            ?>">
                            <?= ucfirst($log['level']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($log['event']) ?></div>
                        <div class="tw-text-sm tw-text-gray-500"><?= ucfirst($log['category']) ?></div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($log['user']) ?></div>
                        <div class="tw-text-sm tw-text-gray-500"><?= e($log['ip']) ?></div>
                    </td>
                    <td class="tw-px-6 tw-py-4">
                        <div class="tw-text-sm tw-text-gray-900 tw-max-w-xs tw-truncate" title="<?= e($log['details']) ?>">
                            <?= e($log['details']) ?>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-2">
                            <button class="tw-text-primary-600 hover:tw-text-primary-900" onclick="viewLogDetails('<?= $log['id'] ?>')">
                                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <?php if ($log['level'] === 'critical' || $log['level'] === 'error'): ?>
                                <button class="tw-text-red-600 hover:tw-text-red-900" onclick="blockIP('<?= $log['ip'] ?>')">
                                    <i data-feather="shield" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                            <button class="tw-text-blue-600 hover:tw-text-blue-900" onclick="investigateEvent('<?= $log['id'] ?>')">
                                <i data-feather="search" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
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
                    Showing <span class="tw-font-medium">1</span> to <span class="tw-font-medium">50</span> of <span class="tw-font-medium"><?= number_format($totalLogs ?? 15847) ?></span> results
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
let refreshInterval;
let refreshCountdown = 30;

// Security log functions
function viewLogDetails(logId) {
    window.location.href = `/admin/logs/${logId}`;
}

function blockIP(ipAddress) {
    if (confirm(`Are you sure you want to block IP address ${ipAddress}?`)) {
        fetch('<?= url('/admin/security/block-ip') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ip: ipAddress })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('IP address blocked successfully.');
                location.reload();
            } else {
                alert('Error blocking IP: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while blocking the IP address.');
        });
    }
}

function investigateEvent(logId) {
    window.location.href = `/admin/logs/${logId}/investigate`;
}

function showCriticalAlerts() {
    document.getElementById('level-filter').value = 'critical';
    filterLogs();
}

function refreshLogs() {
    location.reload();
}

// Search and filter functionality
document.getElementById('log-search').addEventListener('input', function() {
    filterLogs();
});

document.getElementById('level-filter').addEventListener('change', function() {
    filterLogs();
});

document.getElementById('category-filter').addEventListener('change', function() {
    filterLogs();
});

document.getElementById('date-filter').addEventListener('change', function() {
    filterLogs();
});

function filterLogs() {
    const search = document.getElementById('log-search').value;
    const level = document.getElementById('level-filter').value;
    const category = document.getElementById('category-filter').value;
    const date = document.getElementById('date-filter').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (level) params.append('level', level);
    if (category) params.append('category', category);
    if (date) params.append('date', date);
    
    // Update URL and reload content
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

// Auto-refresh functionality
function startAutoRefresh() {
    refreshInterval = setInterval(function() {
        refreshCountdown--;
        document.getElementById('refresh-countdown').textContent = refreshCountdown;
        
        if (refreshCountdown <= 0) {
            refreshCountdown = 30;
            // In a real implementation, you would fetch new data via AJAX
            // location.reload();
        }
    }, 1000);
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

// Initialize Feather Icons
function initializeFeatherIcons() {
    if (typeof feather !== 'undefined') {
        try {
            feather.replace();
            console.log('Feather icons initialized successfully');
        } catch (error) {
            console.error('Error initializing Feather icons:', error);
        }
    } else {
        console.log('Feather not loaded yet, retrying...');
        setTimeout(initializeFeatherIcons, 100);
    }
}

// Initialize feather icons and auto-refresh
document.addEventListener('DOMContentLoaded', function() {
    initializeFeatherIcons();
    startAutoRefresh();
});

// Stop auto-refresh when page is hidden
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
});
</script>
