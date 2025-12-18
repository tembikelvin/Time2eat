<?php

declare(strict_types=1);

namespace services;

require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/../../config/database.php';

use Time2Eat\Services\EmailService;

/**
 * Email Verification Service
 * Handles email verification tokens and sending verification emails
 */
class EmailVerificationService
{
    private EmailService $emailService;
    private \Database $db;
    private int $tokenExpiryHours = 24; // 24 hours

    public function __construct()
    {
        $this->emailService = new \Time2Eat\Services\EmailService();
        $this->db = \Database::getInstance();
    }

    /**
     * Generate a secure verification token
     */
    public function generateVerificationToken(): string
    {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }

    /**
     * Create and store verification token for user
     */
    public function createVerificationToken(int $userId, string $email): string
    {
        $token = $this->generateVerificationToken();

        try {
            // Store token in database using DATE_ADD for timezone consistency
            $stmt = $this->db->prepare("
                UPDATE users
                SET email_verification_token = ?,
                    email_verification_expires = DATE_ADD(NOW(), INTERVAL ? HOUR),
                    updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$token, $this->tokenExpiryHours, $userId]);

            return $token;
        } catch (\Exception $e) {
            error_log("Error creating verification token: " . $e->getMessage());
            throw new \Exception("Failed to create verification token");
        }
    }

    /**
     * Send verification email to user
     */
    public function sendVerificationEmail(int $userId, string $email, string $firstName): bool
    {
        try {
            // Create verification token
            $token = $this->createVerificationToken($userId, $email);
            
            // Generate verification URL
            $verificationUrl = $this->getVerificationUrl($token);
            
            // Send email using EmailService
            return $this->emailService->sendVerificationEmail([
                'email' => $email,
                'first_name' => $firstName,
                'verification_url' => $verificationUrl,
                'expiry_hours' => $this->tokenExpiryHours
            ]);

        } catch (\Exception $e) {
            error_log("Error sending verification email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a 6-digit verification code
     */
    public function generateVerificationCode(): string
    {
        return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create and store verification code for user
     */
    public function createVerificationCode(int $userId, string $email): string
    {
        $code = $this->generateVerificationCode();

        try {
            // Store code in database using DATE_ADD for timezone consistency
            $stmt = $this->db->prepare("
                UPDATE users
                SET email_verification_code = ?,
                    email_verification_expires = DATE_ADD(NOW(), INTERVAL 10 MINUTE),
                    updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$code, $userId]);

            return $code;
        } catch (\Exception $e) {
            error_log("Error creating verification code: " . $e->getMessage());
            throw new \Exception("Failed to create verification code");
        }
    }

    /**
     * Send verification code email to user
     */
    public function sendVerificationCodeEmail(int $userId, string $email, string $firstName): bool
    {
        try {
            // Create verification code
            $code = $this->createVerificationCode($userId, $email);
            
            // Send email using EmailService
            return $this->emailService->sendVerificationCodeEmail([
                'email' => $email,
                'first_name' => $firstName,
                'verification_code' => $code,
                'expiry_minutes' => 10
            ]);

        } catch (\Exception $e) {
            error_log("Error sending verification code email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify email code and activate user account
     */
    public function verifyEmailCode(string $code): array
    {
        try {
            // Find user with valid code
            $stmt = $this->db->prepare("
                SELECT id, email, first_name, last_name, email_verification_expires
                FROM users 
                WHERE email_verification_code = ? 
                AND email_verification_expires > NOW()
                AND deleted_at IS NULL
            ");
            $stmt->execute([$code]);
            $user = $stmt->fetch();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired verification code'
                ];
            }

            // Update user as verified
            $updateStmt = $this->db->prepare("
                UPDATE users 
                SET email_verified_at = NOW(),
                    email_verification_code = NULL,
                    email_verification_expires = NULL,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$user['id']]);

            return [
                'success' => true,
                'message' => 'Email verified successfully!',
                'user' => $user
            ];

        } catch (\Exception $e) {
            error_log("Error verifying email code: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during verification'
            ];
        }
    }

    /**
     * Verify email token and activate user account
     */
    public function verifyEmailToken(string $token): array
    {
        try {
            // Find user with valid token
            $stmt = $this->db->prepare("
                SELECT id, email, first_name, last_name, email_verification_expires
                FROM users 
                WHERE email_verification_token = ? 
                AND email_verification_expires > NOW()
                AND deleted_at IS NULL
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired verification token'
                ];
            }

            // Update user as verified
            $updateStmt = $this->db->prepare("
                UPDATE users 
                SET email_verified_at = NOW(),
                    email_verification_token = NULL,
                    email_verification_expires = NULL,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$user['id']]);

            return [
                'success' => true,
                'message' => 'Email verified successfully!',
                'user' => $user
            ];

        } catch (\Exception $e) {
            error_log("Error verifying email token: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during verification'
            ];
        }
    }

    /**
     * Check if user's email is verified
     */
    public function isEmailVerified(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT email_verified_at 
                FROM users 
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();

            return $result && $result['email_verified_at'] !== null;
        } catch (\Exception $e) {
            error_log("Error checking email verification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(int $userId): array
    {
        try {
            // Get user details
            $stmt = $this->db->prepare("
                SELECT email, first_name, email_verified_at
                FROM users 
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            if ($user['email_verified_at']) {
                return [
                    'success' => false,
                    'message' => 'Email is already verified'
                ];
            }

            // Send verification email
            $sent = $this->sendVerificationEmail($userId, $user['email'], $user['first_name']);

            if ($sent) {
                return [
                    'success' => true,
                    'message' => 'Verification email sent successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to send verification email'
                ];
            }

        } catch (\Exception $e) {
            error_log("Error resending verification email: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while resending verification email'
            ];
        }
    }

    /**
     * Clean up expired verification tokens
     */
    public function cleanupExpiredTokens(): int
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET email_verification_token = NULL,
                    email_verification_expires = NULL
                WHERE email_verification_expires < NOW()
                AND email_verification_token IS NOT NULL
            ");
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log("Error cleaning up expired tokens: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get verification URL
     */
    private function getVerificationUrl(string $token): string
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        return $baseUrl . '/register?token=' . $token;
    }

    /**
     * Set token expiry hours
     */
    public function setTokenExpiryHours(int $hours): void
    {
        $this->tokenExpiryHours = max(1, min(168, $hours)); // Between 1 hour and 1 week
    }
}
