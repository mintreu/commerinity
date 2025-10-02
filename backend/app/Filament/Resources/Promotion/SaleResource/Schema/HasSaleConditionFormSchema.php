<?php

namespace App\Filament\Resources\Promotion\SaleResource\Schema;

use Filament\Forms;
use Mintreu\LaravelCommerinity\Support\SaleManager;
use Mintreu\LaravelMoney\Filament\Forms\Components\MoneyInput;

trait HasSaleConditionFormSchema
{


    public function getSaleConditionFormSchema():array
    {
        return [
            Forms\Components\Section::make('Conditions_list')
                //->aside()
                ->description('')
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
                                //->options(fn() =>$this->conditions?->pluck('label', 'key')->toArray())
                                ->options(function (){
                                    if ($this->conditions)
                                    {
                                        return $this->conditions->pluck('label', 'key')->toArray();
                                    }
                                    return [];
                                })
                                ->placeholder(__('Select an attribute'))
                                ->columnSpan(function ($state) {
                                    return empty($state) ? 3 : 1;
                                })
                                ->lazy(),

                            Forms\Components\Fieldset::make('options')
                                ->label(__('Options Details'))
                                ->schema(fn(Forms\Get $get) => $this->getConditionOptions($get))
                                ->visible(function (\Filament\Forms\Get $get) {
                                    return ! empty($get('attribute'));
                                })
                                ->columnSpan(2),

                        ])
                        ->columns(3)
                        ->columnSpanFull()
                        ->collapsible(false),

                ])
        ];
    }





    protected function getConditionOptions(Forms\Get $get): array
    {
        if ($get('attribute') !== null) {
            // $conditionList = $this->getCondition();
            //$item = null;
            $this->conditions = $this->conditions ?? SaleManager::make()->getCondition();
            $item = $this->conditions->where('key', $get('attribute'))->first();
            if (! empty($item)) {
                $field = $this->getConditionField($item);
            } else {
                $field = [];
            }

           // dd($field,$item,$get('attribute'),$this->conditions->pluck('key'));

            // return $item['operator'];
            return [Forms\Components\Select::make('operator')->options($item['operator']), $field];
        } else {
            return [];
        }
    }


    protected function getConditionField(array $attribute = [])
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
                'number', 'price' => MoneyInput::make('value')->dehydrateStateUsing(fn($state) => $state),
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
