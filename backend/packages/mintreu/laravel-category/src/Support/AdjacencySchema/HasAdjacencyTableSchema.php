<?php

namespace Mintreu\LaravelCategory\Support\AdjacencySchema;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

trait HasAdjacencyTableSchema
{
    public function getAdjacencyTableColumns(): array
    {
        return [
            TextColumn::make('parent.name')->badge()
                ->placeholder('No Data')->searchable()->sortable(),
            TextColumn::make('name')
                ->searchable()->sortable(),
            TextColumn::make('url')
                ->searchable()->sortable(),
            IconColumn::make('status')
                ->boolean(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public function getAdjacencyTableActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
        ];
    }

    public function getAdjacencyTableFilters(): array
    {
        return [
            SelectFilter::make('Category')
                ->relationship('parent', 'name'),
            SelectFilter::make('status')
                ->options([true => 'True', false => 'False']),

            TernaryFilter::make('toggle_category_type')
                ->label('Category type')
                ->placeholder('All categories')
                ->trueLabel('Parent Categories Only')
                ->falseLabel('Subcategories Only')
                ->queries(
                    // parent only
                    true: fn (Builder $query) => $query->whereNull('parent_id'),
                    // children only
                    false: fn (Builder $query) => $query->whereNotNull('parent_id'),
                    blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                ),

        ];

    }
}
