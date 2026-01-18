<?php

namespace App\Filament\Resources\Returns\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Returns\ReturnRequestResource;

class ListReturnRequests extends ListRecords
{
    protected static string $resource = ReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}