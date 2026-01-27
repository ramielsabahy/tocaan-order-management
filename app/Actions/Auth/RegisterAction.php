<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    public function execute(array $data): User
    {
        $user = new User;
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->name = $data['name'];
        $user->save();
        return $user;
    }
}
