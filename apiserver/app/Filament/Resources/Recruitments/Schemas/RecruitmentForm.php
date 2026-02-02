<?php

declare(strict_types=1);

namespace App\Filament\Resources\Recruitments\Schemas;

use App\Casts\RecruitmentRoleCast;
use App\Casts\RecruitmentStatusCast;
use App\Casts\RecruitmentTypeCast;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RecruitmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make([
                'default' => 1,
                'md' => 12,
            ])->schema([
                // =========================
                // LEFT (Main) - md:8
                // =========================
                Grid::make(1)
                    ->columnSpan(['md' => 8])
                    ->schema([
                        Section::make('Basic Information')
                            ->description('Title, description, role, type, location and timeline')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                ])->schema([
                                    TextInput::make('title')
                                        ->label('Title')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(
                                            fn ($state, callable $set) => $set('slug', Str::slug((string) $state))
                                        ),

                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true),
                                ]),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->placeholder('Write a short description of the role...'),

                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                ])->schema([
                                    Select::make('role')
                                        ->label('Role')
                                        ->options(RecruitmentRoleCast::class)
                                        ->required()
                                        ->placeholder('Select role'),

                                    Select::make('employment_type')
                                        ->label('Employment Type')
                                        ->options(RecruitmentTypeCast::class)
                                        ->default('full_time')
                                        ->required(),

                                    TextInput::make('location')
                                        ->label('Location')
                                        ->maxLength(255)
                                        ->placeholder('Remote / Kolkata / India'),
                                ]),

                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                ])->schema([
                                    TextInput::make('vacancy')
                                        ->label('Vacancy')
                                        ->required()
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1),

                                    DatePicker::make('open_date')
                                        ->label('Open Date')
                                        ->default(now()),

                                    DatePicker::make('close_date')
                                        ->label('Close Date'),
                                ]),
                            ])
                            ->compact(),

                        Section::make('Eligibility Criteria')
                            ->description('Age, education and experience requirements')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 4,
                                ])->schema([
                                    TextInput::make('eligibility.min_age')
                                        ->label('Min Age')
                                        ->numeric()
                                        ->minValue(18)
                                        ->default(18),

                                    TextInput::make('eligibility.max_age')
                                        ->label('Max Age')
                                        ->numeric()
                                        ->maxValue(65)
                                        ->placeholder('Optional'),

                                    TextInput::make('eligibility.education')
                                        ->label('Min Education')
                                        ->placeholder('e.g., Graduate'),

                                    TextInput::make('eligibility.experience')
                                        ->label('Min Experience')
                                        ->placeholder('e.g., 2 years'),
                                ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->compact(),

                        Section::make('Requirements & Benefits')
                            ->description('What you expect and what you offer')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])->schema([
                                    Repeater::make('requirements')
                                        ->label('Requirements')
                                        ->simple(
                                            TextInput::make('requirement')
                                                ->placeholder('e.g., Bachelor’s degree in related field')
                                        )
                                        ->addActionLabel('Add Requirement')
                                        ->defaultItems(0)
                                        ->reorderable(),

                                    Repeater::make('benefits')
                                        ->label('Benefits')
                                        ->simple(
                                            TextInput::make('benefit')
                                                ->placeholder('e.g., Health insurance, Flexible hours')
                                        )
                                        ->addActionLabel('Add Benefit')
                                        ->defaultItems(0)
                                        ->reorderable(),
                                ]),
                            ])
                            ->collapsible()
                            ->compact(),
                    ]),

                // =========================
                // RIGHT (Sidebar) - md:4
                // =========================
                Grid::make(1)
                    ->columnSpan(['md' => 4])
                    ->schema([
                        Section::make('Status')
                            ->description('Workflow status and internal notes')
                            ->icon('heroicon-o-flag')
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options(RecruitmentStatusCast::class)
                                    ->default('draft')
                                    ->required(),

                                Textarea::make('status_feedback')
                                    ->label('Notes')
                                    ->rows(2)
                                    ->placeholder('Optional internal note...'),
                            ])
                            ->compact(),

                        Section::make('Application Fee')
                            ->description('Enable if candidates must pay')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Toggle::make('is_payable')
                                    ->label('Require Payment')
                                    ->helperText('Enable to charge an application fee')
                                    ->live(),

                                TextInput::make('fees')
                                    ->label('Fee Amount (in Rupees)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('₹')
                                    ->helperText('Enter amount in Rupees (stored in paisa)')
                                    ->visible(fn ($get) => (bool) $get('is_payable'))
                                    ->dehydrateStateUsing(fn ($state) => (int) (((float) $state) * 100))
                                    ->formatStateUsing(fn ($state) => $state ? ((int) $state) / 100 : 0),
                            ])
                            ->compact()
                            ->collapsible(),

                        Section::make('Media')
                            ->description('Display image and info PDF')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('display_image')
                                    ->label('Display Image')
                                    ->collection('display_image')
                                    ->image()
                                    ->imageEditor(),

                                SpatieMediaLibraryFileUpload::make('info_pdf')
                                    ->label('Info PDF')
                                    ->collection('info_pdf')
                                    ->acceptedFileTypes(['application/pdf']),
                            ])
                            ->compact()
                            ->collapsible()
                            ->collapsed(),
                    ]),
            ])
                ->columnSpanFull()
                ->extraAttributes(['class' => 'gap-6']),
        ]);
    }
}
