<?php

namespace Time2Eat\Controllers\Vendor;

use core\BaseController;
use models\Order;
use models\Restaurant;

class OrderController extends BaseController
{
    private Order $orderModel;
    private Restaurant $restaurantModel;

    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
        $this->orderModel = new Order();
        $this->restaurantModel = new Restaurant();
    }

    public function show(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);
        $order = $this->orderModel->find($id);

        if (!$restaurant || !$order || $order['restaurant_id'] !== $restaurant['id']) {
            $this->session->setFlash('error', 'Order not found');
            $this->redirect('/vendor/orders');
            return;
        }

        $this->render('vendor/orders/show', [
            'title' => 'Order #' . $order['id'] . ' - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'order' => $order,
            'currentPage' => 'orders'
        ]);
    }

    public function accept(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendor/orders');
            return;
        }

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);
        $order = $this->orderModel->find($id);

        if (!$restaurant || !$order || $order['restaurant_id'] !== $restaurant['id']) {
            $this->session->setFlash('error', 'Order not found');
            $this->redirect('/vendor/orders');
            return;
        }

        try {
            $this->orderModel->updateOrderStatus($id, 'preparing');
            $this->session->setFlash('success', 'Order accepted and is now being prepared');

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Failed to accept order: ' . $e->getMessage());
        }

        $this->redirect('/vendor/orders');
    }

    public function reject(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendor/orders');
            return;
        }

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);
        $order = $this->orderModel->find($id);

        if (!$restaurant || !$order || $order['restaurant_id'] !== $restaurant['id']) {
            $this->session->setFlash('error', 'Order not found');
            $this->redirect('/vendor/orders');
            return;
        }

        try {
            $reason = $this->request->post('reason', 'Order rejected by restaurant');
            $this->orderModel->updateOrderStatus($id, 'cancelled', ['cancellation_reason' => $reason]);
            $this->session->setFlash('success', 'Order has been rejected');

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Failed to reject order: ' . $e->getMessage());
        }

        $this->redirect('/vendor/orders');
    }

    public function ready(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendor/orders');
            return;
        }

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);
        $order = $this->orderModel->find($id);

        if (!$restaurant || !$order || $order['restaurant_id'] !== $restaurant['id']) {
            $this->session->setFlash('error', 'Order not found');
            $this->redirect('/vendor/orders');
            return;
        }

        try {
            $this->orderModel->updateOrderStatus($id, 'ready');
            $this->session->setFlash('success', 'Order marked as ready for pickup');

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Failed to update order status: ' . $e->getMessage());
        }

        $this->redirect('/vendor/orders');
    }
}