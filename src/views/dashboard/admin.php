<?php
$title = $title ?? 'Admin Dashboard - Time2Eat';
$currentPage = $currentPage ?? 'dashboard';
$user = $user ?? null;

// Start output buffering for content
ob_start();
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Admin Dashboard</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Monitor platform performance and manage operations.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <button class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-purple-100 tw-text-purple-800 hover:tw-bg-purple-200 tw-transition-colors">
                <i data-feather="bell" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Send Notification
            </button>
            <button class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-blue-100 tw-text-blue-800 hover:tw-bg-blue-200 tw-transition-colors">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Export Data
            </button>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Users</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">2,847</p>
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
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Revenue</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">1.2M</p>
                <p class="tw-text-sm tw-text-green-600">+18% this month</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Orders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">156</p>
                <p class="tw-text-sm tw-text-blue-600">Real-time</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Restaurants</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900">89</p>
                <p class="tw-text-sm tw-text-gray-500">12 pending approval</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="tw-mb-8">
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4">
        <a href="<?= url('/admin/users') ?>" class="tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-text-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="users" class="tw-h-8 tw-w-8 group-hover:tw-scale-110 tw-transition-transform"></i>
                <div>
                    <h3 class="tw-text-lg tw-font-semibold">User Management</h3>
                    <p class="tw-text-blue-100 tw-text-sm">Manage all users</p>
                </div>
            </div>
        </a>

        <a href="<?= url('/admin/messages') ?>" class="tw-bg-gradient-to-r tw-from-green-500 tw-to-teal-500 tw-text-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="message-square" class="tw-h-8 tw-w-8 group-hover:tw-scale-110 tw-transition-transform"></i>
                <div>
                    <h3 class="tw-text-lg tw-font-semibold">Messages</h3>
                    <p class="tw-text-green-100 tw-text-sm">Communicate with users</p>
                </div>
            </div>
        </a>

        <a href="<?= url('/admin/restaurants') ?>" class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="shopping-bag" class="tw-h-8 tw-w-8 tw-text-orange-500 group-hover:tw-scale-110 tw-transition-transform"></i>
                <div>
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Restaurants</h3>
                    <p class="tw-text-gray-500 tw-text-sm">Approve & manage</p>
                </div>
                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                    12 Pending
                </span>
            </div>
        </a>

        <a href="<?= url('/admin/analytics') ?>" class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-group tw-border tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="bar-chart-2" class="tw-h-8 tw-w-8 tw-text-green-500 group-hover:tw-scale-110 tw-transition-transform"></i>
                <div>
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Analytics</h3>
                    <p class="tw-text-gray-500 tw-text-sm">View reports</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Platform Overview & Recent Activity -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
    <!-- Platform Overview Chart -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Platform Overview</h2>
        </div>
        <div class="tw-p-6">
            <canvas id="platformChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Recent Activity</h2>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-h-8 tw-w-8 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="user-plus" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                </div>
                <div class="tw-flex-1">
                    <p class="tw-text-sm tw-font-medium tw-text-gray-900">New restaurant approved</p>
                    <p class="tw-text-xs tw-text-gray-500">Pizza Palace • 5 minutes ago</p>
                </div>
            </div>

            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-h-8 tw-w-8 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="alert-circle" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                </div>
                <div class="tw-flex-1">
                    <p class="tw-text-sm tw-font-medium tw-text-gray-900">Dispute reported</p>
                    <p class="tw-text-xs tw-text-gray-500">Order #12345 • 15 minutes ago</p>
                </div>
            </div>

            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-h-8 tw-w-8 tw-bg-yellow-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="user" class="tw-h-4 tw-w-4 tw-text-yellow-600"></i>
                </div>
                <div class="tw-flex-1">
                    <p class="tw-text-sm tw-font-medium tw-text-gray-900">New rider registered</p>
                    <p class="tw-text-xs tw-text-gray-500">John Doe • 32 minutes ago</p>
                </div>
            </div>

            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-h-8 tw-w-8 tw-bg-purple-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-text-purple-600"></i>
                </div>
                <div class="tw-flex-1">
                    <p class="tw-text-sm tw-font-medium tw-text-gray-900">Payment processed</p>
                    <p class="tw-text-xs tw-text-gray-500">125,000 XAF • 1 hour ago</p>
                </div>
            </div>

            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-h-8 tw-w-8 tw-bg-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="alert-triangle" class="tw-h-4 tw-w-4 tw-text-red-600"></i>
                </div>
                <div class="tw-flex-1">
                    <p class="tw-text-sm tw-font-medium tw-text-gray-900">System alert</p>
                    <p class="tw-text-xs tw-text-gray-500">High server load • 2 hours ago</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Approvals & System Status -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
    <!-- Pending Approvals -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Pending Approvals</h2>
                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                    15 Items
                </span>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <!-- Approval Item -->
            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-h-10 tw-w-10 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-orange-600"></i>
                    </div>
                    <div>
                        <h3 class="tw-font-medium tw-text-gray-900">Burger House</h3>
                        <p class="tw-text-sm tw-text-gray-500">Restaurant registration</p>
                    </div>
                </div>
                <div class="tw-flex tw-space-x-2">
                    <button class="tw-bg-green-500 hover:tw-bg-green-600 tw-text-white tw-px-3 tw-py-1 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                        Approve
                    </button>
                    <button class="tw-bg-red-500 hover:tw-bg-red-600 tw-text-white tw-px-3 tw-py-1 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                        Reject
                    </button>
                </div>
            </div>

            <!-- Approval Item -->
            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-h-10 tw-w-10 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <i data-feather="truck" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="tw-font-medium tw-text-gray-900">Mike Johnson</h3>
                        <p class="tw-text-sm tw-text-gray-500">Rider application</p>
                    </div>
                </div>
                <div class="tw-flex tw-space-x-2">
                    <button class="tw-bg-green-500 hover:tw-bg-green-600 tw-text-white tw-px-3 tw-py-1 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                        Approve
                    </button>
                    <button class="tw-bg-red-500 hover:tw-bg-red-600 tw-text-white tw-px-3 tw-py-1 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                        Reject
                    </button>
                </div>
            </div>

            <!-- Approval Item -->
            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-h-10 tw-w-10 tw-bg-purple-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <i data-feather="percent" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="tw-font-medium tw-text-gray-900">Affiliate Payout</h3>
                        <p class="tw-text-sm tw-text-gray-500">45,000 XAF withdrawal</p>
                    </div>
                </div>
                <div class="tw-flex tw-space-x-2">
                    <button class="tw-bg-green-500 hover:tw-bg-green-600 tw-text-white tw-px-3 tw-py-1 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                        Approve
                    </button>
                    <button class="tw-bg-red-500 hover:tw-bg-red-600 tw-text-white tw-px-3 tw-py-1 tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors">
                        Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">System Status</h2>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <!-- Status Item -->
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-h-3 tw-w-3 tw-bg-green-400 tw-rounded-full"></div>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-900">API Server</span>
                </div>
                <span class="tw-text-sm tw-text-green-600">Operational</span>
            </div>

            <!-- Status Item -->
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-h-3 tw-w-3 tw-bg-green-400 tw-rounded-full"></div>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-900">Database</span>
                </div>
                <span class="tw-text-sm tw-text-green-600">Operational</span>
            </div>

            <!-- Status Item -->
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-h-3 tw-w-3 tw-bg-yellow-400 tw-rounded-full"></div>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-900">Payment Gateway</span>
                </div>
                <span class="tw-text-sm tw-text-yellow-600">Degraded</span>
            </div>

            <!-- Status Item -->
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-h-3 tw-w-3 tw-bg-green-400 tw-rounded-full"></div>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-900">SMS Service</span>
                </div>
                <span class="tw-text-sm tw-text-green-600">Operational</span>
            </div>

            <!-- Status Item -->
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-h-3 tw-w-3 tw-bg-green-400 tw-rounded-full"></div>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-900">Email Service</span>
                </div>
                <span class="tw-text-sm tw-text-green-600">Operational</span>
            </div>

            <!-- System Resources -->
            <div class="tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-200">
                <h3 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-3">System Resources</h3>
                
                <div class="tw-space-y-3">
                    <div>
                        <div class="tw-flex tw-justify-between tw-text-sm">
                            <span class="tw-text-gray-600">CPU Usage</span>
                            <span class="tw-text-gray-900">45%</span>
                        </div>
                        <div class="tw-w-full tw-bg-gray-200 tw-rounded-full tw-h-2 tw-mt-1">
                            <div class="tw-bg-blue-500 tw-h-2 tw-rounded-full" style="width: 45%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="tw-flex tw-justify-between tw-text-sm">
                            <span class="tw-text-gray-600">Memory Usage</span>
                            <span class="tw-text-gray-900">68%</span>
                        </div>
                        <div class="tw-w-full tw-bg-gray-200 tw-rounded-full tw-h-2 tw-mt-1">
                            <div class="tw-bg-yellow-500 tw-h-2 tw-rounded-full" style="width: 68%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="tw-flex tw-justify-between tw-text-sm">
                            <span class="tw-text-gray-600">Disk Usage</span>
                            <span class="tw-text-gray-900">32%</span>
                        </div>
                        <div class="tw-w-full tw-bg-gray-200 tw-rounded-full tw-h-2 tw-mt-1">
                            <div class="tw-bg-green-500 tw-h-2 tw-rounded-full" style="width: 32%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Platform Overview Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('platformChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Customers', 'Vendors', 'Riders', 'Admins'],
            datasets: [{
                data: [2145, 89, 156, 8],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(147, 51, 234, 0.8)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(249, 115, 22)',
                    'rgb(34, 197, 94)',
                    'rgb(147, 51, 234)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});

// Auto-refresh system status
setInterval(function() {
    fetch('<?= url('/api/admin/system-status') ?>')
        .then(response => response.json())
        .then(data => {
            // Update system status indicators
            console.log('System status updated');
        })
        .catch(error => console.log('Failed to refresh system status:', error));
}, 60000); // Refresh every minute
</script>

<?php
// Capture the content
$content = ob_get_clean();

// Include the dashboard layout
include __DIR__ . '/../components/dashboard-layout.php';
?>
