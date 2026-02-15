<?php

namespace Modules\Product\Filament\Resources\Tags\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Product\Filament\Resources\Tags\TagResource;

class ManageTags extends ManageRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
