<?php

namespace Modules\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Order\Models\VendorOrder;

class VendorOrderDelivered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public VendorOrder $vendorOrder
    ) {}
}