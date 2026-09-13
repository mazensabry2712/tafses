<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticateUserAction
{
    public function execute(string $email, string $password): User
    {
        $user = User::query()->where('email', $email)->first();

        if (!$user || !$user->is_active || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are invalid.',
            ]);
        }

        Auth::login($user);
        request()->session()->regenerate();

        return $user;
    }
}
