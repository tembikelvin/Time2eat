<?php

namespace Time2Eat\Controllers\Vendor;

use core\BaseController;
use models\Category;
use models\Restaurant;

class CategoryController extends BaseController
{
    private Category $categoryModel;
    private Restaurant $restaurantModel;

    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
        $this->categoryModel = new Category();
        $this->restaurantModel = new Restaurant();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);

        if (!$restaurant) {
            $this->redirect('/vendor/setup');
            return;
        }

        $categories = $this->categoryModel->getAll();

        $this->render('vendor/categories/index', [
            'title' => 'Categories - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'categories' => $categories,
            'currentPage' => 'categories'
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);

        if (!$restaurant) {
            $this->redirect('/vendor/setup');
            return;
        }

        $this->render('vendor/categories/create', [
            'title' => 'Create Category - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'currentPage' => 'categories'
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendor/categories');
            return;
        }

        try {
            $data = [
                'name' => $this->request->post('name'),
                'description' => $this->request->post('description')
            ];

            $this->categoryModel->create($data);
            
            $this->session->setFlash('success', 'Category created successfully!');
            $this->redirect('/vendor/categories');

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Failed to create category: ' . $e->getMessage());
            $this->redirect('/vendor/categories/create');
        }
    }
}