<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';

use core\BaseController;

/**
 * Admin Base Controller
 * Provides common functionality for all admin controllers
 */
abstract class AdminBaseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
    }

    /**
     * Render dashboard with proper admin layout
     */
    protected function renderDashboard(string $view, array $data = []): void
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

        // Add user data to view data
        $data['user'] = $userData;

        // Start output buffering to capture the dashboard content
        ob_start();

        // Extract data for the view
        extract($data);

        // Include the specific dashboard view using correct relative path
        $viewFile = __DIR__ . "/../views/{$view}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("Admin view not found: {$view}");
        }

        // Get the content
        $content = ob_get_clean();

        // Explicitly set variables for the layout to ensure they're available
        $user = $data['user'] ?? null;
        $currentPage = $data['currentPage'] ?? '';
        $title = $data['title'] ?? 'Dashboard - Time2Eat';

        // Render with dashboard layout using correct relative path
        $layoutFile = __DIR__ . "/../views/components/dashboard-layout.php";
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            // Fallback to content only if layout doesn't exist
            echo $content;
        }
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->role === 'admin';
    }

    /**
     * Send JSON response
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
