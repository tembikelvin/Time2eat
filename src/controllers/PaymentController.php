<?php

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../services/TranzakPaymentService.php';

use core\BaseController;
use Time2Eat\Services\TranzakPaymentService;

class PaymentController extends BaseController
{
    private TranzakPaymentService $tranzakService;

    public function __construct()
    {
        parent::__construct();
        $this->tranzakService = new TranzakPaymentService();
    }

    /**
     * Handle payment success return from Tranzak
     */
    public function success(): void
    {
        $orderId = $_GET['order_id'] ?? null;
        $transactionId = $_GET['transaction_id'] ?? null;
        $status = $_GET['status'] ?? 'success';

        // Verify payment status with Tranzak
        if ($transactionId) {
            $verification = $this->tranzakService->verifyPayment($transactionId);
            
            if ($verification['success'] && $verification['status'] === 'SUCCESS') {
                // Update order status to paid
                $this->updateOrderStatus($orderId, 'paid');
                
                // Clear cart
                if (isset($_SESSION['user_id'])) {
                    $this->clearCart($_SESSION['user_id']);
                }
            }
        }

        $this->render('payment/success', [
            'title' => 'Payment Successful - Time2Eat',
            'orderId' => $orderId,
            'transactionId' => $transactionId,
            'status' => $status
        ]);
    }

    /**
     * Handle Tranzak payment return (for payment-before-order flow)
     */
    public function tranzakReturn(): void
    {
        $tempRef = $_GET['ref'] ?? null;
        $transactionId = $_GET['transaction_id'] ?? null;
        $status = $_GET['status'] ?? 'pending';

        // Clear draft order from session
        if ($tempRef && isset($_SESSION['tranzak_draft_orders']) && 
            ($_SESSION['tranzak_draft_orders']['temp_reference'] ?? '') === $tempRef) {
            // Keep it for now, webhook will clean it up
        }

        // Check if order was created (webhook may have already processed it)
        $orderCreated = false;
        $orderIds = [];
        
        if ($transactionId) {
            try {
                require_once __DIR__ . '/../../config/database.php';
                $db = \Database::getInstance();
                
                // Check if payment record exists (means order was created)
                $stmt = $db->prepare("SELECT order_id FROM payments WHERE transaction_id = ? LIMIT 1");
                $stmt->execute([$transactionId]);
                $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($payment && !empty($payment['order_id'])) {
                    $orderCreated = true;
                    // Get all orders for this payment
                    $orderStmt = $db->prepare("SELECT id, order_number FROM orders WHERE id = ? OR payment_status = 'paid' AND customer_id = (SELECT customer_id FROM orders WHERE id = ?) ORDER BY id DESC LIMIT 10");
                    $orderStmt->execute([$payment['order_id'], $payment['order_id']]);
                    $orders = $orderStmt->fetchAll(\PDO::FETCH_ASSOC);
                    $orderIds = array_column($orders, 'id');
                }
            } catch (\Exception $e) {
                error_log("Error checking order status in return handler: " . $e->getMessage());
            }
        }

        // Render appropriate view
        if ($orderCreated) {
            // Order was created successfully
            $this->render('payment/success', [
                'title' => 'Payment Successful - Time2Eat',
                'orderId' => $orderIds[0] ?? null,
                'orderIds' => $orderIds,
                'transactionId' => $transactionId,
                'status' => 'success',
                'message' => 'Your payment was successful and your order has been placed!'
            ]);
        } else {
            // Payment may still be processing, show pending status
            $this->render('payment/pending', [
                'title' => 'Payment Processing - Time2Eat',
                'tempRef' => $tempRef,
                'transactionId' => $transactionId,
                'status' => $status,
                'message' => 'Your payment is being processed. You will receive a confirmation once your order is placed.'
            ]);
        }
    }

    /**
     * Handle payment failure return from Tranzak
     */
    public function failure(): void
    {
        $orderId = $_GET['order_id'] ?? null;
        $error = $_GET['error'] ?? 'Payment was not completed';
        $status = $_GET['status'] ?? 'failed';

        $this->render('payment/failure', [
            'title' => 'Payment Failed - Time2Eat',
            'orderId' => $orderId,
            'error' => $error,
            'status' => $status
        ]);
    }

    /**
     * Handle Tranzak Payment Notification (TPN) webhook
     * Based on official Tranzak documentation: https://docs.developer.tranzak.me
     */
    public function webhook(): void
    {
        // Set content type for webhook response
        header('Content-Type: application/json');
        
        try {
            // Get the raw POST data
            $rawInput = file_get_contents('php://input');
            
            if (empty($rawInput)) {
                // Try to get from $_POST if raw input is empty
                $rawInput = json_encode($_POST);
            }

            error_log('Tranzak Webhook Raw Input: ' . $rawInput);

            // Parse JSON payload
            $tpnData = json_decode($rawInput, true);
            
            if (!$tpnData || json_last_error() !== JSON_ERROR_NONE) {
                error_log('Tranzak Webhook: Invalid JSON payload. Error: ' . json_last_error_msg());
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid JSON payload',
                    'error' => json_last_error_msg()
                ]);
                return;
            }

            error_log('Tranzak TPN received: ' . json_encode($tpnData));

            // Process the TPN
            $result = $this->tranzakService->handlePaymentNotification($tpnData);

            if ($result['success']) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'] ?? 'TPN processed successfully',
                    'event_type' => $tpnData['eventType'] ?? 'unknown',
                    'resource_id' => $tpnData['resourceId'] ?? null
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $result['message'] ?? 'TPN processing failed',
                    'error' => $result['error'] ?? null
                ]);
            }

        } catch (\Exception $e) {
            error_log('Tranzak webhook error: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error processing webhook',
                'error' => (defined('APP_ENV') && APP_ENV === 'development') ? $e->getMessage() : 'An error occurred'
            ]);
        }
    }

    /**
     * Update order status
     */
    private function updateOrderStatus(?string $orderId, string $status): void
    {
        if (!$orderId) {
            return;
        }

        try {
            require_once __DIR__ . '/../models/Order.php';
            $orderModel = new \models\Order();
            
            // Update order status using Order model
            $orderModel->updateOrderStatus((int)$orderId, $status, [
                'payment_status' => $status === 'paid' ? 'paid' : 'pending'
            ]);
            
            error_log("Order {$orderId} status updated to {$status}");
        } catch (\Exception $e) {
            error_log("Failed to update order status: " . $e->getMessage());
        }
    }

    /**
     * Clear user's cart
     */
    private function clearCart(int $userId): void
    {
        try {
            $sql = "DELETE FROM cart_items WHERE user_id = ?";
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute([$userId]);
            
            error_log("Cart cleared for user {$userId}");
        } catch (\Exception $e) {
            error_log("Failed to clear cart: " . $e->getMessage());
        }
    }
}