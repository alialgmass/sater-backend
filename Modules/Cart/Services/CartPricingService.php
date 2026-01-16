<?php

namespace Modules\Cart\Services;

use Illuminate\Support\Collection;

class CartPricingService
{
    public function calculateCartTotals($cart): array
    {
        $items = $cart instanceof Collection ? $cart : $cart->items;
        
        $vendorGroups = $this->groupItemsByVendor($items);
        $vendors = [];
        $subtotal = 0;

        foreach ($vendorGroups as $vendorId => $vendorItems) {
            $vendorSubtotal = $this->calculateVendorSubtotal($vendorItems);
            $vendorShipping = $this->calculateShipping($vendorItems);
            
            $vendors[] = [
                'vendor_id' => $vendorId,
                'vendor_name' => $vendorItems->first()->vendor->name ?? 'Unknown',
                'items_count' => $vendorItems->count(),
                'subtotal' => $vendorSubtotal,
                'shipping' => $vendorShipping,
                'total' => $vendorSubtotal + $vendorShipping,
            ];
            
            $subtotal += $vendorSubtotal;
        }

        $tax = $this->calculateTax($subtotal);
        $totalShipping = collect($vendors)->sum('shipping');
        $grandTotal = $subtotal + $tax + $totalShipping;

        return [
            'vendors' => $vendors,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($totalShipping, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    public function groupItemsByVendor(Collection $items): Collection
    {
        return $items->groupBy('vendor_id');
    }

    public function calculateVendorSubtotal(Collection $items): float
    {
        return $items->sum(function ($item) {
            // Use current price from product, not snapshot
            return $item->product->price * $item->quantity;
        });
    }

    public function calculateTax(float $subtotal): float
    {
        // Stub: 15% tax rate
        return $subtotal * 0.15;
    }

    public function calculateShipping(Collection $items): float
    {
        // Stub: Flat rate shipping per vendor
        return 10.00;
    }
}
