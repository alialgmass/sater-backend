<?php

namespace Modules\Product\Filament\Resources\Products;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Product\Filament\Resources\Products\Pages\CreateProduct;
use Modules\Product\Filament\Resources\Products\Pages\EditProduct;
use Modules\Product\Filament\Resources\Products\Pages\ListProducts;
use Modules\Product\Filament\Resources\Products\Schemas\ProductForm;
use Modules\Product\Filament\Resources\Products\Tables\ProductsTable;
use Modules\Product\Models\Product;
use Filament\Pages\Page;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

   public static function form(Schema $schema): Schema
    {
        return $schema->schema(
            \Modules\Product\Filament\Resources\Products\Schemas\ProductForm::getSchema()
        );
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        // Scope to vendor's products if user is a vendor
        if (auth()->user()?->hasRole('vendor')) {
            return parent::getEloquentQuery()->where('vendor_id', auth()->user()->id);
        }

        return parent::getEloquentQuery();
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $query = parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        // Scope to vendor's products if user is a vendor
        if (auth()->user()?->hasRole('vendor')) {
            $query = $query->where('vendor_id', auth()->user()->id);
        }

        return $query;
    }
}
