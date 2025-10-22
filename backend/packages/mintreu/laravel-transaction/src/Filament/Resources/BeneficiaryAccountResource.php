<?php

namespace Mintreu\LaravelTransaction\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages\ListBeneficiaryAccounts;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages\CreateBeneficiaryAccount;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages\ViewBeneficiaryAccount;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages\EditBeneficiaryAccount;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelTransaction\Models\BeneficiaryAccount;

class BeneficiaryAccountResource extends Resource
{
    protected static ?string $model = BeneficiaryAccount::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Wallet';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $slug = 'beneficiaries';
    protected static ?string $pluralLabel = 'Beneficiaries';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('accountable_type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('accountable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('upi_handle')
                    ->maxLength(255),
                TextInput::make('ifsc')
                    ->maxLength(255),
                TextInput::make('bank_name')
                    ->maxLength(255),
                TextInput::make('bank_branch')
                    ->maxLength(255),
                TextInput::make('account_name')
                    ->maxLength(255),
                TextInput::make('account_number')
                    ->maxLength(255),
                TextInput::make('account_type'),
                Toggle::make('default')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                Textarea::make('status_feedback')
                    ->columnSpanFull(),
                Select::make('integration_id')
                    ->relationship('integration', 'name'),
                TextInput::make('source_fund_account')
                    ->maxLength(255),
                TextInput::make('source_upi_handle')
                    ->maxLength(255),
                TextInput::make('provider_beneficiary_id')
                    ->maxLength(255),
                TextInput::make('provider_beneficiary_type')
                    ->maxLength(255),
                TextInput::make('provider_upi_handle')
                    ->maxLength(255),
                Toggle::make('beneficiary_active')
                    ->required(),
                TextInput::make('provider_data'),
                Select::make('wallet_id')
                    ->relationship('wallet', 'id'),
                TextInput::make('extra'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('accountable_type')
                    ->searchable(),
                TextColumn::make('accountable_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('upi_handle')
                    ->searchable(),
                TextColumn::make('ifsc')
                    ->searchable(),
                TextColumn::make('bank_name')
                    ->searchable(),
                TextColumn::make('bank_branch')
                    ->searchable(),
                TextColumn::make('account_name')
                    ->searchable(),
                TextColumn::make('account_number')
                    ->searchable(),
                TextColumn::make('account_type'),
                IconColumn::make('default')
                    ->boolean(),
                TextColumn::make('status'),
                TextColumn::make('integration.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('source_fund_account')
                    ->searchable(),
                TextColumn::make('source_upi_handle')
                    ->searchable(),
                TextColumn::make('provider_beneficiary_id')
                    ->searchable(),
                TextColumn::make('provider_beneficiary_type')
                    ->searchable(),
                TextColumn::make('provider_upi_handle')
                    ->searchable(),
                IconColumn::make('beneficiary_active')
                    ->boolean(),
                TextColumn::make('wallet.id')
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
            'index' => ListBeneficiaryAccounts::route('/'),
            'create' => CreateBeneficiaryAccount::route('/create'),
            'view' => ViewBeneficiaryAccount::route('/{record}'),
            'edit' => EditBeneficiaryAccount::route('/{record}/edit'),
        ];
    }
}
