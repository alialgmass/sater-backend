<?php

namespace Modules\Payment\Events;

use Modules\Payment\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRefunded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public float $amount,
        public ?string $reason = null
    ) {}
}