<?php

namespace App\Filament\Resources\Geo\Countries\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)->schema([

                Section::make('Basic')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('name')
                                ->label('Country Name')
                                ->required()
                                ->maxLength(120)
                                ->placeholder('e.g. India'),

                            TextInput::make('iso_code_2')
                                ->label('ISO Code (2)')
                                ->required()
                                ->maxLength(2)
                                ->placeholder('IN'),

                            TextInput::make('iso_code_3')
                                ->label('ISO Code (3)')
                                ->required()
                                ->maxLength(3)
                                ->placeholder('IND'),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('isd_code')
                                ->label('ISD Code')
                                ->numeric()
                                ->minValue(0)
                                ->placeholder('91'),

                            TextInput::make('locale')
                                ->label('Locale')
                                ->maxLength(20)
                                ->placeholder('en_IN'),

                            TextInput::make('region')
                                ->label('Region')
                                ->maxLength(60)
                                ->placeholder('Asia'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('Address & Postal')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Textarea::make('address_format')
                            ->label('Address Format')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Optional formatting template...'),

                        Toggle::make('postcode_required')
                            ->label('Postcode Required')
                            ->default(false),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('Timezone')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('timezone')
                                ->label('Timezone')
                                ->maxLength(80)
                                ->placeholder('Asia/Kolkata'),

                            TextInput::make('timezone_diff')
                                ->label('Timezone Diff')
                                ->maxLength(20)
                                ->placeholder('+05:30'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('Currency & Rates')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('currency')
                                ->label('Currency')
                                ->maxLength(10)
                                ->placeholder('INR'),

                            TextInput::make('multiplier')
                                ->label('Multiplier')
                                ->numeric()
                                ->default(1)
                                ->placeholder('1.0'),

                            TextInput::make('flag')
                                ->label('Flag')
                                ->maxLength(50)
                                ->placeholder('🇮🇳 or asset key'),
                        ]),

                        KeyValue::make('exchange_rate')
                            ->label('Exchange Rates')
                            ->keyLabel('Currency')
                            ->valueLabel('Rate')
                            ->addActionLabel('Add Rate')
                            ->columnSpanFull()
                            ->helperText('Example: USD => 83.50'),
                    ])
                    ->collapsed()
                    ->collapsible()
                    ->compact(),

                Section::make('Status')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->compact(),
            ])->columnSpanFull(),
        ]);
    }
}
