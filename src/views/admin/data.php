<?php
/**
 * Admin Data Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'data';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Data Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Import, export, and manage platform data
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-flex tw-items-center tw-justify-center">
                <i data-feather="help-circle" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Help Guide
            </button>
            <button class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-flex tw-items-center tw-justify-center">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Refresh
            </button>
        </div>
    </div>
</div>

<!-- Data Overview -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <!-- Total Users -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Users</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_users'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">Platform users</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- Total Restaurants -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Restaurants</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_restaurants'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">Active partners</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="home" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Orders</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_orders'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-purple-600">All time</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="shopping-bag" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>

    <!-- Database Size -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Database Size</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['storage_used_gb'] ?? 0, 2) ?>GB</p>
                <p class="tw-text-sm tw-text-yellow-600"><?= $stats['storage_used'] ?? 0 ?>% used</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="database" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Additional Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <!-- Customers -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Customers</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_customers'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-blue-600">Active users</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="user" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- Vendors -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Vendors</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_vendors'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-green-600">Restaurant owners</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="user-check" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <!-- Riders -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Riders</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_riders'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-orange-600">Delivery partners</p>
            </div>
            <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                <i data-feather="truck" class="tw-h-5 tw-w-5 tw-text-orange-600"></i>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Recent Orders</p>
                <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['recent_orders'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-purple-600">Last 24 hours</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Data Operations -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
    <!-- Import Data -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Import Data</h2>
            <p class="tw-text-sm tw-text-gray-600">Upload CSV or JSON files to import data</p>
        </div>
        <div class="tw-p-6 tw-space-y-6">
            <div>
                <label for="importType" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3">Data Type</label>
                <div class="tw-relative">
                    <select id="importType" class="tw-block tw-w-full tw-px-3 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-sm tw-text-gray-900 tw-bg-white focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-colors tw-duration-200 tw-appearance-none tw-pr-10">
                    <option value="">Select data type...</option>
                    <option value="users">Users</option>
                    <option value="restaurants">Restaurants</option>
                    <option value="menu_items">Menu Items</option>
                    <option value="categories">Categories</option>
                    <option value="orders">Orders</option>
                </select>
                    <i data-feather="chevron-down" class="tw-absolute tw-right-3 tw-top-2.5 tw-h-4 tw-w-4 tw-text-gray-400 tw-pointer-events-none"></i>
                </div>
            </div>
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3">File Upload</label>
                <div class="tw-relative tw-flex tw-justify-center tw-px-6 tw-py-12 tw-border-2 tw-border-gray-300 tw-border-dashed tw-rounded-lg tw-bg-gray-50 hover:tw-bg-gray-100 tw-transition-colors tw-duration-200">
                    <div class="tw-space-y-2 tw-text-center">
                        <i data-feather="upload-cloud" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400"></i>
                        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-justify-center tw-text-sm tw-text-gray-600 tw-gap-1">
                            <label for="file-upload" class="tw-relative tw-cursor-pointer tw-font-medium tw-text-primary-600 hover:tw-text-primary-500 tw-transition-colors">
                                <span>Click to upload</span>
                                <input id="file-upload" name="file-upload" type="file" class="tw-sr-only" accept=".csv,.json">
                            </label>
                            <span class="tw-hidden sm:tw-inline">or drag and drop</span>
                        </div>
                        <p class="tw-text-xs tw-text-gray-500">CSV or JSON files up to 10MB</p>
                    </div>
                </div>
            </div>
            
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3">
                <button onclick="downloadTemplate()" class="tw-flex-1 tw-bg-gray-100 tw-text-gray-700 tw-py-2.5 tw-px-4 tw-rounded-lg tw-font-medium hover:tw-bg-gray-200 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                    <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Download Template
                </button>
                <button onclick="startImport()" class="tw-flex-1 tw-bg-primary-600 tw-text-white tw-py-2.5 tw-px-4 tw-rounded-lg tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                    <i data-feather="upload" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Start Import
                </button>
            </div>
        </div>
    </div>

    <!-- Export Data -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Export Data</h2>
            <p class="tw-text-sm tw-text-gray-600">Download data in various formats with advanced filtering</p>
        </div>
        <div class="tw-p-6">
            <!-- Quick Export Options -->
            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4 tw-mb-6">
                <!-- Users Export -->
                <div class="tw-bg-blue-50 tw-p-4 tw-rounded-lg tw-border tw-border-blue-200 tw-transition-all tw-duration-200 hover:tw-shadow-md">
                    <div class="tw-flex tw-items-center tw-mb-3">
                        <i data-feather="users" class="tw-h-5 tw-w-5 tw-text-blue-600 tw-mr-2"></i>
                        <h3 class="tw-text-sm tw-font-semibold tw-text-blue-900">Users Data</h3>
                    </div>
                    <p class="tw-text-xs tw-text-blue-700 tw-mb-4 tw-min-h-[2.5rem]">Export user profiles and activity data</p>
                    <button onclick="exportData('users')" class="tw-w-full tw-bg-blue-600 tw-text-white tw-py-2.5 tw-px-4 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                        <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Export
                    </button>
                </div>

                <!-- Restaurants Export -->
                <div class="tw-bg-green-50 tw-p-4 tw-rounded-lg tw-border tw-border-green-200 tw-transition-all tw-duration-200 hover:tw-shadow-md">
                    <div class="tw-flex tw-items-center tw-mb-3">
                        <i data-feather="home" class="tw-h-5 tw-w-5 tw-text-green-600 tw-mr-2"></i>
                        <h3 class="tw-text-sm tw-font-semibold tw-text-green-900">Restaurants Data</h3>
                    </div>
                    <p class="tw-text-xs tw-text-green-700 tw-mb-4 tw-min-h-[2.5rem]">Export restaurant profiles and menu data</p>
                    <button onclick="exportData('restaurants')" class="tw-w-full tw-bg-green-600 tw-text-white tw-py-2.5 tw-px-4 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-green-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                        <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Export
                    </button>
                </div>

                <!-- Orders Export -->
                <div class="tw-bg-purple-50 tw-p-4 tw-rounded-lg tw-border tw-border-purple-200 tw-transition-all tw-duration-200 hover:tw-shadow-md">
                    <div class="tw-flex tw-items-center tw-mb-3">
                        <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-purple-600 tw-mr-2"></i>
                        <h3 class="tw-text-sm tw-font-semibold tw-text-purple-900">Orders Data</h3>
                    </div>
                    <p class="tw-text-xs tw-text-purple-700 tw-mb-4 tw-min-h-[2.5rem]">Export order details and transactions</p>
                    <button onclick="exportData('orders')" class="tw-w-full tw-bg-purple-600 tw-text-white tw-py-2.5 tw-px-4 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-purple-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                        <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Export
                    </button>
                </div>

                <!-- Analytics Export -->
                <div class="tw-bg-orange-50 tw-p-4 tw-rounded-lg tw-border tw-border-orange-200 tw-transition-all tw-duration-200 hover:tw-shadow-md">
                    <div class="tw-flex tw-items-center tw-mb-3">
                        <i data-feather="bar-chart-2" class="tw-h-5 tw-w-5 tw-text-orange-600 tw-mr-2"></i>
                        <h3 class="tw-text-sm tw-font-semibold tw-text-orange-900">Analytics Report</h3>
                    </div>
                    <p class="tw-text-xs tw-text-orange-700 tw-mb-4 tw-min-h-[2.5rem]">Export comprehensive analytics data</p>
                    <button onclick="exportData('analytics')" class="tw-w-full tw-bg-orange-600 tw-text-white tw-py-2.5 tw-px-4 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-orange-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                        <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Export
                    </button>
                </div>

                <!-- Financial Export -->
                <div class="tw-bg-red-50 tw-p-4 tw-rounded-lg tw-border tw-border-red-200 tw-transition-all tw-duration-200 hover:tw-shadow-md">
                    <div class="tw-flex tw-items-center tw-mb-3">
                        <i data-feather="dollar-sign" class="tw-h-5 tw-w-5 tw-text-red-600 tw-mr-2"></i>
                        <h3 class="tw-text-sm tw-font-semibold tw-text-red-900">Financial Report</h3>
                    </div>
                    <p class="tw-text-xs tw-text-red-700 tw-mb-4 tw-min-h-[2.5rem]">Export financial and payment data</p>
                    <button onclick="exportData('payments')" class="tw-w-full tw-bg-red-600 tw-text-white tw-py-2.5 tw-px-4 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-red-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                        <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Export
                    </button>
            </div>
            
                <!-- All Data Export -->
                <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg tw-border tw-border-gray-200 tw-transition-all tw-duration-200 hover:tw-shadow-md">
                    <div class="tw-flex tw-items-center tw-mb-3">
                        <i data-feather="database" class="tw-h-5 tw-w-5 tw-text-gray-600 tw-mr-2"></i>
                        <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900">All Data</h3>
                    </div>
                    <p class="tw-text-xs tw-text-gray-700 tw-mb-4 tw-min-h-[2.5rem]">Export complete platform data</p>
                    <button onclick="exportData('all')" class="tw-w-full tw-bg-gray-600 tw-text-white tw-py-2.5 tw-px-4 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-gray-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                        <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Export
                    </button>
                </div>
            </div>
            
            <!-- Advanced Export Options -->
            <div class="tw-border-t tw-border-gray-200 tw-pt-6">
                <h3 class="tw-text-md tw-font-semibold tw-text-gray-900 tw-mb-6">Advanced Export Options</h3>
                <div class="tw-space-y-6">
                    <!-- Date Range Section -->
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3">Date Range</label>
                        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                            <div class="tw-relative">
                                <label for="exportStartDate" class="tw-block tw-text-xs tw-font-medium tw-text-gray-500 tw-mb-1">From Date</label>
                                <div class="tw-relative">
                                    <input type="date" id="exportStartDate" class="tw-block tw-w-full tw-px-3 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-sm tw-text-gray-900 tw-bg-white focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-colors tw-duration-200 placeholder:tw-text-gray-400">
                                    <i data-feather="calendar" class="tw-absolute tw-right-3 tw-top-2.5 tw-h-4 tw-w-4 tw-text-gray-400 tw-pointer-events-none"></i>
                                </div>
                            </div>
                            <div class="tw-relative">
                                <label for="exportEndDate" class="tw-block tw-text-xs tw-font-medium tw-text-gray-500 tw-mb-1">To Date</label>
                                <div class="tw-relative">
                                    <input type="date" id="exportEndDate" class="tw-block tw-w-full tw-px-3 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-sm tw-text-gray-900 tw-bg-white focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-colors tw-duration-200 placeholder:tw-text-gray-400">
                                    <i data-feather="calendar" class="tw-absolute tw-right-3 tw-top-2.5 tw-h-4 tw-w-4 tw-text-gray-400 tw-pointer-events-none"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Format Section -->
            <div>
                        <label for="exportFormat" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3">Export Format</label>
                        <div class="tw-relative">
                            <select id="exportFormat" class="tw-block tw-w-full tw-px-3 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-sm tw-text-gray-900 tw-bg-white focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-colors tw-duration-200 tw-appearance-none tw-pr-10">
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="json">JSON (.json)</option>
                            </select>
                            <i data-feather="chevron-down" class="tw-absolute tw-right-3 tw-top-2.5 tw-h-4 tw-w-4 tw-text-gray-400 tw-pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg tw-border tw-border-gray-200">
                        <h4 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-3">Quick Actions</h4>
                        <div class="tw-flex tw-flex-wrap tw-gap-2">
                            <button onclick="setDateRange('today')" class="tw-px-3 tw-py-1.5 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-text-xs tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors">
                                Today
                            </button>
                            <button onclick="setDateRange('week')" class="tw-px-3 tw-py-1.5 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-text-xs tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors">
                                This Week
                            </button>
                            <button onclick="setDateRange('month')" class="tw-px-3 tw-py-1.5 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-text-xs tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors">
                                This Month
                            </button>
                            <button onclick="setDateRange('year')" class="tw-px-3 tw-py-1.5 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-text-xs tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors">
                                This Year
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Operations -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Recent Operations</h2>
            <button class="tw-text-primary-600 hover:tw-text-primary-700 tw-text-sm tw-font-medium">
                View All
            </button>
        </div>
    </div>
    
    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Operation</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Type</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Records</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <?php if (!empty($recentOperations)): ?>
                    <?php foreach ($recentOperations as $operation): ?>
                <tr class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($operation['id']) ?></div>
                        <div class="tw-text-sm tw-text-gray-500"><?= e($operation['file']) ?></div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-items-center">
                            <i data-feather="<?= $operation['type'] === 'Import' ? 'upload' : 'download' ?>" class="tw-h-4 tw-w-4 tw-text-gray-400 tw-mr-2"></i>
                            <div>
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= e($operation['type']) ?></div>
                                <div class="tw-text-sm tw-text-gray-500"><?= e($operation['data_type']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                        <?= number_format($operation['records']) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium 
                            <?php 
                            switch($operation['status']) {
                                case 'completed': echo 'tw-bg-green-100 tw-text-green-800'; break;
                                case 'processing': echo 'tw-bg-blue-100 tw-text-blue-800'; break;
                                case 'failed': echo 'tw-bg-red-100 tw-text-red-800'; break;
                                default: echo 'tw-bg-gray-100 tw-text-gray-800';
                            }
                            ?>">
                            <?= ucfirst($operation['status']) ?>
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                        <?= date('M j, Y H:i', strtotime($operation['date'])) ?>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <div class="tw-flex tw-space-x-2">
                            <?php if ($operation['status'] === 'completed' && $operation['type'] === 'Export'): ?>
                                <button class="tw-text-green-600 hover:tw-text-green-900" onclick="downloadFile('<?= $operation['id'] ?>')">
                                    <i data-feather="download" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                            <button class="tw-text-primary-600 hover:tw-text-primary-900" onclick="viewOperation('<?= $operation['id'] ?>')">
                                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                            </button>
                            <?php if ($operation['status'] === 'failed'): ?>
                                <button class="tw-text-orange-600 hover:tw-text-orange-900" onclick="retryOperation('<?= $operation['id'] ?>')">
                                    <i data-feather="rotate-cw" class="tw-h-4 tw-w-4"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6" class="tw-px-6 tw-py-12 tw-text-center">
                        <div class="tw-flex tw-flex-col tw-items-center">
                            <i data-feather="inbox" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-4"></i>
                            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No Recent Operations</h3>
                            <p class="tw-text-sm tw-text-gray-500 tw-mb-4">No import or export operations have been performed yet.</p>
                            <button onclick="exportData('users')" class="tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                Start Export
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Data management functions
function downloadTemplate() {
    const importType = document.getElementById('importType').value;
    if (!importType) {
        alert('Please select a data type first.');
        return;
    }
    
    window.location.href = `/admin/data/templates/${importType}`;
}

function startImport() {
    const importType = document.getElementById('importType').value;
    const fileInput = document.getElementById('file-upload');
    
    if (!importType) {
        alert('Please select a data type.');
        return;
    }
    
    if (!fileInput.files.length) {
        alert('Please select a file to import.');
        return;
    }
    
    const formData = new FormData();
    formData.append('type', importType);
    formData.append('file', fileInput.files[0]);
    
    fetch('<?= url('/admin/data/import') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Import started successfully. You can monitor progress in the operations table.');
            location.reload();
        } else {
            alert('Error starting import: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while starting the import.');
    });
}

function exportData(type) {
    const format = document.getElementById('exportFormat').value || 'excel';
    const startDate = document.getElementById('exportStartDate').value;
    const endDate = document.getElementById('exportEndDate').value;
    
    // Build URL with parameters
    const params = new URLSearchParams();
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    if (format) params.append('format', format);
    
    const url = `<?= url('/admin/export') ?>/${type}${params.toString() ? '?' + params.toString() : ''}`;
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-inline tw-mr-1 tw-animate-spin"></i>Exporting...';
    button.disabled = true;
    
    // Trigger download
    window.location.href = url;
    
    // Reset button after a delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        feather.replace();
    }, 2000);
}

function startExport() {
    const exportType = document.getElementById('exportType').value;
    if (!exportType) {
        alert('Please select a data type.');
        return;
    }
    exportData(exportType);
}

function setDateRange(range) {
    const startDateInput = document.getElementById('exportStartDate');
    const endDateInput = document.getElementById('exportEndDate');
    const today = new Date();
    
    let startDate, endDate;
    
    switch(range) {
        case 'today':
            startDate = endDate = today;
            break;
        case 'week':
            startDate = new Date(today);
            startDate.setDate(today.getDate() - 7);
            endDate = today;
            break;
        case 'month':
            startDate = new Date(today);
            startDate.setMonth(today.getMonth() - 1);
            endDate = today;
            break;
        case 'year':
            startDate = new Date(today);
            startDate.setFullYear(today.getFullYear() - 1);
            endDate = today;
            break;
        default:
            return;
    }
    
    // Format dates as YYYY-MM-DD
    const formatDate = (date) => {
        return date.toISOString().split('T')[0];
    };
    
    startDateInput.value = formatDate(startDate);
    endDateInput.value = formatDate(endDate);
}

function viewOperation(operationId) {
    window.location.href = `/admin/data/operations/${operationId}`;
}

function downloadFile(operationId) {
    window.location.href = `/admin/data/download/${operationId}`;
}

function retryOperation(operationId) {
    if (confirm('Are you sure you want to retry this operation?')) {
        fetch(`/admin/data/operations/${operationId}/retry`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error retrying operation: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while retrying the operation.');
        });
    }
}

// File upload drag and drop
const fileUpload = document.getElementById('file-upload');
const uploadArea = fileUpload.closest('.tw-border-dashed');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    uploadArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    uploadArea.classList.add('tw-border-primary-500', 'tw-bg-primary-50');
}

function unhighlight(e) {
    uploadArea.classList.remove('tw-border-primary-500', 'tw-bg-primary-50');
}

uploadArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    fileUpload.files = files;
}

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
