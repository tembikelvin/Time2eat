<?php
/**
 * Performance Dashboard View
 */
$pageTitle = $data['page_title'] ?? 'Performance Dashboard';
$performanceMetrics = $data['performance_metrics'] ?? [];
$cacheStats = $data['cache_stats'] ?? [];
$imageStats = $data['image_stats'] ?? [];
$recentOptimizations = $data['recent_optimizations'] ?? [];
?>

<div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
    <!-- Header -->
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-8">
        <div>
            <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900">⚡ Performance Dashboard</h1>
            <p class="tw-text-gray-600 tw-mt-2">Monitor and optimize Time2Eat performance</p>
        </div>
        <div class="tw-flex tw-space-x-4">
            <button id="refresh-metrics" class="tw-bg-blue-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg hover:tw-bg-blue-600 tw-transition-colors">
                🔄 Refresh
            </button>
            <button id="run-optimization" class="tw-bg-green-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg hover:tw-bg-green-600 tw-transition-colors">
                🚀 Optimize Now
            </button>
        </div>
    </div>

    <!-- Performance Metrics Grid -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
        <!-- Page Load Time -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Page Load Time</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-gray-900">
                        <?= isset($performanceMetrics['page_load_time']) ? number_format($performanceMetrics['page_load_time']['avg'], 0) : '0' ?>
                        <span class="tw-text-sm tw-text-gray-500">ms</span>
                    </p>
                </div>
                <div class="tw-text-3xl">⏱️</div>
            </div>
            <div class="tw-mt-4">
                <div class="tw-flex tw-items-center">
                    <div class="tw-w-full tw-bg-gray-200 tw-rounded-full tw-h-2">
                        <?php
                        $loadTime = $performanceMetrics['page_load_time']['avg'] ?? 0;
                        $percentage = min(100, ($loadTime / 3000) * 100); // 3s target
                        $color = $loadTime < 1000 ? 'tw-bg-green-500' : ($loadTime < 2000 ? 'tw-bg-yellow-500' : 'tw-bg-red-500');
                        ?>
                        <div class="<?= $color ?> tw-h-2 tw-rounded-full" style="width: <?= $percentage ?>%"></div>
                    </div>
                </div>
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Target: &lt; 3000ms</p>
            </div>
        </div>

        <!-- Cache Hit Ratio -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Cache Hit Ratio</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-gray-900">
                        <?= $cacheStats['hit_ratio'] ?? 0 ?>%
                    </p>
                </div>
                <div class="tw-text-3xl">🎯</div>
            </div>
            <div class="tw-mt-4">
                <div class="tw-flex tw-items-center">
                    <div class="tw-w-full tw-bg-gray-200 tw-rounded-full tw-h-2">
                        <?php
                        $hitRatio = $cacheStats['hit_ratio'] ?? 0;
                        $color = $hitRatio > 80 ? 'tw-bg-green-500' : ($hitRatio > 60 ? 'tw-bg-yellow-500' : 'tw-bg-red-500');
                        ?>
                        <div class="<?= $color ?> tw-h-2 tw-rounded-full" style="width: <?= $hitRatio ?>%"></div>
                    </div>
                </div>
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Hits: <?= $cacheStats['hits'] ?? 0 ?> | Misses: <?= $cacheStats['misses'] ?? 0 ?></p>
            </div>
        </div>

        <!-- Image Optimization -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Image Optimization</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-gray-900">
                        <?= $imageStats['compression_ratio'] ?? 0 ?>%
                    </p>
                </div>
                <div class="tw-text-3xl">🖼️</div>
            </div>
            <div class="tw-mt-4">
                <p class="tw-text-xs tw-text-gray-500">
                    <?= number_format(($imageStats['space_saved'] ?? 0) / 1024 / 1024, 1) ?>MB saved
                </p>
                <p class="tw-text-xs tw-text-gray-500">
                    <?= $imageStats['optimized_count'] ?? 0 ?> / <?= $imageStats['original_count'] ?? 0 ?> images
                </p>
            </div>
        </div>

        <!-- Memory Usage -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-600">Memory Usage</p>
                    <p class="tw-text-2xl tw-font-bold tw-text-gray-900">
                        <?= isset($performanceMetrics['memory_usage']) ? number_format($performanceMetrics['memory_usage']['avg'], 1) : '0' ?>
                        <span class="tw-text-sm tw-text-gray-500">MB</span>
                    </p>
                </div>
                <div class="tw-text-3xl">🧠</div>
            </div>
            <div class="tw-mt-4">
                <div class="tw-flex tw-items-center">
                    <div class="tw-w-full tw-bg-gray-200 tw-rounded-full tw-h-2">
                        <?php
                        $memoryUsage = $performanceMetrics['memory_usage']['avg'] ?? 0;
                        $percentage = min(100, ($memoryUsage / 128) * 100); // 128MB limit
                        $color = $memoryUsage < 64 ? 'tw-bg-green-500' : ($memoryUsage < 96 ? 'tw-bg-yellow-500' : 'tw-bg-red-500');
                        ?>
                        <div class="<?= $color ?> tw-h-2 tw-rounded-full" style="width: <?= $percentage ?>%"></div>
                    </div>
                </div>
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Limit: 128MB</p>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
        <!-- Load Time Chart -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">📈 Load Time Trends</h3>
            <div id="load-time-chart" class="tw-h-64"></div>
        </div>

        <!-- Cache Performance Chart -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">🎯 Cache Performance</h3>
            <div id="cache-chart" class="tw-h-64"></div>
        </div>
    </div>

    <!-- Optimization Tools -->
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8 tw-mb-8">
        <!-- Image Optimization -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">🖼️ Image Optimization</h3>
            <div class="tw-space-y-4">
                <button id="optimize-images" class="tw-w-full tw-bg-blue-500 tw-text-white tw-py-2 tw-px-4 tw-rounded-lg hover:tw-bg-blue-600 tw-transition-colors">
                    Optimize All Images
                </button>
                <button id="generate-webp" class="tw-w-full tw-bg-green-500 tw-text-white tw-py-2 tw-px-4 tw-rounded-lg hover:tw-bg-green-600 tw-transition-colors">
                    Generate WebP Versions
                </button>
                <div class="tw-text-sm tw-text-gray-600">
                    <p>Original: <?= number_format(($imageStats['original_size'] ?? 0) / 1024 / 1024, 1) ?>MB</p>
                    <p>Optimized: <?= number_format(($imageStats['optimized_size'] ?? 0) / 1024 / 1024, 1) ?>MB</p>
                </div>
            </div>
        </div>

        <!-- Cache Management -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">🗄️ Cache Management</h3>
            <div class="tw-space-y-4">
                <button id="clear-all-cache" class="tw-w-full tw-bg-red-500 tw-text-white tw-py-2 tw-px-4 tw-rounded-lg hover:tw-bg-red-600 tw-transition-colors">
                    Clear All Cache
                </button>
                <button id="clear-expired-cache" class="tw-w-full tw-bg-yellow-500 tw-text-white tw-py-2 tw-px-4 tw-rounded-lg hover:tw-bg-yellow-600 tw-transition-colors">
                    Clear Expired Cache
                </button>
                <div class="tw-text-sm tw-text-gray-600">
                    <p>Memory Items: <?= $cacheStats['memory_items'] ?? 0 ?></p>
                    <p>File Size: <?= number_format(($cacheStats['file_cache_size'] ?? 0) / 1024 / 1024, 1) ?>MB</p>
                    <p>DB Entries: <?= $cacheStats['database_cache_count'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <!-- Performance Tests -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">🧪 Performance Tests</h3>
            <div class="tw-space-y-4">
                <button id="run-performance-test" class="tw-w-full tw-bg-purple-500 tw-text-white tw-py-2 tw-px-4 tw-rounded-lg hover:tw-bg-purple-600 tw-transition-colors">
                    Run Performance Test
                </button>
                <button id="test-load-time" class="tw-w-full tw-bg-indigo-500 tw-text-white tw-py-2 tw-px-4 tw-rounded-lg hover:tw-bg-indigo-600 tw-transition-colors">
                    Test Load Time
                </button>
                <div id="test-results" class="tw-text-sm tw-text-gray-600">
                    <p>Click "Run Performance Test" to start</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Optimizations -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">📋 Recent Optimizations</h3>
        <div class="tw-overflow-x-auto">
            <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                <thead class="tw-bg-gray-50">
                    <tr>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Type</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Description</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Impact</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                    <?php if (empty($recentOptimizations)): ?>
                        <tr>
                            <td colspan="4" class="tw-px-6 tw-py-4 tw-text-center tw-text-gray-500">
                                No recent optimizations found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOptimizations as $optimization): ?>
                            <tr>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">
                                    <?= htmlspecialchars($optimization['type'] ?? 'Unknown') ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                    <?= htmlspecialchars($optimization['description'] ?? 'No description') ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                    <?= htmlspecialchars($optimization['impact'] ?? 'Unknown') ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                    <?= date('M j, Y H:i', strtotime($optimization['created_at'] ?? 'now')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loading-modal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-hidden tw-z-50">
    <div class="tw-bg-white tw-rounded-lg tw-p-8 tw-max-w-sm tw-w-full tw-mx-4">
        <div class="tw-text-center">
            <div class="tw-animate-spin tw-rounded-full tw-h-12 tw-w-12 tw-border-b-2 tw-border-blue-500 tw-mx-auto tw-mb-4"></div>
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Optimizing Performance</h3>
            <p class="tw-text-gray-600" id="loading-message">Please wait while we optimize your application...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Performance Dashboard JavaScript
    const loadingModal = document.getElementById('loading-modal');
    const loadingMessage = document.getElementById('loading-message');

    function showLoading(message = 'Processing...') {
        loadingMessage.textContent = message;
        loadingModal.classList.remove('tw-hidden');
    }

    function hideLoading() {
        loadingModal.classList.add('tw-hidden');
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-lg tw-shadow-lg tw-z-50 ${
            type === 'success' ? 'tw-bg-green-500' : 'tw-bg-red-500'
        } tw-text-white`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Refresh metrics
    document.getElementById('refresh-metrics').addEventListener('click', async function() {
        showLoading('Refreshing metrics...');
        
        try {
            const response = await fetch('<?= url('/admin/performance/metrics') ?>');
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                showNotification('Failed to refresh metrics', 'error');
            }
        } catch (error) {
            showNotification('Error refreshing metrics', 'error');
        } finally {
            hideLoading();
        }
    });

    // Run optimization
    document.getElementById('run-optimization').addEventListener('click', async function() {
        showLoading('Running comprehensive optimization...');
        
        try {
            const response = await fetch('<?= url('/admin/performance/optimize') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('Optimization completed successfully!');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification(data.message || 'Optimization failed', 'error');
            }
        } catch (error) {
            showNotification('Error running optimization', 'error');
        } finally {
            hideLoading();
        }
    });

    // Optimize images
    document.getElementById('optimize-images').addEventListener('click', async function() {
        showLoading('Optimizing images...');
        
        try {
            const response = await fetch('<?= url('/admin/performance/optimize-images') ?>', {
                method: 'POST'
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(`Optimized ${data.results.optimized} images!`);
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification(data.message || 'Image optimization failed', 'error');
            }
        } catch (error) {
            showNotification('Error optimizing images', 'error');
        } finally {
            hideLoading();
        }
    });

    // Clear cache buttons
    document.getElementById('clear-all-cache').addEventListener('click', async function() {
        if (!confirm('Are you sure you want to clear all cache? This may temporarily slow down the application.')) {
            return;
        }
        
        showLoading('Clearing all cache...');
        
        try {
            const response = await fetch('<?= url('/admin/performance/clear-cache') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'cache_type=all'
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('All cache cleared successfully!');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification(data.message || 'Failed to clear cache', 'error');
            }
        } catch (error) {
            showNotification('Error clearing cache', 'error');
        } finally {
            hideLoading();
        }
    });

    document.getElementById('clear-expired-cache').addEventListener('click', async function() {
        showLoading('Clearing expired cache...');
        
        try {
            const response = await fetch('<?= url('/admin/performance/clear-cache') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'cache_type=expired'
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('Expired cache cleared successfully!');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification(data.message || 'Failed to clear expired cache', 'error');
            }
        } catch (error) {
            showNotification('Error clearing expired cache', 'error');
        } finally {
            hideLoading();
        }
    });

    // Run performance test
    document.getElementById('run-performance-test').addEventListener('click', async function() {
        showLoading('Running performance tests...');
        
        try {
            const response = await fetch('<?= url('/admin/performance/test') ?>');
            const data = await response.json();
            
            if (data.success) {
                const resultsDiv = document.getElementById('test-results');
                resultsDiv.innerHTML = `
                    <p><strong>Total Time:</strong> ${data.total_time}ms</p>
                    <p><strong>Database:</strong> ${data.tests.database.time}ms</p>
                    <p><strong>Cache:</strong> ${data.tests.cache.time}ms</p>
                    <p><strong>Memory:</strong> ${data.tests.memory.current}MB</p>
                `;
                showNotification('Performance test completed!');
            } else {
                showNotification('Performance test failed', 'error');
            }
        } catch (error) {
            showNotification('Error running performance test', 'error');
        } finally {
            hideLoading();
        }
    });

    // Auto-refresh metrics every 30 seconds
    setInterval(async function() {
        try {
            const response = await fetch('<?= url('/admin/performance/metrics') ?>');
            const data = await response.json();
            
            if (data.success) {
                // Update metrics without full page reload
                // This would require more complex DOM manipulation
                // For now, we'll just update every 5 minutes with full reload
            }
        } catch (error) {
            // Silent fail for auto-refresh
        }
    }, 30000);
});
</script>
