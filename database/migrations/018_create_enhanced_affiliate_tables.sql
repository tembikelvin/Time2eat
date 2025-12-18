-- ============================================================================
-- ENHANCED AFFILIATE SYSTEM TABLES
-- Additional tables for comprehensive affiliate management
-- ============================================================================

-- Admin logs table for audit trail
CREATE TABLE IF NOT EXISTS `admin_logs` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_id` bigint(20) UNSIGNED NOT NULL,
    `action` varchar(100) NOT NULL,
    `details` json DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_admin_logs_admin_id` (`admin_id`),
    KEY `idx_admin_logs_action` (`action`),
    KEY `idx_admin_logs_created_at` (`created_at`),
    CONSTRAINT `admin_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Affiliate messages table for communication
CREATE TABLE IF NOT EXISTS `affiliate_messages` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `affiliate_id` bigint(20) UNSIGNED NOT NULL,
    `subject` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `sender_type` enum('admin','system') DEFAULT 'admin',
    `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
    `is_read` tinyint(1) DEFAULT 0,
    `read_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_affiliate_messages_affiliate_id` (`affiliate_id`),
    KEY `idx_affiliate_messages_sender` (`sender_type`, `sender_id`),
    KEY `idx_affiliate_messages_created_at` (`created_at`),
    CONSTRAINT `affiliate_messages_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE,
    CONSTRAINT `affiliate_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Affiliate campaigns table for marketing campaigns
CREATE TABLE IF NOT EXISTS `affiliate_campaigns` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `type` enum('referral','bonus','seasonal','milestone') NOT NULL,
    `description` text DEFAULT NULL,
    `start_date` date NOT NULL,
    `end_date` date DEFAULT NULL,
    `bonus_rate` decimal(5,2) DEFAULT 0.00 COMMENT 'Additional bonus percentage',
    `target_amount` decimal(12,2) DEFAULT NULL,
    `current_amount` decimal(12,2) DEFAULT 0.00,
    `max_participants` int(11) DEFAULT NULL,
    `current_participants` int(11) DEFAULT 0,
    `status` enum('draft','active','paused','completed','cancelled') DEFAULT 'draft',
    `terms_conditions` text DEFAULT NULL,
    `created_by` bigint(20) UNSIGNED NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_affiliate_campaigns_type` (`type`),
    KEY `idx_affiliate_campaigns_status` (`status`),
    KEY `idx_affiliate_campaigns_dates` (`start_date`, `end_date`),
    KEY `idx_affiliate_campaigns_created_by` (`created_by`),
    CONSTRAINT `affiliate_campaigns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Affiliate campaign participants table
CREATE TABLE IF NOT EXISTS `affiliate_campaign_participants` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` bigint(20) UNSIGNED NOT NULL,
    `affiliate_id` bigint(20) UNSIGNED NOT NULL,
    `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `total_earnings` decimal(12,2) DEFAULT 0.00,
    `total_referrals` int(11) DEFAULT 0,
    `status` enum('active','completed','withdrawn') DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_campaign_affiliate` (`campaign_id`, `affiliate_id`),
    KEY `idx_campaign_participants_campaign_id` (`campaign_id`),
    KEY `idx_campaign_participants_affiliate_id` (`affiliate_id`),
    KEY `idx_campaign_participants_status` (`status`),
    CONSTRAINT `campaign_participants_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `affiliate_campaigns` (`id`) ON DELETE CASCADE,
    CONSTRAINT `campaign_participants_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Affiliate performance metrics table for analytics
CREATE TABLE IF NOT EXISTS `affiliate_performance_metrics` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `affiliate_id` bigint(20) UNSIGNED NOT NULL,
    `date` date NOT NULL,
    `clicks` int(11) DEFAULT 0,
    `conversions` int(11) DEFAULT 0,
    `earnings` decimal(10,2) DEFAULT 0.00,
    `referrals` int(11) DEFAULT 0,
    `conversion_rate` decimal(5,2) DEFAULT 0.00,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_affiliate_date` (`affiliate_id`, `date`),
    KEY `idx_performance_metrics_date` (`date`),
    KEY `idx_performance_metrics_affiliate_id` (`affiliate_id`),
    CONSTRAINT `performance_metrics_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update affiliates table to add missing columns if they don't exist
ALTER TABLE `affiliates` 
ADD COLUMN IF NOT EXISTS `referral_source` varchar(50) DEFAULT NULL COMMENT 'Source where affiliate was acquired',
ADD COLUMN IF NOT EXISTS `last_activity_at` timestamp NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `notes` text DEFAULT NULL COMMENT 'Admin notes about affiliate';

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_affiliates_referral_source` ON `affiliates` (`referral_source`);
CREATE INDEX IF NOT EXISTS `idx_affiliates_last_activity` ON `affiliates` (`last_activity_at`);
CREATE INDEX IF NOT EXISTS `idx_affiliates_status_earnings` ON `affiliates` (`status`, `total_earnings`);

-- Update affiliate_earnings table to add commission_amount column if it doesn't exist
ALTER TABLE `affiliate_earnings` 
ADD COLUMN IF NOT EXISTS `commission_amount` decimal(10,2) DEFAULT NULL COMMENT 'Commission amount earned',
ADD COLUMN IF NOT EXISTS `commission_rate` decimal(5,4) DEFAULT NULL COMMENT 'Commission rate applied';

-- Add indexes for affiliate_earnings
CREATE INDEX IF NOT EXISTS `idx_affiliate_earnings_commission` ON `affiliate_earnings` (`commission_amount`);
CREATE INDEX IF NOT EXISTS `idx_affiliate_earnings_date_range` ON `affiliate_earnings` (`earned_at`, `status`);

-- ============================================================================
-- SAMPLE DATA FOR TESTING
-- ============================================================================

-- Insert sample admin logs
INSERT IGNORE INTO `admin_logs` (`admin_id`, `action`, `details`, `ip_address`, `user_agent`) VALUES
(1, 'affiliate_withdrawal_approved', '{"withdrawal_id": 1, "amount": 50000, "affiliate_id": 1}', '127.0.0.1', 'Mozilla/5.0 Test Browser'),
(1, 'affiliate_commission_updated', '{"affiliate_id": 2, "old_rate": 5.00, "new_rate": 7.50}', '127.0.0.1', 'Mozilla/5.0 Test Browser'),
(1, 'affiliate_campaign_created', '{"campaign_id": 1, "name": "Holiday Bonus Campaign", "type": "seasonal"}', '127.0.0.1', 'Mozilla/5.0 Test Browser');

-- Insert sample affiliate campaigns
INSERT IGNORE INTO `affiliate_campaigns` (`id`, `name`, `type`, `description`, `start_date`, `end_date`, `bonus_rate`, `status`, `created_by`) VALUES
(1, 'Holiday Bonus Campaign', 'seasonal', 'Special holiday campaign with 2% bonus commission', '2024-12-01', '2024-12-31', 2.00, 'active', 1),
(2, 'New Year Referral Drive', 'referral', 'Double referral bonuses for January', '2025-01-01', '2025-01-31', 5.00, 'draft', 1),
(3, 'Top Performer Milestone', 'milestone', 'Bonus for affiliates reaching 100 referrals', '2024-01-01', NULL, 10.00, 'active', 1);

-- Insert sample affiliate messages
INSERT IGNORE INTO `affiliate_messages` (`affiliate_id`, `subject`, `message`, `sender_type`, `sender_id`) VALUES
(1, 'Welcome to our Affiliate Program!', 'Welcome to our affiliate program! We are excited to have you on board. Your referral code is ready to use.', 'admin', 1),
(2, 'Congratulations on your milestone!', 'Congratulations on reaching 50 referrals! You have earned a special bonus.', 'admin', 1),
(3, 'Your payout has been processed', 'Your affiliate payout of 25,000 XAF has been successfully processed and will be credited to your account within 24 hours.', 'admin', 1);

-- Update existing affiliates with sample data
UPDATE `affiliates` SET 
    `referral_source` = CASE 
        WHEN id % 3 = 0 THEN 'social_media'
        WHEN id % 3 = 1 THEN 'website'
        ELSE 'referral'
    END,
    `last_activity_at` = DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY),
    `notes` = CASE 
        WHEN id % 2 = 0 THEN 'High performing affiliate'
        ELSE 'Regular affiliate'
    END
WHERE `referral_source` IS NULL;

-- ============================================================================
-- VIEWS FOR ENHANCED REPORTING
-- ============================================================================

-- Enhanced affiliate statistics view
CREATE OR REPLACE VIEW `enhanced_affiliate_stats` AS
SELECT 
    a.id,
    a.user_id,
    a.affiliate_code,
    a.commission_rate,
    a.total_earnings,
    a.pending_earnings,
    a.paid_earnings,
    a.total_referrals,
    a.status,
    a.referral_source,
    a.last_activity_at,
    u.first_name,
    u.last_name,
    u.email,
    u.phone,
    COUNT(DISTINCT ref_users.id) as actual_referrals,
    COUNT(DISTINCT ae.id) as total_earning_transactions,
    COALESCE(SUM(CASE WHEN ae.earned_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN ae.amount ELSE 0 END), 0) as earnings_last_30_days,
    COALESCE(SUM(CASE WHEN ae.earned_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN ae.amount ELSE 0 END), 0) as earnings_last_7_days,
    COUNT(DISTINCT aw.id) as total_withdrawal_requests,
    COUNT(DISTINCT CASE WHEN aw.status = 'pending' THEN aw.id END) as pending_withdrawals,
    COUNT(DISTINCT acp.id) as active_campaigns,
    COALESCE(AVG(ae.commission_amount), 0) as avg_commission_per_transaction
FROM affiliates a
JOIN users u ON a.user_id = u.id
LEFT JOIN users ref_users ON ref_users.referred_by = a.affiliate_code
LEFT JOIN affiliate_earnings ae ON a.id = ae.affiliate_id AND ae.status = 'confirmed'
LEFT JOIN affiliate_withdrawals aw ON a.id = aw.affiliate_id
LEFT JOIN affiliate_campaign_participants acp ON a.id = acp.affiliate_id AND acp.status = 'active'
GROUP BY a.id, a.user_id, a.affiliate_code, a.commission_rate, a.total_earnings, 
         a.pending_earnings, a.paid_earnings, a.total_referrals, a.status, 
         a.referral_source, a.last_activity_at, u.first_name, u.last_name, u.email, u.phone;

-- ============================================================================
-- TRIGGERS FOR AUTOMATIC UPDATES
-- ============================================================================

DELIMITER $$

-- Trigger to update affiliate last_activity_at when earnings are added
CREATE TRIGGER IF NOT EXISTS `update_affiliate_activity_after_earning`
AFTER INSERT ON `affiliate_earnings`
FOR EACH ROW
BEGIN
    UPDATE `affiliates` 
    SET `last_activity_at` = NOW(), `updated_at` = NOW()
    WHERE `id` = NEW.affiliate_id;
END$$

-- Trigger to update campaign participant stats
CREATE TRIGGER IF NOT EXISTS `update_campaign_participant_stats`
AFTER INSERT ON `affiliate_earnings`
FOR EACH ROW
BEGIN
    UPDATE `affiliate_campaign_participants` acp
    JOIN `affiliate_campaigns` ac ON acp.campaign_id = ac.id
    SET 
        acp.total_earnings = acp.total_earnings + NEW.amount,
        acp.total_referrals = acp.total_referrals + 1,
        acp.updated_at = NOW()
    WHERE acp.affiliate_id = NEW.affiliate_id 
    AND ac.status = 'active'
    AND NEW.earned_at BETWEEN ac.start_date AND COALESCE(ac.end_date, '2099-12-31');
END$$

DELIMITER ;

-- ============================================================================
-- PERFORMANCE OPTIMIZATION
-- ============================================================================

-- Additional indexes for better query performance
CREATE INDEX IF NOT EXISTS `idx_admin_logs_action_date` ON `admin_logs` (`action`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_affiliate_messages_read_status` ON `affiliate_messages` (`is_read`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_affiliate_campaigns_active` ON `affiliate_campaigns` (`status`, `start_date`, `end_date`);
CREATE INDEX IF NOT EXISTS `idx_performance_metrics_date_range` ON `affiliate_performance_metrics` (`date`, `affiliate_id`);

-- Optimize existing tables
OPTIMIZE TABLE `affiliates`;
OPTIMIZE TABLE `affiliate_earnings`;
OPTIMIZE TABLE `affiliate_withdrawals`;
OPTIMIZE TABLE `affiliate_payouts`;
