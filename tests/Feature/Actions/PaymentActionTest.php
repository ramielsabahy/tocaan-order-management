<?php

use App\Actions\Payment\PaymentAction;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentGatewayFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\HttpResponseException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->paymentAction = new PaymentAction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('successfully processes payment for confirmed order', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'total' => 100,
    ]);

    $gateway = Mockery::mock();
    $gateway->shouldReceive('processPayment')
        ->once()
        ->andReturn([
            'success' => true,
            'transaction_id' => '123456',
            'gateway_response' => ['message' => 'Payment successful'],
        ]);
    
    $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
    $mock->shouldReceive('create')
        ->with('credit_card')
        ->andReturn($gateway);

    $paymentData = ['card_number' => '1234'];

    $payment = $this->paymentAction->execute($order, 'credit_card', $paymentData);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->order_id)->toBe($order->id)
        ->and($payment->payment_method)->toBe('credit_card')
        ->and($payment->amount)->toBe(100)
        ->and($payment->status)->toBe(PaymentStatusEnum::SUCCESSFUL->value);
});

it('throws exception when order is not confirmed', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::PENDING->value,
        'total' => 100,
    ]);

    $this->paymentAction->execute($order, 'credit_card');
})->throws(HttpResponseException::class);

it('handles failed payment from gateway', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'total' => 100,
    ]);

    $gateway = Mockery::mock();
    $gateway->shouldReceive('processPayment')
        ->once()
        ->andReturn([
            'success' => false,
            'transaction_id' => null,
            'gateway_response' => ['error' => 'Insufficient funds'],
        ]);

    $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
    $mock->shouldReceive('create')
        ->with('credit_card')
        ->andReturn($gateway);

    $payment = $this->paymentAction->execute($order, 'credit_card');

    expect($payment->status)->toBe(PaymentStatusEnum::FAILED->value)
        ->and($payment->transaction_id)->toBeNull();
});

it('stores payment data as json', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'total' => 100,
    ]);

    $gateway = Mockery::mock();
    $gateway->shouldReceive('processPayment')
        ->once()
        ->andReturn([
            'success' => true,
            'transaction_id' => '56789',
            'gateway_response' => ['status' => 'approved'],
        ]);

    $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
    $mock->shouldReceive('create')
        ->andReturn($gateway);

    $paymentData = [
        'card_type' => 'visa',
        'last_four' => '1234',
        'expiry' => '12/25',
    ];

    $payment = $this->paymentAction->execute($order, 'credit_card', $paymentData);

    expect(json_decode($payment->payment_data, true))->toBe($paymentData);
});

it('rolls back transaction on gateway exception', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'total' => 100,
    ]);

    $gateway = Mockery::mock();
    $gateway->shouldReceive('processPayment')
        ->once()
        ->andThrow(new \Exception('Gateway timeout'));

    $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
    $mock->shouldReceive('create')
        ->andReturn($gateway);

    try {
        $this->paymentAction->execute($order, 'credit_card');
    } catch (HttpResponseException $e) {

    }

    $payment = Payment::where('order_id', $order->id)->first();

    expect($payment->status)->toBe(PaymentStatusEnum::FAILED->value);

    $gatewayResponse = json_decode($payment->gateway_response, true);
    expect($gatewayResponse['error'])->toBe('Gateway timeout');
});

it('updates payment status on exception', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'total' => 100,
    ]);

    $gateway = Mockery::mock();
    $gateway->shouldReceive('processPayment')
        ->once()
        ->andThrow(new \Exception('Network error'));

    $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
    $mock->shouldReceive('create')
        ->andReturn($gateway);

    try {
        $this->paymentAction->execute($order, 'credit_card');
    } catch (HttpResponseException $e) {

    }

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => PaymentStatusEnum::FAILED->value,
    ]);
});

it('throws exception on gateway failure', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'total' => 100,
    ]);

    $gateway = Mockery::mock();
    $gateway->shouldReceive('processPayment')
        ->once()
        ->andThrow(new \Exception('Payment declined'));

    $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
    $mock->shouldReceive('create')
        ->andReturn($gateway);

    $this->paymentAction->execute($order, 'credit_card');
})->throws(HttpResponseException::class);

it('keeps order status confirmed on successful payment', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'total' => 100.00,
    ]);

    $gateway = Mockery::mock();
    $gateway->shouldReceive('processPayment')
        ->once()
        ->andReturn([
            'success' => true,
            'transaction_id' => '999999',
            'gateway_response' => ['status' => 'success'],
        ]);

    $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
    $mock->shouldReceive('create')
        ->andReturn($gateway);

    $this->paymentAction->execute($order, 'credit_card');

    expect($order->fresh()->status)->toBe(OrderStatusEnum::CONFIRMED->value);
});

it('does not update order status on failed payment', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'total' => 100,
    ]);

    $gateway = Mockery::mock();
    $gateway->shouldReceive('processPayment')
        ->once()
        ->andReturn([
            'success' => false,
            'transaction_id' => null,
            'gateway_response' => ['error' => 'Declined'],
        ]);

    $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
    $mock->shouldReceive('create')
        ->andReturn($gateway);

    $this->paymentAction->execute($order, 'credit_card');

    expect($order->fresh()->status)->toBe(OrderStatusEnum::CONFIRMED->value);
});

it('handles different payment methods', function () {
    $paymentMethods = ['credit_card', 'paypal', 'cash_on_delivery'];

    foreach ($paymentMethods as $method) {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatusEnum::CONFIRMED->value,
            'total' => 100,
        ]);

        $gateway = Mockery::mock();
        $gateway->shouldReceive('processPayment')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => "123_{$method}",
                'gateway_response' => ['method' => $method],
            ]);

        $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
        $mock->shouldReceive('create')
            ->with($method)
            ->andReturn($gateway);

        $payment = $this->paymentAction->execute($order, $method);

        expect($payment->payment_method)->toBe($method);

        Mockery::close();
    }
});

it('creates payment record before processing', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'total' => 100,
    ]);

    $gateway = Mockery::mock();
    $gateway->shouldReceive('processPayment')
        ->once()
        ->andReturn([
            'success' => true,
            'transaction_id' => '123456',
            'gateway_response' => [],
        ]);

    $mock = Mockery::mock('overload:' . PaymentGatewayFactory::class);
    $mock->shouldReceive('create')
        ->andReturn($gateway);

    $initialCount = Payment::count();

    $this->paymentAction->execute($order, 'credit_card');

    expect(Payment::count())->toBe($initialCount + 1);
});
