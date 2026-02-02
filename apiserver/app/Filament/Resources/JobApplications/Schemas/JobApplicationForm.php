<?php

namespace App\Filament\Resources\JobApplications\Schemas;

use App\Casts\JobApplicationStatusCast;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class JobApplicationForm
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
                        Section::make('Application')
                            ->description('Recruitment, applicant, guardian and address')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                ])->schema([
                                    TextInput::make('uuid')
                                        ->label('UUID')
                                        ->required()
                                        ->maxLength(36)
                                        ->placeholder('Unique identifier')
                                        ->readOnly(fn (string $operation): bool => $operation === 'edit'),

                                    Select::make('recruitment_id')
                                        ->label('Recruitment')
                                        ->relationship('recruitment', 'title')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->placeholder('Select recruitment'),
                                ]),

                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                ])->schema([
                                    Select::make('applicant_type')
                                        ->label('Applicant Type')
                                        ->options([
                                            User::class => 'User',
                                        ])
                                        ->required()
                                        ->default(User::class)
                                        ->live()
                                        ->afterStateUpdated(fn (Set $set) => $set('applicant_id', null)),

                                    Select::make('applicant_id')
                                        ->label('Applicant')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Search user by name/email...')
                                        ->options(function (Get $get): array {
                                            // show initial options only when type is User
                                            if ($get('applicant_type') !== User::class) {
                                                return [];
                                            }

                                            return User::query()
                                                ->orderBy('name')
                                                ->limit(50)
                                                ->pluck('name', 'id')
                                                ->all();
                                        })
                                        ->getSearchResultsUsing(function (string $search, Get $get): array {
                                            if ($get('applicant_type') !== User::class) {
                                                return [];
                                            }

                                            return User::query()
                                                ->where('name', 'like', "%{$search}%")
                                                ->orWhere('email', 'like', "%{$search}%")
                                                ->orderBy('name')
                                                ->limit(50)
                                                ->pluck('name', 'id')
                                                ->all();
                                        })
                                        ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->name)
                                        ->visible(fn (Get $get) => $get('applicant_type') === User::class),



        TextInput::make('guardian_name')
                                        ->label('Guardian Name')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Full name'),
                                ]),

                                Select::make('address_id')
                                    ->label('Address')
                                    ->relationship('address', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select address')
                                    ->getOptionLabelFromRecordUsing(
                                        fn ($record) => $record->title ?: ('Address #' . $record->getKey())
                                    ),
                            ])
                            ->compact(),

                        Section::make('Profile')
                            ->description('Education, skills and experience (JSON)')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([

                                Repeater::make('educations')
                                    ->label('Educations')
                                    ->schema([
                                        TextInput::make('key')
                                            ->label('Field')
                                            ->placeholder('degree / institute / year')
                                            ->required()
                                            ->maxLength(80),

                                        TextInput::make('value')
                                            ->label('Value')
                                            ->placeholder('B.Sc / XYZ College / 2022')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Add Education Field')
                                    ->defaultItems(0)
                                    ->reorderable(),

                                Repeater::make('skills')
                                    ->label('Skills')
                                    ->schema([
                                        TextInput::make('key')
                                            ->label('Skill')
                                            ->placeholder('Laravel / Sales / Excel')
                                            ->required()
                                            ->maxLength(80),

                                        TextInput::make('value')
                                            ->label('Level / Notes')
                                            ->placeholder('Beginner / Intermediate / Expert')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Add Skill')
                                    ->defaultItems(0)
                                    ->reorderable(),

                                Repeater::make('experiences')
                                    ->label('Experiences')
                                    ->schema([
                                        TextInput::make('key')
                                            ->label('Title')
                                            ->placeholder('Company / Role / Duration')
                                            ->required()
                                            ->maxLength(120),

                                        TextInput::make('value')
                                            ->label('Details')
                                            ->placeholder('Worked on X, handled Y, 2021–2024')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Add Experience')
                                    ->defaultItems(0)
                                    ->reorderable(),
                            ])
                            ->collapsible()
                            ->compact(),


                        Section::make('References')
                            ->description('Reference person details')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                ])->schema([
                                    TextInput::make('reference_name')
                                        ->label('Reference Name')
                                        ->maxLength(255)
                                        ->placeholder('Optional'),

                                    TextInput::make('reference_contact')
                                        ->label('Reference Contact')
                                        ->maxLength(255)
                                        ->placeholder('Phone / email (optional)'),
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
                        Section::make('Payment')
                            ->description('Fee payment and transaction')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 1, // sidebar single column on desktop
                                ])->schema([
                                    Toggle::make('is_paid')
                                        ->label('Paid')
                                        ->required()
                                        ->onIcon('heroicon-o-check-circle')
                                        ->offIcon('heroicon-o-x-circle'),

                                    TextInput::make('amount')
                                        ->label('Amount')
                                        ->required()
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(0)
                                        ->prefix('₹'),
                                ]),

                                Select::make('transaction_id')
                                    ->label('Transaction')
                                    ->relationship('transaction', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select transaction'),
                            ])
                            ->compact()
                            ->collapsible(),

                        Section::make('Status')
                            ->description('Workflow status and feedback')
                            ->icon('heroicon-o-flag')
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options(JobApplicationStatusCast::class)
                                    ->default('draft')
                                    ->required(),

                                Textarea::make('status_feedback')
                                    ->label('Status Feedback')
                                    ->rows(3)
                                    ->placeholder('Optional notes...')
                                    ->columnSpanFull(),

                                DateTimePicker::make('submitted_at')
                                    ->label('Submitted At'),
                            ])
                            ->compact(),

                        Section::make('Import')
                            ->description('Optional import fields')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->schema([
                                TextInput::make('import_batch_id')
                                    ->label('Import Batch ID')
                                    ->placeholder('Optional'),

                                Textarea::make('import_data')
                                    ->label('Import Data')
                                    ->rows(3)
                                    ->placeholder('Raw import payload / JSON...')
                                    ->columnSpanFull(),
                            ])
                            ->collapsed()
                            ->collapsible()
                            ->compact(),
                    ]),
            ])
                ->columnSpanFull()
                ->extraAttributes(['class' => 'gap-6']),
        ]);
    }
}
