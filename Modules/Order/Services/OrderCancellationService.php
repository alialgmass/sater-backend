<?php

namespace Modules\Order\Services;

use Modules\Order\Events\VendorOrderCancelled;
use Modules\Order\Models\Order;
use Modules\Order\Enums\VendorOrderStatusEnum;
use Illuminate\Support\Facades\DB;

class OrderCancellationService
{
    public function cancelOrder(Order $order, array $vendorOrderIds = []): void
    {
        DB::transaction(function () use ($order, $vendorOrderIds) {
            $vendorOrdersToCancel = $order->vendorOrders()
                ->whereIn('status', [VendorOrderStatusEnum::CONFIRMED, VendorOrderStatusEnum::PROCESSING])
                ->when($vendorOrderIds, function ($query) use ($vendorOrderIds) {
                    return $query->whereIn('id', $vendorOrderIds);
                })
                ->get();

            if ($vendorOrdersToCancel->isEmpty()) {
                abort(422, 'No cancellable items in this order.');
            }

            foreach ($vendorOrdersToCancel as $vendorOrder) {
                $vendorOrder->update(['status' => VendorOrderStatusEnum::CANCELLED]);
                event(new VendorOrderCancelled($vendorOrder));
            }

            // If all vendor orders are cancelled, cancel the master order
            if ($order->vendorOrders()->where('status', '!=', VendorOrderStatusEnum::CANCELLED)->count() === 0) {
                $order->update(['status' => \Modules\Order\Enums\OrderStatusEnum::CANCELLED]);
                event(new \Modules\Order\Events\OrderCancelled($order));
            }
        });
    }
}
