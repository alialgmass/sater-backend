<?php

namespace App\Services;

use Illuminate\Support\Facades\Event;
use Modules\Order\Events\VendorOrderShipped;
use Modules\Order\Models\Shipment;
use Modules\Order\Models\VendorOrder;

class ShipmentService
{
    /**
     * Add shipping information to a vendor order
     */
    public function addShippingInfo(
        VendorOrder $vendorOrder,
        string $courierName,
        string $trackingNumber,
        ?string $trackingUrl = null
    ): Shipment {
        $shipment = $vendorOrder->shipment()->updateOrCreate([
            'vendor_order_id' => $vendorOrder->id,
        ], [
            'courier_name' => $courierName,
            'tracking_number' => $trackingNumber,
            'tracking_url' => $trackingUrl,
        ]);

        // Dispatch event when shipment is added
        Event::dispatch(new VendorOrderShipped($vendorOrder, $shipment));

        return $shipment;
    }

    /**
     * Get shipment by vendor order
     */
    public function getShipmentByVendorOrder(VendorOrder $vendorOrder): ?Shipment
    {
        return $vendorOrder->shipment;
    }
}