-- ============================================================================
-- Fix Affiliate Code Mismatches - Production Version
-- This migration fixes mismatches between users.affiliate_code and affiliates.affiliate_code
-- The affiliates table is considered the source of truth for affiliate codes
-- ============================================================================

-- Set safe mode to prevent accidental updates
SET SQL_SAFE_UPDATES = 0;

-- Start transaction for data integrity
START TRANSACTION;

-- Create migration log table if it doesn't exist
CREATE TABLE IF NOT EXISTS `migration_log` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(255) NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `status` enum('started','completed','failed') NOT NULL DEFAULT 'started',
  `records_affected` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_name_unique` (`migration_name`),
  KEY `idx_status` (`status`),
  KEY `idx_started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log the start of migration
INSERT INTO migration_log (migration_name, started_at, status) 
VALUES ('fix_affiliate_code_mismatches', NOW(), 'started')
ON DUPLICATE KEY UPDATE started_at = NOW(), status = 'started';

-- Find and display mismatches before fixing
SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.affiliate_code as user_affiliate_code,
    a.affiliate_code as affiliate_table_code,
    'MISMATCH - Will be fixed' as status
FROM users u
INNER JOIN affiliates a ON u.id = a.user_id
WHERE u.affiliate_code IS NOT NULL 
  AND u.affiliate_code != a.affiliate_code
  AND u.role = 'customer'
  AND u.deleted_at IS NULL;

-- Count mismatches
SET @mismatch_count = (
    SELECT COUNT(*)
    FROM users u
    INNER JOIN affiliates a ON u.id = a.user_id
    WHERE u.affiliate_code IS NOT NULL 
      AND u.affiliate_code != a.affiliate_code
      AND u.role = 'customer'
      AND u.deleted_at IS NULL
);

-- Update users table to match affiliates table (affiliates table is source of truth)
UPDATE users u
INNER JOIN affiliates a ON u.id = a.user_id
SET u.affiliate_code = a.affiliate_code,
    u.updated_at = NOW()
WHERE u.affiliate_code IS NOT NULL 
  AND u.affiliate_code != a.affiliate_code
  AND u.role = 'customer'
  AND u.deleted_at IS NULL;

-- Get count of records updated
SET @records_updated = ROW_COUNT();

-- Verify the fix by showing all users with affiliate codes and their records
SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.affiliate_code as user_affiliate_code,
    a.affiliate_code as affiliate_table_code,
    a.status as affiliate_status,
    CASE 
        WHEN u.affiliate_code = a.affiliate_code THEN 'SYNCED'
        WHEN a.id IS NULL THEN 'MISSING AFFILIATE RECORD'
        ELSE 'MISMATCH'
    END as sync_status
FROM users u
LEFT JOIN affiliates a ON u.id = a.user_id
WHERE u.affiliate_code IS NOT NULL
  AND u.role = 'customer'
  AND u.deleted_at IS NULL
ORDER BY u.id;

-- Log the completion of migration
UPDATE migration_log 
SET completed_at = NOW(), 
    status = 'completed',
    records_affected = @records_updated
WHERE migration_name = 'fix_affiliate_code_mismatches';

-- Commit the transaction
COMMIT;

-- Reset safe mode
SET SQL_SAFE_UPDATES = 1;

-- Final summary
SELECT 
    @mismatch_count as mismatches_found,
    @records_updated as records_updated,
    'Affiliate code mismatches fixed successfully' as description,
    NOW() as completed_at;
