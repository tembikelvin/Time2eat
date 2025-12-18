<?php

namespace Time2Eat\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;
    private array $config;

    public function __construct()
    {
        // Load email configuration
        $emailConfig = require __DIR__ . '/../../config/email.php';
        
        $this->config = [
            'host' => $emailConfig['smtp']['host'],
            'port' => $emailConfig['smtp']['port'],
            'username' => $emailConfig['smtp']['username'],
            'password' => $emailConfig['smtp']['password'],
            'encryption' => $emailConfig['smtp']['encryption'],
            'from_address' => $emailConfig['from']['address'],
            'from_name' => $emailConfig['from']['name'],
            'app_url' => $emailConfig['app_url'],
        ];

        $this->initializeMailer();
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation(array $user, array $order, array $paymentResult): bool
    {
        $subject = "Payment Confirmation - Order #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('payment_confirmation', [
            'user' => $user,
            'order' => $order,
            'payment_result' => $paymentResult
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send payment success email
     */
    public function sendPaymentSuccess(array $user, array $order): bool
    {
        $subject = "Payment Successful - Order #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('payment_success', [
            'user' => $user,
            'order' => $order
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send payment failure email
     */
    public function sendPaymentFailure(array $user, array $order): bool
    {
        $subject = "Payment Failed - Order #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('payment_failure', [
            'user' => $user,
            'order' => $order
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send refund notification email
     */
    public function sendRefundNotification(array $user, array $order, float $refundAmount): bool
    {
        $subject = "Refund Processed - Order #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('refund_notification', [
            'user' => $user,
            'order' => $order,
            'refund_amount' => $refundAmount
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(array $user, array $order): bool
    {
        $subject = "Order Confirmation - #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('order_confirmation', [
            'user' => $user,
            'order' => $order
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send order status update email
     */
    public function sendOrderStatusUpdate(array $user, array $order, string $oldStatus, string $newStatus): bool
    {
        $subject = "Order Update - #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('order_status_update', [
            'user' => $user,
            'order' => $order,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send order delivered email
     */
    public function sendOrderDelivered(array $user, array $order): bool
    {
        $subject = "Order Delivered - #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('order_delivered', [
            'user' => $user,
            'order' => $order
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send order cancelled email
     */
    public function sendOrderCancelled(array $user, array $order, string $reason): bool
    {
        $subject = "Order Cancelled - #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('order_cancelled', [
            'user' => $user,
            'order' => $order,
            'reason' => $reason
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send new order notification to vendor
     */
    public function sendNewOrderToVendor(array $vendor, array $order): bool
    {
        $subject = "New Order Received - #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('new_order_vendor', [
            'vendor' => $vendor,
            'order' => $order
        ]);

        return $this->sendEmail($vendor['email'], $subject, $body);
    }

    /**
     * Send delivery assignment to rider
     */
    public function sendDeliveryAssignmentToRider(array $rider, array $order): bool
    {
        $subject = "New Delivery Assignment - #{$order['order_number']}";
        
        $body = $this->getEmailTemplate('delivery_assignment', [
            'rider' => $rider,
            'order' => $order
        ]);

        return $this->sendEmail($rider['email'], $subject, $body);
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
     * Send password reset email
     */
    public function sendPasswordReset(array $user, string $resetToken): bool
    {
        $subject = "Password Reset Request - Time2Eat";
        
        $resetUrl = $_ENV['APP_URL'] . "/reset-password?token=" . $resetToken;
        
        $body = $this->getEmailTemplate('password_reset', [
            'user' => $user,
            'reset_url' => $resetUrl,
            'reset_token' => $resetToken
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send account verification email
     */
    public function sendAccountVerification(array $user, string $verificationToken): bool
    {
        $subject = "Verify Your Account - Time2Eat";
        
        $verificationUrl = $_ENV['APP_URL'] . "/verify-account?token=" . $verificationToken;
        
        $body = $this->getEmailTemplate('account_verification', [
            'user' => $user,
            'verification_url' => $verificationUrl,
            'verification_token' => $verificationToken
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send promotional email
     */
    public function sendPromotionalEmail(array $user, string $subject, string $message, array $options = []): bool
    {
        $body = $this->getEmailTemplate('promotional', [
            'user' => $user,
            'message' => $message,
            'options' => $options
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send bulk email
     */
    public function sendBulkEmail(array $user, string $subject, string $message, array $options = []): bool
    {
        $body = $this->getEmailTemplate('bulk_notification', [
            'user' => $user,
            'message' => $message,
            'options' => $options
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send maintenance notification email
     */
    public function sendMaintenanceNotification(array $user, string $message, \DateTime $scheduledTime): bool
    {
        $subject = "Scheduled Maintenance - Time2Eat";
        
        $body = $this->getEmailTemplate('maintenance_notification', [
            'user' => $user,
            'message' => $message,
            'scheduled_time' => $scheduledTime
        ]);

        return $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send email verification email
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
     * Send email verification code email
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
     * Send email
     */
    private function sendEmail(string $to, string $subject, string $body, array $attachments = []): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Send email directly to the intended recipient

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

            return $this->mailer->send();

        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
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
                    <p>Bamenda, Cameroon</p>
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
            case 'payment_confirmation':
                $order = $data['order'];
                return "<p>Your payment for order #{$order['order_number']} has been received and is being processed.</p>
                        <p>Order Total: {$order['total_amount']} XAF</p>
                        <p>We'll notify you once the payment is confirmed.</p>";

            case 'payment_success':
                $order = $data['order'];
                return "<p>Great news! Your payment for order #{$order['order_number']} has been successfully processed.</p>
                        <p>Your order is now being prepared.</p>";

            case 'order_confirmation':
                $order = $data['order'];
                return "<p>Thank you for your order! We've received your order #{$order['order_number']} and it's being processed.</p>
                        <p>Order Total: {$order['total_amount']} XAF</p>
                        <p>Estimated delivery time: {$order['estimated_delivery_time']}</p>";

            case 'welcome':
                return "<p>Welcome to Time2Eat, Bamenda's premier food delivery platform!</p>
                        <p>We're excited to have you join our community. Start exploring delicious local restaurants and get your favorite meals delivered right to your door.</p>
                        <a href='" . $_ENV['APP_URL'] . "/browse' class='button'>Start Ordering</a>";

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
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];

            // Recipients
            $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';

        } catch (Exception $e) {
            error_log("PHPMailer initialization error: " . $e->getMessage());
        }
    }

    /**
     * Test email configuration
     */
    public function testConfiguration(): array
    {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            
            return [
                'success' => true,
                'message' => 'Email configuration is working'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Email configuration error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send email verification code
     */
    public function sendVerificationCode(string $email, string $code): bool
    {
        $subject = "Verify Your Email - Time2Eat";
        
        $body = $this->getVerificationCodeTemplate($code);
        
        return $this->sendEmail($email, $subject, $body);
    }

    /**
     * Get verification code email template
     */
    private function getVerificationCodeTemplate(string $code): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verify Your Email - Time2Eat</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #ff6b35; }
                .logo { font-size: 24px; font-weight: bold; color: #ff6b35; }
                .content { padding: 30px 0; }
                .code-container { background: #f8f9fa; border: 2px dashed #ff6b35; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
                .verification-code { font-size: 32px; font-weight: bold; color: #ff6b35; letter-spacing: 5px; font-family: monospace; }
                .footer { text-align: center; padding: 20px 0; border-top: 1px solid #eee; color: #666; font-size: 14px; }
                .button { display: inline-block; background: #ff6b35; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>🍽️ Time2Eat</div>
                </div>
                
                <div class='content'>
                    <h2>Verify Your Email Address</h2>
                    <p>Thank you for registering with Time2Eat! To complete your registration, please verify your email address using the code below:</p>
                    
                    <div class='code-container'>
                        <p style='margin: 0 0 10px 0; color: #666;'>Your verification code is:</p>
                        <div class='verification-code'>{$code}</div>
                    </div>
                    
                    <p><strong>Important:</strong></p>
                    <ul>
                        <li>This code will expire in 5 minutes</li>
                        <li>Enter this code in the registration form to continue</li>
                        <li>If you didn't request this code, please ignore this email</li>
                    </ul>
                    
                    <p>If you have any questions, please contact our support team.</p>
                </div>
                
                <div class='footer'>
                    <p>© " . date('Y') . " Time2Eat. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
