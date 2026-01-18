<?php

namespace App\Services\Shipping;

use App\DTOs\Shipping\ShippingAddress;
use App\Models\Shipping\ShippingZone;
use App\Models\Shipping\ShippingZoneLocation;

class ShippingZoneResolver
{
    /**
     * Resolve the shipping zone based on the customer's address
     */
    public function resolve(ShippingAddress $address): ?ShippingZone
    {
        // First, try to find a zone that matches the exact address (country, region, city)
        $zoneLocation = ShippingZoneLocation::where('country', $address->getCountry())
            ->where('region', $address->getRegion())
            ->where('city', $address->getCity())
            ->first();

        if ($zoneLocation) {
            return $zoneLocation->zone;
        }

        // If no exact match, try to find a zone that matches country and region
        $zoneLocation = ShippingZoneLocation::where('country', $address->getCountry())
            ->where('region', $address->getRegion())
            ->whereNull('city')
            ->first();

        if ($zoneLocation) {
            return $zoneLocation->zone;
        }

        // Finally, try to find a zone that matches just the country
        $zoneLocation = ShippingZoneLocation::where('country', $address->getCountry())
            ->whereNull('region')
            ->whereNull('city')
            ->first();

        if ($zoneLocation) {
            return $zoneLocation->zone;
        }

        return null;
    }

    /**
     * Get all zones that cover a specific address
     */
    public function getZonesForAddress(ShippingAddress $address): array
    {
        $zones = [];

        // Find zones that match the exact address (country, region, city)
        $exactMatches = ShippingZoneLocation::where('country', $address->getCountry())
            ->where('region', $address->getRegion())
            ->where('city', $address->getCity())
            ->with('zone')
            ->get();

        foreach ($exactMatches as $location) {
            $zones[] = $location->zone;
        }

        // Find zones that match country and region
        if ($address->getRegion()) {
            $regionMatches = ShippingZoneLocation::where('country', $address->getCountry())
                ->where('region', $address->getRegion())
                ->whereNull('city')
                ->with('zone')
                ->get();

            foreach ($regionMatches as $location) {
                if (!in_array($location->zone->id, array_column($zones, 'id'))) {
                    $zones[] = $location->zone;
                }
            }
        }

        // Find zones that match just the country
        $countryMatches = ShippingZoneLocation::where('country', $address->getCountry())
            ->whereNull('region')
            ->whereNull('city')
            ->with('zone')
            ->get();

        foreach ($countryMatches as $location) {
            if (!in_array($location->zone->id, array_column($zones, 'id'))) {
                $zones[] = $location->zone;
            }
        }

        return $zones;
    }
}