-- Migration: Add email verification fields to users table
-- Description: Adds email verification token and expiry fields for secure email verification

-- Add email verification fields to users table
ALTER TABLE `users` 
ADD COLUMN `email_verification_token` VARCHAR(255) NULL DEFAULT NULL AFTER `email_verified_at`,
ADD COLUMN `email_verification_expires` TIMESTAMP NULL DEFAULT NULL AFTER `email_verification_token`;

-- Add index for email verification token for faster lookups
ALTER TABLE `users` 
ADD INDEX `idx_users_email_verification_token` (`email_verification_token`);

-- Add index for email verification expiry for cleanup queries
ALTER TABLE `users` 
ADD INDEX `idx_users_email_verification_expires` (`email_verification_expires`);

-- Update existing users to have verified emails (for existing data)
UPDATE `users` 
SET `email_verified_at` = NOW() 
WHERE `email_verified_at` IS NULL 
AND `deleted_at` IS NULL;

