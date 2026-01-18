<?php

namespace App\Filament\Resources\Returns\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Returns\RefundResource;

class ListRefunds extends ListRecords
{
    protected static string $resource = RefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}