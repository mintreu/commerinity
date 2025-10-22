<?php

namespace App\Filament\Resources\Promotion\Vouchers\Pages;

use App\Filament\Resources\Promotion\Vouchers\Schema\HasVoucherFormSchema;
use Filament\Support\Enums\Width;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Promotion\Vouchers\VoucherResource;
use Filament\Forms;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;
use Mintreu\LaravelCommerinity\Casts\VoucherConditionMatchingCast;
use Mintreu\LaravelCommerinity\Support\VoucherManager;


class CreateVoucher extends CreateRecord
{
    use HasVoucherFormSchema;
    protected static string $resource = VoucherResource::class;
    protected Width|string|null $maxContentWidth = '9xl';

    protected ?Collection $conditions = null;
    protected VoucherManager $voucherManager;

    public function mount(): void
    {
        $this->voucherManager = VoucherManager::make();
        $this->conditions = $this->voucherManager->getCondition();
        $this->fillForm();
    }






    public function form(Schema $schema): Schema
    {
        return parent::form($schema)
            ->components(array_merge($this->getFormSchema(),[]));
    }

    protected function getFormSchema(): array
    {
        return [


            Fieldset::make('Conditions_list')
                ->schema([

                    Select::make('condition_type')
                        ->options(collect(VoucherConditionMatchingCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                        ->required()
                        ->label('Apply By'),

                    Repeater::make('conditions')
                        ->label(__('Condition List'))
                        ->schema([
                            Select::make('attribute')
                                ->label('Choose Condition')
                                ->options(function (){
                                    $this->conditions = $this->conditions ?? VoucherManager::make()->getCondition();
                                    return $this->conditions->pluck('label', 'key')->toArray();
                                })
                                ->columnSpan(function ($state) {
                                    return empty($state) ? 3 : 1;
                                })
                                ->lazy(),

                            Fieldset::make('options')
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
                                        return [Select::make('operator')->options($item['operator']), $field];
                                    } else {
                                        return [];
                                    }
                                })
                                ->label('Details')
                                ->visible(function (Get $get) {
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
                'select' => Select::make('value')
                    ->label('Value')
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                'multiselect' => Select::make('value')->label('Value')
                    ->multiple()
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                default => TextInput::make('value')
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
