<?php

namespace App\Filament\Resources\Advertisements\Tables;

use App\Casts\AdPlacementCast;
use App\Casts\AdTypeCast;
use App\Casts\AdvertisementPageCast;
use App\Casts\AdvertisementPositionCast;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AdvertisementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('placement')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position_type')
                    ->badge()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('page_target')
                    ->badge()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('block')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('position')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_premium')
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('impressions')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('target_views')
                    ->numeric()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('clicks')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(AdTypeCast::class),
                SelectFilter::make('placement')
                    ->options(AdPlacementCast::class),
                SelectFilter::make('position_type')
                    ->options(AdvertisementPositionCast::class),
                SelectFilter::make('page_target')
                    ->options(AdvertisementPageCast::class),
                TernaryFilter::make('is_active'),
                TernaryFilter::make('is_premium'),
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
