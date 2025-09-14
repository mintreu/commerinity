<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelTransaction\Models\Transaction;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('checkout_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('provider_gen_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('provider_transaction_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('verified')
                    ->required(),
                Forms\Components\DateTimePicker::make('expire_at'),
                Forms\Components\TextInput::make('transactionable_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('transactionable_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('provider_data'),
                Forms\Components\TextInput::make('success_url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
                Forms\Components\Select::make('integration_id')
                    ->relationship('integration', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('checkout_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider_gen_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider_transaction_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expire_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transactionable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transactionable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('success_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('integration.name')
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
