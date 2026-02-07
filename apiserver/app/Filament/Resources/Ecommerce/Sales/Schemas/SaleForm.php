<?php

namespace App\Filament\Resources\Ecommerce\Sales\Schemas;

use App\Casts\ConditionMatchingCast;
use App\Casts\SaleActionTypeCast;
use App\Casts\UserTypeCast;
use App\Services\Ecommerce\SaleManager;
use Filament\Forms\Components\DateTimePicker;
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

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('uuid')
                                ->label('UUID')
                                ->disabled()
                                ->dehydrated(false)
                                ->helperText('Auto-generated on create.'),
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            DateTimePicker::make('starts_from')
                                ->required(),
                            DateTimePicker::make('ends_till')
                                ->required(),
                        ]),
                        Grid::make(3)->schema([
                            Toggle::make('status')
                                ->required(),
                            TextInput::make('sort_order')
                                ->required()
                                ->numeric()
                                ->default(0),
                            Toggle::make('end_other_rules')
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            Select::make('action_type')
                                ->options(SaleActionTypeCast::class)
                                ->required(),
                            TextInput::make('discount_amount')
                                ->required()
                                ->numeric()
                                ->default(0),
                        ]),
                    ]),

                Section::make('Targeting')
                    ->schema([
                        Select::make('categories')
                            ->multiple()
                            ->relationship('categories', 'name')
                            ->searchable(),
                        Select::make('products')
                            ->multiple()
                            ->relationship('products', 'name')
                            ->searchable(),
                        Select::make('stages')
                            ->multiple()
                            ->relationship('stages', 'name')
                            ->searchable(),
                        Select::make('levels')
                            ->multiple()
                            ->relationship('levels', 'full_name')
                            ->searchable(),
                        Select::make('users')
                            ->multiple()
                            ->relationship('users', 'name')
                            ->searchable(),
                        Select::make('target_user_types')
                            ->label('User Types')
                            ->multiple()
                            ->options(UserTypeCast::class)
                            ->searchable(),
                        Toggle::make('target_wholesale_only')
                            ->label('Wholesale Only'),
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
                                    ->options(fn () => SaleManager::make()->getCondition()->pluck('label', 'key')->toArray())
                                    ->columnSpan(fn ($state) => empty($state) ? 3 : 1)
                                    ->live(),
                                Fieldset::make('options')
                                    ->schema(function (Get $get): array {
                                        $attribute = $get('attribute');
                                        if (! $attribute) {
                                            return [];
                                        }

                                        $conditions = SaleManager::make()->getCondition();
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
                ->numeric(in_array($attribute['type'] ?? '', ['number'], true))
                ->placeholder('Enter '.$attribute['label'])
                ->required(),
        };
    }
}
