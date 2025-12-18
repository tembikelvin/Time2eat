<?php
/**
 * Time2Eat Performance Controller
 * Handles performance optimization and monitoring
 */

declare(strict_types=1);

require_once __DIR__ . '/../services/PerformanceOptimizer.php';
require_once __DIR__ . '/../services/ImageOptimizer.php';
require_once __DIR__ . '/../services/CacheManager.php';
require_once __DIR__ . '/../services/DatabaseOptimizer.php';

class PerformanceController extends BaseController
{
    private PerformanceOptimizer $optimizer;
    private ImageOptimizer $imageOptimizer;
    private CacheManager $cacheManager;
    private DatabaseOptimizer $databaseOptimizer;
    
    public function __construct()
    {
        parent::__construct();
        $this->optimizer = new PerformanceOptimizer();
        $this->imageOptimizer = new ImageOptimizer();
        $this->cacheManager = new CacheManager();
        $this->databaseOptimizer = new DatabaseOptimizer();
    }
    
    /**
     * Performance dashboard
     */
    public function dashboard(): void
    {
        $this->requireAuth(['admin']);
        
        $data = [
            'page_title' => 'Performance Dashboard',
            'performance_metrics' => $this->getPerformanceMetrics(),
            'cache_stats' => $this->cacheManager->getStats(),
            'image_stats' => $this->imageOptimizer->getOptimizationStats(),
            'database_metrics' => $this->databaseOptimizer->getPerformanceMetrics(),
            'recent_optimizations' => $this->getRecentOptimizations()
        ];
        
        $this->render('admin/performance/dashboard', $data);
    }
    
    /**
     * Run performance optimization
     */
    public function optimize(): void
    {
        $this->requireAuth(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $results = $this->optimizer->optimize();
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Performance optimization completed successfully',
                    'results' => $results
                ]);
                
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Optimization failed: ' . $e->getMessage()
                ], 500);
            }
        } else {
            $this->redirect('/admin/performance');
        }
    }
    
    /**
     * Optimize images
     */
    public function optimizeImages(): void
    {
        $this->requireAuth(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $directory = $_POST['directory'] ?? null;
                $results = $this->imageOptimizer->batchOptimize($directory);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Image optimization completed',
                    'results' => $results
                ]);
                
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Image optimization failed: ' . $e->getMessage()
                ], 500);
            }
        } else {
            $this->redirect('/admin/performance');
        }
    }
    
    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        $this->requireAuth(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $cacheType = $_POST['cache_type'] ?? 'all';
                
                switch ($cacheType) {
                    case 'all':
                        $result = $this->cacheManager->clear();
                        break;
                    case 'expired':
                        $result = $this->cacheManager->cleanExpired();
                        break;
                    default:
                        throw new Exception('Invalid cache type');
                }
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Cache cleared successfully',
                    'result' => $result
                ]);
                
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Cache clear failed: ' . $e->getMessage()
                ], 500);
            }
        } else {
            $this->redirect('/admin/performance');
        }
    }
    
    /**
     * Get performance metrics API
     */
    public function metrics(): void
    {
        $this->requireAuth(['admin']);
        
        try {
            $metrics = $this->getPerformanceMetrics();
            
            $this->jsonResponse([
                'success' => true,
                'metrics' => $metrics
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch metrics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Serve optimized image
     */
    public function serveImage(): void
    {
        $path = $_GET['path'] ?? '';
        $format = $_GET['format'] ?? 'webp';
        $size = $_GET['size'] ?? 'medium';
        
        if (empty($path)) {
            http_response_code(400);
            die('Path parameter required');
        }
        
        // Security check - prevent directory traversal
        $path = str_replace(['../', '..\\'], '', $path);
        $imagePath = ROOT_PATH . '/public/images/' . $path;
        
        if (!file_exists($imagePath)) {
            http_response_code(404);
            die('Image not found');
        }
        
        // Check if browser supports WebP
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $supportsWebP = strpos($acceptHeader, 'image/webp') !== false;
        
        if ($format === 'webp' && !$supportsWebP) {
            $format = 'original';
        }
        
        $optimizedUrl = $this->imageOptimizer->getOptimizedUrl($imagePath, $format, $size);
        $optimizedPath = ROOT_PATH . '/public' . $optimizedUrl;
        
        if (!file_exists($optimizedPath)) {
            // Create optimized version on-the-fly
            $this->imageOptimizer->optimizeUpload($imagePath, $imagePath, [
                'formats' => [$format],
                'sizes' => [$size]
            ]);
        }
        
        if (file_exists($optimizedPath)) {
            $this->serveImageFile($optimizedPath);
        } else {
            $this->serveImageFile($imagePath);
        }
    }
    
    /**
     * Performance test endpoint
     */
    public function test(): void
    {
        $startTime = microtime(true);
        
        // Simulate various operations
        $tests = [
            'database' => $this->testDatabasePerformance(),
            'cache' => $this->testCachePerformance(),
            'image' => $this->testImagePerformance(),
            'memory' => $this->testMemoryUsage()
        ];
        
        $totalTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->jsonResponse([
            'success' => true,
            'total_time' => $totalTime,
            'tests' => $tests,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        try {
            $stmt = $this->getDb()->prepare("
                SELECT 
                    metric_name,
                    AVG(metric_value) as avg_value,
                    MIN(metric_value) as min_value,
                    MAX(metric_value) as max_value,
                    COUNT(*) as count,
                    metric_unit
                FROM performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY metric_name, metric_unit
                ORDER BY metric_name
            ");
            $stmt->execute();
            
            $metrics = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $metrics[$row['metric_name']] = [
                    'avg' => round($row['avg_value'], 2),
                    'min' => round($row['min_value'], 2),
                    'max' => round($row['max_value'], 2),
                    'count' => (int)$row['count'],
                    'unit' => $row['metric_unit']
                ];
            }
            
            return $metrics;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get recent optimizations
     */
    private function getRecentOptimizations(): array
    {
        try {
            $stmt = $this->getDb()->prepare("
                SELECT * FROM optimization_logs 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Serve image file with proper headers
     */
    private function serveImageFile(string $imagePath): void
    {
        $mimeType = mime_content_type($imagePath);
        $lastModified = filemtime($imagePath);
        $etag = md5_file($imagePath);
        
        // Set caching headers
        header("Content-Type: {$mimeType}");
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        header("ETag: \"{$etag}\"");
        
        // Check if client has cached version
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        
        if (($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified) ||
            ($ifNoneMatch && $ifNoneMatch === "\"{$etag}\"")) {
            http_response_code(304);
            exit;
        }
        
        readfile($imagePath);
        exit;
    }
    
    /**
     * Test database performance
     */
    private function testDatabasePerformance(): array
    {
        $startTime = microtime(true);
        
        try {
            // Simple query test
            $this->getDb()->query("SELECT COUNT(*) FROM users");
            
            $time = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'status' => 'pass',
                'time' => $time,
                'unit' => 'ms'
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'fail',
                'error' => $e->getMessage(),
                'time' => 0
            ];
        }
    }
    
    /**
     * Test cache performance
     */
    private function testCachePerformance(): array
    {
        $startTime = microtime(true);
        
        try {
            $testKey = 'performance_test_' . time();
            $testValue = 'test_data_' . rand(1000, 9999);
            
            // Test cache set
            $this->cacheManager->set($testKey, $testValue, 60);
            
            // Test cache get
            $retrieved = $this->cacheManager->get($testKey);
            
            // Clean up
            $this->cacheManager->delete($testKey);
            
            $time = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'status' => $retrieved === $testValue ? 'pass' : 'fail',
                'time' => $time,
                'unit' => 'ms'
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'fail',
                'error' => $e->getMessage(),
                'time' => 0
            ];
        }
    }
    
    /**
     * Test image processing performance
     */
    private function testImagePerformance(): array
    {
        $startTime = microtime(true);
        
        try {
            // Create a test image
            $testImage = imagecreatetruecolor(100, 100);
            $color = imagecolorallocate($testImage, 255, 0, 0);
            imagefill($testImage, 0, 0, $color);
            
            // Test image processing
            imagedestroy($testImage);
            
            $time = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'status' => 'pass',
                'time' => $time,
                'unit' => 'ms'
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'fail',
                'error' => $e->getMessage(),
                'time' => 0
            ];
        }
    }
    
    /**
     * Test memory usage
     */
    private function testMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);

        return [
            'status' => 'pass',
            'current' => round($memoryUsage / 1024 / 1024, 2),
            'peak' => round($peakMemory / 1024 / 1024, 2),
            'unit' => 'MB'
        ];
    }

    /**
     * Record performance metrics from client
     */
    public function recordMetrics(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Method not allowed');
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !isset($input['metrics'])) {
                throw new Exception('Invalid metrics data');
            }

            $metrics = $input['metrics'];
            $url = $input['url'] ?? '';
            $userAgent = $input['user_agent'] ?? '';

            // Store metrics in database
            foreach ($metrics as $name => $data) {
                $stmt = $this->getDb()->prepare("
                    INSERT INTO performance_metrics
                    (metric_name, metric_value, metric_unit, url, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $name,
                    $data['value'],
                    $data['unit'],
                    $url,
                    $userAgent
                ]);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Metrics recorded successfully'
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to record metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance status
     */
    public function getStatus(): void
    {
        try {
            $status = [
                'cache' => $this->cacheManager->getStats(),
                'database' => $this->testDatabasePerformance(),
                'memory' => $this->testMemoryUsage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];

            $this->jsonResponse([
                'success' => true,
                'status' => $status
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize database performance
     */
    public function optimizeDatabase(): void
    {
        $this->requireAuth(['admin']);

        try {
            $results = $this->databaseOptimizer->optimize();

            // Log optimization results
            $this->logOptimization('database', $results);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Database optimization completed',
                'results' => $results
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Database optimization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run database performance migration
     */
    public function runDatabaseMigration(): void
    {
        $this->requireAuth(['admin']);

        try {
            // Read and execute the migration file
            $migrationFile = __DIR__ . '/../../database/migrations/019_optimize_database_performance.sql';

            if (!file_exists($migrationFile)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Migration file not found'
                ], 404);
                return;
            }

            $sql = file_get_contents($migrationFile);

            // Split SQL into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
                    return !empty($stmt) && !str_starts_with($stmt, '--') && !str_starts_with($stmt, '/*');
                }
            );

            $executed = 0;
            $errors = [];

            foreach ($statements as $statement) {
                try {
                    $this->getDb()->exec($statement);
                    $executed++;
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Database migration completed',
                'results' => [
                    'statements_executed' => $executed,
                    'errors' => $errors,
                    'total_statements' => count($statements)
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get database performance metrics API
     */
    public function getDatabaseMetrics(): void
    {
        $this->requireAuth(['admin']);

        try {
            $metrics = $this->databaseOptimizer->getPerformanceMetrics();

            $this->jsonResponse([
                'success' => true,
                'metrics' => $metrics
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get database metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log optimization results
     */
    private function logOptimization(string $type, array $results): void
    {
        try {
            $logData = [
                'optimization_type' => $type,
                'results' => $results,
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $this->getCurrentUser()['id'] ?? null
            ];

            // Insert into logs table if it exists
            $this->getDb()->prepare("
                INSERT INTO logs (level, message, context, user_id, created_at)
                VALUES (?, ?, ?, ?, ?)
            ")->execute([
                'info',
                ucfirst($type) . ' optimization completed',
                json_encode($logData),
                $this->getCurrentUser()['id'] ?? null,
                date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            error_log("Failed to log optimization: " . $e->getMessage());
        }
    }
}
