-- ============================================================================
-- Time2Eat Complete Database Schema and Data
-- Generated for installation script
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================================
-- DATABASE SCHEMA
-- ============================================================================

-- Users table with multi-role support and affiliate system
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('customer','vendor','rider','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended','pending') NOT NULL DEFAULT 'active',
  `is_available` boolean DEFAULT FALSE COMMENT 'Rider availability status',
  `affiliate_code` varchar(20) UNIQUE DEFAULT NULL,
  `affiliate_rate` decimal(5,4) DEFAULT 0.0500 COMMENT 'Commission rate for affiliates (5%)',
  `referred_by` varchar(20) DEFAULT NULL COMMENT 'Referral code used during signup',
  `balance` decimal(10,2) DEFAULT 0.00 COMMENT 'User wallet balance in XAF',
  `total_earnings` decimal(12,2) DEFAULT 0.00 COMMENT 'Total affiliate earnings',
  `referral_count` int(11) DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `email_notifications` boolean DEFAULT TRUE,
  `sms_notifications` boolean DEFAULT TRUE,
  `push_notifications` boolean DEFAULT TRUE,
  `two_factor_enabled` boolean DEFAULT FALSE,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_affiliate_code_unique` (`affiliate_code`),
  KEY `idx_users_role_status` (`role`, `status`),
  KEY `idx_users_affiliate_code` (`affiliate_code`),
  KEY `idx_users_referred_by` (`referred_by`),
  KEY `idx_users_email_verified` (`email_verified_at`),
  KEY `idx_users_is_available` (`is_available`),
  KEY `idx_users_role_available` (`role`, `is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email verifications table for tracking email verification codes
CREATE TABLE `email_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `verification_token` varchar(255) NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email_verification` (`email`),
  KEY `idx_email_verifications_token` (`verification_token`),
  KEY `idx_email_verifications_expires` (`expires_at`),
  KEY `idx_email_verifications_verified` (`verified_at`),
  KEY `idx_email_verifications_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User profiles for extended information
CREATE TABLE `user_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Cameroon',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
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

-- User addresses table for saved delivery addresses
CREATE TABLE `user_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(100) NOT NULL COMMENT 'Address label (e.g., Home, Work, Office)',
  `address_line_1` varchar(255) NOT NULL COMMENT 'Street address',
  `address_line_2` varchar(255) DEFAULT NULL COMMENT 'Apartment, suite, unit, etc.',
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Cameroon',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'GPS latitude',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'GPS longitude',
  `is_default` tinyint(1) DEFAULT 0 COMMENT 'Is this the default address',
  `delivery_instructions` text DEFAULT NULL COMMENT 'Special delivery instructions',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_default` (`is_default`),
  KEY `idx_user_default` (`user_id`, `is_default`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_addresses_active` (`user_id`, `deleted_at`),
  KEY `idx_user_addresses_location` (`latitude`, `longitude`),
  CONSTRAINT `user_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table with hierarchical support
CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` boolean DEFAULT TRUE,
  `is_featured` boolean DEFAULT FALSE,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `idx_categories_parent` (`parent_id`),
  KEY `idx_categories_active_featured` (`is_active`, `is_featured`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restaurants table with comprehensive business information
CREATE TABLE `restaurants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Owner/Manager user ID',
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `cuisine_type` varchar(100) DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL DEFAULT 'Bamenda',
  `state` varchar(100) NOT NULL DEFAULT 'Northwest',
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Cameroon',
  `latitude` decimal(10,8) NULL DEFAULT NULL,
  `longitude` decimal(11,8) NULL DEFAULT NULL,
  `delivery_radius` decimal(5,2) DEFAULT 10.00 COMMENT 'Delivery radius in KM',
  `minimum_order` decimal(8,2) DEFAULT 0.00 COMMENT 'Minimum order amount in XAF',
  `delivery_fee` decimal(6,2) DEFAULT 500.00 COMMENT 'Delivery fee in XAF',
  `delivery_time` varchar(50) DEFAULT '30-45 mins',
  `commission_rate` decimal(5,4) DEFAULT 0.1500 COMMENT 'Platform commission rate (15%)',
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `total_orders` int(11) DEFAULT 0,
  `status` enum('pending','approved','active','inactive','suspended','rejected') NOT NULL DEFAULT 'pending',
  `is_featured` boolean DEFAULT FALSE,
  `is_open` boolean DEFAULT TRUE,
  `opening_hours` json DEFAULT NULL,
  `special_hours` json DEFAULT NULL COMMENT 'Holiday/special day hours',
  `payment_methods` json DEFAULT NULL,
  `social_links` json DEFAULT NULL,
  `business_license` varchar(255) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `bank_details` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurants_slug_unique` (`slug`),
  KEY `idx_restaurants_user` (`user_id`),
  KEY `idx_restaurants_status_featured` (`status`, `is_featured`),
  KEY `idx_restaurants_location` (`latitude`, `longitude`),
  KEY `idx_restaurants_cuisine` (`cuisine_type`),
  CONSTRAINT `restaurants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu categories table for vendor menu management
CREATE TABLE `menu_categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_per_restaurant` (`restaurant_id`, `name`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_menu_categories_restaurant_active` (`restaurant_id`, `is_active`),
  CONSTRAINT `menu_categories_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu items with comprehensive product information
CREATE TABLE `menu_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `images` json DEFAULT NULL COMMENT 'Additional product images',
  `price` decimal(8,2) NOT NULL COMMENT 'Price in XAF',
  `compare_price` decimal(8,2) DEFAULT NULL COMMENT 'Original price for discounts',
  `cost_price` decimal(8,2) DEFAULT NULL COMMENT 'Cost price for profit calculation',
  `sku` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `allergens` json DEFAULT NULL,
  `nutritional_info` json DEFAULT NULL,
  `preparation_time` int(11) DEFAULT 15 COMMENT 'Prep time in minutes',
  `calories` int(11) DEFAULT NULL,
  `spice_level` enum('none','mild','medium','hot','very_hot') DEFAULT 'none',
  `dietary_tags` json DEFAULT NULL COMMENT 'vegetarian, vegan, gluten-free, etc.',
  `is_available` boolean DEFAULT TRUE,
  `is_featured` boolean DEFAULT FALSE,
  `is_popular` boolean DEFAULT FALSE,
  `stock_quantity` int(11) DEFAULT NULL COMMENT 'NULL = unlimited',
  `low_stock_threshold` int(11) DEFAULT 5,
  `min_stock_level` int(11) DEFAULT 5,
  `is_vegetarian` tinyint(1) DEFAULT 0,
  `is_vegan` tinyint(1) DEFAULT 0,
  `is_gluten_free` tinyint(1) DEFAULT 0,
  `customization_options` json DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `total_orders` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_items_restaurant_slug_unique` (`restaurant_id`, `slug`),
  KEY `idx_menu_items_restaurant` (`restaurant_id`),
  KEY `idx_menu_items_category` (`category_id`),
  KEY `idx_menu_items_available_featured` (`is_available`, `is_featured`),
  KEY `idx_menu_items_price` (`price`),
  KEY `idx_menu_items_stock_quantity` (`stock_quantity`),
  KEY `idx_menu_items_deleted_at` (`deleted_at`),
  KEY `idx_menu_items_restaurant_category` (`restaurant_id`, `category_id`),
  KEY `idx_menu_items_restaurant_available` (`restaurant_id`, `is_available`),
  KEY `idx_menu_items_category_available` (`category_id`, `is_available`),
  CONSTRAINT `menu_items_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu item variants (sizes, add-ons, customizations)
CREATE TABLE `menu_item_variants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('size','addon','customization') NOT NULL DEFAULT 'addon',
  `price_adjustment` decimal(6,2) DEFAULT 0.00 COMMENT 'Price difference from base item',
  `is_required` boolean DEFAULT FALSE,
  `max_selections` int(11) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `is_available` boolean DEFAULT TRUE,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_variants_menu_item` (`menu_item_id`),
  CONSTRAINT `menu_item_variants_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table with comprehensive tracking
CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL UNIQUE,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `rider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `affiliate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','confirmed','preparing','ready','picked_up','on_the_way','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded','partially_refunded') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Items total in XAF',
  `tax_amount` decimal(8,2) DEFAULT 0.00 COMMENT 'Tax amount in XAF',
  `delivery_fee` decimal(6,2) DEFAULT 0.00 COMMENT 'Delivery fee in XAF',
  `service_fee` decimal(6,2) DEFAULT 0.00 COMMENT 'Platform service fee',
  `discount_amount` decimal(8,2) DEFAULT 0.00 COMMENT 'Total discount applied',
  `affiliate_commission` decimal(6,2) DEFAULT 0.00 COMMENT 'Affiliate commission',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Final total in XAF',
  `currency` varchar(3) DEFAULT 'XAF',
  `delivery_address` json NOT NULL COMMENT 'Delivery address details',
  `delivery_instructions` text DEFAULT NULL,
  `estimated_delivery_time` timestamp NULL DEFAULT NULL,
  `actual_delivery_time` timestamp NULL DEFAULT NULL,
  `preparation_time` int(11) DEFAULT NULL COMMENT 'Estimated prep time in minutes',
  `delivery_distance` decimal(5,2) DEFAULT NULL COMMENT 'Distance in KM',
  `coupon_code` varchar(50) DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `rating` tinyint(1) DEFAULT NULL COMMENT '1-5 star rating',
  `review` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT 0.00,
  `refund_reason` text DEFAULT NULL,
  `tracking_data` json DEFAULT NULL COMMENT 'Real-time tracking information',
  `metadata` json DEFAULT NULL COMMENT 'Additional order metadata',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `idx_orders_customer` (`customer_id`),
  KEY `idx_orders_restaurant` (`restaurant_id`),
  KEY `idx_orders_rider` (`rider_id`),
  KEY `idx_orders_affiliate` (`affiliate_id`),
  KEY `idx_orders_status_date` (`status`, `created_at`),
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items (individual items within an order)
CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `menu_item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(8,2) NOT NULL COMMENT 'Price per unit in XAF',
  `total_price` decimal(10,2) NOT NULL COMMENT 'Total for this line item',
  `variants` json DEFAULT NULL COMMENT 'Selected variants/customizations',
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_menu_item` (`menu_item_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order status history for detailed tracking
CREATE TABLE `order_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status_history_order` (`order_id`),
  KEY `idx_status_history_changed_by` (`changed_by`),
  CONSTRAINT `order_status_history_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deliveries table for rider management
CREATE TABLE `deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `rider_id` bigint(20) UNSIGNED NOT NULL,
  `pickup_address` json NOT NULL,
  `delivery_address` json NOT NULL,
  `pickup_time` timestamp NULL DEFAULT NULL,
  `delivery_time` timestamp NULL DEFAULT NULL,
  `estimated_duration` int(11) DEFAULT NULL COMMENT 'Estimated delivery time in minutes',
  `actual_duration` int(11) DEFAULT NULL COMMENT 'Actual delivery time in minutes',
  `distance` decimal(5,2) DEFAULT NULL COMMENT 'Distance in KM',
  `delivery_fee` decimal(6,2) NOT NULL COMMENT 'Delivery fee in XAF',
  `rider_earnings` decimal(6,2) NOT NULL COMMENT 'Rider earnings in XAF',
  `platform_commission` decimal(6,2) NOT NULL COMMENT 'Platform commission in XAF',
  `status` enum('assigned','accepted','picked_up','on_the_way','delivered','cancelled') NOT NULL DEFAULT 'assigned',
  `cancellation_reason` text DEFAULT NULL,
  `delivery_proof` varchar(255) DEFAULT NULL COMMENT 'Photo proof of delivery',
  `customer_signature` text DEFAULT NULL COMMENT 'Digital signature',
  `rating` tinyint(1) DEFAULT NULL COMMENT '1-5 star rating from customer',
  `review` text DEFAULT NULL,
  `rider_notes` text DEFAULT NULL,
  `tracking_data` json DEFAULT NULL COMMENT 'Real-time GPS tracking data',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deliveries_order_id_unique` (`order_id`),
  KEY `idx_deliveries_rider` (`rider_id`),
  KEY `idx_deliveries_status_date` (`status`, `created_at`),
  CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliveries_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rider schedules and availability
CREATE TABLE `rider_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rider_id` bigint(20) UNSIGNED NOT NULL,
  `day_of_week` tinyint(1) NOT NULL COMMENT '0=Sunday, 1=Monday, etc.',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` boolean DEFAULT TRUE,
  `max_orders` int(11) DEFAULT 5 COMMENT 'Max concurrent orders',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rider_schedules_rider_day` (`rider_id`, `day_of_week`),
  CONSTRAINT `rider_schedules_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Real-time rider locations
CREATE TABLE `rider_locations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rider_id` bigint(20) UNSIGNED NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `accuracy` decimal(5,2) DEFAULT NULL COMMENT 'GPS accuracy in meters',
  `speed` decimal(5,2) DEFAULT NULL COMMENT 'Speed in km/h',
  `heading` decimal(5,2) DEFAULT NULL COMMENT 'Direction in degrees',
  `is_online` boolean DEFAULT TRUE,
  `battery_level` tinyint(3) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rider_locations_rider_time` (`rider_id`, `created_at`),
  CONSTRAINT `rider_locations_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Affiliate system
CREATE TABLE `affiliates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `affiliate_code` varchar(20) NOT NULL UNIQUE,
  `commission_rate` decimal(5,4) DEFAULT 0.0500 COMMENT 'Commission rate (5%)',
  `total_referrals` int(11) DEFAULT 0,
  `total_earnings` decimal(12,2) DEFAULT 0.00,
  `pending_earnings` decimal(10,2) DEFAULT 0.00,
  `paid_earnings` decimal(12,2) DEFAULT 0.00,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `payment_details` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliates_user_id_unique` (`user_id`),
  UNIQUE KEY `affiliates_affiliate_code_unique` (`affiliate_code`),
  CONSTRAINT `affiliates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Affiliate referrals tracking
CREATE TABLE `affiliate_referrals` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` bigint(20) UNSIGNED NOT NULL,
  `referred_user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `commission_amount` decimal(8,2) DEFAULT 0.00,
  `status` enum('pending','confirmed','paid','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_affiliate_referrals_affiliate` (`affiliate_id`),
  KEY `idx_affiliate_referrals_referred_user` (`referred_user_id`),
  KEY `idx_affiliate_referrals_order` (`order_id`),
  CONSTRAINT `affiliate_referrals_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affiliate_referrals_referred_user_id_foreign` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affiliate_referrals_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Affiliate payouts
CREATE TABLE `affiliate_payouts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) NOT NULL COMMENT 'bank_transfer, mobile_money, etc.',
  `reference` varchar(100) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_affiliate_payouts_affiliate` (`affiliate_id`),
  KEY `idx_affiliate_payouts_status_date` (`status`, `created_at`),
  CONSTRAINT `affiliate_payouts_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment methods for users
CREATE TABLE `payment_methods` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('card','mobile_money','bank_account','wallet') NOT NULL,
  `provider` varchar(50) NOT NULL COMMENT 'stripe, paypal, orange_money, mtn_money',
  `name` varchar(100) NOT NULL COMMENT 'User-friendly name',
  `details` json NOT NULL COMMENT 'Encrypted payment details',
  `is_default` boolean DEFAULT FALSE,
  `is_verified` boolean DEFAULT FALSE,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payment_methods_user` (`user_id`),
  KEY `idx_payment_methods_type_provider` (`type`, `provider`),
  CONSTRAINT `payment_methods_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table for transaction tracking
CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaction_id` varchar(100) NOT NULL UNIQUE,
  `gateway` varchar(50) NOT NULL COMMENT 'stripe, paypal, tranzak, etc.',
  `gateway_transaction_id` varchar(100) DEFAULT NULL,
  `type` enum('payment','refund','payout','fee') NOT NULL DEFAULT 'payment',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'XAF',
  `status` enum('pending','processing','completed','failed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `gateway_response` json DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  KEY `idx_payments_order` (`order_id`),
  KEY `idx_payments_user` (`user_id`),
  KEY `idx_payments_status_date` (`status`, `created_at`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews and ratings system
CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewable_type` varchar(50) NOT NULL COMMENT 'restaurant, menu_item, delivery',
  `reviewable_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT '1-5 stars',
  `title` varchar(200) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `images` json DEFAULT NULL COMMENT 'Review images',
  `is_verified` boolean DEFAULT FALSE COMMENT 'Verified purchase',
  `is_featured` boolean DEFAULT FALSE,
  `is_approved` boolean DEFAULT TRUE,
  `status` enum('pending','approved','rejected','hidden') DEFAULT 'approved',
  `helpful_count` int(11) DEFAULT 0,
  `unhelpful_count` int(11) DEFAULT 0,
  `response` text DEFAULT NULL COMMENT 'Business response',
  `responded_at` timestamp NULL DEFAULT NULL,
  `responded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_notes` text DEFAULT NULL COMMENT 'Admin moderation notes',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `hidden_by` bigint(20) UNSIGNED DEFAULT NULL,
  `hidden_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reviews_user` (`user_id`),
  KEY `idx_reviews_order` (`order_id`),
  KEY `idx_reviews_reviewable` (`reviewable_type`, `reviewable_id`),
  KEY `idx_reviews_rating_status` (`rating`, `status`),
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_responded_by_foreign` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_hidden_by_foreign` FOREIGN KEY (`hidden_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review helpful votes table
CREATE TABLE `review_helpful` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_review_user` (`review_id`, `user_id`),
  KEY `idx_review_id` (`review_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `review_helpful_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_helpful_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review reports table
CREATE TABLE `review_reports` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_review_id` (`review_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `review_reports_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review helpfulness votes
CREATE TABLE `review_votes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `vote` enum('helpful','unhelpful') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `review_votes_review_user_unique` (`review_id`, `user_id`),
  KEY `idx_review_votes_review` (`review_id`),
  KEY `idx_review_votes_user` (`user_id`),
  CONSTRAINT `review_votes_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin popup notifications
CREATE TABLE `popup_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','promotion') DEFAULT 'info',
  `target_audience` enum('all','customers','vendors','riders','new_users') DEFAULT 'all',
  `target_user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Specific user target',
  `is_active` boolean DEFAULT TRUE,
  `priority` tinyint(1) DEFAULT 1 COMMENT '1=low, 5=high',
  `display_count` int(11) DEFAULT 0,
  `max_displays` int(11) DEFAULT NULL COMMENT 'Max times to show',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `action_text` varchar(100) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_popup_notifications_active_dates` (`is_active`, `start_date`, `end_date`),
  KEY `idx_popup_notifications_target` (`target_audience`, `target_user_id`),
  CONSTRAINT `popup_notifications_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `popup_notifications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rider assignments for order delivery tracking
CREATE TABLE `rider_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `rider_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('assigned','accepted','picked_up','delivered','cancelled') NOT NULL DEFAULT 'assigned',
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `picked_up_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
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

-- Messages system for communication (updated for customer messaging)
CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `conversation_id` varchar(100) DEFAULT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `message_type` enum('text','image','file','location','system') NOT NULL DEFAULT 'text',
  `type` enum('general','order','support','system') DEFAULT 'general',
  `is_read` boolean DEFAULT FALSE,
  `read_at` timestamp NULL DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_conversation_id` (`conversation_id`),
  KEY `idx_messages_sender` (`sender_id`),
  KEY `idx_messages_recipient` (`recipient_id`),
  KEY `idx_messages_order` (`order_id`),
  KEY `idx_messages_read_status` (`is_read`, `created_at`),
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wishlists for user favorites
CREATE TABLE `wishlists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `menu_item_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_user_menu_item_unique` (`user_id`, `menu_item_id`),
  KEY `idx_wishlists_user` (`user_id`),
  KEY `idx_wishlists_menu_item` (`menu_item_id`),
  CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shopping cart items
CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `menu_item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Price per unit in XAF',
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total for this line item',
  `customizations` json DEFAULT NULL COMMENT 'Selected customizations/variants',
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cart_items_user` (`user_id`),
  KEY `idx_cart_items_menu_item` (`menu_item_id`),
  CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Disputes and complaints
CREATE TABLE `disputes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `initiator_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('order_issue','payment_issue','delivery_issue','quality_issue','other') NOT NULL,
  `subject` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `evidence` json DEFAULT NULL COMMENT 'Photos, documents, etc.',
  `status` enum('open','investigating','resolved','closed','escalated') DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `resolution` text DEFAULT NULL,
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `compensation_amount` decimal(8,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_disputes_order` (`order_id`),
  KEY `idx_disputes_initiator` (`initiator_id`),
  KEY `idx_disputes_status_priority` (`status`, `priority`),
  CONSTRAINT `disputes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disputes_initiator_id_foreign` FOREIGN KEY (`initiator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disputes_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site settings for admin configuration
CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL UNIQUE,
  `value` longtext DEFAULT NULL,
  `type` enum('string','integer','boolean','json','text','float') DEFAULT 'string',
  `group` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_public` boolean DEFAULT FALSE COMMENT 'Can be accessed by frontend',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_settings_key_unique` (`key`),
  KEY `idx_site_settings_group` (`group`),
  KEY `idx_site_settings_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System logs for debugging and monitoring
CREATE TABLE `logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `level` enum('emergency','alert','critical','error','warning','notice','info','debug') NOT NULL,
  `message` text NOT NULL,
  `context` json DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_level_date` (`level`, `created_at`),
  KEY `idx_logs_user` (`user_id`),
  CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupons and discount codes
CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('percentage','fixed_amount','free_delivery') NOT NULL,
  `value` decimal(8,2) NOT NULL COMMENT 'Percentage or fixed amount',
  `minimum_order` decimal(8,2) DEFAULT 0.00,
  `maximum_discount` decimal(8,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Total usage limit',
  `usage_limit_per_user` int(11) DEFAULT 1,
  `used_count` int(11) DEFAULT 0,
  `is_active` boolean DEFAULT TRUE,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `applicable_to` enum('all','restaurants','menu_items','categories') DEFAULT 'all',
  `applicable_ids` json DEFAULT NULL COMMENT 'Specific restaurant/item/category IDs',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  KEY `idx_coupons_active_dates` (`is_active`, `starts_at`, `expires_at`),
  CONSTRAINT `coupons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupon usage tracking
CREATE TABLE `coupon_usages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `discount_amount` decimal(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_coupon_usages_coupon` (`coupon_id`),
  KEY `idx_coupon_usages_user` (`user_id`),
  KEY `idx_coupon_usages_order` (`order_id`),
  CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics data for reporting
CREATE TABLE `analytics` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `data` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_analytics_event` (`event_type`, `event_name`),
  KEY `idx_analytics_user_date` (`user_id`, `created_at`),
  KEY `idx_analytics_session` (`session_id`),
  CONSTRAINT `analytics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily aggregated statistics for performance
CREATE TABLE `daily_stats` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `restaurant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `total_orders` int(11) DEFAULT 0,
  `total_revenue` decimal(12,2) DEFAULT 0.00,
  `total_commission` decimal(10,2) DEFAULT 0.00,
  `total_deliveries` int(11) DEFAULT 0,
  `average_order_value` decimal(8,2) DEFAULT 0.00,
  `average_delivery_time` int(11) DEFAULT 0 COMMENT 'Average delivery time in minutes',
  `customer_satisfaction` decimal(3,2) DEFAULT 0.00 COMMENT 'Average rating',
  `new_customers` int(11) DEFAULT 0,
  `returning_customers` int(11) DEFAULT 0,
  `cancelled_orders` int(11) DEFAULT 0,
  `refunded_orders` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_stats_date_restaurant_user_unique` (`date`, `restaurant_id`, `user_id`),
  KEY `idx_daily_stats_date` (`date`),
  KEY `idx_daily_stats_restaurant` (`restaurant_id`),
  KEY `idx_daily_stats_user` (`user_id`),
  CONSTRAINT `daily_stats_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_stats_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================================

-- Additional composite indexes for common queries
CREATE INDEX idx_orders_customer_status_date ON orders (customer_id, status, created_at);
CREATE INDEX idx_orders_restaurant_status_date ON orders (restaurant_id, status, created_at);
CREATE INDEX idx_orders_rider_status_date ON orders (rider_id, status, created_at);
CREATE INDEX idx_menu_items_restaurant_available_sort ON menu_items (restaurant_id, is_available, sort_order);
CREATE INDEX idx_reviews_reviewable_rating ON reviews (reviewable_type, reviewable_id, rating, status);
CREATE INDEX idx_payments_user_status_date ON payments (user_id, status, created_at);
CREATE INDEX idx_deliveries_rider_status_date ON deliveries (rider_id, status, created_at);
CREATE INDEX idx_affiliate_referrals_affiliate_status ON affiliate_referrals (affiliate_id, status, created_at);

-- Full-text search indexes
ALTER TABLE restaurants ADD FULLTEXT(name, description, cuisine_type);
ALTER TABLE menu_items ADD FULLTEXT(name, description, ingredients);
ALTER TABLE categories ADD FULLTEXT(name, description);

-- ============================================================================
-- AUTO-INCREMENT STARTING VALUES
-- ============================================================================

ALTER TABLE users AUTO_INCREMENT = 1000;
ALTER TABLE restaurants AUTO_INCREMENT = 100;
ALTER TABLE menu_items AUTO_INCREMENT = 1000;
ALTER TABLE orders AUTO_INCREMENT = 10000;
ALTER TABLE categories AUTO_INCREMENT = 10;

-- ============================================================================
-- DATABASE TRIGGERS FOR AUTOMATIC CALCULATIONS
-- ============================================================================

DELIMITER $$

-- Trigger to update restaurant ratings when reviews are added/updated
CREATE TRIGGER update_restaurant_rating_after_review_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    IF NEW.reviewable_type = 'restaurant' THEN
        UPDATE restaurants
        SET rating = (
            SELECT AVG(rating)
            FROM reviews
            WHERE reviewable_type = 'restaurant'
            AND reviewable_id = NEW.reviewable_id
            AND status = 'approved'
        ),
        total_reviews = (
            SELECT COUNT(*)
            FROM reviews
            WHERE reviewable_type = 'restaurant'
            AND reviewable_id = NEW.reviewable_id
            AND status = 'approved'
        )
        WHERE id = NEW.reviewable_id;
    END IF;

    IF NEW.reviewable_type = 'menu_item' THEN
        UPDATE menu_items
        SET rating = (
            SELECT AVG(rating)
            FROM reviews
            WHERE reviewable_type = 'menu_item'
            AND reviewable_id = NEW.reviewable_id
            AND status = 'approved'
        ),
        total_reviews = (
            SELECT COUNT(*)
            FROM reviews
            WHERE reviewable_type = 'menu_item'
            AND reviewable_id = NEW.reviewable_id
            AND status = 'approved'
        )
        WHERE id = NEW.reviewable_id;
    END IF;
END$$

-- Trigger to update order totals when order items are modified
CREATE TRIGGER update_order_total_after_item_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE delivery_fee_amount DECIMAL(6,2) DEFAULT 0;
    
    -- Get the delivery fee for this order
    SELECT COALESCE(delivery_fee, 0) INTO delivery_fee_amount 
    FROM orders WHERE id = NEW.order_id;
    
    -- Update the order totals
    UPDATE orders
    SET subtotal = (
        SELECT SUM(total_price)
        FROM order_items
        WHERE order_id = NEW.order_id
    ),
    total_amount = (
        SELECT SUM(total_price)
        FROM order_items
        WHERE order_id = NEW.order_id
    ) + delivery_fee_amount
    WHERE id = NEW.order_id;
END$$

-- Trigger to update restaurant order count when order is completed
CREATE TRIGGER update_restaurant_stats_after_order_complete
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'delivered' AND OLD.status != 'delivered' THEN
        UPDATE restaurants
        SET total_orders = total_orders + 1
        WHERE id = NEW.restaurant_id;

        UPDATE menu_items
        SET total_orders = total_orders + (
            SELECT SUM(quantity)
            FROM order_items
            WHERE order_id = NEW.id AND menu_item_id = menu_items.id
        )
        WHERE id IN (
            SELECT menu_item_id
            FROM order_items
            WHERE order_id = NEW.id
        );
    END IF;
END$$

-- Trigger to update affiliate earnings when order is completed
CREATE TRIGGER update_affiliate_earnings_after_order_complete
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'delivered' AND OLD.status != 'delivered' AND NEW.affiliate_id IS NOT NULL THEN
        UPDATE affiliates
        SET total_earnings = total_earnings + NEW.affiliate_commission,
            pending_earnings = pending_earnings + NEW.affiliate_commission
        WHERE user_id = NEW.affiliate_id;

        INSERT INTO affiliate_referrals (affiliate_id, referred_user_id, order_id, commission_amount, status)
        SELECT id, NEW.customer_id, NEW.id, NEW.affiliate_commission, 'confirmed'
        FROM affiliates
        WHERE user_id = NEW.affiliate_id;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- ============================================================================

DELIMITER $$

-- Procedure to calculate daily statistics
CREATE PROCEDURE CalculateDailyStats(IN target_date DATE)
BEGIN
    -- Insert or update daily stats for restaurants
    INSERT INTO daily_stats (
        date, restaurant_id, total_orders, total_revenue, total_commission,
        average_order_value, cancelled_orders, refunded_orders
    )
    SELECT
        target_date,
        restaurant_id,
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        SUM(total_amount * 0.15) as total_commission,
        AVG(total_amount) as average_order_value,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) as refunded_orders
    FROM orders
    WHERE DATE(created_at) = target_date
    GROUP BY restaurant_id
    ON DUPLICATE KEY UPDATE
        total_orders = VALUES(total_orders),
        total_revenue = VALUES(total_revenue),
        total_commission = VALUES(total_commission),
        average_order_value = VALUES(average_order_value),
        cancelled_orders = VALUES(cancelled_orders),
        refunded_orders = VALUES(refunded_orders);
END$$

-- Procedure to clean old data
CREATE PROCEDURE CleanOldData()
BEGIN
    -- Clean old analytics data (older than 1 year)
    DELETE FROM analytics WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

    -- Clean old logs (older than 3 months)
    DELETE FROM logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 MONTH);

    -- Clean old rider locations (older than 1 week)
    DELETE FROM rider_locations WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 WEEK);

    -- Archive old orders (older than 2 years) - mark as archived instead of deleting
    UPDATE orders SET metadata = JSON_SET(COALESCE(metadata, '{}'), '$.archived', true)
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)
    AND JSON_EXTRACT(metadata, '$.archived') IS NULL;
END$$

DELIMITER ;

-- ============================================================================
-- INITIAL DATA SETUP
-- ============================================================================

-- Insert default site settings (ignore duplicates)
INSERT IGNORE INTO `site_settings` (`key`, `value`, `type`, `group`, `description`, `is_public`) VALUES
-- General Settings
('site_name', 'Time2Eat', 'string', 'general', 'Site name', TRUE),
('site_description', 'Bamenda Food Delivery Platform', 'text', 'general', 'Site description', TRUE),
('timezone', 'Africa/Douala', 'string', 'general', 'Site timezone', TRUE),

-- Contact Settings
('contact_email', 'info@time2eat.cm', 'string', 'contact', 'Main contact email', TRUE),
('contact_phone', '+237 6XX XXX XXX', 'string', 'contact', 'Main contact phone', TRUE),
('contact_address', 'Bamenda, North West Region, Cameroon', 'text', 'contact', 'Business address', TRUE),
('contact_hours', 'Mon-Sat: 8AM-10PM, Sun: 10AM-8PM', 'string', 'contact', 'Business hours', TRUE),
('support_email', 'support@time2eat.cm', 'string', 'contact', 'Support email address', TRUE),
('emergency_contact', '+237 6XX XXX XXX', 'string', 'contact', 'Emergency contact number', TRUE),

-- Social Media Settings
('facebook_url', '', 'string', 'social', 'Facebook page URL', TRUE),
('twitter_url', '', 'string', 'social', 'Twitter profile URL', TRUE),
('instagram_url', '', 'string', 'social', 'Instagram profile URL', TRUE),
('youtube_url', '', 'string', 'social', 'YouTube channel URL', TRUE),
('linkedin_url', '', 'string', 'social', 'LinkedIn profile URL', TRUE),
('tiktok_url', '', 'string', 'social', 'TikTok profile URL', TRUE),
('whatsapp_number', '', 'string', 'social', 'WhatsApp business number', TRUE),

-- Business Settings
('delivery_fee', '500', 'integer', 'business', 'Standard delivery fee in XAF', TRUE),
('free_delivery_threshold', '5000', 'integer', 'business', 'Free delivery threshold in XAF', TRUE),
('commission_rate', '0.15', 'float', 'business', 'Platform commission rate (15%)', TRUE),
('tax_rate', '0.1925', 'float', 'business', 'Tax rate (19.25%)', TRUE),
('currency', 'XAF', 'string', 'business', 'Primary currency', TRUE),
('max_delivery_distance', '15', 'integer', 'business', 'Maximum delivery distance in KM', TRUE),

-- SEO Settings
('meta_keywords', 'food delivery, Bamenda, restaurants, online ordering', 'text', 'seo', 'Meta keywords for SEO', TRUE),
('google_analytics_id', '', 'string', 'seo', 'Google Analytics tracking ID', FALSE),
('facebook_pixel_id', '', 'string', 'seo', 'Facebook Pixel ID', FALSE),

-- Authentication Settings
('allow_registration', 'true', 'boolean', 'auth', 'Allow new user registration', TRUE),
('email_verification_required', 'true', 'boolean', 'auth', 'Require email verification', TRUE),

-- System Settings
('maintenance_mode', 'false', 'boolean', 'system', 'Enable maintenance mode', TRUE),

-- Map and Location Settings
('map_provider', 'leaflet', 'string', 'maps', 'Map provider to use: "google" for Google Maps or "leaflet" for OpenStreetMap. Changing this will instantly switch all maps across the application.', TRUE),
('google_maps_api_key', '', 'string', 'maps', 'Google Maps API key. Required if map_provider is set to "google". Get your API key from Google Cloud Console.', FALSE),
('mapbox_access_token', '', 'string', 'maps', 'Mapbox access token (optional). Can be used for additional map features.', FALSE),
('default_latitude', '5.9631', 'string', 'maps', 'Default map center latitude (Bamenda, Cameroon: 5.9631)', TRUE),
('default_longitude', '10.1591', 'string', 'maps', 'Default map center longitude (Bamenda, Cameroon: 10.1591)', TRUE),
('default_zoom_level', '13', 'integer', 'maps', 'Default map zoom level (1-20, recommended: 13 for city view)', TRUE),
('enable_location_tracking', 'true', 'boolean', 'maps', 'Enable real-time GPS location tracking for riders and customers', TRUE),

-- Payment Gateway Settings
('payment_methods_enabled', '["cash","mobile_money","orange_money","mtn_momo"]', 'json', 'payment', 'Enabled payment methods', TRUE),
('cash_on_delivery_enabled', 'true', 'boolean', 'payment', 'Enable cash on delivery', TRUE),
('online_payment_enabled', 'true', 'boolean', 'payment', 'Enable online payments', TRUE),
('stripe_publishable_key', '', 'string', 'payment', 'Stripe Publishable Key', FALSE),
('stripe_secret_key', '', 'string', 'payment', 'Stripe Secret Key', FALSE),
('paypal_client_id', '', 'string', 'payment', 'PayPal Client ID', FALSE),
('paypal_client_secret', '', 'string', 'payment', 'PayPal Client Secret', FALSE),
('paypal_sandbox_mode', 'true', 'boolean', 'payment', 'PayPal Sandbox Mode', TRUE),
('orange_money_merchant_id', '', 'string', 'payment', 'Orange Money Merchant ID', FALSE),
('orange_money_api_key', '', 'string', 'payment', 'Orange Money API Key', FALSE),
('mtn_momo_api_key', '', 'string', 'payment', 'MTN Mobile Money API Key', FALSE),
('mtn_momo_user_id', '', 'string', 'payment', 'MTN Mobile Money User ID', FALSE),
('payment_processing_fee', '0.025', 'float', 'payment', 'Payment processing fee percentage (2.5%)', TRUE),
('minimum_order_amount', '1000', 'integer', 'payment', 'Minimum order amount in XAF', TRUE),

-- Currency and Pricing Settings
('primary_currency', 'XAF', 'string', 'currency', 'Primary currency code', TRUE),
('currency_symbol', 'FCFA', 'string', 'currency', 'Currency symbol', TRUE),
('currency_position', 'after', 'string', 'currency', 'Currency symbol position (before/after)', TRUE),
('decimal_places', '0', 'integer', 'currency', 'Number of decimal places for currency', TRUE),
('thousand_separator', ',', 'string', 'currency', 'Thousand separator', TRUE),
('decimal_separator', '.', 'string', 'currency', 'Decimal separator', TRUE),
('exchange_rate_usd', '600', 'float', 'currency', 'Exchange rate to USD (1 USD = X XAF)', TRUE),
('auto_update_exchange_rates', 'false', 'boolean', 'currency', 'Automatically update exchange rates', TRUE),

-- PWA and Mobile Settings
('pwa_enabled', 'true', 'boolean', 'system', 'Enable Progressive Web App features', TRUE),
('app_name', 'Time2Eat - Food Delivery', 'string', 'system', 'PWA application name', TRUE),
('app_short_name', 'Time2Eat', 'string', 'system', 'PWA short name', TRUE),
('app_description', 'Order delicious food from your favorite restaurants in Bamenda', 'text', 'system', 'PWA application description', TRUE),
('theme_color', '#3B82F6', 'string', 'system', 'PWA theme color', TRUE),
('background_color', '#FFFFFF', 'string', 'system', 'PWA background color', TRUE),
('push_notifications_enabled', 'true', 'boolean', 'system', 'Enable push notifications', TRUE),

-- Additional Payment Settings
('cod_enabled', 'true', 'boolean', 'payment', 'Enable Cash on Delivery (alias for cash_on_delivery_enabled)', TRUE),

-- Tranzak Payment Settings
('tranzak_enabled', 'true', 'boolean', 'payment', 'Enable Tranzak payment gateway', TRUE),
('tranzak_app_id', 'aps1rr28n2qxbs', 'string', 'payment', 'Tranzak App ID / Merchant ID', FALSE),
('tranzak_api_key', 'PROD_EB888C479CE947CAA05CC14918BB9F08', 'string', 'payment', 'Tranzak Production API Key', FALSE),
('tranzak_sandbox_api_key', 'SAND_100DA717BD5844F39B03AB73AC7DA448', 'string', 'payment', 'Tranzak Sandbox API Key', FALSE),
('tranzak_api_secret', '', 'string', 'payment', 'Tranzak API Secret', FALSE),
('tranzak_sandbox_mode', 'false', 'boolean', 'payment', 'Use Tranzak sandbox environment', FALSE),

-- Withdrawal Settings
('min_withdrawal_affiliate', '5000', 'integer', 'withdrawal', 'Minimum withdrawal amount for affiliates (XAF)', FALSE),
('min_withdrawal_rider', '2000', 'integer', 'withdrawal', 'Minimum withdrawal amount for riders (XAF)', FALSE),
('min_withdrawal_restaurant', '10000', 'integer', 'withdrawal', 'Minimum withdrawal amount for restaurants (XAF)', FALSE),
('withdrawal_processing_fee', '0', 'decimal', 'withdrawal', 'Processing fee for withdrawals (XAF)', FALSE),
('withdrawal_auto_approve_limit', '50000', 'integer', 'withdrawal', 'Auto-approve withdrawals below this amount (XAF)', FALSE),
('withdrawal_processing_time', '24', 'integer', 'withdrawal', 'Standard withdrawal processing time in hours', TRUE),
('withdrawal_notification_enabled', 'true', 'boolean', 'withdrawal', 'Enable withdrawal status notifications', TRUE);

-- ============================================================================
-- NOTIFICATIONS TABLE
-- ============================================================================

-- General Notifications system
CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'NULL for system-wide notifications',
  `type` varchar(50) NOT NULL COMMENT 'order_update, promotion, system_alert, etc.',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL COMMENT 'Additional notification data',
  `channels` json DEFAULT NULL COMMENT 'email, sms, push, in_app',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `status` enum('pending','sent','delivered','failed','read') DEFAULT 'pending',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL COMMENT 'order, restaurant, user, etc.',
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_scheduled_at` (`scheduled_at`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_related` (`related_type`, `related_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Promo codes for discounts and promotions
CREATE TABLE `promo_codes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(8,2) NOT NULL,
  `max_discount` decimal(8,2) DEFAULT NULL,
  `minimum_order` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `usage_limit_per_user` int(11) DEFAULT NULL,
  `is_active` boolean NOT NULL DEFAULT 1,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_promo_codes_code` (`code`),
  KEY `idx_promo_codes_active` (`is_active`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- UNIFIED ORDER SYSTEM MIGRATION
-- ============================================================================

-- Add missing columns to existing orders table (if they don't already exist)
-- Note: MySQL doesn't support IF NOT EXISTS with ADD COLUMN, so these may fail if columns already exist
-- estimated_delivery_time and delivery_distance already exist in the table definition, so they're omitted
ALTER TABLE orders
ADD COLUMN estimated_preparation_time DATETIME NULL AFTER actual_delivery_time,
ADD COLUMN admin_notes TEXT NULL AFTER estimated_preparation_time,
ADD COLUMN updated_by INT NULL AFTER admin_notes;

-- Add indexes (MySQL doesn't support IF NOT EXISTS with ADD INDEX either)
-- Note: These may fail if indexes already exist, but won't cause installation to fail if caught properly
ALTER TABLE orders ADD INDEX idx_orders_status (status);
ALTER TABLE orders ADD INDEX idx_orders_updated_at (updated_at);
ALTER TABLE orders ADD INDEX idx_orders_customer_status (customer_id, status);
ALTER TABLE orders ADD INDEX idx_orders_restaurant_status (restaurant_id, status);
ALTER TABLE orders ADD INDEX idx_orders_rider_status (rider_id, status);

-- Create order status history table for audit trail
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    old_status VARCHAR(50) NOT NULL,
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_status_history_order (order_id),
    INDEX idx_order_status_history_changed_by (changed_by),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create restaurant commissions table
CREATE TABLE IF NOT EXISTS restaurant_commissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    commission_rate DECIMAL(5,4) NOT NULL DEFAULT 0.1000,
    commission_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'disputed') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_restaurant_commissions_order (order_id),
    INDEX idx_restaurant_commissions_restaurant (restaurant_id),
    INDEX idx_restaurant_commissions_status (status),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- Create rider earnings table
CREATE TABLE IF NOT EXISTS rider_earnings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    rider_id INT NOT NULL,
    base_fee DECIMAL(8,2) NOT NULL,
    distance_fee DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    bonus DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    total_earnings DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'disputed') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rider_earnings_order (order_id),
    INDEX idx_rider_earnings_rider (rider_id),
    INDEX idx_rider_earnings_status (status),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================================
-- WITHDRAWAL SYSTEM TABLES
-- ============================================================================

-- Withdrawals table
CREATE TABLE `withdrawals` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `withdrawal_type` enum('affiliate','rider','restaurant') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `account_details` json DEFAULT NULL,
  `status` enum('pending','approved','rejected','processing') NOT NULL DEFAULT 'pending',
  `withdrawal_reference` varchar(50) NOT NULL UNIQUE,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_withdrawal_type` (`withdrawal_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_withdrawal_reference` (`withdrawal_reference`),
  CONSTRAINT `withdrawals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `withdrawals_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User balances table
CREATE TABLE `user_balances` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `balance_type` enum('affiliate','rider','restaurant') NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_balance` (`user_id`, `balance_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_balance_type` (`balance_type`),
  CONSTRAINT `user_balances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Withdrawal logs table
CREATE TABLE `withdrawal_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `withdrawal_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_withdrawal_id` (`withdrawal_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `withdrawal_logs_withdrawal_id_foreign` FOREIGN KEY (`withdrawal_id`) REFERENCES `withdrawals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Earnings table
CREATE TABLE `earnings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `earning_type` enum('affiliate','rider','restaurant') NOT NULL,
  `source_type` enum('order','commission','bonus','refund') NOT NULL,
  `source_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
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

-- ============================================================================
-- AFFILIATE SYSTEM SYNCHRONIZATION
-- Ensure affiliate codes are properly synchronized between users and affiliates tables
-- ============================================================================

-- Create missing affiliate records for users who have affiliate_code but no affiliates record
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

-- Fix affiliate code mismatches (affiliates table is source of truth)
UPDATE users u
INNER JOIN affiliates a ON u.id = a.user_id
SET u.affiliate_code = a.affiliate_code,
    u.updated_at = NOW()
WHERE u.affiliate_code IS NOT NULL 
  AND u.affiliate_code != a.affiliate_code
  AND u.role = 'customer'
  AND u.deleted_at IS NULL;

-- ============================================================================
-- CART SYSTEM TABLES
-- Shopping cart and promotional code system
-- ============================================================================

-- Create cart_items table
CREATE TABLE IF NOT EXISTS `cart_items` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `menu_item_id` bigint(20) UNSIGNED NOT NULL,
    `quantity` int(11) NOT NULL DEFAULT 1,
    `unit_price` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Price per unit in XAF',
    `total_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total for this line item',
    `customizations` json DEFAULT NULL COMMENT 'Selected customizations/variants',
    `special_instructions` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cart_items_user` (`user_id`),
    KEY `idx_cart_items_menu_item` (`menu_item_id`),
    CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `cart_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create promo_codes table
CREATE TABLE IF NOT EXISTS `promo_codes` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` varchar(20) NOT NULL UNIQUE,
    `description` text DEFAULT NULL,
    `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
    `discount_value` decimal(8,2) NOT NULL,
    `max_discount` decimal(8,2) DEFAULT NULL,
    `minimum_order` decimal(10,2) DEFAULT NULL,
    `usage_limit` int(11) DEFAULT NULL,
    `usage_count` int(11) NOT NULL DEFAULT 0,
    `usage_limit_per_user` int(11) DEFAULT NULL,
    `is_active` boolean NOT NULL DEFAULT 1,
    `starts_at` timestamp NULL DEFAULT NULL,
    `expires_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_promo_codes_code` (`code`),
    KEY `idx_promo_codes_active` (`is_active`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample promo codes
INSERT IGNORE INTO `promo_codes` 
(code, description, discount_type, discount_value, max_discount, minimum_order, usage_limit, usage_count, usage_limit_per_user, is_active, starts_at, expires_at)
VALUES 
('WELCOME10', 'Welcome discount', 'percentage', 10.00, 1000.00, 5000.00, 100, 0, 1, 1, NULL, NULL),
('SAVE20', 'Save 20%', 'percentage', 20.00, 2000.00, 10000.00, 50, 0, 1, 1, NULL, NULL),
('FIXED500', 'Fixed discount', 'fixed', 500.00, NULL, 2000.00, 25, 0, 1, 1, NULL, NULL);

-- Set foreign key checks back on
SET FOREIGN_KEY_CHECKS = 1;

-- Commit the transaction
COMMIT;
