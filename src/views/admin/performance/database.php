<?php
/**
 * Database Performance Optimization View
 */
?>

<div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
    <div class="tw-max-w-6xl tw-mx-auto">
        <!-- Header -->
        <div class="tw-mb-8">
            <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-mb-2">Database Performance Optimization</h1>
            <p class="tw-text-gray-600">Monitor and optimize database performance for better application speed</p>
        </div>

        <!-- Performance Status Cards -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
            <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Database Size</p>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900" id="database-size">Loading...</p>
                    </div>
                    <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                        <i data-feather="database" class="tw-w-6 tw-h-6 tw-text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Indexes</p>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900" id="index-count">Loading...</p>
                    </div>
                    <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                        <i data-feather="zap" class="tw-w-6 tw-h-6 tw-text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Query Cache Hit Rate</p>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900" id="cache-hit-rate">Loading...</p>
                    </div>
                    <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                        <i data-feather="trending-up" class="tw-w-6 tw-h-6 tw-text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Optimization Status</p>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900" id="optimization-status">Ready</p>
                    </div>
                    <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                        <i data-feather="settings" class="tw-w-6 tw-h-6 tw-text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6 tw-mb-8">
            <h2 class="tw-text-xl tw-font-semibold tw-text-gray-900 tw-mb-4">Database Optimization Actions</h2>
            
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4">
                <button onclick="runDatabaseOptimization()" 
                        class="tw-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                    <i data-feather="zap" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Optimize Database
                </button>

                <button onclick="runMigration()" 
                        class="tw-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-bg-green-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-green-700 tw-transition-colors">
                    <i data-feather="database" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Run Migration
                </button>

                <button onclick="refreshMetrics()" 
                        class="tw-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-bg-gray-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-gray-700 tw-transition-colors">
                    <i data-feather="refresh-cw" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Refresh Metrics
                </button>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
            <!-- Table Sizes -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Largest Tables</h3>
                <div class="tw-overflow-x-auto">
                    <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                        <thead class="tw-bg-gray-50">
                            <tr>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Table</th>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Size (MB)</th>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Rows</th>
                            </tr>
                        </thead>
                        <tbody id="table-sizes" class="tw-bg-white tw-divide-y tw-divide-gray-200">
                            <tr>
                                <td colspan="3" class="tw-px-4 tw-py-4 tw-text-center tw-text-gray-500">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Index Usage -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Index Performance</h3>
                <div class="tw-overflow-x-auto">
                    <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                        <thead class="tw-bg-gray-50">
                            <tr>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Table</th>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Index</th>
                                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Quality</th>
                            </tr>
                        </thead>
                        <tbody id="index-usage" class="tw-bg-white tw-divide-y tw-divide-gray-200">
                            <tr>
                                <td colspan="3" class="tw-px-4 tw-py-4 tw-text-center tw-text-gray-500">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- N+1 Query Problems -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6 tw-mb-8">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">N+1 Query Problems & Solutions</h3>
            <div id="n-plus-one-problems" class="tw-space-y-4">
                <div class="tw-text-center tw-text-gray-500 tw-py-8">Loading optimization analysis...</div>
            </div>
        </div>

        <!-- Optimization Log -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Optimization Log</h3>
            <div id="optimization-log" class="tw-bg-gray-50 tw-rounded tw-p-4 tw-h-64 tw-overflow-y-auto tw-font-mono tw-text-sm">
                <div class="tw-text-gray-500">Ready to run optimizations...</div>
            </div>
        </div>
    </div>
</div>

<script>
// Database Performance Optimization JavaScript
let optimizationInProgress = false;

// Load initial metrics
document.addEventListener('DOMContentLoaded', function() {
    refreshMetrics();
});

// Refresh performance metrics
async function refreshMetrics() {
    try {
        const response = await fetch('/admin/performance/database/metrics');
        const data = await response.json();
        
        if (data.success) {
            updateMetricsDisplay(data.metrics);
        } else {
            logMessage('Error loading metrics: ' + data.message, 'error');
        }
    } catch (error) {
        logMessage('Failed to load metrics: ' + error.message, 'error');
    }
}

// Update metrics display
function updateMetricsDisplay(metrics) {
    // Update summary cards
    document.getElementById('database-size').textContent = metrics.total_database_size || 'Unknown';
    document.getElementById('index-count').textContent = metrics.index_usage ? metrics.index_usage.length : '0';
    
    // Update table sizes
    const tableSizesBody = document.getElementById('table-sizes');
    if (metrics.table_sizes && metrics.table_sizes.length > 0) {
        tableSizesBody.innerHTML = metrics.table_sizes.slice(0, 10).map(table => `
            <tr>
                <td class="tw-px-4 tw-py-4 tw-text-sm tw-font-medium tw-text-gray-900">${table.table_name}</td>
                <td class="tw-px-4 tw-py-4 tw-text-sm tw-text-gray-500">${table.size_mb}</td>
                <td class="tw-px-4 tw-py-4 tw-text-sm tw-text-gray-500">${parseInt(table.table_rows).toLocaleString()}</td>
            </tr>
        `).join('');
    } else {
        tableSizesBody.innerHTML = '<tr><td colspan="3" class="tw-px-4 tw-py-4 tw-text-center tw-text-gray-500">No data available</td></tr>';
    }
    
    // Update index usage
    const indexUsageBody = document.getElementById('index-usage');
    if (metrics.index_usage && metrics.index_usage.length > 0) {
        indexUsageBody.innerHTML = metrics.index_usage.slice(0, 10).map(index => `
            <tr>
                <td class="tw-px-4 tw-py-4 tw-text-sm tw-font-medium tw-text-gray-900">${index.table_name}</td>
                <td class="tw-px-4 tw-py-4 tw-text-sm tw-text-gray-500">${index.index_name}</td>
                <td class="tw-px-4 tw-py-4 tw-text-sm">
                    <span class="tw-inline-flex tw-px-2 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full ${getIndexQualityClass(index.index_quality)}">
                        ${index.index_quality}
                    </span>
                </td>
            </tr>
        `).join('');
    } else {
        indexUsageBody.innerHTML = '<tr><td colspan="3" class="tw-px-4 tw-py-4 tw-text-center tw-text-gray-500">No data available</td></tr>';
    }
}

// Get CSS class for index quality
function getIndexQualityClass(quality) {
    switch (quality) {
        case 'Good': return 'tw-bg-green-100 tw-text-green-800';
        case 'Low selectivity': return 'tw-bg-yellow-100 tw-text-yellow-800';
        case 'Unused': return 'tw-bg-red-100 tw-text-red-800';
        default: return 'tw-bg-gray-100 tw-text-gray-800';
    }
}

// Run database optimization
async function runDatabaseOptimization() {
    if (optimizationInProgress) return;
    
    optimizationInProgress = true;
    document.getElementById('optimization-status').textContent = 'Running...';
    logMessage('Starting database optimization...', 'info');
    
    try {
        const response = await fetch('/admin/performance/database/optimize', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            logMessage('Database optimization completed successfully!', 'success');
            logMessage(`Execution time: ${data.results.execution_time}ms`, 'info');
            
            // Log detailed results
            if (data.results.optimizations) {
                const opts = data.results.optimizations;
                if (opts.indexes) {
                    logMessage(`Indexes: ${opts.indexes.added} added, ${opts.indexes.skipped} skipped`, 'info');
                }
                if (opts.tables) {
                    logMessage(`Tables optimized: ${opts.tables.optimized}`, 'info');
                }
                if (opts.statistics) {
                    logMessage(`Statistics updated: ${opts.statistics.updated} tables`, 'info');
                }
            }
            
            // Show N+1 problems
            if (data.results.optimizations && data.results.optimizations.n_plus_one) {
                displayN1Problems(data.results.optimizations.n_plus_one);
            }
            
            // Refresh metrics
            setTimeout(refreshMetrics, 1000);
            
        } else {
            logMessage('Optimization failed: ' + data.message, 'error');
        }
        
    } catch (error) {
        logMessage('Optimization error: ' + error.message, 'error');
    } finally {
        optimizationInProgress = false;
        document.getElementById('optimization-status').textContent = 'Ready';
    }
}

// Run database migration
async function runMigration() {
    if (optimizationInProgress) return;
    
    optimizationInProgress = true;
    logMessage('Running database migration...', 'info');
    
    try {
        const response = await fetch('/admin/performance/database/migrate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            logMessage('Migration completed successfully!', 'success');
            logMessage(`Statements executed: ${data.results.statements_executed}/${data.results.total_statements}`, 'info');
            
            if (data.results.errors && data.results.errors.length > 0) {
                logMessage(`Errors: ${data.results.errors.length}`, 'warning');
                data.results.errors.forEach(error => logMessage(`  - ${error}`, 'warning'));
            }
            
            // Refresh metrics
            setTimeout(refreshMetrics, 1000);
            
        } else {
            logMessage('Migration failed: ' + data.message, 'error');
        }
        
    } catch (error) {
        logMessage('Migration error: ' + error.message, 'error');
    } finally {
        optimizationInProgress = false;
    }
}

// Display N+1 problems
function displayN1Problems(n1Data) {
    const container = document.getElementById('n-plus-one-problems');
    
    if (n1Data.identified_problems && n1Data.identified_problems.length > 0) {
        container.innerHTML = n1Data.identified_problems.map(problem => `
            <div class="tw-border tw-border-yellow-200 tw-rounded-lg tw-p-4 tw-bg-yellow-50">
                <h4 class="tw-font-semibold tw-text-yellow-800 tw-mb-2">${problem.location}</h4>
                <p class="tw-text-yellow-700 tw-mb-2">${problem.problem}</p>
                <p class="tw-text-sm tw-text-yellow-600"><strong>Solution:</strong> ${problem.solution}</p>
                ${problem.optimized_query ? `<pre class="tw-mt-2 tw-text-xs tw-bg-yellow-100 tw-p-2 tw-rounded tw-overflow-x-auto"><code>${problem.optimized_query}</code></pre>` : ''}
            </div>
        `).join('');
        
        if (n1Data.recommendations) {
            container.innerHTML += `
                <div class="tw-border tw-border-blue-200 tw-rounded-lg tw-p-4 tw-bg-blue-50">
                    <h4 class="tw-font-semibold tw-text-blue-800 tw-mb-2">Recommendations</h4>
                    <ul class="tw-list-disc tw-list-inside tw-text-blue-700 tw-space-y-1">
                        ${n1Data.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                    </ul>
                </div>
            `;
        }
    } else {
        container.innerHTML = '<div class="tw-text-center tw-text-gray-500 tw-py-4">No N+1 query problems detected</div>';
    }
}

// Log message to optimization log
function logMessage(message, type = 'info') {
    const log = document.getElementById('optimization-log');
    const timestamp = new Date().toLocaleTimeString();
    const typeIcon = {
        'info': 'ℹ️',
        'success': '✅',
        'warning': '⚠️',
        'error': '❌'
    };
    
    const logEntry = document.createElement('div');
    logEntry.className = `tw-mb-1 tw-text-${type === 'error' ? 'red' : type === 'success' ? 'green' : type === 'warning' ? 'yellow' : 'gray'}-600`;
    logEntry.textContent = `[${timestamp}] ${typeIcon[type] || 'ℹ️'} ${message}`;
    
    log.appendChild(logEntry);
    log.scrollTop = log.scrollHeight;
}

// Initialize Feather icons
feather.replace();
</script>
