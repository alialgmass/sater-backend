<?php

namespace Modules\Vendor\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Enums\Shipping\ShipmentStatus;
use App\Services\Shipping\ShipmentCreator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Shipping\Shipment;
use App\Models\Shipping\DeliveryAttempt;

class VendorShippingController extends ApiController
{
    public function __construct(
        protected ShipmentCreator $shipmentCreator
    ) {
        parent::__construct();
    }

    /**
     * Display a listing of the vendor's shipments.
     */
    public function index(Request $request): JsonResponse
    {
        $vendorId = $request->user()->id;
        
        $shipments = Shipment::where('vendor_id', $vendorId)
            ->with(['order', 'method'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->apiBody([
            'shipments' => $shipments
        ])->apiResponse();
    }

    /**
     * Display the specified shipment.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $vendorId = $request->user()->id;
        
        $shipment = Shipment::where('vendor_id', $vendorId)
            ->where('id', $id)
            ->with(['order', 'method', 'attempts'])
            ->firstOrFail();

        return $this->apiBody([
            'shipment' => $shipment
        ])->apiResponse();
    }

    /**
     * Update shipment status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,shipped,delivered,failed'
        ]);

        $vendorId = $request->user()->id;
        
        $shipment = Shipment::where('vendor_id', $vendorId)
            ->where('id', $id)
            ->firstOrFail();

        $status = ShipmentStatus::from($request->status);
        $updatedShipment = $this->shipmentCreator->updateShipmentStatus($shipment->id, $status);

        return $this->apiMessage('Shipment status updated successfully')
            ->apiBody([
                'shipment' => $updatedShipment
            ])->apiResponse();
    }

    /**
     * Add tracking information to a shipment.
     */
    public function addTrackingInfo(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'courier_name' => 'required|string|max:255',
            'tracking_number' => 'required|string|max:255',
        ]);

        $vendorId = $request->user()->id;
        
        $shipment = Shipment::where('vendor_id', $vendorId)
            ->where('id', $id)
            ->firstOrFail();

        $updatedShipment = $this->shipmentCreator->addTrackingInfo(
            $shipment->id,
            $request->courier_name,
            $request->tracking_number
        );

        return $this->apiMessage('Tracking information added successfully')
            ->apiBody([
                'shipment' => $updatedShipment
            ])->apiResponse();
    }

    /**
     * View delivery attempts for a shipment.
     */
    public function deliveryAttempts(Request $request, int $id): JsonResponse
    {
        $vendorId = $request->user()->id;
        
        $shipment = Shipment::where('vendor_id', $vendorId)
            ->where('id', $id)
            ->firstOrFail();

        $attempts = DeliveryAttempt::where('shipment_id', $shipment->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->apiBody([
            'attempts' => $attempts
        ])->apiResponse();
    }

    /**
     * Mark a COD order as delivered (special handling for COD).
     */
    public function markCodAsDelivered(Request $request, int $id): JsonResponse
    {
        $vendorId = $request->user()->id;
        
        $shipment = Shipment::where('vendor_id', $vendorId)
            ->where('id', $id)
            ->firstOrFail();

        // Verify this is a COD shipment
        if (!$shipment->method->is_cod) {
            return $this->apiMessage('This shipment is not a COD order')
                ->apiCode(400)
                ->apiResponse();
        }

        $updatedShipment = $this->shipmentCreator->updateShipmentStatus($shipment->id, ShipmentStatus::DELIVERED);

        return $this->apiMessage('COD order marked as delivered successfully')
            ->apiBody([
                'shipment' => $updatedShipment
            ])->apiResponse();
    }
}