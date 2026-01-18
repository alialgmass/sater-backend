<?php

namespace App\Filament\Resources\VendorShippingMethod\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\VendorShippingMethod\VendorShippingMethodResource;

class ListVendorShippingMethods extends ListRecords
{
    protected static string $resource = VendorShippingMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}