-- ============================================================================
-- Migration: Make restaurant coordinates nullable
-- ============================================================================
-- This migration makes latitude and longitude fields nullable in the restaurants table
-- to prevent errors when creating restaurants without coordinates.

-- Make latitude and longitude nullable
ALTER TABLE `restaurants` 
MODIFY COLUMN `latitude` decimal(10,8) NULL DEFAULT NULL,
MODIFY COLUMN `longitude` decimal(11,8) NULL DEFAULT NULL;

-- Update existing restaurants with NULL coordinates to have default Bamenda coordinates
UPDATE `restaurants` 
SET `latitude` = 5.9631, `longitude` = 10.1591 
WHERE `latitude` IS NULL OR `longitude` IS NULL;

-- Log the migration
INSERT INTO `logs` (`level`, `message`, `context`, `created_at`) 
VALUES ('info', 'Restaurant coordinates made nullable successfully', 
        JSON_OBJECT('migration', '021_fix_restaurant_coordinates_nullable', 'restaurants_updated', 
                   (SELECT COUNT(*) FROM restaurants WHERE latitude IS NOT NULL AND longitude IS NOT NULL)), 
        NOW());

-- ============================================================================
-- Migration Complete
-- ============================================================================
