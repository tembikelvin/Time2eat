<?php

namespace Time2Eat\Services;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class SMSService
{
    private ?Client $client;
    private array $config;

    public function __construct()
    {
        $this->config = [
            'sid' => $_ENV['TWILIO_SID'] ?? '',
            'token' => $_ENV['TWILIO_TOKEN'] ?? '',
            'from' => $_ENV['TWILIO_FROM'] ?? '',
            'enabled' => !empty($_ENV['TWILIO_SID']) && !empty($_ENV['TWILIO_TOKEN'])
        ];

        $this->initializeClient();
    }

    /**
     * Send payment confirmation SMS
     */
    public function sendPaymentConfirmation(array $user, array $order, array $paymentResult): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Payment for order #{$order['order_number']} received. Amount: {$order['total_amount']} XAF. Processing...";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send payment success SMS
     */
    public function sendPaymentSuccess(array $user, array $order): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Payment successful! Order #{$order['order_number']} confirmed. Your food is being prepared.";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send payment failure SMS
     */
    public function sendPaymentFailure(array $user, array $order): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Payment failed for order #{$order['order_number']}. Please try again or contact support.";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send refund notification SMS
     */
    public function sendRefundNotification(array $user, array $order, float $refundAmount): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Refund of {$refundAmount} XAF processed for order #{$order['order_number']}. Funds will reflect in 3-5 business days.";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send order confirmation SMS
     */
    public function sendOrderConfirmation(array $user, array $order): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Order #{$order['order_number']} confirmed! Total: {$order['total_amount']} XAF. Est. delivery: {$order['estimated_delivery_time']}.";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send order status update SMS
     */
    public function sendOrderStatusUpdate(array $user, array $order, string $newStatus): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $statusMessages = [
            'confirmed' => 'Order confirmed and being prepared',
            'preparing' => 'Your order is being prepared',
            'ready' => 'Order ready for pickup',
            'picked_up' => 'Order picked up by rider',
            'on_the_way' => 'Order on the way to you',
            'delivered' => 'Order delivered successfully',
            'cancelled' => 'Order has been cancelled'
        ];

        $statusText = $statusMessages[$newStatus] ?? ucfirst(str_replace('_', ' ', $newStatus));
        $message = "Time2Eat: Order #{$order['order_number']} - {$statusText}.";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send order delivered SMS
     */
    public function sendOrderDelivered(array $user, array $order): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Order #{$order['order_number']} delivered! Enjoy your meal. Rate your experience at time2eat.cm";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send order cancelled SMS
     */
    public function sendOrderCancelled(array $user, array $order, string $reason): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Order #{$order['order_number']} cancelled. Reason: {$reason}. Refund will be processed if payment was made.";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send new order notification to vendor
     */
    public function sendNewOrderToVendor(array $vendor, array $order): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: New order #{$order['order_number']} received! Amount: {$order['total_amount']} XAF. Check your dashboard to accept.";
        
        return $this->sendSMS($vendor['phone'], $message);
    }

    /**
     * Send delivery assignment to rider
     */
    public function sendDeliveryAssignmentToRider(array $rider, array $order): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: New delivery #{$order['order_number']} assigned to you. Check your app for pickup details.";
        
        return $this->sendSMS($rider['phone'], $message);
    }

    /**
     * Send welcome SMS
     */
    public function sendWelcomeSMS(array $user): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Welcome to Time2Eat! Bamenda's best food delivery. Download our app or visit time2eat.cm to order now!";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send password reset code SMS
     */
    public function sendPasswordResetCode(array $user, string $code): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Your password reset code is {$code}. Valid for 15 minutes. Don't share this code.";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send verification code SMS
     */
    public function sendVerificationCode(array $user, string $code): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Your verification code is {$code}. Enter this code to verify your account.";
        
        return $this->sendSMS($user['phone'], $message);
    }

    /**
     * Send promotional SMS
     */
    public function sendPromotionalSMS(array $user, string $message): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $fullMessage = "Time2Eat: {$message} Reply STOP to unsubscribe.";
        
        return $this->sendSMS($user['phone'], $fullMessage);
    }

    /**
     * Send bulk SMS
     */
    public function sendBulkSMS(array $user, string $message): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $fullMessage = "Time2Eat: {$message}";
        
        return $this->sendSMS($user['phone'], $fullMessage);
    }

    /**
     * Send maintenance notification SMS
     */
    public function sendMaintenanceNotification(array $user, string $message, \DateTime $scheduledTime): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $timeStr = $scheduledTime->format('M j, Y g:i A');
        $fullMessage = "Time2Eat: Scheduled maintenance on {$timeStr}. {$message}";
        
        return $this->sendSMS($user['phone'], $fullMessage);
    }

    /**
     * Send OTP SMS
     */
    public function sendOTP(string $phone, string $otp): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $message = "Time2Eat: Your OTP is {$otp}. Valid for 5 minutes. Don't share this code.";
        
        return $this->sendSMS($phone, $message);
    }

    /**
     * Send custom SMS
     */
    public function sendCustomSMS(string $phone, string $message): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return $this->sendSMS($phone, $message);
    }

    /**
     * Send SMS using Twilio
     */
    private function sendSMS(string $to, string $message): bool
    {
        if (!$this->client) {
            return false;
        }

        try {
            // Format phone number for Cameroon
            $to = $this->formatPhoneNumber($to);
            
            $this->client->messages->create($to, [
                'from' => $this->config['from'],
                'body' => $message
            ]);

            return true;

        } catch (TwilioException $e) {
            error_log("SMS sending error: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log("SMS service error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number for international use
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle Cameroon phone numbers
        if (strlen($phone) === 9 && (substr($phone, 0, 1) === '6' || substr($phone, 0, 1) === '7')) {
            // Add Cameroon country code
            $phone = '+237' . $phone;
        } elseif (strlen($phone) === 12 && substr($phone, 0, 3) === '237') {
            // Already has country code without +
            $phone = '+' . $phone;
        } elseif (substr($phone, 0, 1) !== '+') {
            // Add + if not present
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Validate phone number
     */
    public function validatePhoneNumber(string $phone): bool
    {
        $formatted = $this->formatPhoneNumber($phone);
        
        // Check if it's a valid Cameroon number
        return preg_match('/^\+237[67]\d{8}$/', $formatted);
    }

    /**
     * Check if SMS service is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'];
    }

    /**
     * Initialize Twilio client
     */
    private function initializeClient(): void
    {
        if (!$this->config['enabled']) {
            $this->client = null;
            return;
        }

        try {
            $this->client = new Client($this->config['sid'], $this->config['token']);
        } catch (\Exception $e) {
            error_log("Twilio client initialization error: " . $e->getMessage());
            $this->client = null;
        }
    }

    /**
     * Test SMS configuration
     */
    public function testConfiguration(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'SMS service is not configured'
            ];
        }

        try {
            // Try to get account info to test connection
            $account = $this->client->api->accounts($this->config['sid'])->fetch();
            
            return [
                'success' => true,
                'message' => 'SMS configuration is working',
                'account_status' => $account->status
            ];
        } catch (TwilioException $e) {
            return [
                'success' => false,
                'message' => 'SMS configuration error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get SMS delivery status
     */
    public function getDeliveryStatus(string $messageSid): array
    {
        if (!$this->client) {
            return [
                'success' => false,
                'message' => 'SMS service not available'
            ];
        }

        try {
            $message = $this->client->messages($messageSid)->fetch();
            
            return [
                'success' => true,
                'status' => $message->status,
                'error_code' => $message->errorCode,
                'error_message' => $message->errorMessage,
                'date_sent' => $message->dateSent ? $message->dateSent->format('Y-m-d H:i:s') : null,
                'price' => $message->price,
                'price_unit' => $message->priceUnit
            ];
        } catch (TwilioException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get delivery status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get account balance
     */
    public function getAccountBalance(): array
    {
        if (!$this->client) {
            return [
                'success' => false,
                'message' => 'SMS service not available'
            ];
        }

        try {
            $balance = $this->client->api->accounts($this->config['sid'])->balance->fetch();
            
            return [
                'success' => true,
                'balance' => $balance->balance,
                'currency' => $balance->currency
            ];
        } catch (TwilioException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get account balance: ' . $e->getMessage()
            ];
        }
    }
}
