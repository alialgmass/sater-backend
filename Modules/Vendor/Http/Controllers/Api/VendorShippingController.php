<?php

namespace Modules\Vendor\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\Shipping\ShipmentStatus;
use App\Services\Shipping\ShipmentCreator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Shipping\Shipment;
use App\Models\Shipping\DeliveryAttempt;

class VendorShippingController extends Controller
{
    public function __construct(
        protected ShipmentCreator $shipmentCreator
    ) {}

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

        return response()->json($shipments);
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

        return response()->json($shipment);
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

        return response()->json([
            'message' => 'Shipment status updated successfully',
            'shipment' => $updatedShipment
        ]);
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

        return response()->json([
            'message' => 'Tracking information added successfully',
            'shipment' => $updatedShipment
        ]);
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

        return response()->json($attempts);
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
            return response()->json([
                'error' => 'This shipment is not a COD order'
            ], 400);
        }

        $updatedShipment = $this->shipmentCreator->updateShipmentStatus($shipment->id, ShipmentStatus::DELIVERED);

        return response()->json([
            'message' => 'COD order marked as delivered successfully',
            'shipment' => $updatedShipment
        ]);
    }
}