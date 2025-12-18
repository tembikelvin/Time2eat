<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/AdminBaseController.php';

use controllers\AdminBaseController;

/**
 * Admin Tools Controller
 * Handles advanced admin dashboard tools including analytics, approvals, backups, and notifications
 */
class AdminToolsController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Analytics Dashboard
     */
    public function analytics(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        $period = $_GET['period'] ?? '30days';

        try {
            // Get basic analytics data
            $analyticsData = $this->getAnalyticsData($period);

            $this->renderDashboard('admin/tools/analytics', [
                'title' => 'Advanced Analytics - Time2Eat Admin',
                'user' => $userData,
                'analyticsData' => $analyticsData,
                'currentPeriod' => $period,
                'currentPage' => 'analytics'
            ]);

        } catch (\Exception $e) {
            error_log('Analytics dashboard error: ' . $e->getMessage());

            $this->renderDashboard('admin/tools/analytics', [
                'title' => 'Advanced Analytics - Time2Eat Admin',
                'user' => $userData,
                'error' => 'Failed to load analytics data',
                'currentPeriod' => $period,
                'currentPage' => 'analytics'
            ]);
        }
    }



    /**
     * Database Backup Management
     */
    public function backups(): void
    {
        // Handle POST requests for backup operations
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Skip CSRF check for backup operations
            $_SESSION['skip_csrf_check'] = true;
            
            // Debug logging
            error_log("Backup POST request - Session: " . json_encode($_SESSION ?? []));
            error_log("Backup POST request - User: " . json_encode($this->getCurrentUser()));
            
            // Check authentication for AJAX requests
            if (!$this->isAuthenticated()) {
                error_log("Backup POST - Not authenticated");
                $this->jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
                return;
            }

            $user = $this->getCurrentUser();
            if (!$user || $user->role !== 'admin') {
                error_log("Backup POST - Not admin. User role: " . ($user->role ?? 'null'));
                $this->jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
                return;
            }

            error_log("Backup POST - Authentication successful, proceeding with backup action");
            $this->handleBackupAction();
            return;
        }

        // Handle GET requests for page display
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get backup settings (simplified)
        $backupSettings = [
            'auto_backup_enabled' => true,
            'backup_frequency' => 'daily',
            'backup_retention_days' => 30,
            'backup_location' => '/backups/',
            'last_backup' => date('Y-m-d H:i:s')
        ];

        // Get backup list (simplified)
        $backups = $this->getBackupList();

        $this->renderDashboard('admin/tools/backups', [
            'title' => 'Database Backup Management - Time2Eat Admin',
                'user' => $userData,
            'backups' => $backups,
            'backupSettings' => $backupSettings,
            'currentPage' => 'backups'
        ]);
    }

    /**
     * Handle backup actions (create, download, restore, delete)
     */
    private function handleBackupAction(): void
    {
        $action = $_POST['action'] ?? '';
        
        // Log the action for debugging
        error_log("Backup action requested: " . $action);
        
        try {
            switch ($action) {
                case 'create_backup':
                    $this->createBackup();
                    break;
                case 'download_backup':
                    $this->downloadBackup();
                    break;
                case 'restore_backup':
                    $this->restoreBackup();
                    break;
                case 'delete_backup':
                    $this->deleteBackup();
                    break;
                default:
                    error_log("Invalid backup action: " . $action);
                    $this->jsonResponse(['success' => false, 'message' => 'Invalid action: ' . $action]);
            }
        } catch (\Exception $e) {
            error_log("Backup action error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    /**
     * Create a new database backup
     */
    private function createBackup(): void
    {
        try {
            error_log("Starting backup creation process");
            
            // Create backups directory if it doesn't exist
            $backupDir = STORAGE_PATH . '/backups';
            if (!is_dir($backupDir)) {
                error_log("Creating backup directory: " . $backupDir);
                mkdir($backupDir, 0755, true);
            }

            // Generate backup filename
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupDir . '/' . $filename;

            // Get database configuration from environment
            $dbConfig = [
                'host' => defined('DB_HOST') ? DB_HOST : ($_ENV['DB_HOST'] ?? 'localhost'),
                'database' => defined('DB_NAME') ? DB_NAME : ($_ENV['DB_NAME'] ?? 'time2eat'),
                'username' => defined('DB_USER') ? DB_USER : ($_ENV['DB_USER'] ?? 'root'),
                'password' => defined('DB_PASS') ? DB_PASS : ($_ENV['DB_PASS'] ?? '')
            ];

            // Try mysqldump first, fallback to PHP-based backup
            $success = $this->createBackupWithMysqldump($dbConfig, $filepath);
            
            if (!$success) {
                // Fallback to PHP-based backup
                $this->createBackupWithPHP($dbConfig, $filepath);
            }

            if (!file_exists($filepath) || filesize($filepath) === 0) {
                throw new \Exception('Backup file was not created or is empty.');
            }

            // Log backup creation
            error_log("Backup created successfully: $filename");
            error_log("Backup file size: " . filesize($filepath) . " bytes");

            $this->jsonResponse([
                'success' => true,
                'message' => 'Backup created successfully',
                'filename' => $filename,
                'size' => $this->formatBytes(filesize($filepath))
            ]);

        } catch (\Exception $e) {
            error_log("Backup creation error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create backup using mysqldump
     */
    private function createBackupWithMysqldump($dbConfig, $filepath): bool
    {
        try {
            // Try different MySQL paths for WAMP and production
            $mysqlPaths = [
                // WAMP development paths
                'E:\\Wamp64\\bin\\mysql\\mysql5.7.44\\bin\\mysqldump',
                'E:\\Wamp64\\bin\\mysql\\mysql9.1.0\\bin\\mysqldump',
                'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump',
                'C:\\wamp64\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump',
                // Production paths
                '/usr/bin/mysqldump',
                '/usr/local/bin/mysqldump',
                '/opt/mysql/bin/mysqldump',
                // System PATH
                'mysqldump'
            ];

            $mysqldumpPath = null;
            foreach ($mysqlPaths as $path) {
                if (is_file($path) || $this->commandExists($path)) {
                    $mysqldumpPath = $path;
                    break;
                }
            }

            if (!$mysqldumpPath) {
                return false;
            }

            // Create mysqldump command
            $command = sprintf(
                '"%s" --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                $mysqldumpPath,
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($filepath)
            );

            // Execute backup command
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            return $returnCode === 0 && file_exists($filepath) && filesize($filepath) > 0;

        } catch (\Exception $e) {
            error_log("Mysqldump backup failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create backup using PHP PDO (fallback method)
     */
    private function createBackupWithPHP($dbConfig, $filepath): void
    {
        try {
            // Create PDO connection
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
            $pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);

            $backup = fopen($filepath, 'w');
            if (!$backup) {
                throw new \Exception('Cannot create backup file');
            }

            // Write header
            fwrite($backup, "-- Database Backup\n");
            fwrite($backup, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($backup, "-- Database: {$dbConfig['database']}\n\n");
            fwrite($backup, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Get table structure
                fwrite($backup, "-- Table structure for table `$table`\n");
                fwrite($backup, "DROP TABLE IF EXISTS `$table`;\n");
                
                $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
                fwrite($backup, $createTable['Create Table'] . ";\n\n");

                // Get table data
                fwrite($backup, "-- Data for table `$table`\n");
                
                $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll();
                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';
                    
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                // Cast to string to handle integers and other types
                                $values[] = "'" . addslashes((string)$value) . "'";
                            }
                        }
                        
                        fwrite($backup, "INSERT INTO `$table` ($columnList) VALUES (" . implode(', ', $values) . ");\n");
                    }
                }
                fwrite($backup, "\n");
            }

            fwrite($backup, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($backup);

        } catch (\Exception $e) {
            error_log("PHP backup creation failed: " . $e->getMessage());
            throw new \Exception('Failed to create backup using PHP method: ' . $e->getMessage());
        }
    }

    /**
     * Check if a command exists
     */
    private function commandExists($command): bool
    {
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';
        $process = proc_open(
            "$whereIsCommand $command",
            array(
                0 => array("pipe", "r"), // stdin
                1 => array("pipe", "w"), // stdout
                2 => array("pipe", "w"), // stderr
            ),
            $pipes
        );
        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return $stdout != '';
        }
        return false;
    }

    /**
     * Download a backup file
     */
    private function downloadBackup(): void
    {
        $filename = $_POST['filename'] ?? '';
        
        if (empty($filename)) {
            $this->jsonResponse(['success' => false, 'message' => 'No filename provided']);
            return;
        }

        $backupDir = STORAGE_PATH . '/backups';
        $filepath = $backupDir . '/' . basename($filename);

        if (!file_exists($filepath)) {
            $this->jsonResponse(['success' => false, 'message' => 'Backup file not found']);
                return;
            }

        // Set headers for file download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        // Output file
        readfile($filepath);
        exit;
    }

    /**
     * Restore a backup
     */
    private function restoreBackup(): void
    {
        $filename = $_POST['filename'] ?? '';
        
        if (empty($filename)) {
            $this->jsonResponse(['success' => false, 'message' => 'No filename provided']);
            return;
        }

        $backupDir = STORAGE_PATH . '/backups';
        $filepath = $backupDir . '/' . basename($filename);

        if (!file_exists($filepath)) {
            $this->jsonResponse(['success' => false, 'message' => 'Backup file not found']);
            return;
        }

        try {
            // Get database configuration
            $db = Database::getInstance();
            $connection = $db->getConnection();
            
            // Get database config from connection
            $dbConfig = [
                'host' => defined('DB_HOST') ? DB_HOST : ($_ENV['DB_HOST'] ?? 'localhost'),
                'database' => defined('DB_NAME') ? DB_NAME : ($_ENV['DB_NAME'] ?? 'time2eat'),
                'username' => defined('DB_USER') ? DB_USER : ($_ENV['DB_USER'] ?? 'root'),
                'password' => defined('DB_PASS') ? DB_PASS : ($_ENV['DB_PASS'] ?? '')
            ];

            // Try different MySQL paths for WAMP
            $mysqlPaths = [
                'E:\\Wamp64\\bin\\mysql\\mysql5.7.44\\bin\\mysql',
                'E:\\Wamp64\\bin\\mysql\\mysql9.1.0\\bin\\mysql',
                'mysql' // System PATH
            ];

            $mysqlPath = null;
            foreach ($mysqlPaths as $path) {
                if (is_file($path) || $this->commandExists($path)) {
                    $mysqlPath = $path;
                    break;
                }
            }

            if (!$mysqlPath) {
                throw new \Exception('MySQL client not found. Please ensure MySQL is properly installed.');
            }

            // Create mysql command to restore
            $command = sprintf(
                '"%s" --host=%s --user=%s --password=%s %s < %s',
                $mysqlPath,
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($filepath)
            );

            // Execute restore command
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Failed to restore backup. Check database credentials and file integrity.');
            }

            error_log("Backup restored successfully: $filename");

            $this->jsonResponse([
                'success' => true,
                'message' => 'Database restored successfully from backup'
            ]);

        } catch (\Exception $e) {
            error_log("Backup restore error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete a backup file
     */
    private function deleteBackup(): void
    {
        $filename = $_POST['filename'] ?? '';
        
        if (empty($filename)) {
            $this->jsonResponse(['success' => false, 'message' => 'No filename provided']);
            return;
        }

        $backupDir = STORAGE_PATH . '/backups';
        $filepath = $backupDir . '/' . basename($filename);

        if (!file_exists($filepath)) {
            $this->jsonResponse(['success' => false, 'message' => 'Backup file not found']);
            return;
        }

        if (unlink($filepath)) {
            error_log("Backup deleted successfully: $filename");
            $this->jsonResponse(['success' => true, 'message' => 'Backup deleted successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete backup file']);
        }
    }

    /**
     * Get list of backup files
     */
    private function getBackupList(): array
    {
        $backups = [];
        $backupDir = STORAGE_PATH . '/backups';

        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/backup_*.sql');
            
            foreach ($files as $file) {
                $filename = basename($file);
                $backups[] = [
                    'filename' => $filename,
                    'size' => $this->formatBytes(filesize($file)),
                    'size_bytes' => filesize($file),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'type' => 'manual'
                ];
            }

            // Sort by creation date (newest first)
            usort($backups, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }

        return $backups;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Popup Notifications Management
     */
    public function notifications(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get notification data from database
        $activeNotifications = [];
        $scheduledNotifications = [];
        $notificationStats = [
            'total_notifications' => 0,
            'active_count' => 0,
            'scheduled_count' => 0,
            'click_rate' => 0
        ];

        try {
            // Get active notifications
            $activeNotifications = $this->fetchAll("
                SELECT pn.*, u.first_name, u.last_name
                FROM popup_notifications pn
                LEFT JOIN users u ON pn.created_by = u.id
                WHERE pn.is_active = 1 
                AND (pn.start_date IS NULL OR pn.start_date <= NOW())
                AND (pn.end_date IS NULL OR pn.end_date >= NOW())
                ORDER BY pn.priority DESC, pn.created_at DESC
                LIMIT 20
            ");

            // Get scheduled notifications
            $scheduledNotifications = $this->fetchAll("
                SELECT pn.*, u.first_name, u.last_name
                FROM popup_notifications pn
                LEFT JOIN users u ON pn.created_by = u.id
                WHERE pn.is_active = 1 
                AND pn.start_date > NOW()
                ORDER BY pn.start_date ASC
                LIMIT 20
            ");

            // Get notification statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM popup_notifications");
            $notificationStats['total_notifications'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM popup_notifications WHERE is_active = 1 AND (start_date IS NULL OR start_date <= NOW()) AND (end_date IS NULL OR end_date >= NOW())");
            $notificationStats['active_count'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM popup_notifications WHERE is_active = 1 AND start_date > NOW()");
            $notificationStats['scheduled_count'] = $result['count'] ?? 0;

        } catch (\Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
        }

        $this->renderDashboard('admin/tools/notifications', [
            'title' => 'Notification Management - Time2Eat Admin',
            'user' => $userData,
            'notifications' => [],
            'activeNotifications' => $activeNotifications,
            'scheduledNotifications' => $scheduledNotifications,
            'notificationStats' => $notificationStats,
            'currentPage' => 'notifications'
        ]);
    }

    /**
     * Notify user of approval
     */
    private function notifyUserOfApproval(array $user): void
    {
        try {
            // Get the current admin user ID from session, fallback to finding first admin
            $adminId = $_SESSION['user_id'] ?? null;
            
            if (!$adminId) {
                // Get first admin user as fallback
                $admin = $this->fetchOne("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                $adminId = $admin['id'] ?? null;
            }
            
            // Only create notification if we have a valid admin ID
            if ($adminId) {
                $this->insertRecord('popup_notifications', [
                    'title' => 'Application Approved',
                    'message' => "Congratulations! Your {$user['role']} application has been approved. You can now log in and access your dashboard.",
                    'type' => 'success',
                    'target_audience' => 'all',
                    'target_user_id' => $user['id'],
                    'priority' => 4,
                    'action_url' => '/login',
                    'action_text' => 'Login Now',
                    'created_by' => $adminId
                ]);
            } else {
                error_log("Cannot create approval notification: No admin user found");
            }
        } catch (\Exception $e) {
            error_log("Failed to notify user of approval: " . $e->getMessage());
        }
    }

    /**
     * Notify user of rejection
     */
    private function notifyUserOfRejection(array $user, string $reason): void
    {
        try {
            // Get the current admin user ID from session, fallback to finding first admin
            $adminId = $_SESSION['user_id'] ?? null;
            
            if (!$adminId) {
                // Get first admin user as fallback
                $admin = $this->fetchOne("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                $adminId = $admin['id'] ?? null;
            }
            
            // Only create notification if we have a valid admin ID
            if ($adminId) {
                $this->insertRecord('popup_notifications', [
                    'title' => 'Application Rejected',
                    'message' => "Your {$user['role']} application has been rejected. Reason: {$reason}. You can contact support for more information.",
                    'type' => 'error',
                    'target_audience' => 'all',
                    'target_user_id' => $user['id'],
                    'priority' => 4,
                    'action_url' => '/contact',
                    'action_text' => 'Contact Support',
                    'created_by' => $adminId
                ]);
            } else {
                error_log("Cannot create rejection notification: No admin user found");
            }
        } catch (\Exception $e) {
            error_log("Failed to notify user of rejection: " . $e->getMessage());
        }
    }

    /**
     * View Notifications (User-facing notifications page)
     */
    public function viewNotifications(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect(url('/login'));
            return;
        }

        try {
            $user = $this->getCurrentUser();
            
            // Get all notifications for the admin user
            $notifications = $this->fetchAll("
                SELECT n.*, 
                       CASE 
                           WHEN n.created_by IS NOT NULL THEN CONCAT(u.first_name, ' ', u.last_name)
                           ELSE 'System'
                       END as created_by_name
                FROM notifications n
                LEFT JOIN users u ON n.created_by = u.id
                WHERE n.user_id = ? OR n.user_id IS NULL
                ORDER BY n.created_at DESC
                LIMIT 50
            ", [$user->id]);

            // Get notification statistics
            $stats = $this->fetchOne("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN n.read_at IS NULL THEN 1 END) as unread,
                    COUNT(CASE WHEN n.priority = 'urgent' AND n.read_at IS NULL THEN 1 END) as urgent_unread,
                    COUNT(CASE WHEN n.type = 'order_update' THEN 1 END) as order_updates,
                    COUNT(CASE WHEN n.type = 'system_alert' THEN 1 END) as system_alerts
                FROM notifications n
                WHERE n.user_id = ? OR n.user_id IS NULL
            ", [$user->id]);

            // Get recent activity for context
            $recentActivity = $this->fetchAll("
                SELECT 'order' as type, o.id, o.status, o.created_at, r.name as restaurant_name
                FROM orders o
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY o.created_at DESC
                LIMIT 10
            ");

            $this->renderDashboard('admin/notifications', [
                'notifications' => $notifications,
                'stats' => $stats ?: ['total' => 0, 'unread' => 0, 'urgent_unread' => 0, 'order_updates' => 0, 'system_alerts' => 0],
                'recentActivity' => $recentActivity,
                'currentPage' => 'notifications'
            ]);

        } catch (\Exception $e) {
            error_log("Error loading notifications: " . $e->getMessage());
            $this->renderDashboard('admin/notifications', [
                'notifications' => [],
                'stats' => ['total' => 0, 'unread' => 0, 'urgent_unread' => 0, 'order_updates' => 0, 'system_alerts' => 0],
                'recentActivity' => [],
                'error' => 'Failed to load notifications',
                'currentPage' => 'notifications'
            ]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $notificationId = $_POST['notification_id'] ?? null;
            if (!$notificationId) {
                $this->jsonResponse(['success' => false, 'message' => 'Notification ID required']);
                return;
            }

            $user = $this->getCurrentUser();
            
            $result = $this->execute("
                UPDATE notifications 
                SET read_at = NOW(), status = 'read'
                WHERE id = ? AND (user_id = ? OR user_id IS NULL)
            ", [$notificationId, $user->id]);

            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to mark notification as read']);
            }
        } catch (\Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $user = $this->getCurrentUser();
            
            $result = $this->execute("
                UPDATE notifications 
                SET read_at = NOW(), status = 'read'
                WHERE (user_id = ? OR user_id IS NULL) AND read_at IS NULL
            ", [$user->id]);

            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to mark notifications as read']);
            }
        } catch (\Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Delete notification (handles both notifications and popup_notifications)
     */
    public function deleteNotification(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $notificationId = $_POST['notification_id'] ?? null;
            if (!$notificationId) {
                $this->jsonResponse(['success' => false, 'message' => 'Notification ID required']);
                return;
            }

            $user = $this->getCurrentUser();
            
            // Try to delete from notifications table first (new system)
            $result = $this->execute("
                DELETE FROM notifications 
                WHERE id = ? AND (user_id = ? OR user_id IS NULL)
            ", [$notificationId, $user->id]);

            // If not found in notifications table, try popup_notifications table (old system)
            if (!$result) {
                $result = $this->execute("DELETE FROM popup_notifications WHERE id = ?", [$notificationId]);
            }

            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => 'Notification deleted']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to delete notification']);
            }
        } catch (\Exception $e) {
            error_log("Error deleting notification: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Create New Notification
     */
    public function createNotification(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $user = $this->getCurrentUser();
            
            $title = $_POST['title'] ?? '';
            $message = $_POST['message'] ?? '';
            $type = $_POST['type'] ?? 'info';
            $targetAudience = $_POST['target_audience'] ?? 'all';
            $priority = (int)($_POST['priority'] ?? 1);
            $startDate = $_POST['start_date'] ?? null;
            $endDate = $_POST['end_date'] ?? null;
            $actionUrl = $_POST['action_url'] ?? null;
            $actionText = $_POST['action_text'] ?? null;
            $maxDisplays = $_POST['max_displays'] ? (int)$_POST['max_displays'] : null;

            // Validation
            if (empty($title) || empty($message)) {
                $this->jsonResponse(['success' => false, 'message' => 'Title and message are required']);
                return;
            }

            // Insert notification
            $result = $this->execute("
                INSERT INTO popup_notifications 
                (title, message, type, target_audience, priority, start_date, end_date, action_url, action_text, max_displays, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $title, $message, $type, $targetAudience, $priority, 
                $startDate, $endDate, $actionUrl, $actionText, $maxDisplays, $user->id
            ]);

            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => 'Notification created successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to create notification']);
            }
        } catch (\Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while creating notification']);
        }
    }

    /**
     * Update Notification
     */
    public function updateNotification(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $notificationId = $_POST['notification_id'] ?? null;
            if (!$notificationId) {
                $this->jsonResponse(['success' => false, 'message' => 'Notification ID required']);
                return;
            }

            $title = $_POST['title'] ?? '';
            $message = $_POST['message'] ?? '';
            $type = $_POST['type'] ?? 'info';
            $targetAudience = $_POST['target_audience'] ?? 'all';
            $priority = (int)($_POST['priority'] ?? 1);
            $startDate = $_POST['start_date'] ?? null;
            $endDate = $_POST['end_date'] ?? null;
            $actionUrl = $_POST['action_url'] ?? null;
            $actionText = $_POST['action_text'] ?? null;
            $maxDisplays = $_POST['max_displays'] ? (int)$_POST['max_displays'] : null;
            $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

            // Validation
            if (empty($title) || empty($message)) {
                $this->jsonResponse(['success' => false, 'message' => 'Title and message are required']);
                return;
            }

            // Update notification
            $result = $this->execute("
                UPDATE popup_notifications 
                SET title = ?, message = ?, type = ?, target_audience = ?, priority = ?, 
                    start_date = ?, end_date = ?, action_url = ?, action_text = ?, 
                    max_displays = ?, is_active = ?
                WHERE id = ?
            ", [
                $title, $message, $type, $targetAudience, $priority, 
                $startDate, $endDate, $actionUrl, $actionText, $maxDisplays, $isActive, $notificationId
            ]);

            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => 'Notification updated successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update notification']);
            }
        } catch (\Exception $e) {
            error_log("Error updating notification: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while updating notification']);
        }
    }


    /**
     * Toggle Notification Status
     */
    public function toggleNotification(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $notificationId = $_POST['notification_id'] ?? null;
            if (!$notificationId) {
                $this->jsonResponse(['success' => false, 'message' => 'Notification ID required']);
                return;
            }

            // Toggle notification status
            $result = $this->execute("UPDATE popup_notifications SET is_active = NOT is_active WHERE id = ?", [$notificationId]);

            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => 'Notification status updated successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update notification status']);
            }
        } catch (\Exception $e) {
            error_log("Error toggling notification: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while updating notification status']);
        }
    }

    /**
     * Site Settings and Contact Information
     */
    public function settings(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user->role !== 'admin') {
            $this->redirect(url('/login'));
            return;
        }

        // Convert user object to array for view compatibility
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status
        ];

        // Get site settings (simplified)
        $settings = [
            'site_name' => 'Time2Eat',
            'site_description' => 'Food Delivery Platform',
            'maintenance_mode' => false,
            'registration_enabled' => true,
            'email_verification' => true
        ];

        $contactSettings = [
            'contact_email' => 'admin@time2eat.com',
            'contact_phone' => '+1234567890',
            'contact_address' => '123 Main St, City, Country',
            'support_hours' => '9 AM - 6 PM'
        ];

        $this->renderDashboard('admin/tools/settings', [
            'title' => 'Site Settings - Time2Eat Admin',
            'user' => $userData,
            'settings' => $settings,
            'contactSettings' => $contactSettings,
            'currentPage' => 'settings'
        ]);
    }

    /**
     * Get analytics data for specified period
     */
    private function getAnalyticsData(string $period): array
    {
        // Simplified analytics data
        return [
            'totalUsers' => 0,
            'totalOrders' => 0,
            'totalRevenue' => 0,
            'activeRestaurants' => 0,
            'period' => $period
        ];
    }

}
