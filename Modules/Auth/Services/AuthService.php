<?php

namespace Modules\Auth\Services;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\LoginData;
use Modules\Auth\DTOs\RegisterCustomerData;
use Modules\Auth\DTOs\VerifyOtpData;
use Modules\Auth\Repositories\CustomerRepositoryInterface;
use Modules\Customer\Models\Customer;

class AuthService
{
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository,
        protected TokenService $tokenService,
        protected OtpService $otpService
    ) {}

    public function register(RegisterCustomerData $data): array
    {
        $customer = $this->customerRepository->create($data);

        event(new Registered($customer));

        $token = $this->tokenService->createToken($customer);

        return [
            'customer' => $customer,
            'token' => $token,
            'requires_otp' => is_null($customer->phone_verified_at),
        ];
    }
    public function verify(VerifyOtpData $otpData):string
    {
        $customer=request()->user();
      
       if (!$this->otpService->verify($customer,$otpData->otp)) {
           throw ValidationException::withMessages([
               'otp' => "otp is invalid",
           ]);
       }
       $this->customerRepository->update($customer,[
           'phone_verified_at'=>now()
       ]);
       return $this->tokenService->createToken($customer);
    }

    public function login(LoginData $data): array
    {
        $customer = $this->customerRepository->findByEmail($data->email);

        if (! $customer || ! Hash::check($data->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $this->tokenService->createToken($customer, $data->deviceName);

        return [
            'customer' => $customer,
            'token' => $token,
            'requires_otp' => is_null($customer->phone_verified_at),
        ];
    }

    public function logout(Customer $customer): void
    {
        $this->tokenService->revokeTokens($customer);
    }
}
