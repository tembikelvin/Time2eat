<?php

declare(strict_types=1);

/**
 * Security Audit Trait
 * Provides comprehensive security auditing and logging capabilities
 */
trait SecurityAuditTrait
{
    /**
     * Log user action for security audit
     */
    protected function logAction(string $action, array $context = []): void
    {
        try {
            $logData = [
                'user_id' => $_SESSION['user_id'] ?? null,
                'action' => $action,
                'resource_type' => $context['resource_type'] ?? null,
                'resource_id' => $context['resource_id'] ?? null,
                'ip_address' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'request_data' => json_encode($this->sanitizeLogData($_POST)),
                'response_status' => http_response_code(),
                'execution_time' => $this->getExecutionTime(),
                'memory_usage' => memory_get_usage(true),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->insert('action_logs', $logData);
        } catch (Exception $e) {
            error_log("Failed to log action: " . $e->getMessage());
        }
    }
    
    /**
     * Log security event
     */
    protected function logSecurityEvent(string $eventType, string $description, array $additionalData = [], string $severity = 'medium'): void
    {
        try {
            $eventData = [
                'event_type' => $eventType,
                'severity' => $severity,
                'description' => $description,
                'ip_address' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'user_id' => $_SESSION['user_id'] ?? null,
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'request_data' => json_encode($this->sanitizeLogData($_POST)),
                'additional_data' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->insert('security_events', $eventData);
            
            // Send immediate alert for critical events
            if ($severity === 'critical') {
                $this->sendSecurityAlert($eventType, $description, $additionalData);
            }
        } catch (Exception $e) {
            error_log("Failed to log security event: " . $e->getMessage());
        }
    }
    
    /**
     * Log failed login attempt
     */
    protected function logFailedLogin(string $email, string $reason = 'invalid_credentials'): void
    {
        try {
            $attemptData = [
                'email' => $email,
                'ip_address' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'attempted_at' => date('Y-m-d H:i:s')
            ];
            
            $this->insert('failed_login_attempts', $attemptData);
            
            // Check if IP should be blocked
            $this->checkAndBlockSuspiciousIp($this->getClientIp());
            
            // Log as security event
            $this->logSecurityEvent('failed_login', "Failed login attempt for email: {$email}", [
                'reason' => $reason,
                'email' => $email
            ]);
        } catch (Exception $e) {
            error_log("Failed to log failed login: " . $e->getMessage());
        }
    }
    
    /**
     * Log successful login
     */
    protected function logSuccessfulLogin(int $userId, string $email): void
    {
        try {
            $this->logAction('user_login', [
                'resource_type' => 'user',
                'resource_id' => $userId
            ]);
            
            // Update user's last login info
            $this->query(
                "UPDATE users SET last_login_at = NOW(), last_login_ip = ?, failed_login_attempts = 0 WHERE id = ?",
                [$this->getClientIp(), $userId]
            );

            // Clear failed login attempts for this email
            $this->query(
                "DELETE FROM failed_login_attempts WHERE email = ?",
                [$email]
            );
        } catch (Exception $e) {
            error_log("Failed to log successful login: " . $e->getMessage());
        }
    }
    
    /**
     * Check for suspicious activity patterns
     */
    protected function detectSuspiciousActivity(): array
    {
        $suspiciousPatterns = [];
        $ip = $this->getClientIp();
        $userId = $_SESSION['user_id'] ?? null;
        
        try {
            // Check for rapid requests from same IP
            $rapidRequests = $this->fetchOne(
                "SELECT COUNT(*) as count FROM action_logs 
                 WHERE ip_address = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
                [$ip]
            );
            
            if ($rapidRequests['count'] > 30) {
                $suspiciousPatterns[] = [
                    'type' => 'rapid_requests',
                    'description' => 'Unusually high request rate',
                    'count' => $rapidRequests['count']
                ];
            }
            
            // Check for multiple failed logins
            $failedLogins = $this->fetchOne(
                "SELECT COUNT(*) as count FROM failed_login_attempts 
                 WHERE ip_address = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                [$ip]
            );
            
            if ($failedLogins['count'] > 5) {
                $suspiciousPatterns[] = [
                    'type' => 'multiple_failed_logins',
                    'description' => 'Multiple failed login attempts',
                    'count' => $failedLogins['count']
                ];
            }
            
            // Check for unusual access patterns if user is logged in
            if ($userId) {
                $unusualAccess = $this->fetchOne(
                    "SELECT COUNT(DISTINCT ip_address) as ip_count 
                     FROM action_logs 
                     WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                    [$userId]
                );
                
                if ($unusualAccess['ip_count'] > 3) {
                    $suspiciousPatterns[] = [
                        'type' => 'multiple_ip_access',
                        'description' => 'Access from multiple IP addresses',
                        'ip_count' => $unusualAccess['ip_count']
                    ];
                }
            }
            
        } catch (Exception $e) {
            error_log("Failed to detect suspicious activity: " . $e->getMessage());
        }
        
        return $suspiciousPatterns;
    }
    
    /**
     * Audit user permissions
     */
    protected function auditUserPermissions(int $userId, string $action, string $resource = null): bool
    {
        try {
            $user = $this->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                $this->logSecurityEvent('unauthorized_access', "Access attempt by non-existent user ID: {$userId}", [], 'high');
                return false;
            }
            
            // Log permission check
            $this->logAction('permission_check', [
                'resource_type' => 'permission',
                'checked_action' => $action,
                'checked_resource' => $resource,
                'user_role' => $user['role']
            ]);
            
            // Check if user account is active
            if ($user['status'] !== 'active') {
                $this->logSecurityEvent('inactive_user_access', "Access attempt by inactive user: {$user['email']}", [
                    'user_status' => $user['status']
                ], 'medium');
                return false;
            }
            
            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                $this->logSecurityEvent('locked_user_access', "Access attempt by locked user: {$user['email']}", [
                    'locked_until' => $user['locked_until']
                ], 'medium');
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to audit user permissions: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check and block suspicious IP
     */
    private function checkAndBlockSuspiciousIp(string $ip): void
    {
        try {
            // Count recent failed attempts from this IP
            $recentAttempts = $this->fetchOne(
                "SELECT COUNT(*) as count FROM failed_login_attempts 
                 WHERE ip_address = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                [$ip]
            );
            
            if ($recentAttempts['count'] >= 10) {
                // Add to blacklist temporarily
                $this->query(
                    "INSERT IGNORE INTO ip_access_control (ip_address, access_type, reason, expires_at)
                     VALUES (?, 'blacklist', 'Automated block - multiple failed logins', DATE_ADD(NOW(), INTERVAL 24 HOUR))",
                    [$ip]
                );
                
                $this->logSecurityEvent('ip_auto_blocked', "IP automatically blocked due to suspicious activity: {$ip}", [
                    'failed_attempts' => $recentAttempts['count'],
                    'block_duration' => '24 hours'
                ], 'high');
            }
        } catch (Exception $e) {
            error_log("Failed to check suspicious IP: " . $e->getMessage());
        }
    }
    
    /**
     * Send security alert
     */
    private function sendSecurityAlert(string $eventType, string $description, array $additionalData): void
    {
        try {
            require_once __DIR__ . '/../services/ErrorReportingService.php';
            $errorReporting = ErrorReportingService::getInstance();
            
            $errorReporting->logCustomError("Security Alert: {$eventType}", [
                'description' => $description,
                'additional_data' => $additionalData,
                'ip' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'timestamp' => date('Y-m-d H:i:s')
            ], 'critical');
        } catch (Exception $e) {
            error_log("Failed to send security alert: " . $e->getMessage());
        }
    }
    
    /**
     * Sanitize data for logging
     */
    private function sanitizeLogData(array $data): array
    {
        $sanitized = [];
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'captcha', 'credit_card', 'cvv'];
        
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeLogData($value);
            } else {
                $sanitized[$key] = is_string($value) ? substr($value, 0, 255) : $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get execution time
     */
    private function getExecutionTime(): float
    {
        if (defined('REQUEST_START_TIME')) {
            return round((microtime(true) - REQUEST_START_TIME) * 1000, 3);
        }
        return 0.0;
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
}
