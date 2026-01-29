<?php

use App\Actions\Auth\RegisterAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->registerAction = new RegisterAction();
});

it('successfully registers a new user with valid data', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@test.com',
        'password' => 'password123',
    ];

    $user = $this->registerAction->execute($data);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@test.com')
        ->and($user->exists)->toBeTrue();

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@test.com',
    ]);
});

it('hashes the password before saving', function () {
    $data = [
        'name' => 'some user',
        'email' => 'user@test.com',
        'password' => 'plainpassword',
    ];

    $user = $this->registerAction->execute($data);

    expect($user->password)->not->toBe('plainpassword')
        ->and(Hash::check('plainpassword', $user->password))->toBeTrue();
});

it('saves user to database', function () {
    $data = [
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => 'testpassword',
    ];

    $this->registerAction->execute($data);

    expect(User::where('email', 'test@test.com')->exists())->toBeTrue();
});

it('returns a user model instance', function () {
    $data = [
        'name' => 'Another User',
        'email' => 'another@test.com',
        'password' => 'password',
    ];

    $result = $this->registerAction->execute($data);

    expect($result)->toBeInstanceOf(User::class)
        ->and($result->id)->not->toBeNull();
});
