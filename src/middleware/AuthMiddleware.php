<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseMiddleware.php';

/**
 * Authentication Middleware
 * Ensures user is authenticated before accessing protected routes
 */
class AuthMiddleware extends BaseMiddleware
{
    
    /**
     * Handle the middleware
     */
    public function handle(array $parameters = []): bool
    {
        $this->startSession();
        $this->loadUser();
        
        if (!$this->isAuthenticated()) {
            $this->handleUnauthenticated();
            return false;
        }
        
        // Check if user account is active
        if (!$this->isUserActive()) {
            $this->handleInactiveUser();
            return false;
        }
        
        // Check if email is verified (if required) - only for authenticated users
        if ($this->requiresEmailVerification() && !$this->isEmailVerified()) {
            $this->handleUnverifiedEmail();
            return false;
        }
        
        return true;
    }
    
    /**
     * Handle unauthenticated user
     */
    private function handleUnauthenticated(): void
    {
        if ($this->isApiRequest()) {
            $this->jsonError('Authentication required', 401);
        } else {
            // Store intended URL for redirect after login
            if (!$this->isPostRequest()) {
                $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            }
            
            $this->flash('error', 'Please log in to continue');
            $this->redirect('/login');
        }
    }
    
    /**
     * Handle inactive user
     */
    private function handleInactiveUser(): void
    {
        $this->logout();
        
        if ($this->isApiRequest()) {
            $this->jsonError('Account is inactive', 403);
        } else {
            $this->flash('error', 'Your account has been deactivated. Please contact support.');
            $this->redirect('/login');
        }
    }
    
    /**
     * Handle unverified email
     */
    private function handleUnverifiedEmail(): void
    {
        if ($this->isApiRequest()) {
            $this->jsonError('Email verification required', 403, [
                'verification_required' => true
            ]);
        } else {
            $this->flash('warning', 'Please verify your email address to continue.');
            $this->redirect('/register');
        }
    }
    
    /**
     * Check if user account is active
     */
    private function isUserActive(): bool
    {
        return $this->currentUser && 
               isset($this->currentUser->status) && 
               $this->currentUser->status === 'active';
    }
    
    /**
     * Check if email verification is required
     */
    private function requiresEmailVerification(): bool
    {
        require_once __DIR__ . '/../helpers/auth_helpers.php';
        return isEmailVerificationRequired();
    }
    
    /**
     * Check if user's email is verified
     */
    private function isEmailVerified(): bool
    {
        return $this->currentUser && 
               isset($this->currentUser->email_verified_at) && 
               $this->currentUser->email_verified_at !== null;
    }

}

/**
 * API Authentication Middleware
 * JWT-based authentication for API routes
 */
class ApiAuthMiddleware extends BaseMiddleware
{
    
    /**
     * Handle the middleware
     */
    public function handle(array $parameters = []): bool
    {
        $token = $this->getAuthToken();
        
        if (!$token) {
            $this->jsonError('Authorization token required', 401);
            return false;
        }
        
        // Validate JWT token
        require_once __DIR__ . '/../helpers/JWTHelper.php';
        
        if (JWTHelper::isBlacklisted($token)) {
            $this->jsonError('Token has been revoked', 401);
            return false;
        }
        
        $payload = JWTHelper::decode($token);
        
        if (!$payload) {
            $this->jsonError('Invalid or expired token', 401);
            return false;
        }
        
        // Load user from token
        if (isset($payload['user_id'])) {
            $this->currentUser = $this->getUserById($payload['user_id']);
            
            if (!$this->currentUser) {
                $this->jsonError('User not found', 401);
                return false;
            }
            
            if (!$this->isUserActive()) {
                $this->jsonError('Account is inactive', 403);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get auth token from header
     */
    private function getAuthToken(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Get user by ID
     */
    private function getUserById(int $userId): ?object
    {
        try {
            $userData = $this->fetchOne(
                "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL",
                [$userId]
            );
            
            return $userData ? (object) $userData : null;
        } catch (\Exception $e) {
            error_log("Error fetching user by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if user account is active
     */
    private function isUserActive(): bool
    {
        return $this->currentUser && 
               isset($this->currentUser->status) && 
               $this->currentUser->status === 'active';
    }

}

/**
 * Role-based Authorization Middleware
 * Ensures user has required role to access route
 */
class RoleMiddleware extends BaseMiddleware
{
    
    /**
     * Handle the middleware
     */
    public function handle(array $parameters = []): bool
    {
        $this->startSession();
        $this->loadUser();
        
        if (!$this->isAuthenticated()) {
            $this->handleUnauthenticated();
            return false;
        }
        
        // Extract required role from parameters
        $requiredRole = $parameters['role'] ?? $parameters[0] ?? null;
        
        if (!$requiredRole) {
            return true; // Allow access if no role specified
        }
        
        if (!$this->hasRole($requiredRole)) {
            $this->handleUnauthorized($requiredRole);
            return false;
        }
        
        return true;
    }
    
    /**
     * Handle unauthenticated user
     */
    private function handleUnauthenticated(): void
    {
        if ($this->isApiRequest()) {
            $this->jsonError('Authentication required', 401);
        } else {
            $this->redirect('/login');
        }
    }
    
    /**
     * Handle unauthorized user
     */
    private function handleUnauthorized(string $requiredRole): void
    {
        if ($this->isApiRequest()) {
            $this->jsonError('Insufficient permissions', 403);
        } else {
            http_response_code(403);
            echo "<h1>403 - Access Denied</h1>";
            echo "<p>You need {$requiredRole} role to access this resource.</p>";
        }
    }
}
