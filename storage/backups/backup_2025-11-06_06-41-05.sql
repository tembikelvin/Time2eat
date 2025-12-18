-- Database Backup
-- Generated: 2025-11-06 06:41:06
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
INSERT INTO `affiliates` (`id`, `user_id`, `affiliate_code`, `commission_rate`, `total_referrals`, `total_earnings`, `pending_earnings`, `paid_earnings`, `status`, `payment_details`, `created_at`, `updated_at`) VALUES ('1', '1001', 'JOHN2024', '0.0500', '3', '150.00', '0.00', '0.00', 'active', NULL, '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `affiliates` (`id`, `user_id`, `affiliate_code`, `commission_rate`, `total_referrals`, `total_earnings`, `pending_earnings`, `paid_earnings`, `status`, `payment_details`, `created_at`, `updated_at`) VALUES ('2', '1002', 'MARY2024', '0.0500', '1', '75.00', '0.00', '0.00', 'active', NULL, '2025-10-31 22:02:01', '2025-10-31 22:02:01');

-- Table structure for table `analytics`
DROP TABLE IF EXISTS `analytics`;
CREATE TABLE `analytics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `referrer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_analytics_event` (`event_type`,`event_name`),
  KEY `idx_analytics_user_date` (`user_id`,`created_at`),
  KEY `idx_analytics_session` (`session_id`),
  CONSTRAINT `analytics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `analytics`
INSERT INTO `analytics` (`id`, `event_type`, `event_name`, `user_id`, `session_id`, `data`, `ip_address`, `user_agent`, `referrer`, `created_at`) VALUES ('1', 'page_view', 'homepage', '1001', NULL, '{\"page\": \"/\", \"referrer\": \"google.com\"}', NULL, NULL, NULL, '2025-10-31 21:02:01');
INSERT INTO `analytics` (`id`, `event_type`, `event_name`, `user_id`, `session_id`, `data`, `ip_address`, `user_agent`, `referrer`, `created_at`) VALUES ('2', 'page_view', 'restaurant_view', '1002', NULL, '{\"page\": \"/restaurant/mama-grace-kitchen\", \"restaurant_id\": 101}', NULL, NULL, NULL, '2025-10-31 20:02:01');
INSERT INTO `analytics` (`id`, `event_type`, `event_name`, `user_id`, `session_id`, `data`, `ip_address`, `user_agent`, `referrer`, `created_at`) VALUES ('3', 'order', 'order_placed', '1001', NULL, '{\"total\": 4000, \"order_id\": 10001, \"restaurant_id\": 101}', NULL, NULL, NULL, '2025-10-29 22:02:01');
INSERT INTO `analytics` (`id`, `event_type`, `event_name`, `user_id`, `session_id`, `data`, `ip_address`, `user_agent`, `referrer`, `created_at`) VALUES ('4', 'search', 'menu_search', '1003', NULL, '{\"query\": \"ndole\", \"results\": 3}', NULL, NULL, NULL, '2025-10-31 19:02:01');
INSERT INTO `analytics` (`id`, `event_type`, `event_name`, `user_id`, `session_id`, `data`, `ip_address`, `user_agent`, `referrer`, `created_at`) VALUES ('5', 'cart', 'add_to_cart', '1002', NULL, '{\"quantity\": 1, \"menu_item_id\": 1006}', NULL, NULL, NULL, '2025-10-30 22:02:01');

-- Table structure for table `cart_items`
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `menu_item_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Price per unit in XAF',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Total for this line item',
  `customizations` json DEFAULT NULL COMMENT 'Selected customizations/variants',
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cart_items_user` (`user_id`),
  KEY `idx_cart_items_menu_item` (`menu_item_id`),
  CONSTRAINT `cart_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `cart_items`
INSERT INTO `cart_items` (`id`, `user_id`, `menu_item_id`, `quantity`, `unit_price`, `total_price`, `customizations`, `special_instructions`, `created_at`, `updated_at`) VALUES ('3', '1003', '1504', '1', '2800.00', '2800.00', '[]', '', '2025-11-04 01:36:02', '2025-11-04 01:36:02');

-- Table structure for table `categories`
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `is_featured` tinyint(1) DEFAULT '0',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `idx_categories_parent` (`parent_id`),
  KEY `idx_categories_active_featured` (`is_active`,`is_featured`),
  FULLTEXT KEY `name` (`name`,`description`),
  FULLTEXT KEY `name_2` (`name`,`description`),
  FULLTEXT KEY `name_3` (`name`,`description`),
  FULLTEXT KEY `name_4` (`name`,`description`),
  FULLTEXT KEY `name_5` (`name`,`description`),
  FULLTEXT KEY `name_6` (`name`,`description`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `categories`
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `icon`, `parent_id`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES ('1', 'African Cuisine', 'african-cuisine', 'Traditional African dishes including authentic Cameroonian specialties like Ndolé, Eru, Yellow Soup, Pepper Soup, and more local favorites', 'categories/african-cuisine.jpg', 'utensils', NULL, '1', '1', '1', NULL, NULL, '2025-10-31 22:00:36', '2025-11-01 04:28:08');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `icon`, `parent_id`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES ('2', 'Fast Food', 'fast-food', 'Quick and delicious fast food options', 'categories/fast-food.jpg', 'hamburger', NULL, '2', '1', '1', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `icon`, `parent_id`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES ('3', 'Chinese Food', 'chinese-food', 'Authentic Chinese cuisine and Asian fusion', 'categories/chinese-food.jpg', 'dragon', NULL, '3', '1', '1', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `icon`, `parent_id`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES ('4', 'Pizza & Italian', 'pizza-italian', 'Wood-fired pizzas and Italian classics', 'categories/pizza-italian.jpg', 'pizza-slice', NULL, '4', '1', '1', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `icon`, `parent_id`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES ('5', 'Beverages', 'beverages', 'Refreshing drinks, juices, and soft drinks', 'categories/beverages.jpg', 'glass-water', NULL, '5', '1', '0', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `icon`, `parent_id`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES ('6', 'Desserts', 'desserts', 'Sweet treats and desserts', 'categories/desserts.jpg', 'cake', NULL, '6', '1', '0', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `icon`, `parent_id`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES ('7', 'Healthy Options', 'healthy-options', 'Nutritious and healthy meal choices', 'categories/healthy.jpg', 'leaf', NULL, '7', '1', '1', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `icon`, `parent_id`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES ('8', 'Grilled & BBQ', 'grilled-bbq', 'Grilled meats and barbecue specialties', 'categories/grilled-bbq.jpg', 'fire', NULL, '8', '1', '1', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');

-- Table structure for table `coupon_usages`
DROP TABLE IF EXISTS `coupon_usages`;
CREATE TABLE `coupon_usages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `discount_amount` decimal(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_coupon_usages_coupon` (`coupon_id`),
  KEY `idx_coupon_usages_user` (`user_id`),
  KEY `idx_coupon_usages_order` (`order_id`),
  CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `coupon_usages`

-- Table structure for table `coupons`
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('percentage','fixed_amount','free_delivery') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(8,2) NOT NULL COMMENT 'Percentage or fixed amount',
  `minimum_order` decimal(8,2) DEFAULT '0.00',
  `maximum_discount` decimal(8,2) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL COMMENT 'Total usage limit',
  `usage_limit_per_user` int DEFAULT '1',
  `used_count` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `applicable_to` enum('all','restaurants','menu_items','categories') COLLATE utf8mb4_unicode_ci DEFAULT 'all',
  `applicable_ids` json DEFAULT NULL COMMENT 'Specific restaurant/item/category IDs',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  KEY `idx_coupons_active_dates` (`is_active`,`starts_at`,`expires_at`),
  KEY `coupons_created_by_foreign` (`created_by`),
  CONSTRAINT `coupons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `coupons`
INSERT INTO `coupons` (`id`, `code`, `name`, `description`, `type`, `value`, `minimum_order`, `maximum_discount`, `usage_limit`, `usage_limit_per_user`, `used_count`, `is_active`, `starts_at`, `expires_at`, `applicable_to`, `applicable_ids`, `created_by`, `created_at`, `updated_at`) VALUES ('1', 'WELCOME10', 'Welcome Discount', '10% off for new customers', 'percentage', '10.00', '2000.00', NULL, '100', '1', '0', '1', '2025-10-31 22:00:37', '2025-11-30 22:00:37', 'all', NULL, '1000', '2025-10-31 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `coupons` (`id`, `code`, `name`, `description`, `type`, `value`, `minimum_order`, `maximum_discount`, `usage_limit`, `usage_limit_per_user`, `used_count`, `is_active`, `starts_at`, `expires_at`, `applicable_to`, `applicable_ids`, `created_by`, `created_at`, `updated_at`) VALUES ('2', 'FREEDELIV', 'Free Delivery', 'Free delivery on orders above 3000 XAF', 'free_delivery', '0.00', '3000.00', NULL, '500', '3', '0', '1', '2025-10-31 22:00:37', '2025-12-30 22:00:37', 'all', NULL, '1000', '2025-10-31 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `coupons` (`id`, `code`, `name`, `description`, `type`, `value`, `minimum_order`, `maximum_discount`, `usage_limit`, `usage_limit_per_user`, `used_count`, `is_active`, `starts_at`, `expires_at`, `applicable_to`, `applicable_ids`, `created_by`, `created_at`, `updated_at`) VALUES ('3', 'SAVE500', 'Save 500 XAF', '500 XAF off on orders above 4000 XAF', 'fixed_amount', '500.00', '4000.00', NULL, '200', '2', '0', '1', '2025-10-31 22:00:37', '2025-12-15 22:00:37', 'all', NULL, '1000', '2025-10-31 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `coupons` (`id`, `code`, `name`, `description`, `type`, `value`, `minimum_order`, `maximum_discount`, `usage_limit`, `usage_limit_per_user`, `used_count`, `is_active`, `starts_at`, `expires_at`, `applicable_to`, `applicable_ids`, `created_by`, `created_at`, `updated_at`) VALUES ('4', 'WEEKEND20', 'Weekend Special', '20% off on weekends', 'percentage', '20.00', '2500.00', NULL, '1000', '1', '0', '1', '2025-10-31 22:00:37', '2026-01-29 22:00:37', 'all', NULL, '1000', '2025-10-31 22:00:37', '2025-10-31 22:00:37');

-- Table structure for table `daily_stats`
DROP TABLE IF EXISTS `daily_stats`;
CREATE TABLE `daily_stats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `restaurant_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `total_orders` int DEFAULT '0',
  `total_revenue` decimal(12,2) DEFAULT '0.00',
  `total_commission` decimal(10,2) DEFAULT '0.00',
  `total_deliveries` int DEFAULT '0',
  `average_order_value` decimal(8,2) DEFAULT '0.00',
  `average_delivery_time` int DEFAULT '0' COMMENT 'Average delivery time in minutes',
  `customer_satisfaction` decimal(3,2) DEFAULT '0.00' COMMENT 'Average rating',
  `new_customers` int DEFAULT '0',
  `returning_customers` int DEFAULT '0',
  `cancelled_orders` int DEFAULT '0',
  `refunded_orders` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_stats_date_restaurant_user_unique` (`date`,`restaurant_id`,`user_id`),
  KEY `idx_daily_stats_date` (`date`),
  KEY `idx_daily_stats_restaurant` (`restaurant_id`),
  KEY `idx_daily_stats_user` (`user_id`),
  CONSTRAINT `daily_stats_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_stats_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `daily_stats`
INSERT INTO `daily_stats` (`id`, `date`, `restaurant_id`, `user_id`, `total_orders`, `total_revenue`, `total_commission`, `total_deliveries`, `average_order_value`, `average_delivery_time`, `customer_satisfaction`, `new_customers`, `returning_customers`, `cancelled_orders`, `refunded_orders`, `created_at`, `updated_at`) VALUES ('1', '2025-10-31', '101', NULL, '5', '17500.00', '2625.00', '0', '3500.00', '0', '4.80', '0', '0', '0', '0', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `daily_stats` (`id`, `date`, `restaurant_id`, `user_id`, `total_orders`, `total_revenue`, `total_commission`, `total_deliveries`, `average_order_value`, `average_delivery_time`, `customer_satisfaction`, `new_customers`, `returning_customers`, `cancelled_orders`, `refunded_orders`, `created_at`, `updated_at`) VALUES ('2', '2025-10-31', '102', NULL, '8', '18000.00', '2700.00', '0', '2250.00', '0', '4.50', '0', '0', '0', '0', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `daily_stats` (`id`, `date`, `restaurant_id`, `user_id`, `total_orders`, `total_revenue`, `total_commission`, `total_deliveries`, `average_order_value`, `average_delivery_time`, `customer_satisfaction`, `new_customers`, `returning_customers`, `cancelled_orders`, `refunded_orders`, `created_at`, `updated_at`) VALUES ('3', '2025-10-31', '103', NULL, '3', '11400.00', '1710.00', '0', '3800.00', '0', '4.60', '0', '0', '0', '0', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `daily_stats` (`id`, `date`, `restaurant_id`, `user_id`, `total_orders`, `total_revenue`, `total_commission`, `total_deliveries`, `average_order_value`, `average_delivery_time`, `customer_satisfaction`, `new_customers`, `returning_customers`, `cancelled_orders`, `refunded_orders`, `created_at`, `updated_at`) VALUES ('4', '2025-10-31', '104', NULL, '4', '18000.00', '2700.00', '0', '4500.00', '0', '4.70', '0', '0', '0', '0', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `daily_stats` (`id`, `date`, `restaurant_id`, `user_id`, `total_orders`, `total_revenue`, `total_commission`, `total_deliveries`, `average_order_value`, `average_delivery_time`, `customer_satisfaction`, `new_customers`, `returning_customers`, `cancelled_orders`, `refunded_orders`, `created_at`, `updated_at`) VALUES ('5', '2025-10-30', '101', NULL, '7', '24500.00', '3675.00', '0', '3500.00', '0', '4.90', '0', '0', '0', '0', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `daily_stats` (`id`, `date`, `restaurant_id`, `user_id`, `total_orders`, `total_revenue`, `total_commission`, `total_deliveries`, `average_order_value`, `average_delivery_time`, `customer_satisfaction`, `new_customers`, `returning_customers`, `cancelled_orders`, `refunded_orders`, `created_at`, `updated_at`) VALUES ('6', '2025-10-30', '102', NULL, '12', '27000.00', '4050.00', '0', '2250.00', '0', '4.40', '0', '0', '0', '0', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `daily_stats` (`id`, `date`, `restaurant_id`, `user_id`, `total_orders`, `total_revenue`, `total_commission`, `total_deliveries`, `average_order_value`, `average_delivery_time`, `customer_satisfaction`, `new_customers`, `returning_customers`, `cancelled_orders`, `refunded_orders`, `created_at`, `updated_at`) VALUES ('7', '2025-10-30', '103', NULL, '6', '22800.00', '3420.00', '0', '3800.00', '0', '4.70', '0', '0', '0', '0', '2025-10-31 22:02:01', '2025-10-31 22:02:01');

-- Table structure for table `deliveries`
DROP TABLE IF EXISTS `deliveries`;
CREATE TABLE `deliveries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `rider_id` bigint unsigned NOT NULL,
  `pickup_address` json NOT NULL,
  `delivery_address` json NOT NULL,
  `pickup_time` timestamp NULL DEFAULT NULL,
  `delivery_time` timestamp NULL DEFAULT NULL,
  `estimated_duration` int DEFAULT NULL COMMENT 'Estimated delivery time in minutes',
  `actual_duration` int DEFAULT NULL COMMENT 'Actual delivery time in minutes',
  `distance` decimal(5,2) DEFAULT NULL COMMENT 'Distance in KM',
  `delivery_fee` decimal(6,2) NOT NULL COMMENT 'Delivery fee in XAF',
  `rider_earnings` decimal(6,2) NOT NULL COMMENT 'Rider earnings in XAF',
  `platform_commission` decimal(6,2) NOT NULL COMMENT 'Platform commission in XAF',
  `status` enum('assigned','accepted','picked_up','on_the_way','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assigned',
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `delivery_proof` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Photo proof of delivery',
  `customer_signature` text COLLATE utf8mb4_unicode_ci COMMENT 'Digital signature',
  `rating` tinyint(1) DEFAULT NULL COMMENT '1-5 star rating from customer',
  `review` text COLLATE utf8mb4_unicode_ci,
  `rider_notes` text COLLATE utf8mb4_unicode_ci,
  `tracking_data` json DEFAULT NULL COMMENT 'Real-time GPS tracking data',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deliveries_order_id_unique` (`order_id`),
  KEY `idx_deliveries_rider` (`rider_id`),
  KEY `idx_deliveries_status_date` (`status`,`created_at`),
  KEY `idx_deliveries_rider_status_date` (`rider_id`,`status`,`created_at`),
  CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliveries_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `deliveries`

-- Table structure for table `disputes`
DROP TABLE IF EXISTS `disputes`;
CREATE TABLE `disputes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `initiator_id` bigint unsigned NOT NULL,
  `type` enum('order_issue','payment_issue','delivery_issue','quality_issue','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `evidence` json DEFAULT NULL COMMENT 'Photos, documents, etc.',
  `status` enum('open','investigating','resolved','closed','escalated') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `resolution` text COLLATE utf8mb4_unicode_ci,
  `resolved_by` bigint unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `compensation_amount` decimal(8,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_disputes_order` (`order_id`),
  KEY `idx_disputes_initiator` (`initiator_id`),
  KEY `idx_disputes_status_priority` (`status`,`priority`),
  KEY `disputes_resolved_by_foreign` (`resolved_by`),
  CONSTRAINT `disputes_initiator_id_foreign` FOREIGN KEY (`initiator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disputes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disputes_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `disputes`

-- Table structure for table `earnings`
DROP TABLE IF EXISTS `earnings`;
CREATE TABLE `earnings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `earning_type` enum('affiliate','rider','restaurant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` enum('order','commission','bonus','refund') COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmed',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_earning_type` (`earning_type`),
  KEY `idx_source_type` (`source_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `earnings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `earnings`

-- Table structure for table `email_verifications`
DROP TABLE IF EXISTS `email_verifications`;
CREATE TABLE `email_verifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email_verification` (`email`),
  KEY `idx_email_verifications_token` (`verification_token`),
  KEY `idx_email_verifications_expires` (`expires_at`),
  KEY `idx_email_verifications_verified` (`verified_at`),
  KEY `idx_email_verifications_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `email_verifications`

-- Table structure for table `logs`
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `level` enum('emergency','alert','critical','error','warning','notice','info','debug') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` json DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_level_date` (`level`,`created_at`),
  KEY `idx_logs_user` (`user_id`),
  CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `logs`

-- Table structure for table `menu_categories`
DROP TABLE IF EXISTS `menu_categories`;
CREATE TABLE `menu_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `parent_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_per_restaurant` (`restaurant_id`,`name`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_menu_categories_restaurant_active` (`restaurant_id`,`is_active`),
  CONSTRAINT `menu_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menu_categories_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `menu_categories`
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('1', '101', 'African Cuisine', 'Traditional African dishes including authentic Cameroonian specialties like Ndolé, Eru, Yellow Soup, Pepper Soup, and more local favorites', '1', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('2', '101', 'Fast Food', 'Quick and delicious fast food options', '2', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('3', '101', 'Chinese Food', 'Authentic Chinese cuisine and Asian fusion', '3', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('4', '101', 'Pizza & Italian', 'Wood-fired pizzas and Italian classics', '4', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('5', '101', 'Beverages', 'Refreshing drinks, juices, and soft drinks', '5', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('6', '101', 'Desserts', 'Sweet treats and desserts', '6', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('7', '101', 'Healthy Options', 'Nutritious and healthy meal choices', '7', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('8', '101', 'Grilled & BBQ', 'Grilled meats and barbecue specialties', '8', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('9', '102', 'African Cuisine', 'Traditional African dishes including authentic Cameroonian specialties like Ndolé, Eru, Yellow Soup, Pepper Soup, and more local favorites', '1', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('10', '102', 'Fast Food', 'Quick and delicious fast food options', '2', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('11', '102', 'Chinese Food', 'Authentic Chinese cuisine and Asian fusion', '3', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('12', '102', 'Pizza & Italian', 'Wood-fired pizzas and Italian classics', '4', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('13', '102', 'Beverages', 'Refreshing drinks, juices, and soft drinks', '5', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('14', '102', 'Desserts', 'Sweet treats and desserts', '6', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('15', '102', 'Healthy Options', 'Nutritious and healthy meal choices', '7', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('16', '102', 'Grilled & BBQ', 'Grilled meats and barbecue specialties', '8', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('17', '103', 'African Cuisine', 'Traditional African dishes including authentic Cameroonian specialties like Ndolé, Eru, Yellow Soup, Pepper Soup, and more local favorites', '1', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('18', '103', 'Fast Food', 'Quick and delicious fast food options', '2', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('19', '103', 'Chinese Food', 'Authentic Chinese cuisine and Asian fusion', '3', '1', NULL, '2025-11-02 11:28:03', '2025-11-02 11:28:03');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('20', '103', 'Pizza & Italian', 'Wood-fired pizzas and Italian classics', '4', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('21', '103', 'Beverages', 'Refreshing drinks, juices, and soft drinks', '5', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('22', '103', 'Desserts', 'Sweet treats and desserts', '6', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('23', '103', 'Healthy Options', 'Nutritious and healthy meal choices', '7', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('24', '103', 'Grilled & BBQ', 'Grilled meats and barbecue specialties', '8', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('25', '104', 'African Cuisine', 'Traditional African dishes including authentic Cameroonian specialties like Ndolé, Eru, Yellow Soup, Pepper Soup, and more local favorites', '1', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('26', '104', 'Fast Food', 'Quick and delicious fast food options', '2', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('27', '104', 'Chinese Food', 'Authentic Chinese cuisine and Asian fusion', '3', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('28', '104', 'Pizza & Italian', 'Wood-fired pizzas and Italian classics', '4', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('29', '104', 'Beverages', 'Refreshing drinks, juices, and soft drinks', '5', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('30', '104', 'Desserts', 'Sweet treats and desserts', '6', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('31', '104', 'Healthy Options', 'Nutritious and healthy meal choices', '7', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('32', '104', 'Grilled & BBQ', 'Grilled meats and barbecue specialties', '8', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('33', '105', 'African Cuisine', 'Traditional African dishes including authentic Cameroonian specialties like Ndolé, Eru, Yellow Soup, Pepper Soup, and more local favorites', '1', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('34', '105', 'Fast Food', 'Quick and delicious fast food options', '2', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('35', '105', 'Chinese Food', 'Authentic Chinese cuisine and Asian fusion', '3', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('36', '105', 'Pizza & Italian', 'Wood-fired pizzas and Italian classics', '4', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('37', '105', 'Beverages', 'Refreshing drinks, juices, and soft drinks', '5', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('38', '105', 'Desserts', 'Sweet treats and desserts', '6', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('39', '105', 'Healthy Options', 'Nutritious and healthy meal choices', '7', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('40', '105', 'Grilled & BBQ', 'Grilled meats and barbecue specialties', '8', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('41', '106', 'African Cuisine', 'Traditional African dishes including authentic Cameroonian specialties like Ndolé, Eru, Yellow Soup, Pepper Soup, and more local favorites', '1', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('42', '106', 'Fast Food', 'Quick and delicious fast food options', '2', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('43', '106', 'Chinese Food', 'Authentic Chinese cuisine and Asian fusion', '3', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('44', '106', 'Pizza & Italian', 'Wood-fired pizzas and Italian classics', '4', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('45', '106', 'Beverages', 'Refreshing drinks, juices, and soft drinks', '5', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('46', '106', 'Desserts', 'Sweet treats and desserts', '6', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('47', '106', 'Healthy Options', 'Nutritious and healthy meal choices', '7', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');
INSERT INTO `menu_categories` (`id`, `restaurant_id`, `name`, `description`, `sort_order`, `is_active`, `parent_id`, `created_at`, `updated_at`) VALUES ('48', '106', 'Grilled & BBQ', 'Grilled meats and barbecue specialties', '8', '1', NULL, '2025-11-02 11:28:04', '2025-11-02 11:28:04');

-- Table structure for table `menu_item_variants`
DROP TABLE IF EXISTS `menu_item_variants`;
CREATE TABLE `menu_item_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('size','addon','customization') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'addon',
  `price_adjustment` decimal(6,2) DEFAULT '0.00' COMMENT 'Price difference from base item',
  `is_required` tinyint(1) DEFAULT '0',
  `max_selections` int DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `is_available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_variants_menu_item` (`menu_item_id`),
  CONSTRAINT `menu_item_variants_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `menu_item_variants`

-- Table structure for table `menu_items`
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` json DEFAULT NULL COMMENT 'Additional product images',
  `price` decimal(8,2) NOT NULL COMMENT 'Price in XAF',
  `compare_price` decimal(8,2) DEFAULT NULL COMMENT 'Original price for discounts',
  `cost_price` decimal(8,2) DEFAULT NULL COMMENT 'Cost price for profit calculation',
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ingredients` text COLLATE utf8mb4_unicode_ci,
  `allergens` json DEFAULT NULL,
  `nutritional_info` json DEFAULT NULL,
  `preparation_time` int DEFAULT '15' COMMENT 'Prep time in minutes',
  `calories` int DEFAULT NULL,
  `spice_level` enum('none','mild','medium','hot','very_hot') COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `dietary_tags` json DEFAULT NULL COMMENT 'vegetarian, vegan, gluten-free, etc.',
  `is_available` tinyint(1) DEFAULT '1',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_popular` tinyint(1) DEFAULT '0',
  `stock_quantity` int DEFAULT NULL COMMENT 'NULL = unlimited',
  `low_stock_threshold` int DEFAULT '5',
  `min_stock_level` int DEFAULT '5',
  `is_vegetarian` tinyint(1) DEFAULT '0',
  `is_vegan` tinyint(1) DEFAULT '0',
  `is_gluten_free` tinyint(1) DEFAULT '0',
  `customization_options` json DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int DEFAULT '0',
  `total_orders` int DEFAULT '0',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_items_restaurant_slug_unique` (`restaurant_id`,`slug`),
  KEY `idx_menu_items_restaurant` (`restaurant_id`),
  KEY `idx_menu_items_category` (`category_id`),
  KEY `idx_menu_items_available_featured` (`is_available`,`is_featured`),
  KEY `idx_menu_items_price` (`price`),
  KEY `idx_menu_items_stock_quantity` (`stock_quantity`),
  KEY `idx_menu_items_deleted_at` (`deleted_at`),
  KEY `idx_menu_items_restaurant_category` (`restaurant_id`,`category_id`),
  KEY `idx_menu_items_restaurant_available` (`restaurant_id`,`is_available`),
  KEY `idx_menu_items_category_available` (`category_id`,`is_available`),
  KEY `idx_menu_items_restaurant_available_sort` (`restaurant_id`,`is_available`,`sort_order`),
  FULLTEXT KEY `name` (`name`,`description`,`ingredients`),
  FULLTEXT KEY `name_2` (`name`,`description`,`ingredients`),
  FULLTEXT KEY `name_3` (`name`,`description`,`ingredients`),
  FULLTEXT KEY `name_4` (`name`,`description`,`ingredients`),
  FULLTEXT KEY `name_5` (`name`,`description`,`ingredients`),
  FULLTEXT KEY `name_6` (`name`,`description`,`ingredients`),
  CONSTRAINT `menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menu_items_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1516 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `menu_items`
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1501', '101', '1', 'Yellow Soup with Achu', 'yellow-soup-achu', 'Traditional Cameroonian yellow soup served with pounded cocoyam (achu)', 'https://duabaafrofoods.co.uk/wp-content/uploads/2024/11/Yellow-Soup-and-Achu.jpg', NULL, '4200.00', NULL, NULL, NULL, NULL, 'Palm oil, fish, meat, vegetables, spices, cocoyam', NULL, NULL, '50', NULL, 'none', NULL, '1', '1', '1', NULL, '5', '5', '0', '0', '0', NULL, NULL, '0', '4.80', '142', '645', NULL, NULL, '2025-11-01 05:01:02', '2025-11-04 02:44:58', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1502', '101', '1', 'Lentil Stew', 'lentil-stew', 'Hearty Cameroonian-style lentil stew with vegetables and spices', 'https://www.preciouscore.com/wp-content/uploads/2022/01/Lentil-Stew-276x276.jpg', NULL, '2500.00', NULL, NULL, NULL, NULL, 'Lentils, tomatoes, onions, garlic, palm oil, spices', NULL, NULL, '35', NULL, 'none', NULL, '1', '0', '1', NULL, '5', '5', '0', '0', '0', NULL, NULL, '0', '4.60', '42', '289', NULL, NULL, '2025-11-01 05:01:02', '2025-11-01 05:01:02', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1503', '101', '1', 'Cameroonian Pepper Soup', 'cameroonian-pepper-soup', 'Spicy traditional pepper soup with assorted meat and local spices', 'https://www.preciouscore.com/wp-content/uploads/2023/01/Cameroonian-Pepper-Soup-1138x1536.jpg', NULL, '3800.00', NULL, NULL, NULL, NULL, 'Assorted meat, pepper soup spices, scent leaves, ginger, garlic', NULL, NULL, '45', NULL, 'none', NULL, '1', '1', '1', NULL, '5', '5', '0', '0', '0', NULL, NULL, '0', '4.80', '164', '756', NULL, NULL, '2025-11-01 05:01:02', '2025-11-01 05:01:56', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1504', '101', '1', 'African Fried Rice', 'african-fried-rice', 'Delicious fried rice prepared the African way with local seasonings', 'https://www.preciouscore.com/wp-content/uploads/2020/12/African-Fried-Rice-276x276.jpg', NULL, '2800.00', NULL, NULL, NULL, NULL, 'Rice, mixed vegetables, meat, African spices, palm oil', NULL, NULL, '30', NULL, 'none', NULL, '1', '1', '1', NULL, '5', '5', '0', '0', '0', NULL, NULL, '0', '4.80', '153', '823', NULL, NULL, '2025-11-01 05:01:02', '2025-11-01 05:01:56', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1505', '101', '1', 'Beef Fried Rice', 'beef-fried-rice', 'Savory fried rice with tender beef pieces and vegetables', 'https://www.preciouscore.com/wp-content/uploads/2019/12/Beef-Fried-Rice.jpg', NULL, '3200.00', NULL, NULL, NULL, NULL, 'Rice, beef, carrots, green beans, onions, soy sauce, spices', NULL, NULL, '35', NULL, 'none', NULL, '1', '0', '1', NULL, '5', '5', '0', '0', '0', NULL, NULL, '0', '4.50', '56', '334', NULL, NULL, '2025-11-01 05:01:02', '2025-11-01 05:01:02', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1506', '101', '1', 'Stewed Pinto Beans', 'stewed-pinto-beans', 'Traditional Cameroonian stewed beans with palm oil and spices', 'https://www.preciouscore.com/wp-content/uploads/2018/09/Stewed-Pinto-Beans-Recipe.jpg', NULL, '2200.00', NULL, NULL, NULL, NULL, 'Pinto beans, palm oil, onions, tomatoes, crayfish, spices', NULL, NULL, '40', NULL, 'none', NULL, '1', '0', '0', NULL, '5', '5', '0', '0', '0', NULL, NULL, '0', '4.40', '34', '267', NULL, NULL, '2025-11-01 05:01:02', '2025-11-01 05:01:02', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1507', '102', '2', 'Lemon Pepper Chicken Drumsticks', 'lemon-pepper-chicken-drumsticks', 'Juicy chicken drumsticks marinated in lemon pepper seasoning', 'https://www.preciouscore.com/wp-content/uploads/2025/02/Lemon-Pepper-Chicken-Drumsticks-thumbnail-1-276x276.jpg', NULL, '2800.00', NULL, NULL, NULL, NULL, 'Chicken drumsticks, lemon, black pepper, herbs, spices', NULL, NULL, '25', NULL, 'none', NULL, '1', '1', '1', NULL, '5', '5', '0', '0', '0', NULL, NULL, '0', '4.80', '120', '598', NULL, NULL, '2025-11-01 05:01:03', '2025-11-01 05:01:56', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1508', '102', '8', 'Fresh Lettuce Salad', 'fresh-lettuce-salad', 'Crisp lettuce salad with tomatoes, cucumbers and local dressing', 'https://www.preciouscore.com/wp-content/uploads/2020/05/lettuce-salad-recipe.jpg', NULL, '1500.00', NULL, NULL, NULL, NULL, 'Lettuce, tomatoes, cucumbers, carrots, local vinaigrette', NULL, NULL, '10', NULL, 'none', NULL, '1', '0', '0', NULL, '5', '5', '0', '0', '0', NULL, NULL, '0', '4.30', '23', '156', NULL, NULL, '2025-11-01 05:01:03', '2025-11-01 05:01:03', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1510', '101', '1', 'Test Menu Item 1762079356', 'test-menu-item-1762079356', 'This is a test menu item created by automated script', NULL, NULL, '2500.00', NULL, NULL, NULL, NULL, 'Test ingredients', NULL, NULL, '20', '500', 'none', NULL, '1', '0', '0', '100', '5', '10', '0', '0', '0', NULL, NULL, '0', '0.00', '0', '0', NULL, NULL, '2025-11-02 11:29:16', '2025-11-02 11:29:16', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1513', '101', '1', 'Test Menu Item 124152', 'test-menu-item-124152', 'This is a test menu item created via terminal', NULL, NULL, '2500.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '20', NULL, 'none', NULL, '1', '0', '0', '51', '5', '5', '1', '1', '1', NULL, NULL, '0', '0.00', '0', '0', NULL, NULL, '2025-11-02 12:41:52', '2025-11-04 02:56:00', NULL);
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `images`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `ingredients`, `allergens`, `nutritional_info`, `preparation_time`, `calories`, `spice_level`, `dietary_tags`, `is_available`, `is_featured`, `is_popular`, `stock_quantity`, `low_stock_threshold`, `min_stock_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `customization_options`, `image_url`, `sort_order`, `rating`, `total_reviews`, `total_orders`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1515', '101', '2', 'beef burger', 'beef-burger', 'asdfghjkjhgfdsa', NULL, NULL, '1000.00', NULL, NULL, NULL, NULL, NULL, '[]', NULL, '15', '106', 'none', NULL, '1', '0', '0', '11', '5', '5', '0', '0', '0', '[]', NULL, '0', '0.00', '0', '0', NULL, NULL, '2025-11-02 14:31:08', '2025-11-02 14:31:08', NULL);

-- Table structure for table `messages`
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `recipient_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_type` enum('text','image','file','location','system') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `type` enum('general','order','support','system') COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_conversation_id` (`conversation_id`),
  KEY `idx_messages_sender` (`sender_id`),
  KEY `idx_messages_recipient` (`recipient_id`),
  KEY `idx_messages_order` (`order_id`),
  KEY `idx_messages_read_status` (`is_read`,`created_at`),
  CONSTRAINT `messages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `messages_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `messages`

-- Table structure for table `notifications`
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'NULL for system-wide notifications',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'order_update, promotion, system_alert, etc.',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL COMMENT 'Additional notification data',
  `channels` json DEFAULT NULL COMMENT 'email, sms, push, in_app',
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `status` enum('pending','sent','delivered','failed','read') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `related_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'order, restaurant, user, etc.',
  `related_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_scheduled_at` (`scheduled_at`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_related` (`related_type`,`related_id`),
  KEY `fk_notifications_created_by` (`created_by`),
  CONSTRAINT `fk_notifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `notifications`

-- Table structure for table `order_items`
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `menu_item_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(8,2) NOT NULL COMMENT 'Price per unit in XAF',
  `total_price` decimal(10,2) NOT NULL COMMENT 'Total for this line item',
  `variants` json DEFAULT NULL COMMENT 'Selected variants/customizations',
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_menu_item` (`menu_item_id`),
  CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `order_items`
INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `quantity`, `unit_price`, `total_price`, `variants`, `special_instructions`, `created_at`, `updated_at`) VALUES ('7', '10007', '1504', '1', '2800.00', '2800.00', '[]', '', '2025-11-04 01:02:21', '2025-11-04 01:02:21');

-- Table structure for table `order_status_history`
DROP TABLE IF EXISTS `order_status_history`;
CREATE TABLE `order_status_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `changed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status_history_order` (`order_id`),
  KEY `idx_status_history_changed_by` (`changed_by`),
  CONSTRAINT `order_status_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_status_history_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `order_status_history`

-- Table structure for table `orders`
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `restaurant_id` bigint unsigned NOT NULL,
  `rider_id` bigint unsigned DEFAULT NULL,
  `affiliate_id` bigint unsigned DEFAULT NULL,
  `status` enum('pending','confirmed','preparing','ready','picked_up','on_the_way','delivered','cancelled','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `customer_confirmed` tinyint(1) DEFAULT '0' COMMENT 'Customer confirmed receipt',
  `customer_confirmed_at` datetime DEFAULT NULL COMMENT 'When customer confirmed receipt',
  `payment_status` enum('pending','paid','failed','refunded','partially_refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Transaction ID from payment gateway',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Items total in XAF',
  `tax_amount` decimal(8,2) DEFAULT '0.00' COMMENT 'Tax amount in XAF',
  `delivery_fee` decimal(6,2) DEFAULT '0.00' COMMENT 'Delivery fee in XAF',
  `service_fee` decimal(6,2) DEFAULT '0.00' COMMENT 'Platform service fee',
  `discount_amount` decimal(8,2) DEFAULT '0.00' COMMENT 'Total discount applied',
  `affiliate_commission` decimal(6,2) DEFAULT '0.00' COMMENT 'Affiliate commission',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Final total in XAF',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'cash_on_delivery' COMMENT 'Payment method used',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'XAF',
  `delivery_address` json NOT NULL COMMENT 'Delivery address details',
  `delivery_instructions` text COLLATE utf8mb4_unicode_ci,
  `estimated_delivery_time` timestamp NULL DEFAULT NULL,
  `actual_delivery_time` timestamp NULL DEFAULT NULL,
  `estimated_preparation_time` datetime DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `updated_by` int DEFAULT NULL,
  `preparation_time` int DEFAULT NULL COMMENT 'Estimated prep time in minutes',
  `delivery_distance` decimal(5,2) DEFAULT NULL COMMENT 'Distance in KM',
  `coupon_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `rating` tinyint(1) DEFAULT NULL COMMENT '1-5 star rating',
  `review` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `refund_amount` decimal(10,2) DEFAULT '0.00',
  `refund_reason` text COLLATE utf8mb4_unicode_ci,
  `tracking_data` json DEFAULT NULL COMMENT 'Real-time tracking information',
  `metadata` json DEFAULT NULL COMMENT 'Additional order metadata',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `idx_orders_customer` (`customer_id`),
  KEY `idx_orders_restaurant` (`restaurant_id`),
  KEY `idx_orders_rider` (`rider_id`),
  KEY `idx_orders_affiliate` (`affiliate_id`),
  KEY `idx_orders_status_date` (`status`,`created_at`),
  KEY `idx_orders_customer_status_date` (`customer_id`,`status`,`created_at`),
  KEY `idx_orders_restaurant_status_date` (`restaurant_id`,`status`,`created_at`),
  KEY `idx_orders_rider_status_date` (`rider_id`,`status`,`created_at`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_updated_at` (`updated_at`),
  KEY `idx_orders_customer_status` (`customer_id`,`status`),
  KEY `idx_orders_restaurant_status` (`restaurant_id`,`status`),
  KEY `idx_orders_rider_status` (`rider_id`,`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_customer_confirmed` (`customer_confirmed`,`customer_confirmed_at`),
  CONSTRAINT `orders_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10008 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `orders`
INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `restaurant_id`, `rider_id`, `affiliate_id`, `status`, `customer_confirmed`, `customer_confirmed_at`, `payment_status`, `payment_transaction_id`, `subtotal`, `tax_amount`, `delivery_fee`, `service_fee`, `discount_amount`, `affiliate_commission`, `total_amount`, `payment_method`, `currency`, `delivery_address`, `delivery_instructions`, `estimated_delivery_time`, `actual_delivery_time`, `estimated_preparation_time`, `admin_notes`, `updated_by`, `preparation_time`, `delivery_distance`, `coupon_code`, `special_instructions`, `rating`, `review`, `cancellation_reason`, `refund_amount`, `refund_reason`, `tracking_data`, `metadata`, `created_at`, `updated_at`) VALUES ('10001', 'ORD-2024-001', '1001', '101', '1007', NULL, 'delivered', '0', NULL, 'paid', NULL, '3500.00', '0.00', '500.00', '0.00', '0.00', '0.00', '4000.00', 'cash_on_delivery', 'XAF', '{\"phone\": \"+237 6XX XXX 001\", \"address\": \"Mile 3, Bamenda\", \"instructions\": \"Call when you arrive\"}', NULL, '2025-10-31 22:35:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', 'Excellent food and fast delivery!', NULL, '0.00', NULL, NULL, NULL, '2025-10-29 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `restaurant_id`, `rider_id`, `affiliate_id`, `status`, `customer_confirmed`, `customer_confirmed_at`, `payment_status`, `payment_transaction_id`, `subtotal`, `tax_amount`, `delivery_fee`, `service_fee`, `discount_amount`, `affiliate_commission`, `total_amount`, `payment_method`, `currency`, `delivery_address`, `delivery_instructions`, `estimated_delivery_time`, `actual_delivery_time`, `estimated_preparation_time`, `admin_notes`, `updated_by`, `preparation_time`, `delivery_distance`, `coupon_code`, `special_instructions`, `rating`, `review`, `cancellation_reason`, `refund_amount`, `refund_reason`, `tracking_data`, `metadata`, `created_at`, `updated_at`) VALUES ('10002', 'ORD-2024-002', '1002', '102', '1008', NULL, 'delivered', '0', NULL, 'paid', NULL, '2200.00', '0.00', '300.00', '0.00', '0.00', '0.00', '2500.00', 'cash_on_delivery', 'XAF', '{\"phone\": \"+237 6XX XXX 002\", \"address\": \"Commercial Avenue, Bamenda\", \"instructions\": \"Leave at the gate\"}', NULL, '2025-10-31 22:20:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4', 'Good food, quick service', NULL, '0.00', NULL, NULL, NULL, '2025-10-30 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `restaurant_id`, `rider_id`, `affiliate_id`, `status`, `customer_confirmed`, `customer_confirmed_at`, `payment_status`, `payment_transaction_id`, `subtotal`, `tax_amount`, `delivery_fee`, `service_fee`, `discount_amount`, `affiliate_commission`, `total_amount`, `payment_method`, `currency`, `delivery_address`, `delivery_instructions`, `estimated_delivery_time`, `actual_delivery_time`, `estimated_preparation_time`, `admin_notes`, `updated_by`, `preparation_time`, `delivery_distance`, `coupon_code`, `special_instructions`, `rating`, `review`, `cancellation_reason`, `refund_amount`, `refund_reason`, `tracking_data`, `metadata`, `created_at`, `updated_at`) VALUES ('10003', 'ORD-2024-003', '1003', '103', '1007', NULL, 'on_the_way', '0', NULL, 'paid', NULL, '3800.00', '0.00', '600.00', '0.00', '0.00', '0.00', '4400.00', 'cash_on_delivery', 'XAF', '{\"phone\": \"+237 6XX XXX 003\", \"address\": \"Up Station, Bamenda\", \"instructions\": \"Ring the bell\"}', NULL, '2025-10-31 22:15:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', NULL, NULL, NULL, '2025-10-31 21:30:37', '2025-10-31 22:00:37');
INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `restaurant_id`, `rider_id`, `affiliate_id`, `status`, `customer_confirmed`, `customer_confirmed_at`, `payment_status`, `payment_transaction_id`, `subtotal`, `tax_amount`, `delivery_fee`, `service_fee`, `discount_amount`, `affiliate_commission`, `total_amount`, `payment_method`, `currency`, `delivery_address`, `delivery_instructions`, `estimated_delivery_time`, `actual_delivery_time`, `estimated_preparation_time`, `admin_notes`, `updated_by`, `preparation_time`, `delivery_distance`, `coupon_code`, `special_instructions`, `rating`, `review`, `cancellation_reason`, `refund_amount`, `refund_reason`, `tracking_data`, `metadata`, `created_at`, `updated_at`) VALUES ('10004', 'ORD-2024-004', '1001', '104', NULL, NULL, 'preparing', '0', NULL, 'paid', NULL, '1000.00', '0.00', '400.00', '0.00', '0.00', '0.00', '1400.00', 'cash_on_delivery', 'XAF', '{\"phone\": \"+237 6XX XXX 001\", \"address\": \"Ntarikon Quarter, Bamenda\", \"instructions\": \"Apartment 2B\"}', NULL, '2025-10-31 22:25:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', NULL, NULL, NULL, '2025-10-31 21:50:37', '2025-11-03 23:54:54');
INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `restaurant_id`, `rider_id`, `affiliate_id`, `status`, `customer_confirmed`, `customer_confirmed_at`, `payment_status`, `payment_transaction_id`, `subtotal`, `tax_amount`, `delivery_fee`, `service_fee`, `discount_amount`, `affiliate_commission`, `total_amount`, `payment_method`, `currency`, `delivery_address`, `delivery_instructions`, `estimated_delivery_time`, `actual_delivery_time`, `estimated_preparation_time`, `admin_notes`, `updated_by`, `preparation_time`, `delivery_distance`, `coupon_code`, `special_instructions`, `rating`, `review`, `cancellation_reason`, `refund_amount`, `refund_reason`, `tracking_data`, `metadata`, `created_at`, `updated_at`) VALUES ('10007', 'T2E2511045257', '1003', '101', NULL, NULL, 'ready', '0', NULL, 'pending', NULL, '2800.00', '0.00', '1000.00', '70.00', '0.00', '0.00', '3800.00', 'cash_on_delivery', 'XAF', '{\"type\": \"gps\", \"latitude\": 6.0045747, \"longitude\": 10.2556803, \"instructions\": \"tyrtbvtyty46yt\"}', 'tyrtbvtyty46yt', '2025-11-06 06:06:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', NULL, NULL, NULL, '2025-11-04 01:02:21', '2025-11-06 05:41:26');

-- Table structure for table `payment_methods`
DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE `payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` enum('card','mobile_money','bank_account','wallet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'stripe, paypal, orange_money, mtn_money',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'User-friendly name',
  `details` json NOT NULL COMMENT 'Encrypted payment details',
  `is_default` tinyint(1) DEFAULT '0',
  `is_verified` tinyint(1) DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payment_methods_user` (`user_id`),
  KEY `idx_payment_methods_type_provider` (`type`,`provider`),
  CONSTRAINT `payment_methods_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `payment_methods`
INSERT INTO `payment_methods` (`id`, `user_id`, `type`, `provider`, `name`, `details`, `is_default`, `is_verified`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('1', '1001', 'mobile_money', 'mtn_money', 'MTN Mobile Money', '{\"name\": \"John Doe\", \"number\": \"+237 6XX XXX 001\"}', '1', '1', NULL, NULL, '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `payment_methods` (`id`, `user_id`, `type`, `provider`, `name`, `details`, `is_default`, `is_verified`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('2', '1002', 'mobile_money', 'orange_money', 'Orange Money', '{\"name\": \"Mary Smith\", \"number\": \"+237 6XX XXX 002\"}', '1', '1', NULL, NULL, '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `payment_methods` (`id`, `user_id`, `type`, `provider`, `name`, `details`, `is_default`, `is_verified`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('3', '1003', 'card', 'stripe', 'Visa Card', '{\"brand\": \"visa\", \"last4\": \"1234\", \"exp_year\": 2025, \"exp_month\": 12}', '1', '1', NULL, NULL, '2025-10-31 22:02:01', '2025-10-31 22:02:01');

-- Table structure for table `payments`
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `payment_method_id` bigint unsigned DEFAULT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'stripe, paypal, tranzak, etc.',
  `gateway_transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('payment','refund','payout','fee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'payment',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'XAF',
  `status` enum('pending','processing','completed','failed','cancelled','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `gateway_response` json DEFAULT NULL,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  KEY `idx_payments_order` (`order_id`),
  KEY `idx_payments_user` (`user_id`),
  KEY `idx_payments_status_date` (`status`,`created_at`),
  KEY `payments_payment_method_id_foreign` (`payment_method_id`),
  KEY `idx_payments_user_status_date` (`user_id`,`status`,`created_at`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `payments`

-- Table structure for table `popup_notifications`
DROP TABLE IF EXISTS `popup_notifications`;
CREATE TABLE `popup_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('info','success','warning','error','promotion') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `target_audience` enum('all','customers','vendors','riders','new_users') COLLATE utf8mb4_unicode_ci DEFAULT 'all',
  `target_user_id` bigint unsigned DEFAULT NULL COMMENT 'Specific user target',
  `is_active` tinyint(1) DEFAULT '1',
  `priority` tinyint(1) DEFAULT '1' COMMENT '1=low, 5=high',
  `display_count` int DEFAULT '0',
  `max_displays` int DEFAULT NULL COMMENT 'Max times to show',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_popup_notifications_active_dates` (`is_active`,`start_date`,`end_date`),
  KEY `idx_popup_notifications_target` (`target_audience`,`target_user_id`),
  KEY `popup_notifications_target_user_id_foreign` (`target_user_id`),
  KEY `popup_notifications_created_by_foreign` (`created_by`),
  CONSTRAINT `popup_notifications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `popup_notifications_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `popup_notifications`

-- Table structure for table `promo_codes`
DROP TABLE IF EXISTS `promo_codes`;
CREATE TABLE `promo_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(8,2) NOT NULL,
  `max_discount` decimal(8,2) DEFAULT NULL,
  `minimum_order` decimal(10,2) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `usage_count` int NOT NULL DEFAULT '0',
  `usage_limit_per_user` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_promo_codes_code` (`code`),
  KEY `idx_promo_codes_active` (`is_active`,`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `promo_codes`
INSERT INTO `promo_codes` (`id`, `code`, `description`, `discount_type`, `discount_value`, `max_discount`, `minimum_order`, `usage_limit`, `usage_count`, `usage_limit_per_user`, `is_active`, `starts_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('1', 'WELCOME10', 'Welcome discount', 'percentage', '10.00', '1000.00', '5000.00', '100', '0', '1', '1', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `promo_codes` (`id`, `code`, `description`, `discount_type`, `discount_value`, `max_discount`, `minimum_order`, `usage_limit`, `usage_count`, `usage_limit_per_user`, `is_active`, `starts_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('2', 'SAVE20', 'Save 20%', 'percentage', '20.00', '2000.00', '10000.00', '50', '0', '1', '1', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `promo_codes` (`id`, `code`, `description`, `discount_type`, `discount_value`, `max_discount`, `minimum_order`, `usage_limit`, `usage_count`, `usage_limit_per_user`, `is_active`, `starts_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('3', 'FIXED500', 'Fixed discount', 'fixed', '500.00', NULL, '2000.00', '25', '0', '1', '1', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36');

-- Table structure for table `restaurant_commissions`
DROP TABLE IF EXISTS `restaurant_commissions`;
CREATE TABLE `restaurant_commissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `restaurant_id` int NOT NULL,
  `commission_rate` decimal(5,4) NOT NULL DEFAULT '0.1000',
  `commission_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','disputed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_restaurant_commissions_order` (`order_id`),
  KEY `idx_restaurant_commissions_restaurant` (`restaurant_id`),
  KEY `idx_restaurant_commissions_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `restaurant_commissions`

-- Table structure for table `restaurants`
DROP TABLE IF EXISTS `restaurants`;
CREATE TABLE `restaurants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT 'Owner/Manager user ID',
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuisine_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bamenda',
  `state` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Northwest',
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Cameroon',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `delivery_radius` decimal(5,2) DEFAULT '10.00' COMMENT 'Delivery radius in KM',
  `minimum_order` decimal(8,2) DEFAULT '0.00' COMMENT 'Minimum order amount in XAF',
  `delivery_fee` decimal(6,2) DEFAULT '500.00' COMMENT 'Delivery fee in XAF',
  `delivery_fee_per_extra_km` decimal(6,2) DEFAULT '100.00' COMMENT 'Extra fee per km beyond delivery_radius (in XAF)',
  `delivery_time` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '30-45 mins',
  `commission_rate` decimal(5,4) DEFAULT '0.1500' COMMENT 'Platform commission rate (15%)',
  `rating` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int DEFAULT '0',
  `total_orders` int DEFAULT '0',
  `status` enum('pending','approved','active','inactive','suspended','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_open` tinyint(1) DEFAULT '1',
  `opening_hours` json DEFAULT NULL,
  `special_hours` json DEFAULT NULL COMMENT 'Holiday/special day hours',
  `payment_methods` json DEFAULT NULL,
  `social_links` json DEFAULT NULL,
  `business_license` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_details` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `restaurants_slug_unique` (`slug`),
  KEY `idx_restaurants_user` (`user_id`),
  KEY `idx_restaurants_status_featured` (`status`,`is_featured`),
  KEY `idx_restaurants_location` (`latitude`,`longitude`),
  KEY `idx_restaurants_cuisine` (`cuisine_type`),
  FULLTEXT KEY `name` (`name`,`description`,`cuisine_type`),
  FULLTEXT KEY `name_2` (`name`,`description`,`cuisine_type`),
  FULLTEXT KEY `name_3` (`name`,`description`,`cuisine_type`),
  FULLTEXT KEY `name_4` (`name`,`description`,`cuisine_type`),
  FULLTEXT KEY `name_5` (`name`,`description`,`cuisine_type`),
  FULLTEXT KEY `name_6` (`name`,`description`,`cuisine_type`),
  CONSTRAINT `restaurants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `restaurants`
INSERT INTO `restaurants` (`id`, `user_id`, `name`, `slug`, `description`, `image`, `cover_image`, `logo`, `cuisine_type`, `tags`, `phone`, `email`, `website`, `address`, `city`, `state`, `postal_code`, `country`, `latitude`, `longitude`, `delivery_radius`, `minimum_order`, `delivery_fee`, `delivery_fee_per_extra_km`, `delivery_time`, `commission_rate`, `rating`, `total_reviews`, `total_orders`, `status`, `is_featured`, `is_open`, `opening_hours`, `special_hours`, `payment_methods`, `social_links`, `business_license`, `tax_id`, `bank_details`, `settings`, `created_at`, `updated_at`, `deleted_at`) VALUES ('101', '1004', 'Mama Grace Kitchen', 'mama-grace-kitchen', 'Authentic Cameroonian cuisine prepared with love and tradition. Specializing in traditional dishes like Ndolé, Eru, Yellow Soup with Achu, Pepper Soup, and other local favorites that remind you of home.', 'restaurants/mama-grace.jpg', NULL, NULL, 'Cameroonian', '[\"Traditional\", \"Cameroonian\", \"Local\", \"Authentic\", \"Home-style\", \"Ndolé\", \"Eru\", \"Pepper Soup\"]', '+237 6XX XXX 004', 'grace@mamagrace.com', NULL, 'Ntarikon Quarter, Bamenda', 'Bamenda', 'Northwest', NULL, 'Cameroon', '5.96540000', '10.15080000', '25.00', '2000.00', '1000.00', '150.00', '25-35 mins', '0.1500', '4.65', '451', '3047', 'active', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-31 22:00:37', '2025-11-02 10:28:43', NULL);
INSERT INTO `restaurants` (`id`, `user_id`, `name`, `slug`, `description`, `image`, `cover_image`, `logo`, `cuisine_type`, `tags`, `phone`, `email`, `website`, `address`, `city`, `state`, `postal_code`, `country`, `latitude`, `longitude`, `delivery_radius`, `minimum_order`, `delivery_fee`, `delivery_fee_per_extra_km`, `delivery_time`, `commission_rate`, `rating`, `total_reviews`, `total_orders`, `status`, `is_featured`, `is_open`, `opening_hours`, `special_hours`, `payment_methods`, `social_links`, `business_license`, `tax_id`, `bank_details`, `settings`, `created_at`, `updated_at`, `deleted_at`) VALUES ('102', '1005', 'Quick Bites Express', 'quick-bites-express', 'Fast and delicious meals for busy people. Burgers, sandwiches, and quick African dishes.', 'restaurants/quick-bites.jpg', NULL, NULL, 'Fast Food', NULL, '+237 6XX XXX 005', 'paul@quickbites.com', NULL, 'Commercial Avenue, Bamenda', 'Bamenda', 'Northwest', NULL, 'Cameroon', '5.95870000', '10.14410000', '25.00', '1500.00', '1000.00', '150.00', '15-25 mins', '0.1500', '4.55', '136', '1567', 'active', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-31 22:00:37', '2025-11-02 10:28:43', NULL);
INSERT INTO `restaurants` (`id`, `user_id`, `name`, `slug`, `description`, `image`, `cover_image`, `logo`, `cuisine_type`, `tags`, `phone`, `email`, `website`, `address`, `city`, `state`, `postal_code`, `country`, `latitude`, `longitude`, `delivery_radius`, `minimum_order`, `delivery_fee`, `delivery_fee_per_extra_km`, `delivery_time`, `commission_rate`, `rating`, `total_reviews`, `total_orders`, `status`, `is_featured`, `is_open`, `opening_hours`, `special_hours`, `payment_methods`, `social_links`, `business_license`, `tax_id`, `bank_details`, `settings`, `created_at`, `updated_at`, `deleted_at`) VALUES ('103', '1006', 'Golden Dragon Chinese', 'golden-dragon-chinese', 'Authentic Chinese cuisine with a Cameroonian twist. Fresh ingredients and traditional cooking methods.', 'restaurants/golden-dragon.jpg', NULL, NULL, 'Chinese Food', NULL, '+237 6XX XXX 006', 'li@golddragon.com', NULL, 'Up Station, Bamenda', 'Bamenda', 'Northwest', NULL, 'Cameroon', '5.95970000', '10.14540000', '25.00', '2500.00', '1000.00', '150.00', '30-40 mins', '0.1500', '5.00', '1', '634', 'active', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-31 22:00:37', '2025-11-02 10:28:43', NULL);
INSERT INTO `restaurants` (`id`, `user_id`, `name`, `slug`, `description`, `image`, `cover_image`, `logo`, `cuisine_type`, `tags`, `phone`, `email`, `website`, `address`, `city`, `state`, `postal_code`, `country`, `latitude`, `longitude`, `delivery_radius`, `minimum_order`, `delivery_fee`, `delivery_fee_per_extra_km`, `delivery_time`, `commission_rate`, `rating`, `total_reviews`, `total_orders`, `status`, `is_featured`, `is_open`, `opening_hours`, `special_hours`, `payment_methods`, `social_links`, `business_license`, `tax_id`, `bank_details`, `settings`, `created_at`, `updated_at`, `deleted_at`) VALUES ('104', '1004', 'Bamenda Pizza Corner', 'bamenda-pizza-corner', 'Wood-fired pizzas and Italian classics. The best pizza in Northwest Region!', 'restaurants/pizza-corner.jpg', NULL, NULL, 'Pizza & Italian', NULL, '+237 6XX XXX 009', 'info@pizzacorner.com', NULL, 'Mile 4, Bamenda', 'Bamenda', 'Northwest', NULL, 'Cameroon', '5.97560000', '10.15880000', '25.00', '1800.00', '1000.00', '150.00', '20-30 mins', '0.1500', '5.00', '1', '756', 'active', '0', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-31 22:00:37', '2025-11-02 10:28:43', NULL);
INSERT INTO `restaurants` (`id`, `user_id`, `name`, `slug`, `description`, `image`, `cover_image`, `logo`, `cuisine_type`, `tags`, `phone`, `email`, `website`, `address`, `city`, `state`, `postal_code`, `country`, `latitude`, `longitude`, `delivery_radius`, `minimum_order`, `delivery_fee`, `delivery_fee_per_extra_km`, `delivery_time`, `commission_rate`, `rating`, `total_reviews`, `total_orders`, `status`, `is_featured`, `is_open`, `opening_hours`, `special_hours`, `payment_methods`, `social_links`, `business_license`, `tax_id`, `bank_details`, `settings`, `created_at`, `updated_at`, `deleted_at`) VALUES ('105', '1005', 'Healthy Harvest', 'healthy-harvest', 'Fresh, organic, and healthy meal options. Perfect for health-conscious food lovers.', 'restaurants/healthy-harvest.jpg', NULL, NULL, 'Healthy Options', NULL, '+237 6XX XXX 010', 'info@healthyharvest.com', NULL, 'Cow Street, Bamenda', 'Bamenda', 'Northwest', NULL, 'Cameroon', '5.96000000', '10.15400000', '25.00', '2200.00', '1000.00', '150.00', '25-35 mins', '0.1500', '4.40', '67', '423', 'active', '0', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-31 22:00:37', '2025-11-02 10:28:43', NULL);
INSERT INTO `restaurants` (`id`, `user_id`, `name`, `slug`, `description`, `image`, `cover_image`, `logo`, `cuisine_type`, `tags`, `phone`, `email`, `website`, `address`, `city`, `state`, `postal_code`, `country`, `latitude`, `longitude`, `delivery_radius`, `minimum_order`, `delivery_fee`, `delivery_fee_per_extra_km`, `delivery_time`, `commission_rate`, `rating`, `total_reviews`, `total_orders`, `status`, `is_featured`, `is_open`, `opening_hours`, `special_hours`, `payment_methods`, `social_links`, `business_license`, `tax_id`, `bank_details`, `settings`, `created_at`, `updated_at`, `deleted_at`) VALUES ('106', '1006', 'BBQ Masters', 'bbq-masters', 'Grilled perfection! The finest grilled meats and barbecue in Bamenda.', 'restaurants/bbq-masters.jpg', NULL, NULL, 'Grilled & BBQ', NULL, '+237 6XX XXX 011', 'info@bbqmasters.com', NULL, 'Food Market, Bamenda', 'Bamenda', 'Northwest', NULL, 'Cameroon', '5.96390000', '10.16010000', '25.00', '2000.00', '1000.00', '150.00', '35-45 mins', '0.1500', '5.00', '1', '987', 'active', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-31 22:00:37', '2025-11-02 10:28:43', NULL);

-- Table structure for table `review_helpful`
DROP TABLE IF EXISTS `review_helpful`;
CREATE TABLE `review_helpful` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_review_user` (`review_id`,`user_id`),
  KEY `idx_review_id` (`review_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `review_helpful_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_helpful_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `review_helpful`

-- Table structure for table `review_reports`
DROP TABLE IF EXISTS `review_reports`;
CREATE TABLE `review_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','reviewed','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_review_id` (`review_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `review_reports_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `review_reports`

-- Table structure for table `review_votes`
DROP TABLE IF EXISTS `review_votes`;
CREATE TABLE `review_votes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `vote` enum('helpful','unhelpful') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `review_votes_review_user_unique` (`review_id`,`user_id`),
  KEY `idx_review_votes_review` (`review_id`),
  KEY `idx_review_votes_user` (`user_id`),
  CONSTRAINT `review_votes_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `review_votes`

-- Table structure for table `reviews`
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `reviewable_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'restaurant, menu_item, delivery',
  `reviewable_id` bigint unsigned NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT '1-5 stars',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `images` json DEFAULT NULL COMMENT 'Review images',
  `is_verified` tinyint(1) DEFAULT '0' COMMENT 'Verified purchase',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_approved` tinyint(1) DEFAULT '1',
  `status` enum('pending','approved','rejected','hidden') COLLATE utf8mb4_unicode_ci DEFAULT 'approved',
  `helpful_count` int DEFAULT '0',
  `unhelpful_count` int DEFAULT '0',
  `response` text COLLATE utf8mb4_unicode_ci COMMENT 'Business response',
  `responded_at` timestamp NULL DEFAULT NULL,
  `responded_by` bigint unsigned DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Admin moderation notes',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hidden_by` bigint unsigned DEFAULT NULL,
  `hidden_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reviews_user` (`user_id`),
  KEY `idx_reviews_order` (`order_id`),
  KEY `idx_reviews_reviewable` (`reviewable_type`,`reviewable_id`),
  KEY `idx_reviews_rating_status` (`rating`,`status`),
  KEY `reviews_responded_by_foreign` (`responded_by`),
  KEY `reviews_approved_by_foreign` (`approved_by`),
  KEY `reviews_rejected_by_foreign` (`rejected_by`),
  KEY `reviews_hidden_by_foreign` (`hidden_by`),
  KEY `idx_reviews_reviewable_rating` (`reviewable_type`,`reviewable_id`,`rating`,`status`),
  CONSTRAINT `reviews_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_hidden_by_foreign` FOREIGN KEY (`hidden_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_responded_by_foreign` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `reviews`
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('1', '1001', NULL, 'restaurant', '101', '5', 'Amazing Ndolé!', 'Mama Grace makes the best Ndolé in Bamenda. The taste is authentic and reminds me of my grandmother\'s cooking. Highly recommended!', NULL, '1', '0', '1', 'approved', '12', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-16 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('2', '1002', NULL, 'restaurant', '102', '4', 'Quick and tasty', 'Great for a quick meal. The burger was juicy and the service was fast. Will definitely order again.', NULL, '1', '0', '1', 'approved', '8', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('3', '1003', NULL, 'restaurant', '103', '5', 'Excellent Chinese food', 'The sweet and sour chicken was perfect! Authentic taste and generous portions. Golden Dragon is now my go-to for Chinese food.', NULL, '1', '0', '1', 'approved', '15', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-23 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('4', '1001', NULL, 'menu_item', '1001', '5', 'Perfect Ndolé', 'This Ndolé is exactly how it should be made. Rich, flavorful, and the beef was so tender. Worth every franc!', NULL, '1', '0', '1', 'approved', '9', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-19 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('5', '1002', NULL, 'menu_item', '1006', '4', 'Great burger', 'The beef burger was really good. Fresh ingredients and cooked perfectly. The fries could be crispier though.', NULL, '1', '0', '1', 'approved', '6', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-24 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('6', '1003', NULL, 'restaurant', '104', '5', 'Best pizza in town', 'The Margherita pizza was incredible! Thin crust, fresh basil, and the cheese was perfect. Definitely the best pizza in Bamenda.', NULL, '1', '0', '1', 'approved', '18', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('7', '1001', NULL, 'delivery', '1', '4', 'Good delivery service', 'The rider was polite and delivered on time. Food arrived hot and well-packaged. Good service overall.', NULL, '1', '0', '1', 'approved', '4', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-28 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('8', '1002', NULL, 'restaurant', '106', '5', 'BBQ perfection', 'BBQ Masters lives up to its name! The grilled chicken was smoky and delicious. The sauce was amazing too.', NULL, '1', '0', '1', 'approved', '11', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-29 22:00:37', '2025-10-31 22:00:37');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('101', '1001', NULL, 'menu_item', '1501', '5', 'Authentic Yellow Soup!', 'This yellow soup with achu takes me back to my village! The taste is so authentic and the achu was perfectly pounded. Mama Grace knows her traditional cooking!', NULL, '1', '0', '1', 'approved', '18', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-24 04:28:14', '2025-11-01 04:28:14');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('102', '1002', NULL, 'menu_item', '1503', '5', 'Best Pepper Soup in Bamenda', 'The pepper soup is incredibly spicy and flavorful. You can taste all the local spices. Perfect for cold evenings!', NULL, '1', '0', '1', 'approved', '22', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 04:28:14', '2025-11-01 04:28:14');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('103', '1003', NULL, 'menu_item', '1504', '5', 'Amazing African Fried Rice', 'This is not your regular fried rice! The African style preparation makes it so much more flavorful. Love the use of local seasonings!', NULL, '1', '0', '1', 'approved', '15', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 04:28:14', '2025-11-01 04:28:14');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('104', '1001', NULL, 'menu_item', '1507', '4', 'Delicious Lemon Pepper Chicken', 'The chicken drumsticks were well-seasoned and juicy. Great flavor combination with the lemon and pepper!', NULL, '1', '0', '1', 'approved', '11', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-28 04:28:14', '2025-11-01 04:28:14');
INSERT INTO `reviews` (`id`, `user_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `images`, `is_verified`, `is_featured`, `is_approved`, `status`, `helpful_count`, `unhelpful_count`, `response`, `responded_at`, `responded_by`, `admin_notes`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `hidden_by`, `hidden_at`, `created_at`, `updated_at`) VALUES ('105', '1002', NULL, 'menu_item', '1502', '4', 'Hearty Lentil Stew', 'The lentil stew was very filling and nutritious. Good vegetarian option with great local flavors!', NULL, '1', '0', '1', 'approved', '8', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-22 04:28:14', '2025-11-01 04:28:14');

-- Table structure for table `rider_assignments`
DROP TABLE IF EXISTS `rider_assignments`;
CREATE TABLE `rider_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `rider_id` bigint unsigned NOT NULL,
  `status` enum('assigned','accepted','picked_up','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assigned',
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `picked_up_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_rider_id` (`rider_id`),
  KEY `idx_status` (`status`),
  KEY `idx_assigned_at` (`assigned_at`),
  CONSTRAINT `rider_assignments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rider_assignments_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `rider_assignments`

-- Table structure for table `rider_earnings`
DROP TABLE IF EXISTS `rider_earnings`;
CREATE TABLE `rider_earnings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `rider_id` int NOT NULL,
  `base_fee` decimal(8,2) NOT NULL,
  `distance_fee` decimal(8,2) NOT NULL DEFAULT '0.00',
  `bonus` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total_earnings` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','disputed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rider_earnings_order` (`order_id`),
  KEY `idx_rider_earnings_rider` (`rider_id`),
  KEY `idx_rider_earnings_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `rider_earnings`

-- Table structure for table `rider_locations`
DROP TABLE IF EXISTS `rider_locations`;
CREATE TABLE `rider_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rider_id` bigint unsigned NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `accuracy` decimal(5,2) DEFAULT NULL COMMENT 'GPS accuracy in meters',
  `speed` decimal(5,2) DEFAULT NULL COMMENT 'Speed in km/h',
  `heading` decimal(5,2) DEFAULT NULL COMMENT 'Direction in degrees',
  `is_online` tinyint(1) DEFAULT '1',
  `battery_level` tinyint DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rider_locations_rider_time` (`rider_id`,`created_at`),
  CONSTRAINT `rider_locations_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `rider_locations`

-- Table structure for table `rider_schedules`
DROP TABLE IF EXISTS `rider_schedules`;
CREATE TABLE `rider_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rider_id` bigint unsigned NOT NULL,
  `day_of_week` tinyint(1) NOT NULL COMMENT '0=Sunday, 1=Monday, etc.',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT '1',
  `max_orders` int DEFAULT '5' COMMENT 'Max concurrent orders',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rider_schedules_rider_day` (`rider_id`,`day_of_week`),
  CONSTRAINT `rider_schedules_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `rider_schedules`
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('1', '1007', '1', '08:00:00', '18:00:00', '1', '5', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('2', '1007', '2', '08:00:00', '18:00:00', '1', '5', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('3', '1007', '3', '08:00:00', '18:00:00', '1', '5', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('4', '1007', '4', '08:00:00', '18:00:00', '1', '5', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('5', '1007', '5', '08:00:00', '20:00:00', '1', '6', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('6', '1007', '6', '09:00:00', '20:00:00', '1', '6', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('7', '1008', '1', '10:00:00', '22:00:00', '1', '4', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('8', '1008', '2', '10:00:00', '22:00:00', '1', '4', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('9', '1008', '3', '10:00:00', '22:00:00', '1', '4', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('10', '1008', '4', '10:00:00', '22:00:00', '1', '4', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('11', '1008', '5', '10:00:00', '23:00:00', '1', '5', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('12', '1008', '6', '10:00:00', '23:00:00', '1', '5', '2025-10-31 22:02:01', '2025-10-31 22:02:01');
INSERT INTO `rider_schedules` (`id`, `rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`, `created_at`, `updated_at`) VALUES ('13', '1008', '0', '12:00:00', '20:00:00', '1', '3', '2025-10-31 22:02:01', '2025-10-31 22:02:01');

-- Table structure for table `role_change_requests`
DROP TABLE IF EXISTS `role_change_requests`;
CREATE TABLE `role_change_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `current_role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `documents` json DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `role_change_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_change_requests_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `role_change_requests`

-- Table structure for table `site_settings`
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `type` enum('string','integer','boolean','json','text','float') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `group` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_public` tinyint(1) DEFAULT '0' COMMENT 'Can be accessed by frontend',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  UNIQUE KEY `site_settings_key_unique` (`key`),
  KEY `idx_site_settings_group` (`group`),
  KEY `idx_site_settings_public` (`is_public`)
) ENGINE=InnoDB AUTO_INCREMENT=502 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `site_settings`
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('1', 'site_name', 'Time2Eat', 'string', 'general', 'Site name', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('2', 'site_description', 'Bamenda Food Delivery Platform', 'text', 'general', 'Site description', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('3', 'timezone', 'Africa/Douala', 'string', 'general', 'Site timezone', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('4', 'contact_email', 'info@time2eat.cm', 'string', 'contact', 'Main contact email', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('5', 'contact_phone', '+237 6XX XXX XXX', 'string', 'contact', 'Main contact phone', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('6', 'contact_address', 'Bamenda, North West Region, Cameroon', 'text', 'contact', 'Business address', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('7', 'contact_hours', 'Mon-Sat: 8AM-10PM, Sun: 10AM-8PM', 'string', 'contact', 'Business hours', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('8', 'support_email', 'support@time2eat.cm', 'string', 'contact', 'Support email address', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('9', 'emergency_contact', '+237 6XX XXX XXX', 'string', 'contact', 'Emergency contact number', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('10', 'facebook_url', '', 'string', 'social', 'Facebook page URL', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('11', 'twitter_url', '', 'string', 'social', 'Twitter profile URL', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('12', 'instagram_url', '', 'string', 'social', 'Instagram profile URL', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('13', 'youtube_url', '', 'string', 'social', 'YouTube channel URL', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('14', 'linkedin_url', '', 'string', 'social', 'LinkedIn profile URL', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('15', 'tiktok_url', '', 'string', 'social', 'TikTok profile URL', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('16', 'whatsapp_number', '', 'string', 'social', 'WhatsApp business number', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('17', 'delivery_fee', '500', 'integer', 'business', 'Standard delivery fee in XAF', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('18', 'free_delivery_threshold', '5000', 'integer', 'business', 'Free delivery threshold in XAF', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('19', 'commission_rate', '0.15', 'float', 'business', 'Platform commission rate (15%)', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('20', 'tax_rate', '0.1925', 'float', 'business', 'Tax rate (19.25%)', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('21', 'currency', 'XAF', 'string', 'business', 'Primary currency', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('22', 'max_delivery_distance', '15', 'integer', 'business', 'Maximum delivery distance in KM', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('23', 'meta_keywords', 'food delivery, Bamenda, restaurants, online ordering', 'text', 'seo', 'Meta keywords for SEO', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('24', 'google_analytics_id', '', 'string', 'seo', 'Google Analytics tracking ID', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('25', 'facebook_pixel_id', '', 'string', 'seo', 'Facebook Pixel ID', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('26', 'allow_registration', 'true', 'boolean', 'auth', 'Allow new user registration', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('27', 'email_verification_required', 'true', 'boolean', 'auth', 'Require email verification', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('28', 'maintenance_mode', 'false', 'boolean', 'system', 'Enable maintenance mode', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('29', 'map_provider', 'leaflet', 'string', 'maps', 'Map provider to use: \"google\" for Google Maps or \"leaflet\" for OpenStreetMap. Changing this will instantly switch all maps across the application.', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('30', 'google_maps_api_key', '', 'string', 'maps', 'Google Maps API key. Required if map_provider is set to \"google\". Get your API key from Google Cloud Console.', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('31', 'mapbox_access_token', '', 'string', 'maps', 'Mapbox access token (optional). Can be used for additional map features.', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('32', 'default_latitude', '5.9631', 'string', 'maps', 'Default map center latitude (Bamenda, Cameroon: 5.9631)', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('33', 'default_longitude', '10.1591', 'string', 'maps', 'Default map center longitude (Bamenda, Cameroon: 10.1591)', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('34', 'default_zoom_level', '13', 'integer', 'maps', 'Default map zoom level (1-20, recommended: 13 for city view)', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('35', 'enable_location_tracking', 'true', 'boolean', 'maps', 'Enable real-time GPS location tracking for riders and customers', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('36', 'payment_methods_enabled', '[\"cash\",\"mobile_money\",\"orange_money\",\"mtn_momo\"]', 'json', 'payment', 'Enabled payment methods', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('37', 'cash_on_delivery_enabled', 'true', 'boolean', 'payment', 'Enable cash on delivery', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('38', 'online_payment_enabled', 'true', 'boolean', 'payment', 'Enable online payments', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('39', 'stripe_publishable_key', '', 'string', 'payment', 'Stripe Publishable Key', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('40', 'stripe_secret_key', '', 'string', 'payment', 'Stripe Secret Key', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('41', 'paypal_client_id', '', 'string', 'payment', 'PayPal Client ID', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('42', 'paypal_client_secret', '', 'string', 'payment', 'PayPal Client Secret', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('43', 'paypal_sandbox_mode', 'true', 'boolean', 'payment', 'PayPal Sandbox Mode', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('44', 'orange_money_merchant_id', '', 'string', 'payment', 'Orange Money Merchant ID', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('45', 'orange_money_api_key', '', 'string', 'payment', 'Orange Money API Key', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('46', 'mtn_momo_api_key', '', 'string', 'payment', 'MTN Mobile Money API Key', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('47', 'mtn_momo_user_id', '', 'string', 'payment', 'MTN Mobile Money User ID', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('48', 'payment_processing_fee', '0.025', 'float', 'payment', 'Payment processing fee percentage (2.5%)', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('49', 'minimum_order_amount', '1000', 'integer', 'payment', 'Minimum order amount in XAF', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('50', 'primary_currency', 'XAF', 'string', 'currency', 'Primary currency code', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('51', 'currency_symbol', 'FCFA', 'string', 'currency', 'Currency symbol', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('52', 'currency_position', 'after', 'string', 'currency', 'Currency symbol position (before/after)', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('53', 'decimal_places', '0', 'integer', 'currency', 'Number of decimal places for currency', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('54', 'thousand_separator', ',', 'string', 'currency', 'Thousand separator', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('55', 'decimal_separator', '.', 'string', 'currency', 'Decimal separator', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('56', 'exchange_rate_usd', '600', 'float', 'currency', 'Exchange rate to USD (1 USD = X XAF)', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('57', 'auto_update_exchange_rates', 'false', 'boolean', 'currency', 'Automatically update exchange rates', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('58', 'pwa_enabled', 'true', 'boolean', 'system', 'Enable Progressive Web App features', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('59', 'app_name', 'Time2Eat - Food Delivery', 'string', 'system', 'PWA application name', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('60', 'app_short_name', 'Time2Eat', 'string', 'system', 'PWA short name', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('61', 'app_description', 'Order delicious food from your favorite restaurants in Bamenda', 'text', 'system', 'PWA application description', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('62', 'theme_color', '#3B82F6', 'string', 'system', 'PWA theme color', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('63', 'background_color', '#FFFFFF', 'string', 'system', 'PWA background color', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('64', 'push_notifications_enabled', 'true', 'boolean', 'system', 'Enable push notifications', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('65', 'cod_enabled', 'true', 'boolean', 'payment', 'Enable Cash on Delivery (alias for cash_on_delivery_enabled)', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('66', 'tranzak_enabled', 'true', 'boolean', 'payment', 'Enable Tranzak payment gateway', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('67', 'tranzak_app_id', 'aplp1yf70tbaay', 'string', 'payment', 'Tranzak App ID / Merchant ID', '0', '2025-10-31 21:56:27', '2025-11-01 03:55:14');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('68', 'tranzak_api_key', 'PROD_1420487AE95C4A8AA2704A0773593E68', 'string', 'payment', 'Tranzak Production App Key', '0', '2025-10-31 21:56:27', '2025-11-01 03:55:14');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('69', 'tranzak_sandbox_api_key', 'SAND_538F8F22667B4FF7B061E8B07232B48C', 'string', 'payment', 'Tranzak Sandbox App Key', '0', '2025-10-31 21:56:27', '2025-11-01 03:57:39');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('70', 'tranzak_api_secret', '', 'string', 'payment', 'Tranzak API Secret', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('71', 'tranzak_sandbox_mode', 'false', 'boolean', 'payment', 'Use Tranzak sandbox environment for testing', '0', '2025-10-31 21:56:27', '2025-11-01 03:55:15');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('72', 'min_withdrawal_affiliate', '5000', 'integer', 'withdrawal', 'Minimum withdrawal amount for affiliates (XAF)', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('73', 'min_withdrawal_rider', '2000', 'integer', 'withdrawal', 'Minimum withdrawal amount for riders (XAF)', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('74', 'min_withdrawal_restaurant', '10000', 'integer', 'withdrawal', 'Minimum withdrawal amount for restaurants (XAF)', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('75', 'withdrawal_processing_fee', '0', '', 'withdrawal', 'Processing fee for withdrawals (XAF)', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('76', 'withdrawal_auto_approve_limit', '50000', 'integer', 'withdrawal', 'Auto-approve withdrawals below this amount (XAF)', '0', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('77', 'withdrawal_processing_time', '24', 'integer', 'withdrawal', 'Standard withdrawal processing time in hours', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('78', 'withdrawal_notification_enabled', 'true', 'boolean', 'withdrawal', 'Enable withdrawal status notifications', '1', '2025-10-31 21:56:27', '2025-10-31 21:56:27');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('391', 'site_tagline', 'Bamenda\'s Premier Food Delivery Service', 'string', 'general', 'Website tagline', '1', '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('392', 'business_hours', '{\"monday\": \"8:00-22:00\", \"tuesday\": \"8:00-22:00\", \"wednesday\": \"8:00-22:00\", \"thursday\": \"8:00-22:00\", \"friday\": \"8:00-23:00\", \"saturday\": \"8:00-23:00\", \"sunday\": \"10:00-22:00\"}', 'json', 'business', 'Business operating hours', '1', '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('393', 'minimum_order', '2000', 'integer', 'business', 'Minimum order amount in XAF', '1', '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('394', 'social_facebook', 'https://facebook.com/time2eat', 'string', 'social', 'Facebook page URL', '1', '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('395', 'social_instagram', 'https://instagram.com/time2eat', 'string', 'social', 'Instagram profile URL', '1', '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('396', 'social_twitter', 'https://twitter.com/time2eat', 'string', 'social', 'Twitter profile URL', '1', '2025-10-31 22:00:36', '2025-10-31 22:00:36');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('499', 'tranzak_webhook_auth_key', 'q@dLi{3>CMd.p-.$cG0Wqg1@EFk8XxRmhf?', 'string', 'payment', 'Tranzak Webhook Auth Key (set in developer portal)', '0', '2025-11-01 03:55:15', '2025-11-01 03:55:15');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('500', 'tranzak_currency', 'XAF', 'string', 'payment', 'Default currency for Tranzak payments', '1', '2025-11-01 03:55:15', '2025-11-01 03:55:15');
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES ('501', 'tranzak_country', 'CM', 'string', 'payment', 'Country code for Tranzak (Cameroon)', '1', '2025-11-01 03:55:15', '2025-11-01 03:55:15');

-- Table structure for table `user_activities`
DROP TABLE IF EXISTS `user_activities`;
CREATE TABLE `user_activities` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `activity_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_description` text COLLATE utf8mb4_unicode_ci,
  `entity_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `user_activities`

-- Table structure for table `user_addresses`
DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE `user_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Address label (e.g., Home, Work, Office)',
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Street address',
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Apartment, suite, unit, etc.',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Cameroon',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'GPS latitude',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'GPS longitude',
  `is_default` tinyint(1) DEFAULT '0' COMMENT 'Is this the default address',
  `delivery_instructions` text COLLATE utf8mb4_unicode_ci COMMENT 'Special delivery instructions',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_default` (`is_default`),
  KEY `idx_user_default` (`user_id`,`is_default`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_addresses_active` (`user_id`,`deleted_at`),
  KEY `idx_user_addresses_location` (`latitude`,`longitude`),
  CONSTRAINT `user_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `user_addresses`

-- Table structure for table `user_balances`
DROP TABLE IF EXISTS `user_balances`;
CREATE TABLE `user_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `balance_type` enum('affiliate','rider','restaurant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_balance` (`user_id`,`balance_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_balance_type` (`balance_type`),
  CONSTRAINT `user_balances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `user_balances`

-- Table structure for table `user_profiles`
DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Cameroon',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_links` json DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `dietary_restrictions` json DEFAULT NULL,
  `favorite_cuisines` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `user_profiles`

-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','vendor','rider','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_available` tinyint(1) DEFAULT '0' COMMENT 'Rider availability status',
  `affiliate_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `affiliate_rate` decimal(5,4) DEFAULT '0.0500' COMMENT 'Commission rate for affiliates (5%)',
  `referred_by` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Referral code used during signup',
  `balance` decimal(10,2) DEFAULT '0.00' COMMENT 'User wallet balance in XAF',
  `total_earnings` decimal(12,2) DEFAULT '0.00' COMMENT 'Total affiliate earnings',
  `referral_count` int DEFAULT '0',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_notifications` tinyint(1) DEFAULT '1',
  `sms_notifications` tinyint(1) DEFAULT '1',
  `push_notifications` tinyint(1) DEFAULT '1',
  `two_factor_enabled` tinyint(1) DEFAULT '0',
  `two_factor_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `affiliate_code` (`affiliate_code`),
  UNIQUE KEY `users_affiliate_code_unique` (`affiliate_code`),
  KEY `idx_users_role_status` (`role`,`status`),
  KEY `idx_users_affiliate_code` (`affiliate_code`),
  KEY `idx_users_referred_by` (`referred_by`),
  KEY `idx_users_email_verified` (`email_verified_at`),
  KEY `idx_users_is_available` (`is_available`),
  KEY `idx_users_role_available` (`role`,`is_available`)
) ENGINE=InnoDB AUTO_INCREMENT=1011 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `users`
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1000', 'sample_admin', 'sample_admin@time2eat.com', '2025-10-31 22:00:37', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sample', 'Admin', '+237 6XX XXX 000', NULL, 'admin', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', NULL, NULL, '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:00:37', '2025-10-31 22:00:37', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1001', 'john_doe', 'john@example.com', '2025-10-31 22:00:36', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+237 6XX XXX 001', NULL, 'customer', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', '2025-11-01 17:42:29', '127.0.0.1', '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:00:36', '2025-11-01 17:42:29', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1002', 'mary_smith', 'mary@example.com', '2025-10-31 22:00:36', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mary', 'Smith', '+237 6XX XXX 002', NULL, 'customer', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', '2025-11-01 17:42:44', '127.0.0.1', '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:00:36', '2025-11-01 17:42:44', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1003', 'peter_jones', 'peter@example.com', '2025-10-31 22:00:36', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peter', 'Jones', '+237 6XX XXX 003', NULL, 'customer', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', '2025-11-06 01:28:16', '127.0.0.1', '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:00:36', '2025-11-06 01:28:16', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1004', 'mama_grace', 'grace@mamagrace.com', '2025-10-31 22:00:36', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace', 'Mbah', '+237 6XX XXX 004', NULL, 'vendor', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', '2025-11-06 06:24:02', '127.0.0.1', '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:00:36', '2025-11-06 06:24:02', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1005', 'chef_paul', 'paul@quickbites.com', '2025-10-31 22:00:36', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paul', 'Nkeng', '+237 6XX XXX 005', NULL, 'vendor', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', NULL, NULL, '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1006', 'wang_li', 'li@golddragon.com', '2025-10-31 22:00:36', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Li', 'Wang', '+237 6XX XXX 006', NULL, 'vendor', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', NULL, NULL, '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:00:36', '2025-10-31 22:00:36', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1007', 'rider_james', 'james@time2eat.com', '2025-10-31 22:00:37', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James', 'Tabi', '+237 6XX XXX 007', NULL, 'rider', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', '2025-11-01 03:33:11', '127.0.0.1', '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:00:37', '2025-11-01 03:33:11', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1008', 'rider_sarah', 'sarah@time2eat.com', '2025-10-31 22:00:37', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Fon', '+237 6XX XXX 008', NULL, 'rider', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', NULL, NULL, '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:00:37', '2025-10-31 22:00:37', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1009', 'admin_690523ef95167', 'admin@time2eat.com', '2025-10-31 22:02:39', '$2y$10$hxPuwtqBo2.3vD7qL7HeheJ74sBxML.XTjAG9EpT.3j.XFa76FfcO', 'Admin', 'Administrator', '+237000000000', NULL, 'admin', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', '2025-11-06 06:32:03', '127.0.0.1', '1', '1', '1', '0', NULL, NULL, '2025-10-31 22:02:39', '2025-11-06 06:32:03', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `role`, `status`, `is_available`, `affiliate_code`, `affiliate_rate`, `referred_by`, `balance`, `total_earnings`, `referral_count`, `last_login_at`, `last_login_ip`, `email_notifications`, `sms_notifications`, `push_notifications`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1010', '', 'test.customer@time2eat.cm', NULL, '$2y$12$KpTPwjqD/AoRKS5bc.6qFOwCLotN1T2Nszhm690a6Jo.pM5S1S676', 'Test', 'Customer', '+237123456789', NULL, 'customer', 'active', '0', NULL, '0.0500', NULL, '0.00', '0.00', '0', NULL, NULL, '1', '1', '1', '0', NULL, NULL, '2025-11-01 13:02:23', '2025-11-01 13:02:23', NULL);

-- Table structure for table `wishlists`
DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `menu_item_id` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_user_menu_item_unique` (`user_id`,`menu_item_id`),
  KEY `idx_wishlists_user` (`user_id`),
  KEY `idx_wishlists_menu_item` (`menu_item_id`),
  CONSTRAINT `wishlists_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `wishlists`

-- Table structure for table `withdrawal_logs`
DROP TABLE IF EXISTS `withdrawal_logs`;
CREATE TABLE `withdrawal_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `withdrawal_id` bigint unsigned NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_withdrawal_id` (`withdrawal_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `withdrawal_logs_withdrawal_id_foreign` FOREIGN KEY (`withdrawal_id`) REFERENCES `withdrawals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `withdrawal_logs`

-- Table structure for table `withdrawals`
DROP TABLE IF EXISTS `withdrawals`;
CREATE TABLE `withdrawals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `withdrawal_type` enum('affiliate','rider','restaurant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_details` json DEFAULT NULL,
  `status` enum('pending','approved','rejected','processing') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `withdrawal_reference` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_by` bigint unsigned DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `withdrawal_reference` (`withdrawal_reference`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_withdrawal_type` (`withdrawal_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_withdrawal_reference` (`withdrawal_reference`),
  KEY `withdrawals_processed_by_foreign` (`processed_by`),
  CONSTRAINT `withdrawals_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `withdrawals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `withdrawals`

SET FOREIGN_KEY_CHECKS=1;
