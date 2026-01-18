<?php

namespace App\Actions\Payment;

use App\Services\Payment\PaymentService;
use App\Models\Payment\Payment;

class HandlePaymentFailureAction
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function execute(Payment $payment, string $errorMessage, array $gatewayResponse = []): void
    {
        $this->paymentService->processPaymentFailure($payment, $errorMessage, $gatewayResponse);
    }
}