<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Illuminate\Http\Exceptions\HttpResponseException;

class FindOrderAction
{
    public function execute(Order $order): Order
    {
        if ($order->user_id != auth()->id()) {
            throw throw new HttpResponseException(
                response()->failedValidation(
                    __("api.validation.No such order")
                )
            );
        }

        return $order->load(['user', 'items']);
    }
}
