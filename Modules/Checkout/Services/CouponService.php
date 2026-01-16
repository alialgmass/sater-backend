<?php

namespace Modules\Checkout\Services;

use Illuminate\Validation\ValidationException;

class CouponService
{
    public function validateCoupon(string $code, array $items): bool
    {
        // Stub: Validate coupon exists and is active
        // In production: check database, expiry, usage limits, etc.
        return true;
    }

    public function calculateDiscount(string $code, array $items): float
    {
        // Stub: Calculate discount amount
        // In production: check discount type (percentage/fixed), applicable items, etc.
        return 10.00;
    }

    public function applyCoupon(string $code, $session): \Modules\Checkout\Models\AppliedCoupon
    {
        if (!$this->validateCoupon($code, [])) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Invalid or expired coupon code.'
            ]);
        }

        $discount = $this->calculateDiscount($code, []);

        return $session->appliedCoupons()->create([
            'coupon_code' => $code,
            'discount_amount' => $discount,
            'discount_type' => 'fixed',
        ]);
    }
}
