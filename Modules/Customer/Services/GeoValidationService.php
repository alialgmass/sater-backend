<?php

namespace Modules\Customer\Services;

class GeoValidationService
{
    public function validate(string $country, string $city, string $area): bool
    {
        // Stub implementation. In real world, check against database or API.
        // For now, assume all are valid.
        return true;
    }
}
