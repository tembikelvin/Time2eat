-- ============================================================================
-- USER ADDRESSES TABLE
-- ============================================================================

-- Create user_addresses table for saved delivery addresses
CREATE TABLE IF NOT EXISTS `user_addresses` (
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
  CONSTRAINT `user_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX `idx_user_addresses_active` ON `user_addresses` (`user_id`, `deleted_at`);
CREATE INDEX `idx_user_addresses_location` ON `user_addresses` (`latitude`, `longitude`);

-- Insert sample data for testing (optional)
-- INSERT INTO `user_addresses` (`user_id`, `label`, `address_line_1`, `city`, `latitude`, `longitude`, `is_default`) VALUES
-- (1, 'Home', '123 Main Street', 'Douala', 4.0483, 9.7043, 1),
-- (1, 'Work', '456 Business District', 'Douala', 4.0500, 9.7100, 0);
