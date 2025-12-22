<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources;

use App\Filament\Resources\ProductTierResource\Pages;
use App\Filament\Resources\ProductTierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Schemas\ProductTierFormSchema;
use Mintreu\LaravelProductCatalogue\Models\ProductTier;

class ProductTierResource extends Resource
{
    protected static ?string $model = ProductTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catalogue';
    protected static ?string $navigationLabel = 'Purchase Ledger';
    protected static ?string $pluralModelLabel = 'Purchase Ledger';
    protected static ?string $modelLabel = 'Purchase Entry';
    protected static ?string $slug = 'purchase-ledger';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(ProductTierFormSchema::configure());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchase_invoice_id')
                    ->label('Invoice No.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Purchase Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('in_stock')
                    ->label('In Stock')
                    ->boolean(),

                Tables\Columns\TextColumn::make('init_quantity')
                    ->label('Initial Quantity')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sold_quantity')
                    ->label('Sold Quantity')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Available Stock')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Pages\ListProductTiers::route('/'),
            'create' => \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Pages\CreateProductTier::route('/create'),
            'view' => \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Pages\ViewProductTier::route('/{record}'),
            'edit' => \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Pages\EditProductTier::route('/{record}/edit'),
        ];
    }
}
