<?php

namespace App\Actions\Payment;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentGatewayFactory;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PaymentAction
{
    public function execute(Order $order, string $paymentMethod, array $paymentData = []): Payment
    {
        if ($order->status !== OrderStatusEnum::CONFIRMED->value) {
            throw new HttpResponseException(
                response()->failedValidation(
                    message: __("api.validation.Payment can be processed only for confirmed orders"),
                )
            );
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
            'amount' => $order->total,
            'status' => PaymentStatusEnum::SUCCESSFUL->value,
            'payment_data' => json_encode($paymentData),
        ]);
        DB::beginTransaction();
        try {
            $gateway = PaymentGatewayFactory::create($paymentMethod);
            $result = $gateway->processPayment($payment);
            $payment->update([
                'status' => Arr::get($result,'success') ? PaymentStatusEnum::SUCCESSFUL->value : PaymentStatusEnum::FAILED->value,
                'transaction_id' => Arr::get($result, 'transaction_id') ?? null,
                'gateway_response' => Arr::get($result, 'gateway_response') ?? null,
            ]);
            if (Arr::get($result, 'success')) {
                $order->update(['status' => OrderStatusEnum::CONFIRMED->value]);
            }
            DB::commit();
            return $payment;
        }catch (\Exception $exception){
            DB::rollBack();
            $payment->update([
                'status' => PaymentStatusEnum::FAILED->value,
                'gateway_response' => json_encode(['error' => $exception->getMessage()])
            ]);
            throw new HttpResponseException(
                response()->error(
                    $exception->getMessage()
                )
            );
        }
    }
}
