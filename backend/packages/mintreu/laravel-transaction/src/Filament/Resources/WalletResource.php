<?php

namespace Mintreu\LaravelTransaction\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\ViewWallet;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\ManageTransactions;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\ManageBeneficiaries;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\ListWallets;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\CreateWallet;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages\EditWallet;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelTransaction\Models\Wallet;
use Filament\Resources\Pages\Page;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Wallet';
    protected static ?string $recordRouteKeyName = 'uuid';
    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewWallet::class,
            ManageTransactions::class,
            ManageBeneficiaries::class
        ]);
    }





    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(36),
                TextInput::make('pin')
                    ->required()
                    ->maxLength(60),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                TextInput::make('walletable_type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('walletable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('INR'),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('pin')
                    ->searchable(),
                TextColumn::make('balance')
                    ->money(LaravelMoney::defaultCurrency(),100)
                    ->sortable(),
                TextColumn::make('walletable_type')
                    ->searchable(),
                TextColumn::make('walletable_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
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
            'index' => ListWallets::route('/'),
            'create' => CreateWallet::route('/create'),
            'view' => ViewWallet::route('/{record:uuid}'),
            'edit' => EditWallet::route('/{record:uuid}/edit'),
            'transactions' => ManageTransactions::route('/{record:uuid}/transactions'),
            'beneficiaries' => ManageBeneficiaries::route('/{record:uuid}/beneficiaries'),
        ];
    }
}
