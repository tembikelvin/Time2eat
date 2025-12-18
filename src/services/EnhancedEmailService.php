<?php

namespace Time2Eat\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EnhancedEmailService
{
    private PHPMailer $mailer;
    private array $config;
    private string $environment;
    private bool $loggingEnabled;
    private string $logFile;

    public function __construct()
    {
        // Load environment-aware email configuration
        $this->config = require __DIR__ . '/../../config/email_environment.php';
        $this->environment = $this->config['environment'];
        $this->loggingEnabled = $this->config['logging_enabled'] ?? false;
        $this->logFile = STORAGE_PATH . '/logs/email.log';

        $this->initializeMailer();
    }

    /**
     * Send email with retry and fallback mechanisms
     */
    public function sendEmail(string $to, string $subject, string $body, array $attachments = []): bool
    {
        $attempt = 0;
        $maxAttempts = $this->config['retry_attempts'] ?? 1;
        $delay = $this->config['retry_delay'] ?? 1;

        while ($attempt < $maxAttempts) {
            $attempt++;
            
            try {
                $this->log("Attempt $attempt/$maxAttempts - Sending email to: $to");
                
                $this->mailer->clearAddresses();
                $this->mailer->clearAttachments();

                // Handle test mode
                if ($this->config['test_mode'] && $this->config['test_email']) {
                    $to = $this->config['test_email'];
                    $this->log("Test mode: Redirecting email to test address: $to");
                }

                $this->mailer->addAddress($to);
                $this->mailer->Subject = $subject;
                $this->mailer->Body = $body;

                // Add attachments if any
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        $this->mailer->addAttachment($attachment['path'], $attachment['name'] ?? '');
                    } else {
                        $this->mailer->addAttachment($attachment);
                    }
                }

                $result = $this->mailer->send();
                
                if ($result) {
                    $this->log("Email sent successfully to: $to");
                    return true;
                } else {
                    $this->log("Failed to send email to: $to - No error details");
                    throw new Exception("Failed to send email");
                }

            } catch (Exception $e) {
                $this->log("Email send error (attempt $attempt): " . $e->getMessage());
                
                if ($attempt < $maxAttempts) {
                    $this->log("Retrying in $delay seconds...");
                    sleep($delay);
                } else {
                    // Try fallback method if enabled
                    if ($this->config['fallback_enabled']) {
                        return $this->sendEmailFallback($to, $subject, $body, $attachments);
                    }
                    
                    $this->log("All attempts failed for email to: $to");
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Fallback email method (file-based for development)
     */
    private function sendEmailFallback(string $to, string $subject, string $body, array $attachments = []): bool
    {
        try {
            $this->log("Using fallback email method for: $to");
            
            $fallbackDir = STORAGE_PATH . '/emails/fallback';
            if (!is_dir($fallbackDir)) {
                mkdir($fallbackDir, 0755, true);
            }

            $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.html';
            $filepath = $fallbackDir . '/' . $filename;

            $fallbackContent = "
<!DOCTYPE html>
<html>
<head>
    <title>Email Fallback - $subject</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f0f0f0; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .content { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='header'>
        <h3>📧 Email Fallback (Environment: {$this->environment})</h3>
        <p><strong>To:</strong> $to</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
    </div>
    <div class='content'>
        $body
    </div>
    <div class='footer'>
        <p>This email was saved as a fallback because SMTP delivery failed.</p>
        <p>File: $filename</p>
    </div>
</body>
</html>";

            file_put_contents($filepath, $fallbackContent);
            $this->log("Email saved to fallback file: $filepath");
            
            return true;

        } catch (Exception $e) {
            $this->log("Fallback email failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send verification code email
     */
    public function sendVerificationCodeEmail(array $data): bool
    {
        $subject = "Your Verification Code - Time2Eat";
        
        $body = $this->getEmailTemplate('email_verification_code', [
            'user' => $data,
            'verification_code' => $data['verification_code'],
            'expiry_minutes' => $data['expiry_minutes']
        ]);

        return $this->sendEmail($data['email'], $subject, $body);
    }

    /**
     * Send verification email (link-based)
     */
    public function sendVerificationEmail(array $data): bool
    {
        $subject = "Verify Your Email Address - Time2Eat";
        
        $body = $this->getEmailTemplate('email_verification', [
            'user' => $data,
            'verification_url' => $data['verification_url'],
            'expiry_hours' => $data['expiry_hours']
        ]);

        return $this->sendEmail($data['email'], $subject, $body);
    }

    /**
     * Send welcome email
     */
    public function sendWelcomeEmail(array $user): bool
    {
        $subject = "Welcome to Time2Eat!";
        
        $body = $this->getEmailTemplate('welcome', [
            'user' => $user
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Test email configuration
     */
    public function testConfiguration(): array
    {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            
            $this->log("Email configuration test successful");
            
            return [
                'success' => true,
                'message' => 'Email configuration is working',
                'environment' => $this->environment,
                'test_mode' => $this->config['test_mode'] ?? false
            ];
        } catch (Exception $e) {
            $this->log("Email configuration test failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Email configuration error: ' . $e->getMessage(),
                'environment' => $this->environment,
                'test_mode' => $this->config['test_mode'] ?? false
            ];
        }
    }

    /**
     * Get email template
     */
    private function getEmailTemplate(string $template, array $data = []): string
    {
        $templatePath = __DIR__ . "/../views/emails/{$template}.php";
        
        if (file_exists($templatePath)) {
            ob_start();
            extract($data);
            include $templatePath;
            return ob_get_clean();
        }

        // Fallback to simple template
        return $this->getSimpleTemplate($template, $data);
    }

    /**
     * Get simple email template
     */
    private function getSimpleTemplate(string $template, array $data): string
    {
        $user = $data['user'] ?? [];
        $userName = $user['first_name'] ?? $user['username'] ?? 'Customer';

        $baseTemplate = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #e74c3c; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .button { display: inline-block; padding: 10px 20px; background: #e74c3c; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Time2Eat</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userName}!</h2>
                    {CONTENT}
                </div>
                <div class='footer'>
                    <p>© 2024 Time2Eat. All rights reserved.</p>
                    <p>Environment: {$this->environment}</p>
                </div>
            </div>
        </body>
        </html>";

        $content = $this->getTemplateContent($template, $data);
        return str_replace('{CONTENT}', $content, $baseTemplate);
    }

    /**
     * Get template content based on type
     */
    private function getTemplateContent(string $template, array $data): string
    {
        switch ($template) {
            case 'email_verification_code':
                $code = $data['verification_code'] ?? '123456';
                $minutes = $data['expiry_minutes'] ?? 10;
                return "<p>Your verification code is: <strong style='font-size: 24px; color: #e74c3c;'>{$code}</strong></p>
                        <p>This code will expire in {$minutes} minutes.</p>
                        <p>Enter this code on the verification page to complete your registration.</p>";

            case 'welcome':
                return "<p>Welcome to Time2Eat, Bamenda's premier food delivery platform!</p>
                        <p>We're excited to have you join our community. Start exploring delicious local restaurants and get your favorite meals delivered right to your door.</p>
                        <a href='" . $this->config['app_url'] . "/browse' class='button'>Start Ordering</a>";

            default:
                return "<p>Thank you for using Time2Eat!</p>";
        }
    }

    /**
     * Initialize PHPMailer
     */
    private function initializeMailer(): void
    {
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp']['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp']['username'];
            $this->mailer->Password = $this->config['smtp']['password'];
            $this->mailer->SMTPSecure = $this->config['smtp']['encryption'];
            $this->mailer->Port = $this->config['smtp']['port'];

            // Recipients
            $this->mailer->setFrom($this->config['from']['address'], $this->config['from']['name']);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';

            // Debug settings
            if ($this->config['debug'] ?? false) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
            }

        } catch (Exception $e) {
            $this->log("PHPMailer initialization error: " . $e->getMessage());
        }
    }

    /**
     * Log email activities
     */
    private function log(string $message): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $logEntry = "[" . date('Y-m-d H:i:s') . "] [{$this->environment}] $message" . PHP_EOL;
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get email statistics
     */
    public function getEmailStats(): array
    {
        $stats = [
            'environment' => $this->environment,
            'test_mode' => $this->config['test_mode'] ?? false,
            'fallback_enabled' => $this->config['fallback_enabled'] ?? false,
            'logging_enabled' => $this->loggingEnabled,
        ];

        // Count fallback emails
        $fallbackDir = STORAGE_PATH . '/emails/fallback';
        if (is_dir($fallbackDir)) {
            $stats['fallback_emails'] = count(glob($fallbackDir . '/*.html'));
        } else {
            $stats['fallback_emails'] = 0;
        }

        // Get recent log entries
        if ($this->loggingEnabled && file_exists($this->logFile)) {
            $logContent = file_get_contents($this->logFile);
            $stats['recent_errors'] = substr_count($logContent, 'error');
            $stats['recent_success'] = substr_count($logContent, 'successfully');
        } else {
            $stats['recent_errors'] = 0;
            $stats['recent_success'] = 0;
        }

        return $stats;
    }
}
