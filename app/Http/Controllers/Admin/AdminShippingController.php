<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipping\ShippingZone;
use App\Models\Shipping\ShippingZoneLocation;
use App\Models\Shipping\VendorShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminShippingController extends Controller
{
    /**
     * Display a listing of shipping zones.
     */
    public function indexZones(): JsonResponse
    {
        $zones = ShippingZone::with('locations')->paginate(15);
        return response()->json($zones);
    }

    /**
     * Store a newly created shipping zone.
     */
    public function storeZone(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:country,region,city',
        ]);

        $zone = ShippingZone::create($request->only(['name', 'type']));

        return response()->json([
            'message' => 'Shipping zone created successfully',
            'zone' => $zone
        ], 201);
    }

    /**
     * Update the specified shipping zone.
     */
    public function updateZone(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:country,region,city',
        ]);

        $zone = ShippingZone::findOrFail($id);
        $zone->update($request->only(['name', 'type']));

        return response()->json([
            'message' => 'Shipping zone updated successfully',
            'zone' => $zone
        ]);
    }

    /**
     * Remove the specified shipping zone.
     */
    public function destroyZone(int $id): JsonResponse
    {
        $zone = ShippingZone::findOrFail($id);
        $zone->delete();

        return response()->json([
            'message' => 'Shipping zone deleted successfully'
        ]);
    }

    /**
     * Add location to a shipping zone.
     */
    public function addZoneLocation(Request $request, int $zoneId): JsonResponse
    {
        $request->validate([
            'country' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        $location = ShippingZoneLocation::create([
            'shipping_zone_id' => $zoneId,
            'country' => $request->country,
            'region' => $request->region,
            'city' => $request->city,
        ]);

        return response()->json([
            'message' => 'Zone location added successfully',
            'location' => $location
        ], 201);
    }

    /**
     * Display vendor shipping methods.
     */
    public function indexVendorMethods(): JsonResponse
    {
        $methods = VendorShippingMethod::with(['vendor', 'rates.zone'])->paginate(15);
        return response()->json($methods);
    }

    /**
     * Store a new vendor shipping method.
     */
    public function storeVendorMethod(Request $request): JsonResponse
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'is_cod' => 'boolean',
            'min_delivery_days' => 'nullable|integer|min:0',
            'max_delivery_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $method = VendorShippingMethod::create([
            'vendor_id' => $request->vendor_id,
            'name' => $request->name,
            'is_cod' => $request->is_cod ?? false,
            'min_delivery_days' => $request->min_delivery_days,
            'max_delivery_days' => $request->max_delivery_days,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Vendor shipping method created successfully',
            'method' => $method
        ], 201);
    }

    /**
     * Update a vendor shipping method.
     */
    public function updateVendorMethod(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'is_cod' => 'sometimes|boolean',
            'min_delivery_days' => 'sometimes|nullable|integer|min:0',
            'max_delivery_days' => 'sometimes|nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $method = VendorShippingMethod::findOrFail($id);
        $method->update($request->only([
            'name', 'is_cod', 'min_delivery_days', 'max_delivery_days', 'is_active'
        ]));

        return response()->json([
            'message' => 'Vendor shipping method updated successfully',
            'method' => $method
        ]);
    }

    /**
     * Interface for courier API integration (stub implementation).
     */
    public function courierIntegrationIndex(): JsonResponse
    {
        // This would list available courier integrations
        $couriers = [
            [
                'id' => 1,
                'name' => 'Generic Courier API',
                'status' => 'configured',
                'supports_tracking' => true,
            ],
            [
                'id' => 2,
                'name' => 'Another Courier Service',
                'status' => 'not_configured',
                'supports_tracking' => true,
            ]
        ];

        return response()->json($couriers);
    }

    /**
     * Configure a courier API (stub implementation).
     */
    public function configureCourier(Request $request, int $courierId): JsonResponse
    {
        // This would handle the configuration of a specific courier API
        // In a real implementation, this would store API credentials, etc.
        
        return response()->json([
            'message' => 'Courier configuration endpoint - would connect to API in real implementation',
            'courier_id' => $courierId,
            'configuration_data' => $request->all()
        ]);
    }
}