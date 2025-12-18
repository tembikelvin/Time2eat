<?php

namespace Time2Eat\Controllers\Vendor;

use core\BaseController;
use models\MenuItem;
use models\Restaurant;
use models\Category;

class MenuController extends BaseController
{
    private MenuItem $menuItemModel;
    private Restaurant $restaurantModel;
    private Category $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
        $this->menuItemModel = new MenuItem();
        $this->restaurantModel = new Restaurant();
        $this->categoryModel = new Category();
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

        $page = (int)($_GET['page'] ?? 1);
        $view = $_GET['view'] ?? 'menu';
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $menuItems = $this->menuItemModel->getByRestaurant($restaurant['id'], $limit, $offset);
        $totalItems = $this->menuItemModel->countByRestaurant($restaurant['id']);
        $totalPages = ceil($totalItems / $limit);

        // Get categories for filtering
        $categories = $this->categoryModel->getAll();

        // Determine current page based on view parameter
        $currentPage = ($view === 'inventory') ? 'inventory' : 'menu';

        $this->render('vendor/menu/index', [
            'title' => ($view === 'inventory') ? 'Inventory Management - Time2Eat' : 'Menu Management - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'menuItems' => $menuItems,
            'categories' => $categories,
            'currentPage' => $currentPage,
            'currentView' => $view,
            'paginationPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems
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

        $categories = $this->categoryModel->getAll();

        $this->render('vendor/menu/create', [
            'title' => 'Add Menu Item - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'categories' => $categories,
            'currentPage' => 'menu-add'
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendor/menu');
            return;
        }

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);

        if (!$restaurant) {
            $this->redirect('/vendor/setup');
            return;
        }

        try {
            $data = [
                'restaurant_id' => $restaurant['id'],
                'name' => $this->request->post('name'),
                'description' => $this->request->post('description'),
                'price' => (float) $this->request->post('price'),
                'category_id' => (int) $this->request->post('category_id'),
                'stock' => (int) $this->request->post('stock', 999),
                'is_available' => $this->request->post('is_available') ? 1 : 0
            ];

            $this->menuItemModel->create($data);
            
            $this->session->setFlash('success', 'Menu item added successfully!');
            $this->redirect('/vendor/menu');

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Failed to add menu item: ' . $e->getMessage());
            $this->redirect('/vendor/menu/create');
        }
    }

    public function show(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);
        $menuItem = $this->menuItemModel->find($id);

        if (!$restaurant || !$menuItem || $menuItem['restaurant_id'] !== $restaurant['id']) {
            $this->session->setFlash('error', 'Menu item not found');
            $this->redirect('/vendor/menu');
            return;
        }

        $this->render('vendor/menu/show', [
            'title' => $menuItem['name'] . ' - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'menuItem' => $menuItem,
            'currentPage' => 'menu'
        ]);
    }

    public function toggleStatus(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendor/menu');
            return;
        }

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);
        $menuItem = $this->menuItemModel->find($id);

        if (!$restaurant || !$menuItem || $menuItem['restaurant_id'] !== $restaurant['id']) {
            $this->session->setFlash('error', 'Menu item not found');
            $this->redirect('/vendor/menu');
            return;
        }

        try {
            $newStatus = $menuItem['is_available'] ? 0 : 1;
            $this->menuItemModel->update($id, ['is_available' => $newStatus]);
            
            $status = $newStatus ? 'enabled' : 'disabled';
            $this->session->setFlash('success', "Menu item {$status} successfully!");

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Failed to update menu item status: ' . $e->getMessage());
        }

        $this->redirect('/vendor/menu');
    }
}