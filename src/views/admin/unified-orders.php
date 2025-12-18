<?php
/**
 * Admin Unified Orders Dashboard
 * Integrates the unified order management system into the admin dashboard
 */

// Set page variables
$pageTitle = 'Order Management';
$currentPage = 'orders';
$userRole = 'admin';

// Include the unified dashboard component
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Time2Eat Admin</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Chart.js for analytics - Using UMD build for compatibility -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom styles -->
    <style>
        .tw-animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        
        .tw-animate-spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body class="tw-bg-gray-50">
    
    <!-- Admin Navigation -->
    <?php include_once __DIR__ . '/navigation.php'; ?>
    
    <!-- Main Content -->
    <div class="tw-flex">
        <!-- Sidebar -->
        <?php include_once __DIR__ . '/../components/sidebar-content.php'; ?>
        
        <!-- Main Content Area -->
        <div class="tw-flex-1 tw-ml-64 tw-p-8">
            
            <!-- Page Header -->
            <div class="tw-mb-8">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900">Order Management</h1>
                        <p class="tw-text-gray-600 tw-mt-2">Unified order coordination across all dashboards</p>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="tw-flex tw-space-x-4">
                        <button onclick="exportOrders()" 
                                class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                            <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                            Export Orders
                        </button>
                        <button onclick="openBulkActions()" 
                                class="tw-bg-gray-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium hover:tw-bg-gray-700 tw-transition-colors">
                            <i data-feather="settings" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                            Bulk Actions
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Unified Order Dashboard Component -->
            <?php include_once __DIR__ . '/../components/unified-order-dashboard.php'; ?>
            
            <!-- Additional Admin-Specific Features -->
            <div class="tw-mt-8 tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6">
                
                <!-- Order Analytics Chart -->
                <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Order Trends</h3>
                    <canvas id="orderTrendsChart" width="400" height="200"></canvas>
                </div>
                
                <!-- Revenue Analytics -->
                <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Revenue Analytics</h3>
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
                
            </div>
            
            <!-- System Health Indicators -->
            <div class="tw-mt-8">
                <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">System Health</h3>
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-4" id="system-health">
                        <!-- Health indicators will be loaded here -->
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- Bulk Actions Modal -->
    <div id="bulk-actions-modal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
        <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Bulk Actions</h3>
                <button onclick="closeBulkActions()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            <div class="tw-space-y-4">
                <button onclick="bulkUpdateStatus()" 
                        class="tw-w-full tw-text-left tw-p-3 tw-rounded-lg tw-border tw-border-gray-200 hover:tw-bg-gray-50">
                    <i data-feather="edit" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                    Bulk Status Update
                </button>
                <button onclick="bulkExport()" 
                        class="tw-w-full tw-text-left tw-p-3 tw-rounded-lg tw-border tw-border-gray-200 hover:tw-bg-gray-50">
                    <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                    Export Selected
                </button>
                <button onclick="bulkNotify()" 
                        class="tw-w-full tw-text-left tw-p-3 tw-rounded-lg tw-border tw-border-gray-200 hover:tw-bg-gray-50">
                    <i data-feather="bell" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                    Send Notifications
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize charts
        let orderTrendsChart, revenueChart;
        
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            loadSystemHealth();
            feather.replace();
        });
        
        // Initialize analytics charts
        function initializeCharts() {
            // Order Trends Chart
            const orderCtx = document.getElementById('orderTrendsChart').getContext('2d');
            orderTrendsChart = new Chart(orderCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Orders',
                        data: [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Revenue (XAF)',
                        data: [],
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Load chart data
            loadChartData();
        }
        
        // Load chart data
        function loadChartData() {
            fetch('/api/unified-orders/analytics')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCharts(data.analytics);
                    }
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                });
        }
        
        // Update charts with new data
        function updateCharts(analytics) {
            // Update order trends
            orderTrendsChart.data.labels = analytics.dates;
            orderTrendsChart.data.datasets[0].data = analytics.order_counts;
            orderTrendsChart.update();
            
            // Update revenue chart
            revenueChart.data.labels = analytics.dates;
            revenueChart.data.datasets[0].data = analytics.revenues;
            revenueChart.update();
        }
        
        // Load system health indicators
        function loadSystemHealth() {
            fetch('/api/unified-orders/system-health')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderSystemHealth(data.health);
                    }
                })
                .catch(error => {
                    console.error('Error loading system health:', error);
                });
        }
        
        // Render system health indicators
        function renderSystemHealth(health) {
            const container = document.getElementById('system-health');
            
            const indicators = [
                { key: 'database', label: 'Database', status: health.database },
                { key: 'api', label: 'API', status: health.api },
                { key: 'notifications', label: 'Notifications', status: health.notifications },
                { key: 'payments', label: 'Payments', status: health.payments }
            ];
            
            container.innerHTML = indicators.map(indicator => `
                <div class="tw-flex tw-items-center tw-space-x-3 tw-p-3 tw-rounded-lg tw-border ${
                    indicator.status === 'healthy' ? 'tw-border-green-200 tw-bg-green-50' :
                    indicator.status === 'warning' ? 'tw-border-yellow-200 tw-bg-yellow-50' :
                    'tw-border-red-200 tw-bg-red-50'
                }">
                    <div class="tw-h-3 tw-w-3 tw-rounded-full ${
                        indicator.status === 'healthy' ? 'tw-bg-green-500' :
                        indicator.status === 'warning' ? 'tw-bg-yellow-500' :
                        'tw-bg-red-500'
                    }"></div>
                    <div>
                        <div class="tw-font-medium tw-text-gray-900">${indicator.label}</div>
                        <div class="tw-text-sm tw-text-gray-600 tw-capitalize">${indicator.status}</div>
                    </div>
                </div>
            `).join('');
        }
        
        // Export orders
        function exportOrders() {
            const filters = {
                status: document.getElementById('status-filter').value,
                time: document.getElementById('time-filter').value,
                search: document.getElementById('order-search').value
            };
            
            const params = new URLSearchParams(filters);
            window.open(`/api/unified-orders/export?${params}`, '_blank');
        }
        
        // Bulk actions
        function openBulkActions() {
            document.getElementById('bulk-actions-modal').classList.remove('tw-hidden');
        }
        
        function closeBulkActions() {
            document.getElementById('bulk-actions-modal').classList.add('tw-hidden');
        }
        
        function bulkUpdateStatus() {
            // Implementation for bulk status update
            showAlert('Bulk status update feature coming soon', 'info');
            closeBulkActions();
        }
        
        function bulkExport() {
            // Implementation for bulk export
            showAlert('Bulk export feature coming soon', 'info');
            closeBulkActions();
        }
        
        function bulkNotify() {
            // Implementation for bulk notifications
            showAlert('Bulk notification feature coming soon', 'info');
            closeBulkActions();
        }
    </script>
    
</body>
</html>
