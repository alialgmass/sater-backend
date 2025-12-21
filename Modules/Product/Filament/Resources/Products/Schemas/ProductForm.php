<?php

namespace Modules\Product\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;


use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Modules\Category\Models\Category;
use Modules\Vendor\Models\Vendor;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Product Tabs')->tabs([
                Tab::make('General')->schema([
                    Grid::make()->schema([
                        TextInput::make('name')
                            ->required()
                            ->placeholder('Enter product name'),
                        TextInput::make('slug')
                            ->required()
                            ->placeholder('Auto-generated from name')
                            ->hint('Unique identifier for URL'),
                        TextInput::make('sku')
                            ->placeholder('Stock Keeping Unit'),
                        Textarea::make('description')
                            ->placeholder('Enter product description'),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required(),
                    ])->columns(2),
                ]),
                Tab::make('Pricing & Stock')->schema([
                    Grid::make()->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->placeholder('Enter price'),
                        TextInput::make('discounted_price')
                            ->numeric()
                            ->placeholder('Optional discounted price'),
                        TextInput::make('stock')
                            ->numeric()
                            ->placeholder('Available quantity in stock'),
                    ])->columns(3),
                ]),
                Tab::make('Relations')->schema([
                    Grid::make()->schema([
                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->options(Vendor::all()->pluck('name', 'id'))
                            ->required(),
                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->required(),
                    ])->columns(2),
                ]),
                Tab::make('Images')->schema([
                    SpatieMediaLibraryFileUpload::make('images')
                        ->label('Product Images')
                        ->collection('images')
                        ->multiple()
                        ->image()
                        ->disk('public')
                        ->enableReordering()
                        ->imageCropAspectRatio('1:1'),
                    SpatieMediaLibraryFileUpload::make('main_image')
                        ->collection('main_image')
                        ->disk('public')
                        ->label('Main Image')
                        ->required()
                        ->image(),
                ]),
                Tab::make('Attributes')->schema([
                    Repeater::make('attributes')
                        ->label('Product Attributes')
                        ->schema([
                            TextInput::make('key')
                                ->required()
                                ->label('Attribute Name'),
                            TextInput::make('value')
                                ->required()
                                ->label('Attribute Value'),
                        ])
                        ->columns(2),
                ]),
            ])->columnSpanFull(),
        ]);
    }
}
