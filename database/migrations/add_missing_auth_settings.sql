-- Add missing auth settings to production database
-- This migration adds auth settings that exist in development but are missing in production

-- Insert missing auth settings if they don't exist
INSERT IGNORE INTO `site_settings` (`key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
-- Auto-approve customers setting
('auto_approve_customers', 'false', 'boolean', 'auth', 'Automatically approve customer accounts without admin review', FALSE, NOW(), NOW()),

-- Email verification expiry setting
('email_verification_expiry', '2', 'integer', 'auth', 'Email verification token expiry in hours', FALSE, NOW(), NOW()),

-- Email verification method setting
('email_verification_method', 'token', 'string', 'auth', 'Email verification method: token or code', FALSE, NOW(), NOW()),

-- Registration enabled setting (alias for allow_registration)
('registration_enabled', 'true', 'boolean', 'auth', 'Allow new user registrations', TRUE, NOW(), NOW());

-- Update existing auth settings descriptions if needed
UPDATE `site_settings` 
SET `description` = 'Allow new user registration', `updated_at` = NOW()
WHERE `key` = 'allow_registration' AND `group` = 'auth';

UPDATE `site_settings` 
SET `description` = 'Require email verification for new user registrations', `updated_at` = NOW()
WHERE `key` = 'email_verification_required' AND `group` = 'auth';

-- Verify the settings were added
SELECT `key`, `value`, `type`, `description` 
FROM `site_settings` 
WHERE `group` = 'auth' 
ORDER BY `key`;
