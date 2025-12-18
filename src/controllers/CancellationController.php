<?php

namespace Time2Eat\Controllers;

use core\BaseController;
use Time2Eat\Models\Order;
use Time2Eat\Models\Payment;
use Time2Eat\Models\Cancellation;
use Time2Eat\Services\PaymentGatewayService;
use Time2Eat\Services\NotificationService;

class CancellationController extends BaseController
{
    private Order $orderModel;
    private Payment $paymentModel;
    private Cancellation $cancellationModel;
    private PaymentGatewayService $paymentService;
    private NotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->paymentModel = new Payment();
        $this->cancellationModel = new Cancellation();
        $this->paymentService = new PaymentGatewayService();
        $this->notificationService = new NotificationService();
    }

    /**
     * Show cancellation form
     */
    public function create(int $orderId): void
    {
        $this->requireAuth();
        
        $order = $this->orderModel->findById($orderId);
        if (!$order) {
            $this->setFlashMessage('error', 'Order not found.');
            $this->redirect('/customer/orders');
            return;
        }

        $user = $this->getCurrentUser();
        
        // Check permissions
        if (!$this->canCancelOrder($order, $user)) {
            $this->setFlashMessage('error', 'You cannot cancel this order.');
            $this->redirect('/customer/orders');
            return;
        }

        // Check if order can be cancelled
        $cancellationInfo = $this->getCancellationInfo($order);
        if (!$cancellationInfo['can_cancel']) {
            $this->setFlashMessage('error', $cancellationInfo['reason']);
            $this->redirect('/customer/orders');
            return;
        }

        // Get payment information
        $payment = $this->paymentModel->getByOrderId($orderId);
        $refundInfo = $this->getRefundInfo($order, $payment);

        $this->render('cancellations/create', [
            'order' => $order,
            'payment' => $payment,
            'cancellationInfo' => $cancellationInfo,
            'refundInfo' => $refundInfo,
            'title' => 'Cancel Order - Time2Eat'
        ]);
    }

    /**
     * Process order cancellation
     */
    public function store(): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/orders');
            return;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $details = trim($_POST['details'] ?? '');

        $order = $this->orderModel->findById($orderId);
        if (!$order) {
            $this->setFlashMessage('error', 'Order not found.');
            $this->redirect('/customer/orders');
            return;
        }

        $user = $this->getCurrentUser();
        
        // Validate cancellation
        if (!$this->canCancelOrder($order, $user)) {
            $this->setFlashMessage('error', 'You cannot cancel this order.');
            $this->redirect('/customer/orders');
            return;
        }

        $cancellationInfo = $this->getCancellationInfo($order);
        if (!$cancellationInfo['can_cancel']) {
            $this->setFlashMessage('error', $cancellationInfo['reason']);
            $this->redirect('/customer/orders');
            return;
        }

        if (empty($reason)) {
            $this->setFlashMessage('error', 'Please provide a cancellation reason.');
            $this->redirect("/cancellations/create/{$orderId}");
            return;
        }

        try {
            $this->getDb()->beginTransaction();

            // Create cancellation record
            $cancellationId = $this->cancellationModel->create([
                'order_id' => $orderId,
                'user_id' => $user['id'],
                'user_type' => $user['role'],
                'reason' => $reason,
                'details' => $details,
                'status' => 'pending',
                'requested_at' => date('Y-m-d H:i:s')
            ]);

            // Update order status
            $this->orderModel->update($orderId, [
                'status' => 'cancellation_requested',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Process automatic approval for eligible cancellations
            if ($cancellationInfo['auto_approve']) {
                $this->approveCancellation($cancellationId, 'system', 'Automatic approval');
            }

            $this->getDb()->commit();

            // Send notifications
            $this->sendCancellationNotifications($order, $reason);

            $message = $cancellationInfo['auto_approve'] 
                ? 'Order cancelled successfully. Refund will be processed shortly.'
                : 'Cancellation request submitted. You will be notified once it\'s reviewed.';
                
            $this->setFlashMessage('success', $message);
            $this->redirect('/customer/orders');

        } catch (\Exception $e) {
            $this->getDb()->rollback();
            $this->logError('Order cancellation error', [
                'order_id' => $orderId,
                'user_id' => $user['id'],
                'error' => $e->getMessage()
            ]);
            
            $this->setFlashMessage('error', 'Failed to cancel order. Please try again.');
            $this->redirect("/cancellations/create/{$orderId}");
        }
    }

    /**
     * Admin/Vendor: View cancellation requests
     */
    public function index(): void
    {
        $this->requireAuth(['admin', 'vendor']);
        
        $user = $this->getCurrentUser();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? 'all';
        
        $filters = ['status' => $status];
        if ($user['role'] === 'vendor') {
            $filters['restaurant_id'] = $user['restaurant_id'] ?? 0;
        }

        $cancellations = $this->cancellationModel->getPaginated($page, 20, $filters);
        $stats = $this->cancellationModel->getStats($filters);

        $this->render('cancellations/index', [
            'cancellations' => $cancellations,
            'stats' => $stats,
            'currentStatus' => $status,
            'title' => 'Cancellation Requests - Time2Eat'
        ]);
    }

    /**
     * Admin/Vendor: View cancellation details
     */
    public function show(int $id): void
    {
        $this->requireAuth(['admin', 'vendor']);
        
        $cancellation = $this->cancellationModel->findById($id);
        if (!$cancellation) {
            $this->setFlashMessage('error', 'Cancellation request not found.');
            $this->redirect('/cancellations');
            return;
        }

        $user = $this->getCurrentUser();
        
        // Check permissions for vendors
        if ($user['role'] === 'vendor') {
            $order = $this->orderModel->findById($cancellation['order_id']);
            if (!$order || $order['restaurant_id'] !== ($user['restaurant_id'] ?? 0)) {
                $this->setFlashMessage('error', 'Access denied.');
                $this->redirect('/cancellations');
                return;
            }
        }

        $order = $this->orderModel->findById($cancellation['order_id']);
        $payment = $this->paymentModel->getByOrderId($cancellation['order_id']);
        $refundInfo = $this->getRefundInfo($order, $payment);

        $this->render('cancellations/show', [
            'cancellation' => $cancellation,
            'order' => $order,
            'payment' => $payment,
            'refundInfo' => $refundInfo,
            'title' => 'Cancellation Details - Time2Eat'
        ]);
    }

    /**
     * Admin/Vendor: Approve cancellation
     */
    public function approve(int $id): void
    {
        $this->requireAuth(['admin', 'vendor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $notes = trim($_POST['notes'] ?? '');
        $user = $this->getCurrentUser();

        try {
            $result = $this->approveCancellation($id, $user['id'], $notes);
            
            if ($result['success']) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Cancellation approved successfully'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            $this->logError('Cancellation approval error', [
                'cancellation_id' => $id,
                'user_id' => $user['id'],
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to approve cancellation'
            ]);
        }
    }

    /**
     * Admin/Vendor: Reject cancellation
     */
    public function reject(int $id): void
    {
        $this->requireAuth(['admin', 'vendor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $reason = trim($_POST['reason'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $user = $this->getCurrentUser();

        if (empty($reason)) {
            $this->jsonResponse(['success' => false, 'message' => 'Please provide a rejection reason']);
            return;
        }

        try {
            $cancellation = $this->cancellationModel->findById($id);
            if (!$cancellation) {
                $this->jsonResponse(['success' => false, 'message' => 'Cancellation not found']);
                return;
            }

            // Update cancellation status
            $this->cancellationModel->update($id, [
                'status' => 'rejected',
                'reviewed_by' => $user['id'],
                'reviewed_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason,
                'admin_notes' => $notes
            ]);

            // Update order status back to previous status
            $order = $this->orderModel->findById($cancellation['order_id']);
            $previousStatus = $this->determinePreviousOrderStatus($order);
            
            $this->orderModel->update($cancellation['order_id'], [
                'status' => $previousStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Send rejection notification
            $this->sendCancellationRejectionNotification($order, $reason);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Cancellation rejected successfully'
            ]);

        } catch (\Exception $e) {
            $this->logError('Cancellation rejection error', [
                'cancellation_id' => $id,
                'user_id' => $user['id'],
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to reject cancellation'
            ]);
        }
    }

    /**
     * Check if user can cancel order
     */
    private function canCancelOrder(array $order, array $user): bool
    {
        // Customer can cancel their own orders
        if ($user['role'] === 'customer' && $order['customer_id'] === $user['id']) {
            return true;
        }

        // Vendor can cancel orders from their restaurant
        if ($user['role'] === 'vendor' && $order['restaurant_id'] === ($user['restaurant_id'] ?? 0)) {
            return true;
        }

        // Admin can cancel any order
        if ($user['role'] === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Get cancellation information
     */
    private function getCancellationInfo(array $order): array
    {
        $status = $order['status'];
        $createdAt = new \DateTime($order['created_at']);
        $now = new \DateTime();
        $minutesSinceOrder = $now->diff($createdAt)->i + ($now->diff($createdAt)->h * 60);

        // Define cancellation rules
        $rules = [
            'pending' => ['can_cancel' => true, 'auto_approve' => true, 'refund_percentage' => 100],
            'confirmed' => [
                'can_cancel' => $minutesSinceOrder <= 15,
                'auto_approve' => $minutesSinceOrder <= 5,
                'refund_percentage' => $minutesSinceOrder <= 5 ? 100 : 90
            ],
            'preparing' => [
                'can_cancel' => true,
                'auto_approve' => false,
                'refund_percentage' => 75
            ],
            'ready' => [
                'can_cancel' => true,
                'auto_approve' => false,
                'refund_percentage' => 50
            ],
            'picked_up' => [
                'can_cancel' => false,
                'auto_approve' => false,
                'refund_percentage' => 0,
                'reason' => 'Order has already been picked up for delivery'
            ],
            'on_the_way' => [
                'can_cancel' => false,
                'auto_approve' => false,
                'refund_percentage' => 0,
                'reason' => 'Order is currently being delivered'
            ],
            'delivered' => [
                'can_cancel' => false,
                'auto_approve' => false,
                'refund_percentage' => 0,
                'reason' => 'Order has already been delivered'
            ],
            'cancelled' => [
                'can_cancel' => false,
                'auto_approve' => false,
                'refund_percentage' => 0,
                'reason' => 'Order is already cancelled'
            ]
        ];

        return $rules[$status] ?? [
            'can_cancel' => false,
            'auto_approve' => false,
            'refund_percentage' => 0,
            'reason' => 'Order cannot be cancelled at this time'
        ];
    }

    /**
     * Get refund information
     */
    private function getRefundInfo(array $order, ?array $payment): array
    {
        if (!$payment || $payment['status'] !== 'completed') {
            return [
                'eligible' => false,
                'amount' => 0,
                'method' => 'none',
                'message' => 'No refund available - payment not completed'
            ];
        }

        $cancellationInfo = $this->getCancellationInfo($order);
        $refundPercentage = $cancellationInfo['refund_percentage'] ?? 0;
        $refundAmount = ($payment['amount'] * $refundPercentage) / 100;

        return [
            'eligible' => $refundAmount > 0,
            'amount' => $refundAmount,
            'percentage' => $refundPercentage,
            'method' => $payment['payment_method'],
            'gateway' => $payment['gateway'],
            'processing_time' => $this->getRefundProcessingTime($payment['gateway'])
        ];
    }

    /**
     * Approve cancellation and process refund
     */
    private function approveCancellation(int $cancellationId, $reviewerId, string $notes): array
    {
        $cancellation = $this->cancellationModel->findById($cancellationId);
        if (!$cancellation) {
            return ['success' => false, 'message' => 'Cancellation not found'];
        }

        $order = $this->orderModel->findById($cancellation['order_id']);
        $payment = $this->paymentModel->getByOrderId($cancellation['order_id']);

        $this->getDb()->beginTransaction();

        try {
            // Update cancellation status
            $this->cancellationModel->update($cancellationId, [
                'status' => 'approved',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'admin_notes' => $notes
            ]);

            // Update order status
            $this->orderModel->update($cancellation['order_id'], [
                'status' => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Process refund if applicable
            $refundInfo = $this->getRefundInfo($order, $payment);
            if ($refundInfo['eligible'] && $refundInfo['amount'] > 0) {
                $refundResult = $this->paymentService->processRefund(
                    $payment,
                    $refundInfo['amount'],
                    'Order cancellation'
                );

                if (!$refundResult['success']) {
                    // Log refund failure but don't fail the cancellation
                    $this->logError('Refund processing failed', [
                        'order_id' => $order['id'],
                        'payment_id' => $payment['id'],
                        'refund_amount' => $refundInfo['amount'],
                        'error' => $refundResult['message']
                    ]);
                }
            }

            $this->getDb()->commit();

            // Send approval notification
            $this->sendCancellationApprovalNotification($order, $refundInfo);

            return ['success' => true, 'message' => 'Cancellation approved successfully'];

        } catch (\Exception $e) {
            $this->getDb()->rollback();
            throw $e;
        }
    }

    /**
     * Send cancellation notifications
     */
    private function sendCancellationNotifications(array $order, string $reason): void
    {
        // Implementation would use NotificationService to send emails/SMS
        // This is a placeholder for the notification logic
    }

    /**
     * Send cancellation approval notification
     */
    private function sendCancellationApprovalNotification(array $order, array $refundInfo): void
    {
        // Implementation would use NotificationService
    }

    /**
     * Send cancellation rejection notification
     */
    private function sendCancellationRejectionNotification(array $order, string $reason): void
    {
        // Implementation would use NotificationService
    }

    /**
     * Determine previous order status
     */
    private function determinePreviousOrderStatus(array $order): string
    {
        // Logic to determine what status to revert to
        return 'confirmed'; // Default fallback
    }

    /**
     * Get refund processing time
     */
    private function getRefundProcessingTime(string $gateway): string
    {
        $times = [
            'stripe' => '5-10 business days',
            'paypal' => '3-5 business days',
            'tranzak' => '1-3 business days',
            'orange_money' => '1-2 business days',
            'mtn_momo' => '1-2 business days'
        ];

        return $times[$gateway] ?? '3-7 business days';
    }
}
