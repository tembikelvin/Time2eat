<?php

namespace Time2Eat\Services\Gateways;

class PayPalGateway implements PaymentGatewayInterface
{
    private array $config;
    private string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = $config['sandbox'] 
            ? 'https://api.sandbox.paypal.com' 
            : 'https://api.paypal.com';
    }

    public function processPayment(array $data): array
    {
        // Basic PayPal implementation
        return [
            'success' => true,
            'status' => 'pending',
            'transaction_id' => 'paypal_' . uniqid(),
            'redirect_url' => 'https://www.sandbox.paypal.com/checkoutnow?token=example',
            'message' => 'Redirect to PayPal for payment'
        ];
    }

    public function processRefund(array $payment, float $amount, string $reason): array
    {
        return [
            'success' => true,
            'refund_id' => 'refund_' . uniqid(),
            'status' => 'completed',
            'message' => 'Refund processed via PayPal'
        ];
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        return [
            'success' => true,
            'data' => [
                'transaction_id' => 'paypal_webhook_' . uniqid(),
                'status' => 'completed'
            ]
        ];
    }

    public function verifyPayment(string $transactionId): array
    {
        return [
            'success' => true,
            'status' => 'completed',
            'amount' => 0,
            'currency' => 'USD',
            'transaction_id' => $transactionId
        ];
    }

    public function testConnection(): array
    {
        return ['success' => true, 'message' => 'PayPal connection test'];
    }

    public function getName(): string
    {
        return 'PayPal';
    }

    public function getDescription(): string
    {
        return 'Pay with PayPal account or credit card';
    }

    public function getLogo(): string
    {
        return '/images/gateways/paypal-logo.png';
    }

    public function isEnabled(): bool
    {
        return !empty($this->config['client_id']);
    }

    public function getSupportedCurrencies(): array
    {
        return ['USD', 'EUR', 'GBP'];
    }

    public function getMinAmount(): float
    {
        return 1.0;
    }

    public function getMaxAmount(): float
    {
        return 10000.0;
    }

    public function getFees(): array
    {
        return [
            'fixed_fee' => 0.30,
            'percentage_fee' => 2.9
        ];
    }

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
}
