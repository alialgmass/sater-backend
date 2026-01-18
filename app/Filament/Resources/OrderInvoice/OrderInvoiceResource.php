<?php

namespace App\Filament\Resources\OrderInvoice;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderInvoice\Pages\ListOrderInvoices;
use App\Filament\Resources\OrderInvoice\Pages\ViewOrderInvoice;
use Modules\Order\Models\Order;

class OrderInvoiceResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'processing' => 'primary',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
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
                \Filament\Tables\Actions\Action::make('download_invoice')
                    ->label('Download Invoice')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn (Order $record) => route('orders.invoice', ['order_number' => $record->order_number])),
                \Filament\Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->url(fn (Order $record): string => static::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    // Add bulk actions if needed
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => ListOrderInvoices::route('/'),
            'view' => ViewOrderInvoice::route('/{record}'),
        ];
    }
}