<?php
/**
 * Map Configuration
 * 
 * Configuration for map providers and API keys
 */

return [
    // Map provider: 'google' or 'openstreetmap'
    'provider' => $_ENV['MAP_PROVIDER'] ?? 'google',

    // Google Maps configuration
    'google' => [
        'api_key' => $_ENV['GOOGLE_MAPS_API_KEY'] ?? 'AIzaSyA0C5etf1GVmL_ldVAichWwFFVcDfa1y_c',
        'libraries' => ['geometry', 'places'],
        'region' => 'CM', // Cameroon
        'language' => 'en',
        'styles' => [
            // Custom map styles for better visibility
            [
                'featureType' => 'poi',
                'elementType' => 'labels',
                'stylers' => [['visibility' => 'off']]
            ],
            [
                'featureType' => 'transit',
                'elementType' => 'labels',
                'stylers' => [['visibility' => 'off']]
            ]
        ]
    ],
    
    // OpenStreetMap configuration
    'openstreetmap' => [
        'tile_server' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '© OpenStreetMap contributors',
        'max_zoom' => 19,
        'min_zoom' => 1
    ],
    
    // Default map settings
    'defaults' => [
        // Bamenda, Cameroon coordinates
        'center' => [
            'latitude' => 5.9631,
            'longitude' => 10.1591
        ],
        'zoom' => 13,
        'max_zoom' => 19,
        'min_zoom' => 10
    ],
    
    // Marker icons
    'markers' => [
        'restaurant' => [
            'url' => '/images/markers/restaurant-marker.png',
            'size' => [40, 40],
            'anchor' => [20, 40]
        ],
        'delivery' => [
            'url' => '/images/markers/home-marker.png',
            'size' => [40, 40],
            'anchor' => [20, 40]
        ],
        'rider' => [
            'url' => '/images/markers/rider-marker.png',
            'size' => [40, 40],
            'anchor' => [20, 40]
        ]
    ],
    
    // Delivery zones (in kilometers)
    'delivery_zones' => [
        'default_radius' => 10,
        'max_radius' => 25,
        'zones' => [
            'bamenda_center' => [
                'name' => 'Bamenda Center',
                'center' => [5.9631, 10.1591],
                'radius' => 5
            ],
            'bamenda_extended' => [
                'name' => 'Bamenda Extended',
                'center' => [5.9631, 10.1591],
                'radius' => 15
            ]
        ]
    ],
    
    // Real-time tracking settings
    'tracking' => [
        'update_interval' => 5, // seconds
        'location_accuracy' => 10, // meters
        'max_tracking_time' => 3600, // 1 hour in seconds
        'enable_route_optimization' => true
    ],
    
    // Geocoding settings
    'geocoding' => [
        'provider' => $_ENV['GEOCODING_PROVIDER'] ?? 'nominatim',
        'nominatim' => [
            'url' => 'https://nominatim.openstreetmap.org',
            'user_agent' => 'Time2Eat/1.0',
            'rate_limit' => 1 // requests per second
        ],
        'google' => [
            'api_key' => $_ENV['GOOGLE_GEOCODING_API_KEY'] ?? $_ENV['GOOGLE_MAPS_API_KEY'] ?? '',
            'region' => 'CM'
        ]
    ]
];
