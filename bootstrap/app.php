<?php

declare(strict_types=1);

/**
 * Application Bootstrap
 * Initialize the Time2Eat application with enhanced routing and error handling
 */

// Load environment variables based on environment detection
function loadEnvironmentFile($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
            putenv(trim($name) . '=' . trim($value));
        }
    }
}

// Detect environment first (before loading .env)
function detectEnvironment() {
    // Check for explicit environment override
    if (isset($_SERVER['APP_ENV'])) {
        return $_SERVER['APP_ENV'];
    }

    // Check for development indicators
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    $developmentHosts = ['localhost', '127.0.0.1', '::1', '.local', '.test', '.dev'];
    $developmentPaths = ['/wamp', '/xampp', '/mamp', '/laragon', '/eat'];

    // Check host
    foreach ($developmentHosts as $devHost) {
        if (strpos($host, $devHost) !== false) {
            return 'development';
        }
    }

    // Check script path
    foreach ($developmentPaths as $devPath) {
        if (strpos($scriptName, $devPath) !== false) {
            return 'development';
        }
    }

    // Check for production indicators
    if (strpos($host, 'time2eat.org') !== false || strpos($host, 'time2eat.com') !== false) {
        return 'production';
    }

    // Default to development for local development
    return 'development';
}

// Detect environment
$detectedEnv = detectEnvironment();

// Load appropriate environment file
if ($detectedEnv === 'production' && file_exists(__DIR__ . '/../.env.production')) {
    loadEnvironmentFile(__DIR__ . '/../.env.production');
} else {
    // Load .env for development or fallback
    loadEnvironmentFile(__DIR__ . '/../.env');
}

// After loading .env, check for FORCE_ENV override
if (isset($_ENV['FORCE_ENV'])) {
    $detectedEnv = $_ENV['FORCE_ENV'];
    // Reload the appropriate environment file if FORCE_ENV changed the environment
    if ($detectedEnv === 'production' && file_exists(__DIR__ . '/../.env.production')) {
        loadEnvironmentFile(__DIR__ . '/../.env.production');
    } else {
        loadEnvironmentFile(__DIR__ . '/../.env');
    }
}

// Configure session before starting (will be overridden by config.php if needed)
if (session_status() === PHP_SESSION_NONE) {
    // Basic session configuration
    ini_set('session.gc_maxlifetime', 7200);
    session_set_cookie_params([
        'lifetime' => 7200,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('Africa/Douala');

// Load Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    die('Composer autoloader not found. Please run "composer install" to install dependencies.');
}

// Define constants (only if not already defined)
if (!defined('ROOT_PATH')) {
    // Detect the correct root path based on the current script location
    $currentDir = __DIR__;
    $scriptDir = dirname($_SERVER['SCRIPT_FILENAME'] ?? '');
    
    // If we're in a subdirectory, use the subdirectory as root
    if (strpos($scriptDir, $currentDir) === 0) {
        define('ROOT_PATH', $scriptDir);
    } else {
        define('ROOT_PATH', dirname(__DIR__));
    }
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/src');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', ROOT_PATH . '/config');
}
if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', ROOT_PATH . '/storage');
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}



// Autoloader for classes
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace('\\', '/', $class) . '.php';
    
    // Try different base paths
    $paths = [
        ROOT_PATH . '/src/' . $file,
        ROOT_PATH . '/' . $file
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load core configuration first (defines constants)
if (file_exists(CONFIG_PATH . '/config.php')) {
    require_once CONFIG_PATH . '/config.php';
}

// Load helper functions first
if (file_exists(ROOT_PATH . '/src/helpers/functions.php')) {
    require_once ROOT_PATH . '/src/helpers/functions.php';
}

// Load application configuration
$config = [];
if (file_exists(CONFIG_PATH . '/app.php')) {
    $config = require CONFIG_PATH . '/app.php';
}

// Load environment helper after config
if (file_exists(ROOT_PATH . '/src/helpers/environment.php')) {
    require_once ROOT_PATH . '/src/helpers/environment.php';
}

// Load database configuration
if (file_exists(CONFIG_PATH . '/database.php')) {
    require_once CONFIG_PATH . '/database.php';
}

// Set application environment (may already be defined in config.php)
if (defined('APP_ENV')) {
    // Use the already defined constant
    $environment = APP_ENV;
} else {
    // Use config or default
    $environment = $config['environment'] ?? 'production';
    define('APP_ENV', $environment);
}

// Define APP_URL constant
if (!defined('APP_URL')) {
    define('APP_URL', $config['url'] ?? 'http://localhost/eat');
}

// Error handling based on environment
if ($environment === 'production') {
    // Enable error display in production when APP_DEBUG is true
    error_reporting(E_ALL);
    ini_set('display_errors', (defined('APP_DEBUG') && APP_DEBUG) ? '1' : '0');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/error.log');
}

// Custom error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $errorMessage = "Error: {$message} in {$file} on line {$line}";
    error_log($errorMessage);
    
    // Display errors in development or when APP_DEBUG is enabled in production
    // BUT NOT for JSON/AJAX requests to prevent breaking JSON responses
    $isJsonRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    $isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $isApiRoute = strpos($_SERVER['REQUEST_URI'] ?? '', '/rider/delivery-status') !== false || 
                  strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
    
    if ((APP_ENV === 'development' || (defined('APP_DEBUG') && APP_DEBUG)) && !$isJsonRequest && !$isAjaxRequest && !$isApiRoute) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<strong>Error:</strong> {$message}<br>";
        echo "<strong>File:</strong> {$file}<br>";
        echo "<strong>Line:</strong> {$line}";
        echo "</div>";
    }
    
    return true;
});

// Custom exception handler
set_exception_handler(function($exception) {
    $errorMessage = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    error_log($errorMessage);
    
    // Display exceptions in development or when APP_DEBUG is enabled in production
    if (APP_ENV === 'development' || (defined('APP_DEBUG') && APP_DEBUG)) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<h3>Uncaught Exception</h3>";
        echo "<strong>Message:</strong> " . $exception->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
        echo "<strong>Stack Trace:</strong><pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        http_response_code(500);
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>Something went wrong. Please try again later.</p>";
    }
});

// Fatal error handler
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        $errorMessage = "Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'];
        error_log($errorMessage);
        
        if (APP_ENV === 'development') {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "<h3>Fatal Error</h3>";
            echo "<strong>Message:</strong> " . $error['message'] . "<br>";
            echo "<strong>File:</strong> " . $error['file'] . "<br>";
            echo "<strong>Line:</strong> " . $error['line'] . "<br>";
            echo "</div>";
        } else {
            http_response_code(500);
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Something went wrong. Please try again later.</p>";
        }
    }
});

/**
 * Application Class
 * Main application container and router
 */
class Application
{
    private $router;
    private $config;
    private static $instance;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->initializeRouter();
        self::$instance = $this;
    }
    
    /**
     * Get application instance
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }
    
    /**
     * Initialize router
     */
    private function initializeRouter(): void
    {
        // Use enhanced router if available, fallback to simple router
        if (class_exists('core\EnhancedRouter')) {
            $this->router = new core\EnhancedRouter($this->config('url', ''));
        } else {
            $this->router = new core\Router();
        }
    }
    
    /**
     * Load routes
     */
    public function loadRoutes(): void
    {
        $routeFiles = [
            ROOT_PATH . '/routes/web.php',
            ROOT_PATH . '/routes/api.php'
        ];

        foreach ($routeFiles as $routeFile) {
            if (file_exists($routeFile)) {
                // Pass the current router to the route file
                $router = $this->router;
                $loadedRouter = require $routeFile;

                // If the route file returns a router, use it
                if ($loadedRouter instanceof core\EnhancedRouter) {
                    $this->router = $loadedRouter;
                }
                // If no router returned, the routes were added to the existing router
            }
        }
    }
    
    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];

            // Handle method spoofing for PUT, PATCH, DELETE via _method field
            if ($method === 'POST' && isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            }

            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            // Handle static files first (before routing)
            if ($this->handleStaticFiles($uri)) {
                return; // Static file served, exit early
            }

            $this->loadRoutes();

            // Handle base path for subdirectory installations
            $basePath = $this->getBasePath();
            
            // Check for duplicate base path in URL and redirect to canonical URL
            if ($basePath && strpos($uri, $basePath . $basePath) === 0) {
                // Remove duplicate base path - redirect to canonical URL
                $canonicalPath = $basePath . substr($uri, strlen($basePath));
                $canonicalUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
                    . '://' . $_SERVER['HTTP_HOST'] . $canonicalPath;
                header("Location: $canonicalUrl", true, 301);
                exit;
            }
            
            // Remove base path if application is in subdirectory
            if ($basePath && strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }

            // Ensure URI starts with /
            if (empty($uri) || $uri[0] !== '/') {
                $uri = '/' . $uri;
            }

            // Remove trailing slash except for root
            if ($uri !== '/' && substr($uri, -1) === '/') {
                $uri = rtrim($uri, '/');
            }

            // Dispatch request
            if (method_exists($this->router, 'dispatch')) {
                $this->router->dispatch($method, $uri);
            } else {
                // Fallback for simple router
                $this->router->dispatch();
            }

        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle static files (CSS, JS, images, etc.)
     */
    private function handleStaticFiles(string $uri): bool
    {
        // Check if this is a request for a static file
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|webp|pdf|txt|xml)$/i', $uri)) {
            // Remove leading slash and handle different path formats
            $filePath = ltrim($uri, '/');

            // Handle /public/ prefix in URI
            if (strpos($filePath, 'public/') === 0) {
                $filePath = substr($filePath, 7); // Remove 'public/' prefix
            }

            // Construct full file path
            $fullPath = __DIR__ . '/../public/' . $filePath;

            // Security check - ensure file is within public directory
            $realPath = realpath($fullPath);
            $publicDir = realpath(__DIR__ . '/../public/');

            if ($realPath && $publicDir && strpos($realPath, $publicDir) === 0 && is_file($realPath)) {
                // Determine content type
                $contentType = $this->getContentType($realPath);

                // Set appropriate headers
                header('Content-Type: ' . $contentType);
                header('Content-Length: ' . filesize($realPath));

                // Set caching headers for static files
                $expires = 86400 * 30; // 30 days
                header('Cache-Control: public, max-age=' . $expires);
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

                // Output file
                readfile($realPath);
                return true;
            }
        }

        return false;
    }

    /**
     * Get content type for file
     */
    private function getContentType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'xml' => 'application/xml'
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Handle application exceptions
     */
    private function handleException(Exception $e): void
    {
        error_log("Application Exception: " . $e->getMessage());
        
        if (APP_ENV === 'development') {
            throw $e;
        }
        
        // Check if it's an API request
        $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0 ||
                 (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        
        if ($isApi) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'code' => 500
            ]);
        } else {
            http_response_code(500);
            $this->renderErrorPage(500, 'Internal Server Error');
        }
    }
    
    /**
     * Render error page
     */
    private function renderErrorPage(int $code, string $message): void
    {
        $errorFile = ROOT_PATH . "/src/views/errors/{$code}.php";
        
        if (file_exists($errorFile)) {
            $title = "{$code} - {$message}";
            $error_code = $code;
            $error_message = $message;
            
            include $errorFile;
        } else {
            echo "<h1>{$code} - {$message}</h1>";
        }
    }
    
    /**
     * Get configuration value
     */
    public function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * Get router instance
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Get application base path
     */
    private function getBasePath(): string
    {
        // Try to get from APP_URL first
        if (defined('APP_URL')) {
            $parsed = parse_url(APP_URL);
            return $parsed['path'] ?? '';
        }

        // Fallback: detect from script name
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);

        // Normalize path
        if ($basePath === '/' || $basePath === '\\') {
            return '';
        }

        return $basePath;
    }
}

/**
 * Helper Functions
 */

/**
 * Get application instance
 */
function app(): Application
{
    return Application::getInstance();
}

/**
 * Get configuration value
 */
function config(string $key, $default = null)
{
    return app()->config($key, $default);
}

/**
 * Generate URL for named route
 */
function route(string $name, array $parameters = []): string
{
    $router = app()->getRouter();
    
    if (method_exists($router, 'url')) {
        return $router->url($name, $parameters);
    }
    
    // Fallback for simple routing
    return '/' . ltrim($name, '/');
}

// Helper functions are now loaded from src/helpers/functions.php

/**
 * Get old input value
 */
function old(string $key, $default = ''): string
{
    $oldInput = $_SESSION['old_input'] ?? [];
    return $oldInput[$key] ?? $default;
}

/**
 * Check if there are validation errors
 */
function hasErrors(): bool
{
    return !empty($_SESSION['errors']);
}

/**
 * Get validation errors
 */
function errors(): array
{
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);
    return $errors;
}

/**
 * Get flash messages
 */
function flash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

// CSRF functions moved to src/helpers/functions.php for global availability
// This prevents redeclaration errors and ensures consistent CSRF handling

// Create and return application instance
return new Application($config);
