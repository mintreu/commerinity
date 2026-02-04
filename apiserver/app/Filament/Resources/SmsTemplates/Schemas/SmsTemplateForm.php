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
                    ->description('Provider mapping, IDs and sender')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            Select::make('integration_id')
                                ->label('Integration')
                                ->required()
                                ->relationship(
                                    'integration',
                                    'name',
                                    fn ($query) => $query->where('type', IntegrationTypeCast::SMS->value)
                                )
                                ->searchable()
                                ->preload()
                                ->placeholder('Select integration'),

                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(150)
                                ->placeholder('Template name'),

                            TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->maxLength(150)
                                ->placeholder('template-slug'),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('message_id')
                                ->label('Message ID')
                                ->required()
                                ->maxLength(120)
                                ->placeholder('Provider message id'),

                            TextInput::make('sender_id')
                                ->label('Sender ID')
                                ->required()
                                ->maxLength(60)
                                ->placeholder('e.g. MINTREU'),

                            TextInput::make('category')
                                ->label('Category')
                                ->required()
                                ->default('transactional')
                                ->maxLength(60)
                                ->placeholder('transactional'),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('language')
                                ->label('Language')
                                ->required()
                                ->default('en')
                                ->maxLength(10)
                                ->placeholder('en'),

                            TextInput::make('entity_id')
                                ->label('Entity ID')
                                ->maxLength(120)
                                ->placeholder('Optional'),

                            TextInput::make('template_id')
                                ->label('Template ID')
                                ->maxLength(120)
                                ->placeholder('Optional'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('Content')
                    ->description('SMS body and variables')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('content')
                            ->label('Content')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull()
                            ->placeholder('Write SMS content here...'),

                        Textarea::make('variables')
                            ->label('Variables')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('JSON / comma list / placeholder variables...'),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('variable_count')
                                ->label('Variable Count')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('usage_count')
                                ->label('Usage Count')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            DateTimePicker::make('last_used_at')
                                ->label('Last Used At')
                                ->placeholder('—'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('DLT & Status')
                    ->description('Approval and active flags')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            Toggle::make('is_active')
                                ->label('Active')
                                ->required()
                                ->default(true)
                                ->onIcon('heroicon-o-check-circle')
                                ->offIcon('heroicon-o-x-circle'),

                            Toggle::make('is_dlt_approved')
                                ->label('DLT Approved')
                                ->required()
                                ->default(false)
                                ->onIcon('heroicon-o-check-circle')
                                ->offIcon('heroicon-o-x-circle'),

                            DateTimePicker::make('dlt_approved_at')
                                ->label('DLT Approved At')
                                ->placeholder('—'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible()
                    ->collapsed(),
            ])->columnSpanFull(),
        ]);
    }
}
