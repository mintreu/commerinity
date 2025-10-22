<?php

namespace Mintreu\LaravelTransaction\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelTransaction\Filament\Resources\TransactionResource\Pages\ListTransactions;
use Mintreu\LaravelTransaction\Filament\Resources\TransactionResource\Pages\CreateTransaction;
use Mintreu\LaravelTransaction\Filament\Resources\TransactionResource\Pages\ViewTransaction;
use Mintreu\LaravelTransaction\Filament\Resources\TransactionResource\Pages\EditTransaction;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Filament\Resources\TransactionResource\Pages;
use Mintreu\LaravelTransaction\Filament\Resources\TransactionResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelTransaction\Models\Transaction;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Wallet';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(255),
                TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('checkout_type')
                    ->maxLength(255),
                TextInput::make('provider_gen_id')
                    ->required()
                    ->maxLength(255),
                TextInput::make('provider_transaction_id')
                    ->maxLength(255),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('verified')
                    ->required(),
                DateTimePicker::make('expire_at'),
                TextInput::make('transactionable_type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('transactionable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('provider_data'),
                TextInput::make('success_url')
                    ->maxLength(255),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
                Select::make('integration_id')
                    ->relationship('integration', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('checkout_type')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('provider_gen_id')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('provider_transaction_id')
//                    ->searchable(),
                TextColumn::make('amount')
                    //->money(LaravelMoney::defaultCurrency())
                    ->formatStateUsing(fn($state) => LaravelMoney::format($state))
                    ->sortable(),
                IconColumn::make('verified')
                    ->boolean(),
                TextColumn::make('expire_at')
                    ->dateTime()
                    ->sortable(),
//                Tables\Columns\TextColumn::make('transactionable_type')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('transactionable_id')
//                    ->numeric()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('success_url')
//                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('integration.name')
                    ->numeric()
                    ->sortable(),
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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'view' => ViewTransaction::route('/{record}'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }
}
