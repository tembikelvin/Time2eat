-- ============================================================================
-- Migration: Add category_id to restaurants table
-- ============================================================================
-- This migration adds the missing category_id column to the restaurants table
-- to properly link restaurants to categories for browsing and filtering.

-- Add category_id column to restaurants table
ALTER TABLE `restaurants` 
ADD COLUMN `category_id` bigint(20) UNSIGNED NULL DEFAULT NULL AFTER `cuisine_type`,
ADD INDEX `idx_restaurants_category` (`category_id`),
ADD CONSTRAINT `restaurants_category_id_foreign` 
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

-- Update existing restaurants to have default category based on cuisine_type
-- This is a temporary mapping - in production, you'd want to manually assign proper categories
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = r.cuisine_type
SET r.category_id = c.id
WHERE c.id IS NOT NULL;

-- For restaurants without matching categories, assign to a default "Other" category
-- First, ensure there's an "Other" category
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`)
VALUES ('Other', 'other', 'Miscellaneous restaurants', 1, NOW(), NOW());

-- Update remaining restaurants to use "Other" category
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Other'
SET r.category_id = c.id
WHERE r.category_id IS NULL;

-- Add some sample categories if they don't exist
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
('Cameroonian', 'cameroonian', 'Traditional Cameroonian cuisine', 1, NOW(), NOW()),
('Fast Food', 'fast-food', 'Quick service restaurants', 1, NOW(), NOW()),
('Pizza', 'pizza', 'Pizza and Italian cuisine', 1, NOW(), NOW()),
('Chinese', 'chinese', 'Chinese cuisine', 1, NOW(), NOW()),
('Indian', 'indian', 'Indian cuisine', 1, NOW(), NOW()),
('Continental', 'continental', 'Continental cuisine', 1, NOW(), NOW()),
('Bakery', 'bakery', 'Bakery and pastry shops', 1, NOW(), NOW()),
('Beverages', 'beverages', 'Drinks and beverages', 1, NOW(), NOW());

-- Update restaurants with more specific category mappings
UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Cameroonian'
SET r.category_id = c.id
WHERE r.cuisine_type LIKE '%cameroon%' OR r.cuisine_type LIKE '%african%' OR r.cuisine_type LIKE '%traditional%';

UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Fast Food'
SET r.category_id = c.id
WHERE r.cuisine_type LIKE '%fast%' OR r.cuisine_type LIKE '%quick%';

UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Pizza'
SET r.category_id = c.id
WHERE r.cuisine_type LIKE '%pizza%' OR r.cuisine_type LIKE '%italian%';

UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Chinese'
SET r.category_id = c.id
WHERE r.cuisine_type LIKE '%chinese%' OR r.cuisine_type LIKE '%asian%';

UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Indian'
SET r.category_id = c.id
WHERE r.cuisine_type LIKE '%indian%' OR r.cuisine_type LIKE '%curry%';

UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Continental'
SET r.category_id = c.id
WHERE r.cuisine_type LIKE '%continental%' OR r.cuisine_type LIKE '%european%';

UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Bakery'
SET r.category_id = c.id
WHERE r.cuisine_type LIKE '%bakery%' OR r.cuisine_type LIKE '%pastry%' OR r.cuisine_type LIKE '%bread%';

UPDATE `restaurants` r
LEFT JOIN `categories` c ON c.name = 'Beverages'
SET r.category_id = c.id
WHERE r.cuisine_type LIKE '%beverage%' OR r.cuisine_type LIKE '%drink%' OR r.cuisine_type LIKE '%bar%';

-- Log the migration
INSERT INTO `logs` (`level`, `message`, `context`, `created_at`) 
VALUES ('info', 'Restaurant category_id column added successfully', 
        JSON_OBJECT('migration', '020_add_restaurant_category_id', 'restaurants_updated', 
                   (SELECT COUNT(*) FROM restaurants WHERE category_id IS NOT NULL)), 
        NOW());

-- ============================================================================
-- Migration Complete
-- ============================================================================
