<?php

namespace App\Actions\Payment;

use App\Services\Payment\PaymentService;
use App\DTOs\Payment\PaymentVerificationDTO;

class VerifyPaymentAction
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function execute(PaymentVerificationDTO $dto): array
    {
        return $this->paymentService->verifyPayment($dto);
    }
}