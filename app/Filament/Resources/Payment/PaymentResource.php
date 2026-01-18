<?php

namespace App\Filament\Resources\Payment;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Payment\Pages\ListPayments;
use App\Filament\Resources\Payment\Pages\ViewPayment;
use App\Models\Payment\Payment as PaymentModel;

class PaymentResource extends Resource
{
    protected static ?string $model = PaymentModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('masterOrder.order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state->value))),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'secondary',
                        'cancelled' => 'gray',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                \Filament\Tables\Columns\TextColumn::make('gateway')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state->value))),
                \Filament\Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('method')
                    ->options([
                        'cod' => 'Cash on Delivery',
                        'card' => 'Credit Card',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'bank_transfer' => 'Bank Transfer',
                        'wallet' => 'Wallet',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('gateway')
                    ->options([
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'razorpay' => 'Razorpay',
                        'manual' => 'Manual',
                    ]),
                \Filament\Tables\Filters\Filter::make('paid_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('paid_from'),
                        \Filament\Forms\Components\DatePicker::make('paid_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['paid_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_at', '>=', $date),
                            )
                            ->when(
                                $data['paid_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->url(fn (PaymentModel $record): string => static::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
                \Filament\Tables\Actions\Action::make('refund')
                    ->label('Initiate Refund')
                    ->action(function (PaymentModel $record) {
                        // Refund logic would go here
                        $record->update(['status' => 'refunded']);
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->visible(fn (PaymentModel $record) => $record->status === 'completed'),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    // Add bulk actions if needed
                ]),
            ])
            ->defaultSort('paid_at', 'desc');
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
            'index' => ListPayments::route('/'),
            'view' => ViewPayment::route('/{record}'),
        ];
    }
}