<?php

namespace Time2Eat\Controllers\Vendor;

use core\BaseController;
use models\Restaurant;
use models\Order;
use models\MenuItem;

class AnalyticsController extends BaseController
{
    private Restaurant $restaurantModel;
    private Order $orderModel;
    private MenuItem $menuItemModel;

    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
        $this->restaurantModel = new Restaurant();
        $this->orderModel = new Order();
        $this->menuItemModel = new MenuItem();
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

        $period = $_GET['period'] ?? '7days';
        
        // Get analytics data
        $salesData = $this->orderModel->getSalesAnalytics($restaurant['id'], $period);
        $popularItems = $this->menuItemModel->getPopularItemsAnalytics($restaurant['id'], $period);
        $customerAnalytics = $this->orderModel->getCustomerAnalytics($restaurant['id'], $period);
        $revenueBreakdown = $this->orderModel->getRevenueBreakdown($restaurant['id'], $period);

        $this->render('vendor/analytics/index', [
            'title' => 'Analytics - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'salesData' => $salesData,
            'popularItems' => $popularItems,
            'customerAnalytics' => $customerAnalytics,
            'revenueBreakdown' => $revenueBreakdown,
            'currentPeriod' => $period,
            'currentPage' => 'analytics'
        ]);
    }

    public function sales(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);

        if (!$restaurant) {
            $this->redirect('/vendor/setup');
            return;
        }

        $period = $_GET['period'] ?? '30days';
        $salesData = $this->orderModel->getDetailedSalesAnalytics($restaurant['id'], $period);

        $this->render('vendor/analytics/sales', [
            'title' => 'Sales Analytics - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'salesData' => $salesData,
            'currentPeriod' => $period
        ]);
    }

    public function export(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);

        if (!$restaurant) {
            $this->redirect('/vendor/analytics');
            return;
        }

        $period = $_GET['period'] ?? '30days';
        $format = $_GET['format'] ?? 'excel';

        try {
            // Generate and download analytics export
            $exportData = $this->orderModel->getExportData($restaurant['id'], $period);
            
            if ($format === 'excel') {
                $this->exportToExcel($exportData, "analytics_report_{$period}.xlsx");
            } else {
                $this->exportToCSV($exportData, "analytics_report_{$period}.csv");
            }

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Failed to export analytics: ' . $e->getMessage());
            $this->redirect('/vendor/analytics');
        }
    }

    private function exportToExcel(array $data, string $filename): void
    {
        // Implementation for Excel export using PhpSpreadsheet
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Basic implementation - would need proper PhpSpreadsheet integration
        echo "Excel export functionality would be implemented here with PhpSpreadsheet";
        exit;
    }

    private function exportToCSV(array $data, string $filename): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}