<?php

namespace Modules\Order\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Order\Models\VendorOrder;

class VendorOrderExport implements FromView, WithHeadings
{
    protected Collection $vendorOrders;

    public function __construct(array $vendorOrderIds)
    {
        $this->vendorOrders = VendorOrder::with(['masterOrder.customer'])
            ->whereIn('id', $vendorOrderIds)
            ->get();
    }

    public function view(): View
    {
        return view('exports.vendor-orders', [
            'vendorOrders' => $this->vendorOrders,
        ]);
    }

    public function headings(): array
    {
        return [
            'Vendor Order Number',
            'Status',
            'Total Amount',
            'Currency',
            'Is COD',
            'COD Amount',
            'Customer Name',
            'Customer Phone',
            'Shipping Address',
            'Created At',
            'Confirmed At',
            'Shipped At',
            'Delivered At',
            'Fulfillment Duration (minutes)',
        ];
    }
}