<?php

namespace Modules\Vendor\Filament\Resources\Vendors\Schemas;

use Filament\Forms;
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
                ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                ->dehydrated(fn ($state) => filled($state))
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

            FileUpload::make('logo')
                ->label('Logo')
                ->image()
                ->directory('vendors/logos')
                ->maxSize(2048),

            FileUpload::make('cover')
                ->label('Cover')
                ->image()
                ->directory('vendors/covers')
                ->maxSize(4096),

            Select::make('status')
                ->label('Status')
                ->options(VendorStatus::options())
                ->default(VendorStatus::PENDING->value)
                ->required(),
        ]);
    }
}
