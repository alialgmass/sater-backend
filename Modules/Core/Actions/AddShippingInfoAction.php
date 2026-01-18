<?php

namespace App\Actions;

use Modules\Order\Models\VendorOrder;

class AddShippingInfoAction
{
    public function execute(
        VendorOrder $vendorOrder,
        string $courierName,
        string $trackingNumber,
        ?string $trackingUrl = null
    ): \Modules\Order\Models\Shipment {
        $shipmentService = app(\App\Services\ShipmentService::class);
        return $shipmentService->addShippingInfo($vendorOrder, $courierName, $trackingNumber, $trackingUrl);
    }
}