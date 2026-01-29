<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class ListOrdersAction
{
    public function execute(): LengthAwarePaginator
    {
        return Order::query()
            ->filters()
            ->where('user_id', auth()->id())
            ->paginate();
    }
}
