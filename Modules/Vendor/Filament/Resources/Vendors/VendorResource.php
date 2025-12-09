<?php

namespace Modules\Vendor\Filament\Resources\Vendors;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Vendor\Filament\Resources\Vendors\Pages\CreateVendor;
use Modules\Vendor\Filament\Resources\Vendors\Pages\EditVendor;
use Modules\Vendor\Filament\Resources\Vendors\Pages\ListVendors;
use Modules\Vendor\Filament\Resources\Vendors\Schemas\VendorForm;
use Modules\Vendor\Filament\Resources\Vendors\Tables\VendorsTable;
use Modules\Vendor\Models\Vendor;

class VendorResource extends Resource
{

    protected static ?string $model = Vendor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return VendorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendors::route('/'),
            'create' => CreateVendor::route('/create'),
            'edit' => EditVendor::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
