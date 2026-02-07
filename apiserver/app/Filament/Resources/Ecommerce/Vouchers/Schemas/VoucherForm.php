<?php

namespace App\Filament\Resources\Ecommerce\Vouchers\Schemas;

use App\Casts\ConditionMatchingCast;
use App\Casts\VoucherActionTypeCast;
use App\Services\Ecommerce\VoucherManager;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Toggle::make('status')
                                ->required(),
                        ]),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            DatePicker::make('starts_from')
                                ->required(),
                            DatePicker::make('ends_till')
                                ->required(),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('usage_per_customer')
                                ->required()
                                ->numeric()
                                ->default(1),
                            TextInput::make('coupon_usage_limit')
                                ->required()
                                ->numeric()
                                ->default(0),
                            TextInput::make('times_used')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('min_cart_value')
                                ->required()
                                ->numeric()
                                ->default(0),
                            TextInput::make('min_quantity')
                                ->required()
                                ->numeric()
                                ->default(0),
                            TextInput::make('sort_order')
                                ->required()
                                ->numeric()
                                ->default(0),
                        ]),
                    ]),

                Section::make('Discount & Action')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('action_type')
                                ->options(VoucherActionTypeCast::class)
                                ->required(),
                            TextInput::make('discount_amount')
                                ->required()
                                ->numeric()
                                ->default(0),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('discount_quantity')
                                ->numeric()
                                ->default(1),
                            TextInput::make('discount_step'),
                        ]),
                        Grid::make(3)->schema([
                            Toggle::make('apply_to_shipping')
                                ->required(),
                            Toggle::make('free_shipping')
                                ->required(),
                            Toggle::make('end_other_rules')
                                ->required(),
                        ]),
                    ]),

                Section::make('Conditions')
                    ->schema([
                        Select::make('condition_type')
                            ->options(ConditionMatchingCast::class)
                            ->default(ConditionMatchingCast::MATCH_ALL->value)
                            ->required(),
                        Repeater::make('conditions')
                            ->label('Condition List')
                            ->schema([
                                Select::make('attribute')
                                    ->label('Choose Condition')
                                    ->options(fn () => VoucherManager::make()->getCondition()->pluck('label', 'key')->toArray())
                                    ->columnSpan(fn ($state) => empty($state) ? 3 : 1)
                                    ->live(),
                                Fieldset::make('options')
                                    ->schema(function (Get $get): array {
                                        $attribute = $get('attribute');
                                        if (! $attribute) {
                                            return [];
                                        }

                                        $conditions = VoucherManager::make()->getCondition();
                                        $item = $conditions->where('key', $attribute)->first();

                                        if (! $item) {
                                            return [];
                                        }

                                        $field = self::getConditionField($item);

                                        return [
                                            Select::make('operator')
                                                ->options($item['operator'] ?? [])
                                                ->required(),
                                            $field,
                                        ];
                                    })
                                    ->visible(fn (Get $get) => ! empty($get('attribute')))
                                    ->columnSpan(2),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsible(false),
                    ]),
            ]);
    }

    private static function getConditionField(array $attribute)
    {
        return match ($attribute['type'] ?? 'text') {
            'select' => Select::make('value')
                ->label('Value')
                ->options($attribute['options'] ?? [])
                ->required(),
            'multiselect' => Select::make('value')
                ->label('Value')
                ->multiple()
                ->options($attribute['options'] ?? [])
                ->required(),
            default => TextInput::make('value')
                ->numeric(in_array($attribute['type'] ?? '', ['number', 'price', 'integer', 'decimal'], true))
                ->placeholder('Enter '.$attribute['label'])
                ->required(),
        };
    }
}
