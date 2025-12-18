-- ============================================================================
-- Production Migration: Add category_id to restaurants table
-- ============================================================================
-- This migration adds the missing category_id column to the restaurants table
-- for existing production databases. It's safe to run multiple times.

-- Check if category_id column already exists before adding it
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'restaurants' 
    AND COLUMN_NAME = 'category_id'
);

-- Add category_id column if it doesn't exist
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `restaurants` 
     ADD COLUMN `category_id` bigint(20) UNSIGNED NULL DEFAULT NULL AFTER `cuisine_type`,
     ADD INDEX `idx_restaurants_category` (`category_id`),
     ADD CONSTRAINT `restaurants_category_id_foreign` 
         FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL',
    'SELECT "Column category_id already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert default categories if they don't exist
INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `description`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Other', 'other', 'Miscellaneous restaurants', 1, 0, 100, NOW(), NOW()),
(2, 'Cameroonian', 'cameroonian', 'Traditional Cameroonian cuisine', 1, 1, 1, NOW(), NOW()),
(3, 'Fast Food', 'fast-food', 'Quick service restaurants', 1, 1, 2, NOW(), NOW()),
(4, 'Pizza', 'pizza', 'Pizza and Italian cuisine', 1, 1, 3, NOW(), NOW()),
(5, 'Chinese', 'chinese', 'Chinese cuisine', 1, 0, 4, NOW(), NOW()),
(6, 'Indian', 'indian', 'Indian cuisine', 1, 0, 5, NOW(), NOW()),
(7, 'Continental', 'continental', 'Continental cuisine', 1, 0, 6, NOW(), NOW()),
(8, 'Bakery', 'bakery', 'Bakery and pastry shops', 1, 0, 7, NOW(), NOW()),
(9, 'Beverages', 'beverages', 'Drinks and beverages', 1, 0, 8, NOW(), NOW());

-- Update existing restaurants with category mappings based on cuisine_type
-- This is safe to run multiple times as it only updates NULL category_id values

-- Cameroonian restaurants
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Cameroonian'
SET r.category_id = c.id
WHERE r.cuisine_type LIKE '%cameroon%' 
   OR r.cuisine_type LIKE '%african%' 
   OR r.cuisine_type LIKE '%traditional%'
   AND r.category_id IS NULL;

-- Fast Food restaurants
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Fast Food'
SET r.category_id = c.id
WHERE (r.cuisine_type LIKE '%fast%' 
    OR r.cuisine_type LIKE '%quick%')
   AND r.category_id IS NULL;

-- Pizza restaurants
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Pizza'
SET r.category_id = c.id
WHERE (r.cuisine_type LIKE '%pizza%' 
    OR r.cuisine_type LIKE '%italian%')
   AND r.category_id IS NULL;

-- Chinese restaurants
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Chinese'
SET r.category_id = c.id
WHERE (r.cuisine_type LIKE '%chinese%' 
    OR r.cuisine_type LIKE '%asian%')
   AND r.category_id IS NULL;

-- Indian restaurants
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Indian'
SET r.category_id = c.id
WHERE (r.cuisine_type LIKE '%indian%' 
    OR r.cuisine_type LIKE '%curry%')
   AND r.category_id IS NULL;

-- Continental restaurants
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Continental'
SET r.category_id = c.id
WHERE (r.cuisine_type LIKE '%continental%' 
    OR r.cuisine_type LIKE '%european%')
   AND r.category_id IS NULL;

-- Bakery restaurants
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Bakery'
SET r.category_id = c.id
WHERE (r.cuisine_type LIKE '%bakery%' 
    OR r.cuisine_type LIKE '%pastry%' 
    OR r.cuisine_type LIKE '%bread%')
   AND r.category_id IS NULL;

-- Beverage restaurants
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Beverages'
SET r.category_id = c.id
WHERE (r.cuisine_type LIKE '%beverage%' 
    OR r.cuisine_type LIKE '%drink%' 
    OR r.cuisine_type LIKE '%bar%')
   AND r.category_id IS NULL;

-- Assign remaining restaurants to "Other" category
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Other'
SET r.category_id = c.id
WHERE r.category_id IS NULL;

-- Log the migration completion
INSERT INTO `logs` (`level`, `message`, `context`, `created_at`) 
VALUES ('info', 'Restaurant category_id migration completed', 
        JSON_OBJECT(
            'migration', '020_add_restaurant_category_id_production',
            'restaurants_updated', (SELECT COUNT(*) FROM restaurants WHERE category_id IS NOT NULL),
            'categories_created', (SELECT COUNT(*) FROM categories),
            'timestamp', NOW()
        ), 
        NOW());

-- ============================================================================
-- Production Migration Complete
-- ============================================================================
-- This migration is safe to run multiple times and will only make changes
-- if the category_id column doesn't already exist.
-- ============================================================================
