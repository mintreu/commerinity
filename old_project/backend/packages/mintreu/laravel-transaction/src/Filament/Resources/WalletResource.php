<?php

namespace Mintreu\LaravelTransaction\Filament\Resources;

use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelTransaction\Models\Wallet;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Wallet';
    protected static ?string $recordRouteKeyName = 'uuid';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewWallet::class,
            Pages\ManageTransactions::class,
            Pages\ManageBeneficiaries::class
        ]);
    }





    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(36),
                Forms\Components\TextInput::make('pin')
                    ->required()
                    ->maxLength(60),
                Forms\Components\TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('walletable_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('walletable_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('INR'),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->money(LaravelMoney::defaultCurrency(),100)
                    ->sortable(),
                Tables\Columns\TextColumn::make('walletable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('walletable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
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
            'index' => \Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\ListWallets::route('/'),
            'create' => \Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\CreateWallet::route('/create'),
            'view' => \Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\ViewWallet::route('/{record:uuid}'),
            'edit' => \Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\EditWallet::route('/{record:uuid}/edit'),
            'transactions' => Pages\ManageTransactions::route('/{record:uuid}/transactions'),
            'beneficiaries' => Pages\ManageBeneficiaries::route('/{record:uuid}/beneficiaries'),
        ];
    }
}
