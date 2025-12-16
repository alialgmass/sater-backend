<?php

namespace Modules\Product\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Modules\Category\Models\Category;
use Modules\Vendor\Models\Vendor;


class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('slug')->required(),
                    TextInput::make('sku'),
                    TextInput::make('price')->numeric()->required(),
                    TextInput::make('discounted_price')->numeric(),
                    TextInput::make('stock')->numeric(),
                    Textarea::make('description'),
                    Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ]),
                    Select::make('vendor_id')
                        ->label('Vendor')
                        ->options(Vendor::all()->pluck('name','id'))
                        ->required(),
                    Select::make('category_id')
                        ->label('Category')
                        ->options(Category::all()->pluck('name','id'))
                        ->required(),
                    FileUpload::make('images')
                        ->label('Product Images')
                        ->multiple()
                        ->image()
                        ->enableReordering()
                        ->imageCropAspectRatio('1:1'),
                    Select::make('main_image')
                        ->label('Main Image')
                        ->options(function ($get) {
                            $images = $get('images') ?? [];
                            return collect($images)->mapWithKeys(fn($img) => [$img['id'] => $img['name'] ?? 'Image'])->toArray();
                        }),
                    Repeater::make('attributes')
                        ->label('Product Attributes')
                        ->schema([
                            TextInput::make('key')->required()->label('Attribute Name'),
                            TextInput::make('value')->required()->label('Attribute Value'),
                        ])
                        ->columns(2),
                ])->columns(1),
            ]);
    }
}
