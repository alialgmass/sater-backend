<?php

namespace Modules\Order\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Order\Models\Order;

class OrderPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Order $order)
    {
        return $user->id === $order->customer_id;
    }

    public function cancel(User $user, Order $order)
    {
        return $user->id === $order->customer_id;
    }

    public function reorder(User $user, Order $order)
    {
        return $user->id === $order->customer_id;
    }
}
