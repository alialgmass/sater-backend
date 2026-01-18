<?php

namespace App\Services;

use Illuminate\Support\Facades\Event;
use Modules\Order\Enums\VendorOrderStatusEnum;
use Modules\Order\Events\VendorOrderStatusUpdated;
use Modules\Order\Models\VendorOrder;

class VendorOrderStatusService
{
    public function __construct(
        protected VendorOrderStateMachine $stateMachine
    ) {}

    /**
     * Update vendor order status with validation
     */
    public function updateStatus(VendorOrder $vendorOrder, VendorOrderStatusEnum $newStatus): bool
    {
        if (!$this->stateMachine->canTransition($vendorOrder, $newStatus)) {
            throw new \InvalidArgumentException(
                "Invalid status transition from {$vendorOrder->status->value} to {$newStatus->value}"
            );
        }

        // For COD orders, prevent marking as delivered without confirmation
        if ($vendorOrder->is_cod && 
            $newStatus === VendorOrderStatusEnum::DELIVERED && 
            !$vendorOrder->cod_confirmed) {
            throw new \InvalidArgumentException(
                "COD order must be confirmed before marking as delivered"
            );
        }

        $success = $this->stateMachine->transition($vendorOrder, $newStatus);

        if ($success) {
            // Fire event for status update
            Event::dispatch(new VendorOrderStatusUpdated($vendorOrder, $newStatus));
        }

        return $success;
    }

    /**
     * Get allowed next statuses for a vendor order
     */
    public function getAllowedNextStatuses(VendorOrder $vendorOrder): array
    {
        return $this->stateMachine->getAllowedNextStatuses($vendorOrder->status);
    }
}