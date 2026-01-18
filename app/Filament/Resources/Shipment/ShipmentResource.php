<?php

namespace App\Filament\Resources\Shipment;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Shipment\Pages\ListShipments;
use App\Filament\Resources\Shipment\Pages\ViewShipment;
use App\Models\Shipping\Shipment as ShipmentModel;

class ShipmentResource extends Resource
{
    protected static ?string $model = ShipmentModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('method.name')
                    ->label('Shipping Method')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                \Filament\Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Tracking Number')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('courier_name')
                    ->label('Courier')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('estimated_delivery_from')
                    ->label('Est. Delivery')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                    ]),
                \Filament\Tables\Filters\Filter::make('is_cod')
                    ->label('Cash on Delivery')
                    ->query(fn (Builder $query) => $query->whereHas('method', fn ($q) => $q->where('is_cod', true))),
                \Filament\Tables\Filters\Filter::make('delivery_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('delivery_from'),
                        \Filament\Forms\Components\DatePicker::make('delivery_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['delivery_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('estimated_delivery_from', '>=', $date),
                            )
                            ->when(
                                $data['delivery_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('estimated_delivery_to', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->url(fn (ShipmentModel $record): string => static::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\Action::make('mark_shipped')
                        ->label('Mark as Shipped')
                        ->action(function (array $records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'shipped']);
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->hasRole('vendor')),
                    \Filament\Tables\Actions\Action::make('print_packing_slips')
                        ->label('Print Packing Slips')
                        ->action(function (array $records) {
                            // Print packing slips logic would go here
                            \Filament\Notifications\Notification::make()
                                ->title('Packing Slips')
                                ->body('Packing slips would be generated for selected shipments.')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->hasRole('vendor')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        // Scope to vendor's shipments only if user is a vendor
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
            'index' => ListShipments::route('/'),
            'view' => ViewShipment::route('/{record}'),
        ];
    }
}