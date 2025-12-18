<?php

declare(strict_types=1);

/**
 * Security Bootstrap
 * Initializes all security features and middleware for Time2Eat
 */
class SecurityBootstrap
{
    private static bool $initialized = false;
    
    /**
     * Initialize all security features
     */
    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }
        
        // Define request start time for performance monitoring
        if (!defined('REQUEST_START_TIME')) {
            define('REQUEST_START_TIME', microtime(true));
        }
        
        // Initialize error reporting
        self::initializeErrorReporting();
        
        // Initialize security manager
        self::initializeSecurityManager();
        
        // Set security headers
        self::setSecurityHeaders();
        
        // Initialize session security
        self::initializeSessionSecurity();
        
        // Register security middleware
        self::registerSecurityMiddleware();
        
        // Initialize input sanitization
        self::initializeInputSanitization();
        
        self::$initialized = true;
    }
    
    /**
     * Initialize error reporting system
     */
    private static function initializeErrorReporting(): void
    {
        require_once __DIR__ . '/../services/ErrorReportingService.php';
        
        // Initialize error reporting service (sets up handlers)
        ErrorReportingService::getInstance();
        
        // Set error reporting level
        if (defined('APP_ENV') && APP_ENV === 'production') {
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }
    }
    
    /**
     * Initialize security manager
     */
    private static function initializeSecurityManager(): void
    {
        require_once __DIR__ . '/SecurityManager.php';
        
        // Initialize security manager singleton
        SecurityManager::getInstance();
    }
    
    /**
     * Set comprehensive security headers
     */
    private static function setSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        
        // Prevent XSS attacks
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Strict Transport Security (HTTPS only)
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Content Security Policy
        $csp = self::buildContentSecurityPolicy();
        header('Content-Security-Policy: ' . $csp);
        
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
        
        // Add custom security headers
        header('X-Security-Framework: Time2Eat-Security-v1.0');
        header('X-Request-ID: ' . uniqid('req_', true));
    }
    
    /**
     * Initialize secure session handling
     */
    private static function initializeSessionSecurity(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Configure secure session settings
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', self::isHttps() ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', '7200'); // 2 hours
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');
        
        // Use custom session handler for enhanced security
        session_set_save_handler(
            [self::class, 'sessionOpen'],
            [self::class, 'sessionClose'],
            [self::class, 'sessionRead'],
            [self::class, 'sessionWrite'],
            [self::class, 'sessionDestroy'],
            [self::class, 'sessionGc']
        );
        
        // Start session
        session_start();
        
        // Regenerate session ID periodically
        self::regenerateSessionId();
    }
    
    /**
     * Register security middleware
     */
    private static function registerSecurityMiddleware(): void
    {
        // Register global security middleware
        require_once __DIR__ . '/../middleware/SecurityMiddleware.php';
        require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
        
        // Apply security middleware to all requests
        $securityMiddleware = new SecurityMiddleware();
        $rateLimitMiddleware = new RateLimitMiddleware();
        
        // Execute middleware
        $securityMiddleware->handle();
        $rateLimitMiddleware->handle();
    }
    
    /**
     * Initialize input sanitization
     */
    private static function initializeInputSanitization(): void
    {
        // Sanitize superglobals
        $_GET = self::sanitizeArray($_GET);
        $_POST = self::sanitizeArray($_POST);
        $_COOKIE = self::sanitizeArray($_COOKIE);
        $_REQUEST = self::sanitizeArray($_REQUEST);
        
        // Filter server variables
        self::filterServerVariables();
    }
    
    /**
     * Build Content Security Policy
     */
    private static function buildContentSecurityPolicy(): string
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://maps.googleapis.com https://js.stripe.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://api.tranzak.net https://api.stripe.com https://maps.googleapis.com",
            "frame-src 'self' https://js.stripe.com https://www.google.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "upgrade-insecure-requests"
        ];
        
        return implode('; ', $policies);
    }
    
    /**
     * Check if connection is HTTPS
     */
    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Sanitize array recursively
     */
    private static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $cleanKey = preg_replace('/[^a-zA-Z0-9_\-\[\]]/', '', $key);
            
            if (is_array($value)) {
                $sanitized[$cleanKey] = self::sanitizeArray($value);
            } else {
                $sanitized[$cleanKey] = self::sanitizeValue($value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize individual value
     */
    private static function sanitizeValue($value): string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        // Basic XSS prevention
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $value;
    }
    
    /**
     * Filter dangerous server variables
     */
    private static function filterServerVariables(): void
    {
        $dangerousVars = ['PHP_SELF', 'PATH_INFO', 'PATH_TRANSLATED'];
        
        foreach ($dangerousVars as $var) {
            if (isset($_SERVER[$var])) {
                $_SERVER[$var] = htmlspecialchars($_SERVER[$var], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
    }
    
    /**
     * Regenerate session ID for security
     */
    private static function regenerateSessionId(): void
    {
        $lastRegeneration = $_SESSION['last_regeneration'] ?? 0;
        
        // Regenerate every 30 minutes
        if (time() - $lastRegeneration > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    // Custom session handlers for enhanced security
    public static function sessionOpen($savePath, $sessionName): bool
    {
        return true;
    }
    
    public static function sessionClose(): bool
    {
        return true;
    }
    
    public static function sessionRead($sessionId): string
    {
        try {
            require_once __DIR__ . '/../traits/DatabaseTrait.php';
            
            $db = new class {
                use DatabaseTrait;
            };
            
            $session = $db->fetchOne(
                "SELECT payload FROM user_sessions WHERE id = ? AND is_active = 1",
                [$sessionId]
            );
            
            return $session['payload'] ?? '';
        } catch (Exception $e) {
            error_log("Session read error: " . $e->getMessage());
            return '';
        }
    }
    
    public static function sessionWrite($sessionId, $data): bool
    {
        try {
            require_once __DIR__ . '/../traits/DatabaseTrait.php';
            
            $db = new class {
                use DatabaseTrait;
            };
            
            $userId = $_SESSION['user_id'] ?? 0;
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $db->query(
                "INSERT INTO user_sessions (id, user_id, ip_address, user_agent, payload, last_activity)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                 payload = VALUES(payload),
                 last_activity = VALUES(last_activity),
                 updated_at = NOW()",
                [$sessionId, $userId, $ip, $userAgent, $data, time()]
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Session write error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function sessionDestroy($sessionId): bool
    {
        try {
            require_once __DIR__ . '/../traits/DatabaseTrait.php';
            
            $db = new class {
                use DatabaseTrait;
            };
            
            $db->query(
                "UPDATE user_sessions SET is_active = 0 WHERE id = ?",
                [$sessionId]
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Session destroy error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function sessionGc($maxLifetime): int
    {
        try {
            require_once __DIR__ . '/../traits/DatabaseTrait.php';
            
            $db = new class {
                use DatabaseTrait;
            };
            
            $result = $db->query(
                "DELETE FROM user_sessions WHERE last_activity < ?",
                [time() - $maxLifetime]
            );
            
            return $result ? 1 : 0;
        } catch (Exception $e) {
            error_log("Session GC error: " . $e->getMessage());
            return 0;
        }
    }
}
