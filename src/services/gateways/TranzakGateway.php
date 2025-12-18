<?php

namespace Time2Eat\Services\Gateways;

class TranzakGateway implements PaymentGatewayInterface
{
    private array $config;
    private string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = $config['sandbox'] ? 'https://dsapi.tranzak.me' : 'https://api.tranzak.me';
    }

    /**
     * Process payment through Tranzak
     */
    public function processPayment(array $data): array
    {
        try {
            $paymentData = [
                'amount' => $this->formatAmount($data['amount']),
                'currency' => $data['currency'] ?? 'XAF',
                'description' => "Time2Eat Order Payment",
                'customer_phone' => $this->formatPhoneNumber($data['phone_number']),
                'customer_email' => $data['customer_email'] ?? '',
                'customer_name' => $data['customer_name'] ?? '',
                'return_url' => $data['return_url'] ?? '',
                'cancel_url' => $data['cancel_url'] ?? '',
                'webhook_url' => $_ENV['APP_URL'] . '/api/payments/webhook/tranzak',
                'reference' => $data['payment_id'] ?? uniqid('tranzak_'),
                'payment_method' => $data['payment_method'] ?? 'mobile_money'
            ];

            $response = $this->makeRequest('POST', '/payments/initialize', $paymentData);

            if ($response['success']) {
                return [
                    'success' => true,
                    'status' => 'pending',
                    'transaction_id' => $response['data']['transaction_id'],
                    'redirect_url' => $response['data']['payment_url'],
                    'message' => 'Payment initialized successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Payment initialization failed'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process refund through Tranzak
     */
    public function processRefund(array $payment, float $amount, string $reason): array
    {
        try {
            $refundData = [
                'transaction_id' => $payment['transaction_id'],
                'amount' => $this->formatAmount($amount),
                'reason' => $reason
            ];

            $response = $this->makeRequest('POST', '/payments/refund', $refundData);

            if ($response['success']) {
                return [
                    'success' => true,
                    'refund_id' => $response['data']['refund_id'],
                    'status' => 'completed',
                    'message' => 'Refund processed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Refund processing failed'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Refund processing error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle webhook from Tranzak
     */
    public function handleWebhook(string $payload, string $signature): array
    {
        try {
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                return [
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ];
            }

            $data = json_decode($payload, true);
            if (!$data) {
                return [
                    'success' => false,
                    'message' => 'Invalid webhook payload'
                ];
            }

            // Map Tranzak status to our status
            $status = $this->mapStatus($data['status']);

            return [
                'success' => true,
                'data' => [
                    'transaction_id' => $data['transaction_id'],
                    'status' => $status,
                    'amount' => $this->formatAmountFromGateway($data['amount']),
                    'currency' => $data['currency'],
                    'gateway_response' => $data,
                    'failure_reason' => $data['failure_reason'] ?? null
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Webhook processing error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            $response = $this->makeRequest('GET', "/payments/{$transactionId}");

            if ($response['success']) {
                $status = $this->mapStatus($response['data']['status']);
                
                return [
                    'success' => true,
                    'status' => $status,
                    'amount' => $this->formatAmountFromGateway($response['data']['amount']),
                    'currency' => $response['data']['currency'],
                    'transaction_id' => $response['data']['transaction_id']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Payment verification failed'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test connection to Tranzak
     */
    public function testConnection(): array
    {
        try {
            $response = $this->makeRequest('GET', '/ping');
            
            return [
                'success' => $response['success'],
                'message' => $response['success'] ? 'Connection successful' : 'Connection failed'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get gateway name
     */
    public function getName(): string
    {
        return 'Tranzak Mobile Money';
    }

    /**
     * Get gateway description
     */
    public function getDescription(): string
    {
        return 'Pay with Mobile Money (MTN, Orange) via Tranzak';
    }

    /**
     * Get gateway logo
     */
    public function getLogo(): string
    {
        return '/images/gateways/tranzak-logo.png';
    }

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool
    {
        return !empty($this->config['api_key']) && !empty($this->config['api_secret']);
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return ['XAF'];
    }

    /**
     * Get minimum amount
     */
    public function getMinAmount(): float
    {
        return 100.0; // 100 XAF
    }

    /**
     * Get maximum amount
     */
    public function getMaxAmount(): float
    {
        return 1000000.0; // 1,000,000 XAF
    }

    /**
     * Get fees structure
     */
    public function getFees(): array
    {
        return [
            'fixed_fee' => 0,
            'percentage_fee' => 2.5, // 2.5%
            'min_fee' => 25, // 25 XAF
            'max_fee' => 5000 // 5000 XAF
        ];
    }

    /**
     * Calculate fees
     */
    public function calculateFees(float $amount, string $currency = 'XAF'): array
    {
        $fees = $this->getFees();
        $percentageFee = ($amount * $fees['percentage_fee']) / 100;
        $fee = max($fees['min_fee'], min($percentageFee, $fees['max_fee']));
        
        return [
            'fee' => $fee,
            'net_amount' => $amount - $fee,
            'total_amount' => $amount
        ];
    }

    /**
     * Make HTTP request to Tranzak API
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->getAccessToken(),
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => !$this->config['sandbox']
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL error: " . $error);
        }

        $decodedResponse = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $decodedResponse['data'] ?? $decodedResponse,
            'message' => $decodedResponse['message'] ?? null,
            'http_code' => $httpCode
        ];
    }

    /**
     * Get access token for API authentication
     */
    private function getAccessToken(): string
    {
        // In a real implementation, you would cache this token
        // and refresh it when it expires
        
        $tokenData = [
            'api_key' => $this->config['api_key'],
            'api_secret' => $this->config['api_secret']
        ];

        $response = $this->makeTokenRequest($tokenData);
        
        if ($response['success']) {
            return $response['data']['access_token'];
        }

        throw new \Exception('Failed to get access token');
    }

    /**
     * Make token request
     */
    private function makeTokenRequest(array $data): array
    {
        $url = $this->baseUrl . '/auth/token';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => !$this->config['sandbox']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decodedResponse = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $decodedResponse
        ];
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->config['api_secret']);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Map Tranzak status to our status
     */
    private function mapStatus(string $tranzakStatus): string
    {
        $statusMap = [
            'pending' => 'pending',
            'processing' => 'processing',
            'successful' => 'completed',
            'failed' => 'failed',
            'cancelled' => 'cancelled',
            'expired' => 'failed'
        ];

        return $statusMap[$tranzakStatus] ?? 'pending';
    }

    /**
     * Format amount for Tranzak (in centimes)
     */
    private function formatAmount(float $amount): int
    {
        return (int)($amount * 100);
    }

    /**
     * Format amount from Tranzak
     */
    private function formatAmountFromGateway(int $amount): float
    {
        return $amount / 100;
    }

    /**
     * Format phone number for Cameroon
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle Cameroon phone numbers
        if (strlen($phone) === 9 && (substr($phone, 0, 1) === '6' || substr($phone, 0, 1) === '7')) {
            return '237' . $phone;
        } elseif (strlen($phone) === 12 && substr($phone, 0, 3) === '237') {
            return $phone;
        }

        return $phone;
    }
}
