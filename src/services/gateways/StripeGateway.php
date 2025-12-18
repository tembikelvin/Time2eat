<?php

namespace Time2Eat\Services\Gateways;

class StripeGateway implements PaymentGatewayInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        
        // Initialize Stripe if available
        if (class_exists('\Stripe\Stripe')) {
            \Stripe\Stripe::setApiKey($this->config['secret_key']);
        }
    }

    /**
     * Process payment through Stripe
     */
    public function processPayment(array $data): array
    {
        if (!class_exists('\Stripe\Stripe')) {
            return [
                'success' => false,
                'message' => 'Stripe SDK not installed'
            ];
        }

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $this->formatAmount($data['amount']),
                'currency' => strtolower($data['currency'] ?? 'xaf'),
                'payment_method' => $data['payment_method_id'] ?? null,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => $data['return_url'] ?? null,
                'metadata' => [
                    'order_id' => $data['order']['id'] ?? null,
                    'customer_id' => $data['order']['customer_id'] ?? null,
                    'payment_id' => $data['payment_id'] ?? null
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                return [
                    'success' => true,
                    'status' => 'completed',
                    'transaction_id' => $paymentIntent->id,
                    'message' => 'Payment completed successfully'
                ];
            } elseif ($paymentIntent->status === 'requires_action') {
                return [
                    'success' => true,
                    'status' => 'requires_action',
                    'transaction_id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                    'message' => 'Payment requires additional authentication'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Payment failed: ' . $paymentIntent->status
                ];
            }

        } catch (\Stripe\Exception\CardException $e) {
            return [
                'success' => false,
                'message' => 'Card error: ' . $e->getError()->message
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'success' => false,
                'message' => 'Stripe API error: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process refund through Stripe
     */
    public function processRefund(array $payment, float $amount, string $reason): array
    {
        if (!class_exists('\Stripe\Stripe')) {
            return [
                'success' => false,
                'message' => 'Stripe SDK not installed'
            ];
        }

        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment['transaction_id'],
                'amount' => $this->formatAmount($amount),
                'reason' => $this->mapRefundReason($reason),
                'metadata' => [
                    'order_id' => $payment['order_id'],
                    'refund_reason' => $reason
                ]
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => 'completed',
                'message' => 'Refund processed successfully'
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'success' => false,
                'message' => 'Stripe refund error: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Refund processing error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle webhook from Stripe
     */
    public function handleWebhook(string $payload, string $signature): array
    {
        if (!class_exists('\Stripe\Stripe')) {
            return [
                'success' => false,
                'message' => 'Stripe SDK not installed'
            ];
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $this->config['webhook_secret']
            );

            $status = null;
            $transactionId = null;
            $failureReason = null;

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $status = 'completed';
                    $transactionId = $event->data->object->id;
                    break;
                    
                case 'payment_intent.payment_failed':
                    $status = 'failed';
                    $transactionId = $event->data->object->id;
                    $failureReason = $event->data->object->last_payment_error->message ?? 'Payment failed';
                    break;
                    
                case 'payment_intent.canceled':
                    $status = 'cancelled';
                    $transactionId = $event->data->object->id;
                    break;
            }

            if ($status && $transactionId) {
                return [
                    'success' => true,
                    'data' => [
                        'transaction_id' => $transactionId,
                        'status' => $status,
                        'gateway_response' => $event->data->object,
                        'failure_reason' => $failureReason
                    ]
                ];
            }

            return [
                'success' => true,
                'message' => 'Webhook received but not processed'
            ];

        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return [
                'success' => false,
                'message' => 'Invalid webhook signature'
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
        if (!class_exists('\Stripe\Stripe')) {
            return [
                'success' => false,
                'message' => 'Stripe SDK not installed'
            ];
        }

        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($transactionId);
            
            return [
                'success' => true,
                'status' => $this->mapStatus($paymentIntent->status),
                'amount' => $this->formatAmountFromGateway($paymentIntent->amount),
                'currency' => strtoupper($paymentIntent->currency),
                'transaction_id' => $paymentIntent->id
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'success' => false,
                'message' => 'Stripe verification error: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test connection to Stripe
     */
    public function testConnection(): array
    {
        if (!class_exists('\Stripe\Stripe')) {
            return [
                'success' => false,
                'message' => 'Stripe SDK not installed'
            ];
        }

        try {
            \Stripe\Account::retrieve();
            
            return [
                'success' => true,
                'message' => 'Stripe connection successful'
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'success' => false,
                'message' => 'Stripe connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get gateway name
     */
    public function getName(): string
    {
        return 'Stripe';
    }

    /**
     * Get gateway description
     */
    public function getDescription(): string
    {
        return 'Pay securely with credit/debit cards via Stripe';
    }

    /**
     * Get gateway logo
     */
    public function getLogo(): string
    {
        return '/images/gateways/stripe-logo.png';
    }

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool
    {
        return !empty($this->config['secret_key']) && class_exists('\Stripe\Stripe');
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return ['USD', 'EUR', 'GBP', 'XAF'];
    }

    /**
     * Get minimum amount
     */
    public function getMinAmount(): float
    {
        return 0.50; // $0.50 USD or equivalent
    }

    /**
     * Get maximum amount
     */
    public function getMaxAmount(): float
    {
        return 999999.99;
    }

    /**
     * Get fees structure
     */
    public function getFees(): array
    {
        return [
            'fixed_fee' => 0.30, // $0.30 USD
            'percentage_fee' => 2.9, // 2.9%
            'international_fee' => 1.5 // Additional 1.5% for international cards
        ];
    }

    /**
     * Calculate fees
     */
    public function calculateFees(float $amount, string $currency = 'USD'): array
    {
        $fees = $this->getFees();
        $fee = $fees['fixed_fee'] + ($amount * $fees['percentage_fee'] / 100);
        
        return [
            'fee' => $fee,
            'net_amount' => $amount - $fee,
            'total_amount' => $amount
        ];
    }

    /**
     * Format amount for Stripe (in cents)
     */
    private function formatAmount(float $amount): int
    {
        return (int)($amount * 100);
    }

    /**
     * Format amount from Stripe
     */
    private function formatAmountFromGateway(int $amount): float
    {
        return $amount / 100;
    }

    /**
     * Map Stripe status to our status
     */
    private function mapStatus(string $stripeStatus): string
    {
        $statusMap = [
            'requires_payment_method' => 'pending',
            'requires_confirmation' => 'pending',
            'requires_action' => 'processing',
            'processing' => 'processing',
            'requires_capture' => 'processing',
            'succeeded' => 'completed',
            'canceled' => 'cancelled'
        ];

        return $statusMap[$stripeStatus] ?? 'pending';
    }

    /**
     * Map refund reason to Stripe reason
     */
    private function mapRefundReason(string $reason): string
    {
        $reasonMap = [
            'duplicate' => 'duplicate',
            'fraudulent' => 'fraudulent',
            'requested_by_customer' => 'requested_by_customer'
        ];

        return $reasonMap[$reason] ?? 'requested_by_customer';
    }
}
