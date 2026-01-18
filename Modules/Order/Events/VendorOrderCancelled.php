<?php

namespace Modules\Order\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Order\Models\VendorOrder;

class VendorOrderCancelled
{
    use SerializesModels;

    public $vendorOrder;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(VendorOrder $vendorOrder)
    {
        $this->vendorOrder = $vendorOrder;
    }
}
