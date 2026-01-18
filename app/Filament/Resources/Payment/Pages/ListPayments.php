<?php

namespace App\Filament\Resources\Payment\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Payment\PaymentResource;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}