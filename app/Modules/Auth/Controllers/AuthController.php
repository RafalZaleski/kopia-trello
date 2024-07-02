<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;


class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validateUser = (array)$request->validated();
        
        $user = $this->authService->register($validateUser);

        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 200);
    }

    
    public function login(LoginRequest $request): JsonResponse
    {
        $validateUser = (array)$request->validated();

        $this->authService->login($validateUser);

        return response()->json([
            'status' => true,
            'message' => 'User Logged In Successfully',
            'token' => auth()->user()->createToken("API TOKEN")->plainTextToken
        ], 200);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();
        
        return response()->json([
            'status' => true,
            'message' => 'User Logout Successfully'
        ], 200);
    }
}
