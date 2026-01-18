<?php

namespace App\DTOs\Shipping;

class VendorShippingData
{
    public function __construct(
        public readonly int $vendorId,
        public readonly float $totalWeight,
        public readonly float $orderAmount,
        public readonly array $items,
        public readonly ShippingAddress $address
    ) {}

    public function toArray(): array
    {
        return [
            'vendor_id' => $this->vendorId,
            'total_weight' => $this->totalWeight,
            'order_amount' => $this->orderAmount,
            'items' => $this->items,
            'address' => $this->address->toArray(),
        ];
    }
}