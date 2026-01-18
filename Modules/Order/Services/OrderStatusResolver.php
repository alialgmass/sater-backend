<?php

namespace Modules\Order\Services;

use Modules\Order\Enums\OrderStatusEnum;
use Modules\Order\Enums\VendorOrderStatusEnum;
use Modules\Order\Models\Order;
use Illuminate\Support\Collection;

class OrderStatusResolver
{
    public function resolve(Order $order): OrderStatusEnum
    {
        $vendorOrderStatuses = $order->vendorOrders->pluck('status');

        if ($this->allCancelled($vendorOrderStatuses)) {
            return OrderStatusEnum::CANCELLED;
        }

        if ($this->allDelivered($vendorOrderStatuses)) {
            return OrderStatusEnum::DELIVERED;
        }

        if ($this->anyShipped($vendorOrderStatuses)) {
            return OrderStatusEnum::PARTIALLY_SHIPPED;
        }

        if ($this->allConfirmed($vendorOrderStatuses)) {
            return OrderStatusEnum::CONFIRMED;
        }

        if ($this->anyProcessing($vendorOrderStatuses)) {
            return OrderStatusEnum::PROCESSING;
        }

        return $order->status; // fallback to current status
    }

    private function allCancelled(Collection $statuses): bool
    {
        return $statuses->every(fn($status) => $status === VendorOrderStatusEnum::CANCELLED);
    }

    private function allDelivered(Collection $statuses): bool
    {
        return $statuses->every(fn($status) => $status === VendorOrderStatusEnum::DELIVERED);
    }

    private function anyShipped(Collection $statuses): bool
    {
        return $statuses->contains(VendorOrderStatusEnum::SHIPPED);
    }

    private function allConfirmed(Collection $statuses): bool
    {
        return $statuses->every(fn($status) => $status === VendorOrderStatusEnum::CONFIRMED);
    }

    private function anyProcessing(Collection $statuses): bool
    {
        return $statuses->contains(VendorOrderStatusEnum::PROCESSING);
    }
}
