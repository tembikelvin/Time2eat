<?php
/**
 * Direct API endpoint for user approval
 * This bypasses the router and handles the approval directly
 */

// Set JSON header
header('Content-Type: application/json');

// Load required files first
require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if user is authenticated and is admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized - Admin access required']);
        exit;
    }

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $userId = $input['user_id'] ?? null;
    $adminNotes = $input['admin_notes'] ?? '';
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
        exit;
    }

    // Connect to database
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Check if user is already active
    if ($user['status'] === 'active') {
        echo json_encode(['success' => false, 'message' => 'User is already approved']);
        exit;
    }

    // Update user status to active
    $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $result = $stmt->execute([$userId]);

    if ($result) {
        // Create notification for the user
        try {
            $stmt = $pdo->prepare("
                INSERT INTO popup_notifications (title, message, type, target_audience, target_user_id, is_active, priority, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                'Application Approved',
                'Your ' . $user['role'] . ' application has been approved! You can now access your dashboard.',
                'success',
                $user['role'] . 's',
                $userId,
                1,
                1,
                $_SESSION['user_id']
            ]);
        } catch (Exception $e) {
            error_log("Error creating approval notification: " . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'User approved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to approve user']);
    }

} catch (Exception $e) {
    error_log("Error in approval API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while approving user', 'debug' => $e->getMessage()]);
}
?>
