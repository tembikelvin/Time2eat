-- ============================================================================
-- AFFILIATE SYSTEM FIX - Resolve all conflicts and missing components
-- ============================================================================

-- 1. Add referred_by column to users table if it doesn't exist
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `referred_by` VARCHAR(20) NULL COMMENT 'Referral code used during signup',
ADD INDEX IF NOT EXISTS `idx_users_referred_by` (`referred_by`);

-- 2. Create affiliate_earnings table (missing but referenced in code)
CREATE TABLE IF NOT EXISTS `affiliate_earnings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `affiliate_id` BIGINT UNSIGNED NOT NULL,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL COMMENT 'The customer who made the order',
    `amount` DECIMAL(10,2) NOT NULL,
    `type` ENUM('referral', 'bonus', 'commission') DEFAULT 'referral',
    `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'confirmed',
    `earned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_affiliate_earnings_affiliate` (`affiliate_id`),
    KEY `idx_affiliate_earnings_order` (`order_id`),
    KEY `idx_affiliate_earnings_customer` (`customer_id`),
    KEY `idx_affiliate_earnings_earned_at` (`earned_at`),
    KEY `idx_affiliate_earnings_type` (`type`),
    KEY `idx_affiliate_earnings_status` (`status`),
    CONSTRAINT `fk_affiliate_earnings_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_affiliate_earnings_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_affiliate_earnings_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Ensure affiliate_referrals table has correct structure
-- Drop and recreate to ensure consistency
DROP TABLE IF EXISTS `affiliate_referrals_backup`;
CREATE TABLE `affiliate_referrals_backup` AS SELECT * FROM `affiliate_referrals`;

DROP TABLE IF EXISTS `affiliate_referrals`;
CREATE TABLE `affiliate_referrals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `affiliate_id` BIGINT UNSIGNED NOT NULL,
    `referred_user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user who was referred',
    `order_id` BIGINT UNSIGNED NULL COMMENT 'First order by referred user',
    `commission_amount` DECIMAL(8,2) DEFAULT 0.00,
    `status` ENUM('pending', 'confirmed', 'paid', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_affiliate_referred_user` (`affiliate_id`, `referred_user_id`),
    KEY `idx_affiliate_referrals_affiliate` (`affiliate_id`),
    KEY `idx_affiliate_referrals_referred_user` (`referred_user_id`),
    KEY `idx_affiliate_referrals_order` (`order_id`),
    KEY `idx_affiliate_referrals_status` (`status`),
    CONSTRAINT `fk_affiliate_referrals_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_affiliate_referrals_referred_user` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_affiliate_referrals_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restore data
INSERT IGNORE INTO `affiliate_referrals` SELECT * FROM `affiliate_referrals_backup`;
DROP TABLE `affiliate_referrals_backup`;

-- 4. Ensure affiliate_payouts table has correct structure
ALTER TABLE `affiliate_payouts`
ADD COLUMN IF NOT EXISTS `withdrawal_id` BIGINT UNSIGNED NULL AFTER `id`,
ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) NULL AFTER `method`,
ADD COLUMN IF NOT EXISTS `payment_details` JSON NULL AFTER `payment_method`,
ADD COLUMN IF NOT EXISTS `transaction_id` VARCHAR(100) NULL AFTER `payment_details`,
ADD COLUMN IF NOT EXISTS `failure_reason` TEXT NULL AFTER `transaction_id`,
ADD COLUMN IF NOT EXISTS `completed_at` TIMESTAMP NULL AFTER `processed_at`;

-- Update status enum if needed
ALTER TABLE `affiliate_payouts` 
MODIFY COLUMN `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'pending_manual_review') DEFAULT 'pending';

-- 5. Add affiliate_code column to orders table if it doesn't exist
ALTER TABLE `orders`
ADD COLUMN IF NOT EXISTS `affiliate_code` VARCHAR(20) NULL COMMENT 'Affiliate referral code used',
ADD COLUMN IF NOT EXISTS `affiliate_commission` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Commission amount for affiliate',
ADD INDEX IF NOT EXISTS `idx_orders_affiliate_code` (`affiliate_code`);

-- 6. Create trigger to track referrals when users sign up
DROP TRIGGER IF EXISTS `track_affiliate_referral_on_signup`;

DELIMITER $$

CREATE TRIGGER `track_affiliate_referral_on_signup`
AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
    DECLARE v_affiliate_id BIGINT UNSIGNED;
    
    -- If user signed up with a referral code
    IF NEW.referred_by IS NOT NULL AND NEW.referred_by != '' THEN
        -- Get the affiliate ID
        SELECT id INTO v_affiliate_id
        FROM affiliates
        WHERE affiliate_code = NEW.referred_by AND status = 'active'
        LIMIT 1;
        
        -- If affiliate exists, create referral record
        IF v_affiliate_id IS NOT NULL THEN
            INSERT INTO affiliate_referrals (
                affiliate_id,
                referred_user_id,
                status,
                created_at
            ) VALUES (
                v_affiliate_id,
                NEW.id,
                'pending',
                NOW()
            ) ON DUPLICATE KEY UPDATE updated_at = NOW();
            
            -- Update affiliate total_referrals count
            UPDATE affiliates
            SET total_referrals = total_referrals + 1,
                updated_at = NOW()
            WHERE id = v_affiliate_id;
        END IF;
    END IF;
END$$

DELIMITER ;

-- 7. Create trigger to calculate commission when order is delivered
DROP TRIGGER IF EXISTS `calculate_affiliate_commission_on_delivery`;

DELIMITER $$

CREATE TRIGGER `calculate_affiliate_commission_on_delivery`
AFTER UPDATE ON `orders`
FOR EACH ROW
BEGIN
    DECLARE v_affiliate_id BIGINT UNSIGNED;
    DECLARE v_commission_rate DECIMAL(5,4);
    DECLARE v_commission_amount DECIMAL(10,2);
    DECLARE v_referred_by VARCHAR(20);
    
    -- Only process when order status changes to 'delivered'
    IF NEW.status = 'delivered' AND OLD.status != 'delivered' THEN
        
        -- Get the customer's referral code
        SELECT referred_by INTO v_referred_by
        FROM users
        WHERE id = NEW.customer_id AND referred_by IS NOT NULL
        LIMIT 1;
        
        -- If customer was referred and order has affiliate code
        IF (v_referred_by IS NOT NULL OR NEW.affiliate_code IS NOT NULL) THEN
            
            -- Use order's affiliate code if available, otherwise use customer's referred_by
            SET v_referred_by = COALESCE(NEW.affiliate_code, v_referred_by);
            
            -- Get affiliate details
            SELECT id, commission_rate INTO v_affiliate_id, v_commission_rate
            FROM affiliates
            WHERE affiliate_code = v_referred_by AND status = 'active'
            LIMIT 1;
            
            -- Calculate commission
            IF v_affiliate_id IS NOT NULL THEN
                SET v_commission_amount = ROUND((NEW.subtotal * v_commission_rate), 2);
                
                -- Update order with commission if not already set
                IF NEW.affiliate_commission = 0 THEN
                    UPDATE orders
                    SET affiliate_commission = v_commission_amount,
                        affiliate_code = v_referred_by
                    WHERE id = NEW.id;
                END IF;
                
                -- Record earning in affiliate_earnings table
                INSERT INTO affiliate_earnings (
                    affiliate_id,
                    order_id,
                    customer_id,
                    amount,
                    type,
                    status,
                    earned_at
                ) VALUES (
                    v_affiliate_id,
                    NEW.id,
                    NEW.customer_id,
                    v_commission_amount,
                    'referral',
                    'confirmed',
                    NOW()
                ) ON DUPLICATE KEY UPDATE
                    amount = v_commission_amount,
                    status = 'confirmed',
                    updated_at = NOW();
                
                -- Update affiliate balance
                UPDATE affiliates
                SET total_earnings = total_earnings + v_commission_amount,
                    pending_earnings = pending_earnings + v_commission_amount,
                    updated_at = NOW()
                WHERE id = v_affiliate_id;
                
                -- Update referral record with order and commission
                UPDATE affiliate_referrals
                SET order_id = NEW.id,
                    commission_amount = commission_amount + v_commission_amount,
                    status = 'confirmed',
                    updated_at = NOW()
                WHERE affiliate_id = v_affiliate_id 
                  AND referred_user_id = NEW.customer_id
                LIMIT 1;
            END IF;
        END IF;
    END IF;
END$$

DELIMITER ;

-- 8. Create stored procedure for manual commission calculation
DROP PROCEDURE IF EXISTS `RecalculateAffiliateCommissions`;

DELIMITER $$

CREATE PROCEDURE `RecalculateAffiliateCommissions`(IN p_affiliate_id BIGINT UNSIGNED)
BEGIN
    DECLARE v_total_earnings DECIMAL(12,2) DEFAULT 0.00;
    DECLARE v_pending_earnings DECIMAL(12,2) DEFAULT 0.00;
    DECLARE v_paid_earnings DECIMAL(12,2) DEFAULT 0.00;
    
    -- Calculate total earnings from affiliate_earnings
    SELECT COALESCE(SUM(amount), 0) INTO v_total_earnings
    FROM affiliate_earnings
    WHERE affiliate_id = p_affiliate_id AND status = 'confirmed';
    
    -- Calculate paid earnings from affiliate_payouts
    SELECT COALESCE(SUM(amount), 0) INTO v_paid_earnings
    FROM affiliate_payouts
    WHERE affiliate_id = p_affiliate_id AND status = 'completed';
    
    -- Pending earnings = total - paid
    SET v_pending_earnings = v_total_earnings - v_paid_earnings;
    
    -- Update affiliate record
    UPDATE affiliates
    SET total_earnings = v_total_earnings,
        pending_earnings = v_pending_earnings,
        paid_earnings = v_paid_earnings,
        updated_at = NOW()
    WHERE id = p_affiliate_id;
    
    SELECT v_total_earnings as total_earnings,
           v_pending_earnings as pending_earnings,
           v_paid_earnings as paid_earnings;
END$$

DELIMITER ;

-- 9. Add indexes for performance
CREATE INDEX IF NOT EXISTS `idx_affiliate_earnings_affiliate_status` ON `affiliate_earnings` (`affiliate_id`, `status`);
CREATE INDEX IF NOT EXISTS `idx_affiliate_earnings_affiliate_earned_at` ON `affiliate_earnings` (`affiliate_id`, `earned_at`);
CREATE INDEX IF NOT EXISTS `idx_affiliate_referrals_affiliate_status` ON `affiliate_referrals` (`affiliate_id`, `status`);
CREATE INDEX IF NOT EXISTS `idx_affiliate_payouts_affiliate_status` ON `affiliate_payouts` (`affiliate_id`, `status`);

-- 10. Insert default settings if not exists
INSERT IGNORE INTO `site_settings` (`key`, `value`, `type`, `group`, `description`, `is_public`) VALUES
('affiliate_default_commission_rate', '5.00', 'decimal', 'affiliate', 'Default commission rate for new affiliates (%)', 0),
('affiliate_min_withdrawal', '10000', 'integer', 'affiliate', 'Minimum withdrawal amount (XAF)', 0),
('affiliate_max_pending_withdrawals', '3', 'integer', 'affiliate', 'Maximum pending withdrawals per affiliate', 0),
('affiliate_enabled', '1', 'boolean', 'affiliate', 'Enable affiliate system', 1),
('affiliate_signup_bonus', '0', 'decimal', 'affiliate', 'Bonus for new affiliate signups (XAF)', 0);

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Check all affiliate tables exist
SELECT 
    'affiliates' as table_name,
    COUNT(*) as row_count
FROM affiliates
UNION ALL
SELECT 'affiliate_referrals', COUNT(*) FROM affiliate_referrals
UNION ALL
SELECT 'affiliate_earnings', COUNT(*) FROM affiliate_earnings
UNION ALL
SELECT 'affiliate_withdrawals', COUNT(*) FROM affiliate_withdrawals
UNION ALL
SELECT 'affiliate_payouts', COUNT(*) FROM affiliate_payouts;

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================
SELECT 'Affiliate system fix completed successfully!' as message;

