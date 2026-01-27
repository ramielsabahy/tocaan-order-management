<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\RegisterAction;
use App\Actions\CreateTokenAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;

class AuthenticationController extends Controller
{
    public function __construct(
        private readonly RegisterAction $registerAction,
        private readonly CreateTokenAction $createTokenAction,
        private readonly LoginAction $loginAction,
    )
    {
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->registerAction->execute(data: $request->validated());
        [$accessToken, $refreshToken] = $this->createTokenAction->execute($user);
        return response()->created(
            __("api.user.Registered successfully"),
            [
                'user' => new UserResource($user),
                'accessToken' => $accessToken->plainTextToken,
                'refreshToken' => $refreshToken->plainTextToken,
            ]
        );
    }

    public function login(LoginRequest $request)
    {
        $user = $this->loginAction->execute(data: $request->validated());
        [$accessToken, $refreshToken] = $this->createTokenAction->execute($user);
        return response()->successWithMessage(
            __("api.user.Registered successfully"),
            [
                'user' => new UserResource($user),
                'accessToken' => $accessToken->plainTextToken,
                'refreshToken' => $refreshToken->plainTextToken,
            ]
        );
    }
}
