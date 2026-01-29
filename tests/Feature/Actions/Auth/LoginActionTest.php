<?php

use App\Actions\Auth\LoginAction;
use App\Actions\User\FindUserAction;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->findUserAction = Mockery::mock(FindUserAction::class);
    $this->loginAction = new LoginAction($this->findUserAction);
});

it('Successful login for user when credentials are OK', function () {
    $user = User::factory()->create([
        'email' => 'test@test.com',
        'password' => Hash::make('password'),
    ]);

    $this->findUserAction
        ->shouldReceive('execute')
        ->once()
        ->with('test@test.com')
        ->andReturn($user);

    $data = [
        'email' => 'test@test.com',
        'password' => 'password',
    ];

    $result = $this->loginAction->execute($data);

    expect($result)->toBeInstanceOf(User::class)
        ->and($result->id)->toBe($user->id)
        ->and($result->email)->toBe('test@test.com');
});

it('throws exception when user is not found', function () {
    $this->findUserAction
        ->shouldReceive('execute')
        ->once()
        ->with('noUser@test.com')
        ->andReturn(null);

    $data = [
        'email' => 'noUser@test.com',
        'password' => 'password',
    ];

    $this->loginAction->execute($data);
})->throws(HttpResponseException::class);

it('throws exception when password is incorrect', function () {
    $user = User::factory()->create([
        'email' => 'test@test.com',
        'password' => Hash::make('correctPassword'),
    ]);

    $this->findUserAction
        ->shouldReceive('execute')
        ->once()
        ->with('test@test.com')
        ->andReturn($user);

    $data = [
        'email' => 'test@test.com',
        'password' => 'wrongPassword',
    ];

    $this->loginAction->execute($data);
})->throws(HttpResponseException::class);

it('handles missing password in data array', function () {
    $user = User::factory()->create([
        'email' => 'test@test.com',
        'password' => Hash::make('password'),
    ]);

    $this->findUserAction
        ->shouldReceive('execute')
        ->once()
        ->with('test@test.com')
        ->andReturn($user);

    $data = [
        'email' => 'test@test.com',
    ];

    $this->loginAction->execute($data);
})->throws(HttpResponseException::class);
