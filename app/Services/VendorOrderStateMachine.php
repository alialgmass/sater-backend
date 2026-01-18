<?php

namespace App\Services;

use Modules\Order\Enums\VendorOrderStatusEnum;
use Modules\Order\Models\VendorOrder;

class VendorOrderStateMachine
{
    /**
     * Define allowed status transitions
     */
    private const ALLOWED_TRANSITIONS = [
        VendorOrderStatusEnum::CONFIRMED->value => [
            VendorOrderStatusEnum::PROCESSING->value,
        ],
        VendorOrderStatusEnum::PROCESSING->value => [
            VendorOrderStatusEnum::PACKED->value,
        ],
        VendorOrderStatusEnum::PACKED->value => [
            VendorOrderStatusEnum::SHIPPED->value,
        ],
        VendorOrderStatusEnum::SHIPPED->value => [
            VendorOrderStatusEnum::OUT_FOR_DELIVERY->value,
            VendorOrderStatusEnum::DELIVERED->value,
        ],
        VendorOrderStatusEnum::OUT_FOR_DELIVERY->value => [
            VendorOrderStatusEnum::DELIVERED->value,
        ],
    ];

    /**
     * Check if a status transition is allowed
     */
    public function canTransition(VendorOrder $vendorOrder, VendorOrderStatusEnum $newStatus): bool
    {
        $currentStatus = $vendorOrder->status->value;
        
        if (!isset(self::ALLOWED_TRANSITIONS[$currentStatus])) {
            return false;
        }

        return in_array($newStatus->value, self::ALLOWED_TRANSITIONS[$currentStatus]);
    }

    /**
     * Get allowed next statuses for a given status
     */
    public function getAllowedNextStatuses(VendorOrderStatusEnum $currentStatus): array
    {
        if (!isset(self::ALLOWED_TRANSITIONS[$currentStatus->value])) {
            return [];
        }

        return array_map(
            fn($status) => VendorOrderStatusEnum::from($status),
            self::ALLOWED_TRANSITIONS[$currentStatus->value]
        );
    }

    /**
     * Transition a vendor order to a new status
     */
    public function transition(VendorOrder $vendorOrder, VendorOrderStatusEnum $newStatus): bool
    {
        if (!$this->canTransition($vendorOrder, $newStatus)) {
            return false;
        }

        // Update timestamps based on status
        $this->updateTimestamps($vendorOrder, $newStatus);

        $vendorOrder->update([
            'status' => $newStatus,
        ]);

        return true;
    }

    /**
     * Update timestamps based on status
     */
    private function updateTimestamps(VendorOrder $vendorOrder, VendorOrderStatusEnum $newStatus): void
    {
        $updates = [];

        switch ($newStatus) {
            case VendorOrderStatusEnum::CONFIRMED:
                $updates['confirmed_at'] = now();
                break;
            case VendorOrderStatusEnum::PROCESSING:
                $updates['processing_started_at'] = now();
                break;
            case VendorOrderStatusEnum::PACKED:
                $updates['packed_at'] = now();
                break;
            case VendorOrderStatusEnum::SHIPPED:
                $updates['shipped_at'] = now();
                break;
            case VendorOrderStatusEnum::DELIVERED:
                $updates['delivered_at'] = now();
                break;
        }

        if (!empty($updates)) {
            $vendorOrder->update($updates);
        }
    }
}