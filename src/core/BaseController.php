<?php

declare(strict_types=1);

namespace core;

require_once __DIR__ . '/../traits/DatabaseTrait.php';
require_once __DIR__ . '/../traits/ValidationTrait.php';
require_once __DIR__ . '/../traits/AuthTrait.php';
require_once __DIR__ . '/../traits/ResponseTrait.php';

use traits\DatabaseTrait;
use traits\ValidationTrait;
use traits\AuthTrait;
use traits\ResponseTrait;

/**
 * Enhanced Base Controller Class
 * Comprehensive controller with SOLID principles, error handling, and validation
 */
abstract class BaseController
{
    use DatabaseTrait, ValidationTrait, AuthTrait, ResponseTrait;
    
    protected ?\PDO $db = null;
    protected array $data = [];
    protected string $layout = 'app';
    protected ?object $user = null;
    protected array $middleware = [];
    protected array $errors = [];
    protected array $rules = [];
    
    public function __construct()
    {
        $this->initializeController();
    }
    
    /**
     * Initialize controller with common setup
     */
    protected function initializeController(): void
    {
        $this->startSession();
        $this->loadUser();
        $this->setSecurityHeaders();
        $this->handleCsrfProtection();
    }
    
    /**
     * Execute middleware before action
     */
    public function executeMiddleware(string $action): bool
    {
        foreach ($this->middleware as $middlewareClass) {
            if (!class_exists($middlewareClass)) {
                continue;
            }
            
            $middleware = new $middlewareClass();
            if (method_exists($middleware, 'handle') && !$middleware->handle($this, $action)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * View method for compatibility with old controllers
     */
    protected function view(string $view, array $data = []): void
    {
        $this->render($view, $data);
    }
    
    /**
     * Render view with comprehensive error handling
     */
    protected function render(string $view, array $data = []): void
    {
        try {
            $this->data = array_merge($this->data, $data);
            // Only set user if not already provided in data
            if (!isset($data['user'])) {
                $this->data['user'] = $this->user;
            }
            $this->data['errors'] = $this->errors;
            $this->data['csrf_token'] = $this->generateCsrfToken();
            $this->data['flash'] = $this->getFlash();
            
            $content = $this->renderView($view);
            $this->renderLayout($content);
            
        } catch (\Exception $e) {
            $this->handleRenderError($e, $view);
        }
    }
    
    /**
     * Render view file
     */
    private function renderView(string $view): string
    {
        ob_start();
        
        $viewFile = $this->getViewPath($view);
        if (!file_exists($viewFile)) {
            throw new \Exception("View not found: {$view}");
        }
        
        extract($this->data, EXTR_SKIP);
        include $viewFile;
        
        return ob_get_clean();
    }
    
    /**
     * Render layout with content
     */
    private function renderLayout(string $content): void
    {
        $layoutFile = $this->getLayoutPath($this->layout);
        if (file_exists($layoutFile)) {
            $this->data['content'] = $content;
            extract($this->data, EXTR_SKIP);
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    /**
     * Get view file path
     */
    private function getViewPath(string $view): string
    {
        return __DIR__ . "/../views/{$view}.php";
    }
    
    /**
     * Get layout file path
     */
    private function getLayoutPath(string $layout): string
    {
        return __DIR__ . "/../views/layouts/{$layout}.php";
    }
    
    /**
     * Handle render errors gracefully
     */
    private function handleRenderError(\Exception $e, string $view): void
    {
        error_log("Render error for view '{$view}': " . $e->getMessage());
        
        if ($this->isAjaxRequest()) {
            $this->jsonError('Internal server error', 500);
        } else {
            $this->renderErrorPage(500, 'Internal Server Error');
        }
    }
    
    /**
     * Render error page
     */
    protected function renderErrorPage(int $code, string $message): void
    {
        http_response_code($code);
        
        $errorView = "errors/{$code}";
        $errorFile = $this->getViewPath($errorView);
        
        if (file_exists($errorFile)) {
            $this->data['error_code'] = $code;
            $this->data['error_message'] = $message;
            
            try {
                $content = $this->renderView($errorView);
                $this->renderLayout($content);
            } catch (\Exception $e) {
                echo "<h1>{$code} - {$message}</h1>";
            }
        } else {
            echo "<h1>{$code} - {$message}</h1>";
        }
    }
    
    /**
     * Validate request input
     */
    protected function validateRequest(array $rules, array $data = null): array
    {
        $data = $data ?? $this->getRequestData();
        $validator = $this->validate($data, $rules);
        
        if (!$validator['valid']) {
            $this->errors = $validator['errors'];
            
            if ($this->isAjaxRequest()) {
                $this->jsonError('Validation failed', 422, $validator['errors']);
            }
        }
        
        return $validator['data'];
    }
    
    /**
     * Set the layout for rendering
     */
    protected function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Get request data based on method
     */
    protected function getRequestData(): array
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // For method-spoofed requests (POST with _method field), use $_POST
        if ($method === 'POST' && isset($_POST['_method'])) {
            return $_POST;
        }
        
        return match($method) {
            'GET' => $_GET,
            'POST' => $_POST,
            'PUT', 'PATCH', 'DELETE' => $this->getJsonInput(),
            default => []
        };
    }
    
    /**
     * Get input data (alias for getRequestData for compatibility)
     */
    protected function getInput(): array
    {
        return $this->getRequestData();
    }
    
    /**
     * Get JSON input for REST requests
     */
    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjaxRequest(): bool
    {
        // Check for XMLHttpRequest header
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        
        // Check if Accept header requests JSON
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'application/json') !== false) {
            return true;
        }
        
        // Check if Content-Type is JSON (for POST requests)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if request is POST
     */
    protected function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }
    
    /**
     * Check if request accepts JSON
     */
    protected function acceptsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'application/json') !== false;
    }
    
    /**
     * Set security headers
     * Skip HTML headers for JSON API requests to prevent "headers already sent" errors
     */
    private function setSecurityHeaders(): void
    {
        // CRITICAL: Don't set HTML headers for JSON API endpoints
        // JSON responses need their own headers set by ResponseTrait
        $isJsonRequest = $this->isAjaxRequest() || 
                        $this->acceptsJson() || 
                        strpos($_SERVER['REQUEST_URI'] ?? '', '/checkout/place-order') !== false ||
                        strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
        
        if ($isJsonRequest) {
            // For JSON requests, only set headers if they haven't been sent yet
            // Let ResponseTrait handle JSON-specific headers
            return;
        }
        
        // For HTML pages, set standard security headers
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            if (isset($_SERVER['HTTPS'])) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }
    }
    
    /**
     * Handle CSRF protection
     */
    private function handleCsrfProtection(): void
    {
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Skip CSRF validation for specific routes
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        // Skip CSRF for logout since user is already authenticated
        // Skip for login/register as they have their own CSRF handling
        $skipRoutes = ['/login', '/register', '/logout', '/admin/tools/approvals', '/vendor/setup', '/admin/tools/backups'];
        
        // Also skip for backup operations specifically
        if (isset($_POST['action']) && $_POST['action'] === 'create_backup') {
            return;
        }

        foreach ($skipRoutes as $route) {
            if (strpos($uri, $route) !== false) {
                return;
            }
        }

        // Also skip if explicitly set in session (for API-like endpoints)
        if (isset($_SESSION['skip_csrf_check']) && $_SESSION['skip_csrf_check'] === true) {
            unset($_SESSION['skip_csrf_check']); // Clear after use
            return;
        }

        // Skip CSRF for JSON API requests (handled by authentication)
        // JSON requests are authenticated via requireAuth() and don't need CSRF
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false || $this->isAjaxRequest()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
        }
    }
    
    /**
     * Add flash message
     */
    protected function flash(string $type, string $message): void
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash messages
     */
    protected function getFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
    
    /**
     * Get current authenticated user
     */
    protected function getCurrentUser(): ?object
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $this->user;
    }
    
    /**
     * Get authenticated user (alias for getCurrentUser for compatibility)
     */
    protected function getAuthenticatedUser(): ?object
    {
        return $this->getCurrentUser();
    }
    
    /**
     * Check if user has specific role
     */
    protected function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->role === $role;
    }
    
    /**
     * Check if user is admin
     */
    protected function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
    
    /**
     * Check if user is vendor
     */
    protected function isVendor(): bool
    {
        return $this->hasRole('vendor');
    }
    
    /**
     * Check if user is customer
     */
    protected function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }
    
    /**
     * Check if user is rider
     */
    protected function isRider(): bool
    {
        return $this->hasRole('rider');
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            if ($this->isAjaxRequest()) {
                $this->jsonError('Authentication required', 401);
            } else {
                $this->flash('error', 'Please log in to continue');
                $this->redirect('/login');
            }
        }
    }
    
    /**
     * Require specific role
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            if ($this->isAjaxRequest()) {
                $this->jsonError('Insufficient permissions', 403);
            } else {
                $this->renderErrorPage(403, 'Access Denied');
            }
        }
    }
    
    /**
     * Rate limiting check
     */
    protected function checkRateLimit(string $key, int $maxAttempts = 60, int $timeWindow = 3600): bool
    {
        $cacheKey = "rate_limit:{$key}:" . floor(time() / $timeWindow);
        
        $attempts = $this->getCacheValue($cacheKey, 0);
        
        if ($attempts >= $maxAttempts) {
            if ($this->isAjaxRequest()) {
                $this->jsonError('Rate limit exceeded', 429);
            } else {
                $this->renderErrorPage(429, 'Too Many Requests');
            }
            return false;
        }
        
        $this->setCacheValue($cacheKey, $attempts + 1, $timeWindow);
        return true;
    }
    
    /**
     * Simple cache implementation
     */
    private function getCacheValue(string $key, mixed $default = null): mixed
    {
        $cacheFile = __DIR__ . "/../../storage/cache/" . md5($key) . ".cache";
        
        if (file_exists($cacheFile)) {
            $data = unserialize(file_get_contents($cacheFile));
            if ($data['expires'] > time()) {
                return $data['value'];
            }
            unlink($cacheFile);
        }
        
        return $default;
    }
    
    /**
     * Set cache value
     */
    private function setCacheValue(string $key, mixed $value, int $ttl = 3600): void
    {
        $cacheDir = __DIR__ . "/../../storage/cache";
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . "/" . md5($key) . ".cache";
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        file_put_contents($cacheFile, serialize($data));
    }
}
