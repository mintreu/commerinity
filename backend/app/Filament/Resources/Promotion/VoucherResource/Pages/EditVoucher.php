<?php

namespace App\Filament\Resources\Promotion\VoucherResource\Pages;

use App\Filament\Resources\Promotion\VoucherResource;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Mintreu\LaravelCommerinity\Casts\VoucherActionTypeCast;
use Mintreu\LaravelCommerinity\Casts\VoucherConditionMatchingCast;
use Mintreu\LaravelCommerinity\Support\VoucherManager;
use Mintreu\LaravelMoney\LaravelMoney;


class EditVoucher extends EditRecord
{
    protected static string $resource = VoucherResource::class;

    protected ?Collection $conditions = null;
    protected VoucherManager $voucherManager;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }


    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->voucherManager = new VoucherManager();
        $this->conditions = $this->voucherManager->getCondition();
    }





    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema($this->getFormSchema())
            ;
    }

    protected function getFormSchema(): array
    {
        return [

            Forms\Components\Fieldset::make('General Information')
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
                        ->columnSpanFull()
                        ->relationship('targets', 'name')
                        ->placeholder(__('Select some groups'))
                        ->helperText('Choose groups for applicable for that groups only'),

                    Forms\Components\Textarea::make('description')
                        ->placeholder('Write Briefly About This Voucher')
                        ->hint(__('Max: 30,000'))
                        ->maxLength(30000)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('status')->inline(true),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Priority')
                        ->placeholder('Set Priority')
                        ->numeric()
                        ->default(0)
                        ->inlineLabel()
                        ->required(),

                ])->columns(3),

            Forms\Components\Fieldset::make('Voucher Timeline & Usage')
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_from')->required()->placeholder('Set Start Date And Time'),
                    Forms\Components\DateTimePicker::make('ends_till')->required()->placeholder('Set End Date And Time'),
                    Forms\Components\TextInput::make('usage_per_customer')->label('Usage Per Customer')->required(),
                    Forms\Components\TextInput::make('coupon_usage_limit')->label('Coupon Usage Limit')->required(),
                ])->columns(2),

            Forms\Components\Fieldset::make('Discount Information')
                ->schema([

                    Forms\Components\TextInput::make('discount_amount')
                        ->label('Discount Amount')
                        ->numeric()
                        ->inputMode('decimal')
                        ->default(0.00)
                        ->minValue(0)
                        ->maxValue(99999999)
                        ->required()
                        ->lazy()
                        ->extraInputAttributes(['step' => '0.01', 'min' => 0, 'max' => 99999999])
                        ->hint('enter value multiply by 100')
                        ->default(0.00)
                        ->required()
                        ->placeholder('Enter Discount')
                        ->hint(__('eg: 45020 = '.LaravelMoney::format(45020)))
                        ->lazy(),

                    Forms\Components\Placeholder::make('formatted_discount')
                        ->live()
                        ->label(__('Discount (Formatted)'))
                        ->content(function (Get $get) {

                            $discountAmount = $get('discount_amount') ?? 0;
                            return LaravelMoney::format($discountAmount);
                        }),

                    Forms\Components\TextInput::make('discount_quantity')->label('Max Allowed Discountable Quantity'),
                    Forms\Components\TextInput::make('discount_step')->label('By X Quantity'),
                ])->columns(2),

            Forms\Components\Fieldset::make('Action Information')
                ->schema([
                    Forms\Components\Select::make('action_type')
                        ->options(collect(VoucherActionTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                        ->required(),

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

                ])->columns(2),

            Forms\Components\Fieldset::make('Conditions_list')
                ->schema([

                    Forms\Components\Select::make('condition_type')
                        ->options(collect(VoucherConditionMatchingCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                        ->required()
                        ->label('Apply By'),


                    TableRepeater::make('conditions')
                        ->columns(3)
                        ->headers([
                            Header::make('attribute'),
                            Header::make('operator'),
                            Header::make('value'),
                        ])
                        ->schema([
                            Forms\Components\Select::make('attribute')
                                ->label('Choose Condition')
                                ->options(fn() => !is_null($this->conditions) ? $this->conditions->pluck('label', 'key')->toArray() : [])
                                ->columnSpan(function ($state) {
                                    return empty($state) ? 3 : 1;
                                })
                                ->lazy(),


                            Forms\Components\Split::make(function (Get $get){
                                $conditionArray = [];

                                if ($get('attribute') != null) {
                                    $conditionArray = $this->conditions?->where('key', $get('attribute'))->first();
                                }

                                if (! empty($conditionArray)) {
                                    $field = [$this->getConditionField($conditionArray)];
                                } else {
                                    $field = [];
                                }

                                return array_merge([

                                    Forms\Components\Select::make('operator')
                                        ->hiddenLabel()
                                        ->options($conditionArray['operator'] ?? []),

                                ],$field);


                            })->visible(function (\Filament\Forms\Get $get) {
                                return ! empty($get('attribute'));
                            }),




                        ])

                ])->columns(1)->label('Condition Details'),

        ];
    }

    public function getConditionField(array $attribute = [])
    {
        if (! empty($attribute)) {
            return match ($attribute['type']) {
                'select' => Forms\Components\Select::make('value')
                    ->label('Value')
                    ->hiddenLabel()
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                'multiselect' => Forms\Components\Select::make('value')->label('Value')
                    ->hiddenLabel()
                    ->multiple()
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                default => Forms\Components\TextInput::make('value')
                    ->hiddenLabel()
                    ->type(function () use ($attribute) {
                        return $attribute['options'] ?? 'text';
                    })->placeholder(function () use ($attribute) {
                        return 'Enter '.$attribute['label'];
                    })->required(),
            };
        } else {
            return [];
        }
    }








}
