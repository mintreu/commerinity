<?php

namespace App\Filament\Resources\Promotion\SaleResource\Pages;

use App\Filament\Resources\Promotion\SaleResource;
use App\Services\PromotionService\Manager\SaleManager;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;
    protected SaleManager $saleManager;

    public ?Collection $conditions = null;

    public function mount(): void
    {
        $this->saleManager = SaleManager::make();
        $this->conditions = $this->saleManager->getCondition();
        $this->fillForm();
    }


    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema($this->getFormSchema());
    }

    protected function afterCreate(): void
    {
        $this->saleManager->reindexSaleableProducts();

    }



    protected function getFormSchema(): array
    {
        return [

            Forms\Components\Fieldset::make('General')
                ->schema([

                    Forms\Components\TextInput::make('name')
                        ->label(__('Rule Name'))
                        ->placeholder(__('Enter Rule Name'))
                        ->required()
                        ->columnSpan(2)
                        ->hint(__('Max: 250'))
                        ->maxLength(250),

                    Forms\Components\Select::make('targets')
                        ->label('Applicable Groups')
                        ->multiple()
                        ->preload()
                        ->relationship('targets', 'name')
                        ->placeholder(__('Select some groups'))
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->placeholder('Write Briefly About This Rule')
                        ->hint(__('Max: 40,000'))
                        ->columnSpanFull()
                        ->maxLength(40000),

                    Forms\Components\Toggle::make('status')->inline(),
                ])->columns(3),

            Forms\Components\Fieldset::make('Rule Information')
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_from')->required()->placeholder('Set Start Date And Time'),
                    Forms\Components\DateTimePicker::make('ends_till')->required()->placeholder('Set End Date And Time'),
                    Forms\Components\TextInput::make('sort_order')->type('number')->label('Priority')->required()->placeholder('Set Priority'),
                ])->columns(3),

            Forms\Components\Fieldset::make('Discount Information')
                ->schema([
                    Forms\Components\Select::make('action_type')->options([
                        'by_percent' => 'Percentage of Product Price',
                        'by_fixed' => 'Fixed Amount',
                    ])->required()->label('Discount Type'),
                    Forms\Components\TextInput::make('discount_amount')->label('Discount Amount')->required()->placeholder('Enter Discount'),

                    Forms\Components\Select::make('end_other_rules')->options([
                        0 => 'No',
                        1 => 'Yes',
                    ])->required(),
                ])->columns(3),

            Forms\Components\Fieldset::make('Conditions_list')
                ->schema([

                    Forms\Components\Select::make('condition_type')
                        ->label(__('Condition Type'))
                        ->options([
                            0 => 'Match All Conditions',
                            1 => 'Match Any Condition',
                        ])
                        ->placeholder(__('Select condition type'))
                        ->columnSpanFull()
                        ->required(),

                    Forms\Components\Repeater::make('conditions')
                        ->label(__('Condition List'))
                        ->schema([
                            Forms\Components\Select::make('attribute')
                                ->label(__('Choose Attribute'))
                                ->options([])
                                ->options($this->conditions->pluck('label', 'key')->toArray())
                                ->placeholder(__('Select an attribute'))
                                ->columnSpan(function ($state) {
                                    return empty($state) ? 3 : 1;
                                })
                                ->lazy(),

                            Forms\Components\Fieldset::make('options')
                                ->label(__('Options Details'))
                                ->schema(function (callable $get) {
                                    if ($get('attribute') !== null) {
                                        // $conditionList = $this->getCondition();
                                        //$item = null;
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
                                ->visible(function (\Filament\Forms\Get $get) {
                                    return ! empty($get('attribute'));
                                })
                                ->columnSpan(2),

                        ])
                        ->columns(3)
                        ->columnSpanFull()
                        ->collapsible(false),

                ])->columns(2),

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
                'multiselect' => Forms\Components\Select::make('value')->multiple()->label('Value')
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
