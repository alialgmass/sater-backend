<?php

namespace App\Filament\Resources\Payment\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Payment\PaymentResource;
use App\Models\Payment\Payment as PaymentModel;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('initiate_refund')
                ->label('Initiate Refund')
                ->action(function (PaymentModel $record) {
                    // Refund logic would go here
                    $record->update(['status' => 'refunded']);
                })
                ->requiresConfirmation()
                ->color('danger')
                ->visible(fn (PaymentModel $record) => $record->status === 'completed'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Payment Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('transaction_id')
                                ->label('Transaction ID')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('reference_id')
                                ->label('Reference ID')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('amount')
                                ->label('Amount')
                                ->disabled()
                                ->prefix('$'),
                            \Filament\Forms\Components\Select::make('method')
                                ->options([
                                    'cod' => 'Cash on Delivery',
                                    'card' => 'Credit Card',
                                    'paypal' => 'PayPal',
                                    'stripe' => 'Stripe',
                                    'bank_transfer' => 'Bank Transfer',
                                    'wallet' => 'Wallet',
                                ])
                                ->disabled(),
                            \Filament\Forms\Components\Select::make('status')
                                ->options([
                                    'completed' => 'Completed',
                                    'pending' => 'Pending',
                                    'failed' => 'Failed',
                                    'refunded' => 'Refunded',
                                    'cancelled' => 'Cancelled',
                                    'expired' => 'Expired',
                                ])
                                ->disabled(),
                            \Filament\Forms\Components\Select::make('gateway')
                                ->options([
                                    'paypal' => 'PayPal',
                                    'stripe' => 'Stripe',
                                    'razorpay' => 'Razorpay',
                                    'manual' => 'Manual',
                                ])
                                ->disabled(),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Order Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('masterOrder.order_number')
                                ->label('Master Order Number')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('vendorOrder.vendor_order_number')
                                ->label('Vendor Order Number')
                                ->disabled(),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Customer Information')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('customer_name')
                        ->content(fn (PaymentModel $record) => $record->customer->name ?? 'N/A'),
                    \Filament\Forms\Components\Placeholder::make('customer_email')
                        ->content(fn (PaymentModel $record) => $record->customer->email ?? 'N/A'),
                ]),
            
            \Filament\Forms\Components\Section::make('Payment Details')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('gateway_response')
                        ->content(fn (PaymentModel $record) => $record->gateway_response ? json_encode($record->gateway_response, JSON_PRETTY_PRINT) : 'N/A'),
                    \Filament\Forms\Components\Placeholder::make('failure_reason')
                        ->content(fn (PaymentModel $record) => $record->failure_reason ?? 'N/A')
                        ->visible(fn (PaymentModel $record) => $record->status === 'failed'),
                ]),
            
            \Filament\Forms\Components\Section::make('Timeline')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('timestamps')
                        ->content(fn (PaymentModel $record) => collect([
                            'Created' => $record->created_at,
                            'Paid' => $record->paid_at,
                        ])
                        ->filter()
                        ->mapWithKeys(fn ($value, $key) => [$key => $value?->format('M j, Y g:i A')])
                        ->map(fn ($value, $key) => "$key: $value")
                        ->join("\n")),
                ]),
        ];
    }
}