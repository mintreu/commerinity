<?php

namespace App\Filament\Resources\Ecommerce\Products\RelationManagers; // MUST match file location! Do NOT put Products without Ecommerce!

// However, based on the error, you might actually have the file at TWO locations:
// 1. app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php (OLD - DELETE THIS)
// 2. app/Filament/Resources/Ecommerce/Products/RelationManagers/VariantsRelationManager.php (NEW - Keep this)

// If you DO have both files, delete the one in Products/ (without Ecommerce) as it's the old location.

use App\Casts\ProductStatusCast;

use App\Filament\Resources\Ecommerce\Products\Schemas\ProductForm;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('url')
                    ->label('URL Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

               // TextInput::make('price'),

                Select::make('status')
                    ->options(ProductForm::statusOptions())
                    ->default(ProductStatusCast::DRAFT->value)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label('URL Slug')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ProductStatusCast::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state): string => ProductStatusCast::tryFrom($state)?->getColor() ?? 'gray'),

               // TextInput::make('price'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProductForm::statusOptions()),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
