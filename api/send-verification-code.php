<?php
/**
 * API Endpoint: Send Email Verification Code
 * Sends a 6-digit verification code to the user's email
 */

// Prevent any output before JSON response
ob_start();

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// CRITICAL: Prevent caching of verification code response (user-specific, must be real-time)
header('Cache-Control: no-cache, no-store, must-revalidate, private');
header('Pragma: no-cache');
header('Expires: 0');

// Disable error display but log errors
ini_set('display_errors', '0');
error_reporting(E_ALL);

/**
 * Send JSON response and exit
 */
function sendJsonResponse(bool $success, string $message, int $httpCode = 200, array $data = []): void {
    // Clear any output buffer
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code($httpCode);

    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $data);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Wrap everything in try-catch to ensure JSON response
try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Method not allowed', 405);
    }

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Get JSON input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonResponse(false, 'Invalid JSON input');
    }

    if (!$input || !isset($input['email'])) {
        sendJsonResponse(false, 'Email is required');
    }

    $email = trim($input['email']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(false, 'Invalid email format');
    }

    // Rate limiting: Check if too many requests from this session
    $rateLimitKey = 'verification_rate_limit';
    if (isset($_SESSION[$rateLimitKey])) {
        $lastRequest = $_SESSION[$rateLimitKey];
        $timeSinceLastRequest = time() - $lastRequest;

        if ($timeSinceLastRequest < 30) { // 30 seconds between requests
            $waitTime = 30 - $timeSinceLastRequest;
            sendJsonResponse(false, "Please wait {$waitTime} seconds before requesting another code", 429);
        }
    }

    // Load database configuration and User model
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../src/models/User.php';

    // Check if email already exists in database
    try {
        $userModel = new \models\User();
        $existingUser = $userModel->findByEmail($email);

        if ($existingUser) {
            sendJsonResponse(false, 'This email is already registered. Please login instead.');
        }
    } catch (Exception $e) {
        error_log("Database error checking email: " . $e->getMessage());
        sendJsonResponse(false, 'Unable to verify email availability. Please try again.', 500);
    }

    // Generate secure 6-digit verification code
    try {
        $verificationCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        error_log("Random number generation error: " . $e->getMessage());
        // Fallback to less secure method
        $verificationCode = str_pad((string)mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    // Store verification code in session with expiration (5 minutes)
    $_SESSION['email_verification'] = [
        'email' => $email,
        'code' => $verificationCode,
        'expires_at' => time() + 300, // 5 minutes
        'attempts' => 0,
        'created_at' => time()
    ];

    // ALSO store in database for production reliability (load balancers, multiple servers)
    try {
        require_once __DIR__ . '/../config/database.php';
        $db = Database::getInstance();

        // Store verification code in database
        $stmt = $db->prepare("
            INSERT INTO email_verifications (email, verification_token, expires_at, ip_address, user_agent)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), ?, ?)
            ON DUPLICATE KEY UPDATE
                verification_token = VALUES(verification_token),
                expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE),
                verified_at = NULL,
                ip_address = VALUES(ip_address),
                user_agent = VALUES(user_agent)
        ");

        $clientIP = $_SERVER['REMOTE_ADDR'] ??
                    $_SERVER['HTTP_X_FORWARDED_FOR'] ??
                    $_SERVER['HTTP_X_REAL_IP'] ??
                    $_SERVER['HTTP_CLIENT_IP'] ??
                    'unknown';

        if (strpos($clientIP, ',') !== false) {
            $clientIP = trim(explode(',', $clientIP)[0]);
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Store the code as the verification token temporarily
        $stmt->execute([$email, $verificationCode, $clientIP, $userAgent]);

        error_log("Verification code stored in database for: {$email}");
    } catch (Exception $e) {
        error_log("Warning: Failed to store verification code in database: " . $e->getMessage());
        // Continue anyway - session storage is still available
    }

    // Update rate limit
    $_SESSION[$rateLimitKey] = time();

    // Attempt to send verification email
    $emailSent = false;
    $emailError = null;

    try {
        // Load composer autoloader for PHPMailer
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        }

        require_once __DIR__ . '/../src/services/EmailService.php';
        $emailService = new \Time2Eat\Services\EmailService();
        $emailSent = $emailService->sendVerificationCode($email, $verificationCode);
    } catch (Exception $e) {
        $emailError = $e->getMessage();
        error_log("EmailService error: " . $emailError);
    } catch (Throwable $e) {
        $emailError = $e->getMessage();
        error_log("EmailService fatal error: " . $emailError);
    }

    // Always save to fallback file for testing/debugging
    $fallbackFile = __DIR__ . '/../storage/verification_codes.txt';
    $fallbackDir = dirname($fallbackFile);

    // Ensure storage directory exists
    if (!is_dir($fallbackDir)) {
        mkdir($fallbackDir, 0755, true);
    }

    // Save code to file
    $fallbackData = sprintf(
        "[%s] Email: %s | Code: %s | Expires: %s\n",
        date('Y-m-d H:i:s'),
        $email,
        $verificationCode,
        date('Y-m-d H:i:s', $_SESSION['email_verification']['expires_at'])
    );

    @file_put_contents($fallbackFile, $fallbackData, FILE_APPEND | LOCK_EX);

    // Send appropriate response
    if ($emailSent) {
        sendJsonResponse(true, 'Verification code sent to your email. Please check your inbox.');
    } else {
        // Email failed but code is stored in session and file
        sendJsonResponse(
            true,
            'Verification code generated. For testing, check storage/verification_codes.txt',
            200,
            ['fallback_mode' => true]
        );
    }

} catch (Throwable $e) {
    // Catch any errors including fatal errors
    error_log("Send verification code fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    sendJsonResponse(false, 'An unexpected error occurred. Please try again later.', 500);
}
?>
