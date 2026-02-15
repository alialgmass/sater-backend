<?php

namespace Modules\Product\Filament\Resources\Sizes;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use BackedEnum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Product\Models\Size;
use Modules\Product\Filament\Resources\Sizes\Pages\ManageSizes;

class SizeResource extends Resource
{
    protected static ?string $model = Size::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-pointing-out';
    protected static string|\UnitEnum|null $navigationGroup = 'Product Management';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('abbreviation')
                    ->maxLength(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('abbreviation')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSizes::route('/'),
        ];
    }
}
