<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\ConfirmOrderAction;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class ConfirmOrderController extends Controller
{
    public function __construct(
        private readonly ConfirmOrderAction $confirmOrderAction
    )
    {
    }

    public function __invoke(Order $order): JsonResponse
    {
        $this->confirmOrderAction->execute(
            order: $order
        );

        return response()->successWithMessage(__('api.order.Order confirmed successfully'));
    }
}
