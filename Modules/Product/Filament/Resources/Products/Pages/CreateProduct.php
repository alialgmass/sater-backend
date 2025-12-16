<?php

namespace Modules\Product\Filament\Resources\Products\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Product\Filament\Resources\Products\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
