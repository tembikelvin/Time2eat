-- ============================================================================
-- Fix Affiliate Code Synchronization
-- This migration ensures that users who have affiliate records also have
-- their affiliate_code properly set in the users table
-- ============================================================================

-- Update users table to sync affiliate_code from affiliates table
UPDATE users u
INNER JOIN affiliates a ON u.id = a.user_id
SET u.affiliate_code = a.affiliate_code,
    u.updated_at = NOW()
WHERE u.affiliate_code IS NULL 
  AND a.affiliate_code IS NOT NULL
  AND a.status = 'active';

-- Log the number of users updated
SELECT 
    COUNT(*) as users_updated,
    'Users updated with affiliate codes from affiliates table' as description
FROM users u
INNER JOIN affiliates a ON u.id = a.user_id
WHERE u.affiliate_code = a.affiliate_code
  AND a.status = 'active';

-- Verify the sync by showing users with affiliate records and their codes
SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.affiliate_code as user_affiliate_code,
    a.affiliate_code as affiliate_table_code,
    a.status as affiliate_status,
    CASE 
        WHEN u.affiliate_code = a.affiliate_code THEN 'SYNCED'
        ELSE 'MISMATCH'
    END as sync_status
FROM users u
INNER JOIN affiliates a ON u.id = a.user_id
ORDER BY u.id;
