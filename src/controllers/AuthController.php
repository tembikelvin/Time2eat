<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/JWTHelper.php';

use core\BaseController;
use models\User;

/**
 * Authentication Controller for Time2Eat
 * Handles user authentication, registration, and password management
 *
 * @package Time2Eat
 * @author Time2Eat Development Team
 * @version 2.0.0
 */
class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Show login form
     */
    public function showLogin(): void
    {
        // CRITICAL: Prevent caching of login page (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Redirect if already logged in
        if ($this->isAuthenticated()) {
            $user = $this->getCurrentUser();
            $this->redirect($this->getRedirectUrl($user->role));
            return;
        }

        $this->render('auth/login', [
            'title' => 'Login - Time2Eat',
            'page' => 'login'
        ]);
    }

    /**
     * Process login form submission
     */
    public function processLogin(): void
    {
        // CRITICAL: Prevent caching of login response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Redirect if already logged in
        if ($this->isAuthenticated()) {
            $user = $this->getCurrentUser();
            $this->redirect($this->getRedirectUrl($user->role));
            return;
        }

        // Initialize security services
        require_once __DIR__ . '/../security/SecurityManager.php';
        $security = \SecurityManager::getInstance();

        // Validate CSRF token
        $this->startSession();
        $token = $_POST['csrf_token'] ?? null;
        $sessionToken = $_SESSION['csrf_token'] ?? null;

        if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
            error_log("CSRF token validation failed for login attempt");
            $this->render('auth/login', [
                'title' => 'Login - Time2Eat',
                'page' => 'login',
                'error' => 'Security token mismatch. Please refresh the page and try again.',
            ]);
            return;
        }

        // Check rate limiting
        if (!$security->checkRateLimit('login_attempts')) {
            $this->render('auth/login', [
                'title' => 'Login - Time2Eat',
                'page' => 'login',
                'error' => 'Too many login attempts. Please try again later.',
            ]);
            return;
        }

        // Sanitize and validate input
        $email = $security->sanitizeInput($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);

        if (empty($email) || empty($password)) {
            $this->render('auth/login', [
                'title' => 'Login - Time2Eat',
                'page' => 'login',
                'error' => 'Email and password are required.',
                'old' => $_POST,
            ]);
            return;
        }

        // Find user
        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'] ?? '')) {
            // Log failed login attempt
            $this->logSecurityEvent('login_failed', [
                'email' => $email,
                'ip' => $this->getClientIp(),
                'reason' => 'Invalid credentials',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);

            $this->render('auth/login', [
                'title' => 'Login - Time2Eat',
                'page' => 'login',
                'error' => 'Invalid email or password.',
                'old' => $_POST
            ]);
            return;
        }

        // Check if user account is active
        if ($user['status'] !== 'active') {
            $message = match($user['status']) {
                'suspended' => 'Your account has been suspended. Please contact support.',
                'pending' => 'Your account is pending approval.',
                'inactive' => 'Your account is inactive. Please contact support.',
                default => 'Your account is not active. Please contact support.'
            };

            $this->render('auth/login', [
                'title' => 'Login - Time2Eat',
                'page' => 'login',
                'error' => $message,
                'old' => $_POST
            ]);
            return;
        }

        // Check email verification if required BEFORE creating session
        require_once __DIR__ . '/../helpers/auth_helpers.php';
        $emailVerificationRequired = isEmailVerificationRequired();

        if ($emailVerificationRequired && empty($user['email_verified_at'])) {
            // Email verification is required but not completed
            // Show error on login page instead of creating session
            $this->render('auth/login', [
                'title' => 'Login - Time2Eat',
                'page' => 'login',
                'error' => 'Please verify your email address before logging in. Check your email for the verification link.',
                'old' => $_POST,
                'email_verification_required' => true,
                'email' => $email
            ]);
            return;
        }

        // Login successful - create session
        $this->createUserSession($user);

        // Handle remember me
        if ($remember) {
            $this->createRememberToken($user['id']);
        }

        // Update last login
        $this->userModel->updateUser((int)$user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $this->getClientIp()
        ]);

        // Log successful login
        $this->logSecurityEvent('login_success', [
            'user_id' => $user['id'],
            'email' => $email,
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        // Redirect to appropriate dashboard
        $this->redirect($this->getRedirectUrl($user['role']));
    }

    /**
     * Show registration form
     */
    public function showRegister(): void
    {
        // CRITICAL: Prevent caching of registration page (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        try {
            error_log("=== SHOW REGISTER STARTED ===");

            // Redirect if already logged in
            if ($this->isAuthenticated()) {
                error_log("User already authenticated, redirecting to dashboard");
                $user = $this->getCurrentUser();
                $this->redirect($this->getRedirectUrl($user->role));
                return;
            }

            // Check if registration is enabled
            require_once __DIR__ . '/../helpers/auth_helpers.php';
            if (!isRegistrationEnabled()) {
                error_log("Registration is disabled in admin settings");
                $this->render('auth/register', [
                    'title' => 'Register - Time2Eat',
                    'page' => 'register',
                    'error' => 'Registration is currently disabled. Please try again later or contact support.'
                ]);
                return;
            }

            error_log("Rendering registration page");
            $this->render('auth/register', [
                'title' => 'Register - Time2Eat',
                'page' => 'register'
            ]);
        } catch (\Exception $e) {
            error_log("FATAL ERROR in showRegister: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

            // Show a simple error page
            echo "<h1>Registration Error</h1>";
            echo "<p>An error occurred while loading the registration page. Please try again later.</p>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    /**
     * Process registration form submission
     */
    public function processRegister(): void
    {
        // CRITICAL: Prevent caching of registration response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Add comprehensive error handling
        try {
            error_log("=== REGISTRATION PROCESS STARTED ===");
            error_log("POST data: " . json_encode($_POST));
            error_log("Session ID: " . session_id());
            error_log("Request method: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
            error_log("Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));

            // Redirect if already logged in
            if ($this->isAuthenticated()) {
                error_log("User already authenticated, redirecting to dashboard");
                $user = $this->getCurrentUser();
                $this->redirect($this->getRedirectUrl($user->role));
                return;
            }
        } catch (\Exception $e) {
            error_log("FATAL ERROR in processRegister: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->render('auth/register', [
                'title' => 'Register - Time2Eat',
                'page' => 'register',
                'error' => 'An unexpected error occurred. Please try again.',
            ]);
            return;
        }

        // Check if registration is enabled
        require_once __DIR__ . '/../helpers/auth_helpers.php';
        if (!isRegistrationEnabled()) {
            error_log("Registration is disabled in admin settings");
            $this->render('auth/register', [
                'title' => 'Register - Time2Eat',
                'page' => 'register',
                'error' => 'Registration is currently disabled. Please try again later or contact support.',
            ]);
            return;
        }

        // Initialize security services
        require_once __DIR__ . '/../security/SecurityManager.php';
        require_once __DIR__ . '/../services/ErrorReportingService.php';
        require_once __DIR__ . '/../services/EmailVerificationService.php';

        $security = \SecurityManager::getInstance();
        $errorReporting = \ErrorReportingService::getInstance();
        $emailService = new \services\EmailVerificationService();

        // Validate CSRF token
        $this->startSession();
        $token = $_POST['csrf_token'] ?? null;
        $sessionToken = $_SESSION['csrf_token'] ?? null;

        // Debug logging for CSRF token validation
        error_log("CSRF Token Validation Debug:");
        error_log("  - POST token: " . ($token ? substr($token, 0, 16) . '...' : 'NULL'));
        error_log("  - Session token: " . ($sessionToken ? substr($sessionToken, 0, 16) . '...' : 'NULL'));
        error_log("  - Tokens match: " . ($token && $sessionToken && hash_equals($sessionToken, $token) ? 'YES' : 'NO'));

        if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
            error_log("CSRF token validation failed for registration attempt");
            $this->render('auth/register', [
                'title' => 'Register - Time2Eat',
                'page' => 'register',
                'error' => 'Security token mismatch. Please refresh the page and try again.',
            ]);
            return;
        }

        // Check rate limiting
        error_log("Checking rate limit for registration...");
        try {
            $rateLimitResult = $security->checkRateLimit('registration');
            error_log("Rate limit check result: " . ($rateLimitResult ? 'PASS' : 'FAIL'));
            
            if (!$rateLimitResult) {
                error_log("Rate limit exceeded for registration");
                $this->render('auth/register', [
                    'title' => 'Register - Time2Eat',
                    'page' => 'register',
                    'error' => 'Too many registration attempts. Please try again later.',
                ]);
                return;
            }
        } catch (\Exception $e) {
            error_log("Rate limit check failed with exception: " . $e->getMessage());
            $this->render('auth/register', [
                'title' => 'Register - Time2Eat',
                'page' => 'register',
                'error' => 'Registration temporarily unavailable. Please try again later.',
            ]);
            return;
        }

        // Sanitize and validate input
        $email = $security->sanitizeInput($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        // Support both 'password_confirmation' and 'confirm_password' field names
        $confirmPassword = $_POST['password_confirmation'] ?? $_POST['confirm_password'] ?? '';
        $firstName = $security->sanitizeInput($_POST['first_name'] ?? '', 'string');
        $lastName = $security->sanitizeInput($_POST['last_name'] ?? '', 'string');
        $phone = $security->sanitizeInput($_POST['phone'] ?? '', 'string');
        $role = $_POST['role'] ?? 'customer';
        // Support both 'affiliate_code' and 'referral_code' field names
        $affiliateCode = $security->sanitizeInput($_POST['affiliate_code'] ?? $_POST['referral_code'] ?? '', 'string');

        // Validation errors array
        $errors = [];

        // Validate required fields
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (empty($firstName)) {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($lastName)) {
            $errors['last_name'] = 'Last name is required';
        }

        if (empty($phone)) {
            $errors['phone'] = 'Phone number is required';
        }

        // Validate role (form uses 'vendor' but database uses 'vendor')
        $allowedRoles = ['customer', 'rider', 'vendor'];
        if (!in_array($role, $allowedRoles)) {
            $role = 'customer';
        }

        // Check if errors exist
        if (!empty($errors)) {
            $this->render('auth/register', [
                'title' => 'Register - Time2Eat',
                'page' => 'register',
                'errors' => $errors,
                'old' => $_POST
            ]);
            return;
        }

        // Check if email already exists
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser) {
            error_log("Registration attempt with existing email: {$email}");
            $this->render('auth/register', [
                'title' => 'Register - Time2Eat',
                'page' => 'register',
                'error' => 'This email is already registered. Please <a href="' . url('/login') . '" class="tw-underline tw-font-semibold">login here</a> instead.',
                'old' => $_POST
            ]);
            return;
        }

        // Handle affiliate code
        $referrerId = null;
        if (!empty($affiliateCode)) {
            $referrer = $this->userModel->findByAffiliateCode($affiliateCode);
            if ($referrer) {
                $referrerId = $referrer['id'];
            }
        }

        // Check if email verification is required
        require_once __DIR__ . '/../helpers/auth_helpers.php';
        $emailVerificationRequired = isEmailVerificationRequired();

        // If email verification is required, validate email verification
        if ($emailVerificationRequired) {
            $isVerificationValid = false;

            // First, check session
            if (isset($_SESSION['email_verified']) &&
                strtolower($_SESSION['email_verified']['email']) === strtolower($email) &&
                isset($_SESSION['email_verified']['verification_token']) &&
                isset($_SESSION['email_verified']['expires_at']) &&
                time() <= $_SESSION['email_verified']['expires_at']) {
                $isVerificationValid = true;
                error_log("Email verification valid from session: {$email}");
            }

            // If session verification failed, check database
            if (!$isVerificationValid) {
                try {
                    $db = \Database::getInstance();
                    $stmt = $db->prepare("
                        SELECT * FROM email_verifications
                        WHERE email = ? AND verified_at IS NOT NULL
                        AND expires_at > NOW()
                        ORDER BY verified_at DESC LIMIT 1
                    ");
                    $stmt->execute([$email]);
                    $verification = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ($verification) {
                        $isVerificationValid = true;
                        error_log("Email verification valid from database: {$email}");
                    }
                } catch (\Exception $e) {
                    error_log("Failed to check database for verification: " . $e->getMessage());
                }
            }

            if (!$isVerificationValid) {
                error_log("Email verification failed for: {$email}");

                $this->render('auth/register', [
                    'title' => 'Register - Time2Eat',
                    'page' => 'register',
                    'error' => 'Please verify your email address before completing registration.',
                    'old' => $_POST
                ]);
                return;
            }

            error_log("Email verification passed - proceeding with registration");
        }

        // Generate username and affiliate code
        $username = $this->generateUsername($email);
        $affiliateCode = $this->generateAffiliateCode();

        error_log("Generated username: {$username}");
        error_log("Generated affiliate code: {$affiliateCode}");

        // Create user
        try {
            // Determine user status based on role
            // Vendor and Rider registrations require admin approval, so set status to 'pending'
            // Customer registrations are immediately active
            $userStatus = ($role === 'vendor' || $role === 'rider') ? 'pending' : 'active';

            // If email verification is required, user is already verified at this point
            // If not required, mark as verified immediately
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'role' => $role,
                'status' => $userStatus,
                'referred_by' => $referrerId,
                'affiliate_code' => $affiliateCode,
                'email_verified_at' => date('Y-m-d H:i:s') // Always mark as verified since we've verified it
            ];

            $userId = $this->userModel->create($userData);

            if (!$userId) {
                throw new \Exception('Failed to create user account');
            }

            error_log("User created successfully: {$userId}");

            // Create affiliate record for customers only
            // Only customers can be affiliates - vendors/riders have their own earning systems
            if ($role === 'customer') {
                try {
                    $affiliateInsertSql = "
                        INSERT INTO affiliates (user_id, affiliate_code, commission_rate, total_earnings, pending_earnings, paid_earnings, total_referrals, status, created_at, updated_at)
                        VALUES (?, ?, 5.00, 0.00, 0.00, 0.00, 0, 'active', NOW(), NOW())
                        ON DUPLICATE KEY UPDATE updated_at = NOW()
                    ";
                    $this->query($affiliateInsertSql, [$userId, $affiliateCode]);
                    error_log("Affiliate record created for customer: {$userId} with code: {$affiliateCode}");
                } catch (\Exception $e) {
                    error_log("Failed to create affiliate record for customer {$userId}: " . $e->getMessage());
                    // Don't fail registration if affiliate creation fails
                }
            }

            // If user was referred, update referrer's stats and create referral record
            if ($referrerId) {
                try {
                    // Increment referrer's total_referrals count
                    $updateReferrerSql = "
                        UPDATE affiliates
                        SET total_referrals = total_referrals + 1,
                            updated_at = NOW()
                        WHERE user_id = ?
                    ";
                    $this->query($updateReferrerSql, [$referrerId]);
                    error_log("Incremented referral count for referrer user_id: {$referrerId}");

                    // Get affiliate_id for the referrer
                    $affiliateStmt = $this->fetchOne(
                        "SELECT id FROM affiliates WHERE user_id = ?",
                        [$referrerId]
                    );

                    if ($affiliateStmt) {
                        // Create entry in affiliate_referrals table
                        $referralInsertSql = "
                            INSERT INTO affiliate_referrals (
                                affiliate_id, referred_user_id, status, created_at, updated_at
                            ) VALUES (?, ?, 'pending', NOW(), NOW())
                        ";
                        $this->query($referralInsertSql, [$affiliateStmt['id'], $userId]);
                        error_log("Created affiliate_referrals entry for affiliate_id: {$affiliateStmt['id']}, referred_user_id: {$userId}");
                    }
                } catch (\Exception $e) {
                    error_log("Failed to update referrer stats: " . $e->getMessage());
                    // Don't fail registration if referral tracking fails
                }
            }

            // Log successful registration
            $this->logSecurityEvent('registration_success', [
                'user_id' => $userId,
                'email' => $email,
                'role' => $role,
                'status' => $userStatus,
                'ip' => $this->getClientIp(),
                'email_verification_required' => $emailVerificationRequired
            ]);

            // Handle post-registration flow based on role
            if ($role === 'vendor' || $role === 'rider') {
                // For vendor/rider registrations, show pending approval message
                error_log("Vendor/Rider registration - showing pending approval message");
                $this->render('auth/registration-pending', [
                    'title' => 'Application Pending - Time2Eat',
                    'role' => $role,
                    'email' => $email,
                    'firstName' => $firstName
                ]);
            } else {
                // For customer registrations, auto-login
                $user = $this->userModel->findById($userId);
                if ($user) {
                    error_log("Customer registration - auto-logging in user");
                    $this->createUserSession($user);
                    // Clear email verification session after successful registration to prevent reuse
                    unset($_SESSION['email_verified']);
                    $this->redirect($this->getRedirectUrl($user['role']));
                } else {
                    error_log("User not found after creation - redirecting to login");
                    $this->redirect(url('/login?message=' . urlencode('Registration successful! Please login with your credentials.')));
                }
            }

        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $errorReporting->logError($e);

            $this->render('auth/register', [
                'title' => 'Register - Time2Eat',
                'page' => 'register',
                'error' => 'An error occurred during registration. Please try again.',
                'old' => $_POST
            ]);
        }
    }


    /**
     * Logout user
     */
    public function logout(): void
    {
        // CRITICAL: Prevent caching of logout response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Log logout before clearing session
        if ($this->isAuthenticated()) {
            $user = $this->getCurrentUser();
            $this->logSecurityEvent('logout', [
                'user_id' => $user->id,
                'ip' => $this->getClientIp()
            ]);
        }

        // Clear remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];

            try {
                // Delete token from database
                $this->execute("DELETE FROM remember_tokens WHERE token = ?", [$token]);
            } catch (\Exception $e) {
                error_log("Error deleting remember token: " . $e->getMessage());
            }

            // Clear cookie
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }

        // Clear auth token cookie if exists
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', time() - 3600, '/', '', true, true);
        }

        // Clear session completely
        $this->startSession();
        $_SESSION = [];
        session_unset();
        session_destroy();

        // Start a new session to avoid session issues
        session_start();
        session_regenerate_id(true);

        // Redirect to login
        $this->redirect(url('/login'));
    }


    /**
     * Resend verification email
     */
    public function resendVerification(): void
    {
        if (!$this->isAjaxRequest()) {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = $this->getJsonInput();
        $email = $input['email'] ?? '';

        if (empty($email)) {
            $this->jsonError('Email is required', 400);
            return;
        }

        try {
            // Find user by email
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                $this->jsonError('User not found', 404);
                return;
            }

            // Check if already verified
            if (!empty($user['email_verified_at'])) {
                $this->jsonError('Email already verified', 400);
                return;
            }

            // Resend verification email
            require_once __DIR__ . '/../services/EmailVerificationService.php';
            $emailService = new \services\EmailVerificationService();
            $emailService->sendVerificationEmail($email, $user['first_name']);

            $this->jsonSuccess('Verification email sent successfully');
        } catch (\Exception $e) {
            error_log("Resend verification error: " . $e->getMessage());
            $this->jsonError('Failed to resend verification email', 500);
        }
    }

    /**
     * Helper: Create user session
     */
    private function createUserSession(array $user): void
    {
        $this->startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    }

    /**
     * Helper: Create remember token
     */
    private function createRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        try {
            $this->insertRecord('remember_tokens', [
                'user_id' => $userId,
                'token' => $token,
                'expires_at' => $expires,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
        } catch (\Exception $e) {
            error_log("Error creating remember token: " . $e->getMessage());
        }
    }

    /**
     * Helper: Get redirect URL based on role
     */
    private function getRedirectUrl(string $role): string
    {
        return match($role) {
            'admin' => url('/admin/dashboard'),
            'vendor' => url('/vendor/dashboard'),
            'rider' => url('/rider/dashboard'),
            'customer' => url('/customer/dashboard'),
            default => url('/dashboard')
        };
    }

    /**
     * Helper: Log security events
     */
    private function logSecurityEvent(string $event, array $data): void
    {
        try {
            // Log to error log
            error_log("SECURITY: $event - " . json_encode($data));

            // Try to log to database if possible
            $db = \Database::getInstance();
            if ($db) {
                $logData = [
                    'level' => 'info',
                    'message' => "Security event: {$event}",
                    'context' => json_encode(array_merge($data, [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'session_id' => session_id()
                    ])),
                    'channel' => 'security',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $columns = implode(', ', array_keys($logData));
                $placeholders = implode(', ', array_fill(0, count($logData), '?'));
                $sql = "INSERT INTO logs ({$columns}) VALUES ({$placeholders})";

                $stmt = $db->prepare($sql);
                $stmt->execute(array_values($logData));
            }
        } catch (\Exception $e) {
            error_log("Failed to log security event: " . $e->getMessage());
        }
    }

    /**
     * Helper: Get client IP address
     */
    private function getClientIp(): string
    {
        // Get client IP with fallback options
        $clientIP = $_SERVER['REMOTE_ADDR'] ??
                    $_SERVER['HTTP_X_FORWARDED_FOR'] ??
                    $_SERVER['HTTP_X_REAL_IP'] ??
                    $_SERVER['HTTP_CLIENT_IP'] ??
                    '127.0.0.1';

        // If X-Forwarded-For contains multiple IPs, take the first one
        if (strpos($clientIP, ',') !== false) {
            $clientIP = trim(explode(',', $clientIP)[0]);
        }

        return $clientIP;
    }

    /**
     * Helper: Generate unique username from email
     */
    private function generateUsername(string $email): string
    {
        $baseUsername = strtolower(explode('@', $email)[0]);
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);

        $username = $baseUsername;
        $counter = 1;

        while ($this->userModel->findByUsername($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Helper: Generate unique affiliate code
     */
    private function generateAffiliateCode(): string
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(4)));
        } while ($this->userModel->findByAffiliateCode($code));

        return $code;
    }

    /**
     * Validate referral code via API
     */
    public function validateReferral(): void
    {
        // Set JSON response headers
        header('Content-Type: application/json');
        
        try {
            $referralCode = $_GET['code'] ?? '';
            
            if (empty($referralCode)) {
                $this->jsonResponse([
                    'success' => false,
                    'valid' => false,
                    'message' => 'Referral code is required'
                ], 400);
                return;
            }
            
            // Sanitize the referral code
            $referralCode = $this->sanitizeString($referralCode);
            
            // Check if referral code exists and is valid
            $referrer = $this->fetchOne(
                "SELECT id, first_name, last_name, email, status FROM users WHERE affiliate_code = ? AND status = 'active'",
                [$referralCode]
            );
            
            if ($referrer) {
                $this->jsonResponse([
                    'success' => true,
                    'valid' => true,
                    'referrer_name' => $referrer['first_name'] . ' ' . $referrer['last_name'],
                    'message' => 'Valid referral code'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => true,
                    'valid' => false,
                    'message' => 'Invalid or inactive referral code'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Referral validation error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'valid' => false,
                'message' => 'Unable to validate referral code'
            ], 500);
        }
    }
}

