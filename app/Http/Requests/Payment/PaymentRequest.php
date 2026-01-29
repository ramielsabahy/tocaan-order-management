<?php

namespace App\Http\Requests\Payment;

use App\Services\PaymentGatewayFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => [
                'required',
                'string',
                Rule::in(PaymentGatewayFactory::getSupportedMethods())
            ],
            'payment_data' => 'sometimes|array',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => __('api.payment.Invalid payment method, Supported methods are ') .
                implode(', ', PaymentGatewayFactory::getSupportedMethods()),
        ];
    }
}
