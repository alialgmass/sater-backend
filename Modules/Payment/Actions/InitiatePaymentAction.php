<?php

namespace Modules\Payment\Actions;

use Modules\Payment\Services\PaymentService;
use Modules\Payment\DTOs\PaymentInitiationDTO;

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