<?php

namespace App\Services;

use App\Contracts\PaymentInterface;
use Illuminate\Http\Exceptions\HttpResponseException;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public static function create(string $method): PaymentInterface
    {
        return match($method) {
            'credit_card' => new CreditCardService(),
            'paypal' => new PaypalService(),
            default => throw throw new HttpResponseException(
                response()->error(
                    __("api.validation.Unsupported payment method")
                )
            )
        };
    }

    public static function getSupportedMethods(): array
    {
        return ['credit_card', 'paypal'];
    }
}
