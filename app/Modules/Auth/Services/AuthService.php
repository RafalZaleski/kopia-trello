<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $validateUser): User
    {
        $validateUser['password'] = Hash::make($validateUser['password']);

        $user = User::create($validateUser);

        return $user;
    }

    
    public function login(array $validateUser): void
    {
        if(!auth()->attempt($validateUser)){
            throw ValidationException::withMessages(['Email & Password does not match with our record.']);
        }
    }

    public function logout(): void
    {
        auth()->user()->tokens()->delete();
        session()->flush();
        session()->regenerateToken();
    }
}