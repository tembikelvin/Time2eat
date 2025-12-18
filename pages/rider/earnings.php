<?php
/**
 * Rider Earnings Page - Hybrid Router Compatible
 */

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rider') {
    header('Location: ' . url('/login'));
    exit;
}

// Load dependencies
require_once BASE_PATH . '/src/models/Order.php';
require_once BASE_PATH . '/src/models/User.php';

try {
    $db = dbConnection();
    $riderId = $_SESSION['user_id'];
    
    // Get user data
    $userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$riderId]);
    $user = $userStmt->fetch(PDO::FETCH_OBJ);
    
    // Get period filter
    $period = $_GET['period'] ?? 'month';
    
    // Calculate date range
    switch ($period) {
        case 'today':
            $dateCondition = "DATE(o.created_at) = CURDATE()";
            break;
        case 'week':
            $dateCondition = "DATE(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $dateCondition = "DATE(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
        case 'all':
        default:
            $dateCondition = "1=1";
            break;
    }
    
    // Get earnings summary
    $earningsQuery = "
        SELECT 
            COUNT(*) as total_deliveries,
            COALESCE(SUM(o.delivery_fee), 0) as total_delivery_fees,
            COALESCE(SUM(o.delivery_fee * 0.7), 0) as total_earnings,
            COALESCE(AVG(o.delivery_fee * 0.7), 0) as avg_earning_per_delivery,
            COALESCE(SUM(o.delivery_distance), 0) as total_distance
        FROM orders o
        WHERE o.rider_id = ? 
        AND o.status = 'delivered'
        AND {$dateCondition}
    ";
    $earningsStmt = $db->prepare($earningsQuery);
    $earningsStmt->execute([$riderId]);
    $earningsSummary = $earningsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get earnings breakdown by day
    $breakdownQuery = "
        SELECT 
            DATE(o.created_at) as date,
            COUNT(*) as deliveries,
            COALESCE(SUM(o.delivery_fee * 0.7), 0) as earnings
        FROM orders o
        WHERE o.rider_id = ? 
        AND o.status = 'delivered'
        AND {$dateCondition}
        GROUP BY DATE(o.created_at)
        ORDER BY date DESC
    ";
    $breakdownStmt = $db->prepare($breakdownQuery);
    $breakdownStmt->execute([$riderId]);
    $earningsBreakdown = $breakdownStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent earnings (last 20 deliveries)
    $recentEarningsQuery = "
        SELECT o.*, r.name as restaurant_name,
               (o.delivery_fee * 0.7) as earning
        FROM orders o
        LEFT JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.rider_id = ? 
        AND o.status = 'delivered'
        ORDER BY o.created_at DESC
        LIMIT 20
    ";
    $recentEarningsStmt = $db->prepare($recentEarningsQuery);
    $recentEarningsStmt->execute([$riderId]);
    $recentEarnings = $recentEarningsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Rider earnings error: " . $e->getMessage());
    $earningsSummary = [
        'total_deliveries' => 0,
        'total_delivery_fees' => 0,
        'total_earnings' => 0,
        'avg_earning_per_delivery' => 0,
        'total_distance' => 0
    ];
    $earningsBreakdown = [];
    $recentEarnings = [];
}

// Set page variables
$title = 'My Earnings - Time2Eat';
$currentPage = 'earnings';
$userRole = 'rider';
$currentPeriod = $period;

// Start output buffering to capture the view content
ob_start();

// Include the rider earnings view
include BASE_PATH . '/src/views/rider/earnings.php';

// Get the content
$content = ob_get_clean();

// Include layout
require_once BASE_PATH . '/src/views/layouts/dashboard.php';
?>
