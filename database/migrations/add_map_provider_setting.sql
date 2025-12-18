-- Add map provider setting to site_settings table
-- This allows admins to switch between Google Maps and Leaflet (OpenStreetMap)

-- Insert map_provider setting if it doesn't exist
INSERT INTO site_settings (`key`, `value`, `type`, `group`, `description`, `created_at`, `updated_at`)
VALUES (
    'map_provider',
    'leaflet',
    'string',
    'maps',
    'Map provider to use: "google" for Google Maps or "leaflet" for OpenStreetMap. Changing this will instantly switch all maps across the application.',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `description` = 'Map provider to use: "google" for Google Maps or "leaflet" for OpenStreetMap. Changing this will instantly switch all maps across the application.',
    `updated_at` = NOW();

-- Update existing google_maps_api_key setting description
UPDATE site_settings
SET `description` = 'Google Maps API key. Required if map_provider is set to "google". Get your API key from Google Cloud Console.',
    `updated_at` = NOW()
WHERE `key` = 'google_maps_api_key';

-- Update existing mapbox_access_token setting description
UPDATE site_settings
SET `description` = 'Mapbox access token (optional). Can be used for additional map features.',
    `updated_at` = NOW()
WHERE `key` = 'mapbox_access_token';

-- Update default_latitude setting description
UPDATE site_settings
SET `description` = 'Default map center latitude (Bamenda, Cameroon: 5.9631)',
    `updated_at` = NOW()
WHERE `key` = 'default_latitude';

-- Update default_longitude setting description
UPDATE site_settings
SET `description` = 'Default map center longitude (Bamenda, Cameroon: 10.1591)',
    `updated_at` = NOW()
WHERE `key` = 'default_longitude';

-- Update default_zoom_level setting description
UPDATE site_settings
SET `description` = 'Default map zoom level (1-20, recommended: 13 for city view)',
    `updated_at` = NOW()
WHERE `key` = 'default_zoom_level';

-- Update enable_location_tracking setting description
UPDATE site_settings
SET `description` = 'Enable real-time GPS location tracking for riders and customers',
    `updated_at` = NOW()
WHERE `key` = 'enable_location_tracking';

-- Verify the settings
SELECT `key`, `value`, `type`, `group`, `description`
FROM site_settings
WHERE `group` = 'maps'
ORDER BY 
    CASE `key`
        WHEN 'map_provider' THEN 1
        WHEN 'google_maps_api_key' THEN 2
        WHEN 'mapbox_access_token' THEN 3
        WHEN 'default_latitude' THEN 4
        WHEN 'default_longitude' THEN 5
        WHEN 'default_zoom_level' THEN 6
        WHEN 'enable_location_tracking' THEN 7
        ELSE 99
    END;

