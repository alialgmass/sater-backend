<?php

namespace Modules\Category\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               TextInput::make('name')
                    ->required()
                    ->maxLength(255),
               TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
               Select::make('parent_id')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->nullable(),
            ]);
    }
}
