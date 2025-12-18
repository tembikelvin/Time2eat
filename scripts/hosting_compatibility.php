<?php
/**
 * Hosting Compatibility Script for Time2Eat
 * Detects hosting environment and configures the application accordingly
 */

class HostingCompatibility
{
    private array $config = [];
    private array $issues = [];
    private array $recommendations = [];

    public function __construct()
    {
        $this->detectEnvironment();
    }

    /**
     * Detect hosting environment and configuration
     */
    private function detectEnvironment(): void
    {
        // Detect hosting type
        $this->config['hosting_type'] = $this->detectHostingType();
        
        // Detect application path
        $this->config['app_path'] = $this->detectApplicationPath();
        
        // Detect base URL
        $this->config['base_url'] = $this->detectBaseUrl();
        
        // Check server capabilities
        $this->config['server_info'] = $this->getServerInfo();
        
        // Check file permissions
        $this->config['permissions'] = $this->checkPermissions();
        
        // Check URL rewriting
        $this->config['url_rewriting'] = $this->checkUrlRewriting();
    }

    /**
     * Detect hosting type (shared, VPS, cloud, local)
     */
    private function detectHostingType(): string
    {
        // Check for common shared hosting indicators
        if (isset($_SERVER['SHARED_HOSTING']) || 
            strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'cPanel') !== false ||
            strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'public_html') !== false) {
            return 'shared';
        }
        
        // Check for local development
        if (in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) ||
            strpos($_SERVER['HTTP_HOST'] ?? '', '.local') !== false) {
            return 'local';
        }
        
        // Check for cloud platforms
        if (isset($_SERVER['PLATFORM_BRANCH']) || // Platform.sh
            isset($_SERVER['HEROKU_APP_NAME']) ||   // Heroku
            isset($_SERVER['AWS_REGION'])) {        // AWS
            return 'cloud';
        }
        
        return 'vps'; // Default assumption
    }

    /**
     * Detect application installation path
     */
    private function detectApplicationPath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Remove query string from request URI
        $requestUri = strtok($requestUri, '?');
        
        // Get directory path
        $scriptDir = dirname($scriptName);
        
        // Normalize path
        $path = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;
        
        return rtrim($path, '/');
    }

    /**
     * Detect base URL for the application
     */
    private function detectBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = $this->config['app_path'] ?? '';
        
        return $protocol . $host . $path;
    }

    /**
     * Get server information
     */
    private function getServerInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? '',
            'server_name' => $_SERVER['SERVER_NAME'] ?? '',
            'server_port' => $_SERVER['SERVER_PORT'] ?? '',
            'https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'extensions' => get_loaded_extensions()
        ];
    }

    /**
     * Check file permissions
     */
    private function checkPermissions(): array
    {
        $permissions = [];
        $requiredDirs = ['.', 'storage', 'logs', 'cache', 'public/uploads'];
        
        foreach ($requiredDirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            
            $permissions[$dir] = [
                'exists' => is_dir($dir),
                'readable' => is_readable($dir),
                'writable' => is_writable($dir),
                'permissions' => $this->getOctalPermissions($dir)
            ];
        }
        
        return $permissions;
    }

    /**
     * Check if URL rewriting is working
     */
    private function checkUrlRewriting(): bool
    {
        // Simple check - if we can access this script without .php extension
        // and mod_rewrite is available
        return function_exists('apache_get_modules') && 
               in_array('mod_rewrite', apache_get_modules());
    }

    /**
     * Get octal permissions for a file/directory
     */
    private function getOctalPermissions(string $path): string
    {
        if (!file_exists($path)) {
            return 'N/A';
        }
        
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Generate .htaccess content based on hosting environment
     */
    public function generateHtaccess(): string
    {
        $appPath = $this->config['app_path'];
        $rewriteBase = empty($appPath) ? '/' : $appPath . '/';
        
        $content = "# Time2Eat - Auto-generated .htaccess for {$this->config['hosting_type']} hosting\n";
        $content .= "# Generated on " . date('Y-m-d H:i:s') . "\n\n";
        
        $content .= "# Enable URL rewriting\n";
        $content .= "RewriteEngine On\n\n";
        
        if ($this->config['hosting_type'] === 'shared') {
            $content .= "# Shared hosting configuration\n";
            $content .= "RewriteBase {$rewriteBase}\n\n";
        }
        
        $content .= "# Security - Hide sensitive files\n";
        $content .= "<Files \".env\">\n    Require all denied\n</Files>\n";
        $content .= "<Files \"composer.*\">\n    Require all denied\n</Files>\n";
        $content .= "<Files \"*.log\">\n    Require all denied\n</Files>\n\n";
        
        $content .= "# Allow static files\n";
        $content .= "RewriteCond %{REQUEST_FILENAME} -f\n";
        $content .= "RewriteCond %{REQUEST_URI} \\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ [NC]\n";
        $content .= "RewriteRule ^(.*)$ $1 [L]\n\n";
        
        $content .= "# Main rewrite rule\n";
        $content .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
        $content .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
        $content .= "RewriteRule ^(.*)$ index.php [QSA,L]\n\n";
        
        $content .= "# Disable directory browsing\n";
        $content .= "Options -Indexes\n";
        $content .= "Options +FollowSymLinks\n";
        
        return $content;
    }

    /**
     * Generate environment-specific configuration
     */
    public function generateConfig(): array
    {
        return [
            'APP_URL' => $this->config['base_url'],
            'APP_PATH' => $this->config['app_path'],
            'HOSTING_TYPE' => $this->config['hosting_type'],
            'URL_REWRITING' => $this->config['url_rewriting'] ? 'true' : 'false'
        ];
    }

    /**
     * Get configuration array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get detected issues
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /**
     * Get recommendations
     */
    public function getRecommendations(): array
    {
        return $this->recommendations;
    }

    /**
     * Validate hosting compatibility
     */
    public function validate(): bool
    {
        $valid = true;
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            $this->issues[] = 'PHP 8.0+ is required. Current version: ' . PHP_VERSION;
            $valid = false;
        }
        
        // Check required extensions
        $required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'curl', 'gd'];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $this->issues[] = "Required PHP extension missing: {$ext}";
                $valid = false;
            }
        }
        
        // Check permissions
        foreach ($this->config['permissions'] as $dir => $perms) {
            if (!$perms['writable']) {
                $this->issues[] = "Directory not writable: {$dir}";
                $this->recommendations[] = "Set permissions to 755 or 777 for: {$dir}";
                $valid = false;
            }
        }
        
        return $valid;
    }
}
