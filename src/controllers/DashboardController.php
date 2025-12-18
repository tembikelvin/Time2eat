<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';

use core\BaseController;

class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
    }

    /**
     * Main dashboard route that redirects based on user role
     */
    public function index(): void
    {
        // CRITICAL: Prevent caching of dashboard redirect (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Check authentication
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Redirect to role-specific dashboard
        $redirectUrl = $this->getRedirectUrl($user->role);
        $this->redirect($redirectUrl);
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl(string $role): string
    {
        switch ($role) {
            case 'admin':
                return '/admin/dashboard';
            case 'vendor':
                return '/vendor/dashboard';
            case 'rider':
                return '/rider/dashboard';
            default:
                return '/customer/dashboard';
        }
    }
}
