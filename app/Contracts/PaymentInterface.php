<?php

namespace App\Contracts;

use App\Models\Payment;

interface PaymentInterface
{
    public function processPayment(Payment $payment): array;
    public function validateCredentials(): bool;
}
