<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CreateOrderRequest;
use App\Http\Resources\Order\OrderResource;
use App\Jobs\CreateOrderJob;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly CreateOrderAction $createOrderAction)
    {
    }

    public function store(CreateOrderRequest $request)
    {
        $order = $this->createOrderAction->execute($request->validated());
        return response()->created(__("api.orders.Created successfully"), new OrderResource($order));
    }
}
