<?php

namespace App\Actions\Payment;

use App\Services\Payment\PaymentService;
use App\DTOs\Payment\PaymentInitiationDTO;

class InitiatePaymentAction
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function execute(PaymentInitiationDTO $dto): array
    {
        return $this->paymentService->initiatePayment($dto);
    }
}