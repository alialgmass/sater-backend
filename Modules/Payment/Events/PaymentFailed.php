<?php

namespace Modules\Payment\Events;

use Modules\Payment\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public string $errorMessage
    ) {}
}