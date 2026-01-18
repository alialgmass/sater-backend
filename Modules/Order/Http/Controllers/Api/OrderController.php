<?php

namespace Modules\Order\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Order\Http\Resources\OrderDetailResource;
use Modules\Order\Http\Resources\OrderListResource;
use Modules\Order\Services\OrderCancellationService;
use Modules\Order\Services\OrderQueryService;
use Modules\Order\Services\ReorderService;
use Modules\Order\Services\InvoiceService;

class OrderController extends Controller
{
    protected $orderQueryService;
    protected $orderCancellationService;
    protected $reorderService;
    protected $invoiceService;

    public function __construct(
        OrderQueryService $orderQueryService,
        OrderCancellationService $orderCancellationService,
        ReorderService $reorderService,
        InvoiceService $invoiceService
    ) {
        $this->orderQueryService = $orderQueryService;
        $this->orderCancellationService = $orderCancellationService;
        $this->reorderService = $reorderService;
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $orders = $this->orderQueryService->getPaginatedOrdersForCustomer($request->user());
        return OrderListResource::collection($orders);
    }

    public function show(Request $request, $orderNumber)
    {
        $order = $this->orderQueryService->getOrderByOrderNumberForCustomer($request->user(), $orderNumber);
        $this->authorize('view', $order);
        return new OrderDetailResource($order);
    }

    public function cancel(Request $request, $orderNumber)
    {
        $order = $this->orderQueryService->getOrderByOrderNumberForCustomer($request->user(), $orderNumber);
        $this->authorize('cancel', $order);

        $vendorOrderIds = $request->input('vendor_order_ids', []);
        $this->orderCancellationService->cancelOrder($order, $vendorOrderIds);

        return response()->json(['message' => 'Order cancellation request processed.']);
    }

    public function reorder(Request $request, $orderNumber)
    {
        $order = $this->orderQueryService->getOrderByOrderNumberForCustomer($request->user(), $orderNumber);
        $this->authorize('reorder', $order);
        
        $result = $this->reorderService->reorder($order);

        return response()->json($result);
    }

    public function invoice(Request $request, $orderNumber)
    {
        $order = $this->orderQueryService->getOrderByOrderNumberForCustomer($request->user(), $orderNumber);
        $this->authorize('view', $order);

        return $this->invoiceService->generate($order)->download();
    }
}
