<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\DeleteOrderAction;
use App\Actions\Orders\FindOrderAction;
use App\Actions\Orders\ListOrdersAction;
use App\Actions\Orders\UpdateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CreateOrderRequest;
use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private readonly CreateOrderAction $createOrderAction,
        private readonly UpdateOrderAction $updateOrderAction,
        private readonly ListOrdersAction $listOrdersAction,
        private readonly FindOrderAction $findOrderAction,
        private readonly DeleteOrderAction $deleteOrderAction,
    )
    {
    }

    public function index(): ResourceCollection
    {
        $orders = $this->listOrdersAction->execute();
        return OrderResource::collection($orders);
    }

    public function show(Order $order): JsonResponse
    {
        $order = $this->findOrderAction->execute(order: $order);
        return response()->success(new OrderResource($order));
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->createOrderAction->execute($request->validated());
        return response()->created(__("api.orders.Created successfully"), new OrderResource($order));
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $this->updateOrderAction->execute(order: $order, data: $request->validated());
        return response()->successWithMessage(__("api.orders.Updated successfully"));
    }

    public function destroy(Order $order): JsonResponse
    {
        $this->deleteOrderAction->execute(order: $order);
        return response()->successWithMessage(__("api.orders.Deleted successfully"));
    }
}
