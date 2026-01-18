<?php

namespace App\DTOs\Shipping;

class ShippingAddress
{
    public function __construct(
        public readonly string $country,
        public readonly ?string $region = null,
        public readonly ?string $city = null
    ) {}

    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
        ];
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }
}