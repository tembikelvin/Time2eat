-- ============================================================================
-- Production Migration: Make restaurant coordinates nullable
-- ============================================================================
-- This migration makes latitude and longitude fields nullable in the restaurants table
-- for existing production databases. It's safe to run multiple times.

-- Check if latitude column is already nullable
SET @latitude_nullable = (
    SELECT IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'restaurants' 
    AND COLUMN_NAME = 'latitude'
);

-- Check if longitude column is already nullable
SET @longitude_nullable = (
    SELECT IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'restaurants' 
    AND COLUMN_NAME = 'longitude'
);

-- Make latitude nullable if it's not already
SET @sql_latitude = IF(@latitude_nullable = 'NO', 
    'ALTER TABLE `restaurants` MODIFY COLUMN `latitude` decimal(10,8) NULL DEFAULT NULL',
    'SELECT "Latitude column is already nullable" as message'
);

PREPARE stmt_latitude FROM @sql_latitude;
EXECUTE stmt_latitude;
DEALLOCATE PREPARE stmt_latitude;

-- Make longitude nullable if it's not already
SET @sql_longitude = IF(@longitude_nullable = 'NO', 
    'ALTER TABLE `restaurants` MODIFY COLUMN `longitude` decimal(11,8) NULL DEFAULT NULL',
    'SELECT "Longitude column is already nullable" as message'
);

PREPARE stmt_longitude FROM @sql_longitude;
EXECUTE stmt_longitude;
DEALLOCATE PREPARE stmt_longitude;

-- Update existing restaurants with NULL coordinates to have default Bamenda coordinates
-- This is safe to run multiple times as it only updates NULL values
UPDATE `restaurants` 
SET `latitude` = 5.9631, `longitude` = 10.1591 
WHERE `latitude` IS NULL OR `longitude` IS NULL;

-- Log the migration completion
INSERT INTO `logs` (`level`, `message`, `context`, `created_at`) 
VALUES ('info', 'Restaurant coordinates made nullable successfully', 
        JSON_OBJECT(
            'migration', '021_fix_restaurant_coordinates_nullable_production',
            'restaurants_updated', (SELECT COUNT(*) FROM restaurants WHERE latitude IS NOT NULL AND longitude IS NOT NULL),
            'latitude_nullable', @latitude_nullable,
            'longitude_nullable', @longitude_nullable,
            'timestamp', NOW()
        ), 
        NOW());

-- ============================================================================
-- Production Migration Complete
-- ============================================================================
-- This migration is safe to run multiple times and will only make changes
-- if the columns are not already nullable.
-- ============================================================================
