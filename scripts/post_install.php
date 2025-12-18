<?php
/**
 * Post-Install Script
 * Runs after Composer install/update to set up the environment
 */

echo "🔧 Time2Eat Post-Install Setup\n";
echo "==============================\n\n";

// Check if we're in a fresh installation
$isFirstInstall = !file_exists(__DIR__ . '/../.env');

if ($isFirstInstall) {
    echo "🎉 Welcome to Time2Eat!\n";
    echo "This appears to be a fresh installation.\n\n";
}

// Create necessary directories
$directories = [
    'logs',
    'storage/cache',
    'storage/uploads',
    'storage/exports',
    'storage/backups',
    'storage/sessions',
    'public/uploads/restaurants',
    'public/uploads/menu-items',
    'public/uploads/users',
    'public/uploads/temp'
];

echo "📁 Creating directories...\n";
foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/../' . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "✓ Created: {$dir}\n";
    }
}

// Create .gitignore files for upload directories
$gitignoreContent = "*\n!.gitignore\n";
$gitignoreDirs = [
    'logs',
    'storage/cache',
    'storage/uploads',
    'storage/exports',
    'storage/backups',
    'storage/sessions',
    'public/uploads'
];

foreach ($gitignoreDirs as $dir) {
    $gitignoreFile = __DIR__ . '/../' . $dir . '/.gitignore';
    if (!file_exists($gitignoreFile)) {
        file_put_contents($gitignoreFile, $gitignoreContent);
    }
}

// Set up environment file
if (!file_exists(__DIR__ . '/../.env')) {
    if (file_exists(__DIR__ . '/../.env.example')) {
        copy(__DIR__ . '/../.env.example', __DIR__ . '/../.env');
        echo "✓ Created .env from .env.example\n";
        
        // Generate secure keys
        generateSecureKeys();
    } else {
        echo "⚠️  .env.example not found\n";
    }
} else {
    echo "✓ .env file already exists\n";
}

// Set proper permissions (Unix-like systems)
if (PHP_OS_FAMILY !== 'Windows') {
    echo "\n🔒 Setting permissions...\n";
    
    $permissionDirs = [
        'logs' => 0755,
        'storage' => 0755,
        'public/uploads' => 0755,
        '.env' => 0600
    ];
    
    foreach ($permissionDirs as $path => $permission) {
        $fullPath = __DIR__ . '/../' . $path;
        if (file_exists($fullPath)) {
            chmod($fullPath, $permission);
            echo "✓ Set permissions for: {$path}\n";
        }
    }
}

// Create configuration files if they don't exist
createConfigurationFiles();

// Optimize autoloader
echo "\n⚡ Optimizing autoloader...\n";
$output = [];
$returnCode = 0;
exec('composer dump-autoload --optimize 2>&1', $output, $returnCode);
if ($returnCode === 0) {
    echo "✓ Autoloader optimized\n";
} else {
    echo "⚠️  Autoloader optimization failed\n";
}

// Check for Node.js and setup frontend assets
checkFrontendSetup();

// Display next steps
displayNextSteps($isFirstInstall);

/**
 * Generate secure keys for the application
 */
function generateSecureKeys() {
    echo "\n🔐 Generating secure keys...\n";
    
    $envFile = __DIR__ . '/../.env';
    $envContent = file_get_contents($envFile);
    
    // Generate APP_KEY
    if (strpos($envContent, 'APP_KEY=your-secret-key-here') !== false) {
        $appKey = bin2hex(random_bytes(32));
        $envContent = str_replace('APP_KEY=your-secret-key-here', 'APP_KEY=' . $appKey, $envContent);
        echo "✓ Generated APP_KEY\n";
    }
    
    // Generate JWT_SECRET
    if (strpos($envContent, 'JWT_SECRET=your-jwt-secret-key-here') !== false) {
        $jwtSecret = bin2hex(random_bytes(32));
        $envContent = str_replace('JWT_SECRET=your-jwt-secret-key-here', 'JWT_SECRET=' . $jwtSecret, $envContent);
        echo "✓ Generated JWT_SECRET\n";
    }
    
    file_put_contents($envFile, $envContent);
}

/**
 * Create additional configuration files
 */
function createConfigurationFiles() {
    echo "\n📝 Creating configuration files...\n";
    
    // Create phpunit.xml if it doesn't exist
    $phpunitConfig = __DIR__ . '/../phpunit.xml';
    if (!file_exists($phpunitConfig)) {
        $phpunitContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnRisky="true"
         failOnWarning="true">
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
        <exclude>
            <directory suffix=".php">./src/views</directory>
        </exclude>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_NAME" value="time2eat_test"/>
    </php>
</phpunit>
XML;
        file_put_contents($phpunitConfig, $phpunitContent);
        echo "✓ Created phpunit.xml\n";
    }
    
    // Create phpstan.neon if it doesn't exist
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
        - '#Variable \$[a-zA-Z0-9_]+ might not be defined#'
    checkMissingIterableValueType: false
NEON;
        file_put_contents($phpstanConfig, $phpstanContent);
        echo "✓ Created phpstan.neon\n";
    }
    
    // Create .php-cs-fixer.php if it doesn't exist
    $csFixerConfig = __DIR__ . '/../.php-cs-fixer.php';
    if (!file_exists($csFixerConfig)) {
        $csFixerContent = <<<PHP
<?php

\$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->exclude(['views'])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
    ])
    ->setFinder(\$finder);
PHP;
        file_put_contents($csFixerConfig, $csFixerContent);
        echo "✓ Created .php-cs-fixer.php\n";
    }
}

/**
 * Check frontend setup and Node.js availability
 */
function checkFrontendSetup() {
    echo "\n🎨 Checking frontend setup...\n";
    
    // Check if Node.js is available
    $nodeVersion = shell_exec('node --version 2>/dev/null');
    if ($nodeVersion) {
        echo "✓ Node.js available: " . trim($nodeVersion) . "\n";
        
        // Check if package.json exists
        if (file_exists(__DIR__ . '/../package.json')) {
            echo "✓ package.json found\n";
            
            // Install npm dependencies if node_modules doesn't exist
            if (!is_dir(__DIR__ . '/../node_modules')) {
                echo "📦 Installing npm dependencies...\n";
                $output = [];
                $returnCode = 0;
                exec('cd ' . __DIR__ . '/.. && npm install 2>&1', $output, $returnCode);
                
                if ($returnCode === 0) {
                    echo "✓ npm dependencies installed\n";
                } else {
                    echo "⚠️  npm install failed\n";
                }
            } else {
                echo "✓ npm dependencies already installed\n";
            }
        } else {
            echo "⚠️  package.json not found (using CDN for Tailwind CSS)\n";
        }
    } else {
        echo "⚠️  Node.js not found (using CDN for Tailwind CSS)\n";
    }
}

/**
 * Display next steps to the user
 */
function displayNextSteps($isFirstInstall) {
    echo "\n" . str_repeat("=", 50) . "\n";
    
    if ($isFirstInstall) {
        echo "🎉 Time2Eat installation completed!\n\n";
        
        echo "📋 Next steps:\n";
        echo "1. Update your .env file with database credentials\n";
        echo "2. Run: composer install-db\n";
        echo "3. Run: composer seed-db\n";
        echo "4. Run: composer serve\n";
        echo "5. Visit: http://localhost:8000\n\n";
        
        echo "🔧 Optional setup:\n";
        echo "- Add Google Maps API key to .env (MAP_API_KEY)\n";
        echo "- Configure payment gateways (Stripe, PayPal)\n";
        echo "- Set up email/SMS services (Twilio, SendGrid)\n";
        echo "- Configure WebSocket server for real-time features\n\n";
        
        echo "📚 Documentation:\n";
        echo "- See SETUP.md for detailed setup instructions\n";
        echo "- See README.md for feature implementation steps\n\n";
    } else {
        echo "✅ Time2Eat dependencies updated!\n\n";
        
        echo "🔄 You may want to:\n";
        echo "1. Check for database migrations: composer migrate\n";
        echo "2. Clear cache: composer cache-clear\n";
        echo "3. Run tests: composer test\n";
        echo "4. Update frontend assets: npm run build-css\n\n";
    }
    
    echo "🚀 Available commands:\n";
    echo "- composer serve          : Start development server\n";
    echo "- composer websocket      : Start WebSocket server\n";
    echo "- composer test           : Run tests\n";
    echo "- composer analyse        : Run static analysis\n";
    echo "- composer cs-fix         : Fix code style\n";
    echo "- composer build          : Build for development\n";
    echo "- composer build-production : Build for production\n";
    echo "- composer setup-tailwind : Setup Tailwind CSS\n";
    echo "- composer optimize       : Optimize for production\n\n";
    
    echo "💡 Tips:\n";
    echo "- Use 'composer serve' for development\n";
    echo "- Run 'composer build' before committing code\n";
    echo "- Check logs/ directory for error logs\n";
    echo "- Use storage/ directory for file uploads\n\n";
    
    echo "📞 Need help?\n";
    echo "- Check SETUP.md for troubleshooting\n";
    echo "- Review .env.example for configuration options\n";
    echo "- Run 'composer test' to verify installation\n\n";
}
