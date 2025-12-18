<?php

declare(strict_types=1);

/**
 * Application Configuration
 * Main configuration file for Time2Eat application
 */

return [
    
    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes.
    |
    */
    
    'environment' => $_ENV['APP_ENV'] ?? (function() {
        // Auto-detect environment based on hostname
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            $developmentHosts = ['localhost', '127.0.0.1', '::1'];
            foreach ($developmentHosts as $devHost) {
                if (strpos($host, $devHost) !== false) {
                    return 'development';
                }
            }
        }
        return 'production';
    })(),
    
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */
    
    'name' => 'Time2Eat',
    'description' => 'Bamenda Food Delivery Platform',
    'version' => '1.0.0',
    
    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */
    
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/eat',
    
    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */
    
    'timezone' => 'Africa/Douala',
    
    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */
    
    'locale' => 'en',
    'fallback_locale' => 'en',
    'supported_locales' => ['en', 'fr'],
    
    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the application for encryption and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */
    
    'key' => $_ENV['APP_KEY'] ?? 'base64:' . base64_encode(random_bytes(32)),
    
    /*
    |--------------------------------------------------------------------------
    | JWT Secret Key
    |--------------------------------------------------------------------------
    |
    | This key is used for JWT token signing and verification. It should be
    | a strong, random string that is kept secret.
    |
    */
    
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? bin2hex(random_bytes(32)),
    
    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */
    
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Various security settings for the application
    |
    */
    
    'security' => [
        'require_email_verification' => false,
        'password_reset_expires' => 3600, // 1 hour
        'session_lifetime' => 7200, // 2 hours
        'remember_me_lifetime' => 2592000, // 30 days
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'csrf_protection' => true,
        'force_https' => $_ENV['FORCE_HTTPS'] ?? false,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for file uploads including size limits and allowed types
    |
    */
    
    'uploads' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'txt'],
        'upload_path' => '/storage/uploads',
        'image_quality' => 85,
        'generate_thumbnails' => true,
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [800, 600]
        ]
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */
    
    'cache' => [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => '/storage/cache',
                'default_ttl' => 3600
            ],
            'redis' => [
                'driver' => 'redis',
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => $_ENV['REDIS_PASSWORD'] ?? null,
                'database' => $_ENV['REDIS_DB'] ?? 0
            ]
        ]
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the session settings for your application
    |
    */
    
    'session' => [
        'driver' => 'file',
        'lifetime' => 7200, // 2 hours
        'expire_on_close' => false,
        'encrypt' => false,
        'files' => '/storage/sessions',
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => 'time2eat_session',
        'path' => '/',
        'domain' => null,
        'secure' => $_ENV['SESSION_SECURE_COOKIE'] ?? false,
        'http_only' => true,
        'same_site' => 'lax',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application
    |
    */
    
    'logging' => [
        'default' => 'file',
        'channels' => [
            'file' => [
                'driver' => 'file',
                'path' => '/storage/logs/app.log',
                'level' => 'debug',
                'max_files' => 30
            ],
            'error' => [
                'driver' => 'file',
                'path' => '/storage/logs/error.log',
                'level' => 'error'
            ],
            'security' => [
                'driver' => 'file',
                'path' => '/storage/logs/security.log',
                'level' => 'warning'
            ]
        ]
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Mail Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for sending emails
    |
    */
    
    'mail' => [
        'default' => 'smtp',
        'mailers' => [
            'smtp' => [
                'transport' => 'smtp',
                'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
                'port' => $_ENV['MAIL_PORT'] ?? 587,
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
                'username' => $_ENV['MAIL_USERNAME'] ?? null,
                'password' => $_ENV['MAIL_PASSWORD'] ?? null,
                'timeout' => null,
            ],
        ],
        'from' => [
            'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@time2eat.cm',
            'name' => $_ENV['MAIL_FROM_NAME'] ?? 'Time2Eat',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for sending SMS notifications
    |
    */
    
    'sms' => [
        'default' => 'twilio',
        'providers' => [
            'twilio' => [
                'sid' => $_ENV['TWILIO_SID'] ?? null,
                'token' => $_ENV['TWILIO_TOKEN'] ?? null,
                'from' => $_ENV['TWILIO_FROM'] ?? null,
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for payment gateways
    |
    */
    
    'payments' => [
        'default_currency' => 'XAF',
        'gateways' => [
            'stripe' => [
                'public_key' => $_ENV['STRIPE_PUBLIC_KEY'] ?? null,
                'secret_key' => $_ENV['STRIPE_SECRET_KEY'] ?? null,
                'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null,
            ],
            'paypal' => [
                'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? null,
                'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET'] ?? null,
                'sandbox' => $_ENV['PAYPAL_SANDBOX'] ?? true,
            ],
            'orange_money' => [
                'merchant_key' => $_ENV['ORANGE_MONEY_MERCHANT_KEY'] ?? null,
                'api_url' => $_ENV['ORANGE_MONEY_API_URL'] ?? null,
            ],
            'mtn_momo' => [
                'api_key' => $_ENV['MTN_MOMO_API_KEY'] ?? null,
                'api_secret' => $_ENV['MTN_MOMO_API_SECRET'] ?? null,
                'sandbox' => $_ENV['MTN_MOMO_SANDBOX'] ?? true,
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Map Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for map and location services
    |
    */
    
    'maps' => [
        'default' => 'google',
        'providers' => [
            'google' => [
                'api_key' => $_ENV['GOOGLE_MAPS_API_KEY'] ?? 'AIzaSyA0C5etf1GVmL_ldVAichWwFFVcDfa1y_c',
                'places_api_key' => $_ENV['GOOGLE_PLACES_API_KEY'] ?? 'AIzaSyA0C5etf1GVmL_ldVAichWwFFVcDfa1y_c',
            ],
            'openstreetmap' => [
                'tile_server' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                'attribution' => '© OpenStreetMap contributors',
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for real-time WebSocket connections
    |
    */
    
    'websocket' => [
        'host' => $_ENV['WEBSOCKET_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['WEBSOCKET_PORT'] ?? 8080,
        'ssl' => $_ENV['WEBSOCKET_SSL'] ?? false,
        'allowed_origins' => ['*'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Business Configuration
    |--------------------------------------------------------------------------
    |
    | Business-specific settings for the food delivery platform
    |
    */
    
    'business' => [
        'delivery_fee' => 500, // XAF
        'free_delivery_threshold' => 5000, // XAF
        'max_delivery_distance' => 15, // kilometers
        'order_timeout' => 1800, // 30 minutes
        'commission_rate' => 0.15, // 15%
        'tax_rate' => 0, // Tax removed
        'supported_cities' => ['Bamenda', 'Douala', 'Yaoundé'],
        'operating_hours' => [
            'start' => '06:00',
            'end' => '23:00',
        ],
    ],
    
];
