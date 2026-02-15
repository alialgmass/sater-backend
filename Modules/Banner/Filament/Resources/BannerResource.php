<?php

namespace Modules\Banner\Filament\Resources;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Components\Tabs;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;
use BackedEnum;
use Modules\Banner\Enums\BannerStatusEnum;
use Modules\Banner\Filament\Resources\BannerResource\Pages;
use Modules\Banner\Models\Banner;
use Modules\Product\Models\Product;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Translations')
                    ->schema([
                        Tabs::make('Languages')
                            ->tabs([
                                Tabs\Tab::make('English')
                                    ->schema([
                                        TextInput::make('title.en')
                                            ->label('Title (EN)')
                                            ->required()
                                            ->placeholder('Enter banner title in English'),
                                        RichEditor::make('description.en')
                                            ->label('Description (EN)')
                                            ->placeholder('Enter banner description in English'),
                                    ]),
                                Tabs\Tab::make('Arabic')
                                    ->schema([
                                        TextInput::make('title.ar')
                                            ->label('Title (AR)')
                                            ->required()
                                            ->placeholder('Enter banner title in Arabic'),
                                        RichEditor::make('description.ar')
                                            ->label('Description (AR)')
                                            ->placeholder('Enter banner description in Arabic'),
                                    ]),
                            ]),
                    ]),

                Section::make('General Information')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->collection('banners')
                            ->image()
                            ->required()
                            ->columnSpanFull(),
                        
                        Select::make('status')
                            ->options(BannerStatusEnum::class)
                            ->required()
                            ->default(BannerStatusEnum::ACTIVE),
                        
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),

                        DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->native(false),

                        DateTimePicker::make('ends_at')
                            ->label('Ends At')
                            ->native(false),

                        Select::make('products')
                            ->relationship('products', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('banners')
                    ->circular(),
                
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (! is_array($state)) {
                            return $state;
                        }

                        return $state[app()->getLocale()] ?? array_values($state)[0] ?? '';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                TextColumn::make('sort_order')
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->dateTime()
                    ->label('Starts'),

                TextColumn::make('ends_at')
                    ->dateTime()
                    ->label('Ends'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(BannerStatusEnum::class),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
