<?php
/**
 * Admin Dashboard Content
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'dashboard';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Admin Dashboard</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Here's what's happening with Time2Eat today.
            </p>
        </div>
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-2 sm:tw-gap-3">
            <button onclick="exportDashboardData()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-4 tw-py-2.5 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-flex tw-items-center tw-justify-center tw-whitespace-nowrap tw-w-full sm:tw-w-auto">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                <span>Export Data</span>
            </button>
            <button onclick="openLiveMap()" class="tw-bg-green-600 tw-border tw-border-transparent tw-rounded-lg tw-px-4 tw-py-2.5 tw-text-sm tw-font-medium tw-text-white hover:tw-bg-green-700 tw-transition-colors tw-flex tw-items-center tw-justify-center tw-w-full sm:tw-w-auto">
                <i data-feather="map" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                <span>Live Map</span>
            </button>
            <button onclick="refreshDashboard()" class="tw-bg-primary-600 tw-border tw-border-transparent tw-rounded-lg tw-px-4 tw-py-2.5 tw-text-sm tw-font-medium tw-text-white hover:tw-bg-primary-700 tw-transition-colors tw-flex tw-items-center tw-justify-center tw-w-full sm:tw-w-auto">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>
</div>

<!-- Dashboard Stats -->
<div class="tw-grid tw-grid-cols-1 tw-gap-5 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-mb-8">
    <!-- Total Users -->
    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-blue-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="users" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Total Users</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['total_users']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="tw-bg-gray-50 tw-px-5 tw-py-3">
            <div class="tw-text-sm">
                <a href="<?= url('/admin/users') ?>" class="tw-font-medium tw-text-blue-700 hover:tw-text-blue-900">View all users</a>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-green-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Total Orders</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['total_orders']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="tw-bg-gray-50 tw-px-5 tw-py-3">
            <div class="tw-text-sm">
                <a href="<?= url('/admin/orders') ?>" class="tw-font-medium tw-text-green-700 hover:tw-text-green-900">View all orders</a>
            </div>
        </div>
    </div>

    <!-- Active Restaurants -->
    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-yellow-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Active Restaurants</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['active_restaurants']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="tw-bg-gray-50 tw-px-5 tw-py-3">
            <div class="tw-text-sm">
                <a href="<?= url('/admin/restaurants') ?>" class="tw-font-medium tw-text-yellow-700 hover:tw-text-yellow-900">Manage restaurants</a>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-purple-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="dollar-sign" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Total Revenue</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['total_revenue']) ?> XAF</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="tw-bg-gray-50 tw-px-5 tw-py-3">
            <div class="tw-text-sm">
                <a href="<?= url('/admin/financial') ?>" class="tw-font-medium tw-text-purple-700 hover:tw-text-purple-900">View financial reports</a>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="tw-grid tw-grid-cols-1 tw-gap-5 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-mb-8">
    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-red-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Pending Orders</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['pending_orders']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-indigo-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Completed Today</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['completed_orders_today']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-teal-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="truck" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Active Riders</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['active_riders']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
        <div class="tw-p-5">
            <div class="tw-flex tw-items-center">
                <div class="tw-flex-shrink-0">
                    <div class="tw-w-8 tw-h-8 tw-bg-pink-500 tw-rounded-md tw-flex tw-items-center tw-justify-center">
                        <i data-feather="user-check" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                </div>
                <div class="tw-ml-5 tw-w-0 tw-flex-1">
                    <dl>
                        <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">Total Customers</dt>
                        <dd class="tw-text-lg tw-font-medium tw-text-gray-900"><?= number_format($stats['total_customers']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid - Mobile Optimized -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-4 sm:tw-gap-6 lg:tw-gap-8 tw-px-2 sm:tw-px-0">
    <!-- Live Delivery Monitor -->
    <div class="lg:tw-col-span-2">
        <div class="tw-bg-white tw-shadow-xl tw-rounded-2xl tw-overflow-hidden tw-border tw-border-gray-100">
            <!-- Header - Mobile Optimized -->
            <div class="tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-px-4 sm:tw-px-6 tw-py-4">
                <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center tw-justify-between tw-space-y-3 sm:tw-space-y-0">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-p-2 tw-bg-white/20 tw-rounded-lg tw-flex-shrink-0">
                            <i data-feather="truck" class="tw-h-5 tw-w-5 sm:tw-h-6 sm:tw-w-6 tw-text-white"></i>
                        </div>
                        <div class="tw-min-w-0 tw-flex-1">
                            <h3 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-white tw-truncate">Live Delivery Monitor</h3>
                            <p class="tw-text-blue-100 tw-text-xs sm:tw-text-sm tw-truncate">Real-time delivery tracking</p>
                        </div>
                    </div>
                    <div class="tw-flex tw-items-center tw-justify-between sm:tw-justify-end tw-space-x-2 sm:tw-space-x-3">
                        <div class="tw-flex tw-items-center tw-bg-white/20 tw-px-2 sm:tw-px-3 tw-py-1.5 sm:tw-py-1 tw-rounded-full tw-h-8 sm:tw-h-9">
                            <div class="tw-h-2 tw-w-2 tw-bg-green-400 tw-rounded-full tw-animate-pulse tw-mr-1.5 sm:tw-mr-2"></div>
                            <span class="tw-text-white tw-text-xs sm:tw-text-sm tw-font-medium">Live</span>
                        </div>
                        <button onclick="openLiveMap()" class="tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-px-3 sm:tw-px-4 tw-py-1.5 sm:tw-py-2 tw-rounded-lg tw-text-xs sm:tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-flex tw-items-center tw-space-x-1.5 sm:tw-space-x-2 tw-flex-shrink-0 tw-h-8 sm:tw-h-9">
                            <i data-feather="map" class="tw-h-3 tw-w-3 sm:tw-h-4 sm:tw-w-4"></i>
                            <span class="tw-hidden sm:inline">View Map</span>
                            <span class="sm:tw-hidden">Map</span>
                        </button>
                        <button onclick="refreshDeliveries()" class="tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-p-2 sm:tw-p-2 tw-rounded-lg tw-transition-all tw-duration-200 tw-flex-shrink-0 tw-h-8 sm:tw-h-9 tw-flex tw-items-center tw-justify-center" title="Refresh">
                            <i data-feather="refresh-cw" class="tw-h-3 tw-w-3 sm:tw-h-4 sm:tw-w-4"></i>
                        </button>
                    </div>
                    </div>
                </div>

            <!-- Content - Mobile First Design -->
            <div class="tw-p-4 sm:tw-p-6">
                <!-- Delivery Stats - Clean Mobile First Grid -->
                <div class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-3 lg:tw-grid-cols-7 tw-gap-3 tw-mb-6">
                    <!-- Pending -->
                    <div class="tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-gray-100 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <div class="tw-p-2 tw-bg-orange-100 tw-rounded-lg">
                                <i data-feather="clock" class="tw-h-4 tw-w-4 tw-text-orange-600"></i>
                        </div>
                        </div>
                        <div class="tw-text-lg tw-font-bold tw-text-gray-900" id="pendingCount">-</div>
                        <div class="tw-text-xs tw-text-gray-600">Pending</div>
                    </div>

                    <!-- Confirmed -->
                    <div class="tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-gray-100 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                                <i data-feather="check-circle" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                            </div>
                            </div>
                        <div class="tw-text-lg tw-font-bold tw-text-gray-900" id="confirmedCount">-</div>
                        <div class="tw-text-xs tw-text-gray-600">Confirmed</div>
                            </div>

                    <!-- Preparing -->
                    <div class="tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-gray-100 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <div class="tw-p-2 tw-bg-indigo-100 tw-rounded-lg">
                                <i data-feather="coffee" class="tw-h-4 tw-w-4 tw-text-indigo-600"></i>
                            </div>
                        </div>
                        <div class="tw-text-lg tw-font-bold tw-text-gray-900" id="preparingCount">-</div>
                        <div class="tw-text-xs tw-text-gray-600">Preparing</div>
                    </div>

                    <!-- Ready -->
                    <div class="tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-gray-100 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <div class="tw-p-2 tw-bg-yellow-100 tw-rounded-lg">
                                <i data-feather="package" class="tw-h-4 tw-w-4 tw-text-yellow-600"></i>
                </div>
            </div>
                        <div class="tw-text-lg tw-font-bold tw-text-gray-900" id="readyCount">-</div>
                        <div class="tw-text-xs tw-text-gray-600">Ready</div>
                    </div>

                    <!-- Picked Up -->
                    <div class="tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-gray-100 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg">
                                <i data-feather="truck" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                            </div>
                        </div>
                        <div class="tw-text-lg tw-font-bold tw-text-gray-900" id="pickedUpCount">-</div>
                        <div class="tw-text-xs tw-text-gray-600">Picked Up</div>
                    </div>

                    <!-- On the Way -->
                    <div class="tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-gray-100 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <div class="tw-p-2 tw-bg-purple-100 tw-rounded-lg">
                                <i data-feather="navigation" class="tw-h-4 tw-w-4 tw-text-purple-600"></i>
                            </div>
                        </div>
                        <div class="tw-text-lg tw-font-bold tw-text-gray-900" id="onTheWayCount">-</div>
                        <div class="tw-text-xs tw-text-gray-600">En Route</div>
                    </div>

                    <!-- Completed Today -->
                    <div class="tw-bg-white tw-p-3 tw-rounded-lg tw-border tw-border-gray-100 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <div class="tw-p-2 tw-bg-emerald-100 tw-rounded-lg">
                                <i data-feather="check-circle" class="tw-h-4 tw-w-4 tw-text-emerald-600"></i>
                            </div>
                        </div>
                        <div class="tw-text-lg tw-font-bold tw-text-gray-900" id="completedTodayCount">-</div>
                        <div class="tw-text-xs tw-text-gray-600">Completed</div>
                    </div>
                </div>

                <!-- Active Deliveries List -->
                <div class="tw-mb-4">
                    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center tw-justify-between tw-mb-4 tw-space-y-2 sm:tw-space-y-0">
                        <h4 class="tw-text-lg tw-font-semibold tw-text-gray-900">Active Deliveries</h4>
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <span class="tw-text-xs sm:tw-text-sm tw-text-gray-500">Auto-refresh every 30s</span>
                            <div class="tw-h-1 tw-w-1 tw-bg-gray-400 tw-rounded-full tw-hidden sm:tw-block"></div>
                            <span class="tw-text-xs sm:tw-text-sm tw-text-gray-500" id="lastUpdate">Just now</span>
                            </div>
                            </div>
                    
                    <div id="deliveryMonitor" class="tw-space-y-3">
                        <div class="tw-space-y-3" id="activeDeliveries">
                            <!-- This will be populated by JavaScript -->
                            <div class="tw-text-center tw-py-12 tw-text-gray-500" id="noDeliveries">
                                <div class="tw-bg-gray-50 tw-rounded-2xl tw-p-8">
                                    <i data-feather="truck" class="tw-h-16 tw-w-16 tw-mx-auto tw-mb-4 tw-text-gray-300"></i>
                                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-700 tw-mb-2">No Active Deliveries</h3>
                                    <p class="tw-text-gray-500">All deliveries are completed or no orders are in progress</p>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & System Health -->
    <div class="tw-space-y-8">
        <!-- Quick Actions -->
        <div class="tw-bg-white tw-shadow tw-rounded-lg">
            <div class="tw-px-4 tw-py-5 sm:tw-p-6">
                <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900 tw-mb-4">Quick Actions</h3>
                <div class="tw-space-y-3">
                    <a href="<?= url('/admin/orders') ?>" class="tw-flex tw-items-center tw-p-3 tw-bg-red-50 tw-rounded-lg tw-border tw-border-red-200 hover:tw-bg-red-100 tw-transition-colors">
                        <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-red-600 tw-mr-3"></i>
                        <div class="tw-flex-1">
                            <h4 class="tw-font-medium tw-text-red-900">Urgent Orders</h4>
                            <p class="tw-text-sm tw-text-red-700">Handle delayed or priority orders</p>
                        </div>
                        <span class="tw-bg-red-100 tw-text-red-800 tw-text-xs tw-font-medium tw-px-2 tw-py-1 tw-rounded-full" id="urgentOrdersCount">-</span>
                    </a>

                    <button onclick="sendBroadcastNotification()" class="tw-w-full tw-flex tw-items-center tw-p-3 tw-bg-purple-50 tw-rounded-lg tw-border tw-border-purple-200 hover:tw-bg-purple-100 tw-transition-colors">
                        <i data-feather="volume-2" class="tw-h-5 tw-w-5 tw-text-purple-600 tw-mr-3"></i>
                        <div class="tw-text-left">
                            <h4 class="tw-font-medium tw-text-purple-900">Broadcast Alert</h4>
                            <p class="tw-text-sm tw-text-purple-700">Send notification to all users</p>
                        </div>
                    </button>

                    <a href="<?= url('/admin/user-management/role-requests') ?>" class="tw-flex tw-items-center tw-p-3 tw-bg-orange-50 tw-rounded-lg tw-border tw-border-orange-200 hover:tw-bg-orange-100 tw-transition-colors">
                        <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-orange-600 tw-mr-3"></i>
                        <div class="tw-flex-1">
                            <h4 class="tw-font-medium tw-text-orange-900">Role Requests & Approvals</h4>
                            <p class="tw-text-sm tw-text-orange-700">Review role changes, user & restaurant applications</p>
                        </div>
                        <span class="tw-bg-orange-100 tw-text-orange-800 tw-text-xs tw-font-medium tw-px-2 tw-py-1 tw-rounded-full" id="pendingApprovalsCount">-</span>
                    </a>

                    <a href="<?= url('/admin/financial') ?>" class="tw-flex tw-items-center tw-p-3 tw-bg-green-50 tw-rounded-lg tw-border tw-border-green-200 hover:tw-bg-green-100 tw-transition-colors">
                        <i data-feather="trending-up" class="tw-h-5 tw-w-5 tw-text-green-600 tw-mr-3"></i>
                        <div>
                            <h4 class="tw-font-medium tw-text-green-900">Financial Reports</h4>
                            <p class="tw-text-sm tw-text-green-700">View revenue and analytics</p>
                        </div>
                    </a>

                    <button onclick="toggleMaintenanceMode()" class="tw-w-full tw-flex tw-items-center tw-p-3 tw-bg-gray-50 tw-rounded-lg tw-border tw-border-gray-200 hover:tw-bg-gray-100 tw-transition-colors">
                        <i data-feather="settings" class="tw-h-5 tw-w-5 tw-text-gray-600 tw-mr-3"></i>
                        <div class="tw-text-left">
                            <h4 class="tw-font-medium tw-text-gray-900">Maintenance Mode</h4>
                            <p class="tw-text-sm tw-text-gray-700">Toggle site maintenance status</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="tw-bg-white tw-shadow tw-rounded-lg">
            <div class="tw-px-4 tw-py-5 sm:tw-p-6">
                <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900 tw-mb-4">System Health</h3>
                <div class="tw-space-y-3">
                    <?php foreach ($systemHealth as $component => $status): ?>
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-900 tw-capitalize"><?= $component ?></span>
                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium
                                <?= $status === 'healthy' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-red-100 tw-text-red-800' ?>">
                                <i data-feather="<?= $status === 'healthy' ? 'check-circle' : 'alert-circle' ?>"
                                   class="tw-h-3 tw-w-3 tw-mr-1"></i>
                                <?= ucfirst($status) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Map Modal -->
<div id="liveMapModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-top-10 tw-mx-auto tw-p-5 tw-border tw-w-11/12 tw-max-w-6xl tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div>
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Live Delivery Map</h3>
                <p class="tw-text-sm tw-text-gray-500">Real-time tracking of all active deliveries</p>
            </div>
            <button onclick="closeLiveMap()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <i data-feather="x" class="tw-h-6 tw-w-6"></i>
            </button>
        </div>

        <!-- Map Legend -->
        <div class="tw-flex tw-gap-4 tw-mb-3 tw-text-xs tw-text-gray-600">
            <div class="tw-flex tw-items-center">
                <div class="tw-w-3 tw-h-3 tw-rounded-full tw-bg-orange-500 tw-mr-1"></div>
                <span>Restaurant</span>
            </div>
            <div class="tw-flex tw-items-center">
                <div class="tw-w-3 tw-h-3 tw-rounded-full tw-bg-blue-500 tw-mr-1"></div>
                <span>Rider</span>
            </div>
            <div class="tw-flex tw-items-center">
                <div class="tw-w-3 tw-h-3 tw-rounded-full tw-bg-green-500 tw-mr-1"></div>
                <span>Customer</span>
            </div>
        </div>

        <div id="liveMap" class="tw-w-full tw-h-[600px] tw-bg-gray-100 tw-rounded-lg"></div>

        <!-- Map Controls -->
        <div class="tw-mt-4 tw-flex tw-justify-between tw-items-center">
            <div class="tw-text-sm tw-text-gray-600">
                <span id="activeDeliveriesCount">0</span> active deliveries
            </div>
            <button onclick="refreshLiveMap()" class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm hover:tw-bg-blue-700">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                Refresh Map
            </button>
        </div>
    </div>
</div>

<script>
// Live Delivery Monitor
let deliveryUpdateInterval;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Start live delivery monitoring
    startDeliveryMonitoring();

    // Update quick action counts
    updateQuickActionCounts();
});

function startDeliveryMonitoring() {
    // Check if required elements exist
    const activeDeliveries = document.getElementById('activeDeliveries');
    const noDeliveries = document.getElementById('noDeliveries');
    
    if (!activeDeliveries || !noDeliveries) {
        console.error('Required delivery monitor elements not found, retrying in 1 second...');
        setTimeout(startDeliveryMonitoring, 1000);
        return;
    }
    
    // Initial load
    updateDeliveryMonitor();

    // Update every 30 seconds
    deliveryUpdateInterval = setInterval(updateDeliveryMonitor, 30000);
}

function updateDeliveryMonitor() {
    console.log('Updating delivery monitor...');
    
    fetch('<?= url('/admin/api/deliveries/live') ?>')
        .then(response => {
            console.log('API Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response data:', data);
            if (data.success) {
                console.log('Found deliveries:', data.deliveries.length);
                updateDeliveryList(data.deliveries);
                updateDeliveryStats(data.stats);
            } else {
                console.error('API returned error:', data.message);
                showNoDeliveries();
            }
        })
        .catch(error => {
            console.error('Error updating delivery monitor:', error);
            showNoDeliveries();
        });
}

function updateDeliveryList(deliveries) {
    console.log('updateDeliveryList called with:', deliveries);
    
    const container = document.getElementById('activeDeliveries');
    const noDeliveries = document.getElementById('noDeliveries');
    const lastUpdate = document.getElementById('lastUpdate');

    if (!container) {
        console.error('activeDeliveries container not found');
        return;
    }

    if (!noDeliveries) {
        console.error('noDeliveries element not found');
        return;
    }

    // Update last update time
    if (lastUpdate) {
        lastUpdate.textContent = new Date().toLocaleTimeString();
    }

    if (!deliveries || deliveries.length === 0) {
        console.log('No deliveries to display, showing no deliveries message');
        showNoDeliveries();
        return;
    }

    console.log('Displaying', deliveries.length, 'deliveries');
    noDeliveries.style.display = 'none';

    const html = deliveries.map(delivery => `
        <div class="tw-bg-white tw-border tw-border-gray-100 tw-rounded-lg tw-p-4 tw-shadow-sm hover:tw-shadow-md tw-transition-all tw-duration-200 tw-mb-4" 
             data-delivery-id="${delivery.id}" 
             data-rider-phone="${delivery.rider_phone || ''}"
             data-customer-phone="${delivery.customer_phone || ''}"
             data-restaurant-phone="${delivery.restaurant_phone || ''}">
            
            <!-- Header -->
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-orange-100 tw-rounded-lg">
                        <i data-feather="${getStatusIcon(delivery.status)}" class="tw-h-4 tw-w-4 tw-text-orange-600"></i>
                </div>
                    <div>
                        <h4 class="tw-text-sm tw-font-semibold tw-text-gray-900">Order #${delivery.order_id}</h4>
                        <div class="tw-flex tw-items-center tw-space-x-2 tw-mt-1">
                            <span class="tw-px-2 tw-py-1 tw-bg-gray-100 tw-text-gray-600 tw-text-xs tw-font-medium tw-rounded-full">
                                ${delivery.status_display}
                            </span>
                            ${delivery.priority === 'high' ? '<span class="tw-px-2 tw-py-1 tw-bg-red-100 tw-text-red-600 tw-text-xs tw-font-bold tw-rounded-full">URGENT</span>' : ''}
                            ${delivery.priority === 'medium' ? '<span class="tw-px-2 tw-py-1 tw-bg-yellow-100 tw-text-yellow-600 tw-text-xs tw-font-medium tw-rounded-full">PRIORITY</span>' : ''}
                    </div>
                    </div>
                </div>
                    </div>

            <!-- Info Grid - Clean Mobile First -->
            <div class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-3 tw-gap-3 tw-mb-4">
                <!-- Customer -->
                <div class="tw-flex tw-items-center tw-space-x-2 tw-p-2 tw-bg-gray-50 tw-rounded-lg">
                    <i data-feather="user" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                    <div class="tw-min-w-0 tw-flex-1">
                        <p class="tw-text-xs tw-font-medium tw-text-gray-900 tw-truncate">${delivery.customer_name}</p>
                        <p class="tw-text-xs tw-text-gray-500">Customer</p>
                </div>
            </div>

                <!-- Restaurant -->
                <div class="tw-flex tw-items-center tw-space-x-2 tw-p-2 tw-bg-gray-50 tw-rounded-lg">
                    <i data-feather="home" class="tw-h-4 tw-w-4 tw-text-orange-600"></i>
                    <div class="tw-min-w-0 tw-flex-1">
                        <p class="tw-text-xs tw-font-medium tw-text-gray-900 tw-truncate">${delivery.restaurant_name}</p>
                        <p class="tw-text-xs tw-text-gray-500">Restaurant</p>
                    </div>
                </div>

                <!-- Rider -->
                <div class="tw-flex tw-items-center tw-space-x-2 tw-p-2 tw-bg-gray-50 tw-rounded-lg">
                    <i data-feather="truck" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                    <div class="tw-min-w-0 tw-flex-1">
                        <p class="tw-text-xs tw-font-medium tw-text-gray-900 tw-truncate">${delivery.rider_name}</p>
                        <p class="tw-text-xs tw-text-gray-500">Rider</p>
                    </div>
                </div>

                <!-- ETA -->
                <div class="tw-flex tw-items-center tw-space-x-2 tw-p-2 tw-bg-gray-50 tw-rounded-lg">
                    <i data-feather="clock" class="tw-h-4 tw-w-4 tw-text-purple-600"></i>
                    <div class="tw-min-w-0 tw-flex-1">
                        <p class="tw-text-xs tw-font-medium tw-text-gray-900 tw-truncate">${delivery.estimated_time}</p>
                        <p class="tw-text-xs tw-text-gray-500">ETA</p>
                    </div>
                </div>

                <!-- Amount -->
                <div class="tw-flex tw-items-center tw-space-x-2 tw-p-2 tw-bg-gray-50 tw-rounded-lg">
                    <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-text-emerald-600"></i>
                    <div class="tw-min-w-0 tw-flex-1">
                        <p class="tw-text-xs tw-font-medium tw-text-gray-900 tw-truncate">${delivery.total_amount ? delivery.total_amount + ' FCFA' : 'N/A'}</p>
                        <p class="tw-text-xs tw-text-gray-500">Total</p>
                    </div>
                </div>

                <!-- Distance -->
                <div class="tw-flex tw-items-center tw-space-x-2 tw-p-2 tw-bg-gray-50 tw-rounded-lg">
                    <i data-feather="map-pin" class="tw-h-4 tw-w-4 tw-text-indigo-600"></i>
                    <div class="tw-min-w-0 tw-flex-1">
                        <p class="tw-text-xs tw-font-medium tw-text-gray-900 tw-truncate">${delivery.distance}</p>
                        <p class="tw-text-xs tw-text-gray-500">Distance</p>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="tw-mb-4">
                <div class="tw-flex tw-items-center tw-justify-between tw-text-xs tw-text-gray-500 tw-mb-2">
                    <span>Progress</span>
                    <span>${getProgressPercentage(delivery.status)}%</span>
                </div>
                <div class="tw-w-full tw-bg-gray-200 tw-rounded-full tw-h-2">
                    <div class="tw-bg-gradient-to-r tw-from-blue-500 tw-to-green-500 tw-h-2 tw-rounded-full tw-transition-all tw-duration-500" style="width: ${getProgressPercentage(delivery.status)}%"></div>
                </div>
            </div>

            <!-- Action Buttons - Mobile First -->
            <div class="tw-flex tw-flex-wrap tw-gap-2">
                <button onclick="trackDelivery('${delivery.id}')" 
                        class="tw-flex-1 tw-px-3 tw-py-2 tw-bg-blue-50 tw-text-blue-600 hover:tw-bg-blue-100 tw-rounded-lg tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-medium" 
                        title="Track Delivery">
                    <i data-feather="map-pin" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                    Track
                </button>
                <button onclick="callRider('${delivery.id}')" 
                        class="tw-flex-1 tw-px-3 tw-py-2 tw-bg-green-50 tw-text-green-600 hover:tw-bg-green-100 tw-rounded-lg tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-medium" 
                        title="Call Rider">
                    <i data-feather="truck" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                    Rider
                </button>
                <button onclick="callCustomer('${delivery.id}')" 
                        class="tw-flex-1 tw-px-3 tw-py-2 tw-bg-blue-50 tw-text-blue-600 hover:tw-bg-blue-100 tw-rounded-lg tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-medium" 
                        title="Call Customer">
                    <i data-feather="user" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                    Customer
                </button>
                <button onclick="callRestaurant('${delivery.id}')" 
                        class="tw-flex-1 tw-px-3 tw-py-2 tw-bg-orange-50 tw-text-orange-600 hover:tw-bg-orange-100 tw-rounded-lg tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-medium" 
                        title="Call Restaurant">
                    <i data-feather="home" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                    Restaurant
                </button>
                <button onclick="viewOrderDetails('${delivery.id}')" 
                        class="tw-flex-1 tw-px-3 tw-py-2 tw-bg-gray-50 tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-lg tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-medium" 
                        title="View Details">
                    <i data-feather="file-text" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                    Details
                </button>
            </div>
        </div>
    `).join('');

    container.innerHTML = html;

    // Re-initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

function updateDeliveryStats(stats) {
    const pendingCount = document.getElementById('pendingCount');
    const confirmedCount = document.getElementById('confirmedCount');
    const preparingCount = document.getElementById('preparingCount');
    const readyCount = document.getElementById('readyCount');
    const pickedUpCount = document.getElementById('pickedUpCount');
    const onTheWayCount = document.getElementById('onTheWayCount');
    const completedTodayCount = document.getElementById('completedTodayCount');

    if (pendingCount) pendingCount.textContent = stats.pending || 0;
    if (confirmedCount) confirmedCount.textContent = stats.confirmed || 0;
    if (preparingCount) preparingCount.textContent = stats.preparing || 0;
    if (readyCount) readyCount.textContent = stats.ready || 0;
    if (pickedUpCount) pickedUpCount.textContent = stats.picked_up || 0;
    if (onTheWayCount) onTheWayCount.textContent = stats.on_the_way || 0;
    if (completedTodayCount) completedTodayCount.textContent = stats.completed_today || 0;
}

function showNoDeliveries() {
    const noDeliveries = document.getElementById('noDeliveries');
    
    if (!noDeliveries) {
        console.error('noDeliveries element not found in showNoDeliveries');
        return;
    }
    
    noDeliveries.innerHTML = `
        <i data-feather="truck" class="tw-h-12 tw-w-12 tw-mx-auto tw-mb-4 tw-text-gray-300"></i>
        <p>No active deliveries at the moment</p>
    `;
    noDeliveries.style.display = 'block';

    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

function getStatusColor(status) {
    const colors = {
        'pending': 'tw-bg-orange-500',
        'confirmed': 'tw-bg-blue-500',
        'preparing': 'tw-bg-indigo-500',
        'ready': 'tw-bg-yellow-500',
        'picked_up': 'tw-bg-green-500',
        'on_the_way': 'tw-bg-purple-500',
        'delivered': 'tw-bg-emerald-500',
        'cancelled': 'tw-bg-red-500'
    };
    return colors[status] || 'tw-bg-gray-400';
}

function getStatusIcon(status) {
    const icons = {
        'pending': 'clock',
        'confirmed': 'check-circle',
        'preparing': 'coffee',
        'ready': 'package',
        'picked_up': 'truck',
        'on_the_way': 'navigation',
        'delivered': 'check-circle',
        'cancelled': 'x-circle'
    };
    return icons[status] || 'circle';
}

function getProgressPercentage(status) {
    const progress = {
        'pending': 10,
        'confirmed': 20,
        'preparing': 40,
        'ready': 60,
        'picked_up': 80,
        'on_the_way': 90,
        'delivered': 100,
        'cancelled': 0
    };
    return progress[status] || 0;
}

function trackDelivery(deliveryId) {
    // Open tracking modal or redirect to detailed tracking page
    window.open(`<?= url('/admin/deliveries') ?>/${deliveryId}/track`, '_blank');
}

function callRider(deliveryId) {
    // Find the delivery data to get rider phone
    const deliveryElement = document.querySelector(`[onclick*="callRider('${deliveryId}')"]`);
    if (deliveryElement) {
        const deliveryCard = deliveryElement.closest('.tw-group');
        const riderPhone = deliveryCard ? deliveryCard.getAttribute('data-rider-phone') : null;
        
        if (riderPhone) {
            // Open phone dialer
            window.open(`tel:${riderPhone}`, '_self');
        } else {
            alert('Rider phone number not available for this delivery.');
        }
    } else {
        alert('Unable to find delivery information.');
    }
}

// Live Map Functionality
let liveMap = null;
let mapMarkers = [];

async function openLiveMap() {
    const modal = document.getElementById('liveMapModal');
    modal.classList.remove('tw-hidden');
    
    if (!liveMap) {
        // Initialize map
        liveMap = new MapProvider({
            container: 'liveMap',
            provider: window.MAP_CONFIG.provider,
            apiKey: window.MAP_CONFIG.apiKey,
            center: [5.9631, 10.1591], // Default to Bamenda
            zoom: 13
        });
        
        await liveMap.init();
    }
    
    refreshLiveMap();
}

function closeLiveMap() {
    document.getElementById('liveMapModal').classList.add('tw-hidden');
}

function refreshLiveMap() {
    if (!liveMap) return;
    
    const button = document.querySelector('button[onclick="refreshLiveMap()"]');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-inline tw-mr-1 tw-animate-spin"></i> Refreshing...';
    button.disabled = true;
    if (typeof feather !== 'undefined') feather.replace();

    // Clear existing markers
    mapMarkers.forEach(marker => {
        if (liveMap.provider === 'google') {
            marker.setMap(null);
        } else {
            marker.remove();
        }
    });
    mapMarkers = [];

    // Fetch active deliveries
    fetch('<?= url('/admin/api/deliveries/live') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.deliveries) {
                const bounds = [];
                document.getElementById('activeDeliveriesCount').textContent = data.deliveries.length;

                data.deliveries.forEach(delivery => {
                    // Add Restaurant Marker
                    if (delivery.restaurant_latitude && delivery.restaurant_longitude) {
                        const restMarker = liveMap.addMarker({
                            lat: parseFloat(delivery.restaurant_latitude),
                            lng: parseFloat(delivery.restaurant_longitude),
                            title: delivery.restaurant_name,
                            iconType: 'restaurant',
                            infoWindowContent: `
                                <div class="tw-p-2">
                                    <h4 class="tw-font-bold">${delivery.restaurant_name}</h4>
                                    <p class="tw-text-xs">Restaurant</p>
                                    <p class="tw-text-xs">Order #${delivery.order_id}</p>
                                </div>
                            `
                        });
                        mapMarkers.push(restMarker);
                        bounds.push([parseFloat(delivery.restaurant_latitude), parseFloat(delivery.restaurant_longitude)]);
                    }

                    // Add Customer Marker
                    if (delivery.delivery_latitude && delivery.delivery_longitude) {
                        const custMarker = liveMap.addMarker({
                            lat: parseFloat(delivery.delivery_latitude),
                            lng: parseFloat(delivery.delivery_longitude),
                            title: delivery.customer_name,
                            iconType: 'customer',
                            infoWindowContent: `
                                <div class="tw-p-2">
                                    <h4 class="tw-font-bold">${delivery.customer_name}</h4>
                                    <p class="tw-text-xs">Customer</p>
                                    <p class="tw-text-xs">Order #${delivery.order_id}</p>
                                </div>
                            `
                        });
                        mapMarkers.push(custMarker);
                        bounds.push([parseFloat(delivery.delivery_latitude), parseFloat(delivery.delivery_longitude)]);
                    }

                    // Add Rider Marker (if assigned and has location)
                    if (delivery.rider_latitude && delivery.rider_longitude) {
                        const riderMarker = liveMap.addMarker({
                            lat: parseFloat(delivery.rider_latitude),
                            lng: parseFloat(delivery.rider_longitude),
                            title: delivery.rider_name,
                            iconType: 'rider',
                            infoWindowContent: `
                                <div class="tw-p-2">
                                    <h4 class="tw-font-bold">${delivery.rider_name}</h4>
                                    <p class="tw-text-xs">Rider</p>
                                    <p class="tw-text-xs">Status: ${delivery.status}</p>
                                </div>
                            `
                        });
                        mapMarkers.push(riderMarker);
                        bounds.push([parseFloat(delivery.rider_latitude), parseFloat(delivery.rider_longitude)]);
                    }
                });

                // Fit bounds if we have markers
                if (bounds.length > 0) {
                    liveMap.fitBounds(bounds);
                }
            }
        })
        .catch(error => console.error('Error refreshing map:', error))
        .finally(() => {
            button.innerHTML = originalContent;
            button.disabled = false;
            if (typeof feather !== 'undefined') feather.replace();
        });
}

function callCustomer(deliveryId) {
    // Find the delivery data to get customer phone
    const deliveryElement = document.querySelector(`[onclick*="callCustomer('${deliveryId}')"]`);
    if (deliveryElement) {
        const deliveryCard = deliveryElement.closest('.tw-group');
        const customerPhone = deliveryCard ? deliveryCard.getAttribute('data-customer-phone') : null;
        
        if (customerPhone) {
            // Open phone dialer
            window.open(`tel:${customerPhone}`, '_self');
        } else {
            alert('Customer phone number not available for this delivery.');
        }
    } else {
        alert('Unable to find delivery information.');
    }
}

function callRestaurant(deliveryId) {
    // Find the delivery data to get restaurant phone
    const deliveryElement = document.querySelector(`[onclick*="callRestaurant('${deliveryId}')"]`);
    if (deliveryElement) {
        const deliveryCard = deliveryElement.closest('.tw-group');
        const restaurantPhone = deliveryCard ? deliveryCard.getAttribute('data-restaurant-phone') : null;
        
        if (restaurantPhone) {
            // Open phone dialer
            window.open(`tel:${restaurantPhone}`, '_self');
        } else {
            alert('Restaurant phone number not available for this delivery.');
        }
    } else {
        alert('Unable to find delivery information.');
    }
}

function viewOrderDetails(deliveryId) {
    // Open order details modal or redirect to order details page
    window.open(`<?= url('/admin/orders') ?>/${deliveryId}`, '_blank');
}

function refreshDeliveries() {
    // Manual refresh of delivery data
    updateDeliveryMonitor();
    
    // Show refresh feedback
    const refreshBtn = document.querySelector('button[onclick="refreshDeliveries()"]');
    if (refreshBtn) {
        const originalContent = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-animate-spin"></i>';
        refreshBtn.disabled = true;
        
        setTimeout(() => {
            refreshBtn.innerHTML = originalContent;
            refreshBtn.disabled = false;
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }, 2000);
    }
}

// Quick Actions Functions
function updateQuickActionCounts() {
    fetch('<?= url('/admin/api/quick-actions') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.counts) {
                const urgentOrdersCount = document.getElementById('urgentOrdersCount');
                const pendingApprovalsCount = document.getElementById('pendingApprovalsCount');
                
                if (urgentOrdersCount) urgentOrdersCount.textContent = data.counts.urgent_orders || 0;
                if (pendingApprovalsCount) pendingApprovalsCount.textContent = data.counts.pending_approvals || 0;
            }
        })
        .catch(error => console.error('Error updating counts:', error));
}

function sendBroadcastNotification() {
    const message = prompt('Enter broadcast message (max 500 characters):');
    if (message && message.trim()) {
        if (message.length > 500) {
            alert('Message too long. Please keep it under 500 characters.');
            return;
        }
        
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i data-feather="loader" class="tw-animate-spin tw-h-4 tw-w-4 tw-mr-2"></i>Sending...';
        button.disabled = true;
        
        fetch('<?= url('/admin/api/notifications/broadcast') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ message: message.trim() })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Show success notification
                showNotification('Success', `Broadcast sent to ${data.count || 'all'} users successfully!`, 'success');
            } else {
                showNotification('Error', data.message || 'Failed to send notification', 'error');
            }
        })
        .catch(error => {
            console.error('Error sending broadcast:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack
            });
            showNotification('Error', `Failed to send notification: ${error.message}`, 'error');
        })
        .finally(() => {
            // Reset button state
            button.innerHTML = originalText;
            button.disabled = false;
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    }
}

// Helper function to show notifications
function showNotification(title, message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 tw-z-50 tw-max-w-sm tw-p-4 tw-rounded-lg tw-shadow-lg tw-transition-all tw-duration-300 tw-transform tw-translate-x-0`;
    
    const bgColor = type === 'success' ? 'tw-bg-green-500' :
                   type === 'error' ? 'tw-bg-red-500' :
                   type === 'warning' ? 'tw-bg-yellow-500' : 'tw-bg-blue-500';
    
    notification.classList.add(bgColor, 'tw-text-white');
    notification.innerHTML = `
        <div class="tw-flex tw-items-start tw-space-x-3">
            <div class="tw-flex-shrink-0">
                <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="tw-w-5 tw-h-5"></i>
            </div>
            <div class="tw-flex-1">
                <h4 class="tw-font-semibold">${title}</h4>
                <p class="tw-text-sm tw-opacity-90">${message}</p>
            </div>
            <div class="tw-flex-shrink-0">
                <button onclick="this.closest('div').remove()" class="tw-text-white tw-opacity-75 hover:tw-opacity-100 tw-focus:tw-outline-none">
                    <i data-feather="x" class="tw-w-4 tw-h-4"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentElement) {
                notification.parentElement.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

function toggleMaintenanceMode() {
    if (confirm('Are you sure you want to toggle maintenance mode?')) {
        fetch('<?= url('/admin/api/maintenance/toggle') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Maintenance mode ${data.enabled ? 'enabled' : 'disabled'}`);
                location.reload();
            } else {
                alert('Failed to toggle maintenance mode');
            }
        })
        .catch(error => {
            console.error('Error toggling maintenance mode:', error);
            alert('Error toggling maintenance mode');
        });
    }
}

// Dashboard Functions
function refreshDashboard() {
    location.reload();
}

function exportDashboardData() {
    // Show loading indicator
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i data-feather="loader" class="tw-animate-spin tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>Exporting...';
    button.disabled = true;

    // Create download link to export dashboard data
    const link = document.createElement('a');
    link.href = '/admin/export/dashboard';
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Reset button state after delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        // Re-initialize Feather icons after content change
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 3000);
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (deliveryUpdateInterval) {
        clearInterval(deliveryUpdateInterval);
    }
});
</script>
