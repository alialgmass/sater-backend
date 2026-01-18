<?php

namespace Modules\Order\Filament\Resources\Orders\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use Modules\Order\Filament\Resources\Orders\OrderResource;
use Modules\Order\Models\Order;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm_order')
                ->label('Confirm Order')
                ->action(function (Order $record) {
                    // Confirm order logic would go here
                    // This would typically call a service to handle the confirmation
                    $record->update(['status' => 'confirmed']);
                })
                ->requiresConfirmation()
                ->visible(fn (Order $record) => $record->status === 'pending'),
            
            Action::make('cancel_order')
                ->label('Cancel Order')
                ->color('danger')
                ->action(function (Order $record) {
                    // Cancel order logic would go here
                    $record->update(['status' => 'cancelled']);
                })
                ->requiresConfirmation()
                ->visible(fn (Order $record) => in_array($record->status, ['pending', 'confirmed', 'processing'])),
                
            EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load related data for display
        $order = $this->getRecord();
        
        $data['vendor_orders'] = $order->vendorOrders->map(function ($vendorOrder) {
            return [
                'vendor_name' => $vendorOrder->vendor->name ?? 'Unknown Vendor',
                'status' => $vendorOrder->status->value,
                'total_amount' => $vendorOrder->total_amount,
                'items' => $vendorOrder->items->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                })->toArray(),
            ];
        })->toArray();
        
        return $data;
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
            
            \Filament\Forms\Components\Section::make('Vendor Orders')
                ->schema([
                    \Filament\Forms\Components\Repeater::make('vendor_orders')
                        ->schema([
                            \Filament\Forms\Components\Grid::make(2)
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('vendor_name')
                                        ->label('Vendor')
                                        ->disabled(),
                                    \Filament\Forms\Components\Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'pending' => 'Pending',
                                            'confirmed' => 'Confirmed',
                                            'processing' => 'Processing',
                                            'shipped' => 'Shipped',
                                            'delivered' => 'Delivered',
                                            'cancelled' => 'Cancelled',
                                        ])
                                        ->disabled(),
                                    \Filament\Forms\Components\TextInput::make('total_amount')
                                        ->label('Total Amount')
                                        ->prefix('$')
                                        ->disabled(),
                                ]),
                            \Filament\Forms\Components\Repeater::make('items')
                                ->schema([
                                    \Filament\Forms\Components\Grid::make(3)
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
                                        ]),
                                ])
                                ->disabled(),
                        ])
                        ->disabled(),
                ]),
        ];
    }
}