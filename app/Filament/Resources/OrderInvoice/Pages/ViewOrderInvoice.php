<?php

namespace App\Filament\Resources\OrderInvoice\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderInvoice\OrderInvoiceResource;
use Modules\Order\Models\Order;

class ViewOrderInvoice extends ViewRecord
{
    protected static string $resource = OrderInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_invoice')
                ->label('Download Invoice')
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn (Order $record) => route('orders.invoice', ['order_number' => $record->order_number]))
                ->openUrlInNewTab(),
            Action::make('reorder')
                ->label('Simulate Reorder')
                ->icon('heroicon-m-arrow-path')
                ->action(function (Order $record) {
                    // Simulate reorder functionality
                    // In a real implementation, this would create a new order based on the current one
                    \Filament\Notifications\Notification::make()
                        ->title('Reorder simulated')
                        ->body('A new order would be created based on this order.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Order Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('order_number')
                                ->label('Order Number')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('customer.name')
                                ->label('Customer')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('total_amount')
                                ->label('Total Amount')
                                ->disabled()
                                ->prefix('$'),
                            \Filament\Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'confirmed' => 'Confirmed',
                                    'processing' => 'Processing',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->disabled(),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Payment Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Select::make('payment_method')
                                ->options([
                                    'cod' => 'Cash on Delivery',
                                    'card' => 'Credit Card',
                                    'paypal' => 'PayPal',
                                    'wallet' => 'Wallet',
                                ])
                                ->disabled(),
                            \Filament\Forms\Components\Select::make('payment_status')
                                ->options([
                                    'paid' => 'Paid',
                                    'pending' => 'Pending',
                                    'failed' => 'Failed',
                                    'refunded' => 'Refunded',
                                ])
                                ->disabled(),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Shipping Information')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('shipping_address')
                        ->content(fn (Order $record) => json_encode($record->shipping_address, JSON_PRETTY_PRINT)),
                ]),
            
            \Filament\Forms\Components\Section::make('Order Items')
                ->schema([
                    \Filament\Forms\Components\Repeater::make('items')
                        ->schema([
                            \Filament\Forms\Components\Grid::make(4)
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('product_name')
                                        ->label('Product')
                                        ->disabled(),
                                    \Filament\Forms\Components\TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->disabled(),
                                    \Filament\Forms\Components\TextInput::make('price')
                                        ->label('Price')
                                        ->prefix('$')
                                        ->disabled(),
                                    \Filament\Forms\Components\TextInput::make('total')
                                        ->label('Total')
                                        ->prefix('$')
                                        ->disabled(),
                                ]),
                        ])
                        ->disabled(),
                ]),
        ];
    }
}