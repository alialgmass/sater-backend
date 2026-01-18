<?php

namespace App\Actions;

use Modules\Order\Models\VendorOrder;

class GeneratePackingSlipAction
{
    public function execute(VendorOrder $vendorOrder): \Illuminate\Http\Response
    {
        $packingSlipService = app(\App\Services\PackingSlipService::class);
        return $packingSlipService->generatePackingSlip($vendorOrder);
    }
}