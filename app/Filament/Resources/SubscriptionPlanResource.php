<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

/**
 * Subscription Plan Resource
 * 
 * Filament admin resource for managing subscription plans.
 */
class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Plan Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price_monthly')
                            ->label('Monthly Price (SAR)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('SAR')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('price_yearly')
                            ->label('Yearly Price (SAR)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('SAR')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('trial_days')
                            ->label('Trial Days')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Features')
                    ->description('Define plan limits and features')
                    ->schema([
                        Forms\Components\KeyValue::make('features')
                            ->label('Features & Limits')
                            ->keyLabel('Feature Key')
                            ->valueLabel('Value')
                            ->default([
                                'products_limit' => '50',
                                'storage_gb' => '2',
                                'users_limit' => '2',
                                'custom_domain' => 'false',
                                'analytics' => 'basic',
                                'support_level' => 'email',
                                'api_rate_limit' => '100',
                            ])
                            ->columnSpanFull()
                            ->reorderable()
                            ->deletable()
                            ->addable()
                            ->addActionLabel('Add Feature'),
                    ])
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Availability')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active & Available')
                            ->default(true)
                            ->helperText('Inactive plans cannot be selected by new tenants')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('price_monthly')
                    ->label('Monthly')
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_yearly')
                    ->label('Yearly')
                    ->money('SAR')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trial_days')
                    ->label('Trial')
                    ->suffix(' days')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenants_count')
                    ->label('Active Tenants')
                    ->counts('tenants')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Plans')
                    ->trueLabel('Only Active')
                    ->falseLabel('Only Inactive'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (SubscriptionPlan $record) => $record->tenants()->exists()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'view' => Pages\ViewSubscriptionPlan::route('/{record}'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
