<?php

namespace Time2Eat\Services\Gateways;

class MTNMoMoGateway implements PaymentGatewayInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function processPayment(array $data): array
    {
        // Basic MTN Mobile Money implementation for Cameroon
        return [
            'success' => true,
            'status' => 'pending',
            'transaction_id' => 'mtn_' . uniqid(),
            'message' => 'MTN Mobile Money payment initiated'
        ];
    }

    public function processRefund(array $payment, float $amount, string $reason): array
    {
        return [
            'success' => true,
            'refund_id' => 'mtn_refund_' . uniqid(),
            'status' => 'completed',
            'message' => 'MTN Mobile Money refund processed'
        ];
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        return [
            'success' => true,
            'data' => [
                'transaction_id' => 'mtn_webhook_' . uniqid(),
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
            'currency' => 'XAF',
            'transaction_id' => $transactionId
        ];
    }

    public function testConnection(): array
    {
        return ['success' => true, 'message' => 'MTN MoMo connection test'];
    }

    public function getName(): string
    {
        return 'MTN Mobile Money';
    }

    public function getDescription(): string
    {
        return 'Pay with MTN Mobile Money';
    }

    public function getLogo(): string
    {
        return '/images/gateways/mtn-momo-logo.png';
    }

    public function isEnabled(): bool
    {
        return !empty($this->config['api_key']);
    }

    public function getSupportedCurrencies(): array
    {
        return ['XAF'];
    }

    public function getMinAmount(): float
    {
        return 100.0;
    }

    public function getMaxAmount(): float
    {
        return 500000.0;
    }

    public function getFees(): array
    {
        return [
            'fixed_fee' => 0,
            'percentage_fee' => 1.5
        ];
    }

    public function calculateFees(float $amount, string $currency = 'XAF'): array
    {
        $fees = $this->getFees();
        $fee = ($amount * $fees['percentage_fee']) / 100;
        
        return [
            'fee' => $fee,
            'net_amount' => $amount - $fee,
            'total_amount' => $amount
        ];
    }
}
