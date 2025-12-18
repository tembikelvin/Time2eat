-- ============================================================================
-- Tranzak Payment Gateway Configuration
-- Time2Eat Food Delivery Platform
-- ============================================================================
-- This script sets up the Tranzak payment gateway configuration in the
-- site_settings table. Run this after obtaining credentials from Tranzak.
-- ============================================================================

-- Insert or update Tranzak settings
INSERT INTO site_settings (`key`, `value`, `type`, `category`, `description`, `is_public`)
VALUES
    -- Enable/Disable Tranzak
    ('tranzak_enabled', 'true', 'boolean', 'payment', 'Enable Tranzak payment gateway', TRUE),

    -- App ID / Merchant ID
    ('tranzak_app_id', 'aplp1yf70tbaay', 'string', 'payment', 'Tranzak App ID / Merchant ID', FALSE),

    -- Production App Key (formerly called API Key)
    ('tranzak_api_key', 'PROD_1420487AE95C4A8AA2704A0773593E68', 'string', 'payment', 'Tranzak Production App Key', FALSE),

    -- Sandbox App Key
    ('tranzak_sandbox_api_key', 'SAND_100DA717BD5844F39B03AB73AC7DA448', 'string', 'payment', 'Tranzak Sandbox App Key', FALSE),

    -- Webhook Auth Key (Optional - configured in developer portal)
    ('tranzak_webhook_auth_key', '', 'string', 'payment', 'Tranzak Webhook Auth Key (set in developer portal)', FALSE),

    -- Environment Mode (true = sandbox, false = production)
    ('tranzak_sandbox_mode', 'false', 'boolean', 'payment', 'Use Tranzak sandbox environment for testing', FALSE),

    -- Currency
    ('tranzak_currency', 'XAF', 'string', 'payment', 'Default currency for Tranzak payments', TRUE),

    -- Country
    ('tranzak_country', 'CM', 'string', 'payment', 'Country code for Tranzak (Cameroon)', TRUE)

ON DUPLICATE KEY UPDATE 
    `value` = VALUES(`value`),
    `type` = VALUES(`type`),
    `category` = VALUES(`category`),
    `description` = VALUES(`description`),
    `is_public` = VALUES(`is_public`);

-- ============================================================================
-- Verify the settings were inserted
-- ============================================================================
SELECT 
    `key`, 
    CASE 
        WHEN `key` LIKE '%secret%' OR `key` LIKE '%api_key%' THEN CONCAT(LEFT(`value`, 10), '...', RIGHT(`value`, 4))
        ELSE `value`
    END AS `value_masked`,
    `type`,
    `category`,
    `description`
FROM site_settings 
WHERE `key` LIKE 'tranzak%'
ORDER BY `key`;

-- ============================================================================
-- IMPORTANT NOTES:
-- ============================================================================
-- 1. All required credentials are already configured!
-- 2. Set 'tranzak_sandbox_mode' to 'true' for testing, 'false' for production
-- 3. Configure webhooks in the Tranzak developer portal: https://developer.tranzak.me
--    - Event Type: REQUEST.COMPLETED
--    - Webhook URL: https://www.time2eat.org/api/payment/tranzak/notify
--    - Set an Auth Key in the portal and update 'tranzak_webhook_auth_key' if needed
-- 4. Official Documentation: https://docs.developer.tranzak.me
-- 5. For support: support@tranzak.net or +237 674 460 261
-- ============================================================================

