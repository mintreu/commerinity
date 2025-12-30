<?php

namespace App\Filament\Resources\Affiliate\AffiliateGenealogies\Tables;

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

class AffiliateGenealogiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('placementParent.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('placement_position')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('depth')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('direct_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('active_direct_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_1_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_2_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_3_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_4_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_team_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('active_team_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('personal_sales')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_1_sales')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_2_sales')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_3_sales')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level_4_sales')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_team_sales')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('personal_pv')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('team_pv')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currentStage.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currentLevel.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('highestLevel.name')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('activated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_activity_at')
                    ->dateTime()
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
