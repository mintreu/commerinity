<?php

namespace App\Filament\Resources\Geo\Countries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultGroup('is_active')
            ->columns([
                ImageColumn::make('flag'),
                TextColumn::make('name'),
                TextColumn::make('iso_code_2'),
                IconColumn::make('is_active')->boolean()
            ])
            ->filters([
                TernaryFilter::make('is_active')
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
