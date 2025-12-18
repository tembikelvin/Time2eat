<?php

namespace Time2Eat\Controllers\Vendor;

use core\BaseController;
use models\Restaurant;
use models\User;

class RestaurantController extends BaseController
{
    private Restaurant $restaurantModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
        $this->restaurantModel = new Restaurant();
        $this->userModel = new User();
    }

    public function show(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);

        if (!$restaurant) {
            $this->redirect('/vendor/setup');
            return;
        }

        $this->render('vendor/restaurant/show', [
            'title' => 'My Restaurant - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'currentPage' => 'restaurant'
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);

        if (!$restaurant) {
            $this->redirect('/vendor/setup');
            return;
        }

        $this->render('vendor/restaurant/edit', [
            'title' => 'Edit Restaurant - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'currentPage' => 'restaurant'
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendor/restaurant');
            return;
        }

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);

        if (!$restaurant) {
            $this->redirect('/vendor/setup');
            return;
        }

        try {
            // Update restaurant information
            $data = [
                'name' => $this->request->post('name'),
                'description' => $this->request->post('description'),
                'address' => $this->request->post('address'),
                'phone' => $this->request->post('phone'),
                'email' => $this->request->post('email'),
                'opening_hours' => $this->request->post('opening_hours'),
                'cuisine_type' => $this->request->post('cuisine_type')
            ];

            $this->restaurantModel->update($restaurant['id'], $data);
            
            $this->session->setFlash('success', 'Restaurant updated successfully!');
            $this->redirect('/vendor/restaurant');

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Failed to update restaurant: ' . $e->getMessage());
            $this->redirect('/vendor/restaurant/edit');
        }
    }
}