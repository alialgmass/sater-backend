<?php

namespace Modules\Order\Filament\Resources\VendorOrders;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Order\Filament\Resources\VendorOrders\Pages\ListVendorOrders;
use Modules\Order\Filament\Resources\VendorOrders\Pages\ViewVendorOrder;
use Modules\Order\Models\VendorOrder;

class VendorOrderResource extends Resource
{
    protected static ?string $model = VendorOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('vendor_order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('masterOrder.order_number')
                    ->label('Master Order')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'info',
                        'processing' => 'primary',
                        'packed' => 'warning',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                \Filament\Tables\Columns\TextColumn::make('is_cod')
                    ->label('COD')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'packed' => 'Packed',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
                \Filament\Tables\Filters\Filter::make('is_cod')
                    ->label('Cash on Delivery')
                    ->query(fn (Builder $query) => $query->where('is_cod', true)),
                \Filament\Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from'),
                        \Filament\Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->url(fn (VendorOrder $record): string => static::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    // Add bulk actions for vendor orders if needed
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        // Scope to vendor's orders only
        return parent::getEloquentQuery()->where('vendor_id', auth()->user()->id);
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
            'index' => ListVendorOrders::route('/'),
            'view' => ViewVendorOrder::route('/{record}'),
        ];
    }
}