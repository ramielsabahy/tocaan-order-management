<?php

namespace App\Services;

use App\Contracts\PaymentInterface;
use App\Models\Payment;

class CreditCardService implements PaymentInterface
{
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        $this->apiKey = config('payment.gateways.credit_card.api_key');
        $this->apiSecret = config('payment.gateways.credit_card.api_secret');
    }

    public function processPayment(Payment $payment): array
    {
        $success = true;
        return [
            'success' => $success,
            'transaction_id' => 'CC_' . uniqid(),
            'message' => $success ? __('api.payment.Payment processed successfully') : __('api.payment.Payment failed'),
            'gateway_response' => [
                'processor' => 'credit_card',
                'timestamp' => now(),
            ]
        ];
    }

    public function validateCredentials(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiSecret);
    }
}
