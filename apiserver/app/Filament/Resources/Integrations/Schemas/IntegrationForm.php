<?php

namespace App\Filament\Resources\Integrations\Schemas;

use App\Casts\IntegrationTypeCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IntegrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)->schema([

                Section::make('Integration')
                    ->description('Basic identity and type')
                    ->icon('heroicon-o-puzzle-piece')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('uuid')
                                ->label('UUID')
                                ->required()
                                ->maxLength(36)
                                ->placeholder('Unique identifier')
                                ->readOnly(fn (string $operation): bool => $operation === 'edit'),

                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(120)
                                ->placeholder('e.g. Razorpay - Live'),

                            TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->maxLength(150)
                                ->placeholder('razorpay-live'),
                        ]),

                        Select::make('type')
                            ->label('Type')
                            ->options(
                                collect(IntegrationTypeCast::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                    ->all()
                            )
                            ->required()
                            ->searchable()
                            ->placeholder('Select type'),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('Credentials')
                    ->description('Provider-specific key/secret pairs')
                    ->icon('heroicon-o-key')
                    ->schema([
                        KeyValue::make('credentials')
                            ->label('Credentials')
                            ->keyLabel('Key')
                            ->valueLabel('Secret')
                            ->columnSpanFull()
                            ->addActionLabel('Add credential')
                            ->helperText('Store provider keys/secrets here. Keys differ per integration type.'),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('Settings & Flags')
                    ->description('Runtime settings and default flags')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        KeyValue::make('settings')
                            ->label('Settings')
                            ->columnSpanFull()
                            ->addActionLabel('Add setting'),

                        Grid::make([
                            'default' => 1,
                            'sm' => 3,
                        ])->schema([
                            Toggle::make('is_sandbox')
                                ->label('Sandbox')
                                ->required()
                                ->default(fn (): bool => app()->isLocal())
                                ->onIcon('heroicon-o-check-circle')
                                ->offIcon('heroicon-o-x-circle'),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->required()
                                ->default(true)
                                ->onIcon('heroicon-o-check-circle')
                                ->offIcon('heroicon-o-x-circle'),

                            Toggle::make('is_default')
                                ->label('Default')
                                ->required()
                                ->default(false)
                                ->onIcon('heroicon-o-check-circle')
                                ->offIcon('heroicon-o-x-circle'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('Health')
                    ->description('Last test status and logs')
                    ->icon('heroicon-o-heart')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            DateTimePicker::make('last_tested_at')
                                ->label('Last Tested At'),

                            TextInput::make('last_test_result')
                                ->label('Last Test Result')
                                ->placeholder('success / failed / timeout'),
                        ]),

                        Textarea::make('last_test_message')
                            ->label('Last Test Message')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Optional debug output...'),
                    ])
                    ->collapsed()
                    ->collapsible()
                    ->compact(),
            ])->columnSpanFull(),
        ]);
    }
}
