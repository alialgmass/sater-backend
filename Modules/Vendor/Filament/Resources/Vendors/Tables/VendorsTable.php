<?php

namespace Modules\Vendor\Filament\Resources\Vendors\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class VendorTable
{
    public static function configure(): array
    {
        return [
            ImageColumn::make('logo')
                ->label('Logo')
                ->square()
                ->defaultImageUrl(fn () => url('default-logo.png')),

            TextColumn::make('name')
                ->label('Vendor Name')
                ->searchable()
                ->sortable(),

            TextColumn::make('phone')
                ->label('Phone')
                ->searchable(),

            TextColumn::make('shop_name')
                ->label('Shop Name')
                ->searchable()
                ->sortable(),

            TextColumn::make('shop_slug')
                ->label('Slug')
                ->searchable(),

            TextColumn::make('status')
                ->badge()
                ->colors([
                    'warning' => 'pending',
                    'success' => 'active',
                    'danger'  => 'suspended',
                ]),

            TextColumn::make('created_at')
                ->dateTime()
                ->label('Created'),

            TextColumn::make('updated_at')
                ->since()
                ->label('Updated'),
        ];
    }
}
