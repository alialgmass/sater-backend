<?php

namespace App\Filament\Resources\OrderInvoice\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\OrderInvoice\OrderInvoiceResource;

class ListOrderInvoices extends ListRecords
{
    protected static string $resource = OrderInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}