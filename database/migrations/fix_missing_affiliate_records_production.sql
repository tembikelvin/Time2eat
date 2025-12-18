-- ============================================================================
-- Fix Missing Affiliate Records - Production Version
-- This migration creates missing affiliate records for users who have
-- affiliate_code in users table but no corresponding record in affiliates table
-- ============================================================================

-- Set safe mode to prevent accidental updates
SET SQL_SAFE_UPDATES = 0;

-- Start transaction for data integrity
START TRANSACTION;

-- Log the start of migration
INSERT INTO migration_log (migration_name, started_at, status) 
VALUES ('fix_missing_affiliate_records', NOW(), 'started')
ON DUPLICATE KEY UPDATE started_at = NOW(), status = 'started';

-- Find users with affiliate_code but no affiliates record (for logging)
SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.affiliate_code,
    'Missing affiliate record' as issue
FROM users u
LEFT JOIN affiliates a ON u.id = a.user_id
WHERE u.affiliate_code IS NOT NULL 
  AND a.id IS NULL
  AND u.role = 'customer'
  AND u.deleted_at IS NULL;

-- Create missing affiliate records
INSERT INTO affiliates (user_id, affiliate_code, commission_rate, total_earnings, pending_earnings, paid_earnings, status, created_at, updated_at)
SELECT 
    u.id as user_id,
    u.affiliate_code,
    0.0500 as commission_rate,
    0.00 as total_earnings,
    0.00 as pending_earnings,
    0.00 as paid_earnings,
    'active' as status,
    NOW() as created_at,
    NOW() as updated_at
FROM users u
LEFT JOIN affiliates a ON u.id = a.user_id
WHERE u.affiliate_code IS NOT NULL 
  AND a.id IS NULL
  AND u.role = 'customer'
  AND u.deleted_at IS NULL;

-- Get count of records created
SET @records_created = ROW_COUNT();

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
    records_affected = @records_created
WHERE migration_name = 'fix_missing_affiliate_records';

-- Commit the transaction
COMMIT;

-- Reset safe mode
SET SQL_SAFE_UPDATES = 1;

-- Final summary
SELECT 
    @records_created as records_created,
    'Missing affiliate records created successfully' as description,
    NOW() as completed_at;
