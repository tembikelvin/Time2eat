<?php

/**
 * VAPID Configuration for Push Notifications
 * 
 * IMPORTANT: In production, generate your own VAPID keys and store them securely!
 * 
 * To generate VAPID keys:
 * 1. Install web-push library: npm install web-push -g
 * 2. Run: web-push generate-vapid-keys
 * 3. Replace the keys below with your generated keys
 * 4. Store the private key securely (environment variables, encrypted config, etc.)
 */

return [
    // VAPID keys for push notifications (generated with web-push)
    'public_key' => 'BHjc8OwI15SDjS5aJI9M-jZ3vRDJKAFSy2-H0r8l4dY8z8vyPZop2SRyJMXsh8_pmx498dNvdMrLXGP6SFqHjE8',
    'private_key' => 'jJ9rqYACEOu0BozXe1tt2ZdCTRpGKN8bgoS2QCvmvY4', // Keep this secret!

    // VAPID subject (usually your app's contact email or URL)
    'subject' => 'mailto:admin@time2eat.com',

    // Environment-specific settings
    'environment' => [
        'development' => [
            'public_key' => 'BHjc8OwI15SDjS5aJI9M-jZ3vRDJKAFSy2-H0r8l4dY8z8vyPZop2SRyJMXsh8_pmx498dNvdMrLXGP6SFqHjE8',
            'private_key' => 'jJ9rqYACEOu0BozXe1tt2ZdCTRpGKN8bgoS2QCvmvY4',
            'subject' => 'mailto:dev@time2eat.com'
        ],
        'production' => [
            'public_key' => 'BHjc8OwI15SDjS5aJI9M-jZ3vRDJKAFSy2-H0r8l4dY8z8vyPZop2SRyJMXsh8_pmx498dNvdMrLXGP6SFqHjE8',
            'private_key' => 'jJ9rqYACEOu0BozXe1tt2ZdCTRpGKN8bgoS2QCvmvY4',
            'subject' => 'mailto:admin@time2eat.com'
        ]
    ]
];
