<?php

namespace Modules\Payment\Actions;

use Modules\Payment\Services\PaymentService;
use Modules\Payment\DTOs\PaymentVerificationDTO;

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