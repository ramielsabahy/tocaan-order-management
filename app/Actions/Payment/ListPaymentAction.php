<?php

namespace App\Actions\Payment;

use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

class ListPaymentAction
{
    public function execute(mixed $orderId): LengthAwarePaginator
    {
        $userId = auth()->user()->id;
        $payments = Payment::query()
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->filters($orderId)->paginate();
        return $payments;
    }
}
