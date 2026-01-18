<?php

namespace App\Filament\Resources\VendorShippingMethod;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VendorShippingMethod\Pages\ListVendorShippingMethods;
use App\Filament\Resources\VendorShippingMethod\Pages\CreateVendorShippingMethod;
use App\Filament\Resources\VendorShippingMethod\Pages\EditVendorShippingMethod;
use App\Filament\Resources\VendorShippingMethod\RelationManagers\VendorShippingRatesRelationManager;
use App\Models\Shipping\VendorShippingMethod as VendorShippingMethodModel;

class VendorShippingMethodResource extends Resource
{
    protected static ?string $model = VendorShippingMethodModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

  public static function form(Schema $schema): Schema
{
    return $schema
        ->schema([
            \Filament\Forms\Components\Section::make('Shipping Method Information')
                    ->schema([
                        \Filament\Forms\Components\Select::make('vendor_id')
                            ->label('Vendor')
                            ->relationship('vendor', 'name')
                            ->required()
                            ->disabledOn('edit'),
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Method Name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\Toggle::make('is_cod')
                            ->label('Cash on Delivery')
                            ->inline(false),
                        \Filament\Forms\Components\TextInput::make('min_delivery_days')
                            ->label('Min Delivery Days')
                            ->numeric()
                            ->minValue(0),
                        \Filament\Forms\Components\TextInput::make('max_delivery_days')
                            ->label('Max Delivery Days')
                            ->numeric()
                            ->minValue(0),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->inline(false)
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Method Name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_cod')
                    ->label('COD')
                    ->boolean(),
                \Filament\Tables\Columns\TextColumn::make('min_delivery_days')
                    ->label('Min Days')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('max_delivery_days')
                    ->label('Max Days')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('is_cod')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ])
                    ->label('Cash on Delivery'),
                \Filament\Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        // Scope to vendor's shipping methods only if user is a vendor
        if (auth()->user()?->hasRole('vendor')) {
            return parent::getEloquentQuery()->where('vendor_id', auth()->user()->id);
        }
        
        return parent::getEloquentQuery();
    }

    public static function getRelations(): array
    {
        return [
            VendorShippingRatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendorShippingMethods::route('/'),
            'create' => CreateVendorShippingMethod::route('/create'),
            'edit' => EditVendorShippingMethod::route('/{record}/edit'),
        ];
    }
}