<?php

namespace App\Filament\Resources\Returns\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Returns\ReturnRequestResource;
use App\Models\Returns\ReturnRequest as ReturnRequestModel;

class ViewReturnRequest extends ViewRecord
{
    protected static string $resource = ReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve Request')
                ->action(function (ReturnRequestModel $record) {
                    $record->update(['status' => 'approved']);
                })
                ->requiresConfirmation()
                ->color('success')
                ->visible(fn (ReturnRequestModel $record) => $record->status === 'pending'),
                
            Action::make('reject')
                ->label('Reject Request')
                ->action(function (ReturnRequestModel $record) {
                    $record->update(['status' => 'rejected']);
                })
                ->requiresConfirmation()
                ->color('danger')
                ->visible(fn (ReturnRequestModel $record) => $record->status === 'pending'),
                
            Action::make('initiate_refund')
                ->label('Initiate Refund')
                ->action(function (ReturnRequestModel $record) {
                    // Create a refund record based on the return request
                    $refund = \App\Models\Returns\Refund::create([
                        'return_request_id' => $record->id,
                        'customer_id' => $record->customer_id,
                        'vendor_id' => $record->vendor_id,
                        'order_id' => $record->orderItem->order_id,
                        'amount' => $record->refund_amount,
                        'currency' => 'USD',
                        'status' => 'pending',
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Refund Initiated')
                        ->body('A refund has been initiated for this return request.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->color('primary')
                ->visible(fn (ReturnRequestModel $record) => $record->status === 'approved' && !$record->returnRequest->refunds()->exists()),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Return Request Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('orderItem.product_name')
                                ->label('Product Name')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('customer.name')
                                ->label('Customer')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('vendor.name')
                                ->label('Vendor')
                                ->disabled(),
                            \Filament\Forms\Components\Select::make('reason')
                                ->options([
                                    'wrong_size' => 'Wrong Size',
                                    'defective_product' => 'Defective Product',
                                    'not_as_described' => 'Not as Described',
                                    'no_longer_needed' => 'No Longer Needed',
                                    'damaged_during_shipping' => 'Damaged During Shipping',
                                    'other' => 'Other',
                                ])
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('requested_quantity')
                                ->label('Requested Quantity')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('approved_quantity')
                                ->label('Approved Quantity')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('refund_amount')
                                ->label('Refund Amount')
                                ->prefix('$')
                                ->disabled(),
                            \Filament\Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->disabled(),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Description')
                ->schema([
                    \Filament\Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->disabled()
                        ->rows(3),
                ]),
            
            \Filament\Forms\Components\Section::make('Images')
                ->schema([
                    \Filament\Forms\Components\KeyValue::make('images')
                        ->label('Uploaded Images')
                        ->addable(false)
                        ->deletable(false)
                        ->columnSpanFull(),
                ])
                ->visible(fn ($get) => !empty($get('images'))),
            
            \Filament\Forms\Components\Section::make('Notes')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Textarea::make('vendor_notes')
                                ->label('Vendor Notes')
                                ->disabled()
                                ->rows(3),
                            \Filament\Forms\Components\Textarea::make('admin_notes')
                                ->label('Admin Notes')
                                ->disabled()
                                ->rows(3),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Timeline')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('timestamps')
                        ->content(fn (ReturnRequestModel $record) => collect([
                            'Created' => $record->created_at,
                            'Updated' => $record->updated_at,
                        ])
                        ->mapWithKeys(fn ($value, $key) => [$key => $value?->format('M j, Y g:i A')])
                        ->map(fn ($value, $key) => "$key: $value")
                        ->join("\n")),
                ]),
        ];
    }
}