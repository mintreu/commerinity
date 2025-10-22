<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\CartResource\Pages\ListCarts;
use App\Filament\Resources\CartResource\Pages\CreateCart;
use App\Filament\Resources\CartResource\Pages\ViewCart;
use App\Filament\Resources\CartResource\Pages\EditCart;
use App\Filament\Resources\CartResource\Pages;
use App\Filament\Resources\CartResource\RelationManagers;
use App\Models\Cart;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Shop';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('discount')
                    ->numeric()
                    ->default(0),
                TextInput::make('cartable_type')
                    ->maxLength(255),
                TextInput::make('cartable_id')
                    ->numeric(),
                TextInput::make('ownerable_type')
                    ->maxLength(255),
                TextInput::make('ownerable_id')
                    ->numeric(),
                TextInput::make('guest_id')
                    ->maxLength(255),
                Toggle::make('is_guest')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                SpatieMediaLibraryImageColumn::make('cartable.media')
                    ->collection('displayImage')
                    ->label('Thumbnail')
                    ->searchable(),
                TextColumn::make('cartable.name')
                    ->label('Product Name')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_guest')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarts::route('/'),
            'create' => CreateCart::route('/create'),
            'view' => ViewCart::route('/{record}'),
            'edit' => EditCart::route('/{record}/edit'),
        ];
    }
}
