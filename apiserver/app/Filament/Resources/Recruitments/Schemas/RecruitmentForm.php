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

class RecruitmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make('Basic Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                                        TextInput::make('slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true),
                                    ]),

                                Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Grid::make(3)
                                    ->schema([
                                        Select::make('role')
                                            ->options(RecruitmentRoleCast::class)
                                            ->required(),

                                        Select::make('employment_type')
                                            ->options(RecruitmentTypeCast::class)
                                            ->default('full_time')
                                            ->required(),

                                        TextInput::make('location')
                                            ->maxLength(255)
                                            ->placeholder('e.g., Remote, Kolkata, India'),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('vacancy')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1),

                                        DatePicker::make('open_date')
                                            ->default(now()),

                                        DatePicker::make('close_date'),
                                    ]),
                            ]),

                        Section::make('Eligibility Criteria')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('eligibility.min_age')
                                            ->label('Min Age')
                                            ->numeric()
                                            ->minValue(18)
                                            ->default(18),

                                        TextInput::make('eligibility.max_age')
                                            ->label('Max Age')
                                            ->numeric()
                                            ->maxValue(65),

                                        TextInput::make('eligibility.education')
                                            ->label('Min Education')
                                            ->placeholder('e.g., Graduate'),

                                        TextInput::make('eligibility.experience')
                                            ->label('Min Experience')
                                            ->placeholder('e.g., 2 years'),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ]),

                Grid::make(1)
                    ->schema([
                        Section::make('Media')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('display_image')
                                    ->collection('display_image')
                                    ->image()
                                    ->imageEditor(),

                                SpatieMediaLibraryFileUpload::make('info_pdf')
                                    ->collection('info_pdf')
                                    ->acceptedFileTypes(['application/pdf']),
                            ])
                            ->columnSpanFull()
                            ->collapsible()
                            ->collapsed(),

                        Section::make('Application Fee')
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
                                    ->helperText('Enter amount in Rupees (will be stored in paisa)')
                                    ->visible(fn ($get) => $get('is_payable'))
                                    ->dehydrateStateUsing(fn ($state) => (int) ($state * 100))
                                    ->formatStateUsing(fn ($state) => $state / 100),
                            ])
                            ->columns(2),

                        Section::make('Status')
                            ->schema([
                                Select::make('status')
                                    ->options(RecruitmentStatusCast::class)
                                    ->default('draft')
                                    ->required(),

                                Textarea::make('status_feedback')
                                    ->label('Notes')
                                    ->rows(2),
                            ])
                            ->collapsible()
                            ->columnSpanFull()
                    ]),




                Section::make('Requirements & Benefits')
                    ->schema([
                        Repeater::make('requirements')
                            ->simple(
                                TextInput::make('requirement')
                                    ->placeholder('e.g., Bachelor\'s degree in related field')
                            )
                            ->addActionLabel('Add Requirement')
                            ->defaultItems(0)
                            ->reorderable(),

                        Repeater::make('benefits')
                            ->simple(
                                TextInput::make('benefit')
                                    ->placeholder('e.g., Health insurance, Flexible hours')
                            )
                            ->addActionLabel('Add Benefit')
                            ->defaultItems(0)
                            ->reorderable(),
                    ])
                    ->columns(2)
                    ->collapsible(),






            ]);
    }
}
