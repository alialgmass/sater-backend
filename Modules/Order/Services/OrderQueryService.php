<?php

namespace Modules\Order\Services;

use Modules\Auth\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Order\Models\Order;

class OrderQueryService
{
    public function getPaginatedOrdersForCustomer(Customer $customer, int $perPage = 15): LengthAwarePaginator
    {
        return $customer->orders()
            ->latest()
            ->paginate($perPage);
    }

    public function getOrderByOrderNumberForCustomer(Customer $customer, string $orderNumber): ?Order
    {
        return $customer->orders()
            ->where('order_number', $orderNumber)
            ->with(['items', 'vendorOrders.shipment', 'vendorOrders.items'])
            ->first();
    }
}
