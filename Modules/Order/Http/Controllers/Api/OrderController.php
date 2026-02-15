<?php

namespace Modules\Order\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Modules\Order\Http\Resources\OrderDetailResource;
use Modules\Order\Http\Resources\OrderListResource;
use Modules\Order\Services\OrderCancellationService;
use Modules\Order\Services\OrderQueryService;
use Modules\Order\Services\ReorderService;
use Modules\Order\Services\InvoiceService;

class OrderController extends ApiController
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
        parent::__construct();
        $this->orderQueryService = $orderQueryService;
        $this->orderCancellationService = $orderCancellationService;
        $this->reorderService = $reorderService;
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $orders = $this->orderQueryService->getPaginatedOrdersForCustomer($request->user());
        
        return $this->apiBody([
            'orders' => OrderListResource::collection($orders)
        ])->apiResponse();
    }

    public function show(Request $request, $orderNumber)
    {
        $order = $this->orderQueryService->getOrderByOrderNumberForCustomer($request->user(), $orderNumber);
      
        return $this->apiBody([
            'order' => new OrderDetailResource($order)
        ])->apiResponse();
    }

    public function cancel(Request $request, $orderNumber)
    {
        $order = $this->orderQueryService->getOrderByOrderNumberForCustomer($request->user(), $orderNumber);
      
        $vendorOrderIds = $request->input('vendor_order_ids', []);
        $this->orderCancellationService->cancelOrder($order, $vendorOrderIds);

        return $this->apiMessage('Order cancellation request processed.')
            ->apiResponse();
    }

    public function reorder(Request $request, $orderNumber)
    {
        $order = $this->orderQueryService->getOrderByOrderNumberForCustomer($request->user(), $orderNumber);
        
        $result = $this->reorderService->reorder($order);

        return $this->apiBody(['reorder' => $result])
            ->apiResponse();
    }

    public function invoice(Request $request, $orderNumber)
    {
        $order = $this->orderQueryService->getOrderByOrderNumberForCustomer($request->user(), $orderNumber);
       
        return $this->invoiceService->generate($order)->download();
    }
}
