<?php

namespace App\Filament\Resources\Membership\Levels\Tables;

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

class LevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('stage.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->searchable(),
                TextColumn::make('global_rank')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('team_member_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_direct_referrals')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_active_directs')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_personal_purchase')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_team_sales')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('validity_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('joining_bonus')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('purchase_commission')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('recruitment_commission')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('commission_multiplier')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('badge_icon')
                    ->searchable(),
                TextColumn::make('badge_color')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
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
