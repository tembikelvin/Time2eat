-- Add extra delivery fee column for distances beyond free delivery zone
-- This allows admin to set a per-km fee for deliveries beyond the free delivery radius

ALTER TABLE `restaurants` 
ADD COLUMN IF NOT EXISTS `delivery_fee_per_extra_km` DECIMAL(6,2) DEFAULT 100.00 
COMMENT 'Extra fee per km beyond delivery_radius (in XAF)' 
AFTER `delivery_fee`;

-- Update existing restaurants to have the default extra fee
UPDATE `restaurants` 
SET `delivery_fee_per_extra_km` = 100.00 
WHERE `delivery_fee_per_extra_km` IS NULL;

-- Add index for delivery-related queries
CREATE INDEX IF NOT EXISTS idx_restaurants_delivery_settings 
ON restaurants (delivery_radius, delivery_fee, delivery_fee_per_extra_km);

-- Add comments to clarify the delivery fee structure
ALTER TABLE `restaurants` 
MODIFY COLUMN `delivery_fee` DECIMAL(6,2) DEFAULT 500.00 
COMMENT 'Base delivery fee within delivery_radius (in XAF)';

ALTER TABLE `restaurants` 
MODIFY COLUMN `delivery_radius` DECIMAL(5,2) DEFAULT 10.00 
COMMENT 'Free delivery zone radius in KM - base fee applies within this distance';

