<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payment\ListPaymentAction;
use App\Actions\Payment\PaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ListPaymentRequest;
use App\Http\Requests\Payment\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentAction $paymentAction,
        private readonly ListPaymentAction $listPaymentAction,
    )
    {
    }

    public function index(ListPaymentRequest $request): JsonResponse
    {
        $payments = $this->listPaymentAction->execute(orderId: $request->order_id);
        $collection = PaymentResource::collection($payments);
        return response()->success([
            'data' => $collection,
            'links' => $payments->linkCollection(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    public function store(PaymentRequest $request, Order $order): JsonResponse
    {
        $this->paymentAction->execute(
            order: $order,
            paymentMethod: $request->payment_method,
            paymentData: $request->payment_data ?? []
        );

        return response()->success();
    }
}
