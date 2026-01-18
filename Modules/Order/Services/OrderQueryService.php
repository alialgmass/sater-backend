<?php

namespace Modules\Order\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Order\Models\Order;

class OrderQueryService
{
    public function getPaginatedOrdersForCustomer(User $customer, int $perPage = 15): LengthAwarePaginator
    {
        return $customer->orders()
            ->latest()
            ->paginate($perPage);
    }

    public function getOrderByOrderNumberForCustomer(User $customer, string $orderNumber): ?Order
    {
        return $customer->orders()
            ->where('order_number', $orderNumber)
            ->with(['items', 'vendorOrders.shipment', 'vendorOrders.items'])
            ->first();
    }
}
