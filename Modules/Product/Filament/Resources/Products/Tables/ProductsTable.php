<?php

namespace Modules\Product\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Modules\Category\Models\Category;
use Modules\Vendor\Models\Vendor;
use Modules\Product\Models\Product;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('main_image')
                ->collection('main_image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(asset('vendor/filament/images/product-placeholder.png')),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): string => str($record->description)->limit(50))
                    ->wrap(),
                TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . ' ⭐'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                // Category filter
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                // Vendor filter
                SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                // Price range filter
                Filter::make('price_range')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_price')
                            ->label('Min Price')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('max_price')
                            ->label('Max Price')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $value) => $query->where('price', '>=', $value)
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $value) => $query->where('price', '<=', $value)
                            );
                    }),

                // Rating filter
                SelectFilter::make('rating')
                    ->options([
                        5 => '5 Stars',
                        4 => '4 Stars & Up',
                        3 => '3 Stars & Up',
                        2 => '2 Stars & Up',
                        1 => '1 Star & Up',
                    ])
                    ->query(function (Builder $query, $data) {
                        if ($data['value'] === 5) {
                            return $query->where('rating', '>=', 4.5);
                        } elseif ($data['value'] === 4) {
                            return $query->where('rating', '>=', 3.5);
                        } elseif ($data['value'] === 3) {
                            return $query->where('rating', '>=', 2.5);
                        } elseif ($data['value'] === 2) {
                            return $query->where('rating', '>=', 1.5);
                        } elseif ($data['value'] === 1) {
                            return $query->where('rating', '>=', 0.5);
                        }

                        return $query;
                    }),

                // Status filter
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->searchable();
    }
}
