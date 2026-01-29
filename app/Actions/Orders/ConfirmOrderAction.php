<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConfirmOrderAction
{
    public function execute(Order $order): void
    {
        if ($order->user_id != auth()->id()) {
            throw throw new HttpResponseException(
                response()->failedValidation(
                    __("api.validation.No such order")
                )
            );
        }

        if ($order->status == OrderStatusEnum::CONFIRMED->value) {
            throw throw new HttpResponseException(
                response()->failedValidation(
                    __("api.validation.Order already confirmed")
                )
            );
        }

        $order->update(['status' => OrderStatusEnum::CONFIRMED->value]);
    }
}
