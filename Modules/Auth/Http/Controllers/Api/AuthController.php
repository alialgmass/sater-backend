<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\DTOs\LoginData;
use Modules\Auth\DTOs\RegisterCustomerData;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;

class AuthController extends ApiController
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = RegisterCustomerData::fromRequest($request);

        $result = $this->authService->register($data);

        return $this->apiMessage('Customer registered successfully.')
            ->apiBody(['auth' => $result])
            ->apiCode(201)
            ->apiResponse();
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = LoginData::fromRequest($request);

        $result = $this->authService->login($data);

        return $this->apiMessage('Login successful.')
            ->apiBody(['auth' => $result])
            ->apiResponse();
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->apiMessage('Logged out successfully.')
            ->apiResponse();
    }
}
