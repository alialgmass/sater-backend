<?php

namespace Modules\Auth\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Models\Customer;

class OtpService
{
    private const TTL_MINUTES = 5;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private Cache $cache
    ) {}

    public function generate(Customer $customer): string
    {
        $otp = $this->generateSecureOtp();

        $this->cache->put(
            $this->otpKey($customer),
            Hash::make($otp),
            now()->addMinutes(self::TTL_MINUTES)
        );

        $this->cache->put(
            $this->attemptKey($customer),
            0,
            now()->addMinutes(self::TTL_MINUTES)
        );

        return $otp;
    }

    public function verify(Customer $customer, string $otp): bool
    {
        $hashedOtp = $this->cache->get($this->otpKey($customer));

        if (!$hashedOtp) {
            return false;
        }

        if ($this->tooManyAttempts($customer)) {
            return false;
        }

        if (!Hash::check($otp, $hashedOtp)) {
            $this->incrementAttempts($customer);
            return false;
        }

        $this->invalidate($customer);

        return true;
    }

    private function generateSecureOtp(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function otpKey(Customer $customer): string
    {
        return "customer:{$customer->id}:otp";
    }

    private function attemptKey(Customer $customer): string
    {
        return "customer:{$customer->id}:otp_attempts";
    }

    private function incrementAttempts(Customer $customer): void
    {
        $this->cache->increment($this->attemptKey($customer));
    }

    private function tooManyAttempts(Customer $customer): bool
    {
        return $this->cache->get($this->attemptKey($customer), 0) >= self::MAX_ATTEMPTS;
    }

    private function invalidate(Customer $customer): void
    {
        $this->cache->forget($this->otpKey($customer));
        $this->cache->forget($this->attemptKey($customer));
    }
}
