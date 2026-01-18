<?php

namespace App\Services\Shipping;

use App\DTOs\Shipping\ShippingCalculationResult;
use App\DTOs\Shipping\VendorShippingData;
use App\Models\Shipping\ShippingZone;
use App\Models\Shipping\VendorFreeShippingRule;
use App\Models\Shipping\VendorShippingMethod;
use App\Models\Shipping\VendorShippingRate;

class ShippingCostCalculator
{
    public function __construct(
        protected ShippingZoneResolver $zoneResolver
    ) {}

    /**
     * Calculate shipping cost for a vendor's order
     */
    public function calculate(VendorShippingData $vendorData): array
    {
        $results = [];
        
        // Get the shipping zone for the address
        $zone = $this->zoneResolver->resolve($vendorData->address);
        
        if (!$zone) {
            $results[] = new ShippingCalculationResult(
                cost: 0,
                methodName: 'Not Available',
                deliveryEstimate: null,
                isFreeShipping: false,
                isCod: false,
                failureReason: 'No shipping zone found for address'
            );
            return $results;
        }

        // Get active shipping methods for this vendor
        $shippingMethods = VendorShippingMethod::where('vendor_id', $vendorData->vendorId)
            ->where('is_active', true)
            ->get();

        foreach ($shippingMethods as $method) {
            $rate = $this->findRateForZoneAndWeight($method->id, $zone->id, $vendorData->totalWeight);

            if (!$rate) {
                $results[] = new ShippingCalculationResult(
                    cost: 0,
                    methodName: $method->name,
                    deliveryEstimate: null,
                    isFreeShipping: false,
                    isCod: $method->is_cod,
                    failureReason: 'No rate found for this zone and weight'
                );
                continue;
            }

            // Check if free shipping applies
            $freeShippingRule = VendorFreeShippingRule::where('vendor_id', $vendorData->vendorId)
                ->where('shipping_zone_id', $zone->id)
                ->where('min_order_amount', '<=', $vendorData->orderAmount)
                ->first();

            $cost = 0;
            $isFreeShipping = false;

            if ($freeShippingRule) {
                $cost = 0;
                $isFreeShipping = true;
            } else {
                $cost = $rate->price;
            }

            // Calculate delivery estimate
            $deliveryEstimate = $this->calculateDeliveryEstimate($method);

            $results[] = new ShippingCalculationResult(
                cost: $cost,
                methodName: $method->name,
                deliveryEstimate: $deliveryEstimate,
                isFreeShipping: $isFreeShipping,
                isCod: $method->is_cod,
                failureReason: null
            );
        }

        return $results;
    }

    /**
     * Find the appropriate rate for a given zone and weight
     */
    private function findRateForZoneAndWeight(int $methodId, int $zoneId, float $weight): ?VendorShippingRate
    {
        return VendorShippingRate::where('vendor_shipping_method_id', $methodId)
            ->where('shipping_zone_id', $zoneId)
            ->where('min_weight', '<=', $weight)
            ->where(function ($query) use ($weight) {
                $query->whereNull('max_weight')
                      ->orWhere('max_weight', '>=', $weight);
            })
            ->first();
    }

    /**
     * Calculate delivery estimate based on shipping method
     */
    private function calculateDeliveryEstimate(VendorShippingMethod $method): ?string
    {
        if ($method->min_delivery_days === null || $method->max_delivery_days === null) {
            return null;
        }

        if ($method->min_delivery_days === $method->max_delivery_days) {
            return "{$method->min_delivery_days} day" . ($method->min_delivery_days > 1 ? 's' : '');
        }

        return "{$method->min_delivery_days}-{$method->max_delivery_days} days";
    }

    /**
     * Calculate total shipping cost for an order across all vendors
     */
    public function calculateTotalForOrder(array $vendorShippingDataArray): array
    {
        $results = [];

        foreach ($vendorShippingDataArray as $vendorData) {
            $calculationResults = $this->calculate($vendorData);
            $results[$vendorData->vendorId] = $calculationResults;
        }

        return $results;
    }
}