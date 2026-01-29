<?php

namespace App\Http\Resources;

use App\Http\Resources\Order\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'payment_method' => $this->payment_method,
            'status'    => $this->status,
            'amount'    => $this->amount,
            'payment_data' => json_decode($this->payment_data, true),
            'gateway_response' => json_decode($this->gateway_response, true),
            'order'     => new OrderResource($this->order)
        ];
    }
}
