<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseMiddleware.php';

/**
 * Role-based Access Control Middleware
 * Ensures user has the required role before accessing protected routes
 */
class RoleMiddleware extends BaseMiddleware
{
    private string $requiredRole;
    
    /**
     * Handle the middleware
     */
    public function handle(array $parameters = []): bool
    {
        $this->startSession();
        $this->loadUser();
        
        // Get required role from parameters
        $this->requiredRole = $parameters['role'] ?? '';
        
        if (empty($this->requiredRole)) {
            error_log("RoleMiddleware: No role specified");
            return false;
        }
        
        // Check if user is authenticated
        if (!$this->isAuthenticated()) {
            $this->handleUnauthenticated();
            return false;
        }
        
        // Check if user account is active
        $user = $this->getCurrentUser();
        if (!$user || $user->status !== 'active') {
            $this->handleInactiveUser();
            return false;
        }
        
        // Check if user has the required role
        if (!$this->hasRequiredRole()) {
            $this->handleInsufficientRole();
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user has the required role
     */
    private function hasRequiredRole(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->role === $this->requiredRole;
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
     * Handle insufficient role
     */
    private function handleInsufficientRole(): void
    {
        if ($this->isApiRequest()) {
            $this->jsonError('Insufficient permissions', 403);
        } else {
            $this->flash('error', 'You do not have permission to access this page.');
            $this->redirect('/dashboard');
        }
    }
}
