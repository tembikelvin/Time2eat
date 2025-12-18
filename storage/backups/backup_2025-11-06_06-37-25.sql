-- Database Backup
-- Generated: 2025-11-06 06:37:30
-- Database: time2eat

SET FOREIGN_KEY_CHECKS=0;

-- Table structure for table `affiliate_payouts`
DROP TABLE IF EXISTS `affiliate_payouts`;
CREATE TABLE `affiliate_payouts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'bank_transfer, mobile_money, etc.',
  `reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_affiliate_payouts_affiliate` (`affiliate_id`),
  KEY `idx_affiliate_payouts_status_date` (`status`,`created_at`),
  CONSTRAINT `affiliate_payouts_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `affiliate_payouts`

-- Table structure for table `affiliate_referrals`
DROP TABLE IF EXISTS `affiliate_referrals`;
CREATE TABLE `affiliate_referrals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` bigint unsigned NOT NULL,
  `referred_user_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `commission_amount` decimal(8,2) DEFAULT '0.00',
  `status` enum('pending','confirmed','paid','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_affiliate_referrals_affiliate` (`affiliate_id`),
  KEY `idx_affiliate_referrals_referred_user` (`referred_user_id`),
  KEY `idx_affiliate_referrals_order` (`order_id`),
  KEY `idx_affiliate_referrals_affiliate_status` (`affiliate_id`,`status`,`created_at`),
  CONSTRAINT `affiliate_referrals_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affiliate_referrals_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `affiliate_referrals_referred_user_id_foreign` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `affiliate_referrals`

-- Table structure for table `affiliates`
DROP TABLE IF EXISTS `affiliates`;
CREATE TABLE `affiliates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `affiliate_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commission_rate` decimal(5,4) DEFAULT '0.0500' COMMENT 'Commission rate (5%)',
  `total_referrals` int DEFAULT '0',
  `total_earnings` decimal(12,2) DEFAULT '0.00',
  `pending_earnings` decimal(10,2) DEFAULT '0.00',
  `paid_earnings` decimal(12,2) DEFAULT '0.00',
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `payment_details` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliate_code` (`affiliate_code`),
  UNIQUE KEY `affiliates_user_id_unique` (`user_id`),
  UNIQUE KEY `affiliates_affiliate_code_unique` (`affiliate_code`),
  CONSTRAINT `affiliates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `affiliates`
