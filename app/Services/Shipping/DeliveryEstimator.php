<?php

namespace App\Services\Shipping;

use App\Models\Shipping\VendorShippingMethod;
use Carbon\Carbon;

class DeliveryEstimator
{
    /**
     * Estimate delivery date range for a shipping method
     */
    public function estimate(VendorShippingMethod $method, ?Carbon $startDate = null): ?array
    {
        if ($method->min_delivery_days === null || $method->max_delivery_days === null) {
            return null;
        }

        $start = $startDate ?: Carbon::now();
        
        $minDeliveryDate = $start->copy()->addDays($method->min_delivery_days);
        $maxDeliveryDate = $start->copy()->addDays($method->max_delivery_days);

        return [
            'from' => $minDeliveryDate,
            'to' => $maxDeliveryDate,
            'formatted_range' => $this->formatDateRange($minDeliveryDate, $maxDeliveryDate)
        ];
    }

    /**
     * Format the date range for display
     */
    private function formatDateRange(Carbon $from, Carbon $to): string
    {
        if ($from->equalTo($to)) {
            return $from->format('M j, Y');
        }

        if ($from->format('M Y') === $to->format('M Y')) {
            return $from->format('M j') . ' - ' . $to->format('j, Y');
        }

        return $from->format('M j') . ' - ' . $to->format('M j, Y');
    }

    /**
     * Estimate delivery for a specific vendor and method
     */
    public function estimateForVendorMethod(int $vendorId, int $methodId, ?Carbon $startDate = null): ?array
    {
        $method = VendorShippingMethod::where('vendor_id', $vendorId)
            ->where('id', $methodId)
            ->where('is_active', true)
            ->first();

        if (!$method) {
            return null;
        }

        return $this->estimate($method, $startDate);
    }
}