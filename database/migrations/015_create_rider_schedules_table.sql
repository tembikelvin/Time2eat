-- ============================================================================
-- RIDER SCHEDULES TABLE MIGRATION
-- Creates table for managing rider availability schedules and working hours
-- ============================================================================

-- Create rider_schedules table
CREATE TABLE IF NOT EXISTS rider_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rider_id INT NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    max_orders INT DEFAULT 5 COMMENT 'Maximum concurrent orders for this time slot',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes for performance
    INDEX idx_rider_schedules_rider_id (rider_id),
    INDEX idx_rider_schedules_day_time (day_of_week, start_time, end_time),
    INDEX idx_rider_schedules_availability (is_available),
    
    -- Unique constraint to prevent duplicate schedules for same rider/day/time
    UNIQUE KEY unique_rider_day_time (rider_id, day_of_week, start_time, end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create rider_locations table (if not exists)
CREATE TABLE IF NOT EXISTS rider_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rider_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    accuracy FLOAT NULL COMMENT 'GPS accuracy in meters',
    speed FLOAT NULL COMMENT 'Speed in m/s',
    heading FLOAT NULL COMMENT 'Direction in degrees',
    battery_level INT NULL COMMENT 'Battery percentage',
    is_online BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes for performance
    INDEX idx_rider_locations_rider_id (rider_id),
    INDEX idx_rider_locations_online (is_online),
    INDEX idx_rider_locations_created (created_at),
    INDEX idx_rider_locations_coords (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add rider_earnings column to deliveries table if not exists
ALTER TABLE deliveries 
ADD COLUMN IF NOT EXISTS rider_earnings DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Rider earnings for this delivery';

-- Add distance_km column to deliveries table if not exists
ALTER TABLE deliveries 
ADD COLUMN IF NOT EXISTS distance_km DECIMAL(8, 2) DEFAULT 0.00 COMMENT 'Total delivery distance in kilometers';

-- Create indexes on deliveries table for rider queries
ALTER TABLE deliveries 
ADD INDEX IF NOT EXISTS idx_deliveries_rider_status (rider_id, status),
ADD INDEX IF NOT EXISTS idx_deliveries_status_created (status, created_at),
ADD INDEX IF NOT EXISTS idx_deliveries_rider_earnings (rider_id, rider_earnings);

-- Insert default schedules for existing riders
INSERT IGNORE INTO rider_schedules (rider_id, day_of_week, start_time, end_time, is_available, max_orders)
SELECT 
    u.id as rider_id,
    days.day_of_week,
    CASE 
        WHEN days.day_of_week = 0 THEN '10:00:00'  -- Sunday
        WHEN days.day_of_week = 6 THEN '09:00:00'  -- Saturday
        ELSE '08:00:00'                            -- Monday-Friday
    END as start_time,
    CASE 
        WHEN days.day_of_week = 0 THEN '18:00:00'  -- Sunday
        WHEN days.day_of_week = 6 THEN '21:00:00'  -- Saturday
        ELSE '20:00:00'                            -- Monday-Friday
    END as end_time,
    TRUE as is_available,
    5 as max_orders
FROM users u
CROSS JOIN (
    SELECT 0 as day_of_week UNION ALL
    SELECT 1 UNION ALL
    SELECT 2 UNION ALL
    SELECT 3 UNION ALL
    SELECT 4 UNION ALL
    SELECT 5 UNION ALL
    SELECT 6
) days
WHERE u.role = 'rider' AND u.status = 'active';

-- Create view for rider availability
CREATE OR REPLACE VIEW rider_availability AS
SELECT 
    rs.rider_id,
    u.first_name,
    u.last_name,
    u.phone,
    u.email,
    rs.day_of_week,
    CASE rs.day_of_week
        WHEN 0 THEN 'Sunday'
        WHEN 1 THEN 'Monday'
        WHEN 2 THEN 'Tuesday'
        WHEN 3 THEN 'Wednesday'
        WHEN 4 THEN 'Thursday'
        WHEN 5 THEN 'Friday'
        WHEN 6 THEN 'Saturday'
    END as day_name,
    rs.start_time,
    rs.end_time,
    rs.is_available,
    rs.max_orders,
    TIME_TO_SEC(TIMEDIFF(rs.end_time, rs.start_time)) / 3600 as hours_per_day,
    rl.latitude as current_latitude,
    rl.longitude as current_longitude,
    rl.is_online,
    rl.created_at as last_location_update
FROM rider_schedules rs
JOIN users u ON rs.rider_id = u.id
LEFT JOIN rider_locations rl ON u.id = rl.rider_id 
    AND rl.id = (
        SELECT id FROM rider_locations 
        WHERE rider_id = u.id 
        ORDER BY created_at DESC 
        LIMIT 1
    )
WHERE u.role = 'rider' AND u.status = 'active';

-- Create view for rider performance metrics
CREATE OR REPLACE VIEW rider_performance AS
SELECT 
    u.id as rider_id,
    u.first_name,
    u.last_name,
    u.phone,
    COUNT(d.id) as total_deliveries,
    COUNT(CASE WHEN d.status = 'delivered' THEN 1 END) as completed_deliveries,
    COUNT(CASE WHEN d.status = 'cancelled' THEN 1 END) as cancelled_deliveries,
    COALESCE(SUM(CASE WHEN d.status = 'delivered' THEN d.rider_earnings ELSE 0 END), 0) as total_earnings,
    COALESCE(AVG(CASE WHEN d.status = 'delivered' THEN d.rider_earnings END), 0) as avg_earnings_per_delivery,
    COALESCE(AVG(CASE WHEN d.status = 'delivered' THEN d.customer_rating END), 0) as avg_rating,
    COALESCE(SUM(d.distance_km), 0) as total_distance,
    COALESCE(AVG(CASE WHEN d.status = 'delivered' AND d.pickup_time IS NOT NULL AND d.delivery_time IS NOT NULL 
        THEN TIMESTAMPDIFF(MINUTE, d.pickup_time, d.delivery_time) END), 0) as avg_delivery_time_minutes,
    COUNT(CASE WHEN DATE(d.created_at) = CURDATE() THEN 1 END) as deliveries_today,
    COALESCE(SUM(CASE WHEN DATE(d.created_at) = CURDATE() AND d.status = 'delivered' THEN d.rider_earnings ELSE 0 END), 0) as earnings_today,
    COUNT(CASE WHEN d.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as deliveries_this_week,
    COALESCE(SUM(CASE WHEN d.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND d.status = 'delivered' THEN d.rider_earnings ELSE 0 END), 0) as earnings_this_week,
    COUNT(CASE WHEN MONTH(d.created_at) = MONTH(CURDATE()) AND YEAR(d.created_at) = YEAR(CURDATE()) THEN 1 END) as deliveries_this_month,
    COALESCE(SUM(CASE WHEN MONTH(d.created_at) = MONTH(CURDATE()) AND YEAR(d.created_at) = YEAR(CURDATE()) AND d.status = 'delivered' THEN d.rider_earnings ELSE 0 END), 0) as earnings_this_month
FROM users u
LEFT JOIN deliveries d ON u.id = d.rider_id
WHERE u.role = 'rider' AND u.status = 'active'
GROUP BY u.id, u.first_name, u.last_name, u.phone;

-- Create trigger to automatically calculate rider earnings when delivery is completed
DELIMITER //

CREATE TRIGGER IF NOT EXISTS calculate_rider_earnings
    BEFORE UPDATE ON deliveries
    FOR EACH ROW
BEGIN
    -- Calculate rider earnings when status changes to 'delivered'
    IF NEW.status = 'delivered' AND OLD.status != 'delivered' THEN
        -- Base earnings: 70% of delivery fee + distance bonus (50 XAF per km)
        SET NEW.rider_earnings = COALESCE(NEW.delivery_fee * 0.7, 0) + COALESCE(NEW.distance_km * 50, 0);
    END IF;
END//

DELIMITER ;

-- Create stored procedure for getting nearby available riders
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS GetNearbyAvailableRiders(
    IN p_latitude DECIMAL(10, 8),
    IN p_longitude DECIMAL(11, 8),
    IN p_radius_km DECIMAL(8, 2),
    IN p_limit INT
)
BEGIN
    SELECT 
        u.id as rider_id,
        u.first_name,
        u.last_name,
        u.phone,
        u.profile_image,
        rl.latitude,
        rl.longitude,
        rl.is_online,
        rl.battery_level,
        rs.max_orders,
        (6371 * acos(cos(radians(p_latitude)) * cos(radians(rl.latitude)) * 
         cos(radians(rl.longitude) - radians(p_longitude)) + 
         sin(radians(p_latitude)) * sin(radians(rl.latitude)))) AS distance_km,
        COUNT(ad.id) as active_deliveries
    FROM users u
    JOIN rider_schedules rs ON u.id = rs.rider_id
    JOIN rider_locations rl ON u.id = rl.rider_id
    LEFT JOIN deliveries ad ON u.id = ad.rider_id AND ad.status IN ('accepted', 'picked_up', 'on_the_way')
    WHERE u.role = 'rider' 
        AND u.status = 'active'
        AND rl.is_online = 1
        AND rs.day_of_week = DAYOFWEEK(NOW()) - 1
        AND rs.is_available = 1
        AND TIME(NOW()) BETWEEN rs.start_time AND rs.end_time
        AND rl.id = (
            SELECT id FROM rider_locations 
            WHERE rider_id = u.id 
            ORDER BY created_at DESC 
            LIMIT 1
        )
    GROUP BY u.id, u.first_name, u.last_name, u.phone, u.profile_image, 
             rl.latitude, rl.longitude, rl.is_online, rl.battery_level, rs.max_orders
    HAVING distance_km <= p_radius_km 
        AND active_deliveries < rs.max_orders
    ORDER BY distance_km ASC, active_deliveries ASC
    LIMIT p_limit;
END//

DELIMITER ;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_deliveries_rider_created ON deliveries(rider_id, created_at);
CREATE INDEX IF NOT EXISTS idx_deliveries_status_updated ON deliveries(status, updated_at);
CREATE INDEX IF NOT EXISTS idx_rider_locations_rider_created ON rider_locations(rider_id, created_at);

-- Add comments to tables
ALTER TABLE rider_schedules COMMENT = 'Stores rider availability schedules and working hours';
ALTER TABLE rider_locations COMMENT = 'Stores real-time rider location data for tracking and assignment';

-- Insert sample data for testing (optional - remove in production)
-- This creates a test schedule for rider with ID 1 if it exists
INSERT IGNORE INTO rider_schedules (rider_id, day_of_week, start_time, end_time, is_available, max_orders)
SELECT 1, 1, '08:00:00', '20:00:00', TRUE, 5
WHERE EXISTS (SELECT 1 FROM users WHERE id = 1 AND role = 'rider');

COMMIT;
