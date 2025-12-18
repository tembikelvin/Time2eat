<?php

namespace controllers\Api;

require_once __DIR__ . '/../../core/BaseController.php';

use core\BaseController;

class AuthController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check if user is authenticated
     */
    public function checkAuth(): void
    {
        // CRITICAL: Prevent caching of auth check (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        if ($this->isAuthenticated()) {
            $user = $this->getCurrentUser();
            $this->jsonResponse([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'name' => $user->first_name . ' ' . $user->last_name
                ]
            ]);
        } else {
            $this->jsonError('Not authenticated', 401);
        }
    }

    /**
     * Login user via API
     */
    public function login(): void
    {
        // CRITICAL: Prevent caching of login response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Implementation for API login
        $this->jsonError('API login not implemented yet', 501);
    }

    /**
     * Register user via API
     */
    public function register(): void
    {
        // CRITICAL: Prevent caching of registration response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Implementation for API registration
        $this->jsonError('API registration not implemented yet', 501);
    }

    /**
     * Refresh authentication token
     */
    public function refresh(): void
    {
        // CRITICAL: Prevent caching of token refresh (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Implementation for token refresh
        $this->jsonError('Token refresh not implemented yet', 501);
    }

    /**
     * Logout user via API
     */
    public function logout(): void
    {
        // CRITICAL: Prevent caching of logout response (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Implementation for API logout
        $this->jsonError('API logout not implemented yet', 501);
    }
}
