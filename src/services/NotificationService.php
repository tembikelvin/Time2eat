<?php

namespace Time2Eat\Services;

use Time2Eat\Services\EmailService;
use Time2Eat\Services\SMSService;
use Time2Eat\Models\User;
use Time2Eat\Models\Order;

class NotificationService
{
    private EmailService $emailService;
    private SMSService $smsService;
    private User $userModel;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->smsService = new SMSService();
        $this->userModel = new User();
    }

    /**
     * Send payment confirmation notification
     */
    public function sendPaymentConfirmation(array $user, array $order, array $paymentResult): void
    {
        try {
            // Email notification
            $this->emailService->sendPaymentConfirmation($user, $order, $paymentResult);

            // SMS notification if phone number available
            if (!empty($user['phone'])) {
                $this->smsService->sendPaymentConfirmation($user, $order, $paymentResult);
            }

        } catch (\Exception $e) {
            error_log("Payment confirmation notification error: " . $e->getMessage());
        }
    }

    /**
     * Send payment success notification
     */
    public function sendPaymentSuccessNotification(array $user, array $order): void
    {
        try {
            // Email notification
            $this->emailService->sendPaymentSuccess($user, $order);

            // SMS notification
            if (!empty($user['phone'])) {
                $this->smsService->sendPaymentSuccess($user, $order);
            }

        } catch (\Exception $e) {
            error_log("Payment success notification error: " . $e->getMessage());
        }
    }

    /**
     * Send payment failure notification
     */
    public function sendPaymentFailureNotification(array $user, array $order): void
    {
        try {
            // Email notification
            $this->emailService->sendPaymentFailure($user, $order);

            // SMS notification
            if (!empty($user['phone'])) {
                $this->smsService->sendPaymentFailure($user, $order);
            }

        } catch (\Exception $e) {
            error_log("Payment failure notification error: " . $e->getMessage());
        }
    }

    /**
     * Send refund notification
     */
    public function sendRefundNotification(array $user, array $order, float $refundAmount): void
    {
        try {
            // Email notification
            $this->emailService->sendRefundNotification($user, $order, $refundAmount);

            // SMS notification
            if (!empty($user['phone'])) {
                $this->smsService->sendRefundNotification($user, $order, $refundAmount);
            }

        } catch (\Exception $e) {
            error_log("Refund notification error: " . $e->getMessage());
        }
    }

    /**
     * Send order confirmation notification
     */
    public function sendOrderConfirmation(array $user, array $order): void
    {
        try {
            // Email notification
            $this->emailService->sendOrderConfirmation($user, $order);

            // SMS notification
            if (!empty($user['phone'])) {
                $this->smsService->sendOrderConfirmation($user, $order);
            }

        } catch (\Exception $e) {
            error_log("Order confirmation notification error: " . $e->getMessage());
        }
    }

    /**
     * Send order status update notification
     */
    public function sendOrderStatusUpdate(array $user, array $order, string $oldStatus, string $newStatus): void
    {
        try {
            // Email notification
            $this->emailService->sendOrderStatusUpdate($user, $order, $oldStatus, $newStatus);

            // SMS notification for important status changes
            if (!empty($user['phone']) && $this->isImportantStatusChange($newStatus)) {
                $this->smsService->sendOrderStatusUpdate($user, $order, $newStatus);
            }

        } catch (\Exception $e) {
            error_log("Order status update notification error: " . $e->getMessage());
        }
    }

    /**
     * Send order delivery notification
     */
    public function sendOrderDelivered(array $user, array $order): void
    {
        try {
            // Email notification
            $this->emailService->sendOrderDelivered($user, $order);

            // SMS notification
            if (!empty($user['phone'])) {
                $this->smsService->sendOrderDelivered($user, $order);
            }

        } catch (\Exception $e) {
            error_log("Order delivered notification error: " . $e->getMessage());
        }
    }

    /**
     * Send order cancelled notification
     */
    public function sendOrderCancelled(array $user, array $order, string $reason): void
    {
        try {
            // Email notification
            $this->emailService->sendOrderCancelled($user, $order, $reason);

            // SMS notification
            if (!empty($user['phone'])) {
                $this->smsService->sendOrderCancelled($user, $order, $reason);
            }

        } catch (\Exception $e) {
            error_log("Order cancelled notification error: " . $e->getMessage());
        }
    }

    /**
     * Send new order notification to vendor
     */
    public function sendNewOrderToVendor(array $vendor, array $order): void
    {
        try {
            // Email notification
            $this->emailService->sendNewOrderToVendor($vendor, $order);

            // SMS notification
            if (!empty($vendor['phone'])) {
                $this->smsService->sendNewOrderToVendor($vendor, $order);
            }

        } catch (\Exception $e) {
            error_log("New order vendor notification error: " . $e->getMessage());
        }
    }

    /**
     * Send delivery assignment notification to rider
     */
    public function sendDeliveryAssignmentToRider(array $rider, array $order): void
    {
        try {
            // Email notification
            $this->emailService->sendDeliveryAssignmentToRider($rider, $order);

            // SMS notification
            if (!empty($rider['phone'])) {
                $this->smsService->sendDeliveryAssignmentToRider($rider, $order);
            }

        } catch (\Exception $e) {
            error_log("Delivery assignment notification error: " . $e->getMessage());
        }
    }

    /**
     * Send welcome notification to new user
     */
    public function sendWelcomeNotification(array $user): void
    {
        try {
            // Email notification
            $this->emailService->sendWelcomeEmail($user);

            // SMS notification
            if (!empty($user['phone'])) {
                $this->smsService->sendWelcomeSMS($user);
            }

        } catch (\Exception $e) {
            error_log("Welcome notification error: " . $e->getMessage());
        }
    }

    /**
     * Send password reset notification
     */
    public function sendPasswordResetNotification(array $user, string $resetToken): void
    {
        try {
            // Email notification
            $this->emailService->sendPasswordReset($user, $resetToken);

            // SMS notification with short code
            if (!empty($user['phone'])) {
                $shortCode = substr($resetToken, 0, 6);
                $this->smsService->sendPasswordResetCode($user, $shortCode);
            }

        } catch (\Exception $e) {
            error_log("Password reset notification error: " . $e->getMessage());
        }
    }

    /**
     * Send account verification notification
     */
    public function sendAccountVerification(array $user, string $verificationToken): void
    {
        try {
            // Email notification
            $this->emailService->sendAccountVerification($user, $verificationToken);

            // SMS notification with verification code
            if (!empty($user['phone'])) {
                $verificationCode = substr($verificationToken, 0, 6);
                $this->smsService->sendVerificationCode($user, $verificationCode);
            }

        } catch (\Exception $e) {
            error_log("Account verification notification error: " . $e->getMessage());
        }
    }

    /**
     * Send promotional notification
     */
    public function sendPromotionalNotification(array $users, string $subject, string $message, array $options = []): void
    {
        try {
            foreach ($users as $user) {
                // Email notification
                if ($options['send_email'] ?? true) {
                    $this->emailService->sendPromotionalEmail($user, $subject, $message, $options);
                }

                // SMS notification
                if (($options['send_sms'] ?? false) && !empty($user['phone'])) {
                    $this->smsService->sendPromotionalSMS($user, $message);
                }
            }

        } catch (\Exception $e) {
            error_log("Promotional notification error: " . $e->getMessage());
        }
    }

    /**
     * Send bulk notification
     */
    public function sendBulkNotification(array $userIds, string $subject, string $message, array $options = []): void
    {
        try {
            // Get users in batches to avoid memory issues
            $batchSize = 100;
            $offset = 0;

            do {
                $users = $this->userModel->getUsersBatch($userIds, $batchSize, $offset);
                
                foreach ($users as $user) {
                    // Email notification
                    if ($options['send_email'] ?? true) {
                        $this->emailService->sendBulkEmail($user, $subject, $message, $options);
                    }

                    // SMS notification
                    if (($options['send_sms'] ?? false) && !empty($user['phone'])) {
                        $this->smsService->sendBulkSMS($user, $message);
                    }
                }

                $offset += $batchSize;
            } while (count($users) === $batchSize);

        } catch (\Exception $e) {
            error_log("Bulk notification error: " . $e->getMessage());
        }
    }

    /**
     * Send system maintenance notification
     */
    public function sendMaintenanceNotification(string $message, \DateTime $scheduledTime): void
    {
        try {
            // Get all active users
            $users = $this->userModel->getActiveUsers();

            foreach ($users as $user) {
                // Email notification
                $this->emailService->sendMaintenanceNotification($user, $message, $scheduledTime);

                // SMS notification for critical maintenance
                if (!empty($user['phone'])) {
                    $this->smsService->sendMaintenanceNotification($user, $message, $scheduledTime);
                }
            }

        } catch (\Exception $e) {
            error_log("Maintenance notification error: " . $e->getMessage());
        }
    }

    /**
     * Check if status change is important enough for SMS
     */
    private function isImportantStatusChange(string $status): bool
    {
        return in_array($status, [
            'confirmed',
            'ready',
            'picked_up',
            'on_the_way',
            'delivered',
            'cancelled'
        ]);
    }

    /**
     * Get notification preferences for user
     */
    public function getNotificationPreferences(int $userId): array
    {
        // This would typically come from a user_preferences table
        // For now, return default preferences
        return [
            'email_notifications' => true,
            'sms_notifications' => true,
            'order_updates' => true,
            'payment_notifications' => true,
            'promotional_emails' => true,
            'promotional_sms' => false
        ];
    }

    /**
     * Update notification preferences for user
     */
    public function updateNotificationPreferences(int $userId, array $preferences): bool
    {
        // This would typically update a user_preferences table
        // For now, return true
        return true;
    }

    /**
     * Send review request notification
     */
    public function sendReviewRequest(array $user, array $order): bool
    {
        try {
            // Email notification
            $this->emailService->sendReviewRequest($user, $order);

            // SMS notification
            if (!empty($user['phone'])) {
                $this->smsService->sendReviewRequest($user, $order);
            }

            return true;

        } catch (\Exception $e) {
            error_log("Review request notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send vendor notification
     */
    public function sendVendorNotification(int $restaurantId, string $message): bool
    {
        try {
            // Get vendor details
            $vendor = $this->userModel->getVendorByRestaurant($restaurantId);
            if (!$vendor) {
                return false;
            }

            // Email notification
            $this->emailService->sendVendorAlert($vendor, $message);

            // SMS notification
            if (!empty($vendor['phone'])) {
                $this->smsService->sendVendorAlert($vendor, $message);
            }

            return true;

        } catch (\Exception $e) {
            error_log("Vendor notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send rider notification
     */
    public function sendRiderNotification(int $riderId, string $message): bool
    {
        try {
            // Get rider details
            $rider = $this->userModel->getUserById($riderId);
            if (!$rider) {
                return false;
            }

            // Email notification
            $this->emailService->sendRiderAlert($rider, $message);

            // SMS notification
            if (!empty($rider['phone'])) {
                $this->smsService->sendRiderAlert($rider, $message);
            }

            return true;

        } catch (\Exception $e) {
            error_log("Rider notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send admin alert
     */
    public function sendAdminAlert(string $message, array $orderData = []): bool
    {
        try {
            // Get all admin users
            $admins = $this->userModel->getAdminUsers();

            $success = true;
            foreach ($admins as $admin) {
                // Email notification
                $this->emailService->sendAdminAlert($admin, $message, $orderData);

                // SMS notification for critical alerts
                if (!empty($admin['phone'])) {
                    $this->smsService->sendAdminAlert($admin, $message);
                }
            }

            return $success;

        } catch (\Exception $e) {
            error_log("Admin alert error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log notification attempt
     */
    private function logNotification(string $type, array $user, string $subject, bool $success, string $error = null): void
    {
        try {
            // Log to database or file
            $logData = [
                'type' => $type,
                'user_id' => $user['id'],
                'recipient' => $user['email'] ?? $user['phone'] ?? 'unknown',
                'subject' => $subject,
                'success' => $success,
                'error' => $error,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // You could save this to a notifications_log table
            error_log("Notification log: " . json_encode($logData));

        } catch (\Exception $e) {
            error_log("Notification logging error: " . $e->getMessage());
        }
    }
}
