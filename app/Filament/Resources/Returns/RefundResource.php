<?php

namespace App\Filament\Resources\Returns;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Returns\Pages\ListRefunds;
use App\Filament\Resources\Returns\Pages\ViewRefund;
use App\Models\Returns\Refund as RefundModel;

class RefundResource extends Resource
{
    protected static ?string $model = RefundModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
               \Filament\Forms\Components\Section::make('Refund Information')
                    ->schema([
                        \Filament\Forms\Components\Select::make('return_request_id')
                            ->label('Return Request')
                            ->relationship('returnRequest', 'id')
                            ->required(),
                        \Filament\Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->required(),
                        \Filament\Forms\Components\Select::make('vendor_id')
                            ->label('Vendor')
                            ->relationship('vendor', 'name')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required(),
                        \Filament\Forms\Components\Select::make('gateway')
                            ->options([
                                'paypal' => 'PayPal',
                                'stripe' => 'Stripe',
                                'bank_transfer' => 'Bank Transfer',
                                'manual' => 'Manual',
                            ])
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID'),
                        \Filament\Forms\Components\TextInput::make('reference_id')
                            ->label('Reference ID'),
                        \Filament\Forms\Components\Textarea::make('failure_reason')
                            ->label('Failure Reason')
                            ->hidden(fn ($get) => $get('status') !== 'failed'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('returnRequest.id')
                    ->label('Return Request')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                \Filament\Tables\Columns\TextColumn::make('gateway')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                \Filament\Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('processed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('gateway')
                    ->options([
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'bank_transfer' => 'Bank Transfer',
                        'manual' => 'Manual',
                    ]),
                \Filament\Tables\Filters\Filter::make('processed_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('processed_from'),
                        \Filament\Forms\Components\DatePicker::make('processed_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['processed_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('processed_at', '>=', $date),
                            )
                            ->when(
                                $data['processed_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('processed_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    // Add bulk actions if needed
                ]),
            ])
            ->defaultSort('processed_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        // Scope to vendor's refunds only if user is a vendor
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
            'index' => ListRefunds::route('/'),
            'view' => ViewRefund::route('/{record}'),
        ];
    }
}