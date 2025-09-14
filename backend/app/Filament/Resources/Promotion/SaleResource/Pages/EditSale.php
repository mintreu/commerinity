<?php

namespace App\Filament\Resources\Promotion\SaleResource\Pages;

use App\Filament\Resources\Promotion\SaleResource;
use App\Filament\Resources\Promotion\SaleResource\Schema\HasSaleConditionFormSchema;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Mintreu\LaravelCommerinity\Support\SaleManager;

class EditSale extends EditRecord
{
    use HasSaleConditionFormSchema;
    protected static string $resource = SaleResource::class;

    protected ?SaleManager $saleManager = null;

    protected ?Collection $conditions = null;

    public function mount(int|string $record): void
    {

        $this->saleManager = SaleManager::make();
        $this->conditions = $this->saleManager->getCondition();
        parent::mount($record);

    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }






    protected function afterSave()
    {
        $this->saleManager = $this->saleManager ?? SaleManager::make();
        $this->saleManager->reindexSaleableProducts();
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Information')
                            ->schema(self::$resource::getCommonFormSchema()),
                        Forms\Components\Tabs\Tab::make('Conditions')
                            ->schema($this->getSaleConditionFormSchema()),
                    ])
            ]);
    }

//    public function getConditionField(array $attribute = [])
//    {
//        if (! empty($attribute)) {
//            return match ($attribute['type']) {
//                'select' => Forms\Components\Select::make('value')
//                    ->label('Value')
//                    ->options(function () use ($attribute) {
//                        return $attribute['options'];
//                    })->required(),
//                'multiselect' => Forms\Components\Select::make('value')->multiple()->label('Value')
//                    ->options(function () use ($attribute) {
//                        return $attribute['options'];
//                    })->required(),
//                default => Forms\Components\TextInput::make('value')
//                    ->type(function () use ($attribute) {
//                        return $attribute['options'] ?? 'text';
//                    })->placeholder(function () use ($attribute) {
//                        return 'Enter '.$attribute['label'];
//                    })->required(),
//            };
//        } else {
//            return [];
//        }
//    }
//
//    protected function getFormSchema(): array
//    {
//        return [
//
//
//            Forms\Components\Fieldset::make('Conditions_list')
//                ->schema([
//
//                    Forms\Components\Select::make('condition_type')
//                        ->label(__('Condition Type'))
//                        ->options([
//                            0 => 'Match All Conditions',
//                            1 => 'Match Any Condition',
//                        ])
//                        ->placeholder(__('Select condition type'))
//                        ->columnSpanFull()
//                        ->required(),
//
//                    Forms\Components\Repeater::make('conditions')
//                        ->label(__('Condition List'))
//                        ->schema([
//                            Forms\Components\Select::make('attribute')
//                                ->label(__('Choose Attribute'))
//                                ->options(function (){
//                                    $this->saleManager = $this->saleManager ?? SaleManager::make();
//                                    $this->conditions = $this->saleManager->getCondition();
//                                    return $this->conditions?->pluck('label', 'key')?->toArray() ?? [];
//                                })
//                                //->options($this->conditions->pluck('label', 'key')->toArray())
//                                ->placeholder(__('Select an attribute'))
//                                ->columnSpan(function ($state) {
//                                    return empty($state) ? 3 : 1;
//                                })
//                                ->lazy(),
//
//                            Forms\Components\Fieldset::make('options')
//                                ->label(__('Options Details'))
//                                ->schema(function (callable $get) {
//                                    if ($get('attribute') !== null) {
//                                        $this->saleManager = $this->saleManager ?? SaleManager::make();
//                                        $this->conditions = $this->saleManager->getCondition();
//
//                                        $item = $this->conditions?->where('key', $get('attribute'))?->first() ?? null;
//
//                                        if (! empty($item)) {
//                                            $field = $this->getConditionField($item);
//                                        } else {
//                                            $field = [];
//                                        }
//                                        return [];
//                                        // return $item['operator'];
//                                        return [Forms\Components\Select::make('operator')->options($item['operator']), $field];
//                                    } else {
//                                        return [];
//                                    }
//                                })
//                                ->visible(function (\Filament\Forms\Get $get) {
//                                    return ! empty($get('attribute'));
//                                })
//                                ->columnSpan(2),
//
//                        ])
//                        ->columns(3)
//                        ->columnSpanFull()
//                        ->collapsible(false),
//
//                ])->columns(2),
//
//        ];
//    }
//
//








}
