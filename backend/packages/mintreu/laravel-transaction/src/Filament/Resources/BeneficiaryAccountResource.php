<?php

namespace Mintreu\LaravelTransaction\Filament\Resources;

use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelTransaction\Models\BeneficiaryAccount;

class BeneficiaryAccountResource extends Resource
{
    protected static ?string $model = BeneficiaryAccount::class;
    protected static ?string $navigationGroup = 'Wallet';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $slug = 'beneficiaries';
    protected static ?string $pluralLabel = 'Beneficiaries';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('accountable_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('accountable_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('upi_handle')
                    ->maxLength(255),
                Forms\Components\TextInput::make('ifsc')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank_branch')
                    ->maxLength(255),
                Forms\Components\TextInput::make('account_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('account_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('account_type'),
                Forms\Components\Toggle::make('default')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('status_feedback')
                    ->columnSpanFull(),
                Forms\Components\Select::make('integration_id')
                    ->relationship('integration', 'name'),
                Forms\Components\TextInput::make('source_fund_account')
                    ->maxLength(255),
                Forms\Components\TextInput::make('source_upi_handle')
                    ->maxLength(255),
                Forms\Components\TextInput::make('provider_beneficiary_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('provider_beneficiary_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('provider_upi_handle')
                    ->maxLength(255),
                Forms\Components\Toggle::make('beneficiary_active')
                    ->required(),
                Forms\Components\TextInput::make('provider_data'),
                Forms\Components\Select::make('wallet_id')
                    ->relationship('wallet', 'id'),
                Forms\Components\TextInput::make('extra'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('accountable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('accountable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('upi_handle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ifsc')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_branch')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_type'),
                Tables\Columns\IconColumn::make('default')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('integration.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source_fund_account')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_upi_handle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider_beneficiary_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider_beneficiary_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider_upi_handle')
                    ->searchable(),
                Tables\Columns\IconColumn::make('beneficiary_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('wallet.id')
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
            'index' => \Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages\ListBeneficiaryAccounts::route('/'),
            'create' => \Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages\CreateBeneficiaryAccount::route('/create'),
            'view' => \Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages\ViewBeneficiaryAccount::route('/{record}'),
            'edit' => \Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages\EditBeneficiaryAccount::route('/{record}/edit'),
        ];
    }
}
