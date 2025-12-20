<?php

namespace Modules\Vendor\Filament\Resources\Vendors\Schemas;

use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Modules\Vendor\Enums\VendorStatus;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),

            TextInput::make('phone')
                ->label('Phone')
                ->required()
                ->maxLength(20),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                ->dehydrated(fn($state) => filled($state))
                ->maxLength(255),

            TextInput::make('shop_name')
                ->label('Shop Name')
                ->required()
                ->maxLength(255),

            TextInput::make('shop_slug')
                ->label('Shop Slug')
                ->maxLength(255),

            TextInput::make('whatsapp')
                ->label('WhatsApp')
                ->maxLength(20),

            Textarea::make('description')
                ->label('Description')
                ->columnSpanFull(),

            SpatieMediaLibraryFileUpload::make('logo')
                ->label('Logo')
                ->collection('logo')
                ->image()
                ->maxSize(2048),

            SpatieMediaLibraryFileUpload::make('cover')
                ->label('Cover')
                ->collection('cover')
                ->image()
                ->maxSize(4096),

            Select::make('status')
                ->label('Status')
                ->options(VendorStatus::options())
                ->default(VendorStatus::PENDING->value)
                ->required(),
        ]);
    }
}
