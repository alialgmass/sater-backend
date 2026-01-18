<?php

namespace Modules\Order\Http\Controllers\Api;

use App\Actions\AddShippingInfoAction;
use App\Actions\BulkVendorOrderAction;
use App\Actions\GeneratePackingSlipAction;
use App\Actions\UpdateVendorOrderStatusAction;
use App\Enums\FulfillmentActionEnum;
use App\Http\Controllers\Controller;
use App\Services\OrderExportService;
use App\Services\VendorOrderQueryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Enums\VendorOrderStatusEnum;
use Modules\Order\Http\Resources\VendorOrderDetailResource;
use Modules\Order\Http\Resources\VendorOrderListResource;
use Modules\Order\Models\VendorOrder;

class VendorOrderController extends Controller
{
    public function __construct(
        protected VendorOrderQueryService $vendorOrderQueryService,
        protected OrderExportService $orderExportService
    ) {}

    /**
     * Display a listing of the vendor orders.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'cod_only' => $request->get('cod_only') === 'true',
            'vendor_order_number' => $request->get('vendor_order_number'),
            'customer_name' => $request->get('customer_name'),
        ];

        $vendorOrders = $this->vendorOrderQueryService->getPaginatedVendorOrdersForVendor(
            $request->user()->id,
            array_filter($filters, fn($value) => $value !== null && $value !== '')
        );

        return response()->json(VendorOrderListResource::collection($vendorOrders));
    }

    /**
     * Display the specified vendor order.
     */
    public function show(string $vendorOrderNumber): JsonResponse
    {
        $vendorOrder = $this->vendorOrderQueryService->getVendorOrderByNumber(
            request()->user()->id,
            $vendorOrderNumber
        );

        if (!$vendorOrder) {
            return response()->json(['error' => 'Vendor order not found'], 404);
        }

        $this->authorize('view', $vendorOrder);

        return response()->json(new VendorOrderDetailResource($vendorOrder));
    }

    /**
     * Update the status of the specified vendor order.
     */
    public function updateStatus(Request $request, string $vendorOrderNumber): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:' . implode(',', array_column(VendorOrderStatusEnum::cases(), 'value'))
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vendorOrder = $this->vendorOrderQueryService->getVendorOrderByNumber(
            $request->user()->id,
            $vendorOrderNumber
        );

        if (!$vendorOrder) {
            return response()->json(['error' => 'Vendor order not found'], 404);
        }

        $this->authorize('updateStatus', $vendorOrder);

        $newStatus = VendorOrderStatusEnum::from($request->input('status'));

        try {
            $updateStatusAction = app(UpdateVendorOrderStatusAction::class);
            $success = $updateStatusAction->execute($vendorOrder, $newStatus);

            if (!$success) {
                return response()->json(['error' => 'Invalid status transition'], 400);
            }

            return response()->json([
                'message' => 'Vendor order status updated successfully',
                'vendor_order' => new VendorOrderDetailResource($vendorOrder->refresh())
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Add shipping information to the specified vendor order.
     */
    public function addShippingInfo(Request $request, string $vendorOrderNumber): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'courier_name' => 'required|string|max:255',
            'tracking_number' => 'required|string|max:255',
            'tracking_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vendorOrder = $this->vendorOrderQueryService->getVendorOrderByNumber(
            $request->user()->id,
            $vendorOrderNumber
        );

        if (!$vendorOrder) {
            return response()->json(['error' => 'Vendor order not found'], 404);
        }

        $this->authorize('addShippingInfo', $vendorOrder);

        $addShippingInfoAction = app(AddShippingInfoAction::class);
        $shipment = $addShippingInfoAction->execute(
            $vendorOrder,
            $request->input('courier_name'),
            $request->input('tracking_number'),
            $request->input('tracking_url')
        );

        return response()->json([
            'message' => 'Shipping information added successfully',
            'shipment' => $shipment
        ]);
    }

    /**
     * Perform bulk actions on vendor orders.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:mark_as_shipped,print_packing_slips',
            'vendor_order_numbers' => 'required|array|min:1',
            'vendor_order_numbers.*' => 'string|exists:vendor_orders,vendor_order_number',
            'courier_name' => 'required_if:action,mark_as_shipped|string|max:255',
            'tracking_number' => 'required_if:action,mark_as_shipped|string|max:255',
            'tracking_url' => 'nullable_if:action,mark_as_shipped|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $action = FulfillmentActionEnum::from(str_replace('_', '-', $request->input('action')));
        $vendorOrderNumbers = $request->input('vendor_order_numbers');

        // Get vendor orders to ensure they belong to the authenticated vendor
        $vendorOrders = $this->vendorOrderQueryService->getVendorOrdersByNumbers(
            $request->user()->id,
            $vendorOrderNumbers
        );

        // Validate that all requested orders belong to the vendor
        $requestedCount = count($vendorOrderNumbers);
        $foundCount = $vendorOrders->count();

        if ($requestedCount !== $foundCount) {
            return response()->json(['error' => 'Some vendor orders not found or do not belong to you'], 400);
        }

        $data = [
            'courier_name' => $request->input('courier_name'),
            'tracking_number' => $request->input('tracking_number'),
            'tracking_url' => $request->input('tracking_url'),
        ];

        $bulkAction = app(BulkVendorOrderAction::class);
        $results = $bulkAction->execute($vendorOrders->pluck('id')->toArray(), $action, $data);

        return response()->json([
            'message' => 'Bulk action completed',
            'results' => $results
        ]);
    }

    /**
     * Generate packing slip for the specified vendor order.
     */
    public function packingSlip(string $vendorOrderNumber): Response
    {
        $vendorOrder = $this->vendorOrderQueryService->getVendorOrderByNumber(
            request()->user()->id,
            $vendorOrderNumber
        );

        if (!$vendorOrder) {
            return response()->json(['error' => 'Vendor order not found'], 404);
        }

        $this->authorize('generatePackingSlip', $vendorOrder);

        $generatePackingSlipAction = app(GeneratePackingSlipAction::class);
        return $generatePackingSlipAction->execute($vendorOrder);
    }

    /**
     * Export vendor orders.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|string|in:csv,xlsx',
            'vendor_order_numbers' => 'nullable|array',
            'vendor_order_numbers.*' => 'string|exists:vendor_orders,vendor_order_number',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $format = $request->input('format', 'csv');
        $vendorOrderNumbers = $request->input('vendor_order_numbers');

        // Get vendor orders based on filters
        $query = VendorOrder::where('vendor_id', $request->user()->id);

        if ($vendorOrderNumbers) {
            $query->whereIn('vendor_order_number', $vendorOrderNumbers);
        }

        $vendorOrders = $query->get();

        if ($format === 'csv') {
            return $this->orderExportService->exportToCsv($vendorOrders->pluck('id')->toArray());
        } else {
            return $this->orderExportService->exportToExcel($vendorOrders->pluck('id')->toArray());
        }
    }
}