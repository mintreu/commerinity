<?php

namespace App\Filament\Resources\Promotion;

use App\Filament\Resources\Promotion\SaleResource\Pages;
use App\Filament\Resources\Promotion\SaleResource\RelationManagers;
use App\Models\Promotion\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Shop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('starts_from'),
                Forms\Components\DatePicker::make('ends_till'),
                Forms\Components\Toggle::make('status')
                    ->required(),
                Forms\Components\Toggle::make('condition_type')
                    ->required(),
                Forms\Components\TextInput::make('conditions'),
                Forms\Components\Toggle::make('end_other_rules')
                    ->required(),
                Forms\Components\TextInput::make('action_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_from')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_till')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\IconColumn::make('condition_type')
                    ->boolean(),
                Tables\Columns\IconColumn::make('end_other_rules')
                    ->boolean(),
                Tables\Columns\TextColumn::make('action_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
