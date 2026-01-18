<?php

namespace Modules\Product\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\ColorPicker;

use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Grid;
use Modules\Category\Models\Category;
use Modules\Vendor\Models\Vendor;

class ProductForm
{
    public static function getSchema(): array
    {
        return [
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
                            ->placeholder('Enter product description')
                            ->rows(4),
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
                            ->placeholder('Enter price')
                            ->prefix('$'),
                        TextInput::make('discounted_price')
                            ->numeric()
                            ->placeholder('Optional discounted price')
                            ->prefix('$'),
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
                    SpatieMediaLibraryFileUpload::make('main_image')
                        ->collection('main_image')
                        ->disk('public')
                        ->label('Main Image')
                        ->required()
                        ->image()
                        ->maxSize(2048)
                        ->imageCropAspectRatio('1:1'),
                    SpatieMediaLibraryFileUpload::make('images')
                        ->label('Additional Images')
                        ->collection('images')
                        ->multiple()
                        ->image()
                        ->disk('public')
                        ->enableReordering()
                        ->imageCropAspectRatio('1:1')
                        ->maxSize(2048),
                ]),
                Tab::make('Modesty Attributes')->schema([
                    Section::make('Fabric & Material')
                        ->description('Specify fabric and material properties')
                        ->schema([
                            TagsInput::make('fabric_types')
                                ->label('Fabric Types')
                                ->placeholder('Cotton, Polyester, etc.')
                                ->helperText('Enter fabric types separated by commas'),
                            Select::make('opacity_level')
                                ->label('Opacity Level')
                                ->options([
                                    'opaque' => 'Opaque',
                                    'semi_transparent' => 'Semi-Transparent',
                                    'transparent' => 'Transparent',
                                ])
                                ->helperText('How see-through is the fabric'),
                        ]),

                    Section::make('Coverage & Style')
                        ->description('Specify coverage and style attributes')
                        ->schema([
                            Select::make('sleeve_length')
                                ->label('Sleeve Length')
                                ->options([
                                    'short_sleeve' => 'Short Sleeve',
                                    'long_sleeve' => 'Long Sleeve',
                                    'three_quarter_sleeve' => '3/4 Sleeve',
                                    'sleeveless' => 'Sleeveless',
                                    'cap_sleeve' => 'Cap Sleeve',
                                ])
                                ->helperText('Choose sleeve length option'),

                            Select::make('hijab_style')
                                ->label('Hijab Style')
                                ->options([
                                    'khimar' => 'Khimar',
                                    'hijab' => 'Hijab',
                                    'niqab' => 'Niqab',
                                    'shayla' => 'Shayla',
                                    'other' => 'Other',
                                ])
                                ->helperText('Choose hijab style if applicable'),
                        ]),
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
        ];
    }
}
