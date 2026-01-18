<?php

namespace Modules\Order\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Order\Models\Order;

class OrderCancelled
{
    use SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
