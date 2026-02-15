<?php

namespace Modules\Product\Filament\Resources\Sizes\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Product\Filament\Resources\Sizes\SizeResource;

class ManageSizes extends ManageRecords
{
    protected static string $resource = SizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
