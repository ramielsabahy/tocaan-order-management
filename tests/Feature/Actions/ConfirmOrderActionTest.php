<?php

use App\Actions\Orders\ConfirmOrderAction;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\HttpResponseException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->confirmOrderAction = new ConfirmOrderAction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('successfully confirms an order for authenticated user', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::PENDING->value,
    ]);

    $this->confirmOrderAction->execute($order);

    expect($order->fresh()->status)->toBe(OrderStatusEnum::CONFIRMED->value);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
    ]);
});

it('throws exception when user tries to confirm another users order', function () {
    $otherUser = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $otherUser->id,
        'status' => OrderStatusEnum::PENDING->value,
    ]);

    $this->confirmOrderAction->execute($order);
})->throws(HttpResponseException::class);

it('throws exception when order is already confirmed', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
    ]);

    $this->confirmOrderAction->execute($order);
})->throws(HttpResponseException::class);

it('does not update order when user is not the owner', function () {
    $otherUser = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $otherUser->id,
        'status' => OrderStatusEnum::PENDING->value,
    ]);

    try {
        $this->confirmOrderAction->execute($order);
    } catch (HttpResponseException $e) {

    }

    expect($order->fresh()->status)->toBe(OrderStatusEnum::PENDING->value);
});

it('does not update order when already confirmed', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
    ]);

    try {
        $this->confirmOrderAction->execute($order);
    } catch (HttpResponseException $e) {

    }

    expect($order->fresh()->status)->toBe(OrderStatusEnum::CONFIRMED->value);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
    ]);
});

it('can confirm orders with different initial statuses', function () {
    $statuses = [
        OrderStatusEnum::PENDING->value,
        OrderStatusEnum::CONFIRMED->value,
        OrderStatusEnum::CANCELLED->value
    ];

    foreach ($statuses as $status) {
        if ($status === OrderStatusEnum::CONFIRMED->value) {
            continue;
        }

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => $status,
        ]);

        $this->confirmOrderAction->execute($order);

        expect($order->fresh()->status)->toBe(OrderStatusEnum::CONFIRMED->value);
    }
});
