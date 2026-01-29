<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteOrderAction
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

        if (sizeof($order->payments)){
            throw throw new HttpResponseException(
                response()->failedValidation(
                    __("api.validation.Can't delete order with associated payments")
                )
            );
        }

        $order->delete();
    }
}
