<?php

namespace Modules\Customer\DTOs;

use Modules\Customer\Enums\AddressLabelEnum;

readonly class CreateAddressData
{
    public function __construct(
        public AddressLabelEnum $label,
        public string $country,
        public string $city,
        public string $area,
        public string $street,
        public string $building,
        public ?string $floor,
        public ?string $apartment,
        public ?string $postal_code,
        public ?float $latitude,
        public ?float $longitude,
        public bool $is_default,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            label: AddressLabelEnum::tryFrom($request->validated('label')) ?? AddressLabelEnum::HOME,
            country: $request->validated('country'),
            city: $request->validated('city'),
            area: $request->validated('area'),
            street: $request->validated('street'),
            building: $request->validated('building'),
            floor: $request->validated('floor'),
            apartment: $request->validated('apartment'),
            postal_code: $request->validated('postal_code'),
            latitude: $request->validated('latitude'),
            longitude: $request->validated('longitude'),
            is_default: $request->boolean('is_default'),
        );
    }
}
