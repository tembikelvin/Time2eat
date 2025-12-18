<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/AdminBaseController.php';
require_once __DIR__ . '/../models/Dispute.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PopupNotification.php';

use controllers\AdminBaseController;
use models\Dispute;
use models\Order;
use models\User;
use Time2Eat\Models\PopupNotification;

class DisputeController extends AdminBaseController
{
    private $disputeModel;
    private $orderModel;
    private $userModel;
    private $notificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->disputeModel = new Dispute();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->notificationModel = new PopupNotification();
    }

    /**
     * Admin dispute management dashboard
     */
    public function index(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get filters
        $filters = [
            'status' => $_GET['status'] ?? '',
            'type' => $_GET['type'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Remove empty filters
        $filters = array_filter($filters);

        // Get disputes with pagination
        $disputes = $this->disputeModel->getAllWithDetails($filters, $limit, $offset);
        $totalDisputes = $this->disputeModel->countWithFilters($filters);
        $totalPages = ceil($totalDisputes / $limit);

        // Get statistics
        $stats = $this->disputeModel->getStatistics();

        $this->renderDashboard('admin/disputes', [
            'title' => 'Dispute Management - Time2Eat',
            'disputes' => $disputes,
            'stats' => $stats,
            'filters' => $filters,
            'currentPage' => 'disputes',
            'totalPages' => $totalPages,
            'totalDisputes' => $totalDisputes
        ]);
    }

    /**
     * View dispute details
     */
    public function show(int $id): void
    {
        $dispute = $this->disputeModel->getByIdWithDetails($id);
        
        if (!$dispute) {
            $this->setFlashMessage('error', 'Dispute not found');
            $this->redirect('/admin/disputes');
            return;
        }

        $this->renderDashboard('admin/disputes/show', [
            'title' => 'Dispute Details - Time2Eat',
            'dispute' => $dispute,
            'currentPage' => 'disputes'
        ]);
    }

    /**
     * Customer dispute creation form
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $orderId = (int)($_GET['order_id'] ?? 0);
        
        if (!$orderId) {
            $this->setFlashMessage('error', 'Order ID is required');
            $this->redirect('/customer/orders');
            return;
        }

        $user = $this->getCurrentUser();
        
        // Verify order belongs to customer
        $order = $this->orderModel->getById($orderId);
        if (!$order || $order['customer_id'] !== $user->id) {
            $this->setFlashMessage('error', 'Order not found or access denied');
            $this->redirect('/customer/orders');
            return;
        }

        // Check if dispute already exists
        $existingDispute = $this->disputeModel->fetchOne(
            "SELECT id FROM disputes WHERE order_id = ? AND initiator_id = ? AND status NOT IN ('resolved', 'closed')",
            [$orderId, $user->id]
        );

        if ($existingDispute) {
            $this->setFlashMessage('error', 'A dispute for this order already exists');
            $this->redirect('/customer/disputes');
            return;
        }

        $this->render('customer/disputes/create', [
            'title' => 'Report Issue - Time2Eat',
            'user' => $this->getCurrentUser(),
            'userRole' => 'customer',
            'currentPage' => 'disputes',
            'order' => $order
        ]);
    }

    /**
     * Store new dispute
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $validation = $this->validateRequest([
            'order_id' => 'required|integer',
            'type' => 'required|in:order_issue,payment_issue,delivery_issue,quality_issue,other',
            'subject' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'priority' => 'in:low,medium,high,urgent'
        ], $data);

        if (!$validation['isValid']) {
            $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }

        $user = $this->getCurrentUser();
        $validatedData = $validation['data'];

        // Verify order ownership
        $order = $this->orderModel->getById($validatedData['order_id']);
        if (!$order || $order['customer_id'] !== $user->id) {
            $this->jsonResponse(['success' => false, 'message' => 'Order not found or access denied'], 403);
            return;
        }

        try {
            $disputeData = [
                'order_id' => $validatedData['order_id'],
                'initiator_id' => $user->id,
                'type' => $validatedData['type'],
                'subject' => $validatedData['subject'],
                'description' => $validatedData['description'],
                'priority' => $validatedData['priority'] ?? 'medium',
                'status' => 'open'
            ];

            // Handle evidence upload if provided
            if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
                $evidence = $this->handleEvidenceUpload($_FILES['evidence']);
                if ($evidence) {
                    $disputeData['evidence'] = json_encode($evidence);
                }
            }

            $disputeId = $this->disputeModel->createDispute($disputeData);

            if ($disputeId) {
                // Send notification to admin
                $this->sendDisputeNotification($disputeId, 'created');

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dispute submitted successfully. We will review it shortly.',
                    'dispute_id' => $disputeId
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to create dispute'], 500);
            }

        } catch (\Exception $e) {
            error_log("Dispute creation error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while creating the dispute'], 500);
        }
    }

    /**
     * Update dispute status (Admin only)
     */
    public function updateStatus(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $validation = $this->validateRequest([
            'status' => 'required|in:open,investigating,resolved,closed,escalated',
            'resolution' => 'string|max:2000',
            'compensation_amount' => 'numeric|min:0'
        ], $data);

        if (!$validation['isValid']) {
            $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }

        $user = $this->getCurrentUser();
        $validatedData = $validation['data'];

        try {
            $updated = $this->disputeModel->updateStatus(
                $id,
                $validatedData['status'],
                $user->id,
                $validatedData['resolution'] ?? null
            );

            if ($updated) {
                // Add compensation if provided
                if (isset($validatedData['compensation_amount']) && $validatedData['compensation_amount'] > 0) {
                    $this->disputeModel->addCompensation($id, (float)$validatedData['compensation_amount']);
                }

                // Send notification
                $this->sendDisputeNotification($id, 'status_updated');

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dispute status updated successfully'
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update dispute status'], 500);
            }

        } catch (\Exception $e) {
            error_log("Dispute status update error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred while updating the dispute'], 500);
        }
    }

    /**
     * Customer dispute list
     */
    public function customerDisputes(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $disputes = $this->disputeModel->getByUserId($user->id);

        $this->render('customer/disputes/index', [
            'title' => 'My Disputes - Time2Eat',
            'user' => $user,
            'userRole' => 'customer',
            'currentPage' => 'disputes',
            'disputes' => $disputes
        ]);
    }

    /**
     * Vendor dispute list
     */
    public function vendorDisputes(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getCurrentUser();
        
        // Get vendor's restaurant
        $restaurant = $this->fetchOne("SELECT id FROM restaurants WHERE user_id = ?", [$user->id]);
        
        if (!$restaurant) {
            $this->setFlashMessage('error', 'Restaurant not found');
            $this->redirect('/vendor/dashboard');
            return;
        }

        $disputes = $this->disputeModel->getByRestaurantId($restaurant['id']);

        $this->render('vendor/disputes/index', [
            'title' => 'Restaurant Disputes - Time2Eat',
            'disputes' => $disputes,
            'currentPage' => 'disputes'
        ]);
    }

    /**
     * Handle evidence file upload
     */
    private function handleEvidenceUpload(array $file): ?array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        if ($file['size'] > $maxSize) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/disputes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'filename' => $filename,
                'original_name' => $file['name'],
                'type' => $file['type'],
                'size' => $file['size'],
                'uploaded_at' => date('Y-m-d H:i:s')
            ];
        }

        return null;
    }

    /**
     * Send dispute notification
     */
    private function sendDisputeNotification(int $disputeId, string $action): void
    {
        try {
            $dispute = $this->disputeModel->getByIdWithDetails($disputeId);
            
            if (!$dispute) {
                return;
            }

            $messages = [
                'created' => 'A new dispute has been submitted and requires attention.',
                'status_updated' => 'Your dispute status has been updated.'
            ];

            $message = $messages[$action] ?? 'Dispute notification';

            // Notify admin for new disputes
            if ($action === 'created') {
                $this->notificationModel->createNotification([
                    'title' => 'New Dispute Submitted',
                    'message' => $message,
                    'type' => 'dispute',
                    'priority' => $dispute['priority'],
                    'action_url' => '/admin/disputes/' . $disputeId,
                    'action_text' => 'View Dispute'
                ]);
            }

            // Notify customer for status updates
            if ($action === 'status_updated') {
                $this->notificationModel->createNotification([
                    'title' => 'Dispute Status Updated',
                    'message' => $message,
                    'type' => 'dispute',
                    'priority' => 'normal',
                    'target_user_id' => $dispute['initiator_id'],
                    'action_url' => '/customer/disputes',
                    'action_text' => 'View Disputes'
                ]);
            }

        } catch (\Exception $e) {
            error_log("Error sending dispute notification: " . $e->getMessage());
        }
    }
}
