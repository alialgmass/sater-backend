<?php

namespace Modules\Checkout\Services;

use App\DTOs\Shipping\ShippingAddress;
use App\DTOs\Shipping\VendorShippingData;
use App\Services\Shipping\ShippingCostCalculator;
use App\Services\Shipping\ShippingZoneResolver;

class ShippingCalculationService
{
    public function __construct(
        protected ShippingCostCalculator $shippingCostCalculator,
        protected ShippingZoneResolver $zoneResolver
    ) {}

    /**
     * Calculate shipping for items using the new shipping system
     */
    public function calculateShipping(array $items, string $method, array $address): float
    {
        // Convert address to DTO
        $shippingAddress = new ShippingAddress(
            country: $address['country'] ?? '',
            region: $address['region'] ?? null,
            city: $address['city'] ?? null
        );

        // Calculate total weight and amount for the items
        $totalWeight = array_sum(array_map(function($item) {
            return ($item['weight'] ?? 0) * ($item['quantity'] ?? 1);
        }, $items));

        $totalAmount = array_sum(array_map(function($item) {
            return ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
        }, $items));

        // Group items by vendor
        $vendorItems = [];
        foreach ($items as $item) {
            $vendorId = $item['vendor_id'] ?? 0;
            if (!isset($vendorItems[$vendorId])) {
                $vendorItems[$vendorId] = [];
            }
            $vendorItems[$vendorId][] = $item;
        }

        // Calculate shipping for each vendor
        $totalShippingCost = 0;
        foreach ($vendorItems as $vendorId => $vendorSpecificItems) {
            $vendorData = new VendorShippingData(
                vendorId: $vendorId,
                totalWeight: array_sum(array_map(function($item) {
                    return ($item['weight'] ?? 0) * ($item['quantity'] ?? 1);
                }, $vendorSpecificItems)),
                orderAmount: array_sum(array_map(function($item) {
                    return ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                }, $vendorSpecificItems)),
                items: $vendorSpecificItems,
                address: $shippingAddress
            );

            $results = $this->shippingCostCalculator->calculate($vendorData);
            
            // Find the selected method and add its cost
            foreach ($results as $result) {
                if (strtolower($result->methodName) === strtolower($method)) {
                    $totalShippingCost += $result->cost;
                    break;
                }
            }
        }

        return $totalShippingCost;
    }

    /**
     * Get available shipping methods for a vendor
     */
    public function getAvailableMethods(int $vendorId, array $address, float $totalWeight, float $orderAmount): array
    {
        // Convert address to DTO
        $shippingAddress = new ShippingAddress(
            country: $address['country'] ?? '',
            region: $address['region'] ?? null,
            city: $address['city'] ?? null
        );

        $vendorData = new VendorShippingData(
            vendorId: $vendorId,
            totalWeight: $totalWeight,
            orderAmount: $orderAmount,
            items: [], // Items not needed for method listing
            address: $shippingAddress
        );

        $results = $this->shippingCostCalculator->calculate($vendorData);

        $methods = [];
        foreach ($results as $result) {
            if ($result->isSuccess()) {
                $methods[] = [
                    'method' => strtolower($result->methodName),
                    'name' => $result->methodName,
                    'cost' => $result->cost,
                    'estimated_days' => $result->deliveryEstimate,
                    'is_free_shipping' => $result->isFreeShipping,
                    'is_cod' => $result->isCod,
                ];
            }
        }

        return $methods;
    }

    /**
     * Estimate delivery for a specific method
     */
    public function estimateDelivery(string $method, int $vendorId): string
    {
        // This would typically use the DeliveryEstimator service
        // For now, returning a default value
        return 'Estimated delivery time';
    }

    /**
     * Validate if shipping is available for an address
     */
    public function isShippingAvailable(array $address): bool
    {
        $shippingAddress = new ShippingAddress(
            country: $address['country'] ?? '',
            region: $address['region'] ?? null,
            city: $address['city'] ?? null
        );

        $zone = $this->zoneResolver->resolve($shippingAddress);
        return $zone !== null;
    }
}