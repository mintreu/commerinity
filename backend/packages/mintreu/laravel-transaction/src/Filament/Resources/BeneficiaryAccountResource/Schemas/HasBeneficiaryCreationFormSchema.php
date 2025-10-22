<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Schemas;

use Filament\Forms;
use Mintreu\LaravelTransaction\Casts\BeneficiaryAccountTypeCast;

trait HasBeneficiaryCreationFormSchema
{

    public function getBeneficiaryCreationFormSchema():array
    {

        return [

            Forms\Components\Select::make('accountable_type')
                ->required()
                ->options(config('laravel-transaction.allowed_user_types'))
                ->live()
                ->default(get_class(filament()->auth()->user())),

            Forms\Components\Select::make('accountable_id')
                ->required()
                ->live()
                ->options(function (Forms\Get $get){
                    $selectedModel = $get('accountable_type');
                    return $selectedModel ? $selectedModel::latest()->pluck('name','id')->toArray() : [];
                }),


            Forms\Components\Select::make('wallet_id')
                ->options(function (Forms\Get $get){
                    if ($get('accountable_id') && $get('accountable_type'))
                    {
                        $selectedModel = $get('accountable_type');
                        $selectedRecord = $selectedModel::with('wallet')->find($get('accountable_id'));
                        return $selectedRecord->wallet->pluck('uuid','id');
                    }
                    return [];
                })
                ->required(),

            Forms\Components\Select::make('type')
                ->options(collect(BeneficiaryAccountTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                ->required(),


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

            Forms\Components\TextInput::make('extra'),


        ];

    }

}
