-- Set Google Maps as default map provider with API key
-- This migration updates the site_settings table to use Google Maps by default

-- Insert or update map_provider setting to use Google Maps
INSERT INTO site_settings (`key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`)
VALUES (
    'map_provider',
    'google',
    'string',
    'maps',
    'Map provider to use: "google" for Google Maps or "leaflet" for OpenStreetMap. Changing this will instantly switch all maps across the application.',
    FALSE,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `value` = 'google',
    `updated_at` = NOW();

-- Insert or update Google Maps API key
INSERT INTO site_settings (`key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`)
VALUES (
    'google_maps_api_key',
    'AIzaSyA0C5etf1GVmL_ldVAichWwFFVcDfa1y_c',
    'string',
    'maps',
    'Google Maps API Key for map services. Required when map_provider is set to "google".',
    FALSE,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `value` = 'AIzaSyA0C5etf1GVmL_ldVAichWwFFVcDfa1y_c',
    `updated_at` = NOW();

-- Ensure other map settings exist with defaults
INSERT INTO site_settings (`key`, `value`, `type`, `group`, `description`, `is_public`, `created_at`, `updated_at`)
VALUES
    ('default_latitude', '5.9631', 'string', 'maps', 'Default map center latitude (Bamenda, Cameroon)', FALSE, NOW(), NOW()),
    ('default_longitude', '10.1591', 'string', 'maps', 'Default map center longitude (Bamenda, Cameroon)', FALSE, NOW(), NOW()),
    ('default_zoom_level', '13', 'string', 'maps', 'Default map zoom level', FALSE, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `updated_at` = NOW();

