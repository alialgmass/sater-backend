<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Order\Models\VendorOrder;

class PackingSlipService
{
    /**
     * Generate a packing slip for a vendor order
     */
    public function generatePackingSlip(VendorOrder $vendorOrder): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('vendor-orders.packing-slip', [
            'vendorOrder' => $vendorOrder,
            'vendor' => $vendorOrder->vendor,
            'items' => $vendorOrder->items,
            'masterOrder' => $vendorOrder->masterOrder,
        ]);

        return $pdf->download("packing-slip-{$vendorOrder->vendor_order_number}.pdf");
    }

    /**
     * Generate and cache a packing slip
     */
    public function generateAndCachePackingSlip(VendorOrder $vendorOrder): string
    {
        $cacheKey = "packing_slip_{$vendorOrder->vendor_order_number}";
        $cachePath = "packing_slips/{$vendorOrder->vendor_order_number}.pdf";

        // Check if cached version exists
        if (Storage::disk('local')->exists($cachePath)) {
            return Storage::disk('local')->path($cachePath);
        }

        $pdf = Pdf::loadView('vendor-orders.packing-slip', [
            'vendorOrder' => $vendorOrder,
            'vendor' => $vendorOrder->vendor,
            'items' => $vendorOrder->items,
            'masterOrder' => $vendorOrder->masterOrder,
        ]);

        // Store the PDF in storage
        Storage::disk('local')->put($cachePath, $pdf->output());

        return Storage::disk('local')->path($cachePath);
    }

    /**
     * Clear cached packing slip
     */
    public function clearCachedPackingSlip(VendorOrder $vendorOrder): void
    {
        $cachePath = "packing_slips/{$vendorOrder->vendor_order_number}.pdf";
        if (Storage::disk('local')->exists($cachePath)) {
            Storage::disk('local')->delete($cachePath);
        }
    }
}