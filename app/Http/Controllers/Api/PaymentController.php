<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payment\PaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentRequest;
use App\Models\Order;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentAction $paymentAction
    )
    {
    }

    public function __invoke(PaymentRequest $request, Order $order): JsonResponse
    {
        $this->paymentAction->execute(
            order: $order,
            paymentMethod: $request->payment_method,
            paymentData: $request->payment_data ?? []
        );

        return response()->success();
    }
}
