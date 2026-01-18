<?php

namespace App\Filament\Resources\Shipment\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Shipment\ShipmentResource;
use App\Models\Shipping\Shipment as ShipmentModel;
use App\Services\Shipping\ShipmentCreator;
use App\Enums\Shipping\ShipmentStatus;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update_status')
                ->label('Update Status')
                ->form([
                    \Filament\Forms\Components\Select::make('new_status')
                        ->label('New Status')
                        ->options([
                            'pending' => 'Pending',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'failed' => 'Failed',
                        ])
                        ->required(),
                ])
                ->action(function (array $data, ShipmentModel $record) {
                    // Update status logic would go here
                    $record->update(['status' => $data['new_status']]);
                })
                ->visible(fn (ShipmentModel $record) => $record->status !== 'delivered' && $record->status !== 'failed'),
                
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
                ->action(function (array $data, ShipmentModel $record) {
                    // Add tracking info logic would go here
                    $shipmentCreator = app(ShipmentCreator::class);
                    $shipmentCreator->addTrackingInfo($record->id, $data['courier_name'], $data['tracking_number']);
                })
                ->visible(fn (ShipmentModel $record) => $record->status === 'shipped'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Shipment Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('order.order_number')
                                ->label('Order Number')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('method.name')
                                ->label('Shipping Method')
                                ->disabled(),
                            \Filament\Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'failed' => 'Failed',
                                ])
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('tracking_number')
                                ->label('Tracking Number')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('courier_name')
                                ->label('Courier Name')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('tracking_url')
                                ->label('Tracking URL')
                                ->disabled(),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Delivery Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\DatePicker::make('estimated_delivery_from')
                                ->label('Estimated Delivery From')
                                ->displayFormat('M j, Y')
                                ->disabled(),
                            \Filament\Forms\Components\DatePicker::make('estimated_delivery_to')
                                ->label('Estimated Delivery To')
                                ->displayFormat('M j, Y')
                                ->disabled(),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Order Items')
                ->schema([
                    \Filament\Forms\Components\Repeater::make('order.items')
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
                ]),
            
            \Filament\Forms\Components\Section::make('Delivery Attempts')
                ->schema([
                    \Filament\Forms\Components\Repeater::make('attempts')
                        ->schema([
                            \Filament\Forms\Components\Grid::make(3)
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('attempt_number')
                                        ->label('Attempt #')
                                        ->disabled(),
                                    \Filament\Forms\Components\Select::make('status')
                                        ->options([
                                            'success' => 'Success',
                                            'failed' => 'Failed',
                                        ])
                                        ->disabled(),
                                    \Filament\Forms\Components\Textarea::make('failure_reason')
                                        ->label('Failure Reason')
                                        ->disabled()
                                        ->visible(fn ($get) => $get('status') === 'failed'),
                                ]),
                        ])
                        ->disabled(),
                ])
                ->visible(fn (ShipmentModel $record) => $record->attempts->count() > 0),
            
            \Filament\Forms\Components\Section::make('Timeline')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('timestamps')
                        ->content(fn (ShipmentModel $record) => collect([
                            'Created' => $record->created_at,
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