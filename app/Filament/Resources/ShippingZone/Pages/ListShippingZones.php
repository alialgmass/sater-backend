<?php

namespace App\Filament\Resources\ShippingZone\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ShippingZone\ShippingZoneResource;

class ListShippingZones extends ListRecords
{
    protected static string $resource = ShippingZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}