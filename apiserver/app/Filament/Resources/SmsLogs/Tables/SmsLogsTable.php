<?php

namespace App\Filament\Resources\SmsLogs\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SmsLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('integration.name')
                    ->label('Integration')
                    ->searchable(),
                TextColumn::make('provider_slug')
                    ->searchable(),
                TextColumn::make('recipient')
                    ->searchable(),
                TextColumn::make('message_type')
                    ->searchable(),
                TextColumn::make('sms_template_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('template_code')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('loggable_type')
                    ->searchable(),
                TextColumn::make('loggable_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('request_id')
                    ->searchable(),
                TextColumn::make('message_id')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('delivery_status')
                    ->searchable(),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('failed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('segments')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('error_code')
                    ->searchable(),
                TextColumn::make('retry_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_retries')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->searchable(),
                TextColumn::make('user_agent')
                    ->searchable(),
                TextColumn::make('source')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at_range')
                    ->label('Created Date')
                    ->form([
                        DatePicker::make('from_date')->label('From Date'),
                        DatePicker::make('to_date')->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['from_date'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $data['from_date'])
                            )
                            ->when(
                                filled($data['to_date'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $data['to_date'])
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
