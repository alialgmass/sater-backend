<?php

namespace Modules\Checkout\Services;

use Modules\Checkout\Enums\ShippingMethodEnum;

class ShippingCalculationService
{
    public function calculateShipping(array $items, string $method, array $address): float
    {
        // Stub: Calculate shipping based on method
        return match($method) {
            'standard' => 10.00,
            'express' => 25.00,
            default => 10.00,
        };
    }

    public function getAvailableMethods(int $vendorId): array
    {
        return [
            [
                'method' => 'standard',
                'name' => 'Standard Shipping',
                'cost' => 10.00,
                'estimated_days' => '5-7 business days',
            ],
            [
                'method' => 'express',
                'name' => 'Express Shipping',
                'cost' => 25.00,
                'estimated_days' => '2-3 business days',
            ],
        ];
    }

    public function estimateDelivery(string $method, int $vendorId): string
    {
        return match($method) {
            'standard' => '5-7 business days',
            'express' => '2-3 business days',
            default => '5-7 business days',
        };
    }
}
