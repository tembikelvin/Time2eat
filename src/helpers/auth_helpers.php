<?php
/**
 * Authentication Helper Functions
 * Utility functions for authentication and email verification
 */

/**
 * Check if email verification is required
 */
function isEmailVerificationRequired(): bool
{
    try {
        require_once __DIR__ . '/../models/SiteSetting.php';
        $siteSetting = new \Time2Eat\Models\SiteSetting();
        return $siteSetting->get('email_verification_required', true);
    } catch (Exception $e) {
        error_log("Error checking email verification setting: " . $e->getMessage());
        return true; // Default to required for security
    }
}

/**
 * Check if registration is enabled
 */
function isRegistrationEnabled(): bool
{
    try {
        require_once __DIR__ . '/../models/SiteSetting.php';
        $siteSetting = new \Time2Eat\Models\SiteSetting();
        return $siteSetting->get('registration_enabled', true);
    } catch (Exception $e) {
        error_log("Error checking registration setting: " . $e->getMessage());
        return true; // Default to enabled
    }
}

/**
 * Check if auto-approval is enabled for customers
 */
function isAutoApproveCustomers(): bool
{
    try {
        require_once __DIR__ . '/../models/SiteSetting.php';
        $siteSetting = new \Time2Eat\Models\SiteSetting();
        return $siteSetting->get('auto_approve_customers', false);
    } catch (Exception $e) {
        error_log("Error checking auto-approval setting: " . $e->getMessage());
        return false; // Default to manual approval
    }
}

/**
 * Get email verification method
 */
function getEmailVerificationMethod(): string
{
    try {
        require_once __DIR__ . '/../models/SiteSetting.php';
        $siteSetting = new \Time2Eat\Models\SiteSetting();
        return $siteSetting->get('email_verification_method', 'token');
    } catch (Exception $e) {
        error_log("Error getting email verification method: " . $e->getMessage());
        return 'token'; // Default to token method
    }
}

/**
 * Get email verification expiry hours
 */
function getEmailVerificationExpiry(): int
{
    try {
        require_once __DIR__ . '/../models/SiteSetting.php';
        $siteSetting = new \Time2Eat\Models\SiteSetting();
        return (int)$siteSetting->get('email_verification_expiry', 24);
    } catch (Exception $e) {
        error_log("Error getting email verification expiry: " . $e->getMessage());
        return 24; // Default to 24 hours
    }
}