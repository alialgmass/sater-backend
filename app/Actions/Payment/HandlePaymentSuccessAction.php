<?php

namespace App\Actions\Payment;

use App\Services\Payment\PaymentService;
use App\Models\Payment\Payment;

class HandlePaymentSuccessAction
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function execute(Payment $payment, array $gatewayResponse): void
    {
        $this->paymentService->processPaymentSuccess($payment, $gatewayResponse);
    }
}