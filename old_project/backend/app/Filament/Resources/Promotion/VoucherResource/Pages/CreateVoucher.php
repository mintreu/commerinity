<?php

namespace App\Filament\Resources\Promotion\VoucherResource\Pages;

use App\Filament\Resources\Promotion\VoucherResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;
use Mintreu\LaravelCommerinity\Casts\VoucherConditionMatchingCast;
use Mintreu\LaravelCommerinity\Support\VoucherManager;


class CreateVoucher extends CreateRecord
{
    use VoucherResource\Schema\HasVoucherFormSchema;
    protected static string $resource = VoucherResource::class;
    protected ?string $maxContentWidth = '9xl';

    protected ?Collection $conditions = null;
    protected VoucherManager $voucherManager;

    public function mount(): void
    {
        $this->voucherManager = VoucherManager::make();
        $this->conditions = $this->voucherManager->getCondition();
        $this->fillForm();
    }






    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema(array_merge($this->getSchema(),[]));
    }

    protected function getFormSchema(): array
    {
        return [


            Forms\Components\Fieldset::make('Conditions_list')
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
                                ->columnSpan(function ($state) {
                                    return empty($state) ? 3 : 1;
                                })
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

                ])->columns(1)->label('Condition Details'),

        ];
    }

    public function getConditionField(array $attribute = [])
    {
        if (! empty($attribute)) {
            return match ($attribute['type']) {
                'select' => Forms\Components\Select::make('value')
                    ->label('Value')
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                'multiselect' => Forms\Components\Select::make('value')->label('Value')
                    ->multiple()
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                default => Forms\Components\TextInput::make('value')
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
