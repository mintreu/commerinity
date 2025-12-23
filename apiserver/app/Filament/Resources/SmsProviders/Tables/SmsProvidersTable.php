<?php

namespace App\Filament\Resources\SmsProviders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SmsProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('driver')
                    ->searchable(),
                TextColumn::make('sender_id')
                    ->searchable(),
                TextColumn::make('entity_id')
                    ->searchable(),
                TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('per_sms_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_balance_threshold')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance_checked_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rate_valid_until')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_default')
                    ->boolean(),
                TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('supports_dlt')
                    ->boolean(),
                IconColumn::make('supports_otp')
                    ->boolean(),
                IconColumn::make('supports_promotional')
                    ->boolean(),
                IconColumn::make('supports_whatsapp')
                    ->boolean(),
                IconColumn::make('supports_voice_otp')
                    ->boolean(),
                TextColumn::make('total_sent')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_delivered')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_failed')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('success_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_success_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_failure_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_error')
                    ->searchable(),
                TextColumn::make('consecutive_failures')
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
