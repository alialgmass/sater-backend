<?php

namespace App\DTOs\Shipping;

use App\Enums\Shipping\ShipmentStatus;

class ShippingCalculationResult
{
    public function __construct(
        public readonly float $cost,
        public readonly string $methodName,
        public readonly ?string $deliveryEstimate,
        public readonly bool $isFreeShipping,
        public readonly bool $isCod,
        public readonly ?string $failureReason = null
    ) {}

    public function isSuccess(): bool
    {
        return $this->failureReason === null;
    }

    public function toArray(): array
    {
        return [
            'cost' => $this->cost,
            'method_name' => $this->methodName,
            'delivery_estimate' => $this->deliveryEstimate,
            'is_free_shipping' => $this->isFreeShipping,
            'is_cod' => $this->isCod,
            'is_success' => $this->isSuccess(),
            'failure_reason' => $this->failureReason,
        ];
    }
}