<?php
/**
 * Time2Eat Performance Optimizer
 * Comprehensive performance optimization service
 */

declare(strict_types=1);

class PerformanceOptimizer
{
    private Database $db;
    private array $config;
    private string $cacheDir;
    private string $imageDir;
    private array $metrics = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->cacheDir = ROOT_PATH . '/storage/cache';
        $this->imageDir = ROOT_PATH . '/public/images';
        $this->config = [
            'image_quality' => [
                'jpeg' => 85,
                'webp' => 80,
                'png' => 6
            ],
            'cache_duration' => [
                'static' => 31536000, // 1 year
                'dynamic' => 3600,    // 1 hour
                'api' => 300          // 5 minutes
            ],
            'compression' => [
                'gzip_level' => 6,
                'brotli_level' => 4
            ]
        ];
        
        $this->ensureDirectories();
    }
    
    /**
     * Run comprehensive performance optimization
     */
    public function optimize(): array
    {
        $startTime = microtime(true);
        $results = [];
        
        try {
            // Image optimization
            $results['images'] = $this->optimizeImages();
            
            // Database optimization
            $results['database'] = $this->optimizeDatabase();
            
            // Cache optimization
            $results['cache'] = $this->optimizeCache();
            
            // Asset optimization
            $results['assets'] = $this->optimizeAssets();
            
            // Browser caching
            $results['browser_cache'] = $this->optimizeBrowserCaching();
            
            // Performance monitoring
            $results['monitoring'] = $this->setupPerformanceMonitoring();
            
            $results['total_time'] = round((microtime(true) - $startTime) * 1000, 2);
            $results['status'] = 'success';
            
        } catch (Exception $e) {
            $results['status'] = 'error';
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Optimize images with compression and WebP conversion
     */
    public function optimizeImages(): array
    {
        $results = [
            'processed' => 0,
            'compressed' => 0,
            'webp_created' => 0,
            'space_saved' => 0,
            'errors' => []
        ];
        
        if (!extension_loaded('gd')) {
            $results['errors'][] = 'GD extension not available';
            return $results;
        }
        
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $images = [];
        
        foreach ($imageExtensions as $ext) {
            $images = array_merge($images, glob($this->imageDir . "/**/*.{$ext}", GLOB_BRACE));
            $images = array_merge($images, glob($this->imageDir . "/*.{$ext}", GLOB_BRACE));
        }
        
        foreach ($images as $imagePath) {
            try {
                $originalSize = filesize($imagePath);
                $optimized = $this->processImage($imagePath);
                
                if ($optimized) {
                    $newSize = filesize($imagePath);
                    $results['space_saved'] += ($originalSize - $newSize);
                    $results['compressed']++;
                    
                    // Create WebP version
                    if ($this->createWebPVersion($imagePath)) {
                        $results['webp_created']++;
                    }
                }
                
                $results['processed']++;
                
            } catch (Exception $e) {
                $results['errors'][] = "Failed to process {$imagePath}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Process individual image
     */
    private function processImage(string $imagePath): bool
    {
        $info = pathinfo($imagePath);
        $extension = strtolower($info['extension']);
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return $this->optimizeJPEG($imagePath);
            case 'png':
                return $this->optimizePNG($imagePath);
            case 'gif':
                return $this->optimizeGIF($imagePath);
            default:
                return false;
        }
    }
    
    /**
     * Optimize JPEG image
     */
    private function optimizeJPEG(string $imagePath): bool
    {
        $image = imagecreatefromjpeg($imagePath);
        if (!$image) return false;
        
        // Apply optimization
        $optimized = imagejpeg($image, $imagePath, $this->config['image_quality']['jpeg']);
        imagedestroy($image);
        
        return $optimized;
    }
    
    /**
     * Optimize PNG image
     */
    private function optimizePNG(string $imagePath): bool
    {
        $image = imagecreatefrompng($imagePath);
        if (!$image) return false;
        
        // Preserve transparency
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        $optimized = imagepng($image, $imagePath, $this->config['image_quality']['png']);
        imagedestroy($image);
        
        return $optimized;
    }
    
    /**
     * Optimize GIF image
     */
    private function optimizeGIF(string $imagePath): bool
    {
        $image = imagecreatefromgif($imagePath);
        if (!$image) return false;
        
        $optimized = imagegif($image, $imagePath);
        imagedestroy($image);
        
        return $optimized;
    }
    
    /**
     * Create WebP version of image
     */
    private function createWebPVersion(string $imagePath): bool
    {
        if (!function_exists('imagewebp')) {
            return false;
        }
        
        $info = pathinfo($imagePath);
        $webpPath = $info['dirname'] . '/' . $info['filename'] . '.webp';
        
        // Skip if WebP already exists and is newer
        if (file_exists($webpPath) && filemtime($webpPath) >= filemtime($imagePath)) {
            return true;
        }
        
        $extension = strtolower($info['extension']);
        $image = null;
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($imagePath);
                break;
        }
        
        if (!$image) return false;
        
        $success = imagewebp($image, $webpPath, $this->config['image_quality']['webp']);
        imagedestroy($image);
        
        return $success;
    }
    
    /**
     * Optimize database queries and structure
     */
    public function optimizeDatabase(): array
    {
        $results = [
            'tables_optimized' => 0,
            'indexes_created' => 0,
            'queries_cached' => 0,
            'errors' => []
        ];
        
        try {
            // Optimize all tables
            $tables = $this->db->query("SHOW TABLES");
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                $this->db->exec("OPTIMIZE TABLE `{$tableName}`");
                $results['tables_optimized']++;
            }
            
            // Create performance indexes
            $indexes = $this->getPerformanceIndexes();
            foreach ($indexes as $index) {
                try {
                    $this->db->exec($index);
                    $results['indexes_created']++;
                } catch (PDOException $e) {
                    // Index might already exist
                    if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                        $results['errors'][] = $e->getMessage();
                    }
                }
            }
            
            // Update table statistics
            $this->db->exec("ANALYZE TABLE users, restaurants, orders, menu_items, reviews");
            
            // Setup query caching
            $results['queries_cached'] = $this->setupQueryCaching();
            
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Get performance indexes to create
     */
    private function getPerformanceIndexes(): array
    {
        return [
            // Users table indexes
            "CREATE INDEX idx_users_email ON users(email)",
            "CREATE INDEX idx_users_role ON users(role)",
            "CREATE INDEX idx_users_status ON users(status)",
            "CREATE INDEX idx_users_created ON users(created_at)",
            
            // Orders table indexes
            "CREATE INDEX idx_orders_user ON orders(user_id)",
            "CREATE INDEX idx_orders_restaurant ON orders(restaurant_id)",
            "CREATE INDEX idx_orders_status ON orders(status)",
            "CREATE INDEX idx_orders_created ON orders(created_at)",
            "CREATE INDEX idx_orders_delivery ON orders(delivery_time)",
            
            // Menu items indexes
            "CREATE INDEX idx_menu_restaurant ON menu_items(restaurant_id)",
            "CREATE INDEX idx_menu_category ON menu_items(category)",
            "CREATE INDEX idx_menu_status ON menu_items(status)",
            "CREATE INDEX idx_menu_price ON menu_items(price)",
            
            // Reviews indexes
            "CREATE INDEX idx_reviews_restaurant ON reviews(restaurant_id)",
            "CREATE INDEX idx_reviews_user ON reviews(user_id)",
            "CREATE INDEX idx_reviews_rating ON reviews(rating)",
            "CREATE INDEX idx_reviews_created ON reviews(created_at)",
            
            // Restaurants indexes
            "CREATE INDEX idx_restaurants_status ON restaurants(status)",
            "CREATE INDEX idx_restaurants_rating ON restaurants(average_rating)",
            "CREATE INDEX idx_restaurants_location ON restaurants(latitude, longitude)",
            
            // Payments indexes
            "CREATE INDEX idx_payments_order ON payments(order_id)",
            "CREATE INDEX idx_payments_status ON payments(status)",
            "CREATE INDEX idx_payments_method ON payments(payment_method)",
            
            // Notifications indexes
            "CREATE INDEX idx_notifications_user ON notifications(user_id)",
            "CREATE INDEX idx_notifications_read ON notifications(is_read)",
            "CREATE INDEX idx_notifications_created ON notifications(created_at)",
            
            // Affiliate indexes
            "CREATE INDEX idx_affiliate_user ON affiliate_earnings(user_id)",
            "CREATE INDEX idx_affiliate_order ON affiliate_earnings(order_id)",
            "CREATE INDEX idx_affiliate_status ON affiliate_earnings(status)"
        ];
    }
    
    /**
     * Setup query caching system
     */
    private function setupQueryCaching(): int
    {
        $cachedQueries = 0;
        
        // Common queries to cache
        $queries = [
            'popular_restaurants' => "SELECT * FROM restaurants WHERE status = 'active' ORDER BY average_rating DESC LIMIT 10",
            'popular_dishes' => "SELECT mi.*, r.name as restaurant_name FROM menu_items mi JOIN restaurants r ON mi.restaurant_id = r.id WHERE mi.status = 'active' ORDER BY mi.orders_count DESC LIMIT 20",
            'recent_reviews' => "SELECT r.*, u.name as user_name, res.name as restaurant_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN restaurants res ON r.restaurant_id = res.id ORDER BY r.created_at DESC LIMIT 10"
        ];
        
        foreach ($queries as $key => $query) {
            try {
                $result = $this->db->query($query);
                $this->cacheQuery($key, $result);
                $cachedQueries++;
            } catch (Exception $e) {
                // Skip failed queries
            }
        }
        
        return $cachedQueries;
    }
    
    /**
     * Cache query result
     */
    private function cacheQuery(string $key, array $data): void
    {
        $cacheFile = $this->cacheDir . '/queries/' . md5($key) . '.cache';
        $cacheData = [
            'data' => $data,
            'timestamp' => time(),
            'expires' => time() + $this->config['cache_duration']['dynamic']
        ];
        
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0755, true);
        }
        
        file_put_contents($cacheFile, serialize($cacheData));
    }
    
    /**
     * Optimize cache system
     */
    public function optimizeCache(): array
    {
        $results = [
            'cache_cleared' => 0,
            'cache_warmed' => 0,
            'cache_size' => 0,
            'errors' => []
        ];
        
        try {
            // Clear expired cache
            $results['cache_cleared'] = $this->clearExpiredCache();
            
            // Warm up cache with common data
            $results['cache_warmed'] = $this->warmUpCache();
            
            // Calculate cache size
            $results['cache_size'] = $this->calculateCacheSize();
            
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Clear expired cache files
     */
    private function clearExpiredCache(): int
    {
        $cleared = 0;
        $cacheFiles = glob($this->cacheDir . '/**/*.cache', GLOB_BRACE);
        
        foreach ($cacheFiles as $file) {
            try {
                $data = unserialize(file_get_contents($file));
                if (isset($data['expires']) && $data['expires'] < time()) {
                    unlink($file);
                    $cleared++;
                }
            } catch (Exception $e) {
                // Remove corrupted cache files
                unlink($file);
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Warm up cache with common data
     */
    private function warmUpCache(): int
    {
        $warmed = 0;
        
        // Cache common data
        $cacheItems = [
            'restaurants_active' => function() {
                return $this->db->query("SELECT * FROM restaurants WHERE status = 'active'");
            },
            'menu_categories' => function() {
                return $this->db->query("SELECT DISTINCT category FROM menu_items WHERE status = 'active'");
            },
            'system_settings' => function() {
                return $this->db->query("SELECT * FROM settings");
            }
        ];
        
        foreach ($cacheItems as $key => $callback) {
            try {
                $data = $callback();
                $this->cacheQuery($key, $data);
                $warmed++;
            } catch (Exception $e) {
                // Skip failed cache warming
            }
        }
        
        return $warmed;
    }
    
    /**
     * Calculate total cache size
     */
    private function calculateCacheSize(): int
    {
        $size = 0;
        $cacheFiles = glob($this->cacheDir . '/**/*', GLOB_BRACE);
        
        foreach ($cacheFiles as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }
        
        return $size;
    }
    
    /**
     * Optimize CSS and JavaScript assets
     */
    public function optimizeAssets(): array
    {
        $results = [
            'css_minified' => 0,
            'js_minified' => 0,
            'combined_files' => 0,
            'space_saved' => 0,
            'errors' => []
        ];
        
        try {
            // Minify CSS files
            $cssFiles = glob(ROOT_PATH . '/public/css/*.css');
            foreach ($cssFiles as $cssFile) {
                if (strpos($cssFile, '.min.css') === false) {
                    $originalSize = filesize($cssFile);
                    $this->minifyCSS($cssFile);
                    $newSize = filesize(str_replace('.css', '.min.css', $cssFile));
                    $results['space_saved'] += ($originalSize - $newSize);
                    $results['css_minified']++;
                }
            }
            
            // Minify JavaScript files
            $jsFiles = glob(ROOT_PATH . '/public/js/*.js');
            foreach ($jsFiles as $jsFile) {
                if (strpos($jsFile, '.min.js') === false) {
                    $originalSize = filesize($jsFile);
                    $this->minifyJS($jsFile);
                    $newSize = filesize(str_replace('.js', '.min.js', $jsFile));
                    $results['space_saved'] += ($originalSize - $newSize);
                    $results['js_minified']++;
                }
            }
            
            // Combine critical CSS
            $results['combined_files'] = $this->combineCriticalAssets();
            
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Minify CSS file
     */
    private function minifyCSS(string $cssFile): void
    {
        $content = file_get_contents($cssFile);
        
        // Remove comments
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        
        // Remove whitespace
        $content = str_replace(["\r\n", "\r", "\n", "\t"], '', $content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = str_replace(['; ', ' {', '{ ', ' }', '} ', ': ', ', '], [';', '{', '{', '}', '}', ':', ','], $content);
        
        $minFile = str_replace('.css', '.min.css', $cssFile);
        file_put_contents($minFile, trim($content));
    }
    
    /**
     * Minify JavaScript file
     */
    private function minifyJS(string $jsFile): void
    {
        $content = file_get_contents($jsFile);
        
        // Basic JS minification (for production, use a proper minifier)
        $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content); // Remove multi-line comments
        $content = preg_replace('/\/\/.*$/m', '', $content); // Remove single-line comments
        $content = preg_replace('/\s+/', ' ', $content); // Compress whitespace
        $content = str_replace(['; ', ' {', '{ ', ' }', '} '], [';', '{', '{', '}', '}'], $content);
        
        $minFile = str_replace('.js', '.min.js', $jsFile);
        file_put_contents($minFile, trim($content));
    }
    
    /**
     * Combine critical assets
     */
    private function combineCriticalAssets(): int
    {
        $combined = 0;
        
        // Combine critical CSS
        $criticalCSS = [
            ROOT_PATH . '/public/css/app.css',
            ROOT_PATH . '/public/css/components.css'
        ];
        
        $combinedContent = '';
        foreach ($criticalCSS as $file) {
            if (file_exists($file)) {
                $combinedContent .= file_get_contents($file) . "\n";
            }
        }
        
        if ($combinedContent) {
            file_put_contents(ROOT_PATH . '/public/css/critical.css', $combinedContent);
            $combined++;
        }
        
        return $combined;
    }
    
    /**
     * Optimize browser caching headers
     */
    public function optimizeBrowserCaching(): array
    {
        $results = [
            'htaccess_updated' => false,
            'headers_configured' => 0,
            'errors' => []
        ];
        
        try {
            $htaccessPath = ROOT_PATH . '/.htaccess';
            $cacheRules = $this->generateCacheRules();
            
            if (file_exists($htaccessPath)) {
                $content = file_get_contents($htaccessPath);
                
                // Remove existing cache rules
                $content = preg_replace('/# BEGIN Cache Rules.*?# END Cache Rules/s', '', $content);
                
                // Add new cache rules
                $content .= "\n" . $cacheRules;
                
                file_put_contents($htaccessPath, $content);
                $results['htaccess_updated'] = true;
                $results['headers_configured'] = substr_count($cacheRules, 'ExpiresByType');
            }
            
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Generate cache rules for .htaccess
     */
    private function generateCacheRules(): string
    {
        return "
# BEGIN Cache Rules
<IfModule mod_expires.c>
    ExpiresActive On

    # Images
    ExpiresByType image/jpg \"access plus 1 year\"
    ExpiresByType image/jpeg \"access plus 1 year\"
    ExpiresByType image/gif \"access plus 1 year\"
    ExpiresByType image/png \"access plus 1 year\"
    ExpiresByType image/webp \"access plus 1 year\"
    ExpiresByType image/svg+xml \"access plus 1 year\"
    ExpiresByType image/avif \"access plus 1 year\"

    # CSS and JavaScript
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
    ExpiresByType text/javascript \"access plus 1 month\"

    # Fonts
    ExpiresByType font/woff \"access plus 1 year\"
    ExpiresByType font/woff2 \"access plus 1 year\"
    ExpiresByType application/font-woff \"access plus 1 year\"
    ExpiresByType application/font-woff2 \"access plus 1 year\"
    ExpiresByType font/ttf \"access plus 1 year\"
    ExpiresByType font/otf \"access plus 1 year\"

    # HTML
    ExpiresByType text/html \"access plus 1 hour\"

    # JSON and XML
    ExpiresByType application/json \"access plus 1 hour\"
    ExpiresByType application/xml \"access plus 1 hour\"
    ExpiresByType text/xml \"access plus 1 hour\"

    # Manifest and Service Worker
    ExpiresByType application/manifest+json \"access plus 1 week\"
    ExpiresByType text/cache-manifest \"access plus 0 seconds\"
</IfModule>

# Cache-Control headers
<IfModule mod_headers.c>
    # Static assets - long cache
    <FilesMatch \"\.(css|js|png|jpg|jpeg|gif|webp|avif|svg|woff|woff2|ttf|otf|ico)$\">
        Header set Cache-Control \"public, max-age=31536000, immutable\"
        Header set Vary \"Accept-Encoding\"
    </FilesMatch>

    # HTML - short cache
    <FilesMatch \"\.(html|htm)$\">
        Header set Cache-Control \"public, max-age=3600, must-revalidate\"
    </FilesMatch>

    # API responses - very short cache
    <FilesMatch \"\.(json|xml)$\">
        Header set Cache-Control \"public, max-age=300, must-revalidate\"
    </FilesMatch>

    # Service Worker - no cache
    <FilesMatch \"sw\.js$\">
        Header set Cache-Control \"no-cache, no-store, must-revalidate\"
        Header set Pragma \"no-cache\"
        Header set Expires \"0\"
    </FilesMatch>

    # Manifest - moderate cache
    <FilesMatch \"manifest\.json$\">
        Header set Cache-Control \"public, max-age=604800\"
    </FilesMatch>
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE application/manifest+json
    AddOutputFilterByType DEFLATE image/svg+xml
</IfModule>

# Brotli compression (if available)
<IfModule mod_brotli.c>
    BrotliCompressionQuality 4
    BrotliFilterByType text/plain
    BrotliFilterByType text/html
    BrotliFilterByType text/xml
    BrotliFilterByType text/css
    BrotliFilterByType text/javascript
    BrotliFilterByType application/xml
    BrotliFilterByType application/xhtml+xml
    BrotliFilterByType application/rss+xml
    BrotliFilterByType application/javascript
    BrotliFilterByType application/x-javascript
    BrotliFilterByType application/json
    BrotliFilterByType application/manifest+json
    BrotliFilterByType image/svg+xml
</IfModule>

# Security headers for performance
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options \"nosniff\"
    Header always set X-Frame-Options \"DENY\"
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"

    # Preload hints for critical resources
    Header add Link \"</css/critical.css>; rel=preload; as=style\"
    Header add Link \"</js/app.min.js>; rel=preload; as=script\"
    Header add Link \"</fonts/main.woff2>; rel=preload; as=font; type=font/woff2; crossorigin\"
</IfModule>
# END Cache Rules
";
    }
    
    /**
     * Setup performance monitoring
     */
    public function setupPerformanceMonitoring(): array
    {
        $results = [
            'monitoring_enabled' => false,
            'metrics_collected' => 0,
            'errors' => []
        ];
        
        try {
            // Create performance monitoring table if not exists
            $this->createPerformanceTable();
            
            // Collect initial metrics
            $metrics = $this->collectPerformanceMetrics();
            $this->storePerformanceMetrics($metrics);
            
            $results['monitoring_enabled'] = true;
            $results['metrics_collected'] = count($metrics);
            
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Create performance monitoring table
     */
    private function createPerformanceTable(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS performance_metrics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            metric_name VARCHAR(100) NOT NULL,
            metric_value DECIMAL(10,4) NOT NULL,
            metric_unit VARCHAR(20) NOT NULL,
            page_url VARCHAR(255),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_metric_name (metric_name),
            INDEX idx_created_at (created_at)
        )";
        
        $this->db->exec($sql);
    }
    
    /**
     * Collect performance metrics
     */
    private function collectPerformanceMetrics(): array
    {
        return [
            'page_load_time' => $this->measurePageLoadTime(),
            'database_query_time' => $this->measureDatabaseQueryTime(),
            'memory_usage' => memory_get_peak_usage(true) / 1024 / 1024, // MB
            'cache_hit_ratio' => $this->calculateCacheHitRatio(),
            'image_optimization_ratio' => $this->calculateImageOptimizationRatio()
        ];
    }
    
    /**
     * Store performance metrics
     */
    private function storePerformanceMetrics(array $metrics): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO performance_metrics (metric_name, metric_value, metric_unit, page_url, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $pageUrl = $_SERVER['REQUEST_URI'] ?? '/';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        foreach ($metrics as $name => $value) {
            $unit = $this->getMetricUnit($name);
            $stmt->execute([$name, $value, $unit, $pageUrl, $userAgent]);
        }
    }
    
    /**
     * Get metric unit
     */
    private function getMetricUnit(string $metricName): string
    {
        $units = [
            'page_load_time' => 'ms',
            'database_query_time' => 'ms',
            'memory_usage' => 'MB',
            'cache_hit_ratio' => '%',
            'image_optimization_ratio' => '%'
        ];
        
        return $units[$metricName] ?? 'unit';
    }
    
    /**
     * Measure page load time
     */
    private function measurePageLoadTime(): float
    {
        if (defined('APP_START_TIME')) {
            return round((microtime(true) - APP_START_TIME) * 1000, 2);
        }
        return 0.0;
    }
    
    /**
     * Measure database query time
     */
    private function measureDatabaseQueryTime(): float
    {
        $start = microtime(true);
        $this->db->query("SELECT 1");
        return round((microtime(true) - $start) * 1000, 2);
    }
    
    /**
     * Calculate cache hit ratio
     */
    private function calculateCacheHitRatio(): float
    {
        // This would be implemented based on your cache system
        return 85.0; // Placeholder
    }
    
    /**
     * Calculate image optimization ratio
     */
    private function calculateImageOptimizationRatio(): float
    {
        $totalImages = count(glob($this->imageDir . '/**/*.{jpg,jpeg,png,gif}', GLOB_BRACE));
        $webpImages = count(glob($this->imageDir . '/**/*.webp', GLOB_BRACE));
        
        if ($totalImages === 0) return 0.0;
        
        return round(($webpImages / $totalImages) * 100, 2);
    }
    
    /**
     * Ensure required directories exist
     */
    private function ensureDirectories(): void
    {
        $directories = [
            $this->cacheDir,
            $this->cacheDir . '/queries',
            $this->cacheDir . '/images',
            $this->imageDir
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
}
