<?php

namespace Time2Eat\Services;

use Time2Eat\Services\Gateways\StripeGateway;
use Time2Eat\Services\Gateways\PayPalGateway;
use Time2Eat\Services\Gateways\OrangeMoneyGateway;
use Time2Eat\Services\Gateways\MTNMoMoGateway;
use Time2Eat\Services\Gateways\TranzakGateway;

class PaymentGatewayService
{
    private array $gateways = [];

    public function __construct()
    {
        $this->initializeGateways();
    }

    /**
     * Process payment through appropriate gateway
     */
    public function processPayment(string $method, array $data): array
    {
        try {
            $gateway = $this->getGateway($method);
            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => 'Payment method not supported'
                ];
            }

            return $gateway->processPayment($data);

        } catch (\Exception $e) {
            error_log("Payment processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process refund through appropriate gateway
     */
    public function processRefund(array $payment, float $amount, string $reason): array
    {
        try {
            $gateway = $this->getGateway($payment['method']);
            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => 'Refund not supported for this payment method'
                ];
            }

            return $gateway->processRefund($payment, $amount, $reason);

        } catch (\Exception $e) {
            error_log("Refund processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Refund processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle webhook from payment gateway
     */
    public function handleWebhook(string $provider, string $payload, string $signature): array
    {
        try {
            $gateway = $this->getGatewayByProvider($provider);
            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => 'Unknown payment provider'
                ];
            }

            return $gateway->handleWebhook($payload, $signature);

        } catch (\Exception $e) {
            error_log("Webhook processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Webhook processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $method, string $transactionId): array
    {
        try {
            $gateway = $this->getGateway($method);
            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => 'Payment method not supported'
                ];
            }

            return $gateway->verifyPayment($transactionId);

        } catch (\Exception $e) {
            error_log("Payment verification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment verification failed'
            ];
        }
    }

    /**
     * Get supported payment methods
     */
    public function getSupportedMethods(): array
    {
        $methods = [];
        
        foreach ($this->gateways as $method => $gateway) {
            if ($gateway->isEnabled()) {
                $methods[] = [
                    'method' => $method,
                    'name' => $gateway->getName(),
                    'description' => $gateway->getDescription(),
                    'logo' => $gateway->getLogo(),
                    'currencies' => $gateway->getSupportedCurrencies(),
                    'min_amount' => $gateway->getMinAmount(),
                    'max_amount' => $gateway->getMaxAmount(),
                    'fees' => $gateway->getFees()
                ];
            }
        }

        return $methods;
    }

    /**
     * Calculate payment fees
     */
    public function calculateFees(string $method, float $amount, string $currency = 'XAF'): array
    {
        $gateway = $this->getGateway($method);
        if (!$gateway) {
            return [
                'fee' => 0,
                'net_amount' => $amount,
                'total_amount' => $amount
            ];
        }

        return $gateway->calculateFees($amount, $currency);
    }

    /**
     * Initialize payment gateways
     */
    private function initializeGateways(): void
    {
        // Stripe Gateway
        if (!empty($_ENV['STRIPE_SECRET_KEY'])) {
            $this->gateways['stripe'] = new StripeGateway([
                'secret_key' => $_ENV['STRIPE_SECRET_KEY'],
                'public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
                'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null
            ]);
        }

        // PayPal Gateway
        if (!empty($_ENV['PAYPAL_CLIENT_ID'])) {
            $this->gateways['paypal'] = new PayPalGateway([
                'client_id' => $_ENV['PAYPAL_CLIENT_ID'],
                'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET'],
                'sandbox' => $_ENV['PAYPAL_SANDBOX'] ?? true
            ]);
        }

        // Orange Money Gateway
        if (!empty($_ENV['ORANGE_MONEY_MERCHANT_KEY'])) {
            $this->gateways['orange_money'] = new OrangeMoneyGateway([
                'merchant_key' => $_ENV['ORANGE_MONEY_MERCHANT_KEY'],
                'api_url' => $_ENV['ORANGE_MONEY_API_URL'],
                'sandbox' => $_ENV['ORANGE_MONEY_SANDBOX'] ?? true
            ]);
        }

        // MTN Mobile Money Gateway
        if (!empty($_ENV['MTN_MOMO_API_KEY'])) {
            $this->gateways['mtn_momo'] = new MTNMoMoGateway([
                'api_key' => $_ENV['MTN_MOMO_API_KEY'],
                'api_secret' => $_ENV['MTN_MOMO_API_SECRET'],
                'sandbox' => $_ENV['MTN_MOMO_SANDBOX'] ?? true
            ]);
        }

        // Tranzak Gateway (for Cameroon mobile payments)
        if (!empty($_ENV['TRANZAK_API_KEY'])) {
            $this->gateways['mobile_money'] = new TranzakGateway([
                'api_key' => $_ENV['TRANZAK_API_KEY'],
                'api_secret' => $_ENV['TRANZAK_API_SECRET'],
                'sandbox' => $_ENV['TRANZAK_SANDBOX'] ?? true
            ]);
        }
    }

    /**
     * Get gateway by payment method
     */
    private function getGateway(string $method): ?object
    {
        // Map payment methods to gateways
        $methodGatewayMap = [
            'stripe' => 'stripe',
            'card' => 'stripe',
            'paypal' => 'paypal',
            'orange_money' => 'orange_money',
            'mtn_momo' => 'mtn_momo',
            'mobile_money' => 'tranzak', // Default mobile money to Tranzak
        ];

        $gatewayKey = $methodGatewayMap[$method] ?? null;
        return $gatewayKey ? ($this->gateways[$gatewayKey] ?? null) : null;
    }

    /**
     * Get gateway by provider name
     */
    private function getGatewayByProvider(string $provider): ?object
    {
        return $this->gateways[$provider] ?? null;
    }

    /**
     * Validate payment data
     */
    public function validatePaymentData(string $method, array $data): array
    {
        $errors = [];

        // Common validations
        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Invalid payment amount';
        }

        if (empty($data['currency'])) {
            $data['currency'] = 'XAF'; // Default currency
        }

        // Method-specific validations
        switch ($method) {
            case 'stripe':
            case 'card':
                if (empty($data['payment_method_id']) && empty($data['card_token'])) {
                    $errors[] = 'Payment method or card token required';
                }
                break;

            case 'paypal':
                if (empty($data['return_url']) || empty($data['cancel_url'])) {
                    $errors[] = 'Return and cancel URLs required for PayPal';
                }
                break;

            case 'orange_money':
            case 'mtn_momo':
            case 'mobile_money':
                if (empty($data['phone_number'])) {
                    $errors[] = 'Phone number required for mobile money';
                } elseif (!preg_match('/^(\+237|237)?[67]\d{8}$/', $data['phone_number'])) {
                    $errors[] = 'Invalid Cameroon phone number format';
                }
                break;
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors,
            'data' => $data
        ];
    }

    /**
     * Format amount for gateway
     */
    public function formatAmount(float $amount, string $currency = 'XAF'): int
    {
        // Most gateways expect amounts in smallest currency unit (cents)
        switch ($currency) {
            case 'XAF':
                return (int)($amount * 100); // Convert to centimes
            case 'USD':
            case 'EUR':
                return (int)($amount * 100); // Convert to cents
            default:
                return (int)$amount;
        }
    }

    /**
     * Format amount from gateway
     */
    public function formatAmountFromGateway(int $amount, string $currency = 'XAF'): float
    {
        // Convert from smallest currency unit back to main unit
        switch ($currency) {
            case 'XAF':
            case 'USD':
            case 'EUR':
                return $amount / 100;
            default:
                return (float)$amount;
        }
    }

    /**
     * Get gateway configuration
     */
    public function getGatewayConfig(string $method): array
    {
        $gateway = $this->getGateway($method);
        if (!$gateway) {
            return [];
        }

        return [
            'name' => $gateway->getName(),
            'enabled' => $gateway->isEnabled(),
            'currencies' => $gateway->getSupportedCurrencies(),
            'min_amount' => $gateway->getMinAmount(),
            'max_amount' => $gateway->getMaxAmount(),
            'fees' => $gateway->getFees()
        ];
    }

    /**
     * Test gateway connection
     */
    public function testGateway(string $method): array
    {
        try {
            $gateway = $this->getGateway($method);
            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => 'Gateway not found'
                ];
            }

            return $gateway->testConnection();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gateway test failed: ' . $e->getMessage()
            ];
        }
    }
}
