<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\RelationManagers;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource;

class VariantsRelationManager  extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'name';

    public function getDynamicColumns(): array
    {
        $dynamicColumns = [];

        // Get the parent product's filter group
        $parentProduct = $this->getOwnerRecord();
        if ($parentProduct->filterGroup) {
            // Get all filters in the filter group
            $filters = $parentProduct->filterGroup->filters;

            foreach ($filters as $filter) {
                $dynamicColumns[] = TextColumn::make("filterOptions.{$filter->name}")
                    ->label($filter->name)
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (Model $record) use ($filter) {
                        $option = $record->filterOptions()
                            ->whereHas('filter', function ($query) use ($filter) {
                                $query->where('name', $filter->name);
                            })
                            ->first();

                        return $option ? $option->value : null;
                    });
            }
        }

        return $dynamicColumns;
    }

    public function table(Table $table): Table
    {
        $dynamicColumns = $this->getDynamicColumns();

        return $table
            ->columns(array_merge([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ], $dynamicColumns))
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Model $record) => ProductResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn (Model $record) => ProductResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function authorizeAccess(): void {}
}

