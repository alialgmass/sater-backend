<?php

namespace Modules\Order\Filament\Resources\VendorOrders\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Order\Filament\Resources\VendorOrders\VendorOrderResource;

class ListVendorOrders extends ListRecords
{
    protected static string $resource = VendorOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}