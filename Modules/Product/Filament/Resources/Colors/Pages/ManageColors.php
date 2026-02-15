<?php

namespace Modules\Product\Filament\Resources\Colors\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Product\Filament\Resources\Colors\ColorResource;

class ManageColors extends ManageRecords
{
    protected static string $resource = ColorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
