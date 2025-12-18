<?php
/**
 * Time2Eat Installation Script
 * Self-deleting installer for any hosting environment
 * Compatible with shared hosting, VPS, and cloud platforms
 */

// Prevent direct access after installation
if (file_exists('.env') && file_exists('config/installed.lock')) {
    die('Time2Eat is already installed. Delete config/installed.lock to reinstall.');
}

// Start session for installer state with proper configuration
ini_set('session.cookie_lifetime', 3600); // 1 hour
ini_set('session.gc_maxlifetime', 3600);
session_start();

// Debug session issues
if (isset($_GET['debug_session'])) {
    echo "<pre>Session Debug:\n";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Data: " . print_r($_SESSION, true) . "\n";
    echo "</pre>";
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple error handling - don't suppress errors during installation
$errors = [];
$success = [];

// Load hosting compatibility checker
require_once 'scripts/hosting_compatibility.php';
$hostingCompat = new HostingCompatibility();

/**
 * Detect if we're in a production hosting environment
 * Production environments typically have restrictions on triggers/procedures
 */
function isProductionEnvironment() {
    // Check for common hosting environment indicators
    $production_indicators = [
        // Hosting provider specific
        isset($_SERVER['SHARED_HOSTING']),
        isset($_SERVER['HTTP_X_FORWARDED_FOR']),
        isset($_SERVER['HTTP_CF_RAY']), // Cloudflare
        isset($_SERVER['HTTP_X_REAL_IP']),
        
        // Common hosting paths
        strpos(__DIR__, '/public_html/') !== false,
        strpos(__DIR__, '/www/') !== false && strpos(__DIR__, '/home/') !== false,
        strpos(__DIR__, '/domains/') !== false,
        
        // Check if we're not on common development environments
        !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) && 
        !preg_match('/\.local$/', $_SERVER['HTTP_HOST'] ?? ''),
        
        // Check for common hosting PHP configurations
        !ini_get('display_errors'), // Production usually has this off
        ini_get('log_errors'), // Production usually has this on
    ];
    
    return count(array_filter($production_indicators)) >= 2;
}

/**
 * Test MySQL privileges to see if triggers can be created
 */
function canCreateTriggers($pdo) {
    try {
        // Try to create a simple test trigger
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS __trigger_test__ (
                id INT PRIMARY KEY AUTO_INCREMENT,
                test_value VARCHAR(50)
            )
        ");
        
        $pdo->exec("
            CREATE TRIGGER __test_trigger__ 
            BEFORE INSERT ON __trigger_test__ 
            FOR EACH ROW 
            BEGIN 
                SET NEW.test_value = 'test'; 
            END
        ");
        
        // Clean up
        $pdo->exec("DROP TRIGGER IF EXISTS __test_trigger__");
        $pdo->exec("DROP TABLE IF EXISTS __trigger_test__");
        
        return true;
    } catch (PDOException $e) {
        // Clean up in case of partial success
        try {
            $pdo->exec("DROP TRIGGER IF EXISTS __test_trigger__");
            $pdo->exec("DROP TABLE IF EXISTS __trigger_test__");
        } catch (PDOException $cleanup_e) {
            // Ignore cleanup errors
        }
        
        // Check if it's a privilege error
        return !preg_match('/privilege|SUPER|log_bin_trust_function_creators/i', $e->getMessage());
    }
}

// Detect environment and choose appropriate database file
$is_production = isProductionEnvironment();
$database_file = 'database/data.sql';

if ($is_production && file_exists('database/data_production.sql')) {
    $database_file = 'database/data_production.sql';
}

// Installation configuration
$config = [
    'min_php_version' => '8.0.0',
    'required_extensions' => ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'curl', 'gd'],
    'required_directories' => ['public/uploads', 'logs', 'cache', 'storage'],
    'database_file' => $database_file,
    'sample_data_file' => 'database/sample_data.sql',
    'hosting_config' => $hostingCompat->getConfig()
];

// Installation steps
$steps = [
    1 => 'System Requirements Check',
    2 => 'Database Configuration',
    3 => 'Database Setup',
    4 => 'Admin Account Creation',
    5 => 'Final Configuration',
    6 => 'Installation Complete'
];

// Get current step from GET or POST (POST takes precedence for form submissions)
$current_step = isset($_POST['step']) ? (int)$_POST['step'] : (isset($_GET['step']) ? (int)$_GET['step'] : 1);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($current_step) {
        case 2:
            $current_step = handleDatabaseConfig();
            break;
        case 3:
            $current_step = handleDatabaseSetup();
            break;
        case 4:
            $current_step = handleAdminCreation();
            break;
        case 5:
            $current_step = handleFinalConfiguration();
            break;
    }
}

/**
 * Handle database configuration
 */
function handleDatabaseConfig() {
    global $errors, $success;
    
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $db_port = (int)($_POST['db_port'] ?? 3306);
    
    // Validate inputs
    if (empty($db_host)) $errors[] = 'Database host is required';
    if (empty($db_name)) $errors[] = 'Database name is required';
    if (empty($db_user)) $errors[] = 'Database username is required';
    
    if (empty($errors)) {
        // Test database connection
        try {
            $dsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Check if database exists, create if not
            $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([$db_name]);
            
            if (!$stmt->fetch()) {
                $pdo->exec("CREATE DATABASE `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $success[] = "Database '{$db_name}' created successfully";
            }
            
            // Store database config in session
            $_SESSION['db_config'] = [
                'host' => $db_host,
                'name' => $db_name,
                'user' => $db_user,
                'pass' => $db_pass,
                'port' => $db_port
            ];
            
            $success[] = 'Database connection successful';
            return 3; // Next step
            
        } catch (PDOException $e) {
            $error_code = $e->getCode();
            $error_message = $e->getMessage();

            // Provide more specific error messages based on common issues
            if (strpos($error_message, 'Access denied') !== false) {
                $errors[] = 'Database connection failed: Access denied. Please check your username and password.';
                $errors[] = 'Error details: ' . $error_message;
            } elseif (strpos($error_message, 'Unknown database') !== false) {
                $errors[] = 'Database connection failed: Database does not exist and could not be created.';
                $errors[] = 'Please create the database manually or check your permissions.';
                $errors[] = 'Error details: ' . $error_message;
            } elseif (strpos($error_message, 'Connection refused') !== false) {
                $errors[] = 'Database connection failed: Cannot connect to MySQL server.';
                $errors[] = 'Please check if MySQL is running and the host/port are correct.';
                $errors[] = 'Error details: ' . $error_message;
            } elseif (strpos($error_message, 'timeout') !== false) {
                $errors[] = 'Database connection failed: Connection timeout.';
                $errors[] = 'The database server is not responding. Please try again later.';
                $errors[] = 'Error details: ' . $error_message;
            } else {
                $errors[] = 'Database connection failed: ' . $error_message;
                $errors[] = 'Error code: ' . $error_code;
            }
        } catch (Exception $e) {
            $errors[] = 'Unexpected error during database connection: ' . $e->getMessage();
        }
    }
    
    return 2; // Stay on current step
}

/**
 * Parse SQL statements properly handling triggers and procedures
 */
function parseSQLStatements($sql) {
    // Remove comments but preserve structure
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Split by semicolon, but handle DELIMITER statements
    $statements = [];
    $currentStatement = '';
    $delimiter = ';';
    
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines
        if (empty($line)) {
            if (!empty($currentStatement)) {
                $currentStatement .= "\n";
            }
            continue;
        }
        
        // Handle DELIMITER statements
        if (preg_match('/^DELIMITER\s+(.+)$/i', $line, $matches)) {
            $delimiter = trim($matches[1]);
            // Skip DELIMITER lines - they're not SQL statements
            continue;
        }
        
        $currentStatement .= $line . "\n";
        
        // Check if statement ends with current delimiter
        if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
            $statement = trim(substr($currentStatement, 0, -strlen($delimiter) - 1));
            if (!empty($statement)) {
                $statements[] = $statement;
            }
            $currentStatement = '';
        }
    }
    
    // Add any remaining statement
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    // Filter out empty statements and return
    return array_filter($statements, function($stmt) {
        $stmt = trim($stmt);
        return !empty($stmt) && !preg_match('/^(SET|START|COMMIT|ROLLBACK)\s+/i', $stmt);
    });
}

/**
 * Check if a statement is comment-only
 */
function isCommentOnly($statement) {
    $lines = explode("\n", $statement);
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '--') !== 0 && strpos($line, '/*') !== 0) {
            return false;
        }
    }
    return true;
}

/**
 * Handle database setup
 */
function handleDatabaseSetup() {
    global $errors, $success, $config;
    
    if (!isset($_SESSION['db_config'])) {
        $errors[] = 'Database configuration not found. Please go back to step 2.';
        return 2;
    }
    
    $db_config = $_SESSION['db_config'];
    
    try {
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // Import main database schema
        $schema_file = $config['database_file'];
        $is_production_env = isProductionEnvironment();
        
        // Add environment information to success messages
        if ($is_production_env && strpos($schema_file, 'production') !== false) {
            $success[] = 'Production environment detected - using production-compatible database schema (no triggers/procedures)';
        } elseif (!$is_production_env) {
            $success[] = 'Development environment detected - using full database schema with triggers and procedures';
        }
        
        if (!file_exists($schema_file)) {
            // Fallback to schema.sql if data.sql doesn't exist
            $schema_file = 'database/schema.sql';
            $success[] = 'Using fallback schema file: ' . $schema_file;
        }
        
        if (file_exists($schema_file)) {
            $sql = file_get_contents($schema_file);
            $statements = parseSQLStatements($sql);

            try {
                foreach ($statements as $i => $statement) {
                    $statement = trim($statement);
                    if (!empty($statement) && !isCommentOnly($statement)) {
                        try {
                            $pdo->exec($statement);
                        } catch (PDOException $e) {
                            $errorCode = $e->getCode();
                            $errorMessage = $e->getMessage();
                            
                            // Ignore errors for duplicate columns/indexes/tables (already exist)
                            $ignorableErrors = [
                                1060, // Duplicate column name
                                1061, // Duplicate key name (index)
                                1062, // Duplicate entry (unique constraint violation)
                                1050, // Table already exists
                                1051, // Unknown column
                                1091, // Can't DROP column/index that doesn't exist
                            ];
                            
                            // Check if error is about duplicates or existing objects
                            $isIgnorable = in_array($errorCode, $ignorableErrors) ||
                                          stripos($errorMessage, 'already exists') !== false ||
                                          stripos($errorMessage, 'Duplicate column') !== false ||
                                          stripos($errorMessage, 'Duplicate key') !== false ||
                                          stripos($errorMessage, 'Duplicate index') !== false ||
                                          stripos($errorMessage, 'Duplicate entry') !== false;
                            
                            if ($isIgnorable) {
                                // Silently skip - object already exists
                                continue;
                            }
                            
                            // Enhanced error reporting with statement context
                            $error_msg = "Database setup failed: SQL syntax error in database schema.\n";
                            $error_msg .= "The database schema file may be corrupted or incompatible.\n";
                            $error_msg .= "Error details: Database setup failed: " . $e->getMessage() . "\n";
                            $error_msg .= "Statement #" . ($i + 1) . ":\n" . substr($statement, 0, 200) . "...";
                            
                            // Special handling for trigger errors
                            if (stripos($statement, 'CREATE TRIGGER') !== false) {
                                $error_msg .= "\n\nNote: This appears to be a trigger creation error. ";
                                $error_msg .= "Make sure DELIMITER statements are properly used around triggers.";
                            }
                            
                            // Special handling for stored procedure errors
                            if (stripos($statement, 'CREATE PROCEDURE') !== false) {
                                $error_msg .= "\n\nNote: This appears to be a stored procedure creation error. ";
                                $error_msg .= "Make sure DELIMITER statements are properly used around stored procedures.";
                            }
                            
                            throw new PDOException($error_msg);
                        }
                    }
                }
            } catch (PDOException $e) {
                // Re-throw with enhanced context
                throw $e;
            }
            $success[] = 'Database schema imported successfully (' . count($statements) . ' statements)';
        } else {
            $errors[] = 'Database schema file not found: ' . $schema_file;
        }
        
        // Import sample data if requested
        if (isset($_POST['import_sample_data']) && file_exists($config['sample_data_file'])) {
            try {
                $sql = file_get_contents($config['sample_data_file']);
                $statements = parseSQLStatements($sql);

                foreach ($statements as $i => $statement) {
                    $statement = trim($statement);
                    if (!empty($statement) && !isCommentOnly($statement)) {
                        try {
                        $pdo->exec($statement);
                        } catch (PDOException $e) {
                            $errorCode = $e->getCode();
                            $errorMessage = $e->getMessage();
                            
                            // Ignore foreign key constraint errors for sample data (data may reference items that don't exist)
                            $ignorableErrors = [
                                1452, // Cannot add or update a child row: a foreign key constraint fails
                                1451, // Cannot delete or update a parent row: a foreign key constraint fails
                                1060, // Duplicate column name
                                1061, // Duplicate key name (index)
                                1062, // Duplicate entry (unique constraint violation)
                                1050, // Table already exists
                                1051, // Unknown column
                                1091, // Can't DROP column/index that doesn't exist
                            ];
                            
                            // Check if error is about foreign keys or duplicates
                            $isIgnorable = in_array($errorCode, $ignorableErrors) ||
                                          stripos($errorMessage, 'already exists') !== false ||
                                          stripos($errorMessage, 'Duplicate column') !== false ||
                                          stripos($errorMessage, 'Duplicate key') !== false ||
                                          stripos($errorMessage, 'Duplicate index') !== false ||
                                          stripos($errorMessage, 'Duplicate entry') !== false ||
                                          stripos($errorMessage, 'foreign key constraint') !== false ||
                                          stripos($errorMessage, 'Cannot add or update a child row') !== false;
                            
                            if ($isIgnorable) {
                                // Silently skip - foreign key constraint or duplicate
                                continue;
                            }
                            
                            // Re-throw if it's not an ignorable error
                            throw new PDOException("Sample data import failed at statement #" . ($i + 1) . ": " . $e->getMessage());
                        }
                    }
                }

                $success[] = 'Sample data imported successfully (' . count($statements) . ' statements)';
            } catch (PDOException $e) {
                throw new PDOException("Sample data import failed: " . $e->getMessage());
            }
        }

        
        return 4; // Next step
        
    } catch (PDOException $e) {
        $error_message = $e->getMessage();
        $error_code = $e->getCode();

        // Provide specific error messages for common database setup issues
        if (strpos($error_message, 'Syntax error') !== false) {
            $errors[] = 'Database setup failed: SQL syntax error in database schema.';
            $errors[] = 'The database schema file may be corrupted or incompatible.';
            $errors[] = 'Error details: ' . $error_message;
        } elseif (strpos($error_message, 'Table') !== false && strpos($error_message, 'already exists') !== false) {
            $errors[] = 'Database setup failed: Tables already exist.';
            $errors[] = 'The database may have been partially installed. Please drop all tables and try again.';
            $errors[] = 'You can manually drop tables or create a new database for a fresh installation.';
            $errors[] = 'Error details: ' . $error_message;
        } elseif (strpos($error_message, 'Access denied') !== false) {
            $errors[] = 'Database setup failed: Insufficient privileges to create tables.';
            $errors[] = 'Please ensure your database user has CREATE, INSERT, and ALTER privileges.';
            $errors[] = 'Error details: ' . $error_message;
        } elseif (strpos($error_message, 'Disk full') !== false || strpos($error_message, 'No space') !== false) {
            $errors[] = 'Database setup failed: Insufficient disk space.';
            $errors[] = 'Please free up disk space on your server and try again.';
            $errors[] = 'Error details: ' . $error_message;
        } else {
            $errors[] = 'Database setup failed: ' . $error_message;
            $errors[] = 'Error code: ' . $error_code;
            $errors[] = 'Please check your database schema file and try again.';
        }

        return 3; // Stay on current step
    } catch (Exception $e) {
        $errors[] = 'Unexpected error during database setup: ' . $e->getMessage();
        $errors[] = 'Please check file permissions and try again.';
        return 3;
    }
}

/**
 * Handle admin account creation
 */
function handleAdminCreation() {
    global $errors, $success;
    
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';
    $admin_confirm = $_POST['admin_confirm'] ?? '';
    $admin_name = trim($_POST['admin_name'] ?? '');
    
    // Validate inputs
    if (empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid admin email is required';
    }
    if (empty($admin_password) || strlen($admin_password) < 8) {
        $errors[] = 'Admin password must be at least 8 characters';
    }
    if ($admin_password !== $admin_confirm) {
        $errors[] = 'Password confirmation does not match';
    }
    if (empty($admin_name)) {
        $errors[] = 'Admin name is required';
    }
    
    if (empty($errors)) {
        // Validate database configuration exists
        if (!isset($_SESSION['db_config'])) {
            $errors[] = 'Database configuration not found. Please go back to step 2 and configure the database.';
            return 2;
        }

        try {
            $db_config = $_SESSION['db_config'];
            $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Check if admin user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'admin'");
            $stmt->execute([$admin_email]);
            $existing_admin = $stmt->fetch();
            
            if ($existing_admin) {
                // Update existing admin user with new password
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET password = ?, first_name = ?, last_name = 'Administrator', phone = '+237000000000', updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$hashed_password, $admin_name, $existing_admin['id']]);
                $success[] = 'Admin account updated successfully (user already existed)';
            } else {
                // Create new admin user
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                $username = 'admin_' . uniqid();
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, phone, role, status, email_verified_at) 
                    VALUES (?, ?, ?, ?, 'Administrator', '+237000000000', 'admin', 'active', NOW())
                ");
                
                $stmt->execute([$username, $admin_email, $hashed_password, $admin_name]);
                $success[] = 'Admin account created successfully';
            }
            
            $_SESSION['admin_created'] = true;
            return 5; // Next step
            
        } catch (PDOException $e) {
            $error_message = $e->getMessage();
            $error_code = $e->getCode();

            // Provide specific error messages for admin creation issues
            if (strpos($error_message, 'Duplicate entry') !== false && strpos($error_message, 'email') !== false) {
                $errors[] = 'Failed to create admin account: Email address already exists.';
                $errors[] = 'Please use a different email address or check if an admin account already exists.';
                $errors[] = 'You can manually delete existing admin records from the users table if needed.';
                $errors[] = 'Error details: ' . $error_message;
            } elseif (strpos($error_message, 'users') !== false && strpos($error_message, "doesn't exist") !== false) {
                $errors[] = 'Failed to create admin account: Users table does not exist.';
                $errors[] = 'Please go back to step 3 and ensure the database schema was imported correctly.';
                $errors[] = 'Error details: ' . $error_message;
            } elseif (strpos($error_message, 'Data too long') !== false) {
                $errors[] = 'Failed to create admin account: One or more fields exceed maximum length.';
                $errors[] = 'Please use shorter values for name and email fields.';
                $errors[] = 'Error details: ' . $error_message;
            } elseif (strpos($error_message, 'Access denied') !== false) {
                $errors[] = 'Failed to create admin account: Insufficient database privileges.';
                $errors[] = 'Please ensure your database user has INSERT privileges on the users table.';
                $errors[] = 'Error details: ' . $error_message;
            } else {
                $errors[] = 'Failed to create admin account: ' . $error_message;
                $errors[] = 'Error code: ' . $error_code;
                $errors[] = 'Please check your database connection and try again.';
            }
        } catch (Exception $e) {
            $errors[] = 'Unexpected error during admin account creation: ' . $e->getMessage();
            $errors[] = 'Please check your system configuration and try again.';
        }
    }
    
    return 4; // Stay on current step
}

/**
 * Handle final configuration
 */
function handleFinalConfiguration() {
    global $errors, $success, $hostingCompat, $config;

    $app_name = trim($_POST['app_name'] ?? 'Time2Eat');
    $app_url = trim($_POST['app_url'] ?? '');
    $app_env = $_POST['app_env'] ?? 'production';

    // Generate secure keys
    $app_key = bin2hex(random_bytes(32));
    $jwt_secret = bin2hex(random_bytes(32));

    // Get hosting-specific configuration
    $hostingConfig = $hostingCompat->generateConfig();

    // Validate database configuration exists
    if (!isset($_SESSION['db_config'])) {
        $errors[] = 'Database configuration not found. Please go back to step 2 and configure the database.';
        return 2;
    }

    // Create .env file with hosting-aware settings
    $env_content = generateEnvFile($_SESSION['db_config'], [
        'app_name' => $app_name,
        'app_url' => $app_url,
        'app_env' => $app_env,
        'app_key' => $app_key,
        'jwt_secret' => $jwt_secret
    ], $hostingConfig);

    // Create .env file with detailed error handling
    try {
        if (file_put_contents('.env', $env_content)) {
            $success[] = 'Environment configuration created';
        } else {
            $errors[] = 'Failed to create .env file: Unable to write to file.';
            $errors[] = 'Please check that the web server has write permissions to the application directory.';
            $errors[] = 'Try setting directory permissions to 755 and file permissions to 644.';
        }
    } catch (Exception $e) {
        $errors[] = 'Failed to create .env file: ' . $e->getMessage();
        $errors[] = 'Please check file system permissions and available disk space.';
    }

    // Generate hosting-specific .htaccess with error handling
    try {
        $htaccess_content = $hostingCompat->generateHtaccess();
        if (file_put_contents('.htaccess', $htaccess_content)) {
            $success[] = 'URL rewriting configuration updated for your hosting environment';
        } else {
            $errors[] = 'Failed to create .htaccess file: Unable to write to file.';
            $errors[] = 'Please check that the web server has write permissions to the application directory.';
            $errors[] = 'You may need to create this file manually or contact your hosting provider.';
        }
    } catch (Exception $e) {
        $errors[] = 'Failed to create .htaccess file: ' . $e->getMessage();
        $errors[] = 'Please check file system permissions and hosting configuration.';
    }

    // Create required directories with error handling
    try {
        createRequiredDirectories();
    } catch (Exception $e) {
        $errors[] = 'Failed to create required directories: ' . $e->getMessage();
        $errors[] = 'Please create the following directories manually: ' . implode(', ', $config['required_directories']);
    }

    // Create installation lock file with error handling
    try {
        if (!is_dir('config')) {
            if (!mkdir('config', 0755, true)) {
                $errors[] = 'Failed to create config directory for installation lock.';
                $errors[] = 'Please create the config directory manually with 755 permissions.';
            }
        }

        if (file_put_contents('config/installed.lock', date('Y-m-d H:i:s'))) {
            $success[] = 'Installation lock created';
        } else {
            $errors[] = 'Failed to create installation lock file.';
            $errors[] = 'Installation completed but lock file could not be created.';
            $errors[] = 'Please create config/installed.lock manually to prevent reinstallation.';
        }
    } catch (Exception $e) {
        $errors[] = 'Failed to create installation lock: ' . $e->getMessage();
        $errors[] = 'Please create config/installed.lock manually after installation.';
    }

    if (empty($errors)) {
        return 6; // Installation complete
    }

    return 5; // Stay on current step
}

/**
 * Generate .env file content
 */
function generateEnvFile($db_config, $app_config, $hosting_config = []) {
    $content = "# Time2Eat Environment Configuration
# Generated on " . date('Y-m-d H:i:s') . "
# Hosting Type: " . ($hosting_config['HOSTING_TYPE'] ?? 'unknown') . "

# Application
APP_NAME=\"{$app_config['app_name']}\"
APP_URL={$app_config['app_url']}
APP_ENV={$app_config['app_env']}
APP_DEBUG=" . ($app_config['app_env'] === 'development' ? 'true' : 'false') . "
APP_KEY={$app_config['app_key']}";

    // Add hosting-specific configuration
    if (!empty($hosting_config)) {
        $content .= "
APP_PATH=" . ($hosting_config['APP_PATH'] ?? '') . "
HOSTING_TYPE=" . ($hosting_config['HOSTING_TYPE'] ?? 'unknown') . "
URL_REWRITING=" . ($hosting_config['URL_REWRITING'] ?? 'true');
    }

    $content .= "

# Database
DB_HOST={$db_config['host']}
DB_PORT={$db_config['port']}
DB_NAME={$db_config['name']}
DB_USER={$db_config['user']}
DB_PASS={$db_config['pass']}
DB_CHARSET=utf8mb4

# Security
JWT_SECRET={$app_config['jwt_secret']}

# Mail Configuration (Update with your settings)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@time2eat.com
MAIL_FROM_NAME=\"Time2Eat\"

# Payment Gateways (Update with your credentials)
STRIPE_PUBLIC_KEY=pk_test_your_stripe_public_key
STRIPE_SECRET_KEY=sk_test_your_stripe_secret_key
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
TRANZAK_API_KEY=your_tranzak_api_key

# SMS Configuration (Twilio)
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+1234567890

# Maps API
MAP_API_KEY=your_google_maps_api_key
MAP_PROVIDER=google

# File Storage
STORAGE_DRIVER=local
STORAGE_PATH=storage/

# Cache
CACHE_DRIVER=file
CACHE_PATH=cache/

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=7200

# Timezone
APP_TIMEZONE=Africa/Douala
";

    return $content;
}

/**
 * Create required directories
 */
function createRequiredDirectories() {
    global $config, $success, $errors;
    
    foreach ($config['required_directories'] as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                $success[] = "Created directory: {$dir}";
            } else {
                $errors[] = "Failed to create directory: {$dir}";
            }
        }
        
        // Create .htaccess for security
        if (in_array($dir, ['logs', 'cache', 'storage'])) {
            $htaccess = $dir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Deny from all\n");
            }
        }
    }
}

/**
 * Check system requirements
 */
function checkSystemRequirements() {
    global $config;
    
    $requirements = [];
    
    // PHP Version
    $requirements['php_version'] = [
        'name' => 'PHP Version (' . $config['min_php_version'] . '+)',
        'status' => version_compare(PHP_VERSION, $config['min_php_version'], '>='),
        'current' => PHP_VERSION
    ];
    
    // PHP Extensions
    foreach ($config['required_extensions'] as $ext) {
        $requirements['ext_' . $ext] = [
            'name' => "PHP Extension: {$ext}",
            'status' => extension_loaded($ext),
            'current' => extension_loaded($ext) ? 'Loaded' : 'Not loaded'
        ];
    }
    
    // File Permissions
    $requirements['writable_root'] = [
        'name' => 'Root Directory Writable',
        'status' => is_writable('.'),
        'current' => is_writable('.') ? 'Writable' : 'Not writable'
    ];
    
    return $requirements;
}

// Handle step 1 (requirements check)
if ($current_step === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $requirements = checkSystemRequirements();
    $all_passed = true;
    
    foreach ($requirements as $req) {
        if (!$req['status']) {
            $all_passed = false;
            break;
        }
    }
    
    if ($all_passed) {
        $current_step = 2;
    } else {
        $errors[] = 'Please fix the system requirements before continuing.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time2Eat Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .step-active { @apply bg-red-500 text-white; }
        .step-completed { @apply bg-green-500 text-white; }
        .step-pending { @apply bg-gray-300 text-gray-600; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Time2Eat Installation</h1>
            <p class="text-gray-600">Welcome to the Time2Eat food delivery platform installer</p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex flex-wrap justify-center space-x-2 mb-4">
                <?php foreach ($steps as $step_num => $step_name): ?>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold
                            <?= $step_num < $current_step ? 'step-completed' : ($step_num === $current_step ? 'step-active' : 'step-pending') ?>">
                            <?= $step_num ?>
                        </div>
                        <?php if ($step_num < count($steps)): ?>
                            <div class="w-8 h-0.5 bg-gray-300 mx-2"></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <h2 class="text-xl font-semibold text-gray-800"><?= $steps[$current_step] ?></h2>
            </div>
        </div>

        <!-- Messages -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Installation Error<?= count($errors) > 1 ? 's' : '' ?> Occurred
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm">
                                <p class="text-red-800 font-medium">Need Help?</p>
                                <ul class="mt-1 text-red-700 list-disc list-inside">
                                    <li>Check your hosting control panel for PHP and MySQL settings</li>
                                    <li>Verify file permissions (755 for directories, 644 for files)</li>
                                    <li>Ensure your database user has full privileges</li>
                                    <li>Contact your hosting provider if issues persist</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">
                            Step Completed Successfully
                        </h3>
                        <div class="mt-2 text-sm text-green-700">
                            <ul class="list-disc list-inside space-y-1">
                                <?php foreach ($success as $message): ?>
                                    <li><?= htmlspecialchars($message) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Installation Steps Content -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <?php
            // Include the appropriate step content
            switch ($current_step) {
                case 1:
                    include 'install_steps/step1_requirements.php';
                    break;
                case 2:
                    include 'install_steps/step2_database.php';
                    break;
                case 3:
                    include 'install_steps/step3_setup.php';
                    break;
                case 4:
                    include 'install_steps/step4_admin.php';
                    break;
                case 5:
                    include 'install_steps/step5_config.php';
                    break;
                case 6:
                    include 'install_steps/step6_complete.php';
                    break;
            }
            ?>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500">
            <p>&copy; <?= date('Y') ?> Time2Eat. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
