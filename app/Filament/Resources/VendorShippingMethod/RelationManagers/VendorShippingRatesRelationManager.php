<?php

namespace App\Filament\Resources\VendorShippingMethod\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Shipping\VendorShippingRate;

class VendorShippingRatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rates';

    public static function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Select::make('shipping_zone_id')
                ->label('Shipping Zone')
                ->relationship('zone', 'name')
                ->required(),
            \Filament\Forms\Components\TextInput::make('min_weight')
                ->label('Min Weight')
                ->numeric()
                ->step(0.01)
                ->required(),
            \Filament\Forms\Components\TextInput::make('max_weight')
                ->label('Max Weight')
                ->numeric()
                ->step(0.01)
                ->helperText('Leave blank for unlimited'),
            \Filament\Forms\Components\TextInput::make('price')
                ->label('Price')
                ->numeric()
                ->step(0.01)
                ->prefix('$')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('zone.name')
                    ->label('Shipping Zone')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('min_weight')
                    ->label('Min Weight')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('max_weight')
                    ->label('Max Weight')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state : 'Unlimited'),
                \Filament\Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}