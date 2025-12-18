-- ============================================================================
-- Fix Missing Affiliate Records
-- This migration creates missing affiliate records for CUSTOMERS who have
-- affiliate_code in users table but no corresponding record in affiliates table
--
-- NOTE: Only customers should be affiliates. Vendors/riders have their own earning systems.
-- ============================================================================

-- Find customers with affiliate_code but no affiliates record
SELECT
    u.id,
    u.first_name,
    u.last_name,
    u.role,
    u.affiliate_code,
    'Missing affiliate record' as issue
FROM users u
LEFT JOIN affiliates a ON u.id = a.user_id
WHERE u.affiliate_code IS NOT NULL
  AND a.id IS NULL
  AND u.role = 'customer'
  AND u.deleted_at IS NULL;

-- Create missing affiliate records for customers only
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

-- Verify the fix by showing all customers with affiliate codes and their records
SELECT
    u.id,
    u.first_name,
    u.last_name,
    u.role,
    u.affiliate_code as user_affiliate_code,
    a.affiliate_code as affiliate_table_code,
    a.status as affiliate_status,
    CASE
        WHEN u.role = 'customer' AND u.affiliate_code = a.affiliate_code THEN 'SYNCED'
        WHEN u.role = 'customer' AND a.id IS NULL THEN 'MISSING AFFILIATE RECORD'
        WHEN u.role != 'customer' THEN 'NON-CUSTOMER (OK to have no affiliate record)'
        ELSE 'MISMATCH'
    END as sync_status
FROM users u
LEFT JOIN affiliates a ON u.id = a.user_id
WHERE u.affiliate_code IS NOT NULL
  AND u.deleted_at IS NULL
ORDER BY u.role, u.id;

-- Count of records created for customers
SELECT
    COUNT(*) as records_created,
    'Missing affiliate records created for customers' as description
FROM users u
LEFT JOIN affiliates a ON u.id = a.user_id
WHERE u.affiliate_code IS NOT NULL
  AND a.id IS NOT NULL
  AND u.role = 'customer'
  AND u.deleted_at IS NULL;

-- ============================================================================
-- Fix Referral Counts
-- Update total_referrals count for all affiliates based on actual referrals
-- ============================================================================

UPDATE affiliates a
SET total_referrals = (
    SELECT COUNT(*)
    FROM users u
    WHERE u.referred_by = a.user_id
),
updated_at = NOW();

-- Display users with referrals
SELECT
    u.id,
    u.first_name,
    u.last_name,
    u.email,
    u.affiliate_code,
    a.total_referrals,
    (SELECT COUNT(*) FROM users ref WHERE ref.referred_by = u.id) as actual_referrals,
    CASE
        WHEN a.total_referrals = (SELECT COUNT(*) FROM users ref WHERE ref.referred_by = u.id) THEN 'SYNCED'
        ELSE 'MISMATCH'
    END as sync_status
FROM users u
INNER JOIN affiliates a ON u.id = a.user_id
WHERE u.affiliate_code IS NOT NULL
ORDER BY a.total_referrals DESC, u.id;
