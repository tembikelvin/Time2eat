<?php
/**
 * Rider Deliveries Page - Hybrid Router Compatible
 * Delegates to RiderDashboardController for consistent logic
 */

// Load controller
require_once BASE_PATH . '/src/controllers/RiderDashboardController.php';

// Instantiate and call controller
$controller = new \controllers\RiderDashboardController();
$controller->deliveries();
