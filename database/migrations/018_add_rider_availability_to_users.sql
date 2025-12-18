-- Migration: Add rider availability to users table
-- This migration adds the is_available column to the users table for delivery riders

-- Add is_available column to users table
ALTER TABLE `users` 
ADD COLUMN `is_available` boolean DEFAULT FALSE COMMENT 'Rider availability status' AFTER `status`;

-- Add index for performance
CREATE INDEX `idx_users_is_available` ON `users` (`is_available`);

-- Add index for rider availability queries
CREATE INDEX `idx_users_role_available` ON `users` (`role`, `is_available`);

-- Update existing riders to be offline by default
UPDATE `users` 
SET `is_available` = FALSE 
WHERE `role` = 'rider' AND `is_available` IS NULL;

