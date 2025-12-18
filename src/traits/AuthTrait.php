<?php

declare(strict_types=1);

namespace traits;

require_once __DIR__ . '/../helpers/JWTHelper.php';

/**
 * Authentication Trait
 * Provides session and JWT-based authentication
 */
trait AuthTrait
{
    protected ?object $currentUser = null;
    protected ?string $authToken = null;
    
    /**
     * Start session if not already started
     */
    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Load current user from session or JWT
     */
    protected function loadUser(): void
    {
        $this->startSession();
        
        // Try session first
        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            
            // Try to get user from database
            $this->currentUser = $this->getUserById($userId);
            
            // If database fetch fails but session exists, create a minimal user object from session
            // This allows authentication to work even if database is temporarily unavailable
            if (!$this->currentUser && isset($_SESSION['user_role'])) {
                error_log("AuthTrait::loadUser - getUserById failed for user {$userId}, using session fallback");
                $this->currentUser = (object) [
                    'id' => $userId,
                    'email' => $_SESSION['user_email'] ?? '',
                    'username' => $_SESSION['user_name'] ?? '',
                    'first_name' => explode(' ', $_SESSION['user_name'] ?? '')[0] ?? '',
                    'last_name' => explode(' ', $_SESSION['user_name'] ?? '')[1] ?? '',
                    'phone' => null,
                    'role' => $_SESSION['user_role'] ?? 'customer',
                    'status' => 'active',
                    'email_verified_at' => null,
                    'is_available' => false,
                    'balance' => 0,
                    'affiliate_code' => null,
                    'affiliate_rate' => null,
                    'total_earnings' => 0,
                    'referral_count' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            
            // Sync with BaseController's user property if it exists
            if (property_exists($this, 'user')) {
                $this->user = $this->currentUser;
            }
            return;
        }
        
        // Try JWT token
        $token = $this->getAuthToken();
        if ($token) {
            $payload = JWTHelper::decode($token);
            if ($payload && isset($payload['user_id'])) {
                $this->currentUser = $this->getUserById($payload['user_id']);
                $this->authToken = $token;
                // Sync with BaseController's user property if it exists
                if (property_exists($this, 'user')) {
                    $this->user = $this->currentUser;
                }
            }
        }
    }
    
    /**
     * Get auth token from header or cookie
     */
    private function getAuthToken(): ?string
    {
        // Check Authorization header
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }
        
        // Check cookie
        return $_COOKIE['auth_token'] ?? null;
    }
    
    /**
     * Get user by ID
     */
    private function getUserById(int $userId): ?object
    {
        if (!method_exists($this, 'fetchOne')) {
            error_log("AuthTrait::getUserById - fetchOne method not available");
            return null;
        }
        
        try {
            // First, try with status filter (preferred)
            // Temporarily disable strict mode to handle empty TIMESTAMP values
            $originalMode = '';
            try {
                $db = $this->getDb();
                $stmt = $db->query("SELECT @@SESSION.sql_mode as mode");
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $originalMode = $result['mode'] ?? '';
                $db->exec("SET SESSION sql_mode = REPLACE(REPLACE(@@SESSION.sql_mode, 'STRICT_TRANS_TABLES', ''), 'NO_ZERO_DATE', '')");
            } catch (\PDOException $e) {
                error_log("AuthTrait::getUserById - Could not modify sql_mode: " . $e->getMessage());
                // Continue if we can't modify sql_mode
            } catch (\Exception $e) {
                error_log("AuthTrait::getUserById - Error getting sql_mode: " . $e->getMessage());
                // Continue anyway
            }
            
            $userData = $this->fetchOne(
                "SELECT * FROM users WHERE id = ? AND (status = 'active' OR status IS NULL)",
                [$userId]
            );
            
            // If no result, try without status filter (fallback)
            // Handle empty string deleted_at values
            if (!$userData) {
                $userData = $this->fetchOne(
                    "SELECT * FROM users WHERE id = ? AND (
                        deleted_at IS NULL 
                        OR CAST(deleted_at AS CHAR) = '' 
                        OR CAST(deleted_at AS CHAR) = '0000-00-00 00:00:00'
                    )",
                    [$userId]
                );
            }
            
            // Restore sql_mode
            if ($originalMode) {
                try {
                    $this->getDb()->exec("SET SESSION sql_mode = '{$originalMode}'");
                } catch (\PDOException $e) {
                    // Ignore if we can't restore
                }
            }
            
            if (!$userData) {
                error_log("AuthTrait::getUserById - User ID {$userId} not found in database");
                return null;
            }
            
            return (object) [
                'id' => (int)$userData['id'],
                'email' => $userData['email'],
                'username' => $userData['username'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'phone' => $userData['phone'] ?? null,
                'role' => $userData['role'] ?? 'customer',
                'status' => $userData['status'] ?? 'active',
                'email_verified_at' => $userData['email_verified_at'] ?? null,
                'is_available' => isset($userData['is_available']) ? (bool)$userData['is_available'] : false,
                'balance' => $userData['balance'] ?? 0,
                'affiliate_code' => $userData['affiliate_code'] ?? null,
                'affiliate_rate' => $userData['affiliate_rate'] ?? null,
                'total_earnings' => $userData['total_earnings'] ?? 0,
                'referral_count' => $userData['referral_count'] ?? 0,
                'created_at' => $userData['created_at'],
                'updated_at' => $userData['updated_at']
            ];
        } catch (\Exception $e) {
            error_log("Error fetching user by ID {$userId}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Login user with credentials
     */
    protected function login(string $email, string $password, bool $remember = false): array
    {
        if (!method_exists($this, 'fetchOne')) {
            return ['success' => false, 'message' => 'Database not available'];
        }
        
        // Rate limiting
        $this->checkLoginRateLimit($email);
        
        $user = $this->fetchOne(
            "SELECT * FROM users WHERE email = ? AND status = 'active'",
            [$email]
        );
        
        if (!$user || !password_verify($password, $user['password'])) {
            $this->recordFailedLogin($email);
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Update last login
        $this->updateLastLogin($user['id']);
        
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'] ?? 'customer';
        
        // Create JWT token if requested or for API
        $token = null;
        if ($remember || $this->isApiRequest()) {
            $payload = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'customer',
                'exp' => time() + ($remember ? 30 * 24 * 3600 : 24 * 3600) // 30 days or 1 day
            ];
            $token = JWTHelper::encode($payload);
            
            if ($remember) {
                setcookie('auth_token', $token, time() + 30 * 24 * 3600, '/', '', true, true);
            }
        }
        
        $this->currentUser = $this->getUserById($user['id']);
        
        return [
            'success' => true,
            'user' => $this->currentUser,
            'token' => $token
        ];
    }
    
    /**
     * Register new user
     */
    protected function register(array $userData): array
    {
        if (!method_exists($this, 'fetchOne') || !method_exists($this, 'insert')) {
            return ['success' => false, 'message' => 'Database not available'];
        }
        
        // Check if email exists
        $existing = $this->fetchOne("SELECT id FROM users WHERE email = ?", [$userData['email']]);
        if ($existing) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Hash password
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        $userData['status'] = 'active';
        $userData['role'] = $userData['role'] ?? 'customer';
        $userData['created_at'] = date('Y-m-d H:i:s');
        $userData['updated_at'] = date('Y-m-d H:i:s');
        
        try {
            $userId = $this->insertRecord('users', $userData);
            
            // Auto-login after registration
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_role'] = $userData['role'];
            
            $this->currentUser = $this->getUserById($userId);
            
            return [
                'success' => true,
                'user' => $this->currentUser
            ];
        } catch (\Exception $e) {
            error_log("Registration failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    /**
     * Logout user
     */
    protected function logout(): void
    {
        $this->startSession();
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear auth cookie
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', time() - 3600, '/', '', true, true);
        }
        
        $this->currentUser = null;
        $this->authToken = null;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return $this->currentUser !== null;
    }
    
    /**
     * Check if user has specific role
     */
    protected function hasRole(string $role): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        return $this->currentUser->role === $role;
    }
    
    /**
     * Check if user has any of the specified roles
     */
    protected function hasAnyRole(array $roles): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        return in_array($this->currentUser->role, $roles);
    }
    
    /**
     * Check if user is admin
     */
    protected function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
    
    /**
     * Check if user has specific permission
     */
    protected function hasPermission(string $permission): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        return in_array($permission, $this->currentUser->permissions);
    }
    
    /**
     * Check if user can access resource
     */
    protected function canAccess(string $resource, string $action = 'read'): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        $permission = "{$resource}.{$action}";
        return $this->hasPermission($permission) || $this->hasPermission("{$resource}.*");
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken(): string
    {
        $this->startSession();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    protected function verifyCsrfToken(): bool
    {
        $this->startSession();
        
        // For JSON requests, try to get token from JSON body or header
        $token = null;
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            // Try to get CSRF token from JSON body
            $jsonInput = file_get_contents('php://input');
            $jsonData = json_decode($jsonInput, true);
            if (is_array($jsonData) && isset($jsonData['csrf_token'])) {
                $token = $jsonData['csrf_token'];
            }
            // Also check header
            if (!$token && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
        } else {
            // For form POST requests
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }
        
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        
        if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
            if ($this->isAjaxRequest()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'CSRF token mismatch']);
                exit;
            }
            http_response_code(403);
            die('CSRF token mismatch');
        }
        
        return true;
    }
    
    /**
     * Check login rate limiting
     */
    private function checkLoginRateLimit(string $email): void
    {
        $key = "login_attempts:" . md5($email . $_SERVER['REMOTE_ADDR']);
        $attempts = $this->getCacheValue($key, 0);
        
        if ($attempts >= 5) {
            http_response_code(429);
            die('Too many login attempts. Please try again later.');
        }
    }
    
    /**
     * Record failed login attempt
     */
    private function recordFailedLogin(string $email): void
    {
        $key = "login_attempts:" . md5($email . $_SERVER['REMOTE_ADDR']);
        $attempts = $this->getCacheValue($key, 0);
        $this->setCacheValue($key, $attempts + 1, 900); // 15 minutes
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin(int $userId): void
    {
        if (method_exists($this, 'update')) {
            $this->update('users', ['last_login_at' => date('Y-m-d H:i:s')], ['id' => $userId]);
        }
    }
    
    /**
     * Check if request is API request
     */
    private function isApiRequest(): bool
    {
        $path = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($path, '/api/') === 0 || 
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
    
    /**
     * Get current user
     */
    protected function getCurrentUser(): ?object
    {
        return $this->currentUser;
    }
    
    /**
     * Get user ID
     */
    protected function getUserId(): ?int
    {
        return $this->currentUser?->id;
    }
    
    /**
     * Get user role
     */
    protected function getUserRole(): ?string
    {
        return $this->currentUser?->role;
    }
    
    /**
     * Check if user owns resource
     */
    protected function ownsResource(int $resourceUserId): bool
    {
        return $this->isAuthenticated() && $this->currentUser->id === $resourceUserId;
    }
    
    /**
     * Require ownership or admin role
     */
    protected function requireOwnershipOrAdmin(int $resourceUserId): void
    {
        if (!$this->ownsResource($resourceUserId) && !$this->hasRole('admin')) {
            if ($this->isApiRequest()) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                exit;
            } else {
                $this->renderErrorPage(403, 'Access Denied');
            }
        }
    }
}
