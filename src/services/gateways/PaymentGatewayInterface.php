<?php

namespace Time2Eat\Services\Gateways;

interface PaymentGatewayInterface
{
    /**
     * Process payment through the gateway
     *
     * @param array $data Payment data including amount, currency, customer info, etc.
     * @return array Result with success status, transaction ID, redirect URL, etc.
     */
    public function processPayment(array $data): array;

    /**
     * Process refund through the gateway
     *
     * @param array $payment Original payment data
     * @param float $amount Refund amount
     * @param string $reason Refund reason
     * @return array Result with success status, refund ID, etc.
     */
    public function processRefund(array $payment, float $amount, string $reason): array;

    /**
     * Handle webhook from the payment gateway
     *
     * @param string $payload Webhook payload
     * @param string $signature Webhook signature for verification
     * @return array Processed webhook data
     */
    public function handleWebhook(string $payload, string $signature): array;

    /**
     * Verify payment status with the gateway
     *
     * @param string $transactionId Transaction ID to verify
     * @return array Payment status and details
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Test connection to the payment gateway
     *
     * @return array Connection test result
     */
    public function testConnection(): array;

    /**
     * Get the gateway name
     *
     * @return string Gateway name
     */
    public function getName(): string;

    /**
     * Get the gateway description
     *
     * @return string Gateway description
     */
    public function getDescription(): string;

    /**
     * Get the gateway logo URL
     *
     * @return string Logo URL
     */
    public function getLogo(): string;

    /**
     * Check if the gateway is enabled and configured
     *
     * @return bool True if enabled
     */
    public function isEnabled(): bool;

    /**
     * Get supported currencies
     *
     * @return array Array of supported currency codes
     */
    public function getSupportedCurrencies(): array;

    /**
     * Get minimum payment amount
     *
     * @return float Minimum amount
     */
    public function getMinAmount(): float;

    /**
     * Get maximum payment amount
     *
     * @return float Maximum amount
     */
    public function getMaxAmount(): float;

    /**
     * Get fee structure
     *
     * @return array Fee structure with fixed_fee, percentage_fee, etc.
     */
    public function getFees(): array;

    /**
     * Calculate fees for a given amount
     *
     * @param float $amount Payment amount
     * @param string $currency Currency code
     * @return array Calculated fees with fee, net_amount, total_amount
     */
    public function calculateFees(float $amount, string $currency = 'XAF'): array;
}
