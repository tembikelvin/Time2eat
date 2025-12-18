<?php
/**
 * HYBRID ROUTER - Simple & Reliable
 * 
 * This fixes your routing errors by:
 * 1. Using direct file access for pages (no namespace issues)
 * 2. Falling back to old router for unmigrated routes
 * 3. Catching errors gracefully
 * 
 * TO USE: Rename this to index.php (backup old one first!)
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base path
define('BASE_PATH', __DIR__);
define('ROOT_PATH', __DIR__); // Some files use ROOT_PATH

// Load configuration
if (file_exists(BASE_PATH . '/config/config.php')) {
    require_once BASE_PATH . '/config/config.php';
}

// Load core dependencies
require_once BASE_PATH . '/config/database.php';
if (file_exists(BASE_PATH . '/src/helpers/functions.php')) {
    require_once BASE_PATH . '/src/helpers/functions.php';
}
if (file_exists(BASE_PATH . '/src/helpers/environment.php')) {
    require_once BASE_PATH . '/src/helpers/environment.php';
}

// Get request info
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Handle method spoofing for PUT, PATCH, DELETE via _method field
if ($requestMethod === 'POST' && isset($_POST['_method'])) {
    $requestMethod = strtoupper($_POST['_method']);
}

// Smart environment detection based on hostname
$isDevelopment = preg_match('/^(localhost|127\.0\.0\.1|.*\.local|.*\.test|.*\.dev)$/i', $_SERVER['HTTP_HOST'] ?? '');
$appPath = $isDevelopment ? '/eat' : '';

// Remove base directory from URI
if (!empty($appPath)) {
    $uri = str_replace($appPath, '', $requestUri);
} else {
    $uri = $requestUri;
}
$uri = '/' . trim($uri, '/');

// Remove query string
$uri = parse_url($uri, PHP_URL_PATH);

// ============================================
// SECTION 1: API ROUTES (Direct File Access)
// ============================================
if (strpos($uri, '/api/') === 0) {
    // CRITICAL FIX: Don't set Content-Type here!
    // Let the API file set its own headers (including no-cache headers)
    // Setting headers here prevents API files from controlling their own caching
    
    $apiFile = BASE_PATH . $uri;
    // Only append .php if it's not already there
    if (substr($apiFile, -4) !== '.php') {
        $apiFile .= '.php';
    }
    if (file_exists($apiFile)) {
        require $apiFile;
        exit;
    }
    
    // Only set headers for 404 response
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
    exit;
}

// ============================================
// SECTION 2: STATIC ASSETS (Pass Through)
// ============================================
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|ico|woff|woff2|ttf|eot)$/', $uri)) {
    return false;
}

// ============================================
// SECTION 3: SIMPLE PAGE ROUTES
// ============================================

// Simple route mapping - ADD YOUR ROUTES HERE
$simpleRoutes = [
    // Home page uses old router for the complete WARM design with image background
    // 'GET /' => 'pages/home.php',  // Simple version
    // 'GET /home' => 'pages/home.php',  // Simple version

    // Cart is now handled by CartController via EnhancedRouter (routes/web.php line 617)
    // This provides a unified cart experience with modal-based UX
    // 'GET /cart' => 'pages/cart.php',  // REMOVED - use CartController instead

    // Customer routes - Hybrid router compatible
    // Note: These routes are handled by EnhancedRouter in production
    // Fallback routes for development only
    'GET /customer/dashboard' => 'pages/customer/dashboard.php',
    'GET /customer' => 'pages/customer/dashboard.php',
    'GET /customer/orders' => 'pages/customer/orders.php',

    // Rider routes - Hybrid router compatible
    'GET /rider/dashboard' => 'pages/rider/dashboard.php',
    'GET /rider' => 'pages/rider/dashboard.php',
    'GET /rider/deliveries' => 'pages/rider/deliveries.php',
    'GET /rider/earnings' => 'pages/rider/earnings.php',

    // Profile route - Redirects to role-specific profile
    'GET /profile' => 'pages/profile.php',

    // Admin routes - Hybrid router compatible
    'POST /admin/user-management/users/approve' => 'api/admin/user-management/users/approve.php',
    'POST /admin/user-management/users/reject' => 'api/admin/user-management/users/reject.php',
    
    // Add more routes as you migrate them:
    // 'GET /admin/dashboard' => 'pages/admin/dashboard.php',
];

$route = "$requestMethod $uri";

// Check if route exists in simple mapping
if (isset($simpleRoutes[$route])) {
    $pageFile = BASE_PATH . '/' . $simpleRoutes[$route];
    if (file_exists($pageFile)) {
        require $pageFile;
        exit;
    }
}

// ============================================
// SECTION 4: FALLBACK TO OLD ROUTER
// ============================================
// For routes not yet migrated, use old router
if (file_exists(BASE_PATH . '/bootstrap/app.php')) {
    try {
        $app = require BASE_PATH . '/bootstrap/app.php';
        $app->run();
        exit;
    } catch (Exception $e) {
        // Old router failed - show helpful error
        error_log("Router error: " . $e->getMessage());
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Application Error</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; padding: 20px; background: #f5f5f5; }
                .error-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 900px; margin: 0 auto; }
                h1 { color: #e74c3c; margin-top: 0; }
                .error-details { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #e74c3c; }
                pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; line-height: 1.5; }
                .suggestion { background: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin-top: 20px; border-radius: 4px; }
                .suggestion h3 { margin-top: 0; color: #155724; }
                .back-link { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-weight: 500; }
                .back-link:hover { background: #2980b9; }
                code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; color: #e74c3c; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>⚠️ Application Error (Caught by Hybrid Router)</h1>
                
                <div class='error-details'>
                    <p><strong>Error:</strong> <?= htmlspecialchars($e->getMessage()) ?></p>
                    <p><strong>File:</strong> <?= htmlspecialchars($e->getFile()) ?></p>
                    <p><strong>Line:</strong> <?= $e->getLine() ?></p>
                    <p><strong>Requested URI:</strong> <code><?= htmlspecialchars($uri) ?></code></p>
                </div>
                
                <h3>Stack Trace:</h3>
                <pre><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
                
                <div class='suggestion'>
                    <h3>✅ Good News: The Hybrid Router is Working!</h3>
                    <p>The old routing system failed with the error above, but the hybrid router caught it and prevented a complete crash.</p>
                    
                    <h4>🔧 How to Fix This Permanently:</h4>
                    <ol>
                        <li>Create a simple page file at: <code>pages<?= htmlspecialchars($uri) ?>.php</code></li>
                        <li>Add this route to the <code>$simpleRoutes</code> array in <code>index.php</code></li>
                        <li>The page will load instantly without routing errors!</li>
                    </ol>
                    
                    <p><strong>Example:</strong> See <code>pages/home.php</code> for a working example.</p>
                </div>
                
                <a href='/eat/' class='back-link'>← Go to Home Page</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    } catch (Error $e) {
        // Fatal error in old router
        error_log("Fatal error: " . $e->getMessage());
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Fatal Error</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
                .error-box { background: white; padding: 30px; border-radius: 8px; max-width: 900px; margin: 0 auto; }
                h1 { color: #c0392b; }
                pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; }
                .back-link { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>💥 Fatal Error</h1>
                <p><strong>Error:</strong> <?= htmlspecialchars($e->getMessage()) ?></p>
                <p><strong>File:</strong> <?= htmlspecialchars($e->getFile()) ?></p>
                <p><strong>Line:</strong> <?= $e->getLine() ?></p>
                <pre><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
                <a href='/eat/' class='back-link'>← Go to Home Page</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// ============================================
// SECTION 5: 404 NOT FOUND
// ============================================
http_response_code(404);
?>
<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; text-align: center; padding: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .error-box { background: white; padding: 60px 40px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 600px; }
        h1 { color: #667eea; font-size: 120px; margin: 0; font-weight: 700; }
        h2 { color: #333; margin: 20px 0; font-size: 32px; }
        p { color: #7f8c8d; font-size: 18px; margin: 15px 0; }
        a { display: inline-block; margin-top: 30px; padding: 15px 40px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; transition: all 0.3s; }
        a:hover { background: #764ba2; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .uri { display: inline-block; margin-top: 30px; padding: 10px 20px; background: #f8f9fa; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class='error-box'>
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>Oops! The page you're looking for doesn't exist.</p>
        <p>It might have been moved or deleted.</p>
        <a href='<?= function_exists('url') ? url('/') : ($appPath ?: '/') ?>'>← Go Back Home</a>
        <div class='uri'>Requested: <?= htmlspecialchars($uri) ?></div>
    </div>
</body>
</html>
<?php
exit;

