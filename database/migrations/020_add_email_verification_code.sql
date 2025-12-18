-- Add email verification code column to users table
-- This allows users to verify their email with a 6-digit code instead of a link

ALTER TABLE users 
ADD COLUMN email_verification_code VARCHAR(6) NULL AFTER email_verification_expires,
ADD INDEX idx_email_verification_code (email_verification_code);

-- Update existing users who have verification tokens to have codes instead
-- This is optional and can be run after the column is added
-- UPDATE users 
-- SET email_verification_code = LPAD(FLOOR(RAND() * 1000000), 6, '0')
-- WHERE email_verification_token IS NOT NULL 
-- AND email_verified_at IS NULL;
