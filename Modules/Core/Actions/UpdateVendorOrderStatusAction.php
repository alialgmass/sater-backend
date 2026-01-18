<?php

namespace App\Actions;

use Modules\Order\Enums\VendorOrderStatusEnum;
use Modules\Order\Models\VendorOrder;

class UpdateVendorOrderStatusAction
{
    public function execute(VendorOrder $vendorOrder, VendorOrderStatusEnum $newStatus): bool
    {
        $vendorOrderStatusService = app(\App\Services\VendorOrderStatusService::class);
        return $vendorOrderStatusService->updateStatus($vendorOrder, $newStatus);
    }
}