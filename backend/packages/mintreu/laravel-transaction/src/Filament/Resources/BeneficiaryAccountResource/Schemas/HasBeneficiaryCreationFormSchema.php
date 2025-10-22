<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms;
use Mintreu\LaravelTransaction\Casts\BeneficiaryAccountTypeCast;

trait HasBeneficiaryCreationFormSchema
{

    public function getBeneficiaryCreationFormSchema():array
    {

        return [

            Select::make('accountable_type')
                ->required()
                ->options(config('laravel-transaction.allowed_user_types'))
                ->live()
                ->default(get_class(filament()->auth()->user())),

            Select::make('accountable_id')
                ->required()
                ->live()
                ->options(function (Get $get){
                    $selectedModel = $get('accountable_type');
                    return $selectedModel ? $selectedModel::latest()->pluck('name','id')->toArray() : [];
                }),


            Select::make('wallet_id')
                ->options(function (Get $get){
                    if ($get('accountable_id') && $get('accountable_type'))
                    {
                        $selectedModel = $get('accountable_type');
                        $selectedRecord = $selectedModel::with('wallet')->find($get('accountable_id'));
                        return $selectedRecord->wallet->pluck('uuid','id');
                    }
                    return [];
                })
                ->required(),

            Select::make('type')
                ->options(collect(BeneficiaryAccountTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                ->required(),


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

            TextInput::make('extra'),


        ];

    }

}
