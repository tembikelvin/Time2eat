<?php
/**
 * Production Optimization Script
 * Optimizes the application for production deployment
 */

echo "⚡ Time2Eat Production Optimization\n";
echo "===================================\n\n";

// Check environment
if (!file_exists(__DIR__ . '/../.env')) {
    echo "❌ .env file not found. Please create it first.\n";
    exit(1);
}

// Load configuration
require_once __DIR__ . '/../config/config.php';

if (APP_ENV === 'production') {
    echo "✓ Running in production mode\n";
} else {
    echo "⚠️  Not in production mode (APP_ENV=" . APP_ENV . ")\n";
    echo "Continue anyway? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) !== 'y' && trim($line) !== 'Y') {
        echo "Optimization cancelled.\n";
        exit(0);
    }
    fclose($handle);
}

// Optimize autoloader
echo "\n📦 Optimizing autoloader...\n";
$output = [];
$returnCode = 0;
exec('composer dump-autoload --optimize --no-dev 2>&1', $output, $returnCode);
if ($returnCode === 0) {
    echo "✓ Autoloader optimized\n";
} else {
    echo "❌ Autoloader optimization failed\n";
    echo implode("\n", $output) . "\n";
}

// Clear and optimize cache
echo "\n🗄️  Optimizing cache...\n";
clearCache();
precompileTemplates();

// Optimize database
echo "\n🗃️  Optimizing database...\n";
optimizeDatabase();

// Optimize file permissions
echo "\n🔒 Setting production permissions...\n";
setProductionPermissions();

// Generate optimized configuration
echo "\n⚙️  Generating optimized configuration...\n";
generateOptimizedConfig();

// Create production .htaccess
echo "\n🌐 Optimizing web server configuration...\n";
optimizeWebServerConfig();

// Optimize images
echo "\n🖼️  Optimizing images...\n";
optimizeProductionImages();

// Generate sitemap
echo "\n🗺️  Generating sitemap...\n";
generateSitemap();

// Security hardening
echo "\n🛡️  Applying security hardening...\n";
applySecurityHardening();

// Performance monitoring setup
echo "\n📊 Setting up performance monitoring...\n";
setupPerformanceMonitoring();

echo "\n✅ Production optimization completed!\n\n";
echo "📋 Post-deployment checklist:\n";
echo "- [ ] Test all major functionality\n";
echo "- [ ] Verify SSL certificate\n";
echo "- [ ] Check error logs\n";
echo "- [ ] Test payment gateways\n";
echo "- [ ] Verify email/SMS notifications\n";
echo "- [ ] Test WebSocket connections\n";
echo "- [ ] Check mobile responsiveness\n";
echo "- [ ] Verify PWA installation\n";
echo "- [ ] Test backup procedures\n";
echo "- [ ] Monitor performance metrics\n\n";

/**
 * Clear and optimize cache
 */
function clearCache() {
    $cacheDir = __DIR__ . '/../storage/cache';
    
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✓ Cache cleared\n";
    }
    
    // Create cache directories
    $cacheDirs = [
        'storage/cache/views',
        'storage/cache/data',
        'storage/cache/sessions',
        'storage/cache/routes'
    ];
    
    foreach ($cacheDirs as $dir) {
        $fullPath = __DIR__ . '/../' . $dir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }
    
    echo "✓ Cache directories created\n";
}

/**
 * Precompile templates for better performance
 */
function precompileTemplates() {
    $viewsDir = __DIR__ . '/../src/views';
    $cacheDir = __DIR__ . '/../storage/cache/views';
    
    if (!is_dir($viewsDir)) {
        return;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsDir)
    );
    
    $compiled = 0;
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $relativePath = str_replace($viewsDir . '/', '', $file->getPathname());
            $cacheFile = $cacheDir . '/' . str_replace('/', '_', $relativePath) . '.cache';
            
            // Simple template caching (in a real app, you'd use a proper template engine)
            $content = file_get_contents($file->getPathname());
            file_put_contents($cacheFile, $content);
            $compiled++;
        }
    }
    
    echo "✓ Precompiled {$compiled} templates\n";
}

/**
 * Optimize database
 */
function optimizeDatabase() {
    try {
        $db = Database::getInstance();
        
        // Get all tables
        $tables = $db->query("SHOW TABLES");
        $optimized = 0;
        
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $db->exec("OPTIMIZE TABLE `{$tableName}`");
            $optimized++;
        }
        
        echo "✓ Optimized {$optimized} database tables\n";
        
        // Update table statistics
        $db->exec("ANALYZE TABLE users, restaurants, orders, menu_items");
        echo "✓ Updated table statistics\n";
        
    } catch (Exception $e) {
        echo "⚠️  Database optimization failed: " . $e->getMessage() . "\n";
    }
}

/**
 * Set production file permissions
 */
function setProductionPermissions() {
    if (PHP_OS_FAMILY === 'Windows') {
        echo "⚠️  Skipping permission changes on Windows\n";
        return;
    }
    
    $permissions = [
        '.' => 0755,
        'src' => 0755,
        'config' => 0750,
        'logs' => 0755,
        'storage' => 0755,
        'public' => 0755,
        'scripts' => 0750,
        '.env' => 0600,
        'composer.json' => 0644,
        'index.php' => 0644
    ];
    
    foreach ($permissions as $path => $permission) {
        $fullPath = __DIR__ . '/../' . $path;
        if (file_exists($fullPath)) {
            chmod($fullPath, $permission);
            echo "✓ Set permissions for: {$path}\n";
        }
    }
}

/**
 * Generate optimized configuration
 */
function generateOptimizedConfig() {
    $configCache = __DIR__ . '/../storage/cache/config.php';
    
    $config = [
        'app' => [
            'name' => APP_NAME,
            'url' => APP_URL,
            'env' => APP_ENV,
            'debug' => APP_DEBUG,
            'key' => APP_KEY
        ],
        'database' => [
            'host' => DB_HOST,
            'name' => DB_NAME,
            'charset' => DB_CHARSET
        ],
        'cache' => [
            'enabled' => CACHE_ENABLED,
            'duration' => CACHE_DURATION
        ],
        'session' => [
            'lifetime' => SESSION_LIFETIME,
            'secure' => SESSION_SECURE
        ]
    ];
    
    file_put_contents($configCache, '<?php return ' . var_export($config, true) . ';');
    echo "✓ Generated optimized configuration cache\n";
}

/**
 * Optimize web server configuration
 */
function optimizeWebServerConfig() {
    $htaccessFile = __DIR__ . '/../.htaccess';
    
    if (!file_exists($htaccessFile)) {
        echo "⚠️  .htaccess file not found\n";
        return;
    }
    
    $content = file_get_contents($htaccessFile);
    
    // Add production-specific optimizations
    $productionRules = <<<HTACCESS

# Production Optimizations
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>

<IfModule mod_headers.c>
    # Enable HSTS for HTTPS
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    
    # Prevent MIME type sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # Enable XSS protection
    Header always set X-XSS-Protection "1; mode=block"
    
    # Prevent clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
</IfModule>

# Disable server signature
ServerSignature Off

# Hide PHP version
<IfModule mod_headers.c>
    Header unset X-Powered-By
</IfModule>
HTACCESS;
    
    if (strpos($content, 'Production Optimizations') === false) {
        file_put_contents($htaccessFile, $content . $productionRules);
        echo "✓ Added production rules to .htaccess\n";
    } else {
        echo "✓ Production rules already present in .htaccess\n";
    }
}

/**
 * Optimize images for production
 */
function optimizeProductionImages() {
    if (!extension_loaded('gd')) {
        echo "⚠️  GD extension not available\n";
        return;
    }
    
    $imageDir = __DIR__ . '/../public/images';
    $images = glob($imageDir . '/*.{jpg,jpeg,png}', GLOB_BRACE);
    $optimized = 0;
    
    foreach ($images as $imagePath) {
        $info = pathinfo($imagePath);
        $extension = strtolower($info['extension']);
        
        try {
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($imagePath);
                    if ($image) {
                        imagejpeg($image, $imagePath, 80); // Compress to 80% quality
                        imagedestroy($image);
                        $optimized++;
                    }
                    break;
                    
                case 'png':
                    $image = imagecreatefrompng($imagePath);
                    if ($image) {
                        imagepng($image, $imagePath, 6); // Compress PNG
                        imagedestroy($image);
                        $optimized++;
                    }
                    break;
            }
        } catch (Exception $e) {
            // Skip problematic images
        }
    }
    
    echo "✓ Optimized {$optimized} images\n";
}

/**
 * Generate sitemap for SEO
 */
function generateSitemap() {
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Static pages
    $pages = [
        '' => ['priority' => '1.0', 'changefreq' => 'daily'],
        'browse' => ['priority' => '0.9', 'changefreq' => 'daily'],
        'about' => ['priority' => '0.5', 'changefreq' => 'monthly']
    ];
    
    foreach ($pages as $page => $meta) {
        $url = rtrim(APP_URL, '/') . '/' . $page;
        $sitemap .= "  <url>\n";
        $sitemap .= "    <loc>{$url}</loc>\n";
        $sitemap .= "    <changefreq>{$meta['changefreq']}</changefreq>\n";
        $sitemap .= "    <priority>{$meta['priority']}</priority>\n";
        $sitemap .= "  </url>\n";
    }
    
    $sitemap .= '</urlset>';
    
    file_put_contents(__DIR__ . '/../public/sitemap.xml', $sitemap);
    echo "✓ Generated sitemap.xml\n";
}

/**
 * Apply security hardening
 */
function applySecurityHardening() {
    // Create security.txt file
    $securityTxt = <<<TXT
Contact: mailto:security@time2eat.com
Expires: 2025-12-31T23:59:59.000Z
Preferred-Languages: en, fr
Canonical: https://time2eat.com/.well-known/security.txt
TXT;
    
    $wellKnownDir = __DIR__ . '/../public/.well-known';
    if (!is_dir($wellKnownDir)) {
        mkdir($wellKnownDir, 0755, true);
    }
    
    file_put_contents($wellKnownDir . '/security.txt', $securityTxt);
    echo "✓ Created security.txt\n";
    
    // Create robots.txt
    $robotsTxt = <<<TXT
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /vendor/
Disallow: /config/
Disallow: /logs/
Disallow: /storage/
Disallow: /scripts/

Sitemap: {APP_URL}/sitemap.xml
TXT;
    
    file_put_contents(__DIR__ . '/../public/robots.txt', $robotsTxt);
    echo "✓ Created robots.txt\n";
}

/**
 * Setup performance monitoring
 */
function setupPerformanceMonitoring() {
    $monitoringDir = __DIR__ . '/../storage/monitoring';
    if (!is_dir($monitoringDir)) {
        mkdir($monitoringDir, 0755, true);
    }
    
    // Create performance log
    $perfLog = $monitoringDir . '/performance.log';
    if (!file_exists($perfLog)) {
        touch($perfLog);
        chmod($perfLog, 0644);
    }
    
    // Create uptime monitoring script
    $uptimeScript = <<<PHP
<?php
// Simple uptime monitoring
\$url = '{APP_URL}';
\$start = microtime(true);
\$response = @file_get_contents(\$url);
\$time = microtime(true) - \$start;

\$status = \$response !== false ? 'UP' : 'DOWN';
\$logEntry = date('Y-m-d H:i:s') . " - Status: \$status, Response Time: " . round(\$time * 1000, 2) . "ms\n";

file_put_contents(__DIR__ . '/uptime.log', \$logEntry, FILE_APPEND | LOCK_EX);
PHP;
    
    file_put_contents($monitoringDir . '/uptime_check.php', $uptimeScript);
    echo "✓ Created performance monitoring scripts\n";
}
