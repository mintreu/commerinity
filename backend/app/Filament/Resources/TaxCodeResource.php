<?php

namespace App\Filament\Resources\TaxCodes;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\TaxCodeResource\Pages\ListTaxCodes;
use App\Filament\Resources\TaxCodeResource\Pages\CreateTaxCode;
use App\Filament\Resources\TaxCodeResource\Pages\ViewTaxCode;
use App\Filament\Resources\TaxCodeResource\Pages\EditTaxCode;
use App\Filament\Resources\TaxCodeResource\Pages;
use App\Filament\Resources\TaxCodeResource\RelationManagers;
use App\Models\TaxCode;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaxCodeResource extends Resource
{
    protected static ?string $model = TaxCode::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->maxLength(10),
                TextInput::make('type')
                    ->required(),
                TextInput::make('description')
                    ->maxLength(255),
                TextInput::make('cgst_rate')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                TextInput::make('sgst_rate')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                TextInput::make('igst_rate')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                TextInput::make('cess_rate')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('type'),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('cgst_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sgst_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('igst_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cess_rate')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
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
            'index' => ListTaxCodes::route('/'),
            'create' => CreateTaxCode::route('/create'),
            'view' => ViewTaxCode::route('/{record}'),
            'edit' => EditTaxCode::route('/{record}/edit'),
        ];
    }
}
