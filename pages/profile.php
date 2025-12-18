<?php
/**
 * Profile Page - Redirects to Role-Specific Profile
 * 
 * This page redirects users to their appropriate profile page based on their role.
 * It's a fallback for the general /profile route.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: /login');
    exit;
}

// Get user role
$userRole = $_SESSION['user_role'] ?? 'customer';

// Redirect to role-specific profile
switch ($userRole) {
    case 'admin':
        header('Location: /admin/dashboard');
        break;
    case 'vendor':
        header('Location: /vendor/profile');
        break;
    case 'rider':
        header('Location: /rider/profile');
        break;
    case 'customer':
    default:
        header('Location: /customer/profile');
        break;
}

exit;
?>
