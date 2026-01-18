<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Order\Exports\VendorOrderExport;
use Modules\Order\Models\VendorOrder;

class OrderExportService
{
    /**
     * Export vendor orders to CSV format
     */
    public function exportToCsv(array $vendorOrderIds): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new VendorOrderExport($vendorOrderIds), 'vendor-orders.csv');
    }

    /**
     * Export vendor orders to Excel format
     */
    public function exportToExcel(array $vendorOrderIds): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new VendorOrderExport($vendorOrderIds), 'vendor-orders.xlsx');
    }

    /**
     * Prepare vendor orders data for export
     */
    public function prepareExportData(Collection $vendorOrders): array
    {
        return $vendorOrders->map(function (VendorOrder $vendorOrder) {
            return [
                'vendor_order_number' => $vendorOrder->vendor_order_number,
                'status' => $vendorOrder->status->value,
                'total_amount' => $vendorOrder->total_amount,
                'currency' => $vendorOrder->currency,
                'is_cod' => $vendorOrder->is_cod ? 'Yes' : 'No',
                'cod_amount' => $vendorOrder->is_cod ? $vendorOrder->cod_amount : null,
                'customer_name' => $vendorOrder->masterOrder->customer->name ?? '',
                'customer_phone' => $vendorOrder->masterOrder->customer->phone ?? '',
                'shipping_address' => json_encode($vendorOrder->shipping_address),
                'created_at' => $vendorOrder->created_at->toDateTimeString(),
                'confirmed_at' => $vendorOrder->confirmed_at?->toDateTimeString(),
                'shipped_at' => $vendorOrder->shipped_at?->toDateTimeString(),
                'delivered_at' => $vendorOrder->delivered_at?->toDateTimeString(),
                'fulfillment_duration' => $vendorOrder->fulfillment_duration,
            ];
        })->toArray();
    }
}