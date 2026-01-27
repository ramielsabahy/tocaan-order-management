<?php

namespace App\Actions;

use App\Models\User;

class CreateTokenAction
{
    public function execute(User $user): array
    {
        $accessToken = $user->createToken($user->name.' '.'access-token');

        $refreshToken = $user->createToken($user->name.' '.'refresh-token');

        return [$accessToken, $refreshToken];
    }
}
