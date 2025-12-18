-- ============================================================================
-- SEARCH, REVIEWS, AND CANCELLATIONS ENHANCEMENT MIGRATION
-- Migration: 016_create_search_reviews_cancellations_tables.sql
-- Description: Additional tables for enhanced search, reviews, and cancellations
-- ============================================================================

-- Review helpful tracking table
CREATE TABLE `review_helpful` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `review_helpful_unique` (`review_id`, `user_id`),
  KEY `review_helpful_review_id_index` (`review_id`),
  KEY `review_helpful_user_id_index` (`user_id`),
  CONSTRAINT `review_helpful_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_helpful_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review reports table
CREATE TABLE `review_reports` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `review_reports_unique` (`review_id`, `user_id`),
  KEY `review_reports_review_id_index` (`review_id`),
  KEY `review_reports_user_id_index` (`user_id`),
  KEY `review_reports_status_index` (`status`),
  CONSTRAINT `review_reports_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_reports_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order cancellations table
CREATE TABLE `order_cancellations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'User who requested cancellation',
  `user_type` enum('customer','vendor','admin','rider') NOT NULL DEFAULT 'customer',
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Admin/Vendor who reviewed',
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_status` enum('pending','processing','completed','failed') DEFAULT NULL,
  `refund_reference` varchar(255) DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_cancellations_order_id_unique` (`order_id`),
  KEY `order_cancellations_user_id_index` (`user_id`),
  KEY `order_cancellations_status_index` (`status`),
  KEY `order_cancellations_user_type_index` (`user_type`),
  KEY `order_cancellations_reviewed_by_index` (`reviewed_by`),
  CONSTRAINT `order_cancellations_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_cancellations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_cancellations_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Search analytics table for tracking popular searches
CREATE TABLE `search_analytics` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `query` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `results_count` int(11) DEFAULT 0,
  `clicked_result_type` enum('restaurant','menu_item','category') DEFAULT NULL,
  `clicked_result_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `search_analytics_query_index` (`query`),
  KEY `search_analytics_user_id_index` (`user_id`),
  KEY `search_analytics_created_at_index` (`created_at`),
  CONSTRAINT `search_analytics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User search preferences table
CREATE TABLE `user_search_preferences` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `preferred_cuisines` json DEFAULT NULL,
  `preferred_price_range` json DEFAULT NULL,
  `dietary_restrictions` json DEFAULT NULL,
  `default_sort` varchar(50) DEFAULT 'relevance',
  `max_delivery_fee` decimal(8,2) DEFAULT NULL,
  `max_delivery_time` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_search_preferences_user_id_unique` (`user_id`),
  CONSTRAINT `user_search_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review drafts table for saving incomplete reviews
CREATE TABLE `review_drafts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `restaurant_rating` tinyint(1) DEFAULT NULL,
  `restaurant_comment` text DEFAULT NULL,
  `rider_rating` tinyint(1) DEFAULT NULL,
  `rider_comment` text DEFAULT NULL,
  `item_reviews` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `review_drafts_user_order_unique` (`user_id`, `order_id`),
  KEY `review_drafts_order_id_index` (`order_id`),
  CONSTRAINT `review_drafts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_drafts_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add additional columns to existing tables if they don't exist

-- Add search-related columns to restaurants table
ALTER TABLE `restaurants` 
ADD COLUMN IF NOT EXISTS `search_keywords` text DEFAULT NULL COMMENT 'Additional keywords for search',
ADD COLUMN IF NOT EXISTS `search_priority` int(11) DEFAULT 0 COMMENT 'Search ranking priority',
ADD INDEX IF NOT EXISTS `restaurants_search_priority_index` (`search_priority`);

-- Add search-related columns to menu_items table
ALTER TABLE `menu_items` 
ADD COLUMN IF NOT EXISTS `search_keywords` text DEFAULT NULL COMMENT 'Additional keywords for search',
ADD COLUMN IF NOT EXISTS `search_priority` int(11) DEFAULT 0 COMMENT 'Search ranking priority',
ADD INDEX IF NOT EXISTS `menu_items_search_priority_index` (`search_priority`);

-- Add review-related columns to orders table if they don't exist
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `is_reviewed` tinyint(1) DEFAULT 0 COMMENT 'Whether order has been reviewed',
ADD COLUMN IF NOT EXISTS `review_reminder_sent` tinyint(1) DEFAULT 0 COMMENT 'Whether review reminder was sent',
ADD COLUMN IF NOT EXISTS `cancelled_at` timestamp NULL DEFAULT NULL COMMENT 'When order was cancelled',
ADD COLUMN IF NOT EXISTS `cancellation_reason` varchar(255) DEFAULT NULL COMMENT 'Reason for cancellation',
ADD INDEX IF NOT EXISTS `orders_is_reviewed_index` (`is_reviewed`),
ADD INDEX IF NOT EXISTS `orders_cancelled_at_index` (`cancelled_at`);

-- Add approval columns to reviews table if they don't exist
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `is_approved` tinyint(1) DEFAULT 1 COMMENT 'Whether review is approved',
ADD COLUMN IF NOT EXISTS `approved_at` timestamp NULL DEFAULT NULL COMMENT 'When review was approved',
ADD COLUMN IF NOT EXISTS `rejected_at` timestamp NULL DEFAULT NULL COMMENT 'When review was rejected',
ADD COLUMN IF NOT EXISTS `rejection_reason` text DEFAULT NULL COMMENT 'Reason for rejection',
ADD INDEX IF NOT EXISTS `reviews_is_approved_index` (`is_approved`);

-- Create full-text search indexes for better search performance
ALTER TABLE `restaurants` ADD FULLTEXT(`name`, `description`, `cuisine_type`, `address`);
ALTER TABLE `menu_items` ADD FULLTEXT(`name`, `description`, `ingredients`);
ALTER TABLE `categories` ADD FULLTEXT(`name`, `description`);

-- Insert default search preferences for existing users
INSERT IGNORE INTO `user_search_preferences` (`user_id`, `default_sort`, `created_at`)
SELECT `id`, 'relevance', NOW() FROM `users` WHERE `role` = 'customer';

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `reviews_rating_created_index` ON `reviews` (`rating`, `created_at`);
CREATE INDEX IF NOT EXISTS `reviews_reviewable_rating_index` ON `reviews` (`reviewable_type`, `reviewable_id`, `rating`);
CREATE INDEX IF NOT EXISTS `orders_status_created_index` ON `orders` (`status`, `created_at`);
CREATE INDEX IF NOT EXISTS `restaurants_active_approved_index` ON `restaurants` (`is_active`, `is_approved`);
CREATE INDEX IF NOT EXISTS `menu_items_available_index` ON `menu_items` (`is_available`);

-- Add triggers for maintaining search analytics
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS `update_restaurant_search_priority` 
AFTER INSERT ON `reviews` 
FOR EACH ROW 
BEGIN
    IF NEW.reviewable_type = 'restaurant' THEN
        UPDATE `restaurants` 
        SET `search_priority` = (
            SELECT ROUND(AVG(rating) * 10) + COUNT(*) 
            FROM `reviews` 
            WHERE `reviewable_type` = 'restaurant' 
            AND `reviewable_id` = NEW.reviewable_id 
            AND `is_approved` = 1
        )
        WHERE `id` = NEW.reviewable_id;
    END IF;
END$$

CREATE TRIGGER IF NOT EXISTS `update_menu_item_search_priority` 
AFTER INSERT ON `reviews` 
FOR EACH ROW 
BEGIN
    IF NEW.reviewable_type = 'menu_item' THEN
        UPDATE `menu_items` 
        SET `search_priority` = (
            SELECT ROUND(AVG(rating) * 10) + COUNT(*) 
            FROM `reviews` 
            WHERE `reviewable_type` = 'menu_item' 
            AND `reviewable_id` = NEW.reviewable_id 
            AND `is_approved` = 1
        )
        WHERE `id` = NEW.reviewable_id;
    END IF;
END$$

DELIMITER ;

-- Insert sample search keywords for better search results
UPDATE `restaurants` SET `search_keywords` = CONCAT(
    LOWER(name), ',',
    LOWER(cuisine_type), ',',
    LOWER(REPLACE(address, ' ', ',')), ',',
    'food,restaurant,delivery,bamenda'
) WHERE `search_keywords` IS NULL;

UPDATE `menu_items` SET `search_keywords` = CONCAT(
    LOWER(name), ',',
    LOWER(IFNULL(ingredients, '')), ',',
    'food,dish,meal,order'
) WHERE `search_keywords` IS NULL;

-- Create view for search statistics
CREATE OR REPLACE VIEW `search_statistics` AS
SELECT 
    DATE(created_at) as search_date,
    COUNT(*) as total_searches,
    COUNT(DISTINCT user_id) as unique_users,
    COUNT(CASE WHEN clicked_result_id IS NOT NULL THEN 1 END) as successful_searches,
    AVG(results_count) as avg_results_count
FROM `search_analytics`
GROUP BY DATE(created_at)
ORDER BY search_date DESC;

-- Create view for popular search terms
CREATE OR REPLACE VIEW `popular_searches` AS
SELECT 
    query,
    COUNT(*) as search_count,
    COUNT(DISTINCT user_id) as unique_users,
    COUNT(CASE WHEN clicked_result_id IS NOT NULL THEN 1 END) as click_count,
    ROUND((COUNT(CASE WHEN clicked_result_id IS NOT NULL THEN 1 END) / COUNT(*)) * 100, 2) as click_rate
FROM `search_analytics`
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY query
HAVING search_count >= 3
ORDER BY search_count DESC, click_rate DESC
LIMIT 50;

-- Create view for review statistics
CREATE OR REPLACE VIEW `review_statistics` AS
SELECT 
    reviewable_type,
    reviewable_id,
    COUNT(*) as total_reviews,
    AVG(rating) as average_rating,
    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star_count,
    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star_count,
    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star_count,
    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star_count,
    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star_count,
    COUNT(CASE WHEN is_verified = 1 THEN 1 END) as verified_reviews
FROM `reviews`
WHERE `is_approved` = 1
GROUP BY reviewable_type, reviewable_id;

-- Migration completion marker
INSERT INTO `migrations` (`migration`, `batch`) VALUES 
('016_create_search_reviews_cancellations_tables', 16);
