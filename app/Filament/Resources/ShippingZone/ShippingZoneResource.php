<?php

namespace App\Filament\Resources\ShippingZone;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ShippingZone\Pages\ListShippingZones;
use App\Filament\Resources\ShippingZone\Pages\CreateShippingZone;
use App\Filament\Resources\ShippingZone\Pages\EditShippingZone;
use App\Filament\Resources\ShippingZone\RelationManagers\ShippingZoneLocationsRelationManager;
use App\Models\Shipping\ShippingZone as ShippingZoneModel;

class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZoneModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

  public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                 \Filament\Forms\Components\Section::make('Zone Information')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Zone Name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\Select::make('type')
                            ->options([
                                'country' => 'Country',
                                'region' => 'Region',
                                'city' => 'City',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Zone Name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'country' => 'primary',
                        'region' => 'warning',
                        'city' => 'success',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('locations_count')
                    ->label('Locations')
                    ->counts('locations')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'country' => 'Country',
                        'region' => 'Region',
                        'city' => 'City',
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ShippingZoneLocationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShippingZones::route('/'),
            'create' => CreateShippingZone::route('/create'),
            'edit' => EditShippingZone::route('/{record}/edit'),
        ];
    }
}