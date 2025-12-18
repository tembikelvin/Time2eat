<?php

declare(strict_types=1);

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

/**
 * Comprehensive Error Reporting Service
 * Handles error logging, email notifications, and error tracking
 */
class ErrorReportingService
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    private static ?self $instance = null;
    private array $config;
    private array $errorCounts = [];
    
    private function __construct()
    {
        $this->config = [
            'email_notifications' => true,
            'admin_email' => $this->getAdminEmail(),
            'error_threshold' => 10, // Send email after 10 similar errors
            'time_window' => 3600, // 1 hour window
            'log_to_file' => true,
            'log_to_database' => true,
            'include_stack_trace' => true,
            'max_email_per_hour' => 5
        ];
        
        // Set custom error and exception handlers
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        // Don't handle suppressed errors
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorData = [
            'type' => 'php_error',
            'severity' => $this->getSeverityName($severity),
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'trace' => $this->config['include_stack_trace'] ? debug_backtrace() : null,
            'context' => $this->getErrorContext()
        ];
        
        $this->logError($errorData);
        
        // Don't prevent default error handling
        return false;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public function handleException(Throwable $exception): void
    {
        $errorData = [
            'type' => 'uncaught_exception',
            'severity' => 'critical',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $this->config['include_stack_trace'] ? $exception->getTrace() : null,
            'context' => $this->getErrorContext(),
            'exception_class' => get_class($exception)
        ];
        
        $this->logError($errorData);
        
        // Show user-friendly error page
        $this->showErrorPage($errorData);
    }
    
    /**
     * Handle fatal errors
     */
    public function handleFatalError(): void
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorData = [
                'type' => 'fatal_error',
                'severity' => 'critical',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'context' => $this->getErrorContext()
            ];
            
            $this->logError($errorData);
        }
    }
    
    /**
     * Log custom error
     */
    public function logCustomError(string $message, array $context = [], string $severity = 'error'): void
    {
        $errorData = [
            'type' => 'custom_error',
            'severity' => $severity,
            'message' => $message,
            'context' => array_merge($this->getErrorContext(), $context),
            'trace' => $this->config['include_stack_trace'] ? debug_backtrace() : null
        ];
        
        $this->logError($errorData);
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $errorData = [
            'type' => 'security_event',
            'severity' => 'warning',
            'message' => "Security event: {$event}",
            'context' => array_merge($this->getErrorContext(), $context),
            'event_type' => $event
        ];
        
        $this->logError($errorData);
    }
    
    /**
     * Main error logging method
     */
    private function logError(array $errorData): void
    {
        $errorData['timestamp'] = date('Y-m-d H:i:s');
        $errorData['request_id'] = $this->generateRequestId();
        
        // Log to database
        if ($this->config['log_to_database']) {
            $this->logToDatabase($errorData);
        }
        
        // Log to file
        if ($this->config['log_to_file']) {
            $this->logToFile($errorData);
        }
        
        // Send email notification if needed
        if ($this->config['email_notifications'] && $this->shouldSendEmail($errorData)) {
            $this->sendEmailNotification($errorData);
        }
    }
    
    /**
     * Log error to database
     */
    private function logToDatabase(array $errorData): void
    {
        try {
            $logEntry = [
                'level' => $this->mapSeverityToLogLevel($errorData['severity']),
                'message' => $errorData['message'],
                'context' => json_encode([
                    'type' => $errorData['type'],
                    'file' => $errorData['file'] ?? '',
                    'line' => $errorData['line'] ?? 0,
                    'trace' => $errorData['trace'] ?? null,
                    'context' => $errorData['context'] ?? [],
                    'request_id' => $errorData['request_id']
                ]),
                'channel' => 'error',
                'created_at' => $errorData['timestamp']
            ];
            
            $this->insertRecord('logs', $logEntry);
        } catch (Exception $e) {
            // Fallback to file logging if database fails
            error_log("Failed to log to database: " . $e->getMessage());
            $this->logToFile($errorData);
        }
    }
    
    /**
     * Log error to file
     */
    private function logToFile(array $errorData): void
    {
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/error-' . date('Y-m-d') . '.log';
        
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\n",
            $errorData['timestamp'],
            strtoupper($errorData['severity']),
            $errorData['message'],
            $errorData['file'] ?? 'unknown',
            $errorData['line'] ?? 0
        );
        
        if (!empty($errorData['context'])) {
            $logMessage .= "Context: " . json_encode($errorData['context']) . "\n";
        }
        
        if (!empty($errorData['trace'])) {
            $logMessage .= "Stack trace:\n" . $this->formatStackTrace($errorData['trace']) . "\n";
        }
        
        $logMessage .= str_repeat('-', 80) . "\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Check if email should be sent
     */
    private function shouldSendEmail(array $errorData): bool
    {
        if (!$this->config['admin_email']) {
            return false;
        }
        
        // Don't send emails for low severity errors
        if (in_array($errorData['severity'], ['notice', 'info', 'debug'])) {
            return false;
        }
        
        // Check email rate limiting
        if (!$this->checkEmailRateLimit()) {
            return false;
        }
        
        // Check error threshold
        $errorKey = md5($errorData['message'] . ($errorData['file'] ?? '') . ($errorData['line'] ?? ''));
        $this->errorCounts[$errorKey] = ($this->errorCounts[$errorKey] ?? 0) + 1;
        
        return $this->errorCounts[$errorKey] >= $this->config['error_threshold'] ||
               in_array($errorData['severity'], ['critical', 'emergency']);
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification(array $errorData): void
    {
        try {
            $subject = sprintf(
                "[Time2Eat] %s Error: %s",
                ucfirst($errorData['severity']),
                substr($errorData['message'], 0, 50) . '...'
            );
            
            $body = $this->formatEmailBody($errorData);
            
            $headers = [
                'From: errors@time2eat.com',
                'Reply-To: noreply@time2eat.com',
                'X-Priority: 1',
                'Content-Type: text/html; charset=UTF-8'
            ];
            
            mail($this->config['admin_email'], $subject, $body, implode("\r\n", $headers));
            
            // Record email sent
            $this->recordEmailSent();
            
        } catch (Exception $e) {
            error_log("Failed to send error notification email: " . $e->getMessage());
        }
    }
    
    /**
     * Format email body
     */
    private function formatEmailBody(array $errorData): string
    {
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .error-box { background: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin: 10px 0; }
                .context-box { background: #e9ecef; padding: 10px; margin: 10px 0; border-radius: 4px; }
                .trace { background: #f1f3f4; padding: 10px; font-family: monospace; font-size: 12px; overflow-x: auto; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h2>Error Report - Time2Eat</h2>
            
            <div class='error-box'>
                <h3>Error Details</h3>
                <table>
                    <tr><th>Type</th><td>" . htmlspecialchars($errorData['type']) . "</td></tr>
                    <tr><th>Severity</th><td>" . htmlspecialchars($errorData['severity']) . "</td></tr>
                    <tr><th>Message</th><td>" . htmlspecialchars($errorData['message']) . "</td></tr>
                    <tr><th>File</th><td>" . htmlspecialchars($errorData['file'] ?? 'N/A') . "</td></tr>
                    <tr><th>Line</th><td>" . ($errorData['line'] ?? 'N/A') . "</td></tr>
                    <tr><th>Time</th><td>" . $errorData['timestamp'] . "</td></tr>
                    <tr><th>Request ID</th><td>" . ($errorData['request_id'] ?? $this->generateRequestId()) . "</td></tr>
                </table>
            </div>";
        
        if (!empty($errorData['context'])) {
            $html .= "
            <div class='context-box'>
                <h3>Context Information</h3>
                <pre>" . htmlspecialchars(json_encode($errorData['context'], JSON_PRETTY_PRINT)) . "</pre>
            </div>";
        }
        
        if (!empty($errorData['trace'])) {
            $html .= "
            <div class='trace'>
                <h3>Stack Trace</h3>
                <pre>" . htmlspecialchars($this->formatStackTrace($errorData['trace'])) . "</pre>
            </div>";
        }
        
        $html .= "
            <p><small>This is an automated error report from Time2Eat application.</small></p>
        </body>
        </html>";
        
        return $html;
    }
    
    /**
     * Show user-friendly error page
     */
    private function showErrorPage(array $errorData): void
    {
        if (headers_sent()) {
            return;
        }
        
        http_response_code(500);
        
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Error - Time2Eat</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 flex items-center justify-center min-h-screen'>
    <div class='bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center'>
        <div class='text-red-500 text-6xl mb-4'>⚠️</div>
        <h1 class='text-2xl font-bold text-gray-800 mb-4'>Oops! Something went wrong</h1>
        <p class='text-gray-600 mb-6'>
            We're sorry, but something unexpected happened. Our team has been notified and is working to fix the issue.
        </p>
        <div class='space-y-3'>
            <button onclick='history.back()' class='w-full bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 transition-colors'>
                Go Back
            </button>
            <a href='/' class='block w-full bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors'>
                Home Page
            </a>
        </div>
        <p class='text-xs text-gray-500 mt-4'>Error ID: " . ($errorData['request_id'] ?? $this->generateRequestId()) . "</p>
    </div>
</body>
</html>";
    }
    
    // Additional helper methods...
    private function getSeverityName(int $severity): string
    {
        return match($severity) {
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => 'error',
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => 'warning',
            E_NOTICE, E_USER_NOTICE => 'notice',
            E_STRICT => 'strict',
            E_DEPRECATED, E_USER_DEPRECATED => 'deprecated',
            default => 'unknown'
        };
    }
    
    private function mapSeverityToLogLevel(string $severity): string
    {
        return match($severity) {
            'critical', 'emergency' => 'critical',
            'error' => 'error',
            'warning' => 'warning',
            'notice' => 'notice',
            'info' => 'info',
            'debug' => 'debug',
            default => 'error'
        };
    }
    
    private function getErrorContext(): array
    {
        return [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
    
    private function generateRequestId(): string
    {
        return uniqid('req_', true);
    }
    
    private function formatStackTrace(array $trace): string
    {
        $output = '';
        foreach ($trace as $i => $frame) {
            $output .= sprintf(
                "#%d %s(%d): %s%s%s()\n",
                $i,
                $frame['file'] ?? '[internal function]',
                $frame['line'] ?? 0,
                $frame['class'] ?? '',
                $frame['type'] ?? '',
                $frame['function'] ?? ''
            );
        }
        return $output;
    }
    
    private function checkEmailRateLimit(): bool
    {
        // Simple rate limiting - could be enhanced with database storage
        $rateFile = __DIR__ . '/../../logs/email_rate.json';
        $now = time();
        
        if (file_exists($rateFile)) {
            $data = json_decode(file_get_contents($rateFile), true);
            $hourlyCount = array_filter($data, fn($timestamp) => $now - $timestamp < 3600);
            
            if (count($hourlyCount) >= $this->config['max_email_per_hour']) {
                return false;
            }
        }
        
        return true;
    }
    
    private function recordEmailSent(): void
    {
        $rateFile = __DIR__ . '/../../logs/email_rate.json';
        $data = file_exists($rateFile) ? json_decode(file_get_contents($rateFile), true) : [];
        $data[] = time();
        
        // Keep only last 24 hours
        $data = array_filter($data, fn($timestamp) => time() - $timestamp < 86400);
        
        file_put_contents($rateFile, json_encode($data));
    }
    
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
}
