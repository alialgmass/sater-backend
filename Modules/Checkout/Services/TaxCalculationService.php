<?php

namespace Modules\Checkout\Services;

class TaxCalculationService
{
    public function calculateTax(float $subtotal, array $address, ?int $vendorId = null): float
    {
        // Stub: 15% tax rate
        // In production, this would check address region, vendor tax rules, etc.
        return round($subtotal * 0.15, 2);
    }

    public function getTaxRate(array $address): float
    {
        // Stub: Return tax rate based on address
        return 0.15;
    }
}
