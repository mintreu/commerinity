<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\Resources\ProductResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
                $dynamicColumns[] = Tables\Columns\TextColumn::make("filterOptions.{$filter->name}")
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ], $dynamicColumns))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Model $record) => ProductResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->url(fn (Model $record) => ProductResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function authorizeAccess(): void {}
}

