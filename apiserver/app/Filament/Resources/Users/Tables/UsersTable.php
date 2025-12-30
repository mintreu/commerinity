<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->collection('avatar')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('mobile')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                IconColumn::make('onboarded')
                    ->boolean()
                    ->label('Onboarded')
                    ->toggleable(),
                TextColumn::make('referral_code')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),
                TextColumn::make('parent.name')
                    ->label('Referrer')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('gender')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->multiple()
                    ->options(\App\Casts\UserTypeCast::class),
                SelectFilter::make('status')
                    ->multiple()
                    ->options(\App\Casts\UserStatusCast::class),
                TernaryFilter::make('onboarded'),
                SelectFilter::make('gender')
                    ->options(\App\Casts\GenderCast::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
