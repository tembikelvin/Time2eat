-- Database Backup
-- Generated: 2025-10-29 16:29:41
-- Database: time2eat

SET FOREIGN_KEY_CHECKS=0;

-- Table structure for table `affiliate_earnings`
DROP TABLE IF EXISTS `affiliate_earnings`;
CREATE TABLE `affiliate_earnings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL COMMENT 'The customer who made the order',
  `amount` decimal(10,2) NOT NULL,
  `type` enum('referral','bonus','commission') COLLATE utf8mb4_unicode_ci DEFAULT 'referral',
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'confirmed',
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_affiliate_earnings_affiliate` (`affiliate_id`),
  KEY `idx_affiliate_earnings_order` (`order_id`),
  KEY `idx_affiliate_earnings_customer` (`customer_id`),
  KEY `idx_affiliate_earnings_earned_at` (`earned_at`),
  KEY `idx_affiliate_earnings_type` (`type`),
  KEY `idx_affiliate_earnings_status` (`status`),
  KEY `idx_affiliate_earnings_affiliate_status` (`affiliate_id`,`status`),
  KEY `idx_affiliate_earnings_affiliate_earned_at` (`affiliate_id`,`earned_at`),
  CONSTRAINT `fk_affiliate_earnings_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_affiliate_earnings_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_affiliate_earnings_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `affiliate_earnings`
