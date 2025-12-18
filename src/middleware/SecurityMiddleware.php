<?php

declare(strict_types=1);

require_once __DIR__ . '/../security/SecurityManager.php';
require_once __DIR__ . '/../traits/DatabaseTrait.php';

/**
 * Comprehensive Security Middleware
 * Handles input sanitization, threat detection, and security headers
 */
class SecurityMiddleware
{
    use DatabaseTrait;
    
    private SecurityManager $security;
    private array $config;
    
    public function __construct()
    {
        $this->security = SecurityManager::getInstance();
        $this->config = [
            'sanitize_input' => true,
            'detect_threats' => true,
            'security_headers' => true,
            'log_requests' => true,
            'block_threats' => true
        ];
    }
    
    /**
     * Handle security middleware
     */
    public function handle(array $parameters = []): bool
    {
        // Set security headers
        if ($this->config['security_headers']) {
            $this->setSecurityHeaders();
        }
        
        // Sanitize input data
        if ($this->config['sanitize_input']) {
            $this->sanitizeInputData();
        }
        
        // Detect security threats (skip for authentication routes)
        if ($this->config['detect_threats'] && !$this->isAuthRoute()) {
            $threats = $this->detectThreats();

            if (!empty($threats) && $this->config['block_threats']) {
                $this->handleThreats($threats);
                return false;
            }
        }
        
        // Log request if enabled
        if ($this->config['log_requests']) {
            $this->logRequest();
        }
        
        return true;
    }
    
    /**
     * Set comprehensive security headers
     */
    private function setSecurityHeaders(): void
    {
        // Prevent XSS attacks
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Strict Transport Security (HTTPS only)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Content Security Policy
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://maps.googleapis.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://api.tranzak.net https://api.stripe.com",
            "frame-src 'self' https://js.stripe.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ];
        
        header('Content-Security-Policy: ' . implode('; ', $csp));
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy
        $permissions = [
            'geolocation=(self)',
            'microphone=()',
            'camera=()',
            'payment=(self)',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'speaker=(self)'
        ];
        
        header('Permissions-Policy: ' . implode(', ', $permissions));
        
        // Remove server information
        header_remove('X-Powered-By');
        header_remove('Server');
    }
    
    /**
     * Sanitize all input data
     */
    private function sanitizeInputData(): void
    {
        // Sanitize GET parameters
        if (!empty($_GET)) {
            $_GET = $this->sanitizeArray($_GET);
        }
        
        // Sanitize POST parameters
        if (!empty($_POST)) {
            $_POST = $this->sanitizeArray($_POST);
        }
        
        // Sanitize COOKIE data
        if (!empty($_COOKIE)) {
            $_COOKIE = $this->sanitizeArray($_COOKIE);
        }
        
        // Sanitize REQUEST data
        if (!empty($_REQUEST)) {
            $_REQUEST = $this->sanitizeArray($_REQUEST);
        }
    }
    
    /**
     * Sanitize array recursively
     */
    private function sanitizeArray(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $cleanKey = $this->security->sanitizeInput($key, 'alphanumeric');
            
            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->sanitizeArray($value);
            } else {
                // Determine sanitization type based on key
                $type = $this->getSanitizationType($key);
                $sanitized[$cleanKey] = $this->security->sanitizeInput($value, $type);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get sanitization type based on field name
     */
    private function getSanitizationType(string $key): string
    {
        $typeMap = [
            'email' => 'email',
            'url' => 'url',
            'phone' => 'phone',
            'price' => 'float',
            'amount' => 'float',
            'quantity' => 'int',
            'id' => 'int',
            'rating' => 'int',
            'filename' => 'filename'
        ];
        
        $key = strtolower($key);
        
        foreach ($typeMap as $pattern => $type) {
            if (strpos($key, $pattern) !== false) {
                return $type;
            }
        }
        
        return 'html'; // Default to HTML sanitization
    }
    
    /**
     * Check if current route is an authentication route
     */
    private function isAuthRoute(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $authRoutes = ['/login', '/register', '/forgot-password', '/reset-password'];

        foreach ($authRoutes as $route) {
            if (strpos($uri, $route) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect security threats in input
     */
    private function detectThreats(): array
    {
        $allInput = array_merge($_GET, $_POST, $_COOKIE);
        return $this->security->detectThreats($allInput);
    }
    
    /**
     * Handle detected threats
     */
    private function handleThreats(array $threats): void
    {
        // Log all threats
        foreach ($threats as $threat) {
            $this->security->logSecurityEvent($threat['type'], [
                'field' => $threat['field'],
                'value' => $threat['value'],
                'ip' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'uri' => $_SERVER['REQUEST_URI'] ?? ''
            ]);
        }
        
        // Block request
        http_response_code(403);
        
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Security violation detected',
                'message' => 'Your request has been blocked for security reasons.'
            ]);
        } else {
            $this->renderSecurityBlockPage();
        }
        
        exit;
    }
    
    /**
     * Log request for security monitoring
     */
    private function logRequest(): void
    {
        // Only log suspicious or important requests
        if ($this->shouldLogRequest()) {
            $logData = [
                'level' => 'info',
                'message' => 'Security request log',
                'context' => json_encode([
                    'ip' => $this->getClientIp(),
                    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                    'uri' => $_SERVER['REQUEST_URI'] ?? '',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'referer' => $_SERVER['HTTP_REFERER'] ?? '',
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'timestamp' => date('Y-m-d H:i:s')
                ]),
                'channel' => 'security',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            try {
                $this->insertRecord('logs', $logData);
            } catch (Exception $e) {
                error_log("Failed to log security request: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Check if request should be logged
     */
    private function shouldLogRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        
        // Log admin actions
        if (strpos($uri, '/admin/') !== false) {
            return true;
        }
        
        // Log authentication attempts
        if (in_array($uri, ['/login', '/register', '/forgot-password'])) {
            return true;
        }
        
        // Log API requests
        if (strpos($uri, '/api/') !== false) {
            return true;
        }
        
        // Log POST requests to sensitive endpoints
        if ($method === 'POST' && in_array($uri, ['/contact', '/checkout', '/orders'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get client IP address
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
     * Render security block page
     */
    private function renderSecurityBlockPage(): void
    {
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Access Blocked - Time2Eat</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 flex items-center justify-center min-h-screen'>
    <div class='bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center'>
        <div class='text-red-500 text-6xl mb-4'>🛡️</div>
        <h1 class='text-2xl font-bold text-gray-800 mb-4'>Access Blocked</h1>
        <p class='text-gray-600 mb-6'>
            Your request has been blocked for security reasons. If you believe this is an error, please contact support.
        </p>
        <div class='space-y-3'>
            <button onclick='history.back()' class='w-full bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 transition-colors'>
                Go Back
            </button>
            <a href='/' class='block w-full bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors'>
                Home Page
            </a>
        </div>
    </div>
</body>
</html>";
    }
}
