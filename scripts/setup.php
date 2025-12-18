<?php
/**
 * Time2Eat Complete Setup Script
 * Handles dependency installation, configuration, and environment setup
 */

echo "🚀 Time2Eat Complete Setup\n";
echo "==========================\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    echo "❌ PHP 8.0+ is required. Current version: " . PHP_VERSION . "\n";
    exit(1);
}
echo "✓ PHP version: " . PHP_VERSION . "\n";

// Check required extensions
$requiredExtensions = ['pdo', 'json', 'mbstring', 'curl', 'gd', 'zip', 'xml', 'simplexml', 'openssl'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "❌ Missing PHP extensions: " . implode(', ', $missingExtensions) . "\n";
    echo "Please install these extensions and try again.\n";
    exit(1);
}
echo "✓ All required PHP extensions are loaded\n";

// Check if Composer is available
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "⚠️  Composer dependencies not installed. Running composer install...\n";
    
    $composerCommand = 'composer install --no-dev --optimize-autoloader';
    if (getenv('APP_ENV') === 'development') {
        $composerCommand = 'composer install';
    }
    
    $output = [];
    $returnCode = 0;
    exec($composerCommand . ' 2>&1', $output, $returnCode);
    
    if ($returnCode !== 0) {
        echo "❌ Composer install failed:\n";
        echo implode("\n", $output) . "\n";
        exit(1);
    }
    echo "✓ Composer dependencies installed\n";
} else {
    echo "✓ Composer dependencies already installed\n";
}

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Check .env file
if (!file_exists(__DIR__ . '/../.env')) {
    if (file_exists(__DIR__ . '/../.env.example')) {
        copy(__DIR__ . '/../.env.example', __DIR__ . '/../.env');
        echo "✓ Created .env file from .env.example\n";
        echo "⚠️  Please update .env with your configuration before continuing\n";
    } else {
        echo "❌ .env.example file not found\n";
        exit(1);
    }
} else {
    echo "✓ .env file exists\n";
}

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Create required directories
$directories = [
    'logs',
    'storage',
    'storage/cache',
    'storage/uploads',
    'storage/exports',
    'storage/backups',
    'public/uploads',
    'public/uploads/restaurants',
    'public/uploads/menu-items',
    'public/uploads/users',
    'tests/Unit',
    'tests/Integration',
    'tests/Feature'
];

foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/../' . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "✓ Created directory: {$dir}\n";
    }
}

// Set proper permissions (Unix-like systems only)
if (PHP_OS_FAMILY !== 'Windows') {
    $permissionDirs = ['logs', 'storage', 'public/uploads'];
    foreach ($permissionDirs as $dir) {
        $fullPath = __DIR__ . '/../' . $dir;
        chmod($fullPath, 0755);
        echo "✓ Set permissions for: {$dir}\n";
    }
}

// Test database connection
echo "\n📊 Testing database connection...\n";
try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Database connection successful\n";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    if (!$stmt->fetch()) {
        echo "⚠️  Database '" . DB_NAME . "' does not exist\n";
        echo "Run 'composer install-db' to create the database\n";
    } else {
        echo "✓ Database '" . DB_NAME . "' exists\n";
    }
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in .env\n";
}

// Generate application key if not set
if (APP_KEY === 'default-secret-key') {
    $newKey = bin2hex(random_bytes(32));
    $envContent = file_get_contents(__DIR__ . '/../.env');
    $envContent = str_replace('APP_KEY=default-secret-key', 'APP_KEY=' . $newKey, $envContent);
    file_put_contents(__DIR__ . '/../.env', $envContent);
    echo "✓ Generated new APP_KEY\n";
}

// Generate JWT secret if not set
if (JWT_SECRET === 'default-jwt-secret') {
    $newSecret = bin2hex(random_bytes(32));
    $envContent = file_get_contents(__DIR__ . '/../.env');
    $envContent = str_replace('JWT_SECRET=default-jwt-secret', 'JWT_SECRET=' . $newSecret, $envContent);
    file_put_contents(__DIR__ . '/../.env', $envContent);
    echo "✓ Generated new JWT_SECRET\n";
}

// Setup Tailwind CSS
echo "\n🎨 Setting up Tailwind CSS...\n";
$tailwindSetupResult = setupTailwindCSS();
if ($tailwindSetupResult) {
    echo "✓ Tailwind CSS setup completed\n";
} else {
    echo "⚠️  Tailwind CSS setup had issues (check manually)\n";
}

// Create basic configuration files
createConfigurationFiles();

// Setup WebSocket server configuration
setupWebSocketConfig();

// Create sample data files
createSampleDataFiles();

echo "\n✅ Setup completed successfully!\n\n";
echo "📋 Next steps:\n";
echo "1. Update .env with your specific configuration\n";
echo "2. Run 'composer install-db' to create database tables\n";
echo "3. Run 'composer seed-db' to add sample data\n";
echo "4. Run 'composer serve' to start development server\n";
echo "5. Visit http://localhost:8000 to see your application\n\n";

echo "🔧 Available commands:\n";
echo "- composer serve          : Start development server\n";
echo "- composer websocket      : Start WebSocket server\n";
echo "- composer test           : Run tests\n";
echo "- composer analyse        : Run static analysis\n";
echo "- composer cs-fix         : Fix code style\n";
echo "- composer build          : Build for development\n";
echo "- composer build-production : Build for production\n\n";

/**
 * Setup Tailwind CSS configuration
 */
function setupTailwindCSS() {
    $tailwindConfig = __DIR__ . '/../tailwind.config.js';
    $tailwindCSS = __DIR__ . '/../public/css/tailwind.css';
    
    // Create Tailwind config if it doesn't exist
    if (!file_exists($tailwindConfig)) {
        $configContent = <<<JS
/** @type {import('tailwindcss').Config} */
module.exports = {
  prefix: 'tw-',
  content: [
    "./src/**/*.php",
    "./public/**/*.html",
    "./public/**/*.js",
    "./dashboards/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#fef2f2',
          100: '#fee2e2',
          500: '#ef4444',
          600: '#dc2626',
          700: '#b91c1c',
        },
        secondary: {
          50: '#fff7ed',
          100: '#ffedd5',
          500: '#f97316',
          600: '#ea580c',
        }
      },
      fontFamily: {
        'sans': ['Inter', 'Poppins', 'system-ui', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'pulse-slow': 'pulse 3s infinite',
      }
    }
  },
  plugins: []
}
JS;
        file_put_contents($tailwindConfig, $configContent);
        echo "✓ Created tailwind.config.js\n";
    }
    
    // Create Tailwind CSS input file
    if (!file_exists($tailwindCSS)) {
        $cssContent = <<<CSS
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer components {
  .tw-btn-primary {
    @apply tw-bg-primary-600 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-font-medium tw-transition-all tw-duration-200 hover:tw-bg-primary-700 hover:tw-scale-105 tw-min-h-[44px] tw-flex tw-items-center tw-justify-center;
  }
  
  .tw-btn-secondary {
    @apply tw-bg-secondary-500 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-font-medium tw-transition-all tw-duration-200 hover:tw-bg-secondary-600 hover:tw-scale-105 tw-min-h-[44px] tw-flex tw-items-center tw-justify-center;
  }
  
  .tw-btn-outline {
    @apply tw-border-2 tw-border-primary-600 tw-text-primary-600 tw-px-6 tw-py-3 tw-rounded-lg tw-font-medium tw-transition-all tw-duration-200 hover:tw-bg-primary-600 hover:tw-text-white hover:tw-scale-105 tw-min-h-[44px] tw-flex tw-items-center tw-justify-center;
  }
  
  .tw-card {
    @apply tw-bg-white tw-rounded-xl tw-shadow-lg tw-p-6 tw-transition-all tw-duration-200 hover:tw-shadow-xl;
  }
  
  .tw-glass {
    @apply tw-backdrop-blur-md tw-bg-white/10 tw-border tw-border-white/20 tw-rounded-xl;
  }
  
  .tw-glass-card {
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
CSS;
        file_put_contents($tailwindCSS, $cssContent);
        echo "✓ Created Tailwind CSS input file\n";
    }
    
    return true;
}

/**
 * Create additional configuration files
 */
function createConfigurationFiles() {
    // PHPUnit configuration
    $phpunitConfig = __DIR__ . '/../phpunit.xml';
    if (!file_exists($phpunitConfig)) {
        $phpunitContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory suffix="Test.php">./tests/Integration</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">./src</directory>
        </include>
    </coverage>
</phpunit>
XML;
        file_put_contents($phpunitConfig, $phpunitContent);
        echo "✓ Created phpunit.xml\n";
    }
    
    // PHPStan configuration
    $phpstanConfig = __DIR__ . '/../phpstan.neon';
    if (!file_exists($phpstanConfig)) {
        $phpstanContent = <<<NEON
parameters:
    level: 5
    paths:
        - src
    excludePaths:
        - src/views
    ignoreErrors:
        - '#Call to an undefined method PDO::#'
NEON;
        file_put_contents($phpstanConfig, $phpstanContent);
        echo "✓ Created phpstan.neon\n";
    }
}

/**
 * Setup WebSocket server configuration
 */
function setupWebSocketConfig() {
    $wsConfig = __DIR__ . '/../config/websocket.php';
    if (!file_exists($wsConfig)) {
        $wsContent = <<<PHP
<?php
/**
 * WebSocket Server Configuration
 */

return [
    'host' => env('WEBSOCKET_HOST', '0.0.0.0'),
    'port' => env('WEBSOCKET_PORT', 8080),
    'allowed_origins' => [
        env('APP_URL', 'http://localhost'),
        'http://localhost:8000',
        'http://127.0.0.1:8000'
    ],
    'heartbeat_interval' => 30, // seconds
    'max_connections' => 100,
    'timeout' => 60, // seconds
];
PHP;
        file_put_contents($wsConfig, $wsContent);
        echo "✓ Created WebSocket configuration\n";
    }
}

/**
 * Create sample data files for testing
 */
function createSampleDataFiles() {
    $sampleDir = __DIR__ . '/../storage/samples';
    if (!is_dir($sampleDir)) {
        mkdir($sampleDir, 0755, true);
    }
    
    // Sample restaurants CSV
    $restaurantsCsv = $sampleDir . '/restaurants.csv';
    if (!file_exists($restaurantsCsv)) {
        $csvContent = "name,category,description,phone,email,address,delivery_fee\n";
        $csvContent .= "Mama's Kitchen,African Cuisine,Authentic Cameroonian dishes,+237612345678,mama@example.com,Mile 4 Nkwen,500\n";
        $csvContent .= "Pizza Palace,Pizza,Fresh pizzas and Italian food,+237698765432,pizza@example.com,Commercial Avenue,750\n";
        $csvContent .= "Fast Bites,Fast Food,Quick and delicious meals,+237677889900,fast@example.com,Ntarikon,400\n";
        file_put_contents($restaurantsCsv, $csvContent);
        echo "✓ Created sample restaurants CSV\n";
    }
    
    // Sample menu items CSV
    $menuCsv = $sampleDir . '/menu_items.csv';
    if (!file_exists($menuCsv)) {
        $csvContent = "name,restaurant,category,price,description,is_available\n";
        $csvContent .= "Ndolé with Rice,Mama's Kitchen,Main Course,2500,Traditional Cameroonian ndolé with rice,1\n";
        $csvContent .= "Margherita Pizza,Pizza Palace,Pizza,3500,Fresh tomatoes and mozzarella,1\n";
        $csvContent .= "Chicken Burger,Fast Bites,Burgers,2000,Grilled chicken with fries,1\n";
        file_put_contents($menuCsv, $csvContent);
        echo "✓ Created sample menu items CSV\n";
    }
}
