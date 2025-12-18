<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/AdminBaseController.php';

use controllers\AdminBaseController;

/**
 * Admin Controller for Time2Eat
 * Handles admin dashboard and management functions
 */
class AdminController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show admin dashboard
     */
    public function dashboard(): void
    {
        $this->renderDashboard('admin/dashboard', [
            'title' => 'Admin Dashboard - Time2Eat',
            'page' => 'admin-dashboard'
        ]);
    }
}
