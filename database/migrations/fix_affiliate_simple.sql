-- ============================================================================
-- AFFILIATE SYSTEM SIMPLE FIX - Essential fixes only
-- ============================================================================

-- 1. Add referred_by column to users table
ALTER TABLE `users` 
ADD COLUMN `referred_by` VARCHAR(20) NULL COMMENT 'Referral code used during signup';

ALTER TABLE `users`
ADD INDEX `idx_users_referred_by` (`referred_by`);

-- 2. Create affiliate_earnings table
CREATE TABLE `affiliate_earnings` (
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

-- 3. Add affiliate_code column to orders table
ALTER TABLE `orders`
ADD COLUMN `affiliate_code` VARCHAR(20) NULL COMMENT 'Affiliate referral code used';

ALTER TABLE `orders`
ADD COLUMN `affiliate_commission` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Commission amount for affiliate';

ALTER TABLE `orders`
ADD INDEX `idx_orders_affiliate_code` (`affiliate_code`);

-- 4. Add unique constraint to affiliate_referrals
ALTER TABLE `affiliate_referrals`
ADD UNIQUE KEY `unique_affiliate_referred_user` (`affiliate_id`, `referred_user_id`);

-- 5. Add indexes for performance
ALTER TABLE `affiliate_earnings`
ADD INDEX `idx_affiliate_earnings_affiliate_status` (`affiliate_id`, `status`);

ALTER TABLE `affiliate_earnings`
ADD INDEX `idx_affiliate_earnings_affiliate_earned_at` (`affiliate_id`, `earned_at`);

ALTER TABLE `affiliate_referrals`
ADD INDEX `idx_affiliate_referrals_affiliate_status` (`affiliate_id`, `status`);

ALTER TABLE `affiliate_payouts`
ADD INDEX `idx_affiliate_payouts_affiliate_status` (`affiliate_id`, `status`);

-- 6. Insert default settings
INSERT IGNORE INTO `site_settings` (`key`, `value`, `type`, `group`, `description`, `is_public`) VALUES
('affiliate_default_commission_rate', '5.00', 'decimal', 'affiliate', 'Default commission rate for new affiliates (%)', 0),
('affiliate_min_withdrawal', '10000', 'integer', 'affiliate', 'Minimum withdrawal amount (XAF)', 0),
('affiliate_max_pending_withdrawals', '3', 'integer', 'affiliate', 'Maximum pending withdrawals per affiliate', 0),
('affiliate_enabled', '1', 'boolean', 'affiliate', 'Enable affiliate system', 1),
('affiliate_signup_bonus', '0', 'decimal', 'affiliate', 'Bonus for new affiliate signups (XAF)', 0);

