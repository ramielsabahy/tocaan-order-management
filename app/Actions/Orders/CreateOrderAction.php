<?php

namespace App\Actions\Orders;

use App\Actions\Products\ProductsTotalAction;
use App\Actions\User\FindUserAction;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function __construct(
        private readonly FindUserAction $findUserAction,
        private readonly ProductsTotalAction $productsTotalAction,
    )
    {
    }

    public function execute(array $data): Order
    {
        $user = $this->findUserAction->execute(email: request()->user()->email);
        DB::beginTransaction();
        try{
            $productIds = collect(Arr::get($data, 'items'))->pluck('product_id')->toArray();
            $orderTotal = $this->productsTotalAction->execute(productIds: $productIds);
            $order = new Order();
            $order->user_id = $user->id;
            $order->total = $orderTotal;
            $order->save();
            $order->refresh();

            foreach ($data['items'] as $item){
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = Arr::get($item, 'product_id');
                $orderItem->quantity = Arr::get($item, 'quantity');
                $orderItem->total = $this->productsTotalAction->execute(productIds: [Arr::get($item, 'product_id')]);
                $orderItem->save();
            }
            DB::commit();
            return $order;
        }catch (\Exception $exception){
            DB::rollBack();
            throw new HttpResponseException(
                response()->error(
                    __("api.exceptions.Something went wrong"),
                    $exception->getMessage()
                )
            );
        }
    }
}
