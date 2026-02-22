<?php

namespace Modules\Checkout\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Checkout\Http\Requests\CheckoutRequest;
use Modules\Checkout\Services\CheckoutService;
use Modules\Checkout\Services\OrderCreationService;
use Modules\Checkout\Services\PaymentService;

class CheckoutController extends ApiController
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected OrderCreationService $orderService,
        protected PaymentService $paymentService,
    ) {
        parent::__construct();
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $session = $this->checkoutService->placeOrder($request, $request->user('api_customers'));

        if ($session->isExpired()) {
            return $this->apiMessage('Checkout session expired.')->apiCode(400)->apiResponse();
        }

        $masterOrder = $this->orderService->createOrder($session);

        foreach ($masterOrder->vendorOrders as $vendorOrder) {
            $this->paymentService->initiatePayment($vendorOrder);
        }

        return $this->apiMessage('Order placed successfully.')
            ->apiBody([
                'order_number' => $masterOrder->order_number,
                'total'        => $masterOrder->total_amount,
                'summary'      => $this->checkoutService->getSummary($session),
            ])
            ->apiCode(201)
            ->apiResponse();
    }
}
