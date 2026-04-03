<?php

namespace App\Filament\Resources\Membership\UserSubscriptions\Tables;

use App\Filament\Exports\Membership\UserSubscriptionExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stage_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_level_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_achieved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('highest_level_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('personal_pv')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('team_pv')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_commission_earned')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_month_commission')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_renewed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('renewal_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('base_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->boolean(),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('transaction_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('wallet_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('previous_subscription_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('originator_type')
                    ->searchable(),
                TextColumn::make('originator_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(UserSubscriptionExporter::class)
                    ->enableVisibleTableColumnsByDefault()
                    ->columnMappingColumns(3),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(UserSubscriptionExporter::class),
                ]),
            ]);
    }
}
