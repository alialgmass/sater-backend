<?php

namespace App\Filament\Resources\Returns\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Returns\RefundResource;
use App\Models\Returns\Refund as RefundModel;

class ViewRefund extends ViewRecord
{
    protected static string $resource = RefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('process_refund')
                ->label('Process Refund')
                ->action(function (RefundModel $record) {
                    $record->update([
                        'status' => 'completed',
                        'processed_by' => auth()->id(),
                        'processed_at' => now(),
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Refund Processed')
                        ->body('The refund has been successfully processed.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->color('success')
                ->visible(fn (RefundModel $record) => $record->status === 'pending'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Refund Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('returnRequest.id')
                                ->label('Return Request ID')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('customer.name')
                                ->label('Customer')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('vendor.name')
                                ->label('Vendor')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('amount')
                                ->label('Amount')
                                ->prefix('$')
                                ->disabled(),
                            \Filament\Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'completed' => 'Completed',
                                    'failed' => 'Failed',
                                ])
                                ->disabled(),
                            \Filament\Forms\Components\Select::make('gateway')
                                ->options([
                                    'paypal' => 'PayPal',
                                    'stripe' => 'Stripe',
                                    'bank_transfer' => 'Bank Transfer',
                                    'manual' => 'Manual',
                                ])
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('transaction_id')
                                ->label('Transaction ID')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('reference_id')
                                ->label('Reference ID')
                                ->disabled(),
                        ]),
                ]),
            
            \Filament\Forms\Components\Section::make('Return Request Details')
                ->schema([
                    \Filament\Forms\Components\Textarea::make('returnRequest.description')
                        ->label('Return Description')
                        ->disabled()
                        ->rows(3),
                    \Filament\Forms\Components\KeyValue::make('returnRequest.images')
                        ->label('Images')
                        ->addable(false)
                        ->deletable(false),
                ]),
            
            \Filament\Forms\Components\Section::make('Processing Information')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('processedBy.name')
                                ->label('Processed By')
                                ->disabled(),
                            \Filament\Forms\Components\TextInput::make('processed_at')
                                ->label('Processed At')
                                ->disabled(),
                        ]),
                ])
                ->visible(fn ($get) => $get('processed_at')),
            
            \Filament\Forms\Components\Section::make('Failure Information')
                ->schema([
                    \Filament\Forms\Components\Textarea::make('failure_reason')
                        ->label('Failure Reason')
                        ->disabled()
                        ->rows(3),
                ])
                ->visible(fn ($get) => $get('status') === 'failed'),
            
            \Filament\Forms\Components\Section::make('Gateway Response')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('gateway_response')
                        ->content(fn (RefundModel $record) => $record->gateway_response ? json_encode($record->gateway_response, JSON_PRETTY_PRINT) : 'N/A'),
                ])
                ->visible(fn (RefundModel $record) => $record->gateway_response),
        ];
    }
}