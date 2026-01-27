<?php

namespace App\Actions\Auth;

use App\Actions\User\FindUserAction;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginAction
{
    public function __construct(private readonly FindUserAction $findUserAction)
    {
    }

    public function execute(array $data): User
    {
        $user = $this->findUserAction->execute(email: Arr::get($data, 'email'));
        if (isset($user) && Hash::check(Arr::get($data, 'password'), $user->password)) {
            return $user;
        }
        throw new HttpResponseException(
            response()->failedValidation(
                __("api.validation.User doesn't exist"),
                null
            )
        );
    }
}
