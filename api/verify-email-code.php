<?php
/**
 * API Endpoint: Verify Email Code
 * Verifies the 6-digit code sent to the user's email
 */

// Prevent any output before JSON response
ob_start();

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// CRITICAL: Prevent caching of email verification response (user-specific, must be real-time)
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

    if (!$input || !isset($input['email']) || !isset($input['code'])) {
        sendJsonResponse(false, 'Email and verification code are required');
    }

    $email = trim($input['email']);
    $code = trim($input['code']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(false, 'Invalid email format');
    }

    // Validate code format (must be exactly 6 digits)
    if (!preg_match('/^\d{6}$/', $code)) {
        sendJsonResponse(false, 'Verification code must be 6 digits');
    }

    // Try to get verification from session first (fast path)
    $verification = null;
    $isSessionVerification = false;

    if (isset($_SESSION['email_verification'])) {
        $verification = $_SESSION['email_verification'];

        // Validate session data structure
        if (isset($verification['email'], $verification['code'], $verification['expires_at'], $verification['attempts'])) {
            $isSessionVerification = true;
        } else {
            unset($_SESSION['email_verification']);
            $verification = null;
        }
    }

    // If not in session, check database (for production with load balancers)
    if (!$verification) {
        require_once __DIR__ . '/../config/database.php';
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT verification_token as code, expires_at, 0 as attempts, email
            FROM email_verifications
            WHERE email = ? AND verified_at IS NULL AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$email]);
        $dbVerification = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dbVerification) {
            $verification = [
                'email' => $dbVerification['email'],
                'code' => $dbVerification['code'],
                'expires_at' => strtotime($dbVerification['expires_at']),
                'attempts' => 0
            ];
            $isSessionVerification = false;
            error_log("Using database verification for: {$email}");
        }
    }

    // Check if verification exists
    if (!$verification) {
        sendJsonResponse(false, 'No verification code found. Please request a new code.');
    }

    // Check if verification has expired (5 minutes)
    $expiresAt = is_array($verification) ? $verification['expires_at'] : strtotime($verification['expires_at']);
    if (time() > $expiresAt) {
        if ($isSessionVerification) {
            unset($_SESSION['email_verification']);
        }
        $expiredMinutes = ceil((time() - $expiresAt) / 60);
        sendJsonResponse(false, "Verification code expired {$expiredMinutes} minute(s) ago. Please request a new code.");
    }

    // Check if email matches the one in verification
    if ($verification['email'] !== $email) {
        sendJsonResponse(false, 'Email does not match the verification request');
    }

    // Check attempts limit (max 3 attempts)
    if ($verification['attempts'] >= 3) {
        if ($isSessionVerification) {
            unset($_SESSION['email_verification']);
        }
        sendJsonResponse(false, 'Too many failed attempts. Please request a new verification code.');
    }

    // Verify the code
    if ($verification['code'] !== $code) {
        // Increment failed attempts
        if ($isSessionVerification) {
            $_SESSION['email_verification']['attempts']++;
            $remainingAttempts = 3 - $_SESSION['email_verification']['attempts'];
        } else {
            $remainingAttempts = 2; // 3 - 1 (current attempt)
        }

        if ($remainingAttempts > 0) {
            sendJsonResponse(
                false,
                "Invalid verification code. You have {$remainingAttempts} attempt(s) remaining.",
                400,
                ['remaining_attempts' => $remainingAttempts]
            );
            return; // CRITICAL FIX: Stop execution after sending error response
        } else {
            // Max attempts reached
            if ($isSessionVerification) {
                unset($_SESSION['email_verification']);
            }
            sendJsonResponse(false, 'Too many failed attempts. Please request a new verification code.');
            return; // CRITICAL FIX: Stop execution after sending error response
        }
    }

    // Code is correct! Check if email is already registered
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../src/models/User.php';

    $userModel = new \models\User();
    $existingUser = $userModel->findByEmail($email);

    if ($existingUser) {
        unset($_SESSION['email_verification']);
        sendJsonResponse(false, 'This email is already registered. Please login instead.');
    }

    // Store verification in database for reliability across environments
    try {
        $db = \Database::getInstance();
        $verificationToken = bin2hex(random_bytes(32));

        // Store in email_verifications table for tracking
        // Use DATE_ADD to ensure timezone consistency with database
        $stmt = $db->prepare("
            INSERT INTO email_verifications (email, verification_token, verified_at, expires_at, ip_address, user_agent)
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 5 MINUTE), ?, ?)
            ON DUPLICATE KEY UPDATE
                verification_token = VALUES(verification_token),
                verified_at = NOW(),
                expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE),
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

        $stmt->execute([$email, $verificationToken, $clientIP, $userAgent]);

        error_log("Email verification stored in database: {$email}");
    } catch (\Exception $e) {
        error_log("Failed to store email verification in database: " . $e->getMessage());
        sendJsonResponse(false, 'An error occurred during verification. Please try again.', 500);
    }

    // Also store in session for immediate use
    $_SESSION['email_verified'] = [
        'email' => $email,
        'verified_at' => time(),
        'verification_token' => $verificationToken,
        'expires_at' => time() + 300
    ];

    // Clean up verification session
    unset($_SESSION['email_verification']);

    // Log successful verification
    error_log("Email verified successfully: {$email}");

    // Send success response
    sendJsonResponse(
        true,
        'Email verified successfully! You can now complete your registration.',
        200,
        ['verified_email' => $email]
    );

} catch (Throwable $e) {
    // Catch any errors including fatal errors
    error_log("Verify email code fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    sendJsonResponse(false, 'An unexpected error occurred. Please try again later.', 500);
}
?>
