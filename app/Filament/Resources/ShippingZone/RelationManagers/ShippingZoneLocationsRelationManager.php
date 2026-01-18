<?php

namespace App\Filament\Resources\ShippingZone\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Shipping\ShippingZoneLocation;

class ShippingZoneLocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'locations';

    public static function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Select::make('shipping_zone_id')
                ->label('Shipping Zone')
                ->relationship('zone', 'name')
                ->required(),
            \Filament\Forms\Components\TextInput::make('country')
                ->label('Country')
                ->required()
                ->maxLength(255),
            \Filament\Forms\Components\TextInput::make('region')
                ->label('Region')
                ->maxLength(255),
            \Filament\Forms\Components\TextInput::make('city')
                ->label('City')
                ->maxLength(255),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('country')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('region')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('city')
                    ->searchable()
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