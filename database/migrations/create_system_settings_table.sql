-- Create system_settings table for storing application configuration
CREATE TABLE IF NOT EXISTS system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default map settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('map_provider', 'google', 'string', 'Map provider: google or leaflet'),
('google_maps_api_key', '', 'string', 'Google Maps API Key'),
('default_map_latitude', '5.9631', 'string', 'Default map center latitude (Bamenda)'),
('default_map_longitude', '10.1591', 'string', 'Default map center longitude (Bamenda)')
ON DUPLICATE KEY UPDATE updated_at = NOW();

