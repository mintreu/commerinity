<?php

namespace App\Filament\Resources\Promotion\VoucherResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Flex;
use App\Filament\Resources\Promotion\VoucherResource;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Actions;
use Filament\Forms;
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
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }


    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->voucherManager = new VoucherManager();
        $this->conditions = $this->voucherManager->getCondition();
    }





    public function form(Schema $schema): Schema
    {
        return parent::form($schema)
            ->components($this->getFormSchema())
            ;
    }

    protected function getFormSchema(): array
    {
        return [

            Fieldset::make('General Information')
                ->schema([

                    TextInput::make('name')
                        ->placeholder(__('Enter Voucher Name'))
                        ->maxLength(250)
                        ->hint(__('Max: 250'))
                        ->columnSpan(2)
                        ->required(),

                    Select::make('targets')
                        ->label('Applicable Groups')
                        ->multiple()
                        ->preload()
                        ->nullable()
                        ->columnSpanFull()
                        ->relationship('targets', 'name')
                        ->placeholder(__('Select some groups'))
                        ->helperText('Choose groups for applicable for that groups only'),

                    Textarea::make('description')
                        ->placeholder('Write Briefly About This Voucher')
                        ->hint(__('Max: 30,000'))
                        ->maxLength(30000)
                        ->columnSpanFull(),

                    Toggle::make('status')->inline(true),

                    TextInput::make('sort_order')
                        ->label('Priority')
                        ->placeholder('Set Priority')
                        ->numeric()
                        ->default(0)
                        ->inlineLabel()
                        ->required(),

                ])->columns(3),

            Fieldset::make('Voucher Timeline & Usage')
                ->schema([
                    DateTimePicker::make('starts_from')->required()->placeholder('Set Start Date And Time'),
                    DateTimePicker::make('ends_till')->required()->placeholder('Set End Date And Time'),
                    TextInput::make('usage_per_customer')->label('Usage Per Customer')->required(),
                    TextInput::make('coupon_usage_limit')->label('Coupon Usage Limit')->required(),
                ])->columns(2),

            Fieldset::make('Discount Information')
                ->schema([

                    TextInput::make('discount_amount')
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

                    Placeholder::make('formatted_discount')
                        ->live()
                        ->label(__('Discount (Formatted)'))
                        ->content(function (Get $get) {

                            $discountAmount = $get('discount_amount') ?? 0;
                            return LaravelMoney::format($discountAmount);
                        }),

                    TextInput::make('discount_quantity')->label('Max Allowed Discountable Quantity'),
                    TextInput::make('discount_step')->label('By X Quantity'),
                ])->columns(2),

            Fieldset::make('Action Information')
                ->schema([
                    Select::make('action_type')
                        ->options(collect(VoucherActionTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                        ->required(),

                    ToggleButtons::make('apply_to_shipping')
                        ->boolean()
                        ->inline()
                        ->live()
                        ->inlineLabel(),

                    ToggleButtons::make('free_shipping')
                        ->boolean()
                        ->inline()
                        ->inlineLabel()
                        ->disabled(fn(Get $get) => !$get('apply_to_shipping'))
                        ->required(),

                    ToggleButtons::make('end_other_rules')
                        ->inline()
                        ->inlineLabel()
                        ->boolean()
                        ->required(),

                ])->columns(2),

            Fieldset::make('Conditions_list')
                ->schema([

                    Select::make('condition_type')
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
                            Select::make('attribute')
                                ->label('Choose Condition')
                                ->options(fn() => !is_null($this->conditions) ? $this->conditions->pluck('label', 'key')->toArray() : [])
                                ->columnSpan(function ($state) {
                                    return empty($state) ? 3 : 1;
                                })
                                ->lazy(),


                            Flex::make(function (Get $get){
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

                                    Select::make('operator')
                                        ->hiddenLabel()
                                        ->options($conditionArray['operator'] ?? []),

                                ],$field);


                            })->visible(function (Get $get) {
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
                'select' => Select::make('value')
                    ->label('Value')
                    ->hiddenLabel()
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                'multiselect' => Select::make('value')->label('Value')
                    ->hiddenLabel()
                    ->multiple()
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                default => TextInput::make('value')
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
