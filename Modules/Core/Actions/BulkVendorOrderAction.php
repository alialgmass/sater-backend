<?php

namespace Modules\Core\Actions;

use Modules\Core\Enums\FulfillmentActionEnum;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\VendorOrder;

class BulkVendorOrderAction
{
    public function execute(array $vendorOrderIds, FulfillmentActionEnum $action, array $data = []): array
    {
        $results = [];

        DB::transaction(function () use ($vendorOrderIds, $action, $data, &$results) {
            $vendorOrders = VendorOrder::whereIn('id', $vendorOrderIds)->get();

            foreach ($vendorOrders as $vendorOrder) {
                $result = $this->performAction($vendorOrder, $action, $data);
                $results[] = [
                    'vendor_order_id' => $vendorOrder->id,
                    'success' => $result['success'],
                    'message' => $result['message'] ?? '',
                ];
            }
        });

        return $results;
    }

    private function performAction(VendorOrder $vendorOrder, FulfillmentActionEnum $action, array $data): array
    {
        try {
            switch ($action) {
                case FulfillmentActionEnum::MARK_AS_SHIPPED:
                    return $this->markAsShipped($vendorOrder, $data);

                case FulfillmentActionEnum::PRINT_PACKING_SLIPS:
                    return $this->printPackingSlips($vendorOrder);

                default:
                    return [
                        'success' => false,
                        'message' => 'Unsupported action: ' . $action->value
                    ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function markAsShipped(VendorOrder $vendorOrder, array $data): array
    {
        // Check if shipping info is provided
        if (empty($data['courier_name']) || empty($data['tracking_number'])) {
            return [
                'success' => false,
                'message' => 'Courier name and tracking number are required for shipping'
            ];
        }

        // Add shipping info
        $addShippingInfoAction = app(\App\Actions\AddShippingInfoAction::class);
        $addShippingInfoAction->execute(
            $vendorOrder,
            $data['courier_name'],
            $data['tracking_number'],
            $data['tracking_url'] ?? null
        );

        // Update status to shipped
        $updateStatusAction = app(\App\Actions\UpdateVendorOrderStatusAction::class);
        $updateStatusAction->execute($vendorOrder, \Modules\Order\Enums\VendorOrderStatusEnum::SHIPPED);

        return [
            'success' => true,
            'message' => 'Order marked as shipped successfully'
        ];
    }

    private function printPackingSlips(VendorOrder $vendorOrder): array
    {
        // This action just prepares the order for printing
        // The actual PDF generation happens separately
        return [
            'success' => true,
            'message' => 'Packing slip prepared for printing'
        ];
    }
}