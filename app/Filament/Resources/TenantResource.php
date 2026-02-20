<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

/**
 * Tenant Resource
 * 
 * Filament admin resource for managing tenants.
 */
class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'store_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Store Information')
                    ->schema([
                        Forms\Components\TextInput::make('store_name')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('language')
                            ->options([
                                'en' => 'English',
                                'ar' => 'Arabic',
                            ])
                            ->default('en')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Subscription')
                    ->schema([
                        Forms\Components\Select::make('current_plan_id')
                            ->label('Subscription Plan')
                            ->options(
                                SubscriptionPlan::active()
                                    ->ordered()
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('subdomain')
                            ->label('Subdomain')
                            ->disabled()
                            ->suffix('.sater.com')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Status & Suspension')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending_email_verification' => 'Pending Email Verification',
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                                'cancelled' => 'Cancelled',
                                'deleted' => 'Deleted',
                            ])
                            ->required()
                            ->default('active')
                            ->live()
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('suspension_reason')
                            ->label('Suspension Reason')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'suspended')
                            ->required(fn (Forms\Get $get) => $get('status') === 'suspended')
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('deletion_scheduled_at')
                            ->label('Deletion Scheduled At')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['cancelled', 'deleted']))
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store_name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('domains.first.domain')
                    ->label('Domain')
                    ->searchable()
                    ->limit(20),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending_email_verification',
                        'success' => 'active',
                        'danger' => 'suspended',
                        'gray' => 'cancelled',
                        'dark' => 'deleted',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('currentPlan.name')
                    ->label('Plan')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subscriptions.count')
                    ->label('Subscriptions')
                    ->counts('subscriptions')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_email_verification' => 'Pending Email Verification',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'cancelled' => 'Cancelled',
                        'deleted' => 'Deleted',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('current_plan_id')
                    ->label('Subscription Plan')
                    ->options(
                        SubscriptionPlan::query()->pluck('name', 'id')
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Suspend Tenant')
                    ->modalDescription('Are you sure you want to suspend this tenant? Their store will become inaccessible.')
                    ->form([
                        Forms\Components\Textarea::make('suspension_reason')
                            ->required()
                            ->label('Reason for Suspension')
                            ->placeholder('Explain why this tenant is being suspended...'),
                    ])
                    ->action(function (Tenant $record, array $data): void {
                        $record->update([
                            'status' => 'suspended',
                            'suspension_reason' => $data['suspension_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Tenant Suspended')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Tenant $record) => $record->status === 'active'),
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Activate Tenant')
                    ->modalDescription('Are you sure you want to activate this tenant? Their store will become accessible.')
                    ->action(function (Tenant $record): void {
                        $record->update([
                            'status' => 'active',
                            'suspension_reason' => null,
                        ]);
                        
                        Notification::make()
                            ->title('Tenant Activated')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Tenant $record) => $record->status === 'suspended'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('suspend')
                        ->label('Suspend Selected')
                        ->icon('heroicon-o-pause-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Suspend Tenants')
                        ->modalDescription('Are you sure you want to suspend the selected tenants?')
                        ->form([
                            Forms\Components\Textarea::make('suspension_reason')
                                ->required()
                                ->label('Reason for Suspension'),
                        ])
                        ->action(function ($records, array $data): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'suspended',
                                    'suspension_reason' => $data['suspension_reason'],
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Tenants Suspended')
                                ->success()
                                ->body(count($records) . ' tenants have been suspended.')
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-play-circle')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'active',
                                    'suspension_reason' => null,
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Tenants Activated')
                                ->success()
                                ->body(count($records) . ' tenants have been activated.')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTenants::route('/'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Tenants are created via registration flow, not admin
    }
}
