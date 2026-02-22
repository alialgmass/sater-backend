<?php

namespace Modules\Auth\Services;

use Modules\Customer\Models\Customer;

class TokenService
{
    public function createToken(Customer $customer, string $deviceName = 'auth_token'): string
    {
        return $customer->createToken($deviceName)->plainTextToken;
    }

    public function revokeTokens(Customer $customer): void
    {
        $customer->tokens()->delete();
    }
}
