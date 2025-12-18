<?php

declare(strict_types=1);

namespace services;

class TranzackPayoutService
{
	private string $apiKey;
	private string $baseUrl;

	public function __construct()
	{
		$mode = defined('TRANZACK_MODE') ? TRANZACK_MODE : 'sandbox';
		$this->apiKey = defined('TRANZACK_API_KEY') ? TRANZACK_API_KEY : '';
		$this->baseUrl = $mode === 'production'
			? (defined('TRANZACK_BASE_URL_PRODUCTION') ? TRANZACK_BASE_URL_PRODUCTION : '')
			: (defined('TRANZACK_BASE_URL_SANDBOX') ? TRANZACK_BASE_URL_SANDBOX : '');
	}

	public function initiatePayout(array $payload): array
	{
		if (empty($this->apiKey) || empty($this->baseUrl)) {
			return [
				'success' => false,
				'message' => 'Tranzack configuration missing'
			];
		}

		// Expected payload keys (example): amount, currency, method, recipient
		$endpoint = rtrim($this->baseUrl, '/') . '/v1/payouts';

		$headers = [
			"Authorization: Bearer {$this->apiKey}",
			'Content-Type: application/json'
		];

		$ch = curl_init($endpoint);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

		$response = curl_exec($ch);
		$error = curl_error($ch);
		$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($error) {
			return [
				'success' => false,
				'message' => 'Network error: ' . $error
			];
		}

		$data = json_decode($response, true) ?: [];

		if ($httpCode >= 200 && $httpCode < 300) {
			return [
				'success' => true,
				'transaction_id' => $data['transaction_id'] ?? ($data['id'] ?? ''),
				'raw' => $data
			];
		}

		return [
			'success' => false,
			'message' => $data['message'] ?? ('HTTP ' . $httpCode),
			'raw' => $data
		];
	}
}



