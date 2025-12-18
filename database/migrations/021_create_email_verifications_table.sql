-- Migration: Create email_verifications table
-- Description: Stores email verification records for reliable verification across environments
-- This table ensures email verification works in both development and production

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `verification_token` varchar(255) NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email_verification` (`email`),
  KEY `idx_email_verifications_token` (`verification_token`),
  KEY `idx_email_verifications_expires` (`expires_at`),
  KEY `idx_email_verifications_verified` (`verified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for cleanup queries
CREATE INDEX IF NOT EXISTS idx_email_verifications_created ON email_verifications(created_at);

