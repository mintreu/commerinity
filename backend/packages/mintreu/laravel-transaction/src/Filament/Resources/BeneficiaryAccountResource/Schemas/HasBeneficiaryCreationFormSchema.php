<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Schemas;

use Filament\Forms;
use Mintreu\LaravelTransaction\Casts\BeneficiaryAccountStatusCast;
use Mintreu\LaravelTransaction\Casts\BeneficiaryAccountTypeCast;
use Mintreu\LaravelTransaction\Models\Wallet;

trait HasBeneficiaryCreationFormSchema
{

    public function getBeneficiaryCreationFormSchema():array
    {

        return [

            Forms\Components\Section::make('General')
                ->aside()
                ->schema([

//                    Forms\Components\Select::make('wallet_id')
//                        ->options(Wallet::all()->pluck('uuid','id'))
//                        ->afterStateUpdated(function (Forms\Set $set,$state){
//                            $wallet = Wallet::find($state);
//                            $set('accountable_type',$wallet->walletable_type);
//                            $set('accountable_id',$wallet->walletable_id);
//
//                        })
//                        ->live()
//                        ->required(),

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


                    Forms\Components\Select::make('type')
                        ->options(collect(BeneficiaryAccountTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options(collect(BeneficiaryAccountStatusCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                        ->required(),
                ]),


            Forms\Components\Section::make('Config')
                ->aside()
                ->schema([

                    Forms\Components\Radio::make('account_type')
                        ->live()
                        ->options([
                            'upi' => 'UPI Handle',
                            'bank' => 'Bank Account'
                        ])->required()
                        ->inlineLabel()->inline()->columnSpanFull(),

                    Forms\Components\TextInput::make('upi_handle')
                        ->columnSpanFull()
                        ->visible(fn(Forms\Get $get) => $get('account_type') == 'upi')
                        ->maxLength(255),


                    Forms\Components\Grid::make(1)
                        ->columnSpanFull()
                        ->visible(fn(Forms\Get $get) => $get('account_type') == 'bank')
                        ->columns()
                        ->schema([
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
                        ]),

                    Forms\Components\Toggle::make('default')
                        ->required(),
                ]),


            Forms\Components\Section::make('Provider Config')
                ->aside()
                ->visible(false)
                ->schema([

//                    Forms\Components\Textarea::make('status_feedback')
//                        ->columnSpanFull(),

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
                ]),









        ];

    }

}
