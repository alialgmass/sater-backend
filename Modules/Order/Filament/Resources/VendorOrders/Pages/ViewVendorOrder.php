<?php

namespace Modules\Order\Filament\Resources\VendorOrders\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use Modules\Order\Filament\Resources\VendorOrders\VendorOrderResource;
use Modules\Order\Models\VendorOrder;
use App\Services\Shipping\ShipmentCreator;
use App\Enums\Shipping\ShipmentStatus;

class ViewVendorOrder extends ViewRecord
{
    protected static string $resource = VendorOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update_status')
                ->label('Update Status')
                ->form([
                    \Filament\Forms\Components\Select::make('new_status')
                        ->label('New Status')
                        ->options([
                            'confirmed' => 'Confirmed',
                            'processing' => 'Processing',
                            'packed' => 'Packed',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                        ])
                        ->required(),
                ])
                ->action(function (array $data, VendorOrder $record) {
                    // Update status logic would go here
                    $record->update(['status' => $data['new_status']]);
                })
                ->visible(fn (VendorOrder $record) => $record->status !== 'delivered' && $record->status !== 'cancelled'),
                
            Action::make('add_tracking_info')
                ->label('Add Tracking Info')
                ->form([
                    \Filament\Forms\Components\TextInput::make('courier_name')
                        ->label('Courier Name')
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('tracking_number')
                        ->label('Tracking Number')
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('tracking_url')
                        ->label('Tracking URL')
                        ->url(),
                ])
                ->action(function (array $data, VendorOrder $record) {
                    // Add tracking info logic would go here
                    $shipmentCreator = app(ShipmentCreator::class);
                    $shipmentCreator->addTrackingInfo($record->id, $data['courier_name'], $data['tracking_number']);
                })
                ->visible(fn (VendorOrder $record) => $record->status === 'shipped'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load related data for display
        $vendorOrder = $this->getRecord();
        
        $data['items'] = $vendorOrder->items->map(function ($item) {
            return [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->quantity * $item->price,
            ];
        })->toArray();
        
        $data['shipment'] = $vendorOrder->shipment ? [
            'courier_name' => $vendorOrder->shipment->courier_name,
            'tracking_number' => $vendorOrder->shipment->tracking_number,
            'tracking_url' => $vendorOrder->shipment->tracking_url,
        ] : null;
        
        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Order Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('vendor_order_number')
                                ->label('Order Number')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('masterOrder.order_number')
                                ->label('Master Order')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('total_amount')
                                ->label('Total Amount')
                                ->disabled()
                                ->prefix('$'),
                            \Filament\Forms\Components\Select::make('status')
                                ->options([
                                    'confirmed' => 'Confirmed',
                                    'processing' => 'Processing',
                                    'packed' => 'Packed',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->disabled(),
                            \Filament\Forms\Components\Toggle::make('is_cod')
                                ->label('Cash on Delivery')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('cod_amount')
                                ->label('COD Amount')
                                ->prefix('$')
                                ->disabled()
                                ->visible(fn ($get) => $get('is_cod')),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Customer Information')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('customer_name')
                        ->content(fn (VendorOrder $record) => $record->masterOrder->customer->name ?? 'N/A'),
                    \Filament\Forms\Components\Placeholder::make('customer_phone')
                        ->content(fn (VendorOrder $record) => $record->masterOrder->customer->phone ?? 'N/A'),
                ]),
            
            \Filament\Forms\Components\Section::make('Shipping Address')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('shipping_address')
                        ->content(fn (VendorOrder $record) => json_encode($record->shipping_address, JSON_PRETTY_PRINT)),
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
            
            \Filament\Forms\Components\Section::make('Shipment Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(3)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('shipment.courier_name')
                                ->label('Courier Name')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('shipment.tracking_number')
                                ->label('Tracking Number')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('shipment.tracking_url')
                                ->label('Tracking URL')
                                ->disabled(),
                        ])
                        ->visible(fn ($get) => $get('shipment')),
                ]),
            
            \Filament\Forms\Components\Section::make('Timeline')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('timestamps')
                        ->content(fn (VendorOrder $record) => collect([
                            'Created' => $record->created_at,
                            'Confirmed' => $record->confirmed_at,
                            'Processing Started' => $record->processing_started_at,
                            'Packed' => $record->packed_at,
                            'Shipped' => $record->shipped_at,
                            'Delivered' => $record->delivered_at,
                        ])
                        ->filter()
                        ->mapWithKeys(fn ($value, $key) => [$key => $value?->format('M j, Y g:i A')])
                        ->map(fn ($value, $key) => "$key: $value")
                        ->join("\n")),
                ]),
        ];
    }
}