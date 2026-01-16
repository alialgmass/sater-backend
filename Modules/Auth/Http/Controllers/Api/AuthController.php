<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\DTOs\RegisterCustomerData;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = RegisterCustomerData::fromRequest($request);

        $customer = $this->authService->registerCustomer($data);

        $token = $customer->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Customer registered successfully.',
            'data' => [
                'customer' => $customer,
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request, \Modules\Auth\Actions\LoginCustomerAction $loginAction): JsonResponse
    {
        $data = \Modules\Auth\DTOs\LoginData::fromRequest($request);

        $customer = $loginAction->execute($data);

        $token = $customer->createToken($data->deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'customer' => $customer,
                'token' => $token,
            ],
        ]);
    }
}
