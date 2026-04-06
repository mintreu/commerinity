<?php

namespace App\Filament\Resources\SmsTemplates\Schemas;

use App\Casts\IntegrationTypeCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SmsTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)->schema([
                Section::make('Template Details')
                    ->description('Provider mapping, DLT ids and sender setup.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'md' => 3])->schema([
                            Select::make('integration_id')
                                ->label('Integration')
                                ->required()
                                ->relationship('integration', 'name', fn ($query) => $query->where('type', IntegrationTypeCast::SMS->value))
                                ->searchable()
                                ->preload()
                                ->placeholder('Select integration'),
                            TextInput::make('name')
                                ->required()
                                ->maxLength(150),
                            TextInput::make('slug')
                                ->required()
                                ->maxLength(150),
                        ]),
                        Grid::make(['default' => 1, 'sm' => 2, 'md' => 3])->schema([
                            TextInput::make('dlt_template_id')
                                ->label('DLT Template ID')
                                ->maxLength(120)
                                ->helperText('Primary DLT id from approved CSV.'),
                            TextInput::make('message_id')
                                ->label('Message ID (Legacy)')
                                ->maxLength(120),
                            TextInput::make('sender_id')
                                ->required()
                                ->maxLength(60),
                        ]),
                        Grid::make(['default' => 1, 'sm' => 2, 'md' => 3])->schema([
                            TextInput::make('category')
                                ->required()
                                ->default('transactional')
                                ->maxLength(60),
                            TextInput::make('language')
                                ->required()
                                ->default('en')
                                ->maxLength(10),
                            TextInput::make('entity_id')
                                ->maxLength(120),
                        ]),
                        Grid::make(['default' => 1, 'sm' => 2, 'md' => 3])->schema([
                            TextInput::make('template_id')
                                ->maxLength(120),
                            TextInput::make('variable_count')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                            TextInput::make('usage_count')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                        ]),
                    ])
                    ->compact(),
                Section::make('Content')
                    ->description('DLT-aligned body and variable keys.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('content')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        Textarea::make('variables')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Stored as JSON array in DB cast.'),
                        DateTimePicker::make('last_used_at')
                            ->placeholder('-'),
                    ])
                    ->compact(),
                Section::make('Status')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 3])->schema([
                            Toggle::make('is_active')
                                ->required()
                                ->default(true),
                            Toggle::make('is_dlt_approved')
                                ->required()
                                ->default(false),
                            DateTimePicker::make('dlt_approved_at')
                                ->placeholder('-'),
                        ]),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ])->columnSpanFull(),
        ]);
    }
}
