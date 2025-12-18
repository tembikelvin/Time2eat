<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';

use core\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Admin Export Controller
 * Handles data export to Excel for admin users
 */
class AdminExportController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
    }

    /**
     * Export Dashboard - Shows available export options
     */
    public function index(): void
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

        // Get export statistics
        $stats = $this->getExportStats();

        $this->renderDashboard('admin/tools/export/index', [
            'title' => 'Data Export - Time2Eat Admin',
            'user' => $userData,
            'stats' => $stats,
            'currentPage' => 'export'
        ]);
    }

    /**
     * Export Users Data
     */
    public function exportUsers(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Get filters from request
            $role = $_GET['role'] ?? null;
            $status = $_GET['status'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;

            // Build query
            $query = "SELECT
                u.id,
                u.username,
                u.email,
                u.first_name,
                u.last_name,
                u.phone,
                u.role,
                u.status,
                u.email_verified_at,
                u.balance,
                u.total_earnings,
                u.referral_count,
                u.last_login_at,
                u.created_at,
                up.date_of_birth,
                up.gender,
                up.address,
                up.city,
                up.state,
                up.country
            FROM users u
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE 1=1";

            $params = [];

            if ($role) {
                $query .= " AND u.role = ?";
                $params[] = $role;
            }

            if ($status) {
                $query .= " AND u.status = ?";
                $params[] = $status;
            }

            if ($dateFrom) {
                $query .= " AND u.created_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }

            if ($dateTo) {
                $query .= " AND u.created_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }

            $query .= " ORDER BY u.created_at DESC";

            $users = $this->fetchAll($query, $params);

            // Create Excel file
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Users');

            // Set headers
            $headers = [
                'ID',
                'Username',
                'Email',
                'First Name',
                'Last Name',
                'Phone',
                'Role',
                'Status',
                'Email Verified',
                'Balance (XAF)',
                'Total Earnings (XAF)',
                'Referral Count',
                'Last Login',
                'Registration Date',
                'Date of Birth',
                'Gender',
                'Address',
                'City',
                'State',
                'Country'
            ];

            $this->setHeaders($sheet, $headers);

            // Add data
            $row = 2;
            foreach ($users as $user) {
                $sheet->setCellValue('A' . $row, $user['id']);
                $sheet->setCellValue('B' . $row, $user['username']);
                $sheet->setCellValue('C' . $row, $user['email']);
                $sheet->setCellValue('D' . $row, $user['first_name']);
                $sheet->setCellValue('E' . $row, $user['last_name']);
                $sheet->setCellValue('F' . $row, $user['phone']);
                $sheet->setCellValue('G' . $row, ucfirst($user['role']));
                $sheet->setCellValue('H' . $row, ucfirst($user['status']));
                $sheet->setCellValue('I' . $row, $user['email_verified_at'] ? 'Yes' : 'No');
                $sheet->setCellValue('J' . $row, (float)$user['balance']);
                $sheet->setCellValue('K' . $row, (float)$user['total_earnings']);
                $sheet->setCellValue('L' . $row, (int)$user['referral_count']);
                $sheet->setCellValue('M' . $row, $user['last_login_at'] ? date('Y-m-d H:i:s', strtotime($user['last_login_at'])) : 'Never');
                $sheet->setCellValue('N' . $row, date('Y-m-d H:i:s', strtotime($user['created_at'])));
                $sheet->setCellValue('O' . $row, $user['date_of_birth'] ? date('Y-m-d', strtotime($user['date_of_birth'])) : '');
                $sheet->setCellValue('P' . $row, ucfirst($user['gender'] ?? ''));
                $sheet->setCellValue('Q' . $row, $user['address'] ?? '');
                $sheet->setCellValue('R' . $row, $user['city'] ?? '');
                $sheet->setCellValue('S' . $row, $user['state'] ?? '');
                $sheet->setCellValue('T' . $row, $user['country'] ?? '');

                // Format currency columns
                $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0');

                $row++;
            }

            // Auto-size columns
            $this->autoSizeColumns($sheet, range('A', 'T'));

            $this->downloadExcel($spreadsheet, 'users_export_' . date('Y-m-d_H-i-s') . '.xlsx');

        } catch (\Exception $e) {
            error_log("Error exporting users: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Export Orders Data
     */
    public function exportOrders(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Get filters
            $status = $_GET['status'] ?? null;
            $paymentStatus = $_GET['payment_status'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;

            // Build query
            $query = "SELECT
                o.id,
                o.order_number,
                o.status,
                o.payment_status,
                o.payment_method,
                o.subtotal,
                o.tax_amount,
                o.delivery_fee,
                o.service_fee,
                o.discount_amount,
                o.total_amount,
                o.currency,
                o.created_at,
                o.estimated_delivery_time,
                o.actual_delivery_time,
                u.first_name as customer_first_name,
                u.last_name as customer_last_name,
                u.email as customer_email,
                u.phone as customer_phone,
                r.name as restaurant_name,
                ru.first_name as vendor_first_name,
                ru.last_name as vendor_last_name,
                rider.first_name as rider_first_name,
                rider.last_name as rider_last_name
            FROM orders o
            LEFT JOIN users u ON o.customer_id = u.id
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN users ru ON r.user_id = ru.id
            LEFT JOIN users rider ON o.rider_id = rider.id
            WHERE 1=1";

            $params = [];

            if ($status) {
                $query .= " AND o.status = ?";
                $params[] = $status;
            }

            if ($paymentStatus) {
                $query .= " AND o.payment_status = ?";
                $params[] = $paymentStatus;
            }

            if ($dateFrom) {
                $query .= " AND o.created_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }

            if ($dateTo) {
                $query .= " AND o.created_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }

            $query .= " ORDER BY o.created_at DESC";

            $orders = $this->fetchAll($query, $params);

            // Create Excel file
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Orders');

            // Set headers
            $headers = [
                'Order ID',
                'Order Number',
                'Status',
                'Payment Status',
                'Payment Method',
                'Subtotal (XAF)',
                'Tax (XAF)',
                'Delivery Fee (XAF)',
                'Service Fee (XAF)',
                'Discount (XAF)',
                'Total (XAF)',
                'Currency',
                'Order Date',
                'Estimated Delivery',
                'Actual Delivery',
                'Customer Name',
                'Customer Email',
                'Customer Phone',
                'Restaurant Name',
                'Vendor Name',
                'Rider Name'
            ];

            $this->setHeaders($sheet, $headers);

            // Add data
            $row = 2;
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $order['id']);
                $sheet->setCellValue('B' . $row, $order['order_number']);
                $sheet->setCellValue('C' . $row, ucfirst($order['status']));
                $sheet->setCellValue('D' . $row, ucfirst($order['payment_status']));
                $sheet->setCellValue('E' . $row, ucfirst(str_replace('_', ' ', $order['payment_method'])));
                $sheet->setCellValue('F' . $row, (float)$order['subtotal']);
                $sheet->setCellValue('G' . $row, (float)$order['tax_amount']);
                $sheet->setCellValue('H' . $row, (float)$order['delivery_fee']);
                $sheet->setCellValue('I' . $row, (float)$order['service_fee']);
                $sheet->setCellValue('J' . $row, (float)$order['discount_amount']);
                $sheet->setCellValue('K' . $row, (float)$order['total_amount']);
                $sheet->setCellValue('L' . $row, $order['currency']);
                $sheet->setCellValue('M' . $row, date('Y-m-d H:i:s', strtotime($order['created_at'])));
                $sheet->setCellValue('N' . $row, $order['estimated_delivery_time'] ? date('Y-m-d H:i:s', strtotime($order['estimated_delivery_time'])) : '');
                $sheet->setCellValue('O' . $row, $order['actual_delivery_time'] ? date('Y-m-d H:i:s', strtotime($order['actual_delivery_time'])) : '');
                $sheet->setCellValue('P' . $row, trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')));
                $sheet->setCellValue('Q' . $row, $order['customer_email'] ?? '');
                $sheet->setCellValue('R' . $row, $order['customer_phone'] ?? '');
                $sheet->setCellValue('S' . $row, $order['restaurant_name'] ?? '');
                $sheet->setCellValue('T' . $row, trim(($order['vendor_first_name'] ?? '') . ' ' . ($order['vendor_last_name'] ?? '')));
                $sheet->setCellValue('U' . $row, trim(($order['rider_first_name'] ?? '') . ' ' . ($order['rider_last_name'] ?? '')));

                // Format currency columns
                foreach (range('F', 'K') as $col) {
                    $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
                }

                $row++;
            }

            // Auto-size columns
            $this->autoSizeColumns($sheet, range('A', 'U'));

            $this->downloadExcel($spreadsheet, 'orders_export_' . date('Y-m-d_H-i-s') . '.xlsx');

        } catch (\Exception $e) {
            error_log("Error exporting orders: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Export Restaurants Data
     */
    public function exportRestaurants(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Get filters
            $status = $_GET['status'] ?? null;
            $isFeatured = $_GET['is_featured'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;

            // Build query
            $query = "SELECT
                r.id,
                r.name,
                r.slug,
                r.description,
                r.phone,
                r.email,
                r.website,
                r.address,
                r.city,
                r.state,
                r.latitude,
                r.longitude,
                r.delivery_radius,
                r.minimum_order,
                r.delivery_fee,
                r.commission_rate,
                r.rating,
                r.total_reviews,
                r.total_orders,
                r.status,
                r.is_featured,
                r.is_open,
                r.created_at,
                u.first_name as owner_first_name,
                u.last_name as owner_last_name,
                u.email as owner_email,
                u.phone as owner_phone
            FROM restaurants r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE 1=1";

            $params = [];

            if ($status) {
                $query .= " AND r.status = ?";
                $params[] = $status;
            }

            if ($isFeatured !== null) {
                $query .= " AND r.is_featured = ?";
                $params[] = (int)$isFeatured;
            }

            if ($dateFrom) {
                $query .= " AND r.created_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }

            if ($dateTo) {
                $query .= " AND r.created_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }

            $query .= " ORDER BY r.created_at DESC";

            $restaurants = $this->fetchAll($query, $params);

            // Create Excel file
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Restaurants');

            // Set headers
            $headers = [
                'ID',
                'Name',
                'Slug',
                'Description',
                'Phone',
                'Email',
                'Website',
                'Address',
                'City',
                'State',
                'Latitude',
                'Longitude',
                'Delivery Radius (KM)',
                'Min Order (XAF)',
                'Delivery Fee (XAF)',
                'Commission Rate (%)',
                'Rating',
                'Total Reviews',
                'Total Orders',
                'Status',
                'Featured',
                'Currently Open',
                'Registration Date',
                'Owner Name',
                'Owner Email',
                'Owner Phone'
            ];

            $this->setHeaders($sheet, $headers);

            // Add data
            $row = 2;
            foreach ($restaurants as $restaurant) {
                $sheet->setCellValue('A' . $row, $restaurant['id']);
                $sheet->setCellValue('B' . $row, $restaurant['name']);
                $sheet->setCellValue('C' . $row, $restaurant['slug']);
                $sheet->setCellValue('D' . $row, $restaurant['description']);
                $sheet->setCellValue('E' . $row, $restaurant['phone']);
                $sheet->setCellValue('F' . $row, $restaurant['email']);
                $sheet->setCellValue('G' . $row, $restaurant['website']);
                $sheet->setCellValue('H' . $row, $restaurant['address']);
                $sheet->setCellValue('I' . $row, $restaurant['city']);
                $sheet->setCellValue('J' . $row, $restaurant['state']);
                $sheet->setCellValue('K' . $row, $restaurant['latitude']);
                $sheet->setCellValue('L' . $row, $restaurant['longitude']);
                $sheet->setCellValue('M' . $row, (float)$restaurant['delivery_radius']);
                $sheet->setCellValue('N' . $row, (float)$restaurant['minimum_order']);
                $sheet->setCellValue('O' . $row, (float)$restaurant['delivery_fee']);
                $sheet->setCellValue('P' . $row, (float)$restaurant['commission_rate'] * 100);
                $sheet->setCellValue('Q' . $row, (float)$restaurant['rating']);
                $sheet->setCellValue('R' . $row, (int)$restaurant['total_reviews']);
                $sheet->setCellValue('S' . $row, (int)$restaurant['total_orders']);
                $sheet->setCellValue('T' . $row, ucfirst($restaurant['status']));
                $sheet->setCellValue('U' . $row, $restaurant['is_featured'] ? 'Yes' : 'No');
                $sheet->setCellValue('V' . $row, $restaurant['is_open'] ? 'Yes' : 'No');
                $sheet->setCellValue('W' . $row, date('Y-m-d H:i:s', strtotime($restaurant['created_at'])));
                $sheet->setCellValue('X' . $row, trim(($restaurant['owner_first_name'] ?? '') . ' ' . ($restaurant['owner_last_name'] ?? '')));
                $sheet->setCellValue('Y' . $row, $restaurant['owner_email'] ?? '');
                $sheet->setCellValue('Z' . $row, $restaurant['owner_phone'] ?? '');

                // Format currency columns
                $sheet->getStyle('N' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('O' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('P' . $row)->getNumberFormat()->setFormatCode('0.00');

                $row++;
            }

            // Auto-size columns
            $this->autoSizeColumns($sheet, range('A', 'Z'));

            $this->downloadExcel($spreadsheet, 'restaurants_export_' . date('Y-m-d_H-i-s') . '.xlsx');

        } catch (\Exception $e) {
            error_log("Error exporting restaurants: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Export Payments Data
     */
    public function exportPayments(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Get filters
            $type = $_GET['type'] ?? null;
            $status = $_GET['status'] ?? null;
            $method = $_GET['method'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;

            // Build query
            $query = "SELECT
                p.id,
                p.transaction_id,
                p.reference_number,
                p.type,
                p.method,
                p.provider,
                p.amount,
                p.fee,
                p.net_amount,
                p.status,
                p.created_at,
                p.processed_at,
                u.first_name,
                u.last_name,
                u.email,
                o.order_number
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN orders o ON p.order_id = o.id
            WHERE 1=1";

            $params = [];

            if ($type) {
                $query .= " AND p.type = ?";
                $params[] = $type;
            }

            if ($status) {
                $query .= " AND p.status = ?";
                $params[] = $status;
            }

            if ($method) {
                $query .= " AND p.method = ?";
                $params[] = $method;
            }

            if ($dateFrom) {
                $query .= " AND p.created_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }

            if ($dateTo) {
                $query .= " AND p.created_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }

            $query .= " ORDER BY p.created_at DESC";

            $payments = $this->fetchAll($query, $params);

            // Create Excel file
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Payments');

            // Set headers
            $headers = [
                'ID',
                'Transaction ID',
                'Reference Number',
                'Type',
                'Method',
                'Provider',
                'Amount (XAF)',
                'Fee (XAF)',
                'Net Amount (XAF)',
                'Status',
                'Created Date',
                'Processed Date',
                'Customer Name',
                'Customer Email',
                'Order Number'
            ];

            $this->setHeaders($sheet, $headers);

            // Add data
            $row = 2;
            foreach ($payments as $payment) {
                $sheet->setCellValue('A' . $row, $payment['id']);
                $sheet->setCellValue('B' . $row, $payment['transaction_id']);
                $sheet->setCellValue('C' . $row, $payment['reference_number']);
                $sheet->setCellValue('D' . $row, ucfirst($payment['type']));
                $sheet->setCellValue('E' . $row, ucfirst(str_replace('_', ' ', $payment['method'])));
                $sheet->setCellValue('F' . $row, $payment['provider'] ?? '');
                $sheet->setCellValue('G' . $row, (float)$payment['amount']);
                $sheet->setCellValue('H' . $row, (float)$payment['fee']);
                $sheet->setCellValue('I' . $row, (float)$payment['net_amount']);
                $sheet->setCellValue('J' . $row, ucfirst($payment['status']));
                $sheet->setCellValue('K' . $row, date('Y-m-d H:i:s', strtotime($payment['created_at'])));
                $sheet->setCellValue('L' . $row, $payment['processed_at'] ? date('Y-m-d H:i:s', strtotime($payment['processed_at'])) : '');
                $sheet->setCellValue('M' . $row, trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')));
                $sheet->setCellValue('N' . $row, $payment['email'] ?? '');
                $sheet->setCellValue('O' . $row, $payment['order_number'] ?? '');

                // Format currency columns
                foreach (range('G', 'I') as $col) {
                    $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
                }

                $row++;
            }

            // Auto-size columns
            $this->autoSizeColumns($sheet, range('A', 'O'));

            $this->downloadExcel($spreadsheet, 'payments_export_' . date('Y-m-d_H-i-s') . '.xlsx');

        } catch (\Exception $e) {
            error_log("Error exporting payments: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Export Reviews Data
     */
    public function exportReviews(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Get filters
            $reviewableType = $_GET['reviewable_type'] ?? null;
            $status = $_GET['status'] ?? null;
            $rating = $_GET['rating'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;

            // Build query
            $query = "SELECT
                r.id,
                r.reviewable_type,
                r.rating,
                r.title,
                r.comment,
                r.is_verified,
                r.is_featured,
                r.helpful_count,
                r.unhelpful_count,
                r.status,
                r.created_at,
                u.first_name as reviewer_first_name,
                u.last_name as reviewer_last_name,
                u.email as reviewer_email,
                CASE
                    WHEN r.reviewable_type = 'restaurant' THEN rest.name
                    WHEN r.reviewable_type = 'menu_item' THEN mi.name
                    ELSE 'Unknown'
                END as reviewed_item_name,
                o.order_number
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN orders o ON r.order_id = o.id
            LEFT JOIN restaurants rest ON r.reviewable_type = 'restaurant' AND r.reviewable_id = rest.id
            LEFT JOIN menu_items mi ON r.reviewable_type = 'menu_item' AND r.reviewable_id = mi.id
            WHERE 1=1";

            $params = [];

            if ($reviewableType) {
                $query .= " AND r.reviewable_type = ?";
                $params[] = $reviewableType;
            }

            if ($status) {
                $query .= " AND r.status = ?";
                $params[] = $status;
            }

            if ($rating) {
                $query .= " AND r.rating = ?";
                $params[] = (int)$rating;
            }

            if ($dateFrom) {
                $query .= " AND r.created_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }

            if ($dateTo) {
                $query .= " AND r.created_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }

            $query .= " ORDER BY r.created_at DESC";

            $reviews = $this->fetchAll($query, $params);

            // Create Excel file
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Reviews');

            // Set headers
            $headers = [
                'ID',
                'Type',
                'Rating',
                'Title',
                'Comment',
                'Verified Purchase',
                'Featured',
                'Helpful Votes',
                'Unhelpful Votes',
                'Status',
                'Created Date',
                'Reviewer Name',
                'Reviewer Email',
                'Reviewed Item',
                'Order Number'
            ];

            $this->setHeaders($sheet, $headers);

            // Add data
            $row = 2;
            foreach ($reviews as $review) {
                $sheet->setCellValue('A' . $row, $review['id']);
                $sheet->setCellValue('B' . $row, ucfirst($review['reviewable_type']));
                $sheet->setCellValue('C' . $row, (int)$review['rating']);
                $sheet->setCellValue('D' . $row, $review['title'] ?? '');
                $sheet->setCellValue('E' . $row, $review['comment'] ?? '');
                $sheet->setCellValue('F' . $row, $review['is_verified'] ? 'Yes' : 'No');
                $sheet->setCellValue('G' . $row, $review['is_featured'] ? 'Yes' : 'No');
                $sheet->setCellValue('H' . $row, (int)$review['helpful_count']);
                $sheet->setCellValue('I' . $row, (int)$review['unhelpful_count']);
                $sheet->setCellValue('J' . $row, ucfirst($review['status']));
                $sheet->setCellValue('K' . $row, date('Y-m-d H:i:s', strtotime($review['created_at'])));
                $sheet->setCellValue('L' . $row, trim(($review['reviewer_first_name'] ?? '') . ' ' . ($review['reviewer_last_name'] ?? '')));
                $sheet->setCellValue('M' . $row, $review['reviewer_email'] ?? '');
                $sheet->setCellValue('N' . $row, $review['reviewed_item_name'] ?? '');
                $sheet->setCellValue('O' . $row, $review['order_number'] ?? '');

                $row++;
            }

            // Auto-size columns
            $this->autoSizeColumns($sheet, range('A', 'O'));

            $this->downloadExcel($spreadsheet, 'reviews_export_' . date('Y-m-d_H-i-s') . '.xlsx');

        } catch (\Exception $e) {
            error_log("Error exporting reviews: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Export Analytics Data
     */
    public function exportAnalytics(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            // Get date range
            $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
            $dateTo = $_GET['date_to'] ?? date('Y-m-d');

            // Get analytics data
            $analytics = $this->fetchAll("
                SELECT * FROM analytics
                WHERE date BETWEEN ? AND ?
                ORDER BY date DESC, created_at DESC
            ", [$dateFrom, $dateTo]);

            // Get daily stats
            $dailyStats = $this->fetchAll("
                SELECT * FROM daily_stats
                WHERE date BETWEEN ? AND ?
                ORDER BY date DESC
            ", [$dateFrom, $dateTo]);

            // Create Excel file with multiple sheets
            $spreadsheet = new Spreadsheet();

            // Analytics sheet
            $analyticsSheet = $spreadsheet->getActiveSheet();
            $analyticsSheet->setTitle('Analytics');

            $headers = ['ID', 'Metric Name', 'Value', 'Dimensions', 'Date', 'Hour', 'Created At'];
            $this->setHeaders($analyticsSheet, $headers);

            $row = 2;
            foreach ($analytics as $analytic) {
                $analyticsSheet->setCellValue('A' . $row, $analytic['id']);
                $analyticsSheet->setCellValue('B' . $row, $analytic['metric_name']);
                $analyticsSheet->setCellValue('C' . $row, (float)$analytic['metric_value']);
                $analyticsSheet->setCellValue('D' . $row, $analytic['dimensions'] ?? '');
                $analyticsSheet->setCellValue('E' . $row, $analytic['date']);
                $analyticsSheet->setCellValue('F' . $row, $analytic['hour'] ?? '');
                $analyticsSheet->setCellValue('G' . $row, date('Y-m-d H:i:s', strtotime($analytic['created_at'])));
                $row++;
            }

            $this->autoSizeColumns($analyticsSheet, range('A', 'G'));

            // Daily Stats sheet
            $statsSheet = $spreadsheet->createSheet();
            $statsSheet->setTitle('Daily Statistics');

            $headers = [
                'ID', 'Date', 'Restaurant ID', 'User ID', 'Total Orders', 'Total Revenue (XAF)',
                'Total Commission (XAF)', 'Total Deliveries', 'Avg Order Value (XAF)',
                'Avg Delivery Time (mins)', 'Customer Satisfaction', 'New Customers',
                'Returning Customers', 'Cancelled Orders', 'Refunded Orders'
            ];
            $this->setHeaders($statsSheet, $headers);

            $row = 2;
            foreach ($dailyStats as $stat) {
                $statsSheet->setCellValue('A' . $row, $stat['id']);
                $statsSheet->setCellValue('B' . $row, $stat['date']);
                $statsSheet->setCellValue('C' . $row, $stat['restaurant_id'] ?? '');
                $statsSheet->setCellValue('D' . $row, $stat['user_id'] ?? '');
                $statsSheet->setCellValue('E' . $row, (int)$stat['total_orders']);
                $statsSheet->setCellValue('F' . $row, (float)$stat['total_revenue']);
                $statsSheet->setCellValue('G' . $row, (float)$stat['total_commission']);
                $statsSheet->setCellValue('H' . $row, (int)$stat['total_deliveries']);
                $statsSheet->setCellValue('I' . $row, (float)$stat['average_order_value']);
                $statsSheet->setCellValue('J' . $row, (float)$stat['average_delivery_time']);
                $statsSheet->setCellValue('K' . $row, (float)$stat['customer_satisfaction']);
                $statsSheet->setCellValue('L' . $row, (int)$stat['new_customers']);
                $statsSheet->setCellValue('M' . $row, (int)$stat['returning_customers']);
                $statsSheet->setCellValue('N' . $row, (int)$stat['cancelled_orders']);
                $statsSheet->setCellValue('O' . $row, (int)$stat['refunded_orders']);

                // Format currency columns
                $statsSheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $statsSheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $statsSheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');

                $row++;
            }

            $this->autoSizeColumns($statsSheet, range('A', 'O'));

            $this->downloadExcel($spreadsheet, 'analytics_export_' . date('Y-m-d_H-i-s') . '.xlsx');

        } catch (\Exception $e) {
            error_log("Error exporting analytics: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Export Dashboard Data
     */
    public function exportDashboard(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Dashboard Statistics');

            // Get dashboard stats
            $stats = $this->getDashboardStatistics();
            
            // Dashboard Overview Section
            $sheet->setCellValue('A1', 'Time2Eat Admin Dashboard Export');
            $sheet->mergeCells('A1:B1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->setCellValue('A2', 'Export Date');
            $sheet->setCellValue('B2', date('Y-m-d H:i:s'));
            $sheet->getStyle('A2')->getFont()->setBold(true);
            
            // Main Statistics
            $row = 4;
            $sheet->setCellValue('A' . $row, 'KEY METRICS');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']]
            ]);
            $row++;
            
            $this->setHeaders($sheet, ['Metric', 'Value']);
            $row++;
            
            // Add statistics
            $metrics = [
                'Total Users' => number_format($stats['total_users']),
                'Total Customers' => number_format($stats['total_customers']),
                'Total Orders' => number_format($stats['total_orders']),
                'Pending Orders' => number_format($stats['pending_orders']),
                'Completed Today' => number_format($stats['completed_orders_today']),
                'Active Restaurants' => number_format($stats['active_restaurants']),
                'Active Riders' => number_format($stats['active_riders']),
                'Total Revenue (XAF)' => number_format($stats['total_revenue']),
                'Revenue Today (XAF)' => number_format($stats['revenue_today'] ?? 0),
                'Average Order Value (XAF)' => number_format($stats['avg_order_value'] ?? 0)
            ];
            
            foreach ($metrics as $metric => $value) {
                $sheet->setCellValue('A' . $row, $metric);
                $sheet->setCellValue('B' . $row, $value);
                $row++;
            }
            
            // Recent Orders Section
            $row += 2;
            $sheet->setCellValue('A' . $row, 'RECENT ORDERS (Last 20)');
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']]
            ]);
            $row++;
            
            $orderHeaders = ['Order ID', 'Customer', 'Restaurant', 'Amount (XAF)', 'Status', 'Date', 'Time'];
            $col = 'A';
            foreach ($orderHeaders as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
                $col++;
            }
            $row++;
            
            // Get recent orders
            $recentOrders = $this->fetchAll("
                SELECT 
                    o.id, o.order_number, o.total_amount, o.status, o.created_at,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    r.name as restaurant_name
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                ORDER BY o.created_at DESC
                LIMIT 20
            ");
            
            foreach ($recentOrders as $order) {
                $sheet->setCellValue('A' . $row, $order['order_number'] ?? $order['id']);
                $sheet->setCellValue('B' . $row, $order['customer_name'] ?? 'N/A');
                $sheet->setCellValue('C' . $row, $order['restaurant_name'] ?? 'N/A');
                $sheet->setCellValue('D' . $row, number_format((float)$order['total_amount']));
                $sheet->setCellValue('E' . $row, ucfirst($order['status']));
                $sheet->setCellValue('F' . $row, date('Y-m-d', strtotime($order['created_at'])));
                $sheet->setCellValue('G' . $row, date('H:i:s', strtotime($order['created_at'])));
                $row++;
            }
            
            // User Distribution Section
            $row += 2;
            $sheet->setCellValue('A' . $row, 'USER DISTRIBUTION BY ROLE');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']]
            ]);
            $row++;
            
            $this->setHeaders($sheet, ['Role', 'Count']);
            $row++;
            
            $usersByRole = $this->fetchAll("
                SELECT role, COUNT(*) as count
                FROM users
                WHERE deleted_at IS NULL
                GROUP BY role
                ORDER BY count DESC
            ");
            
            foreach ($usersByRole as $roleData) {
                $sheet->setCellValue('A' . $row, ucfirst($roleData['role']));
                $sheet->setCellValue('B' . $row, number_format((int)$roleData['count']));
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $this->downloadExcel($spreadsheet, 'admin_dashboard_export_' . date('Y-m-d_H-i-s') . '.xlsx');
            
        } catch (\Exception $e) {
            error_log("Error exporting dashboard: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get Dashboard Statistics
     */
    private function getDashboardStatistics(): array
    {
        $stats = [
            'total_users' => 0,
            'total_customers' => 0,
            'total_orders' => 0,
            'pending_orders' => 0,
            'completed_orders_today' => 0,
            'active_restaurants' => 0,
            'active_riders' => 0,
            'total_revenue' => 0,
            'revenue_today' => 0,
            'avg_order_value' => 0
        ];

        try {
            // User statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL");
            $stats['total_users'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND deleted_at IS NULL");
            $stats['total_customers'] = $result['count'] ?? 0;

            // Order statistics
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders");
            $stats['total_orders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'confirmed', 'preparing')");
            $stats['pending_orders'] = $result['count'] ?? 0;

            $result = $this->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()");
            $stats['completed_orders_today'] = $result['count'] ?? 0;

            // Revenue statistics
            $result = $this->fetchOne("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'delivered'");
            $stats['total_revenue'] = $result['revenue'] ?? 0;

            $result = $this->fetchOne("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()");
            $stats['revenue_today'] = $result['revenue'] ?? 0;

            $result = $this->fetchOne("SELECT AVG(total_amount) as avg_value FROM orders WHERE status = 'delivered'");
            $stats['avg_order_value'] = $result['avg_value'] ?? 0;

            // Restaurant count
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM restaurants WHERE status = 'active' AND deleted_at IS NULL");
            $stats['active_restaurants'] = $result['count'] ?? 0;

            // Active riders
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'rider' AND status = 'active' AND deleted_at IS NULL");
            $stats['active_riders'] = $result['count'] ?? 0;

        } catch (\Exception $e) {
            error_log("Error getting dashboard statistics: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Export Complete System Data (Multiple Sheets)
     */
    public function exportAllData(): void
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $spreadsheet = new Spreadsheet();

            // Export Users
            $usersSheet = $spreadsheet->createSheet();
            $usersSheet->setTitle('Users');
            $this->exportUsersToSheet($usersSheet);

            // Export Restaurants
            $restaurantsSheet = $spreadsheet->createSheet();
            $restaurantsSheet->setTitle('Restaurants');
            $this->exportRestaurantsToSheet($restaurantsSheet);

            // Export Orders
            $ordersSheet = $spreadsheet->createSheet();
            $ordersSheet->setTitle('Orders');
            $this->exportOrdersToSheet($ordersSheet);

            // Export Menu Items
            $menuSheet = $spreadsheet->createSheet();
            $menuSheet->setTitle('Menu Items');
            $this->exportMenuItemsToSheet($menuSheet);

            // Export Payments
            $paymentsSheet = $spreadsheet->createSheet();
            $paymentsSheet->setTitle('Payments');
            $this->exportPaymentsToSheet($paymentsSheet);

            // Export Reviews
            $reviewsSheet = $spreadsheet->createSheet();
            $reviewsSheet->setTitle('Reviews');
            $this->exportReviewsToSheet($reviewsSheet);

            // Set first sheet as active
            $spreadsheet->setActiveSheetIndex(0);

            $this->downloadExcel($spreadsheet, 'complete_system_export_' . date('Y-m-d_H-i-s') . '.xlsx');

        } catch (\Exception $e) {
            error_log("Error exporting complete data: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Helper Methods for Excel Formatting
     */
    private function setHeaders($sheet, array $headers): void
    {
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);

            // Style header
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            $col++;
        }
    }

    private function autoSizeColumns($sheet, array $columns): void
    {
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function downloadExcel($spreadsheet, string $filename): void
    {
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Helper methods for multi-sheet export
     */
    private function exportUsersToSheet($sheet): void
    {
        $users = $this->fetchAll("
            SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.phone, u.role, u.status,
                   u.balance, u.created_at
            FROM users u ORDER BY u.created_at DESC LIMIT 10000
        ");

        $headers = ['ID', 'Username', 'Email', 'First Name', 'Last Name', 'Phone', 'Role', 'Status', 'Balance (XAF)', 'Registration Date'];
        $this->setHeaders($sheet, $headers);

        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user['id']);
            $sheet->setCellValue('B' . $row, $user['username']);
            $sheet->setCellValue('C' . $row, $user['email']);
            $sheet->setCellValue('D' . $row, $user['first_name']);
            $sheet->setCellValue('E' . $row, $user['last_name']);
            $sheet->setCellValue('F' . $row, $user['phone']);
            $sheet->setCellValue('G' . $row, ucfirst($user['role']));
            $sheet->setCellValue('H' . $row, ucfirst($user['status']));
            $sheet->setCellValue('I' . $row, (float)$user['balance']);
            $sheet->setCellValue('J' . $row, date('Y-m-d H:i:s', strtotime($user['created_at'])));

            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $this->autoSizeColumns($sheet, range('A', 'J'));
    }

    private function exportRestaurantsToSheet($sheet): void
    {
        $restaurants = $this->fetchAll("
            SELECT r.id, r.name, r.phone, r.email, r.city, r.status, r.rating, r.total_orders, r.created_at
            FROM restaurants r ORDER BY r.created_at DESC LIMIT 10000
        ");

        $headers = ['ID', 'Name', 'Phone', 'Email', 'City', 'Status', 'Rating', 'Total Orders', 'Registration Date'];
        $this->setHeaders($sheet, $headers);

        $row = 2;
        foreach ($restaurants as $restaurant) {
            $sheet->setCellValue('A' . $row, $restaurant['id']);
            $sheet->setCellValue('B' . $row, $restaurant['name']);
            $sheet->setCellValue('C' . $row, $restaurant['phone']);
            $sheet->setCellValue('D' . $row, $restaurant['email']);
            $sheet->setCellValue('E' . $row, $restaurant['city']);
            $sheet->setCellValue('F' . $row, ucfirst($restaurant['status']));
            $sheet->setCellValue('G' . $row, (float)$restaurant['rating']);
            $sheet->setCellValue('H' . $row, (int)$restaurant['total_orders']);
            $sheet->setCellValue('I' . $row, date('Y-m-d H:i:s', strtotime($restaurant['created_at'])));
            $row++;
        }

        $this->autoSizeColumns($sheet, range('A', 'I'));
    }

    private function exportOrdersToSheet($sheet): void
    {
        $orders = $this->fetchAll("
            SELECT o.id, o.order_number, o.status, o.payment_status, o.total_amount, o.created_at,
                   u.first_name, u.last_name, r.name as restaurant_name
            FROM orders o
            LEFT JOIN users u ON o.customer_id = u.id
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            ORDER BY o.created_at DESC LIMIT 10000
        ");

        $headers = ['ID', 'Order Number', 'Status', 'Payment Status', 'Total (XAF)', 'Order Date', 'Customer Name', 'Restaurant'];
        $this->setHeaders($sheet, $headers);

        $row = 2;
        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $order['id']);
            $sheet->setCellValue('B' . $row, $order['order_number']);
            $sheet->setCellValue('C' . $row, ucfirst($order['status']));
            $sheet->setCellValue('D' . $row, ucfirst($order['payment_status']));
            $sheet->setCellValue('E' . $row, (float)$order['total_amount']);
            $sheet->setCellValue('F' . $row, date('Y-m-d H:i:s', strtotime($order['created_at'])));
            $sheet->setCellValue('G' . $row, trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')));
            $sheet->setCellValue('H' . $row, $order['restaurant_name'] ?? '');

            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $this->autoSizeColumns($sheet, range('A', 'H'));
    }

    private function exportMenuItemsToSheet($sheet): void
    {
        $menuItems = $this->fetchAll("
            SELECT mi.id, mi.name, mi.price, mi.is_available, mi.sold_count, mi.rating,
                   r.name as restaurant_name, c.name as category_name
            FROM menu_items mi
            LEFT JOIN restaurants r ON mi.restaurant_id = r.id
            LEFT JOIN categories c ON mi.category_id = c.id
            ORDER BY mi.created_at DESC LIMIT 10000
        ");

        $headers = ['ID', 'Name', 'Price (XAF)', 'Available', 'Sold Count', 'Rating', 'Restaurant', 'Category'];
        $this->setHeaders($sheet, $headers);

        $row = 2;
        foreach ($menuItems as $item) {
            $sheet->setCellValue('A' . $row, $item['id']);
            $sheet->setCellValue('B' . $row, $item['name']);
            $sheet->setCellValue('C' . $row, (float)$item['price']);
            $sheet->setCellValue('D' . $row, $item['is_available'] ? 'Yes' : 'No');
            $sheet->setCellValue('E' . $row, (int)$item['sold_count']);
            $sheet->setCellValue('F' . $row, (float)$item['rating']);
            $sheet->setCellValue('G' . $row, $item['restaurant_name'] ?? '');
            $sheet->setCellValue('H' . $row, $item['category_name'] ?? '');

            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $this->autoSizeColumns($sheet, range('A', 'H'));
    }

    private function exportPaymentsToSheet($sheet): void
    {
        $payments = $this->fetchAll("
            SELECT p.id, p.transaction_id, p.type, p.method, p.amount, p.status, p.created_at,
                   u.first_name, u.last_name
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC LIMIT 10000
        ");

        $headers = ['ID', 'Transaction ID', 'Type', 'Method', 'Amount (XAF)', 'Status', 'Date', 'Customer Name'];
        $this->setHeaders($sheet, $headers);

        $row = 2;
        foreach ($payments as $payment) {
            $sheet->setCellValue('A' . $row, $payment['id']);
            $sheet->setCellValue('B' . $row, $payment['transaction_id']);
            $sheet->setCellValue('C' . $row, ucfirst($payment['type']));
            $sheet->setCellValue('D' . $row, ucfirst(str_replace('_', ' ', $payment['method'])));
            $sheet->setCellValue('E' . $row, (float)$payment['amount']);
            $sheet->setCellValue('F' . $row, ucfirst($payment['status']));
            $sheet->setCellValue('G' . $row, date('Y-m-d H:i:s', strtotime($payment['created_at'])));
            $sheet->setCellValue('H' . $row, trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')));

            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $this->autoSizeColumns($sheet, range('A', 'H'));
    }

    private function exportReviewsToSheet($sheet): void
    {
        $reviews = $this->fetchAll("
            SELECT r.id, r.reviewable_type, r.rating, r.title, r.status, r.created_at,
                   u.first_name, u.last_name
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC LIMIT 10000
        ");

        $headers = ['ID', 'Type', 'Rating', 'Title', 'Status', 'Date', 'Reviewer Name'];
        $this->setHeaders($sheet, $headers);

        $row = 2;
        foreach ($reviews as $review) {
            $sheet->setCellValue('A' . $row, $review['id']);
            $sheet->setCellValue('B' . $row, ucfirst($review['reviewable_type']));
            $sheet->setCellValue('C' . $row, (int)$review['rating']);
            $sheet->setCellValue('D' . $row, $review['title'] ?? '');
            $sheet->setCellValue('E' . $row, ucfirst($review['status']));
            $sheet->setCellValue('F' . $row, date('Y-m-d H:i:s', strtotime($review['created_at'])));
            $sheet->setCellValue('G' . $row, trim(($review['first_name'] ?? '') . ' ' . ($review['last_name'] ?? '')));
            $row++;
        }

        $this->autoSizeColumns($sheet, range('A', 'G'));
    }

    /**
     * Get export statistics for dashboard
     */
    private function getExportStats(): array
    {
        try {
            return [
                'total_users' => $this->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
                'total_restaurants' => $this->fetchOne("SELECT COUNT(*) as count FROM restaurants")['count'] ?? 0,
                'total_orders' => $this->fetchOne("SELECT COUNT(*) as count FROM orders")['count'] ?? 0,
                'total_payments' => $this->fetchOne("SELECT COUNT(*) as count FROM payments")['count'] ?? 0,
                'total_reviews' => $this->fetchOne("SELECT COUNT(*) as count FROM reviews")['count'] ?? 0,
                'last_export' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            error_log("Error getting export stats: " . $e->getMessage());
            return [
                'total_users' => 0,
                'total_restaurants' => 0,
                'total_orders' => 0,
                'total_payments' => 0,
                'total_reviews' => 0,
                'last_export' => 'Never'
            ];
        }
    }


    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Render dashboard with proper layout
     */
    protected function renderDashboard(string $view, array $data = []): void
    {
        // Get current user and convert to array for view compatibility
        $currentUser = $this->getCurrentUser();
        if ($currentUser) {
            $userData = [
                'id' => $currentUser->id,
                'email' => $currentUser->email,
                'first_name' => $currentUser->first_name,
                'last_name' => $currentUser->last_name,
                'role' => $currentUser->role,
                'status' => $currentUser->status ?? 'active'
            ];

            // Add user data to view data if not already set
            if (!isset($data['user'])) {
                $data['user'] = $userData;
            }
            if (!isset($data['userRole'])) {
                $data['userRole'] = $currentUser->role;
            }
        }

        // Start output buffering to capture the dashboard content
        ob_start();

        // Extract data for the view
        extract($data);

        // Include the specific dashboard view using correct relative path
        $viewPath = __DIR__ . "/../views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("Dashboard view not found: {$view}");
        }
        include $viewPath;

        // Get the content
        $content = ob_get_clean();

        // Render with dashboard layout using correct relative path
        $layoutPath = __DIR__ . "/../views/components/dashboard-layout.php";
        if (!file_exists($layoutPath)) {
            throw new \Exception("Dashboard layout not found: dashboard-layout.php");
        }
        include $layoutPath;
    }
}
