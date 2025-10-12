<?php

namespace App\Filament\Resources\Promotion\VoucherResource\Schema;

use Filament\Forms;
use Mintreu\LaravelCommerinity\Casts\VoucherActionTypeCast;
use Mintreu\LaravelCommerinity\Casts\VoucherConditionMatchingCast;
use Mintreu\LaravelCommerinity\Support\VoucherManager;
use Mintreu\LaravelMoney\LaravelMoney;

trait HasVoucherFormSchema
{


    public function getSchema():array
    {
        return [
            $this->getGeneralSchema(),
            $this->getVoucherUsageSchema(),
            $this->getActionSchema(),
            $this->getConditionalFormSchema()
        ];
    }


    protected function getGeneralSchema(): Forms\Components\Section
    {
        return  Forms\Components\Section::make('General Information')
            ->aside()
            ->columns(3)
            ->schema([

                Forms\Components\TextInput::make('name')
                    ->placeholder(__('Enter Voucher Name'))
                    ->maxLength(250)
                    ->hint(__('Max: 250'))
                    ->columnSpan(2)
                    ->required(),

                Forms\Components\Select::make('targets')
                    ->label('Applicable Groups')
                    ->multiple()
                    ->preload()
                    ->nullable()
                    ->columnSpan(1)
                    ->relationship('targets', 'name')
                    ->placeholder(__('Select some groups'))
                    ->helperText('Choose groups for applicable for that groups only'),

                Forms\Components\Textarea::make('description')
                    ->placeholder('Write Briefly About This Voucher')
                    ->hint(__('Max: 30,000'))
                    ->maxLength(30000)
                    ->columnSpanFull(),



                Forms\Components\TextInput::make('sort_order')
                    ->label('Priority')
                    ->placeholder('Set Priority')
                    ->numeric()
                    ->default(0)
                    ->columnSpan(2)
                    ->inlineLabel()
                    ->required(),

                Forms\Components\Toggle::make('status')->inline(true)->columnSpan(1),
            ]);
    }



    protected function getVoucherUsageSchema()
    {
        return Forms\Components\Section::make('Voucher Timeline & Usage')
                ->aside()
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_from')->required()->placeholder('Set Start Date And Time'),
                    Forms\Components\DateTimePicker::make('ends_till')->required()->placeholder('Set End Date And Time'),
                    Forms\Components\TextInput::make('usage_per_customer')->label('Usage Per Customer')->required(),
                    Forms\Components\TextInput::make('coupon_usage_limit')->label('Coupon Usage Limit')->required(),
                ])->columns(2);
    }




    protected function getActionSchema()
    {
        return Forms\Components\Section::make('Action Information')
            ->aside()
            ->schema([

                Forms\Components\Select::make('action_type')
                    ->inlineLabel()
                    ->columnSpanFull()
                    ->options(collect(VoucherActionTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                    ->live()
                    ->required(),

                Forms\Components\Grid::make(1)
                    ->columns(1)
                    ->columnSpanFull()
                    ->visible(fn(Forms\Get $get) => $get('action_type'))
                    ->schema([
                        Forms\Components\ToggleButtons::make('apply_to_shipping')
                            ->boolean()
                            ->inline()
                            ->live()
                            ->inlineLabel(),

                        Forms\Components\ToggleButtons::make('free_shipping')
                            ->boolean()
                            ->inline()
                            ->inlineLabel()
                            ->disabled(fn(Forms\Get $get) => !$get('apply_to_shipping'))
                            ->required(),

                        Forms\Components\ToggleButtons::make('end_other_rules')
                            ->inline()
                            ->inlineLabel()
                            ->boolean()
                            ->required(),
                    ]),

            ])->columns(1);
    }


    protected function getDiscountSchema()
    {

        return  Forms\Components\Section::make('Discount Information')
            ->aside()
            ->schema([
                Forms\Components\TextInput::make('discount_amount')
                    ->label('Discount Value')
                    ->helperText('Enter percentage or if fixed amount, enter in paisa')
                    ->hint(function (Forms\Get $get, $state) {
                        if (! $state) {
                            return null;
                        }

                        if (in_array($get('action_type'), [
                            VoucherActionTypeCast::BY_PERCENT->value,
                            VoucherActionTypeCast::CART_PERCENT->value,
                        ])) {
                            return $state . '%';
                        }
                        if ($get('action_type') == VoucherActionTypeCast::BY_X_GET_Y->value)
                        {
                            return 'Free';
                        }

                        return LaravelMoney::make($state);
                    })
                    ->integer()
                    ->inputMode('decimal')
                    ->minValue(1)
                    ->required()
                    ->reactive() // <-- important to re-render when state changes
                    ->placeholder('Enter Discount'),

                Forms\Components\TextInput::make('formatted_discount')->label(__('Discount (Formatted)'))->disabled(),

                Forms\Components\TextInput::make('discount_quantity')->label('Max Allowed Discountable Quantity'),
                Forms\Components\TextInput::make('discount_step')->label('By X Quantity'),
            ])->columnSpanFull();


    }







    public function getConditionalFormSchema()
    {
        return  Forms\Components\Section::make('Conditions_list')
            ->aside()
            ->schema([

                Forms\Components\Select::make('condition_type')
                    ->options(collect(VoucherConditionMatchingCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                    ->required()
                    ->label('Apply By'),

                Forms\Components\Repeater::make('conditions')
                    ->label(__('Condition List'))
                    ->schema([
                        Forms\Components\Select::make('attribute')
                            ->label('Choose Condition')
                            ->options(function (){
                                $this->conditions = $this->conditions ?? VoucherManager::make()->getCondition();
                                return $this->conditions->pluck('label', 'key')->toArray();
                            })
//                            ->columnSpan(function ($state) {
//                                return empty($state) ? 3 : 1;
//                            })
                            ->columnSpanFull()
                            ->lazy(),

                        Forms\Components\Fieldset::make('options')
                            ->schema(function (callable $get) {
                                if ($get('attribute') !== null) {
                                    $this->conditions = $this->conditions ?? VoucherManager::make()->getCondition();
                                    $item = $this->conditions->where('key', $get('attribute'))->first();

                                    if (! empty($item)) {
                                        $field = $this->getConditionField($item);
                                    } else {
                                        $field = [];
                                    }

                                    // return $item['operator'];
                                    return [Forms\Components\Select::make('operator')->options($item['operator']), $field];
                                } else {
                                    return [];
                                }
                            })
                            ->label('Details')
                            ->visible(function (\Filament\Forms\Get $get) {
                                return ! empty($get('attribute'));
                            }),

                    ])
                    ->columns(3)
                    ->defaultItems(0)
                    ->collapsible(false),

            ])
            ->columns(1)->label('Condition Details');
    }










}
