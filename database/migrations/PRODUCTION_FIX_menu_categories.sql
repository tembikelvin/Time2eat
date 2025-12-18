-- ============================================================================
-- PRODUCTION FIX: Add menu_categories table and missing fields
-- This migration fixes the issue where vendors cannot access Menu Items and Categories pages
-- Run this on your production database if you already have the old schema
-- ============================================================================

-- Create menu_categories table if it doesn't exist
CREATE TABLE IF NOT EXISTS `menu_categories` (
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

-- Add missing columns to menu_items table
ALTER TABLE `menu_items` 
ADD COLUMN IF NOT EXISTS `min_stock_level` int(11) DEFAULT 5 AFTER `low_stock_threshold`,
ADD COLUMN IF NOT EXISTS `is_vegetarian` tinyint(1) DEFAULT 0 AFTER `min_stock_level`,
ADD COLUMN IF NOT EXISTS `is_vegan` tinyint(1) DEFAULT 0 AFTER `is_vegetarian`,
ADD COLUMN IF NOT EXISTS `is_gluten_free` tinyint(1) DEFAULT 0 AFTER `is_vegan`,
ADD COLUMN IF NOT EXISTS `customization_options` json DEFAULT NULL AFTER `is_gluten_free`,
ADD COLUMN IF NOT EXISTS `image_url` varchar(255) DEFAULT NULL AFTER `customization_options`;

-- Add missing indexes to menu_items
ALTER TABLE `menu_items` 
ADD INDEX IF NOT EXISTS `idx_menu_items_stock_quantity` (`stock_quantity`),
ADD INDEX IF NOT EXISTS `idx_menu_items_deleted_at` (`deleted_at`),
ADD INDEX IF NOT EXISTS `idx_menu_items_restaurant_category` (`restaurant_id`, `category_id`),
ADD INDEX IF NOT EXISTS `idx_menu_items_restaurant_available` (`restaurant_id`, `is_available`),
ADD INDEX IF NOT EXISTS `idx_menu_items_category_available` (`category_id`, `is_available`);

-- Update the foreign key constraint if it exists and points to wrong table
-- Note: This section checks if the FK exists and drops it before recreating
SET @fk_exists = (SELECT COUNT(*) 
                  FROM information_schema.TABLE_CONSTRAINTS 
                  WHERE CONSTRAINT_NAME = 'menu_items_category_id_foreign' 
                  AND TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'menu_items');

SET @fk_sql = IF(@fk_exists > 0, 
                 'ALTER TABLE `menu_items` DROP FOREIGN KEY `menu_items_category_id_foreign`', 
                 'SELECT "Foreign key does not exist"');

PREPARE stmt FROM @fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Re-create the foreign key to point to menu_categories
ALTER TABLE `menu_items` 
ADD CONSTRAINT `menu_items_category_id_foreign` 
FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL;

-- Insert default categories for existing restaurants
INSERT IGNORE INTO `menu_categories` (restaurant_id, name, description, sort_order)
SELECT 
    r.id,
    'Main Dishes',
    'Primary dishes and entrees',
    1
FROM restaurants r
WHERE r.deleted_at IS NULL;

INSERT IGNORE INTO `menu_categories` (restaurant_id, name, description, sort_order)
SELECT 
    r.id,
    'Appetizers',
    'Starters and small plates',
    2
FROM restaurants r
WHERE r.deleted_at IS NULL;

INSERT IGNORE INTO `menu_categories` (restaurant_id, name, description, sort_order)
SELECT 
    r.id,
    'Beverages',
    'Drinks and refreshments',
    3
FROM restaurants r
WHERE r.deleted_at IS NULL;

INSERT IGNORE INTO `menu_categories` (restaurant_id, name, description, sort_order)
SELECT 
    r.id,
    'Desserts',
    'Sweet treats and desserts',
    4
FROM restaurants r
WHERE r.deleted_at IS NULL;

INSERT IGNORE INTO `menu_categories` (restaurant_id, name, description, sort_order)
SELECT 
    r.id,
    'Sides',
    'Side dishes and extras',
    5
FROM restaurants r
WHERE r.deleted_at IS NULL;

-- Migrate existing menu items to use Main Dishes category if they don't have one
UPDATE menu_items mi
JOIN restaurants r ON mi.restaurant_id = r.id
JOIN menu_categories mc ON mc.restaurant_id = r.id AND mc.name = 'Main Dishes'
SET mi.category_id = mc.id
WHERE mi.category_id IS NULL OR mi.category_id NOT IN (SELECT id FROM menu_categories);

-- Success message
SELECT 'Migration completed successfully! menu_categories table created and menu_items updated.' as Status;
