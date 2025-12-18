<?php

declare(strict_types=1);

namespace Time2Eat\Controllers\Customer;

require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../../models/RoleChangeRequest.php';
require_once __DIR__ . '/../../models/UserActivity.php';

use core\BaseController;
use models\RoleChangeRequest;
use models\UserActivity;

/**
 * Customer Role Change Request Controller
 * Handles customer requests to change their role (customer → vendor/rider)
 */
class RoleRequestController extends BaseController
{
    private RoleChangeRequest $roleChangeModel;
    private UserActivity $activityModel;

    public function __construct()
    {
        parent::__construct();
        $this->roleChangeModel = new RoleChangeRequest();
        $this->activityModel = new UserActivity();
        
        // Create tables if they don't exist
        $this->roleChangeModel->createTableIfNotExists();
        $this->activityModel->createTableIfNotExists();
    }

    /**
     * Show role change request form
     */
    public function showRequestForm(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect(url('/login'));
            return;
        }

        $user = $this->getCurrentUser();
        
        // Check if user already has a pending request
        $pendingRequest = $this->roleChangeModel->getPendingRequestByUserId($user->id);

        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status
        ];

        $this->renderDashboard('customer/role-request', [
            'title' => 'Request Role Change - Time2Eat',
            'user' => $userData,
            'userRole' => 'customer',
            'currentPage' => 'role-request',
            'pendingRequest' => $pendingRequest
        ]);
    }

    /**
     * Submit role change request
     */
    public function submitRequest(): void
    {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getCurrentUser();
        
        // Check if user already has a pending request
        $pendingRequest = $this->roleChangeModel->getPendingRequestByUserId($user->id);
        if ($pendingRequest) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'You already have a pending role change request. Please wait for admin review.'
            ], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $requestedRole = $input['requested_role'] ?? '';
        $reason = trim($input['reason'] ?? '');

        // Validation
        if (!in_array($requestedRole, ['vendor', 'rider'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid role requested. You can only request to become a vendor or rider.'
            ], 400);
            return;
        }

        if (empty($reason)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Please provide a reason for your role change request.'
            ], 400);
            return;
        }

        if (strlen($reason) < 20) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Please provide a more detailed reason (at least 20 characters).'
            ], 400);
            return;
        }

        // Handle document uploads if any
        $documents = null;
        if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
            $documents = $this->handleDocumentUploads($_FILES['documents']);
        }

        // Create the request
        $requestData = [
            'user_id' => $user->id,
            'current_role' => $user->role,
            'requested_role' => $requestedRole,
            'reason' => $reason,
            'documents' => $documents
        ];

        $success = $this->roleChangeModel->createRequest($requestData);

        if ($success) {
            // Log the activity
            $this->activityModel->logActivity([
                'user_id' => $user->id,
                'activity_type' => 'role_change_requested',
                'activity_description' => "Requested role change from {$user->role} to {$requestedRole}",
                'entity_type' => 'role_change_request',
                'metadata' => [
                    'requested_role' => $requestedRole,
                    'reason' => $reason
                ]
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Your role change request has been submitted successfully. You will be notified once it is reviewed.'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to submit role change request. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user's role change request status
     */
    public function getRequestStatus(): void
    {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getCurrentUser();
        $pendingRequest = $this->roleChangeModel->getPendingRequestByUserId($user->id);

        $this->jsonResponse([
            'success' => true,
            'has_pending_request' => $pendingRequest !== null,
            'request' => $pendingRequest
        ]);
    }

    /**
     * Handle document uploads for role change requests
     */
    private function handleDocumentUploads(array $files): ?array
    {
        try {
            $uploadDir = APP_PATH . '/public/uploads/role-requests/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadedFiles = [];
            $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $fileName = $files['name'][$i];
                $fileSize = $files['size'][$i];
                $fileTmp = $files['tmp_name'][$i];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // Validate file type
                if (!in_array($fileExt, $allowedTypes)) {
                    continue;
                }

                // Validate file size
                if ($fileSize > $maxFileSize) {
                    continue;
                }

                // Generate unique filename
                $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
                $uploadPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $uploadPath)) {
                    $uploadedFiles[] = [
                        'original_name' => $fileName,
                        'stored_name' => $newFileName,
                        'file_size' => $fileSize,
                        'file_type' => $fileExt,
                        'upload_time' => date('Y-m-d H:i:s')
                    ];
                }
            }

            return !empty($uploadedFiles) ? $uploadedFiles : null;

        } catch (\Exception $e) {
            error_log("Error uploading role request documents: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Render dashboard with proper layout
     */
    protected function renderDashboard(string $view, array $data = []): void
    {
        // Start output buffering to capture the dashboard content
        ob_start();

        // Extract data for the view
        extract($data);

        // Include the specific dashboard view using correct relative path
        $viewPath = __DIR__ . "/../../views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("Dashboard view not found: {$view}");
        }
        include $viewPath;

        // Get the content
        $content = ob_get_clean();

        // Explicitly set variables for the layout to ensure they're available
        $user = $data['user'] ?? null;
        $currentPage = $data['currentPage'] ?? '';
        $title = $data['title'] ?? 'Dashboard - Time2Eat';

        // Render with dashboard layout using correct relative path
        $layoutPath = __DIR__ . "/../../views/layouts/dashboard.php";
        if (!file_exists($layoutPath)) {
            throw new \Exception("Dashboard layout not found: dashboard.php");
        }
        include $layoutPath;
    }
}
