<?php

namespace Time2Eat\Services;

require_once __DIR__ . '/../../config/database.php';

/**
 * Tranzak Payment Service
 * Handles payment processing through Tranzak Cameroon's API
 */
class TranzakPaymentService
{
    private $appId;
    private $appKey;
    private $baseUrl;
    private $isProduction;
    private $db;
    private $bearerToken = null;
    private $tokenExpiry = null;

    public function __construct()
    {
        try {
            $this->db = \Database::getInstance();

            // Load configuration from database settings
            $this->loadConfiguration();

            // Set base URL based on environment (Official Tranzak API URLs)
            $this->baseUrl = $this->isProduction
                ? 'https://dsapi.tranzak.me'
                : 'https://sandbox.dsapi.tranzak.me';

        } catch (\Exception $e) {
            error_log("TranzakPaymentService initialization failed: " . $e->getMessage());
            throw new \Exception("Payment service initialization failed");
        }
    }

    /**
     * Load configuration from database
     */
    private function loadConfiguration(): void
    {
        try {
            // Check if site_settings table exists
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'site_settings'");
            $tableExists = $tableCheck->fetch();

            if (!$tableExists) {
                // Fallback to environment variables if table doesn't exist
                error_log("Tranzak: site_settings table not found, using environment variables");
                $this->loadFromEnvironment();
                return;
            }

            // Get App ID
            $stmt = $this->db->prepare("SELECT value FROM site_settings WHERE `key` = 'tranzak_app_id'");
            $stmt->execute();
            $appIdResult = $stmt->fetch();
            $this->appId = $appIdResult['value'] ?? '';

            // Get sandbox mode setting
            $stmt = $this->db->prepare("SELECT value FROM site_settings WHERE `key` = 'tranzak_sandbox_mode'");
            $stmt->execute();
            $sandboxMode = $stmt->fetch();
            $this->isProduction = !($sandboxMode && $sandboxMode['value'] === 'true');

            // Get App ID based on mode
            $appIdSetting = $this->isProduction ? 'tranzak_app_id' : 'tranzak_sandbox_app_id';
            $stmt = $this->db->prepare("SELECT value FROM site_settings WHERE `key` = ?");
            $stmt->execute([$appIdSetting]);
            $appIdResult = $stmt->fetch();
            $sandboxAppId = $appIdResult['value'] ?? '';
            
            // Fallback to production App ID if sandbox App ID is not set
            if (!$this->isProduction && empty($sandboxAppId)) {
                $stmt = $this->db->prepare("SELECT value FROM site_settings WHERE `key` = 'tranzak_app_id'");
                $stmt->execute();
                $appIdResult = $stmt->fetch();
            }
            $this->appId = $appIdResult['value'] ?? '';

            // Get App Key based on mode
            $appKeySetting = $this->isProduction ? 'tranzak_api_key' : 'tranzak_sandbox_api_key';
            $stmt = $this->db->prepare("SELECT value FROM site_settings WHERE `key` = ?");
            $stmt->execute([$appKeySetting]);
            $appKeyResult = $stmt->fetch();
            $this->appKey = $appKeyResult['value'] ?? '';

            // If database values are empty or placeholder, try environment variables
            if (empty($this->appKey) || $this->appKey === 'your_tranzak_api_key' ||
                strpos($this->appKey, 'your_') === 0 || empty($this->appId)) {
                error_log("Tranzak: Database configuration incomplete, falling back to environment variables");
                $this->loadFromEnvironment();
                return;
            }

            // Validate configuration
            if (empty($this->appKey) || empty($this->appId)) {
                error_log("Tranzak: App ID or App Key is not configured in database or environment");
                throw new \Exception("Tranzak App ID and App Key are required. Please configure in admin panel or .env file.");
            }

            error_log("Tranzak configuration loaded from database - Environment: " . ($this->isProduction ? 'Production' : 'Sandbox'));

        } catch (\PDOException $e) {
            error_log("Tranzak: Database error loading configuration: " . $e->getMessage());
            // Fallback to environment variables on database error
            $this->loadFromEnvironment();
        } catch (\Exception $e) {
            error_log("Error loading Tranzak configuration: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load configuration from environment variables
     */
    private function loadFromEnvironment(): void
    {
        // Determine environment
        $envMode = $_ENV['TRANZAK_ENVIRONMENT'] ?? 'sandbox';
        $this->isProduction = ($envMode === 'production');

        // Get App ID and App Key based on environment
        if ($this->isProduction) {
            $this->appId = $_ENV['TRANZAK_APP_ID'] ?? '';
            $this->appKey = $_ENV['TRANZAK_API_KEY'] ?? '';
        } else {
            $this->appId = $_ENV['TRANZAK_SANDBOX_APP_ID'] ?? $_ENV['TRANZAK_APP_ID'] ?? '';
            $this->appKey = $_ENV['TRANZAK_SANDBOX_API_KEY'] ?? $_ENV['TRANZAK_API_KEY'] ?? '';
        }

        // Validate configuration
        if (empty($this->appKey) || strpos($this->appKey, 'your_') === 0) {
            error_log("Tranzak: App Key not configured in environment variables");
            throw new \Exception("Tranzak App Key is not configured. Please set TRANZAK_API_KEY in your .env file.");
        }

        if (empty($this->appId) || strpos($this->appId, 'your_') === 0) {
            error_log("Tranzak: App ID not configured in environment variables");
            throw new \Exception("Tranzak App ID is not configured. Please set TRANZAK_APP_ID in your .env file.");
        }

        error_log("Tranzak configuration loaded from environment - Environment: " . ($this->isProduction ? 'Production' : 'Sandbox'));
    }

    /**
     * Get Bearer Token (with caching)
     * Tranzak requires authentication via Bearer token
     */
    private function getBearerToken(): string
    {
        // Check if we have a valid cached token
        if ($this->bearerToken && $this->tokenExpiry && time() < $this->tokenExpiry) {
            return $this->bearerToken;
        }

        // Generate new token
        try {
            $url = $this->baseUrl . '/auth/token';

            $requestData = [
                'appId' => $this->appId,
                'appKey' => $this->appKey
            ];

            // Prepare headers with required Tranzak headers
            $headers = [
                'Content-Type: application/json',
                'X-App-ID: ' . $this->appId,
                'X-App-Env: ' . ($this->isProduction ? 'production' : 'sandbox')
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // SSL Configuration - For development, disable SSL verification
            // For production, enable SSL verification for security
            $isDevelopment = (APP_ENV === 'development' || APP_ENV === 'local');
            if ($isDevelopment) {
                // Development: Disable SSL verification (WAMP/localhost)
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            } else {
                // Production: Enable SSL verification for security
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new \Exception("cURL Error: $error");
            }

            $responseData = json_decode($response, true);

            if ($httpCode === 200 && isset($responseData['success']) && $responseData['success']) {
                $this->bearerToken = $responseData['data']['token'];
                // Cache token for 75% of its validity (recommended by Tranzak)
                $expiresIn = $responseData['data']['expiresIn'] ?? 7200;
                $this->tokenExpiry = time() + ($expiresIn * 0.75);

                error_log("Tranzak: Bearer token obtained successfully, expires in {$expiresIn}s");
                return $this->bearerToken;
            } else {
                $errorMsg = $responseData['errorMsg'] ?? 'Unknown error';
                throw new \Exception("Failed to get bearer token: $errorMsg");
            }

        } catch (\Exception $e) {
            error_log("Tranzak: Error getting bearer token: " . $e->getMessage());
            throw new \Exception("Authentication failed: " . $e->getMessage());
        }
    }

    /**
     * Create a payment request
     */
    public function createPaymentRequest(array $paymentData): array
    {
        try {
            $requiredFields = ['amount', 'currency', 'description', 'order_id', 'return_url'];
            foreach ($requiredFields as $field) {
                if (!isset($paymentData[$field])) {
                    return [
                        'success' => false,
                        'message' => "Missing required field: $field"
                    ];
                }
            }

            // Prepare payment request data according to Tranzak API format
            $requestData = [
                'amount' => (int)$paymentData['amount'], // Amount in XAF (no decimals)
                'currencyCode' => $paymentData['currency'] ?? 'XAF',
                'description' => $paymentData['description'],
                'mchTransactionRef' => $paymentData['order_id'],
                'returnUrl' => $paymentData['return_url'],
                'notifyUrl' => $paymentData['notify_url'] ?? $this->getNotifyUrl(),
                'customerEmail' => $paymentData['customer_email'] ?? '',
                'customerPhone' => $this->formatPhoneNumber($paymentData['customer_phone'] ?? ''),
                'customerName' => $paymentData['customer_name'] ?? '',
                'customerName' => $paymentData['customer_name'] ?? ''
            ];

            // Validate configuration before making request
            if (empty($this->appId) || empty($this->appKey)) {
                error_log("Tranzak Configuration Error: App ID or App Key is empty. App ID: " . ($this->appId ? 'SET' : 'EMPTY') . ", App Key: " . ($this->appKey ? 'SET' : 'EMPTY'));
                return [
                    'success' => false,
                    'message' => 'Tranzak payment is not properly configured. Please contact support.',
                    'error_type' => 'configuration_error'
                ];
            }

            // Log request data for debugging (mask sensitive data)
            $logData = $requestData;
            if (isset($logData['customerPhone'])) {
                $logData['customerPhone'] = substr($logData['customerPhone'], 0, 4) . '****';
            }
            error_log("Tranzak Payment Request - Base URL: {$this->baseUrl}, Environment: " . ($this->isProduction ? 'Production' : 'Sandbox'));
            error_log("Tranzak Payment Request Data: " . json_encode($logData));

            // Make API request to Tranzak's payment initiation endpoint
            $response = $this->makeApiRequest('/xp021/v1/request/create', 'POST', $requestData);

            if ($response['success']) {
                $responseData = $response['data'] ?? [];
                
                // Log response for debugging
                error_log("Tranzak Payment Response: " . json_encode($responseData));
                
                // Extract payment URL and transaction ID from response
                $transactionId = $responseData['transactionId'] ?? $responseData['transaction_id'] ?? $responseData['requestId'] ?? null;
                $paymentUrl = $responseData['paymentAuthUrl'] ?? $responseData['payment_url'] ?? $responseData['redirect_url'] ?? $responseData['paymentUrl'] ?? null;
                
                if (!$paymentUrl) {
                    error_log("Warning: No payment URL in Tranzak response. Full response: " . json_encode($responseData));
                    return [
                        'success' => false,
                        'message' => 'Payment URL not received from Tranzak. Please try again or contact support.',
                        'debug' => 'Response: ' . json_encode($responseData)
                    ];
                }
                
                // Store payment record in database (only if we have a real order_id, not TEMP-)
                if (strpos($paymentData['order_id'], 'TEMP-') !== 0) {
                    $this->storePaymentRecord($paymentData, $responseData);
                }
                
                return [
                    'success' => true,
                    'payment_id' => $transactionId,
                    'payment_url' => $paymentUrl,
                    'message' => 'Payment request created successfully'
                ];
            } else {
                $errorMsg = $response['message'] ?? 'Failed to create payment request';
                $errorCode = $response['error_code'] ?? null;
                $httpCode = $response['http_code'] ?? 'N/A';
                error_log("Tranzak Payment Request Failed: {$errorMsg} (Code: {$errorCode}, HTTP: {$httpCode})");
                
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'error_code' => $errorCode,
                    'http_code' => $response['http_code'] ?? null,
                    'debug_info' => $response['response'] ?? null
                ];
            }

        } catch (\Exception $e) {
            error_log("Tranzak Payment Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            $response = $this->makeApiRequest("/xp021/v1/transaction/$transactionId/status", 'GET');

            if ($response['success']) {
                $status = $response['data']['status'] ?? 'unknown';
                
                // Update payment record in database
                $this->updatePaymentStatus($transactionId, $status);
                
                return [
                    'success' => true,
                    'status' => $status,
                    'transaction_id' => $transactionId,
                    'data' => $response['data']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Failed to verify payment'
                ];
            }

        } catch (\Exception $e) {
            error_log("Tranzak Verification Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle Tranzak Payment Notification (TPN) webhook
     * Based on official Tranzak documentation: https://docs.developer.tranzak.me
     * 
     * Expected TPN format:
     * {
     *   "name": "Tranzak Payment Notification (TPN)",
     *   "version": "1.0",
     *   "eventType": "REQUEST.COMPLETED",
     *   "appId": "...",
     *   "resourceId": "REQ...",
     *   "webhookId": "WH...",
     *   "authKey": "...",
     *   "creationDateTime": "...",
     *   "resource": { ... }
     * }
     */
    public function handlePaymentNotification(array $tpnData): array
    {
        try {
            error_log("Tranzak TPN received: " . json_encode($tpnData));

            // Verify this is a valid TPN format
            if (!isset($tpnData['name']) || $tpnData['name'] !== 'Tranzak Payment Notification (TPN)') {
                error_log("Invalid TPN format: Missing or incorrect 'name' field");
                return [
                    'success' => false,
                    'message' => 'Invalid TPN format: Missing or incorrect name field'
                ];
            }

            // Verify authKey
            if (!$this->verifyNotificationSignature($tpnData)) {
                error_log("Tranzak TPN: Invalid authKey");
                return [
                    'success' => false,
                    'message' => 'Invalid webhook authentication key'
                ];
            }

            // Verify appId matches
            if (isset($tpnData['appId']) && $tpnData['appId'] !== $this->appId) {
                error_log("Tranzak TPN: App ID mismatch. Expected: {$this->appId}, Received: {$tpnData['appId']}");
                return [
                    'success' => false,
                    'message' => 'App ID mismatch'
                ];
            }

            // Extract event type
            $eventType = $tpnData['eventType'] ?? '';
            $resource = $tpnData['resource'] ?? [];
            $resourceId = $tpnData['resourceId'] ?? '';

            error_log("Tranzak TPN: Event Type: {$eventType}, Resource ID: {$resourceId}");

            // Handle different event types
            switch ($eventType) {
                case 'REQUEST.COMPLETED':
                    return $this->handleRequestCompleted($resource, $resourceId, $tpnData);
                
                case 'REFUND.COMPLETED':
                    return $this->handleRefundCompleted($resource, $resourceId, $tpnData);
                
                default:
                    error_log("Tranzak TPN: Unhandled event type: {$eventType}");
                    return [
                        'success' => true,
                        'message' => "Event type {$eventType} received but not processed"
                    ];
            }

        } catch (\Exception $e) {
            error_log("Tranzak TPN Error: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'TPN processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle REQUEST.COMPLETED event (payment success/failure)
     */
    private function handleRequestCompleted(array $resource, string $resourceId, array $tpnData): array
    {
        try {
            // Extract transaction details from resource
            $requestId = $resource['requestId'] ?? $resourceId;
            $status = $resource['status'] ?? $resource['transactionStatus'] ?? '';
            $transactionId = $resource['transactionId'] ?? $requestId;
            $amount = (int)($resource['amount'] ?? 0);
            $currencyCode = $resource['currencyCode'] ?? 'XAF';
            $mchTransactionRef = $resource['mchTransactionRef'] ?? '';
            $errorCode = $resource['errorCode'] ?? null;
            $errorMessage = $resource['errorMessage'] ?? null;

            error_log("Tranzak REQUEST.COMPLETED: Request ID: {$requestId}, Status: {$status}, Transaction ID: {$transactionId}, Amount: {$amount}");

            // Find payment record by transaction_id or mchTransactionRef (order_id)
            $payment = null;
            if (!empty($transactionId)) {
                $stmt = $this->db->prepare("SELECT * FROM payments WHERE transaction_id = ? LIMIT 1");
                $stmt->execute([$transactionId]);
                $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            // If not found by transaction_id, try by order_id (mchTransactionRef)
            if (!$payment && !empty($mchTransactionRef)) {
                $stmt = $this->db->prepare("SELECT * FROM payments WHERE order_id = ? LIMIT 1");
                $stmt->execute([$mchTransactionRef]);
                $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            // Map Tranzak status to our payment status
            $paymentStatus = $this->mapTranzakStatusToPaymentStatus($status);

            if ($payment) {
                // Update payment record
                $this->updatePaymentStatus($transactionId ?: $requestId, $paymentStatus, [
                    'tpn_data' => $tpnData,
                    'resource' => $resource,
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage
                ]);

                // Process based on status
                if ($status === 'SUCCESSFUL') {
                    $this->processSuccessfulPayment($transactionId ?: $requestId, $amount);
                } elseif ($status === 'FAILED') {
                    $this->processFailedPayment($payment['order_id'], $errorCode, $errorMessage);
                }

                return [
                    'success' => true,
                    'message' => 'Payment notification processed successfully',
                    'status' => $paymentStatus,
                    'transaction_id' => $transactionId
                ];
            } else {
                error_log("Tranzak TPN: Payment record not found for transaction: {$transactionId}, request: {$requestId}, order: {$mchTransactionRef}");
                
                // Check if this is a temporary reference (payment before order creation)
                if (!empty($mchTransactionRef) && strpos($mchTransactionRef, 'TEMP-') === 0) {
                    // This is a payment initiated before order creation
                    // Create the order now that payment is confirmed
                    if ($status === 'SUCCESSFUL') {
                        return $this->createOrderFromDraftPayment($mchTransactionRef, $resource, $tpnData, $transactionId);
                    } else {
                        // Payment failed, clean up draft data
                        $this->cleanupDraftOrder($mchTransactionRef);
                        return [
                            'success' => true,
                            'message' => 'Payment failed, draft order cleaned up',
                            'status' => $paymentStatus
                        ];
                    }
                }
                
                // Try to create payment record if we have order_id (legacy flow)
                if (!empty($mchTransactionRef)) {
                    $this->createPaymentFromTPN($mchTransactionRef, $resource, $tpnData);
                }

                return [
                    'success' => true,
                    'message' => 'TPN received but payment record not found (may have been created)',
                    'status' => $paymentStatus
                ];
            }

        } catch (\Exception $e) {
            error_log("Error handling REQUEST.COMPLETED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle REFUND.COMPLETED event
     */
    private function handleRefundCompleted(array $resource, string $resourceId, array $tpnData): array
    {
        try {
            $refundId = $resource['refundId'] ?? $resourceId;
            $refundedTransactionId = $resource['refundedTransactionId'] ?? '';
            $status = $resource['status'] ?? '';
            $amount = (int)($resource['amount'] ?? 0);

            error_log("Tranzak REFUND.COMPLETED: Refund ID: {$refundId}, Transaction: {$refundedTransactionId}, Status: {$status}");

            // Find payment record
            $stmt = $this->db->prepare("SELECT * FROM payments WHERE transaction_id = ? LIMIT 1");
            $stmt->execute([$refundedTransactionId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($payment) {
                // Update payment status to refunded
                $this->updatePaymentStatus($refundedTransactionId, 'refunded', [
                    'tpn_data' => $tpnData,
                    'refund_id' => $refundId,
                    'refund_amount' => $amount
                ]);

                // Update order status if needed
                if (!empty($payment['order_id'])) {
                    $stmt = $this->db->prepare("UPDATE orders SET status = 'refunded', updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$payment['order_id']]);
                }

                return [
                    'success' => true,
                    'message' => 'Refund notification processed successfully',
                    'refund_id' => $refundId
                ];
            } else {
                error_log("Tranzak REFUND: Payment record not found for transaction: {$refundedTransactionId}");
                return [
                    'success' => true,
                    'message' => 'Refund TPN received but payment record not found'
                ];
            }

        } catch (\Exception $e) {
            error_log("Error handling REFUND.COMPLETED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Map Tranzak status to our payment status
     */
    private function mapTranzakStatusToPaymentStatus(string $tranzakStatus): string
    {
        $statusMap = [
            'SUCCESSFUL' => 'paid',
            'FAILED' => 'failed',
            'PENDING' => 'pending',
            'CANCELLED' => 'cancelled',
            'EXPIRED' => 'failed',
            'PAYMENT_IN_PROGRESS' => 'processing',
            'PAYER_REDIRECT_REQUIRED' => 'pending'
        ];

        return $statusMap[strtoupper($tranzakStatus)] ?? 'pending';
    }

    /**
     * Create payment record from TPN if it doesn't exist
     */
    private function createPaymentFromTPN(string $orderId, array $resource, array $tpnData): void
    {
        try {
            $transactionId = $resource['transactionId'] ?? $resource['requestId'] ?? '';
            $amount = (int)($resource['amount'] ?? 0);
            $currencyCode = $resource['currencyCode'] ?? 'XAF';
            $status = $this->mapTranzakStatusToPaymentStatus($resource['status'] ?? '');

            $sql = "INSERT INTO payments (
                order_id, transaction_id, amount, currency, status, 
                payment_method, response_data, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, 'tranzak', ?, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $orderId,
                $transactionId,
                $amount,
                $currencyCode,
                $status,
                json_encode($tpnData)
            ]);

            error_log("Created payment record from TPN for order: {$orderId}");

        } catch (\Exception $e) {
            error_log("Error creating payment from TPN: " . $e->getMessage());
        }
    }

    /**
     * Process failed payment
     */
    private function processFailedPayment($orderId, $errorCode, $errorMessage): void
    {
        try {
            // Update order status
            $stmt = $this->db->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$orderId]);

            error_log("Order {$orderId} cancelled due to payment failure. Error: {$errorCode} - {$errorMessage}");

        } catch (\Exception $e) {
            error_log("Error processing failed payment: " . $e->getMessage());
        }
    }

    /**
     * Create order from draft payment after payment confirmation
     */
    private function createOrderFromDraftPayment(string $tempReference, array $resource, array $tpnData, string $transactionId): array
    {
        try {
            error_log("Creating order from draft payment. Temp Ref: {$tempReference}, Transaction: {$transactionId}");

            // Check if draft_orders table exists, if not, try to get from session (fallback)
            $draftData = null;
            
            try {
                // Try to get draft order from database
                $stmt = $this->db->prepare("SELECT * FROM draft_orders WHERE temp_reference = ? LIMIT 1");
                $stmt->execute([$tempReference]);
                $draftRow = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($draftRow) {
                    $draftData = json_decode($draftRow['order_data'], true);
                    error_log("Found draft order in database for temp ref: {$tempReference}");
                }
            } catch (\PDOException $e) {
                // Table doesn't exist, will use session fallback
                error_log("Draft orders table not found, will try session fallback: " . $e->getMessage());
            }

            // If not in database, try to get from session (this won't work for webhooks, but kept for compatibility)
            if (!$draftData && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['tranzak_draft_orders'])) {
                $sessionDraft = $_SESSION['tranzak_draft_orders'];
                if ($sessionDraft['temp_reference'] === $tempReference) {
                    $draftData = $sessionDraft;
                    error_log("Found draft order in session for temp ref: {$tempReference}");
                }
            }

            if (!$draftData) {
                error_log("Draft order data not found for temp reference: {$tempReference}");
                return [
                    'success' => false,
                    'message' => 'Draft order data not found. Order cannot be created.',
                    'status' => 'failed'
                ];
            }

            // Validate draft data hasn't expired (24 hours)
            $createdAt = $draftData['created_at'] ?? 0;
            if (time() - $createdAt > 86400) {
                error_log("Draft order expired for temp reference: {$tempReference}");
                $this->cleanupDraftOrder($tempReference);
                return [
                    'success' => false,
                    'message' => 'Draft order expired. Please place a new order.',
                    'status' => 'expired'
                ];
            }

            $userId = $draftData['user_id'] ?? null;
            if (!$userId) {
                error_log("User ID not found in draft order data for temp reference: {$tempReference}");
                return [
                    'success' => false,
                    'message' => 'Invalid draft order data',
                    'status' => 'failed'
                ];
            }

            // Start transaction for order creation
            $this->db->beginTransaction();

            try {
                $orderIds = [];
                $deliveryAddress = $draftData['delivery_address'] ?? [];
                $deliveryInstructions = $draftData['delivery_instructions'] ?? '';

                // Create orders for each restaurant
                foreach ($draftData['orders'] ?? [] as $draftOrder) {
                    $orderData = [
                        'order_number' => $draftOrder['order_number'],
                        'customer_id' => $userId,
                        'restaurant_id' => $draftOrder['restaurant_id'],
                        'status' => 'confirmed', // Payment confirmed, order is ready
                        'payment_status' => 'paid',
                        'payment_method' => 'tranzak',
                        'subtotal' => $draftOrder['subtotal'],
                        'service_fee' => $draftOrder['service_fee'],
                        'tax_amount' => 0,
                        'delivery_fee' => $draftOrder['delivery_fee'],
                        'discount_amount' => 0,
                        'total_amount' => $draftOrder['total_amount'],
                        'delivery_address' => json_encode($deliveryAddress),
                        'delivery_instructions' => $deliveryInstructions,
                        'currency' => 'XAF'
                    ];

                    // Create order
                    $sql = "INSERT INTO orders (
                        order_number, customer_id, restaurant_id, status, payment_status, payment_method,
                        subtotal, service_fee, tax_amount, delivery_fee, discount_amount, total_amount,
                        delivery_address, delivery_instructions, currency, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        $orderData['order_number'],
                        $orderData['customer_id'],
                        $orderData['restaurant_id'],
                        $orderData['status'],
                        $orderData['payment_status'],
                        $orderData['payment_method'],
                        $orderData['subtotal'],
                        $orderData['service_fee'],
                        $orderData['tax_amount'],
                        $orderData['delivery_fee'],
                        $orderData['discount_amount'],
                        $orderData['total_amount'],
                        $orderData['delivery_address'],
                        $orderData['delivery_instructions'],
                        $orderData['currency']
                    ]);

                    $orderId = $this->db->lastInsertId();
                    $orderIds[] = $orderId;

                    // Create order items
                    foreach ($draftOrder['items'] ?? [] as $item) {
                        $itemSql = "INSERT INTO order_items (
                            order_id, menu_item_id, quantity, unit_price, total_price, 
                            variants, special_instructions, created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

                        $itemStmt = $this->db->prepare($itemSql);
                        $itemStmt->execute([
                            $orderId,
                            $item['menu_item_id'],
                            $item['quantity'],
                            $item['unit_price'],
                            $item['total_price'],
                            $item['customizations'] ?? null,
                            $item['special_instructions'] ?? ''
                        ]);
                    }

                    // Process affiliate commission
                    $this->processAffiliateCommission($orderId, $userId);
                }

                // Create payment record
                $paymentSql = "INSERT INTO payments (
                    order_id, transaction_id, amount, currency, status, payment_method,
                    response_data, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, 'tranzak', ?, NOW(), NOW())";

                $paymentStmt = $this->db->prepare($paymentSql);
                $paymentStmt->execute([
                    $orderIds[0], // Link to first order
                    $transactionId,
                    (int)$resource['amount'],
                    $resource['currencyCode'] ?? 'XAF',
                    'paid',
                    json_encode($tpnData)
                ]);

                // Clear cart
                $cartStmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ?");
                $cartStmt->execute([$userId]);

                // Clean up draft order
                $this->cleanupDraftOrder($tempReference);

                $this->db->commit();

                error_log("Successfully created " . count($orderIds) . " order(s) from draft payment. Order IDs: " . implode(', ', $orderIds));

                return [
                    'success' => true,
                    'message' => 'Order created successfully after payment confirmation',
                    'status' => 'paid',
                    'order_ids' => $orderIds,
                    'transaction_id' => $transactionId
                ];

            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            error_log("Error creating order from draft payment: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Failed to create order from draft payment: ' . $e->getMessage(),
                'status' => 'failed'
            ];
        }
    }

    /**
     * Process affiliate commission (helper method)
     */
    private function processAffiliateCommission(int $orderId, int $customerId): void
    {
        try {
            $stmt = $this->db->prepare("SELECT referred_by FROM users WHERE id = ?");
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$customer || !$customer['referred_by']) {
                return;
            }

            $affiliateStmt = $this->db->prepare("
                SELECT id, commission_rate, affiliate_code
                FROM affiliates
                WHERE affiliate_code = ? AND status = 'active'
            ");
            $affiliateStmt->execute([$customer['referred_by']]);
            $affiliate = $affiliateStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$affiliate) {
                return;
            }

            $orderStmt = $this->db->prepare("SELECT subtotal FROM orders WHERE id = ?");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch(\PDO::FETCH_ASSOC);

            if ($order) {
                $commissionAmount = round($order['subtotal'] * ($affiliate['commission_rate'] / 100), 2);
                
                $updateStmt = $this->db->prepare("
                    UPDATE orders
                    SET affiliate_commission = ?, affiliate_code = ?
                    WHERE id = ?
                ");
                $updateStmt->execute([$commissionAmount, $affiliate['affiliate_code'], $orderId]);
            }
        } catch (\Exception $e) {
            error_log("Error processing affiliate commission: " . $e->getMessage());
        }
    }

    /**
     * Clean up draft order data
     */
    private function cleanupDraftOrder(string $tempReference): void
    {
        try {
            // Delete from database if table exists
            try {
                $stmt = $this->db->prepare("DELETE FROM draft_orders WHERE temp_reference = ?");
                $stmt->execute([$tempReference]);
            } catch (\PDOException $e) {
                // Table doesn't exist, ignore
            }

            // Clear from session if active
            if (session_status() === PHP_SESSION_ACTIVE) {
                if (isset($_SESSION['tranzak_draft_orders']) && 
                    ($_SESSION['tranzak_draft_orders']['temp_reference'] ?? '') === $tempReference) {
                    unset($_SESSION['tranzak_draft_orders']);
                }
                if (isset($_SESSION['tranzak_payment_info']) && 
                    ($_SESSION['tranzak_payment_info']['temp_reference'] ?? '') === $tempReference) {
                    unset($_SESSION['tranzak_payment_info']);
                }
            }

            error_log("Cleaned up draft order data for temp reference: {$tempReference}");
        } catch (\Exception $e) {
            error_log("Error cleaning up draft order: " . $e->getMessage());
        }
    }

    /**
     * Get payment methods available
     */
    public function getAvailablePaymentMethods(): array
    {
        return [
            'mobile_money' => [
                'mtn_momo' => 'MTN Mobile Money',
                'orange_money' => 'Orange Money'
            ],
            'bank_transfer' => [
                'uba' => 'UBA Bank',
                'afriland' => 'Afriland First Bank'
            ],
            'card' => [
                'visa' => 'Visa Card',
                'mastercard' => 'Mastercard'
            ]
        ];
    }

    /**
     * Get payment methods (alias for compatibility)
     */
    public function getPaymentMethods(): array
    {
        $methods = $this->getAvailablePaymentMethods();
        $flatMethods = [];
        
        foreach ($methods as $category => $methodList) {
            foreach ($methodList as $key => $name) {
                $flatMethods[] = $name;
            }
        }
        
        return [
            'success' => true,
            'methods' => $flatMethods,
            'detailed' => $methods
        ];
    }

    /**
     * Initiate payment (alias for compatibility)
     */
    public function initiatePayment(array $paymentData): array
    {
        // Add required fields for createPaymentRequest
        $paymentData['return_url'] = $paymentData['return_url'] ?? ($_ENV['APP_URL'] ?? 'http://localhost') . '/payment/success';
        $paymentData['notify_url'] = $paymentData['notify_url'] ?? $this->getNotifyUrl();
        
        return $this->createPaymentRequest($paymentData);
    }

    /**
     * Handle webhook (alias for compatibility)
     */
    public function handleWebhook(array $webhookData): array
    {
        return $this->handlePaymentNotification($webhookData);
    }

    /**
     * Make API request to Tranzak
     */
    private function makeApiRequest(string $endpoint, string $method = 'GET', array $data = []): array
    {
        try {
            $url = $this->baseUrl . $endpoint;

            // Get Bearer token
            $bearerToken = $this->getBearerToken();

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $bearerToken,
                'X-App-ID: ' . $this->appId,
                'X-App-Key: ' . $this->appKey,
                'X-App-Env: ' . ($this->isProduction ? 'production' : 'sandbox')
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // SSL Configuration - For development, disable SSL verification
            // For production, enable SSL verification for security
            $isDevelopment = (APP_ENV === 'development' || APP_ENV === 'local');
            if ($isDevelopment) {
                // Development: Disable SSL verification (WAMP/localhost)
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            } else {
                // Production: Enable SSL verification for security
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Time2Eat/1.0');

            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                error_log("Tranzak cURL Error for {$endpoint}: {$error}");
                throw new \Exception("cURL Error: $error");
            }

            // Log raw response for debugging
            error_log("Tranzak API Response for {$endpoint} - HTTP {$httpCode}: " . substr($response, 0, 500));

            // Check if response is empty
            if (empty($response)) {
                error_log("Tranzak API returned empty response for {$endpoint}");
                return [
                    'success' => false,
                    'message' => 'Empty response from Tranzak API. Please check your network connection.',
                    'http_code' => $httpCode
                ];
            }

            // Check if response looks like HTML (error page)
            if (stripos($response, '<html') !== false || stripos($response, '<!DOCTYPE') !== false) {
                error_log("Tranzak API returned HTML instead of JSON for {$endpoint}. This might be an error page.");
                return [
                    'success' => false,
                    'message' => 'Tranzak API returned an error page. Please check your API credentials and network connection.',
                    'http_code' => $httpCode,
                    'response_type' => 'html'
                ];
            }

            $responseData = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Tranzak API JSON decode error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 500));
                return [
                    'success' => false,
                    'message' => 'Invalid JSON response from Tranzak API: ' . json_last_error_msg(),
                    'http_code' => $httpCode,
                    'raw_response' => substr($response, 0, 500)
                ];
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                // Check if response indicates success according to Tranzak format
                if (isset($responseData['success']) && $responseData['success'] === true) {
                    return [
                        'success' => true,
                        'data' => $responseData['data'] ?? $responseData
                    ];
                } elseif (isset($responseData['success']) && $responseData['success'] === false) {
                    // API returned success=false
                    $errorMsg = $responseData['errorMsg'] ?? $responseData['message'] ?? 'Unknown error from Tranzak API';
                    error_log("Tranzak API returned success=false: {$errorMsg}");
                    return [
                        'success' => false,
                        'message' => $errorMsg,
                        'error_code' => $responseData['errorCode'] ?? null,
                        'http_code' => $httpCode,
                        'response' => $responseData
                    ];
                } else {
                    // No success field, assume success if HTTP 200
                    return [
                        'success' => true,
                        'data' => $responseData
                    ];
                }
            } else {
                $errorMsg = $responseData['errorMsg'] ?? $responseData['message'] ?? 'API request failed';
                $errorCode = $responseData['errorCode'] ?? null;
                error_log("Tranzak API Error - HTTP {$httpCode}: {$errorMsg} (Code: {$errorCode})");
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'error_code' => $errorCode,
                    'http_code' => $httpCode,
                    'response' => $responseData
                ];
            }

        } catch (\Exception $e) {
            error_log("Tranzak API Error for {$endpoint}: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Provide more specific error messages
            $errorMessage = 'API request failed: ' . $e->getMessage();
            
            if (strpos($e->getMessage(), 'cURL Error') !== false) {
                $errorMessage = 'Network error connecting to Tranzak. Please check your internet connection and try again.';
            } elseif (strpos($e->getMessage(), 'Authentication failed') !== false) {
                $errorMessage = 'Tranzak authentication failed. Please verify your API credentials.';
            } elseif (strpos($e->getMessage(), 'timeout') !== false) {
                $errorMessage = 'Request to Tranzak timed out. Please try again.';
            }
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'error_type' => 'api_error',
                'original_error' => $e->getMessage()
            ];
        }
    }

    /**
     * Store payment record in database
     */
    private function storePaymentRecord(array $paymentData, array $responseData): void
    {
        try {
            $sql = "INSERT INTO payments (
                order_id, 
                transaction_id, 
                amount, 
                currency, 
                status, 
                payment_method, 
                customer_email, 
                customer_phone, 
                payment_url, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $paymentData['order_id'],
                $responseData['transactionId'] ?? null,
                $paymentData['amount'],
                $paymentData['currency'] ?? 'XAF',
                'pending',
                $paymentData['payment_method'] ?? 'tranzak',
                $paymentData['customer_email'] ?? '',
                $paymentData['customer_phone'] ?? '',
                $responseData['paymentAuthUrl'] ?? null
            ]);

        } catch (\Exception $e) {
            error_log("Error storing payment record: " . $e->getMessage());
        }
    }

    /**
     * Update payment status in database
     */
    private function updatePaymentStatus(string $transactionId, string $status, array $data = []): void
    {
        try {
            $sql = "UPDATE payments 
                    SET status = ?, 
                        updated_at = NOW(),
                        response_data = ?
                    WHERE transaction_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $status,
                json_encode($data),
                $transactionId
            ]);

        } catch (\Exception $e) {
            error_log("Error updating payment status: " . $e->getMessage());
        }
    }

    /**
     * Process successful payment
     */
    private function processSuccessfulPayment(string $transactionId, int $amount): void
    {
        try {
            // Get payment record - try by transaction_id first
            $stmt = $this->db->prepare("SELECT * FROM payments WHERE transaction_id = ? LIMIT 1");
            $stmt->execute([$transactionId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

            // If not found, try by order_id if transactionId looks like a request ID
            if (!$payment && strpos($transactionId, 'REQ') === 0) {
                // This might be a request ID, try to find by matching request ID in response_data
                $stmt = $this->db->prepare("SELECT * FROM payments WHERE response_data LIKE ? LIMIT 1");
                $stmt->execute(['%' . $transactionId . '%']);
                $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            if ($payment && !empty($payment['order_id'])) {
                // Update order payment status
                $stmt = $this->db->prepare("UPDATE orders SET payment_status = 'paid', status = 'confirmed', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$payment['order_id']]);

                error_log("Order {$payment['order_id']} payment confirmed via Tranzak TPN");

                // Send confirmation email
                try {
                    $this->sendPaymentConfirmation($payment);
                } catch (\Exception $e) {
                    error_log("Error sending payment confirmation email: " . $e->getMessage());
                    // Don't fail the payment processing if email fails
                }
            } else {
                error_log("Payment record not found for transaction: {$transactionId}");
            }

        } catch (\Exception $e) {
            error_log("Error processing successful payment: " . $e->getMessage());
        }
    }

    /**
     * Send payment confirmation email
     */
    private function sendPaymentConfirmation(array $payment): void
    {
        try {
            require_once __DIR__ . '/EmailService.php';
            $emailService = new \Time2Eat\Services\EmailService();

            // Get order details
            $order = $this->db->fetchOne(
                "SELECT o.*, u.email, u.first_name, u.last_name 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE o.id = ?",
                [$payment['order_id']]
            );

            if ($order) {
                $emailService->sendPaymentConfirmation(
                    [
                        'email' => $order['email'],
                        'first_name' => $order['first_name'],
                        'last_name' => $order['last_name']
                    ],
                    $order,
                    [
                        'transaction_id' => $payment['transaction_id'],
                        'amount' => $payment['amount'],
                        'status' => 'success'
                    ]
                );
            }

        } catch (\Exception $e) {
            error_log("Error sending payment confirmation: " . $e->getMessage());
        }
    }

    /**
     * Verify Tranzak TPN authKey
     * According to Tranzak documentation, the authKey is sent in the webhook payload
     * and should match the configured webhook auth key
     */
    private function verifyNotificationSignature(array $tpnData): bool
    {
        try {
            // Get webhook auth key from environment or database
            $webhookAuthKey = $this->getWebhookAuthKey();

            if (empty($webhookAuthKey)) {
                error_log("Tranzak TPN: Webhook auth key not configured");
                // In development, allow if auth key is not set (for testing)
                if (APP_ENV === 'development' || APP_ENV === 'local') {
                    error_log("Tranzak TPN: Development mode - allowing without auth key verification");
                    return true;
                }
                return false;
            }

            // Get authKey from TPN payload
            $receivedAuthKey = $tpnData['authKey'] ?? '';

            if (empty($receivedAuthKey)) {
                error_log("Tranzak TPN: No authKey in webhook payload");
                return false;
            }

            // Compare auth keys (use hash_equals for timing attack protection)
            $isValid = hash_equals($webhookAuthKey, $receivedAuthKey);

            if (!$isValid) {
                error_log("Tranzak TPN: Auth key mismatch. Expected: " . substr($webhookAuthKey, 0, 10) . "... Received: " . substr($receivedAuthKey, 0, 10) . "...");
            }

            return $isValid;

        } catch (\Exception $e) {
            error_log("Error verifying TPN signature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get webhook auth key from database or environment
     */
    private function getWebhookAuthKey(): string
    {
        try {
            // Try database first
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'site_settings'");
            $tableExists = $tableCheck->fetch();

            if ($tableExists) {
                $stmt = $this->db->prepare("SELECT value FROM site_settings WHERE `key` = 'tranzak_webhook_auth_key'");
                $stmt->execute();
                $result = $stmt->fetch();
                if ($result && !empty($result['value'])) {
                    return $result['value'];
                }
            }

            // Fallback to environment variable
            return $_ENV['TRANZAK_WEBHOOK_AUTH_KEY'] ?? '';

        } catch (\Exception $e) {
            error_log("Error getting webhook auth key: " . $e->getMessage());
            // Fallback to environment variable
            return $_ENV['TRANZAK_WEBHOOK_AUTH_KEY'] ?? '';
        }
    }

    /**
     * Get notification URL
     */
    private function getNotifyUrl(): string
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        return $baseUrl . '/api/payment/tranzak/notify';
    }

    /**
     * Format phone number for Tranzak (Cameroon format)
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle different formats
        if (strpos($phone, '237') === 0) {
            // Already has country code
            return $phone;
        } elseif (strpos($phone, '6') === 0 && strlen($phone) === 9) {
            // Local format starting with 6
            return '237' . $phone;
        } elseif (strpos($phone, '6') === 0 && strlen($phone) === 8) {
            // Local format without leading 6
            return '2376' . $phone;
        } else {
            // Default: add 237 if not present
            return '237' . $phone;
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        try {
            $response = $this->makeApiRequest('/xp021/v1/health', 'GET');
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'environment' => $this->isProduction ? 'production' : 'sandbox'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Connection failed: ' . $response['message']
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
}
