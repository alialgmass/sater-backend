<?php

namespace App\Filament\Resources\Shipment\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Shipment\ShipmentResource;

class ListShipments extends ListRecords
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}