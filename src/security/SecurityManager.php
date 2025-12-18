<?php

declare(strict_types=1);

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

/**
 * Comprehensive Security Manager for Time2Eat
 * Handles CAPTCHA, rate limiting, input sanitization, and security logging
 */
class SecurityManager
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    private static ?self $instance = null;
    private array $config;
    private array $rateLimits = [];
    
    private function __construct()
    {
        $this->config = [
            'captcha' => [
                'enabled' => true,
                'length' => 5,
                'width' => 120,
                'height' => 40,
                'font_size' => 16,
                'expiry' => 300, // 5 minutes
            ],
            'rate_limiting' => [
                'login_attempts' => ['max' => 5, 'window' => 900], // 5 attempts per 15 minutes
                'registration' => ['max' => 3, 'window' => 3600], // 3 registrations per hour
                'password_reset' => ['max' => 3, 'window' => 3600], // 3 resets per hour
                'api_requests' => ['max' => 100, 'window' => 3600], // 100 API requests per hour
                'search_requests' => ['max' => 200, 'window' => 3600], // 200 searches per hour
                'contact_form' => ['max' => 5, 'window' => 3600], // 5 contact submissions per hour
            ],
            'security' => [
                'max_login_attempts' => 5,
                'lockout_duration' => 900, // 15 minutes
                'session_timeout' => 7200, // 2 hours
                'csrf_token_expiry' => 3600, // 1 hour
                'password_min_length' => 8,
                'require_special_chars' => true,
            ]
        ];
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Generate CAPTCHA image and token
     */
    public function generateCaptcha(): array
    {
        $code = $this->generateCaptchaCode();
        $token = bin2hex(random_bytes(16));
        
        // Store CAPTCHA in session with expiry
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['captcha'] = [
            'code' => strtolower($code),
            'token' => $token,
            'expires' => time() + $this->config['captcha']['expiry']
        ];



        // Generate image
        $image = $this->createCaptchaImage($code);

        return [
            'token' => $token,
            'image' => $image,
            'code' => $code  // Add code for display purposes
        ];
    }
    
    /**
     * Validate CAPTCHA
     */
    public function validateCaptcha(string $userInput, string $token): bool
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $captcha = $_SESSION['captcha'] ?? null;

        if (!$captcha || $captcha['token'] !== $token) {
            return false;
        }

        if (time() > $captcha['expires']) {
            unset($_SESSION['captcha']);
            return false;
        }

        $isValid = strtolower(trim($userInput)) === $captcha['code'];

        // Clear CAPTCHA after validation attempt
        unset($_SESSION['captcha']);

        return $isValid;
    }
    
    /**
     * Check rate limiting
     */
    public function checkRateLimit(string $action, string $identifier = null): bool
    {
        if (!isset($this->config['rate_limiting'][$action])) {
            return true; // No limit configured
        }
        
        $limit = $this->config['rate_limiting'][$action];
        $identifier = $identifier ?? $this->getClientIdentifier();
        $key = $action . ':' . $identifier;
        
        // Clean old entries
        $this->cleanRateLimitData($key, $limit['window']);
        
        // Check current count
        $currentCount = $this->getRateLimitCount($key, $limit['window']);
        
        if ($currentCount >= $limit['max']) {
            $this->logSecurityEvent('rate_limit_exceeded', [
                'action' => $action,
                'identifier' => $identifier,
                'current_count' => $currentCount,
                'limit' => $limit['max']
            ]);
            return false;
        }
        
        // Record this attempt
        $this->recordRateLimitAttempt($key);
        
        return true;
    }
    
    /**
     * Sanitize input data
     */
    public function sanitizeInput(mixed $data, string $type = 'string'): mixed
    {
        if (is_array($data)) {
            return array_map(fn($item) => $this->sanitizeInput($item, $type), $data);
        }
        
        if (!is_string($data)) {
            return $data;
        }
        
        // Basic sanitization
        $data = trim($data);
        
        return match($type) {
            'email' => filter_var($data, FILTER_SANITIZE_EMAIL),
            'url' => filter_var($data, FILTER_SANITIZE_URL),
            'int' => (int) filter_var($data, FILTER_SANITIZE_NUMBER_INT),
            'float' => (float) filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'html' => htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'sql' => $this->escapeSqlString($data),
            'filename' => preg_replace('/[^a-zA-Z0-9._-]/', '', $data),
            'alphanumeric' => preg_replace('/[^a-zA-Z0-9]/', '', $data),
            'phone' => preg_replace('/[^0-9+\-\s()]/', '', $data),
            default => htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8')
        };
    }
    
    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        $minLength = $this->config['security']['password_min_length'];
        
        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if ($this->config['security']['require_special_chars'] && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        // Check against common passwords
        if ($this->isCommonPassword($password)) {
            $errors[] = "Password is too common. Please choose a more secure password";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculatePasswordStrength($password)
        ];
    }
    
    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $logData = [
            'level' => 'warning',
            'message' => "Security event: {$event}",
            'context' => json_encode(array_merge($context, [
                'ip' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'timestamp' => date('Y-m-d H:i:s'),
                'session_id' => session_id()
            ])),
            'channel' => 'security',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            // Only log to error log for now to avoid database issues
            error_log("Security Event: $event - " . json_encode($context));

            // Try to insert into database, but don't fail if it doesn't work
            try {
                $this->insertRecord('logs', $logData);
            } catch (Exception $dbError) {
                error_log("Database logging failed: " . $dbError->getMessage());
            }

            // Send email alert for critical security events
            if (in_array($event, ['multiple_failed_logins', 'sql_injection_attempt', 'xss_attempt'])) {
                $this->sendSecurityAlert($event, $context);
            }
        } catch (Exception $e) {
            error_log("Failed to log security event: " . $e->getMessage());
        }
    }
    
    /**
     * Generate secure CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(string $token): bool
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $tokenTime = $_SESSION['csrf_token_time'] ?? 0;
        
        // Check if token exists and hasn't expired
        if (empty($sessionToken) || (time() - $tokenTime) > $this->config['security']['csrf_token_expiry']) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * Detect potential security threats
     */
    public function detectThreats(array $input): array
    {
        $threats = [];
        
        foreach ($input as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            // SQL Injection detection
            if ($this->detectSqlInjection($value)) {
                $threats[] = [
                    'type' => 'sql_injection',
                    'field' => $key,
                    'value' => substr($value, 0, 100)
                ];
            }
            
            // XSS detection
            if ($this->detectXss($value)) {
                $threats[] = [
                    'type' => 'xss_attempt',
                    'field' => $key,
                    'value' => substr($value, 0, 100)
                ];
            }
            
            // Path traversal detection
            if ($this->detectPathTraversal($value)) {
                $threats[] = [
                    'type' => 'path_traversal',
                    'field' => $key,
                    'value' => substr($value, 0, 100)
                ];
            }
        }
        
        if (!empty($threats)) {
            $this->logSecurityEvent('threat_detected', ['threats' => $threats]);
        }
        
        return $threats;
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
     * Get client identifier for rate limiting
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
     * Generate CAPTCHA code
     */
    private function generateCaptchaCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        
        for ($i = 0; $i < $this->config['captcha']['length']; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $code;
    }
    
    /**
     * Create CAPTCHA image
     */
    private function createCaptchaImage(string $code): string
    {
        $width = $this->config['captcha']['width'];
        $height = $this->config['captcha']['height'];
        
        $image = imagecreate($width, $height);
        
        // Colors
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $lineColor = imagecolorallocate($image, 200, 200, 200);
        
        // Add noise lines
        for ($i = 0; $i < 5; $i++) {
            imageline($image, 0, random_int(0, $height), $width, random_int(0, $height), $lineColor);
        }
        
        // Add text
        $fontSize = $this->config['captcha']['font_size'];
        $x = ($width - strlen($code) * $fontSize * 0.6) / 2;
        $y = ($height + $fontSize) / 2;
        
        imagestring($image, 5, (int)$x, (int)($y - $fontSize), $code, $textColor);
        
        // Convert to base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * Clean old rate limit data
     */
    private function cleanRateLimitData(string $key, int $window): void
    {
        $cutoff = date('Y-m-d H:i:s', time() - $window);

        try {
            $this->query(
                "DELETE FROM rate_limits WHERE rate_key = ? AND created_at < ?",
                [$key, $cutoff]
            );
        } catch (Exception $e) {
            error_log("Failed to clean rate limit data: " . $e->getMessage());
        }
    }

    /**
     * Get current rate limit count
     */
    private function getRateLimitCount(string $key, int $window): int
    {
        $since = date('Y-m-d H:i:s', time() - $window);

        try {
            $result = $this->fetchOne(
                "SELECT COUNT(*) as count FROM rate_limits WHERE rate_key = ? AND created_at >= ?",
                [$key, $since]
            );

            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            error_log("Failed to get rate limit count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Record rate limit attempt
     */
    private function recordRateLimitAttempt(string $key): void
    {
        try {
            $this->insertRecord('rate_limits', [
                'rate_key' => $key,
                'ip_address' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to record rate limit attempt: " . $e->getMessage());
        }
    }

    /**
     * Escape SQL string
     */
    private function escapeSqlString(string $data): string
    {
        return addslashes($data);
    }

    /**
     * Check if password is common
     */
    private function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123', 'password123',
            'admin', 'letmein', 'welcome', 'monkey', '1234567890', 'password1'
        ];

        return in_array(strtolower($password), $commonPasswords);
    }

    /**
     * Calculate password strength score
     */
    private function calculatePasswordStrength(string $password): int
    {
        $score = 0;
        $length = strlen($password);

        // Length score
        if ($length >= 8) $score += 25;
        if ($length >= 12) $score += 25;

        // Character variety
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 10;
        if (preg_match('/[0-9]/', $password)) $score += 10;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 20;

        return min(100, $score);
    }

    /**
     * Send security alert email
     */
    private function sendSecurityAlert(string $event, array $context): void
    {
        try {
            $adminEmail = $this->getAdminEmail();
            if (!$adminEmail) {
                return;
            }

            $subject = "Security Alert - Time2Eat";
            $message = "Security event detected: {$event}\n\n";
            $message .= "Details:\n" . print_r($context, true);
            $message .= "\nIP: " . $this->getClientIp();
            $message .= "\nTime: " . date('Y-m-d H:i:s');

            $headers = [
                'From: security@time2eat.com',
                'Reply-To: security@time2eat.com',
                'X-Priority: 1',
                'Content-Type: text/plain; charset=UTF-8'
            ];

            mail($adminEmail, $subject, $message, implode("\r\n", $headers));
        } catch (Exception $e) {
            error_log("Failed to send security alert: " . $e->getMessage());
        }
    }

    /**
     * Get admin email for alerts
     */
    private function getAdminEmail(): ?string
    {
        try {
            $result = $this->fetchOne(
                "SELECT value FROM site_settings WHERE `key` = 'admin_email' AND is_active = 1"
            );

            return $result['value'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Detect SQL injection attempts
     */
    private function detectSqlInjection(string $input): bool
    {
        $patterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\bor\b.*=.*)/i',
            '/(\band\b.*=.*)/i',
            '/(\'.*or.*\'.*=.*\')/i',
            '/(\".*or.*\".*=.*\")/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect XSS attempts
     */
    private function detectXss(string $input): bool
    {
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/i',
            '/<embed\b[^>]*>/i',
            '/<link\b[^>]*>/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect path traversal attempts
     */
    private function detectPathTraversal(string $input): bool
    {
        $patterns = [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/%2e%2e%2f/',
            '/%2e%2e\\\\/',
            '/\.\.\%2f/',
            '/\.\.\%5c/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
