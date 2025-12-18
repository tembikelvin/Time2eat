-- Update Delivery Radius for All Restaurants
-- This extends the delivery radius to reach outskirts of the city
-- Maximum delivery distance = delivery_radius * 2

-- Update all restaurants to have a larger delivery radius (25 km)
-- This allows deliveries up to 50 km (25 km * 2)
UPDATE restaurants 
SET 
    delivery_radius = 25.0,
    delivery_fee = 1000,
    delivery_fee_per_extra_km = 150
WHERE status = 'active' AND deleted_at IS NULL;

-- Verify the update
SELECT 
    id,
    name,
    delivery_radius as 'Free Zone (km)',
    CONCAT(delivery_radius * 2, ' km') as 'Max Delivery Distance',
    delivery_fee as 'Base Fee (FCFA)',
    delivery_fee_per_extra_km as 'Extra Fee/km (FCFA)'
FROM restaurants 
WHERE status = 'active' AND deleted_at IS NULL;

