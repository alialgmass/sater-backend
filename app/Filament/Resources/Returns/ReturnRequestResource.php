<?php

namespace App\Filament\Resources\Returns;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Returns\Pages\ListReturnRequests;
use App\Filament\Resources\Returns\Pages\ViewReturnRequest;
use App\Models\Returns\ReturnRequest as ReturnRequestModel;

class ReturnRequestResource extends Resource
{
    protected static ?string $model = ReturnRequestModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

   public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
              \Filament\Forms\Components\Section::make('Return Request Information')
                    ->schema([
                        \Filament\Forms\Components\Select::make('order_item_id')
                            ->label('Order Item')
                            ->relationship('orderItem', 'product_name')
                            ->required(),
                        \Filament\Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->required(),
                        \Filament\Forms\Components\Select::make('vendor_id')
                            ->label('Vendor')
                            ->relationship('vendor', 'name')
                            ->required(),
                        \Filament\Forms\Components\Select::make('reason')
                            ->options([
                                'wrong_size' => 'Wrong Size',
                                'defective_product' => 'Defective Product',
                                'not_as_described' => 'Not as Described',
                                'no_longer_needed' => 'No Longer Needed',
                                'damaged_during_shipping' => 'Damaged During Shipping',
                                'other' => 'Other',
                            ])
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                        \Filament\Forms\Components\TextInput::make('requested_quantity')
                            ->label('Requested Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        \Filament\Forms\Components\FileUpload::make('images')
                            ->label('Images')
                            ->multiple()
                            ->image()
                            ->maxFiles(5)
                            ->directory('return-requests'),
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('approved_quantity')
                            ->label('Approved Quantity')
                            ->numeric()
                            ->minValue(0),
                        \Filament\Forms\Components\TextInput::make('refund_amount')
                            ->label('Refund Amount')
                            ->numeric()
                            ->prefix('$'),
                        \Filament\Forms\Components\Textarea::make('vendor_notes')
                            ->label('Vendor Notes')
                            ->rows(2),
                        \Filament\Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('orderItem.product_name')
                    ->label('Product')
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
                \Filament\Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color('primary'),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                \Filament\Tables\Columns\TextColumn::make('requested_quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('refund_amount')
                    ->label('Refund')
                    ->money('USD')
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
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('reason')
                    ->options([
                        'wrong_size' => 'Wrong Size',
                        'defective_product' => 'Defective Product',
                        'not_as_described' => 'Not as Described',
                        'no_longer_needed' => 'No Longer Needed',
                        'damaged_during_shipping' => 'Damaged During Shipping',
                        'other' => 'Other',
                    ]),
                \Filament\Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from'),
                        \Filament\Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    // Add bulk actions if needed
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        // Scope to vendor's return requests only if user is a vendor
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
            'index' => ListReturnRequests::route('/'),
            'view' => ViewReturnRequest::route('/{record}'),
        ];
    }
}