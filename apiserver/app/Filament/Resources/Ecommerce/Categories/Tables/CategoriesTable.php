<?php

namespace App\Filament\Resources\Ecommerce\Categories\Tables;

use App\Filament\Exports\Ecommerce\CategoryExporter;
use App\Filament\Imports\Ecommerce\CategoryImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultGroup(Group::make('parent.name')
                ->titlePrefixedWithLabel(false))
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->collapsedGroupsByDefault()

            ->columns([

                Split::make([
                    SpatieMediaLibraryImageColumn::make('thumbnail')
                        ->imageSize('150px')
                        ->defaultImageUrl('https://placehold.co/400')
                        ->collection('thumbnail'),

                    TextColumn::make('name')
                        ->size(fn(Model $record) => is_null($record->parent_id) ? TextSize::Large : TextSize::Medium)
                        ->color(fn(Model $record) => is_null($record->parent_id) ? 'primary' : 'info')
                        ->searchable(),
                ]),



                Panel::make([
                    Stack::make([

                        TextColumn::make('parent.name')
                            ->badge()
                            ->default('-root-')
                            ->description('Parent')
                            ->sortable(),

                        Split::make([
                            IconColumn::make('status')
                                ->tooltip('Status')
                                ->inline()
                                ->boolean(),
                            TextColumn::make('view_count')
                                ->numeric()
                                ->description('Views')
                                ->sortable(),
                            TextColumn::make('order')
                                ->numeric()
                                ->description('Priority')
                                ->sortable(),
                        ]),

                        Split::make([
                            TextColumn::make('created_at')
                                ->dateTime()
                                ->sortable()
                                ->description('Create On')
                                ->toggledHiddenByDefault()
                                ->toggleable(isToggledHiddenByDefault: true),
                            TextColumn::make('updated_at')
                                ->dateTime()
                                ->sortable()
                                ->description('Last Edited')
                                ->toggledHiddenByDefault()
                                ->toggleable(isToggledHiddenByDefault: true),
                        ])

                    ]),
                ])->collapsible(),



            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(CategoryImporter::class),
                ExportAction::make()
                    ->exporter(CategoryExporter::class)
                    ->enableVisibleTableColumnsByDefault()
                    ->columnMappingColumns(2),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(CategoryExporter::class),
                ]),
            ]);
    }
}
