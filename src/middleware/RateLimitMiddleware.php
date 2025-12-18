<?php

declare(strict_types=1);

require_once __DIR__ . '/../security/SecurityManager.php';
require_once __DIR__ . '/../traits/DatabaseTrait.php';

/**
 * Rate Limiting Middleware
 * Prevents abuse by limiting requests per time window
 */
class RateLimitMiddleware
{
    use DatabaseTrait;
    
    private SecurityManager $security;
    private array $config;
    
    public function __construct()
    {
        $this->security = SecurityManager::getInstance();
        $this->config = [
            'default_limit' => 60,
            'default_window' => 3600,
            'headers' => true,
            'skip_successful_requests' => false
        ];
    }
    
    /**
     * Handle rate limiting
     */
    public function handle(array $parameters = []): bool
    {
        $action = $parameters['action'] ?? $this->detectAction();
        $identifier = $parameters['identifier'] ?? null;
        
        // Check if rate limiting should be applied
        if ($this->shouldSkipRateLimit($action)) {
            return true;
        }
        
        // Check rate limit
        if (!$this->security->checkRateLimit($action, $identifier)) {
            $this->handleRateLimitExceeded($action);
            return false;
        }
        
        // Add rate limit headers
        if ($this->config['headers']) {
            $this->addRateLimitHeaders($action, $identifier);
        }
        
        return true;
    }
    
    /**
     * Detect action from current request
     */
    private function detectAction(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Map common endpoints to actions
        $actionMap = [
            'POST:/login' => 'login_attempts',
            'POST:/register' => 'registration',
            'POST:/forgot-password' => 'password_reset',
            'POST:/contact' => 'contact_form',
            'GET:/search' => 'search_requests',
            'POST:/api/' => 'api_requests',
            'GET:/api/' => 'api_requests'
        ];
        
        $key = $method . ':' . $uri;
        
        foreach ($actionMap as $pattern => $action) {
            if (strpos($key, $pattern) !== false) {
                return $action;
            }
        }
        
        // Default action based on method
        return strtolower($method) . '_requests';
    }
    
    /**
     * Check if rate limiting should be skipped
     */
    private function shouldSkipRateLimit(string $action): bool
    {
        // Skip for admin users
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return true;
        }
        
        // Skip for whitelisted IPs
        $whitelistedIps = ['127.0.0.1', '::1'];
        $clientIp = $this->getClientIp();
        
        if (in_array($clientIp, $whitelistedIps)) {
            return true;
        }
        
        // Skip for certain actions in development
        if (defined('APP_ENV') && APP_ENV === 'development') {
            $skipActions = ['search_requests', 'get_requests'];
            if (in_array($action, $skipActions)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle rate limit exceeded
     */
    private function handleRateLimitExceeded(string $action): void
    {
        $retryAfter = $this->getRetryAfter($action);
        
        // Set headers
        http_response_code(429);
        header('Retry-After: ' . $retryAfter);
        header('X-RateLimit-Limit: ' . $this->getRateLimit($action));
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . (time() + $retryAfter));
        
        // Log the event
        $this->security->logSecurityEvent('rate_limit_exceeded', [
            'action' => $action,
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'retry_after' => $retryAfter
        ]);
        
        // Return appropriate response
        if ($this->isAjaxRequest()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter
            ], 429);
        } else {
            $this->renderRateLimitPage($retryAfter);
        }
    }
    
    /**
     * Add rate limit headers
     */
    private function addRateLimitHeaders(string $action, ?string $identifier): void
    {
        $limit = $this->getRateLimit($action);
        $window = $this->getRateWindow($action);
        $remaining = $this->getRemainingRequests($action, $identifier);
        $resetTime = time() + $window;
        
        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . max(0, $remaining));
        header('X-RateLimit-Reset: ' . $resetTime);
        header('X-RateLimit-Window: ' . $window);
    }
    
    /**
     * Get rate limit for action
     */
    private function getRateLimit(string $action): int
    {
        $limits = [
            'login_attempts' => 5,
            'registration' => 3,
            'password_reset' => 3,
            'contact_form' => 5,
            'search_requests' => 200,
            'api_requests' => 100,
            'get_requests' => 300,
            'post_requests' => 60
        ];
        
        return $limits[$action] ?? $this->config['default_limit'];
    }
    
    /**
     * Get rate window for action
     */
    private function getRateWindow(string $action): int
    {
        $windows = [
            'login_attempts' => 900,  // 15 minutes
            'registration' => 3600,   // 1 hour
            'password_reset' => 3600, // 1 hour
            'contact_form' => 3600,   // 1 hour
            'search_requests' => 3600, // 1 hour
            'api_requests' => 3600,   // 1 hour
            'get_requests' => 3600,   // 1 hour
            'post_requests' => 3600   // 1 hour
        ];
        
        return $windows[$action] ?? $this->config['default_window'];
    }
    
    /**
     * Get retry after time
     */
    private function getRetryAfter(string $action): int
    {
        return $this->getRateWindow($action);
    }
    
    /**
     * Get remaining requests
     */
    private function getRemainingRequests(string $action, ?string $identifier): int
    {
        $limit = $this->getRateLimit($action);
        $window = $this->getRateWindow($action);
        
        $identifier = $identifier ?? $this->getClientIdentifier();
        $key = $action . ':' . $identifier;
        
        $since = date('Y-m-d H:i:s', time() - $window);
        
        try {
            $result = $this->fetchOne(
                "SELECT COUNT(*) as count FROM rate_limits WHERE rate_key = ? AND created_at >= ?",
                [$key, $since]
            );
            
            $used = (int)($result['count'] ?? 0);
            return max(0, $limit - $used);
        } catch (Exception $e) {
            return $limit; // Assume no usage on error
        }
    }
    
    /**
     * Get client identifier
     */
    private function getClientIdentifier(): string
    {
        $ip = $this->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Use user ID if authenticated
        if (isset($_SESSION['user_id'])) {
            return 'user:' . $_SESSION['user_id'];
        }
        
        // Use IP + User Agent hash for anonymous users
        return 'anon:' . md5($ip . $userAgent);
    }
    
    /**
     * Get client IP
     */
    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Return JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Render rate limit exceeded page
     */
    private function renderRateLimitPage(int $retryAfter): void
    {
        $minutes = ceil($retryAfter / 60);
        
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Rate Limit Exceeded - Time2Eat</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 flex items-center justify-center min-h-screen'>
    <div class='bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center'>
        <div class='text-red-500 text-6xl mb-4'>⚠️</div>
        <h1 class='text-2xl font-bold text-gray-800 mb-4'>Rate Limit Exceeded</h1>
        <p class='text-gray-600 mb-6'>
            You have made too many requests. Please wait {$minutes} minute(s) before trying again.
        </p>
        <button onclick='history.back()' class='bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 transition-colors'>
            Go Back
        </button>
    </div>
    <script>
        setTimeout(function() {
            location.reload();
        }, {$retryAfter}000);
    </script>
</body>
</html>";
        exit;
    }
}
