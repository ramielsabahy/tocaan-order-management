<?php

namespace App\Actions\User;

use App\Models\User;

class FindUserAction
{
    public function execute(string $email)
    {
        return User::where('email', $email)->first();
    }
}
