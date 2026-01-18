<?php

namespace App\Services\Shipping;

use App\Enums\Shipping\ShipmentStatus;
use App\Models\Shipping\Shipment;
use App\Models\Shipping\VendorShippingMethod;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;

class ShipmentCreator
{
    public function __construct(
        protected DeliveryEstimator $deliveryEstimator
    ) {}

    /**
     * Create shipments for an order
     */
    public function createForOrder(Order $order, array $shippingSelections): array
    {
        $createdShipments = [];

        DB::transaction(function () use ($order, $shippingSelections, &$createdShipments) {
            foreach ($shippingSelections as $selection) {
                $vendorId = $selection['vendor_id'];
                $methodId = $selection['method_id'];
                
                // Get the shipping method
                $method = VendorShippingMethod::findOrFail($methodId);
                
                // Estimate delivery dates
                $estimates = $this->deliveryEstimator->estimate($method);
                
                // Create the shipment
                $shipment = Shipment::create([
                    'order_id' => $order->id,
                    'vendor_id' => $vendorId,
                    'shipping_method_id' => $methodId,
                    'status' => ShipmentStatus::PENDING->value,
                    'estimated_delivery_from' => $estimates['from'] ?? null,
                    'estimated_delivery_to' => $estimates['to'] ?? null,
                ]);

                $createdShipments[] = $shipment;
            }
        });

        return $createdShipments;
    }

    /**
     * Create a single shipment
     */
    public function createSingleShipment(
        int $orderId,
        int $vendorId,
        int $methodId,
        ?string $courierName = null,
        ?string $trackingNumber = null
    ): Shipment {
        $method = VendorShippingMethod::findOrFail($methodId);
        $estimates = $this->deliveryEstimator->estimate($method);

        return Shipment::create([
            'order_id' => $orderId,
            'vendor_id' => $vendorId,
            'shipping_method_id' => $methodId,
            'courier_name' => $courierName,
            'tracking_number' => $trackingNumber,
            'status' => ShipmentStatus::PENDING->value,
            'estimated_delivery_from' => $estimates['from'] ?? null,
            'estimated_delivery_to' => $estimates['to'] ?? null,
        ]);
    }

    /**
     * Update shipment status
     */
    public function updateShipmentStatus(int $shipmentId, ShipmentStatus $status): Shipment
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $shipment->update(['status' => $status->value]);
        
        return $shipment->fresh();
    }

    /**
     * Add tracking information to a shipment
     */
    public function addTrackingInfo(int $shipmentId, string $courierName, string $trackingNumber): Shipment
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $shipment->update([
            'courier_name' => $courierName,
            'tracking_number' => $trackingNumber,
        ]);
        
        return $shipment->fresh();
    }
}